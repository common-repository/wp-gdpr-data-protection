<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_shortcode('wooenc_reqdata_form', 'wooenc_reqdata_form_function');

function wooenc_reqdata_form_function() {

	$output = '<div class="wooenc-request-page">';
	if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'confirmaction') {
		$output .= wooenc_get_reqdata();
	} else {
		$output .= '<form name="wooenc_reqdata_form" method="POST" id="wooenc-request-form">';
		$output .= apply_filters(
			'wooenc_reqdata_form_email_field',
			sprintf(
				'<p><input type="email" name="wooenc_reqdata_email" id="wooenc-reqdata-form__email" placeholder="%s" required/></p>',
				esc_attr__(apply_filters('wooenc_reqdata_form_email_placeholder', __('Email Address')))
			)
		);
		$output .= apply_filters(
			'wooenc_reqdata_form_request_type_field',
			sprintf(
				'<p><select name="wooenc_reqdata_request_type" id="wooenc-reqdata-form__request_type" required><option value="" selected disabled>%s</option><option value="export">Export Data</option><option value="erase">Erase Data</option></select></p>',
				esc_attr__(apply_filters('wooenc_reqdata_form_request_type_placeholder', __('Select Data Request type')))
			)
		);
		$output .= apply_filters(
			'wooenc_reqdata_form_consent_field',
			sprintf(
				'<p><label><input type="checkbox" name="wooenc_request_consent" id="wooenc-reqdata-form__consent" value="1" required /> %s</label></p>',
				esc_attr__(apply_filters('wooenc_reqdata_form_consent_label', __('By using this form you agree with the storage and handling of your data by this website.')))
			)
		);
		$output .= apply_filters(
			'wooenc_reqdata_form_submit_field',
			sprintf(
				'<p><input type="submit" name="wooenc_request_submit" value="%s" id="wooenc-reqdata-form__btn"/></p>',
				esc_attr__(apply_filters('wooenc_reqdata_form_submit_label', __('Send Request')))
			)
		);
		$output .= '<div class="wooenc-feedback" style="display: none;"></div>';
		$output .= '</form>';
	}

	$output .= '</div>';
	return apply_filters('wooenc_reqdata_form', $output);
}

function wooenc_get_reqdata() {

}