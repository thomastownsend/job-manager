<?php //encoding: utf-8

function jobman_create_db() {
	$options = get_option( 'jobman_options' );

	$options['icons'] = array();
	$options['fields'] = array();

	$options['fields'][1] = array(
								'label' => __( 'Personal Details', 'jobman' ),
								'type' => 'heading',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 0,
								'categories' => array()
							);
	$options['fields'][2] = array(
								'label' => __( 'Name', 'jobman' ),
								'type' => 'text',
								'listdisplay' => 1,
								'data' => '',
								'mandatory' => 1,
								'filter' => '',
								'error' => '',
								'sortorder' => 1,
								'categories' => array()
							);
	$options['fields'][3] = array(
								'label' => __( 'Surname', 'jobman' ),
								'type' => 'text',
								'listdisplay' => 1,
								'data' => '',
								'mandatory' => 1,
								'filter' => '',
								'error' => '',
								'sortorder' => 2,
								'categories' => array()
							);
	$options['fields'][4] = array(
								'label' => __( 'Email Address', 'jobman' ),
								'type' => 'text',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 1,
								'filter' => '',
								'error' => '',
								'sortorder' => 3,
								'categories' => array()
							);
	$options['fields'][5] = array(
								'label' => __( 'Contact Details', 'jobman' ),
								'type' => 'heading',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 4,
								'categories' => array()
							);
	$options['fields'][6] = array(
								'label' => __( 'Address', 'jobman' ),
								'type' => 'textarea',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 5,
								'categories' => array()
							);
	$options['fields'][7] = array(
								'label' => __( 'City', 'jobman' ),
								'type' => 'text',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 6,
								'categories' => array()
							);
	$options['fields'][8] = array(
								'label' => __( 'Post code', 'jobman' ),
								'type' => 'text',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 7,
								'categories' => array()
							);
	$options['fields'][9] = array(
								'label' => __( 'Country', 'jobman' ),
								'type' => 'text',
								'listdisplay' => 1,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 8,
								'categories' => array()
							);
	$options['fields'][10] = array(
								'label' => __( 'Telephone', 'jobman' ),
								'type' => 'text',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 9,
								'categories' => array()
							);
	$options['fields'][11] = array(
								'label' => __( 'Cell phone', 'jobman' ),
								'type' => 'text',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 10,
								'categories' => array()
							);
	$options['fields'][12] = array(
								'label' => __( 'Qualifications', 'jobman' ),
								'type' => 'heading',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 11,
								'categories' => array()
							);
	$options['fields'][13] = array(
								'label' => __( 'Do you have a degree?', 'jobman' ),
								'type' => 'radio',
								'listdisplay' => 1,
								'data' => __( 'Yes', 'jobman' ) . "\r\n" . __( 'No', 'jobman' ),
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 12,
								'categories' => array()
							);
	$options['fields'][14] = array(
								'label' => __( 'Where did you complete your degree?', 'jobman' ),
								'type' => 'text',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 13,
								'categories' => array()
							);
	$options['fields'][15] = array(
								'label' => __( 'Title of your degree', 'jobman' ),
								'type' => 'text',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 14,
								'categories' => array()
							);
	$options['fields'][16] = array(
								'label' => __( 'Upload your CV', 'jobman' ),
								'type' => 'file',
								'listdisplay' => 1,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 15,
								'categories' => array()
							);
	$options['fields'][17] = array(
								'label' => '',
								'type' => 'blank',
								'listdisplay' => 0,
								'data' => '',
								'mandatory' => 0,
								'filter' => '',
								'error' => '',
								'sortorder' => 16,
								'categories' => array()
							);
	$options['fields'][18] = array(
								'label' => '',
								'type' => 'checkbox',
								'listdisplay' => 0,
								'data' => __( 'I have read and understood the privacy policy.', 'jobman' ),
								'mandatory' => 1,
								'filter' => __( 'I have read and understood the privacy policy.', 'jobman' ),
								'error' => __( "You need to read and agree to our privacy policy before we can accept your application. Please click the 'Back' button in your browser, read our privacy policy, and confirm that you accept.", 'jobman' ),
								'sortorder' => 17,
								'categories' => array()
							);

	$options['job_fields'] = array();

	$options['job_fields'][1] = array(
								'label' => __( 'Salary', 'jobman' ),
								'type' => 'text',
								'data' => '',
								'sortorder' => 0,
								'description' => ''
							);

	$options['job_fields'][2] = array(
								'label' => __( 'Start Date', 'jobman' ),
								'type' => 'date',
								'data' => '',
								'sortorder' => 1,
								'description' => __( 'The date that the job starts. For positions available immediately, leave blank.', 'jobman' )
							);

	$options['job_fields'][3] = array(
								'label' => __( 'End Date', 'jobman' ),
								'type' => 'date',
								'data' => '',
								'sortorder' => 2,
								'description' =>  __( 'The date that the job finishes. For ongoing positions, leave blank.', 'jobman' )
							);

	$options['job_fields'][4] = array(
								'label' => __( 'Location', 'jobman' ),
								'type' => 'text',
								'data' => '',
								'sortorder' => 3,
								'description' => ''
							);

	$options['job_fields'][5] = array(
								'label' => __( 'Job Information', 'jobman' ),
								'type' => 'textarea',
								'data' => '',
								'sortorder' => 4,
								'description' => ''
							);

	// Create the root jobs page
	$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'post_content' => '',
				'post_name' => 'jobs',
				'post_title' => __( 'Jobs Listing', 'jobman' ),
				'post_content' => __( 'Hi! This page is used by your Job Manager plugin as a base. Feel free to change settings here, but please do not delete this page. Also note that any content you enter here will not show up when this page is displayed on your site.', 'jobman' ),
				'post_type' => 'page'
			);
	$mainid = wp_insert_post( $page );

	$options['main_page'] = $mainid;

	// Create the apply page
	$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'post_content' => '',
				'post_name' => 'apply',
				'post_title' => __( 'Job Application', 'jobman' ),
				'post_type' => 'jobman_app_form',
				'post_parent' => $mainid
			);
	$id = wp_insert_post( $page );

	// Create the register page
	$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'post_content' => '',
				'post_name' => 'register',
				'post_title' => __( 'Register', 'jobman' ),
				'post_type' => 'jobman_register',
				'post_parent' => $mainid
			);
	$id = wp_insert_post( $page );

	$options['register_page'] = $id;

	update_option( 'jobman_options', $options );
}

function jobman_upgrade_db( $oldversion ) {
	global $wpdb;
	$options = get_option( 'jobman_options' );

	if( $oldversion < 4 ) {
		// Fix any empty slugs in the category list.
		$sql = "SELECT * FROM {$wpdb->prefix}jobman_categories ORDER BY id;";
		$categories = $wpdb->get_results( $sql, ARRAY_A );

		if( count( $categories ) > 0 ) {
			foreach( $categories as $cat ) {
				if('' == $cat['slug'] ) {
					$slug = strtolower( $cat['title'] );
					$slug = str_replace( ' ', '-', $slug );

					$sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}jobman_categories SET slug=%s WHERE id=%d;", $slug, $id );
					$wpdb->query( $sql );
				}
			}
		}
	}

	if( $oldversion < 5 ) {
		// Re-write the database to use the existing WP tables

		// Create the root jobs page
		$page = array(
					'comment_status' => 'closed',
					'ping_status' => 'closed',
					'post_status' => 'publish',
					'post_author' => 1,
					'post_content' => '',
					'post_name' => $options['page_name'],
					'post_title' => __( 'Jobs Listing', 'jobman' ),
					'post_content' => __( 'Hi! This page is used by your Job Manager plugin as a base. Feel free to change settings here, but please do not delete this page. Also note that any content you enter here will not show up when this page is displayed on your site.', 'jobman' ),
					'post_type' => 'page'
				);
		$mainid = wp_insert_post( $page );

		$options['main_page'] = $mainid;

		// Move the categories to WP categories
		$sql = "SELECT * FROM {$wpdb->prefix}jobman_categories;";
		$categories = $wpdb->get_results( $sql, ARRAY_A );

		$oldcats = array();
		$newcats = array();

		if( count( $categories ) > 0 ) {
			foreach( $categories as $cat ) {
				$oldcats[] = $cat['id'];
				$catid = wp_insert_term( $cat['title'], 'jobman_category', array( 'slug' => $cat['slug'], 'description' => $cat['email'] ) );
				$newcats[] = $catid;
			}
		}

		// Create a page for each category, so we have somewhere to store the applications
		$catpages = array();
		foreach( $categories as $cat ) {
			$page = array(
						'comment_status' => 'closed',
						'ping_status' => 'closed',
						'post_status' => 'publish',
						'post_author' => 1,
						'post_content' => '',
						'post_name' => $cat['slug'],
						'post_title' => $cat['title'],
						'post_type' => 'jobman_joblist',
						'post_parent' => $mainid
					);
			$id = wp_insert_post( $page );
			$catpages[] = $id;
			add_post_meta( $id, '_catpage', 1, true );
			add_post_meta( $id, '_cat', $newcats[array_search( $cat['id'], $oldcats )], true );
		}

		// Move the jobs to posts
		$oldjobids = array();
		$newjobids = array();
		$sql = "SELECT * FROM {$wpdb->prefix}jobman_jobs;";
		$jobs = $wpdb->get_results( $sql, ARRAY_A );
		if( count( $jobs ) > 0 ) {
			foreach( $jobs as $job ) {
				$oldjobids[] = $job['id'];

				$page = array(
							'comment_status' => 'closed',
							'ping_status' => 'closed',
							'post_status' => 'publish',
							'post_author' => 1,
							'post_content' => $job['abstract'],
							'post_name' => strtolower( str_replace( ' ', '-', $job['title'] ) ),
							'post_title' => $job['title'],
							'post_type' => 'jobman_job',
							'post_date' => $job['displaystartdate'],
							'post_parent' => $mainid
						);
				$id = wp_insert_post( $page );
				$newjobids[] = $id;

				add_post_meta( $id, 'salary', $job['salary'], true );
				add_post_meta( $id, 'startdate', $job['startdate'], true );
				add_post_meta( $id, 'enddate', $job['enddate'], true );
				add_post_meta( $id, 'location', $job['location'], true );
				add_post_meta( $id, 'displayenddate', $job['displayenddate'], true );
				add_post_meta( $id, 'iconid', $job['iconid'], true );

				// Get the old category ids
				$cats = array();
				$sql = $wpdb->prepare( "SELECT c.id AS id FROM {$wpdb->prefix}jobman_categories AS c LEFT JOIN {$wpdb->prefix}jobman_job_category AS jc ON c.id=jc.categoryid WHERE jc.jobid=%d;", $job['id'] );
				$data = $wpdb->get_results( $sql, ARRAY_A );
				if( count( $data ) > 0 ) {
					foreach( $data as $cat ) {
						// Make an array of the new category ids
						if( term_exists( $newcats[array_search( $cat['id'], $oldcats )], 'jobman_category' ) )
							wp_set_object_terms( $id, $newcats[array_search( $cat['id'], $oldcats )], 'jobman_category', true );
					}
				}
			}
		}

		// Move the icons to jobman_options
		$options['icons'] = array();
		$sql = "SELECT * FROM {$wpdb->prefix}jobman_icons ORDER BY id;";
		$icons = $wpdb->get_results( $sql, ARRAY_A );

		if( count( $icons ) > 0 ) {
			foreach( $icons as $icon ) {
				$options['icons'][$icon['id']] = array(
													'title' => $icon['title'],
													'extension' => $icon['extension']
												);
			}
		}

		// Move the application fields to jobman_options
		$options['fields'] = array();
		$sql = "SELECT af.*, (SELECT COUNT(*) FROM {$wpdb->prefix}jobman_application_field_categories AS afc WHERE afc.afid=af.id) AS categories FROM {$wpdb->prefix}jobman_application_fields AS af ORDER BY af.sortorder ASC;";
		$fields = $wpdb->get_results( $sql, ARRAY_A );

		if( count( $fields ) > 0 ) {
			foreach( $fields as $field ) {
				$options['fields'][$field['id']] = array(
													'label' => $field['label'],
													'type' => $field['type'],
													'listdisplay' => $field['listdisplay'],
													'data' => $field['data'],
													'filter' => $field['filter'],
													'error' => $field['error'],
													'sortorder' => $field['sortorder'],
													'categories' => array()
												);
				if( $field['categories'] > 0 ) {
					// This field is restricted to certain categories
					$sql = "SELECT categoryid FROM {$wpdb->prefix}jobman_application_field_categories WHERE afid={$field['id']};";
					$field_categories = $wpdb->get_results( $sql, ARRAY_A );

					if( count( $categories ) > 0 ) {
						foreach( $categories as $cat ) {
							foreach( $field_categories as $fc ) {
								if( in_array( $cat['id'], $fc ) ) {
									$options['fields'][$field['id']]['categories'][] = $newcats[array_search( $cat['id'], $oldcats )];
									break;
								}
							}
						}
					}
				}
			}
		}

		// Move the applications to sub-pages of the relevant job or category
		$sql = "SELECT a.*, (SELECT COUNT(*) FROM {$wpdb->prefix}jobman_application_categories AS ac WHERE ac.applicationid=a.id) AS categories FROM {$wpdb->prefix}jobman_applications AS a;";
		$apps = $wpdb->get_results( $sql, ARRAY_A );
		if( count( $apps) > 0 ) {
			foreach( $apps as $app ) {
				$sql = "SELECT * FROM {$wpdb->prefix}jobman_application_data WHERE applicationid={$app['id']};";
				$data = $wpdb->get_results( $sql, ARRAY_A );
				if( count( $data ) > 0 ) {
					$content = array();

					$page = array(
								'comment_status' => 'closed',
								'ping_status' => 'closed',
								'post_status' => 'publish',
								'post_author' => 1,
								'post_type' => 'jobman_app',
								'post_content' => '',
								'post_title' => __( 'Application', 'jobman' ),
								'post_date' => $app['submitted']
							);

					$pageid = 0;
					$cat = 0;
					if( $app['jobid'] > 0 ) {
						// Store against the job
						$pageid = $newjobids[array_search( $app['jobid'], $oldjobids )];
						$page['post_parent'] = $pageid;
					}
					else if( $app['categories'] > 0 ) {
						// Store against the category
						if( count( $categories ) > 0 ) {
							$cat = reset($categories);
							$page['post_parent'] = $newcats[array_search( $cat['id'], $oldcats )];
						}
						else {
							$page['post_parent'] = $mainid;
						}
					}
					else {
						// Store against main
						$page['post_parent'] = $mainid;
					}

					$id = wp_insert_post( $page );

					foreach( $data as $item ) {
						add_post_meta( $id, 'data' . $item['fieldid'], $item['data'], true );
					}

					// Add the categories to the page
					if( $cat ) {
						if( term_exists( $cat, 'jobman_category' ) )
							wp_set_object_terms( $id, $cat, 'jobman_category', true );
					}
					if( $pageid ) {
						// Get parent (job) categories, and apply them to application
						$parentcats = wp_get_object_terms( $pageid, 'jobman_category' );
						foreach( $parentcats as $pcat ) {
							if( term_exists( $pcat->term_id, 'jobman_category' ) ) {
								wp_set_object_terms( $id, $pcat->term_id, 'jobman_category', true );
							}
						}
					}
				}
			}
		}

		// Create the apply page
		$page = array(
					'comment_status' => 'closed',
					'ping_status' => 'closed',
					'post_status' => 'publish',
					'post_author' => 1,
					'post_content' => '',
					'post_name' => 'apply',
					'post_title' => __( 'Job Application', 'jobman' ),
					'post_type' => 'jobman_app_form',
					'post_parent' => $mainid
				);
		$id = wp_insert_post( $page );
	}

	if( $oldversion < 6 ) {
		// Fix category pages not having the correct _cat meta value
		$data = get_posts( 'post_type=jobman_joblist' );
		if( count( $data ) > 0 ) {
			foreach( $data as $catpage ) {
				$pagemeta = get_post_custom( $catpage->ID );
				if( ! array_key_exists( '_cat', $pagemeta ) )
					// Page doesn't have a _cat
					continue;

				if( is_array( $pagemeta['_cat'] ) )
					$catid = $pagemeta['_cat'][0];
				else
					$catid = $pagemeta['_cat'];

				if( preg_match( '/^\d+$/', $catid ) )
					// _cat seems to be set properly
					continue;

				$matches = array();
				preg_match( '/"term_id"[^"]+"(\d+)"/', $catid, $matches );
				update_post_meta( $catpage->ID, '_cat', $matches[1] );
			}
		}
	}

	if( $oldversion < 7 ) {
		// Drop the old tables from 0.3
		$tables = array(
					$wpdb->prefix . 'jobman_jobs',
					$wpdb->prefix . 'jobman_categories',
					$wpdb->prefix . 'jobman_job_category',
					$wpdb->prefix . 'jobman_icons',
					$wpdb->prefix . 'jobman_application_fields',
					$wpdb->prefix . 'jobman_application_field_categories',
					$wpdb->prefix . 'jobman_applications',
					$wpdb->prefix . 'jobman_application_categories',
					$wpdb->prefix . 'jobman_application_data'
				);

		foreach( $tables as $table ) {
			$sql = "DROP TABLE IF EXISTS $table";
			$wpdb->query( $sql );
		}

		// Create the register page
		$page = array(
					'comment_status' => 'closed',
					'ping_status' => 'closed',
					'post_status' => 'publish',
					'post_author' => 1,
					'post_content' => '',
					'post_name' => 'register',
					'post_title' => __( 'Register', 'jobman' ),
					'post_type' => 'jobman_register',
					'post_parent' => $options['main_page']
				);
		$id = wp_insert_post( $page );

		$options['register_page'] = $id;
	}

	if( $oldversion < 8 ) {
		// Fix incorrect default forms
		foreach( $options['fields'] as $id => $field ) {
		    if( 'Yes\r\nNo' == $field['data'] )
		        $options['fields'][$id]['data'] = "Yes\r\nNo";
		}
	}

	if( $oldversion < 10 ) {
	    // Fix missing categories on applications
	    $apps = get_posts( 'post_type=jobman_app&numberposts=-1' );
	    foreach( $apps as $app ) {
			$parent = get_post( $app->post_parent );
			if( 'jobman_job' == $parent->post_type ) {
			    $parentcats = wp_get_object_terms( $parent->ID, 'jobman_category' );
				foreach( $parentcats as $pcat ) {
					if( term_exists( $pcat->slug, 'jobman_category' ) )
						$foo = wp_set_object_terms( $app->ID, $pcat->slug, 'jobman_category', true );
				}
			}
			else if( 'jobman_joblist' == $parent->post_type ) {
			    $cat = get_post_meta( $parent->ID, '_cat', true );
			    if( '' != $cat && term_exists( $cat, 'jobman_category' ) )
					wp_set_object_terms( $app->ID, $cat, 'jobman_category', true );
			}
		}
	}

	if( $oldversion < 11 ) {
		// Remove the old category pages
		$cat_pages = get_posts( 'post_type=jobman_joblist&meta_key=_catpage&meta_value=1&numberposts=-1' );
		foreach( $cat_pages as $cp ) {
			wp_delete_post( $cp->ID );
		}

		// Add the 'mandatory' field option
		foreach( $options['fields'] as $key => $field ) {
			$options['fields'][$key]['mandatory'] = 0;
		}
	}

	if( $oldversion < 12 ) {
		// Add the new job fields
		$options['job_fields'] = array();

		$options['job_fields'][1] = array(
									'label' => __( 'Salary', 'jobman' ),
									'type' => 'text',
									'data' => '',
									'sortorder' => 0,
									'description' => ''
								);

		$options['job_fields'][2] = array(
									'label' => __( 'Start Date', 'jobman' ),
									'type' => 'date',
									'data' => '',
									'sortorder' => 1,
									'description' => __( 'The date that the job starts. For positions available immediately, leave blank.', 'jobman' )
								);

		$options['job_fields'][3] = array(
									'label' => __( 'End Date', 'jobman' ),
									'type' => 'date',
									'data' => '',
									'sortorder' => 2,
									'description' =>  __( 'The date that the job finishes. For ongoing positions, leave blank.', 'jobman' )
								);

		$options['job_fields'][4] = array(
									'label' => __( 'Location', 'jobman' ),
									'type' => 'text',
									'data' => '',
									'sortorder' => 3,
									'description' => ''
								);

		$options['job_fields'][5] = array(
									'label' => __( 'Job Information', 'jobman' ),
									'type' => 'textarea',
									'data' => '',
									'sortorder' => 4,
									'description' => ''
								);

		// Convert existing jobs to new format
		$jobs = get_posts( 'post_type=jobman_job&numberposts=-1' );
		foreach( $jobs as $job ) {
			add_post_meta( $job->ID, 'data1', get_post_meta( $job->ID, 'salary', true ), true );
			add_post_meta( $job->ID, 'data2', get_post_meta( $job->ID, 'startdate', true ), true );
			add_post_meta( $job->ID, 'data3', get_post_meta( $job->ID, 'enddate', true ), true );
			add_post_meta( $job->ID, 'data4', get_post_meta( $job->ID, 'location', true ), true );
			add_post_meta( $job->ID, 'data5', $job->post_content, true );
		}

		// Convert file uploads to attachments
		$apps = get_posts( 'post_type=jobman_app&numberposts=-1' );
		foreach( $apps as $app ) {
			foreach( $options['fields'] as $fid => $field ) {
				if( 'file' != $field['type'] )
					continue;

				$filename = get_post_meta( $app->ID, "data$fid", true );
				if( '' == $filename || ! file_exists( WP_CONTENT_DIR . '/' . JOBMAN_FOLDER . "/uploads/$filename" ) ) {
					update_post_meta( $app->ID, "data$fid", '' );
					continue;
				}

				$upload = wp_upload_bits( $filename, NULL, file_get_contents( WP_CONTENT_DIR . '/' . JOBMAN_FOLDER . "/uploads/$filename" ) );
				$aid = '';
				if( ! $upload['error'] ) {
					$filetype = wp_check_filetype( $upload['file'] );
					$attachment = array(
									'post_title' => '',
									'post_content' => '',
									'post_status' => 'private',
									'post_mime_type' => $filetype['type']
								);
					$aid = wp_insert_attachment( $attachment, $upload['file'], $app->ID );
					$attach_data = wp_generate_attachment_metadata( $aid, $upload['file'] );
					wp_update_attachment_metadata( $aid, $attach_data );

					add_post_meta( $aid, '_jobman_attachment', 1, true );
					add_post_meta( $aid, '_jobman_attachment_upload', 1, true );
				}
				else {
					error_log( $upload['error'] );
					update_post_meta( $app->ID, "data$fid", '' );
				}

				update_post_meta( $app->ID, "data$fid", $aid );
			}
		}

		// Convert icons to attachments
		$icons = array();
		foreach( $options['icons'] as $iid => $icon ) {
			$filename = WP_CONTENT_DIR . '/' . JOBMAN_FOLDER . "/icons/$iid.{$icon['extension']}";
				$upload = wp_upload_bits( $_FILES["jobman-field-$fid"]['name'], NULL, file_get_contents( WP_CONTENT_DIR . '/' . JOBMAN_FOLDER . "/uploads/$filename" ) );
				if( ! $upload['error'] ) {
					$attachment = array(
									'post_title' => $icon['title'],
									'post_content' => '',
									'post_status' => 'publish',
									'post_mime_type' => mime_content_type( $upload['file'] )
								);
					$new_iid = wp_insert_attachment( $attachment, $upload['file'], $options['main_pages'] );
					$attach_data = wp_generate_attachment_metadata( $new_iid, $upload['file'] );
					wp_update_attachment_metadata( $new_iid, $attach_data );

					add_post_meta( $data, '_jobman_attachment', 1, true );
					add_post_meta( $data, '_jobman_attachment_icon', 1, true );

					$icons[] = $new_iid;
				}
		}
		$options['icons'] = $icons;
	}

	if( $oldversion < 13 ) {
		// Update all applications to private
		$apps = get_posts( 'post_type=jobman_app&numberposts=-1&post_status=publish' );
		$update = array(
					'post_status' => 'private'
				);
		foreach( $apps as $app ) {
			$update['ID'] = $app->ID;
			wp_update_post( $update );
		}

		// Update all emails to private
		$emails = get_posts( 'post_type=jobman_email&numberposts=-1&post_status=private' );
		foreach( $emails as $email ) {
			$update['ID'] = $email->ID;
			wp_update_post( $update );
		}
	}

	if( $oldversion < 15 ) {
		// Store the job being applied for as metadata
		$apps = get_posts( 'post_type=jobman_app&numberposts=-1&post_status=publish,private' );
		foreach( $apps as $app ) {
			$app = get_post( $app->ID );

			$parent = get_post( $app->post_parent );
			if( empty( $parent ) || 'jobman_job' != $parent->post_type )
				continue;

			add_post_meta( $app->ID, 'job', $parent->ID, false );
		}

		// Add the 'email block' field option
		foreach( $options['fields'] as $id => $field ) {
			$options['fields'][$id]['emailblock'] = 0;
		}
	}

	if( $oldversion < 18 ) {
		// Fix the GMT timestamp on existing jobs
		$jobs = get_posts( 'post_type=jobman_job&numberposts=-1&post_status=publish,private' );
		foreach( $jobs as $job ) {
			$data = array(
						'ID' => $job->ID,
						'post_date_gmt' => $job->post_date
					);
			wp_update_post( $data );
		}
	}

	update_option( 'jobman_options', $options );
}

function jobman_drop_db() {
	$options = get_option( 'jobman_options' );

	// Delete jobs
	if( $options['uninstall']['jobs'] ) {
		$jobs = get_posts( 'post_type=jobman_job&numberposts=-1' );
		if( count( $jobs ) > 0 ) {
			foreach( $jobs as $job ) {
				wp_delete_post( $job->ID );
			}
		}
	}

	// Delete applications
	if( $options['uninstall']['applications'] ) {
		$apps = get_posts( 'post_type=jobman_app&numberposts=-1' );
		if( count( $apps ) > 0 ) {
			foreach( $apps as $app ) {
				wp_delete_post( $app->ID );
			}
		}

		// Delete application uploads
		$uploads = get_posts( 'post_type=attachment&meta_key=_jobman_attachment_upload&meta_value=1&numberposts=-1' );
		if( count( $uploads ) > 0 ) {
			foreach( $uploads as $upload ) {
				wp_delete_attachment( $upload->ID );
			}
		}
	}


	// Delete categories
	if( $options['uninstall']['categories'] ) {
		$categories = get_terms( 'jobman_category', 'hide_empty=0' );

		if( count( $categories ) > 0 ) {
			foreach( $categories as $cat ) {
				wp_delete_term( $cat->term_id, 'jobman_category' );
			}
		}
	}

	// Always delete the base page, register page and application page
	wp_delete_post( $options['main_page'] );
	wp_delete_post( $options['register_page'] );

	$pages = get_posts( 'post_type=jobman_app_form&numberposts=-1' );
	if( count( $pages ) > 0 ) {
		foreach( $pages as $page ) {
			wp_delete_post( $page->ID );
		}
	}
}

?>