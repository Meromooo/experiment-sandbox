<?php
/**
 * The request email (AMM-140, stage 1): what a branch manager receives when a
 * buyer sends the Contact page's request form, and an admin-only page that
 * previews it.
 *
 * One email, no attachment (Ammar, 2026-10-18): the buyer's details, the
 * parts table, and one footer line in Riyadh time. Built as tables with
 * inline styles, the only layout Gmail and Outlook both show the same, in
 * the theme.json palette. Everything a buyer typed is escaped here, and
 * carries dir="auto" so Arabic reads right to left. Part names, numbers and
 * links come from the store (the handler re-reads each part by ID), never
 * from the browser.
 *
 * Nothing here sends mail. The handler (stage 2, inc/request.php) builds the
 * email with demas_theme_request_email() and sends it through Microsoft
 * Graph.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * What the buyer can ask for, as the request form's choices name them
 * (src/request-form/render.php).
 */
function demas_theme_request_needs(): array {
	return array(
		'parts'  => __( 'Parts & prices', 'demas-theme' ),
		'survey' => __( 'Site visit', 'demas-theme' ),
		'repair' => __( 'Repair & maintenance', 'demas-theme' ),
		'other'  => __( 'Something else', 'demas-theme' ),
	);
}

/**
 * The email for one request: its subject and its HTML body.
 *
 * @param array $request {
 *     A checked request; the handler validates every value before this.
 *
 *     @type int    $ref     The request's reference number.
 *     @type string $branch  A branch code from demas_theme_get_branches().
 *     @type string $need    A key of demas_theme_request_needs().
 *     @type string $name    The buyer's name.
 *     @type string $phone   The buyer's phone number.
 *     @type string $email   The buyer's email address, or ''.
 *     @type string $company The buyer's company, or ''.
 *     @type string $message The buyer's message, or ''.
 *     @type array  $lines   Parts, each { name, sku, url, qty }, read from the store.
 *     @type int    $time    When it was sent, as a Unix timestamp.
 * }
 * @return array { subject: string, html: string }
 */
function demas_theme_request_email( array $request ): array {
	$branches = demas_theme_get_branches();
	$needs    = demas_theme_request_needs();
	$city     = (string) ( $branches[ $request['branch'] ]['city'] ?? '' );
	$need     = (string) ( $needs[ $request['need'] ] ?? '' );
	$lines    = (array) $request['lines'];
	$count    = count( $lines );
	$ref      = sprintf( '%04d', (int) $request['ref'] );
	$email    = is_email( $request['email'] ) ? (string) $request['email'] : '';

	if ( $count ) {
		$subject = sprintf(
			/* translators: 1: number of parts, 2: city, 3: reference number */
			_n( 'Price request: %1$s part, %2$s branch (ref. %3$s)', 'Price request: %1$s parts, %2$s branch (ref. %3$s)', $count, 'demas-theme' ),
			number_format_i18n( $count ),
			$city,
			$ref
		);
	} else {
		$subject = sprintf(
			/* translators: 1: what the buyer needs, 2: city, 3: reference number */
			__( '%1$s: %2$s branch (ref. %3$s)', 'demas-theme' ),
			$need,
			$city,
			$ref
		);
	}

	// The theme.json palette: paper, sand, field (sky), canopy (indigo), ink, water.
	$paper  = '#FAF9F6';
	$sand   = '#E8DFCE';
	$field  = '#6BB3DE';
	$canopy = '#232D8E';
	$ink    = '#0F1433';
	$water  = '#1A79AB';
	$muted  = '#4A4F66';
	$font   = "font-family:Arial,'Helvetica Neue',Helvetica,sans-serif;";
	$mono   = "font-family:Consolas,'Courier New',monospace;";
	$cell   = 'padding:12px 0;border-bottom:1px solid ' . $sand . ';vertical-align:top;';
	$label  = $cell . $font . 'width:132px;padding-right:16px;font-size:14px;line-height:22px;color:' . $muted . ';';
	$value  = $cell . $font . 'font-size:17px;line-height:26px;color:' . $ink . ';word-break:break-word;';
	$link   = 'color:' . $water . ';text-decoration:underline;';
	$th     = $font . 'padding:10px 12px;font-size:13px;line-height:18px;font-weight:bold;color:' . $ink . ';background:' . $sand . ';text-align:left;';
	$td     = $font . 'padding:12px;border-bottom:1px solid ' . $sand . ';font-size:16px;line-height:24px;color:' . $ink . ';vertical-align:top;word-break:break-word;';

	$buyer = array(
		array( __( 'Name', 'demas-theme' ), esc_html( $request['name'] ), true ),
		array( __( 'Company', 'demas-theme' ), esc_html( $request['company'] ), true ),
		array(
			__( 'Phone', 'demas-theme' ),
			'<a href="' . esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $request['phone'] ) ) . '" style="' . $link . '">' . esc_html( $request['phone'] ) . '</a>',
			false,
		),
		array(
			__( 'Email', 'demas-theme' ),
			$email ? '<a href="' . esc_url( 'mailto:' . $email ) . '" style="' . $link . '">' . esc_html( $email ) . '</a>' : '',
			false,
		),
		array( __( 'Needs', 'demas-theme' ), esc_html( $need ), false ),
		array( __( 'Message', 'demas-theme' ), nl2br( esc_html( $request['message'] ), false ), true ),
	);

	if ( $email ) {
		/* translators: %s: the buyer's name */
		$answer = sprintf( __( 'Reply to this email to answer %s.', 'demas-theme' ), $request['name'] );
	} else {
		/* translators: %s: the buyer's phone number */
		$answer = sprintf( __( 'No email given: call %s to answer.', 'demas-theme' ), $request['phone'] );
	}

	/* translators: %s: date and time */
	$sent = sprintf( __( 'Sent from the Demas website on %s, Riyadh time.', 'demas-theme' ), wp_date( 'j F Y, H:i', (int) $request['time'], new DateTimeZone( 'Asia/Riyadh' ) ) );

	ob_start();
	?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo esc_html( $subject ); ?></title>
</head>
<body style="margin:0;padding:0;background:<?php echo esc_attr( $paper ); ?>;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:<?php echo esc_attr( $paper ); ?>;">
<tr><td align="center" style="padding:24px 12px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:640px;">
<tr><td style="padding:24px 28px;background:<?php echo esc_attr( $canopy ); ?>;border-radius:12px 12px 0 0;">
	<p style="margin:0 0 6px;<?php echo esc_attr( $font ); ?>font-size:15px;line-height:22px;font-weight:bold;color:<?php echo esc_attr( $field ); ?>;"><?php echo esc_html( $need ); ?></p>
	<h1 style="margin:0;<?php echo esc_attr( $font ); ?>font-size:26px;line-height:32px;font-weight:bold;color:<?php echo esc_attr( $paper ); ?>;">
		<?php
		/* translators: %s: city */
		echo esc_html( sprintf( __( '%s branch', 'demas-theme' ), $city ) );
		?>
	</h1>
	<p style="margin:6px 0 0;<?php echo esc_attr( $font ); ?>font-size:16px;line-height:24px;color:<?php echo esc_attr( $paper ); ?>;">
		<?php
		/* translators: %s: reference number */
		echo esc_html( sprintf( __( 'Ref. %s', 'demas-theme' ), $ref ) );
		?>
	</p>
</td></tr>
<tr><td style="padding:8px 28px 28px;background:#FFFFFF;border:1px solid <?php echo esc_attr( $sand ); ?>;border-top:0;border-radius:0 0 12px 12px;">
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
		<?php foreach ( $buyer as $row ) : ?>
			<?php
			if ( '' === $row[1] ) {
				continue;
			}
			?>
		<tr>
			<td style="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $row[0] ); ?></td>
			<td style="<?php echo esc_attr( $value ); ?>"<?php echo $row[2] ? ' dir="auto"' : ''; ?>><?php echo wp_kses_post( $row[1] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<p style="margin:16px 0 0;<?php echo esc_attr( $font ); ?>font-size:15px;line-height:22px;color:<?php echo esc_attr( $muted ); ?>;"><?php echo esc_html( $answer ); ?></p>
	<?php if ( $count ) : ?>
	<h2 style="margin:32px 0 12px;<?php echo esc_attr( $font ); ?>font-size:20px;line-height:26px;font-weight:bold;color:<?php echo esc_attr( $canopy ); ?>;">
		<?php
		/* translators: %s: number of parts */
		echo esc_html( sprintf( _n( '%s part', '%s parts', $count, 'demas-theme' ), number_format_i18n( $count ) ) );
		?>
	</h2>
	<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">
		<thead>
			<tr>
				<th scope="col" style="<?php echo esc_attr( $th ); ?>width:28px;">#</th>
				<th scope="col" style="<?php echo esc_attr( $th ); ?>"><?php esc_html_e( 'Part', 'demas-theme' ); ?></th>
				<th scope="col" style="<?php echo esc_attr( $th ); ?>"><?php esc_html_e( 'Part number', 'demas-theme' ); ?></th>
				<th scope="col" style="<?php echo esc_attr( $th ); ?>text-align:right;"><?php esc_html_e( 'Qty', 'demas-theme' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( array_values( $lines ) as $i => $line ) : ?>
			<tr>
				<td style="<?php echo esc_attr( $td . 'color:' . $muted . ';' ); ?>"><?php echo esc_html( number_format_i18n( $i + 1 ) ); ?></td>
				<td style="<?php echo esc_attr( $td ); ?>" dir="auto"><a href="<?php echo esc_url( $line['url'] ); ?>" style="<?php echo esc_attr( $link ); ?>"><?php echo esc_html( $line['name'] ); ?></a></td>
				<td style="<?php echo esc_attr( $td . $mono . 'font-size:15px;white-space:nowrap;' ); ?>"><?php echo esc_html( $line['sku'] ); ?></td>
				<td style="<?php echo esc_attr( $td . 'text-align:right;font-weight:bold;white-space:nowrap;' ); ?>"><?php echo esc_html( number_format_i18n( (int) $line['qty'] ) ); ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php endif; ?>
</td></tr>
<tr><td style="padding:16px 28px 0;<?php echo esc_attr( $font ); ?>font-size:13px;line-height:20px;color:<?php echo esc_attr( $muted ); ?>;"><?php echo esc_html( $sent ); ?></td></tr>
</table>
</td></tr>
</table>
</body>
</html>
	<?php

	return array(
		'subject' => $subject,
		'html'    => (string) ob_get_clean(),
	);
}

/**
 * One parts line for the email, read from the store: the handler and the
 * preview build lines the same way.
 *
 * @param WC_Product $product A published part.
 * @param int        $qty     How many the buyer wants.
 * @return array { name, sku, url, qty }
 */
function demas_theme_request_line( WC_Product $product, int $qty ): array {
	return array(
		'name' => html_entity_decode( $product->get_name(), ENT_QUOTES, 'UTF-8' ),
		'sku'  => $product->get_sku(),
		'url'  => (string) get_permalink( $product->get_id() ),
		'qty'  => $qty,
	);
}

/*
 * The preview: Tools > Request email, administrators only. It shows the email
 * for three made-up requests built from real published parts, the settings
 * the handler will read (present or not, never their values), and the
 * visitor address the server sees, so the rate limit (stage 2) knows whether
 * the CDN hides it. It sends nothing and stores nothing.
 */
add_action(
	'admin_menu',
	function () {
		add_management_page(
			__( 'Request email preview', 'demas-theme' ),
			__( 'Request email', 'demas-theme' ),
			'manage_options',
			'demas-request-email',
			'demas_theme_request_preview_page'
		);
	}
);

/**
 * Draws the preview page.
 */
function demas_theme_request_preview_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$products = function_exists( 'wc_get_products' )
		? wc_get_products(
			array(
				'status' => 'publish',
				'limit'  => 200,
			)
		)
		: array();

	// The worst case: the five longest names, repeated to 40 lines.
	$longest = $products;
	usort( $longest, static fn( $a, $b ) => mb_strlen( $b->get_name() ) <=> mb_strlen( $a->get_name() ) );
	$longest = array_slice( $longest, 0, 5 );
	$worst   = array();
	for ( $i = 0; $longest && $i < 40; $i++ ) {
		$worst[] = demas_theme_request_line( $longest[ $i % count( $longest ) ], 0 === $i % 3 ? 9999 : $i + 1 );
	}

	// The buyer's email is your own, read at runtime: none is written in the repo.
	$email = (string) wp_get_current_user()->user_email;
	$base  = array(
		'branch'  => 'jed',
		'email'   => $email,
		'company' => 'Al Waha Landscaping',
		'time'    => time(),
	);

	$samples = array(
		__( 'Parts & prices, three parts', 'demas-theme' ) => $base + array(
			'ref'     => 1042,
			'need'    => 'parts',
			'name'    => 'Faisal Al-Harbi',
			'phone'   => '050 123 4567',
			'message' => 'Please include delivery to our site in Obhur.',
			'lines'   => array_map( 'demas_theme_request_line', array_slice( $products, 0, 3 ), array_slice( array( 4, 12, 1 ), 0, min( 3, count( $products ) ) ) ),
		),
		__( 'Site visit, no parts, no email', 'demas-theme' ) => array(
			'ref'     => 1043,
			'need'    => 'survey',
			'name'    => 'Noura Al-Qahtani',
			'phone'   => '+966 55 987 6543',
			'email'   => '',
			'message' => "A villa garden in Al Shati, about 600 square metres.\nThe drip lines stopped working last week.",
			'lines'   => array(),
		) + $base,
		__( 'Worst case: 40 lines, longest names, long Arabic and English text', 'demas-theme' ) => array(
			'ref'     => 99999,
			'need'    => 'repair',
			'name'    => 'عبدالرحمن بن عبدالعزيز بن عبدالله آل عبدالكريم الشمري',
			'phone'   => '+966 (0) 50 123 4567 89',
			'company' => 'Kingdom Agricultural Development and Landscape Contracting Company Limited',
			'message' => str_repeat( "نحتاج إلى عرض سعر لقطع الغيار المذكورة أدناه مع التوصيل إلى الموقع. We need the parts below delivered to the farm before the end of the month, with an invoice to the head office.\n\n", 12 ) . str_repeat( 'Supercalifragilisticexpialidocious', 4 ),
			'lines'   => $worst,
		) + $base,
	);

	$settings = array(
		'DEMAS_GRAPH_TENANT'     => __( 'Microsoft 365 tenant ID', 'demas-theme' ),
		'DEMAS_GRAPH_CLIENT_ID'  => __( 'App (client) ID', 'demas-theme' ),
		'DEMAS_GRAPH_SECRET'     => __( 'Client secret', 'demas-theme' ),
		'DEMAS_GRAPH_SENDER'     => __( 'The website mailbox that sends', 'demas-theme' ),
		'DEMAS_BRANCH_MAILBOXES' => __( 'Branch code to address, plus "default" for head office', 'demas-theme' ),
		'DEMAS_REQUEST_TEST_TO'  => __( 'Sandbox only: one test address that receives every email', 'demas-theme' ),
	);

	$mailboxes = defined( 'DEMAS_BRANCH_MAILBOXES' ) && is_array( constant( 'DEMAS_BRANCH_MAILBOXES' ) ) ? constant( 'DEMAS_BRANCH_MAILBOXES' ) : array();
	$covered   = count( array_intersect_key( $mailboxes, demas_theme_get_branches() ) );

	$headers = array( 'REMOTE_ADDR', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CF_CONNECTING_IP', 'HTTP_TRUE_CLIENT_IP', 'HTTP_X_CLIENT_IP' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Request email preview', 'demas-theme' ); ?></h1>
		<p><?php esc_html_e( 'What a branch receives when a buyer sends the request form. The parts are real published products; the buyers are made up, with your own email address. Nothing is sent from this page.', 'demas-theme' ); ?></p>

		<?php if ( ! $products ) : ?>
			<div class="notice notice-warning inline"><p><?php esc_html_e( 'No published products found, so the samples have no parts.', 'demas-theme' ); ?></p></div>
		<?php endif; ?>

		<?php foreach ( $samples as $title => $request ) : ?>
			<?php $mail = demas_theme_request_email( $request ); ?>
			<h2><?php echo esc_html( $title ); ?></h2>
			<p><strong><?php esc_html_e( 'Subject:', 'demas-theme' ); ?></strong> <?php echo esc_html( $mail['subject'] ); ?></p>
			<div style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-start;">
				<iframe title="<?php echo esc_attr( $title . ', ' . __( 'desktop width', 'demas-theme' ) ); ?>" sandbox="" srcdoc="<?php echo esc_attr( $mail['html'] ); ?>" style="width:700px;max-width:100%;height:720px;border:1px solid #c3c4c7;background:#fff;"></iframe>
				<iframe title="<?php echo esc_attr( $title . ', ' . __( 'phone width', 'demas-theme' ) ); ?>" sandbox="" srcdoc="<?php echo esc_attr( $mail['html'] ); ?>" style="width:375px;max-width:100%;height:720px;border:1px solid #c3c4c7;background:#fff;"></iframe>
			</div>
		<?php endforeach; ?>

		<h2><?php esc_html_e( 'Settings', 'demas-theme' ); ?></h2>
		<p><?php esc_html_e( 'Read from wp-config.php by the handler. Only whether each one exists is shown, never its value. Without all but the last, the form keeps saying sending is not connected.', 'demas-theme' ); ?></p>
		<table class="widefat striped" style="max-width:760px;">
			<tbody>
				<?php foreach ( $settings as $name => $what ) : ?>
				<tr>
					<td><code><?php echo esc_html( $name ); ?></code></td>
					<td><?php echo esc_html( $what ); ?></td>
					<td><?php echo defined( $name ) ? esc_html__( 'Set', 'demas-theme' ) : esc_html__( 'Not set', 'demas-theme' ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php if ( $mailboxes ) : ?>
			<p>
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: branches with an address, 2: all branches, 3: yes or no */
						__( 'Branches with an address: %1$d of %2$d. Head office fallback: %3$s.', 'demas-theme' ),
						$covered,
						count( demas_theme_get_branches() ),
						isset( $mailboxes['default'] ) ? __( 'yes', 'demas-theme' ) : __( 'no', 'demas-theme' )
					)
				);
				?>
			</p>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Your visitor address, as the server sees it', 'demas-theme' ); ?></h2>
		<p><?php esc_html_e( 'The rate limit counts requests per visitor. If REMOTE_ADDR is your own address, it can use that. If it is a Hostinger or CDN address, every visitor looks the same there, and the real one must come from a forwarding header the CDN sets.', 'demas-theme' ); ?></p>
		<table class="widefat striped" style="max-width:760px;">
			<tbody>
				<?php foreach ( $headers as $header ) : ?>
				<tr>
					<td><code><?php echo esc_html( $header ); ?></code></td>
					<td><?php echo isset( $_SERVER[ $header ] ) ? esc_html( sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) ) : esc_html__( 'Not sent', 'demas-theme' ); ?></td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
