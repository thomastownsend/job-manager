<?php
function jobman_list_jobs() {
	$options = get_option( 'jobman_options' );
	$fields = $options['job_fields'];

	$displayed = 1;
	if( array_key_exists( 'jobman-mass-edit-jobs', $_REQUEST ) ) {
		if( 'delete' == $_REQUEST['jobman-mass-edit-jobs'] ) {
			if( array_key_exists( 'jobman-delete-confirmed', $_REQUEST ) ) {
				check_admin_referer( 'jobman-mass-delete-jobs' );
				jobman_job_delete();
			}
			else {
				check_admin_referer( 'jobman-mass-edit-jobs' );
				jobman_job_delete_confirm();
				return;
			}
		}
		else if( 'archive' == $_REQUEST['jobman-mass-edit-jobs'] ) {
			check_admin_referer( 'jobman-mass-edit-jobs' );
			jobman_job_archive();
		}
		else if( 'unarchive' == $_REQUEST['jobman-mass-edit-jobs'] ) {
			check_admin_referer( 'jobman-mass-edit-jobs' );
			jobman_job_unarchive();
		}
	}
	else if( isset( $_REQUEST['jobman-jobid'] ) ) {
		$displayed = jobman_edit_job( $_REQUEST['jobman-jobid'] );
		if( 1 == $displayed )
			return;
	}


?>
	<div class="wrap">
		<h2><?php _e( 'Job Manager: Jobs List', 'jobman' ) ?></h2>
		<form action="" method="post">
		<input type="hidden" name="jobman-jobid" value="new" />
		<p class="submit"><input type="submit" name="submit" class="button-primary" value="<?php _e( 'New Job', 'jobman' ) ?>" /></p>
		</form>
<?php
	switch($displayed) {
		case 0:
			echo '<div class="error">' . __( 'There is no job associated with that Job ID', 'jobman' ) . '</div>';
			break;
		case 2:
			echo '<div class="updated">' . __( 'New job created', 'jobman' ) . '</div>';
			break;
		case 3:
			echo '<div class="updated">' . __( 'Job updated', 'jobman' ) . '</div>';
			break;
		case 4:
			echo '<div class="error">' . __( 'You do not have permission to edit that Job', 'jobman' ) . '</div>';
			break;
	}

	$jobs = get_posts( 'post_type=jobman_job&numberposts=-1&post_status=publish,draft,future' );
?>
		<form action="" method="post">
<?php
	wp_nonce_field( 'jobman-mass-edit-jobs' );
?>
		<table id="jobman-jobs-list" class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" id="cb" class="column-cb check-column"><input type="checkbox"></th>
				<th scope="col"><?php _e( 'Title', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Categories', 'jobman' ) ?></th>
<?php
	$fieldcount = 0;
	if( count( $fields ) > 0 ) {
		foreach( $fields as $field ) {
			if( array_key_exists( 'listdisplay', $field ) && $field['listdisplay'] ) {
			$fieldcount++;
?>
				<th scope="col"><?php echo $field['label'] ?></th>
<?php
			}
		}
	}
?>
				<th scope="col"><?php _e( 'Display Dates', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Applications', 'jobman' ) ?></th>
			</tr>
			</thead>
<?php
	if( count( $jobs ) > 0 ) {
		$expired = jobman_list_jobs_data( $jobs, false );
		if( count( $expired ) ) {
?>
		<tr class="jobman-expired-jobs">
			<td colspan="<?php echo $fieldcount + 5 ?>"><?php _e( 'Expired jobs', 'jobman' ) ?></td>
		</tr>
<?php
		}
		jobman_list_jobs_data( $expired, true );
	}
	else {
		$fieldcount += 5;
?>
			<tr>
				<td colspan="<?php echo $fieldcount ?>"><?php _e( 'There are currently no jobs in the system.', 'jobman' ) ?></td>
			</tr>
<?php
	}
?>
		</table>
		<div class="alignleft actions">
			<select name="jobman-mass-edit-jobs">
				<option value=""><?php _e( 'Bulk Actions', 'jobman' ) ?></option>
				<option value="delete"><?php _e( 'Delete', 'jobman' ) ?></option>
				<option value="archive"><?php _e( 'Archive', 'jobman' ) ?></option>
				<option value="unarchive"><?php _e( 'Unarchive', 'jobman' ) ?></option>
			</select>
			<input type="submit" value="<?php _e( 'Apply', 'jobman' ) ?>" name="submit" class="button-secondary action" />
		</div>
		</form>
	</div>
<?php
}

function jobman_list_jobs_data( $jobs, $showexpired = false ) {
		global $current_user;

		if( ! is_array( $jobs ) || count( $jobs ) <= 0 )
			return;

		$options = get_option( 'jobman_options' );
		$fields = $options['job_fields'];

		wp_get_current_user();

		$expiredjobs = array();
		foreach( $jobs as $job ) {
			$cats = wp_get_object_terms( $job->ID, 'jobman_category' );
			$cats_arr = array();
			if( count( $cats ) > 0 ) {
				foreach( $cats as $cat ) {
					$cats_arr[] = $cat->name;
				}
			}
			$catstring = implode( ', ', $cats_arr );

			$displayenddate = get_post_meta( $job->ID, 'displayenddate', true );

			$display = false;
			if( ( '' == $displayenddate || strtotime( $displayenddate ) > time() ) && 'publish' == $job->post_status )
				$display = true;

			if( ! ( $display || $showexpired ) ) {
				$expiredjobs[] = $job;
				continue;
			}

			$future = false;
			if( strtotime( $job->post_date ) > time() )
				$future = true;

			$children = get_posts( "post_type=jobman_app&meta_key=job&meta_value=$job->ID&post_status=publish,private&numberposts=-1" );
			if( count( $children ) > 0 )
				$applications = '<a href="' . admin_url( "admin.php?page=jobman-list-applications&amp;jobman-jobid=$job->ID" ) . '">' . count( $children ) . '</a>';
			else
				$applications = 0;

			$class = "live";
			if( $future )
				$class = "future";
			elseif( ! $display )
				$class = "expired";
?>
			<tr class="<?php echo $class ?>">
				<th scope="row" class="check-column">
<?php
			if( current_user_can( 'edit_others_posts' ) || $job->post_author == $current_user->ID ) {
?>
				<input type="checkbox" name="job[]" value="<?php echo $job->ID ?>" />
<?php
			}
?>
				</th>
				<td class="post-title page-title column-title"><strong><a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $job->ID ?>"><?php echo $job->post_title ?></a></strong>
				<div class="row-actions">
<?php
			if( current_user_can( 'edit_others_posts' ) || $job->post_author == $current_user->ID ) {
?>
				<a href="?page=jobman-list-jobs&amp;jobman-jobid=<?php echo $job->ID ?>"><?php _e( 'Edit', 'jobman' ) ?></a> |
<?php
			}
?>
				<a href="<?php echo get_page_link( $job->ID ) ?>"><?php _e( 'View', 'jobman' ) ?></a>
<?php
			if( current_user_can( 'edit_others_posts' ) || $job->post_author == $current_user->ID ) {
				if( $display ) {
?>
				| <a href="<?php echo wp_nonce_url( admin_url( "admin.php?page=jobman-list-jobs&amp;jobman-mass-edit-jobs=archive&amp;job[]=$job->ID" ), 'jobman-mass-edit-jobs' ) ?>"><?php _e( 'Archive', 'jobman' ) ?></a>
<?php
				}
				else {
?>
				| <a href="<?php echo wp_nonce_url( admin_url( "admin.php?page=jobman-list-jobs&amp;jobman-mass-edit-jobs=unarchive&amp;job[]=$job->ID" ), 'jobman-mass-edit-jobs' ) ?>"><?php _e( 'Unarchive', 'jobman' ) ?></a>
<?php
				}
			}
?>
				</div></td>
				<td><?php echo $catstring ?></td>
<?php
			if( count( $fields ) ) {
				foreach( $fields as $id => $field ) {
					if( array_key_exists( 'listdisplay', $field ) && $field['listdisplay'] ) {
						$data = get_post_meta( $job->ID, "data$id", true );
						if( ! empty( $data ) ) {
							if( 'file' == $field['type'] )
								$data = '<a href="' . wp_get_attachment_url( $data ) . '">' . __( 'Download', 'jobman' ) . '</a>';
							else if( is_array( $data ) )
								$data = implode( ', ', $data );
						}
?>
				<td><?php echo $data ?></td>
<?php
					}
				}
			}
			$status = __( 'Live', 'jobman' );
			if( $future )
				$status = __( 'Future', 'jobman' );
			else if( ! $display )
				$status = __( 'Expired', 'jobman' );
?>
				<td><?php echo date( 'Y-m-d', strtotime( $job->post_date ) ) ?> - <?php echo ( '' == $displayenddate )?( __( 'End of Time', 'jobman' ) ):( $displayenddate ) ?><br/>
				<?php echo $status ?></td>
				<td><?php echo $applications ?></td>
			</tr>
<?php
		}
		return $expiredjobs;
}

function jobman_add_job() {
	jobman_edit_job( 'new' );
}

function jobman_edit_job( $jobid ) {
	global $wp_version;
	$options = get_option( 'jobman_options' );

	if( array_key_exists( 'jobmansubmit', $_REQUEST ) ) {
		// Job form has been submitted. Update the database.
		check_admin_referer( "jobman-edit-job-$jobid" );
		$error = jobman_updatedb();

		if( $error )
			return $error;
		else if ( 'new' == $jobid )
			return 2;
		else
			return 3;
	}

	if( 'new' == $jobid ) {
		$title = __( 'Job Manager: New Job', 'jobman' );
		$submit = __( 'Create Job', 'jobman' );
		$job = array();
		$display_jobid = __( 'New', 'jobman' );
	}
	else {
		$title = __( 'Job Manager: Edit Job', 'jobman' );
		$submit = __( 'Update Job', 'jobman' );

		$job = get_post( $jobid );
		if( NULL == $job )
			// No job associated with that id.
			return 0;

		$display_jobid = $jobid;
	}

	if( isset( $job->ID ) ) {
		$jobid = $job->ID;
		$jobmeta = get_post_custom( $job->ID );
		$jobcats = wp_get_object_terms( $job->ID, 'jobman_category' );
	}
	else {
		$jobmeta = array();
		$jobcats = array();
	}

	$icons = $options['icons'];

	$jobdata = array();
	foreach( $jobmeta as $key => $value ) {
		if( is_array( $value ) )
			$jobdata[$key] = $value[0];
		else
			$jobdata[$key] = $value;
	}

	if( user_can_richedit() && version_compare( $wp_version, '3.3-aortic-dissection', '<' ) ) {
			wp_tiny_mce( false, array( 'editor_selector' => 'jobman-editor' ) );
	}
?>
	<form action="<?php echo admin_url('admin.php?page=jobman-list-jobs') ?>" enctype="multipart/form-data" method="post">
	<input type="hidden" name="jobmansubmit" value="1" />
	<input type="hidden" name="jobman-jobid" value="<?php echo $jobid ?>" />
<?php
	wp_nonce_field( "jobman-edit-job-$jobid");
?>
	<div class="wrap">
		<h2><?php echo $title ?></h2>
		<table id="jobman-job-edit" class="form-table">
			<tr>
				<th scope="row"><?php _e( 'Job ID', 'jobman' ) ?></th>
				<td><?php echo $display_jobid ?></td>
				<td></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Categories', 'jobman' ) ?></th>
				<td><div class="jobman-categories-list">
<?php
	$categories = get_terms( 'jobman_category', 'hide_empty=0' );
	if( count( $categories ) > 0 ) {
		foreach( $categories as $cat ) {
			$checked = '';
			if( 'new' != $jobid ) {
				foreach( $jobcats as $jobcat ) {
					if( $cat->term_id == $jobcat->term_id ) {
						$checked = ' checked="checked"';
						break;
					}
				}
			}
?>
					<input type="checkbox" name="jobman-categories[]" value="<?php echo $cat->slug ?>"<?php echo $checked ?> /> <?php echo $cat->name ?><br/>
<?php
		}
	}
?>
				</div></td>
				<td><span class="description"><?php _e( 'Categories that this job belongs to. It will be displayed in the job list for each category selected.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Icon', 'jobman' ) ?></th>
				<td><div class="jobman-icons-list">
<?php
	if( count( $icons ) > 0 ) {
		foreach( $icons as $icon ) {
			if( isset( $jobdata['iconid'] ) && $icon == $jobdata['iconid'] )
				$checked = ' checked="checked"';
			else
				$checked = '';

			$post = get_post( $icon );
?>
					<input type="radio" name="jobman-icon" value="<?php echo $icon ?>"<?php echo $checked ?> /> <img src="<?php echo wp_get_attachment_url( $icon ) ?>" /> <?php echo $post->post_title ?><br/>
<?php
		}
	}

	if( ! isset( $jobdata['iconid'] ) || 0 == $jobdata['iconid'] )
		$checked = ' checked="checked"';
	else
		$checked = '';
?>
					<input type="radio" name="jobman-icon"<?php echo $checked ?> value="" /> <?php _e( 'No Icon', 'jobman' ) ?><br/>
				</div></td>
				<td><span class="description"><?php _e( 'Icon to display for this job in the Job List', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Title', 'jobman' ) ?></th>
				<td><input class="regular-text code" type="text" name="jobman-title" value="<?php echo ( isset( $job->post_title ) )?( $job->post_title ):( '' ) ?>" /></td>
				<td></td>
			</tr>
<?php
	$fields = $options['job_fields'];
	$content = '';
	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );
		foreach( $fields as $id => $field ) {
			if( 'new' == $jobid )
				$data = $field['data'];
			else if( array_key_exists( "data$id", $jobdata ) )
				$data = $jobdata["data$id"];
			else
				$data = '';

			if( 'heading' != $field['type'] )
				echo '<tr>';

			if( ! array_key_exists( 'description', $field ) )
				$field['description'] = '';

			switch( $field['type'] ) {
				case 'text':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th>";
					else
						echo '<td class="th"></td>';

					echo "<td><input type='text' name='jobman-field-$id' value='$data' /></td>";
					echo "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'radio':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th><td>";
					else
						echo '<td class="th"></td><td>';

					$values = explode( "\n", strip_tags( $field['data'] ) );
					$display_values = explode( "\n", $field['data'] );

					foreach( $values as $key => $value ) {
						$checked = '';
						if( $value == $data )
							$checked = ' checked="checked"';
						echo "<input type='radio' name='jobman-field-$id' value='" . trim( $value ) . "'$checked /> {$display_values[$key]}<br/>";
					}
					echo '</td>';
					echo "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'checkbox':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th><td>";
					else
						echo '<td class="th"></td><td>';

					$values = explode( "\n", strip_tags( $field['data'] ) );
					$display_values = explode( "\n", $field['data'] );

					if( 'new' == $jobid )
						$data = array();
					else
						$data = explode( "\n", strip_tags( $data ) );

					foreach( $values as $key => $value ) {
						$value = trim( $value );
						$checked = '';
						if( in_array( $value, $data ) )
							$checked = ' checked="checked"';
						echo "<input type='checkbox' name='jobman-field-{$id}[]' value='$value'$checked /> {$display_values[$key]}<br/>";
					}
					echo '</td>';
					echo "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'textarea':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th>";
					else
						echo '<td class="th"></td>';

					if( '' == $field['description'] )
						echo "<td colspan='2'>";
					else
						echo '<td>';

					if( user_can_richedit() && version_compare( $wp_version, '3.3-aortic-dissection', '<' )) {
						echo "<p id='field-toolbar-$id' class='jobman-editor-toolbar'><a class='toggleHTML'>" . __( 'HTML', 'jobman' ) . '</a><a class="active toggleVisual">' . __( 'Visual', 'jobman' ) . '</a></p>';
						echo "<textarea class='large-text code jobman-editor jobman-field-$id' name='jobman-field-$id' id='jobman-field-$id' rows='7'>$data</textarea></td>";
					}
					else {
						$settings = array(
							'editor_class' => "large-text code jobman-editor jobman-field-$id"
						);
						wp_editor( $data, "jobman-field-$id", $settings );
					}

					if( '' == $field['description'] )
						echo '</tr>';
					else
						echo "<td><span class='description'>{$field['description']}</span></td></tr>";

					break;
				case 'date':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th>";
					else
						echo '<td class="th"></td>';

					echo "<td><input type='text' class='datepicker' name='jobman-field-$id' value='$data' /></td>";
					echo "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'file':
					if( '' != $field['label'] )
						echo "<th scope='row'>{$field['label']}</th>";
					else
						echo '<td class="th"></td>';

					echo '<td>';
					echo "<input type='file' name='jobman-field-$id' />";

					if( ! empty( $data ) ) {
						echo '<br/><a href="' . wp_get_attachment_url( $data ) . '">' . wp_get_attachment_url( $data ) . '</a>';
						echo "<input type='hidden' name='jobman-field-current-$id' value='$data' />";
						echo "<br/><input type='checkbox' name='jobman-field-delete-$id' value='1' />" . __( 'Delete File?', 'jobman' );
					}

					echo "</td>";
					echo "<td><span class='description'>{$field['description']}</span></td></tr>";
					break;
				case 'heading':
					echo '</table>';
					echo "<h3>{$field['label']}</h3>";
					echo "<table>";
					$tablecount++;
					$totalrowcount--;
					$rowcount = 0;
					break;
				case 'html':
					echo "<td colspan='3'>$data</td></tr>";
					break;
				case 'blank':
					echo '<td colspan="3">&nbsp;</td></tr>';
					break;
			}

			$previd = "jobman-field-$id";
		}
	}
?>
			<tr>
				<th scope="row"><?php _e( 'Display Start Date', 'jobman' ) ?></th>
				<td><input class="datepicker" type="text" name="jobman-displaystartdate" value="<?php echo ( 'new' != $jobid )?( date( 'Y-m-d', strtotime( $job->post_date ) ) ):( '' ) ?>" /></td>
				<td><span class="description"><?php _e( 'The date this job should start being displayed on the site. To start displaying immediately, leave blank.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Display End Date', 'jobman' ) ?></th>
				<td><input class="datepicker" type="text" name="jobman-displayenddate" value="<?php echo ( array_key_exists( 'displayenddate', $jobdata ) )?( $jobdata['displayenddate'] ):( '' ) ?>" /></td>
				<td><span class="description"><?php _e( 'The date this job should stop being displayed on the site. To display indefinitely, leave blank.', 'jobman' ) ?></span></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Application Email', 'jobman' ) ?></th>
				<td><input class="regular-text" type="text" name="jobman-email" value="<?php echo ( array_key_exists( 'email', $jobdata ) )?( $jobdata['email'] ):( '' ) ?>" /></td>
				<td><span class="description"><?php _e( 'The email address to notify when an application is submitted for this job. For default behaviour (category email or global email), leave blank.', 'jobman' ) ?></span></td>
			</tr>
<?php
	$checked = '';
	if( array_key_exists( 'highlighted', $jobdata ) && $jobdata['highlighted'] )
		$checked = ' checked="checked"';
?>
			<tr>
				<th scope="row"><?php _e( 'Highlighted?', 'jobman' ) ?></th>
				<td><input type="checkbox" name="jobman-highlighted" value="1" <?php echo $checked ?>/></td>
				<td><span class="description"><?php _e( 'Mark this job as highlighted? For the behaviour of highlighted jobs, see the Display Settings admin page.', 'jobman' ) ?></span></td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php echo $submit ?>" /></p>
	</div>
	</form>
<?php
	return 1;
}

function jobman_updatedb() {
	global $wpdb, $current_user;
	$options = get_option( 'jobman_options' );

	wp_get_current_user();

	if( array_key_exists( 'jobman-displaystartdate', $_REQUEST ) && ! empty( $_REQUEST['jobman-displaystartdate'] ) )
		$displaystartdate = date( 'Y-m-d H:i:s', strtotime( stripslashes( $_REQUEST['jobman-displaystartdate'] ) ) );
	else
		$displaystartdate = date( 'Y-m-d H:i:s' );

	$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'post_content' => '',
				'post_name' => strtolower( str_replace( ' ', '-', $_REQUEST['jobman-title'] ) ),
				'post_title' => stripslashes( html_entity_decode( $_REQUEST['jobman-title'] ) ),
				'post_type' => 'jobman_job',
				'post_date' => $displaystartdate,
				'post_date_gmt' => $displaystartdate,
				'post_parent' => $options['main_page']);

	if( 'new' == $_REQUEST['jobman-jobid'] ) {
		$id = wp_insert_post( $page );

		$fields = $options['job_fields'];
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
							$upload = wp_upload_bits( $_FILES["jobman-field-$fid"]['name'], NULL, file_get_contents( $_FILES["jobman-field-$fid"]['tmp_name'] ) );
							$filetype = wp_check_filetype( $upload['file'] );
							if( ! $upload['error'] ) {
								$attachment = array(
												'post_title' => '',
												'post_content' => '',
												'post_status' => 'publish',
												'post_mime_type' => $filetype['type']
											);
								$data = wp_insert_attachment( $attachment, $upload['file'], $id );
								$attach_data = wp_generate_attachment_metadata( $data, $upload['file'] );
								wp_update_attachment_metadata( $data, $attach_data );
							}
						}
						break;
					case 'checkbox':
						$data = implode( ', ', $_REQUEST["jobman-field-$fid"] );
						break;
					default:
						$data = $_REQUEST["jobman-field-$fid"];
				}

				add_post_meta( $id, "data$fid", $data, true );
			}
		}

		add_post_meta( $id, 'displayenddate', stripslashes( $_REQUEST['jobman-displayenddate'] ), true );
		add_post_meta( $id, 'iconid', $_REQUEST['jobman-icon'], true );
		add_post_meta( $id, 'email', $_REQUEST['jobman-email'], true );

		if( array_key_exists( 'jobman-highlighted', $_REQUEST ) && $_REQUEST['jobman-highlighted'] )
			add_post_meta( $id, 'highlighted', 1, true );
		else
			add_post_meta( $id, 'highlighted', 0, true );
	}
	else {
		$data = get_post( $_REQUEST['jobman-jobid'] );

		if( ! current_user_can( 'edit_others_posts' ) && $data->post_author != $current_user->ID )
			return 4;

		$page['ID'] = $_REQUEST['jobman-jobid'];
		$id = wp_update_post( $page );

		$fields = $options['job_fields'];
		if( count( $fields ) > 0 ) {
			foreach( $fields as $fid => $field ) {
				if( 'file' == $field['type'] && ! array_key_exists( "jobman-field-$fid", $_FILES ) && ! array_key_exists( "jobman-field-delete-$fid", $_REQUEST ) )
					continue;

				$data = '';
				switch( $field['type'] ) {
					case 'file':
						if( array_key_exists( "jobman-field-delete-$fid", $_REQUEST ) ) {
							wp_delete_attachment( $_REQUEST["jobman-field-current-$fid"] );
						}
						else if( is_uploaded_file( $_FILES["jobman-field-$fid"]['tmp_name'] ) ) {
							$upload = wp_upload_bits( $_FILES["jobman-field-$fid"]['name'], NULL, file_get_contents( $_FILES["jobman-field-$fid"]['tmp_name'] ) );
							if( ! $upload['error'] ) {
								// Delete the old attachment
								if( array_key_exists( "jobman-field-current-$fid", $_REQUEST ) )
									wp_delete_attachment( $_REQUEST["jobman-field-current-$fid"] );
								$filetype = wp_check_filetype( $upload['file'] );
								$attachment = array(
												'post_title' => '',
												'post_content' => '',
												'post_status' => 'publish',
												'post_mime_type' => $filetype['type']
											);
								$data = wp_insert_attachment( $attachment, $upload['file'], $id );
								$attach_data = wp_generate_attachment_metadata( $data, $upload['file'] );
								wp_update_attachment_metadata( $data, $attach_data );
							}
							else {
								$data = get_post_meta( $id, "data$fid", true );
							}
						}
						else {
							$data = get_post_meta( $id, "data$fid", true );
						}
						break;
					case 'checkbox':
						if( array_key_exists( "jobman-field-$fid", $_REQUEST ) && is_array( $_REQUEST["jobman-field-$fid"] ) )
							$data = implode( ', ', $_REQUEST["jobman-field-$fid"] );
						break;
					default:
						if( array_key_exists( "jobman-field-$fid", $_REQUEST ) )
							$data = $_REQUEST["jobman-field-$fid"];
				}

				update_post_meta( $id, "data$fid", $data );
			}
		}


		update_post_meta( $id, 'displayenddate', stripslashes( $_REQUEST['jobman-displayenddate'] ) );
		update_post_meta( $id, 'iconid', $_REQUEST['jobman-icon'] );
		update_post_meta( $id, 'email', $_REQUEST['jobman-email'] );

		if( array_key_exists( 'jobman-highlighted', $_REQUEST ) && $_REQUEST['jobman-highlighted'] )
			update_post_meta( $id, 'highlighted', 1 );
		else
			update_post_meta( $id, 'highlighted', 0 );
	}

	if( array_key_exists( 'jobman-categories', $_REQUEST ) )
		wp_set_object_terms( $id, $_REQUEST['jobman-categories'], 'jobman_category', false );

	if( $options['plugins']['gxs'] )
		do_action( 'sm_rebuild' );

	return 0;
}

function jobman_job_delete_confirm() {
?>
	<div class="wrap">
	<form action="" method="post">
	<input type="hidden" name="jobman-delete-confirmed" value="1" />
	<input type="hidden" name="jobman-mass-edit-jobs" value="delete" />
	<input type="hidden" name="jobman-job-ids" value="<?php echo implode( ',', $_REQUEST['job'] ) ?>" />
<?php
	wp_nonce_field( 'jobman-mass-delete-jobs' );
?>
		<h2><?php _e( 'Job Manager: Jobs', 'jobman' ) ?></h2>
		<p class="error"><?php _e( 'This will permanently delete all of the selected jobs. Please confirm that you want to continue.', 'jobman' ) ?></p>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Delete Jobs', 'jobman' ) ?>" /></p>
	</form>
	</div>
<?php
}

function jobman_job_delete() {
	$options = get_option( 'jobman_options' );

	$jobs = explode( ',', $_REQUEST['jobman-job-ids'] );

	// Get the file fields
	$file_fields = array();
	foreach( $options['job_fields'] as $id => $field ) {
		if( 'file' == $field['type'] )
			$file_fields[] = $id;
	}

	foreach( $jobs as $job ) {
		// Remove reference from applications
		$apps = get_posts( 'post_type=jobman_app&numberposts=-1&meta_key=job&meta_value=' . $job );
		if( ! empty( $apps ) ) {
			foreach( $apps as $app ) {
				delete_post_meta( $app->ID, 'job', $job );
			}
		}

		$jobmeta = get_post_custom( $job );
		$jobdata = array();
		if( is_array( $jobmeta ) ) {
			foreach( $jobmeta as $key => $value ) {
				if( is_array( $value ) )
					$jobdata[$key] = $value[0];
				else
					$jobdata[$key] = $value;
			}
		}

		// Delete any files uploaded
		foreach( $file_fields as $fid ) {
			if( array_key_exists( "data$fid", $jobdata )  && '' != $jobdata["data$fid"] )
				wp_delete_post( $jobdata["data$fid"] );
		}
		// Delete the job
		wp_delete_post( $job );
	}
}

function jobman_job_archive() {
	$jobs = $_REQUEST['job'];

	if( ! is_array( $jobs ) )
		return;

	$data = array( 'post_status' => 'draft' );
	foreach( $jobs as $job ) {
		$data['ID'] = $job;
		wp_update_post( $data );
	}
}

function jobman_job_unarchive() {
	$jobs = $_REQUEST['job'];

	if( ! is_array( $jobs ) )
		return;

	$data = array( 'post_status' => 'publish' );
	foreach( $jobs as $job ) {
		$data['ID'] = $job;
		$data['post_date'] = date( 'Y-m-d H:i:s', strtotime( '-1 day' ) );
		$data['post_date_gmt'] = date( 'Y-m-d H:i:s', strtotime( '-1 day' ) );
		wp_update_post( $data );

		update_post_meta( $job, 'displayenddate', '' );
	}
}

?>