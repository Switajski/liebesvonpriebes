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

    window.lpaRegisterTooltips = function() {
        var $tooltips = $('.lpa-tooltip').not('.tooltip-initialized');
        $tooltips.hover(function (e) {
            var $this = $(this).closest('.lpa-tooltip');
            /* Hover in code */
            var text = $this.data('lpaTooltipText');
            if (text) {
                $('<p id="lpa-tooltip-content"></p>').text(text).appendTo('body').data('addedByHover', true).fadeIn(500);
            }
        }, function () {
            /* Hover out code */
            $('#lpa-tooltip-content').fadeOut(500, function () {
                $(this).remove();
            });
        }).mousemove(function (e) {
            var mouseX = e.pageX + 20;
            var mouseY = e.pageY + 10;
            $('#lpa-tooltip-content').css({top: mouseY + "px", left: mouseX + "px"});
        });

        $tooltips.click(function (e) {
            var $this = $(this).closest('.lpa-tooltip');
            var $tooltipContent = $('#lpa-tooltip-content');
            if ($tooltipContent.length) {
                if ($tooltipContent.data('addedByClick')) {
                    $tooltipContent.fadeOut(500, function () {
                        $(this).remove();
                    });
                } else {
                    $tooltipContent.data('addedByClick', true); // make sure the tooltip gets removed on the next click
                }
            } else {
                var text = $this.data('lpaTooltipText');
                if (text) {
                    $('<p id="lpa-tooltip-content"></p>').text(text).appendTo('body').data('addedByClick', true).fadeIn(500);
                }
                var mouseX = e.pageX + 20;
                var mouseY = e.pageY + 10;
                $('#lpa-tooltip-content').css({top: mouseY + "px", left: mouseX + "px"});
            }
        });

        $tooltips.addClass('tooltip-initialized');
    };
    lpaRegisterTooltips();
    $(document).ajaxComplete(function( event, xhr, settings ) {
        if(typeof settings !== "undefined" && settings.url === 'io.php' && typeof settings.data === 'string' && settings.data.includes('checkVarkombiDependencies') && xhr.status === 200) {
            lpaRegisterTooltips();
        }
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
