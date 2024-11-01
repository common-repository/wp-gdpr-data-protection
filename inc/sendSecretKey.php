<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* Send Secret Key to Email
*/
if ( !current_user_can( 'manage_options' ) )
{
    wp_die( 'You are not allowed to be on this page.' );
} 

   	$notice = $email = 0;

	$key = WooEnc_get_key();
	$key = base64_encode($key);

   	if ( isset($_POST['email']) )
   	{
      		// Check that nonce field
   	  	wp_verify_nonce( $_POST['secret_key_nonce_verify'], 'secret_key_nonce_verify' );

		$email = sanitize_email( $_POST['email'] );

		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$to = $email;
		$subject = 'Secret Key for '.$blogname;
		$body = 'Secret Key: <b>'.$key.'</b><br><br><i style="color:#f00"><b>Note:</b> Please Keep this key secret from others.</i>';
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$headers[] = 'From: '.$blogname.' <'.get_option( 'admin_email' ).'>';

		if(wp_mail( $to, $subject, $body, $headers )) {
			add_option( 'WOOEncryptionEmailStatus', 'true', '', 'yes' );
			$notice = 1; 
		} 
   	}
	?>

	<div class="wrap">
		<h1>Send Secret Key to Email <small style="color:#f00">(For Only One Time)</small></h1>
		<p>This page is active for only one time after plugin installation to send the token key to your email address. The mail can be sent only once. So please keep this key safe so you can decrypt all data later.</p>
		<?php if($notice) { ?>
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
			<p><strong>Secret key send successfully to your email address.</strong></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
		</div>
		<?php } ?>
		<h4 style="color: #f00;">Please copy this secret/Token key and keep this key always secret and safe. <br><input type="text" value="<?php echo $key; ?>" style="width:100%" readonly/></h4>
		<form method="post" name="send_key_to_email" action="<?php echo admin_url( 'admin.php' ); ?>?page=wooenc-send-key">
			<?php $nonce = wp_create_nonce( 'secret_key_nonce_verify' ); ?>
			<input type="hidden" name="secret_key_nonce_verify" value="<?php echo($nonce); ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="email">Email <span class="description">(required)</span></label>
						</th>
						<td>
							<input name="email" type="email" id="email" value="<?php echo $email ? $email : get_option( 'admin_email' ); ?>" class="regular-text" aria-required="true" required="required">
						</td>
					</tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" class="button button-primary" value="Send Email"></p>
		</form>
	</div>