{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: marktplatz_uebersicht.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

<div id="content" class="marketplace">
    {if isset($error) && $error|count_characters > 0}         
        <p class="box_error">{$error}</p>
    {/if}

    <div class=" block clearall">
        <div class="tcenter">
            <form name="marktplatz" method="post" action="marktplatz.php">
                 <label for="orderSearch" class="hidden">:</label>
                 <input placeholder="Marktplatz durchsuchen" size="50" name="search" type="text" id="search"{if isset($search)} value="{$search}"{/if}>
                 <button name="submitSuche" type="submit" class="button blue">suchen</button>
            </form>
        </div>
    </div>

    <div class="container">
        <ul class="layout-fluid">
        {foreach from=$data->oKategorie_arr item=category}
            {foreach from=$category->oSubKategorie_arr item=subCategory}
                {if !empty($subCategory->oSubKategorie_arr)}
                    {foreach from=$subCategory->oSubKategorie_arr item=subSubCategory}
                        <li{if $cat && $cat == $subSubCategory->kErweiterungkategorie} class="active"{/if}><a href="marktplatz.php?cat={$subSubCategory->kErweiterungkategorie}{if isset($search)}&search={$search}{/if}" class="cat{$subSubCategory->kErweiterungkategorie}"><span>{$subSubCategory->cName}</span></a></li>
                    {/foreach}
                {else}
                    <li{if $cat && $cat == $subCategory->kErweiterungkategorie} class="active"{/if}><a href="marktplatz.php?cat={$subCategory->kErweiterungkategorie}{if isset($search)}&search={$search}{/if}"><span>{$subCategory->cName}</span></a></li>
                {/if}
            {/foreach}
        {/foreach}
        </ul>

        <hr class="clear" />

        <h2>Beliebte Erweiterungen</h2>
        

        <ul class="layout-fluid fluid-list">
        {foreach from=$dataPopular->oErweiterung_arr item=popularExtension}
            <li>{$popularExtension->cName}</li>
        {/foreach}
        </ul>

        <hr class="clear" />
        
        <h2>Neuste Erweiterungen</h2>

        <ul class="layout-fluid fluid-list">
        {foreach from=$dataNew->oErweiterung_arr item=newExtension}
            <li>{$newExtension->cName}</li>
        {/foreach}
        </ul>
        
        <br class="clear" />
    </div>
</div>