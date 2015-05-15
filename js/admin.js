jobman_templates = new Array();
jobman_new_count = 0;

function jobman_sort_field_up(div) {
	jQuery(div).parent().parent().prev().before(jQuery(div).parent().parent());
}

function jobman_sort_field_down(div) {
	jQuery(div).parent().parent().next().after(jQuery(div).parent().parent());
}

function jobman_delete(div, idname, delete_list) {
	// TODO: Remove .attr() calls when we stop support jQuery < 1.6
	var havePropFunc = false;
	if( typeof jQuery(div).prop == 'function' )
		havePropFunc = true;
	
	var id;
	if( havePropFunc )
		id = jQuery(div).parent().parent().find('[name^="' + idname + '"]').prop('value');
	else
		id = jQuery(div).parent().parent().find('[name^="' + idname + '"]').attr('value');
	
	if(id == '-1') {
		jQuery(div).parent().parent().remove();
		return;
	}
	
	var list;
	if( havePropFunc )
		list = jQuery('#' + delete_list).prop('value');
	else
		list = jQuery('#' + delete_list).attr('value');

	if(list == "") {
		list = id;
	}
	else {
		list = list + ',' + id;
	}

	if( havePropFunc )
		jQuery('#' + delete_list).prop('value', list);
	else
		jQuery('#' + delete_list).attr('value', list);
	
	jQuery(div).parent().parent().remove();
}

function jobman_new(rowid, template_name) {
	jobman_new_count++;

	var htmlDOM = jQuery(jobman_templates[template_name]);
	jQuery('input', htmlDOM).each(jobman_nameFilter);

	jQuery('#' + rowid).before(htmlDOM);
}

jobman_nameFilter = function (i, el) {
	// TODO: Remove .attr() calls when we stop support jQuery < 1.6
	var name;
	if( typeof jQuery(el).prop == 'function' ) {
		name = jQuery(el).prop('name');
		if( 'jobman-categories' == name || 'jobman-listdisplay' == name || 'jobman-mandatory' == name ) {
			jQuery(el).prop('name', name + '[new]');
		}
	}
	else {
		name = jQuery(el).attr('name');
		if( 'jobman-categories' == name || 'jobman-listdisplay' == name || 'jobman-mandatory' == name ) {
			jQuery(el).attr('name', name + '[new]');
		}
	}
}
