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
}

interface ButtonContext {
	item?: Omit< Item, 'qty' >;
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
				state.isOpen = true;
				return;
			}

			save( [ ...state.items, { ...item, qty: 1 } ] );
			announce( fill( state.strings.added, item.name ) );
		},

		open(): void {
			state.isOpen = true;
		},

		close(): void {
			state.isOpen = false;
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
				state.isOpen = false;
			}
		},

		increment(): void {
			const { line } = getContext< LineContext >();
			setQty( line.id, line.qty + 1 );
		},

		decrement(): void {
			const { line } = getContext< LineContext >();
			setQty( line.id, line.qty - 1 );
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
		},

		/** Keeps the native dialog in step with the store. */
		syncDialog(): void {
			const { ref } = getElement();
			const dialog = ref as HTMLDialogElement | null;

			if ( ! dialog ) {
				return;
			}

			if ( state.isOpen && ! dialog.open ) {
				dialog.showModal();
			} else if ( ! state.isOpen && dialog.open ) {
				dialog.close();
			}
		},
	},
} );

function save( items: Item[] ): void {
	state.items = items;
	write( items );
}

function setQty( id: number, qty: number ): void {
	save(
		state.items.map( ( item ) =>
			item.id === id ? { ...item, qty: clamp( qty ) } : item
		)
	);
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
