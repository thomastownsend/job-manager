<?php
function jobman_list_applications() {
	global $wpdb;
	$options = get_option( 'jobman_options' );

	$deleted = false;
	$emailed = false;
	if(array_key_exists( 'jobman-mass-edit', $_REQUEST ) && 'delete' == $_REQUEST['jobman-mass-edit'] ) {
		if( array_key_exists( 'jobman-delete-confirmed', $_REQUEST ) ) {
			check_admin_referer( 'jobman-mass-delete-applications' );
			jobman_application_delete();
			$deleted = true;
		}
		else {
			check_admin_referer( 'jobman-mass-edit-applications' );
			jobman_application_delete_confirm();
			return;
		}
	}
	else if( array_key_exists( 'jobman-mass-edit', $_REQUEST ) && 'email' == $_REQUEST['jobman-mass-edit'] ) {
		check_admin_referer( 'jobman-mass-edit-applications' );
		jobman_application_mailout();
		return;
	}
	else if(array_key_exists( 'appid', $_REQUEST ) ) {
		jobman_application_details_layout( $_REQUEST['appid'] );
		return;
	}
	else if( array_key_exists( 'jobman-mailout-send', $_REQUEST ) ) {
		check_admin_referer( 'jobman-mailout-send' );
		jobman_application_mailout_send();
		$emailed = true;
	}
?>
	<div class="wrap">
		<h2><?php _e( 'Job Manager: Applications', 'jobman' ) ?></h2>
<?php
	if( $deleted )
		echo '<p class="error">' . __( 'Selected applications have been deleted.', 'jobman' ) . '</p>';
	if( $emailed )
		echo '<p class="error">' . __( 'The mailout has been sent.', 'jobman' ) . '</p>';

	$fields = $options['fields'];

	$categories = get_terms( 'jobman_category', 'hide_empty=0' );
?>
		<div id="jobman-filter">
		<form action="" method="post">
			<div class="jobman-filter-normal">
				<h4><?php _e( 'Standard Filters', 'jobman' ) ?></h4>
				<table>
					<tr>
						<th scope="row"><?php _e( 'Job ID', 'jobman' ) ?>:</th>
						<td><input type="text" name="jobman-jobid" value="<?php echo ( array_key_exists( 'jobman-jobid', $_REQUEST ) )?( htmlspecialchars($_REQUEST['jobman-jobid']) ):( '' ) ?>" /></td>
					</tr>
<?php
	if( $options['user_registration'] ) {
?>
					<tr>
						<th scope="row"><?php _e( 'Registered Applicant', 'jobman' ) ?>:</th>
						<td><input type="text" name="jobman-applicant" value="<?php echo ( array_key_exists( 'jobman-applicant', $_REQUEST ) )?( htmlspecialchars($_REQUEST['jobman-applicant']) ):( '' ) ?>" /></td>
					</tr>
<?php
	}

	if( count( $categories ) > 0 ) {
?>
					<tr>
						<th scope="row"><?php _e( 'Categories', 'jobman' ) ?>:</th>
						<td><div class="jobman-categories-list">
<?php
		$ii = 0;
		foreach( $categories as $cat ) {
			$checked = '';
			if( array_key_exists( 'jobman-categories', $_REQUEST ) && is_array( $_REQUEST['jobman-categories'] ) && in_array( $cat->term_id, $_REQUEST['jobman-categories'] ) )
				$checked = ' checked="checked"';
?>
							<input type="checkbox" name="jobman-categories[]" value="<?php echo esc_attr( $cat->term_id ) ?>"<?php echo $checked ?> /> <?php echo $cat->name ?><br/>
<?php
		}
?>
						</div></td>
					</tr>
<?php
	}

	$rating = 0;
	if( array_key_exists( 'jobman-rating', $_REQUEST ) )
	    $rating = $_REQUEST['jobman-rating'];
?>
					<tr>
					    <th scope="row"><?php _e( 'Minimum Rating', 'jobman' ) ?>:</th>
					    <td>
<?php
	jobman_print_rating_stars( 'filter', $rating );
?>
						</td>
					</tr>
				</table>
			</div>
			<div class="jobman-filter-custom">
				<h4><?php _e( 'Custom Filters', 'jobman' ) ?></h4>
<?php
	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );
?>
				<table class="widefat page fixed" cellspacing="0">
					<thead>
					<tr>
<?php
		$fieldcount = 0;
		foreach( $fields as $id => $field ) {
			if( $field['listdisplay'] ) {
				$fieldcount++;
?>
						<th scope="col"><?php echo $field['label'] ?></th>
<?php
			}
		}
?>
					</tr>
					</thead>
<?php
		echo '<tr>';
		foreach( $fields as $id => $field ) {
			if( ! $field['listdisplay'] )
				continue;

			$req_value = '';
			if( array_key_exists( "jobman-field-$id", $_REQUEST ) )
				$req_value = $_REQUEST["jobman-field-$id"];

			switch( $field['type'] ) {
				case 'text':
				case 'textarea':
						echo "<td><input type='text' name='jobman-field-$id' value='".htmlspecialchars($req_value)."' /></td>";
					break;
				case 'date':
					echo "<td><input type='text' class='datepicker' name='jobman-field-$id' value='".htmlspecialchars($req_value)."' /></td>";
					break;
				case 'radio':
				case 'checkbox':
				case 'select':
					echo '<td>';
					$values = explode( "\n", $field['data'] );
					foreach( $values as $value ) {
						$checked = '';
						if( is_array( $req_value ) && in_array( trim( $value ), $req_value ) )
							$checked = ' checked="checked"';

						echo "<input type='checkbox' name='jobman-field-{$id}[]' value='" . trim($value) . "'$checked /> $value<br/>";
					}
					echo '</td>';
					break;
				case 'geoloc':
					if( $options['api_keys']['google_maps'] ) {
						$msg = __( 'Up to %1s km from %2s', 'jobman' );

						$km_value = '';
						if( array_key_exists( "jobman-field2-$id", $_REQUEST ) )
							$km_value = $_REQUEST["jobman-field2-$id"];

						$km = "<input type='text' name='jobman-field2-$id' class='small-text' value='$km_value' />";
						$loc = "<input type='text' name='jobman-field-$id' value='$req_value' />";
						$msg = sprintf( $msg, $km, $loc );
					}
					else {
						$msg = __( 'Please enter a Google Maps API key in your Admin Settings.', 'jobman' );
					}
					echo "<td>$msg</td>";
					break;
				default:
					echo '<td>' . __( 'This field cannot be filtered.', 'jobman' ) . '</td>';
			}
		}
		echo '</tr>';
?>
				</table>
<?php
	}
?>
				</div>
			<div style="clear: both; text-align: right;"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Filter Applications', 'jobman' ) ?>" /></div>

		</form>
		</div>
		<div id="jobman-filter-link-show"><a href="#" onclick="jQuery('#jobman-filter').show('fast'); jQuery('#jobman-filter-link-show').hide(); jQuery('#jobman-filter-link-hide').show(); return false;"><?php _e( 'Show Filter Options', 'jobman' ) ?></a></div>
		<div id="jobman-filter-link-hide" class="hidden"><a href="#" onclick="jQuery('#jobman-filter').hide('fast'); jQuery('#jobman-filter-link-hide').hide(); jQuery('#jobman-filter-link-show').show(); return false;"><?php _e( 'Hide Filter Options', 'jobman' ) ?></a></div>

		<form action="" method="post">
<?php
	wp_nonce_field( 'jobman-mass-edit-applications' );
?>
		<table id="jobman-applications-list" class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col" id="cb" class="column-cb check-column"><input type="checkbox"></th>
				<th scope="col"><?php _e( 'Application', 'jobman' ) ?></th>
<?php
	if( count( $fields ) > 0 ) {
		foreach( $fields as $field ) {
			if( $field['listdisplay'] ) {
?>
				<th scope="col"><?php echo $field['label'] ?></th>
<?php
			}
		}
	}
?>
				<th scope="col"><?php _e( 'Information', 'jobman' ) ?></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<th scope="col" class="column-cb check-column"><input type="checkbox"></th>
				<th scope="col"><?php _e( 'Application', 'jobman' ) ?></th>
<?php
	if( count( $fields ) > 0 ) {
		foreach( $fields as $field ) {
			if( $field['listdisplay'] ) {
?>
				<th scope="col"><?php echo $field['label'] ?></th>
<?php
			}
		}
	}
?>
				<th scope="col"><?php _e( 'Information', 'jobman' ) ?></th>
			</tr>
			</tfoot>
<?php
	$args = array();
	$args['post_type'] = 'jobman_app';
	$args['post_status'] = 'private,publish';
	$args['offset'] = 0;
	$args['numberposts'] = -1;

	$filtered = false;

	// Add applicant filter
	if( array_key_exists( 'jobman-applicant', $_REQUEST ) )
		$args['author_name'] = $_REQUEST['jobman-applicant'];

	// Add category filter
	// Removed this until WP_Query supports *__in for custom taxonomy.
	/*if( array_key_exists( 'jobman-categories', $_REQUEST ) && is_array( $_REQUEST['jobman-categories'] ) ) {
		$filtered = true;
		$args['jcat__in'] = array();
		foreach( $_REQUEST['jobman-categories'] as $cat ) {
			$args['jcat__in'][] = $cat;
		}
	}*/

	$applications = get_posts( $args );

	$app_displayed = false;
	if( count( $applications ) > 0 ) {
		foreach( $applications as $app ) {
			// Filter jobs
			if( array_key_exists( 'jobman-jobid', $_REQUEST ) && ! empty ( $_REQUEST['jobman-jobid'] ) ) {
				$jobs = get_post_meta( $app->ID, 'job', false );

				if( empty( $jobs ) || ! in_array( $_REQUEST['jobman-jobid'], $jobs ) )
					continue;
			}

			$appmeta = get_post_custom( $app->ID );

			$appdata = array();
			foreach( $appmeta as $key => $value ) {
				if( is_array( $value ) )
					$appdata[$key] = $value[0];
				else
					$appdata[$key] = $value;
			}

			if( array_key_exists( 'jobman-rating', $_REQUEST ) && is_numeric( $_REQUEST['jobman-rating'] ) && $_REQUEST['jobman-rating'] > 0 )
			{
				if( array_key_exists( 'rating', $appdata ) && $appdata['rating'] !== $_REQUEST['jobman-rating'] )
				{
					continue;
				}
				elseif( empty($appdata['rating']) )
				{
					continue;
				}
			}

			// Workaround for WP_Query not supporting *__in for custom taxonomy.
			if( array_key_exists( 'jobman-categories', $_REQUEST ) && is_array( $_REQUEST['jobman-categories'] ) ) {
				$cats = wp_get_object_terms( $app->ID, 'jobman_category' );
				if( count( $cats ) > 0 ) {
					$found = false;
					foreach( $cats as $cat ) {
						if( in_array( $cat->term_id, $_REQUEST['jobman-categories'] ) ) {
							// $app is in the list of selected categories. Let it through.
							$found = true;
							break;
						}
					}

					// $app wasn't in the categories. Skip it.
					if( ! $found ) {
						$filtered = true;
						continue;
					}
				}
				else {
					// $app has no categories. Skip it.
					$filtered = true;
					continue;
				}
			}

			// Check against field filters
			if( count( $fields ) > 0 ) {
				foreach( $fields as $id => $field ) {
					if( ! array_key_exists( "jobman-field-$id", $_REQUEST ) || '' == $_REQUEST["jobman-field-$id"] )
						continue;
					if( ! array_key_exists( "data$id", $appdata ) ) {
						// No data for this key application, so it can't match. Go to next $app.
						$filtered = true;
						continue 2;
					}
					switch( $field['type'] ) {
						case 'text':
						case 'textarea':
						case 'date':
							if( $appdata["data$id"] != $_REQUEST["jobman-field-$id"] ) {
								// App doesn't match. Go to the next item in the $applications loop.
								$filtered = true;
								continue 3;
							}
							break;
						case 'radio':
						case 'checkbox':
						case 'select':
							if( is_array( $_REQUEST["jobman-field-$id"] ) ) {
								$data = explode( ',', $appdata["data$id"] );
								foreach( $_REQUEST["jobman-field-$id"] as $selected ) {
									if( in_array( trim( $selected ), $data ) )
										// We have a match. Go to the next item in the $fields loop.
										continue 3;
								}
								// There was no match. Go to next in $applications loop.
								$filtered = true;
								continue 3;
							}
							break;
						case 'geoloc':
							if( empty( $_REQUEST["jobman-field2-$id"] ) || ! is_numeric( $_REQUEST["jobman-field2-$id"] ) )
								// No value or bad value entered for distance
								continue 2;

							$url = 'http://maps.google.com/maps/geo?output=xml&key=' . $options['api_keys']['google_maps'];
							$searchurl = "$url&q=" . urlencode( $_REQUEST["jobman-field-$id"] );

							if( ! $xml = simplexml_load_file( $searchurl ) )
								// Something broken with XML load
								continue 2;
							$status = $xml->Response->Status->code;
							if (strcmp($status, "200") == 0) {
								$coordinates = $xml->Response->Placemark->Point->coordinates;
								$coordinatesSplit = explode(",", $coordinates);

								$search_lat = $coordinatesSplit[1];
								$search_lng = $coordinatesSplit[0];

								$data = $appdata["data$id"];
								if( ! preg_match( '/^[0-9.]+,[0-9.]+$/', $data ) ) {
									// Data not stored as lat,long. Ask Google.
									$searchurl = "$url&q=" . urlencode( $data );
									if( ! $xml = simplexml_load_file( $searchurl ) )
										// Something broken with XML load
										continue 2;

									$status = $xml->Response->Status->code;
									if (strcmp($status, "200") == 0) {
										$coordinates = $xml->Response->Placemark->Point->coordinates;
										$coordinatesSplit = explode(",", $coordinates);

										$data_lat = $coordinatesSplit[1];
										$data_lng = $coordinatesSplit[0];
									}
									else {
										// Geocode failed
										continue 2;
									}
								}
								else {
									list( $data_lat, $data_lng ) = explode( ',', $data );
								}

								// Calculate distance between locations
								$distance = sin( deg2rad( $data_lat ) ) * sin( deg2rad( $search_lat ) ) +
											cos( deg2rad( $data_lat ) ) * cos( deg2rad( $search_lat ) ) * cos( deg2rad( $data_lng - $search_lng ) );

								$distance = rad2deg( acos( $distance ) ) * 69.09 * 1.609344;

								if( $distance > $_REQUEST["jobman-field2-$id"] )
									// Too far away. Move to the next $app
									continue 3;
							}
							else {
								// Geocode failed
								continue 2;
							}
					}
				}
			}
			$app_displayed = true;

			$fromid = $options['application_email_from'];
			$email = $appdata["data$fromid"];
			$grav_url = 'http://www.gravatar.com/avatar/' . md5( strtolower( $email ) ) . '?size=45';

			$parents = get_post_meta( $app->ID, 'job', false );
			$jobstr = '';
			if( ! empty( $parents ) ) {
				$parentstr = array();
				foreach( $parents as $parent ) {
					$data = get_post( $parent );
					$parentstr[] = "<a href='?page=jobman-list-jobs&amp;jobman-jobid=$data->ID'>$data->ID - $data->post_title</a>";
				}

				$jobstr = implode( ', ', $parentstr );
			}
			else {
				$jobstr = __( 'No job', 'jobman' );
			}

			$cats = wp_get_object_terms( $app->ID, 'jobman_category' );
			$cats_arr = array();
			if( count( $cats ) > 0 ) {
				foreach( $cats as $cat ) {
					$cats_arr[] = $cat->name;
				}
			}

			$cats_str = '';
			if( !empty( $cats_arr ) )
				$cats_str = implode( ', ', $cats_arr ) . '<br/>';

			$name = '';
			if( $options['user_registration'] ) {
				if( 0 == $app->post_author ) {
					$name = __( 'Unregistered Applicant', 'jobman' );
				}
				else {
					$author = get_userdata( $app->post_author );
					$name = __( 'User', 'jobman' ) . ": $author->display_name";
				}
				$name .= '<br/>';
			}
?>
			<tr>
				<th scope="row" class="check-column"><input type="checkbox" name="application[]" value="<?php echo $app->ID ?>" /></th>
				<td>
				<img src="<?php echo $grav_url; ?>" alt="" class="jobman-gravatar-list" />
				<strong><?php echo $jobstr ?></strong><br/>
				<?php echo $cats_str ?>
				<?php echo $name ?>
				<strong><a href="?page=jobman-list-applications&amp;appid=<?php echo $app->ID ?>"><?php _e( 'View Details', 'jobman' ) ?></a></strong>
				</td>
<?php
			if( count( $fields ) ) {
				foreach( $fields as $id => $field ) {
					if( $field['listdisplay'] ) {
						$data = '';
						if( array_key_exists("data$id", $appdata ) && '' != $appdata["data$id"] ) {
							switch( $field['type'] ) {
								case 'text':
								case 'radio':
								case 'checkbox':
								case 'date':
								case 'textarea':
								case 'select':
									$data = htmlspecialchars($appdata["data$id"]);
									break;
								case 'file':
									$data = '<a href="' . wp_get_attachment_url( $appdata["data$id"] ) . '">' . __( 'Download', 'jobman' ) . '</a>';
									break;
								case 'geoloc':
									$data = htmlspecialchars($appdata["data-display$id"]);
									break;
							}
						}
?>
				<td><?php echo $data ?></td>
<?php
					}
				}
			}
?>
				<td>
<?php
			echo __( 'Emails', 'jobman' ) . ': ';
			$emailids = get_post_meta( $app->ID, 'contactmail', false );
			if( count( $emailids ) > 0 )
			    echo "<a href='?page=jobman-list-emails&amp;appid=$app->ID'>" . count( $emailids ) . '</a>';
			else
			    echo '0';
			echo '<br/>';

			if( $options['interviews'] ) {
				$iids = get_post_meta( $app->ID, 'interview', false );
				echo __( 'Interviews', 'jobman' ) . ": <a href='?page=jobman-interviews&amp;display=application&filter=$app->ID'>" . count( $iids ) . '</a><br/>';
			}

			$rating = 0;
			if( array_key_exists( 'rating', $appdata ) )
				$rating = $appdata['rating'];

			jobman_print_rating_stars( $app->ID, $rating );
?>
				</td>
			</tr>
<?php
		}
	}
	if( ! $app_displayed ) {
		if( $filtered )
			$msg = __( 'There were no applications that matched your search.', 'jobman' );
		else
			$msg = __( 'There are currently no applications in the system.', 'jobman' );

?>
			<tr>
				<td colspan="<?php echo 3 + $fieldcount ?>"><?php echo $msg ?></td>
			</tr>
<?php
	}
?>
		</table>
		<div class="alignleft actions">
			<select name="jobman-mass-edit">
				<option value=""><?php _e( 'Bulk Actions', 'jobman' ) ?></option>
				<option value="email"><?php _e( 'Email', 'jobman' ) ?></option>
				<option value="delete"><?php _e( 'Delete', 'jobman' ) ?></option>
				<option value="export-csv"><?php _e( 'Export as CSV file', 'jobman' ) ?></option>
			</select>
			<input type="submit" value="<?php _e( 'Apply', 'jobman' ) ?>" name="submit" class="button-secondary action" />
		</div>
		</form>
	</div>
<?php
}

function jobman_rate_application() {
	$rating = get_post_meta( $_REQUEST['appid'], 'rating', true );
	if( '' == $rating )
		add_post_meta( $_REQUEST['appid'], 'rating', $_REQUEST['rating'], true );
	else
	    update_post_meta( $_REQUEST['appid'], 'rating', $_REQUEST['rating'] );

	die();
}

function jobman_application_details_layout( $appid ) {
	$options = get_option( 'jobman_options' );

	if( array_key_exists( 'jobman-email', $_REQUEST ) ) {
		check_admin_referer( 'jobman-reemail-application' );
	    jobman_email_application( $appid, $_REQUEST['jobman-email'] );
	}

	if( array_key_exists( 'new-interview', $_REQUEST ) )
		jobman_interview_new();

	if( array_key_exists( 'comment', $_REQUEST ) )
		jobman_store_comment();
?>
	<div id="jobman-application" class="wrap">
		<h2><?php _e( 'Job Manager: Application Details', 'jobman' ) ?></h2>
		<div class="printicon"><a href="javascript:window.print()"><img src="<?php echo JOBMAN_URL ?>/images/print-icon.png" /></a></div>
		<a href="?page=jobman-list-applications" class="backlink">&larr;<?php _e( 'Back to Application List', 'jobman' ) ?></a>
<?php

	$widths = array( '59%', '39%' );
	$functions = array(
					array( 'jobman_application_display_details' ),
					array( 'jobman_comments', 'jobman_application_email_form' )
				);
	$titles = array(
				array( __( 'Application', 'jobman' ) ),
				array( __( 'Application Comments', 'jobman' ), __( 'Share Application', 'jobman' ) )
			);
	$params = array(
					array( array( $appid ) ),
					array( array( $appid, true ), array() )
			);

	if( $options['interviews'] ) {
		$functions[1] = array_insert( $functions[1], 1, 'jobman_interview_application' );
		$titles[1] = array_insert( $titles[1], 1, __( 'Interviews', 'jobman' ) );
		$params[1] = array_insert( $params[1], 1, array( $appid, 'summary' ) );
	}
	jobman_create_dashboard( $widths, $functions, $titles, $params );
?>
		<a href="?page=jobman-list-applications" class="backlink">&larr;<?php _e( 'Back to Application List', 'jobman' ) ?></a>
	</div>
<?php
}

function jobman_application_display_details( $appid ) {
	$options = get_option( 'jobman_options' );
	$fromid = $options['application_email_from'];

	$app = get_post( $appid );
	$appmeta = get_post_custom( $appid );

	$appdata = array();
	if( ! empty( $appmeta ) ) {
		foreach( $appmeta as $key => $value ) {
			if( is_array( $value ) )
				$appdata[$key] = $value[0];
			else
				$appdata[$key] = $value;
		}
	}

	$fromid = $options['application_email_from'];
	$email = $appdata["data$fromid"];
	$grav_url = 'http://www.gravatar.com/avatar/' . md5( strtolower( $email ) ) . '?size=120';
	echo "<img src='$grav_url' alt='' class='jobman-gravatar' />";

	if( NULL != $app ) {
		echo '<table class="form-table jobman-form-table">';

		$parents = get_post_meta( $app->ID, 'job', false );
		if( ! empty( $parents ) ) {
			$parentstr = array();
			foreach( $parents as $parent ) {
				$data = get_post( $parent );

				$children = get_posts( "post_type=jobman_app&meta_key=job&meta_value=$data->ID&post_status=publish,private" );
				if( count( $children ) > 0 )
					$applications = '<a href="' . admin_url("admin.php?page=jobman-list-applications&amp;jobman-jobid=$data->ID") . '">' . count( $children ) . '</a>';
				else
					$applications = 0;

				$parentstr[] = "<a href='?page=jobman-list-jobs&amp;jobman-jobid=$data->ID'>$data->post_title</a> ($applications)";
			}
			$title = __( 'Job', 'jobman' );
			if( count( $parentstr ) > 1 )
				$title = __( 'Jobs', 'jobman' );
			echo "<tr><th scope='row'><strong>$title</strong></th><td><strong>" . implode( ', ', $parentstr ) . '</strong></td></tr>';
		}
		$post_date = date_i18n( 'l, d F Y, H:i:s', strtotime( $app->post_date ) );
		echo '<tr><th scope="row"><strong>' . __( 'Timestamp', 'jobman' ) . "</strong></th><td>$post_date</td></tr>";

		echo '<tr><th scope="row"><strong>' . __( 'Rating', 'jobman' ) . '</strong></th>';
		echo '<td>';

		$rating = 0;
		if( array_key_exists( 'rating', $appdata ) )
	    	$rating = $appdata['rating'];

		jobman_print_rating_stars( $app->ID, $rating );

		echo '</div></td><tr><td colspan="2">&nbsp;</td></tr>';

		$fields = $options['fields'];
		if( count( $fields ) > 0 ) {
			uasort( $fields, 'jobman_sort_fields' );
			foreach( $fields as $fid => $field ) {
				if( ! array_key_exists( "data$fid", $appdata ) )
					continue;

				$item = $appdata["data$fid"];

				echo '<tr><th scope="row" style="min-width: 150px;"><strong>' . $fields[$fid]['label'] . '</strong></th><td>';
				if( $fid == $fromid ) {
					echo "<a href='mailto:$item'>";
				}
				switch( $fields[$fid]['type'] ) {
					case 'text':
					case 'radio':
					case 'checkbox':
					case 'date':
					case 'textarea':
					case 'select':
						echo htmlspecialchars($item);
						break;
					case 'file':
						$fileurl = wp_get_attachment_url( $item );
						if( ! empty( $fileurl ) )
							echo "<a href='$fileurl'>" . __( 'Download', 'jobman' ) . "</a>";
						break;
					case 'geoloc':
						echo '<a href="http://maps.google.com/maps?q=' . urlencode( $item ) . '">' . htmlspecialchars($appdata['data-display'.$fid]) . ' (' . htmlspecialchars($item) . ')</a>';
						break;
				}
				if( $fid == $fromid ) {
					echo '</a>';
				}
				echo '</td></tr>';
			}
		}
	}
?>
		</table>
<?php
}

function jobman_application_email_form() {
?>
		<div class="emailapplication">
			<p><?php _e( 'Use this form to email the application to a new email address.', 'jobman' ) ?></p>
			<form action="" method="post">
<?php
			wp_nonce_field( 'jobman-reemail-application' );
?>
			<input type="text" name="jobman-email" />
			<input type="submit" name="submit" value="<?php _e( 'Email', 'jobman' ) ?>!" />
			</form>
		</div>
<?php
}

function jobman_application_delete_confirm() {
?>
	<div class="wrap">
	<form action="" method="post">
	<input type="hidden" name="jobman-delete-confirmed" value="1" />
	<input type="hidden" name="jobman-mass-edit" value="delete" />
	<input type="hidden" name="jobman-app-ids" value="<?php echo implode( ',', $_REQUEST['application'] ) ?>" />
<?php
	wp_nonce_field( 'jobman-mass-delete-applications' );
?>
		<h2><?php _e( 'Job Manager: Applications', 'jobman' ) ?></h2>
		<p class="error"><?php _e( 'This will permanently delete all of the selected applications. Please confirm that you want to continue.', 'jobman' ) ?></p>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Delete Applications', 'jobman' ) ?>" /></p>
	</form>
	</div>
<?php
}

function jobman_application_delete() {
	$options = get_option( 'jobman_options' );

	$apps = explode( ',', $_REQUEST['jobman-app-ids'] );

	// Get the file fields
	$file_fields = array();
	foreach( $options['fields'] as $id => $field ) {
		if( 'file' == $field['type'] )
			$file_fields[] = $id;
	}

	foreach( $apps as $app ) {
		$appmeta = get_post_custom( $app );
		$appdata = array();
		if( is_array( $appmeta ) ) {
			foreach( $appmeta as $key => $value ) {
				if( is_array( $value ) )
					$appdata[$key] = $value[0];
				else
					$appdata[$key] = $value;
			}
		}

		// Delete any files uploaded
		foreach( $file_fields as $fid ) {
			if( array_key_exists( "data$fid", $appdata )  && '' != $appdata["data$fid"] )
				wp_delete_post( $appdata["data$fid"] );
		}
		// Delete the application
		wp_delete_post( $app );
	}
}

function jobman_get_application_csv() {
	if( is_multisite() ) {
		// Multisite seems to do some late loading. We need the cookie info now, so we can validate the user.
		require_once( ABSPATH . WPINC . '/default-constants.php' );
		wp_cookie_constants();
	}
	require_once( ABSPATH . WPINC . '/pluggable.php' );

	$options = get_option( 'jobman_options' );

	header( 'Cache-Control: no-cache' );
	header( 'Expires: -1' );

	if( ! current_user_can( 'read_private_pages' ) ) {
		header( $_SERVER["SERVER_PROTOCOL"] . ' 403 Forbidden' );
		header( 'Refresh: 0; url=' . admin_url() );
		echo '<html><head><title>403 Forbidden</title></head><body><p>Access is forbidden.</p></body></html>';
		exit;
	}

	header( 'Content-Type: application/force-download' );
	header( 'Content-type: text/csv' );
	header( 'Content-Type: application/download' );
	header( "Content-Disposition: attachment; filename=applications.csv	" );

	$fields = $options['fields'];
	$out = fopen( 'php://output', 'w' );

	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );

		$labels = array();
		foreach( $fields as $field ) {
			$labels[] = $field['label'];
		}
		fputcsv( $out, $labels );

		$posts = array();
		if( array_key_exists( 'application', $_REQUEST ) && is_array( $_REQUEST['application'] ) )
			$posts = $_REQUEST['application'];
		$apps = get_posts( array( 'post_type' => 'jobman_app', 'post__in' => $posts, 'numberposts' => -1, 'post_status' => 'public,private' ) );

		if( count( $apps ) > 0 ) {
			foreach( $apps as $app ) {
				$data = array();

				$appmeta = get_post_custom( $app->ID );

				$appdata = array();
				foreach( $appmeta as $key => $value ) {
					if( is_array( $value ) )
						$appdata[$key] = $value[0];
					else
						$appdata[$key] = $value;
				}

				foreach( $fields as $id => $field ) {
					if( array_key_exists( "data$id", $appdata ) ) {
						$item = $appdata["data$id"];
						switch( $field['type'] ) {
							case 'text':
							case 'radio':
							case 'checkbox':
							case 'date':
							case 'textarea':
							case 'select':
								$data[] = $item;
								break;
							case 'file':
								$data[] = admin_url("admin.php?page=jobman-list-applications&appid=$app->ID&getfile=$item");
								break;
							case 'geoloc':
								$data[] = $appdata['data-display'.$id] . ' (' . $item . ')';
								break;
							default:
								$data[] = '';
						}
					}
					else {
						$data[] = '';
					}
				}

				fputcsv( $out, $data );
			}
		}
	}

	fclose( $out );

	exit;
}

?>