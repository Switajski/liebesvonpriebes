{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: kundenimport.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: JTL-Software-GmbH
    http://www.jtl-software.de
    
    Copyright (c) 2007 JTL-Software

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="kundenimport"}
{include file="tpl_inc/seite_header.tpl" cTitel=#newsletterMail# cBeschreibung=#newsletterMailDesc# cDokuURL=#newsletterURL#}
<div id="content">

    {if isset($hinweis) && $hinweis|count_characters > 0}
        <p class="box_success">{$hinweis}</p>
    {/if}
    
    <form name="kundenimporter" method="post" action="newsletterimport.php" enctype="multipart/form-data">
        <input type="hidden" name="newsletterimport" value="1" />
        <div class="settings">
            <p>
                <label for="kSprache">{#language#}</label>
                <select name="kSprache" id="kSprache" class="combo">
                    {foreach name=sprache from=$sprachen item=sprache}
                        <option value="{$sprache->kSprache}">{$sprache->cNameDeutsch}</option>
                    {/foreach}
                </select>
            </p>
            <p>
                <label for="csv">{#csvFile#}</label>
                <input type="file" name="csv" id="csv"  tabindex="1" />
            </p>
        </div>      
        <p class="submit">
            <input type="submit" value="{#import#}" class="button orange" />
        </p>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}