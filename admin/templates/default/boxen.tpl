{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: branding.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: andreas.juetten@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{config_load file="$lang.conf" section="boxen"}
{include file='tpl_inc/header.tpl'}
<script type="text/javascript" src="{$PFAD_CKEDITOR}ckeditor.js"></script>
<script type="text/javascript">
{literal}
function confirmDelete(cName) {
    return confirm('Sind Sie sicher, dass Sie die Box "' + cName + '" löschen möchten?');
}
function onFocus(obj)
{
   obj.id = obj.value;
   obj.value = '';
}

function onBlur(obj)
{
   if (obj.value.length == 0)
      obj.value = obj.id;
}
{/literal}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#boxen# cBeschreibung=#boxenDesc# cDokuURL=#boxenURL#}
<div id="content">
	{if !is_array($oBoxenContainer) || $oBoxenContainer|@count == 0}
		<p class="box_error">{#noTemplateConfig#}</p>
	{elseif !$oBoxenContainer.left && !$oBoxenContainer.right && !$oBoxenContainer.top && !$oBoxenContainer.bottom}
		<p class="box_error">{#noBoxActivated#}</p>
	{else}
		
		{if isset($hinweis) && $hinweis|count_characters > 0}
			<p class="box_success">{$hinweis}</p>
		{/if}
		{if isset($fehler) && $fehler|count_characters > 0}
			<p class="box_error">{$fehler}</p>
		{/if}
		
		{if isset($oEditBox) && $oEditBox}
			<div id="editor" class="editor">
				<form action="boxen.php" method="post">
					<div class="editorInner">
						<h2>{#boxEdit#}</h2>
						<p>
							<label>Titel:</label>
							<input type="text" name="boxtitle" value="{$oEditBox->cTitel}" />
						</p>
						
						<div class="container">
							{if $oEditBox->eTyp == "text"}
								{foreach name="sprachen" from=$oSprachen_arr item=oSprache}
									<h2>Sprache: {$oSprache->cNameDeutsch}</h2>
									<p>
										<label>Titel:</label>
										<input type="text" name="title[{$oSprache->cISO}]" value="{foreach from=$oEditBox->oSprache_arr item=oBoxSprache}{if $oSprache->cISO == $oBoxSprache->cISO}{$oBoxSprache->cTitel}{/if}{/foreach}" />
									</p>
									<p class="container">
										<textarea name="text[{$oSprache->cISO}]" class="ckeditor" rows="15" cols="60">
											{foreach from=$oEditBox->oSprache_arr item=oBoxSprache}{if $oSprache->cISO == $oBoxSprache->cISO}{$oBoxSprache->cInhalt}{/if}{/foreach}
										</textarea>
									</p>
								{/foreach}
							{elseif $oEditBox->eTyp == "catbox"}
								<p>
									<label>Kategoriebox-Nummer:</label>
									<input type="text" name="linkID" value="{$oEditBox->kCustomID}" size="3" /> (listet nur die Kategorien mit dem Wawi-Kategorieattribut "kategoriebox" und der gesetzten Nummer. Standard=0 f&uuml;r alle Kategorien mit oder ohne Funktionsattribut.)
								</p>
								<br />
								{foreach name="sprachen" from=$oSprachen_arr item=oSprache}
									<br />
									<h2>Sprache: {$oSprache->cNameDeutsch}</h2>
									<p>
										<label>Titel:</label>
										<input type="text" name="title[{$oSprache->cISO}]" value="{foreach from=$oEditBox->oSprache_arr item=oBoxSprache}{if $oSprache->cISO == $oBoxSprache->cISO}{$oBoxSprache->cTitel}{/if}{/foreach}" />
									</p>
								{/foreach}
							{elseif $oEditBox->eTyp == "link"}
								<p>
									Linkgruppe: 
									<select name="linkID">
										{foreach from=$oLink_arr item=oLink}
											<option value="{$oLink->kLinkgruppe}" {if $oLink->kLinkgruppe == $oEditBox->kCustomID}selected="selected"{/if}>{$oLink->cName}</option>
										{/foreach}
									</select>
								</p>
							{/if}
							<input type="hidden" name="item" id="editor_id" value="{$oEditBox->kBox}" />
							<input type="hidden" name="action" value="edit" />
							<input type="hidden" name="typ" value="{$oEditBox->eTyp}" />
							<input type="hidden" name="page" value="{$nPage}" />
							<div class="container">
								<input type="submit" value="Speichern" class="button orange" />
								<button type="button" onclick="window.location.href='boxen.php'" class="button orange">Abbrechen</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		{else}
			<div class="block tcenter">
				<form name="boxen" method="post" action="boxen.php">
					<input type="hidden" name="boxen" value="1" />
					<label for="{#page#}"><strong>{#page#}:</strong></label>
					<select name="page" class="selectBox" id="{#page#}" onchange="javascript:document.boxen.submit();">
                  {include file="tpl_inc/seiten_liste.tpl"}
					</select>
				</form>
			</div>
			
			<div class="boxWrapper container clearall">
				{if $oBoxenContainer.left}
					<div class="boxLeft">
						<div class="boxContainer">
							<form action="boxen.php" method="post">
								<div class="boxShow">
									<input type="{if $nPage > 0}checkbox{else}hidden{/if}" name="box_show" id="box_left_show" {if $bBoxenAnzeigen.left}checked="checked"{/if} />
									{if $nPage > 0}
										<p class="text">
											<label for="box_left_show">Container anzeigen</label>
										</p>
									{else}
										<a href="boxen.php?action=container&position=left&item={if isset($oBox->kBox)}{$oBox->kBox}{/if}&value=1"><img src="{$currentTemplateDir}/gfx/layout/eye_plus.png" title="Auf jeder Seite aktivieren"></a>
										<a href="boxen.php?action=container&position=left&item={if isset($oBox->kBox)}{$oBox->kBox}{/if}&value=0"><img src="{$currentTemplateDir}/gfx/layout/eye_minus.png" title="Auf jeder Seite deaktivieren"></a>
										<p class="text">
											Container überall anzeigen
										</p>
									{/if}
								</div>
								{foreach name="box" from=$oBoxenLeft_arr item=oBox}
									<div class="boxRow">
										<div style="float:left">
											{$oBox->cTitel}
										</div>
										<div class="boxOptions">
											<input type="hidden" name="box[]" value="{$oBox->kBox}" />
											<input type="{if $nPage == 0}hidden{else}checkbox{/if}" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
											<input type="text" name="sort[]" value="{$oBox->nSort}" autocomplete="off" id="{$oBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
											{if $nPage == 0}
												<a href="boxen.php?action=activate&position=left&item={$oBox->kBox}&value=1"><img src="{$currentTemplateDir}/gfx/layout/eye_plus.png" title="Auf jeder Seite aktivieren"></a>
												<a href="boxen.php?action=activate&position=left&item={$oBox->kBox}&value=0"><img src="{$currentTemplateDir}/gfx/layout/eye_minus.png" title="Auf jeder Seite deaktivieren"></a>
											{/if}
											{if $oBox->eTyp=="text" || $oBox->eTyp=="link" || $oBox->eTyp=="catbox"}<a href="boxen.php?action=edit_mode&page={$nPage}&position=left&item={$oBox->kBox}"><img src="{$currentTemplateDir}/gfx/layout/edit.png" title="{#edit#}"></a>{/if}
											{if $nPage == 0}
												<a href="boxen.php?action=del&page={$nPage}&position=left&item={$oBox->kBox}" onclick="return confirmDelete('{$oBox->cTitel}');"><img src="{$currentTemplateDir}/gfx/layout/remove.png" title="Aus allen Seiten entfernen"></a>
												<a href="#"><img src="{$currentTemplateDir}/gfx/layout/info.png" title="Sichtbar auf folgenden Seiten: {$oBox->cVisibleOn}"></a>
											{/if}
										</div>
										<div style="clear: both;"></div>
									</div>
								{/foreach}
								<div class="boxSaveRow">
									<input type="hidden" name="position" value="left" />
									<input type="hidden" name="page" value="{$nPage}" />
									<input type="hidden" name="action" value="resort" />
									<input type="submit" value="aktualisieren" class="button blue" />
								</div>
							</form>
							<div class="boxOptionRow">
								<form name="newBoxLeft" action="boxen.php" method="post">
									<label for="newBoxLeft">{#new#}:</label>
									<select id="newBoxLeft" name="item" onchange="javascript:document.newBoxLeft.submit();">
										<option value="0">{#pleaseSelect#}</option>
										{foreach from=$oVorlagen_arr item=oVorlagen}
											<optgroup label="{$oVorlagen->cName}">
												{foreach from=$oVorlagen->oVorlage_arr item=oVorlage}
													<option value="{$oVorlage->kBoxvorlage}">{$oVorlage->cName}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
									<input type="hidden" name="position" value="left" />
									<input type="hidden" name="page" value="{$nPage}" />
									<input type="hidden" name="action" value="new" />
								</form>
							</div>
						</div>
					</div>
				{/if}
					
				<div class="boxCenter {if !$oBoxenContainer.left && !$oBoxenContainer.right}boxCenterMax{elseif !$oBoxenContainer.left || !$oBoxenContainer.right}boxCenterSingle{/if}">
					<div class="boxContainer">
						<form action="boxen.php" method="post">
						{if $oBoxenContainer.top}
							<p class="boxShow">
								<label for="box_top_show"><input type="checkbox" name="box_show" id="box_top_show" {if $bBoxenAnzeigen.top}checked="checked"{/if} /> Container anzeigen</label>
							</p>
							{if $oBoxenTop_arr|@count > 0}
							{foreach name="box" from=$oBoxenTop_arr item=oBox}
								{if $oBox->bContainer}
									<div class="boxRow {if isset($oBox->bGlobal) && $oBox->bGlobal && $nPage != 0}boxGlobal{else}boxRowBaseContainer{/if}">
										<div style="float:left">
											<b>Container #{$oBox->kBox}</b>
										</div>
										<div class="boxOptions">
											{if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
											<input type="hidden" name="box[]" value="{$oBox->kBox}" />
											<input type="checkbox" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
											<input type="text" name="sort[]" value="{$oBox->nSort}" autocomplete="off" id="{$oBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
											{if $oBox->eTyp=="text" || $oBox->eTyp=="link" || $oBox->eTyp=="catbox"}<a href="boxen.php?action=edit_mode&page={$nPage}&position=top&item={$oBox->kBox}"><img src="{$currentTemplateDir}/gfx/layout/edit.png" title="{#edit#}"></a>{/if}
											<a href="boxen.php?action=del&page={$nPage}&position=top&item={$oBox->kBox}" onclick="return confirmDelete('{$oBox->cTitel}');"><img src="{$currentTemplateDir}/gfx/layout/remove.png" title="{#remove#}"></a>
											{else}
											<b>{$oBox->nSort}</b>
											{/if}
										</div>
										<div style="clear: both;"></div>
										
										<div class="boxBlockContainer">
											<!-- container -->
											{assign var='a' value=100}
											{foreach from=$oBox->oContainer_arr item=oContainerBox}
											<div style="width:{$a/$oBox->nContainer}%;float:left">
												<div class="boxRowContainer">
													<div class="boxRow">
														<div style="float:left">
															{$oContainerBox->cTitel}
														</div>
														<div class="boxOptions">
															{if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
															<input type="hidden" name="box[]" value="{$oContainerBox->kBox}" />
															<input type="checkbox" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
															<input type="text" name="sort[]" value="{$oContainerBox->nSort}" autocomplete="off" id="{$oContainerBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
															{if $oContainerBox->cTyp=="text"}<a href="boxen.php?action=edit_mode&page={$nPage}&position=top&item={$oContainerBox->kBox}"><img src="{$currentTemplateDir}/gfx/layout/edit.png" title="{#edit#}"></a>{/if}
															<a href="boxen.php?action=del&page={$nPage}&position=top&item={$oContainerBox->kBox}" onclick="return confirmDelete('{$oBox->cTitel}');"><img src="{$currentTemplateDir}/gfx/layout/remove.png" title="{#remove#}"></a>
															{else}
															<b>{$oContainerBox->nSort}</b>
															{/if}
														</div>
														<div style="clear: both;"></div>
													</div>
												</div>
											</div>
											{/foreach}
											<div style="clear: both;"></div>
											<!-- //container -->
										</div>
										
									</div>
								{else}
									<div class="boxRow {if isset($oBox->bGlobal) && $oBox->bGlobal && $nPage != 0}boxGlobal{/if}">
										<div style="float:left">
											{$oBox->cTitel}
										</div>
										<div class="boxOptions">
											{if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
											<input type="hidden" name="box[]" value="{$oBox->kBox}" />
											<input type="checkbox" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
											<input type="text" name="sort[]" value="{$oBox->nSort}" autocomplete="off" id="{$oBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
											{if $oBox->eTyp=="text" || $oBox->eTyp=="link" || $oBox->eTyp=="catbox"}<a href="boxen.php?action=edit_mode&page={$nPage}&position=top&item={$oBox->kBox}"><img src="{$currentTemplateDir}/gfx/layout/edit.png" title="{#edit#}"></a>{/if}
											<a href="boxen.php?action=del&page={$nPage}&position=top&item={$oBox->kBox}" onclick="return confirmDelete('{$oBox->cTitel}');"><img src="{$currentTemplateDir}/gfx/layout/remove.png" title="{#remove#}"></a>
											{else}
											<b>{$oBox->nSort}</b>
											{/if}
										</div>
										<div style="clear: both;"></div>
									</div>
								{/if}
							{/foreach}
							<div class="boxSaveRow">
								<input type="hidden" name="position" value="top" />
								<input type="hidden" name="page" value="{$nPage}" />
								<input type="hidden" name="action" value="resort" />
								<input type="submit" value="aktualisieren" class="button blue" />
							</div>
							</form>
							{/if}
							<div class="boxOptionRow">
								<form name="newBoxTop" action="boxen.php" method="post">
									<label for="newBoxTop">{#new#}:</label>
									<select id="newBoxTop" name="item">
										<option value="" selected="selected">{#pleaseSelect#}</option>
										<optgroup label="Container">
										<option value="0">{#newContainer#}</option>
										</optgroup>
										{foreach from=$oVorlagen_arr item=oVorlagen}
											<optgroup label="{$oVorlagen->cName}">
												{foreach from=$oVorlagen->oVorlage_arr item=oVorlage}
													<option value="{$oVorlage->kBoxvorlage}">{$oVorlage->cName}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
									
									<label for="containerTop">{#inContainer#}:</label>
									<select id="containerTop" name="container">
										<option value="0">Standard</option>
										{foreach from=$oContainerTop_arr item=oContainerTop}
										<option value="{$oContainerTop->kBox}">Container #{$oContainerTop->kBox}</option>
										{/foreach}
									</select>
									
									<input type="submit" value="einf&uuml;gen" class="button blue" />
	
									<input type="hidden" name="position" value="top" />
									<input type="hidden" name="page" value="{$nPage}" />
									<input type="hidden" name="action" value="new" />
							</div>
						{/if}
						</form>
						
						<div class="boxCenterDisabled">Content-Bereich</div>
						
						{if $oBoxenContainer.bottom}
							<form action="boxen.php" method="post">
							<p class="boxShow">
								<label for="box_bottom_show"><input type="checkbox" name="box_show" id="box_bottom_show" {if $bBoxenAnzeigen.bottom}checked="checked"{/if} /> Container anzeigen</label>
							</p>
							{if $oBoxenBottom_arr|@count > 0}
							{foreach name="box" from=$oBoxenBottom_arr item=oBox}
								{if $oBox->bContainer}
									<div class="boxRow {if isset($oBox->bGlobal) && $oBox->bGlobal && $nPage != 0}boxGlobal{else}boxRowBaseContainer{/if}">
										<div style="float:left">
											<b>Container #{$oBox->kBox}</b>
										</div>
										<div class="boxOptions">
											{if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
											<input type="hidden" name="box[]" value="{$oBox->kBox}" />
											<input type="checkbox" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
											<input type="text" name="sort[]" value="{$oBox->nSort}" autocomplete="off" id="{$oBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
											{if $oBox->eTyp=="text" || $oBox->eTyp=="link" || $oBox->eTyp=="catbox"}<a href="boxen.php?action=edit_mode&page={$nPage}&position=bottom&item={$oBox->kBox}"><img src="{$currentTemplateDir}/gfx/layout/edit.png" title="{#edit#}"></a>{/if}
											<a href="boxen.php?action=del&page={$nPage}&position=bottom&item={$oBox->kBox}" onclick="return confirmDelete('{$oBox->cTitel}');"><img src="{$currentTemplateDir}/gfx/layout/remove.png" title="{#remove#}"></a>
											{else}
											<b>{$oBox->nSort}</b>
											{/if}
										</div>
										<div style="clear: both;"></div>
										
										<div class="boxBlockContainer">
											<!-- container -->
											{assign var='a' value=100}
											{foreach from=$oBox->oContainer_arr item=oContainerBox}
											<div style="width:{$a/$oBox->nContainer}%;float:left">
												<div class="boxRowContainer">
													<div class="boxRow">
														<div style="float:left">
															{$oContainerBox->cTitel}
														</div>
														<div class="boxOptions">
															{if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
															<input type="hidden" name="box[]" value="{$oContainerBox->kBox}" />
															<input type="checkbox" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
															<input type="text" name="sort[]" value="{$oContainerBox->nSort}" autocomplete="off" id="{$oContainerBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
															{if $oContainerBox->cTyp=="text"}<a href="boxen.php?action=edit_mode&page={$nPage}&position=bottom&item={$oContainerBox->kBox}"><img src="{$currentTemplateDir}/gfx/layout/edit.png" title="{#edit#}"></a>{/if}
															<a href="boxen.php?action=del&page={$nPage}&position=bottom&item={$oContainerBox->kBox}" onclick="return confirmDelete('{$oBox->cTitel}');"><img src="{$currentTemplateDir}/gfx/layout/remove.png" title="{#remove#}"></a>
															{else}
															<b>{$oContainerBox->nSort}</b>
															{/if}
														</div>
														<div style="clear: both;"></div>
													</div>
												</div>
											</div>
											{/foreach}
											<div style="clear: both;"></div>
											<!-- //container -->
										</div>
										
									</div>
								{else}
									<div class="boxRow {if isset($oBox->bGlobal) && $oBox->bGlobal && $nPage != 0}boxGlobal{/if}">
										<div style="float:left">
											{$oBox->cTitel}
										</div>
										<div class="boxOptions">
											{if !isset($oBox->bGlobal) || !$oBox->bGlobal || $nPage == 0}
											<input type="hidden" name="box[]" value="{$oBox->kBox}" />
											<input type="checkbox" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
											<input type="text" name="sort[]" value="{$oBox->nSort}" autocomplete="off" id="{$oBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
											{if $oBox->eTyp=="text" || $oBox->eTyp=="link" || $oBox->eTyp=="catbox"}<a href="boxen.php?action=edit_mode&page={$nPage}&position=bottom&item={$oBox->kBox}"><img src="{$currentTemplateDir}/gfx/layout/edit.png" title="{#edit#}"></a>{/if}
											<a href="boxen.php?action=del&page={$nPage}&position=bottom&item={$oBox->kBox}" onclick="return confirmDelete('{$oBox->cTitel}');"><img src="{$currentTemplateDir}/gfx/layout/remove.png" title="{#remove#}"></a>
											{else}
											<b>{$oBox->nSort}</b>
											{/if}
										</div>
										<div style="clear: both;"></div>
									</div>
								{/if}
							{/foreach}
							<div class="boxSaveRow">
								<input type="hidden" name="position" value="bottom" />
								<input type="hidden" name="page" value="{$nPage}" />
								<input type="hidden" name="action" value="resort" />
								<input type="submit" value="aktualisieren" class="button blue" />
							</div>
							{/if}
							</form>
							<div class="boxOptionRow">
								<form name="newBoxBottom" action="boxen.php" method="post">
									<label for="newBoxBottom">{#new#}:</label>
									<select id="newBoxBottom" name="item">
										<option value="" selected="selected">{#pleaseSelect#}</option>
										<optgroup label="Container">
										<option value="0">{#newContainer#}</option>
										</optgroup>
										{foreach from=$oVorlagen_arr item=oVorlagen}
											<optgroup label="{$oVorlagen->cName}">
												{foreach from=$oVorlagen->oVorlage_arr item=oVorlage}
													<option value="{$oVorlage->kBoxvorlage}">{$oVorlage->cName}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
									
									<label for="containerBottom">{#inContainer#}:</label>
									<select id="containerBottom" name="container">
										<option value="0">Standard</option>
										{foreach from=$oContainerBottom_arr item=oContainerBottom}
										<option value="{$oContainerBottom->kBox}">Container #{$oContainerBottom->kBox}</option>
										{/foreach}
									</select>
									
									<input type="submit" value="einf&uuml;gen" class="button blue" />
	
									<input type="hidden" name="position" value="bottom" />
									<input type="hidden" name="page" value="{$nPage}" />
									<input type="hidden" name="action" value="new" />
								</form>
							</div>
						{/if}
					</div>
				</div>
					
				{if $oBoxenContainer.right}
					<div class="boxRight">
						<div class="boxContainer">
							<form action="boxen.php" method="post">
								<div class="boxShow">
									<input type="{if $nPage > 0}checkbox{else}hidden{/if}" name="box_show" id="box_right_show" {if $bBoxenAnzeigen.right}checked="checked"{/if} />
									{if $nPage > 0}
										<p class="text">
											<label for="box_right_show">Container anzeigen</label>
										</p>
									{else}
										<a href="boxen.php?action=container&position=right&item={$oBox->kBox}&value=1"><img src="{$currentTemplateDir}/gfx/layout/eye_plus.png" title="Auf jeder Seite aktivieren"></a>
										<a href="boxen.php?action=container&position=right&item={$oBox->kBox}&value=0"><img src="{$currentTemplateDir}/gfx/layout/eye_minus.png" title="Auf jeder Seite deaktivieren"></a>
										<p class="text">
											Container überall anzeigen
										</p>
									{/if}
								</div>
								{foreach name="box" from=$oBoxenRight_arr item=oBox}
									<div class="boxRow">
										<div style="float:left">
											{$oBox->cTitel}
										</div>
										<div class="boxOptions">
											<input type="hidden" name="box[]" value="{$oBox->kBox}" />
											<input type="{if $nPage == 0}hidden{else}checkbox{/if}" name="aktiv[]" {if $oBox->bAktiv == 1}checked="checked"{/if} value="{$oBox->kBox}" />
											<input type="text" name="sort[]" value="{$oBox->nSort}" autocomplete="off" id="{$oBox->nSort}" onfocus="onFocus(this)" onblur="onBlur(this)" />
											{if $nPage == 0}
												<a href="boxen.php?action=activate&position=right&item={$oBox->kBox}&value=1"><img src="{$currentTemplateDir}/gfx/layout/eye_plus.png" title="Auf jeder Seite aktivieren"></a>
												<a href="boxen.php?action=activate&position=right&item={$oBox->kBox}&value=0"><img src="{$currentTemplateDir}/gfx/layout/eye_minus.png" title="Auf jeder Seite deaktivieren"></a>
											{/if}
											{if $oBox->eTyp=="text" || $oBox->eTyp=="link" || $oBox->eTyp=="catbox"}<a href="boxen.php?action=edit_mode&page={$nPage}&position=right&item={$oBox->kBox}"><img src="{$currentTemplateDir}/gfx/layout/edit.png" title="{#edit#}"></a>{/if}
											{if $nPage == 0}
												<a href="boxen.php?action=del&page={$nPage}&position=right&item={$oBox->kBox}" onclick="return confirmDelete('{$oBox->cTitel}');"><img src="{$currentTemplateDir}/gfx/layout/remove.png" title="Aus allen Seiten entfernen"></a>
												<a href="#"><img src="{$currentTemplateDir}/gfx/layout/info.png" title="Sichtbar auf folgenden Seiten: {$oBox->cVisibleOn}"></a>
											{/if}
										</div>
										<div style="clear: both;"></div>
									</div>
								{/foreach}
								<div class="boxSaveRow">
									<input type="hidden" name="position" value="right" />
									<input type="hidden" name="page" value="{$nPage}" />
									<input type="hidden" name="action" value="resort" />
									<input type="submit" value="aktualisieren" class="button blue" />
								</div>
							</form>
							<div class="boxOptionRow">
								<form name="newBoxRight" action="boxen.php" method="post">
									<label for="newBoxRight">{#new#}:</label>
									<select id="newBoxRight" name="item" onchange="javascript:document.newBoxRight.submit();">
										<option value="0">{#pleaseSelect#}</option>
										{foreach from=$oVorlagen_arr item=oVorlagen}
											<optgroup label="{$oVorlagen->cName}">
												{foreach from=$oVorlagen->oVorlage_arr item=oVorlage}
													<option value="{$oVorlage->kBoxvorlage}">{$oVorlage->cName}</option>
												{/foreach}
											</optgroup>
										{/foreach}
									</select>
									<input type="hidden" name="position" value="right" />
									<input type="hidden" name="page" value="{$nPage}" />
									<input type="hidden" name="action" value="new" />
								</form>
							</div>
						</div>
					</div>
				{/if}
				
				
				
			</div>
		{/if}
	{/if}
</div>
{include file='tpl_inc/footer.tpl'}