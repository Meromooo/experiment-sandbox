import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-line-editor' } );

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Catalogue Index — rendered on the front end by render.php: every product group and its subcategories, in the same order as the mega menu. The order lives in inc/navigation.php.',
					'demas-theme'
				) }
			</p>
		</div>
	);
}
