{include file='tpl_inc/seite_header.tpl' cTitel=#exportformatFormat#}
{literal}
    <script type="text/javascript">
        $(document).ready(function () {
            $('#nAlleXStunden').change(function () {
                var val = $(this).val(),
                    customField = $('#custom-freq-input');
                if (val === 'custom') {
                    customField.attr('name', 'nAlleXStundenCustom').show();
                } else {
                    customField.attr('name', '').hide();
                }
            });
        });
    </script>
{/literal}
<div id="content" class="container-fluid2">
    <form name="exportformat_queue" method="post" action="exportformat_queue.php">
        {$jtl_token}
        <input type="hidden" name="erstellen_eintragen" value="1" />
        {if isset($oCron->kCron) && $oCron->kCron > 0}
            <input type="hidden" name="kCron" value="{$oCron->kCron}" />
        {/if}
        {if $oExportformat_arr|@count > 0}
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{if isset($oCron->kCron) && $oCron->kCron > 0}{#save#}{else}{#exportformatAdd#}{/if}</h3>
                </div>
                <table class="kundenfeld table" id="formtable">
                    <tr>
                        <td><label for="kExportformat">{#exportformatFormat#}</label></td>
                        <td>
                            <select name="kExportformat" id="kExportformat" class="form-control">
                                <option value="-1"></option>
                                {foreach name=exportformate from=$oExportformat_arr item=oExportformat}
                                    <option value="{$oExportformat->kExportformat}"{if (isset($oFehler->kExportformat) && $oFehler->kExportformat == $oExportformat->kExportformat) || (isset($oCron->kKey) && $oCron->kKey == $oExportformat->kExportformat)} selected{/if}>{$oExportformat->cName}
                                        ({$oExportformat->Sprache->cNameDeutsch} / {$oExportformat->Waehrung->cName}
                                        / {$oExportformat->Kundengruppe->cName})
                                    </option>
                                {/foreach}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="dStart">{#exportformatStart#}</label></td>
                        <td>
                            <input id="dStart" name="dStart" type="text" class="form-control" value="{if isset($oFehler->dStart) && $oFehler->dStart|strlen > 0}{$oFehler->dStart}{elseif isset($oCron->dStart_de) && $oCron->dStart_de|strlen > 0}{$oCron->dStart_de}{else}{$smarty.now|date_format:'%d.%m.%Y %H:%M'}{/if}" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="nAlleXStunden">{#exportformatEveryXHour#}</label></td>
                        <td>
                            {assign var=showCustomInput value=false}
                            <input type="number" min="1" value="{if !empty($oCron->nAlleXStd) && $oCron->nAlleXStd != 24 && $oCron->nAlleXStd != 48 && $oCron->nAlleXStd != 168}{assign var=showCustomInput value=true}{$oCron->nAlleXStd}{/if}" class="form-control" name="{if $showCustomInput}nAlleXStundenCustom{/if}"{if !$showCustomInput} style="display:none;"{/if} id="custom-freq-input" />
                            <select id="nAlleXStunden" name="nAlleXStunden" class="form-control">
                                <option value="24"{if (isset($oFehler->nAlleXStunden) && $oFehler->nAlleXStunden|strlen > 0 && $oFehler->nAlleXStunden == 24) || (isset($oCron->nAlleXStd) && $oCron->nAlleXStd|strlen > 0 && $oCron->nAlleXStd == 24)} selected{/if}>
                                    24 Stunden
                                </option>
                                <option value="48"{if (isset($oFehler->nAlleXStunden) && $oFehler->nAlleXStunden|strlen > 0 && $oFehler->nAlleXStunden == 48) || (isset($oCron->nAlleXStd) && $oCron->nAlleXStd|strlen > 0 && $oCron->nAlleXStd == 48)} selected{/if}>
                                    48 Stunden
                                </option>
                                <option value="168"{if (isset($oFehler->nAlleXStunden) && $oFehler->nAlleXStunden|strlen > 0 && $oFehler->nAlleXStunden == 168) || (isset($oCron->nAlleXStd) && $oCron->nAlleXStd|strlen > 0 && $oCron->nAlleXStd == 168)} selected{/if}>
                                    1 Woche
                                </option>
                                <option value="custom" id="custom-freq"{if $showCustomInput} selected{/if}>eigene</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <div class="panel-footer">
                    <div class="btn-group">
                        <button name="action[erstellen_eintragen]" type="submit" value="1" class="btn btn-primary"><i class="fa fa-save"></i> {if isset($oCron->kCron) && $oCron->kCron > 0}{#save#}{else}{#exportformatAdd#}{/if}</button>
                        <a class="btn btn-danger" href="exportformat_queue.php"><i class="fa fa-exclamation"></i> Abbrechen</a>
                    </div>
                </div>
            </div>
        {else}
            <div class="alert alert-info">{#exportformatNoFormat#}</div>
        {/if}
    </form>
</div>
