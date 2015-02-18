<?php
function jobman_list_emails() {

	if( array_key_exists('emailid', $_REQUEST ) ) {
		jobman_email_display( $_REQUEST['emailid'] );
		return;
	}
?>
	<div class="wrap">
	    <h2><?php _e( 'Job Manager: Emails', 'jobman' ) ?></h2>
	    
	    <p><?php _e( 'In the "Applications Sent To" column, click the number to go to that application, or click the asterisk (*) next to it to see other emails sent to that application.', 'jobman' ) ?></p>
	    
		<table id="jobman-emails-list" class="widefat page fixed" cellspacing="0">
			<thead>
			<tr>
				<th scope="col"><?php _e( 'Date', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Subject', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Applications Sent To', 'jobman' ) ?></th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<th scope="col"><?php _e( 'Date', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Subject', 'jobman' ) ?></th>
				<th scope="col"><?php _e( 'Applications Sent To', 'jobman' ) ?></th>
			</tr>
			</tfoot>
<?php
	$args = array();
	$args['post_type'] = 'jobman_email';
	$args['post_status'] = 'private,publish';
	$args['offset'] = 0;
	$args['numberposts'] = -1;

	$emails = get_posts( $args );
	
	if( count( $emails ) > 0 ) {
		foreach( $emails as $email ) {
			$apps = get_posts( "post_type=jobman_app&meta_key=contactmail&meta_value=$email->ID&numberposts=-1&post_status=publish,private" );

			$appstrings = array();
			$appids = array();
			foreach( $apps as $app ) {
				$appstrings[] = "<a href='?page=jobman-list-applications&amp;appid=$app->ID'>$app->ID</a> <a href='?page=jobman-list-emails&amp;appid=$app->ID'>*</a>";
				$appids[] = $app->ID;
				
			}
			if( array_key_exists( 'appid', $_REQUEST ) && ! in_array( $_REQUEST['appid'], $appids ) )
				continue;
?>
			<tr>
			    <td><?php echo $email->post_date ?></td>
			    <td><a href="?page=jobman-list-emails&amp;emailid=<?php echo $email->ID ?>"><?php echo $email->post_title ?></a></td>
			    <td>
<?php
			echo implode( ', ', $appstrings );
?>
				</td>
			</tr>
<?php
		}
	}
	else {
?>
			<tr>
				<td colspan="3"><?php _e( 'There are currently no emails in the system.', 'jobman' ) ?></td>
			</tr>
<?php
	}
?>
		</table>
	</div>
<?php
}

function jobman_email_display( $emailid ) {
	$options = get_option( 'jobman_options' );
	$fromid = $options['application_email_from'];

	$email = get_post( $emailid );
	
	if( NULL == $email ) {
	    echo '<p class="error">' . __( 'No such email.', 'jobman' ) . '</p>';
	    return;
	}
?>
	<div class="wrap">
	    <h2><?php _e( 'Job Manager: Email', 'jobman' ) ?></h2>

	    <p><?php _e( 'In the "Applications" field, click the number to go to that application, or click the asterisk (*) next to it to see other emails sent to that application.', 'jobman' ) ?></p>

		<table id="jobman-email" class="form-table">
		    <tr>
		        <th scope="row"><?php _e( 'Subject', 'jobman' ) ?></th>
		        <td><?php echo $email->post_title ?></td>
		    </tr>
<?php
    $apps = get_posts( "post_type=jobman_app&meta_key=contactmail&meta_value=$email->ID&numberposts=-1&post_status=publish,private" );

	$appstrings = array();
	$emails = array();
	foreach( $apps as $app ) {
		$appstrings[] = "<a href='?page=jobman-list-applications&amp;appid=$app->ID'>$app->ID</a> <a href='?page=jobman-list-emails&amp;appid=$app->ID'>*</a>";
		$appids[] = $app->ID;
		
		$emails[] = get_post_meta( $app->ID, "data$fromid", true );
	}
?>
			<tr>
			    <th scope="row"><?php _e( 'Applications', 'jobman' ) ?></th>
			    <td>
<?php
	echo implode( ', ', $appstrings );
?>
				</td>
			</tr>
			<tr>
			    <th scope="row"><?php _e( 'Emails', 'jobman' ) ?></th>
			    <td>
<?php
	echo implode( ', ', $emails );
?>
				</td>
			</tr>
		    <tr>
		        <th scope="row"><?php _e( 'Message', 'jobman' ) ?></th>
		        <td><?php echo wpautop( $email->post_content ) ?></td>
		    </tr>
		</table>
	</div>
<?php
}

function jobman_application_mailout() {
	global $wpdb, $current_user;
	$options = get_option( 'jobman_options' );
	get_currentuserinfo();
	
	$fromid = $options['application_email_from'];
	
	$apps = get_posts( array( 'post_type' => 'jobman_app', 'post__in' => $_REQUEST['application'], 'numberposts' => -1, 'post_status' => 'publish,private' ) );
	
	$emails = array();
	$appids = array();
	foreach( $apps as $app ) {
		$email = get_post_meta( $app->ID, "data$fromid", true );
		if( empty( $email ) )
			// No email for this application
			continue;

		$emails[] = $email;
		$appids[] = $app->ID;
	}
	$email_str = implode( ', ', array_unique( $emails ) );
?>
	<div class="wrap">
		<h2><?php _e( 'Job Manager: Application Email', 'jobman' ) ?></h2>

		<form action="" method="post">
		<input type="hidden" name="jobman-mailout-send" value="1" />
		<input type="hidden" name="jobman-appids" value="<?php echo implode(',', $appids ) ?>" />
<?php
	wp_nonce_field( 'jobman-mailout-send' );
?>
		<table id="jobman-email-edit" class="form-table">
			<tr>
				<th scope="row"><?php _e( 'From', 'jobman' ) ?></th>
				<td><input class="regular-text code" type="text" name="jobman-from" value="<?php echo '&quot;' . $current_user->display_name . '&quot; <' . $current_user->user_email . '>' ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'To', 'jobman' ) ?></th>
				<td><?php echo $email_str ?></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Subject', 'jobman' ) ?></th>
				<td><input class="regular-text code" type="text" name="jobman-subject" /></td>
			</tr>
			<tr>
				<th scope="row"><?php _e( 'Message', 'jobman' ) ?></th>
				<td><textarea class="large-text code" name="jobman-message" rows="15"></textarea></td>
			</tr>
		</table>
		
		<p class="submit"><input type="submit" name="submit"  class="button-primary" value="<?php _e( 'Send Email', 'jobman' ) ?>" /></p>
		</form>
	</div>
<?php
}

function jobman_application_mailout_send() {
	global $current_user;
	get_currentuserinfo();

	$options = get_option( 'jobman_options' );

	$fromid = $options['application_email_from'];

	$from = $_REQUEST['jobman-from'];
	$subject = $_REQUEST['jobman-subject'];
	$message = $_REQUEST['jobman-message'];
	
	$header = "From: $from" . PHP_EOL;
	$header .= "Reply-To: $from" . PHP_EOL;
	$header .= "Return-Path: $from" . PHP_EOL;
	$header .= 'Content-type: text/plain; charset='. get_option( 'blog_charset' ) . PHP_EOL;

	// Workaround for WP to Twitter plugin tweeting about new email
	$_POST['jd_tweet_this'] = 'no';

	$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'private',
				'post_author' => $current_user->ID,
				'post_content' => $message,
				'post_title' => $subject,
				'post_type' => 'jobman_email',
				'post_parent' => $options['main_page']
			);
	$emailid = wp_insert_post( $page );

	$appids = explode(',', $_REQUEST['jobman-appids'] );
	$emails = array();
	foreach( $appids as $appid ) {
		$appmeta = get_post_custom( $appid );
		if( ! array_key_exists("data$fromid", $appmeta ) || '' == $appmeta["data$fromid"] )
			// No email for this application
			continue;

		if( is_array( $appmeta["data$fromid"] ) )
			$emails[] = $appmeta["data$fromid"][0];
		else
			$emails[] = $appmeta["data$fromid"];
			
        add_post_meta( $appid, 'contactmail', $emailid, false );
	}
	
	$emails = array_unique( $emails );
	
	foreach( $emails as $to ) {
		wp_mail( $to, $subject, $message, $header );
	}
}

?>