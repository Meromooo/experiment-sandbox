/**
 * Demas Theme (Sandbox) — front-end behaviour.
 *
 * Four jobs, no dependencies, no build step:
 *  1. Scroll reveals: add .is-in to [data-reveal] elements as they enter the
 *     viewport, and number the children of [data-reveal-group] (--i) so CSS
 *     can stagger them. Reduced-motion users get the final state at once.
 *  2. Marquee: clone each .dh-marquee__track's children once so the CSS
 *     translate(-50%) loop is seamless.
 *  3. Counters: [data-count] numbers count up from zero when they scroll
 *     into view. The markup already holds the final value, so without this
 *     script — or with reduced motion — the number is simply there.
 *  4. Branch Desk: [data-branch-desk] pairs city buttons with detail panels.
 *     Without this script the first branch (Riyadh) stays visible.
 *
 * The .js class on <html> is the gate for every hidden initial state in
 * assets/css/style.css — with this file absent or failing, nothing is hidden.
 */
(function () {
	'use strict';

	var root = document.documentElement;
	root.classList.add('js');

	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
	var hasObserver = 'IntersectionObserver' in window;

	function each(list, fn) {
		Array.prototype.forEach.call(list, fn);
	}

	/* 1. Reveals ------------------------------------------------------- */

	var targets = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));

	each(document.querySelectorAll('[data-reveal-group]'), function (group) {
		each(group.querySelectorAll('[data-reveal]'), function (el, i) {
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

	var revealObserver = null;

	if (reduce.matches || !hasObserver) {
		showAll();
	} else {
		revealObserver = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						entry.target.classList.add('is-in');
						revealObserver.unobserve(entry.target);
					}
				});
			},
			// Threshold stays low: the observer measures the *clipped* box, and
			// collapsed reveal states in the CSS keep only ~16% of it.
			{ rootMargin: '0px 0px -10% 0px', threshold: 0.1 }
		);
		targets.forEach(function (el) {
			revealObserver.observe(el);
		});
	}

	/* 2. Marquee -------------------------------------------------------- */

	each(document.querySelectorAll('.dh-marquee__track'), function (track) {
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

	/* 3. Counters ------------------------------------------------------- */

	function countUp(el) {
		var target = parseFloat(el.getAttribute('data-count'));
		if (isNaN(target)) {
			return;
		}
		var duration = 900;
		var start = null;

		function frame(now) {
			if (start === null) {
				start = now;
			}
			var progress = Math.min(1, (now - start) / duration);
			var eased = 1 - Math.pow(1 - progress, 3);
			el.textContent = Math.round(target * eased).toLocaleString('en-US');
			if (progress < 1) {
				window.requestAnimationFrame(frame);
			}
		}

		el.textContent = '0';
		window.requestAnimationFrame(frame);
	}

	var counters = document.querySelectorAll('[data-count]');

	if (counters.length && hasObserver && !reduce.matches) {
		var countObserver = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (entry.isIntersecting) {
						countUp(entry.target);
						countObserver.unobserve(entry.target);
					}
				});
			},
			{ threshold: 0.6 }
		);
		each(counters, function (el) {
			countObserver.observe(el);
		});
	}

	/* 4. Branch Desk ---------------------------------------------------- */

	each(document.querySelectorAll('[data-branch-desk]'), function (desk) {
		var buttons = desk.querySelectorAll('[data-branch]');
		var panels = desk.querySelectorAll('[data-branch-panel]');

		function select(id) {
			each(buttons, function (button) {
				button.setAttribute('aria-pressed', String(button.getAttribute('data-branch') === id));
			});
			each(panels, function (panel) {
				panel.hidden = panel.getAttribute('data-branch-panel') !== id;
			});
		}

		each(buttons, function (button) {
			button.addEventListener('click', function () {
				select(button.getAttribute('data-branch'));
			});
		});
	});

	/* Reduced-motion change at runtime --------------------------------- */

	if (typeof reduce.addEventListener === 'function') {
		reduce.addEventListener('change', function (event) {
			if (event.matches) {
				if (revealObserver) {
					revealObserver.disconnect();
				}
				showAll();
			}
		});
	}
})();
