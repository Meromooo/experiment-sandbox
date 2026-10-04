/**
 * Schematic card, film mode (AMM-175).
 *
 * render.php prints the film's first frame (the bare site plan) as an image,
 * a silent video with preload="none" over it, the last frame (the finished
 * garden) as a lazy still, and a hidden button. Nothing of the video is
 * fetched until this module plays it.
 *
 *  - Once the card is half in view and its own animations have finished
 *    (the hero's opening grows it from a seed, AMM-168), the film plays,
 *    once, and stays on its last frame.
 *  - With reduced motion, or with Save-Data on, it doesn't play by itself:
 *    the still shows and the button offers Play.
 *  - The button pauses, plays and replays it (WCAG 2.2.2: moving content
 *    that lasts more than five seconds can be paused). Its label says what
 *    it will do next.
 *  - If the browser refuses to play, the still shows and the button offers
 *    Play.
 */

type State = 'idle' | 'playing' | 'paused' | 'ended';

const reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' );
const saveData =
	( navigator as Navigator & { connection?: { saveData?: boolean } } )
		.connection?.saveData === true;

function init( film: HTMLElement ) {
	const video = film.querySelector< HTMLVideoElement >( 'video' );
	const button = film.querySelector< HTMLButtonElement >(
		'[data-film-toggle]'
	);

	if ( ! video || ! button ) {
		return;
	}

	let state: State = 'idle';

	const show = ( next: State ) => {
		state = next;
		film.dataset.state = next;
		const label =
			next === 'playing'
				? button.dataset.labelPause
				: next === 'ended'
				? button.dataset.labelReplay
				: button.dataset.labelPlay;
		button.setAttribute( 'aria-label', label ?? '' );
	};

	const still = () => {
		film.classList.add( 'is-still' );
		show( 'idle' );
	};

	const play = () => {
		film.classList.remove( 'is-still' );
		if ( state === 'ended' ) {
			video.currentTime = 0;
		}
		video.play().catch( still );
	};

	video.addEventListener( 'playing', () => {
		film.classList.add( 'is-started' );
		show( 'playing' );
	} );
	video.addEventListener( 'pause', () => {
		if ( ! video.ended ) {
			show( 'paused' );
		}
	} );
	video.addEventListener( 'ended', () => show( 'ended' ) );

	button.addEventListener( 'click', () => {
		if ( state === 'playing' ) {
			video.pause();
		} else {
			play();
		}
	} );

	button.hidden = false;
	show( 'idle' );

	if ( reduced.matches || saveData ) {
		still();
		return;
	}

	// The card (the reveal) grows in first; play once it has landed.
	const card = film.closest< HTMLElement >( '[data-reveal]' );
	const observer = new IntersectionObserver(
		( entries ) => {
			if ( ! entries.some( ( entry ) => entry.isIntersecting ) ) {
				return;
			}
			observer.disconnect();
			landed( card ).then( () => {
				if ( state === 'idle' ) {
					play();
				}
			} );
		},
		{ threshold: 0.5 }
	);
	observer.observe( film );
}

/**
 * Resolves once a reveal has landed: main.js marks it .is-settled when its
 * transitions end (or at once for the hero's CSS opening, which may still be
 * running, so its animations are awaited too). Gives up waiting after 3 s,
 * so a missing main.js never keeps the film from playing.
 *
 * @param card The card's reveal element, if any.
 */
function landed( card: HTMLElement | null ): Promise< unknown > {
	if ( ! card ) {
		return Promise.resolve();
	}

	const settled = new Promise< void >( ( resolve ) => {
		if ( card.classList.contains( 'is-settled' ) ) {
			resolve();
			return;
		}
		const watch = new MutationObserver( () => {
			if ( card.classList.contains( 'is-settled' ) ) {
				watch.disconnect();
				resolve();
			}
		} );
		watch.observe( card, { attributes: true, attributeFilter: [ 'class' ] } );
	} ).then( () =>
		Promise.all( card.getAnimations().map( ( a ) => a.finished ) )
	);

	return Promise.race( [
		settled.catch( () => undefined ),
		new Promise( ( resolve ) => window.setTimeout( resolve, 3000 ) ),
	] );
}

document
	.querySelectorAll< HTMLElement >( '[data-film]' )
	.forEach( init );

export {};
