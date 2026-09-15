import { store, getContext, getElement } from '@wordpress/interactivity';

interface MegaMenuContext {
	isOpen: boolean;
}

/**
 * Hover opens the menu on devices that actually hover. On touch the same
 * pointer fires a synthetic mouseenter immediately before click, which would
 * open and then instantly re-close the panel — so hover is gated and touch
 * users get click only.
 */
const canHover = () =>
	typeof window !== 'undefined' &&
	window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches;

store( 'demas-theme/mega-menu', {
	actions: {
		toggle: () => {
			const context = getContext< MegaMenuContext >();
			context.isOpen = ! context.isOpen;
		},

		openOnHover: () => {
			if ( ! canHover() ) {
				return;
			}
			getContext< MegaMenuContext >().isOpen = true;
		},

		closeOnHover: () => {
			if ( ! canHover() ) {
				return;
			}
			getContext< MegaMenuContext >().isOpen = false;
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
			context.isOpen = false;

			const { ref } = getElement();
			const trigger = ref?.querySelector< HTMLButtonElement >( '.dh-mega-menu__trigger' );
			trigger?.focus();
		},

		/**
		 * Tabbing past the last link closes the panel behind you.
		 */
		onFocusOut: ( event: FocusEvent ) => {
			const { ref } = getElement();
			const next = event.relatedTarget as Node | null;

			if ( next && ref?.contains( next ) ) {
				return;
			}

			getContext< MegaMenuContext >().isOpen = false;
		},
	},
} );
