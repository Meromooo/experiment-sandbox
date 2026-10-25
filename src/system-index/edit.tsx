import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-line-editor' } );

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Category index: rendered on the front end by render.php. The category tree of the current archive, under the categories’ own names.',
					'demas-theme'
				) }
			</p>
		</div>
	);
}
