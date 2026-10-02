<?php
/**
 * Server-rendered markup for the Request Form (AMM-169, step 2): the Contact
 * page's request — what the buyer needs, the branch it goes to, their
 * details, their message, and their quote list when they have one.
 *
 * Front end only. Sending is AMM-140: a server-side handler that resolves
 * the branch code to an address held outside version control (the form
 * posts a branch code, never an address), with a nonce, rate limiting and
 * a honeypot, after the privacy notice (AMM-162) is in place. Until then
 * the view module (view.ts) checks the form and, when it is complete, says
 * plainly that sending is not connected and offers head office's number and
 * the branch's directions instead. Nothing typed leaves the browser.
 *
 * Without JavaScript the Send button stays disabled, and a note says to call
 * head office: there is nothing on the server to receive the form yet.
 *
 * The branch starts as the Branch Finder's — the main branch, or the one
 * ?branch= names — and the two stay in step through the demas-theme:branch
 * event. ?need= (parts, survey, repair, other) picks what the buyer needs,
 * so "Request a site visit" elsewhere can arrive with Site visit chosen.
 *
 * @param array    $attributes Block attributes (none).
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'demas_theme_get_branches' ) ) {
	return;
}

$demas_branches = demas_theme_get_branches();

if ( ! $demas_branches ) {
	return;
}

$demas_main = (string) key( $demas_branches );
$demas_list = wp_list_filter( $demas_branches, array( 'main' => true ) );

if ( $demas_list ) {
	$demas_main = (string) key( $demas_list );
}

$demas_needs = array(
	'parts'  => array( __( 'Parts & prices', 'demas-theme' ), __( 'Part numbers or names, and how many of each. Or describe the part you are replacing.', 'demas-theme' ) ),
	'survey' => array( __( 'Site visit', 'demas-theme' ), __( 'Where the site is, what it is (a villa, a landscape, a farm) and roughly how big.', 'demas-theme' ) ),
	'repair' => array( __( 'Repair & maintenance', 'demas-theme' ), __( 'What is wrong, where, and which system you have, if you know.', 'demas-theme' ) ),
	'other'  => array( __( 'Something else', 'demas-theme' ), __( 'Tell us what you need, and the branch takes it from there.', 'demas-theme' ) ),
);

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only display parameters.
$demas_asked_branch = isset( $_GET['branch'] ) ? sanitize_key( wp_unslash( $_GET['branch'] ) ) : '';
$demas_asked_need   = isset( $_GET['need'] ) ? sanitize_key( wp_unslash( $_GET['need'] ) ) : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended
$demas_branch = isset( $demas_branches[ $demas_asked_branch ] ) ? $demas_asked_branch : $demas_main;
$demas_need   = isset( $demas_needs[ $demas_asked_need ] ) ? $demas_asked_need : 'parts';

$demas_strings = array(
	/* translators: %s: city */
	'send'         => __( 'Send to the %s branch', 'demas-theme' ),
	'checkOne'     => __( 'Check 1 field before sending', 'demas-theme' ),
	/* translators: %d: number of fields */
	'checkMany'    => __( 'Check %d fields before sending', 'demas-theme' ),
	'errors'       => array(
		'name'     => __( 'Enter your name.', 'demas-theme' ),
		'phone'    => __( 'Enter a phone number the branch can call.', 'demas-theme' ),
		'phoneBad' => __( 'Enter a phone number with at least 9 digits, such as 05x xxx xxxx.', 'demas-theme' ),
		'email'    => __( 'Enter a full email address, or leave it empty.', 'demas-theme' ),
		'message'  => __( 'Tell us what you need.', 'demas-theme' ),
	),
	'notSentTitle' => __( 'Sending isn’t connected yet', 'demas-theme' ),
	'notSent'      => __( 'Nothing was sent, and everything you typed is still here. Until sending is switched on, call head office or visit the branch.', 'demas-theme' ),
	'call'         => __( 'Call 011 463 4102', 'demas-theme' ),
	'directions'   => __( 'Get directions', 'demas-theme' ),
	/* translators: %s: city */
	'directionsTo' => __( 'to the %s branch, on Google Maps (opens in a new tab)', 'demas-theme' ),
	'partOne'      => __( '1 part', 'demas-theme' ),
	/* translators: %d: number of parts */
	'partMany'     => __( '%d parts', 'demas-theme' ),
	/* translators: %d: number of further parts */
	'more'         => __( 'and %d more', 'demas-theme' ),
);

$demas_id      = wp_unique_id( 'dh-ct-form-' );
$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-ct-form dh-card dh-card--sand' ) );
?>
<form <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> method="post" action="#request" data-request-form data-strings="<?php echo esc_attr( wp_json_encode( $demas_strings ) ); ?>">
	<div class="dh-ct-form__summary" tabindex="-1" data-summary hidden>
		<p class="dh-ct-form__summary-title" data-summary-title></p>
		<ul class="dh-ct-form__summary-list" data-summary-list></ul>
	</div>

	<fieldset class="dh-ct-need">
		<legend class="dh-ct-form__label"><?php esc_html_e( 'What do you need?', 'demas-theme' ); ?></legend>
		<div class="dh-ct-need__options">
			<?php foreach ( $demas_needs as $demas_value => $demas_option ) : ?>
				<label class="dh-ct-need__option">
					<input type="radio" name="need" value="<?php echo esc_attr( $demas_value ); ?>" data-hint="<?php echo esc_attr( $demas_option[1] ); ?>"<?php checked( $demas_need, $demas_value ); ?>>
					<span><?php echo esc_html( $demas_option[0] ); ?></span>
				</label>
			<?php endforeach; ?>
		</div>
	</fieldset>

	<div class="dh-ct-field">
		<label class="dh-ct-form__label" for="<?php echo esc_attr( $demas_id . '-branch' ); ?>"><?php esc_html_e( 'Branch', 'demas-theme' ); ?></label>
		<select class="dh-ct-input" id="<?php echo esc_attr( $demas_id . '-branch' ); ?>" name="branch" data-branch-select>
			<?php foreach ( $demas_branches as $demas_code => $demas_branch_data ) : ?>
				<option value="<?php echo esc_attr( $demas_code ); ?>" data-city="<?php echo esc_attr( $demas_branch_data['city'] ); ?>" data-map="<?php echo esc_url( (string) ( $demas_branch_data['map'] ?? '' ) ); ?>"<?php selected( $demas_branch, $demas_code ); ?>>
					<?php
					echo esc_html(
						empty( $demas_branch_data['main'] )
							? $demas_branch_data['city']
							/* translators: %s: city */
							: sprintf( __( '%s · head office', 'demas-theme' ), $demas_branch_data['city'] )
					);
					?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="dh-ct-form__row">
		<div class="dh-ct-field">
			<label class="dh-ct-form__label" for="<?php echo esc_attr( $demas_id . '-name' ); ?>"><?php esc_html_e( 'Your name', 'demas-theme' ); ?> <span class="dh-ct-form__tag"><?php esc_html_e( 'Required', 'demas-theme' ); ?></span></label>
			<input class="dh-ct-input" id="<?php echo esc_attr( $demas_id . '-name' ); ?>" name="name" type="text" autocomplete="name" required aria-describedby="<?php echo esc_attr( $demas_id . '-name-error' ); ?>" data-field="name">
			<p class="dh-ct-field__error" id="<?php echo esc_attr( $demas_id . '-name-error' ); ?>" data-error-for="name" hidden></p>
		</div>

		<div class="dh-ct-field">
			<label class="dh-ct-form__label" for="<?php echo esc_attr( $demas_id . '-phone' ); ?>"><?php esc_html_e( 'Phone', 'demas-theme' ); ?> <span class="dh-ct-form__tag"><?php esc_html_e( 'Required', 'demas-theme' ); ?></span></label>
			<input class="dh-ct-input" id="<?php echo esc_attr( $demas_id . '-phone' ); ?>" name="phone" type="tel" inputmode="tel" autocomplete="tel" required aria-describedby="<?php echo esc_attr( $demas_id . '-phone-hint ' . $demas_id . '-phone-error' ); ?>" data-field="phone">
			<p class="dh-ct-field__hint" id="<?php echo esc_attr( $demas_id . '-phone-hint' ); ?>"><?php esc_html_e( 'The branch calls you back on this number.', 'demas-theme' ); ?></p>
			<p class="dh-ct-field__error" id="<?php echo esc_attr( $demas_id . '-phone-error' ); ?>" data-error-for="phone" hidden></p>
		</div>
	</div>

	<div class="dh-ct-form__row">
		<div class="dh-ct-field">
			<label class="dh-ct-form__label" for="<?php echo esc_attr( $demas_id . '-email' ); ?>"><?php esc_html_e( 'Email', 'demas-theme' ); ?> <span class="dh-ct-form__tag is-optional"><?php esc_html_e( 'Optional', 'demas-theme' ); ?></span></label>
			<input class="dh-ct-input" id="<?php echo esc_attr( $demas_id . '-email' ); ?>" name="email" type="email" autocomplete="email" aria-describedby="<?php echo esc_attr( $demas_id . '-email-error' ); ?>" data-field="email">
			<p class="dh-ct-field__error" id="<?php echo esc_attr( $demas_id . '-email-error' ); ?>" data-error-for="email" hidden></p>
		</div>

		<div class="dh-ct-field">
			<label class="dh-ct-form__label" for="<?php echo esc_attr( $demas_id . '-company' ); ?>"><?php esc_html_e( 'Company', 'demas-theme' ); ?> <span class="dh-ct-form__tag is-optional"><?php esc_html_e( 'Optional', 'demas-theme' ); ?></span></label>
			<input class="dh-ct-input" id="<?php echo esc_attr( $demas_id . '-company' ); ?>" name="company" type="text" autocomplete="organization">
		</div>
	</div>

	<div class="dh-ct-field">
		<label class="dh-ct-form__label" for="<?php echo esc_attr( $demas_id . '-message' ); ?>"><?php esc_html_e( 'Your request', 'demas-theme' ); ?> <span class="dh-ct-form__tag"><?php esc_html_e( 'Required', 'demas-theme' ); ?></span></label>
		<p class="dh-ct-field__hint" id="<?php echo esc_attr( $demas_id . '-message-hint' ); ?>" data-need-hint><?php echo esc_html( $demas_needs[ $demas_need ][1] ); ?></p>
		<textarea class="dh-ct-input" id="<?php echo esc_attr( $demas_id . '-message' ); ?>" name="message" rows="5" required aria-describedby="<?php echo esc_attr( $demas_id . '-message-hint ' . $demas_id . '-message-error' ); ?>" data-field="message"></textarea>
		<p class="dh-ct-field__error" id="<?php echo esc_attr( $demas_id . '-message-error' ); ?>" data-error-for="message" hidden></p>
	</div>

	<div class="dh-ct-quote" data-quote hidden>
		<label class="dh-ct-quote__include">
			<input type="checkbox" name="quote" value="1" checked>
			<span><?php esc_html_e( 'Include my quote list', 'demas-theme' ); ?> <span class="dh-ct-quote__count" data-quote-count></span></span>
		</label>
		<ul class="dh-ct-quote__lines" data-quote-lines></ul>
	</div>

	<div class="dh-ct-form__foot">
		<button class="dh-pill dh-pill--solid dh-ct-form__send" type="submit" data-send disabled>
			<?php
			/* translators: %s: city */
			printf( esc_html__( 'Send to the %s branch', 'demas-theme' ), esc_html( $demas_branches[ $demas_branch ]['city'] ) );
			?>
		</button>
		<p class="dh-ct-form__privacy"><?php esc_html_e( 'Privacy notice: to come.', 'demas-theme' ); ?></p>
	</div>

	<noscript>
		<p class="dh-ct-form__nojs">
			<?php esc_html_e( 'Sending is not connected yet. Call head office on', 'demas-theme' ); ?>
			<a href="tel:+966114634102">011 463 4102</a>.
		</p>
	</noscript>

	<div class="dh-ct-form__status" role="status" data-send-status></div>
</form>
