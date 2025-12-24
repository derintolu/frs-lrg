<?php
/**
 * Single Template for Mortgage Landing Pages
 * Uses WordPress Interactivity API for dynamic interactions
 *
 * @package LendingResourceHub
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while (have_posts()) : the_post();
    // Render block content
    the_content();
endwhile;

get_footer();
