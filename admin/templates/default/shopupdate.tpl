{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: shopupdate.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: Niclas Potthast niclas@jtl-software.de, JTL-Software
	Date: 2008-07-21
	Version: 2.17

	http://www.jtl-software.de
	Copyright (c) 2008 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="shopupdate"}
{include file="tpl_inc/seite_header.tpl" cTitel=#updateShop# cBeschreibung=#updateShopDesc# cDokuURL=#updateShopURL#}
<div id="content">
	
	{if !$bAllowURLOpen}
		<p class="box_error">{#updateShopAllowURLOpen#}</p>
	{/if}
	
	<p>
	{if $Version->nVersionDB_new > 0}
		<a class="externURL" href="http://wiki.jtl-software.de/index.php/5._UPDATES_EINSPIELEN_JTL-SHOP_V2#JTL-Shop_{$strCurrentVersion}" rel="external">{#changelogVersion#} {$strCurrentVersion}</a>
	{else}
		<a class="externURL button" href="http://wiki.jtl-software.de/index.php/5._UPDATES_EINSPIELEN_JTL-SHOP_V2#5.3_Changelogs" rel="external">{#changelogLastVersion#}</a>
	{/if}
	</p>
	
	
	<p class="updateLeft">{#currentShopVersion#}:</p> <p class="updateRight"><strong style="{if $Version->nVersionDB>$Version->nVersion}color:#f66;{/if}">{$strFileVersion}</strong></p>
	<p class="updateLeft-1">{#currentDBVersion#}:</p> <p class="updateRight-1"><strong style="{if $Version->nVersionDB<$Version->nVersion}color:#f66;{/if}">{$strDBVersion}</strong></p>
	<p class="updateLeft-2">{#currentLiveVersion#}:</p> <p class="updateRight-2"><strong>{if $Version->nVersionDB_new > 0}{$strCurrentVersion}{else}-{/if}</strong></p>
	<p class="updateLeft-3">{#lastUpdate#}:</p> <p class="updateRight-3"><strong>{$Version->dAktualisiert}</strong></p>
	<p class="clearer" />
	
	{if $bUpdateError=="1"}<p><span class="warning">{#updateFileError#}</span></p>{/if}
	{if $mysqlError}<p><span class="warning">{#updateDBError#} "{$mysqlError}" ({$mysqlErrorRow})</span></p>{/if}
	{if ($Version->nVersion > $Version->nVersionDB) || $mysqlError}
	<form method="post">
	<input type="hidden" name="shopupdate" value="1" />
	<input type="submit" value="{#updateDB#}" class="button orange" />
	</form>
	{/if}
</div>
{include file='tpl_inc/footer.tpl'}