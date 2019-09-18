/* 
 * Login and Pay with Amazon - Utilities
 */
$(document).ready(function () {
    
    /*
     * Logout the amazon session as well when the user clicks on the logout button
     */
    $('a[href^="jtl.php?logout=1"]').click(lpa_logout);

    /*
     * Catch clicks on the checkout with amazon button and prevent redirect to amazon if user is already logged in.
     */
    $('.lpa-pay-button').click(function (e) {
        if (document.cookie.indexOf('amazon_Login_accessToken') >= 0 && document.URL.indexOf('lpacheckout') === -1) {
            e.preventDefault();
            e.stopImmediatePropagation();
            window.location = lpa_other_url_checkout;
        } else {
            /*
             * Due to the fix for Safari on checkout, we need to force the redirect cookie to the checkout-site after returning.
             */
            document.cookie = 'lpa_redirect='+lpa_other_url_checkout+'; path=/';
        }
    });

    /*
     * LPA Tooltip
     */
    $('.lpa-tooltip').hover(function () {
        // Hover in code
        var text = $(this).data('lpaTooltipText');
        $('<p id="lpa-tooltip-content"></p>').text(text).appendTo('body').fadeIn('slow');
    }, function () {
        // Hover out code
        $('#lpa-tooltip-content').remove();
    }).mousemove(function (e) {
        var mouseX = e.pageX + 20;
        var mouseY = e.pageY + 10;
        $('#lpa-tooltip-content').css({top: mouseY + "px", left: mouseX + "px"});
    });
    
    /*
     * Remove links in Amazon Payments paid orders in Bestellübersicht
     */
    $('*:contains("Amazon Payments") > a[href^="bestellab_again"]').each(function (e) {
        var $this = $(this);
        $this.hide();
        $this.after('Amazon Payments');
    });

});

function lpa_logout() {
    if ((typeof window.amazon !== 'undefined') && (typeof window.amazon.Login !== 'undefined')) {
        window.amazon.Login.logout();
        document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
        document.cookie = "lpa_address_consent_token=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
    }
}

