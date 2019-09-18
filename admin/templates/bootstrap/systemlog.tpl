{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="systemlog"}
{include file='tpl_inc/seite_header.tpl' cTitel=#systemlog# cBeschreibung=#systemlogDesc# cDokuURL=#systemlogURL#}
<div id="content" class="container-fluid">
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'log'} active{/if}">
            <a data-toggle="tab" role="tab" href="#log">{#systemlogLog#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#systemlogConfig#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="log" class="tab-pane fade {if !isset($cTab) || $cTab === 'log'} active in{/if}">
            {include file='tpl_inc/pagination.tpl' oPagination=$oPagination cParam_arr=['cSucheEncode'=>$cSuche,'nLevel'=>$nLevel]}
            <div class="block container2 clearall">
                <div class="left p50">
                    <form method="post" action="systemlog.php">
                        {$jtl_token}
                        <input type="hidden" name="suche" value="1" />
                        <input type="hidden" name="tab" value="log" />
                        <div class="input-group p50 left" style="padding-right: 10px;">
                            <span class="input-group-addon">
                                <label for="nLevel">{#systemlogLevel#}:</label>
                            </span>
                            <span class="input-group-wrap">
                                <select id="nLevel" name="nLevel" class="form-control">
                                    <option value="0">alle</option>
                                    <option value="{$JTLLOG_LEVEL_ERROR}"{if $nLevel == $JTLLOG_LEVEL_ERROR} selected{/if}>{#systemlogError#}</option>
                                    <option value="{$JTLLOG_LEVEL_NOTICE}"{if $nLevel == $JTLLOG_LEVEL_NOTICE} selected{/if}>{#systemlogNotice#}</option>
                                    <option value="{$JTLLOG_LEVEL_DEBUG}"{if $nLevel == $JTLLOG_LEVEL_DEBUG} selected{/if}>{#systemlogDebug#}</option>
                                </select>
                            </span>
                        </div>
                        <div class="input-group p50 left">
                            <span class="input-group-addon">
                                <label for="cSuche">{#systemlogSearch#}</label>
                            </span>
                            <input class="form-control" id="cSuche" name="cSuche" type="text" value="{$cSuche}" />
                            <span class="input-group-btn">
                                <button name="btn_search" type="submit" class="btn btn-primary" value="{#systemlogBTNSearch#}"><i class="fa fa-search"></i> {#systemlogBTNSearch#}</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="content">
                {if $oLog_arr|@count == 0}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {else}
                    <div id="highlighted">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">{#systemlogLog#}</h3>
                            </div>
                            <table class="list table">
                                <thead>
                                <tr>
                                    <th class="tleft" style="width: 85%">Meldung</th>
                                    <th>Typ</th>
                                    <th>Datum</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach from=$oLog_arr item="oLog"}
                                    <tr>
                                        <td>
                                            <div class="highlight">{$oLog->getcLog()}</div>
                                        </td>
                                        <td class="tcenter" valign="top">
                                            <h4 class="label-wrap">
                                                {if $oLog->getLevel() == 1}
                                                    <span class="label label-danger">{#systemlogError#}</span>
                                                {elseif $oLog->getLevel() == 2}
                                                    <span class="label label-success">{#systemlogNotice#}</span>
                                                {elseif $oLog->getLevel() == 4}
                                                    <span class="label label-info info">{#systemlogDebug#}</span>
                                                {else}
                                                    <span class="label labe-default">Unbekannt</span>
                                                {/if}
                                            </h4>
                                        </td>
                                        <td class="tcenter" valign="top">{$oLog->getErstellt()|date_format:"%d.%m.%Y - %H:%M:%S"}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                            <div class="panel-footer">
                                <form name="clear-logs" method="post" action="systemlog.php">
                                    {$jtl_token}
                                    <input type="hidden" name="a" value="del" />
                                    <button type="submit" class="btn btn-danger">Log zur&uuml;cksetzen</button>
                                </form>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            <form name="einstellen" method="post" action="systemlog.php">
                {$jtl_token}
                <input type="hidden" name="einstellungen" value="1" />
                <input type="hidden" name="tab" value="einstellungen" />

                <div class="settings">
                    <p>
                        <label for="nFlag">{#systemlogLevel#}</label>
                        {getHelpDesc cDesc=#systemlogLevelDesc# placement="right"}
                        <input id="syslog-error" name="nFlag[]" type="checkbox" value="{$JTLLOG_LEVEL_ERROR}"{if $nFlag_arr[$JTLLOG_LEVEL_ERROR] != 0} checked{/if} />
                        <label for="syslog-error">Fehler</label>
                        <input id="syslog-notice" name="nFlag[]" type="checkbox" value="{$JTLLOG_LEVEL_NOTICE}"{if $nFlag_arr[$JTLLOG_LEVEL_NOTICE] != 0} checked{/if} />
                        <label for="syslog-notice">Hinweis</label>
                        <input id="syslog-debug" name="nFlag[]" type="checkbox" value="{$JTLLOG_LEVEL_DEBUG}"{if $nFlag_arr[$JTLLOG_LEVEL_DEBUG] != 0} checked{/if} />
                        <label for="syslog-debug">Debug</label>
                    </p>
                </div>
                <p class="submit">
                    <button type="submit" value="{#save#}" class="btn btn-primary"><i class="fa fa-save"></i> {#save#}</button>
                </p>
            </form>
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}