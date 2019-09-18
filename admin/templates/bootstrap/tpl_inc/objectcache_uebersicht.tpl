{include file='tpl_inc/seite_header.tpl' cTitel=#objectcache# cBeschreibung=#objectcacheDesc# cDokuURL=#objectcacheURL#}

<div id="content" class="container-fluid">
    <div class="tabber">
        <div class="tabbertab{if $tab == 'clearall'} tabbertabdefault{/if}">
            <h2>Cache leeren</h2>

            <p class="box_info">Aktive Methode: {$method}</p>

            <form method="post" action="objectcache.php">
                {$jtl_token}
                <input name="a" type="hidden" value="clearAll" />
                <button name="submit" type="submit" value="Kompletten Cache l&ouml;schen" class="btn btn-primary"><i class="fa fa-trash"></i> Kompletten Cache l&ouml;schen</button>
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
                {$jtl_token}
                <input type="hidden" name="a" value="settings">
                <input name="tab" type="hidden" value="settings">
                <div class="settings">
            {foreach name=conf from=$settings item=setting}
                {if $setting->cConf === 'Y'}
                    <label for="{$setting->cWertName}">{$setting->cName}</label>
                    {if $setting->cBeschreibung}
                        {getHelpDesc cDesc=$setting->cBeschreibung}
                    {/if}
                {if $setting->cInputTyp === 'selectbox'}
                    <select name="{$setting->cWertName}" id="{$setting->cWertName}" class="combo">
                    {foreach name=selectfor from=$setting->ConfWerte item=wert}
                        <option value="{$wert->cWert}" {if $setting->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                    {/foreach}
                    </select>
                {else}
                    {if $setting->cWertName == 'newsletter_smtp_pass'}
                        <input autocomplete="off" type="password" name="{$setting->cWertName}" id="{$setting->cWertName}"  value="{$setting->gesetzterWert}" tabindex="1" />
                    {else}
                        <input type="text" name="{$setting->cWertName}" id="{$setting->cWertName}"  value="{$setting->gesetzterWert}" tabindex="1" />
                    {/if}
                {/if}
                {else}
                    {if $setting->cName}<h3 style="text-align:center;">{$setting->cName}</h3>{/if}
                {/if}
            {/foreach}
                </div>

                <p class="submit"><button name="speichern" type="submit" value="{#save#}" class="btn btn-primary">{#save#}</p>
            </form>
        </div>
    </div>
</div>