{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='systemcheck'}

<script>
{literal}
$(function() {
    $('.table tr[data-href]').each(function(){
        $(this).css('cursor','pointer').hover(
            function(){
                $(this).addClass('active');
            },
            function(){
                $(this).removeClass('active');
            }).click( function(){
                document.location = $(this).attr('data-href');
            }
        );
    });

    $('.grid').masonry({
        itemSelector: '.grid-item',
        columnWidth: '.grid-item',
        percentPosition: true
    });

});
{/literal}
</script>

{function render_item title=null desc=null val=null more=null}
    <tr class="text-vcenter"{if $more} data-href="{$more}"{/if}>
        <td {if !$more}colspan="2"{/if}>
            {if $val}
                <i class="fa fa-check-circle text-success fa-fw" aria-hidden="true"></i>
            {else}
                <i class="fa fa-exclamation-circle text-danger fa-fw" aria-hidden="true"></i>
            {/if}
            <span>{$title}</span>
            {if $desc}<p class="text-muted"></p>{/if}
        </td>
        {if $more}
            <td class="text-right">
                <a href="{$more}" class="btn btn-default btn-xs text-uppercase">Details</a>
            </td>
        {/if}
    </tr>
{/function}

{include file='tpl_inc/systemcheck.tpl'}

<div id="content" class="container-fluid" style="padding-top: 10px;">
    <div class="grid">

        <div class="grid-item">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="heading-body"><h4 class="panel-title">Cache</h4></div>
                    <div class="heading-right">
                        <div class="btn-group btn-group-xs">
                            <button class="btn btn-primary dropdown-toggle text-uppercase" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Details <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="cache.php">System-Cache</a></li>
                                <li><a href="bilderverwaltung.php">Bilder-Cache</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <div class="text-center">
                                {if $status->getObjectCache()->getResultCode() === 1}
                                    {$cacheOptions = $status->getObjectCache()->getOptions()}
                                    <i class="fa fa-check-circle text-four-times text-success"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">Aktiviert</h3>
                                    <span style="color:#c7c7c7">{$cacheOptions.method|ucfirst}</span>
                                {else}
                                    <i class="fa fa-exclamation-circle text-four-times text-info"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">Deaktiviert</h3>
                                    <span style="color:#c7c7c7">System-Cache</span>
                                {/if}

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                {$imageCache = $status->getImageCache()}
                                <i class="fa fa-file-image-o text-four-times text-success"></i>
                                <h3 style="margin-top:10px;margin-bottom:0">{$imageCache->total|number_format}</h3>
                                <span style="color:#c7c7c7">Bilder im Cache</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid-item">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Allgemein</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-hover table-striped table-blank text-x1 last-child">
                        <tbody>
                            {render_item title='Datenbank-Struktur' val=$status->validDatabaseStruct() more='dbcheck.php'}
                            {render_item title='Datei-Struktur' val=$status->validFileStruct() more='filecheck.php'}
                            {render_item title='Verzeichnisrechte' val=$status->validFolderPermissions() more='permissioncheck.php'}
                            {render_item title='Ausstehende Updates' val=!$status->hasPendingUpdates() more='dbupdater.php'}
                            {render_item title='Installationsverzeichnis' val=!$status->hasInstallDir()}
                            {render_item title='Template-Version' val=!$status->hasDifferentTemplateVersion()}
                            {render_item title='Profiler aktiv' val=!$status->hasActiveProfiler() more='profiler.php'}
                            {render_item title='Server' val=$status->hasValidEnvironment() more='systemcheck.php'}
                            {render_item title='Verwaiste Kategorien' val=$status->getOrphanedCategories() more='categorycheck.php'}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid-item">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Subscription</h4>
                </div>
                <div class="panel-body">
                    {if $sub === null}
                        <div class="alert alert-danger alert-sm">
                            <p><i class="fa fa-exclamation-circle"></i> Vor&uuml;bergehend keine Informationen verf&uuml;gbar.</p>
                        </div>
                    {else}
                        <div class="row vertical-align">
                            <div class="col-md-3">
                                <div class="text-center">
                                    {if intval($sub->bUpdate) === 0}
                                        <i class="fa fa-check-circle text-four-times text-success"></i>
                                        <h3 style="margin-top:10px;margin-bottom:0">G&uuml;ltig</h3>
                                    {else}
                                        {if $sub->nDayDiff <= 0}
                                            <i class="fa fa-exclamation-circle text-four-times text-danger"></i>
                                            <h3 style="margin-top:10px;margin-bottom:0">Abgelaufen</h3>
                                        {else}
                                            <i class="fa fa-exclamation-circle text-four-times text-info"></i>
                                            <h3 style="margin-top:10px;margin-bottom:0">L&auml;uft in {$sub->nDayDiff} Tagen ab</h3>
                                        {/if}
                                    {/if}
                                </div>
                            </div>
                            <div class="col-md-9">
                                {if intval($sub->bUpdate) === 0}
                                    <table class="table table-hover table-striped table-blank text-x1 last-child">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted text-right"><strong>Version</strong></td>
                                                <td>{formatVersion value=$sub->oShopversion->nVersion} <span class="label label-default">{$sub->eTyp}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted text-right"><strong>Domain</strong></td>
                                                <td>{$sub->cDomain}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted text-right"><strong>G&uuml;ltig bis</strong></td>
                                                <td>{$sub->dDownloadBis_DE} <span class="text-muted">({$sub->nDayDiff} Tage)</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                {/if}
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </div>

        {$incorrectPaymentMethods = $status->getPaymentMethodsWithError()}
        {if count($incorrectPaymentMethods) > 0}
            <div class="grid-item">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Zahlungsarten</h4>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-info">
                            Folgende Zahlungsarten beinhalten Protokolle mit Status 'Fehler'
                        </div>

                        <table class="table table-condensed table-hover table-striped table-blank last-child">
                            <tbody>
                            {foreach $incorrectPaymentMethods as $s}
                                <tr class="text-vcenter">
                                    <td class="text-left" width="55">
                                        <h4 class="label-wrap"><span class="label label-danger" style="display:inline-block;width:3em">{$s->logCount}</span></h4>
                                    </td>
                                    <td class="text-muted"><strong>{$s->cName}</strong></td>
                                    <td class="text-right">
                                        <a class="btn btn-default btn-xs text-uppercase" href="zahlungsarten.php?a=log&kZahlungsart={$s->kZahlungsart}">Details</a>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        {/if}

        {$shared = $status->getPluginSharedHooks()}
        {if count($shared) > 0}
            <div class="grid-item">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Plugin</h4>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-info">
                            Folgende Plugins benutzen einen identischen Hook.
                        </div>

                        <table class="table table-condensed table-hover table-striped table-blank last-child">
                            <tbody>
                            {foreach $shared as $s}
                                {if count($s) > 1}
                                    <tr>
                                        <td class="text-muted text-right" width="33%"><strong>{$s@key}</strong></td>
                                        <td width="66%">
                                            <ul class="list-unstyled">
                                                {foreach $s as $p}
                                                    <li>{$p->cName}</li>
                                                {/foreach}
                                            </ul>
                                        </td>
                                    </tr>
                                {/if}
                            {/foreach}
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        {/if}

        <div class="grid-item">
            {$tests = $status->getEnvironmentTests()}

            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="heading-body"><h4 class="panel-title">Server</h4></div>
                    <div class="heading-right">
                        <a href="systemcheck.php" class="btn btn-primary btn-xs text-uppercase">Details</a>
                    </div>
                </div>
                <div class="panel-body">
                    {if $tests.recommendations|count > 0}
                        <table class="table table-condensed table-hover table-striped table-blank">
                            <thead>
                            <tr>
                                <th class="col-xs-7">&nbsp;</th>
                                <th class="col-xs-3 text-center">Empfohlener Wert</th>
                                <th class="col-xs-2 text-center">Ihr System</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $tests.recommendations as $test}
                                <tr class="text-vcenter">
                                    <td>
                                        <div class="test-name">
                                            {if $test->getDescription()|@count_characters > 0}
                                                <abbr title="{$test->getDescription()|utf8_decode|escape:'html'}">{$test->getName()|utf8_decode}</abbr>
                                            {else}
                                                {$test->getName()|utf8_decode}
                                            {/if}
                                        </div>
                                    </td>
                                    <td class="text-center">{$test->getRequiredState()}</td>
                                    <td class="text-center">{call test_result test=$test}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    {else}
                        <div class="alert alert-success">
                            <p>Alle Vorraussetzungen wurden erf&uuml;llt</p>
                        </div>
                    {/if}
                    {if isset($phpLT55) && $phpLT55}
                        <div class="alert alert-warning">
                            <p class="small">{#systemcheckPHPLT55#|sprintf:phpversion()}</p>
                        </div>
                    {/if}
                </div>
            </div>
        </div>

    </div>
</div>
