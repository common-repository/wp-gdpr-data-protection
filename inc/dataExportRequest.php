<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly


	global $wpdb;
	$posts_table = $wpdb->prefix . 'posts';

   	if ( !current_user_can( 'manage_options' ) )
   	{
		wp_die( __( 'Sorry, you are not allowed to export personal data on this site.' ) );
   	}
   	
	// "Borrow" xfn.js for now so we don't have to create new files.
	wp_enqueue_script( 'xfn' );

   	$error = 0;
   	if ( isset($_POST['wooenc_reqdata_email']) )
   	{
        // Check that nonce field
   	  	wp_verify_nonce( $_POST['wooenc_request_nonce_verify'], 'wooenc_request_nonce_verify' );

		$emailAddress = sanitize_email($_POST['wooenc_reqdata_email']);
		$requestType = 'export';

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
			$subject = '['.$blogname.'] Confirm Action: Export Personal Data';
			$message = 'Howdy,<br><br>A request has been made to perform the following action on your account:<br><br><b>Export Personal Data</b><br><br>To confirm this, please click on the following link: <a href="'.get_page_link(get_option('wooenc_requestdatapage')).'?action=confirmaction&request_id='.$lastid.'&confirm_key='.$confirm_key.'">'.get_page_link(get_option('wooenc_requestdatapage')).'?action=confirmaction&request_id='.$lastid.'&confirm_key='.$confirm_key.'</a><br><br>You can safely ignore and delete this email if you do not want to take this action.<br><br>This email has been sent to '.$emailAddress.'<br><br>Regards,<br>All at '.$blogname.'<br>'.$blogurl;
			if(wooenc_sendMail($emailAddress, $admin_email, $subject, $message)) {
				$settype = 'updated';
				$setmessage = __('Confirmation request initiated successfully.', 'WooEnc');
			} else {
				$settype = 'error';
				 $setmessage = __('Something went wrong while mailing you the request. Please try again.', 'WooEnc');
			}
		} else {
			$settype = 'error';
			$setmessage = __('A request for this email address already exists.', 'WooEnc');
		}

    	add_settings_error(
        	'privacy_action_email_request',
        	esc_attr( 'settings_updated' ),
        	$setmessage,
        	$settype
	    );
   	}

	$requests_table = new WooEnc_Data_Export_Requests_Table( array(
		'plural'   => 'privacy_requests',
		'singular' => 'privacy_request',
	) );

	$requests_table->process_bulk_action();
	$requests_table->prepare_items();

?>

<div class="wrap wooenc nosubsub">
	<h1><?php esc_html_e( 'Data Export Request' ); ?></h1>
	<hr class="wp-header-end">

	<?php settings_errors(); ?>

	<form method="post" class="wp-privacy-request-form" action="<?php echo admin_url( 'admin.php' ); ?>?page=wooenc_export_personal_data">
		<h2><?php esc_html_e( 'Add Data Export Request'); ?></h2>
		<p><?php esc_html_e( 'An email will be sent to the user at this email address asking them to verify the request.' ); ?></p>

		<div class="wp-privacy-request-form-field">
			<label for="wooenc_reqdata_email"><?php esc_html_e( 'Email address' ); ?></label>
			<input type="email" required class="regular-text" id="wooenc_reqdata_email" name="wooenc_reqdata_email" autocomplete="off">
			<?php submit_button( __( 'Send Request' ), 'secondary', 'submit', false ); ?>
		</div>
		<?php $nonce = wp_create_nonce( 'wooenc_request_nonce_verify' ); ?>
		<input type="hidden" name="wooenc_request_nonce_verify" value="<?php echo($nonce); ?>">

		<input type="hidden" name="_wp_http_referer" value="<?php echo admin_url( 'admin.php' ); ?>?page=wooenc_export_personal_data">
		<input type="hidden" name="action" value="wooenc_add_export_personal_data_request">
		<input type="hidden" name="type_of_action" value="wooenc_export_personal_data">
	</form>
	<hr>

	<?php $requests_table->views(); ?>

	<form class="search-form wp-clearfix">
		<?php $requests_table->search_box( __( 'Search Requests' ), 'requests' ); ?>
		<input type="hidden" name="page" value="wooenc_export_personal_data" />
		<input type="hidden" name="filter-status" value="<?php echo isset( $_REQUEST['filter-status'] ) ? esc_attr( sanitize_text_field( $_REQUEST['filter-status'] ) ) : ''; ?>" />
		<input type="hidden" name="orderby" value="<?php echo isset( $_REQUEST['orderby'] ) ? esc_attr( sanitize_text_field( $_REQUEST['orderby'] ) ) : ''; ?>" />
		<input type="hidden" name="order" value="<?php echo isset( $_REQUEST['order'] ) ? esc_attr( sanitize_text_field( $_REQUEST['order'] ) ) : ''; ?>" />
	</form>

	<form method="post">
		<?php
		$requests_table->display();
		$requests_table->embed_scripts();
		?>
	</form>
</div>