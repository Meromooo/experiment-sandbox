import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-line-editor' } );

	return (
		<div { ...blockProps }>
			<p>
				{ __(
					'Branches and Key Plan — rendered on the front end by render.php: the fifteen branches as links to the homepage Branch Desk, beside a plan of where each one sits in the Kingdom. The list lives in inc/branches.php.',
					'demas-theme'
				) }
			</p>
		</div>
	);
}
