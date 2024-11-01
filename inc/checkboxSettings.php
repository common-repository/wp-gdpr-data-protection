<?php

if( !defined( 'ABSPATH' ) ) exit;  // Exit if accessed directly

function WooEnc_checkboxSettings() {
	$wooenc_add_consent_checkboxes_registration = get_option( 'WOOEncryptionRegistrationConsentCheckbox', false );
	$wooenc_add_consent_checkboxes_checkout = get_option( 'WOOEncryptionCheckoutConsentCheckbox', false );
	$wooenc_add_consent_checkboxes_comment = get_option('WOOEncryptionCommentConsentCheckbox', false);
	if ( $wooenc_add_consent_checkboxes_registration ) {
		add_action( 'register_form', 'WooEnc_addRegistrationFormField' );
		add_action( 'woocommerce_register_form', 'WooEnc_addRegistrationFormField' );
		add_action( 'registration_errors', 'WooEnc_addRegistrationErrors', 10, 3 );
		add_action( 'user_register', 'WooEnc_saveUserConsentOnRegistration' );
	}
	if ( $wooenc_add_consent_checkboxes_comment ) {
		add_filter('comment_form_submit_field', 'WooEnc_addCommentFormField', 999);
		add_action('pre_comment_on_post', 'WooEnc_checkCommentForm');
	}
	if ( $wooenc_add_consent_checkboxes_checkout ) {
			add_action( 'woocommerce_review_order_before_submit', 'WooEnc_addWoocommerceCheckoutField', 10, 2 );
			add_filter( 'woocommerce_checkout_process', 'WooEnc_checkWoocommerceCheckoutForm' );
	}
}
add_action('init', 'WooEnc_checkboxSettings');

function WooEnc_addCommentFormField($submitField = '') {
	$wooenc_add_consent_description_comment = get_option( 'WOOEncryptionRegistrationConsentDescription' );
	$field = apply_filters(
		'wooenc_wordpress_field',
		'<p class="wooenc-checkbox"><label><input type="checkbox" name="wooenc" id="wooenc" value="1" /> ' . $wooenc_add_consent_description_comment . ' <abbr class="required" title="' . esc_attr__('required', 'WooEnc') . '">*</abbr></label></p>',
		$submitField
	);
    return $field . $submitField;
}

function WooEnc_checkCommentForm() {
	if (!isset($_POST['wooenc'])) {
		wp_die(
			'<p>' . sprintf(
				__('<strong>ERROR</strong>: %s', 'WooEnc'),
				'Please accept the privacy checkbox.'
			) . '</p>',
			__('Comment Submission Failure'),
			array('back_link' => true)
		);
	}
}

function WooEnc_addWoocommerceCheckoutField() {
	$wooenc_add_consent_description_checkout = get_option( 'WOOEncryptionRegistrationConsentDescription' );
	$args = array(
		'type' => 'checkbox',
		'label' => $wooenc_add_consent_description_checkout,
		'required' => true,
	);
	woocommerce_form_field('wooenc', apply_filters('wooenc_woocommerce_field_args', $args));
}

function WooEnc_checkWoocommerceCheckoutForm() {
	if (!isset($_POST['wooenc'])) {
		wc_add_notice('<b>Error:</b> Please accept the privacy checkbox.', 'error');
	}
}

function WooEnc_addRegistrationFormField() {
	$wooenc_add_consent_description_registration = get_option( 'WOOEncryptionRegistrationConsentDescription' );
	$privacy = ( ! empty( $_POST['wooenc'] ) ) ? sanitize_text_field( $_POST['wooenc'] ) : ''; ?>
	<p class="wooenc-checkbox"><label><input type="checkbox" name="wooenc" id="wooenc" value="1" required/> <?php echo $wooenc_add_consent_description_registration; ?> <abbr class="required" title="<?php echo esc_attr__('required', 'WooEnc') ?>">*</abbr></label></p>
	<?php
}

function WooEnc_addRegistrationErrors( $errors, $sanitized_user_login, $user_email ) {
	if ( ! isset( $_POST['wooenc'] ) ) {
		$errors->add( 'missing_required_consents', sprintf(
			'<strong>%s</strong>: %s.',
			__( 'ERROR', 'WooEnc' ),
			__( 'Please accept the privacy checkbox', 'WooEnc' )
		) );
    }
    return $errors;
}

function WooEnc_saveUserConsentOnRegistration( $user_id ) {
	if ( isset( $_POST['wooenc'] ) ) {
		update_user_meta( $user_id, 'wooenc_privacy_consent', $_POST['wooenc'] );
	}
}