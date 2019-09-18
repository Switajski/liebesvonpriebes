<section class="panel panel-default box box-manufacturers" id="sidebox{$oBox->kBox}">
    <div class="panel-heading">
        <h5 class="panel-title">{lang key="manufacturers" section="global"}</h5>
    </div>
    {if $oBox->manufacturers|@count > 8}
        <div class="box-body">
            <div class="dropdown">
                <button class="btn btn-default btn-block dropdown-toggle" type="button" id="dropdown-manufacturer" data-toggle="dropdown" aria-expanded="true">
                    {lang key="selectManufacturer" section="global"}
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu" aria-labelledby="dropdown-manufacturer">
                    {foreach name=hersteller from=$oBox->manufacturers item=hst}
                        <li role="presentation">
                            <a role="menuitem" tabindex="-1" href="{$hst->cSeo}">{$hst->cName|escape:"html"}</a></li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {else}
        <div class="box-body">
            <ul class="nav nav-list">
                {foreach name=hersteller from=$oBox->manufacturers item=hst}
                    <li><a href="{$hst->cSeo}" title="{$hst->cName|escape:"html"}">{$hst->cName|escape:"html"}</a></li>
                {/foreach}
            </ul>
        </div>
    {/if}
</section>
