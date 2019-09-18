{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: shoplogouploader_uebersicht.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*} 
{include file="tpl_inc/seite_header.tpl" cTitel=#shoplogouploader# cBeschreibung=#shoplogouploaderDesc# cDokuURL=#shoplogouploaderURL#}
<div id="content"> 
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="clearall">
		{if $ShopLogoURL|count_characters > 0}
		<div class="left block" style="margin-right: 15px">
			<img src="{$URL_SHOP}/{$ShopLogoURL}?rnd={$cRnd}">
		</div>
		{/if}
		<div class="left">
			<p class="box_info">
				Hier k&ouml;nnen Sie Ihr Logo als JPG-, GIF- oder PNG-Datei hochladen.
			</p>
			<div class="container">
				<form name="uploader" method="POST" action="shoplogouploader.php" enctype="multipart/form-data">
					<input type="hidden" name="{$session_name}" value="{$session_id}" />
					<input type="hidden" name="upload" value="1" />
					
					<p class="container"><input name="shopLogo" type="file" accept="image/*"></p>
					<p class="container"><input type="checkbox" name="delete" /> altes Logo l&ouml;schen</p>
					
					<p class="container"><input type="submit" value="{#shoplogouploaderSave#}" class="button orange" /></p>
				</form>
			</div>
		</div>
	</div>
</div>