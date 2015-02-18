<?php
function jobman_create_dashboard( $widths, $functions, $titles, $params = array() ) {
?>
<input type="hidden" id="wp_auto_columns" />
<div id="dashboard-widgets-wrap">
	<div id='dashboard-widgets' class='metabox-holder'>
<?php
	$ii = 0;
	foreach( $widths as $width ) {
?>
		<div id='postbox-container-<?php echo $ii + 1; ?>' class='postbox-container' style='width:<?php echo $width ?>'>
			<div id='normal-sortables' class='meta-box-sortables'>
<?php
		$jj = 0;
		foreach( $functions[$ii] as $function ) {
			if( array_key_exists( $ii, $params ) && array_key_exists( $jj, $params[$ii] ) )
				jobman_create_widget( $function, $titles[$ii][$jj], $params[$ii][$jj] );
			else
				jobman_create_widget( $function, $titles[$ii][$jj] );
			$jj++;
		}
?>
			</div>
		</div>
<?php
		$ii++;
	}
?>
	</div>
	<div class="clear"></div>
</div>
<?php
}

function jobman_create_widget( $function, $title, $params = array() ) {
?>
				<div id="jobman-<?php echo $function ?>" class="postbox jobman-postbox">
					<div class="handlediv" title="<?php _e( 'Click to toggle' ) ?>"><br /></div>
					<h3 class='hndle'><span><?php echo $title ?></span></h3>
					<div class="inside">
<?php
	call_user_func_array( $function, $params );
?>
						<div class="clear"></div>
					</div>
				</div>
<?php
}

function jobman_print_rating_stars( $id, $rating, $callback = 'jobman_rate_application', $readonly = false ) {
	if( $readonly )
		$class = "star-holder-readonly";
	else
		$class = "star-holder";
?>
			        <div class="<?php echo $class ?>">
<?php
	if( ! $readonly ) {
?>
						<input type="hidden" id="jobman-rating-<?php echo $id ?>" name="jobman-rating" value="<?php echo $rating ?>" />
						<input type="hidden" name="callbackid" value="<?php echo $id ?>" />
						<input type="hidden" name="callbackfunction" value="<?php echo $callback ?>" />
						<a href="#" onclick="jobman_reset_rating('<?php echo $id ?>', '<?php echo $callback ?>'); return false;"><?php _e( 'No rating', 'jobman' ) ?></a>
<?php
	}
?>
						<div id="jobman-star-rating-<?php echo $id ?>" class="star-rating" style="width: <?php echo $rating * 19 ?>px"></div>
<?php
	for( $ii = 1; $ii <= 5; $ii++) {
?>
						<div class="star star<?php echo $ii ?>"><img src="<?php echo JOBMAN_URL ?>/images/star.gif" alt="<?php echo $ii ?>" /></div>
<?php
	}
?>
					</div>
<?php
}

function jobman_load_translation_file() {
	load_plugin_textdomain( 'jobman', '', JOBMAN_FOLDER . '/translations' );
}

function jobman_page_taxonomy_setup() {
	// Create our new page types
	register_post_type( 'jobman_job', array( 'exclude_from_search' => false, 'public' => true, 'show_ui' => false, 'singular_name' => __( 'Job', 'jobman' ), 'label' => __( 'Jobs', 'jobman' ) ) );
	register_post_type( 'jobman_joblist', array( 'exclude_from_search' => true ) );
	register_post_type( 'jobman_app_form', array( 'exclude_from_search' => true ) );
	register_post_type( 'jobman_app', array( 'exclude_from_search' => true ) );
	register_post_type( 'jobman_register', array( 'exclude_from_search' => true ) );
	register_post_type( 'jobman_email', array( 'exclude_from_search' => true ) );
	register_post_type( 'jobman_interview', array( 'exclude_from_search' => true ) );

	// Create our new taxonomy thing
	$options = get_option( 'jobman_options' );
	
	$root = get_page( $options['main_page'] );
	$url = get_page_uri( $root->ID );
	
	if( substr( $url, 0, 1 ) != '/' )
		$url = "/$url";
	
	register_taxonomy( 'jobman_category', array( 'jobman_job', 'jobman_app' ), array( 'hierarchical' => false, 'label' => __( 'Category', 'series' ), 'query_var' => 'jcat', 'rewrite' => array( 'slug' => $url ) ) );
}

function jobman_page_hierarchical_setup( $types ) {
	$types[] = 'jobman_job';
	$types[] = 'jobman_joblist';
	$types[] = 'jobman_app_form';
	$types[] = 'jobman_register';

	return $types;
}

function jobman_sort_fields( $a, $b ) {
	if($a['sortorder'] == $b['sortorder'])
		return 0;
	
	return ( $a['sortorder'] < $b['sortorder'] ) ? -1 : 1;
}

function jobman_current_url() {
		$pageURL = 'http';
		
		if( is_ssl() )
			$pageURL .= 's';
		
		$pageURL .= '://';
		
		if( '80' != $_SERVER['SERVER_PORT'] )
			$pageURL .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		else
			$pageURL .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

		return $pageURL;
}

function jobman_job_live_where( $where = '' ) {
	global $wpdb;
	$where .= " AND $wpdb->posts.post_date_gmt <= UTC_TIMESTAMP() AND jobman_postmeta.meta_key='displayenddate' AND ( jobman_postmeta.meta_value='' OR jobman_postmeta.meta_value >= UTC_TIMESTAMP() ) ";
	return $where;
}

function jobman_job_live_join( $join = '' ) {
	global $wpdb;
	$join .= " LEFT JOIN $wpdb->postmeta AS jobman_postmeta ON $wpdb->posts.ID = jobman_postmeta.post_id ";
	return $join;
}

function jobman_job_live_distinct( $distinct = '' ) {
	return 'distinct';
}

if( ! function_exists( 'array_insert' ) ) {
	function array_insert( $array, $pos, $val )	{
		$array2 = array_splice( $array, $pos );
		$array[] = $val;
		$array = array_merge( $array, $array2 );
	   
		return $array;
	}
}

?>