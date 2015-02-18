<?php
function jobman_comments( $iid, $editable = false ) {
	if( $editable ) {
?>
	<form action="" method="post">
		<input type="hidden" name="interview" value="<?php echo $iid ?>" />
		<textarea class="large-text code" name="comment"></textarea>
		<input type="submit" name="submit" value="<?php _e( 'Comment', 'jobman' )?>" />
	</form>
<?php
	}
	$comments = get_comments( "post_id=$iid" );
	
	jobman_display_comments( $comments );
}

function jobman_display_comments( $comments ) {
	if( empty( $comments ) ) {
		echo '<p class="error">' . __( 'No comments found', 'jobman' ) . '</p>';
		return;
	}
	
	foreach( $comments as $comment ) {
		$author = get_userdata( $comment->user_id );
		echo "<br/><strong>$comment->comment_date - $author->user_nicename</strong><br/>";
		echo wpautop( $comment->comment_content );
	}
}

function jobman_store_comment() {
	global $current_user;
	get_currentuserinfo();
	
	$comment = array(
		'comment_post_ID' => $_REQUEST['interview'],
		'comment_content' => $_REQUEST['comment'],
		'user_id' => $current_user->ID
	);
	
	wp_insert_comment( $comment );
}

?>
