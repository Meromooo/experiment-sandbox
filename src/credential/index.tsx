import { registerBlockType } from '@wordpress/blocks';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
	useBlockProps,
} from '@wordpress/block-editor';
import { Button, PanelBody, TextControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

type Attributes = {
	name: string;
	logo: number;
	detail: string;
	issuer: string;
	link: string;
};

type EditProps = {
	attributes: Attributes;
	setAttributes: ( next: Partial< Attributes > ) => void;
};

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes }: EditProps ) {
		const blockProps = useBlockProps( { className: 'dh-cred' } );
		const logoUrl = useSelect(
			( select ) => {
				if ( ! attributes.logo ) {
					return '';
				}
				const media = (
					select( coreStore ) as unknown as {
						getMedia: (
							mediaId: number
						) => { source_url?: string } | undefined;
					}
				 ).getMedia( attributes.logo );
				return media?.source_url ?? '';
			},
			[ attributes.logo ]
		);

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Logo', 'demas-theme' ) }>
						<p>
							{ __(
								'A file made by tools/credential-logos.py, so it sits at the same size as the others. Without a logo the plate shows the name.',
								'demas-theme'
							) }
						</p>
						<MediaUploadCheck>
							<MediaUpload
								allowedTypes={ [ 'image' ] }
								value={ attributes.logo || undefined }
								onSelect={ ( picked: { id: number } ) =>
									setAttributes( { logo: picked.id } )
								}
								render={ ( { open }: { open: () => void } ) => (
									<Button
										variant="secondary"
										size="compact"
										onClick={ open }
									>
										{ attributes.logo
											? __( 'Replace logo', 'demas-theme' )
											: __( 'Choose logo', 'demas-theme' ) }
									</Button>
								) }
							/>
						</MediaUploadCheck>
						{ !! attributes.logo && (
							<Button
								variant="link"
								isDestructive
								onClick={ () => setAttributes( { logo: 0 } ) }
							>
								{ __( 'Remove logo', 'demas-theme' ) }
							</Button>
						) }
					</PanelBody>
					<PanelBody title={ __( 'Hang tag', 'demas-theme' ) }>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'What it is', 'demas-theme' ) }
							help={ __(
								'For example "Quality management".',
								'demas-theme'
							) }
							value={ attributes.detail }
							onChange={ ( detail: string ) =>
								setAttributes( { detail } )
							}
						/>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							label={ __( 'Who issued it', 'demas-theme' ) }
							help={ __(
								'For example "Certified by SOCOTEC".',
								'demas-theme'
							) }
							value={ attributes.issuer }
							onChange={ ( issuer: string ) =>
								setAttributes( { issuer } )
							}
						/>
						<TextControl
							__nextHasNoMarginBottom
							__next40pxDefaultSize
							type="url"
							label={ __( 'Certificate link', 'demas-theme' ) }
							help={ __(
								'The certificate itself, a PDF or a page. The plate links to it and the tag says "View certificate". Leave empty when there is none.',
								'demas-theme'
							) }
							value={ attributes.link }
							onChange={ ( link: string ) =>
								setAttributes( { link } )
							}
						/>
					</PanelBody>
				</InspectorControls>
				<li { ...blockProps }>
					<div className="dh-cred__plate">
						{ logoUrl ? (
							<span className="dh-cred__mark">
								<img
									className="dh-cred__logo"
									src={ logoUrl }
									alt=""
								/>
							</span>
						) : null }
						<RichText
							tagName="span"
							className={
								logoUrl ? 'dh-cred__caption' : 'dh-cred__name'
							}
							value={ attributes.name }
							onChange={ ( name: string ) =>
								setAttributes( { name } )
							}
							allowedFormats={ [] }
							placeholder={ __( 'Certificate', 'demas-theme' ) }
						/>
					</div>
				</li>
			</>
		);
	},
	// Rendered by render.php.
	save: () => null,
} );
