{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: keywording.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="keywording"}
{include file="tpl_inc/seite_header.tpl" cTitel=#excludeKeywords# cBeschreibung=#keywordingDesc#}
<div id="content">
	 <div id="settings">
		  <form name="login" method="post" action="keywording.php">
				<input type="hidden" name="keywording" value="1" />
				
				{foreach name=sprachen from=$sprachen item=sprache}
				{assign var="cISO" value=$sprache->cISO}
					 <div class="category">{$sprache->cNameDeutsch}</div>
					 <div class="item">
						  <div class="name">{#excludeKeywords#} Text ({#spaceSeparated#})</div>
						  <div class="for">
								<textarea name="keywords_{$cISO}" rows="10" class="p99">{$keywords[$cISO]}</textarea>
						  </div>
				{/foreach}
				
				<p class="submit"><input type="submit" value="{#save#}" class="button orange" /></p>
		  </form>
	 </div>
</div>
{include file='tpl_inc/footer.tpl'}