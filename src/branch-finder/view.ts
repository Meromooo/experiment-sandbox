/**
 * Branch Finder — the Contact page's branches (AMM-169).
 *
 * Enhances the markup in render.php, which already works without this
 * module: each city in the list, and each area and dot on the map, is a link
 * to its branch's card (#branch-jed), and CSS shows the card a link targets.
 *
 * With it:
 *
 *  - choosing a branch changes the card in place instead of jumping to it,
 *    marks the city (aria-current) and the branch's area and dot on the map
 *    (.is-current), announces the change, and puts #branch-jed in the
 *    address bar without scrolling — so the link can be shared;
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

	const cardFor = ( code: string ) =>
		cards.find( ( card ) => card.dataset.code === code );

	/** Choose a branch; `told` when the page already knows (it came from the request form, or the page is opening), so it is not announced or passed on. */
	function select( code: string, told: boolean ): void {
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
				select( code, false );
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
			select( code, true );
		}
	} );

	window.addEventListener( 'hashchange', () => {
		const asked = /^#branch-([a-z]+)$/.exec( window.location.hash );
		if ( asked && cardFor( asked[ 1 ] ) ) {
			select( asked[ 1 ], false );
		}
	} );

	// The branch to start on: a #branch-xxx link's, or the server's default
	// (the main branch, or ?branch=).
	const asked = /^#branch-([a-z]+)$/.exec( window.location.hash );
	const first =
		asked && cardFor( asked[ 1 ] ) ? asked[ 1 ] : root.dataset.default ?? '';

	select( first, true );
	// Tell the request form, without announcing: nobody chose anything yet.
	document.dispatchEvent(
		new CustomEvent< BranchEvent >( 'demas-theme:branch', {
			detail: { code: first, source: 'finder' },
		} )
	);
	refresh();
	window.setInterval( refresh, 60 * 1000 );
}

document
	.querySelectorAll< HTMLElement >( '[data-branch-finder]' )
	.forEach( init );

// A module, not a script: its names stay its own.
export {};
