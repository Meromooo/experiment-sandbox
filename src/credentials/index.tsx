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
			useBlockProps( { className: 'dh-creds__list' } ),
			{
				template: [ [ 'demas-theme/credential' ] ],
				orientation: 'horizontal',
			}
		);

		return <ul { ...innerBlocksProps } />;
	},
	// The list itself comes from render.php; only the credentials are saved.
	save: () => <InnerBlocks.Content />,
} );
