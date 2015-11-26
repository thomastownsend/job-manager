<?php //encoding: utf-8
/*
Plugin Name: Job Manager
Plugin URI: http://wp-jobmanager.com/
Description: A job listing and job application management plugin for WordPress.
Version: 0.7.25
Author: Tom Townsend
Author URI: http://www.linkedin.com/in/thomastownsend
Text Domain: jobman
Tags: application, applicant tracking, ats, board, candidate, candidates, career, company, current opportunities, direct hire, employee, employer, employees, employment, freelance,
hiring, hire, interview, interviews, job, jobs, job board, job list, job lists ,job listing, job manager, job management, job role, job search, list, listing, manager, opportunities,
placement, position, positions, recruiter, recruiting, recruitment, talent
/*

/*
	Copyright 2009, 2010 Gary Pendergast (http://pento.net/)
	Copyright 2010 Automattic (http://automattic.com/)
    Copyright 2015 Tom Townsend - SMBsocial (http://smbsocial.com/)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Version
define( 'JOBMAN_VERSION', '0.7.25' );
define( 'JOBMAN_DB_VERSION', 19 );

// Define the URL to the plugin folder
define( 'JOBMAN_FOLDER', 'job-manager' );
if( ! defined( 'JOBMAN_URL' ) )
	define( 'JOBMAN_URL', WP_PLUGIN_URL . '/' . JOBMAN_FOLDER );

// Define the basename
define( 'JOBMAN_BASENAME', plugin_basename( __FILE__ ) );

// Define the complete directory path
define( 'JOBMAN_DIR', dirname( __FILE__ ) );

// Some Global vars

global $jobman_shortcodes;
$jobman_shortcodes = array( 'job_loop', 'job_row_number', 'job_id', 'job_highlighted', 'job_odd_even',
							'job_link', 'job_icon', 'job_title', 'job_field', 'job_field_label',
							'job_categories', 'job_category_links', 'job_field_loop', 'job_apply_link',
							'job_checkbox', 'job_apply_multi', 'job_page_count', 'job_page_previous_link',
							'job_page_previous_number', 'job_page_next_link', 'job_page_next_number',
							'job_page_minimum', 'job_page_maximum', 'job_total', 'current_category_name',
							'current_category_link' );

$jobman_options = get_option( 'jobman_options' );
global $jobman_field_shortcodes;
$jobman_field_shortcodes = array();
if( is_array( $jobman_options ) && array_key_exists( 'job_fields', $jobman_options ) )
	foreach( $jobman_options['job_fields'] as $fid => $field )
		$jobman_field_shortcodes[] = "job_field$fid";

global $jobman_app_shortcodes;
$jobman_app_shortcodes = array( 'job_app_submit', 'job_links', 'job_list', 'cat_list' );

global $jobman_app_field_shortcodes;
$jobman_app_field_shortcodes = array();
if( is_array( $jobman_options ) && array_key_exists( 'fields', $jobman_options ) )
{
	foreach( $jobman_options['fields'] as $fid => $field )
    {
		$jobman_app_field_shortcodes[] = "job_app_field$fid";
    }
}

/* Display a notice that can be dismissed */

add_action('admin_notices', 'jobman_admin_notice');

function jobman_admin_notice() {
if ( current_user_can( 'install_plugins' ) )
   {
	global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */
	if ( ! get_user_meta($user_id, 'jobman_ignore_notice') ) {
        echo '<div class="updated"><p>';   
        printf(__('Thanks! We hope you enjoy using <a href="http://www.wp-jobmanager.com/go/jobman/" 

target="_blank"><b>Job Manager</b></a>.Please consider<a href="http://www.wp-jobmanager.com/go/rating/" 

target="_blank"><b>Rating</b></a> us. You can also check-out and contribute to our<a href="http://www.wp-jobmanager.com/go/kb/"

target="_blank"><b>Knowledge Base</b></a>. Thanks for your help and rating. | <a 

href="%1$s">Hide Notice</a>'), '?jobman_nag_ignore=0');
        echo "</p></div>";
	}
    }
}

add_action('admin_init', 'jobman_nag_ignore');

function jobman_nag_ignore() {
	global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['jobman_nag_ignore']) && '0' == $_GET['jobman_nag_ignore'] ) {
             add_user_meta($user_id, 'jobman_ignore_notice', 'true', true);
	}
}
    
//
// Load Jobman
//

// Jobman global functions
require_once( JOBMAN_DIR . '/functions.php' );

// Jobman setup (for installation/upgrades)
require_once( JOBMAN_DIR . '/setup.php' );

// Jobman database
require_once( JOBMAN_DIR . '/db.php' );

// Jobman admin
require_once( JOBMAN_DIR . '/admin.php' );

// Support for other plugins
require_once( JOBMAN_DIR . '/plugins.php' );

// Jobman frontend
require_once( JOBMAN_DIR . '/frontend.php' );

// Widgets
require_once( JOBMAN_DIR . '/widgets.php' );

// Add hooks at the end
require_once( JOBMAN_DIR . '/hooks.php' );

// If the user is after a CSV export, give it to them
if( array_key_exists( 'jobman-mass-edit', $_REQUEST ) && 'export-csv' == $_REQUEST['jobman-mass-edit'] )
	jobman_get_application_csv();
?>