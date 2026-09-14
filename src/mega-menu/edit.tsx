import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-mega-menu-editor' } );

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Mega Menu — rendered on the front end by render.php. Category columns are populated from the WooCommerce category tree.',
					'demas-theme'
				) }
			</p>
		</div>
	);
}
