import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-summary-editor' } );

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Product Datasheet — rendered on the front end by render.php: category, name, the nameplate (SKU, brand, series, system stage), the quote action and the specification for the product being viewed.',
					'demas-theme'
				) }
			</p>
		</div>
	);
}
