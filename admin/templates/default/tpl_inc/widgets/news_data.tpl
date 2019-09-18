{if is_array($oNews_arr)}
    <ul class="linklist">
    {foreach name="news" from=$oNews_arr item=oNews}
        <li>
	        <img src="http://images.jtl-software.de/shop/icon_info.gif" alt="" title="{$oNews->cBetreff}" />
            <a href="{$oNews->cUrlExt|urldecode}" title="{$oNews->cBetreff}" target="_blank">{$oNews->cBetreff|truncate:'50':'...'}</a>
            <span class="date">{$oNews->dGueltigVon|date_format:"%d.%m.%Y"}</span>
        </li>
    {/foreach}
    </ul>
{else}
   <p class="container tcenter"><span class="error">Keine Daten verf&uuml;gbar</span></p>
{/if}

{*
{if is_array($oNews_arr)}
   <ul class="linklist">
   {foreach name="news" from=$oNews_arr item=oNews}
      <li>
         {if $oNews->cIconURL|count_characters > 0}
            <img src="{$oNews->cIconURL|urldecode}" alt="" title="{$oNews->cHeadline}" />
         {/if}
         <a href="{$oNews->cURL|urldecode}" title="{$oNews->cHeadline}" target="_blank">{$oNews->cHeadline|truncate:'50':'...'}</a>
         <span class="date">{$oNews->cCreated|date_format:"%d.%m.%Y"}</span>
      </li>
   {/foreach}
   </ul>
{else}
   <p class="container tcenter"><span class="error">Keine Daten verf&uuml;gbar</span></p>
{/if}
*}