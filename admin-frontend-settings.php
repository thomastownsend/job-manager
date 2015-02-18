<?php
function jobman_display_conf() {
	if( array_key_exists( 'jobmandisplaysubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-display-updatedb' );
		jobman_display_updatedb();
	}
	else if( array_key_exists( 'jobmansortsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-sort-updatedb' );
		jobman_sort_updatedb();
	}
	else if( array_key_exists( 'jobmantemplatesubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-template-updatedb' );
		jobman_template_updatedb();
	}
	else if( array_key_exists( 'jobmanappformsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-appform-updatedb' );
		jobman_appform_updatedb();
	}
	else if( array_key_exists( 'jobmanapptemplatesubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-app-template-updatedb' );
		jobman_app_template_updatedb();
	}
	else if( array_key_exists( 'jobmanwraptextsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-wraptext-updatedb' );
		jobman_wrap_text_updatedb();
	}
	else if( array_key_exists( 'jobmanmisctextsubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-misctext-updatedb' );
		jobman_misc_text_updatedb();
	}
?>
	<div class="wrap">
<?php
	jobman_print_settings_tabs();
	
	if( ! get_option( 'smb_consulting' ) ) {
		$widths = array( '78%', '20%' );
		$functions = array(
						array( 'jobman_print_display_settings_box', 'jobman_print_sort_box', 'jobman_print_template_box', 'jobman_print_app_settings_box', 'jobman_print_app_template_box', 'jobman_print_misc_text_box', 'jobman_print_wrap_text_box' ),
						array( 'jobman_print_survey_box','jobman_print_donate_box', 'jobman_print_about_box' )
					);
		$titles = array(
					array( __( 'Display Settings', 'jobman' ), __( 'Job List Sorting', 'jobman' ), __( 'Job Templates', 'jobman' ), __( 'Application Form Settings', 'jobman' ), __( 'Application Form Template', 'jobman' ), __( 'Miscellaneous Text', 'jobman' ), __( 'Page Text', 'jobman' ) ),
					array( __( 'Job Manager - SURVEY -', 'jobman' ), __( 'Donate', 'jobman' ), __( 'About This Plugin', 'jobman' ))
				);
	}
	else {
		$widths = array( '49%', '49%' );
		$functions = array(
						array( 'jobman_print_display_settings_box', 'jobman_print_misc_text_box', 'jobman_print_wrap_text_box' ),
						array( 'jobman_print_sort_box', 'jobman_print_template_box', 'jobman_print_app_settings_box', 'jobman_print_app_template_box' )
					);
		$titles = array(
					array( __( 'Display Settings', 'jobman' ), __( 'Miscellaneous Text', 'jobman' ), __( 'Page Text', 'jobman' ) ),
					array( __( 'Job List Sorting', 'jobman' ), __( 'Job Templates', 'jobman' ), __( 'Application Form Settings', 'jobman' ), __( 'Application Form Template', 'jobman' ) )
				);
	}
	jobman_create_dashboard( $widths, $functions, $titles );
}

function jobman_print_display_settings_box() {
	$options = get_option( 'jobman_options' );
	?>
		<form action="" method="post">
		<input type="hidden" name="jobmandisplaysubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-display-updatedb' ); 
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Job Manager Page Template', 'jobman' ) ?></th>
				<td colspan="2"><?php printf( __( 'You can edit the page template used by Job Manager, by editing the Template Attribute of <a href="%s">this page</a>.', 'jobman' ), get_edit_post_link( $options['main_page'] ) ) ?></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Jobs Per Page', 'jobman' ) ?></th>
				<td><input type="text" name="jobs-per-page" class="small-text" value="<?php echo $options['jobs_per_page'] ?>" /></td>
				<td><span class="description"><?php _e( 'The number of jobs to display per page in the main and category jobs lists. For no limit, set this to 0.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Date Format', 'jobman' ) ?></th>
				<td><input type="text" name="date-format" class="small-text" value="<?php echo $options['date_format'] ?>" /></td>
				<td><span class="description"><?php printf( __( "The format to use for Job date fields. Leave blank to use the dates as they're entered. See the <a href='%1s'>documentation on date formatting</a> for further details.", 'jobman' ), 'http://codex.wordpress.org/Formatting_Date_and_Time' ) ?></span></td>
			</tr>
<?php
	if( ! get_option( 'smb_consulting' ) ) {
?>
			<tr>
				<th scope="row"><?php _e( 'Hide "Powered By" link?', 'jobman' ) ?></th>
				<td><input type="checkbox" value="1" name="promo-link" <?php echo ( $options['promo_link'] )?( 'checked="checked" ' ):( '' ) ?>/></td>
				<td><span class="description"><?php _e( "If you're unable to donate, we would appreciate it if you left this unchecked.", 'jobman' ) ?></span></td>
			</tr>
<?php
	}
?>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Display Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_sort_box() {
	$options = get_option( 'jobman_options' );
?>
		<form action="" method="post">
		<input type="hidden" name="jobmansortsubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-sort-updatedb' ); 
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Sort By:', 'jobman' ) ?></th>
				<td><select name="sort-by">
					<option value=""<?php echo ( '' == $options['sort_by'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Default', 'jobman' ) ?></option>
					<option value="title"<?php echo ( 'title' == $options['sort_by'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Job Title', 'jobman' ) ?></option>
					<option value="dateposted"<?php echo ( 'dateposted' == $options['sort_by'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Date Posted', 'jobman' ) ?></option>
					<option value="closingdate"<?php echo ( 'closingdate' == $options['sort_by'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Closing Date', 'jobman' ) ?></option>
<?php
	$fields = $options['job_fields'];
	uasort( $fields, 'jobman_sort_fields' );
	foreach( $fields as $fid => $field ) {
?>
					<option value="data<?php echo $fid ?>"<?php echo ( "data$fid" == $options['sort_by'] )?( ' selected="selected"' ):( '' ) ?>><?php printf( __( 'Custom Field: %1s', 'jobman' ), $field['label'] ) ?></option>
<?php
	}
?>
				</select></td>
				<td><span class="description"><?php _e( "Select the criteria you'd like to have job lists sorted by.", 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Sort Order', 'jobman' ) ?></th>
				<td><select name="sort-order">
					<option value=""<?php echo ( '' == $options['sort_order'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Default', 'jobman' ) ?></option>
					<option value="asc"<?php echo ( 'asc' == $options['sort_order'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Ascending', 'jobman' ) ?></option>
					<option value="desc"<?php echo ( 'desc' == $options['sort_order'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Descending', 'jobman' ) ?></option>
				</select></td>
				<td><span class="description">
					<?php _e( "Ascending: Lowest value to highest value, alphabetical or chronological order", 'jobman' ) ?><br/>
					<?php _e( "Descending: Highest value to lowest value, reverse alphabetical or chronological order", 'jobman' ) ?>
				</span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Highlighted jobs behaviour', 'jobman' ) ?></th>
				<td><select name="highlighted-behaviour">
					<option value="sticky"<?php echo ( 'sticky' == $options['highlighted_behaviour'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Sticky', 'jobman' ) ?></option>
					<option value="inline"<?php echo ( 'inline' == $options['highlighted_behaviour'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Inline', 'jobman' ) ?></option>
				</select></td>
				<td><span class="description">
					<?php _e( 'Sticky: Put highlighted jobs at the top of the jobs list.', 'jobman' ) ?><br/>
					<?php _e( 'Inline: Leave highlighted jobs in their normal place in the jobs list.', 'jobman' ) ?>
				</span></td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Sort Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_template_box() {
	$options = get_option( 'jobman_options' );
?>
		<p><?php _e( 'These settings allow you to define the templates for displaying lists of jobs, and individual jobs. To do this, you will need to make use of the available shortcodes.', 'jobman' ) ?></p>
		<p>
			<strong><?php _e( 'Job Information', 'jobman' ) ?></strong><br/>
			<tt>[job_loop]...[/job_loop]</tt> - <?php _e( 'This will loop over a list of all the Jobs, and display the contained HTML and shortcodes for each.', 'jobman' ) ?><br/>
			<tt>[job_id]</tt> - <?php _e( 'This will display the ID of the Job currently being displayed, either in a <tt>[job_loop]</tt> or on an Individual Job page.', 'jobman' ) ?><br/>
			<tt>[job_title]</tt> - <?php _e( 'This will display the Title of the current Job.', 'jobman' ) ?><br/>
			<tt>[job_row_number]</tt> - <?php _e( 'While inside a <tt>[job_loop]</tt>, this will display the row number of the job currently being displayed.', 'jobman' ) ?><br/>
			<tt>[job_odd_even]</tt> - <?php _e( 'While inside a <tt>[job_loop]</tt>, this will display "odd", if the current <tt>[job_row_number]</tt> is odd, or "even" if <tt>[job_row_number]</tt> is even.', 'jobman' ) ?></br>
			<tt>[job_highlighted]</tt> - <?php _e( 'If the current Job has been marked as highlighted, this will display the word "highlighted".', 'jobman' ) ?><br/>
			<tt>[job_icon]</tt> - <?php _e( 'If the current Job has an icon assigned to it, this will display the icon.', 'jobman' ) ?><br/>
			<tt>[job_link]...[/job_link]</tt> - <?php _e( 'This will display a link to the current Job, with the contained text as the link text.', 'jobman' ) ?><br/>
			<tt>[job_apply_link]...[/job_apply_link]</tt> - <?php _e( 'This will display a link to the Application Form for the current Job, with the contained text as the link text. If it is used outside of the <tt>[job_loop]</tt>, it will display a link to the main Application Form.', 'jobman' ) ?><br/><br/>
			
			<strong><?php _e( 'Job Category Information', 'jobman' ) ?></strong><br/>
			<tt>[job_categories]</tt> - <?php _e( 'If the current Job is assigned to any Categories, this will display a comma-separated list of the Category Titles.', 'jobman' ) ?><br/>
			<tt>[job_category_links]</tt> - <?php _e( 'If the current Job is assigned to any Categories, this will display a comma-separated list of the Category Titles, with each Title as a link to that Category.', 'jobman' ) ?><br/>
			<tt>[current_category_name]</tt> - <?php _e( 'This will display the category name, if the current job list is a category.', 'jobman' ) ?><br/>
			<tt>[current_category_link]...[/current_category_link]</tt> - <?php _e( 'This will display a link to the current category, with the contained text as the link text.', 'jobman' ) ?><br/><br/>
			
			<strong><?php _e( 'Page Navigation', 'jobman' ) ?></strong><br/>
			<tt>[job_page_count]</tt> - <?php _e( 'Returns the number of jobs that are being shown per page, or 0 for all of them.', 'jobman' ) ?><br/>
			<tt>[job_page_previous_link]...[/job_page_previous_link]</tt> - <?php _e( 'This will display a link to the previous page, with the contained text as the link text. If the user is on the first page, it will display nothing.', 'jobman' ) ?><br/>
			<tt>[job_page_previous_number]</tt> - <?php _e( 'Returns the page number of the previous page.', 'jobman' ) ?><br/>
			<tt>[job_page_next_link]...[/job_page_next_link]</tt> - <?php _e( 'This will display a link to the next page, with the contained text as the link text. If the user is on the last page, it will display nothing.', 'jobman' ) ?><br/>
			<tt>[job_page_next_number]</tt> - <?php _e( 'Returns the page number of the next page.', 'jobman' ) ?><br/>
			<tt>[job_page_current_number]</tt> - <?php _e( 'Returns the page number of the current page.', 'jobman' ) ?><br/>
			<tt>[job_page_minimum]</tt> - <?php _e( 'The job number of the first job being displayed on the current page.', 'jobman' ) ?><br/>
			<tt>[job_page_maximum]</tt> - <?php _e( 'The job number of the last job being displayed on the current page.', 'jobman' ) ?><br/>
			<tt>[job_total]</tt> - <?php _e( 'The total number of jobs over all pages of this list.', 'jobman' ) ?><br/><br/>

			<strong><?php _e( 'Job Field Information', 'jobman' ) ?></strong><br/>
			<tt>[job_field_loop]...[/job_field_loop]</tt> - <?php _e( 'This will loop over all of the defined Job Fields, and display the contained HTML and shortcodes for each. This can be used inside a <tt>[job_loop]</tt>, or on an Individual Job page.', 'jobman' ) ?><br/>
			<tt>[job_field_label]</tt> - <?php _e( 'While inside a <tt>[job_field_loop]</tt>, this will display the label of the current field being displayed.', 'jobman' ) ?><br/>
			<tt>[job_field]</tt> - <?php _e( 'While inside a <tt>[job_field_loop]</tt>, this will display the data associated with the current field and Job being displayed. If the field is a file, it will obey the <tt>type="url"</tt> attribute, to only return the URL, instead of a link to the file, or the <tt>type="image"</tt> attribute, to return an image. If the field is a large text field, it will obey the <tt>length="<em>n</em>"</tt> attribute, to restrict the output to <em>n</em> characters.', 'jobman' ) ?><br/><br/>
			
			<strong><?php _e( 'Custom Job Field Information', 'jobman' ) ?></strong><br/>
			<?php _e( "For each of the Custom Job Fields defined, there are two shortcodes defined, one for the Label and one for the Data. Note that these numbers won't change, even if you re-order, add or delete Job Fields.", 'jobman' ) ?><br/>
<?php
	$fields = $options['job_fields'];
	uasort( $fields, 'jobman_sort_fields' );
	foreach( $fields as $jfid => $field ) {
		echo "<tt>[job_field{$jfid}_label], [job_field{$jfid}]</tt> - {$field['label']} ";
		if( 'file' == $field['type'] )
			echo '(' . __( 'As a file field, the <tt>type="(url|image)"</tt> attribute can be used.', 'jobman' ) . ')';
		if( 'textarea' == $field['type'] )
			echo '(' . __( 'As a large text field, the <tt>length="<em>n</em>"</tt> attribute can be used.', 'jobman' ) . ')';
		echo '<br/>';
	}
?>
			<br/>
			
			<strong><?php _e( 'Conditionals', 'jobman' ) ?></strong><br/>
			<?php _e( 'All of the shortcodes defined above can be prefixed with <tt>if_</tt> to turn them into a conditional statement. For example, if you wanted to display the text "Categories: ", and then a list of the Categories a job is in, but you don\'t want to display it if there are no categories, you could put in the template:', 'jobman' ) ?><br/><br/>
			<code>[if_job_categories]<br/>
&nbsp;&nbsp;&nbsp;&nbsp;<?php _e( 'Categories', 'jobman' ) ?>: [job_categories]<br/>
[/if_job_categories]
			</code><br/><br/>
			
			<strong><?php _e( 'Multi-Applications', 'jobman' ) ?></strong><br/>
			<?php _e( 'These shortcodes are only valid if the "Allow Multi-Applications" option is checked under Admin Options. If it is not checked, they will not display.', 'jobman' ) ?><br/>
			<tt>[job_checkbox]</tt> - <?php _e( 'While inside a <tt>[job_field_loop]</tt>, this will display a checkbox associated with the current job.', 'jobman' ) ?><br/>
			<tt>[job_apply_multi]...[/job_apply_multi]</tt> - <?php _e( 'This will display a button to allow the applicant to apply for all checked jobs, with the contained text as the button text.', 'jobman' ) ?>
		</p>
		<form action="" method="post">
		<input type="hidden" name="jobmantemplatesubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-template-updatedb' ); 
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Job List Template', 'jobman' ) ?></th>
				<td><textarea name="job-list" class="large-text code" rows="7"><?php echo $options['templates']['job_list'] ?></textarea></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Individual Job Template', 'jobman' ) ?></th>
				<td><textarea name="job" class="large-text code" rows="7"><?php echo $options['templates']['job'] ?></textarea></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Template Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_app_settings_box() {
	$options = get_option( 'jobman_options' );
?>
		<form action="" method="post">
		<input type="hidden" name="jobmanappformsubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-appform-updatedb' ); 
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Category Selector', 'jobman' ) ?></th>
				<td><select name="app-cat-select">
					<option value=""<?php echo ( '' == $options['app_cat_select'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( "Don't Display", 'jobman' ) ?></option>
					<option value="select"<?php echo ( 'select' == $options['app_cat_select'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Dropdown', 'jobman' ) ?></option>
					<option value="individual"<?php echo ( 'individual' == $options['app_cat_select'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'HTML List', 'jobman' ) ?></option>
					<option value="popout"<?php echo ( 'popout' == $options['app_cat_select'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Popout', 'jobman' ) ?></option>
				</select></td>
				<td><span class="description">
					<?php _e( 'Allows an applicant to select a category or multiple categories to apply for. This will only display if the applicant is not applying for a specific job.', 'jobman' ) ?><br/>
					<?php _e( 'Dropdown: Shows a normal selector', 'jobman' ) ?><br/>
					<?php _e( 'HTML List: Shows a list of checkboxes', 'jobman' ) ?><br/>
					<?php _e( 'Popout: Shows a list of checkboxes when the list is clicked on', 'jobman' ) ?><br/>
				</span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Job Selector', 'jobman' ) ?></th>
				<td><select name="app-job-select">
					<option value=""<?php echo ( '' == $options['app_job_select'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( "Don't Display", 'jobman' ) ?></option>
					<option value="select"<?php echo ( 'select' == $options['app_job_select'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Dropdown', 'jobman' ) ?></option>
					<option value="individual"<?php echo ( 'individual' == $options['app_job_select'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'HTML List', 'jobman' ) ?></option>
					<option value="popout"<?php echo ( 'popout' == $options['app_job_select'] )?( ' selected="selected"' ):( '' ) ?>><?php _e( 'Popout', 'jobman' ) ?></option>
				</select></td>
				<td><span class="description">
					<?php _e( 'Allows an applicant to select a category or multiple categories to apply for. This will only display if the applicant is not applying for a specific job. On category application forms, it will only list jobs from that category.', 'jobman' ) ?><br/>
					<?php _e( 'Dropdown: Shows a normal selector', 'jobman' ) ?><br/>
					<?php _e( 'HTML List: Shows a list of radio buttons or checkboxes', 'jobman' ) ?><br/>
					<?php _e( 'Popout: Shows a list of radio buttons or checkboxes when the list is clicked on', 'jobman' ) ?><br/>
				</span></td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Application Form Options', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_app_template_box() {
	$options = get_option( 'jobman_options' );
?>
		<p><?php _e( "This setting allows you to define the template for displaying the application form. If you're happy with the current application form, just leave this blank, as you'll need to update it each time you add a new field to the application form.", 'jobman' ) ?></p>
		<p><?php _e( 'If you do want to do this, you will need to make use of the available shortcodes.', 'jobman' ) ?></p>
		<p>
			<strong><?php _e( 'Application Form', 'jobman' ) ?></strong><br/>
			<tt>[job_links]</tt> - <?php _e( 'Display a list of links to the jobs being applied for.', 'jobman' ) ?><br/>
			<tt>[job_list]</tt> - <?php _e( 'This will display a list of jobs to select from. If a category application form is being used, it will display all the jobs in that category. Otherwise, it will display all jobs. It has one optional attribute:', 'jobman' ) ?><br/>
			<tt>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type</tt> - <?php _e( 'Can be set to one of: "select", "individual" or "popout". "select" will show a dropdown box, "individual" will show a list with radio buttons or checkboxes, "popout" is the same as "individual", but only shows the list when it is clicked on.', 'jobman' ) ?><br/>
			<tt>[cat_list]</tt> - <?php _e( 'This will display a list of categories to select from. It has one optional attribute:', 'jobman' ) ?><br/>
			<tt>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;type</tt> - <?php _e( 'Can be set to one of: "select", "individual" or "popout". "select" will show a dropdown box, "individual" will show a list with checkboxes, "popout" is the same as "individual", but only shows the list when it is clicked on.', 'jobman' ) ?><br/>
			<tt>[job_app_submit]...[/job_app_submit]</tt> - <?php _e( 'Display a submit button for the application form, with the contained text as the button text.', 'jobman' ) ?><br/><br/>
			
			<strong><?php _e( 'Custom Application Field Information', 'jobman' ) ?></strong><br/>
			<?php _e( "For each of the Custom Job Fields defined, there are several shortcodes defined. Note that the numbers ('n' in the samples) won't change, even if you re-order, add or delete Application Fields.", 'jobman' ) ?><br/><br/>
			<tt>[job_app_field<em>n</em>_label]</tt> - <?php _e( 'Display the field label', 'jobman' ) ?><br/>
			<tt>[job_app_field<em>n</em>]</tt> - <?php _e( 'Display the field input element. ', 'jobman' ) ?><br/>
			<tt>[job_app_field<em>n</em>_mandatory]</tt> - <?php _e( 'If the field has been marked as mandatory, this will display the word "mandatory".', 'jobman' ) ?><br/><br/>
			
			<strong><?php _e( 'Custom Application Fields', 'jobman' ) ?></strong><br/>
<?php
	$fields = $options['fields'];
	uasort( $fields, 'jobman_sort_fields' );
	foreach( $fields as $fid => $field ) {
		$fieldlabel = '';
		if( ! empty( $field['label'] ) )
			$fieldlabel = $field['label'];
		elseif( ! empty( $field['data'] ) )
			$fieldlabel = $field['data'];
		else
			$fieldlabel = '(' . __( 'No Label', 'jobman' ) . ')';
		
		echo "<tt>[job_app_field{$fid}_label], [job_app_field{$fid}], [job_app_field{$fid}_mandatory]</tt> - $fieldlabel ({$field['type']})<br/>";
	}
?>
			<br/>
			
			<strong><?php _e( 'Conditionals', 'jobman' ) ?></strong><br/>
			<?php _e( 'All of the shortcodes defined above can be prefixed with <tt>if_</tt> to turn them into a conditional statement. For example, if you wanted to display an asterisk "*" next to the label of a mandatory field, you could put in the template:', 'jobman' ) ?><br/><br/>
			<code>[job_app_field1_label] [if_job_app_field1_mandatory]*[/if_job_app_field1_mandatory]</code>
		</p>
		<form action="" method="post">
		<input type="hidden" name="jobmanapptemplatesubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-app-template-updatedb' ); 
?>
		<textarea name="application-form" class="large-text code" rows="7"><?php echo $options['templates']['application_form'] ?></textarea>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Template Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_misc_text_box() {
	$options = get_option( 'jobman_options' );
?>
		<p><?php _e( "These text options will be displayed in various places around your job listings.", 'jobman' ) ?></p>
		<form action="" method="post">
		<input type="hidden" name="jobmanmisctextsubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-misctext-updatedb' ); 
?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Job Title Prefix', 'jobman' ) ?></th>
				<td><input type="text" name="job-title-prefix" class="regular-text code" value="<?php esc_attr_e( $options['text']['job_title_prefix'] ) ?>" /></td>
				<td><span class="description"><?php _e( 'This text is displayed before the Job Name in the page title.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Application Acceptance', 'jobman' ) ?></th>
				<td><textarea name="application-acceptance" class="large-text code" rows="7"><?php echo $options['text']['application_acceptance'] ?></textarea></td>
				<td><span class="description"><?php _e( "This text is displayed after an application has been accepted. If it is not filled in, the default text will be used.", 'jobman' ) ?></span></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Text Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_print_wrap_text_box() {
	$options = get_option( 'jobman_options' );
?>
		<p><?php _e( 'This text will be displayed before or after the lists/job/forms on the respective pages. You can enter HTML in these boxes.', 'jobman' ) ?></p>
		<form action="" method="post">
		<input type="hidden" name="jobmanwraptextsubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-wraptext-updatedb' ); 
?>
		<table class="form-table">
<?php
	$fields = array(
				'main' => array( 'before' => __( 'Before the Main Jobs List', 'jobman' ), 'after' => __( 'After the Main Jobs List', 'jobman' ) ),
				'category' => array( 'before' => __( 'Before any Category Jobs Lists', 'jobman' ), 'after' => __( 'After any Category Jobs Lists', 'jobman' ) ),
				'job' => array( 'before' => __( 'Before a Job', 'jobman' ), 'after' => __( 'After a Job', 'jobman' ) ),
				'apply' => array( 'before' => __( 'Before the Application Form', 'jobman' ), 'after' => __( 'After the Application Form', 'jobman' ) ),
				'registration' => array( 'before' => __( 'Before the Registration Form', 'jobman' ), 'after' => __( 'After the Registration Form', 'jobman' ) )
			);
	$positions = array( 'before', 'after' );
	foreach( $fields as $key => $field ) {
		foreach( $positions as $pos ) {
			$label = $field[$pos];
			$name = "{$key}-{$pos}";
			$value = $options['text']["{$key}_{$pos}"];
?>
			<tr>
				<th scope="row"><?php echo $label ?></th>
				<td><textarea name="<?php echo $name ?>" class="large-text code" rows="7"><?php echo $value ?></textarea></td>
			</tr>
<?php
		}
	}
?>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Text Settings', 'jobman' ) ?>" /></p>
		</form>
<?php
}

function jobman_display_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$options['jobs_per_page'] = $_REQUEST['jobs-per-page'];
	$options['date_format'] = $_REQUEST['date-format'];

	if( array_key_exists( 'promo-link', $_REQUEST ) && $_REQUEST['promo-link'] )
		$options['promo_link'] = 1;
	else
		$options['promo_link'] = 0;

	update_option( 'jobman_options', $options );
	
	if( $options['plugins']['gxs'] )
		do_action( 'sm_rebuild' );
}

function jobman_sort_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$options['sort_by'] = $_REQUEST['sort-by'];
	$options['sort_order'] = $_REQUEST['sort-order'];
	$options['highlighted_behaviour'] = $_REQUEST['highlighted-behaviour'];

	update_option( 'jobman_options', $options );
}

function jobman_template_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$options['templates']['job_list'] = stripslashes( $_REQUEST['job-list'] );
	$options['templates']['job'] = stripslashes( $_REQUEST['job'] );

	update_option( 'jobman_options', $options );
}

function jobman_appform_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$options['app_cat_select'] = $_REQUEST['app-cat-select'];
	$options['app_job_select'] = $_REQUEST['app-job-select'];

	update_option( 'jobman_options', $options );
}

function jobman_app_template_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$options['templates']['application_form'] = stripslashes( $_REQUEST['application-form'] );

	update_option( 'jobman_options', $options );
}

function jobman_misc_text_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$options['text']['job_title_prefix'] = stripslashes( $_REQUEST['job-title-prefix'] );
	$options['text']['application_acceptance'] = stripslashes( $_REQUEST['application-acceptance'] );

	update_option( 'jobman_options', $options );
}

function jobman_wrap_text_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$pages = array( 'main', 'category', 'job', 'apply', 'registration' );
	
	foreach( $pages as $page ) {
		$options['text']["{$page}_before"] = stripslashes( $_REQUEST["{$page}-before"] );
		$options['text']["{$page}_after"] = stripslashes( $_REQUEST["{$page}-after"] );
	}

	update_option( 'jobman_options', $options );
}

?>