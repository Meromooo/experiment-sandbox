/**
 * Demas Theme (Sandbox) — front-end behaviour.
 *
 * Two jobs, no dependencies, no build step:
 *  1. Scroll reveals: add .is-in to [data-reveal] elements as they enter the
 *     viewport, and number the children of [data-reveal-group] (--i) so CSS
 *     can stagger them. Reduced-motion users get the final state at once.
 *  2. Marquee: clone each .dh-marquee__track's children once so the CSS
 *     translate(-50%) loop is seamless.
 *
 * The .js class on <html> is the gate for every hidden initial state in
 * assets/css/style.css — with this file absent or failing, nothing is hidden.
 */
(function () {
	'use strict';

	var root = document.documentElement;
	root.classList.add('js');

	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

	/* 1. Reveals ------------------------------------------------------- */

	var targets = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));

	var groups = document.querySelectorAll('[data-reveal-group]');
	Array.prototype.forEach.call(groups, function (group) {
		var members = group.querySelectorAll('[data-reveal]');
		Array.prototype.forEach.call(members, function (el, i) {
			if (!el.style.getPropertyValue('--i')) {
				el.style.setProperty('--i', String(i));
			}
		});
	});

	function showAll() {
		targets.forEach(function (el) {
			el.classList.add('is-in');
		});
	}

	if (reduce.matches || !('IntersectionObserver' in window)) {
		showAll();
	} else {
		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-in');
						observer.unobserve(entry.target);
					}
				});
			},
			{ rootMargin: '0px 0px -10% 0px', threshold: 0.15 }
		);
		targets.forEach(function (el) {
			observer.observe(el);
		});

		if (typeof reduce.addEventListener === 'function') {
			reduce.addEventListener('change', function (event) {
				if (event.matches) {
					observer.disconnect();
					showAll();
				}
			});
		}
	}

	/* 2. Marquee -------------------------------------------------------- */

	var tracks = document.querySelectorAll('.dh-marquee__track');
	Array.prototype.forEach.call(tracks, function (track) {
		if (track.getAttribute('data-cloned') === '1') {
			return;
		}
		var originals = Array.prototype.slice.call(track.children);
		originals.forEach(function (node) {
			var copy = node.cloneNode(true);
			copy.setAttribute('aria-hidden', 'true');
			track.appendChild(copy);
		});
		track.setAttribute('data-cloned', '1');
	});
})();
