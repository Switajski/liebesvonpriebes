{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: exportformat_queue_erstellen.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#exportformatTodaysWork#}
<div id="content">

    {if isset($hinweis) && $hinweis|count_characters > 0}			
        <p class="box_success">{$hinweis}</p>
    {/if}
    {if isset($fehler) && $fehler|count_characters > 0}			
        <p class="box_error">{$fehler}</p>
    {/if}
            
    <form method="POST" action="exportformat_queue.php">
    <input type="hidden" name="{$session_name}" value="{$session_id}">
    <input name="fertiggestellt" type="hidden" value="1">

    
    <div class="block tcenter container">
        {#exportformatLastXHourPre#} <input name="nStunden" type="text"  value="{$nStunden}" style="width: 30px;" /> {#exportformatLastXHourPost#}
        <input name="submitXHour" type="submit" value="{#exportformatShow#}" class="button blue">
    </div>
    
    {if $oExportformatQueueBearbeitet_arr|@count > 0}
    <div id="payment">
        <div id="tabellenLivesuche">
        <table>
            <tr>
                <th class="th-1">{#exportformatFormatSingle#}</th>
                <th class="th-2">{#exportformatFilename#}</th>
                <th class="th-3">{#exportformatOptions#}</th>
                <th class="th-4">{#exportformatExported#}</th>
                <th class="th-5">{#exportformatLastStart#}</th>
            </tr>
        {foreach name=exportformatqueue from=$oExportformatQueueBearbeitet_arr item=oExportformatQueueBearbeitet}
            <tr class="tab_bg{$smarty.foreach.exportformatqueue.iteration%2}">
                <td class="TD1">{$oExportformatQueueBearbeitet->cName}</td>
                <td class="TD2">{$oExportformatQueueBearbeitet->cDateiname}</td>
                <td class="TD3">{$oExportformatQueueBearbeitet->cNameSprache} / {$oExportformatQueueBearbeitet->cNameWaehrung} / {$oExportformatQueueBearbeitet->cNameKundengruppe}</td>
                <td class="TD4">{$oExportformatQueueBearbeitet->nLimitN}</td>
                <td class="TD5">{$oExportformatQueueBearbeitet->dZuletztGelaufen_DE}</td>
            </tr>
        {/foreach}
        </table>
        </div>
    </div>        
    {else}
        <div class="box_info">
            {#exportformatNoTodaysWork#}
        </div>
    {/if}
    </form>    
</div>
