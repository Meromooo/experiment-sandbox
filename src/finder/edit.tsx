import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-line-editor' } );

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Jump to Part — rendered on the front end by render.php and view.ts: the header search control and the finder it opens. Matching lives in inc/search.php.',
					'demas-theme'
				) }
			</p>
		</div>
	);
}
