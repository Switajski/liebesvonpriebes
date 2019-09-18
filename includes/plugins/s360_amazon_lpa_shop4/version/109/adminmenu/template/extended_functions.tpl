<script type="text/javascript">
    var s360_lpa_admin_url = '{$oPlugin->cAdminmenuPfadURL}';
</script>
<script type="text/javascript" src="{$oPlugin->cAdminmenuPfadURL}js/admin-extended-functions.js" charset="UTF8"></script>
<link type="text/css" href="{$oPlugin->cAdminmenuPfadURL}css/admin.css" rel="stylesheet" media="screen">
<div id="settings">
    <div id="extended-functions-feedback">Hier k&ouml;nnen Sie erweiterte/automatisierte Funktionen ausf&uuml;hren:</div>

    <button style="margin-top: 20px;margin-bottom: 5px;" id="update-frontend-links-button" class="btn btn-primary"><i class="fa fa-refresh"></i>&nbsp;Frontendlinks aktualisieren</button>
    <div class="well">Aktualisiert automatisiert die Frontendlinks und verschiebt sie ggf. in die Linkgruppe "hidden". Wenn die Linkgruppe "hidden" nicht existiert, wird sie angelegt.<br/><b>Wichtig:</b> F&uuml;hren Sie diese Funktion unbedingt nach der Erstinstallation und <i>nach jedem Update</i> des Plugins aus!</div>

    {if $lpa_backup_writable}
        <button style="margin-top: 20px;margin-bottom: 5px;" id="db-export-button" class="btn btn-danger"><i class="fa fa-upload"></i>&nbsp;DB-Export</button>
        <div class="well">Exportiert die aktuellen Tabellen des Plugins (Zahlungsobjekte und Accountverkn&uuml;pfungen) in den Plugin-Ordner "/frontend/backup".</div>
    {else}
        <button style="margin-top: 20px;margin-bottom: 5px;" class="btn disabled"><i class="fa fa-upload"></i>&nbsp;DB-Export</button>
        <div class="alert alert-danger"><b>Export-Verzeichnis "/frontend/backup" ist nicht beschreibbar. Sie k&ouml;nnen versuchen, dem Ordner manuell die entsprechenden Schreibrechte zu geben, oder stattdessen phpMyAdmin nutzen, um die Plugintabellen zu sichern.</b></div>
    {/if}
    {if $lpa_backup_folders && count($lpa_backup_folders) > 0 && $lpa_backup_readable}
        <div>
            <button style="display: inline-block;margin-top: 20px;margin-bottom: 5px;" id="db-import-button" class="btn btn-danger"><i class="fa fa-download"></i>&nbsp;DB-Import</button>
            <form method="" action="" id="lpa-order-db-form" style="display: inline-block; margin-bottom: 5px; vertical-align:bottom;">
                <select class="form-control" name="lpa_import_path">
                    {foreach item=backupFolder from=$lpa_backup_folders}
                        <option value="{$backupFolder}">{$backupFolder}</option>
                    {/foreach}
                </select>
            </form>
        </div>
        <div class="well">W&auml;hlen Sie die Daten (nach Timestamp) aus, die importiert werden sollen. Der h&ouml;chste Timestamp entspricht den aktuellsten Daten.</div>
    {elseif !$lpa_backup_readable}
        <button style="margin-top: 20px;margin-bottom: 5px;" class="btn disabled"><i class="fa fa-download"></i>&nbsp;DB-Import</button>
        <div class="alert alert-danger" style="color:red;"><b>Import-Verzeichnis "/frontend/backup" ist nicht lesbar. Sie k&ouml;nnen versuchen, dem Ordner manuell die entsprechenden Leserechte zu geben, oder stattdessen phpMyAdmin nutzen, um die Plugintabellen zu importieren.</b></div>
    {else}
        <button style="margin-top: 20px;margin-bottom: 5px;" class="btn disabled"><i class="fa fa-download"></i>&nbsp;DB-Import</button>
        <div class="alert alert-warning">Es sind noch keine exportierten Daten vorhanden, die importiert werden k&ouml;nnten.</div>
    {/if}
    
    {if $lpa_import_old_plugin !== "disabled"}
        <button style="margin-top: 20px;margin-bottom: 5px;" id="db-migrate-button" class="btn btn-danger"><i class="fa fa-download"></i>&nbsp;Import Shop3-Plugin-Daten</button>
        <div class="well">Importiert Plugin-Daten (Bestelldaten, Kundendaten) aus dem alten Plugin. Achtung: Konfigurationsdaten werden nicht übernommen.</div>
        {if $lpa_import_old_plugin === "warning"}
            <div class="alert alert-danger"><b>ACHTUNG: </b>Die Shop4-Plugin-Tabellen enthalten bereits Daten. Diese werden zwar bei einem Import der Shop3-Tabellen nicht gelöscht, es kann dadurch aber ggf. zu Kollisionen kommen, die unvorhersehbare Fehler auslösen.</div>
        {/if}
    {else}
        <button style="margin-top: 20px;margin-bottom: 5px;" class="btn disabled"><i class="fa fa-download"></i>&nbsp;Import Shop3-Plugin-Daten</button>
        <div class="alert alert-info">Es wurden keine Shop3-Plugin-Tabellen gefunden.</div>
    {/if}

    {if isset($lpa_easytemplate_detected) && $lpa_easytemplate_detected}
        <button style="margin-top: 20px;margin-bottom: 5px;" id="easytemplate-config-button" class="btn btn-primary"><i class="fa fa-cog"></i>&nbsp;EasyTemplate-Einstellungen setzen</button>
        <div class="well">Setzt die Einstellungen im Tab "Einstellungen Allgemein", die f&uuml;r das EasyTemplate360 optimiert sind, und speichert diese automatisch ab.</div>
    {/if}
</div>