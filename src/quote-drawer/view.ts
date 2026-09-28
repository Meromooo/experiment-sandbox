import { store, getContext, getElement } from '@wordpress/interactivity';

/**
 * The quote list — shared by the header's Quote List block (this module) and
 * every Add to Quote button on the page.
 *
 * The list lives in the buyer's browser. Each line is a snapshot of the part
 * as it was when it was added — ID, quantity, name, SKU, thumbnail, link — so
 * the list draws on any page without a request. Sending it to a branch is
 * AMM-140 and will resolve parts by ID on the server, so a renamed product
 * only ever looks stale here, never gets quoted wrong.
 */

/** One part in the list. */
interface Item {
	id: number;
	qty: number;
	name: string;
	sku: string;
	image: string;
	url: string;
}

/** An item as the list draws it: numbered and with its control labels. */
interface Line extends Item {
	n: string;
	atMin: boolean;
	quantityLabel: string;
	decreaseLabel: string;
	increaseLabel: string;
	removeLabel: string;
}

/** Translated strings, passed from render.php as server state. */
interface Strings {
	part: string;
	parts: string;
	added: string;
	removed: string;
	cleared: string;
	quantity: string;
	decrease: string;
	increase: string;
	remove: string;
	sheetTitle: string;
	printed: string;
	colLine: string;
	colPart: string;
	colSku: string;
	colQty: string;
	blanks: string[];
	textTitle: string;
	copy: string;
	share: string;
	copied: string;
	copyFallback: string;
	contact: string;
}

interface ButtonContext {
	item?: Omit< Item, 'qty' >;
	/** Sheet view: how many "Add to quote" adds, until the part is in the quote. */
	qty?: number;
}

interface LineContext {
	line: Line;
}

const STORAGE_KEY = 'demas-theme/quote';

/** A bill of quantities, not a warehouse: four digits is plenty. */
const MAX_QTY = 9999;

const pad = ( n: number ): string => String( n ).padStart( 3, '0' );

const fill = ( template: string, name: string ): string =>
	template.replace( '%s', name );

const clamp = ( n: number ): number =>
	Math.min( MAX_QTY, Math.max( 1, Math.round( n ) ) );

const isItem = ( value: unknown ): value is Item => {
	const v = value as Item;

	return (
		!! v &&
		typeof v.id === 'number' &&
		typeof v.qty === 'number' &&
		typeof v.name === 'string' &&
		typeof v.sku === 'string' &&
		typeof v.image === 'string' &&
		typeof v.url === 'string'
	);
};

/**
 * Anything unreadable — storage blocked, a hand-edited value, an older shape —
 * reads as an empty list rather than breaking every page the header is on.
 */
const read = (): Item[] => {
	try {
		const parsed: unknown = JSON.parse(
			window.localStorage.getItem( STORAGE_KEY ) || '[]'
		);

		return Array.isArray( parsed )
			? parsed
					.filter( isItem )
					.map( ( item ) => ( { ...item, qty: clamp( item.qty ) } ) )
			: [];
	} catch {
		return [];
	}
};

const write = ( items: Item[] ): void => {
	try {
		window.localStorage.setItem( STORAGE_KEY, JSON.stringify( items ) );
	} catch {
		// Storage blocked (private browsing, full quota): the list still works
		// on this page, it just will not follow the buyer to the next one.
	}
};

const { state } = store( 'demas-theme/quote', {
	state: {
		items: [] as Item[],
		isOpen: false,
		/** False until the saved list has been read; buttons stay hidden until then. */
		ready: false,
		announcement: '',
		strings: {} as Strings,
		/** "Copy list", or "Share" where the phone has a share sheet. */
		shareLabel: '',
		/** The copy-by-hand field, shown only when the clipboard is blocked. */
		copyText: '',
		showCopy: false,

		get hasItems(): boolean {
			return state.items.length > 0;
		},

		get countLabel(): string {
			return pad( state.items.length );
		},

		/** "003" reads as "zero zero three" aloud; the header says "3 parts" instead. */
		get countSpoken(): string {
			const n = state.items.length;

			return n
				? `${ n } ${ n === 1 ? state.strings.part : state.strings.parts }`
				: '';
		},

		get partsLabel(): string {
			const n = state.items.length;

			return `${ pad( n ) } ${
				n === 1 ? state.strings.part : state.strings.parts
			}`;
		},

		/** For an Add to Quote button: is its part already in the list? */
		get isAdded(): boolean {
			const { item } = getContext< ButtonContext >();

			return !! item && state.items.some( ( i ) => i.id === item.id );
		},

		/**
		 * Sheet view's quantity field: the quantity in the quote once the part
		 * is there, otherwise the one the buyer is about to add.
		 */
		get rowQty(): number {
			const { item, qty } = getContext< ButtonContext >();
			const stored = item ? state.items.find( ( i ) => i.id === item.id ) : undefined;

			return stored ? stored.qty : clamp( qty ?? 1 );
		},

		get rowAtMin(): boolean {
			return state.rowQty <= 1;
		},

		/**
		 * Numbered like the lines of a bill of quantities — 001, 002 — because a
		 * buyer on the phone to a branch cites parts by line.
		 */
		get lines(): Line[] {
			return state.items.map( ( item, i ) => ( {
				...item,
				n: pad( i + 1 ),
				atMin: item.qty <= 1,
				quantityLabel: fill( state.strings.quantity, item.name ),
				decreaseLabel: fill( state.strings.decrease, item.name ),
				increaseLabel: fill( state.strings.increase, item.name ),
				removeLabel: fill( state.strings.remove, item.name ),
			} ) );
		},
	},

	actions: {
		/**
		 * First press adds the part. Once it is in the list the button reads
		 * "Added to quote", and pressing it opens the list — a second press
		 * never adds a duplicate line.
		 */
		addOrOpen(): void {
			const { item } = getContext< ButtonContext >();

			if ( ! item ) {
				return;
			}

			if ( state.items.some( ( i ) => i.id === item.id ) ) {
				showList();
				return;
			}

			const { qty } = getContext< ButtonContext >();

			save( [ ...state.items, { ...item, qty: clamp( qty ?? 1 ) } ] );
			announce( fill( state.strings.added, item.name ) );
		},

		/*
		 * Sheet view's steppers and field. Before the part is in the quote they
		 * set the row's own quantity; after, they edit the quote — the same
		 * number the list shows.
		 */
		rowIncrement(): void {
			setRowQty( state.rowQty + 1 );
		},

		rowDecrement(): void {
			setRowQty( state.rowQty - 1 );
		},

		rowSetQuantity( event: Event ): void {
			const input = event.target as HTMLInputElement;
			const typed = parseInt( input.value, 10 );
			const next = Number.isFinite( typed ) ? clamp( typed ) : state.rowQty;

			setRowQty( next );
			input.value = String( next );
		},

		open(): void {
			showList();
		},

		close(): void {
			hideList();
		},

		/** Escape, or the dialog closing for any other reason. */
		onDialogClose(): void {
			state.isOpen = false;
		},

		/**
		 * The panel's inner wrapper fills the dialog, so a click that lands on
		 * the <dialog> element itself landed on the backdrop around it.
		 */
		onDialogClick( event: MouseEvent ): void {
			if ( event.target === event.currentTarget ) {
				hideList();
			}
		},

		/*
		 * Steps read the stored quantity, not the drawn row's: the row redraws a
		 * frame later, so two presses before that (a held-down key) would both
		 * start from the old number and add one instead of two.
		 */
		increment(): void {
			const { line } = getContext< LineContext >();
			setQty( line.id, currentQty( line.id ) + 1 );
		},

		decrement(): void {
			const { line } = getContext< LineContext >();
			setQty( line.id, currentQty( line.id ) - 1 );
		},

		/** Typed quantities: clamped to 1–9999; anything unreadable reverts. */
		setQuantity( event: Event ): void {
			const input = event.target as HTMLInputElement;
			const { line } = getContext< LineContext >();
			const typed = parseInt( input.value, 10 );
			const next = Number.isFinite( typed ) ? clamp( typed ) : line.qty;

			setQty( line.id, next );

			// If the clamped value equals the stored one nothing re-renders, and
			// the field would keep showing what was typed.
			input.value = String( next );
		},

		remove(): void {
			const { line } = getContext< LineContext >();
			const { ref } = getElement();
			const heading = ref
				?.closest( 'dialog' )
				?.querySelector< HTMLElement >( '#dh-quote-title' );

			save( state.items.filter( ( i ) => i.id !== line.id ) );
			announce( fill( state.strings.removed, line.name ) );

			// The focused button leaves with its row; keep focus in the dialog.
			heading?.focus();
		},

		clear(): void {
			const { ref } = getElement();
			const heading = ref
				?.closest( 'dialog' )
				?.querySelector< HTMLElement >( '#dh-quote-title' );

			save( [] );
			announce( state.strings.cleared );
			heading?.focus();
		},

		/*
		 * Print the list (AMM-163). The sheet is built from the stored list and
		 * printed on its own; the list reopens afterwards (see printQuote).
		 */
		printList(): void {
			printQuote();
		},

		/**
		 * The list as text: the phone's share sheet where there is one (WhatsApp,
		 * Mail…), otherwise the clipboard — and, if the clipboard is blocked,
		 * a selected field to copy from by hand.
		 */
		shareList(): void {
			const text = plainText();

			if ( ! text ) {
				return;
			}

			if ( canShare() ) {
				navigator
					.share( { title: firstLine( text ), text } )
					.catch( () => {
						// Dismissed, or the share sheet failed: nothing to undo.
					} );
				return;
			}

			if ( navigator.clipboard?.writeText ) {
				navigator.clipboard.writeText( text ).then(
					() => {
						state.showCopy = false;
						announce( state.strings.copied );
					},
					() => showCopyField( text )
				);
				return;
			}

			showCopyField( text );
		},

		/** Another tab changed the list: follow it. */
		syncFromStorage( event: StorageEvent ): void {
			if ( event.key === STORAGE_KEY || event.key === null ) {
				state.items = read();
			}
		},
	},

	callbacks: {
		init(): void {
			state.items = read();
			state.ready = true;

			// Set here, not left to the server's value: the store's own initial
			// '' above would otherwise replace it and blank the button.
			state.shareLabel = canShare() ? state.strings.share : state.strings.copy;

			/*
			 * Every print decides afresh what it prints. Print pressed: the list
			 * (already prepared). Otherwise, the list if it is open (Ctrl+P
			 * while reading it), else the page.
			 */
			window.addEventListener( 'beforeprint', () => {
				if ( printingQuote ) {
					return;
				}

				if ( dialogElement()?.open && state.items.length ) {
					preparePrint( true );
				} else {
					clearPrintMode();
				}
			} );

			window.addEventListener( 'afterprint', finishPrint );

			// The printed sheet is invisible on screen, so it can wait to be
			// removed until the buyer next clicks or types — never while a print
			// may still be capturing the page.
			[ 'pointerdown', 'keydown' ].forEach( ( type ) =>
				window.addEventListener(
					type,
					() => {
						if ( ! printingQuote ) {
							clearPrintMode();
						}
					},
					true
				)
			);
		},

	},
} );

function save( items: Item[] ): void {
	state.items = items;
	write( items );
}

/*
 * The dialog is opened and closed here, inside the click, and nowhere else.
 *
 * An earlier version flipped state.isOpen and let a data-wp-watch move the
 * dialog a frame later. A busy or throttled page held that frame back, so a
 * press on "Your quote" after closing with the ✕ appeared to do nothing; and
 * the watch could reopen a dialog the browser had just closed on Escape,
 * because the browser's `close` event arrives a moment after the close. The
 * only way the dialog closes without passing through here is the browser's
 * own (Escape), and onDialogClose brings the state back into line for that.
 */
function dialogElement(): HTMLDialogElement | null {
	return document.getElementById( 'dh-quote-dialog' ) as HTMLDialogElement | null;
}

function showList(): void {
	state.isOpen = true;

	const dialog = dialogElement();

	if ( dialog && ! dialog.open ) {
		dialog.showModal();
	}
}

function hideList(): void {
	state.isOpen = false;

	const dialog = dialogElement();

	if ( dialog?.open ) {
		dialog.close();
	}
}

function currentQty( id: number ): number {
	return state.items.find( ( item ) => item.id === id )?.qty ?? 1;
}

/** A card's quantity: into the quote if the part is there, else the card's own. */
function setRowQty( qty: number ): void {
	const context = getContext< ButtonContext >();
	const { item } = context;

	if ( item && state.items.some( ( i ) => i.id === item.id ) ) {
		setQty( item.id, qty );
	} else {
		context.qty = clamp( qty );
	}
}

function setQty( id: number, qty: number ): void {
	save(
		state.items.map( ( item ) =>
			item.id === id ? { ...item, qty: clamp( qty ) } : item
		)
	);
}

/* Printing and sharing (AMM-163) ------------------------------------------ */

const SHEET_ID = 'dh-quote-print';
const PRINT_CLASS = 'dh-print-quote';

/** Reopen the list after printing: it was open when printing began. */
let reopenAfterPrint = false;

/** Between pressing Print (or Ctrl+P on the list) and the print finishing. */
let printingQuote = false;

/** Phones and tablets with a share sheet; desktops copy instead. */
function canShare(): boolean {
	return (
		typeof navigator.share === 'function' &&
		window.matchMedia( '(pointer: coarse)' ).matches
	);
}

/** "%1$s … %2$s" templates from the server. */
function format( template: string, ...values: string[] ): string {
	return values.reduce(
		( out, value, i ) => out.replace( `%${ i + 1 }$s`, value ),
		template
	);
}

function totalQty(): number {
	return state.items.reduce( ( sum, item ) => sum + item.qty, 0 );
}

function printedDate(): string {
	return new Date().toLocaleDateString( 'en-GB', {
		day: 'numeric',
		month: 'short',
		year: 'numeric',
	} );
}

function el< K extends keyof HTMLElementTagNameMap >(
	tag: K,
	className: string,
	text?: string
): HTMLElementTagNameMap[ K ] {
	const node = document.createElement( tag );
	node.className = className;
	if ( text !== undefined ) {
		node.textContent = text;
	}
	return node;
}

/**
 * The printed sheet, built as nodes from the stored list — text only, never
 * parsed HTML. A real table, so the columns line up and the heading row
 * repeats on every printed page.
 */
function buildSheet(): HTMLElement {
	const s = state.strings;
	const sheet = el( 'section', 'dh-qsheet' );
	sheet.id = SHEET_ID;

	const head = el( 'header', 'dh-qsheet__head' );
	head.append(
		el( 'p', 'dh-qsheet__title', format( s.sheetTitle, pad( state.items.length ), String( totalQty() ) ) ),
		el( 'p', 'dh-qsheet__date', format( s.printed.replace( '%s', '%1$s' ), printedDate() ) )
	);

	const table = el( 'table', 'dh-qsheet__table' );
	const headRow = document.createElement( 'tr' );
	[ s.colLine, s.colPart, s.colSku, s.colQty ].forEach( ( label, i ) => {
		const th = el( 'th', `dh-qsheet__col dh-qsheet__col--${ i }`, label );
		th.scope = 'col';
		headRow.append( th );
	} );
	table.createTHead().append( headRow );

	const body = table.createTBody();
	state.items.forEach( ( item, i ) => {
		const row = document.createElement( 'tr' );
		row.append(
			el( 'td', 'dh-qsheet__line', pad( i + 1 ) ),
			el( 'td', 'dh-qsheet__name', item.name ),
			el( 'td', 'dh-qsheet__sku', item.sku ),
			el( 'td', 'dh-qsheet__qty', String( item.qty ) )
		);
		body.append( row );
	} );

	const blanks = el( 'div', 'dh-qsheet__blanks' );
	( s.blanks || [] ).forEach( ( label ) => {
		const field = el( 'p', 'dh-qsheet__blank' );
		field.append( el( 'span', 'dh-qsheet__blank-label', label ), el( 'span', 'dh-qsheet__blank-line' ) );
		blanks.append( field );
	} );

	sheet.append( head, table, blanks, el( 'p', 'dh-qsheet__contact', s.contact ) );

	return sheet;
}

/** Mount the sheet and switch the page to print only it. */
function preparePrint( reopen: boolean ): void {
	clearPrintMode();
	reopenAfterPrint = reopen;
	printingQuote = true;
	hideList();
	document.body.append( buildSheet() );
	document.documentElement.classList.add( PRINT_CLASS );
}

/** Back to printing the page (the sheet is hidden on screen either way). */
function clearPrintMode(): void {
	document.documentElement.classList.remove( PRINT_CLASS );
	document.getElementById( SHEET_ID )?.remove();
}

function printQuote(): void {
	if ( ! state.items.length ) {
		return;
	}

	preparePrint( true );
	window.print();
}

/*
 * The print dialog closed — printed or cancelled. Reopen the list where the
 * buyer left it. The sheet itself stays until the next print or interaction:
 * some print paths (a PDF export, found in testing) fire afterprint before
 * they have captured the page, and removing it here printed the page instead.
 */
function finishPrint(): void {
	if ( ! printingQuote ) {
		return;
	}

	printingQuote = false;

	if ( reopenAfterPrint ) {
		reopenAfterPrint = false;
		showList();
		document.querySelector< HTMLElement >( '.dh-quote-dialog__action--print' )?.focus();
	}
}

/** The list as plain text, for WhatsApp or email. */
function plainText(): string {
	if ( ! state.items.length ) {
		return '';
	}

	const lines = state.items.map( ( item, i ) =>
		[ pad( i + 1 ), item.sku, item.name ].filter( Boolean ).join( '  ' ) + `  × ${ item.qty }`
	);

	return [
		format( state.strings.textTitle, String( state.items.length ), String( totalQty() ) ),
		...lines,
	].join( '\n' );
}

function firstLine( text: string ): string {
	return text.split( '\n' )[ 0 ];
}

/** Clipboard blocked: show the text, selected, to copy by hand. */
function showCopyField( text: string ): void {
	state.copyText = text;
	state.showCopy = true;
	announce( state.strings.copyFallback );

	window.setTimeout( () => {
		const field = document.getElementById( 'dh-quote-copy' ) as HTMLTextAreaElement | null;
		field?.focus();
		field?.select();
	}, 60 );
}

/**
 * Screen-reader announcement via the drawer's role="status" region. Cleared
 * first so the same message twice in a row is still read.
 */
function announce( message: string ): void {
	state.announcement = '';
	window.setTimeout( () => {
		state.announcement = message;
	}, 50 );
}
