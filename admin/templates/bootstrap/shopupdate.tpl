{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="shopupdate"}
{include file='tpl_inc/seite_header.tpl' cTitel=#updateShop# cBeschreibung=#updateShopDesc# cDokuURL=#updateShopURL#}
<div id="content" class="container-fluid settings">
    {if !$bAllowURLOpen}
        <div class="alert alert-danger">{#updateShopAllowURLOpen#}</div>
    {/if}
    <p>
        {if $Version->nVersionDB_new > 0}
            <a class="externURL" href="https://www.jtl-software.de/Onlineshop-Software-JTL-Shop" rel="external">{#changelogVersion#} {$strCurrentVersion}</a>
        {else}
            <a class="externURL button" href="http://guide.jtl-software.de/jtl/JTL-Shop:Installation:Changelog" rel="external">{#changelogLastVersion#}</a>
        {/if}
    </p>
    <p class="updateLeft">{#currentShopVersion#}:</p>
    <p class="updateRight">
        <strong style="{if $Version->nVersionDB>$Version->nVersion}color:#f66;{/if}">{$strFileVersion}</strong>
    </p>
    <p class="updateLeft-1">{#currentDBVersion#}:</p>
    <p class="updateRight-1">
        <strong style="{if $Version->nVersionDB<$Version->nVersion}color:#f66;{/if}">{$strDBVersion}</strong>
    </p>
    <p class="updateLeft-2">{#currentLiveVersion#}:</p>
    <p class="updateRight-2"><strong>{if $Version->nVersionDB_new > 0}{$strCurrentVersion}{else}-{/if}</strong></p>
    <p class="updateLeft-3">{#lastUpdate#}:</p>
    <p class="updateRight-3"><strong>{$Version->dAktualisiert}</strong></p>
    <p class="clearer"></p>
    {if $bUpdateError == '1'}
        <p><span class="warning">{#updateFileError#}</span></p>
    {/if}
    {if $mysqlError}
        <p><span class="warning">{#updateDBError#} "{$mysqlError}" ({$mysqlErrorRow})</span></p>
    {/if}
    {if ($Version->nVersion > $Version->nVersionDB) || $mysqlError}
        <form method="post">
            {$jtl_token}
            <input type="hidden" name="shopupdate" value="1" />
            <input type="submit" value="{#updateDB#}" class="btn btn-primary" />
        </form>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}