<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function wooenc_processAction()
{
	global $wpdb;
	$emailAddress = (isset($_POST['email']) && is_email($_POST['email'])) ? $_POST['email'] : false;
	$requestType = (isset($_POST['type'])) ? $_POST['type'] : false;
	$consent = (isset($_POST['consent'])) ? filter_var($_POST['consent'], FILTER_VALIDATE_BOOLEAN) : false;

	if (!$emailAddress) {
		$output['error'] = __('Missing or incorrect email address.', 'WooEnc');
	}

    if (!$requestType) {
		$output['error'] = __('You need to select valid data request type.', 'WooEnc');
	}

	if (!$consent) {
		$output['error'] = __('You need to accept the privacy checkbox.', 'WooEnc');
	}

	if (empty($output['error'])) {
		if (!wooenc_existsByEmailAddress($emailAddress, $requestType)) {
			$requestType = ($requestType == 'export') ? 'wooenc_export_personal_data' : 'wooenc_remove_personal_data';
			$confirm_key = wp_generate_password( 15, false );
			$wpdb->insert(
				$wpdb->posts,
				array(
					'post_type' 		=> 'user_request',
					'post_title' 		=> $emailAddress,
					'post_name' 		=> $requestType,
					'post_author'		=> 0,
					'post_content'		=> '[]',
					'post_status'		=> 'request-pending',
					'comment_status'	=> 'closed',
					'ping_status'		=> 'closed',
					'post_date'			=> current_time('mysql'),
					'post_date_gmt'		=> current_time('mysql'),
					'post_modified'		=> current_time('mysql'),
					'post_modified_gmt'	=> current_time('mysql'),
					'post_password'		=> wp_hash_password($confirm_key),
				)
			);
			$lastid = $wpdb->insert_id;
			$blogname = get_bloginfo('name');
			$admin_email = get_bloginfo('admin_email');
			$blogurl = get_bloginfo('url');
			if($requestType == 'wooenc_export_personal_data') {
				$subject = '['.$blogname.'] Confirm Action: Export Personal Data';
				$message = 'Howdy,<br><br>A request has been made to perform the following action on your account:<br><br><b>Export Personal Data</b><br><br>To confirm this, please click on the following link: <a href="'.get_page_link(get_option('wooenc_requestdatapage')).'?action=confirmaction&request_id='.$lastid.'&confirm_key='.$confirm_key.'">'.get_page_link(get_option('wooenc_requestdatapage')).'?action=confirmaction&request_id='.$lastid.'&confirm_key='.$confirm_key.'</a><br><br>You can safely ignore and delete this email if you do not want to take this action.<br><br>This email has been sent to '.$emailAddress.'<br><br>Regards,<br>All at '.$blogname.'<br>'.$blogurl;
				if(wooenc_sendMail($emailAddress, $admin_email, $subject, $message)) {
					$output['message'] = __('<b>Thanks for confirming your export request.</b><br>The site administrator has been notified. You will receive a link to download your export via email when they fulfill your request.', 'WooEnc');
				} else {
					 $output['error'] = __('Something went wrong while saving the request. Please try again.'.$message, 'WooEnc');
				}
			} else {
				$subject = '['.$blogname.'] Confirm Action: Erase Personal Data';
				$message = 'Howdy,<br><br>A request has been made to perform the following action on your account:<br><br><b>Erase Personal Data</b><br><br>To confirm this, please click on the following link: <a href="'.get_page_link(get_option('wooenc_requestdatapage')).'?action=confirmaction&request_id='.$lastid.'&confirm_key='.$confirm_key.'">'.get_page_link(get_option('wooenc_requestdatapage')).'?action=confirmaction&request_id='.$lastid.'&confirm_key='.$confirm_key.'</a><br><br>You can safely ignore and delete this email if you do not want to take this action.<br><br>This email has been sent to '.$emailAddress.'<br><br>Regards,<br>All at '.$blogname.'<br>'.$blogurl;
				if(wooenc_sendMail($emailAddress, $admin_email, $subject, $message)) {
					$output['message'] = __('<b>Thanks for confirming your erasure request.</b><br>The site administrator has been notified. You will receive an email confirmation when they erase your data.', 'WooEnc');
				} else {
					 $output['error'] = __('Something went wrong while mailing you this request. Please try again.', 'WooEnc');
				}
			}
		} else {
			$output['error'] = __('You have already requested your data. Please check your mailbox.', 'WooEnc');
		}
	}

	echo json_encode($output);
    wp_die();
}

function wooenc_existsByEmailAddress($email = '', $requestType = 'export') {
	global $wpdb;
	$requestType = ($requestType == 'export') ? 'wooenc_export_personal_data' : 'wooenc_remove_personal_data';
	$query = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'user_request' and post_title = '".$email."' and post_name = '".$requestType."'";
	$row = $wpdb->get_var( $query );
    return $row;
}

function wooenc_sendMail($to, $from, $subject, $body) {
		$headers = 'Content-type: text/html;charset=utf-8' . "\r\n";
		$headers .= 'From: '.get_bloginfo('name').' <'.$from.'>' . "\r\n";
		$mail_sent = wp_mail( $to, $subject, $body, $headers );
		return $mail_sent;
}


function wooenc_woocommerceActivate() {

	global $wpdb;

	$fHash = WooEnc_get_key();

	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';
	$postmeta_table = $wpdb->prefix . 'postmeta';

	$users = $wpdb->get_results("Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0");

	foreach ( $users as $user ) 
	{
		$ID 		=  $user->ID;
		$user_login 	=  WooEnc_safeDecrypt($user->user_login, $fHash);
		$user_pass 		=  WooEnc_safeDecrypt($user->user_pass, $fHash);
		$user_nicename 	=  WooEnc_safeDecrypt($user->user_nicename, $fHash);
		$user_email 	=  WooEnc_safeDecrypt($user->user_email, $fHash);
		$user_url 		=  WooEnc_safeDecrypt($user->user_url, $fHash);
		$display_name 	=  WooEnc_safeDecrypt($user->display_name, $fHash);

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

		$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $ID" );

			foreach ( $usermetas as $usermeta ) 
			{
				$WooEnc_userMetaKeys = array('nickname', 'first_name', 'last_name', 'description');
				if ( in_array($usermeta->meta_key, $WooEnc_userMetaKeys)) {
        			$wpdb->update($usermeta_table, array('meta_value' => WooEnc_safeDecrypt($usermeta->meta_value, $fHash)), array('umeta_id' => $usermeta->umeta_id, 'meta_key' => $usermeta->meta_key) );
				}
			}
		}

		$users = $wpdb->get_results( "SELECT * FROM $user_table" );

		$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table WHERE meta_key IN ('nickname', 'first_name', 'last_name', 'description', 'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_country', 'billing_state', 'billing_phone', 'billing_email', 'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode', 'shipping_country', 'shipping_state')" );

		$postmetas = $wpdb->get_results( "SELECT * FROM $postmeta_table WHERE meta_key IN ('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index')" );

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
					'user_pass'	=> $user_pass,
					'user_nicename' => $user_nicename,
					'user_email' 	=> $user_email,
					'user_url' 	=> $user_url,
					'display_name'	=> $display_name,
				),
				array('id' => $ID)
			);
			update_user_meta( $ID, 'user_logged_in_successfully', 0);
		}

		foreach ( $usermetas as $usermeta ) 
		{
			$umeta_id 	=  $usermeta->umeta_id;
			$meta_value 	=  WooEnc_safeEncrypt($usermeta->meta_value, $fHash);

			$wpdb->update(
				$usermeta_table,
				array( 
					'meta_value' => $meta_value,
				),
				array('umeta_id' => $umeta_id, 'meta_key' => $usermeta->meta_key)
			);
		}

		foreach ( $postmetas as $postmeta ) 
		{
			$meta_id 	=  $postmeta->meta_id;
			$meta_value 	=  WooEnc_safeEncrypt($postmeta->meta_value, $fHash);

			$wpdb->update(
				$postmeta_table,
				array( 
					'meta_value' => $meta_value,
				),
				array('meta_id' => $meta_id, 'meta_key' => $postmeta->meta_key)
			);
		}
}