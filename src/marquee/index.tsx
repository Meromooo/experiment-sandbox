import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

type Attributes = {
	label: string;
	branches: boolean;
	items: string[];
	reverse: boolean;
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
					<PanelBody title={ __( 'Marquee', 'demas-theme' ) }>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'What it lists', 'demas-theme' ) }
							help={ __(
								'Read to screen-reader users in place of the moving line, e.g. "Branches across Saudi Arabia".',
								'demas-theme'
							) }
							value={ attributes.label }
							onChange={ ( label: string ) =>
								setAttributes( { label } )
							}
						/>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'List the branch cities', 'demas-theme' ) }
							help={ __(
								'From the theme’s branch list, in its order.',
								'demas-theme'
							) }
							checked={ attributes.branches }
							onChange={ ( branches: boolean ) =>
								setAttributes( { branches } )
							}
						/>
						{ ! attributes.branches && (
							<TextareaControl
								__nextHasNoMarginBottom
								label={ __( 'Words, one per line', 'demas-theme' ) }
								value={ attributes.items.join( '\n' ) }
								onChange={ ( text: string ) =>
									setAttributes( { items: text.split( '\n' ) } )
								}
							/>
						) }
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Scroll the other way', 'demas-theme' ) }
							checked={ attributes.reverse }
							onChange={ ( reverse: boolean ) =>
								setAttributes( { reverse } )
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
