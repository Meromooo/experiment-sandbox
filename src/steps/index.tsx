import { registerBlockType } from '@wordpress/blocks';
import {
	InnerBlocks,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: function Edit() {
		const innerBlocksProps = useInnerBlocksProps(
			useBlockProps( { className: 'dh-process__steps' } ),
			{
				template: [ [ 'demas-theme/step' ] ],
				orientation: 'horizontal',
			}
		);

		return <ol { ...innerBlocksProps } />;
	},
	// The list itself comes from render.php; only the steps are saved.
	save: () => <InnerBlocks.Content />,
} );
