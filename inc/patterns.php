<?php
/**
 * Block pattern categories.
 *
 * Patterns themselves live in patterns/ and are registered by WordPress from
 * their file headers. This only declares the category they file under, so
 * they group together in the inserter.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	register_block_pattern_category(
		'demas',
		array(
			'label'       => __( 'Demas', 'demas-theme' ),
			'description' => __( 'Homepage and site sections for the Demas theme.', 'demas-theme' ),
		)
	);
} );
