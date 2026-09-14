import { store, getContext } from '@wordpress/interactivity';

interface MegaMenuContext {
	isOpen: boolean;
}

store( 'demas-theme/mega-menu', {
	actions: {
		toggle: () => {
			const context = getContext< MegaMenuContext >();
			context.isOpen = ! context.isOpen;
		},
		open: () => {
			const context = getContext< MegaMenuContext >();
			context.isOpen = true;
		},
		close: () => {
			const context = getContext< MegaMenuContext >();
			context.isOpen = false;
		},
	},
} );
