{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: statusemail_uebersicht.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehemr@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#statusemail# cBeschreibung=#statusemailDesc# cDokuURL=#statusemailURL#}
<div id="content">
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
    
    <form name="einstellen" method="post" action="statusemail.php">
        <input type="hidden" name="{$session_name}" value="{$session_id}" />
        <input type="hidden" name="einstellungen" value="1" />       
        
        <div id="settings">
            <div class="category">{#settings#}</div>
            <div class="item">
                <div class="name">
                    <label for="nAktiv">{#statusemailUse#}</label>
                </div>
                <div class="for">
                    <select name="nAktiv" id="nAktiv">
                        <option value="1" {if $oStatusemailEinstellungen->nAktiv == 1}selected{/if}>Ja</option>
                        <option value="0" {if $oStatusemailEinstellungen->nAktiv == 0}selected{/if}>Nein</option>
                    </select>
                    <div class="help" ref="0" title="{#statusemailUseDesc#}"></div>
                </div>
            </div>
        
            <div class="item">
                <div class="name">
                    <label for="cEmail">{#statusemailEmail#}</label>
                </div>
                <div class="for">
                    <input type="text" name="cEmail" id="cEmail" value="{$oStatusemailEinstellungen->cEmail}" tabindex="1" />
                    <div class="help" ref="1" title="{#statusemailEmailDesc#}"></div>
                </div>
            </div>
            
            <div class="item">
                <div class="name">
                    <label for="cIntervall">{#statusemailIntervall#}</label>
                </div>
                <div class="for">
                    <select name="cIntervall_arr[]" id="cIntervall" multiple="multiple" class="multiple"> 
                    {foreach name=intervallmoeglich from=$oStatusemailEinstellungen->cIntervallMoeglich_arr key=key item=cIntervallMoeglich}
                        <option value="{$cIntervallMoeglich}"{foreach name=cintervall from=$oStatusemailEinstellungen->nIntervall_arr item=nIntervall}{if $nIntervall == $cIntervallMoeglich} selected{/if}{/foreach}>{$key}</option>
                    {/foreach}
                    </select>
                    <div class="help" ref="2" title="{#statusemailIntervallDesc#}"></div>
                </div>
            </div>
            
            <div class="item">
                <div class="name">
                    <label for="cInhalt">{#statusemailContent#}</label>
                </div>
                <div class="for">
                    <select name="cInhalt_arr[]" id="cInhalt" multiple="multiple" class="multiple"> 
                    {foreach name=inhaltmoeglich from=$oStatusemailEinstellungen->cInhaltMoeglich_arr key=key item=cInhaltMoeglich}
                        <option value="{$cInhaltMoeglich}"{foreach name=cinhalt from=$oStatusemailEinstellungen->nInhalt_arr item=nInhalt}{if $nInhalt == $cInhaltMoeglich} selected{/if}{/foreach}>{$key}</option>
                    {/foreach}
                    </select>
                    <div class="help" ref="3" title="{#statusemailContentDesc#}"></div>
                </div>
            </div>
        </div>
        <div class="save_wrapper">
            <button type="submit" class="button orange">{#statusemailSave#}</button>
        </div>
    </form>
</div>