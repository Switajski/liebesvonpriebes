{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: links_neuer_link.tpl, smarty template inc file

	page for JTL-Shop 3
	Admin

	Author: JTL-Software-GmbH
	http://www.jtl-software.de

	Copyright (c) 2007 JTL-Software


-------------------------------------------------------------------------------
*}
<script type="text/javascript">
function append_file_selector()
{ldelim}
	var file_input = $('<input type="file" name="Bilder[]" maxlength="2097152" accept="image/*" />');
	var container = $('<p class="multi_input vmiddle"><a href="#" title="Entfernen"><img src="{$currentTemplateDir}/gfx/layout/delete.png" class="vmiddle" /></a></p>').prepend(file_input);
	$('#file_input_wrapper').append(container);
	$(container).find('img').bind('click', function() {ldelim}
		$(file_input).parent().remove();
		return false;
	{rdelim});
	$(file_input).trigger('click');
	return false;
{rdelim}

{literal}
$(function() {
	$('#lang').change(function() {
		var iso = $('#lang option:selected').val();
		$('.iso_wrapper').slideUp();
		$('#iso_' + iso).slideDown();
		return false;
	});
	
	$('input[name="nLinkart"]').change(function() {
		var lnk = $('input[name="nLinkart"]:checked').val();
	}).trigger('change');

	$('#content_template_type ul li a').click(function() {
		
		$('#content_template_type ul li a').parent().removeClass('active');
		$(this).parent().addClass('active');
		
		var tpl = $(this).parent().attr('rel');
		if (tpl.length == 0)
			tpl = 'default';
		
		xajax_getContentTemplate(tpl);

		return false;
	});
});

function link_dynamic_init() {
	$('.ckeditor_dyn').each(function(idx, item) {
		set_editor($(item).attr('id'));
	});
}

function set_editor(id) {
    var instance = CKEDITOR.instances[id];
    if (instance)
        CKEDITOR.remove(instance);
    CKEDITOR.replace(id);
}

{/literal}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#newLinks#}

{if isset($hinweis) && $hinweis|count_characters > 0}
    <p class="box_success">{$hinweis}</p>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}
    <p class="box_error">{$fehler}</p>
{/if}

<div id="content">
	 <div id="settings">
		  <form name="link_erstellen" method="post" action="links.php" enctype="multipart/form-data">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="neu_link" value="1" />
				<input type="hidden" name="kLinkgruppe" value="{$Link->kLinkgruppe}" />
				<input type="hidden" name="kLink" value="{$Link->kLink}" />
				<input type="hidden" name="kPlugin" value="{$Link->kPlugin}" />

				<div class="category">
					 Allgemein
				</div>

				<div class="item">
					 <div class="name"><label for="cName">{#link#}</label></div>
					 <div class="for"><input type="text" name="cName" id="cName"{if isset($xPlausiVar_arr.cName)} class="fieldfillout"{/if}  value="{if isset($xPostVar_arr.cName) && $xPostVar_arr.cName}{$xPostVar_arr.cName}{elseif isset($Link->cName)}{$Link->cName}{/if}" tabindex="1" />{if isset($xPlausiVar_arr.cName)}<font class="fillout">{#FillOut#}</font>{/if}</div>
				</div>

				<div class="item">
					 <div class="name">
						  {#linkType#}
					 </div>
					 <div class="{if isset($xPlausiVar_arr.nLinkart)}for fieldfillout{else}for{/if}">
						  {* workaround
						   * todo: tspezialseite bei der Plugininstallation f�llen
						   *}
						  {if isset($Link->kPlugin) && $Link->kPlugin > 0}
								<p class="multi_input">
									 <input type="hidden" name="nLinkart" value="25" />
									 <input type="radio" id="nLink3" name="nLinkart" checked="checked" disabled="disabled" />
									 <label for="nLink3">{#linkToSpecalPage#}</label>
									 <select name="nSpezialseite" disabled="disabled">
										  <option selected="selected">Plugin</option>
									 </select>
								</p>
						  {else}
								<p class="multi_input">
									 <input type="radio" id="nLink1" name="nLinkart" value="1" tabindex="2" {if $Link->nLinkart==1}checked{/if} />
									 <label for="nLink1">{#linkWithOwnContent#}</label>
								</p>
								<p class="multi_input">
									 <input type="radio" id="nLink2" name="nLinkart" value="2" onclick="$('#nLinkInput2').val('http://')" tabindex="3" {if $Link->nLinkart==2}checked{/if} />
									<label for="nLink2">{#linkToExternalURL#}</label>
									 <input type="text" name="cURL" value="{if isset($Link->cURL)}{$Link->cURL}{/if}" id="nLinkInput2" />
								</p>
								<p class="multi_input">
									 <input type="radio" id="nLink3" name="nLinkart" value="3" {if $Link->nLinkart>2}checked{/if} />
									 <label for="nLink3">{#linkToSpecalPage#}</label>
									 <select name="nSpezialseite">
										  <option value="0">{#choose#}</option>
										  {foreach name=spezialseiten from=$oSpezialseite_arr item=oSpezialseite}
												<option value="{$oSpezialseite->nLinkart}" {if $Link->nLinkart == $oSpezialseite->nLinkart}selected{/if}>{$oSpezialseite->cName}</option>
										  {/foreach}
									 </select>
								</p>
						  {/if}                          
                          {if isset($xPlausiVar_arr.nLinkart)}<font class="fillout">{#FillOut#}</font>{/if}
					 </div>                    
				</div>

				<div class="item">
					 <div class="name">
						  <label for="cKundengruppen">{#restrictedToCustomerGroups#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#multipleChoice#}" title="{#multipleChoice#}" style="vertical-align:middle; cursor:help;" /></label>
					 </div>
					 <div class="for">
						  <select name="cKundengruppen[]"{if isset($xPlausiVar_arr.cKundengruppen)} class="fieldfillout"{/if} multiple="multiple" size="6" id="cKundengruppen">
                                <option value="-1"{if $Link->kLink > 0 && isset($gesetzteKundengruppen[0]) && $gesetzteKundengruppen[0]} selected{elseif isset($xPostVar_arr.cKundengruppen)}
                                {foreach name=postkndgrp from=$xPostVar_arr.cKundengruppen item=cPostKndGrp}
                                    {if $cPostKndGrp|count_characters > 0 && $cPostKndGrp == "-1"}selected{/if}
                                {/foreach}
                                {elseif !$Link->kLink}selected{/if}>{#all#}</option>
                                
								{foreach name=kdgrp from=$kundengruppen item=kundengruppe}
									 {assign var="kKundengruppe" value=$kundengruppe->kKundengruppe}
                                     {assign var=postkndgrp value='0'}
                                     {foreach name=postkndgrp from=$xPostVar_arr.cKundengruppen item=cPostKndGrp}
                                        {if $cPostKndGrp == $kKundengruppe}{assign var=postkndgrp value='1'}{/if}
                                     {/foreach}
									 <option value="{$kundengruppe->kKundengruppe}" {if (isset($gesetzteKundengruppen[$kKundengruppe]) && $gesetzteKundengruppen[$kKundengruppe]) || (isset($postkndgrp) && $postkndgrp == 1)}selected{/if}>{$kundengruppe->cName}</option>
								{/foreach}
						 </select>
                         {if isset($xPlausiVar_arr.cKundengruppen)}<font class="fillout">{#FillOut#}</font>{/if}
					 </div>
				</div>

				<div class="item">
					 <div class="name"><label for="cSichtbarNachLogin">{#visibleAfterLogin#}</label></div>
					 <div class="for"><input type="checkbox" name="cSichtbarNachLogin" id="cSichtbarNachLogin" value="Y" {if $Link->cSichtbarNachLogin=='Y' || (isset($xPostVar_arr.cSichtbarNachLogin) && $xPostVar_arr.cSichtbarNachLogin)}checked{/if} /></div>
				</div>

				<div class="item">
					 <div class="name"><label for="cDruckButton">{#showPrintButton#}</label></div>
					 <div class="for"><input type="checkbox" name="cDruckButton" id="cDruckButton" value="Y" {if $Link->cDruckButton=='Y' || (isset($xPostVar_arr.cDruckButton) && $xPostVar_arr.cDruckButton)}checked{/if} /></div>
				</div>
                
                <div class="item">
					 <div class="name"><label for="cNoFollow">{#noFollow#}</label></div>
					 <div class="for"><input type="checkbox" name="cNoFollow" id="cNoFollow" value="Y" {if $Link->cNoFollow == "Y" || (isset($xPostVar_arr.cNoFollow) && $xPostVar_arr.cNoFollow)}checked{/if} /></div>
				</div>

				<div class="item">
					 <div class="name"><label for="nSort">{#sortNo#}</label></div>
					 <div class="for"><input type="text" name="nSort" id="nSort"  value="{if isset($xPostVar_arr.nSort) && $xPostVar_arr.nSort}{$xPostVar_arr.nSort}{elseif isset($Link->nSort)}{$Link->nSort}{/if}" tabindex="6" /></div>
				</div>

				<!-- OLD -->
				<div class="item">
					 <div class="name">
						  Bilder <img src="{$currentTemplateDir}gfx/help.png" alt="{#titleDesc#}" title="{#titleDesc#}" style="vertical-align:middle; cursor:help;" />
					 </div>
					 <div class="for">
						  <div id="file_input_wrapper">
								<p class="multi_input"><input id="Bilder_0" name="Bilder[]" type="file"  maxlength="2097152" accept="image/*" /></p>
						  </div>
						  <div class="container"><input name="hinzufuegen" type="button" value="{#linkPicAdd#}" onclick="return append_file_selector();" class="button blue" /></div>
					 </div>
				</div>

				<div class="container elem">
					 <div class="name">{#linkPics#}</div>
					 <div class="for vmiddle">
						 {if isset($cDatei_arr)}
						  {foreach name=bilder from=$cDatei_arr item=cDatei}
								<span class="block tcenter vmiddle">
									<a href="links.php?kLink={$Link->kLink}&delpic=1&cName={$cDatei->cNameFull}{if isset($Link->kPlugin) && $Link->kPlugin > 0}{$Link->kPlugin}{/if}"><img src="templates/default/gfx/layout/remove.png" alt="delete"></a>
									 $#{$cDatei->cName}#$
									 <div>{$cDatei->cURL}</div>
								</span>
						  {/foreach}
						 {/if}
					 </div>
				</div>
				<!-- // OLD -->
				
				<div class="container block tcenter">
					<label for="lang">Sprache:</label>
					<select name="cISO" id="lang">
					{foreach name=sprachen from=$sprachen item=sprache}
						<option value="{$sprache->cISO}" {if $sprache->cShopStandard=="Y"}selected="selected"{/if}>{$sprache->cNameDeutsch} {if $sprache->cShopStandard=="Y"}(Standard){/if}</option>
					{/foreach}
					</select>
				</div>

				{foreach name=sprachen from=$sprachen item=sprache}
					 {assign var="cISO" value=$sprache->cISO}
               
                <div id="iso_{$cISO}" class="iso_wrapper {if $sprache->cShopStandard!="Y"}hidden{/if}">
               
					<div class="category">Meta / Seo ({$sprache->cNameDeutsch})</div>

					<div class="item">
						<div class="name"><label for="cName_{$cISO}">{#showedName#}</label></div>
						{assign var=cName_ISO value="cName_`$cISO`"}
						<div class="for"><input type="text" name="cName_{$cISO}" id="cName_{$cISO}"  value="{if isset($xPostVar_arr.$cName_ISO) && $xPostVar_arr.$cName_ISO}{$xPostVar_arr.$cName_ISO}{elseif isset($Linkname[$cISO])}{$Linkname[$cISO]}{/if}" tabindex="7" /></div>
					</div>

					<div class="item">
						<div class="name"><label for="cSeo_{$cISO}">{#linkSeo#}</label></div>
						{assign var=cSeo_ISO value="cSeo_`$cISO`"}
						<div class="for"><input type="text" name="cSeo_{$cISO}" id="cSeo_{$cISO}"  value="{if isset($xPostVar_arr.$cSeo_ISO) && $xPostVar_arr.$cSeo_ISO}{$xPostVar_arr.$cSeo_ISO}{elseif isset($Linkseo[$cISO])}{$Linkseo[$cISO]}{/if}" tabindex="7" /></div>
					</div>

					<div class="item">
						{assign var=cTitle_ISO value="cTitle_`$cISO`"}
						<div class="name"><label for="cTitle_{$cISO}">{#linkTitle#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#titleDesc#}" title="{#titleDesc#}" style="vertical-align:middle; cursor:help;" /></label></div>
						<div class="for"><input type="text" name="cTitle_{$cISO}" id="cTitle_{$cISO}"  value="{if isset($xPostVar_arr.$cTitle_ISO) && $xPostVar_arr.$cTitle_ISO}{$xPostVar_arr.$cTitle_ISO}{elseif isset($Linktitle[$cISO])}{$Linktitle[$cISO]}{/if}" tabindex="8" /></div>
					</div>

					<!-- OLD -->
					<div class="item">
						{assign var=cContent_ISO value="cContent_`$cISO`"}
						<div class="name"><label for="cContent_{$cISO}">{#linkContent#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#titleDesc#}" title="{#titleDesc#}" style="vertical-align:middle; cursor:help;" /></label></div>
						<div class="for"><textarea class="ckeditor" id="cContent_{$cISO}" name="cContent_{$cISO}" rows="10" cols="40">{if isset($xPostVar_arr.$cContent_ISO) && $xPostVar_arr.$cContent_ISO}{$xPostVar_arr.$cContent_ISO}{elseif isset($Linkcontent[$cISO])}{$Linkcontent[$cISO]}{/if}</textarea></div>
					</div>
					<!-- // OLD -->

					<div class="item">
						{assign var=cMetaTitle_ISO value="cMetaTitle_`$cISO`"}
						<div class="name"><label for="cMetaTitle_{$cISO}">{#metaTitle#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#metaTitleDesc#}" title="{#metaTitleDesc#}" style="vertical-align:middle; cursor:help;" /></label></div>
						<div class="for"><input type="text" name="cMetaTitle_{$cISO}" id="cMetaTitle_{$cISO}"  value="{if isset($xPostVar_arr.$cMetaTitle_ISO) && $xPostVar_arr.$cMetaTitle_ISO}{$xPostVar_arr.$cMetaTitle_ISO}{elseif isset($Linkmetatitle[$cISO])}{$Linkmetatitle[$cISO]}{/if}" tabindex="9" /></div>
					</div>

					<div class="item">
						{assign var=cMetaKeywords_ISO value="cMetaKeywords_`$cISO`"}
						<div class="name"><label for="cMetaKeywords_{$cISO}">{#metaKeywords#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#metaKeywordsDesc#}" title="{#metaKeywordsDesc#}" style="vertical-align:middle; cursor:help;" /></label></div>
						<div class="for"><input type="text" name="cMetaKeywords_{$cISO}" id="cMetaKeywords_{$cISO}"  value="{if isset($xPostVar_arr.$cMetaKeywords_ISO) && $xPostVar_arr.$cMetaKeywords_ISO}{$xPostVar_arr.$cMetaKeywords_ISO}{elseif isset($Linkmetakeys[$cISO])}{$Linkmetakeys[$cISO]}{/if}" tabindex="9" /></div>
					</div>

					<div class="item">
						{assign var=cMetaDescription_ISO value="cMetaDescription_`$cISO`"}
						<div class="name"><label for="cMetaDescription_{$cISO}">{#metaDescription#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#metaDescriptionDesc#}" title="{#metaDescriptionDesc#}" style="vertical-align:middle; cursor:help;" /></label></div>
						<div class="for"><input type="text" name="cMetaDescription_{$cISO}" id="cMetaDescription_{$cISO}"  value="{if isset($xPostVar_arr.$cMetaDescription_ISO) && $xPostVar_arr.$cMetaDescription_ISO}{$xPostVar_arr.$cMetaDescription_ISO}{elseif isset($Linkmetadesc[$cISO])}{$Linkmetadesc[$cISO]}{/if}" tabindex="9" /></div>
					</div>
					
				</div>
				{/foreach}
				
				<!-- NEW 
				<div class="category">
					Darstellung
				</div>
				
				<div id="content_template_type" class="clearall">
					<ul>
						<li rel="default">
							<a href="#"><img src="templates/gfx/contenttypes/cl0.png" /></a>
						</li>
						<li rel="layout1">
							<a href="#"><img src="templates/gfx/contenttypes/cl1.png" /></a>
						</li>
						<li rel="layout2">
							<a href="#"><img src="templates/gfx/contenttypes/cl2.png" /></a>
						</li>
						<li rel="layout3">
							<a href="#"><img src="templates/gfx/contenttypes/cl3.png" /></a>
						</li>
						<li rel="layout4">
							<a href="#"><img src="templates/gfx/contenttypes/cl4.png" /></a>
						</li>
						<li rel="layout5">
							<a href="#"><img src="templates/gfx/contenttypes/cl5.png" /></a>
						</li>
					</ul>
				</div>
				
				<div class="category">
					Darstellung - Eigenschaften
				</div>
				
				<div id="content_template_data">
				
					none set :/
					
				</div>
				-->

				<div class="save_wrapper">
					<input type="submit" value="{#newLinksSave#}" class="button orange" />
				</div>
		  </form>
	 </div>
</div>
