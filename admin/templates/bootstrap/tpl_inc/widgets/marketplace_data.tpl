{if count($oMarketplace_arr) > 0}
    {foreach name="marketplace" from=$oMarketplace_arr item=oMarketplaceGroup}
        {if $oMarketplaceGroup->oExtension_arr|@count > 0}
            <h5 class="linklist_head">{$oMarketplaceGroup->cName|truncate:'50':'...'}</h5>
            <ul class="linklist padded">
                {foreach from=$oMarketplaceGroup->oExtension_arr item=oExtension}
                    <li {if $oExtension->bHighlight}class="highlight"{/if}>
                        <img src="{$oExtension->cLogoPfad}" />
                        <p><a href="{$oExtension->cUrl}" target="_blank">
                            {$oExtension->cName|truncate:'50':'...'}
                            {if $oExtension->cKurzBeschreibung|strlen > 0}
                                {$oExtension->cKurzBeschreibung|truncate:'50':'...'}
                            {/if}
                        </a></p>
                    </li>
                {/foreach}
            </ul>
        {/if}
    {/foreach}
{else}
    <div class="alert alert-success" role="alert">
        <strong>Zur Zeit stehen keine Erweiterungen zur Verf&uuml;gung</strong>
    </div>
{/if}
