<div class="jtlsearch_actioncolumn">
    <div class="jtlsearch_inner">
        <input type="button" name="start_export" id="start_export" value="Export starten" class="button orange" />
        <div id="outputDIV">
            Zum starten des Exports bitte auf den Button "Export starten" klicken.
        </div>
    </div>
    
</div>
<div class="jtlsearch_infocolumn">
    <div class="jtlsearch_inner">
        <table>
            <tr>
                <td>Letzter vollständiger Export: </td>
                <td>{if $oExportStats_arr.lastFinished->cDatum|count_characters > 0}Am {$oExportStats_arr.lastFinished->cDatum} um {$oExportStats_arr.lastFinished->cZeit} Uhr{else}Es wurde noch kein Export vollendet.{/if}</td>
            </tr>
            <tr>
                <td>Aktueller Export: </td>
                <td>{if $oExportStats_arr.current->cDatum|count_characters > 0}Am {$oExportStats_arr.current->cDatum} um {$oExportStats_arr.current->cZeit} Uhr{else}Aktuell läuft kein Export.{/if}</td>
            </tr>
            {*<tr>
                <td rowspan="2">Vsl. nächster Export: </td>
                <td id="jtlsearch_td_next_export">{if $oExportStats_arr.nextStart->cDatum|count_characters > 0}Am {$oExportStats_arr.nextStart->cDatum} um {$oExportStats_arr.nextStart->cZeit} Uhr{else}Es ist kein weiterer Export geplant.{/if}</td>
            </tr>*}
            <tr>
                <td>
                    <a href="#" id="jtlsearch_change_cron" class="button edit">Ändern</a>
                    <div id="jtlsearch_change_dialog" title="Exportzeit ändern">
                        <span id="jtlsearch_change_cron_error" class="error" style="display: none;"></span>
                        Startdatum: <input type="text" class="datepicker" id="jtlsearch_dStart" name="dStart" value="{$oExportStats_arr.nextStart->dStart}" tabindex="2" />
                        <div class="jtlsearch_change_dialog_bottom">
                            <input type="button" id="btn_save_export_cron" class="orange" value="Speichern" />
                            <img id="jtlsearch_change_dialog_loading" style="display: none;" src="{$oPlugin->cAdminmenuPfadURL}verwaltung/modules/export/templates/images/ajax-loader.gif" alt="loading" />
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
<div class="jtlsearch_clear"></div>

<script type="text/javascript">

    var time = new Date();
    
    $(function() {ldelim}
        $('.datepicker').datetimepicker($.datepicker.regional['de']);
    {rdelim});

    $('#jtlsearch_change_dialog').dialog({ldelim}
      autoOpen: false,
      open: function(){ldelim}
         jQuery('#jtlsearch_change_dialog input:first').blur();
         jQuery('#ui-datepicker-div').hide();
         $('.datepicker').datepicker("hide");
      {rdelim}
    {rdelim});
    
    $('#jtlsearch_change_cron').click(function() {ldelim}
        $('#jtlsearch_change_dialog').dialog("open");
        return false;
    {rdelim});
    
    $('#btn_save_export_cron').click(function() {ldelim}
        $('#btn_save_export_cron').hide();
        $('#jtlsearch_change_dialog_loading').show();
        $.ajax({ldelim}
            type: "POST",
            url: "{$URL_SHOP}/index.php",
            data: "jtlsearch_change_cron=1&dStart="+$('#jtlsearch_dStart').val(),
            success: function(cRes) {ldelim}
                var oRes = jQuery.parseJSON(cRes);
                if(oRes.bError == 0) {ldelim}
                    $('#jtlsearch_td_next_export').html("Am "+oRes.cDatum+" um "+oRes.cZeit+" Uhr");
                    $('#jtlsearch_change_cron_error').hide();
                    $('#btn_save_export_cron').show();
                    $('#jtlsearch_change_dialog_loading').hide();
                    $('#jtlsearch_change_dialog').dialog("close");
                {rdelim} else {ldelim}
                    $('#jtlsearch_change_cron_error').html(oRes.cMessage);
                    $('#jtlsearch_change_cron_error').show();
                    $('#btn_save_export_cron').show();
                    $('#jtlsearch_change_dialog_loading').hide();
                {rdelim}
            {rdelim},
            error: function() {ldelim}
                alert('Fehler');
            {rdelim}
            
        {rdelim});
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
                    $('#start_export').show();
                {rdelim}

            {rdelim},
            error: function() {ldelim}
                $('#outputDIV').html('Es ist ein Fehler beim Export aufgetreten.');
                $('#start_export').show();
            {rdelim}
        {rdelim});
    {rdelim}
</script>