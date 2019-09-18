{include file='tpl_inc/seite_header.tpl' cTitel=#cache# cBeschreibung=#objectcacheDesc# cDokuURL=#cacheURL#}
    <script type="text/javascript">
        var disabledMethods = {$non_available_methods};
        {literal}
        jQuery(document).ready(function ($) {
            var elem,
                methods = $('#caching_method option');
            if (methods) {
                methods.each(function () {
                    elem = $(this);
                    if (disabledMethods.indexOf(elem.val()) >= 0) {
                        elem.attr('disabled', 'disabled');
                    }
                });
            }
            $('#massaction-main-switch').click(function () {
                var checkboxes = $('.massaction-checkbox'),
                    checked = $(this).prop('checked');
                checkboxes.prop('checked', checked);
            });

            $('#btn_toggle_cache').click(function () {
                $("#row_toggle_cache").slideToggle('slow', 'linear');
            });
        });
    </script>
{/literal}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($tab) || $tab === 'massaction' || $tab === 'uebersicht'} active{/if}">
            <a data-toggle="tab" role="tab" href="#massaction">{#management#}</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'stats'} active{/if}">
            <a data-toggle="tab" role="tab" href="#stats">{#stats#}</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'benchmark'} active{/if}">
            <a data-toggle="tab" role="tab" href="#benchmark">{#benchmark#}</a>
        </li>
        <li class="tab{if isset($tab) && $tab === 'settings'} active{/if}">
            <a data-toggle="tab" role="tab" href="#settings">{#settings#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="massaction" class="tab-pane fade {if !isset($tab) || $tab === 'massaction' || $tab === 'uebersicht'} active in{/if}">
            <form method="post" action="cache.php">
                {$jtl_token}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#management#}</h3>
                    </div>
                    <div class="table-responsive">
                    <table id="cache-type-status" class="table list">
                        <thead>
                        <tr>
                            <th class="tleft">
                                <input type="checkbox" class="massaction-checkbox" id="massaction-main-switch" />
                            </th>
                            <th class="tleft"><label style="margin-bottom:0;" for="massaction-main-switch">{#type#}</label></th>
                            <th class="tleft">{#description#}</th>
                            <th class="tleft">{#status#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name=cgfe from=$caching_groups item=cg}
                            <tr class="{if ($smarty.foreach.cgfe.index % 2) === 0}even{else}odd{/if}">
                                <td>
                                    <input type="checkbox" class="massaction-checkbox" value="{$cg.value}" name="cache-types[]" id="group-cb-{$smarty.foreach.cgfe.index}">
                                </td>
                                <td>
                                    {assign var=nicename value=$cg.nicename}
                                    <label for="group-cb-{$smarty.foreach.cgfe.index}">{$smarty.config.$nicename}</label>
                                </td>
                                <td>
                                    {assign var=description value=$cg.description}
                                    {$smarty.config.$description}
                                </td>
                                <td>
                                    <h4 class="label-wrap">
                                        {if $cache_enabled === false || $cg.value|in_array:$disabled_caches}
                                            <span class="label label-danger inactive">{#inactive#}</span>
                                        {else}
                                            <span class="label label-success active">{#active#}</span>
                                        {/if}
                                    </h4>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                    </div>
                    <div class="panel-footer">
                        <div class="input-container" style="max-width: 50%;">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="cache-action">Aktion</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select class="form-control" name="cache-action" id="cache-action">
                                        <option name="flush" value="flush">{#empty#}</option>
                                        <option name="deaktivieren" value="deactivate">{#deactivate#}</option>
                                        <option name="aktivieren" value="activate">{#activate#}</option>
                                    </select>
                                </span>
                                <span class="input-group-btn">
                                    <button type="submit" value="{#submit#}" class="btn btn-primary">{#submit#}</button>
                                </span>
                            </div>
                            <input name="a" type="hidden" value="cacheMassAction" />
                        </div>
                        <div class="input-container">
                            <form method="post" action="cache.php" class="submit-form">
                                {$jtl_token}
                                <span class="submit_wrap btn-group">
                                    <button name="a" type="submit" value="flush_object_cache" class="btn btn-default delete"{if !$cache_enabled} disabled="disabled"{/if}><i class="fa fa-trash"></i>&nbsp;{#clearObjectCache#}</button>
                                    <button name="a" type="submit" value="flush_template_cache" class="btn btn-default delete"><i class="fa fa-trash"></i>&nbsp;{#clearTemplateCache#}</button>
                                </span>
                            </form>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </form>
        </div>
        <div id="stats" class="tab-pane fade {if isset($tab) && $tab === 'stats'} active in{/if}">
            {if is_array($stats) && $stats|@count > 0}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#objectcache#}</h3>
                    </div>
                    <table class="table">
                        {if isset($stats.uptime_h) && $stats.uptime_h !== null}
                            <tr class="cache-row">
                                <td>Uptime:</td>
                                <td>{$stats.uptime_h}</td>
                            </tr>
                        {/if}
                        {if isset($stats.mem) && $stats.mem !== null}
                            <tr class="cache-row">
                                <td>Komplette Gr&ouml;&szlig;e:</td>
                                <td>{$stats.mem} Bytes ({($stats.mem/1024/1024)|string_format:"%.2f"} MB)</td>
                            </tr>
                        {/if}
                        {if isset($stats.entries) && $stats.entries !== null}
                            <tr class="cache-row">
                                <td>Anzahl Eintr&auml;ge:</td>
                                <td>{$stats.entries}</td>
                            </tr>
                        {/if}
                        {if isset($stats.misses) && $stats.misses !== null}
                            <tr class="cache-row">
                                <td>Misses:</td>
                                <td>{$stats.misses}
                                    {if isset($stats.mps) && $stats.mps !== null}
                                        <span class="inline"> ({$stats.mps|string_format:"%.2f"} Misses/s)</span>
                                    {/if}
                                </td>
                            </tr>
                        {/if}
                        {if isset($stats.hits) && $stats.hits !== null}
                            <tr class="cache-row">
                                <td>Hits:</td>
                                <td>{$stats.hits}
                                    {if isset($stats.hps) && $stats.hps !== null}
                                        <span class="inline"> ({$stats.hps|string_format:"%.2f"} Hits/s)</span>
                                    {/if}
                                </td>
                            </tr>
                        {/if}
                        {if isset($stats.inserts) && $stats.inserts !== null}
                            <tr class="cache-row">
                                <td>Inserts:</td>
                                <td>{$stats.inserts}</td>
                            </tr>
                        {/if}
                    </table>
                </div>
                {if isset($stats.slow) && is_array($stats.slow)}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Slowlog</h3>
                        </div>
                        {if $stats.slow|@count > 0}
                            <table class="table">
                                {foreach name=slowlog from=$stats.slow key=type item=slow}
                                    <tr>
                                        <td>{$slow.date}</td>
                                        <td>{$slow.cmd} ({$slow.exec_time}s)</td>
                                    </tr>
                                {/foreach}
                            </table>
                        {else}
                            <div class="panel-body">
                                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                            </div>
                        {/if}
                    </div>
                {/if}
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
            {if $opcache_stats !== null}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">OpCache</h3>
                    </div>
                    <table class="table cache-stats" id="opcache-stats">
                        <tr class="cache-row">
                            <td>Aktiviert:</td>
                            <td class="value">{if $opcache_stats->enabled === true}ja{else}nein{/if}</td>
                        </tr>
                        <tr class="cache-row">
                            <td>Belegter Speicher:</td>
                            <td class="value">{$opcache_stats->memoryUsed} MB</td>
                        </tr>
                        <tr class="cache-row">
                            <td>Freier Speicher:</td>
                            <td class="value">{$opcache_stats->memoryFree} MB</td>
                        </tr>
                        <tr class="cache-row">
                            <td>Anzahl Skripte im Cache:</td>
                            <td class="value">{$opcache_stats->numberScrips}</td>
                        </tr>
                        <tr class="cache-row">
                            <td>Anzahl Keys im Cache:</td>
                            <td class="value">{$opcache_stats->numberKeys}</td>
                        </tr>
                        <tr class="cache-row">
                            <td>Hits:</td>
                            <td class="value">{$opcache_stats->hits}</td>
                        </tr>
                        <tr class="cache-row">
                            <td>Misses:</td>
                            <td class="value">{$opcache_stats->misses}</td>
                        </tr>
                        <tr class="cache-row collapsed clickable" data-toggle="collapse" data-target="#hitRateDetail" style="cursor: pointer">
                            <td>Hit-Rate:</td>
                            <td class="value">{$opcache_stats->hitRate}%&nbsp;<i class="fa fa-info-circle right"></i></td>
                        </tr>
                        <tr class="cache-row">
                            <td colspan="2" style="padding: 0">
                                <div id="hitRateDetail" class="panel-collapse collapse">
                                    <table class="table cache-stats">
                                        {foreach name=scripts from=$opcache_stats->scripts item=script}
                                            <tr class="cache-row">
                                                <td class="file-path">{$script.full_path}</td>
                                                <td class="value">{$script.hits} Hits</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            {/if}
            {if $tplcacheStats !== null}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Template Cache</h3>
                    </div>
                    <table class="table cache-stats" id="tplcache-stats">
                        <tr class="cache-row collapsed clickable" data-toggle="collapse" data-target="#cachefilesFrontendDetail" style="cursor: pointer">
                            <td>Dateien Frontend:</td>
                            <td class="value">{$tplcacheStats->frontend|count}&nbsp;<i class="fa fa-info-circle right"></i></td>
                        </tr>
                        {if $tplcacheStats->frontend|count > 0}
                        <tr class="cache-row">
                            <td colspan="2" style="padding: 0">
                                <div id="cachefilesFrontendDetail" class="panel-collapse collapse">
                                    <table class="table cache-stats">
                                        {foreach name=frontend from=$tplcacheStats->frontend item=file}
                                            <tr class="cache-row">
                                                <td class="file-path">{$file->fullname}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                            </td>
                        </tr>
                        {/if}
                        <tr class="cache-row collapsed clickable" data-toggle="collapse" data-target="#cachefilesBackendDetail" style="cursor: pointer">
                            <td>Dateien Backend:</td>
                            <td class="value">{$tplcacheStats->backend|count}&nbsp;<i class="fa fa-info-circle right"></i></td>
                        </tr>
                        {if $tplcacheStats->backend|count > 0}
                        <tr class="cache-row">
                            <td colspan="2" style="padding: 0">
                                <div id="cachefilesBackendDetail" class="panel-collapse collapse">
                                    <table class="table cache-stats">
                                        {foreach name=backend from=$tplcacheStats->backend item=file}
                                            <tr class="cache-row">
                                                <td class="file-path">{$file->fullname}</td>
                                            </tr>
                                        {/foreach}
                                    </table>
                                </div>
                            </td>
                        </tr>
                        {/if}
                    </table>
                </div>
            {/if}
        </div>
        <div id="benchmark" class="tab-pane fade {if isset($tab) && $tab === 'benchmark'} active in{/if}">
            {if !empty($all_methods) && $all_methods|@count > 0}
                <div class="panel panel-default settings">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#config#}</h3>
                    </div>
                    <form method="post" action="cache.php">
                        {$jtl_token}
                        <div class="panel-body">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="runcount">Durchl&auml;ufe</label>
                                </span>
                                <input class="form-control" type="number" name="runcount" id="runcount" value="{if isset($smarty.post.runcount) && is_numeric($smarty.post.runcount)}{$smarty.post.runcount}{else}1000{/if}" size="5" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="repeat">Wiederholungen</label>
                                </span>
                                <input class="form-control" type="number" name="repeat" id="repeat" value="{if isset($smarty.post.repeat) && is_numeric($smarty.post.repeat)}{$smarty.post.repeat}{else}1{/if}" size="5" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="testdata">Testdaten</label>
                                </span>
                                <span class="input-group-wrap">
                                    <select class="form-control" name="testdata" id="testdata">
                                        <option value="array"{if isset($smarty.post.testdata) && $smarty.post.testdata === 'array'} selected{/if}>Array</option>
                                        <option value="object"{if isset($smarty.post.testdata) && $smarty.post.testdata === 'object'} selected{/if}>Objekt</option>
                                        <option value="string"{if isset($smarty.post.testdata) && $smarty.post.testdata === 'string'} selected{/if}>String</option>
                                    </select>
                                </span>
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="methods">Methoden</label>
                                </span>
                                <select class="form-control" name="methods[]" id="methods" multiple>
                                    {foreach from=$all_methods item=method}
                                        <option value="{$method}"{if !empty($smarty.post.methods) && $method|in_array:$smarty.post.methods}selected{/if}>{$method}</option>
                                    {/foreach}
                                </select>
                            </div>
                            <input name="a" type="hidden" value="benchmark" />
                        </div>
                        <div class="panel-footer">
                            <button name="submit" type="submit" value="Benchmark starten" class="btn btn-primary">{#startBenchmark#}</button>
                        </div>
                    </form>
                </div>
                {if isset($bench_results)}
                    {if is_array($bench_results)}
                        {foreach from=$bench_results key=resultsKey item=result}
                            {if isset($result.method)}
                                <div class="bench-result panel panel-default" style="margin-top: 20px;">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">{$result.method}</h3>
                                    </div>
                                    <div class="panel-body">
                                    <p><span class="opt">Status: </span> <span class="label {if $result.status === 'ok'}label-success{else}label-danger{/if}">{$result.status}</span></p>
                                    <p><span class="opt">Zeit get: </span>
                                        {if $result.status !== 'failed' && $result.status !== 'invalid'}
                                            <span class="text">{$result.timings.get}s</span>
                                            <span class="text">({$result.rps.get} Eintr&auml;ge/s)</span>
                                        {else}
                                            <span class="text">-</span>
                                        {/if}
                                    </p>

                                    <p><span class="opt">Zeit set: </span>
                                        {if $result.status !== 'failed' && $result.status !== 'invalid'}
                                            <span class="text">{$result.timings.set}s</span>
                                            <span class="text">({$result.rps.set} Eintr&auml;ge/s)</span>
                                        {else}
                                            <span class="text">-</span>
                                        {/if}
                                    </p>
                                    </div>
                                </div>
                            {/if}
                        {/foreach}
                    {else}
                        <div class="alert alert-warning">Konnte Benchmark nicht ausf&uuml;hren.</div>
                    {/if}
                {/if}
            {else}
                <div class="alert alert-warning">Keine Methoden gefunden.</div>
            {/if}
        </div>
        <div id="settings" class="tab-pane fade {if isset($tab) && $tab === 'settings'} active in{/if}">
            <form method="post" action="cache.php">
                {$jtl_token}
                <input type="hidden" name="a" value="settings" />
                <input name="tab" type="hidden" value="settings" />

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Allgemein</h3>
                    </div>
                    <div class="panel-body">
                        {foreach name=conf from=$settings item=setting}
                            {if $setting->cConf === 'Y'}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="{$setting->cWertName}">{$setting->cName}</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        {if $setting->cInputTyp === 'selectbox'}
                                            <select name="{$setting->cWertName}" id="{$setting->cWertName}" class="form-control">
                                                {foreach name=selectfor from=$setting->ConfWerte item=wert}
                                                    <option value="{$wert->cWert}" {if isset($setting->gesetzterWert) && $setting->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                                {/foreach}
                                            </select>
                                        {elseif $setting->cInputTyp === 'number'}
                                            <input class="form-control" type="number" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
                                        {else}
                                            <input class="form-control" type="text" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
                                        {/if}
                                    </span>
                                    <span class="input-group-addon">
                                        {if $setting->cBeschreibung}
                                            {getHelpDesc cDesc=$setting->cBeschreibung}
                                        {/if}
                                    </span>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                </div>
                <a id="btn_toggle_cache" class="btn btn-default down" style="margin: 10px 0;">{#showAdvanced#}</a>

                <div id="row_toggle_cache" style="display: none;">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Erweitert</h3>
                        </div>
                        <div class="panel-body">
                            {foreach name=conf from=$advanced_settings item=setting}
                                {if $setting->cConf === 'Y'}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="{$setting->cWertName}">{$setting->cName}</label>
                                        </span>
                                        <span class="input-group-wrap">
                                            {if $setting->cInputTyp === 'selectbox'}
                                                <select name="{$setting->cWertName}" id="{$setting->cWertName}" class="form-control">
                                                    {foreach name=selectfor from=$setting->ConfWerte item=wert}
                                                        <option value="{$wert->cWert}" {if isset($setting->gesetzterWert) && $setting->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                                    {/foreach}
                                                </select>
                                            {elseif $setting->cInputTyp === 'number'}
                                                <input class="form-control" type="number" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
                                            {elseif $setting->cInputTyp === 'pass'}
                                                <input class="form-control" type="password" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
                                            {else}
                                                <input class="form-control" type="text" name="{$setting->cWertName}" id="{$setting->cWertName}" value="{if isset($setting->gesetzterWert)}{$setting->gesetzterWert}{/if}" tabindex="1" />
                                            {/if}
                                        </span>
                                        {if $setting->cBeschreibung}
                                            <span class="input-group-addon">{getHelpDesc cDesc=$setting->cBeschreibung}</span>
                                        {/if}
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
                <p class="submit">
                    <button name="speichern" type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                </p>
            </form>
        </div>
    </div>
</div>