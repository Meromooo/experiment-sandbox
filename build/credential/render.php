<?php
/**
 * Server-rendered markup for one Credential: a plate on the credentials belt
 * (AMM-178), and its hang tag.
 *
 * The plate shows the logo twice in one place: as a CSS mask filled with
 * canopy green (at rest, so every logo reads as one family), and as the
 * image in its own colours (on hover and keyboard focus). The logo files are
 * made by tools/credential-logos.py on one canvas, so every plate shows its
 * logo at the same size. Without a logo the plate shows the name instead.
 *
 * The tag says what the credential is and who issued it. With a link the
 * plate is a link to the certificate and the tag ends in "View certificate";
 * without one the plate takes keyboard focus only when there is a tag to
 * show. The tag is drawn for sight only (aria-hidden); the plate's name and
 * description carry the same words for screen readers.
 *
 * @param array    $attributes Block attributes: name, logo, detail, issuer, link.
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// The name is edited as rich text with every format turned off: plain text,
// which may hold entities such as &amp;. The other fields are plain text.
$demas_name   = trim( wp_strip_all_tags( (string) ( $attributes['name'] ?? '' ) ) );
$demas_detail = trim( wp_strip_all_tags( (string) ( $attributes['detail'] ?? '' ) ) );
$demas_issuer = trim( wp_strip_all_tags( (string) ( $attributes['issuer'] ?? '' ) ) );
$demas_link   = esc_url( trim( (string) ( $attributes['link'] ?? '' ) ) );
$demas_logo   = absint( $attributes['logo'] ?? 0 ) ? wp_get_attachment_image_url( absint( $attributes['logo'] ), 'full' ) : '';

if ( '' === $demas_name ) {
	return;
}

$demas_desc_id = ( '' === $demas_detail && '' === $demas_issuer ) ? '' : wp_unique_id( 'dh-cred-' );
$demas_has_tag = '' !== $demas_desc_id || '' !== $demas_link;
$demas_is_pdf  = (bool) preg_match( '/\.pdf(?:$|[?#])/i', $demas_link );

$demas_plate = '' === $demas_desc_id ? '' : ' aria-describedby="' . esc_attr( $demas_desc_id ) . '"';
if ( '' !== $demas_link ) {
	$demas_plate = '<a class="dh-cred__plate" href="' . $demas_link . '"' . $demas_plate . '>';
} else {
	$demas_plate = '<span class="dh-cred__plate"' . ( $demas_has_tag ? ' tabindex="0"' : '' ) . $demas_plate . '>';
}

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-cred' ) );
?>
<li <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?>>
	<?php echo $demas_plate; // phpcs:ignore WordPress.Security.EscapeOutput -- built from esc_url and esc_attr above. ?>
		<?php if ( $demas_logo ) : ?>
			<span class="dh-cred__mark">
				<span class="dh-cred__ink" style="--dh-logo:url(<?php echo esc_url( $demas_logo ); ?>)" aria-hidden="true"></span>
				<img class="dh-cred__logo" src="<?php echo esc_url( $demas_logo ); ?>" alt="" width="168" height="68" loading="lazy" decoding="async">
			</span>
			<span class="dh-sr"><?php echo wp_kses( $demas_name, array() ); ?></span>
		<?php else : ?>
			<span class="dh-cred__name"><?php echo wp_kses( $demas_name, array() ); ?></span>
		<?php endif; ?>
		<?php if ( $demas_is_pdf ) : ?>
			<span class="dh-sr"><?php esc_html_e( '(PDF)', 'demas-theme' ); ?></span>
		<?php endif; ?>
		<?php if ( $demas_has_tag ) : ?>
			<span class="dh-cred__tag" aria-hidden="true">
				<span class="dh-cred__tag-name"><?php echo wp_kses( $demas_name, array() ); ?></span>
				<?php if ( '' !== $demas_desc_id ) : ?>
					<span class="dh-cred__tag-text" id="<?php echo esc_attr( $demas_desc_id ); ?>">
						<?php if ( '' !== $demas_detail ) : ?>
							<span class="dh-cred__tag-detail"><?php echo esc_html( $demas_detail ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== $demas_issuer ) : ?>
							<span class="dh-cred__tag-issuer"><?php echo esc_html( $demas_issuer ); ?></span>
						<?php endif; ?>
					</span>
				<?php endif; ?>
				<?php if ( '' !== $demas_link ) : ?>
					<span class="dh-cred__tag-link"><?php esc_html_e( 'View certificate', 'demas-theme' ); ?></span>
				<?php endif; ?>
			</span>
		<?php endif; ?>
	<?php echo '' !== $demas_link ? '</a>' : '</span>'; ?>
</li>
