{if count($oHelp_arr) > 0}
   {foreach name="help" from=$oHelp_arr item=oHelp}
      <li>
         {if $oHelp->cIconURL|count_characters > 0}
            <img src="{$oHelp->cIconURL|urldecode}" alt="" title="{$oHelp->cTitle}" />
         {/if}
         <a href="{$oHelp->cURL}" title="{$oHelp->cTitle}" target="_blank">{$oHelp->cTitle|truncate:'50':'...'}</a>
      </li>
   {/foreach}
{/if}
