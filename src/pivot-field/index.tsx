import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

registerBlockType( metadata.name, {
	// No settings: the drawing is fixed. The canvas shows it through
	// render.php, behind the hero's text, as the site does.
	edit: function Edit() {
		return (
			<div { ...useBlockProps() }>
				<ServerSideRender block={ metadata.name } />
			</div>
		);
	},
	// Rendered by render.php.
	save: () => null,
} );
