import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';

registerBlockType('lrh/biolink-page', {
    edit: Edit,
});
