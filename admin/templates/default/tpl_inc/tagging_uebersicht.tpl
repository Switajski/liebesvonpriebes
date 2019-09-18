{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: tagging_uebersicht.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#tagging# cBeschreibung=#taggingDesc# cDokuURL=#taggingURL#}
<div id="content">

{if isset($hinweis) && $hinweis|count_characters > 0}
	<p class="box_success">{$hinweis}</p>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}
	<p class="box_error">{$fehler}</p>
{/if}

<div class="container">
<form name="sprache" method="post" action="tagging.php">
<p class="txtCenter">
<label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
    <input type="hidden" name="sprachwechsel" value="1" />
     <select id="{#changeLanguage#}" name="kSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
     {foreach name=sprachen from=$Sprachen item=sprache}
         <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
     {/foreach}
     </select>
     </p>
     </form>
   
    <div class="tabber">
        
        <div class="tabbertab{if isset($cTab) && $cTab == 'freischalten'} tabbertabdefault{/if}">
            
            
            <h2>{#tags#}</h2>
        {if $Tags && $Tags|@count > 0}
            <form name="login" method="post" action="tagging.php">
            <input type="hidden" name="tagging" value="1" />
            <input type="hidden" name="s1" value="{$oBlaetterNaviTags->nAktuelleSeite}" />
            <input type="hidden" name="tab" value="tags" />
            
            {if $oBlaetterNaviTags->nAktiv == 1}
            <div class="container">
                    <p>
                    {$oBlaetterNaviTags->nVon} - {$oBlaetterNaviTags->nBis} {#from#} {$oBlaetterNaviTags->nAnzahl}
                    {if $oBlaetterNaviTags->nAktuelleSeite == 1}
                        &laquo; {#back#}
                    {else}
                        <a href="tagging.php?s1={$oBlaetterNaviTags->nVoherige}&tab=tags">&laquo; {#back#}</a>
                    {/if}
                    
                    {if $oBlaetterNaviTags->nAnfang != 0}<a href="tagging.php?s1={$oBlaetterNaviTags->nAnfang}&tab=tags">{$oBlaetterNaviTags->nAnfang}</a> ... {/if}
                    {foreach name=blaetternavi from=$oBlaetterNaviTags->nBlaetterAnzahl_arr item=Blatt}
                        {if $oBlaetterNaviTags->nAktuelleSeite == $Blatt}[{$Blatt}]
                        {else}
                            <a href="tagging.php?s1={$Blatt}&tab=tags">{$Blatt}</a>
                        {/if}
                    {/foreach}
                    
                    {if $oBlaetterNaviTags->nEnde != 0} ... <a href="tagging.php?s1={$oBlaetterNaviTags->nEnde}&tab=tags">{$oBlaetterNaviTags->nEnde}</a>{/if}
                    
                    {if $oBlaetterNaviTags->nAktuelleSeite == $oBlaetterNaviTags->nSeiten}
                        {#next#} &raquo;
                    {else}
                        <a href="tagging.php?s1={$oBlaetterNaviTags->nNaechste}&tab=tags">{#next#} &raquo;</a>
                    {/if}
                    
                    </p>
            </div>
            {/if}
            
            <div id="payment">
                <div id="tabellenLivesuche">
                <table>
                    <tr>
                        <th class="th-1"></th>
                        <th class="tleft">{#tag#}</th>
                        <th class="th-3">{#tagcount#}</th>
                        <th class="th-4">{#active#}</th>
                        <th class="th-5">{#mapping#}</th>
                    </tr>
                {foreach name=tags from=$Tags item=tag}
                    <input name="kTagAll[]" type="hidden" value="{$tag->kTag}">
                    <tr class="tab_bg{$smarty.foreach.tags.iteration%2}">
                        <td class="TD1"><input type="checkbox" name="kTag[]" value="{$tag->kTag}" /></td>
                        <td class=""><a href="tagging.php?tagdetail=1&kTag={$tag->kTag}&{$session_name}={$session_id}&tab=tags">{$tag->cName}</a></td>
                        <td class="tcenter">{$tag->Anzahl}</td>
                        <td class="tcenter"><input type="checkbox" name="nAktiv[]" value="{$tag->kTag}" {if $tag->nAktiv==1}checked{/if} /></td>
                        <td class="tcenter"><input class="fieldOther" type="text" name="mapping_{$tag->kTag}" /></td>
                    </tr>
                {/foreach}
                </table>
                </div>
            </div>                    
            <p style="text-align:center;"><input name="update" type="submit" value="{#update#}"class="button orange" />
                <input name="delete" type="submit" value="{#delete#}"class="button orange" /></p>
            </form>
            
        {else}
            <br/>{#noDataAvailable#}<br/><br/>
        {/if}
        
        </div>
        
        <div class="tabbertab{if isset($cTab) && $cTab == 'mapping'} tabbertabdefault{/if}">
            
            
            <h2>{#mapping#}</h2>
        
        {if $Tagmapping && $Tagmapping|@count > 0}
            <form name="login" method="post" action="tagging.php">
            <input type="hidden" name="tagging" value="2" />
            <input type="hidden" name="tab" value="mapping">
            <div id="payment">
                <div id="tabellenLivesuche">
                <table>
                    <tr>
                        <th class="th-1"></th>
                        <th class="th-2">{#tag#}</th>
                        <th class="th-3">{#tagnew#}</th>
                    </tr>
                {foreach name=tagsmapping from=$Tagmapping item=tagmapping}
                    <tr class="tab_bg{$smarty.foreach.tagsmapping.iteration%2}">                                
                        <td class="TD1"><input name="kTagMapping[]" type="checkbox" value="{$tagmapping->kTagMapping}" /></td>
                        <td class="tcenter">{$tagmapping->cName}</td>
                        <td class="tcenter">{$tagmapping->cNameNeu}</td>
                    </tr>
                {/foreach}
                </table>
                </div>
            </div>                    
            <p style="text-align:center;"><input name="delete" type="submit" value="{#delete#}" class="button orange" /></p>
            </form>
        
        {else}
            <br/>{#noDataAvailable#}<br/><br/>
        {/if}
        
        </div>
        
        <div class="tabbertab{if isset($cTab) && $cTab == 'einstellungen'} tabbertabdefault{/if}">
                
            
            <h2>{#taggingSettings#}</h2>
            <form name="einstellen" method="post" action="tagging.php">
            <input type="hidden" name="{$session_name}" value="{$session_id}">
            <input type="hidden" name="tagging" value="3">
            <input type="hidden" name="tab" value="einstellungen">
            <div class="settings">
                {foreach name=conf from=$oConfig_arr item=oConfig}
                    {if $oConfig->cConf == "Y"}
                        <p><label for="{$oConfig->cWertName}">({$oConfig->kEinstellungenConf}) {$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>
                    {/if}
                    {if $oConfig->cInputTyp=="selectbox"}
                        <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="combo"> 
                        {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                            <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                        {/foreach}
                        </select>
                    {elseif $oConfig->cInputTyp=="listbox"}
                        <select name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}" multiple="multiple" class="combo" style="width: 250px; height: 150px;"> 
                        {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                            <option value="{$wert->kKundengruppe}" {foreach name=werte from=$oConfig->gesetzterWert item=gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                        {/foreach}
                        </select>
                    {else}
                        <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
                    {/if}
                    {else}
                        {if $oConfig->cName}<div class="category">({$oConfig->kEinstellungenConf}) {$oConfig->cName}</div>{/if}
                    {/if}
                {/foreach}
            </div>
            
            <p class="submit"><input type="submit" value="{#taggingSave#}" class="button orange" /></p>
            </form>    
        </div>
        
    </div>                  
</div>