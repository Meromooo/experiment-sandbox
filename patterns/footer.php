<?php
/**
 * Title: Footer — title block
 * Slug: demas-theme/footer
 * Categories: demas
 * Inserter: no
 * Description: The site footer, drawn as an engineering drawing's title block: the catalogue index, the branches with their key plan, the company's registration details, and the closing line.
 *
 * Placed by parts/footer.html. The catalogue index and the branch list are
 * blocks because they are data (inc/navigation.php, inc/branches.php); the
 * company details below are copy an editor may change.
 *
 * CR number, VAT number and the registered name are placeholders until Demas
 * supplies them (AMM-144). Staff email addresses never appear here.
 *
 * @package Demas_Theme
 */

$demas_certificates = content_url( 'uploads/2026/03/DEMAS-Certificates.pdf' );
?>
<!-- wp:group {"className":"dh-foot","layout":{"type":"constrained"}} -->
<div class="wp-block-group dh-foot">
	<!-- wp:group {"className":"dh-foot__sheet","layout":{"type":"default"}} -->
	<div class="wp-block-group dh-foot__sheet">
		<!-- wp:demas-theme/catalogue-index /-->

		<!-- wp:group {"className":"dh-foot__block","layout":{"type":"default"}} -->
		<div class="wp-block-group dh-foot__block">
			<!-- wp:demas-theme/branch-plan /-->

			<!-- wp:html -->
			<section class="dh-foot__id" aria-labelledby="dh-foot-company">
				<h2 class="dh-foot__name" id="dh-foot-company">Demas Group</h2>
				<p class="dh-foot__trade">Irrigation and landscape infrastructure, supplied and supported across the Kingdom.</p>
				<dl class="dh-foot-plate">
					<div class="dh-foot-plate__row"><dt>Est.</dt><dd class="dh-mono">1979</dd></div>
					<div class="dh-foot-plate__row"><dt>Telephone</dt><dd class="dh-mono"><a href="tel:+966114634102">011 463 4102</a></dd></div>
					<div class="dh-foot-plate__row is-wide"><dt>Head office</dt><dd>8018 King Abdulaziz Rd, As Sulimaniyah, Riyadh 12245</dd></div>
					<div class="dh-foot-plate__row is-wide"><dt>Registered name</dt><dd class="is-pending">Pending</dd></div>
					<div class="dh-foot-plate__row"><dt>CR no.</dt><dd class="is-pending">Pending</dd></div>
					<div class="dh-foot-plate__row"><dt>VAT no.</dt><dd class="is-pending">Pending</dd></div>
					<div class="dh-foot-plate__row is-wide"><dt>Certified</dt><dd><span class="dh-mono">ISO 9001, 14001, 45001</span> <a class="dh-foot-plate__doc" href="<?php echo esc_url( $demas_certificates ); ?>">Our certificates <span class="dh-foot-plate__meta">PDF, 1.2 MB</span></a></dd></div>
				</dl>
			</section>
			<!-- /wp:html -->
		</div>
		<!-- /wp:group -->

		<!-- wp:html -->
		<div class="dh-foot__base">
			<p class="dh-foot__legal">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Demas Group</p>
			<ul class="dh-foot__links">
				<li><a href="https://www.linkedin.com/company/108843024/" target="_blank" rel="noopener noreferrer">LinkedIn<svg class="dh-foot__out" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><path d="M5 11 11 5M6 5h5v5"/></svg><span class="dh-sr">(opens in a new tab)</span></a></li>
				<li><button type="button" class="dh-foot__quote" data-wp-interactive="demas-theme/quote" data-wp-on--click="actions.open">Your quote</button></li>
			</ul>
		</div>
		<!-- /wp:html -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
