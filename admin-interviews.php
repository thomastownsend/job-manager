<?php
function jobman_interviews() {
	$options = get_option( 'jobman_options' );
	
	$display_type = $options['interview_default_view'];
	$filter = '';
	
	if( array_key_exists( 'display', $_REQUEST ) )
		$display_type = $_REQUEST['display'];
		
	if( array_key_exists( 'filter', $_REQUEST ) && ! empty( $_REQUEST['filter'] ) )
		$filter = $_REQUEST['filter'];
		
	if( array_key_exists( 'new-interview', $_REQUEST ) )
		jobman_interview_new();
		
	if( array_key_exists( 'comment', $_REQUEST ) )
		jobman_store_comment();
		
	switch( $display_type ) {
		case 'year':
			if( empty( $filter ) )
				$filter = date( 'Y' );
			jobman_interview_year( $filter );
			break;
		case 'day':
			if( empty( $filter ) )
				$filter = date( 'Y-m-d' );
			jobman_interview_day( $filter );
			break;
		case 'interview':
			jobman_interview_details( $filter );
			break;
		case 'job':
			jobman_interview_job( $filter );
			break;
		case 'application':
			jobman_interview_application( $filter );
			break;
		case 'month':
		default:
			if( empty( $filter ) )
				$filter = date( 'Y-m' );
			jobman_interview_month( $filter );
			break;
	}
}

function jobman_interview_month( $filter, $caltype = 'full' ) {
	$year = date( 'Y', strtotime( $filter ) );
	$month = date( 'n', strtotime( $filter ) );
	
	$firstday = date( 'w', strtotime( "$filter-1" ) );
	if( ! $firstday )
		$firstday = 7;
	
	$days = cal_days_in_month( CAL_GREGORIAN, $month, $year );
	
	$tableclass = '';
	$dayformat = 'D';
	if( 'full' == $caltype ) {
		$tableclass = 'widefat page fixed';
		$dayformat = 'l';
	}
?>
	<div class="wrap">
<?php
	if( 'full' == $caltype ) {
?>
		<h2><?php printf( __( 'Job Manager: %1s Interviews', 'jobman' ), date_i18n( 'F Y', strtotime( $filter ) ) ) ?></h2>
		<form action="" method="post">
		<div class="jobman-interview-nav">
			<a href="<?php echo admin_url( 'admin.php?page=jobman-interviews&amp;display=month&amp;filter=' . date( 'Y-m', strtotime( "$filter -1 month" ) ) ) ?>">&lt;&lt;-- <?php echo date_i18n( 'F Y', strtotime( "$filter -1 month" ) ) ?></a>
			<select name="month">
<?php
		for( $ii = 1; $ii <= 12; $ii++ ) {
			$iiname = date_i18n( 'F', strtotime( "2009-$ii" ) );
			
			$selected = '';
			if( $ii == $month )
				$selected = ' selected="selected"';
				
			echo "<option value='$ii'$selected>$iiname</option>";
		}
?>
			</select>
			<select name="year">
<?php
		for( $ii = $year - 10; $ii <= $year + 10; $ii++ ) {
			$selected = '';
			if( $ii == $year )
				$selected = ' selected="selected"';
				
			echo "<option value='$ii'$selected>$ii</option>";
		}
?>
			</select>
			<input type="submit" name="submit" value="<?php _e( 'Go', 'jobman' ) ?>" />
			<a href="<?php echo admin_url( 'admin.php?page=jobman-interviews&amp;display=month&amp;filter=' . date( 'Y-m', strtotime( "$filter +1 month" ) ) ) ?>"><?php echo date_i18n( 'F Y', strtotime( "$filter +1 month" ) ) ?> --&gt;&gt;</a>
		</div>
		</form>
<?php
	}
	else {
?>
		<div class="jobman-month-name"><a href="<?php echo admin_url( "admin.php?page=jobman-interviews&amp;display=month&amp;filter=$filter" ) ?>"><?php echo date_i18n( 'F', strtotime( $filter ) ) ?></a></div>
<?php
	}
?>
		<form action="" method="post">
		<table class="<?php echo $tableclass ?>">
			<thead>
			<tr>
				<th><?php echo date_i18n( $dayformat, strtotime( 'Monday' ) ) ?></th>
				<th><?php echo date_i18n( $dayformat, strtotime( 'Tuesday' ) ) ?></th>
				<th><?php echo date_i18n( $dayformat, strtotime( 'Wednesday' ) ) ?></th>
				<th><?php echo date_i18n( $dayformat, strtotime( 'Thursday' ) ) ?></th>
				<th><?php echo date_i18n( $dayformat, strtotime( 'Friday' ) ) ?></th>
				<th><?php echo date_i18n( $dayformat, strtotime( 'Saturday' ) ) ?></th>
				<th><?php echo date_i18n( $dayformat, strtotime( 'Sunday' ) ) ?></th>
			</tr>
			</thead>
<?php
	for( $daycount = 1; ( $daycount - $firstday < $days ) || ( $daycount % 7 != 1 ); $daycount++ ) {
		if( $daycount % 7 == 1 )
			echo '<tr>';
		
		echo '<td>';
		if( $daycount >= $firstday && ( $daycount - $firstday ) < $days ) {
			$day = $daycount - $firstday + 1;
			$interviews = jobman_get_interviews( "$filter-$day" );
			if( count( $interviews ) )
				echo "<a href='" . admin_url( "admin.php?page=jobman-interviews&amp;display=day&amp;filter=$filter-$day" ) . "'>$day</a>";
			else
				echo $day;
				
			if( 'full' == $caltype ) {
				foreach( $interviews as $interview ) {
					echo "<br/><a href='" . admin_url( "admin.php?page=jobman-interviews&amp;display=interview&amp;filter=$interview->ID" ) . "'>$interview->post_title</a>";
				}
			}
		}
		echo '</td>';
		
		if( $daycount % 7 == 0 )
			echo '</tr>';
?>
<?php
	}
?>
		</table>
		</form>
	</div>
<?php
}

function jobman_interview_year( $filter ) {
	$year = $filter;
?>
	<div class="wrap">
		<h2><?php printf( __( 'Job Manager: %1s Interviews', 'jobman' ), $year ) ?></h2>
		<form action="" method="post">
		<div class="jobman-interview-nav">
			<a href="<?php echo admin_url( 'admin.php?page=jobman-interviews&amp;display=year&amp;filter=' . ( $year - 1 ) ) ?>">&lt;&lt;-- <?php echo $year - 1 ?></a>
			<select name="filter">
<?php
		for( $ii = $year - 10; $ii <= $year + 10; $ii++ ) {
			$selected = '';
			if( $ii == $year )
				$selected = ' selected="selected"';
				
			echo "<option value='$ii'$selected>$ii</option>";
		}
?>
			</select>
			<input type="submit" name="submit" value="<?php _e( 'Go', 'jobman' ) ?>" />
			<a href="<?php echo admin_url( 'admin.php?page=jobman-interviews&amp;display=year&amp;filter=' . ( $year + 1 ) ) ?>"><?php echo $year + 1 ?> --&gt;&gt;</a>
		</div>
		</form>
<?php
	for( $ii = 1; $ii <= 12; $ii++ ) {
		$clear = '';
		if( $ii % 3 == 1 )
			$clear = ' jobman-month-clear';
			
		echo "<div class='jobman-month$clear'>";
		jobman_interview_month( "$year-$ii", 'short' );
		echo '</div>';
	}
?>
	</div>
<?php
}

function jobman_get_interviews( $date ) {
	$filter = array(
				'post_type' => 'jobman_interview',
				'numberposts' => -1,
				'post_status' => 'private'
			);
	
	$matches = array();
	if( preg_match( '/^(\d+)-(\d+)-(\d+)$/', $date, $matches ) ) {
		$filter['year'] = $matches[1];
		$filter['monthnum'] = $matches[2];
		$filter['day'] = $matches[3];
	}
	else {
		// register to filter WHERE on post_date range
	}
	
	$applications = get_posts( $filter );
	
	if( ! empty( $applications ) )
		return $applications;
	
	return array();
}

function jobman_interview_day( $filter ) {
	$year = date( 'Y', strtotime( $filter ) );
	$month = date( 'n', strtotime( $filter ) );
	$day = date( 'j', strtotime( $filter ) );
?>
	<div class="wrap">
		<h2><?php printf( __( 'Job Manager: %1s Interviews', 'jobman' ), date_i18n( 'l jS F Y', strtotime( $filter ) ) ) ?></h2>
		<form action="" method="post">
		<div class="jobman-interview-nav">
			<a href="<?php echo admin_url( 'admin.php?page=jobman-interviews&amp;display=day&amp;filter=' . date( 'Y-m-d', strtotime( "$filter -1 day" ) ) ) ?>">&lt;&lt;-- <?php echo date_i18n( 'l jS F Y', strtotime( "$filter -1 day" ) ) ?></a>
			<select name="day">
<?php
		for( $ii = 1; $ii <= 31; $ii++ ) {
			$selected = '';
			if( $ii == $day )
				$selected = ' selected="selected"';
				
			echo "<option value='$ii'$selected>$ii</option>";
		}
?>
			</select>
			<select name="month">
<?php
		for( $ii = 1; $ii <= 12; $ii++ ) {
			$iiname = date_i18n( 'F', strtotime( "2009-$ii" ) );
			
			$selected = '';
			if( $ii == $month )
				$selected = ' selected="selected"';
				
			echo "<option value='$ii'$selected>$iiname</option>";
		}
?>
			</select>
			<select name="year">
<?php
		for( $ii = $year - 10; $ii <= $year + 10; $ii++ ) {
			$selected = '';
			if( $ii == $year )
				$selected = ' selected="selected"';
				
			echo "<option value='$ii'$selected>$ii</option>";
		}
?>
			</select>
			<input type="submit" name="submit" value="<?php _e( 'Go', 'jobman' ) ?>" />
			<a href="<?php echo admin_url( 'admin.php?page=jobman-interviews&amp;display=day&amp;filter=' . date( 'Y-m-d', strtotime( "$filter +1 day" ) ) ) ?>"><?php echo date_i18n( 'l jS F Y', strtotime( "$filter +1 day" ) ) ?> --&gt;&gt;</a>
		</div>
		</form>
<?php
	$interviews = jobman_get_interviews( $filter );
	
	if( ! empty( $interviews ) ) {
?>
		<table class="widefat page fixed">
			<thead>
			<tr>
				<th><?php _e( 'Interview Time', 'jobman' ) ?></th>
				<th><?php _e( 'Interview Rating', 'jobman' ) ?></th>
				<th><?php _e( 'Interview Details', 'jobman' ) ?></th>
			<tr>
			</thead>
<?php
		foreach( $interviews as $interview ) {
			$rating = get_post_meta( $interview->ID, 'rating', true );
?>
			<tr>
				<td><?php echo date( 'H:i', strtotime( $interview->post_date ) ) ?></td>
				<td><?php jobman_print_rating_stars( $interview->ID, $rating ) ?></td>
				<td><a href="<?php echo admin_url( "admin.php?page=jobman-interviews&amp;display=interview&amp;filter=$interview->ID" ) ?>"><?php echo $interview->post_title ?></a></td>
			</tr>
<?php
		}
		echo '</table>';
	}
	else {
		echo '<p class="error">' . __( 'No applications scheduled for this day.', 'jobman' ) . '</p>';
	}
?>
	</div>
<?php
}

function jobman_interview_details( $iid ) {
	$interview = get_post( $iid );
?>
	<div class="wrap">
	<h2><?php _e( 'Job Manager: Interview Details', 'jobman' ) ?></h2>
<?php
	$aid = get_post_meta( $iid, 'application', true );
	$widths = array( '49%', '49%' );
	$functions = array(
					array( 'jobman_application_display_details' ),
					array( 'jobman_comments', 'jobman_interview_past_comments', 'jobman_comments' )
				);
	$titles = array(
				array( __( 'Application', 'jobman' ) ),
				array( __( 'Interview Comments', 'jobman' ), __( 'Previous Interview Comments', 'jobman' ), __( 'Application Comments', 'jobman' ) )
			);
	$params = array(
					array( array( $aid ) ),
					array( array( $iid, true ), array( $iid, $aid ), array( $aid ) )
			);
	jobman_create_dashboard( $widths, $functions, $titles, $params );
?>
	</div>
<?php
}

function jobman_interview_past_comments( $current_iid, $aid ) {
	$interviews = get_post_meta( $aid, 'interview', false );
	
	foreach( $interviews as $iid ) {
		if( $iid != $current_iid ) {
			$comments = get_comments( "post_id=$iid" );
			
			$interview = get_post( $iid );
			
			$rating = get_post_meta( $iid, 'rating', true );
			
			echo '<strong>' . date( 'Y-m-d H:i', strtotime( $interview->post_date ) ) . '</strong><br/>';
			
			jobman_print_rating_stars( $interview->ID, $rating, NULL, true );
			
			jobman_display_comments( $comments );
		}
	}
}

function jobman_interview_job( $jid ) {
}

function jobman_interview_application( $aid, $display = 'full' ) {
	$filter = array( 
				'post_type' => 'jobman_interview',
				'post_status' => 'private',
				'numberposts' => '-1',
				'meta_key' => 'application',
				'meta_value' => $aid
			);
			
	$interviews = get_posts( $filter );
?>
	<div class="wrap">
<?php
	if( 'full' == $display ) {
?>
	<h2><?php _e( 'Job Manager: Application Interview Summary', 'jobman' ) ?></h2>
<?php
	}
	jobman_interview_new_form( date( 'Y-m-d' ), $aid );
?>
	<table class="widefat page fixed">
		<thead>
		<tr>
			<th><?php _e( 'Interview Date', 'jobman' ) ?></th>
			<th><?php _e( 'Interview Rating', 'jobman' ) ?></th>
			<th><?php _e( 'Interview Details', 'jobman' ) ?></th>
		</tr>
		</thead>
<?php
	if( count( $interviews ) ) {
		foreach( $interviews as $interview ) {
			$rating = get_post_meta( $interview->ID, 'rating', true );
?>
		<tr>
			<td><?php echo date( 'Y-m-d H:i', strtotime( $interview->post_date ) ) ?></td>
			<td><?php jobman_print_rating_stars( $interview->ID, $rating ) ?></td>
			<td><a href="<?php echo admin_url( "admin.php?page=jobman-interviews&amp;display=interview&amp;filter=$interview->ID" ) ?>"><?php _e( 'Interview Details', 'jobman' ) ?></a></td>
		</tr>
<?php
		}
	}
	else {
?>
		<tr>
			<td colspan="3"><?php _e( 'There are no interviews for this application.', 'jobman' ) ?></td>
		</tr>
<?php
	}
?>
	</table>
	</div>
<?php
}

function jobman_interview_new_form( $date, $aid ) {
?>
	<div class="jobman-new-interview">
	<form action="" method="post">
		<input type="hidden" name="new-interview" value="1" />
<?php
	if( ! empty( $aid ) ) {
?>
		<input type="hidden" name="application" value="<?php echo $aid ?>" />
<?php
	}
	else {
?>
<?php
	}
?>
		<input type='text' class='datepicker' name='date' value='<?php echo date( 'Y-m-d' ) ?>' />
		<select class="time" name="hour">
<?php
	for( $ii = 0; $ii < 24; $ii++ ) {
		$nice = sprintf( '%02d', $ii );
		echo "<option value='$nice'>$nice</option>";
	}
?>
		</select> :
		<select class="time" name="minute">
<?php
	for( $ii = 0; $ii < 60; $ii += 5 ) {
		$nice = sprintf( '%02d', $ii );
		echo "<option value='$nice'>$nice</option>";
	}
?>
		</select>
		<input type="submit" name="submit" value="<?php _e( 'New Interview', 'jobman' ) ?>" />
	</form>
	</div>
<?php
}

function jobman_interview_new() {
	$options = get_option( 'jobman_options' );
	
	$aid = $_REQUEST['application'];
	$post_date = $_REQUEST['date'] . ' ' . $_REQUEST['hour'] . ':' . $_REQUEST['minute'] . ':00';

	$title = $options['interview_title_text'];
	if( ! empty( $title ) )
		$title .= ' ';

	$fids = $options['interview_title_fields'];

	if( count( $fids ) > 0 ) {
		foreach( $fids as $fid ) {
			$data = get_post_meta( $aid, "data$fid", true );
			if( ! empty( $data ) )
				$title .= $data . ' ';
		}
	}
	
	trim( $title );
	
	if( empty( $title ) )
		$title = __( 'Interview', 'jobman' );

	$interview = array(
					'post_type' => 'jobman_interview',
					'post_status' => 'private',
					'post_date' => $post_date,
					'post_title' => $title
				);
	
	$iid = wp_insert_post( $interview );
	
	add_post_meta( $iid, 'application', $aid, true );
	
	add_post_meta( $aid, 'interview', $iid, false );
}
?>
