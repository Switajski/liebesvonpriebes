{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: auswahlassistent.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file='tpl_inc/header.tpl'}

{config_load file="$lang.conf" section="auswahlassistent"}

<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

<!--
<script type="text/javascript">
{literal}
function MerkmalWerteAA(elem, kSprache)
{
    var kMM_arr = new Array();
    var i = 0;    
    $("#Merkmal option:selected").each(function() {
        kMM_arr[i] = $(this).val();
        i++;
    });
        
    xajax_getMerkmalWerteAA(kMM_arr, kSprache);
}
{/literal}    
</script>
-->

{include file="tpl_inc/seite_header.tpl" cTitel=#auswahlassistent# cBeschreibung=#auswahlassistentDesc# cDokuURL=#auswahlassistentURL#}
<div id="content">
	
{if isset($cHinweis) && $cHinweis|count_characters > 0}
    <p class="box_success">{$cHinweis}</p>
{/if}
{if isset($cFehler) && $cFehler|count_characters > 0}
    <p class="box_error">{$cFehler}</p>
{/if}

{if !isset($noModule) || !$noModule}
    <form name="sprache" method="post" action="auswahlassistent.php">
        <p class="txtCenter">
            <label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
            <input id="{#changeLanguage#}" type="hidden" name="sprachwechsel" value="1">
            <select name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
        {foreach name=sprachen from=$Sprachen item=sprache}
                <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
        {/foreach}
            </select>
        </p>
    </form>
    
    <div class="container">
        
	    <div class="tabber">
				 
                <div class="tabbertab{if isset($cTab) && $cTab == 'uebersicht'} tabbertabdefault{/if}">

                    <h2>{#aaOverview#}</h2>
                    
                  <!-- �bersicht Checkbox -->
                  {if isset($oAuswahlAssistentGruppe_arr) && $oAuswahlAssistentGruppe_arr|@count > 0}

                        <div id="payment">
                             <div id="tabellenLivesuche">
                                  <form name="uebersichtForm" method="POST" action="auswahlassistent.php">
                                  <input type="hidden" name="{$session_name}" value="{$session_id}" />
                                  <input type="hidden" name="tab" value="uebersicht" />
                                  <input type="hidden" name="a" value="delGrp" />

										    <div class="category first">{#aaOverview#}</div>

                                  <table class="list">
													 <thead>
														  <tr>
																 <th class="check">&nbsp;</th>
																 <th class="tleft">{#aaName#}</th>                                                                 
                                                                 <th class="tcenter">{#aaLocation#}</th>
																 <th class="tcenter">{#aaLanguage#}</th>
																 <th class="tcenter">{#aaActive#}</th>
																 <th class="tright">&nbsp;</th>
														  </tr>
													 </thead>
													 <tbody>
                                  {foreach name=auswahlgruppen from=$oAuswahlAssistentGruppe_arr item=oAuswahlAssistentGruppe}
                                        <tr>
                                             <td class="check"><input name="kAuswahlAssistentGruppe_arr[]" type="checkbox" value="{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"></td>
                                             <td class="tleft">{$oAuswahlAssistentGruppe->cName}</td>                                             
                                             <td class="tcenter">
                                             {foreach name=anzeigeort from=$oAuswahlAssistentGruppe->oAuswahlAssistentOrt_arr item=oAuswahlAssistentOrt}
                                                 {$oAuswahlAssistentOrt->cOrt}{if !$smarty.foreach.anzeigeort.last}, {/if}
                                             {/foreach}
                                             </td>
                                             <td class="tcenter">{$oAuswahlAssistentGruppe->cSprache}</td>
                                             <td class="tcenter">{if $oAuswahlAssistentGruppe->nAktiv}<span class="success">{#yes#}</span>{else}<span class="error">{#no#}</span>{/if}</td>
                                             <td class="tright" style="width:250px">
																{if isset($oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr) && $oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr|@count > 0}<a class="button down" id="btn_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}">Fragen anzeigen</a>{/if}
																<a href="auswahlassistent.php?a=editGrp&g={$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}" class="button edit">{#aaEdit#}</a>
															</td>
                                        </tr>
                                  {if isset($oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr) && $oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr|@count > 0}
                                        <tr>
                                             <td class="tleft" colspan="6" id="row_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}" style="display: none;">
                                             
                                                 <table class="list">
                                                    <tr>                                                         
                                                         <th class="tleft">{#aaQuestionName#}</th>
                                                         <th class="tcenter">{#aaMerkmal#}</th>
                                                         <th class="tcenter">{#aaSort#}</th>
                                                         <th class="tcenter">{#aaActive#}</th>
                                                         <th class="tright">&nbsp;</th>
                                                         <th class="check">&nbsp;</th>
                                                    </tr>
                                                {foreach name=auswahlfragen from=$oAuswahlAssistentGruppe->oAuswahlAssistentFrage_arr item=oAuswahlAssistentFrage}    
                                                    <tr class="tab_bg{$smarty.foreach.auswahlfragen.iteration%2}">
                                                         <td class="tleft" align="center">{$oAuswahlAssistentFrage->cFrage}</td>
                                                         <td class="tcenter" align="center">{$oAuswahlAssistentFrage->oMerkmal->cName}</td>
                                                         <td class="tcenter" align="center">{$oAuswahlAssistentFrage->nSort}</td>
                                                         <td class="tcenter" align="center">{if $oAuswahlAssistentFrage->nAktiv}<span class="success">{#yes#}</span>{else}<span class="error">{#no#}</span>{/if}</td>
                                                         <td class="tright" style="width:250px"><a href="auswahlassistent.php?a=editQuest&q={$oAuswahlAssistentFrage->kAuswahlAssistentFrage}" class="button edit">{#aaEdit#}</a></td>
                                                         <td class="tright" style="width:50px"><a href="auswahlassistent.php?a=delQuest&q={$oAuswahlAssistentFrage->kAuswahlAssistentFrage}" class="button remove">{#aaDelete#}</a></td>                                                          
                                                    </tr>
                                                {/foreach}
                                                 </table>
                                                 
                                             </td>
                                        </tr>
                                        <script>
                                            $("#btn_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}").click(function() {ldelim}
                                              $("#row_toggle_{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}").slideToggle("slow", "linear");
                                            {rdelim});
                                        </script>
                                  {/if}
                                  {/foreach}
													 </tbody>
													 <tfoot>
														  <tr>
																<td class="check"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
	                                             <td colspan="5" class="tleft">{#globalSelectAll#}</td>
														  </tr>
													 </tfoot>
                                  </table>
                                  <p class="submit"><input name="aaDelete" type="submit" class="button orange" value="{#aaDelete#}"></p>
                                  </form>
                             </div>
                        </div>
                  {else}
                        <br/>{#noDataAvailable#}<br/><br/>
                  {/if}
                </div>
            
                <div class="tabbertab{if isset($cTab) && $cTab == 'frage'} tabbertabdefault{/if}">

                      <br />
                      <h2>{#aaQuestion#}</h2>
                      
                      <div class="settings">

                        <form method="POST" action="auswahlassistent.php">
                            <input name="speichern" type="hidden" value="1">
                            <input name="kSprache" type="hidden" value="{$smarty.session.kSprache}">
                            <input name="tab" type="hidden" value="frage">
                            <input name="a" type="hidden" value="addQuest">
                        {if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) || (isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0)}
                            <input name="kAuswahlAssistentFrage" type="hidden" value="{if isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0}{$kAuswahlAssistentFrage}{else}{$oFrage->kAuswahlAssistentFrage}{/if}">
                        {/if}
                            <p>
                                <label for="cFrage">{#aaQuestionName#}: {getHelpDesc cDesc="Wie soll die Frage lauten?"}</label>
                                <input name="cFrage" type="text"{if isset($cPlausi_arr.cFrage)} class="fieldfillout"{/if} value="{if isset($cPost_arr.cFrage)}{$cPost_arr.cFrage}{elseif isset($oFrage->cFrage)}{$oFrage->cFrage}{/if}">
                                {if isset($cPlausi_arr.cName)}<font class="fillout">{#FillOut#}</font>{/if}
                            </p>
                            
                            <p>
                                <label for="kAuswahlAssistentGruppe">Gruppe: {getHelpDesc cDesc="In welche Gruppe soll die Frage hinzugef&uuml;gt werden?"}</label>
                                <select name="kAuswahlAssistentGruppe"{if isset($cPlausi_arr.kAuswahlAssistentGruppe)} class="fieldfillout"{/if}>
                                    <option value="-1">{#aaChoose#}</option>
                            {foreach name=gruppen from=$oAuswahlAssistentGruppe_arr item=oAuswahlAssistentGruppe}
                                    <option value="{$oAuswahlAssistentGruppe->kAuswahlAssistentGruppe}"{if isset($oAuswahlAssistentGruppe->kAuswahlAssistentGruppe) && ((isset($cPost_arr.kAuswahlAssistentGruppe) && $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe == $cPost_arr.kAuswahlAssistentGruppe) || (isset($oFrage->kAuswahlAssistentGruppe) && $oAuswahlAssistentGruppe->kAuswahlAssistentGruppe == $oFrage->kAuswahlAssistentGruppe))} selected{/if}>{$oAuswahlAssistentGruppe->cName}</option>
                            {/foreach}
                                </select>
                                {if isset($cPlausi_arr.kAuswahlAssistentGruppe)}<font class="fillout">{#FillOut#}</font>{/if}
                            </p>
                            
                            <p>
                                <label for="kMM">Merkmal: {getHelpDesc cDesc="Welches Merkmal soll die Frage erhalten?"}</label>
                                <select name="kMerkmal"{if isset($cPlausi_arr.kMerkmal)} class="fieldfillout"{/if}>
                                    <option value="-1">{#aaChoose#}</option>
                            {foreach name=merkmale from=$oMerkmal_arr item=oMerkmal}                        
                                    <option value="{$oMerkmal->kMerkmal}"{if (isset($cPost_arr.kMerkmal) && $oMerkmal->kMerkmal == $cPost_arr.kMerkmal) || (isset($oFrage->kMerkmal) && $oMerkmal->kMerkmal == $oFrage->kMerkmal)} selected{/if}>{$oMerkmal->cName}</option>
                            {/foreach}
                                </select>
                                {if isset($cPlausi_arr.kMerkmal) && $cPlausi_arr.kMerkmal == 1}<font class="fillout">{#FillOut#}</font>{/if}
                                {if isset($cPlausi_arr.kMerkmal) && $cPlausi_arr.kMerkmal == 2}<font class="fillout">{#aaMerkmalTaken#}</font>{/if}
                            </p>

                            <p>
                                <label for="nSort">Sortierung: {getHelpDesc cDesc="An welcher Position soll die Frage stehen? (Umso h&ouml;her desto weiter unten, z.b. 3)"}</label>
                                <input name="nSort" type="text"{if isset($cPlausi_arr.nSort)} class="fieldfillout"{/if} value="{if isset($cPost_arr.nSort)}{$cPost_arr.nSort}{elseif isset($oFrage->nSort)}{$oFrage->nSort}{else}1{/if}">
                                {if isset($cPlausi_arr.nSort)}<font class="fillout">{#FillOut#}</font>{/if}
                            </p>

                            <p>
                                <label for="nAktiv">Aktiv: {getHelpDesc cDesc="Soll die Frage aktiviert sein? (Aktivierte Fragen werden angezeigt)"}</label>
                                <select name="nAktiv">
                                    <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oFrage->nAktiv) && $oFrage->nAktiv == 1)} selected{/if}>Ja</option>
                                    <option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oFrage->nAktiv) && $oFrage->nAktiv == 0)} selected{/if}>Nein</option>
                                </select>
                            </p>

                            <p class="submit"><input name="speichernSubmit" type="submit" value="{if (isset($oFrage->kAuswahlAssistentFrage) && $oFrage->kAuswahlAssistentFrage > 0) || (isset($kAuswahlAssistentFrage) && $kAuswahlAssistentFrage > 0)}{#aaEdit#}{else}{#save#}{/if}" class="button orange" /></p>
                        </form>

                    </div>

                </div>
            
                <div class="tabbertab{if isset($cTab) && $cTab == 'gruppe'} tabbertabdefault{/if}">

                      <br />
                      <h2>{#aaGroup#}</h2>
                  
                      <div class="settings">

                        <form method="POST" action="auswahlassistent.php">
                            <input name="kSprache" type="hidden" value="{$smarty.session.kSprache}">
                            <input name="tab" type="hidden" value="gruppe">
                            <input name="a" type="hidden" value="addGrp">
                        {if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) || (isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0)}
                            <input name="kAuswahlAssistentGruppe" type="hidden" value="{if isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0}{$kAuswahlAssistentGruppe}{else}{$oGruppe->kAuswahlAssistentGruppe}{/if}">
                        {/if}
                            <p>
                                <label for="cName">{#aaName#}: {getHelpDesc cDesc="Welchen Namen soll die Gruppe erhalten?"}</label>
                                <input name="cName" type="text"{if isset($cPlausi_arr.cName)} class="fieldfillout"{/if} value="{if isset($cPost_arr.cName)}{$cPost_arr.cName}{elseif isset($oGruppe->cName)}{$oGruppe->cName}{/if}">
                                {if isset($cPlausi_arr.cName)}<font class="fillout">{#FillOut#}</font>{/if}
                            </p>
                            
                            <p>
                                <label for="cBeschreibung">{#aaDesc#}: {getHelpDesc cDesc="Wie soll die Beschreibung lauten?"}</label>
                                <textarea name="cBeschreibung" class="description">{if isset($cPost_arr.cBeschreibung)}{$cPost_arr.cBeschreibung}{elseif isset($oGruppe->cBeschreibung)}{$oGruppe->cBeschreibung}{/if}</textarea>
                            </p>
                            
                            <p>
                                <label for="cKategorie">{#aaKat#}: {getHelpDesc cDesc="In welcher Kategorie soll die Gruppe angezeigt werden?"}</label>
                                <input name="cKategorie" id="assign_categories_list" type="text"{if isset($cPlausi_arr.cOrt)} class="fieldfillout"{/if} value="{if isset($cPost_arr.cKategorie)}{$cPost_arr.cKategorie}{elseif isset($oGruppe->cKategorie)}{$oGruppe->cKategorie}{/if}">
                                <a href="#" class="button edit" id="show_categories_list">Kategorien verwalten</a>
                                <div id="ajax_list_picker" class="categories">{include file="tpl_inc/popup_kategoriesuche.tpl"}</div>
                                {if isset($cPlausi_arr.cOrt)}<font class="fillout">{#FillOut#}</font>{/if}
                                {if isset($cPlausi_arr.cKategorie) && $cPlausi_arr.cKategorie != 3}<font class="fillout">{#aaKatSyntax#}</font>{/if}
                                {if isset($cPlausi_arr.cKategorie) && $cPlausi_arr.cKategorie == 3}<font class="fillout">{#aaKatTaken#}</font>{/if}
                            </p>
                            
                            <p>
                                <label for="kLink_arr">{#aaSpecialSite#}: {getHelpDesc cDesc="Auf welcher Spezialseite soll die Gruppe angezeigt werden? (Mehrfachauswahl und Abwahl mit STRG m&ouml;glich)"}</label>
                                <select id="kLink_arr" name="kLink_arr[]"{if isset($cPlausi_arr.cOrt)} class="fieldfillout"{/if} MULTIPLE>
                            {foreach name="links" from=$oLink_arr item=oLink}
                                    {assign var=bAOSelect value=false}
                                    {if isset($oGruppe->oAuswahlAssistentOrt_arr) && $oGruppe->oAuswahlAssistentOrt_arr|@count > 0}
                                        {foreach name=gruppelinks from=$oGruppe->oAuswahlAssistentOrt_arr item=oAuswahlAssistentOrt}
                                            {if $oLink->kLink == $oAuswahlAssistentOrt->kKey && $oAuswahlAssistentOrt->cKey == $AUSWAHLASSISTENT_ORT_LINK}
                                                {assign var=bAOSelect value=true}
                                            {/if}
                                        {/foreach}
                                    {elseif isset($cPost_arr.kLink_arr) && $cPost_arr.kLink_arr|@count > 0}
                                        {foreach name=gruppelinks from=$cPost_arr.kLink_arr item=kLink}
                                            {if $kLink == $oLink->kLink}
                                                {assign var=bAOSelect value=true}
                                            {/if}
                                        {/foreach}
                                    {/if}
                                    <option value="{$oLink->kLink}"{if $bAOSelect} selected{/if}>{$oLink->cName}</option>
                            {/foreach}
                                </select>
                                {if isset($cPlausi_arr.cOrt)}<font class="fillout">{#FillOut#}</font>{/if}
                                {if isset($cPlausi_arr.kLink_arr)}<font class="fillout">{#aaLinkTaken#}</font>{/if}
                            </p>

                            <p>
                                <label for="nStartseite">{#aaStartSite#}: {getHelpDesc cDesc="Soll die Gruppe auf der Startseite angezeigt werden? (Es darf immer nur eine Gruppe auf der Startseite aktiv sein)"}</label>
                                <select name="nStartseite"{if isset($cPlausi_arr.cOrt)} class="fieldfillout"{/if}>
                                    <option value="0"{if (isset($cPost_arr.nStartseite) && $cPost_arr.nStartseite == 0) || (isset($oGruppe->nStartseite) && $oGruppe->nStartseite == 0)} selected{/if}>Nein</option>
                                    <option value="1"{if (isset($cPost_arr.nStartseite) && $cPost_arr.nStartseite == 1) || (isset($oGruppe->nStartseite) && $oGruppe->nStartseite == 1)} selected{/if}>Ja</option>
                                </select>
                                {if isset($cPlausi_arr.cOrt)}<font class="fillout">{#FillOut#}</font>{/if}
                                {if isset($cPlausi_arr.nStartseite)}<font class="fillout">{#aaStartseiteTaken#}</font>{/if}
                            </p>
                            
                            <p>
                                <label for="nAktiv">{#aaActive#}: {getHelpDesc cDesc="Soll die Checkbox im Frontend aktiv und somit sichtbar sein?"}</label>
                                <select name="nAktiv">
                                    <option value="1"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 1) || (isset($oGruppe->nAktiv) && $oGruppe->nAktiv == 1)} selected{/if}>Ja</option>
                                    <option value="0"{if (isset($cPost_arr.nAktiv) && $cPost_arr.nAktiv == 0) || (isset($oGruppe->nAktiv) && $oGruppe->nAktiv == 0)} selected{/if}>Nein</option>
                                </select>
                            </p>

                            <p class="submit"><input name="speicherGruppe" type="submit" value="{if (isset($oGruppe->kAuswahlAssistentGruppe) && $oGruppe->kAuswahlAssistentGruppe > 0) || (isset($kAuswahlAssistentGruppe) && $kAuswahlAssistentGruppe > 0)}{#aaEdit#}{else}{#save#}{/if}" class="button orange" /></p>
                        </form>

                      </div>
                      
                </div>
            
                <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
				 
					  <br />
					  <h2>{#aaConfig#}</h2>

					  <form name="einstellen" method="post" action="auswahlassistent.php">
						<input type="hidden" name="{$session_name}" value="{$session_id}">
						<input type="hidden" name="a" value="saveSettings">
						<input name="tab" type="hidden" value="einstellungen">
						<div class="settings">
					{foreach name=conf from=$oConfig_arr item=oConfig}
						{if $oConfig->cConf == "Y"}
							<p><label for="{$oConfig->cWertName}">{$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>{/if}
							{if $oConfig->cInputTyp=="selectbox"}
								<select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="combo"> 
								{foreach name=selectfor from=$oConfig->ConfWerte item=wert}
									<option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
								{/foreach}
								</select>
							{else}	
								<input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" />
							{/if}
							</p>
						{else}
							{if $oConfig->cName}<p style="text-align: center;"><strong>{$oConfig->cName}</strong></p>{/if}
						{/if}
					{/foreach}
						</div>
					
						<p class="submit"><input name="speicherSettings" class="button orange" type="submit" value="{#save#}" /></p>
					</form>
					  
				 </div>
            
            </div>
        </div>
{else}
    <p class="box_error">{#noModuleAvailable#}</p>
{/if}
</div>

{include file='tpl_inc/footer.tpl'}