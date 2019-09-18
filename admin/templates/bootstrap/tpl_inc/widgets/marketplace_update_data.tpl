{if count($oMarketplaceUpdates_arr) > 0}
    <p class="linklist_head">Plugin Name <span class="right">Version</p>
    <ul class="linklist padded infolist">

        {foreach name="marketplace_updates" from=$oMarketplaceUpdates_arr item=oMarketplaceUpdate}
            <li>
                <p><a href="{$oMarketplaceUpdate->cUrl}" target="_blank">
                    {$oMarketplaceUpdate->cName|truncate:'50':'...'}
                    <span class="date">{$oMarketplaceUpdate->cVersion}</span>
                </a></p>
            </li>
        {/foreach}
    </ul>
{else}
    <div class="alert alert-success" role="alert">
        <p>Alle Plugins sind auf einem aktuellen Stand.</p>
    </div>
{/if}