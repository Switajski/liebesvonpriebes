<script type="text/javascript">
    var i = 10,
        j = 2,
        file2large = false;

    function addInputRow() {ldelim}
        var row = document.getElementById('formtable').insertRow(i),
                cell_1,
                cell_2,
                input1,
                label,
                myText;
        row.id = '' + i;
        row.valign = 'top';

        cell_1 = row.insertCell(0);
        cell_2 = row.insertCell(1);
        input1 = document.createElement('input');
        input1.type = 'file';
        input1.name = 'Bilder[]';
        input1.className = 'field';
        input1.id = 'Bilder_' + i;
        input1.maxlength = '2097152';
        input1.accept = 'image/*';
        label = document.createElement('label');
        label.setAttribute('for', 'Bilder_' + i);
        myText = document.createTextNode('Bild ' + j + ':');
        label.appendChild(myText);
        cell_1.appendChild(label);
        cell_2.appendChild(input1);
        i += 1;
        j += 1;
    {rdelim}
    {literal}

    $(document).ready(function () {

        $('form input[type=file]').change(function(e){
            $('form div.alert').slideUp();
            var filesize= this.files[0].size;
            {/literal}
            var maxsize = {$nMaxFileSize};
            {literal}
            if (filesize >= maxsize) {
                $(this).after('<div class="alert alert-danger"><i class="fa fa-warning"></i> Die Datei ist gr&ouml;&szlig;er als das Uploadlimit des Servers.</div>').slideDown();
                file2large = true;
            } else {
                $(this).closest('div.alert').slideUp();
                file2large = false;
            }
        });

    });

    function checkfile(e){
        e.preventDefault();
        if (!file2large){
            document.news.submit();
        }
    }
    {/literal}
</script>

{include file='tpl_inc/seite_header.tpl' cTitel=#news# cBeschreibung=#newsDesc#}
<div id="content" class="container-fluid">
    <form name="news" method="post" action="news.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="news_speichern" value="1" />
        <input type="hidden" name="tab" value="aktiv" />
        {if isset($oNews->kNews) && $oNews->kNews > 0}
            <input type="hidden" name="news_edit_speichern" value="1" />
            <input type="hidden" name="kNews" value="{$oNews->kNews}" />
            {if isset($cSeite)}
                <input type="hidden" name="s2" value="{$cSeite}" />
            {/if}
        {/if}
        <div class="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{if isset($oNews->kNews) && $oNews->kNews > 0}{#newsEdit#}{else}{#newAdd#}{/if}</h3>
                </div>
                <div class="table-responsive">
                    <table id="formtable" class="table list">
                        <tr>
                            <td><label for="betreff">{#newsHeadline#} *</label></td>
                            <td>
                                <input class="form-control{if !empty($cPlausiValue_arr.cBetreff)} error{/if}" id="betreff" type="text" name="betreff" value="{if isset($cPostVar_arr.betreff) && $cPostVar_arr.betreff}{$cPostVar_arr.betreff}{elseif isset($oNews->cBetreff)}{$oNews->cBetreff}{/if}" />
                            </td>
                        </tr>
                        <tr>
                            <td><label for="seo">{#newsSeo#}</label></td>
                            <td><input id="seo" name="seo" class="form-control" type="text" value="{if isset($cPostVar_arr.seo) && $cPostVar_arr.seo}{$cPostVar_arr.seo}{elseif isset($oNews->cSeo)}{$oNews->cSeo}{/if}" /></td>
                        </tr>
                        <tr>
                            <td><label for="kkundengruppe">{#newsCustomerGrp#} *</label></td>
                            <td>
                                <select id="kkundengruppe" name="kKundengruppe[]" multiple="multiple" class="form-control{if !empty($cPlausiValue_arr.kKundengruppe_arr)} error{/if}">
                                    <option value="-1"
                                            {if isset($cPostVar_arr.kKundengruppe)}
                                                {foreach name=kkundengruppe from=$cPostVar_arr.kKundengruppe item=kKundengruppe}
                                                    {if $kKundengruppe == "-1"}selected{/if}
                                                {/foreach}
                                            {elseif isset($oNews->kKundengruppe_arr)}
                                                {foreach name=kundengruppen from=$oNews->kKundengruppe_arr item=kKundengruppe}
                                                    {if $kKundengruppe == "-1"}selected{/if}
                                                {/foreach}
                                            {/if}>
                                        Alle
                                    </option>
                                    {foreach name=kundengruppen from=$oKundengruppe_arr item=oKundengruppe}
                                        <option value="{$oKundengruppe->kKundengruppe}"
                                            {if isset($cPostVar_arr.kKundengruppe)}
                                                {foreach name=kkundengruppe from=$cPostVar_arr.kKundengruppe item=kKundengruppe}
                                                    {if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}
                                                {/foreach}
                                            {elseif isset($oNews->kKundengruppe_arr)}
                                                {foreach name=kkundengruppen from=$oNews->kKundengruppe_arr item=kKundengruppe}
                                                    {if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}
                                                {/foreach}
                                            {/if}>{$oKundengruppe->cName}</option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="kNewsKategorie">{#newsCat#} *</label></td>
                            <td>
                                <select id="kNewsKategorie" class="form-control{if !empty($cPlausiValue_arr.kNewsKategorie_arr)} error{/if}" name="kNewsKategorie[]" multiple="multiple">
                                    {foreach name=newskategorie from=$oNewsKategorie_arr item=oNewsKategorie}
                                        <option value="{$oNewsKategorie->kNewsKategorie}"
                                                {if isset($cPostVar_arr.kNewsKategorie)}
                                            {foreach name=kNewsKategorieNews from=$cPostVar_arr.kNewsKategorie item=kNewsKategorieNews}
                                                {if $oNewsKategorie->kNewsKategorie == $kNewsKategorieNews}selected{/if}
                                            {/foreach}
                                                {elseif isset($oNewsKategorieNews_arr)}
                                            {foreach name=kNewsKategorieNews from=$oNewsKategorieNews_arr item=oNewsKategorieNews}
                                                {if $oNewsKategorie->kNewsKategorie == $oNewsKategorieNews->kNewsKategorie}selected{/if}
                                            {/foreach}
                                                {/if}>{$oNewsKategorie->cName}</option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="dGueltigVon">{#newsValidation#} *</label></td>
                            <td>
                                <input class="form-control" id="dGueltigVon" name="dGueltigVon" type="text" value="{if isset($cPostVar_arr.dGueltigVon) && $cPostVar_arr.dGueltigVon}{$cPostVar_arr.dGueltigVon}{elseif isset($oNews->dGueltigVon_de) && $oNews->dGueltigVon_de|strlen > 0}{$oNews->dGueltigVon_de}{else}{$smarty.now|date_format:'%d.%m.%Y %H:%M'}{/if}" />
                            </td>
                        </tr>
                        <tr>
                            <td><label for="nAktiv">{#newsActive#} *</label></td>
                            <td>
                                <select class="form-control" id="nAktiv" name="nAktiv">
                                    <option value="1"{if isset($cPostVar_arr.nAktiv)}{if $cPostVar_arr.nAktiv == 1} selected{/if}{elseif isset($oNews->nAktiv) && $oNews->nAktiv == 1} selected{/if}>Ja</option>
                                    <option value="0"{if isset($cPostVar_arr.nAktiv)}{if $cPostVar_arr.nAktiv == 0} selected{/if}{elseif isset($oNews->nAktiv) && $oNews->nAktiv == 0} selected{/if}>Nein
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cMetaTitle">{#newsMetaTitle#}</label></td>
                            <td>
                                <input class="form-control" id="cMetaTitle" name="cMetaTitle" type="text" value="{if isset($cPostVar_arr.cMetaTitle) && $cPostVar_arr.cMetaTitle}{$cPostVar_arr.cMetaTitle}{elseif isset($oNews->cMetaTitle)}{$oNews->cMetaTitle}{/if}" />
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cMetaDescription">{#newsMetaDescription#}</label></td>
                            <td>
                                <input id="cMetaDescription" class="form-control" name="cMetaDescription" type="text" value="{if isset($cPostVar_arr.cMetaDescription) && $cPostVar_arr.cMetaDescription}{$cPostVar_arr.cMetaDescription}{elseif isset($oNews->cMetaDescription)}{$oNews->cMetaDescription}{/if}" />
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cMetaKeywords">{#newsMetaKeywords#}</label></td>
                            <td>
                                <input class="form-control" id="cMetaKeywords" name="cMetaKeywords" type="text" value="{if isset($cPostVar_arr.cMetaKeywords) && $cPostVar_arr.cMetaKeywords}{$cPostVar_arr.cMetaKeywords}{elseif isset($oNews->cMetaKeywords)}{$oNews->cMetaKeywords}{/if}" />
                            </td>
                        </tr>
                        {if $oPossibleAuthors_arr|count > 0}
                            <tr>
                                <td><label for="kAuthor">{#newsAuthor#}</label></td>
                                <td>
                                    <select class="form-control" id="kAuthor" name="kAuthor">
                                        <option value="0">Autor ausw&auml;hlen</option>
                                        {foreach name=author from=$oPossibleAuthors_arr item=oPossibleAuthor}
                                            <option value="{$oPossibleAuthor->kAdminlogin}"{if isset($cPostVar_arr.nAuthor)}{if isset($cPostVar_arr.nAuthor) && $cPostVar_arr.nAuthor == $oPossibleAuthor->kAdminlogin} selected="selected"{/if}{elseif isset($oAuthor->kAdminlogin) && $oAuthor->kAdminlogin == $oPossibleAuthor->kAdminlogin} selected="selected"{/if}>{$oPossibleAuthor->cName}</option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                        {/if}
                        <tr>
                            <td><label for="previewImage">{#newsPreview#}</label></td>
                            <td valign="top">
                                {if !empty($oNews->cPreviewImage)}
                                    <img src="{$shopURL}/{$oNews->cPreviewImage}" alt="" height="20" width="20" class="preview-image left" style="margin-right: 10px;" />
                                {/if}
                                <input id="previewImage" name="previewImage" type="file" maxlength="2097152" accept="image/*" />
                                <input name="previewImage" type="hidden" value="{if !empty($oNews->cPreviewImage)}{$oNews->cPreviewImage}{/if}" />
                            </td>
                        </tr>
                        <tr>
                            <td><label for="Bilder_0">{#newsPictures#}</label></td>
                            <td valign="top">
                                <input id="Bilder_0" name="Bilder[]" type="file" maxlength="2097152" accept="image/*" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <button name="hinzufuegen" type="button" value="{#newsPicAdd#}" onclick="addInputRow();" class="btn btn-primary add">{#newsPicAdd#}</button>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="newstext">{#newsText#} *</label></td>
                            <td>
                                <textarea id="newstext" class="ckeditor" name="text" rows="15" cols="60">{if isset($cPostVar_arr.text) && $cPostVar_arr.text}{$cPostVar_arr.text}{elseif isset($oNews->cText)}{$oNews->cText}{/if}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="previewtext">{#newsPreviewText#}</label></td>
                            <td>
                                <textarea id="previewtext" class="ckeditor" name="cVorschauText" rows="15" cols="60">{if isset($cPostVar_arr.cVorschauText) && $cPostVar_arr.cVorschauText}{$cPostVar_arr.cVorschauText}{elseif isset($oNews->cVorschauText)}{$oNews->cVorschauText}{/if}</textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><label>{#newsPics#}</label></td>
                            {if isset($oDatei_arr) && $oDatei_arr|@count > 0}
                                <td valign="top">
                                {foreach name=bilder from=$oDatei_arr item=oDatei}
                                    <div class="well col-xs-3">
                                        <div class="thumbnail">{$oDatei->cURL}</div>
                                        <label>Link: </label>
                                        <div class="input-group">
                                            <input class="form-control" type="text" disabled="disabled" value="$#{$oDatei->cName}#$">
                                            <div class="input-group-addon">
                                                <a href="news.php?news=1&news_editieren=1&kNews={$oNews->kNews}&delpic={$oDatei->cName}&token={$smarty.session.jtl_token}" title="{#delete#}"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                                </td>
                            {else}
                            <td valign="top"></td>
                            {/if}
                        </tr>
                    </table>
                </div>
                <div class="panel-body">
                    <div class="alert alert-info">{#newsMandatoryFields#}</div>
                </div>
                <div class="panel-footer">
                    <span class="btn-group">
                        <button name="speichern" type="button" value="{#newsSave#}" onclick="checkfile(event);" class="btn btn-primary"><i class="fa fa-save"></i> {#newsSave#}</button>
                        {if isset($oNews->kNews) && $oNews->kNews > 0}
                            <button type="submit" name="continue" value="1" class="btn btn-default" id="save-and-continue">{#newsSave#} und weiter bearbeiten</button>
                        {/if}
                        <a class="btn btn-danger" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-exclamation"></i> Abbrechen</a>
                    </span>
                </div>
            </div>
        </div>
    </form>
    {if isset($oNews->kNews) && $oNews->kNews > 0}
        {getRevisions type='news' key=$oNews->kNews show=['cText'] secondary=false data=$oNews}
    {/if}
</div>