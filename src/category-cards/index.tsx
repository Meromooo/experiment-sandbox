import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	TextControl,
	TextareaControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

type Card = {
	category: string;
	name?: string;
	description?: string;
	icon?: string;
	wide?: boolean;
};

type EditProps = {
	attributes: { cards: Card[] };
	setAttributes: ( next: { cards: Card[] } ) => void;
};

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes }: EditProps ) {
		const { cards } = attributes;

		const update = ( index: number, change: Partial< Card > ) =>
			setAttributes( {
				cards: cards.map( ( card, i ) =>
					i === index ? { ...card, ...change } : card
				),
			} );

		return (
			<>
				<InspectorControls>
					{ cards.map( ( card, index ) => (
						<PanelBody
							key={ card.category }
							title={ card.name || card.category }
							initialOpen={ index === 0 }
						>
							<TextControl
								__nextHasNoMarginBottom
								__next40pxDefaultSize
								label={ __( 'Name', 'demas-theme' ) }
								help={ __(
									'Leave empty to use the category’s own name.',
									'demas-theme'
								) }
								value={ card.name ?? '' }
								onChange={ ( name: string ) =>
									update( index, { name } )
								}
							/>
							<TextareaControl
								__nextHasNoMarginBottom
								label={ __( 'Description', 'demas-theme' ) }
								value={ card.description ?? '' }
								onChange={ ( description: string ) =>
									update( index, { description } )
								}
							/>
						</PanelBody>
					) ) }
				</InspectorControls>
				<div { ...useBlockProps() }>
					{ /* The cards as the site renders them, counts included,
					   made inert so a click selects the block instead of
					   following a link. */ }
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
