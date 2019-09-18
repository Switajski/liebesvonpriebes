{*
-------------------------------------------------------------------------------
JTL-Shop 3
File: emailhistory_uebersicht.tpl, smarty template inc file

page for JTL-Shop 3
Admin

Author: daniel.boehmer@jtl-software.de, JTL-Software
http://www.jtl-software.de

Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#objectcache# cBeschreibung=#objectcacheDesc# cDokuURL=#objectcacheURL#}

<div id="content">
     {if isset($notice) && $notice|count_characters > 0}
          <p class="box_success">{$notice}</p>
     {/if}
     {if isset($error) && $error|count_characters > 0}          
          <p class="box_error">{$error}</p>
     {/if}

    <div class="tabber">
        <div class="tabbertab{if $tab == 'clearall'} tabbertabdefault{/if}">
            <h2>Cache leeren</h2>

            <p class="box_info">Aktive Methode: {$method}</p>

            <form method="post" action="objectcache.php">
                <input name="a" type="hidden" value="clearAll" />
                <input name="submit" type="submit" value="Kompletten Cache l&ouml;schen" class="button orange" />
            </form>
        </div>

        <div class="tabbertab{if $tab == 'clearall'} tabbertabdefault{/if}">
            <h2>Cache Stats</h2>

        {if $stat !== null}
            <p class="box_info">Komplette Gr&ouml;&szlig;e: {$stat->getTotalSize()} Bytes ({$stat->getTotalSize(true)} Megabytes)</p>
            <p class="box_info">Komplette Anzahl: {$stat->getTotalCount()}</p>
        {else}
            <p class="box_info">Keine Stats vorhanden</p>
        {/if}

        </div>

        <div class="tabbertab{if $tab == 'settings'} tabbertabdefault{/if}">
            <h2>Einstellungen</h2>

            <form method="post" action="objectcache.php">
                <input type="hidden" name="a" value="settings">
                <input name="tab" type="hidden" value="settings">
                <div class="settings">
            {foreach name=conf from=$settings item=setting}
                {if $setting->cConf == "Y"}
                    <p><label for="{$setting->cWertName}">{$setting->cName} {if $setting->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$setting->cBeschreibung}" title="{$setting->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>
                {/if}
                {if $setting->cInputTyp=="selectbox"}
                    <select name="{$setting->cWertName}" id="{$setting->cWertName}" class="combo"> 
                    {foreach name=selectfor from=$setting->ConfWerte item=wert}
                        <option value="{$wert->cWert}" {if $setting->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                    {/foreach}
                    </select> 
                {else}
                    {if $setting->cWertName == 'newsletter_smtp_pass'}
                        <input type="password" name="{$setting->cWertName}" id="{$setting->cWertName}"  value="{$setting->gesetzterWert}" tabindex="1" /></p>
                    {else}
                        <input type="text" name="{$setting->cWertName}" id="{$setting->cWertName}"  value="{$setting->gesetzterWert}" tabindex="1" /></p>
                    {/if}
                {/if}
                {else}
                    {if $setting->cName}<h3 style="text-align:center;">{$setting->cName}</h3>{/if}
                {/if}
            {/foreach}
                </div>

                <p class="submit"><input name="speichern" type="submit" value="{#save#}" class="button orange" /></p>
            </form>
        </div>
    </div>
</div>