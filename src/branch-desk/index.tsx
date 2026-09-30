import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { Disabled, PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

type Attributes = {
	eyebrow: string;
	title: string;
};

type EditProps = {
	attributes: Attributes;
	setAttributes: ( next: Partial< Attributes > ) => void;
};

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes }: EditProps ) {
		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Heading', 'demas-theme' ) }>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Label above', 'demas-theme' ) }
							value={ attributes.eyebrow }
							onChange={ ( eyebrow: string ) =>
								setAttributes( { eyebrow } )
							}
						/>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Title', 'demas-theme' ) }
							help={ __(
								'The cities and who answers at each come from the theme’s branch list.',
								'demas-theme'
							) }
							value={ attributes.title }
							onChange={ ( title: string ) =>
								setAttributes( { title } )
							}
						/>
					</PanelBody>
				</InspectorControls>
				<div { ...useBlockProps() }>
					<Disabled>
						<ServerSideRender
							block={ metadata.name }
							attributes={ attributes }
						/>
					</Disabled>
				</div>
			</>
		);
	},
	// Rendered by render.php.
	save: () => null,
} );
