import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps( { className: 'dh-quote-add-editor' } );

	return (
		<div { ...blockProps }>
			<span>{ __( '+ Add to quote', 'demas-theme' ) }</span>
		</div>
	);
}
