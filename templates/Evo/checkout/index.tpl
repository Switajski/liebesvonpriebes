{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
 
{block name="header"}
    {if !isset($bAjaxRequest) || !$bAjaxRequest}
        {include file='layout/header.tpl'}
    {/if}
{/block}

{block name="content"}
    <div id="result-wrapper">
        <div id="checkout">
            {include file="checkout/inc_steps.tpl"}
    
            {include file="snippets/extension.tpl"}
            {if $step === 'accountwahl'}
                {include file='checkout/step0_login_or_register.tpl'}{*bestellvorgang_accountwahl.tpl*}
            {elseif $step === 'edit_customer_address' || $step === 'Lieferadresse'}
                {include file='checkout/step1_edit_customer_address.tpl'}{*bestellvorgang_unregistriert_formular.tpl*}
            {elseif $step === 'Versand' || $step === 'Zahlung'}
                {include file='checkout/step3_shipping_options.tpl'}{*bestellvorgang_versand.tpl*}
            {elseif $step === 'ZahlungZusatzschritt'}
                {include file='checkout/step4_payment_additional.tpl'}{*bestellvorgang_zahlung_zusatzschritt*}
            {elseif $step === 'Bestaetigung'}
                {include file='checkout/step5_confirmation.tpl'}{*bestellvorgang_bestaetigung*}
            {/if}
        </div>
    </div>
    
    {if (isset($nWarenkorb2PersMerge) && $nWarenkorb2PersMerge === 1)}
        <script type="text/javascript">
            $(function() {
                eModal.confirm({ldelim}message: '{lang key="basket2PersMerge" section="login"}', label1: '{lang key="no" section="global"}', label2: '{lang key="yes" section="global"}'{rdelim}, '{lang key="basket" section="global"}', function(res) {
                    if (res) {
                        window.location = "{get_static_route id='bestellvorgang.php'}?basket2Pers=1"
                    }
                });
            });
        </script>
    {/if}
    
    <script type="text/javascript">
        if (top.location !== self.location) {ldelim}
            top.location = self.location.href;
        {rdelim}
    </script>
{/block}

{block name="footer"}
    {if !isset($bAjaxRequest) || !$bAjaxRequest}
        {include file='layout/footer.tpl'}
    {/if}
{/block}