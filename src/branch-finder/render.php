<?php
/**
 * Server-rendered markup for the Branch Finder (AMM-169): the Contact page's
 * fifteen branches as a list, a card for the chosen one, and a layout plan.
 *
 * The layout plan draws the Kingdom the way an irrigation drawing draws a
 * site: Riyadh head office is the pump, a mainline runs along its latitude,
 * and a lateral runs up or down from it to each branch, a sprinkler head at
 * the city's real latitude and longitude (the projection the footer's key
 * plan uses, demas_theme_branch_plan_point()). Laterals that would sit
 * within two units of each other on the same side share one pipe (Buraidah
 * and Unaizah). The plan is a pointer-only twin of the list, hidden from
 * assistive technology like the footer plan: the list is the control.
 *
 * Every city is a link to its card (#branch-jed), and so is every head on
 * the plan. That is the whole mechanism without JavaScript — the card a link
 * targets is the one shown (:target), the default branch's otherwise — so
 * the page works, and deep links work, before or without view.ts. With it,
 * choosing a branch runs water from the pump along the pipes to that head,
 * the head sprays, the card changes in place and the URL follows.
 *
 * The default branch is the main one, or the one named by ?branch= (the
 * homepage Branch Desk and the cards' "Send a request" link carry it).
 *
 * Each card: a photo (chosen per branch in the block's sidebar, the drawn
 * placeholder until then), who answers there, the address, the opening
 * hours with an "Open now" status the browser works out in Riyadh time, and
 * directions — the branch's Google Maps link. Branch phone numbers are not
 * shown by decision (head office's is, where hours are not listed); staff
 * email addresses never appear anywhere.
 *
 * @param array    $attributes Block attributes: photos (branch code → attachment ID).
 * @param string   $content    Inner content (none).
 * @param WP_Block $block      Block instance.
 *
 * @package Demas_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'demas_theme_get_branches' ) || ! function_exists( 'demas_theme_branch_plan_point' ) ) {
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

$demas_asked  = isset( $_GET['branch'] ) ? sanitize_key( wp_unslash( $_GET['branch'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only display parameter.
$demas_chosen = isset( $demas_branches[ $demas_asked ] ) ? $demas_asked : $demas_main;
$demas_photos = (array) ( $attributes['photos'] ?? array() );

/*
 * The drawing. The view box runs 36.4–55°E × 15.6–31.6°N: east past the
 * last branch, over the Empty Quarter, where the chosen branch's card sits
 * on wide screens.
 */
list( $demas_vx, $demas_vy ) = demas_theme_branch_plan_point( 31.6, 36.4 );
list( $demas_vr, $demas_vb ) = demas_theme_branch_plan_point( 15.6, 55 );
$demas_vw                    = $demas_vr - $demas_vx;
$demas_vh                    = $demas_vb - $demas_vy;

list( $demas_hx, $demas_hy ) = demas_theme_branch_plan_point( (float) $demas_branches[ $demas_main ]['lat'], (float) $demas_branches[ $demas_main ]['lon'] );

$demas_points = array();
foreach ( $demas_branches as $demas_code => $demas_branch ) {
	$demas_points[ $demas_code ] = demas_theme_branch_plan_point( (float) $demas_branch['lat'], (float) $demas_branch['lon'] );
}

// Laterals within two units of a neighbour, on the same side of the mainline, share its pipe.
uasort( $demas_points, static fn( $a, $b ) => $a[0] <=> $b[0] );
$demas_last = null;
foreach ( $demas_points as $demas_code => $demas_point ) {
	if ( $demas_last && abs( $demas_point[0] - $demas_last[0] ) < 2 && ( $demas_point[1] < $demas_hy ) === ( $demas_last[1] < $demas_hy ) ) {
		$demas_points[ $demas_code ][0] = $demas_last[0];
	}
	$demas_last = $demas_points[ $demas_code ];
}

// Drawn outward from the pump: nearest lateral first.
uasort( $demas_points, static fn( $a, $b ) => abs( $a[0] - $demas_hx ) <=> abs( $b[0] - $demas_hx ) );
$demas_xs = array_column( $demas_points, 0 );

// Where a point sits in the drawing, as a percentage, for the HTML labels.
$demas_pct = static function ( float $value, float $origin, float $size ): string {
	return round( ( $value - $origin ) / $size * 100, 2 ) . '%';
};

// Hovering a city in the list, or a head on the plan, lights that head and names it.
$demas_css = '';
foreach ( array_keys( $demas_branches ) as $demas_code ) {
	$demas_code = sanitize_key( $demas_code );
	$demas_css .= sprintf(
		'.dh-ct-finder:has([data-branch-link="%1$s"]:is(:hover,:focus-visible),.dh-ct-plan__head[data-code="%1$s"]:hover) :is(.dh-ct-plan__head,.dh-ct-plan__label)[data-code="%1$s"]{--_on:1}',
		$demas_code
	);
}
wp_add_inline_style( generate_block_asset_handle( 'demas-theme/branch-finder', 'style' ), $demas_css );

$demas_strings = array(
	/* translators: %s: closing time, e.g. 13:00 */
	'open'     => __( 'Open now · closes %s', 'demas-theme' ),
	/* translators: %s: opening time later today, e.g. 16:00 */
	'later'    => __( 'Closed now · opens %s', 'demas-theme' ),
	/* translators: 1: short weekday, 2: opening time, e.g. Sat 8:00 */
	'day'      => __( 'Closed now · opens %1$s %2$s', 'demas-theme' ),
	'days'     => array(
		_x( 'Sun', 'short weekday', 'demas-theme' ),
		_x( 'Mon', 'short weekday', 'demas-theme' ),
		_x( 'Tue', 'short weekday', 'demas-theme' ),
		_x( 'Wed', 'short weekday', 'demas-theme' ),
		_x( 'Thu', 'short weekday', 'demas-theme' ),
		_x( 'Fri', 'short weekday', 'demas-theme' ),
		_x( 'Sat', 'short weekday', 'demas-theme' ),
	),
	/* translators: %s: city */
	'announce' => __( '%s branch', 'demas-theme' ),
);

$demas_pick_id = wp_unique_id( 'dh-ct-pick-' );
$demas_wrapper = get_block_wrapper_attributes( array( 'class' => 'dh-ct-finder' ) );
?>
<div <?php echo $demas_wrapper; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by core. ?> data-branch-finder data-default="<?php echo esc_attr( $demas_chosen ); ?>" data-strings="<?php echo esc_attr( wp_json_encode( $demas_strings ) ); ?>">
	<nav class="dh-ct-pick" aria-labelledby="<?php echo esc_attr( $demas_pick_id ); ?>" data-reveal="rise">
		<p class="dh-ct-pick__label" id="<?php echo esc_attr( $demas_pick_id ); ?>">
			<?php
			/* translators: %d: number of branches. */
			printf( esc_html__( 'Pick a branch · %d', 'demas-theme' ), count( $demas_branches ) );
			?>
		</p>
		<ul class="dh-ct-pick__list">
			<?php foreach ( $demas_branches as $demas_code => $demas_branch ) : ?>
				<li>
					<a class="dh-ct-city" href="#branch-<?php echo esc_attr( $demas_code ); ?>" data-branch-link="<?php echo esc_attr( $demas_code ); ?>"<?php echo $demas_chosen === $demas_code ? ' aria-current="true"' : ''; ?>>
						<span class="dh-ct-city__code" aria-hidden="true"><?php echo esc_html( strtoupper( $demas_code ) ); ?></span>
						<span class="dh-ct-city__name"><?php echo esc_html( $demas_branch['city'] ); ?></span>
						<?php if ( ! empty( $demas_branch['main'] ) ) : ?>
							<span class="dh-ct-city__tag"><?php esc_html_e( 'Main', 'demas-theme' ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>

	<div class="dh-ct-stage">
		<div class="dh-ct-cards" data-reveal="rise">
			<p class="screen-reader-text" aria-live="polite" data-announce></p>
			<?php foreach ( $demas_branches as $demas_code => $demas_branch ) : ?>
				<?php
				$demas_hours    = demas_theme_branch_hours( (string) ( $demas_branch['hours'] ?? '' ) );
				$demas_photo_id = absint( $demas_photos[ $demas_code ] ?? 0 );
				$demas_photo    = $demas_photo_id ? wp_get_attachment_image(
					$demas_photo_id,
					'large',
					false,
					array(
						'class'   => 'dh-ct-card__img',
						'loading' => 'lazy',
						'sizes'   => '(min-width: 56rem) 34rem, 100vw',
					)
				) : '';
				$demas_classes  = 'dh-ct-card dh-card dh-card--sand' . ( $demas_chosen === $demas_code ? ' is-default' : '' );
				$demas_title_id = 'dh-ct-city-' . $demas_code;
				?>
				<article class="<?php echo esc_attr( $demas_classes ); ?>" id="branch-<?php echo esc_attr( $demas_code ); ?>" data-code="<?php echo esc_attr( $demas_code ); ?>" aria-labelledby="<?php echo esc_attr( $demas_title_id ); ?>"<?php echo $demas_hours['week'] ? ' data-hours="' . esc_attr( wp_json_encode( (object) $demas_hours['week'] ) ) . '"' : ''; ?>>
					<div class="dh-ct-card__photo">
						<?php if ( $demas_photo ) : ?>
							<?php echo $demas_photo; // phpcs:ignore WordPress.Security.EscapeOutput -- core image markup. ?>
						<?php else : ?>
							<div class="dh-svc-placeholder dh-ct-card__placeholder">
								<p class="dh-svc-placeholder__chip">
									<span class="dh-svc-placeholder__label"><?php esc_html_e( 'Photo to come', 'demas-theme' ); ?></span>
									<?php
									/* translators: %s: city */
									printf( esc_html__( 'The %s branch', 'demas-theme' ), esc_html( $demas_branch['city'] ) );
									?>
								</p>
							</div>
						<?php endif; ?>
					</div>

					<div class="dh-ct-card__body">
						<p class="dh-ct-card__meta">
							<span class="dh-mono"><?php echo esc_html( strtoupper( $demas_code ) ); ?></span>
							<?php echo ! empty( $demas_branch['main'] ) ? esc_html__( 'Main branch · head office', 'demas-theme' ) : esc_html__( 'Branch', 'demas-theme' ); ?>
						</p>
						<h2 class="dh-ct-card__city" id="<?php echo esc_attr( $demas_title_id ); ?>"><?php echo esc_html( $demas_branch['city'] ); ?></h2>

						<dl class="dh-ct-card__facts">
							<?php if ( ! empty( $demas_branch['person'] ) ) : ?>
								<div class="dh-ct-card__fact">
									<dt><?php esc_html_e( 'Ask for', 'demas-theme' ); ?></dt>
									<dd class="dh-ct-card__person"><?php echo esc_html( $demas_branch['person'] ); ?></dd>
								</div>
							<?php endif; ?>
							<?php if ( ! empty( $demas_branch['address'] ) ) : ?>
								<div class="dh-ct-card__fact">
									<dt><?php esc_html_e( 'Address', 'demas-theme' ); ?></dt>
									<dd><?php echo esc_html( $demas_branch['address'] ); ?></dd>
								</div>
							<?php endif; ?>
							<div class="dh-ct-card__fact">
								<dt><?php esc_html_e( 'Hours', 'demas-theme' ); ?></dt>
								<dd>
									<?php if ( $demas_hours['lines'] ) : ?>
										<ul class="dh-ct-hours">
											<?php foreach ( $demas_hours['lines'] as $demas_line ) : ?>
												<li>
													<span class="dh-ct-hours__days"><?php echo esc_html( $demas_line['days'] ); ?></span>
													<span class="dh-ct-hours__times">
														<?php if ( $demas_line['times'] ) : ?>
															<?php foreach ( $demas_line['times'] as $demas_n => $demas_range ) : ?>
																<?php echo $demas_n ? ', ' : ''; ?><span class="dh-ct-hours__range"><?php echo esc_html( $demas_range ); ?></span>
															<?php endforeach; ?>
														<?php else : ?>
															<?php esc_html_e( 'Closed', 'demas-theme' ); ?>
														<?php endif; ?>
													</span>
												</li>
											<?php endforeach; ?>
										</ul>
										<p class="dh-ct-card__status" data-status hidden></p>
									<?php else : ?>
										<?php esc_html_e( 'Not listed yet. Head office can tell you:', 'demas-theme' ); ?>
										<a href="tel:+966114634102">011 463 4102</a>
									<?php endif; ?>
								</dd>
							</div>
						</dl>

						<div class="dh-ct-card__actions">
							<a class="dh-pill dh-pill--solid" href="<?php echo esc_url( '?branch=' . $demas_code . '#request' ); ?>" data-request="<?php echo esc_attr( $demas_code ); ?>">
								<?php
								/* translators: %s: city */
								printf( esc_html__( 'Send a request to %s', 'demas-theme' ), esc_html( $demas_branch['city'] ) );
								?>
							</a>
							<?php if ( ! empty( $demas_branch['map'] ) ) : ?>
								<a class="dh-pill dh-pill--outline" href="<?php echo esc_url( $demas_branch['map'] ); ?>" target="_blank" rel="noopener noreferrer">
									<?php esc_html_e( 'Get directions', 'demas-theme' ); ?>
									<span class="screen-reader-text"><?php esc_html_e( '(Google Maps, opens in a new tab)', 'demas-theme' ); ?></span>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7 17 17 7M8.5 7H17v8.5"/></svg>
								</a>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<figure class="dh-ct-plan dh-card dh-card--canopy" data-reveal="seed" aria-hidden="true">
			<div class="dh-ct-plan__draw">
				<svg class="dh-ct-plan__svg" xmlns="http://www.w3.org/2000/svg" viewBox="<?php echo esc_attr( "$demas_vx $demas_vy $demas_vw $demas_vh" ); ?>" focusable="false" data-hub="<?php echo esc_attr( "$demas_hx $demas_hy" ); ?>">
					<text class="dh-ct-plan__sea" transform="translate(<?php echo esc_attr( implode( ' ', demas_theme_branch_plan_point( 19.7, 38.3 ) ) ); ?>) rotate(55)"><?php esc_html_e( 'Red Sea', 'demas-theme' ); ?></text>
					<text class="dh-ct-plan__sea" transform="translate(<?php echo esc_attr( implode( ' ', demas_theme_branch_plan_point( 29.4, 49.1 ) ) ); ?>) rotate(54)"><?php esc_html_e( 'Arabian Gulf', 'demas-theme' ); ?></text>
					<text class="dh-ct-plan__sea" x="<?php echo esc_attr( demas_theme_branch_plan_point( 20, 50.2 )[0] ); ?>" y="<?php echo esc_attr( demas_theme_branch_plan_point( 20, 50.2 )[1] ); ?>" text-anchor="middle"><?php esc_html_e( 'Empty Quarter', 'demas-theme' ); ?></text>

					<g class="dh-ct-plan__pipes">
						<path class="dh-ct-plan__main" pathLength="1" d="<?php echo esc_attr( "M$demas_hx {$demas_hy}H" . min( $demas_xs ) ); ?>" />
						<path class="dh-ct-plan__main" pathLength="1" d="<?php echo esc_attr( "M$demas_hx {$demas_hy}H" . max( $demas_xs ) ); ?>" />
						<?php
						$demas_i      = 0;
						$demas_valves = array();
						foreach ( $demas_points as $demas_code => $demas_point ) :
							if ( $demas_code === $demas_main ) {
								continue;
							}
							++$demas_i;
							?>
							<path class="dh-ct-plan__lateral" pathLength="1" style="--i:<?php echo (int) $demas_i; ?>" d="<?php echo esc_attr( "M{$demas_point[0]} {$demas_hy}V{$demas_point[1]}" ); ?>" />
							<?php
							$demas_side = $demas_point[1] < $demas_hy ? -1 : 1;
							$demas_key  = $demas_point[0] . '|' . $demas_side;
							if ( abs( $demas_point[1] - $demas_hy ) >= 16 && ! isset( $demas_valves[ $demas_key ] ) ) {
								$demas_valves[ $demas_key ] = array( $demas_point[0], $demas_hy + 7 * $demas_side, $demas_i );
							}
						endforeach;
						?>
					</g>

					<path class="dh-ct-plan__flow" pathLength="1" d="<?php echo esc_attr( "M$demas_hx $demas_hy" ); ?>" data-flow />

					<g class="dh-ct-plan__valves">
						<?php foreach ( $demas_valves as $demas_valve ) : ?>
							<path class="dh-ct-plan__valve" style="--i:<?php echo (int) $demas_valve[2]; ?>" d="<?php echo esc_attr( sprintf( 'M%1$s %2$sH%3$sL%1$s %4$sH%3$sZ', $demas_valve[0] - 2.4, $demas_valve[1] - 3, $demas_valve[0] + 2.4, $demas_valve[1] + 3 ) ); ?>" />
						<?php endforeach; ?>
					</g>

					<g class="dh-ct-plan__heads">
						<?php
						$demas_i = 0;
						foreach ( $demas_points as $demas_code => $demas_point ) :
							$demas_is_main = $demas_code === $demas_main;
							?>
							<a class="dh-ct-plan__head<?php echo $demas_is_main ? ' is-main' : ''; ?><?php echo $demas_chosen === $demas_code ? ' is-current' : ''; ?>" href="#branch-<?php echo esc_attr( $demas_code ); ?>" tabindex="-1" data-code="<?php echo esc_attr( $demas_code ); ?>" data-at="<?php echo esc_attr( "{$demas_point[0]} {$demas_point[1]}" ); ?>" style="--i:<?php echo (int) $demas_i++; ?>">
								<circle class="dh-ct-plan__hit" cx="<?php echo esc_attr( $demas_point[0] ); ?>" cy="<?php echo esc_attr( $demas_point[1] ); ?>" r="9" />
								<?php if ( $demas_is_main ) : ?>
									<circle class="dh-ct-plan__ring" cx="<?php echo esc_attr( $demas_point[0] ); ?>" cy="<?php echo esc_attr( $demas_point[1] ); ?>" r="8.5" />
									<rect class="dh-ct-plan__pump" x="<?php echo esc_attr( $demas_point[0] - 4 ); ?>" y="<?php echo esc_attr( $demas_point[1] - 4 ); ?>" width="8" height="8" />
								<?php else : ?>
									<circle class="dh-ct-plan__dot" cx="<?php echo esc_attr( $demas_point[0] ); ?>" cy="<?php echo esc_attr( $demas_point[1] ); ?>" r="3.4" />
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
					</g>

					<g class="dh-ct-plan__spray" data-spray>
						<circle r="6" />
						<circle r="10" />
						<circle r="14" />
					</g>
				</svg>

				<div class="dh-ct-plan__labels">
					<?php foreach ( $demas_points as $demas_code => $demas_point ) : ?>
						<?php
						/*
						 * A name sits at the end of its pipe: above a head north of
						 * the mainline, below one south of it — unless another head
						 * is right there (Unaizah, under Buraidah on one lateral);
						 * then it goes to the left. The head office's is on the left.
						 */
						$demas_side = $demas_point[1] < $demas_hy ? -1 : 1;
						$demas_at   = $demas_code === $demas_main ? 'left' : ( $demas_side < 0 ? 'above' : 'below' );
						foreach ( $demas_points as $demas_other => $demas_near ) {
							if ( $demas_other !== $demas_code && abs( $demas_near[0] - $demas_point[0] ) < 6 && ( $demas_near[1] - $demas_point[1] ) * $demas_side > 0 && abs( $demas_near[1] - $demas_point[1] ) < 14 ) {
								$demas_at = 'left';
							}
						}
						?>
						<span class="dh-ct-plan__label is-<?php echo esc_attr( $demas_at ); ?><?php echo $demas_code === $demas_main ? ' is-main' : ''; ?><?php echo $demas_chosen === $demas_code ? ' is-current' : ''; ?>" data-code="<?php echo esc_attr( $demas_code ); ?>" style="--x:<?php echo esc_attr( $demas_pct( $demas_point[0], $demas_vx, $demas_vw ) ); ?>;--y:<?php echo esc_attr( $demas_pct( $demas_point[1], $demas_vy, $demas_vh ) ); ?>"><b><?php echo esc_html( strtoupper( $demas_code ) ); ?></b> <?php echo esc_html( $demas_branches[ $demas_code ]['city'] ); ?></span>
					<?php endforeach; ?>
				</div>
			</div>

			<figcaption class="dh-ct-plan__legend">
				<span class="dh-ct-plan__title"><?php esc_html_e( 'Layout plan', 'demas-theme' ); ?></span>
				<span class="dh-ct-plan__key dh-ct-plan__key--main"><?php esc_html_e( 'Mainline from head office', 'demas-theme' ); ?></span>
				<span class="dh-ct-plan__key dh-ct-plan__key--head"><?php esc_html_e( 'Branch', 'demas-theme' ); ?></span>
				<span class="dh-ct-plan__scale" style="--km:<?php echo esc_attr( round( 200 / 111.32 * 20 / $demas_vw * 100, 2 ) ); ?>"><?php esc_html_e( '200 km', 'demas-theme' ); ?></span>
			</figcaption>
		</figure>
	</div>
</div>
