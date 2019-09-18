{*
-------------------------------------------------------------------------------
JTL-Shop 3
File: zahlungsarten_log.tpl, smarty template inc file

page for JTL-Shop 3 
Admin

Author: daniel.boehmer@jtl-software.de, JTL-Software
http://www.jtl-software.de

Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#paymentmethods# cBeschreibung=#log# cDokuURL=#paymentmethodsURL#}
<div id="content">
	 {if isset($cHinweis) && $cHinweis|count_characters > 0}			
		  <p class="box_success">{$cHinweis}</p>
	 {/if}

{if isset($oLog_arr) && $oLog_arr|@count > 0}
     <div><a href="zahlungsarten.php?a=logreset&kZahlungsart={$kZahlungsart}" class="button reset">{#logReset#}</a></div><br />  
          
	 <table class="list">
        <thead>
            <tr>
                 <th class="tleft">{#logText#}</th>
                 <th class="th-2">{#logLevel#}</th>
                 <th class="th-3">{#logDate#}</th>                 
            </tr>
        </thead>
    
        <tbody>
        {foreach name=log from=$oLog_arr item=oLog}
            <tr class="tab_bg{$smarty.foreach.log.iteration % 2}">
                <td class="TD1">{$oLog->cLog}</td>
                <td class="TD2" style="text-align: center;">{if $oLog->nLevel == 1}<button class="button logError">{#logError#}</button>{elseif $oLog->nLevel == 2}<button class="button logNotice">{#logNotice#}</button>{else}<button class="button logDebug">{#logDebug#}</button>{/if}</td>
                <td class="TD2" style="text-align: center;">{$oLog->dDatum}</td>
            </tr>
        {/foreach}
        </tbody>
	 </table>
     
     <br />
     <div><a href="zahlungsarten.php" class="button">{#pageBack#}</a></div>
{else}
     <p>Keine Logs vorhanden!</p>
     <br />
     <div><a href="zahlungsarten.php" class="button">{#pageBack#}</a></div>
{/if}
</div>