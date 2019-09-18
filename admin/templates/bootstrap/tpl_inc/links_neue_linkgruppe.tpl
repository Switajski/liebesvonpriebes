{assign var=cTitel value=#newLinkGroup#}
{if isset($Linkgruppe->kLinkgruppe) && $Linkgruppe->kLinkgruppe > 0}
    {assign var=cTitel value=#saveLinkGroup#}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$cTitel}

<div id="content">
    <form name="linkgruppe_erstellen" method="post" action="links.php">
        {$jtl_token}
        <input type="hidden" name="neu_linkgruppe" value="1" />
        <input type="hidden" name="kLinkgruppe" value="{if isset($Linkgruppe->kLinkgruppe)}{$Linkgruppe->kLinkgruppe}{/if}" />

        <div class="settings">
            <div class="input-group{if isset($xPlausiVar_arr.cName)} error{/if}">
                <span class="input-group-addon">
                    <label for="cName">{#linkGroup#}{if isset($xPlausiVar_arr.cName)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                </span>
                <input type="text" name="cName" id="cName"  class="form-control{if isset($xPlausiVar_arr.cName)} fieldfillout{/if}" value="{if isset($xPostVar_arr.cName)}{$xPostVar_arr.cName}{elseif isset($Linkgruppe->cName)}{$Linkgruppe->cName}{/if}" />
            </div>

            <div class="input-group{if isset($xPlausiVar_arr.cTemplatename)} error{/if}">
                <span class="input-group-addon">
                    <label for="cTemplatename">{#linkGroupTemplatename#}{if isset($xPlausiVar_arr.cTemplatename)} <span class="fillout">{#FillOut#}</span>{/if}</label>
                </span>
                <input type="text" name="cTemplatename" id="cTemplatename" class="form-control{if isset($xPlausiVar_arr.cTemplatename)} fieldfillout{/if}" value="{if isset($xPostVar_arr.cTemplatename)}{$xPostVar_arr.cTemplatename}{elseif isset($Linkgruppe->cTemplatename)}{$Linkgruppe->cTemplatename}{/if}" />
            </div>
            {foreach name=sprachen from=$sprachen item=sprache}
                {assign var="cISO" value=$sprache->cISO}
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="cName_{$cISO}">{#showedName#} ({$sprache->cNameDeutsch})</label>
                    </span>
                    <input class="form-control" type="text" name="cName_{$cISO}" id="cName_{$cISO}" value="{if isset($Linkgruppenname[$cISO])}{$Linkgruppenname[$cISO]}{/if}" />
                </div>
            {/foreach}
        </div>
        <div class="save_wrapper">
            <button type="submit" class="btn btn-primary">{$cTitel}</button>
        </div>
    </form>
</div>