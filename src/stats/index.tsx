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
			useBlockProps( { className: 'dh-stats' } ),
			{ template: [ [ 'demas-theme/stat' ] ] }
		);

		return <dl { ...innerBlocksProps } />;
	},
	// The list itself comes from render.php; only the numbers are saved.
	save: () => <InnerBlocks.Content />,
} );
