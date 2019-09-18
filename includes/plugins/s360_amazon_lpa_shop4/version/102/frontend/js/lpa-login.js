/* 
 * Solution 360 GmbH
 */


function lpa_addressSelectedOnCreate(orderReference) {
    // send request to our form (set via script provider)
    $('#lpa-create-submit').hide();
    $('.lpa-error-message').hide();
   
    var request = $.ajax({
        url: lpa_ajax_url_select_account_address,
        type: "post",
        dataType: "json",
        data: {'orid': orderReference.getAmazonOrderReferenceId()}
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
            $('#lpa-create-submit').show();
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