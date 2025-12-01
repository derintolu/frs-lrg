/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<Placeholder
				icon="grid-view"
				label={ __( 'Bento Grid', 'lending-resource-hub' ) }
			>
				<p>
					{ __( 'Portal welcome dashboard with stats and widgets.', 'lending-resource-hub' ) }
				</p>
			</Placeholder>
		</div>
	);
}
