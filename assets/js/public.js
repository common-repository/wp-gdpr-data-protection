jQuery(document).ready(function($) {
    $('#wooenc-request-form').on('submit', function(e) {

        e.preventDefault();

        if(!$('input[name=wooenc_request_consent]').is(':checked')) {
            alert('You need to accept the privacy checkbox.');
            return false;
        }
        var $feedback = $('.wooenc-feedback'),
            email = $('#wooenc-reqdata-form__email').val(),
            type = $('#wooenc-reqdata-form__request_type').val(),
            consent = $('input[name=wooenc_request_consent]:checked').val();
        $.ajax({
            url: WooEncAjax_front.ajaxurl,
            type: "POST",
            data: 'action=wooenc_doajax_process_action&email='+email+'&type='+type+'&consent='+consent,
            dataType:'json',
            success: function(response) {
                if (response.message) {
                    $feedback.html(response.message);
                    $feedback.removeClass('wooenc-feedback--error').addClass('wooenc-feedback--success');
                    $feedback.show();
                    $('#wooenc-request-form').reset();
                }
                if (response.error) {
                    $('#wooenc-reqdata-form__email').focus();
                    $feedback.html(response.error);
                    $feedback.removeClass('wooenc-feedback--success').addClass('wooenc-feedback--error');
                    $feedback.show();
                }
            }
        });
    });
});