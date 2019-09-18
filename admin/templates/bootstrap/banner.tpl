{include file='tpl_inc/header.tpl' bForceFluid=($cAction === 'area')}
{config_load file="$lang.conf" section="banner"}
{include file='tpl_inc/seite_header.tpl' cTitel=#banner# cBeschreibung=#bannerDesc# cDokuURL=#bannerURL#}

<div id="content">
    {if $cFehler}
        {if isset($cPlausi_arr.vDatum)}
            <div class="alert alert-danger">{if $cPlausi_arr.vDatum == 1}Konnte Ihre Eingabe f&uuml;r das 'Aktiv von Datum' nicht verarbeiten.{/if}</div>
        {/if}
        {if isset($cPlausi_arr.bDatum)}
            <div class="alert alert-danger">
                {if $cPlausi_arr.bDatum == 1}
                    Konnte Ihre Eingabe f&uuml;r das 'Aktiv bis Datum' nicht verarbeiten.
                {elseif $cPlausi_arr.bDatum == 2}
                    Das Datum bis wann ein Banner aktiv ist muss gr&ouml;&szlig;er sein als das 'Aktiv von Datum'.
                {/if}
            </div>
        {/if}
        {if isset($cPlausi_arr.oFile)}
            <div class="alert alert-danger"><i class="fa fa-warning"></i> Die Bilddatei ist zu gro&szlig;.</div>
        {/if}
    {/if}

    {if $cAction === 'edit' || $cAction === 'new'}
    <script type="text/javascript">
        var file2large = false;

        function checkfile(e){
            e.preventDefault();
            if (!file2large){
                document.banner.submit();
            }
        }

        $(document).ready(function () {
            $('#nSeitenTyp').change(filterConfigUpdate);
            $('#cKey').change(filterConfigUpdate);

            filterConfigUpdate();

            $('form #oFile').change(function(e){
                $('form div.alert').slideUp();
                var filesize= this.files[0].size;
                var maxsize = {$nMaxFileSize};
                if (filesize >= maxsize) {
                    $('.input-group.file-input')
                        .after('<div class="alert alert-danger"><i class="fa fa-warning"></i> Die Datei ist gr&ouml;&szlig;er als das Uploadlimit des Servers.</div>')
                        .slideDown();
                    file2large = true;
                } else {
                    $('form div.alert').slideUp();
                    file2large = false;
                }
            });

        });

        function filterConfigUpdate()
        {
            var $nSeitenTyp = $('#nSeitenTyp');
            var $type2      = $('#type2');
            var $nl         = $('.nl');
            var $cKey       = $('#cKey');

            $nl.hide();
            $('.key').hide();
            $type2.hide();

            switch ($nSeitenTyp.val()) {
                case '1':
                    $nl.show();
                    $('#keykArtikel').show();
                    $cKey.val('');
                    break;
                case '2':
                    $type2.show();
                    if ($cKey.val() !== '') {
                        $('#key' + $cKey.val()).show();
                        $nl.show();
                    }
                    break;
                case '31':
                    $nl.show();
                    $('#keykLink').show();
                    $cKey.val('');
                    break;
                default:
                    $cKey.val('');
                    break;
            }
        }
    </script>
    <div id="settings">
        <form name="banner" action="banner.php" method="post" enctype="multipart/form-data" onsubmit="checkfile(event);">
            {$jtl_token}
            <input type="hidden" name="action" value="{$cAction}" />
            {if $cAction === 'edit'}
                <input type="hidden" name="kImageMap" value="{$oBanner->kImageMap}" />
            {/if}

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Allgemein</h3>
                </div>
                <div class="panel-body">
                    <div class="input-group">
                        <span class="input-group-addon"><label for="cName">Interner Name *</label></span>
                        <input class="form-control" type="text" name="cName" id="cName" value="{if isset($cName)}{$cName}{elseif isset($oBanner->cTitel)}{$oBanner->cTitel}{/if}" />
                    </div>
                    <div class="input-group file-input">
                        <span class="input-group-addon"><label for="oFile">Banner *</label></span>
                        <input class="form-control" id="oFile" type="file" name="oFile" />
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="cPath">&raquo; vorhandene Datei w&auml;hlen</label></span>
                        <span class="input-group-wrap">
                        {if $cBannerFile_arr|@count > 0}
                            <select id="cPath" name="cPath" class="form-control">
                                <option value="">Banner w&auml;hlen</option>
                                {foreach from=$cBannerFile_arr item=cBannerFile}
                                    <option value="{$cBannerFile}" {if (isset($oBanner->cBildPfad) && $cBannerFile == $oBanner->cBildPfad) || (isset($oBanner->cBild) && $cBannerFile == $oBanner->cBild)}selected="selected"{/if}>{$cBannerFile}</option>
                                {/foreach}
                            </select>
                        {else}
                            Kein Banner im Ordner <strong>{$cBannerLocation}</strong> vorhanden
                        {/if}
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="vDatum">Aktiv von</label></span>
                        <input class="form-control" type="text" name="vDatum" id="vDatum" value="{if isset($vDatum) && $vDatum > 0}{$vDatum|date_format:"%d.%m.%Y"}{elseif isset($oBanner->vDatum) && $oBanner->vDatum > 0}{$oBanner->vDatum|date_format:"%d.%m.%Y"}{/if}" />
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="bDatum">Aktiv bis</label></span>
                        <input class="form-control" type="text" name="bDatum" id="bDatum" value="{if isset($bDatum) && $bDatum > 0}{$bDatum|date_format:"%d.%m.%Y"}{elseif isset($oBanner->bDatum) && $oBanner->bDatum > 0}{$oBanner->bDatum|date_format:"%d.%m.%Y"}{/if}" />
                    </div>
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->

            {* extensionpoint begin *}

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Anzeigeoptionen</h3>
                </div>
                <div class="panel-body">
                    <div class="input-group">
                        <span class="input-group-addon"><label for="kSprache">Sprache</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="kSprache" name="kSprache">
                                <option value="0">Alle</option>
                                {foreach from=$oSprachen_arr item=oSprache}
                                    <option value="{$oSprache->kSprache}" {if isset($kSprache) && $kSprache == $oSprache->kSprache}selected="selected" {elseif isset($oExtension->kSprache) && $oExtension->kSprache == $oSprache->kSprache}selected="selected"{/if}>{$oSprache->cNameDeutsch}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="kKundengruppe">Kundengruppe</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="kKundengruppe" name="kKundengruppe">
                                <option value="0">Alle</option>
                                {foreach from=$oKundengruppe_arr item=oKundengruppe}
                                    <option value="{$oKundengruppe->getKundengruppe()}" {if isset($kKundengruppe) && $kKundengruppe == $oKundengruppe->getKundengruppe()}selected="selected" {elseif isset($oExtension->kKundengruppe) && $oExtension->kKundengruppe == $oKundengruppe->getKundengruppe()}selected="selected"{/if}>{$oKundengruppe->getName()}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon"><label for="nSeitenTyp">Seitentyp</label></span>
                        <span class="input-group-wrap">
                            <select class="form-control" id="nSeitenTyp" name="nSeitenTyp">
                                {if isset($nSeitenTyp) && intval($nSeitenTyp) > 0}
                                    {include file="tpl_inc/seiten_liste.tpl" nPage=$nSeitenTyp}
                                {elseif isset($oExtension->nSeite)}
                                    {include file="tpl_inc/seiten_liste.tpl" nPage=$oExtension->nSeite}
                                {else}
                                    {include file="tpl_inc/seiten_liste.tpl" nPage=0}
                                {/if}
                            </select>
                        </span>
                    </div>
                    <div id="type2" class="custom">
                        <div class="input-group">
                            <span class="input-group-addon"><label for="cKey">Filter</label></span>
                            <span class="input-group-wrap">
                                <select class="form-control" id="cKey" name="cKey">
                                    <option value="" {if isset($oExtension->cKey) && $oExtension->cKey === ''}selected="selected"{/if}>
                                        Kein Filter
                                    </option>
                                    <option value="kTag" {if isset($cKey) && $cKey === 'kTag'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kTag'}selected="selected"{/if}>
                                        Tag
                                    </option>
                                    <option value="kMerkmalWert" {if isset($cKey) && $cKey === 'kMerkmalWert'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert'}selected="selected"{/if}>
                                        Merkmal
                                    </option>
                                    <option value="kKategorie" {if isset($cKey) && $cKey === 'kKategorie'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie'}selected="selected"{/if}>
                                        Kategorie
                                    </option>
                                    <option value="kHersteller" {if isset($cKey) && $cKey === 'kHersteller'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller'}selected="selected"{/if}>
                                        Hersteller
                                    </option>
                                    <option value="cSuche" {if isset($cKey) && $cKey === 'cSuche'}selected="selected" {elseif isset($oExtension->cKey) && $oExtension->cKey === 'cSuche'}selected="selected"{/if}>
                                        Suchbegriff
                                    </option>
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="nl">
                        <div id="keykArtikel" class="input-group key">
                            <span class="input-group-addon"><label for="article_name">Artikel</label></span>
                            <input type="hidden" name="article_key" id="article_key"
                                   value="{if (isset($cKey) && $cKey === 'kArtikel') || (isset($oExtension->cKey) && $oExtension->cKey === 'kArtikel')}{$oExtension->cValue}{/if}">
                            <input class="form-control" type="text" name="article_name" id="article_name">
                            <script>
                                enableTypeahead('#article_name', 'getProducts', 'cName', null, function(e, item) {
                                    $('#article_name').val(item.cName);
                                    $('#article_key').val(item.kArtikel);
                                });
                                {if (isset($cKey) && $cKey === 'kArtikel') || (isset($oExtension->cKey) && $oExtension->cKey === 'kArtikel')}
                                    ioCall('getProducts', [[$('#article_key').val()]], function (data) {
                                        $('#article_name').val(data[0].cName);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keykLink" class="input-group key">
                            <span class="input-group-addon"><label for="link_name">Eigene Seite</label></span>
                            <input type="hidden" name="link_key" id="link_key"
                                   value="{if (isset($cKey) && $cKey === 'kLink') || (isset($oExtension->cKey) && $oExtension->cKey === 'kLink')}{$oExtension->cValue}{/if}">
                            <input class="form-control" type="text" name="link_name" id="link_name">
                            <script>
                                enableTypeahead('#link_name', 'getPages', 'cName', null, function(e, item) {
                                    $('#link_name').val(item.cName);
                                    $('#link_key').val(item.kLink);
                                });
                                {if (isset($cKey) && $cKey === 'kLink') || (isset($oExtension->cKey) && $oExtension->cKey === 'kLink')}
                                    ioCall('getPages', [[$('#link_key').val()]], function (data) {
                                        $('#link_name').val(data[0].cName);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keykTag" class="input-group key">
                            <span class="input-group-addon"><label for="tag_name">Tag</label></span>
                            <input type="hidden" name="tag_key" id="tag_key"
                                   value="{if (isset($cKey) && $cKey === 'kTag') || (isset($oExtension->cKey) && $oExtension->cKey === 'kTag')}{$oExtension->cValue}{/if}">
                            <input class="form-control" type="text" name="tag_name" id="tag_name">
                            <script>
                                enableTypeahead('#tag_name', 'getTags', 'cName', null, function(e, item) {
                                    $('#tag_name').val(item.cName);
                                    $('#tag_key').val(item.kTag);
                                });
                                {if (isset($cKey) && $cKey === 'kTag') || (isset($oExtension->cKey) && $oExtension->cKey === 'kTag')}
                                    ioCall('getTags', [[$('#tag_key').val()]], function (data) {
                                        $('#tag_name').val(data[0].cName);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keykMerkmalWert" class="input-group key">
                            <span class="input-group-addon"><label for="attribute_name">Merkmal</label></span>
                            <input type="hidden" name="attribute_key" id="attribute_key"
                                   value="{if (isset($cKey) && $cKey === 'kMerkmalWert') || (isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert')}{$oExtension->cValue}{/if}">
                            <input class="form-control" type="text" name="attribute_name" id="attribute_name">
                            <script>
                                enableTypeahead('#attribute_name', 'getAttributes', 'cWert', null, function(e, item) {
                                    $('#attribute_name').val(item.cWert);
                                    $('#attribute_key').val(item.kMerkmalWert);
                                });
                                {if (isset($cKey) && $cKey === 'kMerkmalWert') || (isset($oExtension->cKey) && $oExtension->cKey === 'kMerkmalWert')}
                                    ioCall('getAttributes', [[$('#attribute_key').val()]], function (data) {
                                        $('#attribute_name').val(data[0].cWert);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keykKategorie" class="input-group key">
                            <span class="input-group-addon"><label for="categories_name">Kategorie</label></span>
                            <input type="hidden" name="categories_key" id="categories_key"
                                   value="{if (isset($cKey) && $cKey === 'kKategorie') || (isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie')}{$oExtension->cValue}{/if}">
                            <input class="form-control" type="text" name="categories_name" id="categories_name">
                            <script>
                                enableTypeahead('#categories_name', 'getCategories', 'cName', null, function(e, item) {
                                    $('#categories_name').val(item.cName);
                                    $('#categories_key').val(item.kKategorie);
                                });
                                {if (isset($cKey) && $cKey === 'kKategorie') || (isset($oExtension->cKey) && $oExtension->cKey === 'kKategorie')}
                                    ioCall('getCategories', [[$('#categories_key').val()]], function (data) {
                                        $('#categories_name').val(data[0].cName);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keykHersteller" class="input-group key">
                            <span class="input-group-addon"><label for="manufacturer_name">Hersteller</label></span>
                            <input type="hidden" name="manufacturer_key" id="manufacturer_key"
                                   value="{if (isset($cKey) && $cKey === 'kHersteller') || (isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller')}{$oExtension->cValue}{/if}">
                            <input class="form-control" type="text" name="manufacturer_name" id="manufacturer_name">
                            <script>
                                enableTypeahead('#manufacturer_name', 'getManufacturers', 'cName', null, function(e, item) {
                                    $('#manufacturer_name').val(item.cName);
                                    $('#manufacturer_key').val(item.kHersteller);
                                });
                                {if (isset($cKey) && $cKey === 'kHersteller') || (isset($oExtension->cKey) && $oExtension->cKey === 'kHersteller')}
                                    ioCall('getManufacturers', [[$('#manufacturer_key').val()]], function (data) {
                                        $('#manufacturer_name').val(data[0].cName);
                                    });
                                {/if}
                            </script>
                        </div>
                        <div id="keycSuche" class="key input-group">
                            <span class="input-group-addon"><label for="ikeycSuche">Suchbegriff</label></span>
                            <input class="form-control" type="text" id="ikeycSuche" name="keycSuche"
                                   value="{if (isset($cKey) &&  $cKey === 'cSuche') || (isset($oExtension->cKey) && $oExtension->cKey === 'cSuche')}{if isset($keycSuche) && $keycSuche !== ''}{$keycSuche}{else}{$oExtension->cValue}{/if}{/if}" />
                        </div>
                    </div>
                    {* extensionpoint end *}
                </div>
            </div>

            <div class="save_wrapper">
                <button type="submit" class="btn btn-primary" value="Banner speichern"><i class="fa fa-save"></i> Banner speichern</button>
            </div>

        </form>
    </div>
    {elseif $cAction == 'area'}
    <script type="text/javascript" src="{$shopURL}/includes/libs/flashchart/js/json/json2.js"></script>
    <script type="text/javascript" src="{$shopURL}/{$PFAD_ADMIN}/{$currentTemplateDir}js/clickareas.js"></script>
    <link rel="stylesheet" href="{$shopURL}/{$PFAD_ADMIN}/{$currentTemplateDir}css/clickareas.css" type="text/css" media="screen" />
    <script type="text/javascript">
        $(function () {ldelim}
            $.clickareas({ldelim}
                'id': '#area_wrapper',
                'editor': '#area_editor',
                'save': '#area_save',
                'add': '#area_new',
                'info': '#area_info',
                'data': {$oBanner|@json_encode nofilter}
            {rdelim});
        {rdelim});
    </script>
    <script type="text/javascript">
        {literal}
        $(document).ready(function () {
            $('#article_unlink').click(function () {
                $('#article_id').val(0);
                $('#article_name').val('');
                return false;
            });
        });
        {/literal}
    </script>
    <div class="category clearall">
        <div class="left">Zonen</div>
        <div class="right" id="area_info"></div>
    </div>
    <div id="area_container">
        <div id="area_editor" class="panel panel-default">
            <div class="category first panel-heading">
                <h3 class="panel-title">Einstellungen</h3>
            </div>
            <div id="settings" class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="title">Titel</label>
                    </span>
                    <input class="form-control" type="text" id="title" name="title" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="desc">Beschreibung</label>
                    </span>
                    <textarea class="form-control" id="desc" name="desc"></textarea>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="url">Url</label>
                    </span>
                    <input class="form-control" type="text" id="url" name="url" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="style">CSS-Klasse</label>
                    </span>
                    <input class="form-control" type="text" id="style" name="style" />
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="article_name">Artikel</label>
                    </span>
                    <input type="hidden" name="article" id="article" value="{if isset($oBanner->kArtikel)}{$oBanner->kArtikel}{/if}" />
                    <input type="text" name="article_name" id="article_name" value="" class="form-control">
                    <input type="hidden" name="article_id" id="article_id" value="">
                    <script>
                        enableTypeahead('#article_name', 'getProducts', 'cName', null, function (e, item) {
                            $('#article_name').val(item.cName);
                            $('#article_id').val(item.kArtikel);
                        });
                    </script>
                </div>
                <input type="hidden" name="id" id="id" />
                <div class="save_wrapper btn-group">
                    <a href="#" class="btn btn-default" id="article_browser">Artikel w&auml;hlen</a>
                    <a href="#" class="btn btn-default" id="article_unlink">Artikel L&ouml;sen</a>
                    <button type="button" class="btn btn-danger" id="remove"><i class="fa fa-trash"></i> Zone l&ouml;schen</button>
                </div>
            </div>
        </div>
        <div id="area_wrapper">
            <img src="{$oBanner->cBildPfad}" title="" id="clickarea" />
        </div>
    </div>
    <div class="save_wrapper btn-group">
        <a class="btn btn-default" href="#" id="area_new"><i class="fa fa-share"></i> Neue Zone</a>
        <a class="btn btn-primary" href="#" id="area_save"><i class="fa fa-save"></i> Zonen speichern</a>
        <a class="btn btn-danger" href="banner.php" id="cancel"><i class="fa fa-angle-double-left"></i> zur&uuml;ck</a>
    </div>
    {else}
        <div id="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Vorhandene Banner</h3>
                </div>
                <table class="list table">
                    <thead>
                    <tr>
                        <th class="tleft" width="50%">Name</th>
                        <th width="20%">Status</th>
                        <th width="30%">Aktionen</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name="banner" from=$oBanner_arr item=oBanner}
                        <tr>
                            <td class="tleft">
                                {$oBanner->cTitel}
                            </td>
                            <td class="tcenter">
                                <h4 class="label-wrap">
                                    <span class="label success label-success">aktiv</span>
                                </h4>
                            </td>
                            <td class="tcenter">
                                <form action="banner.php" method="post">
                                    {$jtl_token}
                                    <input type="hidden" name="id" value="{$oBanner->kImageMap}" />
                                    <div class="btn-group">
                                        <button class="btn btn-default" name="action" value="area" title="verlinken"><i class="fa fa-link"></i></button>
                                        <button class="btn btn-default" name="action" value="edit" title="bearbeiten"><i class="fa fa-edit"></i></button>
                                        <button class="btn btn-danger" name="action" value="delete" title="entfernen"><i class="fa fa-trash"></i></button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>

                {if $oBanner_arr|@count === 0}
                   <div class="panel-body">
                       <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
                   </div>
                {/if}
                <div class="panel-footer">
                    <a class="btn btn-primary" href="banner.php?action=new&token={$smarty.session.jtl_token}"><i class="fa fa-share"></i> Banner hinzuf&uuml;gen</a>
                </div>
            </div>
        </div>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}