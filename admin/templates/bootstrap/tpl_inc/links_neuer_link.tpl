<script type="text/javascript">
    function append_file_selector() {ldelim}
        var file_input = $('<input type="file" name="Bilder[]" maxlength="2097152" accept="image/*" />');
        var container = $('<p class="multi_input vmiddle"><a href="#" title="Entfernen"><img src="{$currentTemplateDir}/gfx/layout/delete.png" class="vmiddle" /></a></p>').prepend(file_input);
        $('#file_input_wrapper').append(container);
        $(container).find('img').bind('click', function () {ldelim}
            $(file_input).parent().remove();
            return false;
        {rdelim});
        $(file_input).trigger('click');
        return false;
    {rdelim}

    {literal}
    $(function () {
        $('#lang').change(function () {
            var iso = $('#lang option:selected').val();
            $('.iso_wrapper').slideUp();
            $('#iso_' + iso).slideDown();
            return false;
        });

        $('input[name="nLinkart"]').change(function () {
            var lnk = $('input[name="nLinkart"]:checked').val();
            if (lnk == '1') {
                $('#option_isActive').slideDown("slow");
            } else {
                $('#option_isActive').slideUp("slow");
                $('#option_isActive select').val(1);
            }
        }).trigger('change');

        $('#content_template_type ul li a').click(function () {

            $('#content_template_type ul li a').parent().removeClass('active');
            $(this).parent().addClass('active');

            var tpl = $(this).parent().attr('rel');
            if (tpl.length == 0)
                tpl = 'default';

            xajax_getContentTemplate(tpl);

            return false;
        });
    });

    function link_dynamic_init() {
        $('.ckeditor_dyn').each(function (idx, item) {
            set_editor($(item).attr('id'));
        });
    }

    function set_editor(id) {
        var instance = CKEDITOR.instances[id];
        if (instance)
            CKEDITOR.remove(instance);
        CKEDITOR.replace(id);
    }
    {/literal}
</script>
{if isset($Link->kLink) && isset($Link->cName)}
    {assign var=description value=$Link->cName|cat:' (ID '|cat:$Link->kLink|cat:')'}
{else}
    {assign var=description value=''}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=#newLinks# cBeschreibung=$description}
<div id="content" class="container-fluid">
    <div id="settings">
        <form id="create_link" name="link_erstellen" method="post" action="links.php" enctype="multipart/form-data">
            {$jtl_token}
            <input type="hidden" name="neu_link" value="1" />
            <input type="hidden" name="kLinkgruppe" value="{if isset($Link->kLinkgruppe)}{$Link->kLinkgruppe}{/if}" />
            <input type="hidden" name="kLink" value="{if isset($Link->kLink)}{$Link->kLink}{/if}" />
            <input type="hidden" name="kPlugin" value="{if isset($Link->kPlugin)}{$Link->kPlugin}{/if}" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Allgemein</h3>
                </div>
                <div class="panel-body">
                    <div class="input-group{if isset($xPlausiVar_arr.cName)} error{/if}">
                        <span class="input-group-addon">
                            <label for="cName">Name{if isset($xPlausiVar_arr.cName)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                        </span>
                        <input type="text" name="cName" id="cName" class="form-control{if isset($xPlausiVar_arr.cName)} fieldfillout{/if}" value="{if isset($xPostVar_arr.cName) && $xPostVar_arr.cName}{$xPostVar_arr.cName}{elseif isset($Link->cName)}{$Link->cName}{/if}" tabindex="1" />
                    </div>
                    <div class="input-group{if isset($xPlausiVar_arr.nLinkart)} error{/if}">
                        <span class="input-group-addon">
                            <label>{#linkType#}{if isset($xPlausiVar_arr.nLinkart)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                        </span>
                        <div class="input-group-wrap">
                        {if isset($Link->kPlugin) && $Link->kPlugin > 0}
                            <p class="multi_input">
                                <input type="hidden" name="nLinkart" value="25" />
                                <input type="radio" id="nLink3" name="nLinkart" checked="checked" disabled="disabled" />
                                <label for="nLink3">{#linkToSpecalPage#}</label>
                                <select id="nLink3" name="nSpezialseite" disabled="disabled">
                                    <option selected="selected">Plugin</option>
                                </select>
                            </p>
                        {else}
                            <p class="multi_input" style="margin-top: 10px;">
                                <input type="radio" id="nLink1" name="nLinkart" value="1" tabindex="2" {if isset($Link->nLinkart) && $Link->nLinkart==1}checked{/if} />
                                <label for="nLink1">{#linkWithOwnContent#}</label>
                            </p>
                            <p class="multi_input">
                                <input type="radio" id="nLink2" name="nLinkart" value="2" onclick="$('#nLinkInput2').val('http://')" tabindex="3" {if isset($Link->nLinkart) && $Link->nLinkart==2}checked{/if} />
                                <label for="nLink2">{#linkToExternalURL#}</label>
                                <input class="form-control" type="text" name="cURL" value="{if isset($Link->cURL)}{$Link->cURL}{/if}" id="nLink2" style="border:1px solid #ccc;margin-right:20px;" />
                            </p>
                            <p class="multi_input" style="margin-bottom: 10px;">
                                <input type="radio" id="nLink3" name="nLinkart" value="3" {if isset($Link->nLinkart) && $Link->nLinkart>2}checked{/if} />
                                <label for="nLink3">{#linkToSpecalPage#}</label>
                                <select id="nLink3" name="nSpezialseite">
                                    <option value="0">{#choose#}</option>
                                    {foreach name=spezialseiten from=$oSpezialseite_arr item=oSpezialseite}
                                        <option value="{$oSpezialseite->nLinkart}" {if isset($Link->nLinkart) && $Link->nLinkart == $oSpezialseite->nLinkart}selected{/if}>{$oSpezialseite->cName}</option>
                                    {/foreach}
                                </select>
                            </p>
                        {/if}
                        </div>
                    </div>
                    <div class="input-group{if isset($xPlausiVar_arr.cKundengruppen)} error{/if}">
                        <span class="input-group-addon">
                            <label for="cKundengruppen">{#restrictedToCustomerGroups#}{if isset($xPlausiVar_arr.cKundengruppen)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                        </span>
                        <select name="cKundengruppen[]" class="form-control{if isset($xPlausiVar_arr.cKundengruppen)} fieldfillout{/if}" multiple="multiple" size="6" id="cKundengruppen">
                            <option value="-1"{if isset($Link->kLink) && $Link->kLink > 0 && isset($gesetzteKundengruppen[0]) && $gesetzteKundengruppen[0]} selected{elseif isset($xPostVar_arr.cKundengruppen)}
                                {foreach name=postkndgrp from=$xPostVar_arr.cKundengruppen item=cPostKndGrp}
                                    {if $cPostKndGrp|strlen > 0 && $cPostKndGrp == "-1"}selected{/if}
                                {/foreach}
                                    {elseif !isset($Link->kLink) || !$Link->kLink}selected{/if}>{#all#}</option>

                            {foreach name=kdgrp from=$kundengruppen item=kundengruppe}
                                {assign var='kKundengruppe' value=$kundengruppe->kKundengruppe}
                                {assign var=postkndgrp value='0'}
                                    {if isset($xPostVar_arr.cKundengruppen)}
                                    {foreach name=postkndgrp from=$xPostVar_arr.cKundengruppen item=cPostKndGrp}
                                        {if $cPostKndGrp == $kKundengruppe}{assign var=postkndgrp value='1'}{/if}
                                    {/foreach}
                                {/if}
                                <option value="{$kundengruppe->kKundengruppe}" {if (isset($gesetzteKundengruppen[$kKundengruppe]) && $gesetzteKundengruppen[$kKundengruppe]) || (isset($postkndgrp) && $postkndgrp == 1)}selected{/if}>{$kundengruppe->cName}</option>
                            {/foreach}
                        </select>
                        <span class="input-group-addon">{getHelpDesc cDesc=#multipleChoice#}</span>
                    </div>
                    <div class="input-group" id="option_isActive">
                        <span class="input-group-addon"><label for="bIsActive">{#active#}</label></span>
                        <div class="input-group-wrap">
                            <select class="form-control" type="selectbox" name="bIsActive" id="bIsActive">
                                <option value="1" {if (isset($Link->bIsActive) && $Link->bIsActive === '1') || (isset($xPostVar_arr.bIsActive) && $xPostVar_arr.bIsActive === '1')}selected{/if}>Aktiviert</option>
                                <option value="0" {if (isset($Link->bIsActive) && $Link->bIsActive === '0') || (isset($xPostVar_arr.bIsActive) && $xPostVar_arr.bIsActive === '0')}selected{/if}>Deaktiviert</option>
                            </select>
                        </div>
                    </div>
                    {if !isset($Link->nLinkart) || $Link->nLinkart != LINKTYP_LOGIN}
                    <div class="input-group">
                        <span class="input-group-addon"><label for="cSichtbarNachLogin">{#visibleAfterLogin#}</label></span>
                        <div class="input-group-wrap">
                            <input class="form-control2" type="checkbox" name="cSichtbarNachLogin" id="cSichtbarNachLogin" value="Y" {if (isset($Link->cSichtbarNachLogin) && $Link->cSichtbarNachLogin === 'Y') || (isset($xPostVar_arr.cSichtbarNachLogin) && $xPostVar_arr.cSichtbarNachLogin)}checked{/if} />
                        </div>
                    </div>
                    {/if}
                    <div class="input-group">
                        <span class="input-group-addon"><label for="bSSL">SSL</label></span>
                        <span class="input-group-wrap">
                            <select id="bSSL" class="form-control" name="bSSL">
                                {*<option value="1"{if (isset($Link->bSSL) && $Link->bSSL == 1) || (isset($xPostVar_arr.bSSL) && $xPostVar_arr.bSSL == 1)} selected="selected"{/if}>optional</option>*}
                                <option value="0"{if (isset($Link->bSSL) && ($Link->bSSL == 0 || $Link->bSSL == 1)) || (isset($xPostVar_arr.bSSL) && ($xPostVar_arr.bSSL == 0 || $xPostVar_arr.bSSL == 1))} selected="selected"{/if}>standard</option>
                                <option value="2"{if (isset($Link->bSSL) && $Link->bSSL == 2) || (isset($xPostVar_arr.bSSL) && $xPostVar_arr.bSSL == 2)} selected="selected"{/if}>erzwungen</option>
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="cNoFollow">{#noFollow#}</label></span>
                        <div class="input-group-wrap">
                            <input class="form-control2" type="checkbox" name="cNoFollow" id="cNoFollow" value="Y" {if (isset($Link->cNoFollow) && $Link->cNoFollow === 'Y') || (isset($xPostVar_arr.cNoFollow) && $xPostVar_arr.cNoFollow)}checked{/if} />
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="nSort">{#sortNo#}</label></span>
                        <input class="form-control" type="text" name="nSort" id="nSort" value="{if isset($xPostVar_arr.nSort) && $xPostVar_arr.nSort}{$xPostVar_arr.nSort}{elseif isset($Link->nSort)}{$Link->nSort}{/if}" tabindex="6" />
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="Bilder_0">Bilder</label></span>
                        <span class="input-group-wrap">
                            <div id="file_input_wrapper">
                                <p class="multi_input">
                                    <input class="form-control-upload" id="Bilder_0" name="Bilder[]" type="file" maxlength="2097152" accept="image/*" />
                                </p>
                            </div>
                        </span>
                        <span class="input-group-btn input-group-addon">
                            <button type="button" title="{#linkPicAdd#}" name="hinzufuegen" value="{#linkPicAdd#}"
                                    onclick="return append_file_selector();" class="btn btn-info">
                                <i class="fa fa-plus"></i>
                            </button>
                        </span>

                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label>{#linkPics#}</label></span>
                        <div class="input-group-wrap">
                        {if isset($cDatei_arr)}
                            {foreach name=bilder from=$cDatei_arr item=cDatei}
                                <span class="block tcenter vmiddle">
                                    <a href="links.php?kLink={$Link->kLink}&token={$smarty.session.jtl_token}&delpic=1&cName={$cDatei->cNameFull}{if isset($Link->kPlugin) && $Link->kPlugin > 0}{$Link->kPlugin}{/if}"><img src="{$currentTemplateDir}/gfx/layout/remove.png" alt="delete"></a>
                                    $#{$cDatei->cName}#$
                                    <div>{$cDatei->cURL}</div>
                                </span>
                            {/foreach}
                        {/if}
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="lang">Sprache</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" name="cISO" id="lang">
                                {foreach name=sprachen from=$sprachen item=sprache}
                                    <option value="{$sprache->cISO}" {if $sprache->cShopStandard === 'Y'}selected="selected"{/if}>{$sprache->cNameDeutsch} {if $sprache->cShopStandard === 'Y'}(Standard){/if}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="bIsFluid">{#bIsFluidText#}</label></span>
                        <div class="input-group-wrap">
                            <input class="form-control2" type="checkbox" name="bIsFluid" id="bIsFluid" value="1" {if (isset($Link->bIsFluid) && $Link->bIsFluid === '1') || (isset($xPostVar_arr.bIsFluid) && $xPostVar_arr.bIsFluid === '1')}checked{/if} />
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="cIdentifier">{#cIdentifierText#}</label></span>
                        <div class="input-group-wrap">
                            <input class="form-control" type="text" name="cIdentifier" id="cIdentifier" value="{if isset($Link->cIdentifier)}{$Link->cIdentifier}{elseif isset($xPostVar_arr.bIsFluid)}$xPostVar_arr.bIsFluid{/if}" />
                        </div>
                    </div>
                </div>
            </div>

            {foreach name=sprachen from=$sprachen item=sprache}
                {assign var="cISO" value=$sprache->cISO}
                <div id="iso_{$cISO}" class="iso_wrapper{if $sprache->cShopStandard != "Y"} hidden-soft{/if}">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Meta/Seo ({$sprache->cNameDeutsch})</h3>
                        </div>
                        <div class="panel-body">
                            <div class="input-group">
                                <span class="input-group-addon"><label for="cName_{$cISO}">{#showedName#}</label></span>
                                {assign var=cName_ISO value="cName_"|cat:$cISO}
                                <input class="form-control" type="text" name="cName_{$cISO}" id="cName_{$cISO}" value="{if isset($xPostVar_arr.$cName_ISO) && $xPostVar_arr.$cName_ISO}{$xPostVar_arr.$cName_ISO}{elseif isset($Linkname[$cISO])}{$Linkname[$cISO]}{/if}" tabindex="7" />
                            </div>
                            <div class="input-group">
                                <span class="input-group-addon"><label for="cSeo_{$cISO}">{#linkSeo#}</label></span>
                                {assign var=cSeo_ISO value="cSeo_"|cat:$cISO}
                                <input class="form-control" type="text" name="cSeo_{$cISO}" id="cSeo_{$cISO}" value="{if isset($xPostVar_arr.$cSeo_ISO) && $xPostVar_arr.$cSeo_ISO}{$xPostVar_arr.$cSeo_ISO}{elseif isset($Linkseo[$cISO])}{$Linkseo[$cISO]}{/if}" tabindex="7" />
                            </div>
                            {assign var=cTitle_ISO value="cTitle_"|cat:$cISO}
                            <div class="input-group">
                                <span class="input-group-addon"><label for="cTitle_{$cISO}">{#linkTitle#}</label></span>
                                <span class="input-group-wrap">
                                    <input class="form-control" type="text" name="cTitle_{$cISO}" id="cTitle_{$cISO}" value="{if isset($xPostVar_arr.$cTitle_ISO) && $xPostVar_arr.$cTitle_ISO}{$xPostVar_arr.$cTitle_ISO}{elseif isset($Linktitle[$cISO])}{$Linktitle[$cISO]}{/if}" tabindex="8" />
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#titleDesc#}</span>
                            </div>
                            <div class="input-group">
                                {assign var=cContent_ISO value="cContent_"|cat:$cISO}
                                <span class="input-group-addon"><label for="cContent_{$cISO}">{#linkContent#}</label></span>
                                <span class="input-group-wrap">
                                    <textarea class="form-control ckeditor" id="cContent_{$cISO}" name="cContent_{$cISO}" rows="10" cols="40">{if isset($xPostVar_arr.$cContent_ISO) && $xPostVar_arr.$cContent_ISO}{$xPostVar_arr.$cContent_ISO}{elseif isset($Linkcontent[$cISO])}{$Linkcontent[$cISO]}{/if}</textarea>
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#titleDesc#}</span>
                            </div>
                            <div class="input-group">
                                {assign var=cMetaTitle_ISO value="cMetaTitle_"|cat:$cISO}
                                <span class="input-group-addon"><label for="cMetaTitle_{$cISO}">{#metaTitle#}</label></span>
                                <span class="input-group-wrap">
                                    <input class="form-control" type="text" name="cMetaTitle_{$cISO}" id="cMetaTitle_{$cISO}" value="{if isset($xPostVar_arr.$cMetaTitle_ISO) && $xPostVar_arr.$cMetaTitle_ISO}{$xPostVar_arr.$cMetaTitle_ISO}{elseif isset($Linkmetatitle[$cISO])}{$Linkmetatitle[$cISO]}{/if}" tabindex="9" />
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#metaTitleDesc#}</span>
                            </div>
                            <div class="input-group">
                            {assign var=cMetaKeywords_ISO value="cMetaKeywords_"|cat:$cISO}
                                <span class="input-group-addon"><label for="cMetaKeywords_{$cISO}">{#metaKeywords#}</label></span>
                                <span class="input-group-wrap">
                                    <input class="form-control" type="text" name="cMetaKeywords_{$cISO}" id="cMetaKeywords_{$cISO}" value="{if isset($xPostVar_arr.$cMetaKeywords_ISO) && $xPostVar_arr.$cMetaKeywords_ISO}{$xPostVar_arr.$cMetaKeywords_ISO}{elseif isset($Linkmetakeys[$cISO])}{$Linkmetakeys[$cISO]}{/if}" tabindex="9" />
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#metaKeywordsDesc#}</span>
                            </div>
                            <div class="input-group">
                                {assign var=cMetaDescription_ISO value="cMetaDescription_"|cat:$cISO}
                                <span class="input-group-addon"><label for="cMetaDescription_{$cISO}">{#metaDescription#}</label></span>
                                <span class="input-group-wrap">
                                    <input class="form-control" type="text" name="cMetaDescription_{$cISO}" id="cMetaDescription_{$cISO}" value="{if isset($xPostVar_arr.$cMetaDescription_ISO) && $xPostVar_arr.$cMetaDescription_ISO}{$xPostVar_arr.$cMetaDescription_ISO}{elseif isset($Linkmetadesc[$cISO])}{$Linkmetadesc[$cISO]}{/if}" tabindex="9" />
                                </span>
                                <span class="input-group-addon">{getHelpDesc cDesc=#metaDescriptionDesc#}</span>
                            </div>
                        </div>
                    </div>
                </div>
            {/foreach}
            <div class="panel{if isset($Link->kLink)} btn-group{/if}">
                <button type="submit" value="{#newLinksSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#newLinksSave#}</button>
                {if isset($Link->kLink)}<button type="submit" name="continue" value="1" class="btn btn-default" id="save-and-continue">{#newLinksSave#} und weiter bearbeiten</button>{/if}
            </div>
        </form>
        {if isset($Link->kLink)}
            {getRevisions type='link' key=$Link->kLink show=['cContent'] secondary=true data=$Linkcontent}
        {/if}
    </div>
</div>