<script type="text/javascript">
var method = '{$pqMethodCart}';
var selector = '{$pqSelectorCart}';

$(function() {
    _push_paypalexpress();
    $(document).on('evo:loaded.io.request', function() {
        _push_paypalexpress();
    });
});

function _push_paypalexpress() {
    $('.btn-ppe-cart-container').each(function(i, item) {
        if ($(item).parents('#paypalexpress-basket').length === 0) {
            $(item).remove();
        }
    });
    if ($(selector).length > 0) {
        var content = $('#paypalexpress-basket').html();
        $(selector)[method](content);
    }
}
</script>

<div id="paypalexpress-basket" style="display: none">
    <li class="btn-ppe-cart-container">
      <a href="index.php?s={$link->kLink}&jtl_paypal_checkout_cart=1" class="paypalexpress btn-ppe-cart-popup">
        <img src="{$ppCheckout}" alt="{$oPlugin->cName}" />
      </a>
    </li>
</div>