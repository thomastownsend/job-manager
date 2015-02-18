<?php
function jobman_display_apply( $jobid, $cat = NULL ) {
	global $si_image_captcha;
	get_currentuserinfo();

	$options = get_option( 'jobman_options' );

	$content = '';

	$data = get_posts( 'post_type=jobman_app_form&numberposts=-1' );
	if( count( $data ) > 0) {
		$page = $data[0];
	}
	else {
		$page = new stdClass;
		$page->post_author = 1;
		$page->post_date = time();
		$page->post_type = 'page';
		$page->post_status = 'published';
	}

	if( array_key_exists( 'jobman-apply', $_REQUEST ) ) {

		if( isset( $si_image_captcha ) && $options['plugins']['sicaptcha'] ) {
			$fake_comment = array( 'comment_type' => 'comment' );
			// No need to check return - will wp_die() if CAPTCHA failed
			$si_image_captcha->si_captcha_comment_post( $fake_comment );
		}

		$err = jobman_store_application( $jobid, $cat );
		switch( $err ) {
			case -1:
				// No error, stored properly
				$msg = $options['text']['application_acceptance'];
				break;
			case -2:
				// Recent application form same job
				$msg = __( 'It seems you recently applied for this job. If you would like to add further information to your application, please contact us directly.', 'jobman' );
				break;
			default:
				if( is_array( $err ) ) {
					$msg = __( 'There was an error uploading your application. Please contact us directly, and quote the information below:', 'jobman' );
					foreach( $err as $e )
						$msg .= '<div class="jobman-error">' . esc_html( $e->get_error_message() ) . '</div>';
				}
				else {
					// Failed filter rules
					$msg = $options['fields'][$err]['error'];

					if( NULL == $msg || '' == $msg )
						$msg = __( "Thank you for your application. While your application doesn't fit our current requirements, please contact us directly to see if we have other positions available.", 'jobman' );
				}
				break;
		}

		$page->post_title = __( 'Job Application', 'jobman' );
		$page->post_content .= "<div class='jobman-message'>$msg</div>";

		return array( $page );
	}

	if( $options['user_registration'] && ( $options['loginform_apply'] || $options['user_registration_required'] ) )
		$content .= jobman_display_login();

	if( $options['user_registration'] && $options['user_registration_required'] && ! is_user_logged_in() ) {
		// Skip the application form if the user hasn't registered yet, and we're enforcing registration.

		$pleaseregister = '<p>' . __( 'Before completing your application, please login using the form above, or register using the form below.', 'jobman' ) . '</p>';

		$content .= apply_filters( 'jobman_pleaseregister_html', $pleaseregister );

		$reg = jobman_display_register();
		$content .= $reg[0]->post_content;

		$page->post_content = $content;

		return array( $page );
	}

	if( $jobid > 0 )
		$job = get_post( $jobid );
	else
		$job = NULL;

	$cat_arr = array();

	if( NULL != $job ) {
		$page->post_title = __( 'Job Application', 'jobman' ) . ': ' . $job->post_title;
		$foundjob = true;
		$jobid = $job->ID;

		$categories = wp_get_object_terms( $job->ID, 'jobman_category' );
		if( count( $categories ) > 0 ) {
			foreach( $categories as $category ) {
				$cat_arr[] = $category->slug;
			}
		}
	}
	else {
		$page->post_title = __( 'Job Application', 'jobman' );
		$foundjob = false;
		$jobid = -1;
		if( NULL != $cat ) {
			$data = get_term_by( 'slug', $cat, 'jobman_category' );
			if( isset( $data->slug ) )
				$cat_arr[] = $data->slug;
		}
	}

	$content .= '<form action="" enctype="multipart/form-data" onsubmit="return jobman_apply_filter()" method="post">';
	$content .= '<input type="hidden" name="jobman-apply" value="1" />';
	$content .= '<input type="hidden" name="jobman-jobid" value="' . $jobid . '" />';
	$content .= '<input type="hidden" name="jobman-categoryid" value="' . implode( ',', $cat_arr ) . '" />';

	if( array_key_exists( 'jobman-joblist', $_REQUEST ) )
		$content .= '<input type="hidden" name="jobman-joblist" value="' . implode( ',', $_REQUEST['jobman-joblist'] ) . '" />';

	if( empty( $options['templates']['application_form'] ) ) {
		$gencat = NULL;
		if( ! empty( $cat_arr ) )
			$gencat = $cat_arr[0];

		$content .= jobman_display_apply_generated( $foundjob, $job, $gencat );
	}
	else {
		global $jobman_app_field_shortcodes, $jobman_app_shortcodes, $jobman_shortcode_job, $jobman_shortcode_categories;

		$jobman_shortcode_job = $job;
		$jobman_shortcode_categories = $cat_arr;

		jobman_add_app_field_shortcodes( $jobman_app_field_shortcodes );
		jobman_add_app_shortcodes( $jobman_app_shortcodes );

		$content .= do_shortcode( $options['templates']['application_form'] );

		jobman_remove_shortcodes( array_merge( $jobman_app_field_shortcodes, $jobman_app_shortcodes ) );
	}

	$content .= '</form>';
	$content .= '<div id="jobman-map" style="width: 1px; height: 1px; display: none;"></div>';

	$page->post_content = $content;

	return array( $page );
}

function jobman_display_apply_generated( $foundjob = false, $job = NULL, $cat = NULL ) {
	global $current_user, $si_image_captcha, $wp_version;
	$options = get_option( 'jobman_options' );

	$content = '';

	if( ! empty( $cat ) )
		$cat = get_term_by( 'slug', $cat, 'jobman_category' );

	if( $foundjob )
		$content .= '<p>' . __( 'Title', 'jobman' ) . ': <a href="'. get_page_link( $job->ID ) . '">' . $job->post_title . '</a></p>';

	if( ! $foundjob ) {
		if( ! empty( $options['app_job_select'] ) )
			$content .= '<p><strong>' . __( 'Select the jobs you would like to apply for', 'jobman' ) . '</strong>: ' . jobman_generate_job_select( $cat, $options['app_job_select'] ) . '</p>';
		if( ! empty( $options['app_cat_select'] ) )
			$content .= '<p><strong>' . __( 'Select the categories that you are interested in', 'jobman' ) . '</strong>: ' . jobman_generate_cat_select( $cat, $options['app_cat_select'] ) . '</p>';
	}

	$fields = $options['fields'];

	$start = true;

	$content .= '<p>' . __( 'Fields marked with an asterisk (*) must be filled out before submitting.', 'jobman' ) . '</p>';

	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );
		$rowcount = 1;
		$totalrowcount = 1;
		$tablecount = 1;
		foreach( $fields as $id => $field ) {
			if( array_key_exists( 'categories', $field ) && count( $field['categories'] ) > 0 ) {
				// If there are cats defined for this field, check that either the job has one of those categories, or we're submitting to that category
				if( empty( $cat ) || ! in_array( $cat->term_id, $field['categories'] ) )
					continue;
			}

			if( $start && 'heading' != $field['type'] ) {
				$content .= "<table class='job-apply-table table$tablecount'>";
				$tablecount++;
				$rowcount = 1;
			}

			$data = trim( strip_tags( $field['data'] ) );

			// Auto-populate logged in user email address
			if( $id == $options['application_email_from'] && '' == $data && is_user_logged_in() ) {
			    $data = $current_user->user_email;
			}

			if( 'heading' != $field['type'] ) {
				$content .= "<tr class='row$rowcount totalrow$totalrowcount field$id ";
				$content .= ( $rowcount % 2 )?( 'odd' ):( 'even' );
				$content .= "'>";
			}

			$mandatory = '';
			if( $field['mandatory'] )
				$mandatory = ' *';

			switch( $field['type'] ) {
				case 'text':
				case 'radio':
				case 'checkbox':
				case 'textarea':
				case 'date':
				case 'file':
				case 'select':
				case 'geoloc':
					if( '' != $field['label'] )
						$content .= "<th scope='row'>{$field['label']}$mandatory</th>";
					else
						$content .= '<td class="th"></td>';

					$content .= '<td>' . jobman_app_field_input_html( $id, $field, $data, $mandatory ) . '</td></tr>';
					break;
				case 'heading':
					if( ! $start )
						$content .= '</table>';

					$content .= "<h3>{$field['label']}</h3>";
					$content .= "<table class='job-apply-table table$tablecount'>";
					$tablecount++;
					$totalrowcount--;
					$rowcount = 0;
					break;
				case 'html':
					$content .= '<td colspan="2">' . jobman_app_field_input_html( $id, $field, $data ) . '</td></tr>';
					break;
				case 'blank':
					$content .= '<td colspan="2">&nbsp;</td></tr>';
					break;
			}
			$start = false;
			$rowcount++;
			$totalrowcount++;
		}
	}

	$content .= '<tr><td colspan="2">&nbsp;</td></tr>';
	if( isset( $si_image_captcha ) && $options['plugins']['sicaptcha'] ) {
		// SI CAPTCHA echos directly to screen. We need to redirect that to our $content buffer.
		ob_start();
		if( $wp_version[0] > 2 )
			$si_image_captcha->si_captcha_comment_form_wp3();
		else
			$si_image_captcha->si_captcha_comment_form();
		$content .= '<tr><td colspan="2">' . ob_get_contents() . '</td></tr>';
		ob_end_clean();
	}

	$content .= '<tr><td colspan="2" class="submit"><input type="submit" name="submit"  class="button-primary" value="' . __( 'Submit Your Application', 'jobman' ) . '" /></td></tr>';
	$content .= '</table>';

	return $content;
}

function jobman_generate_job_select( $cat, $type = 'select' ) {
	$options = get_option( 'jobman_options' );

	if( is_object( $cat ) )
		$cat = $cat->slug;

	$sortby = '';
	switch( $options['sort_by'] ) {
		case 'title':
			$sortby = '&orderby=title';
			break;
		case 'dateposted':
			$sortby = '&orderby=date';
			break;
		case 'closingdate':
			$sortby = '&orderby=meta_value&meta_key=displayenddate';
			break;
	}

	$sortorder = '';
	if( in_array( $options['sort_order'], array( 'ASC', 'DESC' ) ) )
		$sortorder = '&order=' . $options['sort_order'];

	if( empty( $cat ) ) {
		$jobs = get_posts( "post_type=jobman_job&numberposts=-1$sortby$sortorder" );
	}
	else {
		$jobs = get_posts( "post_type=jobman_job&jcat=$cat&numberposts=-1$sortby$sortorder" );
	}

	foreach( $jobs as $id => $job ) {
		// Remove expired jobs
		$displayenddate = get_post_meta( $job->ID, 'displayenddate', true );
		if( '' != $displayenddate && strtotime( $displayenddate ) <= time() ) {
			unset( $jobs[$id] );
			continue;
		}

		// Remove future jobs
		$displaystartdate = $job->post_date;
		if( '' != $displaystartdate && strtotime( $displaystartdate ) > time() ) {
			unset( $jobs[$id] );
			continue;
		}
	}

	if( 'sticky' == $options['highlighted_behaviour'] )
		// Sort the sticky jobs to the top
		uasort( $jobs, 'jobman_sort_highlighted_jobs' );

	$content = '<span id="jobman-jobselect">';

	$inputtype = 'radio';
	$inputarray = '';
	$selectsize = 1;
	$selectmultiple = '';
	if( array_key_exists( 'multi_applications', $options ) && $options['multi_applications'] ) {
		$inputtype = 'checkbox';
		$inputarray = '[]';
		$selectsize = 5;
		$selectmultiple = ' multiple="multiple"';
	}

	$style = '';
	$class = '';
	$closebutton = '';
	if( 'popout' == $type ) {
		$style = 'display: none;';
		$class = 'jobselect-popout';
		$content .= '<span id="jobman-jobselect-echo"></span>';
		$closebutton = '<span id="jobman-jobselect-close"><a href="#">[x]</a></span>';
	}

	$selected_job = get_query_var( 'jobman_data' );

	switch( $type ) {
		case 'popout':
		case 'individual':
			$content .= "<span style='$style' class='$class'>";
			$content .= $closebutton;
			foreach( $jobs as $job ) {
				$checked = '';
				if( array_key_exists( 'jobman-joblist', $_REQUEST ) && in_array( $job->ID, $_REQUEST['jobman-joblist'] ) )
					$checked = ' checked="checked"';
				if( array_key_exists( 'jobman-jobid', $_REQUEST ) && $job->ID == $_REQUEST['jobman-jobid'] )
					$checked = ' checked="checked"';
				if( $job->ID == $selected_job )
					$checked = ' checked="checked"';
				$content .= "<span><label><input type='$inputtype' name='jobman-jobselect$inputarray' title='$job->post_title' value='$job->ID'$checked /> $job->post_title</label></span>";
			}
			$content .= '</span>';
			break;
		case 'select':
		default:
			$content .= "<select name='jobman-jobselect$inputarray'$selectmultiple>";
			$content .= '<option value="">' . __( 'None', 'jobman' ) . '</option>';
			foreach( $jobs as $job ) {
				$selected = '';
				if( array_key_exists( 'jobman-joblist', $_REQUEST ) && in_array( $job->ID, $_REQUEST['jobman-joblist'] ) )
					$selected = ' selected="selected"';
				if( array_key_exists( 'jobman-jobid', $_REQUEST ) && $job->ID == $_REQUEST['jobman-jobid'] )
					$selected = ' selected="selected"';
				$content .= "<option value='$job->ID'$selected>$job->post_title</option>";
			}
			$content .= '</select>';
	}

	$content .= '</span>';

	return $content;
}

function jobman_generate_cat_select( $cat, $type ) {
	$categories = get_terms( 'jobman_category', 'hide_empty=0' );

	$content = '<span id="jobman-catselect">';

	$style = '';
	$class = '';
	$closebutton = '';
	if( 'popout' == $type ) {
		$style = 'display: none;';
		$class = 'catselect-popout';
		$content .= '<span id="jobman-catselect-echo"></span>';
		$closebutton = '<span id="jobman-catselect-close"><a href="#">[x]</a></span>';
	}

	switch( $type ) {
		case 'popout':
		case 'individual':
			$content .= "<span style='$style' class='$class'>";
			$content .= $closebutton;
			foreach( $categories as $category ) {
				$checked = '';
				if( $category->slug == $cat )
					$checked = ' checked="checked"';
				$content .= "<span><input type='checkbox' name='jobman-catselect[]' title='$category->name' value='$category->slug'$checked /> $category->name</span>";
			}
			$content .= '</span>';
			break;
		case 'select':
		default:
			$content .= "<select name='jobman-catselect[]' multiple='multiple'>";
			$content .= '<option value="">' . __( 'None', 'jobman' ) . '</option>';
			foreach( $categories as $category ) {
				$selected = '';
				if( $category->slug == $cat )
					$selected = ' selected="selected"';
				$content .= "<option value='$category->slug'$selected>$category->name</option>";
			}
			$content .= '</select>';
	}

	$content .= '</span>';

	return $content;
}

function jobman_app_field_input_html( $id, $field, $data, $mandatory = '' ) {
	global $jobman_geoloc;
	$content = '';

	$data = esc_attr( $data );

	if( ! empty( $field['label'] ) )
		$mandatory = '';

	switch( $field['type'] ) {
		case 'text':
			return "<input type='text' name='jobman-field-$id' value='$data' />";
		case 'radio':
			$values = explode( "\n", $data );
			$display_values = explode( "\n", $field['data'] );

			foreach( $values as $key => $value ) {
				$content .= "$mandatory <input type='radio' name='jobman-field-$id' value='" . trim( $value ) . "' /> {$display_values[$key]}";
				if( count( $values ) > 1 )
					$content .= '<br/>';
			}
			return $content;
		case 'checkbox':
			$values = explode( "\n", $data );
			$display_values = explode( "\n", $field['data'] );

			foreach( $values as $key => $value ) {
				$content .= "$mandatory <input type='checkbox' name='jobman-field-{$id}[]' value='" . trim( $value ) . "' /> {$display_values[$key]}";
				if( count( $values ) > 1 )
					$content .= '<br/>';
			}
			return $content;
		case 'select':
			$values = explode( "\n", $data );
			$display_values = explode( "\n", $field['data'] );

			$content .= "$mandatory <select name='jobman-field-{$id}[]'>";
			foreach( $values as $key => $value ) {
				$content .= "<option value='" . trim( $value ) . "' /> {$display_values[$key]}</option>";
			}
			$content .= "</select>";
			return $content;
		case 'textarea':
			return "$mandatory <textarea name='jobman-field-$id'>{$field['data']}</textarea>";
		case 'date':
			return "$mandatory <input type='text' class='datepicker' name='jobman-field-$id' value='$data' />";
		case 'file':
			return "$mandatory <input type='file' name='jobman-field-$id' />";
		case 'geoloc':
			$jobman_geoloc = true;
			$content .= "<input type='hidden' class='jobman-geoloc-data' name='jobman-field-$id' />";
			$content .= "<input type='hidden' class='jobman-geoloc-original-display' name='jobman-field-original-display-$id' />";
			$content .= "$mandatory <input type='text' class='jobman-geoloc-display' name='jobman-field-display-$id' />";
			return $content;
		case 'html':
			return $field['data'];
		case 'heading':
		case 'blank':
		default:
			return NULL;
	}
}

function jobman_store_application( $jobid, $cat ) {
	global $current_user;
	get_currentuserinfo();

	$cat = get_term_by( 'slug', $cat, 'jobman_category' );

	$filter_err = jobman_check_filters( $jobid, $cat );
	if($filter_err != -1) {
		// Failed filter rules
		return $filter_err;
	}

	$dir = dirname( $_SERVER['SCRIPT_FILENAME'] );

	if( ! file_exists( "$dir/wp-admin/includes/file.php" ) )
		$dir = WP_CONTENT_DIR . '/..';

	require_once( "$dir/wp-admin/includes/file.php" );
	require_once( "$dir/wp-admin/includes/image.php" );
	require_once( "$dir/wp-admin/includes/media.php" );

	$options = get_option( 'jobman_options' );

	$fields = $options['fields'];

	$job = NULL;
	if( -1 != $jobid )
		$job = get_post( $jobid );

	// Workaround for WP to Twitter plugin tweeting about new application
	$_POST['jd_tweet_this'] = 'no';

	// Check for recent applications for the same job by the same user
	if( ! empty( $current_user ) && $current_user->ID > 0 && -1 != $jobid ) {
		$args = array(
					'post_status' => 'private',
					'post_type' => 'jobman_app',
					'author' => $current_user->ID,
					'meta_key' => 'job',
					'meta_value' => $jobid,
					'suppress_filters'  => false
				);

		add_filter( 'posts_where', 'jobman_dupe_app_check_where' );
		$posts = get_posts( $args );
		remove_filter( 'posts_where', 'jobman_dupe_app_check_where' );

		if( ! empty( $posts ) )
			return -2;
	}

	$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'private',
				'post_type' => 'jobman_app',
				'post_content' => '',
				'post_title' => __( 'Application', 'jobman' ),
				'post_parent' => $options['main_page']
			);

	$appid = wp_insert_post( $page );

	// Add the categories to the application
	$append = false;
	if( NULL != $cat && term_exists( $cat->slug, 'jobman_category' ) ) {
		wp_set_object_terms( $appid, $cat->slug, 'jobman_category', false );
		$append = true;
	}

	if( NULL != $job ) {
		// Get parent (job) categories, and apply them to application
		$parentcats = wp_get_object_terms( $job->ID, 'jobman_category' );
		foreach( $parentcats as $pcat ) {
			if( term_exists( $pcat->slug, 'jobman_category' ) ) {
				wp_set_object_terms( $appid, $pcat->slug, 'jobman_category', $append );
				$append = true;
			}
		}
	}

	if( array_key_exists( 'jobman-catselect', $_REQUEST ) && ! empty( $_REQUEST['jobman-catselect'] ) && is_array( $_REQUEST['jobman-catselect'] ) ) {
		// Get any categories selected from the category dropdown
		foreach( $_REQUEST['jobman-catselect'] as $bonuscat ) {
			if( term_exists( $bonuscat, 'jobman_category' ) ) {
				wp_set_object_terms( $appid, $bonuscat, 'jobman_category', $append );
				$append = true;
			}
		}
	}

	// Add the jobs to the application
	$jobs = array();
	if( -1 != $jobid )
		$jobs[] = $jobid;

	if( array_key_exists( 'jobman-joblist', $_REQUEST ) ) {
		$joblist = explode( ',', $_REQUEST['jobman-joblist'] );
		$jobs = array_merge( $jobs, $joblist );
	}

	// Add any extra jobs to the application
	if( array_key_exists( 'jobman-jobselect', $_REQUEST ) && ! empty( $_REQUEST['jobman-jobselect'] ) ) {
		if( is_array( $_REQUEST['jobman-jobselect'] ) )
			$jobs = array_merge( $jobs, $_REQUEST['jobman-jobselect'] );
		else
			$jobs[] = $_REQUEST['jobman-jobselect'];
	}

	$jobs = array_unique( $jobs );

	foreach( $jobs as $data ) {
		add_post_meta( $appid, 'job', $data, false );
	}

	$errors = array();

	if( count( $fields ) > 0 ) {
		foreach( $fields as $fid => $field ) {
			if($field['type'] != 'file' && ( ! array_key_exists( "jobman-field-$fid", $_REQUEST ) || '' == $_REQUEST["jobman-field-$fid"] ) )
				continue;

			if( 'file' == $field['type'] && ! array_key_exists( "jobman-field-$fid", $_FILES ) )
				continue;

			$data = '';
			switch( $field['type'] ) {
				case 'file':
					if( is_uploaded_file( $_FILES["jobman-field-$fid"]['tmp_name'] ) ) {
							$data = media_handle_upload( "jobman-field-$fid", $appid, array( 'post_status' => 'private' ) );
							if( is_wp_error( $data ) ) {
								// Upload failed, move to next field
								$errors[] = $data;
								continue 2;
							}

							add_post_meta( $data, '_jobman_attachment', 1, true );
							add_post_meta( $data, '_jobman_attachment_upload', 1, true );
					}
					break;
				case 'geoloc':
					if( $_REQUEST["jobman-field-original-display-$fid"] == $_REQUEST["jobman-field-display-$fid"] )
						$data = $_REQUEST["jobman-field-$fid"];
					else
						$data = $_REQUEST["jobman-field-display-$fid"];

					add_post_meta( $appid, "data-display$fid", $_REQUEST["jobman-field-display-$fid"], true );
					break;
				default:
					if( is_array( $_REQUEST["jobman-field-$fid"] ) )
						$data = implode( ', ', $_REQUEST["jobman-field-$fid"] );
					else
						$data = $_REQUEST["jobman-field-$fid"];
			}

			add_post_meta( $appid, "data$fid", $data, true );
		}
	}

	jobman_email_application( $appid );

	if( ! empty( $errors ) )
		return $errors;

	// No error
	return -1;
}

function jobman_dupe_app_check_where( $where = '' ) {
	$where .= " AND post_date > '" . date('Y-m-d H:i:s', strtotime('-5 minutes')) . "'";
	return $where;
}

function jobman_check_filters( $jobid, $cat ) {
	$options = get_option( 'jobman_options' );

	$fields = $options['fields'];

	$matches = array();
	if( count( $fields ) > 0 ) {
		foreach( $fields as $id => $field ) {
			if( '' == $field['filter'] && ! $field['mandatory'] )
				// No filter for this field, not mandatory
				continue;

			if( in_array( $field['type'], array( 'html', 'heading', 'blank' ) ) )
				// Not a field that we should be checking
				continue;

			if( array_key_exists( 'categories', $field ) && count( $field['categories'] ) > 0 ) {
				// If there are cats defined for this field, check that either the job has one of those categories, or we're submitting to that category
				if( empty( $cat ) || ! in_array( $cat->term_id, $field['categories'] ) )
					continue;
			}

			$used_eq = false;
			$eqflag = false;

			$data = '';
			if( array_key_exists( "jobman-field-$id", $_REQUEST ) )
				$data = $_REQUEST["jobman-field-$id"];

			if( is_array( $data ) ) {
				foreach( $data as $dataid => $datum )
					$data[$dataid] = esc_attr( trim( $datum ) );
			}
			else
				$data = esc_attr( trim( $data ) );

			// If the field is mandatory, check that there is data submitted
			if( $field['mandatory'] ) {
				if( 'file' == $field['type'] ) {
					if ( ! array_key_exists( "jobman-field-$id", $_FILES ) )
						return $id;
				}
				else if( '' == $data || ( is_array( $data ) && count( $data ) == 0 ) )
					return $id;
			}

			if( '' == $field['filter'] )
				// No filter for this field, and mandatory check has passed
				continue;

			if( 'file' == $field['type'] )
				// We can't filter files, so ignore any filter on it.
				continue;

			$filters = explode( "\n", $field['filter'] );

			foreach($filters as $filter) {
				$filter = trim( $filter );

				// Date
				if( 'date' == $field['type'] ) {
					$data = strtotime($data);

					// [<>][+-]P(\d+Y)?(\d+M)?(\d+D)?
					if( preg_match( '/^([<>])([+-])P(\d+Y)?(\d+M)?(\d+D)?$/', $filter, $matches ) ) {
						$intervalstr = $matches[2];
						for( $ii = 3; $ii < count($matches); $ii++ ) {
							$interval = array();
							preg_match( '/(\d+)([YMD])/', $matches[$ii], $interval );
							switch( $interval[2] ) {
								case 'Y':
									$intervalstr .= $interval[1] . ' years ';
									break;
								case 'M':
									$intervalstr .= $interval[1] . ' months ';
									break;
								case 'D':
									$intervalstr .= $interval[1] . ' days ';
									break;
							}
						}

						$cmp = strtotime( $intervalstr );

						switch( $matches[1] ) {
							case '<':
								if( $cmp > $data )
									return $id;
								break;
							case '>':
								if( $cmp < $data )
									return $id;
								break;
						}

						break;
					}
				}

				preg_match( '/^([<>]=?|[!]|)(.+)/', $filter, $matches );
				if( 'date' == $field['type'] )
					$fdata = strtotime($matches[2]);
				else
					$fdata = $matches[2];

				if( 'checkbox' != $field['type'] ) {
					switch( $matches[1] ) {
						case '<=':
							if( $data > $fdata )
								return $id;
							break;
						case '>=':
							if( $data > $fdata )
								return $id;
							break;
						case '<':
							if( $data >= $fdata )
								return $id;
							break;
						case '>':
							if( $data <= $fdata )
								return $id;
							break;
						case '!':
							if( $data == $fdata )
								return $id;
							break;
						default:
							$used_eq = true;
							if( $data == $fdata ) {
								$eqflag = true;
								break 2;
							}
							break;
					}
				}
				else {
					switch( $matches[1] ) {
						case '!':
							if( in_array( $fdata, $data ) )
								return $id;
							break;
						default:
							$used_eq = true;
							if( in_array( $fdata, $data ) ) {
								$eqflag = true;
								break 2;
							}
							break;
					}
				}
			}

			if( $used_eq && ! $eqflag )
				return $id;

			$used_eq = false;
			$eqflag = false;
		}
	}

	return -1;
}

function jobman_email_application( $appid, $sendto = '' ) {
	$options = get_option( 'jobman_options' );

	$app = get_post( $appid );
	if( NULL == $app )
		return;

	$parent = get_post( $app->ancestors[0] );
	$job_email = '';

	$jobs = get_post_meta( $app->ID, 'job', false );
	if( ! empty( $jobs ) ) {
		$job_emails = array();
		foreach( $jobs as $job ) {
			$je = get_post_meta( $job, 'email', true );
			if( ! empty( $je ) && ! in_array( $je, $job_emails ) )
				$job_emails[] = $je;
		}
		$job_email = implode( ',', $job_emails );
	}

	$appmeta = get_post_custom( $appid );

	$appdata = array();
	foreach( $appmeta as $key => $value ) {
		if( is_array( $value ) )
			$appdata[$key] = $value[0];
		else
			$appdata[$key] = $value;
	}

	$categories = wp_get_object_terms( $appid, 'jobman_category' );

	$to = '';
	if( '' != $sendto ) {
	    $to = $sendto;
	}
	else if( '' != $job_email ) {
	    $to = $job_email;
	}
	else if( count( $categories ) > 0 ) {
		$ii = 1;
		foreach( $categories as $cat ) {
			$to .= $cat->description;
			if( $ii < count( $categories ) )
				$to .= ', ';
		}
	}

	if( '' == $to )
		$to = $options['default_email'];

	if( '' == $to )
		return;

	$fromid = $options['application_email_from'];
	$from = '';

	if('' == $fromid )
		$from = $options['default_email'];
	else if( array_key_exists( "data$fromid", $appdata ) )
		$from = $appdata["data$fromid"];

	if( '' == $from )
		$from = get_option( 'admin_email' );

	$fids = $options['application_email_from_fields'];

	$fromname = '';
	if( count( $fids ) > 0 ) {
		foreach( $fids as $fid ) {
			if( array_key_exists( "data$fid", $appdata ) && '' != $appdata["data$fid"] )
				$fromname .= $appdata["data$fid"] . ' ';
		}
	}
	$fromname = trim( $fromname );

	$from = "\"$fromname\" <$from>";

	$subject = $options['application_email_subject_text'];
	if( ! empty( $subject ) )
		$subject .= ' ';

	$fids = $options['application_email_subject_fields'];

	if( count( $fids ) > 0 ) {
		foreach( $fids as $fid ) {
			if( array_key_exists( "data$fid", $appdata ) && '' != $appdata["data$fid"] )
				$subject .= $appdata["data$fid"] . ' ';
		}
	}

	trim( $subject );

	if( empty( $subject ) )
		$subject = __( 'Job Application', 'jobman' );

	$msg = '';

	$msg .= __( 'Application Link', 'jobman' ) . ': ' . admin_url( 'admin.php?page=jobman-list-applications&appid=' . $app->ID ) . PHP_EOL;

	$parents = get_post_meta( $app->ID, 'job', false );
	if( ! empty( $parents ) ) {
		$msg .= PHP_EOL;
		foreach( $parents as $parent ) {
			$data = get_post( $parent );
			$msg .= __( 'Job', 'jobman' ) . ': ' . $data->ID . ' - ' . $data->post_title . PHP_EOL;
			$msg .= get_page_link( $data->ID ) . PHP_EOL;
		}
		$msg .= PHP_EOL;
	}

	$msg .= __( 'Timestamp', 'jobman' ) . ': ' . $app->post_date . PHP_EOL . PHP_EOL;

	$fields = $options['fields'];

	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );
		foreach( $fields as $id => $field ) {
			// Don't include the field if it has no data
			if( ! array_key_exists("data$id", $appdata ) || '' == $appdata["data$id"] )
				continue;

			// Don't include the field if it has been blocked
			if( $field['emailblock'] )
				continue;

			switch( $field['type'] ) {
				case 'text':
				case 'radio':
				case 'checkbox':
				case 'date':
				case 'select':
					$msg .= $field['label'] . ': ' . $appdata['data'.$id] . PHP_EOL;
					break;
				case 'textarea':
					$msg .= $field['label'] . ':' . PHP_EOL . $appdata['data'.$id] . PHP_EOL;
					break;
				case 'file':
					$msg .= $field['label'] . ': ' . wp_get_attachment_url( $appdata["data$id"] ) . PHP_EOL;
					break;
				case 'geoloc':
					$msg .= $field['label'] . ': ' . $appdata['data-display'.$id] . ' (' . $appdata['data'.$id] . ')' . PHP_EOL;
					$msg .= 'http://maps.google.com/maps?q=' . urlencode( $appdata['data'.$id] ) . PHP_EOL;
					break;
			}
		}
	}

	$header = "From: $from" . PHP_EOL;
	$header .= "Reply-To: $from" . PHP_EOL;
	$header .= "Return-Path: $from" . PHP_EOL;
	$header .= 'Content-type: text/plain; charset='. get_option( 'blog_charset' ) . PHP_EOL;

	wp_mail( $to, $subject, $msg, $header );
}

?>