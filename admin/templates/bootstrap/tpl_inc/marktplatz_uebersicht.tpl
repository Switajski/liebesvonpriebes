<div id="content" class="marketplace">
    <div class="centered marketplace-search">
        <img src="templates/bootstrap/gfx/jtl_marktplatz_logo.png" class="mp-logo" alt="">
        <form name="marktplatz" class="navbar-form navbar-right marktplatz-search" method="post" action="marktplatz.php" role="search" id="search">
            {$jtl_token}
            <div class="input-group">
                <input class="form-control" placeholder="JTL Marktplatz durchsuchen..."  size="50" name="search" type="text" id="search"{if isset($search)} value="{$search}"{/if}>
                <span class="mp-sortorder">
                    <select class="mp-sort" name="sort">
                        <option selected="" value="">Relevanz</option>
                        <option value="cName">Alphabetisch</option>
                        <option value="nWeiterleitungen">Beliebteste</option>
                        <option value="dErstellt">Neuste</option>
                    </select>
                    <select class="mp-order" name="order">
                        <option value="DESC">Absteigend</option>
                        <option value="ASC">Aufsteigend</option>
                    </select>
                </span>
                <span class="input-group-btn">
                    <button type="submit" name="submitSuche" class="btn btn-default search_submit"><i class="fa fa-search"></i></button>
                </span>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-xs-6 col-md-3 col-lg-2 mp-menu">
            <ul class="nav nav-pills nav-stacked">
                {foreach from=$data->oKategorie_arr item=category}
                    {foreach from=$category->oSubKategorie_arr item=subCategory}
                        {if !empty($subCategory->oSubKategorie_arr)}
                            {foreach from=$subCategory->oSubKategorie_arr item=subSubCategory}
                                <li role="presentation" class="{if !empty($cat) && $cat == $subSubCategory->kErweiterungkategorie} active{/if}">
                                    <span class="cat-icon"><i class="fa" id="cat-{$subSubCategory->cName|strtolower|htmlspecialchars|replace:'/':''|replace:' ':''}"></i></span>
                                    <a href="marktplatz.php?cat={$subSubCategory->kErweiterungkategorie}{if isset($search)}&search={$search}{/if}">{$subSubCategory->cName}</a>
                                </li>
                            {/foreach}
                        {else}
                            <li role="presentation" class="{if !empty($cat) && $cat == $subCategory->kErweiterungkategorie} active{/if}">
                                <span class="cat-icon"><i class="fa" id="cat-{$subCategory->cName|strtolower|htmlspecialchars|replace:'/':''|replace:' ':''}"></i></span>
                                <a href="marktplatz.php?cat={$subCategory->kErweiterungkategorie}{if isset($search)}&search={$search}{/if}">{$subCategory->cName}</a>
                            </li>
                        {/if}
                    {/foreach}
                {/foreach}
            </ul>

            <div class="sidebar-item-wrapper row-fluid">
                <h4>JTL-Zertifikat</h4>
                <div class="col-md-12 col-lg-5">
                    <img src="https://images.jtl-software.de/servicepartner/cert/jtl_certified_128.png" alt="JTL zertifizierte Erweiterung" width="90" height="auto">
                </div>
                <div class="col-md-12 col-lg-7">
                    <p>Erweiterungen mit diesem Siegel sind von JTL-Software zertifiziert.</p>
                </div>
                <div class="col-md-12 btn-wrapper">
                    <a href="https://www.jtl-software.de/Erweiterung-zertifizieren-lassen" title="Zertifizierungsprogramm f&uuml;r Software-Erweiterungen" class="btn btn-primary" target="_blank">
                        Mehr erfahren
                    </a>
                </div>
            </div>   
        </div>

        <div class="col-xs-6 col-md-9 col-lg-10">
            {assign var=clear value=false}
            {if !empty($search)}<h3 class="mp-headline">Gefundene Erweiterungen f&uuml;r <i>{$search}</i></h3>{/if}
            <ul class="layout-fluid fluid-list row">
                {foreach from=$data->oErweiterung_arr item=filteredExtension name=ef}
                    {include file='tpl_inc/marktplatz_item.tpl' clear=$clear extension=$filteredExtension}
                {/foreach}
            </ul>

            <h3 class="mp-headline">Beliebte Erweiterungen</h3>
            <ul class="layout-fluid fluid-list row">
                {foreach from=$dataPopular->oErweiterung_arr item=popularExtension name=ep}
                    {include file='tpl_inc/marktplatz_item.tpl' clear=$clear extension=$popularExtension}
                {/foreach}
            </ul>

            <h3 class="mp-headline">Neuste Erweiterungen</h3>
            <ul class="layout-fluid fluid-list row">
                {foreach from=$dataNew->oErweiterung_arr item=newExtension name=en}
                    {include file='tpl_inc/marktplatz_item.tpl' clear=$clear extension=$newExtension}
                {/foreach}
            </ul>
        </div>
    </div>

</div>