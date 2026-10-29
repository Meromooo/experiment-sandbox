<?php
/**
 * The request form's handler (AMM-140, stage 2): POST
 * /wp-json/demas-theme/v1/request with JSON
 * { need, branch, name, phone, email, company, message, items: [ { id, qty } ], hp }.
 *
 * In order: a filled honeypot gets a quiet fake success; every field is
 * checked here, never trusted from the browser (400 { code: invalid,
 * fields }); without the Microsoft 365 settings it answers 503
 * { code: not_connected }; 5 requests an hour per visitor and 200 an hour
 * site-wide (429 { code: busy }; the site-wide cap is a spend backstop that
 * logs one line when reached, AMM-236); then the next ref, the email from
 * inc/request-email.php, and Microsoft Graph's sendMail (200 { ref, branch },
 * or 502 { code: not_sent }).
 *
 * No nonce (Ammar, 2026-10-21): pages are cached and shared, so a nonce in
 * the HTML would be stale for most visitors, and the form needs no login,
 * so a forged post can do only what any visitor can, within the rate limit.
 *
 * Nothing about a request is kept in WordPress: the email (and the copy in
 * the sending mailbox's Sent Items) is the record. WordPress keeps only the
 * ref counter and the hour-long rate-limit counts, keyed by a hash of the
 * visitor's address. The addresses, the client secret and its token never
 * leave wp-config.php and memory; the browser only ever names a branch code.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'demas-theme/v1',
			'/request',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'permission_callback' => '__return_true',
				'callback'            => 'demas_theme_request_handle',
			)
		);
	}
);

/**
 * Handles one request from the form.
 *
 * @param WP_REST_Request $request The posted request.
 */
function demas_theme_request_handle( WP_REST_Request $request ): WP_REST_Response {
	$data     = (array) $request->get_json_params();
	$branches = demas_theme_get_branches();
	$branch   = is_string( $data['branch'] ?? null ) && isset( $branches[ $data['branch'] ] ) ? $data['branch'] : '';

	// A bot filled the field people never see: let it think it worked.
	if ( ! empty( $data['hp'] ) ) {
		return new WP_REST_Response(
			array(
				'ref'    => sprintf( '%04d', wp_rand( 1001, 9999 ) ),
				'branch' => $branch,
			)
		);
	}

	$fields = array();
	$need   = is_string( $data['need'] ?? null ) && isset( demas_theme_request_needs()[ $data['need'] ] ) ? $data['need'] : '';
	$name   = demas_theme_request_text( $data['name'] ?? '' );
	$phone  = demas_theme_request_text( $data['phone'] ?? '' );
	$email  = demas_theme_request_text( $data['email'] ?? '' );
	$org    = demas_theme_request_text( $data['company'] ?? '' );
	$text   = demas_theme_request_text( $data['message'] ?? '', true );
	$digits = strlen( preg_replace( '/\D/', '', $phone ) );

	if ( '' === $need ) {
		$fields[] = 'need';
	}
	if ( '' === $branch ) {
		$fields[] = 'branch';
	}
	if ( '' === $name || mb_strlen( $name ) > 100 ) {
		$fields[] = 'name';
	}
	// The form's own rule (src/request-form/view.ts): digits, spaces and + ( ) -, 9 to 15 digits.
	if ( ! preg_match( '/^[+\d\s()-]+$/', $phone ) || $digits < 9 || $digits > 15 || strlen( $phone ) > 30 ) {
		$fields[] = 'phone';
	}
	if ( '' !== $email && ( ! is_email( $email ) || strlen( $email ) > 254 ) ) {
		$fields[] = 'email';
	}
	if ( mb_strlen( $org ) > 150 ) {
		$fields[] = 'company';
	}

	// Parts are re-read from the store; a part unpublished since it was listed is dropped.
	$items = $data['items'] ?? array();
	$lines = array();
	if ( ! is_array( $items ) || count( $items ) > 200 ) {
		$fields[] = 'items';
		$items    = array();
	}
	// One query for every listed part instead of one per line: this runs before the rate limit.
	_prime_post_caches( array_map( static fn( $item ) => absint( is_array( $item ) ? ( $item['id'] ?? 0 ) : 0 ), $items ) );
	foreach ( $items as $item ) {
		$qty = filter_var(
			is_array( $item ) ? ( $item['qty'] ?? null ) : null,
			FILTER_VALIDATE_INT,
			array(
				'options' => array(
					'min_range' => 1,
					'max_range' => 9999,
				),
			)
		);
		if ( false === $qty ) {
			$fields[] = 'items';
			break;
		}
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( absint( $item['id'] ?? 0 ) ) : null;
		if ( $product && 'publish' === $product->get_status() ) {
			$lines[] = demas_theme_request_line( $product, $qty );
		}
	}

	// The message may be empty only when the parts list carries the request (AMM-209).
	if ( ( '' === $text && ! ( 'parts' === $need && $lines ) ) || mb_strlen( $text ) > 4000 ) {
		$fields[] = 'message';
	}

	if ( $fields ) {
		return new WP_REST_Response(
			array(
				'code'   => 'invalid',
				'fields' => array_values( array_unique( $fields ) ),
			),
			400
		);
	}

	$to = demas_theme_request_recipient( $branch );
	if ( '' === $to ) {
		return new WP_REST_Response( array( 'code' => 'not_connected' ), 503 );
	}

	/*
	 * REMOTE_ADDR is the visitor's own address on this host, even behind the
	 * CDN (measured on the sandbox, 2026-10-21), so no forwarded header is
	 * read. shortcut: each count lasts an hour from its last request, and
	 * buyers behind one shared phone-network address share their 5; raise the
	 * numbers if that ever turns a buyer away. The site-wide 200 is set far
	 * above a real busy hour so a few addresses can't fill it for everyone
	 * (AMM-236); it logs once when it first fills in a window.
	 */
	$address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$limits  = array(
		'demas_theme_rq_' . wp_hash( $address ) => 5,
		'demas_theme_rq_site'                   => 200,
	);
	foreach ( $limits as $key => $max ) {
		if ( (int) get_transient( $key ) >= $max ) {
			return new WP_REST_Response( array( 'code' => 'busy' ), 429 );
		}
	}
	foreach ( $limits as $key => $max ) {
		$count = (int) get_transient( $key ) + 1;
		set_transient( $key, $count, HOUR_IN_SECONDS );
		if ( 'demas_theme_rq_site' === $key && $max === $count ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- the only trace that the site-wide cap filled; the limit only.
			error_log( 'Demas request: site-wide limit of ' . $max . ' an hour reached; further requests get 429 until it resets' );
		}
	}

	// shortcut: two requests in the same instant can share a ref; harmless, the email says which branch.
	$ref = (int) get_option( 'demas_theme_request_ref', 1000 ) + 1;
	update_option( 'demas_theme_request_ref', $ref, false );

	$mail = demas_theme_request_email(
		array(
			'ref'     => $ref,
			'branch'  => $branch,
			'need'    => $need,
			'name'    => $name,
			'phone'   => $phone,
			'email'   => $email,
			'company' => $org,
			'message' => $text,
			'lines'   => $lines,
			'time'    => time(),
		)
	);

	if ( ! demas_theme_request_send( $to, $email, $mail ) ) {
		return new WP_REST_Response( array( 'code' => 'not_sent' ), 502 );
	}

	return new WP_REST_Response(
		array(
			'ref'    => sprintf( '%04d', $ref ),
			'branch' => $branch,
		)
	);
}

/**
 * A typed value as plain text. Not sanitize_text_field(): it would turn a
 * buyer's "< 50 mm" into an entity, and the email escapes everything anyway.
 *
 * @param mixed $value     The posted value.
 * @param bool  $multiline Keep line breaks (the message).
 */
function demas_theme_request_text( $value, bool $multiline = false ): string {
	if ( ! is_string( $value ) ) {
		return '';
	}
	$value = wp_check_invalid_utf8( $value );
	$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', str_replace( "\r\n", "\n", $value ) );
	$value = $multiline
		? preg_replace( '/\n{3,}/', "\n\n", str_replace( "\r", "\n", $value ) )
		: preg_replace( '/\s+/u', ' ', $value );

	return trim( (string) $value );
}

/**
 * Where a branch's requests go, from wp-config.php: the sandbox's test
 * address when set, else the branch's mailbox, else head office's
 * ("default"). '' until every Microsoft 365 setting is there.
 *
 * @param string $branch A branch code.
 */
function demas_theme_request_recipient( string $branch ): string {
	foreach ( array( 'DEMAS_GRAPH_TENANT', 'DEMAS_GRAPH_CLIENT_ID', 'DEMAS_GRAPH_SECRET', 'DEMAS_GRAPH_SENDER', 'DEMAS_BRANCH_MAILBOXES' ) as $setting ) {
		if ( ! defined( $setting ) ) {
			return '';
		}
	}

	$boxes = (array) constant( 'DEMAS_BRANCH_MAILBOXES' );
	$to    = defined( 'DEMAS_REQUEST_TEST_TO' ) ? constant( 'DEMAS_REQUEST_TEST_TO' ) : ( $boxes[ $branch ] ?? $boxes['default'] ?? '' );

	return is_string( $to ) && is_email( $to ) ? $to : '';
}

/**
 * Sends the email through Microsoft Graph: a token for the website's app
 * (client credentials), then sendMail as the sending mailbox. The token is
 * used once and never stored. Failures are logged by status code only,
 * never with the buyer's details.
 *
 * @param string $to       The recipient.
 * @param string $reply_to The buyer's email, or ''.
 * @param array  $mail     { subject, html } from demas_theme_request_email().
 */
function demas_theme_request_send( string $to, string $reply_to, array $mail ): bool {
	$token  = wp_remote_post(
		'https://login.microsoftonline.com/' . rawurlencode( (string) constant( 'DEMAS_GRAPH_TENANT' ) ) . '/oauth2/v2.0/token',
		array(
			'timeout' => 10,
			'body'    => array(
				'client_id'     => (string) constant( 'DEMAS_GRAPH_CLIENT_ID' ),
				'client_secret' => (string) constant( 'DEMAS_GRAPH_SECRET' ),
				'scope'         => 'https://graph.microsoft.com/.default',
				'grant_type'    => 'client_credentials',
			),
		)
	);
	$access = json_decode( (string) wp_remote_retrieve_body( $token ), true )['access_token'] ?? '';
	if ( ! is_string( $access ) || '' === $access ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- the only trace of a failed send; status code only.
		error_log( 'Demas request: no Microsoft 365 token (HTTP ' . wp_remote_retrieve_response_code( $token ) . ')' );
		return false;
	}

	$message = array(
		'subject'      => $mail['subject'],
		'body'         => array(
			'contentType' => 'HTML',
			'content'     => $mail['html'],
		),
		'toRecipients' => array( array( 'emailAddress' => array( 'address' => $to ) ) ),
	);
	if ( is_email( $reply_to ) ) {
		$message['replyTo'] = array( array( 'emailAddress' => array( 'address' => $reply_to ) ) );
	}

	$sent = wp_remote_post(
		'https://graph.microsoft.com/v1.0/users/' . rawurlencode( (string) constant( 'DEMAS_GRAPH_SENDER' ) ) . '/sendMail',
		array(
			'timeout' => 10,
			'headers' => array(
				'Authorization' => 'Bearer ' . $access,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'message'         => $message,
					'saveToSentItems' => true,
				)
			),
		)
	);
	if ( 202 !== (int) wp_remote_retrieve_response_code( $sent ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- the only trace of a failed send; status code only.
		error_log( 'Demas request: Microsoft 365 did not send (HTTP ' . wp_remote_retrieve_response_code( $sent ) . ')' );
		return false;
	}

	return true;
}
