{include file='tpl_inc/seite_header.tpl' cTitel=#exportformats# cBeschreibung=#exportformatsDesc# cDokuURL=#exportformatsURL#}
<div id="content" class="container-fluid">
    <script type="text/javascript" src="{$currentTemplateDir}js/jquery.progressbar.js"></script>
    <script type="text/javascript">
        var url = "{$shopURL}/{$PFAD_ADMIN}exportformate.php",
            token = "{$smarty.session.jtl_token}",
            tpl = "{$shopURL}/{$PFAD_ADMIN}{$currentTemplateDir}gfx/jquery";
        {literal}
        $(function () {
            $('#exportall').click(function () {
                $('.extract_async').trigger('click');
                return false;
            });
        });

        function init_export(id) {
            $.getJSON(url, {token: token, action: 'export', kExportformat: id, ajax: '1'}, function (cb) {
                do_export(cb);
            });
            return false;
        }

        function do_export(cb) {
            if (typeof cb !== 'object') {
                error_export();
            } else if (cb.bFinished) {
                finish_export(cb);
            } else {
                show_export_info(cb);
                $.getJSON(cb.cURL, {token: token, action: 'export', e: cb.kExportqueue, back: 'admin', ajax: '1', max: cb.nMax}, function (cb) {
                    do_export(cb);
                });
            }
        }

        function error_export(cb) {
            alert('Es ist ein Fehler beim Erstellen der Exportdatei aufgetreten');
        }

        function show_export_info(cb) {
            var elem = '#progress' + cb.kExportformat;
            $(elem).find('p').hide();
            $(elem).find('div').fadeIn();
            $(elem).find('div').progressBar(cb.nCurrent, {
                max:          cb.nMax,
                textFormat:   'fraction',
                steps:        cb.bFirst ? 0 : 20,
                stepDuration: cb.bFirst ? 0 : 20,
                boxImage:     tpl + '/progressbar.gif',
                barImage:     {
                    0: tpl + '/progressbg_red.gif',
                    30: tpl + '/progressbg_orange.gif',
                    50: tpl + '/progressbg_yellow.gif',
                    70: tpl + '/progressbg_green.gif'
                }
            });
        }

        function finish_export(cb) {
            var elem = '#progress' + cb.kExportformat;
            $(elem).find('div').fadeOut(250, function () {
                $('#error-msg-' + cb.kExportformat).remove();
                var text  = $(elem).find('p').html(),
                    error = '';
                if (cb.errorMessage.length > 0) {
                    error = '<span class="red" id="error-msg-' + cb.kExportformat + '"><br>' + cb.errorMessage + '</span>';
                }
                $(elem).find('p').html(text).append(error).fadeIn(1000);
            });
        }
        {/literal}
    </script>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Vorhandene Exportformate</h3>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th class="tleft">{#name#}</th>
                    <th class="tleft" style="width:320px">{#filename#}</th>
                    <th class="tcenter">{#language#}</th>
                    <th class="tcenter">{#currency#}</th>
                    <th class="tcenter">{#customerGroup#}</th>
                    <th class="tcenter">{#lastModified#}</th>
                    <th class="tcenter" width="200">{#actions#}</th>
                </tr>
                </thead>
                <tbody>
                {foreach name=exportformate from=$exportformate item=exportformat}
                    {if $exportformat->nSpecial == 0}
                        <tr>
                            <td class="tleft"> {$exportformat->cName}</td>
                            <td class="tleft" id="progress{$exportformat->kExportformat}">
                                <p>{$exportformat->cDateiname}</p>

                                <div></div>
                            </td>
                            <td class="tcenter">{$exportformat->Sprache->cNameDeutsch}</td>
                            <td class="tcenter">{$exportformat->Waehrung->cName}</td>
                            <td class="tcenter">{$exportformat->Kundengruppe->cName}</td>
                            <td class="tcenter">{if !empty($exportformat->dZuletztErstellt) && $exportformat->dZuletztErstellt !== '0000-00-00 00:00:00'}{$exportformat->dZuletztErstellt}{else}-{/if}</td>
                            <td class="tcenter">
                                <form method="post" action="exportformate.php">
                                    {$jtl_token}
                                    <input type="hidden" name="kExportformat" value="{$exportformat->kExportformat}" />
                                    <div class="btn-group">
                                        <button name="action" value="export" class="btn btn-default btn-sm extract notext" title="{#createExportFile#}"><i class="fa fa-plus"></i></button>
                                        {if !$exportformat->bPluginContentExtern}
                                            <a href="#" onclick="return init_export('{$exportformat->kExportformat}');" class="btn btn-primary btn-sm extract_async notext" title="{#createExportFileAsync#}"><i class="fa fa-plus-square"></i></a>
                                        {/if}
                                        <button name="action" value="download" class="btn btn-default btn-sm download notext" title="{#download#}"><i class="fa fa-download"></i></button>
                                        <button name="action" value="edit" class="btn btn-default btn-sm edit notext" title="{#edit#}"><i class="fa fa-edit"></i></button>
                                        <button name="action" value="delete" class="btn btn-danger btn-sm remove notext" title="{#delete#}" onclick="return confirm('Exportformat l&ouml;schen?');"><i class="fa fa-trash"></i></button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    {/if}
                {/foreach}
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <div class="submit-wrap btn-group">
                <a class="btn btn-primary" href="exportformate.php?neuerExport=1&token={$smarty.session.jtl_token}"><i class="fa fa-share"></i> {#newExportformat#}</a>
                <a class="btn btn-default" href="#" id="exportall">Alle exportieren</a>
            </div>
        </div>
    </div>
</div>