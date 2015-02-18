<?php
function jobman_application_setup() {
	if( array_key_exists( 'jobmansubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-application-setup' );
		jobman_application_setup_updatedb();
	}
	
	$options = get_option( 'jobman_options' );
	
	$fieldtypes = array(
						'text' => __( 'Text Input', 'jobman' ),
						'radio' => __( 'Radio Buttons', 'jobman' ),
						'select' => __( 'Select Dropdown', 'jobman' ),
						'checkbox' => __( 'Checkboxes', 'jobman' ),
						'textarea' => __( 'Large Text Input (textarea)', 'jobman' ),
						'date' => __( 'Date Selector', 'jobman' ),
						'file' => __( 'File Upload', 'jobman' ),
						'geoloc' => __( 'Geolocation', 'jobman' ),
						'heading' => __( 'Heading', 'jobman' ),
						'html' => __( 'HTML Code', 'jobman' ),
						'blank' => __( 'Blank Space', 'jobman' )
				);
				
	$categories = get_terms( 'jobman_category', 'hide_empty=0' );
?>
	<form action="" method="post">
	<input type="hidden" name="jobmansubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-application-setup' ); 
?>
	<div class="wrap">
<?php
	jobman_print_settings_tabs();
?>
<br/>
		<table id="jobman-application-setup" class="widefat page fixed">
			<thead>
			<tr>
				<th scope="col"><?php _e( 'Field Label/Type', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Categories', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Data', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Submit Filter/Filter Error Message', 'jobman' ) ?></th>
				<th scope="col" class="jobman-fieldsortorder"><?php _e('Sort Order', 'jobman' ) ?></th>
				<th scope="col" class="jobman-fielddelete"><?php _e('Delete', 'jobman' ) ?></th>
			</tr>
			</thead>
<?php
	$fields = $options['fields'];

	if( count( $fields ) > 0 ) {
		uasort( $fields, 'jobman_sort_fields' );
		foreach( $fields as $id => $field ) {
?>
			<tr class="form-table">
				<td>
					<input type="hidden" name="jobman-fieldid[]" value="<?php echo $id ?>" />
					<input class="regular-text code" type="text" name="jobman-label[]" value="<?php echo $field['label'] ?>" /><br/>
					<select name="jobman-type[]">
<?php
			foreach( $fieldtypes as $type => $label ) {
				if( $field['type'] == $type )
					$selected = ' selected="selected"';
				else
					$selected = '';
?>
						<option value="<?php echo $type ?>"<?php echo $selected ?>><?php echo $label ?></option>
<?php
			}
?>
					</select><br />
<?php
			if( 1 == $field['listdisplay'] )
				$checked = ' checked="checked"';
			else
				$checked = '';
?>
					<input type="checkbox" name="jobman-listdisplay[<?php echo $id ?>]" value="1"<?php echo $checked ?> /> <?php _e( 'Show this field in the Application List?', 'jobman' ) ?><br/>
<?php
			if( 1 == $field['emailblock'] )
				$checked = ' checked="checked"';
			else
				$checked = '';
?>
					<input type="checkbox" name="jobman-emailblock[<?php echo $id ?>]" value="1"<?php echo $checked ?> /> <?php _e( 'Block this field from application emails?', 'jobman' ) ?>
				</td>
				<td><div class="jobman-categories-list">
<?php
			if( count( $categories ) > 0 ) {
				foreach( $categories as $cat ) {
					$checked = '';
					if( array_key_exists( 'categories', $field ) && in_array( $cat->term_id, $field['categories'] ) )
						$checked = ' checked="checked"';
?>
					<input type="checkbox" name="jobman-categories[<?php echo $id ?>][]" value="<?php echo $cat->term_id ?>"<?php echo $checked ?> /> <?php echo $cat->name ?><br/>
<?php
				}
			}
?>
				</div></td>
				<td><textarea class="large-text code" name="jobman-data[]"><?php echo $field['data'] ?></textarea></td>
				<td>
<?php
			if( 1 == $field['mandatory'] )
				$checked = ' checked="checked"';
			else
				$checked = '';
?>
					<input type="checkbox" name="jobman-mandatory[<?php echo $id ?>]" value="1"<?php echo $checked ?> /> <?php _e( 'Mandatory field?', 'jobman' ) ?><br/>
					<textarea class="large-text code" name="jobman-filter[]"><?php echo $field['filter'] ?></textarea><br/>
					<input class="regular-text code" type="text" name="jobman-error[]" value="<?php echo esc_attr( $field['error'] ) ?>" />
				</td>
				<td><a href="#" onclick="jobman_sort_field_up( this ); return false;"><?php _e( 'Up', 'jobman' ) ?></a> <a href="#" onclick="jobman_sort_field_down( this ); return false;"><?php _e( 'Down', 'jobman' ) ?></a></td>
				<td><a href="#" onclick="jobman_delete( this, 'jobman-fieldid', 'jobman-delete-list' ); return false;"><?php _e( 'Delete', 'jobman' ) ?></a></td>
			</tr>
<?php
		}
	}

	$template = '<tr class="form-table">';
	$template .= '<td><input type="hidden" name="jobman-fieldid[]" value="-1" /><input class="regular-text code" type="text" name="jobman-label[]" /><br/>';
	$template .= '<select name="jobman-type[]">';

	foreach( $fieldtypes as $type => $label ) {
		$template .= '<option value="' . $type . '">' . $label . '</option>';
	}
	$template .= '</select><br/>';
	$template .= '<input type="checkbox" name="jobman-listdisplay" value="1" />' . __( 'Show this field in the Application List?', 'jobman' ) . '<br/>';
	$template .= '<input type="checkbox" name="jobman-emailblock" value="1" />' . __( 'Block this field from application emails?', 'jobman' ) . '</td>';
	$template .= '<td>';
	if( count( $categories ) > 0 ) {
		foreach( $categories as $cat ) {
			$template .= '<input type="checkbox" name="jobman-categories" class="jobman-categories" value="' . $cat->term_id . '" />' . $cat->name . '<br/>';
		}
	}
	$template .= '</td>';
	$template .= '<td><textarea class="large-text code" name="jobman-data[]"></textarea></td>';
	$template .= '<td><input type="checkbox" name="jobman-mandatory" value="1" />' . __( 'Mandatory field?', 'jobman' ) . '<br/>';
	$template .= '<textarea class="large-text code" name="jobman-filter[]"></textarea><br/>';
	$template .= '<input class="regular-text code" type="text" name="jobman-error[]" /></td>';
	$template .= '<td><a href="#" onclick="jobman_sort_field_up( this ); return false;">' . __( 'Up', 'jobman' ) . '</a> <a href="#" onclick="jobman_sort_field_down( this ); return false;">' . __( 'Down', 'jobman' ) . '</a></td>';
	$template .= '<td><a href="#" onclick="jobman_delete( this, \\\'jobman-fieldid\\\', \\\'jobman-delete-list\\\' ); return false;">' . __( 'Delete', 'jobman' ) . '</a></td></tr>';
	
	// Replace names for the empty version being displayed
	$display_template = str_replace( 'jobman-categories', 'jobman-categories[new][0][]', $template );
	$display_template = str_replace( 'jobman-listdisplay', 'jobman-listdisplay[new][0][]', $display_template );
	$display_template = str_replace( 'jobman-emailblock', 'jobman-emailblock[new][0][]', $display_template );
	$display_template = str_replace( 'jobman-mandatory', 'jobman-mandatory[new][0][]', $display_template );
	$display_template = str_replace( "\\'", "'", $display_template );
	
	echo $display_template;
?>
		<tr id="jobman-fieldnew">
				<td colspan="6" style="text-align: right;">
					<input type="hidden" name="jobman-delete-list" id="jobman-delete-list" value="" />
					<a href="#" onclick="jobman_new( 'jobman-fieldnew', 'field' ); return false;"><?php _e( 'Add New Field', 'jobman' ) ?></a>
				</td>
		</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Application Form', 'jobman' ) ?>" /></p>
<script type="text/javascript"> 
//<![CDATA[
	jobman_templates['field'] = '<?php echo $template ?>';
//]]>
</script> 
	</div>
	</form>
<?php
}

function jobman_application_setup_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$ii = 0;
	$newcount = -1;

	foreach( $_REQUEST['jobman-fieldid'] as $id ) {
		if( -1 == $id ) {
			$newcount++;
			$listdisplay = 0;
			$emailblock = 0;
			$mandatory = 0;
			if( array_key_exists( 'jobman-listdisplay', $_REQUEST ) && array_key_exists( 'new', $_REQUEST['jobman-listdisplay'] ) && array_key_exists( $newcount, $_REQUEST['jobman-listdisplay']['new'] ) )
				$listdisplay = 1;
			if( array_key_exists( 'jobman-emailblock', $_REQUEST ) && array_key_exists( 'new', $_REQUEST['jobman-emailblock'] ) && array_key_exists( $newcount, $_REQUEST['jobman-emailblock']['new'] ) )
				$emailblock = 1;
			if( array_key_exists( 'jobman-mandatory', $_REQUEST ) && array_key_exists( 'new', $_REQUEST['jobman-mandatory'] ) && array_key_exists( $newcount, $_REQUEST['jobman-mandatory']['new'] ) )
				$mandatory = 1;

			// INSERT new field
			if( '' != $_REQUEST['jobman-label'][$ii]  || '' != $_REQUEST['jobman-data'][$ii] || 'blank' == $_REQUEST['jobman-type'][$ii] ) {
					$options['fields'][] = array(
												'label' => $_REQUEST['jobman-label'][$ii],
												'type' => $_REQUEST['jobman-type'][$ii],
												'listdisplay' => $listdisplay,
												'emailblock' => $emailblock,
												'data' => stripslashes( $_REQUEST['jobman-data'][$ii] ),
												'mandatory' => $mandatory,
												'filter' => stripslashes( $_REQUEST['jobman-filter'][$ii] ),
												'error' => stripslashes( $_REQUEST['jobman-error'][$ii] ),
												'sortorder' => $ii
											);
			}
			else {
				// No input, not a 'blank' field. Don't insert into the DB.
				$ii++;
				continue;
			}
		}
		else {
			$listdisplay = 0;
			$emailblock = 0;
			$mandatory = 0;
			if( array_key_exists( 'jobman-listdisplay', $_REQUEST ) && array_key_exists( $id, $_REQUEST['jobman-listdisplay'] ) )
				$listdisplay = 1;
			if( array_key_exists( 'jobman-emailblock', $_REQUEST ) && array_key_exists( $id, $_REQUEST['jobman-emailblock'] ) )
				$emailblock = 1;
			if( array_key_exists( 'jobman-mandatory', $_REQUEST ) && array_key_exists( $id, $_REQUEST['jobman-mandatory'] ) )
				$mandatory = 1;

			// UPDATE existing field
			if( array_key_exists( $id, $options['fields'] ) ) {
				$options['fields'][$id]['label'] = $_REQUEST['jobman-label'][$ii];
				$options['fields'][$id]['type'] = $_REQUEST['jobman-type'][$ii];
				$options['fields'][$id]['listdisplay'] = $listdisplay;
				$options['fields'][$id]['emailblock'] = $emailblock;
				$options['fields'][$id]['data'] = stripslashes( $_REQUEST['jobman-data'][$ii] );
				$options['fields'][$id]['mandatory'] = $mandatory;
				$options['fields'][$id]['filter'] = stripslashes( $_REQUEST['jobman-filter'][$ii] );
				$options['fields'][$id]['error'] = stripslashes( $_REQUEST['jobman-error'][$ii] );
				$options['fields'][$id]['sortorder'] = $ii;
			}
		}

		$categories = array();
		if( array_key_exists('jobman-categories', $_REQUEST ) ) {
			if( -1 == $id ) {
				if( array_key_exists( 'new', $_REQUEST['jobman-categories'] ) && array_key_exists( $newcount, $_REQUEST['jobman-categories']['new'] ) ) {
					$categories = $_REQUEST['jobman-categories']['new'][$newcount];
					$keys = array_keys( $options['fields'] );
					$id = end( $keys );
				}
			}
			else if( array_key_exists( $id, $_REQUEST['jobman-categories'] ) ) {
				$categories = $_REQUEST['jobman-categories'][$id];
			}
		}
		if( count( $categories ) > 0 ) {
			if( array_key_exists( $id, $options['fields'] ) ) {
				$options['fields'][$id]['categories'] = array();
				foreach( $categories as $categoryid ) {
					$options['fields'][$id]['categories'][] = $categoryid;
				}
			}
		}
		else if( array_key_exists( $id, $options['fields'] ) ) {
			$options['fields'][$id]['categories'] = array();
		}
		
		$ii++;
	}
	
	$deletes = explode( ',', $_REQUEST['jobman-delete-list'] );
	foreach( $deletes as $delete ) {
		unset( $options['fields'][$delete] );
	}

	update_option( 'jobman_options', $options );
}

?>