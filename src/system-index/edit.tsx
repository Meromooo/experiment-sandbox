import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-line-editor' } );

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Browse by System — rendered on the front end by render.php: the stages of the irrigation, fog or workshop system for the current archive, each linking to its existing categories. The map lives in inc/system-map.php.',
					'demas-theme'
				) }
			</p>
		</div>
	);
}
