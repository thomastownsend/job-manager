<?php
function jobman_conf() {
	global $jobman_formats;

	$options = get_option( 'jobman_options' );

	if( array_key_exists( 'tab', $_REQUEST ) ) {
		switch( $_REQUEST['tab'] ) {
			case 'display':
				jobman_display_conf();
				return;
			case 'appform':
				jobman_application_setup();
				return;
			case 'jobform':
				jobman_job_setup();
				return;
		}
	}

	if( array_key_exists( 'jobmanconfsubmit', $_REQUEST ) ) {
		// Configuration form as been submitted. Updated the database.
		check_admin_referer( 'jobman-conf-updatedb' );
		jobman_conf_updatedb();
	}
	else if( array_key_exists( 'jobmanresumesubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-resume-settings-updatedb' );
		jobman_resume_settings_updatedb();
	}
	else if( array_key_exists( 'jobmancatsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-categories-updatedb' );
		jobman_categories_updatedb();
	}
	else if( array_key_exists( 'jobmaniconsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-icons-updatedb' );
		jobman_icons_updatedb();
	}
	else if( array_key_exists( 'jobmanusersubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-users-updatedb' );
		jobman_users_updatedb();
	}
	else if( array_key_exists( 'jobmanappemailsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-application-email-updatedb' );
		jobman_application_email_updatedb();
	}
	else if( array_key_exists( 'jobmaninterviewsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-interview-updatedb' );
		jobman_interview_updatedb();
	}
	else if( array_key_exists( 'jobmanotherpluginssubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-other-plugins-updatedb' );
		jobman_other_plugins_updatedb();
	}
	else if( array_key_exists( 'jobmanuninstallsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-uninstall-updatedb' );
		jobman_uninstall_updatedb();
	}
?>
	<div class="wrap">
<?php
	jobman_print_settings_tabs();

	if( ! get_option( 'smb_consulting' ) ) {
		$widths = array( '78%', '20%' );
		$functions = array(
						array( 'jobman_print_settings_box', 'jobman_print_categories_box', 'jobman_print_icons_box', 'jobman_print_user_box', 'jobman_print_application_email_box', 'jobman_print_other_plugins_box', 'jobman_print_uninstall_box' ),
						array('jobman_print_survey_box', 'jobman_print_donate_box', 'jobman_print_about_box', 'jobman_print_translators_box' )
					);
		$titles = array(
					array( __( 'Settings', 'jobman' ),  __( 'Categories', 'jobman' ), __( 'Icons', 'jobman' ), __( 'User Settings', 'jobman' ), __( 'Application Email Settings', 'jobman' ), __( 'Other Plugins', 'jobman' ), __( 'Uninstall Settings', 'jobman' ) ),
					array( __( 'Job Manager - SURVEY -', 'jobman' ), __( 'Donate', 'jobman' ), __( 'About This Plugin', 'jobman' ), __( 'Translators', 'jobman' ) )
				);

		if( $options['interviews'] ) {
			$functions[0] = array_insert( $functions[0], 5, 'jobman_print_interview_box' );
			$titles[0] = array_insert( $titles[0], 5, __( 'Interview Settings', 'jobman' ) );
		}
	}
	else {
		$widths = array( '49%', '49%' );
		$functions = array(
						array( 'jobman_print_settings_box', 'jobman_print_categories_box', 'jobman_print_other_plugins_box' ),
						array( 'jobman_print_icons_box', 'jobman_print_user_box', 'jobman_print_application_email_box', 'jobman_print_uninstall_box' )
					);
		$titles = array(
					array( __( 'Settings', 'jobman' ), __( 'Categories', 'jobman' ), __( 'Other Plugins', 'jobman' ) ),
					array( __( 'Icons', 'jobman' ), __( 'User Settings', 'jobman' ), __( 'Application Email Settings', 'jobman' ), __( 'Uninstall Settings', 'jobman' ) )
				);

		if( $options['interviews'] ) {
			$functions[1] = array_insert( $functions[1], 3, 'jobman_print_interview_box' );
			$titles[1] = array_insert( $titles[1], 3, __( 'Interview Settings', 'jobman' ) );
		}
	}
	jobman_create_dashboard( $widths, $functions, $titles );
?>
	</div>
<?php
}

function jobman_print_settings_box() {
	$options = get_option( 'jobman_options' );
?>
		<form action="" method="post">
		<input type="hidden" name="jobmanconfsubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-conf-updatedb' ); 
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'URL path', 'jobman' ) ?></th>
				<td colspan="2">
					<a href="<?php echo get_page_link( $options['main_page'] ) ?>"><?php echo get_page_link( $options['main_page'] ) ?></a> 
					(<a href="<?php echo get_edit_post_link( $options['main_page'] ) ?>"><?php _e( 'edit', 'jobman' ) ?></a>)
				</td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Allow Multi-Applications', 'jobman' ) ?></th>
				<td><input type="checkbox" name="multi-applications" value="1" <?php echo ( $options['multi_applications'] )?( 'checked="checked" ' ):( '' )?> /></td>
				<td><span class="description"><?php _e( 'This will allow applicants to send through a single application for multiple jobs.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Enable Interview Scheduling', 'jobman' ) ?></th>
				<td><input type="checkbox" name="interviews" value="1" <?php echo ( $options['interviews'] )?( 'checked="checked" ' ):( '' )?> /></td>
				<td><span class="description"><?php _e( 'This will enable interview scheduling functionality.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Default email', 'jobman' ) ?></th>
				<td colspan="2"><input class="regular-text code" type="text" name="default-email" value="<?php echo $options['default_email'] ?>" /></td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_categories_box() {
	$options = get_option( 'jobman_options' );
?>
		<p><?php _e( 'Similar to the normal WordPress Categories, Job Manager categories can be used to split jobs into different groups. They can also be used to customise how the Application Form appears for jobs in different categories.', 'jobman' ) ?></p>
		<p>
			<strong><?php _e( 'Title', 'jobman' ) ?></strong> - <?php _e( 'The display name of the category', 'jobman' ) ?><br/>
			<strong><?php _e( 'Slug', 'jobman' ) ?></strong> - <?php _e( 'The URL of the category', 'jobman' ) ?><br/>
			<strong><?php _e( 'Email', 'jobman' ) ?></strong> - <?php _e( 'The address to notify when new applications are submitted in this category', 'jobman' ) ?><br/>
			<strong><?php _e( 'Link', 'jobman' ) ?></strong> - <?php _e( 'The URL of the list of jobs in this category', 'jobman' ) ?>
		</p>
		<form action="" method="post">
		<input type="hidden" name="jobmancatsubmit" value="1" />
<?php
	wp_nonce_field( 'jobman-categories-updatedb' );
?>
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col"><?php _e( 'Title', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Slug', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Email', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Link', 'jobman' ) ?></th>
				<th scope="col" class="jobman-fielddelete"><?php _e( 'Delete', 'jobman' ) ?></th>
			</tr>
			</thead>
<?php
	$categories = get_terms( 'jobman_category', 'hide_empty=0' );

	if( count( $categories ) > 0 ) {
		foreach( $categories as $cat ) {
			$url = get_term_link( $cat->slug, 'jobman_category' );
?>
			<tr>
				<td>
					<input type="hidden" name="id[]" value="<?php echo $cat->term_id ?>" />
					<input class="regular-text code" type="text" name="title[]" value="<?php echo $cat->name ?>" />
				</td>
				<td><input class="regular-text code" type="text" name="slug[]" value="<?php echo $cat->slug ?>" /></td>
				<td><input class="regular-text code" type="text" name="email[]" value="<?php echo $cat->description ?>" /></td>
				<td><a href="<?php echo $url ?>"><?php _e( 'Link', 'jobman' ) ?></a></td>
				<td><a href="#" onclick="jobman_delete( this, 'id', 'jobman-delete-category-list' ); return false;"><?php _e( 'Delete', 'jobman' ) ?></a></td>
			</tr>
<?php
		}
	}

	$template = '<tr><td><input type="hidden" name="id[]" value="-1" />';
	$template .= '<input class="regular-text code" type="text" name="title[]" /></td>';
	$template .= '<td><input class="regular-text code" type="text" name="slug[]" /></td>';
	$template .= '<td><input class="regular-text code" type="text" name="email[]" /></td>';
	$template .= '<td>&nbsp;</td>';
	$template .= '<td><a href="#" onclick="jobman_delete( this, \\\'id\\\', \\\'jobman-delete-category-list\\\' ); return false;">' . __( 'Delete', 'jobman' ) . '</a></td></tr>';

	$display_template = str_replace( "\\'", "'", $template );

	echo $display_template;
?>
			<tr id="jobman-catnew">
					<td colspan="5" style="text-align: right;">
						<input type="hidden" name="jobman-delete-list" id="jobman-delete-category-list" value="" />
						<a href="#" onclick="jobman_new( 'jobman-catnew', 'category' ); return false;"><?php _e( 'Add New Category', 'jobman' ) ?></a>
					</td>
			</tr>
		</table>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Show related categories?', 'jobman' ) ?></th>
				<td><input type="checkbox" name="related-categories" <?php echo ( $options['related_categories'] )?( 'checked="checked" ' ):( '' ) ?>/></td>
				<td><span class="description"><?php _e( 'This will show a list of categories that any jobs in a given job list belong to.', 'jobman' ) ?></span></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Categories', 'jobman' ) ?>" /></p>
<script type="text/javascript">
//<![CDATA[
	jobman_templates['category'] = '<?php echo $template ?>';
//]]>
</script>
		</form>
<?php
}

function jobman_print_icons_box() {
	$options = get_option( 'jobman_options' );
?>
		<p><?php _e( 'Icons can be assigned to jobs that you want to draw attention to. These icons will only be displayed when using the "Summary" jobs list type. Optimal size 32 x 32 px', 'jobman' ) ?></p>
		<p>
			<strong><?php _e( 'Icon', 'jobman' ) ?></strong> - <?php _e( 'The current icon', 'jobman' ) ?><br/>
			<strong><?php _e( 'Title', 'jobman' ) ?></strong> - <?php _e( 'The display name of the icon', 'jobman' ) ?><br/>
			<strong><?php _e( 'File', 'jobman' ) ?></strong> - <?php _e( 'The icon file', 'jobman' ) ?><br/>
		</p>
		<form action="" enctype="multipart/form-data" method="post">
		<input type="hidden" name="jobmaniconsubmit" value="1" />
<?php
	wp_nonce_field( 'jobman-icons-updatedb' );
?>
		<table class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" class="jobman-icon"><?php _e( 'Icon', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Title', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'File', 'jobman' ) ?></th>
				<th scope="col" class="jobman-fielddelete"><?php _e( 'Delete', 'jobman' ) ?></th>
			</tr>
			</thead>
<?php
	$icons = $options['icons'];

	if( count( $icons ) > 0 ) {
		foreach( $icons as $icon ) {
		$post = get_post( $icon );
?>
			<tr>
				<td>
					<input type="hidden" name="id[]" value="<?php echo $icon ?>" />
					<img src="<?php echo wp_get_attachment_url( $icon ) ?>" />
				</td>
				<td><input class="regular-text code" type="text" name="title[]" value="<?php echo $post->post_title ?>" /></td>
				<td><input class="regular-text code" type="file" name="icon[]" /></td>
				<td><a href="#" onclick="jobman_delete( this, 'id', 'jobman-delete-icon-list' ); return false;"><?php _e( 'Delete', 'jobman' ) ?></a></td>
			</tr>
<?php
		}
	}

	$template = '<tr><td><input type="hidden" name="id[]" value="-1" /></td>';
	$template .= '<td><input class="regular-text code" type="text" name="title[]" /></td>';
	$template .= '<td><input class="regular-text code" type="file" name="icon[]" /></td>';
	$template .= '<td><a href="#" onclick="jobman_delete( this, \\\'id\\\', \\\'jobman-delete-icon-list\\\' ); return false;">' . __( 'Delete', 'jobman' ) . '</a></td></tr>';

	$print_template = str_replace( "\\'", "'", $template );
	echo $print_template;
?>
		<tr id="jobman-iconnew">
				<td colspan="4" style="text-align: right;">
					<input type="hidden" name="jobman-delete-list" id="jobman-delete-icon-list" value="" />
					<a href="#" onclick="jobman_new( 'jobman-iconnew', 'icon' ); return false;"><?php _e( 'Add New Icon', 'jobman' ) ?></a>
				</td>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Icons', 'jobman' ) ?>" /></p>
<script type="text/javascript">
//<![CDATA[
	jobman_templates['icon'] = '<?php echo $template ?>';
//]]>
</script>
		</form>
<?php
}

function jobman_print_user_box() {
	$options = get_option( 'jobman_options' );
?>
		<p><?php _e( "Allowing users to register means that they and you can more easily keep track of jobs they've applied for.", 'jobman' ) ?></p>
		<form action="" method="post">
		<input type="hidden" name="jobmanusersubmit" value="1" />
<?php
	wp_nonce_field( 'jobman-users-updatedb' );
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Enable User Registration', 'jobman' ) ?></th>
				<td><input type="checkbox" value="1" name="user-registration" <?php echo ( $options['user_registration'] )?( 'checked="checked" ' ):( '' ) ?>/></td>
				<td><span class="description"><?php _e( 'This will allow users to register for the Jobs system, even if user registration is disabled for your blog.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Require User Registration', 'jobman' ) ?></th>
				<td><input type="checkbox" value="1" name="user-registration-required" <?php echo ( $options['user_registration_required'] )?( 'checked="checked" ' ):( '' ) ?>/></td>
				<td><span class="description"><?php _e( 'If the previous option is checked, this option will require users to login before they can complete the application form.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Which pages should the login form be displayed on?', 'jobman' ) ?></th>
				<td colspan="2">
					<input type="checkbox" value="1" name="loginform-main" <?php echo ( $options['loginform_main'] )?( 'checked="checked" ' ):( '' ) ?>/> <?php _e( 'The main jobs list', 'jobman' ) ?><br />
					<input type="checkbox" value="1" name="loginform-category" <?php echo ( $options['loginform_category'] )?( 'checked="checked" ' ):( '' ) ?>/> <?php _e( 'Category jobs lists', 'jobman' ) ?><br />
					<input type="checkbox" value="1" name="loginform-job" <?php echo ( $options['loginform_job'] )?( 'checked="checked" ' ):( '' ) ?>/> <?php _e( 'Individual jobs', 'jobman' ) ?><br />
					<input type="checkbox" value="1" name="loginform-apply" <?php echo ( $options['loginform_apply'] )?( 'checked="checked" ' ):( '' ) ?>/> <?php _e( 'The application form', 'jobman' ) ?><br />
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update User Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_application_email_box() {
	$options = get_option( 'jobman_options' );

	$fields = $options['fields'];
?>
		<p><?php _e( 'When an applicant successfully submits an application, an email will be sent to the appropriate user. These options allow you to customise that email.', 'jobman' ) ?></p>
		<form action="" method="post">
		<input type="hidden" name="jobmanappemailsubmit" value="1" />
<?php
	wp_nonce_field( 'jobman-application-email-updatedb' );
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Email Address', 'jobman' ) ?></th>
				<td><select name="jobman-from">
					<option value=""><?php _e( 'None', 'jobman' ) ?></option>
<?php
	$fid = $options['application_email_from'];
	if( count( $fields ) > 0 ) {
		foreach( $fields as $id => $field ) {
			if( 'text' == $field['type'] || 'textarea' == $field['type'] ) {
				$selected = '';
				if( $id == $fid ) {
					$selected = ' selected="selected"';
				}
?>
					<option value="<?php echo $id ?>"<?php echo $selected ?>><?php echo $field['label'] ?></option>
<?php
			}
		}
	}
?>
				</select></td>
				<td><span class="description"><?php _e( 'The application field to use as the email address. This will be the "From" address in the initial application, and the field used for emailing applicants.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'From Name', 'jobman' ) ?></th>
				<td>
					<select name="jobman-from-fields[]" multiple="multiple" size="5" class="multiselect">
					<option value="" style="font-weight: bold; border-bottom: 1px solid black;"><?php _e( 'None', 'jobman' ) ?></option>
<?php
	$fids = $options['application_email_from_fields'];
	if( count( $fields ) > 0 ) {
		foreach( $fields as $id => $field ) {
			if( 'text' == $field['type'] || 'textarea' == $field['type'] ) {
				$selected = '';
				if( in_array( $id, $fids ) ) {
					$selected = ' selected="selected"';
				}
?>
					<option value="<?php echo $id ?>"<?php echo $selected ?>><?php echo $field['label'] ?></option>
<?php
			}
		}
	}
?>
					</select>
				</td>
				<td><span class="description"><?php _e( 'The name that will appear with the "From" email address.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Subject', 'jobman' ) ?></th>
				<td>
					<input class="regular-text code" type="text" name="jobman-subject-text" value="<?php echo $options['application_email_subject_text'] ?>" /><br/>
					<select name="jobman-subject-fields[]" multiple="multiple" size="5" class="multiselect">
					<option value="" style="font-weight: bold; border-bottom: 1px solid black;"><?php _e( 'None', 'jobman' ) ?></option>
<?php
	$fids = $options['application_email_subject_fields'];
	if( count( $fields ) > 0 ) {
		foreach( $fields as $id => $field ) {
			if( 'text' == $field['type'] || 'textarea' == $field['type'] ) {
				$selected = '';
				if( in_array( $id, $fids ) ) {
					$selected = ' selected="selected"';
				}
?>
					<option value="<?php echo $id ?>"<?php echo $selected ?>><?php echo $field['label'] ?></option>
<?php
			}
		}
	}
?>
					</select>
				</td>
				<td><span class="description"><?php _e( 'The email subject, and any fields to include in the subject.', 'jobman' ) ?></span></td>
			</tr>
		</table>

		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Email Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_interview_box() {
	$options = get_option( 'jobman_options' );

	$fields = $options['fields'];
?>
		<form action="" method="post">
		<input type="hidden" name="jobmaninterviewsubmit" value="1" />
<?php
	wp_nonce_field( 'jobman-interview-updatedb' );
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Default View', 'jobman' ) ?></th>
				<td>
					<select name="jobman-default-view">
<?php
	$views = array(
					'day' => __( 'Day', 'jobman' ),
					'month' => __( 'Month', 'jobman' ),
					'year' => __( 'Year', 'jobman' )
				);
	foreach( $views as $value => $text ) {
		$selected = '';
		if( $value == $options['interview_default_view'] ) {
			$selected = ' selected="selected"';
		}
?>
					<option value="<?php echo $value ?>"<?php echo $selected ?>><?php echo $text ?></option>
<?php
	}
?>
					</select>
				<td><span class="description"><?php _e( 'The default calendar view on the "Interviews" page.', 'jobman' ) ?></span></td>
				</td>
			<tr>
				<th scope="row"><?php _e( 'Title', 'jobman' ) ?></th>
				<td>
					<input class="regular-text code" type="text" name="jobman-title-text" value="<?php echo $options['interview_title_text'] ?>" /><br/>
					<select name="jobman-title-fields[]" multiple="multiple" size="5" class="multiselect">
					<option value="" style="font-weight: bold; border-bottom: 1px solid black;"><?php _e( 'None', 'jobman' ) ?></option>
<?php
	$fids = $options['interview_title_fields'];
	if( count( $fields ) > 0 ) {
		foreach( $fields as $id => $field ) {
			if( 'text' == $field['type'] || 'textarea' == $field['type'] ) {
				$selected = '';
				if( in_array( $id, $fids ) ) {
					$selected = ' selected="selected"';
				}
?>
					<option value="<?php echo $id ?>"<?php echo $selected ?>><?php echo $field['label'] ?></option>
<?php
			}
		}
	}
?>
					</select>
				</td>
				<td><span class="description"><?php _e( 'The Interview title, and any fields to include in the title, as displayed on the "Interviews" page.', 'jobman' ) ?></span></td>
			</tr>
		</table>

		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Interview Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_other_plugins_box() {
	$options = get_option( 'jobman_options' );
?>
	<p><?php _e( 'Job Manager provides extra functionality through the use of other plugins available for WordPress. These plugins are not required for Job Manager to function, but do provide enhancements.', 'jobman' ) ?></p>
	<form action="" method="post">
	<input type="hidden" name="jobmanotherpluginssubmit" value="1" />
<?php
	wp_nonce_field( 'jobman-other-plugins-updatedb' );

	if( class_exists( 'GoogleSitemapGeneratorLoader' ) ) {
		$gxs = true;
		$gxs_status = __( 'Installed', 'jobman' );
		$gxs_version = GoogleSitemapGeneratorLoader::GetVersion();
	}
	else {
		$gxs = false;
		$gxs_status = __( 'Not Installed', 'jobman' );
	}
?>
		<h4><?php _e( 'Google XML Sitemaps', 'jobman' ) ?></h4>
		<p><?php _e( 'Allows you to automatically add all your job listing and job detail pages to your sitemap. By default, only the main job list is added.', 'jobman' ) ?></p>
		<p>
			<a href="http://wordpress.org/extend/plugins/google-sitemap-generator/"><?php _e( 'Download', 'jobman' ) ?></a><br/>
			<?php _e( 'Status', 'jobman' ) ?>: <span class="<?php echo ( $gxs )?( 'pluginokay' ):( 'pluginwarning' ) ?>"><?php echo $gxs_status ?></span><br/>
			<?php echo ( $gxs )?( __( 'Version', 'jobman' ) . ": $gxs_version" ):( '' ) ?>
			<?php echo ( ! $gxs || version_compare( $gxs_version, '3.2', '<' ) )?( ' <span class="pluginwarning">' . __( 'Job Manager requires Google XML Sitemaps version 3.2 or later.', 'jobman' ) . '</span>' ):( '' ) ?>
		</p>
<?php
	if( $gxs && version_compare( $gxs_version, '3.2', '>=' ) ) {
?>
		<strong><?php _e( 'Options', 'jobman' ) ?></strong>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Add Job pages to your Sitemap?', 'jobman' ) ?></th>
				<td><input type="checkbox" value="1" name="plugin-gxs"<?php echo ( $options['plugins']['gxs'] )?( ' checked="checked"' ):( '' ) ?> /></td>
			</tr>
		</table>
<?php
	}

	$sicaptcha = false;
	$class = 'pluginwarning';
	$sistatus = __( 'Not Installed', 'jobman' );

	if( class_exists( 'siCaptcha' ) ) {
		$sicaptcha = true;
		$class = 'pluginokay';
		$sistatus = __( 'Installed', 'jobman' );
	}
?>
		<h4><?php _e( 'SI Captcha', 'jobman' ) ?></h4>
		<p><?php _e( 'Allows you to add a Captcha to your Application submission form.', 'jobman' ) ?></p>
		<p>
			<a href="http://wordpress.org/extend/plugins/si-captcha-for-wordpress/"><?php _e( 'Download', 'jobman' ) ?></a><br/>
			<?php _e( 'Status', 'jobman' ) ?>: <span class="<?php echo $class ?>"><?php echo $sistatus ?></span>
		</p>
<?php
	if( $sicaptcha ) {
?>
		<strong><?php _e( 'Options', 'jobman' ) ?></strong>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Add a captcha to the application form?', 'jobman' ) ?></th>
				<td><input type="checkbox" value="1" name="plugin-sicaptcha"<?php echo ( $options['plugins']['sicaptcha'] )?( ' checked="checked"' ):( '' ) ?> /></td>
			</tr>
		</table>
<?php
	}
?>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Plugin Settings', 'jobman' ) ?>" /></p>
	</form>
<?php
}

function jobman_print_uninstall_box() {
	$options = get_option( 'jobman_options' );
?>
		<p><?php _e( 'If you ever choose to uninstall Job Manager, you can select what parts should be deleted from the database.', 'jobman' ) ?></p>
		<form action="" method="post">
		<input type="hidden" name="jobmanuninstallsubmit" value="1" />
<?php
	wp_nonce_field( 'jobman-uninstall-updatedb' );
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Options', 'jobman' ) ?></th>
				<td><input type="checkbox" value="1" name="options" <?php echo ( $options['uninstall']['options'] )?( 'checked="checked" ' ):( '' ) ?>/></td>
				<td><span class="description"><?php _e( 'The options selected on the Admin Settings and Display Settings pages. This includes any icons uploaded.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Jobs', 'jobman' ) ?></th>
				<td><input type="checkbox" value="1" name="jobs" <?php echo ( $options['uninstall']['jobs'] )?( 'checked="checked" ' ):( '' ) ?>/></td>
				<td><span class="description"><?php _e( 'Jobs that have been created.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Applications', 'jobman' ) ?></th>
				<td><input type="checkbox" value="1" name="applications" <?php echo ( $options['uninstall']['applications'] )?( 'checked="checked" ' ):( '' ) ?>/></td>
				<td><span class="description"><?php _e( 'Applications that have been submitted. This includes any files uploaded (resumes, etc).', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Categories', 'jobman' ) ?></th>
				<td><input type="checkbox" value="1" name="categories" <?php echo ( $options['uninstall']['categories'] )?( 'checked="checked" ' ):( '' ) ?>/></td>
				<td><span class="description"><?php _e( 'Job Manager Categories that have been created.', 'jobman' ) ?></span></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Uninstall Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_conf_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$options['default_email'] = $_REQUEST['default-email'];

	if( array_key_exists( 'multi-applications', $_REQUEST ) && $_REQUEST['multi-applications'] )
		$options['multi_applications'] = 1;
	else
		$options['multi_applications'] = 0;

	if( array_key_exists( 'interviews', $_REQUEST ) && $_REQUEST['interviews'] )
		$options['interviews'] = 1;
	else
		$options['interviews'] = 0;

	update_option( 'jobman_options', $options );
}

function jobman_categories_updatedb() {
	$options = get_option( 'jobman_options' );

	$ii = 0;
	$newcount = -1;
	foreach( $_REQUEST['id'] as $id ) {
		if( -1 == $id ) {
			$newcount++;
			// INSERT new field
			if( '' != $_REQUEST['title'][$ii] ) {
				$cat = wp_insert_term( $_REQUEST['title'][$ii], 'jobman_category', array( 'slug' => $_REQUEST['slug'][$ii], 'description' => $_REQUEST['email'][$ii] ) );
			}
			else {
				// No input. Don't insert into the DB.
				$ii++;
				continue;
			}
		}
		else {
			// UPDATE existing field
			if( '' != $_REQUEST['slug'][$ii] )
				wp_update_term( $id, 'jobman_category', array( 'name' => $_REQUEST['title'][$ii], 'slug' => $_REQUEST['slug'][$ii], 'description' => $_REQUEST['email'][$ii] ) );
			else
				wp_update_term( $id, 'jobman_category', array( 'name' => $_REQUEST['title'][$ii], 'description' => $_REQUEST['email'][$ii] ) );
		}
		$ii++;
	}

	$deletes = explode( ',', $_REQUEST['jobman-delete-list'] );
	foreach( $deletes as $delete ) {
		wp_delete_term( $delete, 'jobman_category' );

		// Delete the category from any fields
		foreach( $options['fields'] as $fid => $field ) {
			if( ! array_key_exists( 'categories', $field ) || ! is_array( $field['categories'] ) )
				continue;

			$loc = array_search( $delete, $field['categories'] );
			if( false !== $loc ) {
				unset( $options['fields'][$fid]['categories'][$loc] );
				$options['fields'][$fid]['categories'] = array_values( $options['fields'][$fid]['categories'] );
			}
		}
	}

	if( array_key_exists( 'related-categories', $_REQUEST ) && $_REQUEST['related-categories'] )
		$options['related_categories'] = 1;
	else
		$options['related_categories'] = 0;

	if( $options['plugins']['gxs'] )
		do_action( 'sm_rebuild' );

	update_option( 'jobman_options', $options );
}

function jobman_icons_updatedb() {
	$options = get_option( 'jobman_options' );

	$ii = 0;
	$newcount = -1;

	foreach( $_REQUEST['id'] as $id ) {
		if( -1 == $id ) {
			$newcount++;
			// INSERT new field
			if( '' != $_REQUEST['title'][$ii] || '' != $_FILES['icon']['name'][$ii] ) {
				$upload = wp_upload_bits( $_FILES['icon']['name'][$ii], NULL, file_get_contents( $_FILES['icon']['tmp_name'][$ii] ) );
				if( ! $upload['error'] ) {
					$filetype = wp_check_filetype( $upload['file'] );
					$attachment = array(
									'post_title' => $_REQUEST['title'][$ii],
									'post_content' => '',
									'post_status' => 'publish',
									'post_mime_type' => $filetype['type']
								);
					$data = wp_insert_attachment( $attachment, $upload['file'], $options['main_page'] );
					$attach_data = wp_generate_attachment_metadata( $data, $upload['file'] );
					wp_update_attachment_metadata( $data, $attach_data );

					add_post_meta( $data, '_jobman_attachment', 1, true );
					add_post_meta( $data, '_jobman_attachment_icon', 1, true );

					$options['icons'][] = $data;
				}
			}
			else {
				// No input. Don't insert into the DB.
				$ii++;
				continue;
			}
		}
		else {
			// UPDATE existing field
			if( '' != $_FILES['icon']['name'][$ii] ) {
				$upload = wp_upload_bits( $_FILES['icon']['name'][$ii], NULL, file_get_contents( $_FILES['icon']['tmp_name'][$ii] ) );
				if( ! $upload['error'] ) {
					wp_update_attachment( $id, $upload['file'] );
					$attach_data = wp_generate_attachment_metadata( $id, $upload['file'] );
					wp_update_attachment_metadata( $id, $attach_data );
				}
			}
			$updatepost = array(
								'ID' => $id,
								'post_title' => $_REQUEST['title'][$ii]
						);
			wp_update_post( $updatepost );
		}

		$ii++;
	}

	$deletes = explode( ',', $_REQUEST['jobman-delete-list'] );
	foreach( $deletes as $delete ) {
		if( empty( $delete ) )
			continue;

		wp_delete_attachment( $delete );

		unset( $options['icons'][array_search( $delete, $options['icons'] )] );

		// Remove the icon from any jobs that have it
		$jobs = get_posts( "post_type=jobman_job&meta_key=iconid&meta_value=$delete&numberposts=-1" );
		foreach( $jobs as $job ) {
			update_post_meta( $job->ID, 'iconid', '' );
		}
	}

	update_option( 'jobman_options', $options );
}

function jobman_users_updatedb() {
	$options = get_option( 'jobman_options' );

	$postnames = array( 'user-registration', 'user-registration-required', 'loginform-main', 'loginform-category', 'loginform-job', 'loginform-apply' );
	$optionnames = array( 'user_registration', 'user_registration_required', 'loginform_main', 'loginform_category', 'loginform_job', 'loginform_apply' );

	foreach( $postnames as $key => $var ) {
		if( array_key_exists( $var, $_REQUEST ) && $_REQUEST[$var] )
			$options[$optionnames[$key]] = 1;
		else
			$options[$optionnames[$key]] = 0;
	}

	update_option( 'jobman_options', $options );
}

function jobman_application_email_updatedb() {
	$options = get_option( 'jobman_options' );

	$options['application_email_from'] = $_REQUEST['jobman-from'];
	$options['application_email_subject_text'] = $_REQUEST['jobman-subject-text'];
	if( is_array( $_REQUEST['jobman-subject-fields'] ) )
		$options['application_email_subject_fields'] = $_REQUEST['jobman-subject-fields'];
	else
		$options['application_email_subject_fields'] = array();

	if( is_array( $_REQUEST['jobman-from-fields'] ) )
		$options['application_email_from_fields'] = $_REQUEST['jobman-from-fields'];
	else
		$options['application_email_from_fields'] = array();

	update_option( 'jobman_options', $options );
}

function jobman_interview_updatedb() {
	$options = get_option( 'jobman_options' );

	$options['interview_default_view'] = $_REQUEST['jobman-default-view'];
	$options['interview_title_text'] = $_REQUEST['jobman-title-text'];
	if( is_array( $_REQUEST['jobman-title-fields'] ) )
		$options['interview_title_fields'] = $_REQUEST['jobman-title-fields'];
	else
		$options['interview_title_fields'] = array();

	update_option( 'jobman_options', $options );
}

function jobman_api_keys_updatedb() {
	$options = get_option( 'jobman_options' );

	$postnames = array( 'google-maps' );
	$optionnames = array( 'google_maps' );

	foreach( $postnames as $key => $var ) {
		$options['api_keys'][$optionnames[$key]] = $_REQUEST[$var];
	}

	update_option( 'jobman_options', $options );
}

function jobman_other_plugins_updatedb() {
	$options = get_option( 'jobman_options' );

	if( array_key_exists( 'plugin-gxs', $_REQUEST ) && $_REQUEST['plugin-gxs'] )
		$options['plugins']['gxs'] = 1;
	else
		$options['plugins']['gxs'] = 0;

    if( array_key_exists( 'plugin-sicaptcha', $_REQUEST ) && $_REQUEST['plugin-sicaptcha'] )
        $options['plugins']['sicaptcha'] = 1;
    else
        $options['plugins']['sicaptcha'] = 0;

	update_option( 'jobman_options', $options );
}

function jobman_uninstall_updatedb() {
	$options = get_option( 'jobman_options' );

	$names = array( 'options', 'jobs', 'applications', 'categories' );

	foreach( $names as $var ) {
		if( array_key_exists( $var, $_REQUEST ) && $_REQUEST[$var] )
			$options['uninstall'][$var] = 1;
		else
			$options['uninstall'][$var] = 0;
	}

	update_option( 'jobman_options', $options );
}
?>