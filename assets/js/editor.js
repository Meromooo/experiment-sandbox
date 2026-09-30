/**
 * Demas Theme (Sandbox) — block editor additions (AMM-153).
 *
 * The "Highlight" text format: a word set on the soft field-green pill
 * (.dh-highlight, style.css section 2) that sweeps in behind it as the page
 * reveals (data-reveal="wipe", section 7), as "engineered" does in the
 * homepage headline. Select a word in a heading or paragraph, then choose
 * Highlight from the block toolbar's drop-down (the ⌄ beside the link tool).
 *
 * Plain script on the editor's own globals, enqueued from inc/homepage.php;
 * no build step.
 */
(function (wp) {
	'use strict';

	var name = 'demas-theme/highlight';
	var __ = wp.i18n.__;

	wp.richText.registerFormatType(name, {
		title: __('Highlight', 'demas-theme'),
		tagName: 'span',
		className: 'dh-highlight',
		attributes: { reveal: 'data-reveal' },
		edit: function (props) {
			return wp.element.createElement(wp.blockEditor.RichTextToolbarButton, {
				icon: 'admin-customizer',
				title: __('Highlight', 'demas-theme'),
				isActive: props.isActive,
				onClick: function () {
					props.onChange(
						wp.richText.toggleFormat(props.value, {
							type: name,
							attributes: { reveal: 'wipe' },
						})
					);
				},
			});
		},
	});
})(window.wp);
