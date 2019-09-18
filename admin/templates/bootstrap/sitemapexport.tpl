{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="sitemapExport"}
{include file='tpl_inc/seite_header.tpl' cTitel=#sitemapExport# cBeschreibung=#sitemapExportDesc# cDokuURL=#sitemapExportURL#}
<div id="content" class="container-fluid">
    <div id="confirmModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title">Achtung!</h4>
                </div>
                <div class="modal-body">
                    <p>Wollen Sie wirklich alle Eintr&auml;ge f&uuml;r das ausgew&auml;hlte Jahr l&ouml;schen?</p>
                </div>
                <div class="modal-footer">
                    <div class="btn-group">
                        <button type="button" class="btn btn-info" name="cancel" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;Abbrechen</button>
                        <button id="formSubmit" type="button" class="btn btn-danger" name="delete" ><i class="fa fa-trash"></i>&nbsp;L&ouml;schen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'export'} active{/if}">
            <a data-toggle="tab" role="tab" href="#export">{#sitemapExport#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'downloads'} active{/if}">
            <a data-toggle="tab" role="tab" href="#downloads">{#sitemapDownload#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'report'} active{/if}">
            <a data-toggle="tab" role="tab" href="#report">{#sitemapReport#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#sitemapSettings#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="export" class="tab-pane fade {if !isset($cTab) || $cTab === 'export'} active in{/if}">
            {if isset($errorNoWrite) && $errorNoWrite|strlen > 0}
                <div class="alert alert-danger">{$errorNoWrite}</div>
            {/if}

            <p><input type="text" readonly="readonly" value="{$URL}" class="form-control" /></p>

            <div class="alert alert-info">
                <p>{#searchEngines#}</p>
                <p>{#download#} <a href="{$URL}">{#xml#}</a></p>
            </div>

            <form action="sitemap.php" method="post">
                {$jtl_token}
                <input type="hidden" name="update" value="1" />
                <input type="hidden" name="tab" value="export" />

                <p class="submit">
                    <button type="submit" value="{#sitemapExportSubmit#}" class="btn btn-primary"><i class="fa fa-share"></i> {#sitemapExportSubmit#}</button>
                </p>
            </form>
        </div>
        <div id="downloads" class="tab-pane fade {if isset($cTab) && $cTab === 'downloads'} active in{/if}">
            <div class="panel-heading well well-sm">
                <div class="toolbar well well-sm">
                    <form id="formDeleteSitemapExport" method="post" action="sitemapexport.php" class="form-inline">
                        <div class="form-group">
                            {$jtl_token}
                            <input type="hidden" name="action" value="">
                            <input type="hidden" name="tab" value="downloads">
                            <input type="hidden" name="SitemapDownload_nPage" value="0">
                            <label for="nYear">Jahr</label>
                            <select id="nYear" name="nYear_downloads">
                                {foreach name=oSitemapDownloadYears from=$oSitemapDownloadYears_arr item=oSitemapDownloadYear}
                                    <option value="{$oSitemapDownloadYear->year}"{if isset($nSitemapDownloadYear) && $nSitemapDownloadYear == $oSitemapDownloadYear->year} selected="selected"{/if}>{$oSitemapDownloadYear->year}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="btn-group">
                            <button name="action[year_downloads]" type="submit" value="1" class="btn btn-info"><i class="fa fa-search"></i>&nbsp;Zeigen</button>
                            <button type="button" class="btn btn-danger"
                                    data-form="#formDeleteSitemapExport" data-action="year_downloads_delete" data-target="#confirmModal" data-toggle="modal" data-title="{#sitemapDownload#} l&ouml;schen"><i class="fa fa-trash"></i>&nbsp;L&ouml;schen</button>
                        </div>
                    </form>
                </div>
                {include file='tpl_inc/pagination.tpl' oPagination=$oSitemapDownloadPagination cParam_arr=['tab' => 'downloads', 'nYear_downloads' => {$nSitemapDownloadYear}]}
            </div>
            {if isset($oSitemapDownload_arr) && $oSitemapDownload_arr|@count > 0}
                <div class="panel panel-default">
                    <form name="sitemapdownload" method="post" action="sitemapexport.php">
                        {$jtl_token}
                        <input type="hidden" name="download_edit" value="1" />
                        <input type="hidden" name="tab" value="downloads" />
                        <input type="hidden" name="nYear_downloads" value="{$nSitemapDownloadYear}" />
                        <div id="payment">
                            <div id="tabellenBewertung" class="table-responsive">
                                <table class="table">
                                    <tr>
                                        <th>&nbsp;</th>
                                        <th>{#sitemapName#}</th>
                                        <th>{#sitemapBot#}</th>
                                        <th class="text-right">{#sitemapDate#}</th>
                                    </tr>
                                    {foreach name=sitemapdownloads from=$oSitemapDownload_arr item=oSitemapDownload}
                                        <tr class="tab_bg{$smarty.foreach.sitemapdownloads.iteration%2}">
                                            <td width="20">
                                                <input name="kSitemapTracker[]" type="checkbox" value="{$oSitemapDownload->kSitemapTracker}">
                                            </td>
                                            <td><a href="{Shop::getURL()}/{$oSitemapDownload->cSitemap}" target="_blank">{$oSitemapDownload->cSitemap}</a></td>
                                            <td>
                                                <strong>{#sitemapIP#}</strong>: {$oSitemapDownload->cIP}<br />
                                                {if $oSitemapDownload->cBot|strlen > 0}
                                                    <strong>{#sitemapBot#}</strong>: {$oSitemapDownload->cBot}
                                                {else}
                                                    <strong>{#sitemapUserAgent#}</strong>: <abbr title="{$oSitemapDownload->cUserAgent}">{$oSitemapDownload->cUserAgent|truncate:60}</abbr>
                                                {/if}
                                            </td>
                                            <td class="text-right" width="130">{$oSitemapDownload->dErstellt_DE}</td>
                                        </tr
                                    {/foreach}
                                    <tr>
                                        <td class="TD1">
                                            <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                                        </td>
                                        <td colspan="6" class="TD7"><label for="ALLMSGS">{#sitemapSelectAll#}</label></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="panel-footer">
                                <div class="button-group">
                                    <button class="btn btn-danger" name="loeschen" type="submit" value="{#sitemapDelete#}"><i class="fa fa-trash"></i> {#deleteSelected#}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="report" class="tab-pane fade {if isset($cTab) && $cTab === 'report'} active in{/if}">
            <div class="panel-heading well well-sm">
                <div class="toolbar well well-sm">
                    <form id="formDeleteSitemapReport" method="post" action="sitemapexport.php" class="form-inline">
                        <div class="form-group">
                            {$jtl_token}
                            <input type="hidden" name="action" value="">
                            <input type="hidden" name="tab" value="report">
                            <input type="hidden" name="SitemapReport_nPage" value="0">
                            <label for="nYear">Jahr</label>
                            <select id="nYear" name="nYear_reports">
                                {foreach name=oSitemapReportYears from=$oSitemapReportYears_arr item=oSitemapReportYear}
                                    <option value="{$oSitemapReportYear->year}"{if isset($nSitemapReportYear) && $nSitemapReportYear == $oSitemapReportYear->year} selected="selected"{/if}>{$oSitemapReportYear->year}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div class="btn-group">
                            <button name="action[year_reports]" type="submit" value="1" class="btn btn-info"><i class="fa fa-search"></i>&nbsp;Zeigen</button>
                            <button type="button" class="btn btn-danger"
                                    data-form="#formDeleteSitemapReport" data-action="year_reports_delete" data-target="#confirmModal" data-toggle="modal" data-title="{#sitemapReport#} l&ouml;schen"><i class="fa fa-trash"></i>&nbsp;L&ouml;schen</button>
                        </div>
                    </form>
                </div>
                {include file='tpl_inc/pagination.tpl' oPagination=$oSitemapReportPagination cParam_arr=['tab' => 'report', 'nYear_reports' => {$nSitemapReportYear}]}
            </div>
            {if isset($oSitemapReport_arr) && $oSitemapReport_arr|@count > 0}
                <div class="panel panel-default">
                    <form name="sitemapreport" method="post" action="sitemapexport.php">
                        {$jtl_token}
                        <input type="hidden" name="report_edit" value="1">
                        <input type="hidden" name="tab" value="report">
                        <input type="hidden" name="nYear_reports" value="{$nSitemapReportYear}" />
                        <div class="table-responsive">
                            <table class="table">
                                <tr>
                                    <th class="check"></th>
                                    <th class="th-1"></th>
                                    <th class="tleft">{#sitemapProcessTime#}</th>
                                    <th class="th-3">{#sitemapTotalURL#}</th>
                                    <th class="th-5">{#sitemapDate#}</th>
                                </tr>
                                {foreach name=sitemapreports from=$oSitemapReport_arr item=oSitemapReport}
                                    <tr class="tab_bg{$smarty.foreach.sitemapreports.iteration%2}">
                                        <td class="check">
                                            <input name="kSitemapReport[]" type="checkbox" value="{$oSitemapReport->kSitemapReport}">
                                        </td>
                                        {if isset($oSitemapReport->oSitemapReportFile_arr) && $oSitemapReport->oSitemapReportFile_arr|@count > 0}
                                            <td>
                                                <a href="#" onclick="$('#info_{$oSitemapReport->kSitemapReport}').toggle();return false;"><i class="fa fa-plus-circle"></i></a>
                                            </td>
                                        {else}
                                            <td class="TD1">&nbsp;</td>
                                        {/if}
                                        <td class="tcenter">{$oSitemapReport->fVerarbeitungszeit}s</td>
                                        <td class="tcenter">{$oSitemapReport->nTotalURL}</td>
                                        <td class="tcenter">{$oSitemapReport->dErstellt_DE}</td>
                                    </tr>
                                    {if isset($oSitemapReport->oSitemapReportFile_arr) && $oSitemapReport->oSitemapReportFile_arr|@count > 0}
                                        <tr id="info_{$oSitemapReport->kSitemapReport}" style="display: none;">
                                            <td>&nbsp;</td>
                                            <td colspan="4">

                                                <table border="0" cellspacing="1" cellpadding="0" width="100%">
                                                    <tr>
                                                        <th class="tleft">{#sitemapName#}</th>
                                                        <th class="th-2">{#sitemapCountURL#}</th>
                                                        <th class="th-3">{#sitemapSize#}</th>
                                                    </tr>

                                                    {foreach name=sitemapreportfiles from=$oSitemapReport->oSitemapReportFile_arr item=oSitemapReportFile}
                                                        <tr class="tab_bg{$smarty.foreach.sitemapreports.iteration%2}">
                                                            <td class="TD1">{$oSitemapReportFile->cDatei}</td>
                                                            <td class="tcenter">{$oSitemapReportFile->nAnzahlURL}</td>
                                                            <td class="tcenter">{$oSitemapReportFile->fGroesse} KB</td>
                                                        </tr>
                                                    {/foreach}
                                                </table>

                                            </td>
                                        </tr>
                                    {/if}
                                {/foreach}
                                <tr>
                                    <td class="check">
                                        <input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);">
                                    </td>
                                    <td colspan="4" class="TD5"><label for="ALLMSGS2">{#sitemapSelectAll#}</label></td>
                                </tr>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <div class="button-group">
                                <button name="loeschen" type="submit" value="{#sitemapDelete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#deleteSelected#}</button>
                            </div>
                        </div>
                    </form>
                </div>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' action='sitemapexport.php' buttonCaption=#save# title='Einstellungen' tab='einstellungen'}
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#confirmModal').on('show.bs.modal', function (ev) {
        var $btn = $(ev.relatedTarget);
        var $dlg = $(this);
        $dlg.find('.modal-title').text($btn.data('title'));
        $dlg.find('#formSubmit')
            .data('form', $btn.data('form'))
            .data('action', $btn.data('action'));
    });
    $('#formSubmit').on('click', function (ev) {
        var $form = $($(this).data('form'));
        if ($form.length) {
            $form.find('input[name="action"]').val($(this).data('action'));
            $form.submit();
        }
    });
</script>
{include file='tpl_inc/footer.tpl'}