<?php //encoding: utf-8

// Hook for initial setup
register_activation_hook( JOBMAN_FOLDER . '/job-manager.php', 'jobman_activate' );

// Huh. Upgrades.
add_filter( 'upgrader_post_install', 'jobman_activate' );

// New blog!
add_action( 'wpmu_new_blog', 'jobman_new_blog', 10, 6);

// Translation hook
add_action( 'init', 'jobman_load_translation_file' );

//
// Display Hooks
//
// URL magic
add_filter( 'query_vars', 'jobman_queryvars' );
add_action( 'generate_rewrite_rules', 'jobman_add_rewrite_rules' );
add_action( 'init', 'jobman_flush_rewrite_rules' );
add_filter( 'the_posts', 'jobman_display_jobs', 10 ) ;
// Add our init stuff
add_action( 'init', 'jobman_display_init' );
// Set the template we want to use
add_action( 'template_redirect', 'jobman_display_template' );
// Add our own <head> information
add_action( 'wp_head', 'jobman_display_head' );

// For the slugs (treat jobman pages as WP pages)
add_filter( 'hierarchical_post_types', 'jobman_page_hierarchical_setup' );
// For the page links
add_filter( 'post_link', 'jobman_page_link', 10, 2 );
add_filter( 'post_type_link', 'jobman_page_link', 10, 2 );
add_filter( 'the_permalink_rss', 'jobman_rss_page_link', 10 );

// Our custom page/taxonomy setup
add_action( 'init', 'jobman_page_taxonomy_setup' );

// RSS Feeds
add_action( 'do_feed_jobman', 'jobman_rss_feed', 1, 1 );


//
// Widgets
//
add_action( 'widgets_init', create_function( '', 'return register_widget("JobmanLatestJobsWidget");' ) );
add_action( 'widgets_init', create_function( '', 'return register_widget("JobmanCategoriesWidget");' ) );
add_action( 'widgets_init', create_function( '', 'return register_widget("JobmanHighlightedJobsWidget");' ) );
add_action( 'widgets_init', create_function( '', 'return register_widget("JobmanJobsWidget");' ) );

//
// Admin Hooks
//
// Admin menu
add_action( 'admin_menu', 'jobman_admin_setup' );
// Plugin settings links
add_filter( 'plugin_row_meta', 'jobman_plugin_row_meta', 10, 2 );
// For the application rating AJAX call
add_action( 'wp_ajax_jobman_rate_application', 'jobman_rate_application' );
// For the interview rating AJAX call
add_action( 'wp_ajax_jobman_rate_interview', 'jobman_rate_interview' );

//
// Plugins
//
// Google XML Sitemap
add_action( 'sm_buildmap', 'jobman_gxs_buildmap' );

// Fare thee well, blog
add_action( 'delete_blog', 'jobman_delete_blog', 10, 2 );

// Uninstall function
if( function_exists( 'register_uninstall_hook' ) )
	register_uninstall_hook( WP_PLUGIN_DIR . '/' . JOBMAN_FOLDER . '/job-manager.php', 'jobman_uninstall' );

?>