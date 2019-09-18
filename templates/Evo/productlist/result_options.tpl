{assign var='show_filters' value=false}
{if $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab == 0 || $Suchergebnisse->GesamtanzahlArtikel >= $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab || $NaviFilter->nAnzahlFilter > 0}
    {assign var='show_filters' value=true}
{/if}
<div id="result-options" class="panel-wrap{if !$show_filters} hidden-xs{/if}">
    <div class="row">
        <div class="col-sm-8 col-sm-push-4 displayoptions form-inline text-right hidden-xs">
            {block name="productlist-result-options-sort"}
            <div class="form-group">
                <select name="Sortierung" onchange="$('#improve_search').submit();" class="form-control form-small">
                    {if !isset($Suchergebnisse->Sortierung) || !$Suchergebnisse->Sortierung}
                        <option value="0">{lang key="sorting" section="productOverview"}</option>{/if}
                    <option value="100" {if isset($smarty.session.Usersortierung) && isset($Sort) && $smarty.session.Usersortierung==$Sort->value}selected="selected"{/if}>{lang key="standard" section="global"}</option>
                    {foreach name=sortierliste from=$Sortierliste item=Sort}
                        <option value="{$Sort->value}" {if $smarty.session.Usersortierung==$Sort->value}selected="selected"{/if}>{$Sort->angezeigterName}</option>
                    {/foreach}
                </select>
            </div>
            <div class="form-group">
                <select name="af" onchange="$('#improve_search').submit();" class="form-control form-small">
                    <option value="0"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 0} selected="selected"{/if}>{lang key="productsPerPage" section="productOverview"}</option>
                    <option value="9"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 9} selected="selected"{/if}>9 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="18"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 18} selected="selected"{/if}>18 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="30"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 30} selected="selected"{/if}>30 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="90"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 90} selected="selected"{/if}>90 {lang key="productsPerPage" section="productOverview"}</option>
                </select>
            </div>
            {if isset($oErweiterteDarstellung) && isset($Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung) &&
            $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung === 'Y' && empty($AktuelleKategorie->categoryFunctionAttributes['darstellung'])}
                <div class="btn-group">
                    <a href="{$oErweiterteDarstellung->cURL_arr[1]}" id="ed_list" class="btn btn-default btn-option ed list {if $oErweiterteDarstellung->nDarstellung == 1}active{/if}" role="button" title="{lang key="list" section="productOverview"}"><span class="fa fa-th-list"></span></a>
                    <a href="{$oErweiterteDarstellung->cURL_arr[2]}" id="ed_gallery" class="btn btn-default btn-option ed gallery {if $oErweiterteDarstellung->nDarstellung == 2}active{/if}" role="button" title="{lang key="gallery" section="productOverview"}"><span class="fa fa-th-large"></span></a>
                </div>
            {/if}
            {/block}
        </div>
        {if $show_filters}
            <div class="col-sm-4 col-sm-pull-8 filter-collapsible-control">
                <a class="btn btn-default" data-toggle="collapse" href="#filter-collapsible" aria-expanded="false" aria-controls="filter-collapsible">
                    <span class="fa fa-filter"></span> {lang key='filterBy' section='global'}
                    <span class="caret"></span>
                </a>
            </div>
        {/if}
    </div>{* /row *}
    {if $show_filters}
        <div id="filter-collapsible" class="collapse top10">
        <nav class="panel panel-default">
            <div id="navbar-filter" class="panel-body">
                <div class="form-inline">
                    {if $Einstellungen.navigationsfilter.allgemein_kategoriefilter_benutzen === 'Y' && (!empty($Suchergebnisse->Kategorieauswahl) && $Suchergebnisse->Kategorieauswahl|@count > 1)}
                        {block name="productlist-result-options-filter-category"}
                        <div class="form-group dropdown filter-type-category">
                            <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                {lang key="allCategories" section="global"} <span class="caret"></span>
                            </a>
                            {include file='snippets/filter/category.tpl' class="dropdown-menu"}
                        </div>
                        {/block}
                    {/if}

                    {if $Einstellungen.navigationsfilter.allgemein_herstellerfilter_benutzen === 'Y' &&
                        !isset($oExtendedJTLSearchResponse) &&
                        (!empty($Suchergebnisse->Herstellerauswahl) && $Suchergebnisse->Herstellerauswahl|@count > 1)}
                        {block name="productlist-result-options-filter-manufacturer"}
                        <div class="form-group dropdown filter-type-manufacturer">
                            <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                {lang key="allManufacturers" section="global"} <span class="caret"></span>
                            </a>
                            {include file='snippets/filter/manufacturer.tpl' class="dropdown-menu"}
                        </div>
                        {/block}
                    {/if}

                    {if $Einstellungen.navigationsfilter.merkmalfilter_verwenden === 'content' && $Suchergebnisse->MerkmalFilter|@count > 0 && $Suchergebnisse->Artikel->elemente|@count > 0}
                        {block name="productlist-result-options-filter-attributes"}
                        {foreach name=merkmalfilter from=$Suchergebnisse->MerkmalFilter item=Merkmal}
                            <div class="form-group dropdown filter-type-characteristic">
                                <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {$Merkmal->cName} <span class="caret"></span>
                                </a>
                                {include file='snippets/filter/characteristic.tpl' class="dropdown-menu" role="menu"}
                            </div>
                        {/foreach}
                        {/block}
                    {/if}{* /merkmalfilter *}
                    {if !empty($Suchergebnisse->Suchspecialauswahl)}
                        <div class="form-group dropdown filter-type-special">
                            <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                {lang key="specificProducts" section="global"} <span class="caret"></span>
                            </a>
                            {include file='snippets/filter/special.tpl' class="dropdown-menu"}
                        </div>
                    {/if}{* /suchspecials *}
                    {if $Einstellungen.navigationsfilter.preisspannenfilter_benutzen === 'content' && (empty($NaviFilter->PreisspannenFilter) && !empty($Suchergebnisse->Preisspanne))}
                        {block name="productlist-result-options-filter-price"}
                        <div class="form-group dropdown filter-type-pricerange">
                            <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                {lang key="rangeOfPrices" section="global"} <span class="caret"></span>
                            </a>
                            {include file='snippets/filter/pricerange.tpl' class="dropdown-menu"}
                        </div>
                        {/block}
                    {elseif isset($NaviFilter->PreisspannenFilter) && $NaviFilter->PreisspannenFilter->fBis > 0}
                        <input type="hidden" name="pf" value="{$NaviFilter->PreisspannenFilter->cWert}">
                    {/if}{* /preisspannenfilter *}

                    {if $Einstellungen.navigationsfilter.bewertungsfilter_benutzen === 'content' && (empty($NaviFilter->BewertungFilter) && !empty($Suchergebnisse->Bewertung))}
                        {block name="productlist-result-options-filter-rating"}
                        <div class="form-group dropdown filter-type-review">
                            <a href="#" class="btn btn-default dropdown-toggle form-control" data-toggle="dropdown" role="button" aria-expanded="false">
                                {lang key="Votes" section="global"} <span class="caret"></span>
                            </a>
                            {include file='snippets/filter/review.tpl' class="dropdown-menu"}
                        </div>
                        {/block}
                    {elseif isset($NaviFilter->BewertungFilter) && $NaviFilter->BewertungFilter->nSterne > 0}
                        <input type="hidden" name="bf" value="{$NaviFilter->BewertungFilter->nSterne}">
                    {/if}
                </div>{* /form-inline *}
            </div>
            <!-- /.navbar-collapse -->
        </nav>
        </div>{* /collapse *}
        {if $NaviFilter->nAnzahlFilter > 0}
            <div class="clearfix top10"></div>
            <div class="active-filters panel panel-default">
            <div class="panel-body">
                {if isset($NaviFilter->SuchspecialFilter->kKey) && $NaviFilter->SuchspecialFilter->kKey > 0 && (!isset($NaviFilter->Suchspecial) || $NaviFilter->Suchspecial->kKey != $NaviFilter->SuchspecialFilter->kKey)}
                    {strip}
                    <a href="{$NaviFilter->URL->cAlleSuchspecials}" rel="nofollow" title="{lang key="specificProducts" section="global"} {lang key="delete" section="global"}" class="label label-info filter-type-special">
                        {if $NaviFilter->SuchspecialFilter->kKey == 1}
                            {lang key="bestsellers" section="global"}
                        {elseif $NaviFilter->SuchspecialFilter->kKey == 2}
                            {lang key="specialOffer" section="global"}
                        {elseif $NaviFilter->SuchspecialFilter->kKey == 3}
                            {lang key="newProducts" section="global"}
                        {elseif $NaviFilter->SuchspecialFilter->kKey == 4}
                            {lang key="topOffer" section="global"}
                        {elseif $NaviFilter->SuchspecialFilter->kKey == 5}
                            {lang key="upcomingProducts" section="global"}
                        {elseif $NaviFilter->SuchspecialFilter->kKey == 6}
                            {lang key="topReviews" section="global"}
                        {/if}
                        &nbsp;
                        <span class="fa fa-trash-o"></span>
                    </a>
                    {/strip}
                {/if}
                {if !empty($NaviFilter->KategorieFilter->kKategorie)}
                    {strip}
                        <a href="{$NaviFilter->URL->cAlleKategorien}" rel="nofollow" title="{lang key="category" section="global"} {lang key="delete" section="global"}" class="label label-info filter-type-category">{if $Einstellungen.navigationsfilter.kategoriefilter_anzeigen_als === 'HF' && !empty($NaviFilter->KategorieFilter->kKategorie) && $NaviFilter->KategorieFilter->kKategorie > 0}{$NaviFilter->KategorieFilter->cName}{else}{$Suchergebnisse->Kategorieauswahl[0]->cName}{/if}
                            &nbsp;<span class="fa fa-trash-o"></span>
                        </a>
                    {/strip}
                {/if}
                {if !empty($NaviFilter->Hersteller->kHersteller) || !empty($NaviFilter->HerstellerFilter->kHersteller)}
                    {strip}
                        <a href="{$NaviFilter->URL->cAlleHersteller}" rel="nofollow" title="{lang key="manufacturers" section="global"} {lang key="delete" section="global"}" class="label label-info filter-type-manufacturer">{$Suchergebnisse->Herstellerauswahl[0]->cName}
                            &nbsp;<span class="fa fa-trash-o"></span>
                        </a>
                    {/strip}
                {/if}
                {if !empty($NaviFilter->PreisspannenFilter->fBis)}
                    {strip}
                        <a href="{$NaviFilter->URL->cAllePreisspannen}" rel="nofollow" title="{lang key="rangeOfPrices" section="global"} {lang key="delete" section="global"}" class="label label-info  filter-type-pricerange">{$NaviFilter->PreisspannenFilter->cVonLocalized}
                        - {$NaviFilter->PreisspannenFilter->cBisLocalized}
                        &nbsp;<span class="fa fa-trash-o"></span>
                        </a>{/strip}
                {/if}
                {if !empty($NaviFilter->BewertungFilter->nSterne)}
                    {strip}
                        <a href="{$NaviFilter->URL->cAlleBewertungen}" rel="nofollow" title="{lang key="paginationOrderByRating" section="global"} {lang key="delete" section="global"}" class="label label-info filter-type-review">{lang key="from" section="productDetails"} {$NaviFilter->BewertungFilter->nSterne} {if $NaviFilter->BewertungFilter->nSterne > 1}{lang key="starPlural"}{else}{lang key="starSingular"}{/if}
                            &nbsp;<span class="fa fa-trash-o"></span>
                        </a>
                    {/strip}
                {/if}
                {if !empty($NaviFilter->TagFilter[0]->kTag)}
                    {strip}
                        <a href="{$NaviFilter->URL->cAlleTags}" rel="nofollow" title="{lang key="tags" section="global"} {lang key="delete" section="global"}" class="label label-info filter-type-tag">{$NaviFilter->TagFilter[0]->cName}
                            &nbsp;<span class="fa fa-trash-o"></span>
                        </a>
                    {/strip}
                {/if}
                {foreach name=merkmalfilter from=$NaviFilter->MerkmalFilter item=Merkmal}
                    {strip}
                        <a href="{$NaviFilter->URL->cAlleMerkmalWerte[$Merkmal->kMerkmalWert]}" rel="nofollow" title="{lang key="characteristics" section="comparelist"} {lang key="delete" section="global"}" class="label label-info filter-type-characteristic">
                            {$Merkmal->cName|escape:'html'} &nbsp;<span class="fa fa-trash-o"></span>
                        </a>
                    {/strip}
                {/foreach}
                {if !empty($NaviFilter->URL->cNoFilter)}
                    {strip}
                        <a href="{$NaviFilter->URL->cNoFilter}" title="{lang key="removeFilters" section="global"}" class="label label-warning">
                            {lang key="removeFilters" section="global"}
                        </a>
                    {/strip}
                {/if}
            </div>
            </div>{* /active-filters *}
        {/if}
    {/if}
</div>
