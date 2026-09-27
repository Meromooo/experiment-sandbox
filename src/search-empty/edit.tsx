import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-line-editor' } );

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Search: No Results — rendered on the front end by render.php when a search finds nothing: what was searched, a part-number hint, and the ways onward.',
					'demas-theme'
				) }
			</p>
		</div>
	);
}
