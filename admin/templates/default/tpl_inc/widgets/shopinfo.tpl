<div class="widget-custom-data">
   <ul class="infolist clearall">
      <li class="first">
         <p class="key">Shopversion<span class="value" id="current_shop_version">{$strFileVersion} {if $strMinorVersion != '0'}(Build: {$strMinorVersion}){/if}</span></p>
      </li>
      <li>
         <p class="key">Templateversion<span class="value" id="current_shop_version">{$strTplVersion}</span></p>
      </li>
      <li>
         <p class="key">Datenbankversion <span class="value">{$strDBVersion}</span></p>
      </li>
      <li>
         <p class="key">Datenbank zuletzt aktualisiert <span class="value">{$strUpdated}</span></p>
      </li>
    {if isset($oSubscription->kShop) && $oSubscription->kShop > 0}
      <li>
      {if isset($oSubscription->cUpdate)}
         <p class="key"><span class="error">
         {if $oSubscription->nDayDiff < 0}Subscription ist abgelaufen!{else}Subscription l&auml;uft{if $oSubscription->nDayDiff == 0} heute{else} in{if $oSubscription->nDayDiff > 1} {$oSubscription->nDayDiff} Tagen{else} einem Tag{/if}{/if} ab!{/if}</span> 
         <span class="value"><a href="{$oSubscription->cUpdate}" target="_blank" class="button blue">Update</a></span></p>
      {else}
         <p class="key">Subscription g&uuml;ltig bis <span class="value success">{$oSubscription->dDownloadBis_DE}</span></p>
      {/if}
      </li>
    {/if}
      <li class="last">
         <div id="version_data_wrapper">
            <p class="ajax_preloader update">Nach Aktualisierungen suchen...</p>
         </div>
      </li>
   </ul>
</div>

<script type="text/javascript">
$(document).ready(function() {ldelim}
   xajax_getRemoteDataAjax('{$JTLURL_GET_SHOPVERSION}?v={$nVersionFile}', 'oVersion', 'widgets/shopinfo_version.tpl', 'version_data_wrapper');
{rdelim});
</script>
