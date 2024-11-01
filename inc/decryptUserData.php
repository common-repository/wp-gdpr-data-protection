<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* Decrypt All Userdata to Original
*/
global $wpdb;
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';

   	if ( !current_user_can( 'manage_options' ) )
   	{
      		wp_die( 'You are not allowed to be on this page.' );
   	} 

   	$notice = $secretKey = '';

   	if ( isset($_POST['secretKey']) )
   	{
      		// Check that nonce field
   	  	wp_verify_nonce( $_POST['secret_key_for_decrypt_nonce_verify'], 'secret_key_for_decrypt_nonce_verify' );

		$secretKey= $_POST['secretKey'];

		$fHash = WooEnc_get_key();

		$key = base64_encode($fHash);

		if($key != $secretKey) {
			$settype = 'error';
			$setmessage = __('Your secret key is not matched. Please try again.', 'WooEnc');
		} 
		else {
			$users = $wpdb->get_results("Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0");

				foreach ( $users as $user ) 
				{
					$ID 		=  $user->ID;
					$user_login 	=  WooEnc_safeDecrypt($user->user_login, $fHash);
					$user_pass 	=  WooEnc_safeDecrypt($user->user_pass, $fHash);
					$user_nicename 	=  WooEnc_safeDecrypt($user->user_nicename, $fHash);
					$user_email 	=  WooEnc_safeDecrypt($user->user_email, $fHash);
					$user_url 	=  WooEnc_safeDecrypt($user->user_url, $fHash);
					$display_name 	=  WooEnc_safeDecrypt($user->display_name, $fHash);

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

					$usermetas = $wpdb->get_results( "SELECT * FROM $usermeta_table where user_id = $ID" );

					foreach ( $usermetas as $usermeta ) 
					{
						$WooEnc_userMetaKeys = array('nickname', 'first_name', 'last_name', 'description');
						if ( in_array($usermeta->meta_key, $WooEnc_userMetaKeys)) {
        					$wpdb->update($usermeta_table, array('meta_value' => WooEnc_safeDecrypt($usermeta->meta_value, $fHash)), array('umeta_id' => $usermeta->umeta_id, 'meta_key' => $usermeta->meta_key) );
						}
					}

					do_action('WooEnc_wpAllUsersDecrypt', $ID);

				}

			delete_option( 'wpEncryptionEmailStatus' );
			deactivate_plugins( WOOENCRYPTION__PLUGIN_DIR.'/wp-gdpr-data-protection.php');
			$settype = 'updated';
			$setmessage = __('All user data is successfully decrypted in database and <b>WooCommerce GDPR (DSGVO) Data Protection</b> plugin also successfully deactivated.', 'WooEnc');
		}
    	add_settings_error(
        	'decrypt_order_data',
        	esc_attr( 'settings_updated' ),
        	$setmessage,
        	$settype
	    );

   	}
	?>

	<div class="wrap">
		<h1>Decrypt All Data Permanently</h1>
		<p>This page will decrypt your user data to normal in database table and deactivate the <b>WooCommerce GDPR (DSGVO) Data Protection</b> plugin automatically.</p>
		<?php settings_errors(); ?>
		<form method="post" name="decrypt_data_after_key_verify" action="<?php echo admin_url( 'admin.php' ); ?>?page=wooenc-decrypt-userdata">
			<?php $nonce = wp_create_nonce( 'secret_key_for_decrypt_nonce_verify' ); ?>
			<input type="hidden" name="secret_key_for_decrypt_nonce_verify" value="<?php echo($nonce); ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="email">Your Secret Key <span class="description">(required)</span></label>
						</th>
						<td>
							<input name="secretKey" type="password" id="secretKey" value="<?php echo $secretKey; ?>" class="regular-text" aria-required="true" required="required">
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" class="button button-primary" value="Decrypt All User Data"></p>
		</form>
	</div>