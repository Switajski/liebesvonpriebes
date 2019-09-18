{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="trennzeichen"}

{include file='tpl_inc/seite_header.tpl' cTitel=#Trennzeichen# cBeschreibung=#trennzeichenDesc# cDokuURL=#trennzeichenURL#}
<div id="content" class="container-fluid">
    <div class="block">
        {if isset($Sprachen) && $Sprachen|@count > 1}
            <form name="sprache" method="post" action="trennzeichen.php" class="inline_block">
                {$jtl_token}
                <input type="hidden" name="sprachwechsel" value="1" />
                <div class="input-group p25 left">
                    <span class="input-group-addon">
                        <label for="{#changeLanguage#}">{#changeLanguage#}</label>
                    </span>
                    <span class="input-group-wrap last">
                        <select id="{#changeLanguage#}" name="kSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                            {foreach name=sprachen from=$Sprachen item=sprache}
                                <option value="{$sprache->kSprache}" {if $sprache->kSprache==$smarty.session.kSprache}selected{/if}>{$sprache->cNameDeutsch}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
            </form>
        {/if}
    </div>
    <form method="post" action="trennzeichen.php">
        {$jtl_token}
        <input type="hidden" name="save" value="1" />
        <div id="settings">
            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">Trennzeichen</h3></div>
                <div class="panel-body table-responsive">
                    <table class="list table">
                    <thead>
                    <tr>
                        <th class="tleft">Einheit</th>
                        <th class="tcenter">Anzahl Dezimalstellen</th>
                        <th class="tcenter">Dezimaltrennzeichen</th>
                        <th class="tcenter">Tausendertrennzeichen</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        {assign var=nDezimal_weight value="nDezimal_"|cat:$JTLSEPARATER_WEIGHT}
                        {assign var=cDezZeichen_weight value="cDezZeichen_"|cat:$JTLSEPARATER_WEIGHT}
                        {assign var=cTausenderZeichen_weight value="cTausenderZeichen_"|cat:$JTLSEPARATER_WEIGHT}
                        {if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT])}
                            <input type="hidden" name="kTrennzeichen_{$JTLSEPARATER_WEIGHT}" value="{$oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT]->getTrennzeichen()}" />
                        {/if}
                        <td class="tleft">Gewicht</td>
                        <td class="widthheight tcenter">
                            <input size="2" type="number" name="nDezimal_{$JTLSEPARATER_WEIGHT}" class="form-control{if isset($xPlausiVar_arr[$nDezimal_weight])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$nDezimal_weight])}{$xPostVar_arr[$nDezimal_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT]->getDezimalstellen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight tcenter">
                            <input size="2" type="text" name="cDezZeichen_{$JTLSEPARATER_WEIGHT}" class="form-control{if isset($xPlausiVar_arr[$cDezZeichen_weight])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cDezZeichen_weight])}{$xPostVar_arr[$cDezZeichen_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT]->getDezimalZeichen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight tcenter">
                            <input size="2" type="text" name="cTausenderZeichen_{$JTLSEPARATER_WEIGHT}" class="form-control{if isset($xPlausiVar_arr[$cTausenderZeichen_weight])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cTausenderZeichen_weight])}{$xPostVar_arr[$cTausenderZeichen_weight]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_WEIGHT]->getTausenderZeichen()}{/if}{/if}" />
                        </td>
                    </tr>
                    <tr>
                        {assign var=nDezimal_amount value="nDezimal_"|cat:$JTLSEPARATER_AMOUNT}
                        {assign var=cDezZeichen_amount value="cDezZeichen_"|cat:$JTLSEPARATER_AMOUNT}
                        {assign var=cTausenderZeichen_amount value="cTausenderZeichen_"|cat:$JTLSEPARATER_AMOUNT}
                        {if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT])}
                            <input type="hidden" name="kTrennzeichen_{$JTLSEPARATER_AMOUNT}" value="{$oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT]->getTrennzeichen()}" />
                        {/if}
                        <td class="tleft">Menge</td>
                        <td class="widthheight tcenter">
                            <input size="2" type="number" name="nDezimal_{$JTLSEPARATER_AMOUNT}" class="form-control{if isset($xPlausiVar_arr[$nDezimal_amount])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$nDezimal_amount])}{$xPostVar_arr[$nDezimal_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT]->getDezimalstellen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight tcenter">
                            <input size="2" type="text" name="cDezZeichen_{$JTLSEPARATER_AMOUNT}" class="form-control{if isset($xPlausiVar_arr[$cDezZeichen_amount])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cDezZeichen_amount])}{$xPostVar_arr[$cDezZeichen_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT]->getDezimalZeichen()}{/if}{/if}" />
                        </td>
                        <td class="widthheight tcenter">
                            <input size="2" type="text" name="cTausenderZeichen_{$JTLSEPARATER_AMOUNT}" class="form-control{if isset($xPlausiVar_arr[$cTausenderZeichen_amount])} fieldfillout{/if}" value="{if isset($xPostVar_arr[$cTausenderZeichen_amount])}{$xPostVar_arr[$cTausenderZeichen_amount]}{else}{if isset($oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT])}{$oTrennzeichenAssoc_arr[$JTLSEPARATER_AMOUNT]->getTausenderZeichen()}{/if}{/if}" />
                        </td>
                    </tr>

                    </tbody>
                </table>
                </div>
                <div class="panel-footer">
                    <button name="speichern" type="submit" value="{#trennzeichenSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#trennzeichenSave#}</button>
                </div>
            </div>
        </div>
    </form>
</div>

{include file='tpl_inc/footer.tpl'}