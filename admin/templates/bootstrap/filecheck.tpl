{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="filecheck"}
{include file='tpl_inc/seite_header.tpl' cTitel=#filecheck# cBeschreibung=#filecheckDesc# cDokuURL=#filecheckURL#}

<div id="content" class="container-fluid">
    <div id="pageCheck">
        {if isset($oDatei_arr) && $oDatei_arr|@count > 0}
            <div id="contentCheck">
                <div class="alert alert-info"><strong>Anzahl Dateien:</strong> {$nStat_arr.nAnzahl}<br /><strong>Anzahl modifizierter Dateien:</strong> {$nStat_arr.nFehler}</div>
                {if $nStat_arr.nFehler > 0}
                    <p>
                        <button id="viewAll" name="viewAll" type="button" class="btn btn-primary hide" value="Alle anzeigen"><i class="fa fa-share"></i> Alle anzeigen</button>
                        <button id="viewModified" name="viewModified" type="button" class="btn btn-danger viewModified" value="Modifizierte anzeigen"><i class="fa fa-warning"></i> Modifizierte anzeigen</button>
                    </p>
                    <br />
                {/if}
                <table class="table req">
                    <thead>
                        <tr>
                            <th>Datei</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    {foreach name=datei from=$oDatei_arr item=oDatei}
                    <tr class="filestate mod{$smarty.foreach.datei.iteration%2} {if !$oDatei->bFehler}unmodified{else}modified{/if}">
                        <td>{$oDatei->cName}</td>
                        <td><span class="badge {if !$oDatei->bFehler}green{else}red{/if}">{if !$oDatei->bFehler}Ok{else}modifiziert{/if}</span></td>
                    </tr>
                    {/foreach}
                </table>
            </div>
        {else}
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
</script>{include file='tpl_inc/footer.tpl'}