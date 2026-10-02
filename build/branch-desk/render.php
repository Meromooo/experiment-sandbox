<?php
/**
 * Server-rendered markup for the Branch Desk: every branch as a button and,
 * for the one chosen, where it is and who answers there.
 *
 * The branches, their codes, cities and people come from
 * demas_theme_get_branches() (inc/branches.php), the list the footer's key
 * plan reads too, so the two never disagree. main.js pairs the buttons with
 * the panels ([data-branch-desk])
 * and selects the city a #branch-xxx link names; without JavaScript the main
 * branch's panel is the one shown. The panel changes are announced
 * (aria-live), and each button says whether it is the chosen one
 * (aria-pressed).
 *
 * @param array    $attributes Block attributes: eyebrow, title.
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

// The main branch is chosen to start with; failing that, the first.
$demas_chosen = (string) key( $demas_branches );
$demas_mains  = wp_list_filter( $demas_branches, array( 'main' => true ) );

if ( $demas_mains ) {
	$demas_chosen = (string) key( $demas_mains );
}

foreach ( $demas_branches as $demas_code => $demas_branch ) {
	$demas_where = $demas_branch['city'];

	if ( ! empty( $demas_branch['main'] ) ) {
		/* translators: %s: city */
		$demas_where = sprintf( __( '%s · Main branch', 'demas-theme' ), $demas_branch['city'] );
	}

	$demas_branches[ $demas_code ]['where'] = $demas_where;

	/* translators: %s: city */
	$demas_branches[ $demas_code ]['action'] = sprintf( __( 'Message the %s branch', 'demas-theme' ), $demas_branch['city'] );
}

$demas_eyebrow = trim( (string) ( $attributes['eyebrow'] ?? '' ) );
$demas_title   = trim( (string) ( $attributes['title'] ?? '' ) );
$demas_wrapper = get_block_wrapper_attributes(
	array(
		'class' => 'dh-desk dh-card dh-card--sand',
		'id'    => 'find-your-branch',
	)
);
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> data-branch-desk data-reveal="seed">
	<div data-reveal-content>
		<?php if ( '' !== $demas_eyebrow ) : ?>
			<p class="dh-eyebrow"><?php echo esc_html( $demas_eyebrow ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $demas_title ) : ?>
			<h2 class="dh-desk__title"><?php echo esc_html( $demas_title ); ?></h2>
		<?php endif; ?>
		<ul class="dh-desk__list">
			<?php foreach ( $demas_branches as $demas_code => $demas_branch ) : ?>
				<li><button type="button" class="dh-desk__city" id="branch-<?php echo esc_attr( $demas_code ); ?>" data-branch="<?php echo esc_attr( $demas_code ); ?>" aria-pressed="<?php echo $demas_chosen === $demas_code ? 'true' : 'false'; ?>"><span class="dh-desk__code dh-mono"><?php echo esc_html( strtoupper( $demas_code ) ); ?></span><span><?php echo esc_html( $demas_branch['city'] ); ?></span></button></li>
			<?php endforeach; ?>
		</ul>
		<div class="dh-desk__detail" aria-live="polite">
			<?php foreach ( $demas_branches as $demas_code => $demas_branch ) : ?>
				<div class="dh-desk__panel" data-branch-panel="<?php echo esc_attr( $demas_code ); ?>"<?php echo $demas_chosen === $demas_code ? '' : ' hidden'; ?>>
					<p class="dh-desk__where"><span class="dh-mono"><?php echo esc_html( strtoupper( $demas_code ) ); ?></span> <?php echo esc_html( $demas_branch['where'] ); ?></p>
					<?php if ( ! empty( $demas_branch['person'] ) ) : ?>
						<p class="dh-desk__name"><?php echo esc_html( $demas_branch['person'] ); ?></p>
					<?php endif; ?>
					<a class="dh-pill dh-pill--solid" href="<?php echo esc_url( function_exists( 'demas_theme_contact_url' ) ? demas_theme_contact_url( $demas_code ) : '#contact' ); ?>"><?php echo esc_html( $demas_branch['action'] ); ?></a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
