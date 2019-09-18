{include file='tpl_inc/seite_header.tpl' cTitel=#news# cBeschreibung=#newsDesc#}
<div id="content" class="container-fluid">
    <div class="category first clearall">
        <div class="left">{$oNews->cBetreff}</div>
        <div class="no_overflow tright">{$oNews->Datum}</div>
    </div>
    <div class="container-fluid">
        {$oNews->cText}
    </div>
    {if $oNewsKommentar_arr|@count > 0}
        <form method="post" action="news.php">
            {$jtl_token}
            <input type="hidden" name="news" value="1" />
            <input type="hidden" name="kNews" value="{$oNews->kNews}" />
            <input type="hidden" name="kommentare_loeschen" value="1" />
            {if isset($cTab)}
                <input type="hidden" name="tab" value="{$cTab}" />
            {/if}
            {if isset($cSeite)}
                <input type="hidden" name="s2" value="{$cSeite}" />
            {/if}
            <input type="hidden" name="nd" value="1" />
            <div class="category">{#newsComments#}</div>
            {foreach name=kommentare from=$oNewsKommentar_arr item=oNewsKommentar}
                <table width="100%" cellpadding="5" cellspacing="5" class="kundenfeld">
                    <tr>
                        <td valign="top" align="left" style="width: 33%;">

                            <table  class="table">
                                <tr>
                                    <td style="width: 10px;">
                                        <input name="kNewsKommentar[]" type="checkbox" value="{$oNewsKommentar->kNewsKommentar}" id="nk-{$oNewsKommentar->kNewsKommentar}" />
                                    </td>
                                    <td>
                                        <strong>
                                            {if $oNewsKommentar->cVorname|strlen > 0}
                                                <label for="nk-{$oNewsKommentar->kNewsKommentar}">{$oNewsKommentar->cVorname} {$oNewsKommentar->cNachname|truncate:1:""}., {$oNewsKommentar->dErstellt_de}</label>
                                            {else}
                                                <label for="nk-{$oNewsKommentar->kNewsKommentar}">{$oNewsKommentar->cName}, {$oNewsKommentar->dErstellt_de}</label>
                                            {/if}
                                            <a href="news.php?news=1&kNews={$oNews->kNews}&kNewsKommentar={$oNewsKommentar->kNewsKommentar}{if isset($cBackPage)}&{$cBackPage}{elseif isset($cTab)}&tab={$cTab}{/if}&nkedit=1&token={$smarty.session.jtl_token}" class="btn btn-default" title="{#modify#}"><i class="fa fa-edit"></i></a>
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>{$oNewsKommentar->cKommentar}</td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
            {/foreach}
            <div class="btn-group">
                <a class="btn btn-primary" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-angle-double-left"></i> zur&uuml;ck</a>
                <button name="kommentar_loeschen" type="submit" value="{#delete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#delete#}</button>
            </div>
        </form>
    {else}
        <p>
            <a class="btn btn-primary" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-angle-double-left"></i> zur&uuml;ck</a>
        </p>
    {/if}
</div>