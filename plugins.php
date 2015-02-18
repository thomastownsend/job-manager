<?php
//
// Google XML Sitemap
// http://wordpress.org/extend/plugins/google-sitemap-generator/
//

// Intercept the sitemap build and add all our URLs to it.
function jobman_gxs_buildmap() {
	global $wpdb;
	$options = get_option( 'jobman_options' );

	if( ! $options['plugins']['gxs'] )
		return;

	$generatorObject = &GoogleSitemapGenerator::GetInstance();
	if( NULL == $generatorObject )
		// GXS doesn't seem to be here. Abort.
		return;
	
	// Add each job if individual jobs are displayed
	if( 'summary' == $options['list_type'] ) {
		$jobs = get_posts( 'post_type=jobman_job' );

		if( count( $jobs ) > 0 ) {
			foreach( $jobs as $job ) {
				$generatorObject->AddUrl( get_page_link( $job->ID ), time(), "daily", 0.5 );
			}
		}
	}
	
	// Add the categories
	$categories = get_terms( 'jobman_category', 'hide_empty=0' );
	
	if( count( $categories ) > 0 ) {
		foreach( $categories as $cat ) {
			$generatorObject->AddUrl( get_term_link( $cat->slug, 'jobman_category' ), time(), "daily", 0.5 );
		}
	}
}
?>