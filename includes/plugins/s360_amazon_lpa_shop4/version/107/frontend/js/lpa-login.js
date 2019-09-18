/* 
 * Solution 360 GmbH
 */


function lpa_addressSelectedOnCreate(orderReference) {
    // send request to our form (set via script provider)
    $('#lpa-create-submit').css('visibility', 'hidden');
    $('.lpa-error-message').hide();
   
    var request = $.ajax({
        url: lpa_ajax_url_select_account_address,
        type: "post",
        dataType: "json",
        data: {'lpa_ajax':'1', 'orid': orderReference.getAmazonOrderReferenceId()}
    });
    // callback handler that will be called on success
    request.done(function (data) {
        if (data.status === 'success') {
            var adr = data.address;
            for(var key in adr) {
                if(adr.hasOwnProperty(key)) {
                    $('#lpa-create-account-form input[name="'+key+'"]').val(adr[key]);
                }
            }
            $('#lpa-create-submit').show(); // compatibility for older plugin versions where in the custom template the button might be display:none by default
            $('#lpa-create-submit').css('visibility', 'visible');
        } else {
            $('#lpa-error-' + data.code).show();
        }
    });
    // callback handler that will be called on failure
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
    });
    // callback handler that will be called regardless
    // if the request failed or succeeded
    request.always(function () {

    });
}