<?php

if( ! defined( 'JOBMAN_URL' ) )
	define( 'JOBMAN_URL', '/wp-content/themes/vip/' . JOBMAN_VIP_SITE . '/plugins/job-manager' );


function jobman_change_loggedin_html( $html ) {
	return NULL;
}

function jobman_change_login_html( $html ) {
	if( 'automattic-2011' == JOBMAN_VIP_SITE )
		$loginurl = 'http://automattic.wordpress.com/wp-login.php?redirect_to=';

	$registerurl = 'http://en.wordpress.com/signup/?redirect_to=';

	$data = get_posts( 'post_type=jobman_app_form&numberposts=1' );
	if( count( $data ) > 0 ) {
		$applypage = $data[0];

		$applypageurl = get_page_link( $applypage->ID );
		$loginurl .= $applypageurl;
		$registerurl .= $applypageurl;
	}

	$html = "<p>Do you have a WordPress.com account? If so, please <a href='$loginurl'>login here</a>. If not, please <a href='$registerurl'>register one now</a>!</p>";

	return $html;
}

function jobman_change_register_html( $html ) {
	return NULL;
}

function jobman_change_pleaseregister_html( $html ) {
	return NULL;
}

add_filter( 'jobman_loggedin_html', 'jobman_change_loggedin_html' );
add_filter( 'jobman_login_html', 'jobman_change_login_html' );
add_filter( 'jobman_register_html', 'jobman_change_register_html' );
add_filter( 'jobman_pleaseregister_html', 'jobman_change_pleaseregister_html' );

if( 'automattic-2011' == JOBMAN_VIP_SITE ) {
	add_action( 'wp_head', 'jobman_automattic_display_head' );
	add_action( 'admin_head-job-manager_page_jobman-list-applications', 'jobman_automattic_display_admin_head' );
	add_filter( 'wp_get_attachment_url', 'jobman_automattic_filter_attachment_url', 10, 2 );
}

function jobman_automattic_display_head() {
	global $jobman_displaying;

	if( ! $jobman_displaying )
		return;

	if( is_feed() )
		return;
?>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function() {
	jQuery("#jobman-appform p.jobman-oss span").css( 'display', 'none' );
	jQuery("#jobman-appform p.jobman-oss select").change(function( event ) {
		jQuery("#jobman-appform p.jobman-oss span").animate({ opacity: 'toggle', height: 'toggle' }, "fast");
	});
});
//]]>
</script>
<?php
}

function jobman_automattic_display_admin_head() {
?>
<style type="text/css">
div.printicon {
	display: none !important;
}
</style>
<?php
}

function jobman_automattic_filter_attachment_url( $url, $postid ) {
	return str_replace( 'automattic.com/files/', 'automattic.files.wordpress.com/', $url );
}
?>
