<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* Wordpress Core Functions Overwrite
*/

if( ! function_exists('wp_authenticate') ) {
    function wp_authenticate($username, $password) {

	global $wpdb;
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';

	$key = WooEnc_get_key();

	$username = sanitize_user($username);
	$password = trim($password);

	if ( is_email( $username ) ) {
		$nologinusers = $wpdb->get_results( "Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0" );
		foreach( $nologinusers as $user ) 
		{
			$user_email = WooEnc_safeDecrypt($user->user_email, $key);
			if($username == $user_email) {
				$username = WooEnc_safeDecrypt($user->user_login, $key);
				break;
			}
		}
		$loginusers = $wpdb->get_results( "Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 1" );
		foreach( $loginusers as $user ) 
		{
			if($username == $user_email) {
				$username = $user->user_login;
				break;
			}
		}
	}

	if ( !$username) {
		return new WP_Error( 'empty_username',
			__( '<strong>ERROR</strong>: The username field is empty.' )
		);
	}

	$check_users = $wpdb->get_results( "Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 1" );
	$is_user_already_logged_in = 0;
	foreach( $check_users as $check_user) {
		if($check_user->user_login == $username) {
			$is_user_already_logged_in = 1;
			break;
		}
	}

if(!$is_user_already_logged_in) {
	$users = $wpdb->get_results( "Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0" );
	$is_username = 0;
	foreach ( $users as $user ) 
	{
		$user_login = WooEnc_safeDecrypt($user->user_login, $key);

		if($username == $user_login) {
			$user_login 	=  WooEnc_safeDecrypt($user->user_login, $key);
			$user_pass 	=  WooEnc_safeDecrypt($user->user_pass, $key);
			$user_nicename 	=  WooEnc_safeDecrypt($user->user_nicename, $key);
			$user_email 	=  WooEnc_safeDecrypt($user->user_email, $key);
			$user_url 	=  WooEnc_safeDecrypt($user->user_url, $key);
			$display_name 	=  WooEnc_safeDecrypt($user->display_name, $key);
        		$wpdb->update(
				$user_table,
				array( 
					'user_login' 	=> $user_login,
					'user_pass'	=> $user_pass,
					'user_nicename' => $user_nicename,
					'user_email' 	=> $user_email,
					'user_url' 	=> $user_url,
					'display_name'	=> $display_name,
				),
				array('id' => $user->ID)
			);

			update_user_meta( $user->ID, 'user_logged_in_successfully', 1);

			$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $user->ID" );

			foreach ( $usermetas as $usermeta ) 
			{
				$WooEnc_userMetaKeys = array('nickname', 'first_name', 'last_name', 'description');
				if ( in_array($usermeta->meta_key, $WooEnc_userMetaKeys)) {
        				$wpdb->update($usermeta_table, array('meta_value' => WooEnc_safeDecrypt($usermeta->meta_value, $key)), array('umeta_id' => $usermeta->umeta_id, 'meta_key' => $usermeta->meta_key) );
				}
			}

			do_action('WooEnc_wpUserDecrypt', $user->ID);

			$is_username = 1;
        		break;
		}
	}
	        
	if ( !$is_username ) {
		return new WP_Error( 'invalid_username',
			__( '<strong>ERROR</strong>: Invalid username.' ) .
			' <a href="' . wp_lostpassword_url() . '">' .
			__( 'Lost your password?' ) .
			'</a>'
		);
	}
}
	
	$user = apply_filters( 'authenticate', null, $username, $password );
		
	if ( $user == null ) {
		$user = new WP_Error( 'authentication_failed', __( '<strong>ERROR</strong>: Invalid username, email address or incorrect password.' ) );
	}
	
	$ignore_codes = array('empty_username', 'empty_password');
	
	if (is_wp_error($user) && !in_array($user->get_error_code(), $ignore_codes) ) {
	       do_action( 'wp_login_failed', $username );
	}
	
	return $user;
    }
}

function WooEnc_get_key() {
	
	$upload = wp_upload_dir();
 	$secure_dir = $upload['basedir'];
 	$secure_dir = $secure_dir . '/wooenc_secure';
	$filePath = $secure_dir.'/pass.encrypted';
	$pwdFile = fopen($filePath, "r") or die("Unable to open file!");
	$key = fread($pwdFile,filesize($filePath));
	fclose($pwdFile);
	return $key;
}

function WooEnc_get_decryptUserData( $user_id ) {

    global $wpdb;
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';

	$key = WooEnc_get_key();

	$user = $wpdb->get_row( "SELECT * FROM $user_table WHERE ID = $user_id" );
	$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $user->ID" );

	$user->user_login 	= WooEnc_safeDecrypt($user->user_login, $key);
	$user->user_nicename 	= WooEnc_safeDecrypt($user->user_nicename, $key);
	$user->user_email 	= WooEnc_safeDecrypt($user->user_email, $key);
	$user->user_url 	= WooEnc_safeDecrypt($user->user_url, $key);
	$user->display_name 	= WooEnc_safeDecrypt($user->display_name, $key);

	return $user;
}

if ( !function_exists('wp_new_user_notification') ) {
function wp_new_user_notification( $user_id, $deprecated = null, $notify = '' ) {
    if ( $deprecated !== null ) {
        _deprecated_argument( __FUNCTION__, '4.3.1' );
    }
 
    global $wpdb, $wp_hasher;
    $user = WooEnc_get_decryptUserData( $user_id );
 
    // The blogname option is escaped with esc_html on the way into the database in sanitize_option
    // we want to reverse this for the plain text arena of emails.
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
 
    if ( 'user' !== $notify ) {
        $switched_locale = switch_to_locale( get_locale() );
 
        /* translators: %s: site title */
        $message  = sprintf( __( 'New user registration on your site %s:' ), $blogname ) . "\r\n\r\n";
        /* translators: %s: user login */
        $message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "\r\n\r\n";
        /* translators: %s: user email address */
        $message .= sprintf( __( 'Email: %s' ), $user->user_email ) . "\r\n";
 
        $wp_new_user_notification_email_admin = array(
            'to'      => get_option( 'admin_email' ),
            /* translators: Password change notification email subject. %s: Site title */
            'subject' => __( '[%s] New User Registration' ),
            'message' => $message,
            'headers' => '',
        );
 
        /**
         * Filters the contents of the new user notification email sent to the site admin.
         *
         * @since 4.9.0
         *
         * @param array   $wp_new_user_notification_email {
         *     Used to build wp_mail().
         *
         *     @type string $to      The intended recipient - site admin email address.
         *     @type string $subject The subject of the email.
         *     @type string $message The body of the email.
         *     @type string $headers The headers of the email.
         * }
         * @param WP_User $user     User object for new user.
         * @param string  $blogname The site title.
         */
        $wp_new_user_notification_email_admin = apply_filters( 'wp_new_user_notification_email_admin', $wp_new_user_notification_email_admin, $user, $blogname );
 
        @wp_mail(
            $wp_new_user_notification_email_admin['to'],
            wp_specialchars_decode( sprintf( $wp_new_user_notification_email_admin['subject'], $blogname ) ),
            $wp_new_user_notification_email_admin['message'],
            $wp_new_user_notification_email_admin['headers']
        );
 
        if ( $switched_locale ) {
            restore_previous_locale();
        }
    }
 
    // `$deprecated was pre-4.3 `$plaintext_pass`. An empty `$plaintext_pass` didn't sent a user notification.
    if ( 'admin' === $notify || ( empty( $deprecated ) && empty( $notify ) ) ) {
        return;
    }
 
    // Generate something random for a password reset key.
    $key = wp_generate_password( 20, false );
 
    /** This action is documented in wp-login.php */
    do_action( 'retrieve_password_key', $user->user_login, $key );
 
    // Now insert the key, hashed, into the DB.
    if ( empty( $wp_hasher ) ) {
        require_once ABSPATH . WPINC . '/class-phpass.php';
        $wp_hasher = new PasswordHash( 8, true );
    }
    $hashed = time() . ':' . $wp_hasher->HashPassword( $key );
    $wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'ID' => $user_id ) );
 
    $switched_locale = switch_to_locale( get_user_locale( $user ) );
 
	$user_row = $wpdb->get_row("SELECT * FROM $wpdb->users WHERE ID = $user_id");

    /* translators: %s: user login */
    $message = sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
    $message .= __('To set your password, visit the following address:') . "\r\n\r\n";
    $message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_row->user_login), 'login') . ">\r\n\r\n";
 
    $message .= wp_login_url() . "\r\n";
 
    $wp_new_user_notification_email = array(
        'to'      => $user->user_email,
        /* translators: Password change notification email subject. %s: Site title */
        'subject' => __( '[%s] Your username and password info' ),
        'message' => $message,
        'headers' => '',
    );
 
    /**
     * Filters the contents of the new user notification email sent to the new user.
     *
     * @since 4.9.0
     *
     * @param array   $wp_new_user_notification_email {
     *     Used to build wp_mail().
     *
     *     @type string $to      The intended recipient - New user email address.
     *     @type string $subject The subject of the email.
     *     @type string $message The body of the email.
     *     @type string $headers The headers of the email.
     * }
     * @param WP_User $user     User object for new user.
     * @param string  $blogname The site title.
     */
    $wp_new_user_notification_email = apply_filters( 'wp_new_user_notification_email', $wp_new_user_notification_email, $user, $blogname );
 
    wp_mail(
        $wp_new_user_notification_email['to'],
        wp_specialchars_decode( sprintf( $wp_new_user_notification_email['subject'], $blogname ) ),
        $wp_new_user_notification_email['message'],
        $wp_new_user_notification_email['headers']
    );
 
    if ( $switched_locale ) {
        restore_previous_locale();
    }
}
}
