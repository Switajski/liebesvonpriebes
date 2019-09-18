{include file='tpl_inc/seite_header.tpl' cTitel=#taggingdetail# cBeschreibung=#taggingdetailDesc#}
<div id="content">
    {if !empty($cTagName)}
        <p>{#taggingdetailTag#} <strong>{$cTagName}</strong></p>
    {else}
        <p class="alert alert-info">Keine Daten vorhanden.</p>
    {/if}
    {if isset($oTagArtikel_arr) && $oTagArtikel_arr|@count > 0}
        {include file='tpl_inc/pagination.tpl' oPagination=$oPagiTagDetail}
        <!-- Tag Detailansicht -->
        <form method="post" action="tagging.php">
            {$jtl_token}
            <input name="detailloeschen" type="hidden" value="1" />
            <input name="tagdetail" type="hidden" value="1" />
            <input type="hidden" name="kTag" value="{$kTag}" />

            <div id="payment">
                <div id="tabellenLivesuche" class="table-responsive">
                    <table class="table">
                        <tr>
                            <th class="check">&nbsp;</th>
                            <th class="th-2">{#taggingProduct#}</th>
                        </tr>
                        {foreach name=tagdetail from=$oTagArtikel_arr item=oTagArtikel}
                            <tr class="tab_bg{$smarty.foreach.tagdetail.iteration%2}">
                                <td class="check">
                                    <input name="kArtikel_arr[]" type="checkbox" value="{$oTagArtikel->kArtikel}" />
                                </td>
                                <td class="TD2"><a href="{$oTagArtikel->cURL}">{$oTagArtikel->acName}</a></td>
                            </tr>
                        {/foreach}
                        <tr>
                            <td class="check">
                                <input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" />
                            </td>
                            <td colspan="5" class="TD7"><label for="ALLMSGS">{#taggingSelectAll#}</label></td>
                        </tr>
                    </table>
                </div>
            </div>
            <p class="submit">
                <button name="loeschen" type="submit" value="{#taggingdelete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#taggingdelete#}</button>
            </p>
        </form>
    {/if}
</div>