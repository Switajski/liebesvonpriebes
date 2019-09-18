{if count($oHelp_arr) > 0}
    {foreach name="help" from=$oHelp_arr item=oHelp}
        <li>
            <p>
                {if $oHelp->cIconURL|strlen > 0}
                    <img src="{$oHelp->cIconURL|urldecode}" alt="" title="{$oHelp->cTitle}" />
                {/if}
                <a href="{$oHelp->cURL}" title="{$oHelp->cTitle}" target="_blank">{$oHelp->cTitle|utf8_decode|truncate:'50':'...'}</a>
            </p>
        </li>
    {/foreach}
{/if}
