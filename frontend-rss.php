<?php
function jobman_rss_feed( $forcomments ) {
	global $post;
	$dir = dirname( $_SERVER['SCRIPT_FILENAME'] );
	
	add_filter( 'posts_where', 'jobman_job_live_where' );
	add_filter( 'posts_join', 'jobman_job_live_join' );
	
	$posts = get_option( 'posts_per_rss' );
	query_posts( "post_type=jobman_job&posts_per_page=$posts&numberposts=$posts" );
	
	remove_filter( 'posts_where', 'jobman_job_live_where' );
	remove_filter( 'posts_join', 'jobman_job_live_join' );
	
	add_filter( 'the_content', 'jobman_rss_content', 10, 1 );
	add_filter( 'get_the_excerpt', 'jobman_rss_content', 10, 1 );
	
	require_once( "$dir/wp-includes/feed-rss2.php" );
	
	remove_filter( 'the_content', 'jobman_rss_content', 10, 1 );
	remove_filter( 'get_the_excerpt', 'jobman_rss_content', 10, 1 );
	
	exit;
}

function jobman_rss_page_link( $link ) {
	global $post;
	if( NULL == $post || 'jobman_job' != $post->post_type )
		return $link;
		
	return get_page_link( $post->ID );
}

function jobman_rss_content( $content ) {
	global $post;
	
	$data = jobman_display_job( $post );
	
	return $data[0]->post_content;
}

?>