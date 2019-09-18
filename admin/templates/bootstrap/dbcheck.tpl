{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="dbcheck"}
{include file='tpl_inc/seite_header.tpl' cTitel=#dbcheck# cBeschreibung=#dbcheckDesc# cDokuURL=#dbcheckURL#}
<div id="content" class="container-fluid">
    {if $maintenanceResult !== null}
        {if $maintenanceResult|is_array}
            <ul class="list-group">
                {foreach name=results from=$maintenanceResult item=result}
                    <li class="list-group-item">
                        <strong>{$result->Op} {$result->Table}:</strong> {$result->Msg_text}
                    </li>
                {/foreach}
            </ul>
        {else}
            <div class="alert alert-info">Konnte Aktion nicht ausf&uuml;hren.</div>
        {/if}
    {/if}
    <div id="pageCheck">
        {if isset($cDBFileStruct_arr) && $cDBFileStruct_arr|@count > 0}
            <div class="alert alert-info"><strong>Anzahl Tabellen:</strong> {$cDBFileStruct_arr|@count}<br /><strong>Anzahl modifizierter Tabellen:</strong> {$cDBError_arr|@count}</div>
            {if $cDBError_arr|@count > 0}
                <p>
                    <button id="viewAll" name="viewAll" type="button" class="btn btn-primary hide" value="Alle anzeigen"><i class="fa fa-share"></i> Alle anzeigen</button>
                    <button id="viewModified" name="viewModified" type="button" class="btn btn-danger viewModified" value="Modifizierte anzeigen"><i class="fa fa-warning"></i> Modifizierte anzeigen</button>
                </p>
                <br />
            {/if}
            <form action="dbcheck.php" method="post">
                <div id="contentCheck" class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">DB-Struktur</h3>
                    </div>
                    <table class="table req">
                        <thead>
                        <tr>
                            <th>Tabelle</th>
                            <th>Status</th>
                            <th>Aktion</th>
                        </tr>
                        </thead>
                        {foreach name=datei from=$cDBFileStruct_arr key=cTable item=oDatei}
                            {assign var=hasError value=$cTable|array_key_exists:$cDBError_arr}
                            <tr class="filestate mod{$smarty.foreach.datei.iteration%2} {if !$cTable|array_key_exists:$cDBError_arr}unmodified{else}modified{/if}">
                                <td>
                                    {if $hasError}
                                        {$cTable}
                                    {else}
                                        <label for="check-{$smarty.foreach.datei.iteration}">{$cTable}</label>
                                    {/if}
                                </td>
                                <td>
                                    {if $hasError}
                                        <span class="badge red">{$cDBError_arr[$cTable]}</span>
                                    {else}
                                        <span class="badge green">Ok</span>
                                    {/if}
                                </td>
                                <td>
                                    {if !$hasError}
                                        <input id="check-{$smarty.foreach.datei.iteration}" type="checkbox" name="check[]" value="{$cTable}" />
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                    </table>
                    <div class="panel-footer">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <input type="checkbox" name="ALL_MSG" id="ALLMSGS" onclick="AllMessages(this.form);"/> <label for="ALLMSGS">alle markieren</label>
                            </span>
                            <select name="action" class="form-control">
                                <option value="">Aktion</option>
                                <option value="optimize">optimieren</option>
                                <option value="repair">reparieren</option>
                                <option value="analyze">analysieren</option>
                                <option value="check">pr&uuml;fen</option>
                            </select>
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-primary">absenden</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        {else}
            {if isset($cFehler) && $cFehler|strlen > 0}
                <div class="alert alert-danger">{$cFehler}</div>
            {/if}
        {/if}
    </div>
</div>

<script>
    {literal}
    $(document).ready(function () {
        $('#viewAll').click(function () {
            $('#viewAll').hide();
            $('#viewModified').show().removeClass('hide');
            $('.unmodified').show();
            $('.modified').show();
            colorLines();
        });

        $('#viewModified').click(function () {
            $('#viewAll').show().removeClass('hide');
            $('#viewModified').hide();
            $('.unmodified').hide();
            $('.modified').show();
            colorLines();
        });

        function colorLines() {
            var mod = 1;
            $('.req li:not(:hidden)').each(function () {
                if (mod == 1) {
                    $(this).removeClass('mod0');
                    $(this).removeClass('mod1');
                    $(this).addClass('mod1');
                    mod = 0;
                } else {
                    $(this).removeClass('mod1');
                    $(this).removeClass('mod0');
                    $(this).addClass('mod0');
                    mod = 1;
                }
            });
        }
    });

    {/literal}
</script>
{include file='tpl_inc/footer.tpl'}