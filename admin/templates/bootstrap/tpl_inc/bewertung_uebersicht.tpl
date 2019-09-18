{include file='tpl_inc/seite_header.tpl' cTitel=#votesystem# cBeschreibung=#votesystemDesc# cDokuURL=#votesystemURL#}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="sprache" method="post" action="bewertung.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="input-group col-xs-6">
                <span class="input-group-addon">
                    <label for="{#changeLanguage#}">{#changeLanguage#}</label>
                </span>
                <span class="input-group-wrap last">
                    <select id="{#changeLanguage#}" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                        {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>
    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'freischalten'} active{/if}">
            <a data-toggle="tab" role="tab" href="#freischalten">{#ratingsInaktive#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'letzten50'} active{/if}">
            <a data-toggle="tab" role="tab" href="#letzten50">{#ratingLast50#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'artikelbewertung'} active{/if}">
            <a data-toggle="tab" role="tab" href="#artikelbewertung">{#ratingForProduct#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#ratingSettings#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="freischalten" class="tab-pane fade {if !isset($cTab) || $cTab === 'freischalten'} active in{/if}">
            {if $oBewertung_arr && $oBewertung_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiInaktiv cAnchor='freischalten'}
                <form method="post" action="bewertung.php">
                    {$jtl_token}
                    <input type="hidden" name="bewertung_nicht_aktiv" value="1" />
                    <input type="hidden" name="tab" value="freischalten" />
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#ratingsInaktive#}</h3>
                        </div>
                        <div class="table-responsive">
                            <table  class="table">
                                <thead>
                                <tr>
                                    <th class="check">&nbsp;</th>
                                    <th class="tleft">{#productName#}</th>
                                    <th class="tleft">{#customerName#}</th>
                                    <th class="tleft">{#ratingText#}</th>
                                    <th class="th-5">{#ratingStars#}</th>
                                    <th class="th-6">{#ratingDate#}</th>
                                    <th class="th-7">&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                    {foreach name=bewertung from=$oBewertung_arr item=oBewertung key=kKey}
                                        <tr class="tab_bg{$smarty.foreach.bewertung.iteration%2}">
                                            <td class="check">
                                                <input type="hidden" name="kArtikel[{$kKey}]" value="{$oBewertung->kArtikel}" />
                                                <input name="kBewertung[{$kKey}]" type="checkbox" value="{$oBewertung->kBewertung}" />
                                            </td>
                                            <td class="TD2"><a href="../index.php?a={$oBewertung->kArtikel}" target="_blank">{$oBewertung->ArtikelName}</a></td>
                                            <td class="TD3">{$oBewertung->cName}.</td>
                                            <td class="TD4"><b>{$oBewertung->cTitel}</b><br />{$oBewertung->cText}</td>
                                            <td class="tcenter">{$oBewertung->nSterne}</td>
                                            <td class="tcenter">{$oBewertung->Datum}</td>
                                            <td class="tcenter">
                                                <a href="bewertung.php?a=editieren&kBewertung={$oBewertung->kBewertung}&tab=freischalten&token={$smarty.session.jtl_token}"
                                                   class="btn btn-default" title="{#modify#}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    {/foreach}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="check"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);"></td>
                                        <td colspan="6" class="TD7"><label for="ALLMSGS">{#ratingSelectAll#}</label></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group">
                                <button name="aktivieren" type="submit" value="{#ratingActive#}" class="btn btn-primary"><i class="fa fa-thumbs-up"></i> {#ratingActive#}</button>
                                <button name="loeschen" type="submit" value="{#ratingDelete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#ratingDelete#}</button>
                            </div>
                        </div>
                    </div>
                </form>
            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="letzten50" class="tab-pane fade {if isset($cTab) && $cTab === 'letzten50'} active in{/if}">
            {if $oBewertungLetzten50_arr && $oBewertungLetzten50_arr|@count > 0}
                {include file='tpl_inc/pagination.tpl' oPagination=$oPagiAktiv cAnchor='letzten50'}
                <form name="letzten50" method="post" action="bewertung.php">
                    {$jtl_token}
                    <input type="hidden" name="bewertung_aktiv" value="1" />
                    <input type="hidden" name="tab" value="letzten50" />
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#ratingLast50#}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th class="check">&nbsp;</th>
                                    <th class="tleft">{#productName#}</th>
                                    <th class="tleft">{#customerName#}</th>
                                    <th class="tleft">{#ratingText#}</th>
                                    <th class="th-5">{#ratingStars#}</th>
                                    <th class="th-6">{#ratingDate#}</th>
                                    <th class="th-7">&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach name=bewertungletzten50 from=$oBewertungLetzten50_arr item=oBewertungLetzten50}
                                    <tr class="tab_bg{$smarty.foreach.bewertungletzten50.iteration%2}">
                                        <td class="check"><input name="kBewertung[]" type="checkbox" value="{$oBewertungLetzten50->kBewertung}"><input type="hidden" name="kArtikel[]" value="{$oBewertungLetzten50->kArtikel}"></td>
                                        <td class="TD2"><a href="../index.php?a={$oBewertungLetzten50->kArtikel}" target="_blank">{$oBewertungLetzten50->ArtikelName}</a></td>
                                        <td class="TD3">{$oBewertungLetzten50->cName}.</td>
                                        <td class="TD4">
                                            <strong>{$oBewertungLetzten50->cTitel}</strong><br>
                                            {$oBewertungLetzten50->cText}
                                            {if !empty($oBewertungLetzten50->cAntwort)}
                                                <blockquote class="review-reply">
                                                    <strong>{#ratingReply#}:</strong><br>
                                                    {$oBewertungLetzten50->cAntwort}
                                                </blockquote>
                                            {/if}
                                        </td>
                                        <td class="tcenter">{$oBewertungLetzten50->nSterne}</td>
                                        <td class="tcenter">{$oBewertungLetzten50->Datum}</td>
                                        <td class="tcenter7 tright" style="min-width: 100px;">
                                            <div class="btn-group">
                                                {if !empty($oBewertungLetzten50->cAntwort)}
                                                    <a href="bewertung.php?a=delreply&kBewertung={$oBewertungLetzten50->kBewertung}&tab=letzten50&token={$smarty.session.jtl_token}"
                                                       class="btn btn-danger" title="{#removeReply#}">
                                                        <i class="fa fa-times-circle-o"></i>
                                                    </a>
                                                {/if}
                                                <a href="bewertung.php?a=editieren&kBewertung={$oBewertungLetzten50->kBewertung}&tab=letzten50&token={$smarty.session.jtl_token}"
                                                   class="btn btn-default" title="{#modify#}">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td class="check"><input name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);"></td>
                                    <td colspan="6" class="TD7"><label for="ALLMSGS3">{#ratingSelectAll#}</label></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <button name="loeschen" type="submit" value="{#ratingDelete#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#deleteSelected#}</button>
                        </div>
                    </div>
                </form>

            {else}
                <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
            {/if}
        </div>
        <div id="artikelbewertung" class="tab-pane fade {if isset($cTab) && $cTab === 'artikelbewertung'} active in{/if}">
            <form name="artikelbewertung" method="post" action="bewertung.php">
                {$jtl_token}
                <div class="input-group col-xs-6" style="float: none;">
                    <span class="input-group-addon">
                        <label for="content">{#ratingcArtNr#}</label>
                    </span>
                    <input type="hidden" name="bewertung_aktiv" value="1" />
                    <input type="hidden" name="tab" value="artikelbewertung" />
                    <input class="form-control" name="cArtNr" type="text" />
                    <span class="input-group-btn">
                        <button name="submitSearch" type="submit" value="{#ratingSearch#}" class="btn btn-info"><i class="fa fa-search"></i> {#ratingSearch#}</button>
                    </span>
                </div>
                {if isset($cArtNr) && $cArtNr|strlen > 0}
                    <div class="alert alert-info">{#ratingSearchedFor#}: {$cArtNr}</div>
                {/if}
                {if $oBewertungAktiv_arr && $oBewertungAktiv_arr|@count > 0}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#ratingsInaktive#}</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th class="th-1">&nbsp;</th>
                                    <th class="tleft">{#productName#}</th>
                                    <th class="tleft">{#customerName#}</th>
                                    <th class="tleft">{#ratingText#}</th>
                                    <th class="th-5">{#ratingStars#}</th>
                                    <th class="th-6">{#ratingDate#}</th>
                                    <th class="th-7">&nbsp;</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach name=bewertungaktiv from=$oBewertungAktiv_arr item=oBewertungAktiv}
                                    <tr class="tab_bg{$smarty.foreach.bewertungaktiv.iteration%2}">
                                        <td class="TD1"><input name="kBewertung[]" type="checkbox" value="{$oBewertungAktiv->kBewertung}"><input type="hidden" name="kArtikel[]" value="{$oBewertungAktiv->kArtikel}"></td>
                                        <td class="TD2"><a href="../index.php?a={$oBewertungAktiv->kArtikel}" target="_blank">{$oBewertungAktiv->ArtikelName}</a></td>
                                        <td class="TD3">{$oBewertungAktiv->cName}.</td>
                                        <td class="TD4"><b>{$oBewertungAktiv->cTitel}</b><br />{$oBewertungAktiv->cText}</td>
                                        <td class="tcenter">{$oBewertungAktiv->nSterne}</td>
                                        <td class="tcenter">{$oBewertungAktiv->Datum}</td>
                                        <td class="tcenter">
                                            <a href="bewertung.php?a=editieren&kBewertung={$oBewertungAktiv->kBewertung}&tab=artikelbewertung"
                                               class="btn btn-default" title="{#modify#}">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td class="TD1"><input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);"></td>
                                    <td colspan="6" class="TD7"><label for="ALLMSGS2">{#ratingSelectAll#}</label></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <button name="loeschen" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {#ratingDelete#}</button>
                        </div>
                    </div>
                {else}
                    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                {/if}
            </form>
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            <form name="einstellen" method="post" action="bewertung.php">
                {$jtl_token}
                <input type="hidden" name="einstellungen" value="1" />
                <input type="hidden" name="tab" value="einstellungen" />
                <div class="settings panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">{#ratingSettings#}</h3>
                    </div>
                    <div class="panel-body">
                        {foreach name=conf from=$oConfig_arr item=oConfig}
                            {if $oConfig->cConf === 'Y'}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <label for="{$oConfig->cWertName}">{$oConfig->cName}
                                            {if $oConfig->cWertName|strpos:"_guthaben"} <span id="EinstellungAjax_{$oConfig->cWertName}"></span>{/if}
                                        </label>
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
                                            <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1"{if $oConfig->cWertName|strpos:"_guthaben"} onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$oConfig->cWertName}', this);"{/if} />
                                        {else}
                                            <input class="form-control" type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1"{if $oConfig->cWertName|strpos:"_guthaben"} onKeyUp="setzePreisAjax(false, 'EinstellungAjax_{$oConfig->cWertName}', this);"{/if} />
                                        {/if}
                                    </span>
                                    {if $oConfig->cBeschreibung}
                                        <span class="input-group-addon">{getHelpDesc cDesc=$oConfig->cBeschreibung cID=$oConfig->kEinstellungenConf}</span>
                                    {/if}
                                </div>
                            {else}
                                {if $oConfig->cBeschreibung}
                                    {getHelpDesc cDesc=$oConfig->cBeschreibung cID=$oConfig->kEinstellungenConf}
                                {/if}
                            {/if}
                        {/foreach}
                    </div>
                    <div class="panel-footer">
                        <button type="submit" value="{#ragingSave#}" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    {foreach name=conf from=$oConfig_arr item=oConfig}
        {if $oConfig->cWertName|strpos:"_guthaben"}
            ioCall('getCurrencyConversion', [0, $('#{$oConfig->cWertName}').val(), 'EinstellungAjax_{$oConfig->cWertName}']);
        {/if}
    {/foreach}
</script>
