/**
 * Branch Finder — the Contact page's branches (AMM-169).
 *
 * Enhances the markup in render.php, which already works without this
 * module: each city in the list and each head on the plan is a link to its
 * branch's card (#branch-jed), and CSS shows the card a link targets.
 *
 * With it:
 *
 *  - choosing a branch changes the card in place instead of jumping to it,
 *    marks the city (aria-current), announces the change, and puts
 *    #branch-jed in the address bar without scrolling — so the link can be
 *    shared;
 *  - water runs from the pump (head office) along the mainline and the
 *    branch's lateral, then the head sprays. CSS draws both; with reduced
 *    motion the route is drawn at once and nothing sprays;
 *  - every card with opening hours gets its status — "Open now · closes
 *    13:00", "Closed now · opens 16:00" — worked out from the hours in Riyadh
 *    time, whatever the visitor's own clock says, and refreshed each minute.
 *    The server prints the hours only, so a cached page is never wrong;
 *  - "Send a request to Jeddah" stays on the page: it scrolls to the request
 *    section (#request) and moves focus there.
 *
 * Choosing a branch is announced to the rest of the page as a
 * `demas-theme:branch` event on document ({ code, source: 'finder' }), and an
 * event from anywhere else (the request form, AMM-169 step 2) chooses that
 * branch here — so the two stay in step.
 */

interface Strings {
	open: string;
	later: string;
	day: string;
	days: string[];
	announce: string;
}

type Week = Record< string, Array< [ number, number ] > >;

interface BranchEvent {
	code: string;
	source: string;
}

const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' );

/** How long the water takes to reach a head; style.css times the spray after it. */
const RUN_MS = 700;

/** Long enough for the plan to draw itself as the page opens. */
const OPENING_MS = 1500;

function format( template: string, ...values: string[] ): string {
	let next = 0;
	return template.replace( /%(?:(\d)\$)?s/g, ( _match, position ) =>
		String( values[ position ? Number( position ) - 1 : next++ ] ?? '' )
	);
}

function clock( minutes: number ): string {
	return `${ Math.floor( minutes / 60 ) }:${ String( minutes % 60 ).padStart(
		2,
		'0'
	) }`;
}

/** The day (0 = Sunday) and the minutes since midnight in Riyadh, now. */
function riyadhNow(): { day: number; minutes: number } {
	const parts = new Intl.DateTimeFormat( 'en-US', {
		timeZone: 'Asia/Riyadh',
		weekday: 'short',
		hour: 'numeric',
		minute: 'numeric',
		hourCycle: 'h23',
	} ).formatToParts( new Date() );
	const part = ( type: string ) =>
		parts.find( ( item ) => item.type === type )?.value ?? '';

	return {
		day: [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ].indexOf(
			part( 'weekday' )
		),
		minutes: Number( part( 'hour' ) ) * 60 + Number( part( 'minute' ) ),
	};
}

/** Whether a branch is open now, and the line that says so. */
function openStatus(
	week: Week,
	strings: Strings
): { text: string; open: boolean } {
	const now = riyadhNow();

	for ( const [ opens, closes ] of week[ now.day ] ?? [] ) {
		if ( now.minutes >= opens && now.minutes < closes ) {
			return { text: format( strings.open, clock( closes ) ), open: true };
		}
		if ( now.minutes < opens ) {
			return {
				text: format( strings.later, clock( opens ) ),
				open: false,
			};
		}
	}

	for ( let ahead = 1; ahead <= 7; ahead++ ) {
		const day = ( now.day + ahead ) % 7;
		const first = week[ day ]?.[ 0 ];
		if ( first ) {
			return {
				text: format( strings.day, strings.days[ day ], clock( first[ 0 ] ) ),
				open: false,
			};
		}
	}

	return { text: '', open: false };
}

function init( root: HTMLElement ): void {
	const strings = JSON.parse( root.dataset.strings || '{}' ) as Strings;
	const svg = root.querySelector< SVGSVGElement >( '.dh-ct-plan__svg' );
	const flow = root.querySelector< SVGPathElement >( '[data-flow]' );
	const spray = root.querySelector< SVGGElement >( '[data-spray]' );
	const announcer = root.querySelector< HTMLElement >( '[data-announce]' );
	const cards = Array.from(
		root.querySelectorAll< HTMLElement >( '.dh-ct-card' )
	);
	const marked = Array.from(
		root.querySelectorAll< Element >(
			'.dh-ct-plan__head, .dh-ct-plan__label'
		)
	);
	const links = Array.from(
		root.querySelectorAll< HTMLElement >( '[data-branch-link]' )
	);
	const [ hubX, hubY ] = ( svg?.dataset.hub ?? '0 0' )
		.split( ' ' )
		.map( Number );
	const main =
		root.querySelector< SVGElement >( '.dh-ct-plan__head.is-main' )
			?.dataset.code ?? '';
	let timer = 0;

	const cardFor = ( code: string ) =>
		cards.find( ( card ) => card.dataset.code === code );

	/** Water from the pump to the branch's head, then the spray. */
	function run( code: string, animate: boolean ): void {
		const head = root.querySelector< SVGElement >(
			`.dh-ct-plan__head[data-code="${ code }"]`
		);
		if ( ! flow || ! spray || ! head ) {
			return;
		}

		const [ x, y ] = ( head.dataset.at ?? '0 0' ).split( ' ' ).map( Number );
		const still = ! animate || reduce.matches;

		window.clearTimeout( timer );
		flow.classList.remove( 'is-running' );
		spray.classList.remove( 'is-spraying' );
		flow.setAttribute(
			'd',
			code === main ? `M${ hubX } ${ hubY }` : `M${ hubX } ${ hubY }H${ x }V${ y }`
		);
		spray.setAttribute( 'transform', `translate(${ x } ${ y })` );
		flow.classList.toggle( 'is-instant', still );

		// Restart: let the browser see the empty pipe before the water runs.
		flow.getBoundingClientRect();
		flow.classList.add( 'is-running' );

		if ( ! still ) {
			timer = window.setTimeout(
				() => spray.classList.add( 'is-spraying' ),
				code === main ? 0 : RUN_MS
			);
		}
	}

	function select(
		code: string,
		{ animate, told }: { animate: boolean; told: boolean }
	): void {
		const card = cardFor( code );
		if ( ! card ) {
			return;
		}

		root.dataset.current = code;
		cards.forEach( ( item ) =>
			item.classList.toggle( 'is-current', item === card )
		);
		marked.forEach( ( item ) =>
			item.classList.toggle(
				'is-current',
				( item as HTMLElement ).dataset.code === code
			)
		);
		links.forEach( ( link ) => {
			if ( link.dataset.branchLink === code ) {
				link.setAttribute( 'aria-current', 'true' );
			} else {
				link.removeAttribute( 'aria-current' );
			}
		} );

		if ( animate ) {
			run( code, true );
		}

		if ( ! told ) {
			if ( announcer ) {
				announcer.textContent = format(
					strings.announce,
					card.querySelector( '.dh-ct-card__city' )?.textContent ?? ''
				);
			}
			document.dispatchEvent(
				new CustomEvent< BranchEvent >( 'demas-theme:branch', {
					detail: { code, source: 'finder' },
				} )
			);
		}
	}

	function refresh(): void {
		cards.forEach( ( card ) => {
			const line = card.querySelector< HTMLElement >( '[data-status]' );
			if ( ! line || ! card.dataset.hours ) {
				return;
			}
			const now = openStatus( JSON.parse( card.dataset.hours ) as Week, strings );
			line.textContent = now.text;
			line.dataset.open = String( now.open );
			line.hidden = '' === now.text;
		} );
	}

	root.addEventListener( 'click', ( event ) => {
		const target = event.target as Element;
		const pick = target.closest< HTMLElement | SVGElement >(
			'[data-branch-link], .dh-ct-plan__head'
		);

		if ( pick ) {
			const code = pick.dataset.branchLink ?? pick.dataset.code ?? '';
			if ( cardFor( code ) ) {
				event.preventDefault();
				select( code, { animate: true, told: false } );
				window.history.replaceState( null, '', `#branch-${ code }` );
			}
			return;
		}

		const request = target.closest< HTMLAnchorElement >( '[data-request]' );
		const section = document.getElementById( 'request' );
		if ( request && section ) {
			event.preventDefault();
			if ( ! section.hasAttribute( 'tabindex' ) ) {
				section.setAttribute( 'tabindex', '-1' );
			}
			section.focus( { preventScroll: true } );
			section.scrollIntoView( {
				behavior: reduce.matches ? 'auto' : 'smooth',
				block: 'start',
			} );
		}
	} );

	document.addEventListener( 'demas-theme:branch', ( event ) => {
		const { code, source } = ( event as CustomEvent< BranchEvent > ).detail;
		if ( 'finder' !== source && root.dataset.current !== code ) {
			select( code, { animate: true, told: true } );
		}
	} );

	window.addEventListener( 'hashchange', () => {
		const asked = /^#branch-([a-z]+)$/.exec( window.location.hash );
		if ( asked && cardFor( asked[ 1 ] ) ) {
			select( asked[ 1 ], { animate: true, told: false } );
		}
	} );

	// The branch to start on: a #branch-xxx link's, or the server's default
	// (the main branch, or ?branch=). Its water runs once the plan has drawn.
	const asked = /^#branch-([a-z]+)$/.exec( window.location.hash );
	const first =
		asked && cardFor( asked[ 1 ] )
			? asked[ 1 ]
			: root.dataset.default ?? main;

	select( first, { animate: false, told: true } );
	if ( first !== main ) {
		timer = window.setTimeout(
			() => run( first, true ),
			reduce.matches ? 0 : OPENING_MS
		);
	}

	refresh();
	window.setInterval( refresh, 60 * 1000 );
}

document
	.querySelectorAll< HTMLElement >( '[data-branch-finder]' )
	.forEach( init );
