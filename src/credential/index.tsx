import { registerBlockType } from '@wordpress/blocks';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

type Attributes = {
	name: string;
	detail: string;
};

type EditProps = {
	attributes: Attributes;
	setAttributes: ( next: Partial< Attributes > ) => void;
};

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes }: EditProps ) {
		const blockProps = useBlockProps( { className: 'dh-creds__item' } );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Detail', 'demas-theme' ) }>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __(
								'One line shown on hover and keyboard focus',
								'demas-theme'
							) }
							help={ __(
								'Leave empty for a mark that needs no explanation.',
								'demas-theme'
							) }
							value={ attributes.detail }
							onChange={ ( detail: string ) =>
								setAttributes( { detail } )
							}
						/>
					</PanelBody>
				</InspectorControls>
				<li { ...blockProps }>
					<RichText
						tagName="span"
						className="dh-creds__mark"
						value={ attributes.name }
						onChange={ ( name: string ) => setAttributes( { name } ) }
						allowedFormats={ [] }
						placeholder={ __( 'Certification', 'demas-theme' ) }
					/>
				</li>
			</>
		);
	},
	// Rendered by render.php.
	save: () => null,
} );
