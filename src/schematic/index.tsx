import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

type Attributes = {
	tabTitle: string;
	tabCopy: string;
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
					<PanelBody title={ __( 'Corner note', 'demas-theme' ) }>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Title', 'demas-theme' ) }
							value={ attributes.tabTitle }
							onChange={ ( tabTitle: string ) =>
								setAttributes( { tabTitle } )
							}
						/>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Line below', 'demas-theme' ) }
							help={ __(
								'Leave both empty for the drawing alone.',
								'demas-theme'
							) }
							value={ attributes.tabCopy }
							onChange={ ( tabCopy: string ) =>
								setAttributes( { tabCopy } )
							}
						/>
					</PanelBody>
				</InspectorControls>
				<div { ...useBlockProps() }>
					<ServerSideRender
						block={ metadata.name }
						attributes={ attributes }
					/>
				</div>
			</>
		);
	},
	// Rendered by render.php.
	save: () => null,
} );
