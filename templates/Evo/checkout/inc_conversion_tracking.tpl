{if empty($conversion_tracked)}
    {assign var="conversion_tracked" value=1}
    <div id="conversiontracking">
        {block name="checkout-conversion-tracking"}
        {* Google Analytics E-Commerce Tracking *}
        {if !empty($Einstellungen.global.global_google_analytics_id)}
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
        {/block}
    </div>
{/if}