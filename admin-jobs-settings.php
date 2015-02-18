<?php
function jobman_job_setup() {
	if( array_key_exists( 'jobmansubmit', $_REQUEST ) ) {
		check_admin_referer( 'jobman-job-setup' );
		jobman_job_setup_updatedb();
	}
	
	$options = get_option( 'jobman_options' );
	
	$fieldtypes = array(
						'text' => __( 'Text Input', 'jobman' ),
						'radio' => __( 'Radio Buttons', 'jobman' ),
						'checkbox' => __( 'Checkboxes', 'jobman' ),
						'textarea' => __( 'Large Text Input (textarea)', 'jobman' ),
						'date' => __( 'Date Selector', 'jobman' ),
						'file' => __( 'File Upload', 'jobman' ),
						'heading' => __( 'Heading', 'jobman' ),
						'html' => __( 'HTML Code', 'jobman' ),
						'blank' => __( 'Blank Space', 'jobman' )
				);
				
	$categories = get_terms( 'jobman_category', 'hide_empty=0' );
?>
	<form action="" method="post">
	<input type="hidden" name="jobmansubmit" value="1" />
<?php 
	wp_nonce_field( 'jobman-job-setup' ); 
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
				<th scope="col"><?php _e( 'Data', 'jobman' ) ?></th>
				<th scope="col" class="jobman-fieldsortorder"><?php _e('Sort Order', 'jobman' ) ?></th>
				<th scope="col" class="jobman-fielddelete"><?php _e('Delete', 'jobman' ) ?></th>
			</tr>
			</thead>
<?php
	$fields = $options['job_fields'];

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
			if( array_key_exists( 'listdisplay', $field ) && 1 == $field['listdisplay'] )
				$checked = ' checked="checked"';
			else
				$checked = '';
?>
					<input type="checkbox" name="jobman-listdisplay[<?php echo $id ?>]" value="1"<?php echo $checked ?> /> <?php _e( 'Show this field in the Admin Job List?', 'jobman' ) ?>

				</td>
				<td><textarea class="large-text code" name="jobman-data[]"><?php echo $field['data'] ?></textarea></td>
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
		$template .= '<option value="' . $type. '">' . $label . '</option>';
	}
	$template .= '</select><br/>';
	$template .= '<input type="checkbox" name="jobman-listdisplay" value="1" />' . __( 'Show this field in the Admin Job List?', 'jobman' ) . '</td>';
	$template .= '<td><textarea class="large-text code" name="jobman-data[]"></textarea></td>';
	$template .= '<td><a href="#" onclick="jobman_sort_field_up( this ); return false;">' . __( 'Up', 'jobman' ) . '</a> <a href="#" onclick="jobman_sort_field_down( this ); return false;">' . __( 'Down', 'jobman' ) . '</a></td>';
	$template .= '<td><a href="#" onclick="jobman_delete( this, \\\'jobman-fieldid\\\', \\\'jobman-delete-list\\\' ); return false;">' . __( 'Delete', 'jobman' ) . '</a></td></tr>';
		
	// Replace names for the empty version being displayed
	$display_template = str_replace( 'jobman-listdisplay', 'jobman-listdisplay[new][0][]', $template );
	$display_template = str_replace( "\\'", "'", $display_template );

	echo $display_template;
?>
		<tr id="jobman-fieldnew">
				<td colspan="4" style="text-align: right;">
					<input type="hidden" name="jobman-delete-list" id="jobman-delete-list" value="" />
					<a href="#" onclick="jobman_new( 'jobman-fieldnew', 'field' ); return false;"><?php _e( 'Add New Field', 'jobman' ) ?></a>
				</td>
		</tr>
		</table>
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Update Jobs Form', 'jobman' ) ?>" /></p>
<script type="text/javascript"> 
//<![CDATA[
	jobman_templates['field'] = '<?php echo $template ?>';
//]]>
</script> 
	</div>
	</form>
<?php
}

function jobman_job_setup_updatedb() {
	$options = get_option( 'jobman_options' );
	
	$ii = 0;
	$newcount = -1;

	foreach( $_REQUEST['jobman-fieldid'] as $id ) {
		if( -1 == $id ) {
			$newcount++;

			$listdisplay = 0;
			if( array_key_exists( 'jobman-listdisplay', $_REQUEST ) && array_key_exists( 'new', $_REQUEST['jobman-listdisplay'] ) && array_key_exists( $newcount, $_REQUEST['jobman-listdisplay']['new'] ) )
				$listdisplay = 1;

			// INSERT new field
			if( '' != $_REQUEST['jobman-label'][$ii]  || '' != $_REQUEST['jobman-data'][$ii] || 'blank' == $_REQUEST['jobman-type'][$ii] ) {
					$options['job_fields'][] = array(
												'label' => $_REQUEST['jobman-label'][$ii],
												'type' => $_REQUEST['jobman-type'][$ii],
												'listdisplay' => $listdisplay,
												'data' => stripslashes( $_REQUEST['jobman-data'][$ii] ),
												'description' => '',
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
			if( array_key_exists( 'jobman-listdisplay', $_REQUEST ) && array_key_exists( $id, $_REQUEST['jobman-listdisplay'] ) )
				$listdisplay = 1;
			// UPDATE existing field
			if( array_key_exists( $id, $options['fields'] ) ) {
				$options['job_fields'][$id]['label'] = $_REQUEST['jobman-label'][$ii];
				$options['job_fields'][$id]['type'] = $_REQUEST['jobman-type'][$ii];
				$options['job_fields'][$id]['listdisplay'] = $listdisplay;
				$options['job_fields'][$id]['data'] = stripslashes( $_REQUEST['jobman-data'][$ii] );
				$options['job_fields'][$id]['sortorder'] = $ii;
			}
		}

		$ii++;
	}
	
	$deletes = explode( ',', $_REQUEST['jobman-delete-list'] );
	foreach( $deletes as $delete ) {
		unset( $options['job_fields'][$delete] );
	}

	update_option( 'jobman_options', $options );
}

?>