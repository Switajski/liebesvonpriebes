{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="permissioncheck"}
{include file='tpl_inc/seite_header.tpl' cTitel=#permissioncheck# cBeschreibung=#permissioncheckDesc# cDokuURL=#permissioncheckURL#}

<div id="content" class="container-fluid">
    <div id="pageCheck">
        {if isset($cDirAssoc_arr) && $cDirAssoc_arr|@count > 0}
            <div id="contentCheck">
                <div class="alert alert-info">
                    <strong>Anzahl Verzeichnisse:</strong> {$oStat->nCount}<br />
                    <strong>Anzahl nicht beschreibbarer Verzeichnisse:</strong> {$oStat->nCountInValid}
                </div>
                {if $oStat->nCountInValid > 0}
                    <p>
                        <button id="viewAll" name="viewAll" type="button" class="btn btn-primary hide" value="Alle anzeigen"><i class="fa fa-"></i> Alle anzeigen</button>
                        <button id="viewModified" name="viewModified" type="button" class="btn btn-default viewModified" value="Modifizierte anzeigen"><i class="fa fa-warning"></i> Modifizierte anzeigen</button>
                    </p>
                    <br />
                {/if}
                <ul class="list-group">
                    {foreach name=dirs from=$cDirAssoc_arr key=cDir item=isValid}
                        <li class="filestate list-group-item mod{$smarty.foreach.dirs.iteration%2} {if $isValid}unmodified{else}modified{/if}">
                        {if $isValid}<i class="fa fa-check-circle success"></i>{else}<i class="fa fa-exclamation-circle error"></i>{/if}
                        <span class="dir-check">{$cDir}</span>
                        </li>
                    {/foreach}
                </ul>
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
</script>
{include file='tpl_inc/footer.tpl'}