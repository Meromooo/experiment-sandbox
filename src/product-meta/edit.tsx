import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-line-editor' } );

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Part Details — rendered on the front end by render.php: line number, part number and category for the product in this card.',
					'demas-theme'
				) }
			</p>
		</div>
	);
}
