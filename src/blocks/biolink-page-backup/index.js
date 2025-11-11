import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit.js';

registerBlockType('frs/biolink-page', {
    edit: Edit,
});
