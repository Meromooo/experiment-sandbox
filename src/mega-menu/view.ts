import { store, getContext, getElement } from '@wordpress/interactivity';

interface MegaMenuContext {
	isOpen: boolean;
	/** True once the panel is held open by a click rather than by hover. */
	isPinned: boolean;
}

/**
 * Hover only drives the menu on devices that actually hover. On touch the same
 * pointer fires a synthetic mouseenter immediately before click, which would
 * open and then instantly re-close the panel — so hover is gated and touch
 * users get click only.
 */
const canHover = () =>
	typeof window !== 'undefined' &&
	window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches;

const close = ( context: MegaMenuContext ) => {
	context.isOpen = false;
	context.isPinned = false;
};

store( 'demas-theme/mega-menu', {
	actions: {
		/**
		 * Hover previews, click pins.
		 *
		 * Without the pin, hovering would open the panel and the click that
		 * follows would read as "already open" and shut it again — the menu
		 * would appear to close the moment you clicked its own trigger.
		 */
		toggle: () => {
			const context = getContext< MegaMenuContext >();

			if ( context.isOpen && ! context.isPinned ) {
				context.isPinned = true;
				return;
			}

			context.isOpen = ! context.isOpen;
			context.isPinned = context.isOpen;
		},

		openOnHover: () => {
			if ( ! canHover() ) {
				return;
			}
			getContext< MegaMenuContext >().isOpen = true;
		},

		closeOnHover: () => {
			const context = getContext< MegaMenuContext >();

			if ( ! canHover() || context.isPinned ) {
				return;
			}

			context.isOpen = false;
		},

		/**
		 * Escape closes and hands focus back to the trigger, so keyboard users
		 * are never stranded inside a panel they can no longer see.
		 */
		onKeydown: ( event: KeyboardEvent ) => {
			if ( event.key !== 'Escape' ) {
				return;
			}

			const context = getContext< MegaMenuContext >();

			if ( ! context.isOpen ) {
				return;
			}

			event.preventDefault();
			close( context );

			const { ref } = getElement();
			ref?.querySelector< HTMLButtonElement >( '.dh-mega-menu__trigger' )?.focus();
		},

		/** Tabbing past the last link closes the panel behind you. */
		onFocusOut: ( event: FocusEvent ) => {
			const { ref } = getElement();
			const next = event.relatedTarget as Node | null;

			if ( next && ref?.contains( next ) ) {
				return;
			}

			close( getContext< MegaMenuContext >() );
		},

		/** A pinned panel stays open when the pointer leaves; this dismisses it. */
		onDocumentClick: ( event: MouseEvent ) => {
			const context = getContext< MegaMenuContext >();

			if ( ! context.isOpen ) {
				return;
			}

			const { ref } = getElement();
			const target = event.target as Node | null;

			if ( target && ref?.contains( target ) ) {
				return;
			}

			close( context );
		},
	},
} );
