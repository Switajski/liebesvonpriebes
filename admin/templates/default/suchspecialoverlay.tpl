{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: suchspecialoverlay.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="suchspecialoverlay"}
{include file='tpl_inc/header.tpl'}

{include file="tpl_inc/seite_header.tpl" cTitel=#suchspecialoverlay# cBeschreibung=#suchspecialoverlayDesc# cDokuURL=#suchspecialoverlayUrl#}
<div id="content">	
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
	 
	<div class="block tcenter">
		{if isset($Sprachen) && $Sprachen|@count > 1}
			<form name="sprache" method="post" action="suchspecialoverlay.php" class="inline_block">
				<label for="{#changeLanguage#}">{#changeLanguage#}</label>
				<input type="hidden" name="sprachwechsel" value="1" />
				<select id="{#changeLanguage#}" name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
				{foreach name=sprachen from=$Sprachen item=sprache}
				<option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
				{/foreach}
				</select>
			</form>
		{/if}
		
		<form name="suchspecialoverlay" method="post" action="suchspecialoverlay.php" class="inline_block">
			<input type="hidden" name="suchspecialoverlay" value="1" />
			<label for="{#suchspecial#}">{#suchspecial#}</label>
			<select name="kSuchspecialOverlay" class="selectBox" id="{#suchspecial#}" onchange="javascript:document.suchspecialoverlay.submit();">
				<option value="0"></option>
				{foreach name=suchspecialoverlay from=$oSuchspecialOverlay_arr item=oSuchspecialOverlayTMP}
				<option value="{$oSuchspecialOverlayTMP->kSuchspecialOverlay}" {if $oSuchspecialOverlayTMP->kSuchspecialOverlay == $oSuchspecialOverlay->kSuchspecialOverlay}selected{/if}>{$oSuchspecialOverlayTMP->cSuchspecial}</option>
				{/foreach}
			</select>
		</form>
	</div>

	{if $oSuchspecialOverlay->kSuchspecialOverlay > 0}
		<div class="container">
			<form name="einstellen" method="post" action="suchspecialoverlay.php" enctype="multipart/form-data">
				<input type="hidden" name="{$session_name}" value="{$session_id}" />
				<input type="hidden" name="suchspecialoverlay" value="1" />
				<input type="hidden" name="kSuchspecialOverlay" value="{$oSuchspecialOverlay->kSuchspecialOverlay}" />
				<input type="hidden" name="speicher_einstellung" value="1" />
				
				<div class="clearall">
					
					{if $oSuchspecialOverlay->cBildPfad|count_characters > 0}
						<div class="left block" style="margin-right: 15px">
							<img src="{$URL_SHOP}/{$PFAD_SUCHSPECIALOVERLAY}{$oSuchspecialOverlay->cBildPfad}?rnd={$cRnd}">
						</div>
					{/if}
					
					<div class="no_overflow" id="settings">
						
						<div class="item">
							<div class="name">
								<label for="{#suchspecialoverlayActive#}">{#suchspecialoverlayActive#}</label>
							</div>
							<div class="for">
								<select name="nAktiv" id="nAktiv" class="combo"> 
									<option value="1"{if $oSuchspecialOverlay->nAktiv == 1} selected{/if}>Ja</option>
									<option value="0"{if $oSuchspecialOverlay->nAktiv == 0} selected{/if}>Nein</option>
								</select>
								<div class="help" ref="1" title="{#suchspecialoverlayActiveDesc#}"></div>
							</div>
						</div>
						
						<div class="item">
							<div class="name">
								<label for="{#suchspecialoverlayFileName#}">{#suchspecialoverlayFileName#}</label>
							</div>
							<div class="for">
								<input type="file" name="cSuchspecialOverlayBild" maxlength="2097152" accept="image/jpeg,image/gif,image/png,image/bmp" id="cSuchspecialOverlayBild" value="" tabindex="1" />
								<div class="help" ref="2" title="{#suchspecialoverlayFileNameDesc#}"></div>
							</div>
						</div>
						
						<div class="item">
							<div class="name">
								<label for="{#suchspecialoverlayPrio#}">{#suchspecialoverlayPrio#}</label>
							</div>
							<div class="for">
								<select name="nPrio" class="combo">
									<option value="-1"></option>
									{section name=prios loop=$nSuchspecialOverlayAnzahl start=1 step=1}
										<option value="{$smarty.section.prios.index}"{if $smarty.section.prios.index == $oSuchspecialOverlay->nPrio} selected{/if}>{$smarty.section.prios.index}</option>
									{/section}
								</select>
								<div class="help" ref="3" title="{#suchspecialoverlayPrioDesc#}"></div>
							</div>
						</div>
						
						<div class="item">
							<div class="name">
								<label for="{#suchspecialoverlayMargin#}">{#suchspecialoverlayMargin#}</label>
							</div>
							<div class="for">
								<input name="nMargin" type="text" value="{$oSuchspecialOverlay->nMargin}" />
								<div class="help" ref="4" title="{#suchspecialoverlayMarginDesc#}"></div>
							</div>
						</div>
						
						<div class="item">
							<div class="name">
								<label for="{#suchspecialoverlayClarity#}">{#suchspecialoverlayClarity#}</label>
							</div>
							<div class="for">
								<select name="nTransparenz" class="combo">
								{section name=transparenz loop=101 start=0 step=1}
									<option value="{$smarty.section.transparenz.index}"{if $smarty.section.transparenz.index == $oSuchspecialOverlay->nTransparenz} selected{/if}>{$smarty.section.transparenz.index}</option>
								{/section}
								</select>
								<div class="help" ref="5" title="{#suchspecialoverlayClarityDesc#}"></div>
							</div>
						</div>
						
						<div class="item">
							<div class="name">
								<label for="{#suchspecialoverlaySize#}">{#suchspecialoverlaySize#}</label>
							</div>
							<div class="for">
								<input name="nGroesse" type="text" value="{$oSuchspecialOverlay->nGroesse}" />
								<div class="help" ref="5" title="{#suchspecialoverlaySizeDesc#}"></div>
							</div>
						</div>
						
						<div class="item">
							<div class="name">
								<label for="{#suchspecialoverlayPosition#}">{#suchspecialoverlayPosition#}</label>
							</div>
							<div class="for">
								<select name="nPosition" id="nPosition" class="combo"> 
									<option value="1"{if $oSuchspecialOverlay->nPosition == "1"} selected{/if}>oben-links</option>
									<option value="2"{if $oSuchspecialOverlay->nPosition == "2"} selected{/if}>oben</option>
									<option value="3"{if $oSuchspecialOverlay->nPosition == "3"} selected{/if}>oben-rechts</option>
									<option value="4"{if $oSuchspecialOverlay->nPosition == "4"} selected{/if}>rechts</option>
									<option value="5"{if $oSuchspecialOverlay->nPosition == "5"} selected{/if}>unten-rechts</option>
									<option value="6"{if $oSuchspecialOverlay->nPosition == "6"} selected{/if}>unten</option>
									<option value="7"{if $oSuchspecialOverlay->nPosition == "7"} selected{/if}>unten-links</option>
									<option value="8"{if $oSuchspecialOverlay->nPosition == "8"} selected{/if}>links</option>					
									<option value="9"{if $oSuchspecialOverlay->nPosition == "9"} selected{/if}>zentriert</option>
								</select>
								<div class="help" ref="6" title="{#suchspecialoverlayPositionDesc#}"></div>
							</div>
						</div>
						
						<div class="save_wrapper">
							<input type="submit" value="{#save#}" class="button orange" />
						</div>
					
					</div>
					
				</div>
			</form>
		</div>
	{/if}
</div>

{include file='tpl_inc/footer.tpl'}