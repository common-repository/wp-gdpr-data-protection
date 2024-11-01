<?php
/*
Plugin Name: WooCommerce GDPR (DSGVO) Data Protection
Plugin URI: http://www.telberia.com/
Description: WooCommerce GDPR(DSGVO) Data Protection will help you to secure all your user’s private data with the most secure AES Encryption and Decryption with OpenSSL method.

Author: codemenschen
Author URI: http://www.codemenschen.at/
Version: 1.0.3
License: GPL2
*/

/*  Copyright 2018 Telberia

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
*/

// plugin variable: WooEnc

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

define( 'WOOENCRYPTION_VERSION', '1.0.3' );
define( 'WOOENCRYPTION__MINIMUM_WP_VERSION', '4.9.6' );
define( 'WOOENCRYPTION__PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'WOOENCRYPTION__PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

require_once(WOOENCRYPTION__PLUGIN_DIR.'/inc/safeCrypto.php');
require_once(WOOENCRYPTION__PLUGIN_DIR.'/inc/core.php');
require_once(WOOENCRYPTION__PLUGIN_DIR.'/inc/shortcode.php');
require_once(WOOENCRYPTION__PLUGIN_DIR.'/inc/requestProcess.php');
require_once(WOOENCRYPTION__PLUGIN_DIR.'/inc/checkboxSettings.php');

if (is_admin()) {
	add_action( 'plugins_loaded', function () {
		WooEnc_WPUsersAdminPage::get_instance();
	} );
	require_once(WOOENCRYPTION__PLUGIN_DIR.'/inc/users.php');
	require_once(WOOENCRYPTION__PLUGIN_DIR.'/inc/userTable.php');
	require_once(WOOENCRYPTION__PLUGIN_DIR.'/inc/requestsTable.php');
}
if (get_option('WooEncWoocommerceEnable')) {
	require_once(WOOENCRYPTION__PLUGIN_DIR.'/inc/wooenc-action.php');
}

add_action('wp_ajax_nopriv_wooenc_doajax_process_action', 'wooenc_processAction');
add_action('wp_ajax_wooenc_doajax_process_action', 'wooenc_processAction');

function wooenc_loadAssets() {
	wp_enqueue_script('jquery');
    wp_enqueue_style('wooenc.css', WOOENCRYPTION__PLUGIN_URL . '/assets/css/style.css', array());
    wp_enqueue_script('wooenc.js', WOOENCRYPTION__PLUGIN_URL . '/assets/js/public.js', array(), true);
    wp_localize_script('wooenc.js', 'WooEncAjax_front', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
}
add_action('wp_enqueue_scripts', 'wooenc_loadAssets');

register_activation_hook( __FILE__, 'WooEnc_activate' );
register_deactivation_hook( __FILE__, "WooEnc_deactivate" );
register_uninstall_hook( __FILE__, "WooEnc_uninstall" );

function WooEnc_activate() {

	global $wpdb;

	$upload = wp_upload_dir();
 	$secure_dir = $upload['basedir'];
 	$secure_dir = $secure_dir . '/wooenc_secure';
 	if (! is_dir($secure_dir)) {
    	mkdir( $secure_dir, 0744 );
 	}

	if(!file_exists($secure_dir.'/pass.encrypted')) {
		$fHash = WooEnc_generateRandomKey();
		$file = $secure_dir.'/pass.encrypted';
		$fp = fopen($file, 'w');
		fwrite($fp, $fHash);
		fclose($fp);
		chmod($file, 0600);
		add_option( 'WooEnc_allUserHidekey', $fHash, '', 'yes' );
	} else {
		$fHash = WooEnc_get_key();
	}
	
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';

	$wpdb->query("alter table $user_table change user_login user_login varchar(1024)");
	$wpdb->query("alter table $user_table change user_pass user_pass varchar(1024)");
	$wpdb->query("alter table $user_table change user_nicename user_nicename varchar(1024)");
	$wpdb->query("alter table $user_table change user_email user_email varchar(1024)");
	$wpdb->query("alter table $user_table change user_url user_url varchar(1024)");
	$wpdb->query("alter table $user_table change display_name display_name varchar(1024)");

	$users = $wpdb->get_results( "SELECT * FROM $user_table" );
		
	$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table WHERE meta_key IN ('nickname', 'first_name', 'last_name', 'description')" );

	foreach ( $users as $user ) 
	{
		$ID 		=  $user->ID;
		$user_login 	=  WooEnc_safeEncrypt($user->user_login, $fHash);
		$user_pass 	=  WooEnc_safeEncrypt($user->user_pass, $fHash);
		$user_nicename 	=  WooEnc_safeEncrypt($user->user_nicename, $fHash);
		$user_email 	=  WooEnc_safeEncrypt($user->user_email, $fHash);
		$user_url 	=  WooEnc_safeEncrypt($user->user_url, $fHash);
		$display_name 	=  WooEnc_safeEncrypt($user->display_name, $fHash);

		$wpdb->update(
			$user_table,
			array( 
				'user_login' 	=> $user_login,
				'user_pass'		=> $user_pass,
				'user_nicename' => $user_nicename,
				'user_email' 	=> $user_email,
				'user_url' 		=> $user_url,
				'display_name'	=> $display_name,
			),
			array('id' => $ID)
		);
		update_user_meta( $ID, 'user_logged_in_successfully', 0);
	}

	foreach ( $usermetas as $usermeta ) 
	{
		$umeta_id 	=  $usermeta->umeta_id;
		$meta_value =  WooEnc_safeEncrypt($usermeta->meta_value, $fHash);

		$wpdb->update(
			$usermeta_table,
			array( 
				'meta_value' => $meta_value,
			),
			array('umeta_id' => $umeta_id, 'meta_key' => $usermeta->meta_key)
		);
	}

	// Create Pages
  	$requestDataPage = array(
      'post_title'    	=> 'GDPR - Request Your Data',
      'post_content'  	=> '[wooenc_reqdata_form]',
      'post_name' 		=> 'wp-gdpr-request-your-data',
      'post_author'   	=> get_current_user_id(),
      'post_type'     	=> 'page',
      'post_status'     => 'publish',
  	);
  	$requestPage = wp_insert_post( $requestDataPage, true);
  	update_option('wooenc_requestdatapage', $requestPage);
}

function WooEnc_deactivate() {
	delete_option("WOOEncryptionEmailStatus");
	if(get_option('WooEncWoocommerceEnable'))
	delete_option("WOOEncryptionOrderEncryptStatus");
}

function WooEnc_uninstall() {
}

/* Login failed Hook */
add_action('wp_login_failed', 'WooEnc_userLoginFailedHookFunction');

function WooEnc_userLoginFailedHookFunction($username) {

	global $wpdb;

	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';

	$key = WooEnc_get_key();

	$user = $wpdb->get_row( "SELECT * FROM $user_table where user_login = '$username'" );

	$user_login 	=  WooEnc_safeEncrypt($user->user_login, $key);
	$user_pass 	=  WooEnc_safeEncrypt($user->user_pass, $key);
	$user_nicename 	=  WooEnc_safeEncrypt($user->user_nicename, $key);
	$user_email 	=  WooEnc_safeEncrypt($user->user_email, $key);
	$user_url 	=  WooEnc_safeEncrypt($user->user_url, $key);
	$display_name 	=  WooEnc_safeEncrypt($user->display_name, $key);

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

	update_user_meta( $user->ID, 'user_logged_in_successfully', 0);

	$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $user->ID" );

	foreach ( $usermetas as $usermeta ) 
	{
		$WooEnc_userMetaKeys = array('nickname', 'first_name', 'last_name', 'description');
		if ( in_array($usermeta->meta_key, $WooEnc_userMetaKeys)) {
        		$wpdb->update($usermeta_table, array('meta_value' => WooEnc_safeEncrypt($usermeta->meta_value, $key)), array('umeta_id' => $usermeta->umeta_id, 'meta_key' => $usermeta->meta_key) );
		}
	}

	do_action('WooEnc_wpUserEncryptLoginFailed', $user->ID);

}

/* Logout Hook */
add_action('wp_logout', 'WooEnc_userLogoutHookFunction');

function WooEnc_userLogoutHookFunction() {

	$user = wp_get_current_user();
	global $wpdb;

	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';

	$key = WooEnc_get_key();

	$user_login 	=  WooEnc_safeEncrypt($user->user_login, $key);
	$user_pass 	=  WooEnc_safeEncrypt($user->user_pass, $key);
	$user_nicename 	=  WooEnc_safeEncrypt($user->user_nicename, $key);
	$user_email 	=  WooEnc_safeEncrypt($user->user_email, $key);
	$user_url 	=  WooEnc_safeEncrypt($user->user_url, $key);
	$display_name 	=  WooEnc_safeEncrypt($user->display_name, $key);

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

	update_user_meta( $user->ID, 'user_logged_in_successfully', 0);

	$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $user->ID" );

	foreach ( $usermetas as $usermeta ) 
	{
		$WooEnc_userMetaKeys = array('nickname', 'first_name', 'last_name', 'description');
		if ( in_array($usermeta->meta_key, $WooEnc_userMetaKeys)) {
        		$wpdb->update($usermeta_table, array('meta_value' => WooEnc_safeEncrypt($usermeta->meta_value, $key)), array('umeta_id' => $usermeta->umeta_id, 'meta_key' => $usermeta->meta_key) );
		}
	}

	do_action('WooEnc_wpUserEncryptLogout', $user->ID);

}

/* New User Registration Hook */
add_action( 'user_register', 'WooEnc_registrationSaveEncrypted', 10, 1 );

function WooEnc_registrationSaveEncrypted( $user_id ) {

	global $wpdb;
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';

	$key = WooEnc_get_key();

	$user = $wpdb->get_row( "SELECT * FROM $user_table WHERE ID = $user_id" );

	$user_login 	=  WooEnc_safeEncrypt($user->user_login, $key);
	$user_pass 	=  WooEnc_safeEncrypt($user->user_pass, $key);
	$user_nicename 	=  WooEnc_safeEncrypt($user->user_nicename, $key);
	$user_email 	=  WooEnc_safeEncrypt($user->user_email, $key);
	$user_url 	=  WooEnc_safeEncrypt($user->user_url, $key);
	$display_name 	=  WooEnc_safeEncrypt($user->display_name, $key);

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
		array('id' => $user_id)
	);

	add_user_meta( $user_id, 'user_logged_in_successfully', 0);

	$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $user->ID" );

	foreach ( $usermetas as $usermeta ) 
	{
		$WooEnc_userMetaKeys = array('nickname', 'first_name', 'last_name', 'description');
		if ( in_array($usermeta->meta_key, $WooEnc_userMetaKeys)) {
        		$wpdb->update($usermeta_table, array('meta_value' => WooEnc_safeEncrypt($usermeta->meta_value, $key)), array('umeta_id' => $usermeta->umeta_id, 'meta_key' => $usermeta->meta_key) );
		}
	}

}

/* Before Registration Check Errors*/
add_filter( 'after_password_reset', 'WooEnc_afterPasswordReset', 10, 2 );

function WooEnc_afterPasswordReset($user, $new_pass) {

	global $wpdb;
	$user_table = $wpdb->prefix . 'users';

	$key = WooEnc_get_key();

	$hash = wp_hash_password( $new_pass );

	$user_pass = WooEnc_safeEncrypt($hash, $key);

	$wpdb->update(
		$user_table,
		array(
			'user_pass' => $user_pass,
		),
		array('id' => $user->ID)
	);

}

/* Before Registration Check Errors*/
add_filter( 'register_post', 'WooEnc_beforeRegistrationErrorCheck', 10, 3 );

function WooEnc_beforeRegistrationErrorCheck( $sanitized_user_login, $user_email, $errors ) {

	global $wpdb;
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';

	$key = WooEnc_get_key();

	$nologinusers = $wpdb->get_results( "Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0" );
	foreach( $nologinusers as $user ) 
	{
		$decryptuser_email = WooEnc_safeDecrypt($user->user_email, $key);
		$decryptuser_login = WooEnc_safeDecrypt($user->user_login, $key);
		if($sanitized_user_login== $decryptuser_login) {
			$errors->add( 'username_exists', __( '<strong>ERROR</strong>: This username is already registered. Please choose another one.' ) );
		}
		if($user_email == $decryptuser_email) {
			$errors->add( 'email_exists', __( '<strong>ERROR</strong>: This email is already registered, please choose another one.' ) );
		}
	}
	return $errors;
}

/* Before Registration Check Email Errors*/
add_filter( 'pre_user_email', 'WooEnc_beforeRegistrationEmailCheck', 10, 1 );

function WooEnc_beforeRegistrationEmailCheck( $raw_user_email ) {

	global $wpdb;
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';

	$key = WooEnc_get_key();

	$nologinusers = $wpdb->get_results( "Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0" );
	foreach( $nologinusers as $user ) 
	{
		$decryptuser_email = WooEnc_safeDecrypt($user->user_email, $key);
		if($raw_user_email == $decryptuser_email) {
			wp_die('That Email Address is already used');
		}
	}
	return $raw_user_email;
}

/* Before Registration Check Username Errors*/
add_filter( 'pre_user_login', 'WooEnc_beforeRegistrationUsernameCheck', 10, 1 );

function WooEnc_beforeRegistrationUsernameCheck( $sanitized_user_login ) {

	global $wpdb;
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';

	$key = WooEnc_get_key();

	$nologinusers = $wpdb->get_results( "Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0" );
	foreach( $nologinusers as $user ) 
	{
		$decryptuser_login = WooEnc_safeDecrypt($user->user_login, $key);
		if($sanitized_user_login == $decryptuser_login) {
			wp_die('That Username is already used');
		}
	}
	return $sanitized_user_login;
}
