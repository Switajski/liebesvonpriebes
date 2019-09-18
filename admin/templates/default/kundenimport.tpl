{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: kundenimport.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="kundenimport"}
{include file="tpl_inc/seite_header.tpl" cTitel=#customerImport# cBeschreibung=#customerImportDesc# cDokuURL=#customerImportURL#}
<div id="content">
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
	 <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
	 <p class="box_error">{$fehler}</p>
	 {/if}
	 
	 <div class="container">
	 <form name="kundenimporter" method="post" action="kundenimport.php" enctype="multipart/form-data">
	 <input type="hidden" name="kundenimport" value="1" />
	 <div class="settings">
	 <p><label for="kSprache">{#language#}</label>
	 <select name="kSprache" id="kSprache" class="combo">
	 {foreach name=sprache from=$sprachen item=sprache}
	 <option value="{$sprache->kSprache}">{$sprache->cNameDeutsch}</option>
	 {/foreach}
	 </select></p>
	 <p><label for="kKundengruppe">{#customerGroup#}</label>
	 <select name="kKundengruppe" id="kKundengruppe" class="combo">
	 {foreach name=kdgrp from=$kundengruppen item=kundengruppe}
	 {assign var="kKundengruppe" value=$kundengruppe->kKundengruppe}
	 <option value="{$kundengruppe->kKundengruppe}">{$kundengruppe->cName}</option>
	 {/foreach}
	 </select></p> 
		 <p style="margin-bottom:5px"><strong>{#generateNewPass#}</p>
	 <p style="margin:0 0 15px;"><select name="PasswortGenerieren" id="PasswortGenerieren" class="comboFullSize">
	 <option value="0">{#passNo#}</option>
	 <option value="1">{#passYes#}</option>
	 </select></p>
		 <p><label for="csv">{#csvFile#}</label>
	 <input type="file" name="csv" id="csv"  tabindex="1" /></p>
			  
	 </div>      
	 <p class="submit"><input type="submit" value="{#import#}" class="button orange" /></p>
	 </form>
</div>
{include file='tpl_inc/footer.tpl'}