/* 
 * Login and Pay with Amazon - Utilities
 */
$(document).ready(function () {

    /*
     * Logout the amazon session as well when the user clicks on the logout button
     */
    $('a[href*="?logout=1"]').click(lpa_logout);

    /*
     * LPA Tooltip
     */
    $('.lpa-tooltip').hover(function () {
        /* Hover in code */
        var text = $(this).data('lpaTooltipText');
        $('<p id="lpa-tooltip-content"></p>').text(text).appendTo('body').fadeIn('slow');
    }, function () {
        /* Hover out code */
        $('#lpa-tooltip-content').remove();
    }).mousemove(function (e) {
        var mouseX = e.pageX + 20;
        var mouseY = e.pageY + 10;
        $('#lpa-tooltip-content').css({top: mouseY + "px", left: mouseX + "px"});
    });

    /*
     * Remove links in Amazon Pay paid orders in Bestellübersicht
     */
    $('*:contains("Amazon Payments") > a[href^="bestellab_again"], *:contains("Amazon Pay") > a[href^="bestellab_again"]').each(function (e) {
        var $this = $(this);
        $this.hide();
        $this.after('Amazon Pay');
    });

});

function lpa_logout() {
    document.cookie = "amazon_Login_accessToken=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
    document.cookie = "lpa_address_consent_token=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
    document.cookie = "lpa_redirect=; expires=Thu, 01 Jan 1970 00:00:00 GMT";
    if ((typeof window.amazon !== 'undefined') && (typeof window.amazon.Login !== 'undefined')) {
        window.amazon.Login.logout();
    }
}

function lpa_toggle_passwords() {
    if (jQuery('#lpa-create-account:checked').length) {
        jQuery('#lpa-create-passwords').css('max-height', 'none');
        jQuery('#lpa-create-passwords').css('visibility', 'visible');
    } else {
        jQuery('#lpa-create-passwords').css('max-height', '0');
        jQuery('#lpa-create-passwords').css('visibility', 'hidden');
    }
}
