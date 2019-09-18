<script type="text/javascript">
    var file2large = false;
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

{include file='tpl_inc/seite_header.tpl' cTitel=#newsCat#}
<div id="content">
    <form name="news" method="post" action="news.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="news" value="1" />
        <input type="hidden" name="news_kategorie_speichern" value="1" />
        <input type="hidden" name="tab" value="kategorien" />
        {if isset($oNewsKategorie->kNewsKategorie) && $oNewsKategorie->kNewsKategorie > 0}
            <input type="hidden" name="newskategorie_edit_speichern" value="1" />
            <input type="hidden" name="kNewsKategorie" value="{$oNewsKategorie->kNewsKategorie}" />
            {if isset($cSeite)}
                <input type="hidden" name="s3" value="{$cSeite}" />
            {/if}
        {/if}
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{if isset($oNewsKategorie->kNewsKategorie) && $oNewsKategorie->kNewsKategorie > 0}{#newsCatNew#}{else}{#newsCatAdd#}{/if}</h3>
            </div>
            <div class="table-responsive">
                <table class="list table" id="formtable">
                    <tr>
                        <td><label for="cName">{#newsCatName#}</label></td>
                        <td>
                            <input class="form-control{if !empty($cPlausiValue_arr.cName)} error{/if}" id="cName" name="cName" type="text" value="{if isset($cPostVar_arr.cName)}{$cPostVar_arr.cName}{elseif isset($oNewsKategorie->cName)}{$oNewsKategorie->cName}{/if}" />{if isset($cPlausiValue_arr.cName) && $cPlausiValue_arr.cName == 2} {#newsAlreadyExists#}{/if}
                        </td>
                    </tr>
                    <tr>
                        <td><label for="cSeo">{#newsSeo#}</label></td>
                        <td>
                            <input class="form-control{if !empty($cPlausiValue_arr.cSeo)} error{/if}" id="cSeo" name="cSeo" type="text" value="{if isset($cPostVar_arr.cSeo)}{$cPostVar_arr.cSeo}{elseif isset($oNewsKategorie->cSeo)}{$oNewsKategorie->cSeo}{/if}" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="nSort">{#newsCatSort#}</label></td>
                        <td>
                            <input class="form-control{if !empty($cPlausiValue_arr.nSort)} error{/if}" id="nSort" name="nSort" type="text" value="{if isset($cPostVar_arr.nSort)}{$cPostVar_arr.nSort}{elseif isset($oNewsKategorie->nSort)}{$oNewsKategorie->nSort}{/if}" style="width: 60px;" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="nAktiv">{#newsActive#}</label></td>
                        <td>
                            <select class="form-control" id="nAktiv" name="nAktiv">
                                <option value="1"{if (isset($cPostVar_arr.nAktiv) && $cPostVar_arr.nAktiv == "1") || (isset($oNewsKategorie->nAktiv) && $oNewsKategorie->nAktiv == 1)} selected{/if}>
                                    Ja
                                </option>
                                <option value="0"{if (isset($cPostVar_arr.nAktiv) && $cPostVar_arr.nAktiv == "0") || (isset($oNewsKategorie->nAktiv) && $oNewsKategorie->nAktiv == 0)} selected{/if}>
                                    Nein
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="cMetaTitle">{#newsMetaTitle#}</label></td>
                        <td>
                            <input class="form-control{if !empty($cPlausiValue_arr.cMetaTitle)} error{/if}" id="cMetaTitle" name="cMetaTitle" type="text" value="{if isset($cPostVar_arr.cMetaTitle)}{$cPostVar_arr.cMetaTitle}{elseif isset($oNewsKategorie->cMetaTitle)}{$oNewsKategorie->cMetaTitle}{/if}" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="cMetaDescription">{#newsMetaDescription#}</label></td>
                        <td>
                            <input class="form-control{if !empty($cPlausiValue_arr.cMetaDescription)} error{/if}" id="cMetaDescription" name="cMetaDescription" type="text" value="{if isset($cPostVar_arr.cMetaDescription)}{$cPostVar_arr.cMetaDescription}{elseif isset($oNewsKategorie->cMetaDescription)}{$oNewsKategorie->cMetaDescription}{/if}" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="previewImage">{#newsPreview#}</label></td>
                        <td valign="top">
                            {if !empty($oNewsKategorie->cPreviewImage)}
                                <img src="{$shopURL}/{$oNewsKategorie->cPreviewImage}" alt="" height="20" width="20" class="preview-image left" style="margin-right: 10px;" />
                            {/if}
                            <input id="previewImage" name="previewImage" type="file" maxlength="2097152" accept="image/*" />
                            <input name="previewImage" type="hidden" value="{if !empty($oNewsKategorie->cPreviewImage)}{$oNewsKategorie->cPreviewImage}{/if}" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="cBeschreibung">{#newsCatDesc#}</label></td>
                        <td>
                            <textarea id="cBeschreibung" class="ckeditor" name="cBeschreibung" rows="15" cols="60">{if isset($cPostVar_arr.cBeschreibung)}{$cPostVar_arr.cBeschreibung}{elseif isset($oNewsKategorie->cBeschreibung)}{$oNewsKategorie->cBeschreibung}{/if}</textarea>
                        </td>
                    </tr>
                    <tr>
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
                                                <a href="news.php?news=1&newskategorie_editieren=1&kNewsKategorie={$oNewsKategorie->kNewsKategorie}&delpic={$oDatei->cName}&token={$smarty.session.jtl_token}" title="{#delete#}"><i class="fa fa-trash"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                {/foreach}
                            </td>
                        {else}
                            <td valign="top"></td>
                        {/if}
                    </tr>
                    </tr>
                </table>
            </div>
            <div class="panel-footer">
                <span class="btn-group">
                    <button name="speichern" type="button" value="{#newsSave#}" onclick="document.news.submit();" class="btn btn-primary"><i class="fa fa-save"></i> {#newsSave#}</button>
                    <a class="btn btn-danger" href="news.php{if isset($cBackPage)}?{$cBackPage}{elseif isset($cTab)}?tab={$cTab}{/if}"><i class="fa fa-exclamation"></i> Abbrechen</a>
                </span>
            </div>
        </div>
    </form>
</div>