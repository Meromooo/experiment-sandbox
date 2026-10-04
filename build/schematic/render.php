<?php
/**
 * Server-rendered markup for the Schematic card: an irrigation line drawn as
 * an engineering schematic (controller, pump, filter, mains, laterals,
 * emitters) that draws itself once the card lands (style.css, "Irrigation
 * schematic card"), with a tab socketed into its corner by the notch.
 *
 * The drawing is fixed, decorative and hidden from screen readers; the tab's
 * title and line are the editor's (block attributes, edited in the sidebar).
 * With both left empty there is no tab.
 *
 * With a film chosen in the sidebar (AMM-175), the card plays it instead of
 * drawing: a pale sheet laid on the card, the tab under it. The film's
 * files are media-library attachments (`film`: av1, mp4, poster, still), made
 * by tools/hero-film.sh. The poster (the bare plan) shows at once; view.ts
 * plays the film once the card is in view and has landed, leaves it on the
 * finished garden, and adds the Pause / Play / Replay button. The still (the
 * finished garden) stands in with reduced motion, Save-Data or no
 * JavaScript, so the video is never fetched then. Without an MP4 or AV1
 * file the card draws the schematic as before.
 *
 * @param array    $attributes Block attributes: tabTitle, tabCopy, film, filmLabel.
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$demas_tab_title = trim( (string) ( $attributes['tabTitle'] ?? '' ) );
$demas_tab_copy  = trim( (string) ( $attributes['tabCopy'] ?? '' ) );

$demas_film_ids = (array) ( $attributes['film'] ?? array() );
$demas_film     = array();
foreach ( array( 'av1', 'mp4', 'poster', 'still' ) as $demas_key ) {
	$demas_id                 = (int) ( $demas_film_ids[ $demas_key ] ?? 0 );
	$demas_film[ $demas_key ] = $demas_id ? (string) wp_get_attachment_url( $demas_id ) : '';
}
$demas_has_film = '' !== $demas_film['mp4'] || '' !== $demas_film['av1'];

$demas_film_label = trim( (string) ( $attributes['filmLabel'] ?? '' ) );
if ( '' === $demas_film_label ) {
	$demas_film_label = __( 'Animation: an irrigation system is drawn onto a site plan, its pump station is fitted, the sprinklers run, and the garden grows green.', 'demas-theme' );
}

$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-schem dh-notch dh-card dh-card--canopy' . ( $demas_has_film ? ' dh-schem--film' : '' ) ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> data-reveal="seed">
	<?php if ( $demas_has_film ) : ?>
		<div class="dh-film" data-film>
			<?php if ( '' !== $demas_film['poster'] ) : ?>
				<img class="dh-film__poster" src="<?php echo esc_url( $demas_film['poster'] ); ?>" alt="" width="1200" height="950" decoding="async">
			<?php endif; ?>
			<video class="dh-film__video" muted playsinline preload="none" aria-hidden="true" width="1200" height="950">
				<?php if ( '' !== $demas_film['av1'] ) : ?>
					<source src="<?php echo esc_url( $demas_film['av1'] ); ?>" type='video/webm; codecs="av01.0.08M.08"'>
				<?php endif; ?>
				<?php if ( '' !== $demas_film['mp4'] ) : ?>
					<source src="<?php echo esc_url( $demas_film['mp4'] ); ?>" type="video/mp4">
				<?php endif; ?>
			</video>
			<?php if ( '' !== $demas_film['still'] ) : ?>
				<img class="dh-film__still" src="<?php echo esc_url( $demas_film['still'] ); ?>" alt="" width="1200" height="950" loading="lazy" decoding="async">
				<noscript><img class="dh-film__still is-shown" src="<?php echo esc_url( $demas_film['still'] ); ?>" alt="" width="1200" height="950"></noscript>
			<?php endif; ?>
			<p class="dh-sr"><?php echo esc_html( $demas_film_label ); ?></p>
			<button type="button" class="dh-film__toggle" data-film-toggle hidden
				data-label-pause="<?php esc_attr_e( 'Pause the animation', 'demas-theme' ); ?>"
				data-label-play="<?php esc_attr_e( 'Play the animation', 'demas-theme' ); ?>"
				data-label-replay="<?php esc_attr_e( 'Replay the animation', 'demas-theme' ); ?>">
				<svg class="dh-film__icon" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
					<path class="dh-film__glyph dh-film__glyph--pause" d="M7 5v10M13 5v10" />
					<path class="dh-film__glyph dh-film__glyph--play" d="M7 4.5v11l9-5.5z" />
					<path class="dh-film__glyph dh-film__glyph--replay" d="M15.5 10a5.5 5.5 0 1 1-1.6-3.9M14.5 3v3.5H11" />
				</svg>
			</button>
		</div>
	<?php else : ?>
	<svg class="dh-schem__svg" viewBox="0 0 600 420" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
<rect class="dh-schem__line" style="--i:0" x="36" y="36" width="64" height="44" rx="6" stroke-width="2" pathLength="1"/>
<path class="dh-schem__line" style="--i:0" d="M52 58h10M70 58h16M52 68h34" stroke-width="2" pathLength="1"/>
<path class="dh-schem__wire" d="M100 58H200V190M100 58H330V190M100 58H460V190" stroke-width="1.5" stroke-dasharray="3 7" pathLength="1"/>
<circle class="dh-schem__line" style="--i:1" cx="60" cy="210" r="18" stroke-width="2.5" pathLength="1"/>
<path class="dh-schem__line" style="--i:1" d="M53 202l15 8-15 8z" stroke-width="2" pathLength="1"/>
<path class="dh-schem__line" style="--i:2" d="M100 194h36l-14 16v14l-8 4v-18z" stroke-width="2" pathLength="1"/>
<path class="dh-schem__line dh-schem__main" style="--i:3" d="M78 210H100M136 210H540" stroke-width="3.5" pathLength="1"/>
<path class="dh-schem__line" style="--i:4" d="M189 201v18l11-9zM211 201v18l-11-9z" stroke-width="2" pathLength="1"/><path class="dh-schem__line" style="--i:4" d="M319 201v18l11-9zM341 201v18l-11-9z" stroke-width="2" pathLength="1"/><path class="dh-schem__line" style="--i:4" d="M449 201v18l11-9zM471 201v18l-11-9z" stroke-width="2" pathLength="1"/>
<path class="dh-schem__line" style="--i:5" d="M200 201V62M200 219V372M330 201V62M330 219V372M460 201V62M460 219V372" stroke-width="2.5" pathLength="1"/>
<path class="dh-schem__line" style="--i:6" d="M140 80H260M140 120H260M140 160H260M140 260H260M140 300H260M140 340H260M270 80H390M270 120H390M270 160H390M270 260H390M270 300H390M270 340H390M400 80H520M400 120H520M400 160H520M400 260H520M400 300H520M400 340H520" stroke-width="1.5" pathLength="1"/>
<g class="dh-schem__emit" fill="currentColor" stroke="none"><circle cx="152" cy="80" r="2.6"/><circle cx="176" cy="80" r="2.6"/><circle cx="200" cy="80" r="2.6"/><circle cx="224" cy="80" r="2.6"/><circle cx="248" cy="80" r="2.6"/><circle cx="152" cy="120" r="2.6"/><circle cx="176" cy="120" r="2.6"/><circle cx="200" cy="120" r="2.6"/><circle cx="224" cy="120" r="2.6"/><circle cx="248" cy="120" r="2.6"/><circle cx="152" cy="160" r="2.6"/><circle cx="176" cy="160" r="2.6"/><circle cx="200" cy="160" r="2.6"/><circle cx="224" cy="160" r="2.6"/><circle cx="248" cy="160" r="2.6"/><circle cx="152" cy="260" r="2.6"/><circle cx="176" cy="260" r="2.6"/><circle cx="200" cy="260" r="2.6"/><circle cx="224" cy="260" r="2.6"/><circle cx="248" cy="260" r="2.6"/><circle cx="152" cy="300" r="2.6"/><circle cx="176" cy="300" r="2.6"/><circle cx="200" cy="300" r="2.6"/><circle cx="224" cy="300" r="2.6"/><circle cx="248" cy="300" r="2.6"/><circle cx="152" cy="340" r="2.6"/><circle cx="176" cy="340" r="2.6"/><circle cx="200" cy="340" r="2.6"/><circle cx="224" cy="340" r="2.6"/><circle cx="248" cy="340" r="2.6"/><circle cx="282" cy="80" r="2.6"/><circle cx="306" cy="80" r="2.6"/><circle cx="330" cy="80" r="2.6"/><circle cx="354" cy="80" r="2.6"/><circle cx="378" cy="80" r="2.6"/><circle cx="282" cy="120" r="2.6"/><circle cx="306" cy="120" r="2.6"/><circle cx="330" cy="120" r="2.6"/><circle cx="354" cy="120" r="2.6"/><circle cx="378" cy="120" r="2.6"/><circle cx="282" cy="160" r="2.6"/><circle cx="306" cy="160" r="2.6"/><circle cx="330" cy="160" r="2.6"/><circle cx="354" cy="160" r="2.6"/><circle cx="378" cy="160" r="2.6"/><circle cx="282" cy="260" r="2.6"/><circle cx="306" cy="260" r="2.6"/><circle cx="330" cy="260" r="2.6"/><circle cx="354" cy="260" r="2.6"/><circle cx="378" cy="260" r="2.6"/><circle cx="282" cy="300" r="2.6"/><circle cx="306" cy="300" r="2.6"/><circle cx="330" cy="300" r="2.6"/><circle cx="354" cy="300" r="2.6"/><circle cx="378" cy="300" r="2.6"/><circle cx="282" cy="340" r="2.6"/><circle cx="306" cy="340" r="2.6"/><circle cx="330" cy="340" r="2.6"/><circle cx="354" cy="340" r="2.6"/><circle cx="378" cy="340" r="2.6"/><circle cx="412" cy="80" r="2.6"/><circle cx="436" cy="80" r="2.6"/><circle cx="460" cy="80" r="2.6"/><circle cx="484" cy="80" r="2.6"/><circle cx="508" cy="80" r="2.6"/><circle cx="412" cy="120" r="2.6"/><circle cx="436" cy="120" r="2.6"/><circle cx="460" cy="120" r="2.6"/><circle cx="484" cy="120" r="2.6"/><circle cx="508" cy="120" r="2.6"/><circle cx="412" cy="160" r="2.6"/><circle cx="436" cy="160" r="2.6"/><circle cx="460" cy="160" r="2.6"/><circle cx="484" cy="160" r="2.6"/><circle cx="508" cy="160" r="2.6"/><circle cx="412" cy="260" r="2.6"/><circle cx="436" cy="260" r="2.6"/><circle cx="460" cy="260" r="2.6"/><circle cx="484" cy="260" r="2.6"/><circle cx="508" cy="260" r="2.6"/><circle cx="412" cy="300" r="2.6"/><circle cx="436" cy="300" r="2.6"/><circle cx="460" cy="300" r="2.6"/><circle cx="484" cy="300" r="2.6"/><circle cx="508" cy="300" r="2.6"/><circle cx="412" cy="340" r="2.6"/><circle cx="436" cy="340" r="2.6"/><circle cx="460" cy="340" r="2.6"/><circle cx="484" cy="340" r="2.6"/><circle cx="508" cy="340" r="2.6"/></g>
</svg>
	<?php endif; ?>
	<?php if ( '' !== $demas_tab_title || '' !== $demas_tab_copy ) : ?>
		<div class="dh-notch__tab" data-notch="bottom-start">
			<?php if ( '' !== $demas_tab_title ) : ?>
				<p class="dh-schem__tab-title"><?php echo esc_html( $demas_tab_title ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $demas_tab_copy ) : ?>
				<p class="dh-schem__tab-copy"><?php echo esc_html( $demas_tab_copy ); ?></p>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
