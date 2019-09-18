{if $error}
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
                <i class="fa fa-times" aria-hidden="true"></i>
            </button>
            <h4 class="modal-title">{$l10n['jtl_paypal_verify_error']}</h4>
        </div>
        <div class="modal-body">
            <div class="alert alert-warning">
                {capture link_content assign=link}
                    {if $sameAsBilling}
                        <a href="bestellvorgang.php?editRechnungsadresse=1">{lang key="billingAdress" section="checkout"}</a>
                    {else}
                        <a href="bestellvorgang.php?editLieferadresse=1">{lang key="shippingAdress" section="checkout"}</a>
                    {/if}
                {/capture}
                <p>{$l10n['jtl_paypal_verify_error_text']|replace:"%link%":$link}</p>
            </div>

            <dl class="dl-horizontal">
                {foreach $error->getDetails() as $detail}
                <dt>{lang key=$detail->getField() section="account data"}</dt>
                <dd class="text-muted">{$detail->getIssue()}</dd>
                {/foreach}
            </dl>
        </div>
        <div class="modal-footer">
            {if $sameAsBilling}
                <a class="btn btn-primary" href="bestellvorgang.php?editRechnungsadresse=1">{lang key="modifyBillingAdress" section="global"}</a>
            {else}
                <a class="btn btn-primary" href="bestellvorgang.php?editLieferadresse=1">{lang key="modifyShippingAdress" section="checkout"}</a>
            {/if}
        </div>
    </div>
{else}
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">
                <i class="fa fa-times" aria-hidden="true"></i>
            </button>
            <h4 class="modal-title">{$l10n['jtl_paypal_verify_internal']}</h4>
        </div>
        <div class="modal-body">
            {$l10n['jtl_paypal_verify_internal_text']}
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">{lang key="modifyPaymentOption" section="checkout"}</button>
        </div>
    </div>
{/if}