{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: dbupdater_uebersicht.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
<style type="text/css">
    .normal {ldelim}
        height: 15px;
        width: 100px;
        background-color: #FCE5BA;
    {rdelim}

    .filled {ldelim}
        height: 15px;
        background: #FFA600;
    {rdelim}
</style>

<script type="text/javascript">
    function changeButton(elem) {ldelim}
        elem.disabled = true;
        elem.style.visibility = "hidden";
        document.getElementById('updateStatus').innerHTML = "<img src='{$PFAD_GFX}ajax-loader.gif' /><br><strong>Update wird durchgef&uuml;hrt ... bitte warten ...</strong>";
        document.forms['updateForm'].submit();
        {rdelim}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#dbupdater# cBeschreibung=#dbupdaterDesc# cDokuURL=#dbupdaterURL#}
<div id="content">
    {if isset($hinweis) && $hinweis|count_characters > 0}
        <p class="box_success">{$hinweis}</p>

        {if $nShopVersion == 300}
            <div class="box_info">
                <p><strong>Verschlüsselungspasswort:</strong></p>
                <p>{$BLOWFISH_KEY}</p>
                <p><br /></p>
                <p>Bitte sichern Sie Ihren Blowfish-Key und drucken Ihn aus. Dieser ist wichtig, um Ihre Kundendaten im Shop wieder zu entschlüsseln.</p>
            </div>
        {/if}
    {/if}

    {if isset($fehler) && $fehler|count_characters > 0}
        <p class="box_error">{$fehler}</p>
    {/if}

    <div class="container">
        {if $nShopVersion > 0 && $nUpdateVersion > 0}
            <form name="updateForm" method="POST">
                {$jtl_token}
                <input type="hidden" name="{$session_name}" value="{$session_id}" />
                <input type="hidden" name="update" value="1" />

                {*if $nErsterStart == 1}
           <div class="container">
                     <p class="box_plain"><strong>{#updateShopContains#}</strong></p>
                     <p><strong>1.</strong> {#updateShopStep1#}</p>
                     <p><strong>2.</strong> {#updateShopStep2#}</p>
                </div>
                {/if*}

                <div class="container block">
                    <ul class="hlist">
                        <li class="p33 tcenter">{#currentShopVersion#}: <span class="{if $nLiveVersion > $nShopVersion}error{/if}">{$cShopVersion}</span></li>
                        <li class="p33 tcenter">{#currentLiveVersion#}: <strong>{if $nLiveVersion > 0}{$cLiveVersion}{else}-{/if}</strong></li>
                        <li class="p33 tcenter">{#lastUpdate#}: <strong>{$oVersion->dAktualisiert}</strong></li>
                    </ul>
                </div>

                {if $oVersion->nZeileVon > 0 && $oVersion->nZeileBis > 0}
                    <br><br>
                    <table style="width: 270px;">
                        <tr>
                            <td style="width: 80px;">{$oVersion->nZeileVon} ({$fDivBreite}%)</td>
                            <td style="width: 100px;">
                                <div class="normal">
                                    <div style="width: {$fDivBreite|round}px;" class="filled"></div>
                                </div>
                            </td>
                            <td style="width: 90px;">{$oVersion->nZeileBis} (100%)</td>
                        </tr>
                    </table>

                    <div class="box_info">
                        Bitte klicken Sie auf Fortfahren, bis der Balken bei 100% ankommt und Sie eine Best&auml;tigung erhalten.<br>
                        Der Button zum Fortfahren wird pro Durchlauf verschwinden, um ein doppeltes Ausf&uuml;hren zu vermeiden.<br>
                        Die Ausf&uuml;hrung eines Schritts kann unter Umst&auml;nden ein paar Minuten dauern. Bitte schlie&szlig;en Sie in dieser Zeit nicht den Browser oder wechseln die Seite.
                    </div>

                    <div id="updateStatus"></div>
                    <p class="submit"><input id="updateNow" type="button" value="{#nextStep#}" onclick="javascript:changeButton(this);" class="button orange" /></p>
                {elseif $oVersion->nTyp > 1}
                    <div class="box_info">
                        Bitte klicken Sie auf Fortfahren.<br>
                        Der Button zum Fortfahren wird pro Durchlauf verschwinden, um ein doppeltes Ausf&uuml;hren zu vermeiden.<br>
                        Die Ausf&uuml;hrung eines Schritts kann unter Umst&auml;nden ein paar Minuten dauern. Bitte schlie&szlig;en Sie in dieser Zeit nicht den Browser oder wechseln die Seite.
                    </div>

                    <div class="container" id="updateStatus"></div>
                    <p class="submit"><input id="nextStep" type="button" value="{#nextStep#}" onclick="javascript:changeButton(this);" class="button orange" /></p>
                {else}
                    <div class="box_info">
                        Um das Update zu starten, klicken Sie bitte den Button 'Update starten'.<br>
                        Die Ausf&uuml;hrung eines Schritts kann unter Umst&auml;nden ein paar Minuten dauern. Bitte schlie&szlig;en Sie in dieser Zeit nicht den Browser oder wechseln die Seite.
                    </div>

                    <div class="container" id="updateStatus"></div>
                    {if $cVersionsHinweis}
                        <p class="box_error importantNote">{$cVersionsHinweis}</p>
                    {/if}
                    <p class="submit"><input id="updateNow" type="button" value="{#updateNow#}" onclick="javascript:changeButton(this);" class="button orange" /></p>
                {/if}
            </form>
        {else}
            <div id="content">
                <div class="container block">
                    <ul class="hlist">
                        <li class="p33 tcenter">{#currentShopVersion#}: <span class="{if $nShopVersion > $oVersion->nVersion}value error{else}value{/if}">{$cShopVersion}</span></li>
                        <li class="p33 tcenter">{#currentLiveVersion#}: <strong>{if $nLiveVersion > 0}{$cLiveVersion}{else}-{/if}</strong></li>
                        <li class="p33 tcenter">{#lastUpdate#}: <strong>{$oVersion->dAktualisiert}</strong></li>
                    </ul>
                </div>

                {if $bUpdateError=="1"}
                    <p class="box_error">{#updateFileError#}</p>
                {/if}

                {if $mysqlError}
                    <p class="box_info">{#updateDBError#} "{$mysqlError}" ({$mysqlErrorRow})</p>
                {/if}

                {if ($Version->nVersion > $Version->nVersionDB) || $mysqlError}
                    {if $cVersionsHinweis}
                        <p class="box_error importantNote">{$cVersionsHinweis}</p>
                    {/if}

                    <form method="post">
                        {$jtl_token}
                        <input type="hidden" name="shopupdate" value="1" />
                        <input type="submit" value="{#updateDB#}" class="button orange" />
                    </form>
                {/if}
            </div>
        {/if}

    </div>
</div>
