import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-toolbar-editor' } );

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Catalogue Toolbar — rendered on the front end by render.php: product count, child-category rail and sort links for the current archive.',
					'demas-theme'
				) }
			</p>
		</div>
	);
}
