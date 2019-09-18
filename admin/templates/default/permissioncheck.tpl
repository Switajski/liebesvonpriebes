{*
-------------------------------------------------------------------------------
JTL-Shop 3
File: permissioncheck.tpl, smarty template inc file

page for JTL-Shop 3 
Admin

Author: daniel.boehmer@jtl-software.de, JTL-Software
http://www.jtl-software.de

Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file='tpl_inc/header.tpl'}

{config_load file="$lang.conf" section="permissioncheck"}

{include file="tpl_inc/seite_header.tpl" cTitel=#permissioncheck# cBeschreibung=#permissioncheckDesc# cDokuURL=#permissioncheckkURL#}

<div id="content">

    <div id="pageCheck">

        {if isset($cDirAssoc_arr) && $cDirAssoc_arr|@count > 0}
            <div id="contentCheck">

                <p class="box_info"><strong>Anzahl Verzeichnisse:</strong> {$oStat->nCount}<br /><strong>Anzahl nicht beschreibbarer Verzeichnisse:</strong> {$oStat->nCountInValid}</p>

                {if $oStat->nCountInValid > 0}
                    <p>
                        <input name="viewAll" type="button" class="button viewAll hide" value="Alle anzeigen" />
                        <input name="viewModified" type="button" class="button viewModified" value="Modifizierte anzeigen" />
                    </p>
                    <br />
                {/if}
                <ul class="req">
                    {foreach name=dirs from=$cDirAssoc_arr key=cDir item=isValid}
                        <li class="mod{$smarty.foreach.dirs.iteration%2} {if $isValid}unmodified{else}modified{/if}">{$cDir}<span class="{if $isValid}green{else}red{/if}">{if $isValid}Ok{else}Schreibrechte fehlen{/if}</span></li>
                        {/foreach}
                </ul>
            </div>
        {else}
            {if isset($cHinweis) && $cHinweis|count_characters > 0}
                <p class="box_success">{$cHinweis}</p>
            {/if}
            {if isset($cFehler) && $cFehler|count_characters > 0}
                <p class="box_error">{$cFehler}</p>
            {/if}
        {/if}    

    </div>

</div>
<script>
    {literal}
        $(document).ready(function () {

            $(".viewAll").click(function () {
                $(".viewAll").hide();
                $(".viewModified").show();
                $(".unmodified").show();
                $(".modified").show();
                colorLines();
            });

            $(".viewModified").click(function () {
                $(".viewAll").show();
                $(".viewModified").hide();
                $(".unmodified").hide();
                $(".modified").show();
                colorLines();
            });

            function colorLines() {
                var mod = 1;
                $(".req li:not(:hidden)").each(function () {
                    if (mod == 1) {
                        $(this).removeClass("mod0");
                        $(this).removeClass("mod1");
                        $(this).addClass("mod1");
                        mod = 0;
                    } else {
                        $(this).removeClass("mod1");
                        $(this).removeClass("mod0");
                        $(this).addClass("mod0");
                        mod = 1;
                    }
                });
            }
        });

    {/literal}
</script>
{include file='tpl_inc/footer.tpl'}