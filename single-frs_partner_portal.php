<?php
/**
 * Single Template for Partner Company Portals
 *
 * Displays partner company portal pages with custom branding.
 *
 * Each partner company (e.g., "Keller Williams Downtown") gets one portal that is shared by:
 * - All realtors from that partner company
 * - All loan officers assigned to that partner company
 * - Tied to a BuddyPress group containing all members
 *
 * @package LendingResourceHub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while (have_posts()) : the_post();
    // Render block content (partner portal will use custom blocks)
    the_content();
endwhile;

get_footer();
