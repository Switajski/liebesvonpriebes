<div class="jtlsearch_actioncolumn">
    <div class="jtlsearch_inner">
        {if $oResultImportStatus}
            {if $oResultImportStatus->_code == 1 || $oResultImportStatus->_code == 4 || $oResultImportStatus->_code == 5}
                <div id="outputDIV">
                    {if $oResultImportStatus->_code == 1}
                        Es befindet sich bereits ein Export in der Warteschlange auf dem Server.
                    {else}
                        Fehlercode {$oResultImportStatus->_code}: Bitte wenden Sie sich an den Support.
                    {/if}
                </div>
            {else}
                <input type="button" name="start_export" id="start_export" value="Export starten" class="button orange" />
                <div id="outputDIV">
                    Zum starten des Exports bitte auf den Button "Export starten" klicken.
                </div>
            {/if}
        {else}
        <input type="button" name="start_export" id="start_export" value="Export starten" class="button orange" />
        <div id="outputDIV">
            Zum starten des Exports bitte auf den Button "Export starten" klicken.
        </div>
        {/if}
    </div>
    
</div>
<div class="jtlsearch_infocolumn">
    <div class="jtlsearch_inner">
        
        <table>
            <tr>
                <th>Letzte Suchindex Updates</th>
                <th>Updatemethode</th>
                <th>ben�tigte Zeit</th>
            </tr>
            {if $oResultImportHistory->_code == 1 && is_array($oResultImportHistory->oImportHistories)}
                {foreach from=$oResultImportHistory->oImportHistories item=oImportHistory}
                    <tr>
                        <td class="tcenter">{$oImportHistory->dCreated|date_format:"%d.%m.%Y %H:%M:%S"}</td>
                        <td class="tcenter">{if $oImportHistory->nType == 1} Komplett{else}Delta{/if}</td>
                        <td class="tcenter">{$oImportHistory->nTimeNeeded} Sekunden</td>
                    </tr>
                {/foreach}
            {elseif $oResultImportHistory->_code == 2}
            <tr>
                <td colspan="3">Es wurden noch keine erfolgreichen Imports durchgef�hrt.</td>
            </tr>
            {/if}
        </table>
    </div>
</div>
<div class="jtlsearch_clear"></div>

<script type="text/javascript">

    var time = new Date();
    
    $(function() {ldelim}
        $('.datepicker').datetimepicker($.datepicker.regional['de']);
    {rdelim});


    $('#start_export').click(function() {ldelim}
        $('#start_export').hide();
        $('#outputDIV').html('Exportformat wird exportiert.<br />');

        $.ajax({ldelim}
            url: "{$URL_SHOP}/index.php?jtlsearchsetqueue=2&v="+time.getTime(),
            success: function(cRes){ldelim}
                if(cRes == 1) {ldelim}
                sendExportRequest();
                {rdelim}
            {rdelim},
            error: function() {ldelim}
                $('#outputDIV').html('Es ist ein Fehler beim Export aufgetreten.');
                $('#start_export').show();
            {rdelim},
            timeout: 15000
        {rdelim});
    {rdelim});
    
    function sendExportRequest(){ldelim}
        var time = new Date();
        $.ajax({ldelim}
            url: "{$URL_SHOP}/index.php?jtlsearch=true&nExportMethod=2&v="+time.getTime(),
            success: function(cRes){ldelim}
                var oRes = jQuery.parseJSON(cRes);
                if(oRes.nReturnCode == 1) {ldelim}
                    $('#outputDIV').html(oRes.nExported+" von "+oRes.nCountAll+" Items exportiert.<br />");
                    $('#outputDIV').html($('#outputDIV').html()+'<div style="border: 1px solid #000000; margin: 10px auto; width: 230px; height: 20px;"><div style="background-color: #FF0000; height: 100%; width:'+(100/oRes.nCountAll*oRes.nExported)+'%;"></div></div>');
                    sendExportRequest();
                {rdelim} else {ldelim}
                    $('#outputDIV').html(oRes.nExported+" von "+oRes.nCountAll+" Items exportiert.<br /><br />");
                    
                    //Antwort-/Fehler-Codes:
                    // 1 = Alles O.K.
                    // 2 = Authentifikation fehlgeschlagen
                    // 3 = Benutzer wurde nicht gefunden
                    // 4 = Auftrag konnte nicht in die Queue gespeichert werden
                    // 5 = Requester IP stimmt nicht mit der Domain aus der Datenbank ueberein
                    // 6 = Der Shop wurde bereits zum Importieren markiert
                    // 7 = Exception
                    // 8 = Zeitintervall von Full Import zu gering
                    switch (parseInt(oRes.nServerResponse)) {ldelim}
                        case 1:
                        case 6:
                            $('#outputDIV').html($('#outputDIV').html()+"Export wurde erfolgreich in die<br /> Importqueue des Servers geschrieben.");
                            break;
                        case 2:
                            $('#outputDIV').html($('#outputDIV').html()+"Fehler 2: Authentifikation fehlgeschlagen<br />Export wurde NICHT in die<br /> Importqueue des Servers geschrieben.");
                            break;
                        case 3:
                            $('#outputDIV').html($('#outputDIV').html()+"Fehler 3: Testzeitraum abgelaufen oder Usershop wurde nicht gefunden<br />Export wurde NICHT in die<br /> Importqueue des Servers geschrieben.");
                            break;
                        case 4:
                            $('#outputDIV').html($('#outputDIV').html()+"Fehler 4: Auftrag konnte nicht in die Server-Queue gespeichert werden<br />Export wurde NICHT in die<br /> Importqueue des Servers geschrieben.");
                            break;
                        case 5:
                            $('#outputDIV').html($('#outputDIV').html()+"Fehler 5: Requester IP konnte nicht validiert werden.<br />Export wurde NICHT in die<br /> Importqueue des Servers geschrieben.");
                            break;
                        case 7:
                            $('#outputDIV').html($('#outputDIV').html()+"Fehler 7: Unbekannter Server-Fehler<br />Export wurde NICHT in die<br /> Importqueue des Servers geschrieben. Bitte kontaktieren Sie unseren Support.");
                            break;
                        case 8:
                            $('#outputDIV').html($('#outputDIV').html()+"Fehler 8: Sie haben das maximale Limit an Voll-Abgleichen pro Tag erreicht.<br />Export wurde NICHT in die<br /> Importqueue des Servers geschrieben.");
                            break;
                        case 0:
                            $('#outputDIV').html($('#outputDIV').html()+"Export wurde NICHT in die<br /> Importqueue des Servers geschrieben da keine Daten Exportiert wurden.");
                            break;
                        default:
                            $('#outputDIV').html($('#outputDIV').html()+"Unbekannter Server-Fehler<br />Export wurde NICHT in die<br /> Importqueue des Servers geschrieben. Bitte kontaktieren Sie unseren Support.");
                            break;
                    {rdelim}
                {rdelim}

            {rdelim},
            error: function() {ldelim}
                $('#outputDIV').html('Es ist ein Fehler beim Export aufgetreten.');
                $('#start_export').show();
            {rdelim}
        {rdelim});
    {rdelim}
</script>