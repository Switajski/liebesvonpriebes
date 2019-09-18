{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='pluginprofiler'}

{include file='tpl_inc/seite_header.tpl' cTitel=#pluginprofiler# cBeschreibung=#pluginprofilerDesc# cDokuURL=#pluginprofilerURL#}
<script type="text/javascript" src="{$shopURL}/{$PFAD_ADMIN}/{$currentTemplateDir}js/profiler.js"></script>
<script type="text/javascript">
var pies = [];
{foreach name=piecharts from=$pluginProfilerData item=pie}
    pies.push({ldelim}categories: {$pie->pieChart->categories}, data: {$pie->pieChart->data}, target: 'profile-pie-chart{$smarty.foreach.piecharts.index}'{rdelim});
{/foreach}
</script>

<div id="content" class="container-fluid">
    <div class="block">
        <form class="delete-run" action="profiler.php" method="post">
            {$jtl_token}
            <input type="hidden" value="y" name="delete-all" />
            <button type="submit" class="btn btn-danger" name="delete-run-submit"><i class="fa fa-trash"></i> Alle Eintr&auml;ge l&ouml;schen</button>
        </form>
    </div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($tab) || $tab === 'plugin' || $tab === 'uebersicht'} active{/if}">
            <a data-toggle="tab" role="tab" href="#plugins">Plugins</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'sqlprofiler'} active{/if}">
            <a data-toggle="tab" role="tab" href="#sqlprofiler">SQL</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="plugins" class="tab-pane fade {if !isset($tab) || $tab === 'massaction' || $tab === 'uebersicht'} active in{/if}">
            {if $pluginProfilerData|@count > 0}
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                    {foreach name=profiles from=$pluginProfilerData item=profile}
                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" data-idx="{$smarty.foreach.profiles.index}" id="heading-profile-{$smarty.foreach.profiles.index}">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#profile-{$smarty.foreach.profiles.index}" aria-expanded="true" aria-controls="profile-{$smarty.foreach.profiles.index}">
                                    <span class="badge left">{$profile->runID}</span> {$profile->url} - {$profile->timestamp} - {$profile->total_time}s
                                </a>
                            </h4>
                        </div>
                        <div id="profile-{$smarty.foreach.profiles.index}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-profile-{$smarty.foreach.profiles.index}">
                            <div class="panel-body">
                                <div id="profile-pie-chart{$smarty.foreach.profiles.index}" class="profiler-pie-chart"></div>
                                <div class="list-group">
                                    {foreach name=files from=$profile->data item=file}
                                        <div class="list-group-item">
                                            <h5 class="list-group-item-heading">{$file->filename}</h5>
                                            <p class="list-group-item-text">
                                                Hook: {$file->hookID}<br />Zeit: {$file->runtime}s<br />Aufrufe: {$file->runcount}
                                            </p>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                            <div class="panel-footer">
                                <form class="delete-run" action="profiler.php" method="post">
                                    {$jtl_token}
                                    <input type="hidden" value="{$profile->runID}" name="run-id" />
                                    <button type="submit" class="btn btn-default" name="delete-run-submit"><i class="fa fa-trash"></i> Eintrag l&ouml;schen</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    {/foreach}
                </div>
            {else}
                <div class="alert alert-info"><i class="fa fa-info-circle"></i> Keine Daten vorhanden.</div>
            {/if}
        </div>
        <div id="sqlprofiler" class="tab-pane fade{if isset($tab) && $tab === 'sqlprofiler'} active in{/if}">
            {if $sqlProfilerData !== null && $sqlProfilerData|@count > 0}
                <div class="panel-group" id="accordion2" role="tablist" aria-multiselectable="true">
                    {foreach name=pd from=$sqlProfilerData item=run}
                        <div class="panel panel-default">
                            <div class="panel-heading" role="tab" data-idx="{$smarty.foreach.pd.index}" id="heading-sql-profile-{$smarty.foreach.pd.index}">
                                <h4 class="panel-title">
                                    <a data-toggle="collapse" data-parent="#accordion2" href="#sql-profile-{$smarty.foreach.pd.index}" aria-expanded="true" aria-controls="profile-{$smarty.foreach.pd.index}">
                                        <span class="badge left">{$run->runID}</span> {$run->url} - {$run->timestamp} - {$run->total_time}s
                                    </a>
                                </h4>
                            </div>
                            <div id="sql-profile-{$smarty.foreach.pd.index}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-sql-profile-{$smarty.foreach.pd.index}">
                                <div class="panel-body">
                                    <p><span class="label2">Total Queries: </span> <span class="text"> {$run->total_count}</span></p>
                                    <p><span class="label2">Runtime: </span> <span class="text"> {$run->total_time}</span></p>
                                    <p><span class="label2">Tables:</span></p>
                                    <ul class="affacted-tables">
                                        {foreach name=ac from=$run->data item=query}
                                            <li class="list a-table">
                                                <strong>{$query->tablename}</strong> ({$query->runcount} times, {$query->runtime}s)<br />
                                                {if $query->statement !== null}
                                                    <strong>Statement:</strong> <code class="sql">{$query->statement}</code><br />
                                                {/if}
                                                {if $query->data !== null}
                                                    {assign var=data value=$query->data|@unserialize}
                                                    <strong>Backtrace:</strong>
                                                    <ol class="backtrace">
                                                        {foreach name=bt from=$data.backtrace item=backtrace}
                                                            <li class="list bt-item">{$backtrace.file}:{$backtrace.line} - {if $backtrace.class !== ''}{$backtrace.class}::{/if}{$backtrace.function}()</li>
                                                        {/foreach}
                                                    </ol>
                                                    {if isset($data.message)}
                                                        <strong>Error message:</strong>
                                                        {$data.message}
                                                    {/if}
                                                {/if}
                                            </li>
                                        {/foreach}
                                    </ul>
                                </div>
                                <div class="panel-footer">
                                    <form class="delete-run" action="profiler.php" method="post">
                                        {$jtl_token}
                                        <input type="hidden" value="{$run->runID}" name="run-id" />
                                        <button type="submit" class="btn btn-default" name="delete-run-submit">Eintrag l&ouml;schen</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    {/foreach}
                </div>
            {else}
                <div class="alert alert-info">Keine Daten vorhanden.</div>
            {/if}
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}