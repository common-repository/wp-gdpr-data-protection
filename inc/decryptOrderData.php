<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* Decrypt All Userdata to Original
*/

	global $wpdb;
	$user_table = $wpdb->prefix . 'users';
	$usermeta_table = $wpdb->prefix . 'usermeta';
	$postmeta_table = $wpdb->prefix . 'postmeta';

   	if ( !current_user_can( 'manage_options' ) )
   	{
      		wp_die( 'You are not allowed to be on this page.' );
   	} 

   	$notice = $secretKey = '';

   	if ( isset($_POST['secretKey']) && isset($_POST['mode']) )
   	{
      		// Check that nonce field
   	  	wp_verify_nonce( $_POST['secret_key_for_decrypt_nonce_verify'], 'secret_key_for_decrypt_nonce_verify' );

		$secretKey= $_POST['secretKey'];

		$fHash = WooEnc_get_key();

		$key = base64_encode($fHash);

		if($key != $secretKey) {
			$settype = 'error';
			$setmessage = __('Your secret key is not matched. Please try again.', 'WooEnc');
		} else {
			if($_POST['mode'] == 'decrypt') {
				$directorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where meta_key = '_customer_user' and meta_value = 0" );

				foreach($directorders as $postmeta) {
					$order_id = $postmeta->post_id;
					$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
					foreach ( $postorders as $postorder ) 
					{
						$WooEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
						if ( in_array($postorder->meta_key, $WooEnc_postMetaKeys)) {
        					$wpdb->update($postmeta_table, array('meta_value' => WooEnc_safeDecrypt($postorder->meta_value, $fHash)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
						}
					}
				}

				$users = $wpdb->get_results("Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0");

				foreach ( $users as $user ) 
				{
					$ID  =  $user->ID;
					$postmetas = $wpdb->get_results( "SELECT * FROM $postmeta_table where meta_key = '_customer_user' and meta_value = $user->ID" );

					foreach($postmetas as $postmeta) {
						$order_id = $postmeta->post_id;
						$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
						foreach ( $postorders as $postorder ) 
						{
							$WooEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
							if ( in_array($postorder->meta_key, $WooEnc_postMetaKeys)) {
        						$wpdb->update($postmeta_table, array('meta_value' => WooEnc_safeDecrypt($postorder->meta_value, $fHash)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
							}
						}
					}
				}
				add_option( 'WOOEncryptionOrderEncryptStatus', 'true', '', 'yes' );
				$settype = 'updated';
				$setmessage = __('All order data is successfully decrypted.', 'WooEnc');
			} else {

				$directorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where meta_key = '_customer_user' and meta_value = 0" );

				foreach($directorders as $postmeta) {
					$order_id = $postmeta->post_id;
					$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
					foreach ( $postorders as $postorder ) 
					{
						$WooEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
						if ( in_array($postorder->meta_key, $WooEnc_postMetaKeys)) {
        					$wpdb->update($postmeta_table, array('meta_value' => WooEnc_safeEncrypt($postorder->meta_value, $fHash)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
						}
					}
				}

				$users = $wpdb->get_results("Select * from $usermeta_table as um INNER JOIN $user_table as u ON u.ID = um.user_id where um.meta_key = 'user_logged_in_successfully' and um.meta_value = 0");

				foreach ( $users as $user ) 
				{
					$postmetas = $wpdb->get_results( "SELECT * FROM $postmeta_table where meta_key = '_customer_user' and meta_value = $user->ID" );

					foreach($postmetas as $postmeta) {
						$order_id = $postmeta->post_id;
						$postorders = $wpdb->get_results( "SELECT * FROM $postmeta_table where post_id = $order_id" );
						foreach ( $postorders as $postorder ) 
						{
							$WooEnc_postMetaKeys = array('_billing_first_name', '_billing_last_name', '_billing_company', '_billing_address_1', '_billing_address_2', '_billing_city', '_billing_postcode', '_billing_country', '_billing_state', '_billing_phone', '_billing_email', '_shipping_first_name', '_shipping_last_name', '_shipping_company', '_shipping_address_1', '_shipping_address_2', '_shipping_city', '_shipping_postcode', '_shipping_country', '_shipping_state', '_billing_address_index', '_shipping_address_index');
							if ( in_array($postorder->meta_key, $WooEnc_postMetaKeys)) {
        						$wpdb->update($postmeta_table, array('meta_value' => WooEnc_safeEncrypt($postorder->meta_value, $fHash)), array('meta_id' => $postorder->meta_id, 'meta_key' => $postorder->meta_key) );
							}
						}
					}
				}
				delete_option( 'WOOEncryptionOrderEncryptStatus');
				$settype = 'updated';
				$setmessage = __('All order data is successfully encrypted.', 'WooEnc');
			}

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
		<h1>Decrypt All Order Data</h1>
		<p>This page will decrypt your all orders data to normal.</p>
		<?php settings_errors(); ?>
		<?php if(get_option('WOOEncryptionOrderEncryptStatus')) { ?>
			<h3 style="color: #f00;">Note: Data is already decrypted.</h3>
		<?php } ?>
		<form method="post" name="decrypt_data_after_key_verify" action="<?php echo admin_url( 'admin.php' ); ?>?page=wooenc-decrypt-orderdata">
			<?php $nonce = wp_create_nonce( 'secret_key_for_decrypt_nonce_verify' ); ?>
			<input type="hidden" name="secret_key_for_decrypt_nonce_verify" value="<?php echo($nonce); ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="email">Mode <span class="description">(required)</span></label>
						</th>
						<td>
							<label><input name="mode" type="radio" value="decrypt" checked> Decrypt Order Data</label><br>
							<label><input name="mode" type="radio" value="encrypt" > Encrypt Order Data</label>
						</td>
					</tr>
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