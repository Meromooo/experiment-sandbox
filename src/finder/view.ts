/**
 * Jump to Part — the header finder (AMM-142).
 *
 * Enhances the markup in render.php:
 *
 *  - swaps the no-JavaScript link (to the catalogue) for the button that
 *    opens the finder, and opens it on "/" from anywhere on the page unless
 *    the visitor is typing;
 *  - turns the finder's search field into an ARIA 1.2 combobox: as the
 *    visitor types, suggestions come from demas-theme/v1/find (the same
 *    matching and ranking as the search results page, inc/search.php) and
 *    are drawn as a grouped listbox — categories with their path and
 *    product count, parts with their path and part number;
 *  - keys: ↑/↓ choose a suggestion, Enter opens it; Enter on an exact part
 *    number goes straight to that part; otherwise Enter submits the form to
 *    the results page, exactly as it does without this script. Escape
 *    closes.
 *
 * A plain module rather than an Interactivity API store: each row marks the
 * matched fragment of its name and part number, and the store's templating
 * can only set whole text nodes. Rows are built as DOM nodes — never from
 * HTML strings — so nothing the endpoint returns is ever parsed as markup.
 */

interface Strings {
	categories: string;
	parts: string;
	none: string;
	ask: string;
	error: string;
	all: string;
	allOne: string;
	status: string;
	statusOne: string;
	exact: string;
}

interface Category {
	name: string;
	path: string;
	count: number;
	url: string;
}

interface Part {
	name: string;
	sku: string;
	path: string;
	url: string;
	exact: boolean;
}

interface Found {
	query: string;
	needles: string[];
	total: number;
	categories: Category[];
	parts: Part[];
	resultsUrl: string;
}

interface Row {
	name: string;
	path: string;
	meta: string;
	url: string;
	markMeta: boolean;
	exact?: boolean;
}

/** Long enough to skip the keystrokes of a word being typed, short enough to feel live. */
const DEBOUNCE_MS = 160;

/** One character matches too much to be useful. */
const MIN_LENGTH = 2;

const pad = ( n: number ): string => String( n ).padStart( 3, '0' );

const fill = ( template: string, value: string | number ): string =>
	template.replace( '%s', String( value ) );

const escapeRegExp = ( text: string ): string =>
	text.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );

/** Is the visitor typing somewhere, so "/" is a character and not a shortcut? */
function isTyping( target: EventTarget | null ): boolean {
	if ( ! ( target instanceof HTMLElement ) ) {
		return false;
	}

	return (
		target.isContentEditable ||
		[ 'INPUT', 'TEXTAREA', 'SELECT' ].includes( target.tagName )
	);
}

/** Text with each needle wrapped in <mark class="dh-hit">, built as nodes. */
function highlight( text: string, needles: string[] ): DocumentFragment {
	const fragment = document.createDocumentFragment();
	const usable = needles
		.map( ( needle ) => needle.trim() )
		.filter( ( needle ) => needle.length > 0 )
		.sort( ( a, b ) => b.length - a.length );

	if ( ! usable.length ) {
		fragment.append( text );
		return fragment;
	}

	const pattern = new RegExp( `(${ usable.map( escapeRegExp ).join( '|' ) })`, 'i' );

	text.split( pattern ).forEach( ( part, index ) => {
		if ( ! part ) {
			return;
		}

		if ( index % 2 === 1 ) {
			const mark = document.createElement( 'mark' );
			mark.className = 'dh-hit';
			mark.textContent = part;
			fragment.append( mark );
		} else {
			fragment.append( part );
		}
	} );

	return fragment;
}

function span( className: string, content: string | Node ): HTMLSpanElement {
	const element = document.createElement( 'span' );
	element.className = className;
	element.append( content );
	return element;
}

function setUp( root: HTMLElement ): void {
	const dialog = root.querySelector< HTMLDialogElement >( '.dh-finder__dialog' );
	const opener = root.querySelector< HTMLButtonElement >( '.dh-finder__trigger--open' );
	const fallback = root.querySelector< HTMLAnchorElement >( '.dh-finder__trigger--fallback' );
	const form = root.querySelector< HTMLFormElement >( '.dh-finder__form' );
	const input = root.querySelector< HTMLInputElement >( '.dh-finder__input' );
	const closer = root.querySelector< HTMLButtonElement >( '.dh-finder__close' );
	const start = root.querySelector< HTMLElement >( '.dh-finder__start' );
	const list = root.querySelector< HTMLElement >( '.dh-finder__list' );
	const empty = root.querySelector< HTMLElement >( '.dh-finder__empty' );
	const foot = root.querySelector< HTMLElement >( '.dh-finder__foot' );
	const all = root.querySelector< HTMLAnchorElement >( '.dh-finder__all' );
	const status = root.querySelector< HTMLElement >( '[data-finder-status]' );
	const endpoint = root.dataset.endpoint;

	if (
		! dialog ||
		typeof dialog.showModal !== 'function' ||
		! opener ||
		! fallback ||
		! form ||
		! input ||
		! closer ||
		! start ||
		! list ||
		! empty ||
		! foot ||
		! all ||
		! status ||
		! endpoint
	) {
		// The link to the catalogue stays: search still works from there.
		return;
	}

	let strings: Strings;

	try {
		strings = JSON.parse( root.dataset.strings || '{}' ) as Strings;
	} catch {
		return;
	}

	const cache = new Map< string, Found >();
	let options: HTMLAnchorElement[] = [];
	let active = -1;
	let current: Found | null = null;
	let timer = 0;
	let controller: AbortController | null = null;
	let sequence = 0;

	fallback.hidden = true;
	opener.hidden = false;
	root.classList.add( 'is-ready' );

	/* Opening and closing ------------------------------------------------ */

	function show(): void {
		if ( ! dialog!.open ) {
			dialog!.showModal();
		}

		input!.focus();
		input!.select();
	}

	function hide(): void {
		if ( dialog!.open ) {
			dialog!.close();
		}
	}

	opener.addEventListener( 'click', show );
	closer.addEventListener( 'click', hide );

	// The panel fills the dialog's width, so a click on the <dialog> itself
	// landed on the backdrop below the sheet.
	dialog.addEventListener( 'click', ( event ) => {
		if ( event.target === dialog ) {
			hide();
		}
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if (
			event.key !== '/' ||
			event.ctrlKey ||
			event.metaKey ||
			event.altKey ||
			event.defaultPrevented ||
			isTyping( event.target ) ||
			document.querySelector( 'dialog[open]' )
		) {
			return;
		}

		event.preventDefault();
		show();
	} );

	/* States --------------------------------------------------------------- */

	function setActive( index: number ): void {
		options[ active ]?.setAttribute( 'aria-selected', 'false' );
		active = index;

		const option = options[ index ];

		if ( option ) {
			option.setAttribute( 'aria-selected', 'true' );
			input!.setAttribute( 'aria-activedescendant', option.id );
			option.scrollIntoView( { block: 'nearest' } );
		} else {
			input!.removeAttribute( 'aria-activedescendant' );
		}
	}

	function announce( message: string ): void {
		status!.textContent = message;
	}

	/** Nothing typed yet: the hint and the catalogue's groups. */
	function showStart(): void {
		current = null;
		options = [];
		setActive( -1 );
		list!.replaceChildren();
		list!.hidden = true;
		empty!.hidden = true;
		foot!.hidden = true;
		start!.hidden = false;
		input!.setAttribute( 'aria-expanded', 'false' );
		announce( '' );
	}

	function showMessage( message: string, withBranch: boolean ): void {
		options = [];
		setActive( -1 );
		list!.replaceChildren();
		list!.hidden = true;
		start!.hidden = true;
		foot!.hidden = true;
		input!.setAttribute( 'aria-expanded', 'false' );

		const text = document.createElement( 'p' );
		text.className = 'dh-finder__none';
		text.textContent = message;
		empty!.replaceChildren( text );

		if ( withBranch && root.dataset.branch ) {
			const link = document.createElement( 'a' );
			link.className = 'dh-finder__ask';
			link.href = root.dataset.branch;
			link.textContent = strings.ask;
			empty!.append( link );
		}

		empty!.hidden = false;
		announce( message );
	}

	function row( data: Row, needles: string[] ): HTMLAnchorElement {
		const option = document.createElement( 'a' );
		option.className = 'dh-finder__row';
		option.href = data.url;
		option.id = `dh-finder-option-${ options.length }`;
		option.tabIndex = -1;
		option.setAttribute( 'role', 'option' );
		option.setAttribute( 'aria-selected', 'false' );

		const meta = span(
			'dh-finder__meta dh-mono',
			data.markMeta ? highlight( data.meta, needles ) : data.meta
		);

		if ( data.exact ) {
			meta.append( span( 'dh-finder__exact', strings.exact ) );
		}

		option.append(
			span( 'dh-finder__name', highlight( data.name, needles ) ),
			span( 'dh-finder__path dh-mono', data.path ),
			meta
		);

		options.push( option );
		return option;
	}

	function group( label: string, rows: HTMLAnchorElement[] ): HTMLElement | null {
		if ( ! rows.length ) {
			return null;
		}

		const id = `dh-finder-group-${ list!.children.length }`;
		const element = document.createElement( 'div' );
		element.className = 'dh-finder__set';
		element.setAttribute( 'role', 'group' );
		element.setAttribute( 'aria-labelledby', id );

		const heading = document.createElement( 'div' );
		heading.className = 'dh-finder__group';
		heading.id = id;
		heading.setAttribute( 'role', 'presentation' );
		heading.textContent = label;

		element.append( heading, ...rows );
		return element;
	}

	function render( found: Found ): void {
		current = found;
		options = [];
		setActive( -1 );
		start!.hidden = true;

		if ( ! found.categories.length && ! found.parts.length ) {
			showMessage( fill( strings.none, found.query ), true );
			return;
		}

		empty!.hidden = true;
		list!.replaceChildren();

		const categories = group(
			strings.categories,
			found.categories.map( ( category ) =>
				row(
					{
						name: category.name,
						path: category.path,
						meta: pad( category.count ),
						url: category.url,
						markMeta: false,
					},
					found.needles
				)
			)
		);

		const parts = group(
			strings.parts,
			found.parts.map( ( part ) =>
				row(
					{
						name: part.name,
						path: part.path,
						meta: part.sku,
						url: part.url,
						markMeta: true,
						exact: part.exact,
					},
					found.needles
				)
			)
		);

		list!.append( ...[ categories, parts ].filter( ( element ): element is HTMLElement => element !== null ) );
		list!.hidden = false;
		input!.setAttribute( 'aria-expanded', 'true' );

		if ( found.total > 0 ) {
			all!.href = found.resultsUrl;
			all!.textContent = found.total === 1 ? strings.allOne : fill( strings.all, pad( found.total ) );
			foot!.hidden = false;
		} else {
			foot!.hidden = true;
		}

		const count = found.categories.length + found.total;
		announce( count === 1 ? strings.statusOne : fill( strings.status, count ) );
	}

	/* Fetching ------------------------------------------------------------- */

	function find( query: string ): void {
		const cached = cache.get( query );

		if ( cached ) {
			render( cached );
			return;
		}

		controller?.abort();
		controller = new AbortController();

		const request = ++sequence;
		const url = new URL( endpoint!, window.location.href );
		url.searchParams.set( 'q', query );

		root.classList.add( 'is-loading' );
		list!.setAttribute( 'aria-busy', 'true' );

		fetch( url.toString(), {
			signal: controller.signal,
			headers: { Accept: 'application/json' },
		} )
			.then( ( response ) => {
				if ( ! response.ok ) {
					throw new Error( String( response.status ) );
				}

				return response.json() as Promise< Found >;
			} )
			.then( ( found ) => {
				cache.set( query, found );

				// Only the answer to what is in the field now is drawn.
				if ( request === sequence && input!.value.trim() === query ) {
					render( found );
				}
			} )
			.catch( ( error: Error ) => {
				if ( error.name !== 'AbortError' && request === sequence ) {
					showMessage( strings.error, false );
				}
			} )
			.finally( () => {
				if ( request === sequence ) {
					root.classList.remove( 'is-loading' );
					list!.removeAttribute( 'aria-busy' );
				}
			} );
	}

	input.addEventListener( 'input', () => {
		window.clearTimeout( timer );

		const query = input.value.trim();

		if ( query.length < MIN_LENGTH ) {
			controller?.abort();
			sequence++;
			root.classList.remove( 'is-loading' );
			showStart();
			return;
		}

		timer = window.setTimeout( () => find( query ), DEBOUNCE_MS );
	} );

	/* Keys ----------------------------------------------------------------- */

	input.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			// Also stops the browser clearing the field first: one press closes.
			event.preventDefault();
			hide();
			return;
		}

		if ( ! options.length || ( event.key !== 'ArrowDown' && event.key !== 'ArrowUp' ) ) {
			return;
		}

		event.preventDefault();

		if ( event.key === 'ArrowDown' ) {
			setActive( active + 1 >= options.length ? 0 : active + 1 );
		} else {
			setActive( active <= 0 ? options.length - 1 : active - 1 );
		}
	} );

	// Enter. Without this handler the form still submits to the results page.
	form.addEventListener( 'submit', ( event ) => {
		const query = input.value.trim();

		if ( ! query ) {
			event.preventDefault();
			return;
		}

		const chosen = options[ active ];

		if ( chosen ) {
			event.preventDefault();
			window.location.assign( chosen.href );
			return;
		}

		const exact = current && current.query === query ? current.parts.find( ( part ) => part.exact ) : undefined;

		if ( exact ) {
			event.preventDefault();
			window.location.assign( exact.url );
		}
	} );

	// Typing moves the choice back to the field.
	input.addEventListener( 'input', () => setActive( -1 ) );
}

document.querySelectorAll< HTMLElement >( '.dh-finder' ).forEach( setUp );
