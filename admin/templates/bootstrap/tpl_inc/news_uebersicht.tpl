<script type="text/javascript" src="{$URL_SHOP}/{$PFAD_ADMIN}{$currentTemplateDir}js/sorttable.js"></script>
{include file='tpl_inc/seite_header.tpl' cTitel=#news# cBeschreibung=#newsDesc# cDokuURL=#newsURL#}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="sprache" method="post" action="news.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="input-group p25 left">
                <span class="input-group-addon">
                    <label for="lang-changer">{#changeLanguage#}</label>
                </span>
                <span class="input-group-wrap last">
                    <select id="lang-changer" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                        {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>

    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'inaktiv'} active{/if}">
            <a data-toggle="tab" role="tab" href="#inaktiv">{#newsCommentActivate#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'aktiv'} active{/if}">
            <a data-toggle="tab" role="tab" href="#aktiv">{#newsOverview#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'kategorien'} active{/if}">
            <a data-toggle="tab" role="tab" href="#kategorien">{#newsCatOverview#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#newsSettings#}</a>
        </li>
    </ul>

    <div class="tab-content">
        <div id="inaktiv" class="tab-pane fade{if !isset($cTab) || $cTab === 'inaktiv'} active in{/if}">
            {if $oNewsKommentar_arr && $oNewsKommentar_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiKommentar cAnchor='inaktiv'}
                <form method="post" action="news.php">
                    {$jtl_token}
                    <input type="hidden" name="news" value="1" />
                    <input type="hidden" name="newskommentar_freischalten" value="1" />
                    <input type="hidden" name="nd" value="1" />
                    <input type="hidden" name="tab" value="inaktiv" />
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#newsCommentActivate#}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="list table">
                                <thead>
                                <tr>
                                    <th class="check">&nbsp;</th>
                                    <th class="tleft">{#newsUser#}</th>
                                    <th class="tleft">{#newsHeadline#}</th>
                                    <th class="tleft">{#newsText#}</th>
                                    <th class="th-5">{#newsDate#}</th>
                                    <th class="th-6" style="min-width: 140px;"></th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach name=newskommentare from=$oNewsKommentar_arr item=oNewsKommentar}
                                    <tr class="tab_bg{$smarty.foreach.newskommentare.iteration%2}">
                                        <td class="check">
                                            <input type="checkbox" name="kNewsKommentar[]" value="{$oNewsKommentar->kNewsKommentar}" id="comment-{$oNewsKommentar->kNewsKommentar}" />
                                        </td>
                                        <td class="TD2">
                                            <label for="comment-{$oNewsKommentar->kNewsKommentar}">
                                            {if $oNewsKommentar->cVorname|strlen > 0}
                                                {$oNewsKommentar->cVorname} {$oNewsKommentar->cNachname}
                                            {else}
                                                {$oNewsKommentar->cName}
                                            {/if}
                                            </label>
                                        </td>
                                        <td class="TD3">{$oNewsKommentar->cBetreff|truncate:50:"..."}</td>
                                        <td class="TD4">{$oNewsKommentar->cKommentar|truncate:150:"..."}</td>
                                        <td class="tcenter">{$oNewsKommentar->dErstellt_de}</td>
                                        <td class="tcenter">
                                            <a href="news.php?news=1&kNews={$oNewsKommentar->kNews}&kNewsKommentar={$oNewsKommentar->kNewsKommentar}&nkedit=1&tab=inaktiv&token={$smarty.session.jtl_token}"
                                               class="btn btn-primary" title="{#modify#}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="check"><input name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" /></td>
                                        <td colspan="5"><label for="ALLMSGS1">Alle ausw&auml;hlen</label></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button name="freischalten" type="submit" value="{#newsActivate#}" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> {#newsActivate#}</button>
                                <button name="kommentareloeschenSubmit" type="submit" value="{#delete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#delete#}</button>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <!-- #inaktiv -->
        <div id="aktiv" class="tab-pane fade{if isset($cTab) && $cTab === 'aktiv'} active in{/if}">
            {include file='tpl_inc/pagination.tpl' oPagination=$oPagiNews cAnchor='aktiv'}
            <form name="news" method="post" action="news.php">
                {$jtl_token}
                <input type="hidden" name="news" value="1" />
                <input type="hidden" name="news_loeschen" value="1" />
                <input type="hidden" name="tab" value="aktiv" />
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#newsOverview#}</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="sortable list table">
                            <thead>
                            <tr>
                                <th class="check"></th>
                                <th class="tleft">{#newsHeadline#}</th>
                                <th class="tleft">{#newsCategory#}</th>
                                <th class="tleft">{#newsCustomerGrp#}</th>
                                <th class="tleft">{#newsValidation#}</th>
                                <th>{#newsActive#}</th>
                                <th>{#newsComments#}</th>
                                <th>{#newsCatLastUpdate#}</th>
                                <th style="min-width: 100px;"></th>
                            </tr>
                            </thead>
                            <tbody>
                            {if $oNews_arr|@count > 0 && $oNews_arr}
                                {foreach name=news from=$oNews_arr item=oNews}
                                    <tr class="tab_bg{$smarty.foreach.news.iteration%2}">
                                        <td class="check"><input type="checkbox" name="kNews[]" value="{$oNews->kNews}" id="news-cb-{$oNews->kNews}" /></td>
                                        <td class="TD2"><label for="news-cb-{$oNews->kNews}">{$oNews->cBetreff}</label></td>
                                        <td class="TD3">{$oNews->KategorieAusgabe}</td>
                                        <td class="TD4">
                                            {foreach name=kundengruppen from=$oNews->cKundengruppe_arr item=cKundengruppe}
                                                {$cKundengruppe}{if !$smarty.foreach.kundengruppen.last},{/if}
                                            {/foreach}
                                        </td>
                                        <td class="TD5">{$oNews->dGueltigVon_de}</td>
                                        <td class="tcenter"><i class="fa fa-{if $oNews->nAktiv == 1}check{else}close{/if}"></i></td>
                                        <td class="tcenter">
                                            {if $oNews->nNewsKommentarAnzahl > 0}
                                                <a href="news.php?news=1&nd=1&kNews={$oNews->kNews}&tab=aktiv&token={$smarty.session.jtl_token}">{$oNews->nNewsKommentarAnzahl}</a>
                                            {else}
                                                {$oNews->nNewsKommentarAnzahl}
                                            {/if}
                                        </td>
                                        <td class="tcenter">{$oNews->Datum}</td>
                                        <td class="tcenter">
                                            <div class="btn-group">
                                                <a href="news.php?news=1&news_editieren=1&kNews={$oNews->kNews}&tab=aktiv&token={$smarty.session.jtl_token}"
                                                   class="btn btn-primary" title="{#modify#}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="news.php?news=1&nd=1&kNews={$oNews->kNews}&tab=aktiv&token={$smarty.session.jtl_token}"
                                                   class="btn btn-default" title="{#newsPreview#}">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr>
                                    <td colspan="9">
                                        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                                    </td>
                                </tr>
                            {/if}
                            </tbody>
                            <tfoot>
                            <tr>
                                <td class="check"><input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" /></td>
                                <td colspan="8"><label for="ALLMSGS2">Alle ausw&auml;hlen</label></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <input type="hidden" name="news" value="1" />
                    <input type="hidden" name="erstellen" value="1" />
                    <input type="hidden" name="tab" value="aktiv" />
                    <div class="panel-footer">
                        <div class="btn-group">
                            <button name="news_erstellen" type="submit" value="{#newAdd#}" class="btn btn-primary"><i class="fa fa-share"></i> {#newAdd#}</button>
                            <button name="loeschen" type="submit" value="{#delete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#delete#}</button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="container2">
                <form name="erstellen" method="post" action="news.php">
                    {$jtl_token}
                </form>
            </div>
        </div>
        <!-- #inaktiv -->
        <div id="kategorien" class="tab-pane fade{if isset($cTab) && $cTab === 'kategorien'} active in{/if}">
            {include file='tpl_inc/pagination.tpl' oPagination=$oPagiKats cAnchor='kategorien'}
            <form name="news" method="post" action="news.php">
                {$jtl_token}
                <input type="hidden" name="news" value="1" />
                <input type="hidden" name="news_kategorie_loeschen" value="1" />
                <input type="hidden" name="tab" value="kategorien" />
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#newsCatOverview#}</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="list table">
                            <thead>
                            <tr>
                                <th class="check"></th>
                                <th class="tleft">{#newsCatName#}</th>
                                <th class="">{#newsCatSortShort#}</th>
                                <th class="th-4">{#newsActive#}</th>
                                <th class="th-5">{#newsCatLastUpdate#}</th>
                                <th class="th-5">&nbsp;</th>
                            </tr>
                            </thead>
                            <tbody>
                            {if $oNewsKategorie_arr|@count > 0 && $oNewsKategorie_arr}
                                {foreach name=newskategorie from=$oNewsKategorie_arr item=oNewsKategorie}
                                    <tr class="tab_bg{$smarty.foreach.newskategorie.iteration%2}">
                                        <td class="check">
                                            <input type="checkbox" name="kNewsKategorie[]" value="{$oNewsKategorie->kNewsKategorie}" id="newscat-{$oNewsKategorie->kNewsKategorie}" />
                                        </td>
                                        <td class="TD2"><label for="newscat-{$oNewsKategorie->kNewsKategorie}">{$oNewsKategorie->cName}</label></td>
                                        <td class="tcenter">{$oNewsKategorie->nSort}</td>
                                        <td class="tcenter">{if $oNewsKategorie->nAktiv === '1'}ja{else}nein{/if}</td>
                                        <td class="tcenter">{$oNewsKategorie->dLetzteAktualisierung_de}</td>
                                        <td class="tcenter">
                                            <a href="news.php?news=1&newskategorie_editieren=1&kNewsKategorie={$oNewsKategorie->kNewsKategorie}&tab=kategorien&token={$smarty.session.jtl_token}"
                                               class="btn btn-primary" title="{#modify#}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr>
                                    <td colspan="6">
                                        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                                    </td>
                                </tr>
                            {/if}
                            </tbody>
                            <tfoot>
                            <tr>
                                <td class="check"><input name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);" /></td>
                                <td colspan="5"><label for="ALLMSGS3">Alle ausw&auml;hlen</label></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                    <input type="hidden" name="news" value="1" />
                    <input type="hidden" name="erstellen" value="1" />
                    <input type="hidden" name="tab" value="kategorien" />
                    <div class="panel-footer">
                        <div class="btn-group">
                            <button name="news_kategorie_erstellen" type="submit" value="{#newsCatAdd#}" class="btn btn-primary"><i class="fa fa-share"></i> {#newsCatAdd#}</button>
                            <button name="loeschen" type="submit" value="{#delete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#delete#}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!-- #kategorien -->
        <div id="einstellungen" class="tab-pane fade{if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            <form name="einstellen" method="post" action="news.php">
                {$jtl_token}
                <input type="hidden" name="einstellungen" value="1" />
                <input type="hidden" name="tab" value="einstellungen" />

                <div class="panel panel-default settings">
                    <div class="panel-body">
                        {foreach name=conf from=$oConfig_arr item=oConfig}
                            {if $oConfig->cConf === 'Y'}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="{$oConfig->cWertName}">{$oConfig->cName}</label>
                                    </span>
                                    <span class="input-group-wrap">
                                        {if $oConfig->cInputTyp === 'selectbox'}
                                            <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="form-control combo">
                                                {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                                    <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                                {/foreach}
                                            </select>
                                        {elseif $oConfig->cInputTyp === 'listbox'}
                                            <select name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}" multiple="multiple" class="form-control combo">
                                                {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                                    <option value="{$wert->kKundengruppe}" {foreach name=werte from=$oConfig->gesetzterWert item=gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                                                {/foreach}
                                            </select>
                                        {elseif $oConfig->cInputTyp === 'number'}
                                            <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                                        {else}
                                            <input class="form-control" type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                                        {/if}
                                    </span>
                                    {if $oConfig->cBeschreibung}
                                        <span class="input-group-addon">{getHelpDesc cDesc=$oConfig->cBeschreibung} <span class="sid badge">{$oConfig->kEinstellungenConf}</span></span>
                                    {/if}
                                </div>
                            {/if}
                        {/foreach}

                        {foreach name=monatspraefix from=$oNewsMonatsPraefix_arr item=oNewsMonatsPraefix}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="praefix_{$oNewsMonatsPraefix->cISOSprache}">{#newsPraefix#} ({$oNewsMonatsPraefix->cNameDeutsch})</label>
                                </span>
                                <input type="text" class="form-control" id="praefix_{$oNewsMonatsPraefix->cISOSprache}" name="praefix_{$oNewsMonatsPraefix->cISOSprache}" value="{$oNewsMonatsPraefix->cPraefix}" tabindex="1" />
                            </div>
                        {/foreach}
                    </div>
                    <div class="panel-footer">
                        <button type="submit" value="{#newsSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#newsSave#}</button>
                    </div>
                </div>
            </form>
        </div>
        <!-- #einstellungen -->
    </div>
    <!-- .tab-content -->
</div>