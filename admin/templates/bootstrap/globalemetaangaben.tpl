{config_load file="$lang.conf" section="globalemetaangaben"}
{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=#globalemetaangaben# cBeschreibung=#globalemetaangabenDesc# cDokuURL=#globalemetaangabenUrl#}
{assign var=currentLanguage value=''}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="sprache" method="post" action="globalemetaangaben.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="input-group p25 left">
                <span class="input-group-addon">
                    <label for="{#changeLanguage#}">{#changeLanguage#}</label>
                </span>
                <span class="input-group-wrap last">
                    <select id="{#changeLanguage#}" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                        {foreach name=sprachen from=$Sprachen item=sprache}
                            <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}{assign var=currentLanguage value=$sprache->cNameDeutsch}selected{/if}>{$sprache->cNameDeutsch}</option>
                        {/foreach}
                    </select>
                </span>
            </div>
        </form>
    </div>
    <form method="post" action="globalemetaangaben.php">
        {$jtl_token}
        <input type="hidden" name="einstellungen" value="1" />
        <div class="settings">
            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">{$currentLanguage}</h3></div>
                <div class="panel-body">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="Title">{#globalemetaangabenTitle#}</label>
                        </span>
                        <input type="text" class="form-control" id="Title" name="Title" value="{if isset($oMetaangaben_arr.Title)}{$oMetaangaben_arr.Title}{/if}" tabindex="1" />
                    </div>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="Meta_Description">{#globalemetaangabenMetaDesc#}</label>
                        </span>
                        <input type="text" class="form-control" id="Meta_Description" name="Meta_Description" value="{if isset($oMetaangaben_arr.Meta_Description)}{$oMetaangaben_arr.Meta_Description}{/if}" tabindex="1" />
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="Meta_Keywords">{#globalemetaangabenKeywords#}</label>
                        </span>
                        <input type="text" class="form-control" id="Meta_Keywords" name="Meta_Keywords" value="{if isset($oMetaangaben_arr.Meta_Keywords)}{$oMetaangaben_arr.Meta_Keywords}{/if}" tabindex="1" />
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="Meta_Description_Praefix">{#globalemetaangabenMetaDescPraefix#}</label>
                        </span>
                        <input type="text" class="form-control" id="Meta_Description_Praefix" name="Meta_Description_Praefix" value="{if isset($oMetaangaben_arr.Meta_Description_Praefix)}{$oMetaangaben_arr.Meta_Description_Praefix}{/if}" tabindex="1" />
                    </div>
                </div>
            </div>

            {assign var=open value=false}
            {foreach name=conf from=$oConfig_arr item=oConfig}
                {if $oConfig->cConf === 'Y'}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <label for="{$oConfig->cWertName}">{$oConfig->cName}</label>
                        </span>
                        {if $oConfig->cInputTyp === 'selectbox'}
                            <span class="input-group-wrap">
                                <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="form-control combo">
                                    {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                        <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                    {/foreach}
                                </select>
                            </span>
                        {elseif $oConfig->cInputTyp === 'number'}
                            <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                        {else}
                            <input class="form-control" type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                        {/if}
                        {if $oConfig->cBeschreibung}
                            <span class="input-group-addon">{getHelpDesc cDesc=$oConfig->cBeschreibung}</span>
                        {/if}
                    </div>
                {else}
                    {if $open}</div></div>{/if}
                    <div class="panel panel-default">
                        {if $oConfig->cName}
                            <div class="panel-heading"><h3 class="panel-title">Einstellungen</h3></div>
                        {/if}
                        <div class="panel-body">
                        {assign var=open value=true}
                {/if}
            {/foreach}
            {if $open}
                </div>
            </div>
            {/if}
        </div>

        <div class="submit">
            <button name="speichern" type="submit" value="{#globalemetaangabenSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#globalemetaangabenSave#}</button>
        </div>
    </form>
</div>

{include file='tpl_inc/footer.tpl'}