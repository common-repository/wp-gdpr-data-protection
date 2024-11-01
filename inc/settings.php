<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
* WooEnc_WPUsersAdminPage Class for add Admin Pages in Menu
*/

   	$sendemail = 0;
	$settype = 'updated';

	$key = WooEnc_get_key();
	$key = base64_encode($key);

	$wooenc_email_status = get_option( 'WOOEncryptionEmailStatus' );
	$wooenc_woocommerce_enable = get_option('WooEncWoocommerceEnable');
	$wooenc_add_consent_checkboxes_registration = get_option( 'WOOEncryptionRegistrationConsentCheckbox' );
	$wooenc_add_consent_description_registration = esc_textarea(get_option('WOOEncryptionRegistrationConsentDescription'));
	$wooenc_add_consent_checkboxes_checkout = get_option( 'WOOEncryptionCheckoutConsentCheckbox' );
	$wooenc_add_consent_description_checkout = esc_textarea(get_option('WOOEncryptionCheckoutConsentDescription'));
	$wooenc_add_consent_checkboxes_comment = get_option('WOOEncryptionCommentConsentCheckbox');
	$wooenc_add_consent_description_comment = esc_textarea(get_option('WOOEncryptionCommentConsentDescription'));

   	if ( isset($_POST['sendemail']) )
   	{
      	// Check that nonce field
   	  	wp_verify_nonce( $_POST['wooenc_secret_key_nonce_verify'], 'wooenc_secret_key_nonce_verify' );

		$sendemail = sanitize_email( $_POST['sendemail'] );

		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

		$to = $sendemail;
		$subject = 'Secret Key for '.$blogname;
		$body = 'Secret Key: <b>'.$key.'</b><br><br><i style="color:#f00"><b>Note:</b> Please Keep this key secret from others.</i>';
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$headers[] = 'From: '.$blogname.' <'.get_option( 'admin_email' ).'>';

		if(wp_mail( $to, $subject, $body, $headers )) {
			add_option( 'WOOEncryptionEmailStatus', 'true', '', 'yes' );
			$setmessage = __('Secret key send successfully to your email address.', 'WooEnc');
		} else {
			$settype = 'error';
			$setmessage = __('Something went wrong while sending you mail. Please try again.', 'WooEnc');		
		}
    		add_settings_error(
        		'send_key_to_email',
        		esc_attr( 'settings_updated' ),
        		$setmessage,
        		$settype
	    	);
   	}

   	if(isset($_POST['wooenc_add_consent_description_registration']))
   	{
      	// Check that nonce field
   	  	wp_verify_nonce( $_POST['wooenc_settings_nonce_verify'], 'wooenc_settings_nonce_verify' );

		$wooenc_woocommerce_enable = isset($_POST['wooenc_woocommerce_enable']) ? $_POST['wooenc_woocommerce_enable'] : '';
		$wooenc_add_consent_checkboxes_registration = isset($_POST['wooenc_add_consent_checkboxes_registration']) ? $_POST['wooenc_add_consent_checkboxes_registration'] : '';
		$wooenc_add_consent_description_registration = isset($_POST['wooenc_add_consent_description_registration']) ? $_POST['wooenc_add_consent_description_registration'] : '';
		$wooenc_add_consent_checkboxes_checkout = isset($_POST['wooenc_add_consent_checkboxes_checkout']) ? $_POST['wooenc_add_consent_checkboxes_checkout'] : '';
		$wooenc_add_consent_description_checkout = isset($_POST['wooenc_add_consent_description_checkout']) ? $_POST['wooenc_add_consent_description_checkout'] : '';
		$wooenc_add_consent_checkboxes_comment = isset($_POST['wooenc_add_consent_checkboxes_comment']) ? $_POST['wooenc_add_consent_checkboxes_comment'] : '';
		$wooenc_add_consent_description_comment = isset($_POST['wooenc_add_consent_description_comment']) ? $_POST['wooenc_add_consent_description_comment'] : '';

   	  	update_option('WooEncWoocommerceEnable', $wooenc_woocommerce_enable);
   	  	update_option('WOOEncryptionRegistrationConsentCheckbox', $wooenc_add_consent_checkboxes_registration);
   	  	update_option('WOOEncryptionRegistrationConsentDescription', esc_textarea($wooenc_add_consent_description_registration));
   	  	update_option('WOOEncryptionCheckoutConsentCheckbox', $wooenc_add_consent_checkboxes_checkout);
   	  	update_option('WOOEncryptionCheckoutConsentDescription', esc_textarea($wooenc_add_consent_description_checkout));
   	  	update_option('WOOEncryptionCommentConsentCheckbox', $wooenc_add_consent_checkboxes_comment);
   	  	update_option('WOOEncryptionCommentConsentDescription', esc_textarea($wooenc_add_consent_description_comment));
		$setmessage = __('Your Settings Saved Successfully.', 'WooEnc');
    	add_settings_error(
        	'wooenc_settings_updated',
        	esc_attr( 'settings_updated' ),
        	$setmessage,
        	$settype
	    );
   	}

   	if(get_option('WooEncWoocommerceEnable') && !get_option('WooEncWoocommerceEnableStatus')) {
   		update_option('WooEncWoocommerceEnableStatus', 'true', '', 'yes' );
   		wooenc_woocommerceActivate();
   	}
?>


<div class="wrap wooenc-settings">
	<h1>WooCommerce GDPR (DSGVO) Data Protection</h1>
	<hr>
	<?php settings_errors(); ?>
	<div class="wooenc-row">
		<div class="wooenc-col75">
			<?php if(!$wooenc_email_status) { ?>
			<div class="white-box">
				<h2>Send Secret Key to Email <small style="color:#f00">(For Only One Time)</small></h2>
				<p>This box is active for only one time after plugin installation to send the token key to your email address. The mail can be sent only once. So please keep this key safe so you can decrypt all data later.</p>
				<h4 style="color: #f00;">
					<span>Please copy this secret/Token key and keep this key always secret and safe.</span>
					<input type="text" value="<?php echo $key; ?>" style="width:100%" readonly/>
				</h4>
				<form method="post" name="send_key_to_email" action="<?php echo admin_url( 'admin.php' ); ?>?page=woo_gdpr_data_protection">
					<?php $nonce = wp_create_nonce( 'wooenc_secret_key_nonce_verify' ); ?>
					<input type="hidden" name="wooenc_secret_key_nonce_verify" value="<?php echo($nonce); ?>">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row" style="width:150px">
									<label for="sendemail">Email</label>
								</th>
								<td>
									<input name="sendemail" type="email" id="sendemail" value="<?php echo $sendemail ? $sendemail : get_option( 'admin_email' ); ?>" class="regular-text" aria-required="true" required="required">
								</td>
							</tr>
						</tbody>
					</table>
					<input type="submit" name="submit" class="button button-primary" value="Send Email">
				</form>		
			</div>
			<?php } ?>
			<div class="white-box">
				<h2>Settings</h2>
				<form method="post" name="send_key_to_email" action="<?php echo admin_url( 'admin.php' ); ?>?page=woo_gdpr_data_protection">
					<?php $nonce = wp_create_nonce( 'wooenc_settings_nonce_verify' ); ?>
					<input type="hidden" name="wooenc_settings_nonce_verify" value="<?php echo($nonce); ?>">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">
									<label for="wooenc_woocommerce_enable">WooCommerce Data Encryption</label>
								</th>
								<td>
									<label><input name="wooenc_woocommerce_enable" type="checkbox" id="wooenc_woocommerce_enable" value="1" <?php echo $wooenc_woocommerce_enable ? 'checked' : '' ?>> Enable</label>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wooenc_add_consent_checkboxes_checkout">Woocommerce Checkout <p class="description">The GDPR checkbox will be added automatically at the end of your checkout page.</p></label>
								</th>
								<td>
									<label><input name="wooenc_add_consent_checkboxes_checkout" type="checkbox" id="wooenc_add_consent_checkboxes_checkout" value="1" <?php echo $wooenc_add_consent_checkboxes_checkout ? 'checked' : '' ?>> Enable</label>
								</td>
							</tr>
							<tr scope="row" class="checkout-box">
								<th><label for="wooenc_add_consent_description_checkout">Checkout Consent Description <p class="description">HTML tags not allowed.</p></label></th>
								<td><textarea name="wooenc_add_consent_description_checkout" id="wooenc_add_consent_description_checkout" class="regular-text" aria-required="true" rows="3"><?php echo $wooenc_add_consent_description_checkout ? $wooenc_add_consent_description_checkout : 'You read and agreed to our Privacy Policy.' ?></textarea></td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wooenc_add_consent_checkboxes_registration">WordPress Registration <p class="description">The consent checkboxes will be added automatically to the registration form</p></label>
								</th>
								<td>
									<label><input name="wooenc_add_consent_checkboxes_registration" type="checkbox" id="wooenc_add_consent_checkboxes_registration" value="1" <?php echo $wooenc_add_consent_checkboxes_registration ? 'checked' : '' ?>> Enable</label>
								</td>
							</tr>
							<tr scope="row" class="registration-box">
								<th><label for="wooenc_add_consent_description_registration">Registration Consent Description <p class="description">HTML tags not allowed.</p></label></th>
								<td><textarea name="wooenc_add_consent_description_registration" id="wooenc_add_consent_description_registration" class="regular-text" aria-required="true" rows="3"><?php echo $wooenc_add_consent_description_registration ? $wooenc_add_consent_description_registration : 'You read and agreed to our Privacy Policy.' ?></textarea></td>
							</tr>
							<tr>
								<th scope="row">
									<label for="wooenc_add_consent_checkboxes_comment">WordPress Comment <p class="description">When activated the GDPR checkbox will be added automatically just above the submit button.</p></label>
								</th>
								<td>
									<label><input name="wooenc_add_consent_checkboxes_comment" type="checkbox" id="wooenc_add_consent_checkboxes_comment" value="1" <?php echo $wooenc_add_consent_checkboxes_comment ? 'checked' : '' ?>> Enable</label>
								</td>
							</tr>
							<tr scope="row" class="comment-box">
								<th><label for="wooenc_add_consent_description_comment">Comment Consent Description <p class="description">HTML tags not allowed.</p></label></th>
								<td><textarea name="wooenc_add_consent_description_comment" id="wooenc_add_consent_description_comment" class="regular-text" aria-required="true" rows="3"><?php echo $wooenc_add_consent_description_comment ? $wooenc_add_consent_description_comment : 'You read and agreed to our Privacy Policy.' ?></textarea></td>
							</tr>
						</tbody>
					</table>
					<input type="submit" name="submit" class="button button-primary" value="Save Changes">
				</form>
			</div>
		</div>
		<div class="wooenc-col25">
			<div class="white-box rating-box">
				<h2>Rate Our Plugin</h2>
				<div class="star-ratings">
					<span class="dashicons dashicons-star-filled"></span>
					<span class="dashicons dashicons-star-filled"></span>
					<span class="dashicons dashicons-star-filled"></span>
					<span class="dashicons dashicons-star-filled"></span>
					<span class="dashicons dashicons-star-filled"></span>
				</div>
				<p>Did WooCommerce GDPR(DSGVO) Data Protection help you out? Please leave a 5-star review. Thank you!</p>
				<a href="#" class="button button-primary">Write a review</a>
			</div>
			<div class="white-box">
				<h2>Buy Premium</h2>
				<p>Do you want to discover all plugin features without any limitations? Would you like to try it?</p>
				<a href="http://www.codemenschen.at/downloads/woocommerce-gdpr-dsgvo-data-protection/" class="button button-primary" target="_blank">Buy Premium Plugin</a>
			</div>
			<div class="white-box">
				<h2>Support</h2>
				<p>Need a helping hand? Please ask for help on the <a href="http://www.codemenschen.at/submit-ticket/" target="_blank">Support forum</a>. Be sure to mention your WordPress version and give as much additional information as possible.</p>
				<a href="http://www.codemenschen.at/submit-ticket/?page=tickets&section=create-ticket" class="button button-primary" target="_blank">Submit your question</a>
			</div>
			<div class="white-box">
				<h2>Customization Service</h2>
				<p>We are a European Company. To hire our agency to help you with this plugin installation or any other customization or requirements please contact us through our site <a href="http://www.codemenschen.at/contact-us" target="_blank">contact form</a> or email <a href="mailto:office@telberia.com">office@telberia.com</a> directly.</p>
				<a href="http://www.codemenschen.at/contact-us" class="button button-primary" target="_blank">Hire Us Now</a>
			</div>
		</div>
	</div>
	<span class="wooenc-disclaimer">Thank you for using <b>WooCommerce GDPR (DSGVO) Data Protection</b>.<br>Disclaimer: The creators of this plugin do not have a legal background please contact a law firm for rock solid legal advice.</span>
</div>