import { registerBlockType } from '@wordpress/blocks';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	TextareaControl,
	TextControl,
} from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { __ } from '@wordpress/i18n';

import metadata from './block.json';

type FilmKey = 'av1' | 'mp4' | 'poster' | 'still';
type Film = Partial< Record< FilmKey, number > >;

type Attributes = {
	tabTitle: string;
	tabCopy: string;
	film: Film;
	filmLabel: string;
};

type EditProps = {
	attributes: Attributes;
	setAttributes: ( next: Partial< Attributes > ) => void;
};

// The four files tools/hero-film.sh writes, in the order they are chosen.
const FILES: Array< {
	key: FilmKey;
	label: string;
	help: string;
	types: string[];
} > = [
	{
		key: 'av1',
		label: __( 'Film, AV1', 'demas-theme' ),
		help: 'demas-hero-film-av1.webm',
		types: [ 'video' ],
	},
	{
		key: 'mp4',
		label: __( 'Film, MP4', 'demas-theme' ),
		help: 'demas-hero-film-h264.mp4',
		types: [ 'video' ],
	},
	{
		key: 'poster',
		label: __( 'First frame', 'demas-theme' ),
		help: 'demas-hero-film-start.webp',
		types: [ 'image' ],
	},
	{
		key: 'still',
		label: __( 'Last frame', 'demas-theme' ),
		help: 'demas-hero-film-end.webp',
		types: [ 'image' ],
	},
];

function FilePicker( {
	file,
	id,
	onChange,
}: {
	file: ( typeof FILES )[ number ];
	id: number;
	onChange: ( id: number ) => void;
} ) {
	const name = useSelect(
		( select ) => {
			if ( ! id ) {
				return '';
			}
			const media = (
				select( coreStore ) as unknown as {
					getMedia: (
						mediaId: number
					) => { source_url?: string } | undefined;
				}
			 ).getMedia( id );
			return media?.source_url?.split( '/' ).pop() ?? '';
		},
		[ id ]
	);

	return (
		<div className="dh-schem-film-file">
			<p>
				<strong>{ file.label }</strong>
				<br />
				<span>{ id ? name || '…' : file.help }</span>
			</p>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ file.types }
					value={ id || undefined }
					onSelect={ ( picked: { id: number } ) =>
						onChange( picked.id )
					}
					render={ ( { open }: { open: () => void } ) => (
						<Button variant="secondary" size="compact" onClick={ open }>
							{ id
								? __( 'Replace', 'demas-theme' )
								: __( 'Choose', 'demas-theme' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			{ !! id && (
				<Button
					variant="link"
					isDestructive
					onClick={ () => onChange( 0 ) }
				>
					{ __( 'Remove', 'demas-theme' ) }
				</Button>
			) }
		</div>
	);
}

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes }: EditProps ) {
		const film = attributes.film ?? {};

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
					<PanelBody
						title={ __( 'Film', 'demas-theme' ) }
						initialOpen={ ! film.mp4 && ! film.av1 }
					>
						<p>
							{ __(
								'With a film chosen, the card plays it once in place of the drawing. Choose the four files tools/hero-film.sh makes. Without a film file, the card draws the schematic.',
								'demas-theme'
							) }
						</p>
						{ FILES.map( ( file ) => (
							<FilePicker
								key={ file.key }
								file={ file }
								id={ film[ file.key ] ?? 0 }
								onChange={ ( id ) =>
									setAttributes( {
										film: { ...film, [ file.key ]: id },
									} )
								}
							/>
						) ) }
						<TextareaControl
							__nextHasNoMarginBottom
							label={ __( 'Description for screen readers', 'demas-theme' ) }
							help={ __(
								'Read out in place of the film. Leave empty for the standard description.',
								'demas-theme'
							) }
							value={ attributes.filmLabel }
							onChange={ ( filmLabel: string ) =>
								setAttributes( { filmLabel } )
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
