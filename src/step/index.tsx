import { registerBlockType } from '@wordpress/blocks';
import {
	InnerBlocks,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: function Edit() {
		// A step is its name and nothing else (AMM-187): the text is
		// editable, the structure is not, so every step keeps the same shape.
		const innerBlocksProps = useInnerBlocksProps(
			useBlockProps( { className: 'dh-process__step' } ),
			{
				template: [
					[
						'core/heading',
						{
							level: 3,
							className: 'dh-process__title',
							placeholder: __( 'Step', 'demas-theme' ),
						},
					],
				],
				templateLock: 'all',
			}
		);

		return <li { ...innerBlocksProps } />;
	},
	// The list item comes from render.php; only its name is saved.
	save: () => <InnerBlocks.Content />,
} );
