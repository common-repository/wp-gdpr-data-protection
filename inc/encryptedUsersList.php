<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

/**
 * Encrypted Users page
 */

		global $wpdb;
		$table_name = $wpdb->prefix . 'users';

   		if ( !current_user_can( 'manage_options' ) )
   		{
      		    wp_die( 'You are not allowed to be on this page.' );
   		}

   		$error = 0;

   		if ( isset($_POST['encryptionKeyField']) )
   		{
      		    // Check that nonce field
   	  	    wp_verify_nonce( $_POST['encryption_key_verify'], 'encryption_key_verify' );

		    $encryptionKey = $_POST['encryptionKeyField'];

			$key = WooEnc_get_key();

		    $key = base64_encode($key);

		    if($key != $encryptionKey) {$error=1;}

   		}
		?>
		<div class="wrap encrypt-user-table">
			<h1>All Encrypted Users</h1>
			<div class="content">
				<div id="post-body" class="metabox-holder">
					<div class="key-box">
						<form method="post" name="encryption-key-form" action="<?php echo admin_url( 'admin.php' ); ?>?page=wooenc-users-lists">
							<?php $nonce = wp_create_nonce( 'encryption_key_verify' ); ?>
							<input type="hidden" name="encryption_key_verify" value="<?php echo($nonce); ?>">
							<input type="password" id="user-input-key" name="encryptionKeyField" value="" autocomplete="off" placeholder="Insert Your Key" required="required">
							<input type="submit" id="encrypted-key-submit" class="button" value="Submit Key">
							<?php if($error) { ?><div class="errorKey"><span>Key is not matched</span></div><?php } ?>
						</form>
					</div>
					<hr>
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->users_obj->prepare_items();
								$this->users_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>