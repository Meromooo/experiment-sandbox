/**
 * Demas Theme (Sandbox) — front-end behaviour.
 *
 * Six jobs, no dependencies, no build step:
 *  1. Scroll reveals: add .is-in to [data-reveal] elements as they enter the
 *     viewport, and number the reveals of each [data-reveal-group] (--i) so
 *     CSS can stagger them. Reduced-motion users get the final state at once.
 *     The homepage hero is not one: CSS plays it as the page opens.
 *  2. Marquee and belt: clone each .dh-marquee__track's and .dh-belt__track's
 *     children once so the CSS translate(-50%) loop is seamless; then drive
 *     the certificates belt (stamp-in, ease to a stop, stop on focus).
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

	var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
	var hasObserver = 'IntersectionObserver' in window;

	function each(list, fn) {
		Array.prototype.forEach.call(list, fn);
	}

	/* 1. Reveals ------------------------------------------------------- */

	// A reveal that CSS already plays as the page opens (the homepage hero,
	// style.css section 7) is left to CSS: marked landed before .js switches
	// the scroll reveals on, so they never hide it.
	var targets = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]')).filter(function (el) {
		if (window.getComputedStyle(el).animationName === 'none') {
			return true;
		}
		el.classList.add('is-in', 'is-settled');
		return false;
	});

	root.classList.add('js');

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

	// Once a reveal has landed it settles (.is-settled, style.css section 7):
	// its transition and clip let go, so the element's own transforms and
	// transitions apply (a card's hover lift) and nothing drawn outside it
	// stays cut off (a focus ring, a tooltip, a hover shadow). It waits for the
	// element's own transitions, not its children's. With none running — it was
	// already in its final state, so no transitionend would ever come — it
	// settles at once.
	function reveal(el) {
		el.classList.add('is-in');
		if (!el.getAnimations) {
			return;
		}
		var running = el.getAnimations().map(function (animation) {
			return animation.finished;
		});
		Promise.allSettled(running).then(function () {
			el.classList.add('is-settled');
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
						reveal(entry.target);
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

	/* 2. Marquee and belt ----------------------------------------------- */

	// The copies only fill the loop: hidden from screen readers, out of the
	// keyboard's reach (inert), and without ids, so nothing points at them.
	each(document.querySelectorAll('.dh-marquee__track, .dh-belt__track'), function (track) {
		if (track.getAttribute('data-cloned') === '1') {
			return;
		}
		var originals = Array.prototype.slice.call(track.children);
		var copyOf = function (node) {
			var copy = node.cloneNode(true);
			copy.setAttribute('aria-hidden', 'true');
			copy.setAttribute('inert', '');
			each(copy.querySelectorAll('[id]'), function (el) {
				el.removeAttribute('id');
			});
			each(copy.querySelectorAll('[aria-describedby]'), function (el) {
				el.removeAttribute('aria-describedby');
			});
			return copy;
		};
		originals.forEach(function (node) {
			track.appendChild(copyOf(node));
		});
		// The belt keeps a set before the originals too (its loop runs over
		// the middle third), so focus can bring any plate to mid belt with
		// plates either side of it.
		if (track.classList.contains('dh-belt__track')) {
			originals.forEach(function (node) {
				track.insertBefore(copyOf(node), originals[0]);
			});
		}
		track.setAttribute('data-cloned', '1');
	});

	// The certificates belt (AMM-178, style.css "Credentials") waits still
	// until it first comes into view, stamps its plates in one after another,
	// then runs. The pointer slows it to a stop and lets it go again. Keyboard
	// focus stops it at once and moves the loop so the focused plate sits mid
	// belt: it is never focused out of sight. With reduced motion the plates
	// stand still as a wall and none of this runs.
	each(document.querySelectorAll('.dh-belt'), function (belt) {
		var track = belt.querySelector('.dh-belt__track');
		var loop = track && track.getAnimations ? track.getAnimations()[0] : null;
		if (!loop || reduce.matches) {
			return;
		}

		var plates = track.querySelectorAll('.dh-cred:not([aria-hidden])');
		each(track.children, function (plate, i) {
			plate.style.setProperty('--i', String(i % plates.length));
		});
		belt.classList.add('is-driven');

		var run = function () {
			belt.classList.remove('is-waiting', 'is-stamping');
		};

		if (hasObserver && plates.length) {
			belt.classList.add('is-waiting');
			var arrival = new IntersectionObserver(
				function (entries) {
					if (!entries[0].isIntersecting) {
						return;
					}
					arrival.disconnect();
					belt.classList.replace('is-waiting', 'is-stamping');
					// The last plate's stamp ends the stamp-in; its transition
					// exists from the next frame. 3 s in case it never runs.
					var fallback = window.setTimeout(run, 3000);
					window.requestAnimationFrame(function () {
						var last = plates[plates.length - 1];
						var stamping = last.getAnimations().map(function (animation) {
							return animation.finished;
						});
						Promise.allSettled(stamping).then(function () {
							window.clearTimeout(fallback);
							run();
						});
					});
				},
				{ threshold: 0.4 }
			);
			arrival.observe(belt);
		}

		// One motion at a time: easing the speed, or gliding to a plate.
		var ramp = 0;
		// How far the track is moved past where the loop can take it (the CSS
		// translate property, on top of the loop's transform): the first
		// plates sit at the start of the loop, so reaching mid belt needs more.
		var extra = 0;

		function tween(ms, step) {
			window.cancelAnimationFrame(ramp);
			var start = null;

			function frame(now) {
				if (start === null) {
					start = now;
				}
				var progress = Math.min(1, (now - start) / ms);
				step(1 - Math.pow(1 - progress, 3));
				if (progress < 1) {
					ramp = window.requestAnimationFrame(frame);
				}
			}

			ramp = window.requestAnimationFrame(frame);
		}

		function moveExtra(px) {
			extra = px;
			track.style.translate = px ? px + 'px' : '';
		}

		// Running again, the belt also slides back onto its loop.
		function easeTo(rate) {
			var from = loop.playbackRate;
			var fromExtra = extra;
			tween(600, function (eased) {
				loop.playbackRate = from + (rate - from) * eased;
				if (rate > 0 && fromExtra) {
					moveExtra(fromExtra * (1 - eased));
				}
			});
		}

		belt.addEventListener('pointerenter', function (event) {
			if (event.pointerType === 'mouse') {
				easeTo(0);
			}
		});

		belt.addEventListener('pointerleave', function (event) {
			if (event.pointerType === 'mouse' && !belt.contains(document.activeElement)) {
				easeTo(1);
			}
		});

		belt.addEventListener('focusin', function (event) {
			var plate = event.target.closest('.dh-cred');
			if (!plate) {
				return;
			}
			loop.playbackRate = 0;
			belt.scrollLeft = 0; // Where overflow: clip is missing, focus scrolls a hidden overflow.

			// The loop moves the track by a third of its width (the originals'
			// length); its progress is how far along that it is. The plate's
			// centre goes to the belt's: as far as the loop reaches, then the
			// rest with the translate.
			var set = track.scrollWidth / 3;
			var duration = loop.effect.getComputedTiming().duration;
			var toPx = window.getComputedStyle(track).direction === 'rtl' ? set : -set;
			var view = belt.getBoundingClientRect();
			var box = plate.getBoundingClientRect();
			var shift = view.left + view.width / 2 - (box.left + box.width / 2);
			var from = (Number(loop.currentTime) % duration) / duration;
			var fromExtra = extra;
			var target = from + (extra + shift) / toPx;
			var to = Math.min(1, Math.max(0, target));
			var toExtra = (target - to) * toPx;

			tween(350, function (eased) {
				loop.currentTime = (from + (to - from) * eased) * duration;
				moveExtra(fromExtra + (toExtra - fromExtra) * eased);
			});
		});

		belt.addEventListener('focusout', function (event) {
			if (!belt.contains(event.relatedTarget) && !belt.matches(':hover')) {
				easeTo(1);
			}
		});
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
