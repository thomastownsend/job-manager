function jobman_apply_filter() {
	var ii, field, value, type;
	var empty = new Array();
	for( ii = 0; ii < jobman_mandatory_ids.length; ii++ ) {
		field = jQuery('[name^="jobman-field-' + jobman_mandatory_ids[ii] + '"]');
		
		// jQuery 1.6 introduces .prop(), and breaks .attr() backwards compatibility
		// TODO: Remove the .attr() code when we stop suporting jQuery < 1.6
		if( typeof field.prop == 'function' ) {
			value = field.prop('value');
			type = field.prop('type');
		}
		else {
			value = field.attr('value');
			type = field.attr('type');
		}		
		
		if( 1 == field.length && '' == value ) {
			empty.push( jobman_mandatory_labels[ii] );
		}
		
		if( 'radio' == type || 'checkbox' == type ) {
			var checked = false;
			
			for( var jj = 0; jj < field.length; jj++ ) {
				if( field[jj].checked ) {
					checked = true;
					break;
				}
			}
			
			if( ! checked ) {
				empty.push( jobman_mandatory_labels[ii] );
			}
		}
	}
	
	if( empty.length > 0 ) {
		var error = jobman_strings['apply_submit_mandatory_warning'] + ":\n";
		for( ii = 0; ii < empty.length; ii++ ) {
			error += empty[ii] + "\n";
		}
		alert( error );
		return false;
	}
	else {
		return true;
	}
}
