import { registerBlockType } from '@wordpress/blocks';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	useBlockProps,
} from '@wordpress/block-editor';
import { Button, Disabled, PanelBody } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { __, sprintf } from '@wordpress/i18n';

import metadata from './block.json';
import './style.scss';

type Photos = Record< string, number >;

type Attributes = {
	photos: Photos;
};

type EditProps = {
	attributes: Attributes;
	setAttributes: ( next: Partial< Attributes > ) => void;
};

type Branch = {
	code: string;
	city: string;
};

type Media = {
	alt_text?: string;
	media_details?: { sizes?: Record< string, { source_url: string } > };
	source_url?: string;
};

declare global {
	interface Window {
		// The branch list (inc/branches.php), printed by inc/contact.php.
		demasThemeBranches?: Branch[];
	}
}

const rowStyle = {
	display: 'flex',
	flexWrap: 'wrap' as const,
	alignItems: 'center',
	gap: 8,
	padding: '8px 0',
	borderTop: '1px solid #e0e0e0',
};

/** One branch's photo: a thumbnail, and buttons to choose or remove it. */
function PhotoRow( {
	branch,
	id,
	onChange,
}: {
	branch: Branch;
	id: number;
	onChange: ( next: number ) => void;
} ) {
	const media = useSelect(
		( select ) =>
			id
				? ( select( coreStore ).getMedia( id, {
						context: 'view',
				  } ) as Media | undefined )
				: undefined,
		[ id ]
	);
	const thumb =
		media?.media_details?.sizes?.thumbnail?.source_url ?? media?.source_url;

	return (
		<div style={ rowStyle }>
			<p style={ { margin: 0, flex: '1 0 100%', fontWeight: 500 } }>
				{ branch.city }
			</p>
			{ thumb ? (
				<img
					src={ thumb }
					alt={ media?.alt_text ?? '' }
					style={ { width: 48, height: 48, objectFit: 'cover' } }
				/>
			) : (
				<p style={ { margin: 0, opacity: 0.7 } }>
					{ __( 'Placeholder shown', 'demas-theme' ) }
				</p>
			) }
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ id || undefined }
					onSelect={ ( picked: { id: number } ) =>
						onChange( picked.id )
					}
					render={ ( { open }: { open: () => void } ) => (
						<Button variant="secondary" size="compact" onClick={ open }>
							{ id
								? __( 'Replace', 'demas-theme' )
								: __( 'Choose photo', 'demas-theme' ) }
						</Button>
					) }
				/>
			</MediaUploadCheck>
			{ !! id && (
				<Button
					variant="link"
					isDestructive
					onClick={ () => onChange( 0 ) }
					aria-label={ sprintf(
						/* translators: %s: city */
						__( 'Remove the %s photo', 'demas-theme' ),
						branch.city
					) }
				>
					{ __( 'Remove', 'demas-theme' ) }
				</Button>
			) }
		</div>
	);
}

registerBlockType( metadata.name, {
	edit: function Edit( { attributes, setAttributes }: EditProps ) {
		const branches = window.demasThemeBranches ?? [];
		const photos = attributes.photos ?? {};

		const setPhoto = ( code: string, id: number ) => {
			const next: Photos = { ...photos };
			if ( id ) {
				next[ code ] = id;
			} else {
				delete next[ code ];
			}
			setAttributes( { photos: next } );
		};

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Branch photos', 'demas-theme' ) }>
						<p>
							{ __(
								'One photo per branch, shown on its card. Cities, people, addresses and hours come from the theme’s branch list.',
								'demas-theme'
							) }
						</p>
						{ branches.map( ( branch ) => (
							<PhotoRow
								key={ branch.code }
								branch={ branch }
								id={ photos[ branch.code ] ?? 0 }
								onChange={ ( id ) => setPhoto( branch.code, id ) }
							/>
						) ) }
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
