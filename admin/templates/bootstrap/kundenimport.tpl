{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="kundenimport"}
{include file='tpl_inc/seite_header.tpl' cTitel=#customerImport# cBeschreibung=#customerImportDesc# cDokuURL=#customerImportURL#}
<div id="content" class="container-fluid">
    <form name="kundenimporter" method="post" action="kundenimport.php" enctype="multipart/form-data">
        {$jtl_token}
        <input type="hidden" name="kundenimport" value="1" />
        <div class="settings panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#customerImport#}</h3>
            </div>
            <div class="panel-body">
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="kSprache">{#language#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <select name="kSprache" id="kSprache" class="form-control combo">
                            {foreach name=sprache from=$sprachen item=sprache}
                                <option value="{$sprache->kSprache}">{$sprache->cNameDeutsch}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="kKundengruppe">{#customerGroup#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <select name="kKundengruppe" id="kKundengruppe" class="form-control combo">
                            {foreach name=kdgrp from=$kundengruppen item=kundengruppe}
                                {assign var="kKundengruppe" value=$kundengruppe->kKundengruppe}
                                <option value="{$kundengruppe->kKundengruppe}">{$kundengruppe->cName}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="PasswortGenerieren">{#generateNewPass#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <select name="PasswortGenerieren" id="PasswortGenerieren" class="form-control comboFullSize">
                            <option value="0">{#passNo#}</option>
                            <option value="1">{#passYes#}</option>
                        </select>
                    </span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="csv">{#csvFile#}</label>
                    </span>
                    <span class="input-group-wrap">
                        <input class="form-control" type="file" name="csv" id="csv" tabindex="1" />
                    </span>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" value="{#import#}" class="btn btn-primary">{#import#}</button>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}