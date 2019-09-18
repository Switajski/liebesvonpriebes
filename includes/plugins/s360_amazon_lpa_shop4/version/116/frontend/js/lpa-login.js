/* 
 * Solution 360 GmbH
 */


function lpa_addressSelectedOnCreate(orderReference) {
    $('#lpa-create-submit').css('visibility', 'hidden');
    $('.lpa-error-message').hide();
   
    var request = $.ajax({
        url: lpa_ajax_url_select_account_address,
        type: "post",
        dataType: "json",
        data: {'lpa_ajax':'1', 'orid': orderReference.getAmazonOrderReferenceId()}
    });
    request.done(function (data) {
        if (data.status === 'success') {
            var adr = data.address;
            for(var key in adr) {
                if(adr.hasOwnProperty(key)) {
                    $('#lpa-create-account-form input[name="'+key+'"]').val(adr[key]);
                }
            }
            $('#lpa-create-submit').show(); /* compatibility for older plugin versions where in the custom template the button might be display:none by default */
            $('#lpa-create-submit').css('visibility', 'visible');
        } else {
            $('#lpa-error-' + data.code).show();
        }
    });
    request.fail(function (jqXHR, textStatus, errorThrown) {
        console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
    });
    request.always(function () {

    });
}