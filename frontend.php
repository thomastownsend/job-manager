<?php //encoding: utf-8

// Job lists and individual jobs
require_once( JOBMAN_DIR . '/frontend-jobs.php' );
// Application form, application filtering and storage
require_once( JOBMAN_DIR . '/frontend-application.php' );
// User registration and login
require_once( JOBMAN_DIR . '/frontend-user.php' );
// RSS Feeds
require_once( JOBMAN_DIR . '/frontend-rss.php' );
// Shortcode magic
require_once( JOBMAN_DIR . '/frontend-shortcodes.php' );

global $jobman_displaying, $jobman_finishedpage, $jobman_geoloc;
$jobman_finishedpage = $jobman_displaying = $jobman_geoloc = false;

function jobman_queryvars( $qvars ) {
	$qvars[] = 'j';
	$qvars[] = 'c';
	$qvars[] = 'jobman_root_id';
	$qvars[] = 'jobman_page';
	$qvars[] = 'jobman_data';
	$qvars[] = 'jobman_username';
	$qvars[] = 'jobman_password';
	$qvars[] = 'jobman_password2';
	$qvars[] = 'jobman_email';
	$qvars[] = 'jobman_register';
	return $qvars;
}

function jobman_add_rewrite_rules( $wp_rewrite ) {
	$options = get_option( 'jobman_options' );

	if( ! empty( $wp_rewrite->rules ) && is_array( $wp_rewrite->rules ) && is_array( $options ) && array_key_exists( 'rewrite_rules', $options ) && is_array($options['rewrite_rules'] ) )
		$wp_rewrite->rules = array_merge( $options['rewrite_rules'], $wp_rewrite->rules );
}

function jobman_flush_rewrite_rules() {
	global $wp_rewrite;

	$wp_rewrite->feeds[] = 'jobman';

	$options = get_option( 'jobman_options' );

	$root = get_page( $options['main_page'] );
	$url = get_page_uri( $root->ID );

	if( ! $url )
		return;

	$lang = '';
	// Hack to support WPML languages in the nice URL
	if( defined( 'ICL_SITEPRESS_VERSION' ) && function_exists( 'icl_get_languages' ) ) {
		$languages = icl_get_languages('skip_missing=0');
		if( ! empty( $languages ) ) {
			$lang_arr = array();
			foreach( $languages as $language ) {
				$lang_arr[] = $language['language_code'];
			}
			$lang = '(' . implode( '|', $lang_arr ) . ')/';
		}
	}

	if( empty( $lang ) ) {
		$new_rules = array(
							"$url/?([page]*/(\d+)/?)?$" => "index.php?jobman_root_id=$root->ID" .
							'&page=$matches[2]',
							"$url/apply(/([^/]+))?/?$" => "index.php?jobman_root_id=$root->ID" .
							'&jobman_page=apply&jobman_data=$matches[2]',
							"$url/register(/([^/]+))?/?$" => "index.php?jobman_root_id=$root->ID" .
							'&jobman_page=register&jobman_data=$matches[2]',
							"$url/feed/?$" => "index.php?feed=jobman",
							"$url/([^/]+)/?(page/(\d+)/?)?$" => 'index.php?jobman_data=$matches[1]'.
							'&page=$matches[3]',
					);
	}
	else {
		$new_rules = array(
							"($lang)?$url/?([page]*/(\d+)/?)?$" => "index.php?jobman_root_id=$root->ID" .
							'&lang=$matches[1]' .
							'&page=$matches[3]',
							"($lang)?$url/apply(/([^/]+))?/?$" => "index.php?jobman_root_id=$root->ID" .
							'&lang=$matches[1]' .
							'&jobman_page=apply&jobman_data=$matches[3]',
							"($lang)?$url/register(/([^/]+))?/?$" => "index.php?jobman_root_id=$root->ID" .
							'&lang=$matches[1]' .
							'&jobman_page=register&jobman_data=$matches[3]',
							"($lang)?$url/feed/?$" => 'index.php?feed=jobman&lang=$matches[1]',
							"($lang)?$url/([^/]+)/?(page/(\d+)/?)?$" => 'index.php?jobman_data=$matches[1]' .
							'&lang=$matches[2]' .
							'&page=$matches[4]',
					);
	}

	if( array_key_exists( 'rewrite_rules', $options ) && $options['rewrite_rules'] == $new_rules )
		return;

	$options['rewrite_rules'] = $new_rules;
	update_option( 'jobman_options', $options );

	$wp_rewrite->flush_rules( false );
}

function jobman_page_link( $link, $page = NULL ) {
	global $post;
	if( NULL == $page && NULL == $post )
		return $link;

	if( NULL == $page )
		$page = $post;

	if( is_int( $page ) )
		$page = get_post( $page );

	if( ! in_array( $page->post_type, array( 'jobman_job', 'jobman_app_form' ) ) )
		return $link;

	return get_page_link( $page->ID );
}

function jobman_display_jobs( $posts ) {
	global $wp_query, $wpdb, $jobman_displaying, $jobman_finishedpage, $sitepress, $wp_rewrite;

	if( $jobman_finishedpage || $jobman_displaying )
		return $posts;

	// Hack to fix Mystique theme CSS
	if( array_key_exists( 'mystique', $wp_query->query_vars ) && 'css' == $wp_query->query_vars['mystique'] )
		return $posts;

	$options = get_option( 'jobman_options' );

	$post = NULL;

	$displaycat = false;

	if( array_key_exists( 'jobman_data', $wp_query->query_vars ) && ! array_key_exists( 'jobman_page', $wp_query->query_vars ) ) {
		if( term_exists( $wp_query->query_vars['jobman_data'], 'jobman_category' ) ) {
			$wp_query->query_vars['jcat'] = $wp_query->query_vars['jobman_data'];
		}
		else {
			$sql = "SELECT * FROM $wpdb->posts WHERE post_type='jobman_job' AND post_name=%s;";
			$sql = $wpdb->prepare( $sql, $wp_query->query_vars['jobman_data'] );
			$data = $wpdb->get_results( $sql, OBJECT );
			if( count( $data ) > 0 )
				$wp_query->query_vars['page_id'] = $data[0]->ID;
			else
				return $posts;
		}
	}

	if( ! array_key_exists( 'jcat', $wp_query->query_vars ) ) {
		if( isset( $wp_query->query_vars['jobman_root_id'] ) )
			$post = get_post( $wp_query->query_vars['jobman_root_id'] );
		else if( isset( $wp_query->query_vars['page_id'] ) )
			$post = get_post( $wp_query->query_vars['page_id'] );

		if( $post == NULL || ( ! isset( $wp_query->query_vars['jobman_page'] ) && $post->ID != $options['main_page'] && ! in_array( $post->post_type, array( 'jobman_job', 'jobman_app_form', 'jobman_register' ) ) ) )
			return $posts;
	}

	// We're going to be displaying a Job Manager page.
	$jobman_displaying = true;
	$wp_query->is_home = false;
	remove_filter( 'the_content', 'wpautop' );

	// Hack to kill WPML on Job Manager pages. Need to add proper support later.
	if( defined( 'ICL_SITEPRESS_VERSION' ) && ! empty( $sitepress ) ) {
		remove_filter( 'posts_join', array( $sitepress, 'posts_join_filter' ) );
		remove_filter( 'posts_where', array( $sitepress, 'posts_where_filter' ) );
	}

	if( NULL != $post ) {
		$postmeta = get_post_custom( $post->ID );
		$postcats = wp_get_object_terms( $post->ID, 'jobman_category' );

		$postdata = array();
		foreach( $postmeta as $key => $value ) {
			if( is_array( $value ) )
				$postdata[$key] = $value[0];
			else
				$postdata[$key] = $value;
		}
	}

	if( array_key_exists( 'jobman_register', $wp_query->query_vars ) )
		jobman_register();
	else if( array_key_exists( 'jobman_username', $wp_query->query_vars ) )
		jobman_login();

	global $jobman_data;
	$jobman_data = '';
	if( array_key_exists( 'jobman_data', $wp_query->query_vars ) )
		$jobman_data = $wp_query->query_vars['jobman_data'];
	else if( array_key_exists( 'j', $wp_query->query_vars ) )
		$jobman_data = $wp_query->query_vars['j'];
	else if( array_key_exists( 'c', $wp_query->query_vars ) )
		$jobman_data = $wp_query->query_vars['c'];

	if( array_key_exists( 'jcat', $wp_query->query_vars ) ) {
		// We're looking at a category
		$cat = get_term_by( 'slug', $wp_query->query_vars['jcat'], 'jobman_category' );

		$posts = jobman_display_jobs_list( $cat->term_id );

		if( count( $posts ) > 0 )
			$posts[0]->post_content = $options['text']['category_before'] . $posts[0]->post_content . $options['text']['category_after'];
	}
	else if( isset( $wp_query->query_vars['jobman_page'] ) || ( NULL != $post && in_array( $post->post_type, array( 'jobman_job', 'jobman_app_form', 'jobman_register' ) ) ) )
	{
		if( NULL == $post  || ! in_array( $post->post_type, array( 'jobman_job', 'jobman_app_form', 'jobman_register' ) ) ) {
			$sql = "SELECT * FROM $wpdb->posts WHERE (post_type='jobman_job' OR post_type='jobman_app_form' OR post_type='jobman_register') AND post_name=%s;";
			$sql = $wpdb->prepare( $sql, $wp_query->query_vars['jobman_page'] );
			$data = $wpdb->get_results( $sql, OBJECT );
		}
		else {
			$data = array( $post );
		}

		if( count( $data ) > 0 ) {
			$post = $data[0];
			$postmeta = get_post_custom( $post->ID );
			$postcats = wp_get_object_terms( $post->ID, 'jobman_category' );

			$postdata = array();
			foreach( $postmeta as $key => $value ) {
				if( is_array( $value ) )
					$postdata[$key] = $value[0];
				else
					$postdata[$key] = $value;
			}

			if( 'jobman_job' == $post->post_type ) {
				// We're looking at a job
				$posts = jobman_display_job( $post->ID );
				if( count( $posts ) > 0 )
					$posts[0]->post_content = $options['text']['job_before'] . $posts[0]->post_content . $options['text']['job_after'];
			}
			else if( 'jobman_app_form' == $post->post_type ) {
				// We're looking at an application form
				$jobid = (int) $jobman_data;
				if( '' == $jobman_data )
					$posts = jobman_display_apply( -1 );
				else if( $jobid > 0 )
					$posts = jobman_display_apply( $jobid );
				else
					$posts = jobman_display_apply( -1, $jobman_data );

				if( count( $posts ) > 0 )
					$posts[0]->post_content = $options['text']['apply_before'] . $posts[0]->post_content . $options['text']['apply_after'];
			}
			else if( 'jobman_register' == $post->post_type ) {
				// Looking for the registration form
				if( is_user_logged_in() ) {
					wp_redirect( get_page_link( $options['main_page'] ) );
					exit;
				}
				else {
					$posts = jobman_display_register();
					if( count( $posts ) > 0 )
						$posts[0]->post_content = $options['text']['registration_before'] . $posts[0]->post_content . $options['text']['registration_after'];
				}
			}
			else {
				$posts = array();
			}
		}
		else {
			$posts = array();
		}
	}
	else if( NULL != $post && $post->ID == $options['main_page'] ) {
		// We're looking at the main job list page
		$posts = jobman_display_jobs_list( 'all' );

		if( count( $posts ) > 0 )
			$posts[0]->post_content = $options['text']['main_before'] . $posts[0]->post_content . $options['text']['main_after'];
	}
	else {
		$posts = array();
	}

	if( ! empty( $posts ) ) {
		$wp_query->queried_object = $posts[0];
		$wp_query->queried_object_id = $posts[0]->ID;
		$wp_query->is_page = true;
		$wp_query->is_singular = true;
	}

	$hidepromo = $options['promo_link'];

	if( get_option( 'smb_consulting' ) )
		$hidepromo = true;

	if( ! $hidepromo && count( $posts ) > 0 )
		$posts[0]->post_content .= '<p class="jobmanpromo">' . sprintf( __( 'This job listing was created using <a href="%s" title="%s">Job Manager</a> for WordPress, by <a href="%s">Tom Townsend</a>.', 'jobman'), 'http://www.wp-jobmanager.com/', __( 'The ORIGINAL Job Manager plugin', 'jobman' ), 'https://profiles.wordpress.org/smb-dev/' ) . '</p>';

	$jobman_finishedpage = true;
	return $posts;
}

function jobman_display_init() {
	$options = get_option( 'jobman_options' );

	if( defined( 'WP_ADMIN' ) && WP_ADMIN )
		return;

	// Check for a geoloc field, so we can decide if we maybe need to include the geoloc JS
	$include_geoloc = false;
	if( ! empty( $options['fields'] ) ) {
		foreach( $options['fields'] as $field ) {
			if( 'geoloc' == $field['type'] ) {
				$include_geoloc = true;
				break;
			}
		}
	}

	wp_enqueue_script( 'jquery-ui-datepicker', JOBMAN_URL . '/js/jquery-ui-datepicker.js', array( 'jquery-ui-core' ), JOBMAN_VERSION );
	wp_enqueue_script( 'jobman-display', JOBMAN_URL . '/js/display.js', false, JOBMAN_VERSION );

	if( $include_geoloc ) {
		wp_enqueue_script( 'google-gears', JOBMAN_URL . '/js/gears_init.js', false, JOBMAN_VERSION );
		wp_enqueue_script( 'google-maps', "http://maps.google.com/maps/api/js?sensor=true", false );
	}

	wp_enqueue_style( 'jobman-display', JOBMAN_URL . '/css/display.css', false, JOBMAN_VERSION );
}

function jobman_display_template() {
	global $wp_query, $jobman_displaying;
	$options = get_option( 'jobman_options' );

	if( ! $jobman_displaying )
		return;

	$root = get_page( $options['main_page'] );
	$id = $root->ID;
	$template = get_post_meta( $id, '_wp_page_template', true );
	$pagename = get_query_var( 'pagename' );
	$category = get_query_var( 'jcat' );

	$post_id = get_query_var( 'page_id' );

	$job_cats = array();
	$post = NULL;
	if( ! empty( $post_id ) ) {
		$post = get_post( $post_id );
		if( ! empty( $post ) && 'jobman_job' == $post->post_type ) {
			$categories = wp_get_object_terms( $post->ID, 'jobman_category' );
			if( ! empty( $categories ) ) {
				foreach( $categories as $cat ) {
					$job_cats[] = $cat->slug;
				}
			}
		}
	}

	if( 'default' == $template )
		$template = '';

	$templates = array();
	if( ! empty( $template ) && ! validate_file( $template ) )
		$templates[] = $template;
	if( $category )
		$templates[] = "category-$category.php";
	if( ! empty( $job_cats ) ) {
		foreach( $job_cats as $jcat ) {
		if( ! empty( $post ) && 'jobman_job' == $post->post_type )
			$templates[] = "category-$jcat-job.php";
			$templates[] = "category-$jcat.php";
		}
	}
	if( $pagename )
		$templates[] = "page-$pagename.php";
	if( $id )
		$templates[] = "page-$id.php";

	if( ! empty( $post ) && 'jobman_job' == $post->post_type )
		$templates[] = 'job.php';

	$templates[] = "page.php";

	$template = apply_filters( 'page_template', locate_template( $templates ) );

	if( '' != $template ) {
		load_template( $template );
		// The exit tells WP to not try to load any more templates
		exit;
	}
}

function jobman_display_head() {
	global $jobman_displaying, $jobman_geoloc;

	if( ! $jobman_displaying )
		return;

	if( is_feed() )
		return;

	$options = get_option( 'jobman_options' );

	$url = get_page_link( $options['main_page'] );
	$structure = get_option( 'permalink_structure' );
	if( '' == $structure ) {
		$url = get_option( 'home' ) . '?feed=jobman';
	}
	else {
		if( '/' != substr( $url, -1 ) )
			$url .= '/';
		$url .= 'feed/';
	}

	$mandatory_ids = array();
	$mandatory_labels = array();
	foreach( $options['fields'] as $id => $field ) {
		if( $field['mandatory'] ) {
			$mandatory_ids[] = $id;
			if( !empty( $field['label'] ) )
				$mandatory_labels[] = $field['label'];
			else
				$mandatory_labels[] = $field['data'];
		}
	}
?>
	<link rel="alternate" type="application/rss+xml" href="<?php echo $url ?>" title="<?php _e( 'Latest Jobs', 'jobman' ) ?>" />
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function() {
	jQuery(".datepicker").datepicker({dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true, gotoCurrent: true});
	jQuery("#ui-datepicker-div").css('display', 'none');

	jQuery("#jobman-jobselect-echo").click(function( event ) {
		if( jQuery(this).hasClass("open") ) {
			jQuery(this).removeClass("open");
		}
		else {
			jQuery(".jobselect-popout").css( "left", jQuery("#jobman-jobselect").position().left + "px" );
			jQuery(".jobselect-popout").css( "top", ( jQuery("#jobman-jobselect").position().top + 20 ) + "px" );
			jQuery(this).addClass("open");
		}

		jQuery(".jobselect-popout").animate({ opacity: 'toggle', height: 'toggle' }, "fast");

		event.preventDefault();
	});

	jQuery("#jobman-catselect-echo").click(function( event ) {
		if( jQuery(this).hasClass("open") ) {
			jQuery(this).removeClass("open");
		}
		else {
			jQuery(".catselect-popout").css( "left", jQuery("#jobman-catselect").position().left + "px" );
			jQuery(".catselect-popout").css( "top", ( jQuery("#jobman-catselect").position().top + 20 ) + "px" );
			jQuery(this).addClass("open");
		}

		jQuery(".catselect-popout").animate({ opacity: 'toggle', height: 'toggle' }, "fast");

		event.preventDefault();
	});

	jQuery("#jobman-jobselect-close a").click( function() { jQuery("#jobman-jobselect-echo").click(); return false; } );

	jQuery("#jobman-catselect-close a").click( function() { jQuery("#jobman-catselect-echo").click(); return false; } );

	jQuery(".jobselect-popout input").click(function() {
		jobman_update_selected_jobs();
		return true;
	});

	jQuery(".catselect-popout input").click(function() {
		jobman_update_selected_cats();
		return true;
	});

	jobman_update_selected_jobs();
	jobman_update_selected_cats();

<?php if( $jobman_geoloc ) { ?>
	var geo;
	if( navigator.geolocation ) {
		// HTML5
		geo = navigator.geolocation;
		geo.getCurrentPosition( jobman_geo_success, jobman_geo_error );
	}
	else if( google.gears ) {
		// Google Gears

		geo = google.gears.factory.create('beta.geolocation');
		geo.getCurrentPosition( jobman_geo_success,
								jobman_geo_error,
								{ enableHighAccuracy: true,
                                     gearsRequestAddress: true } );
	}
<?php } ?>
});

var jobman_mandatory_ids = <?php echo json_encode( $mandatory_ids ) ?>;
var jobman_mandatory_labels = <?php echo json_encode( $mandatory_labels ) ?>;

var jobman_strings = new Array();
jobman_strings['apply_submit_mandatory_warning'] = "<?php _e( 'The following fields must be filled out before submitting', 'jobman' ) ?>";
jobman_strings['no_selected_jobs'] = "<?php _e( 'click to select', 'jobman' ) ?>";
jobman_strings['no_selected_cats'] = "<?php _e( 'click to select', 'jobman' ) ?>";

var jobman_selected_jobs_names;
function jobman_update_selected_jobs() {
	jobman_selected_jobs_names = new Array();

	jQuery(".jobselect-popout").find("input:checked").each( function() {
		jobman_selected_jobs_names.push( jQuery(this).attr( 'title' ) );
	});

	var jobs;
	if( jobman_selected_jobs_names.length ) {
		jobs = jobman_selected_jobs_names.join( ", " );
	}
	else {
		jobs = "&lt;" + jobman_strings['no_selected_jobs'] + "&gt;";
	}
	jQuery("#jobman-jobselect-echo").html( jobs );

}

var jobman_selected_cats_names;
function jobman_update_selected_cats() {
	jobman_selected_cats_names = new Array();

	jQuery(".catselect-popout").find("input:checked").each( function() {
		jobman_selected_cats_names.push( jQuery(this).attr( 'title' ) );
	});

	var cats;
	if( jobman_selected_cats_names.length ) {
		cats = jobman_selected_cats_names.join( ", " );
	}
	else {
		cats = "&lt;" + jobman_strings['no_selected_cats'] + "&gt;";
	}
	jQuery("#jobman-catselect-echo").html( cats );

}

<?php if( $jobman_geoloc ) { ?>
function jobman_geo_success( pos ) {
	var description = "";

	if( pos.address ) {
		description = pos.address.city + ", " + pos.address.region + ", " + pos.address.country;
	}
	else if( pos.gearsAddress ) {
		description = pos.gearsAddress.city + ", " + pos.gearsAddress.region + ", " + pos.gearsAddress.country;
	}
	else {
		var latlng = new google.maps.LatLng(40.730885,-73.997383);
		var myOptions = {
		  zoom: 8,
		  center: latlng,
		  mapTypeId: 'roadmap'
		}
		map = new google.maps.Map(document.getElementById("jobman-map"), myOptions);
		var geocoder = google.maps.Geocoder();
		var latLng = new google.maps.LatLng( pos.coords.latitude, pos.coords.longitude );

		if( geocoder ) {
			geocoder.geocode( { 'latLng': latLng },
				function( results, status ) {
					if( status == google.maps.GeocoderStatus.OK && results[1] ) {
						jQuery(".jobman-geoloc-original-display").val( results[1].formatted_address );
						jQuery(".jobman-geoloc-display").val( results[1].formatted_address );
					}
				}
			);
		}
	}

	jQuery(".jobman-geoloc-data").val( pos.coords.latitude + "," + pos.coords.longitude );
	jQuery(".jobman-geoloc-original-display").val( description );
	jQuery(".jobman-geoloc-display").val( description );
}

function jobman_geo_error( err ) {
	return;
}

<?php } ?>
//]]>
</script>
<?php
}

function jobman_display_robots_noindex() {
	if( is_feed() )
		return;
?>
	<!-- Generated by Job Manager plugin -->
	<meta name="robots" content="noindex" />
<?php
}

function jobman_format_abstract( $text ) {
	$textsplit = preg_split( "[\n]", $text );

	$listlevel = 0;
	$starsmatch = array();
	foreach( $textsplit as $key => $line ) {
		preg_match( '/^[*]*/', $line, $starsmatch );
		$stars = strlen( $starsmatch[0] );

		$line = preg_replace( '/^[*]*/', '', $line );

		$listhtml_start = '';
		$listhtml_end = '';
		while( $stars > $listlevel ) {
			$listhtml_start .= '<ul>';
			$listlevel++;
		}
		while( $stars < $listlevel ) {
			$listhtml_start .= '</ul>';
			$listlevel--;
		}
		if( $listlevel > 0 ) {
			$listhtml_start .= '<li>';
			$listhtml_end = '</li>';
		}

		$textsplit[$key] = $listhtml_start . $line . $listhtml_end;
	}

	$text = implode( "\n", $textsplit );

	while( $listlevel > 0 ) {
		$text .= '</ul>';
		$listlevel--;
	}

	// Bold
	$text = preg_replace( "/'''(.*?)'''/", '<strong>$1</strong>', $text );

	// Italic
	$text = preg_replace( "/''(.*?)''/", '<em>$1</em>', $text );

	$text = '<p>' . $text . '</p>';
	return $text;
}

?>