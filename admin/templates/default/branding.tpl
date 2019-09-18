{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: branding.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="branding"}
{include file='tpl_inc/header.tpl'}

{include file="tpl_inc/seite_header.tpl" cTitel=#branding# cBeschreibung=#brandingDesc# cDokuURL=#brandingUrl#}
<div id="content">
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<form name="branding" method="post" action="branding.php">
	<input type="hidden" name="branding" value="1" />
	<div class="block tcenter">
	<label for="{#brandingActive#}">{#brandingPictureKat#}:</strong></label>
	<select name="kBranding" class="selectBox" id="{#brandingActive#}" onchange="javascript:document.branding.submit();">
	<option value="0"></option>
	{foreach name=brandings from=$oBranding_arr item=oBrandingTMP}
	<option value="{$oBrandingTMP->kBranding}" {if $oBrandingTMP->kBranding == $oBranding->kBrandingTMP}selected{/if}>{$oBrandingTMP->cBildKategorie}</option>
	{/foreach}
	</select>
	</div>
	</form>
		
	{if $oBranding->kBrandingTMP > 0}
		<div class="container clearall">
			
			{if $oBranding->cBrandingBild|count_characters > 0}
				<div class="left block" style="margin-right: 15px">
					<img src="{$URL_SHOP}/{$PFAD_BRANDINGBILDER}{$oBranding->cBrandingBild}?rnd={$cRnd}">
				</div>
			{/if}
			
			<div class="no_overflow" id="settings">

				<form name="einstellen" method="post" action="branding.php" enctype="multipart/form-data">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="branding" value="1" />
				<input type="hidden" name="kBranding" value="{$oBranding->kBrandingTMP}" />
				<input type="hidden" name="speicher_einstellung" value="1" />
				<div class="settings">
				<p><label for="{#brandingActive#}">{#brandingActive#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#brandingActiveDesc#}" title="{#brandingActiveDesc#}" style="vertical-align:middle; cursor:help;" /></label>
				<select name="nAktiv" id="nAktiv" class="combo"> 
				<option value="1"{if $oBranding->nAktiv == 1} selected{/if}>Ja</option>
				<option value="0"{if $oBranding->nAktiv == 0} selected{/if}>Nein</option>
				</select></p>
				
				<p><label for="{#brandingPosition#}">{#brandingPosition#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#brandingPositionDesc#}" title="{#brandingPositionDesc#}" style="vertical-align:middle; cursor:help;" /></label>
				<select name="cPosition" id="cPosition" class="combo"> 
				<option value="oben"{if $oBranding->cPosition == "oben"} selected{/if}>oben</option>
				<option value="oben-rechts"{if $oBranding->cPosition == "oben-rechts"} selected{/if}>oben-rechts</option>
				<option value="rechts"{if $oBranding->cPosition == "rechts"} selected{/if}>rechts</option>
				<option value="unten-rechts"{if $oBranding->cPosition == "unten-rechts"} selected{/if}>unten-rechts</option>
				<option value="unten"{if $oBranding->cPosition == "unten"} selected{/if}>unten</option>
				<option value="unten-links"{if $oBranding->cPosition == "unten-links"} selected{/if}>unten-links</option>
				<option value="links"{if $oBranding->cPosition == "links"} selected{/if}>links</option>
				<option value="oben-links"{if $oBranding->cPosition == "oben-links"} selected{/if}>oben-links</option>
				<option value="zentriert"{if $oBranding->cPosition == "zentriert"} selected{/if}>zentriert</option>
				</select></p>
				
				<p><label for="{#brandingTransparency#}">{#brandingTransparency#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#brandingTransparencyDesc#}" title="{#brandingTransparencyDesc#}" style="vertical-align:middle; cursor:help;" /></label>
				<input type="text" name="dTransparenz" id="dTransparenz"  value="{$oBranding->dTransparenz}" tabindex="1" /></p>
				
				<p><label for="{#brandingSize#}">{#brandingSize#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#brandingSizeDesc#}" title="{#brandingSizeDesc#}" style="vertical-align:middle; cursor:help;" /></label>
				<input type="text" name="dGroesse" id="dGroesse"  value="{$oBranding->dGroesse}" tabindex="1" /></p>
				
				<p><label for="{#brandingEdgeDistance#}">{#brandingEdgeDistance#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#brandingEdgeDistanceDesc#}" title="{#brandingEdgeDistanceDesc#}" style="vertical-align:middle; cursor:help;" /></label>
				<input type="text" name="dRandabstand" id="dRandabstand"  value="{$oBranding->dRandabstand}" tabindex="1" /></p>
				
				<p><label for="{#brandingFileName#}">{#brandingFileName#} <img src="{$currentTemplateDir}gfx/help.png" alt="{#brandingFileNameDesc#}" title="{#brandingFileNameDesc#}" style="vertical-align:middle; cursor:help;" /></label>
				<input type="file" name="cBrandingBild" maxlength="2097152" accept="image/jpeg,image/gif,image/png,image/bmp" id="cBrandingBild"  value="" tabindex="1" /></p>
				</div>
				
				<p class="submit"><input type="submit" value="{#save#}" class="button orange" /></p>
				</form>
			
			</div>
			
		</div>
	{/if}
	
</div>

{include file='tpl_inc/footer.tpl'}