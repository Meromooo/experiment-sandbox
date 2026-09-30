/**
 * Demas Theme (Sandbox) — front-end behaviour.
 *
 * Six jobs, no dependencies, no build step:
 *  1. Scroll reveals: add .is-in to [data-reveal] elements as they enter the
 *     viewport, and number the reveals of each [data-reveal-group] (--i) so
 *     CSS can stagger them. Reduced-motion users get the final state at once.
 *  2. Marquee: clone each .dh-marquee__track's children once so the CSS
 *     translate(-50%) loop is seamless.
 *  3. Counters: [data-count] numbers count up from zero when they scroll
 *     into view. The markup already holds the final value, so without this
 *     script — or with reduced motion — the number is simply there.
 *  4. Branch Desk: [data-branch-desk] pairs city buttons with detail panels.
 *     Without this script the first branch (Riyadh) stays visible. A
 *     #branch-xxx hash (the footer's branch links) selects that city.
 *  5. Finale: measures the homepage's closing CTA so CSS can pin it while
 *     the footer slides over it. Without this script nothing is pinned.
 *  6. Catalogue view: switches Gallery / Sheet in place, remembers it, and
 *     animates. Without this script the toggle links reload with ?view=.
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

	// A group numbers its own reveals, in order. A group inside it numbers its
	// own, and a reveal inside another reveal (the highlighted word in a
	// headline) keeps its container's number, inherited through --i.
	each(document.querySelectorAll('[data-reveal-group]'), function (group) {
		var i = 0;
		each(group.querySelectorAll('[data-reveal]'), function (el) {
			if (el.parentElement.closest('[data-reveal-group], [data-reveal]') !== group) {
				return;
			}
			if (!el.style.getPropertyValue('--i')) {
				el.style.setProperty('--i', String(i));
			}
			i += 1;
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

		// The footer's branch links arrive as /#branch-jed. The browser has
		// already scrolled to that city's button (it carries the id); this
		// selects it, on load and when the link is followed on this page.
		function selectFromHash() {
			var match = /^#branch-([a-z]+)$/.exec(window.location.hash);
			if (match && desk.querySelector('[data-branch="' + match[1] + '"]')) {
				select(match[1]);
			}
		}

		selectFromHash();
		window.addEventListener('hashchange', selectFromHash);
	});

	/* 5. Finale --------------------------------------------------------- */

	// Homepage: the closing CTA stays pinned while the footer slides over it.
	// CSS needs the CTA's height to pin it with its bottom edge at the bottom
	// of the viewport (style.css, section 13); without this, no pin.
	if ('ResizeObserver' in window) {
		each(document.querySelectorAll('.dh-finale'), function (finale) {
			var pinned = finale.querySelector(':scope > .dh-closing');
			if (!pinned) {
				return;
			}

			var measure = function () {
				finale.style.setProperty('--dh-pin-h', pinned.offsetHeight + 'px');
			};

			measure();
			new ResizeObserver(measure).observe(pinned);
			finale.classList.add('is-pinnable');
		});
	}

	/* 6. Catalogue view: Gallery / Sheet (AMM-141) ---------------------- */

	// The toolbar's Gallery | Sheet links reload with ?view=… without this;
	// with it they switch in place, remember the choice for every catalogue
	// page (applied before paint by the head script in inc/sheet-view.php),
	// update the address so the view can be shared, and morph the cards
	// between grid and rows where View Transitions are supported.
	var VIEW_KEY = 'demas-theme/view';
	var viewGrids = document.querySelectorAll('.dh-grid--catalogue');
	var viewLinks = document.querySelectorAll('[data-dh-view]');

	if (viewGrids.length && viewLinks.length) {
		var currentView = function () {
			return root.classList.contains('dh-view-sheet') || viewGrids[0].classList.contains('is-sheet') ? 'sheet' : 'gallery';
		};

		// Every link that reloads the catalogue carries the view, so the next
		// page renders the same one even where storage is blocked.
		var carryView = function (view) {
			each(viewLinks, function (link) {
				link.setAttribute('aria-current', String(link.getAttribute('data-dh-view') === view));
			});

			each(document.querySelectorAll('.dh-toolbar__sort:not(.dh-toolbar__view) a, .dh-toolbar__chips a, .dh-pagination a'), function (link) {
				var url = new URL(link.href, window.location.href);
				if (view === 'sheet') {
					url.searchParams.set('view', 'sheet');
				} else {
					url.searchParams.delete('view');
				}
				link.href = url.toString();
			});
		};

		var applyView = function (view) {
			root.classList.toggle('dh-view-sheet', view === 'sheet');
			each(viewGrids, function (grid) {
				grid.classList.toggle('is-sheet', view === 'sheet');
			});
			carryView(view);
		};

		var chooseView = function (view) {
			if (view === currentView()) {
				return;
			}

			try {
				window.localStorage.setItem(VIEW_KEY, view);
			} catch (e) {
				// Storage blocked: the address still carries the view.
			}

			var url = new URL(window.location.href);
			if (view === 'sheet') {
				url.searchParams.set('view', 'sheet');
			} else {
				url.searchParams.delete('view');
			}
			window.history.replaceState(window.history.state, '', url.toString());

			if (typeof document.startViewTransition !== 'function' || reduce.matches) {
				applyView(view);
				return;
			}

			// Name each card for the transition so it morphs into its row;
			// names are cleared afterwards to keep them out of later transitions.
			var cards = document.querySelectorAll('.dh-grid--catalogue .dh-pcard');
			each(cards, function (card, i) {
				card.style.viewTransitionName = 'dh-card-' + i;
			});

			document.startViewTransition(function () {
				applyView(view);
			}).finished.finally(function () {
				each(cards, function (card) {
					card.style.viewTransitionName = '';
				});
			});
		};

		each(viewLinks, function (link) {
			link.addEventListener('click', function (event) {
				// New tab / window: let the browser follow the link.
				if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
					return;
				}
				event.preventDefault();
				chooseView(link.getAttribute('data-dh-view'));
			});
		});

		carryView(currentView());
	}

	// The printed sheet's date is the day it is printed, not the day the
	// page was served.
	window.addEventListener('beforeprint', function () {
		each(document.querySelectorAll('[data-dh-print-date]'), function (el) {
			el.textContent = new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
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
