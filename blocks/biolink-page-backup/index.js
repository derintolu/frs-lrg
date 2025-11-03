import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('frs/biolink-page', {
    edit: Edit,
});
