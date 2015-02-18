<?php
global $jobman_login_failed;
$jobman_login_failed = false;

function jobman_display_login() {
	global $current_user;
	get_currentuserinfo();
	
	$options = get_option( 'jobman_options' );
	
	$content = '';
	
	if( is_user_logged_in() ) {
		$loggedin_html = '<div id="jobman_loggedin"><span class="message">';
		$loggedin_html .= apply_filters( 'jobman_loggedin_msg', sprintf( __( 'Welcome, %1s!', 'jobman' ), $current_user->display_name ) );
		$loggedin_html .= '</span>';
		$loggedin_html .= '</div>';
		
		$content .= apply_filters( 'jobman_loggedin_html', $loggedin_html );
	}
	else {
		$login_html = '<form action="" method="post">';
		$login_html .= '<div id="jobman_login">';
		$login_html .= '<span class="message">';
		$login_html .= apply_filters( 'jobman_login_msg', __( "If you've registered with us previously, please login now. If you'd like to register, please click the 'Register' link below.", 'jobman' ) );
		$login_html .= '</span>';
		$login_html .= '<label class="username" for="jobman_username">' . __( 'Username', 'jobman' ) . '</label>: ';
		$login_html .= '<input type="text" name="jobman_username" id="jobman_username" class="username" />';
		$login_html .= '<label class="password" for="jobman_password">' . __( 'Password', 'jobman' ) . '</label>: ';
		$login_html .= '<input type="password" name="jobman_password" id="jobman_password" class="password" />';
		$login_html .= '<input class="submit" type="submit" name="submit" value="' . __( 'Login', 'jobman' ) . '" />';
		$login_html .= '<span><a href="' . get_page_link( $options['register_page'] ) . '">' . __( 'Register', 'jobman' ) . '</a> | <a href="' . wp_lostpassword_url( urlencode( jobman_current_url() ) ) . '">' . __( 'Forgot your password?', 'jobman' ) . '</a></span></div>';
		$login_html .= '</form>';
		
		$content .= apply_filters( 'jobman_login_html', $login_html );
	}
	
	return $content;
}

function jobman_login() {
	global $wp_query, $jobman_login_failed;
	
	$username = $wp_query->query_vars['jobman_username'];
	$password = $wp_query->query_vars['jobman_password'];
	
	if( user_pass_ok( $username, $password ) ) {
		$creds = array(
					'user_login' => $username,
					'user_password' => $password,
					'remember' => true
				);
		wp_signon( $creds );
		
		wp_redirect( jobman_current_url() );
		exit;
	}
	else {
		$jobman_login_failed = true;
	}
}

global $jobman_register_failed;
$jobman_register_failed = 0;

function jobman_display_register() {
	global $jobman_register_failed, $wp_query;
	$options = get_option( 'jobman_options' );
	
	$page = get_post( $options['register_page'] );
	
	$content = '';
	
	$register_html = '<div id="jobman_register">';
	
	$register_html .= '<form action="" method="post">';
	$register_html .= '<input type="hidden" name="jobman_register" value="1" />';
	
	$register_html .= '<table>';
	
	if( 4 == $jobman_register_failed )
		$register_html .= '<tr><td>&nbsp;</td><td class="error">' . __( 'Please fill in all fields.', 'jobman' ) . '</td></tr>';
	
	if( 1 == $jobman_register_failed )
		$register_html .= '<tr><td>&nbsp;</td><td class="error">' . __( 'This username has already been registered.', 'jobman' ) . '</td></tr>';
	
	$register_html .= '<tr><th scope="row"><label class="username" for="jobman_username">' . __( 'Username', 'jobman' ) . '</label>:</th>';
	$register_html .= '<td><input class="username" type="text" name="jobman_username" id="jobman_username" value="';
	$register_html .= ( array_key_exists( 'jobman_username', $wp_query->query_vars ) )?( $wp_query->query_vars['jobman_username'] ):( '' );
	$register_html .= '" /></td></tr>';
	
	if( 2 == $jobman_register_failed )
		$register_html .= '<tr><td>&nbsp;</td><td class="error">' . __( 'Passwords do not match.', 'jobman' ) . '</td></tr>';
	
	$register_html .= '<tr><th scope="row"><label class="password" for="jobman_password">' . __( 'Password', 'jobman' ) . '</label>:</th>';
	$register_html .= '<td><input class="password" type="password" name="jobman_password" id="jobman_password" /></td></tr>';
	
	$register_html .= '<tr><th scope="row"><label class="password" for="jobman_password2">' . __( 'Password Again', 'jobman' ) . '</label>:</th>';
	$register_html .= '<td><input class="password" type="password" name="jobman_password2" id="jobman_password2" /></td></tr>';
	
	if( 3 == $jobman_register_failed )
		$register_html .= '<tr><td>&nbsp;</td><td class="error">' . sprintf( __( "This email address has already been registered. If you've previously registered but don't remember your password, please visit the <a href='%1s'>password reset page</a>.", 'jobman' ), wp_lostpassword_url( jobman_current_url() ) ) . '</td></tr>';
	
	$register_html .= '<tr><th scope="row"><label class="email" for="jobman_email">' . __( 'Email Address', 'jobman' ) . '</label>:</th>';
	$register_html .= '<td><input class="email" type="text" name="jobman_email" id="jobman_email" value="';
	$register_html .= ( array_key_exists( 'jobman_email', $wp_query->query_vars ) )?( $wp_query->query_vars['jobman_email'] ):( '' );
	$register_html .= '" /></td></tr>';
	
	$register_html .= '<tr><td colspan="2"><input class="submit" type="submit" name="submit" value="' . __( 'Register', 'jobman' ) . '" /></td></tr>';

	$register_html .= '</table></form></div>';
	
	$content .= apply_filters( 'jobman_register_html', $register_html );
	
	$page->post_content = $content;
	
	return array( $page );
}

function jobman_register() {
	global $jobman_register_failed, $wp_query;
	
	require_once( ABSPATH . WPINC . '/registration.php' );
	
	$vars = array( 'jobman_username', 'jobman_password', 'jobman_password2', 'jobman_email' );
	
	foreach( $vars as $var ) {
		if( ! array_key_exists( $var, $wp_query->query_vars ) ) {
			$jobman_register_failed = 4;
			return;
		}
	}
	
	if( $wp_query->query_vars['jobman_password'] != $wp_query->query_vars['jobman_password2'] ) {
		$jobman_register_failed = 2;
		return;
	}
	
	if( username_exists( $wp_query->query_vars['jobman_username'] ) ) {
		$jobman_register_failed = 1;
		return;
	}
	
	if( email_exists( $wp_query->query_vars['jobman_email'] ) ) {
		$jobman_register_failed = 3;
		return;
	}
	
	$userid = wp_create_user( $wp_query->query_vars['jobman_username'], $wp_query->query_vars['jobman_password'], $wp_query->query_vars['jobman_email'] );
	
	update_usermeta( $userid, 'jobman', 1 );
	
	jobman_login();
}
?>