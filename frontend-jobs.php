<?php // encoding: UTF-8
function jobman_display_jobs_list( $cat ) {
	global $jobman_shortcode_jobs, $jobman_shortcode_all_jobs, $jobman_shortcode_category, $jobman_shortcodes, $jobman_field_shortcodes, $wp_query;
	$options = get_option( 'jobman_options' );

	$content = '';

	$page = get_post( $options['main_page'] );

	if( 'all' != $cat ) {
		$page->ID = -1;
		$page->post_type = 'jobman_joblist';
		$page->post_title = __( 'Jobs Listing', 'jobman' );
	}

	if( 'all' != $cat ) {
		$jobman_shortcode_category = $category = get_term( $cat, 'jobman_category' );
		if( NULL == $category ) {
			$cat = 'all';
		}
		else {
			$page->post_title = $category->name;
			$page->post_parent = $options['main_page'];
			$page->post_name = $category->slug;
		}
	}

	$args = array(
				'post_type' => 'jobman_job',
				'suppress_filters' => false
			);

	if( ! empty( $options['sort_by'] ) ) {
		switch( $options['sort_by'] ) {
			case 'title':
				$args['orderby'] = 'title';
				break;
			case 'dateposted':
				$args['orderby'] = 'date';
				break;
			case 'closingdate':
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'displayenddate';
				break;
			default:
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = $options['sort_by'];
				break;
		}
	}

	if( $options['jobs_per_page'] > 0 ) {
		$args['numberposts'] = $options['jobs_per_page'];
		$args['posts_per_page'] = $options['jobs_per_page'];

		if( array_key_exists( 'page', $wp_query->query_vars ) && $wp_query->query_vars['page'] > 1 )
			$args['offset'] = ( $wp_query->query_vars['page'] - 1 ) * $options['jobs_per_page'];
	}
	else {
		$args['numberposts'] = -1;
	}

	if( in_array( $options['sort_order'], array( 'asc', 'desc' ) ) )
		$args['order'] = $options['sort_order'];
	else
		$args['order'] = 'asc';

	if( 'all' != $cat )
		$args['jcat'] = $category->slug;

	add_filter( 'posts_where', 'jobman_job_live_where' );
	add_filter( 'posts_join', 'jobman_job_live_join' );
	add_filter( 'posts_distinct', 'jobman_job_live_distinct' );

	$jobs = get_posts( $args );

	$args['posts_per_page'] = '';
	$args['offset'] = '';
	$args['numberposts'] = -1;
	$jobman_shortcode_all_jobs = get_posts( $args );

	remove_filter( 'posts_where', 'jobman_job_live_where' );
	remove_filter( 'posts_join', 'jobman_job_live_join' );
	remove_filter( 'posts_distinct', 'jobman_job_live_distinct' );

	if( $options['user_registration'] ) {
		if( 'all' == $cat && $options['loginform_main'] )
			$content .= jobman_display_login();
		else if( 'all' != $cat && $options['loginform_category'] )
			$content .= jobman_display_login();
	}

	$related_cats = array();
	foreach( $jobs as $id => $job ) {
		// Get related categories
		if( $options['related_categories'] ) {
			$categories = wp_get_object_terms( $job->ID, 'jobman_category' );
			if( count( $categories ) > 0 ) {
				foreach( $categories as $cat ) {
					$related_cats[] = $cat->slug;
				}
			}
		}
	}
	$related_cats = array_unique( $related_cats );

	if( $options['related_categories'] && count( $related_cats ) > 0 ) {
		$links = array();
		foreach( $related_cats as $rc ) {
			$cat = get_term_by( 'slug', $rc, 'jobman_category' );
			$links[] = '<a href="'. get_term_link( $cat->slug, 'jobman_category' ) . '" title="' . sprintf( __( 'Jobs for %s', 'jobman' ), $cat->name ) . '">' . $cat->name . '</a>';
		}

		$content .= '<h3>' . __( 'Related Categories', 'jobman' ) . '</h3>';
		$content .= implode(', ', $links) . '<br>';
	}

	$applyform = false;
	$data = get_posts( 'post_type=jobman_app_form&numberposts=-1' );
	if( count( $data ) > 0 ) {
		$applyform = true;
		$applypage = $data[0];

		$url = get_page_link( $applypage->ID );

		$content .= "<form action='$url' method='post'>";
	}

	if( count( $jobs ) > 0 ) {
		if( 'sticky' == $options['highlighted_behaviour'] )
			// Sort the sticky jobs to the top
			uasort( $jobs, 'jobman_sort_highlighted_jobs' );

		$template = $options['templates']['job_list'];

		jobman_add_shortcodes( $jobman_shortcodes );
		jobman_add_field_shortcodes( $jobman_field_shortcodes );

		$jobman_shortcode_jobs = $jobs;
		$content .= do_shortcode( $template );

		jobman_remove_shortcodes( array_merge( $jobman_shortcodes, $jobman_field_shortcodes ) );

	}
	else {
		$data = get_posts( 'post_type=jobman_app_form&numberposts=-1' );

		if( count( $data > 0 ) )
			$applypage = $data[0];

		$content .= '<p>';
		if( 'all' == $cat ||  ! isset( $category->term_id ) ) {
			$content .= sprintf( __( "We currently don't have any jobs available. Please check back regularly, as we frequently post new jobs. In the meantime, you can also <a href='%s'>send through your résumé</a>, which we'll keep on file.", 'jobman' ), get_page_link( $applypage->ID ) );
		}
		else {
			$url = get_page_link( $applypage->ID );
			$structure = get_option( 'permalink_structure' );
			if( '' == $structure ) {
				$url .= '&amp;c=' . $category->term_id;
			}
			else {
				if( substr( $url, -1 ) == '/' )
					$url .= $category->slug . '/';
				else
					$url .= '/' . $category->slug;
			}
			$content .= sprintf( __( "We currently don't have any jobs available in this area. Please check back regularly, as we frequently post new jobs. In the mean time, you can also <a href='%s'>send through your résumé</a>, which we'll keep on file, and you can check out the <a href='%s'>jobs we have available in other areas</a>.", 'jobman' ), $url, get_page_link( $options['main_page'] ) );
		}
	}
	$content .= '</p>';

	if( $applyform )
		$content .= '</form>';

	$page->post_content = $content;

	return array( $page );
}

function jobman_sort_highlighted_jobs( $a, $b ) {
	$ahighlighted = get_post_meta( $a->ID, 'highlighted', true );
	$bhighlighted = get_post_meta( $b->ID, 'highlighted', true );

	if( $ahighlighted == $bhighlighted )
		return 0;

	if( 1 == $ahighlighted )
		return -1;

	return 1;
}

function jobman_display_job( $job ) {
	global $jobman_shortcode_job, $jobman_shortcodes, $jobman_field_shortcodes;
	$options = get_option( 'jobman_options' );

	$content = '';

	if( is_string( $job ) || is_int( $job ) )
		$job = get_post( $job );

	if( $options['user_registration'] && $options['loginform_job'] )
		$content .= jobman_display_login();

	if( NULL != $job ) {
		$jobmeta = get_post_custom( $job->ID );
		$jobdata = array();
		foreach( $jobmeta as $key => $value ) {
			if( is_array( $value ) )
				$jobdata[$key] = $value[0];
			else
				$jobdata[$key] = $value;
		}
	}

	// Check that the job hasn't expired
	if( array_key_exists( 'displayenddate', $jobdata ) && '' != $jobdata['displayenddate'] && strtotime($jobdata['displayenddate']) <= time() )
		$job = NULL;

	// Check that the job isn't in the future
	if( strtotime( $job->post_date ) > time() )
		$job = NULL;

	if( NULL == $job ) {
		$page = get_post( $options['main_page'] );
		$page->post_type = 'jobman_job';
		$page->post_title = __( 'This job doesn\'t exist', 'jobman' );

		$content .= '<p>' . sprintf( __( 'Perhaps you followed an out-of-date link? Please check out the <a href="%s">jobs we have currently available</a>.', 'jobman' ), get_page_link( $options['main_page'] ) ) . '</p>';

		$page->post_content = $content;

		return array( $page );
	}

	$template = $options['templates']['job'];

	jobman_add_shortcodes( $jobman_shortcodes );
	jobman_add_field_shortcodes( $jobman_field_shortcodes );

	$jobman_shortcode_job = $job;
	$content .= do_shortcode( $template );

	jobman_remove_shortcodes( array_merge( $jobman_shortcodes, $jobman_field_shortcodes ) );

	$page = $job;

	$page->post_title = $options['text']['job_title_prefix'] . $job->post_title;

	$page->post_content = $content;

	return array( $page );
}
?>