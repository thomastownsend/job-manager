<?php //encoding: utf-8

function jobman_activate() {
	global $wpdb;

	if( function_exists( 'is_multisite' ) && is_multisite() ) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ( isset( $_GET['networkwide'] ) && ( 1 == $_GET['networkwide'] ) ) {
	                $old_blog = $wpdb->blogid;
			// Get all blog ids
			$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs" ) );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				_jobman_activate();
			}
			switch_to_blog( $old_blog );
			return;
		}
	}
	_jobman_activate();
}

function jobman_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	global $wpdb;

	if ( is_plugin_active_for_network( JOBMAN_BASENAME ) ) {
		$old_blog = $wpdb->blogid;
		switch_to_blog( $blog_id );
		_jobman_activate();
		switch_to_blog( $old_blog );
	}
}

function _jobman_activate() {
	$options = get_option( 'jobman_options' );
	if( is_array( $options ) ) {
		$version = $options['version'];
		$dbversion = $options['db_version'];
	}
	else {
		// For folks upgrading from 0.3.x or earlier
		$version = get_option( 'jobman_version' );
		$dbversion = get_option( 'jobman_db_version' );
	}

	jobman_page_taxonomy_setup();
	jobman_load_translation_file();

	if( '' == $dbversion ) {
		// Never been run, create the database.
		jobman_create_default_settings();
		jobman_create_db();
	}
	elseif( JOBMAN_DB_VERSION != $dbversion ) {
		// New version, upgrade
		jobman_upgrade_settings( $dbversion );
		jobman_upgrade_db( $dbversion );
	}

	$options = get_option( 'jobman_options' );
	$options['version'] = JOBMAN_VERSION;
	$options['db_version'] = JOBMAN_DB_VERSION;
	update_option( 'jobman_options', $options );
}

function jobman_create_default_settings() {
	$options = array(
					'app_cat_select' => '',
					'app_job_select' => '',
					'application_email_from' => 4,
					'application_email_from_fields' => array( 2, 3 ),
					'application_email_subject_text' => __( 'Job Application', 'jobman' ) . ':',
					'application_email_subject_fields' => array( 2, 3 ),
					'date_format' => '',
					'default_email' => get_option( 'admin_email' ),
					'highlighted_behaviour' => 'sticky',
					'interviews' => 1,
					'interview_default_view' => 'month',
					'interview_title_text' => '',
					'interview_title_fields' => array( 2, 3 ),
					'jobs_per_page' => 0,
					'loginform_apply' => 1,
					'loginform_category' => 1,
					'loginform_job' => 1,
					'loginform_main' => 1,
					'multi_applications' => 0,
					'plugins' => array(
									'gxs' => 1,
									'sicaptcha' => 0
								),
					'promo_link' => 1,
					'related_categories' => 1,
					'rewrite_rules' => array(),
					'sort_by' => '',
					'sort_order' => '',
					'templates' => array(
									'application_form' => ''
								),
					'text' => array(
								'main_before' => '',
								'main_after' => '',
								'category_before' => '',
								'category_after' => '',
								'job_before' => '',
								'job_after' => '',
								'apply_before' => '',
								'apply_after' => '',
								'registration_before' => '',
								'registration_after' => '',
								'job_title_prefix' => __( 'Job', 'jobman' ) . ': ',
								'application_acceptance' => __( 'Thank you for your application! We\'ll check it out, and get back to you soon!', 'jobman' )
							),
					'uninstall' => array(
									'options' => 1,
									'jobs' => 1,
									'applications' => 1,
									'categories' => 1
								),
					'user_registration' => 0,
					'user_registration_required' => 0,
				);

	$titletext = __( 'Title', 'jobman' );
	$cattext = __( 'Categories', 'jobman' );
	$applynowtext = __( 'Apply Now', 'jobman' );

	$navprevious = sprintf( __( 'Page %1s', 'jobman' ), '[job_page_previous_number]' );
	$navnext = sprintf( __( 'Page %1s', 'jobman' ), '[job_page_next_number]' );
	$navdesc = sprintf( __( 'Jobs %1s-%2s of %3s', 'jobman' ), '[job_page_minimum]', '[job_page_maximum]', '[job_total]' );

	$options['templates']['job'] = <<<EOT
<table class="job-table[if_job_highlighted] highlighted[/if_job_highlighted]">
  <tr>
    <th scope="row">$titletext</th>
    <td>[job_icon] [job_title]</td>
  </tr>
[if_job_categories]
  <tr>
     <th scope="row">$cattext</th>
     <td>[job_category_links]</td>
  </tr>
[/if_job_categories]
[job_field_loop]
  [if_job_field]
  <tr>
    <th scope="row">[job_field_label]</th>
    <td>[job_field]</td>
  </tr>
  [/if_job_field]
[/job_field_loop]
  <tr>
    <td></td>
    <td class="jobs-applynow">[job_apply_link]{$applynowtext}[/job_apply_link]</td>
  </tr>
</table>
EOT;
		$options['templates']['job_list'] = <<<EOT
[job_loop]
<div class="job[job_row_number] job[job_id] [job_odd_even]">
<table class="job-table[if_job_highlighted] highlighted[/if_job_highlighted]">
  <tr>
    <th scope="row">$titletext</th>
    <td>[job_icon] [job_link][job_title][/job_link]</td>
  </tr>
[if_job_categories]
  <tr>
     <th scope="row">$cattext</th>
     <td>[job_category_links]</td>
  </tr>
[/if_job_categories]
[job_field_loop]
  [if_job_field]
  <tr>
    <th scope="row">[job_field_label]</th>
    <td>[job_field]</td>
  </tr>
  [/if_job_field]
[/job_field_loop]
  <tr>
    <td></td>
    <td class="jobs-applynow">[job_apply_link]{$applynowtext}[/job_apply_link]</td>
  </tr>
</table>
</div><br/><br/>
[/job_loop]

[if_job_page_count]
<div class="job-nav">
	<div class="previous">[job_page_previous_link]{$navprevious}[/job_page_previous_link]</div>
	<div class="this">$navdesc</div>
	<div class="next">[job_page_next_link]{$navnext}[/job_page_next_link]</div>
</div>
[/if_job_page_count]
EOT;

	update_option( 'jobman_options', $options );
}

function jobman_upgrade_settings( $oldversion ) {
	if( $oldversion < 2 )
		update_option( 'jobman_list_type', 'full' );

	if( $oldversion < 3 )
		update_option( 'jobman_plugin_gxs', 1 );

	if( $oldversion < 5 ) {
		// Move everything to single option
		$options = array(
						'version' => get_option( 'jobman_version' ),
						'db_version' => get_option( 'jobman_db_version' ),
						'page_name' => get_option( 'jobman_page_name' ),
						'default_email' => get_option( 'jobman_default_email' ),
						'list_type' => get_option( 'jobman_list_type' ),
						'application_email_from' => get_option( 'jobman_application_email_from' ),
						'application_email_subject_text' => get_option( 'jobman_application_email_subject_text' ),
						'application_email_subject_fields' => explode( ',', get_option( 'jobman_application_email_subject_fields' ) ),
						'promo_link' => get_option( 'jobman_promo_link' ),
						'plugins' => array(
										'gxs' => get_option( 'jobman_plugin_gxs' )
									)
					);

		update_option( 'jobman_options', $options );

		// Delete the old options
		delete_option( 'jobman_version' );
		delete_option( 'jobman_db_version' );
		delete_option( 'jobman_page_name' );
		delete_option( 'jobman_default_email' );
		delete_option( 'jobman_list_type' );
		delete_option( 'jobman_application_email_from' );
		delete_option( 'jobman_application_email_subject_text' );
		delete_option( 'jobman_application_email_subject_fields' );
		delete_option( 'jobman_promo_link' );
		delete_option( 'jobman_plugin_gxs' );
	}

	if( $oldversion < 7 ) {
		$options = get_option( 'jobman_options' );

		$options['user_registration'] = 0;
		$options['user_registration_required'] = 0;
		$options['loginform_main'] = 1;
		$options['loginform_category'] = 1;
		$options['loginform_job'] = 1;
		$options['loginform_apply'] = 1;

		update_option( 'jobman_options', $options );
	}

	if( $oldversion < 9 ) {
		mkdir( JOBMAN_UPLOAD_DIR . '/uploads', 0777, true );
		mkdir( JOBMAN_UPLOAD_DIR . '/icons', 0777, true );
	}

	if( $oldversion < 11 ) {
		$options = get_option( 'jobman_options' );

		$options['related_categories'] = 1;
		$options['sort_by'] = '';
		$options['sort_order'] = '';
		$options['highlighted_behaviour'] = 'sticky';

		$options['uninstall'] = array(
									'options' => 1,
									'jobs' => 1,
									'applications' => 1,
									'categories' => 1
								);

		$options['text'] = array(
								'main_before' => '',
								'main_after' => '',
								'category_before' => '',
								'category_after' => '',
								'job_before' => '',
								'job_after' => '',
								'apply_before' => '',
								'apply_after' => '',
								'job_title_prefix' => __( 'Job', 'jobman' ) . ': ',
								'application_acceptance' => __( 'Thank you for your application! We\'ll check it out, and get back to you soon!', 'jobman' )
							);

		$options['application_email_from_fields'] = array();
		$options['plugins']['sicaptcha'] = 0;

		$options['templates'] = array();
		$options['templates']['job'] = <<<EOT
<table class="job-table[if_job_highlighted] highlighted[/if_job_highlighted]">
  <tr>
    <th scope="row">Title</th>
    <td>[job_icon] [job_link][job_title][/job_link]</td>
  </tr>
[if_job_categories]
  <tr>
     <th scope="row">Categories</th>
     <td>[job_category_links]</td>
  </tr>
[/if_job_categories]
[job_field_loop]
  [if_job_field]
  <tr>
    <th scope="row">[job_field_label]</th>
    <td>[job_field]</td>
  </tr>
  [/if_job_field]
[/job_field_loop]
  <tr>
    <td></td>
    <td class="jobs-applynow">[job_apply_link]Apply Now[/job_apply_link]</td>
  </tr>
</table>
EOT;
		if( 'summary' == $options['list_type'] ) {
			$options['templates']['job_list'] = <<<EOT
<table class="jobs-table">
  <tr class="heading">
    <th>Title</th>
    <th>[job_field1_label]</th>
    <th>[job_field2_label]</th>
    <th>[job_field4_label]</th>
  </tr>

[job_loop]
  <tr class="job[job_row_number] job[job_id] [if_job_highlighted]highlighted [/if_job_highlighted] [job_odd_even]">
    <td>[if_job_icon][job_icon]<br/>[/if_job_icon] [job_link] [job_title] [/job_link]</td>
    <td>[job_field1]</td>
    <td>[job_field2]</td>
    <td>[job_field4]</td>
    <td>[job_link]More Info[/job_link]</td>
  </tr>
[/job_loop]

</table>
EOT;
		}
		else {
			$options['templates']['job_list'] = <<<EOT
[job_loop]
<div class="job[job_row_number] job[job_id] [job_odd_even]">
<table class="job-table[if_job_highlighted] highlighted[/if_job_highlighted]">
  <tr>
    <th scope="row">Title</th>
    <td>[job_icon] [job_title]</td>
  </tr>
[if_job_categories]
  <tr>
     <th scope="row">Categories</th>
     <td>[job_category_links]</td>
  </tr>
[/if_job_categories]
[job_field_loop]
  [if_job_field]
  <tr>
    <th scope="row">[job_field_label]</th>
    <td>[job_field]</td>
  </tr>
  [/if_job_field]
[/job_field_loop]
  <tr>
    <td></td>
    <td class="jobs-applynow">[job_apply_link]Apply Now[/job_apply_link]</td>
  </tr>
</table>
</div><br/><br/>
[/job_loop]
EOT;
		}

		update_option( 'jobman_options', $options );
	}

	if( $oldversion < 16 ) {
		$options = get_option( 'jobman_options' );

		$options['templates']['application_form'] = '';
		$options['multi_applications'] = 0;
		$options['api_keys'] = array(
									'google_maps' => ''
								);
		$options['interviews'] = 1;
		$options['interview_default_view'] = 'month';
		$options['interview_title_text'] = '';
		$options['interview_title_fields'] = array();
		$options['date_format'] = '';

		$navprevious = sprintf( __( 'Page %1s', 'jobman' ), '[job_page_previous_number]' );
		$navnext = sprintf( __( 'Page %1s', 'jobman' ), '[job_page_next_number]' );
		$navdesc = sprintf( __( 'Jobs %1s-%2s of %3s', 'jobman' ), '[job_page_minimum]', '[job_page_maximum]', '[job_total]' );

		$options['templates']['job_list'] .= <<<EOT

[if_job_page_count]
<div class="job-nav">
	<div class="previous">[job_page_previous_link]{$navprevious}[/job_page_previous_link]</div>
	<div class="this">$navdesc</div>
	<div class="next">[job_page_next_link]{$navnext}[/job_page_next_link]</div>
</div>
[/if_job_page_count]
EOT;

		update_option( 'jobman_options', $options );
	}

	if( $oldversion < 17 ) {
		$options = get_option( 'jobman_options' );

		if( ! array_key_exists( 'jobs_per_page', $options ) )
			$options['jobs_per_page'] = 0;

		update_option( 'jobman_options', $options );
	}

    if( $oldversion < 19 ) {
        $options = get_option( 'jobman_options' );

		if( ! array_key_exists( 'rewrite_rules', $options ) )
			$options['rewrite_rules'] = array();
		
		update_option( 'jobman_options', $options );
	}
}

function jobman_delete_blog( $blog_id ) {
	global $wpdb;

	if ( is_plugin_active_for_network( JOBMAN_BASENAME ) ) {
		$old_blog = $wpdb->blogid;
		switch_to_blog( $blog_id );
		_jobman_uninstall();
		switch_to_blog( $old_blog );
	}
}

function jobman_uninstall() {
	global $wpdb;

	if( function_exists( 'is_multisite' ) && is_multisite() ) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ( isset( $_GET['networkwide'] ) && ( 1 == $_GET['networkwide'] ) ) {
			$old_blog = $wpdb->blogid;
			// Get all blog ids
			$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs" ) );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				_jobman_uninstall();
			}
			switch_to_blog( $old_blog );
			return;
		}
	}
	_jobman_uninstall();
}

function _jobman_uninstall() {
	jobman_drop_db();

	$options = get_option( 'jobman_options' );

	if( $options['uninstall']['options'] ) {
		// Delete the icon uploads
		$uploads = get_posts( 'post_type=attachment&meta_key=_jobman_attachment_icon&meta_value=1&numberposts=-1' );
		if( count( $uploads ) > 0 ) {
			foreach( $uploads as $upload ) {
				wp_delete_attachment( $upload->ID );
			}
		}

		delete_option( 'jobman_options' );
	}
}

?>