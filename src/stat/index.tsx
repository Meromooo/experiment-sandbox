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
	value: string;
	prefix: string;
	label: string;
};

type EditProps = {
	attributes: Attributes;
	setAttributes: ( next: Partial< Attributes > ) => void;
};

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes }: EditProps ) {
		const blockProps = useBlockProps( { className: 'dh-stat' } );

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Number', 'demas-theme' ) }>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Before the number', 'demas-theme' ) }
							help={ __(
								'Optional, in green — for example ~ or +. A whole number counts up from zero as it scrolls into view.',
								'demas-theme'
							) }
							value={ attributes.prefix }
							onChange={ ( prefix: string ) =>
								setAttributes( { prefix } )
							}
						/>
					</PanelBody>
				</InspectorControls>
				<div { ...blockProps }>
					<RichText
						tagName="dt"
						className="dh-stat__label"
						value={ attributes.label }
						onChange={ ( label: string ) => setAttributes( { label } ) }
						allowedFormats={ [] }
						placeholder={ __( 'What it counts', 'demas-theme' ) }
					/>
					<dd className="dh-stat__value">
						{ attributes.prefix && (
							<span className="dh-stat__prefix">
								{ attributes.prefix }
							</span>
						) }
						<RichText
							tagName="span"
							value={ attributes.value }
							onChange={ ( value: string ) =>
								setAttributes( { value } )
							}
							allowedFormats={ [] }
							placeholder="46"
						/>
					</dd>
				</div>
			</>
		);
	},
	// Rendered by render.php.
	save: () => null,
} );
