{if isset($cError)}
    <p class="box_error">{$cError}</p>
{/if}
<div id="amazonpayments">
    <div id="order_completed">
        <div class="box_info alert alert-info">{lang key="orderConfirmationPost" section="checkout"}</div>
        <p>{lang key="yourOrderId" section="checkout"}: {$Bestellung->cBestellNr}</p>
        <p>{lang key="yourChosenPaymentOption" section="checkout"}: {$Bestellung->cZahlungsartName}</p>
    </div>
    {if $lpa_shop3_compatibility === "1"}
        {if !$conversion_tracked}
        {assign var="conversion_tracked" value=1}
        <div id="conversiontracking">
            {* Google Analytics E-Commerce Tracking *}
            {if $Einstellungen.global.global_google_analytics_id}
                <script type="text/javascript">
                    var _gaq = _gaq || [];
                    _gaq.push(['_setAccount', '{$Einstellungen.global.global_google_analytics_id}']);
                    _gaq.push(['_gat._anonymizeIp']);
                    _gaq.push(['_trackPageview']);

                    (function () {ldelim}
                            var ga = document.createElement('script');
                            ga.type = 'text/javascript';
                            ga.async = true;
                            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                            var s = document.getElementsByTagName('script')[0];
                            s.parentNode.insertBefore(ga, s);
                    {rdelim})();

                    {if $Einstellungen.global.global_google_ecommerce == 1}
                       _gaq.push(['_addTrans',
                           '{$Bestellung->cBestellNr}',
                           '{if $Einstellungen.global.global_shopname}{$Einstellungen.global.global_shopname}{else}{$Firma->cName}{/if}',
                           '{$Bestellung->fWarensummeNetto}',
                           '{$Bestellung->fSteuern}',
                           '{$Bestellung->fVersandNetto}',
                           '{$smarty.session.Kunde->cOrt}',
                           '{$smarty.session.Kunde->cBundesland}',
                           '{$smarty.session.Kunde->cLand}'
                       ]);

                        {foreach name=Bestell item=order from=$Bestellung->Positionen}
                            {if $order->nPosTyp == 1}
                       _gaq.push(['_addItem',
                           '{$Bestellung->cBestellNr}',
                           '{$order->cArtNr}',
                           '{$order->cName}',
                           '{$order->Category}',
                           '{$order->fPreis}',
                           '{$order->nAnzahl|replace:",":"."}'
                       ]);
                            {/if}
                        {/foreach}

                       _gaq.push(['_trackTrans']);
                    {/if}
                </script>
            {/if}




            {* Google Adwords Conversion Tracking. Assign your ga_conversion_id and ga_conversion_label to activate Adwords Conversion Tracking*}

            {assign var="ga_conversion_id" value=""}
            {assign var="ga_conversion_label" value=""}

            {if $ga_conversion_id ne "" && $ga_conversion_label ne ""}
                <script type="text/javascript">
                    /* <![CDATA[ */
                    var google_conversion_id = {$ga_conversion_id};
                    var google_conversion_language = "de";
                    var google_conversion_format = "3";
                    var google_conversion_color = "ffffff";
                    var google_conversion_label = "{$ga_conversion_label}";
                    var google_conversion_value = {$Bestellung->fWarensummeNetto};
                    /* ]]> */
                </script>
                <script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js">
                </script>
                <noscript>
                <img height="0" width="0" class="hidden" alt="" src="https://www.googleadservices.com/pagead/conversion/{$ga_conversion_id}/?value={$Bestellung->fWarensummeNetto}&amp;label={$ga_conversion_label}&amp;guid=ON&amp;script=0" />
                </noscript>
            {/if}

        </div>
    {/if}
    {else}
        {include file='checkout/inc_conversion_tracking.tpl'}
    {/if}
</div>