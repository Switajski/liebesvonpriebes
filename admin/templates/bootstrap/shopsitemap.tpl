{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='shopsitemap'}

{include file='tpl_inc/seite_header.tpl' cTitel=#shopsitemap# cBeschreibung=#shopsitemapDesc# cDokuURL=#shopsitemapURL#}
<div id="content" class="container-fluid">
    <form name="einstellen" method="post" action="shopsitemap.php" id="einstellen">
        {$jtl_token}
        <input type="hidden" name="speichern" value="1" />
        <div id="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#settings#}</h3>
                </div>
                <div class="panel-body">
                    {foreach name=conf from=$oConfig_arr item=cnf}
                        {if $cnf->cConf === 'Y'}
                            <div class="input-group item{if isset($cnf->kEinstellungenConf) && isset($cSuche) && $cnf->kEinstellungenConf == $cSuche} highlight{/if}">
                                <span class="input-group-addon">
                                    <label for="{$cnf->cWertName}">{$cnf->cName}</label>
                                </span>
                                {if $cnf->cInputTyp === 'selectbox'}
                                    <span class="input-group-wrap">
                                        <select class="form-control" name="{$cnf->cWertName}" id="{$cnf->cWertName}">
                                            {foreach name=selectfor from=$cnf->ConfWerte item=wert}
                                                <option value="{$wert->cWert}" {if $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
                                            {/foreach}
                                        </select>
                                    </span>
                                {elseif $cnf->cInputTyp === 'pass'}
                                    <input class="form-control" type="password" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                                {else}
                                    <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                                {/if}
                                <span class="input-group-addon">
                                    {if $cnf->cBeschreibung}
                                        {getHelpDesc cDesc=$cnf->cBeschreibung cID=$cnf->kEinstellungenConf}
                                    {/if}
                                </span>
                            </div>
                        {/if}
                    {/foreach}
                </div>
                <div class="panel-footer">
                    <button name="speichern" type="submit" value="{#shopsitemapSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#shopsitemapSave#}</button>
                </div>
            </div>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}