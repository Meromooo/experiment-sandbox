import { registerBlockType } from '@wordpress/blocks';
import {
	InnerBlocks,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';

import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: function Edit() {
		// The editor shows the plates standing still, as a wall.
		const innerBlocksProps = useInnerBlocksProps(
			useBlockProps( { className: 'dh-belt__wall' } ),
			{
				template: [ [ 'demas-theme/credential' ] ],
				orientation: 'horizontal',
			}
		);

		return <ul { ...innerBlocksProps } />;
	},
	// The belt itself comes from render.php; only the credentials are saved.
	save: () => <InnerBlocks.Content />,
} );
