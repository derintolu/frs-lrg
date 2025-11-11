import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit.js';
import metadata from './block.json';

registerBlockType(metadata.name, {
	...metadata,
	edit: Edit,
	save: () => null // Dynamic block, rendered in PHP
});
