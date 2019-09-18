<script type="text/javascript">
function ackCheck(kPlugin, hash)
{
    var bCheck = confirm('Wollen Sie das Plugin wirklich updaten?');
    var href = '';

    if (bCheck) {
        href += 'pluginverwaltung.php?pluginverwaltung_uebersicht=1&updaten=1&token={$smarty.session.jtl_token}&kPlugin=' + kPlugin;
        if (hash && hash.length > 0) {
            href += '#' + hash;
        }
        window.location.href = href;
    }
}

{if isset($bReload) && $bReload}
    window.location.href = window.location.href + "?h={$hinweis64}";
{/if}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#pluginverwaltung# cBeschreibung=#pluginverwaltungDesc# cDokuURL=#pluginverwaltungURL#}
<div id="content" class="container-fluid">
    <div id="settings">
        {if $PluginInstalliertByStatus_arr|@count > 0}
            <ul class="nav nav-tabs" role="tablist">
                <li class="tab{if !isset($cTab) || $cTab === 'aktiviert'} active{/if}">
                    <a data-toggle="tab" role="tab" href="#aktiviert">Aktiviert <span class="badge">{$PluginInstalliertByStatus_arr.status_2|@count}</span></a>
                </li>
                <li class="tab{if isset($cTab) && $cTab === 'deaktiviert'} active{/if}">
                    <a data-toggle="tab" role="tab" href="#deaktiviert">Deaktiviert <span class="badge">{$PluginInstalliertByStatus_arr.status_1|@count}</span></a>
                </li>
                <li class="tab{if isset($cTab) && $cTab === 'probleme'} active{/if}">
                    <a data-toggle="tab" role="tab" href="#probleme">Probleme <span class="badge">{$PluginErrorCount}</span></a>
                </li>
                <li class="tab{if isset($cTab) && $cTab === 'verfuegbar'} active{/if}">
                    <a data-toggle="tab" role="tab" href="#verfuegbar">Verf&uuml;gbar <span class="badge">{if isset($PluginVerfuebar_arr)}{$PluginVerfuebar_arr|@count}{else}0{/if}</span></a>
                </li>
                <li class="tab{if isset($cTab) && $cTab === 'fehlerhaft'} active{/if}">
                    <a data-toggle="tab" role="tab" href="#fehlerhaft">Fehlerhaft <span class="badge">{if isset($PluginFehlerhaft_arr)}{$PluginFehlerhaft_arr|@count}{else}0{/if}</span></a>
                </li>
                <li class="tab{if isset($cTab) && $cTab === 'upload'} active{/if}">
                    <a data-toggle="tab" role="tab" href="#upload">Upload</a>
                </li>
                <li class="tab">
                    <a href="marktplatz.php">Marktplatz</a>
                </li>
            </ul>
            <div class="tab-content">
                {include file='tpl_inc/pluginverwaltung_uebersicht_aktiviert.tpl'}
                {include file='tpl_inc/pluginverwaltung_uebersicht_deaktiviert.tpl'}
                {include file='tpl_inc/pluginverwaltung_uebersicht_probleme.tpl'}
                {include file='tpl_inc/pluginverwaltung_uebersicht_verfuegbar.tpl'}
                {include file='tpl_inc/pluginverwaltung_uebersicht_fehlerhaft.tpl'}
                <div class="tab-pane fade" id="upload">
                    <form enctype="multipart/form-data">
                        {$jtl_token}
                        <div class="form-group">
                            <input id="plugin-install-upload" type="file" multiple class="file">
                        </div>
                        <hr>
                    </form>
                    <script>
                        var x = $('#plugin-install-upload').fileinput({ldelim}
                            uploadUrl: '{$shopURL}/{$PFAD_ADMIN}pluginverwaltung.php',
                            allowedFileExtensions : ['zip'],
                            overwriteInitial: false,
                            showPreview: false,
                            language: 'de',
                            maxFileSize: 100000,
                            maxFilesNum: 1
                        {rdelim}).on('fileuploaded', function(event, data, previewId, index) {ldelim}
                            var response = data.response;
                            if (response.status === 'OK') {ldelim}
                                var wasActiveVerfuegbar = $('#verfuegbar').hasClass('active'),
                                    wasActiveFehlerhaft = $('#fehlerhaft').hasClass('active');
                                $('#verfuegbar').replaceWith(response.html.verfuegbar);
                                $('#fehlerhaft').replaceWith(response.html.fehlerhaft);
                                $('a[href="#fehlerhaft"]').find('.badge').html(response.html.fehlerhaft_count);
                                $('a[href="#verfuegbar"]').find('.badge').html(response.html.verfuegbar_count);
                                $('#plugin-upload-success').show().removeClass('hidden');
                                if (wasActiveFehlerhaft) {ldelim}
                                    $('#fehlerhaft').addClass('active in');
                                    {rdelim} else if (wasActiveVerfuegbar) {ldelim}
                                    $('#verfuegbar').addClass('active in');
                                    {rdelim}
                                {rdelim} else {ldelim}
                                    $('#plugin-upload-error').show().removeClass('hidden');
                                {rdelim}
                                var fi = $('#plugin-install-upload');
                                fi.fileinput('reset');
                                fi.fileinput('clear');
                                fi.fileinput('refresh');
                                fi.fileinput('enable');
                        {rdelim});
                    </script>
                    <div id="plugin-upload-success" class="alert alert-info hidden">Plugin erfolgreich hochgeladen.</div>
                    <div id="plugin-upload-error" class="alert alert-danger hidden">Plugin konnte nicht hochgeladen werden.</div>
                </div>
            </div>
        {/if}
    </div>
</div>