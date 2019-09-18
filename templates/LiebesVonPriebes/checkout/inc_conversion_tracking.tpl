{* Beispiel-Datei zur Erweiterung des Evo-Templates inc_conversion_tracking.tpl *} 

{extends file="../../Evo/checkout/inc_conversion_tracking.tpl"} 

{* Beispiel-Code für Google Adwords Conversion Tracking. Der nachfolgende Code wird im Block "checkout-conversion-tracking" im Evo angehängt (append) *} 
{block name="checkout-conversion-tracking" append}

    {* Google Adwords Conversion Tracking. Assign your ga_conversion_id and ga_conversion_label to activate Adwords Conversion Tracking*}
    {assign var="ga_conversion_id" value=""}
    {assign var="ga_conversion_label" value=""}
    
    {if $ga_conversion_id !== '' && $ga_conversion_label !== ''}
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
        <script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js"></script>
        <noscript>
            <img height="0" width="0" class="hidden" alt="" src="https://www.googleadservices.com/pagead/conversion/{$ga_conversion_id}/?value={$Bestellung->fWarensummeNetto}&amp;label={$ga_conversion_label}&amp;guid=ON&amp;script=0" />
        </noscript>
    {/if}
{/block}