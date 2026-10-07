import { registerBlockType } from '@wordpress/blocks';
import {
	InspectorControls,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

type Attributes = {
	value: string;
	prefix: string;
	label: string;
	unit: string;
	drawing: string;
	since: string;
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
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Word beside the number', 'demas-theme' ) }
							help={ __(
								'Optional, for example Years. On the homepage band it is shown in place of what the number counts, which screen readers still hear.',
								'demas-theme'
							) }
							value={ attributes.unit }
							onChange={ ( unit: string ) =>
								setAttributes( { unit } )
							}
						/>
						<SelectControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Drawing under the number', 'demas-theme' ) }
							help={ __(
								'Drawn on the site, not here. It needs a whole number.',
								'demas-theme'
							) }
							value={ attributes.drawing }
							options={ [
								{ label: __( 'None', 'demas-theme' ), value: '' },
								{
									label: __( 'Scale of years', 'demas-theme' ),
									value: 'scale',
								},
								{
									label: __( 'Branch network', 'demas-theme' ),
									value: 'network',
								},
							] }
							onChange={ ( drawing: string ) =>
								setAttributes( { drawing } )
							}
						/>
						{ 'scale' === attributes.drawing && (
							<TextControl
								__nextHasNoMarginBottom
								__next40pxDefaultSize
								label={ __( 'First year on the scale', 'demas-theme' ) }
								help={ __(
									'For example 1979. The scale runs from it to today, a tick a year.',
									'demas-theme'
								) }
								value={ attributes.since }
								onChange={ ( since: string ) =>
									setAttributes( { since } )
								}
							/>
						) }
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
						<span className="dh-stat__figure">
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
							{ attributes.unit && (
								<span className="dh-stat__unit">
									{ attributes.unit }
								</span>
							) }
						</span>
					</dd>
				</div>
			</>
		);
	},
	// Rendered by render.php.
	save: () => null,
} );
