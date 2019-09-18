{config_load file="$lang.conf" section="suchspecials"}
{include file='tpl_inc/header.tpl'}

{include file='tpl_inc/seite_header.tpl' cTitel=#suchspecials# cBeschreibung=#suchspecialsDesc# cDokuURL=#suchspecialURL#}
<div id="content" class="container-fluid">
    <div class="block">
        <form name="sprache" method="post" action="suchspecials.php">
            {$jtl_token}
            <input type="hidden" name="sprachwechsel" value="1" />
            <div class="input-group p25 left">
                <span class="input-group-addon">
                    <label for="{#changeLanguage#}">{#changeLanguage#}:</label>
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
    </div>

    <ul class="nav nav-tabs" role="tablist">
        <li class="tab{if !isset($cTab) || $cTab === 'suchspecials'} active{/if}">
            <a data-toggle="tab" role="tab" href="#suchspecials">{#suchspecials#}</a>
        </li>
        <li class="tab{if isset($cTab) && $cTab === 'einstellungen'} active{/if}">
            <a data-toggle="tab" role="tab" href="#einstellungen">{#suchsepcialsSettings#}</a>
        </li>
    </ul>
    <div class="tab-content">
        <div id="suchspecials" class="tab-pane fade {if !isset($cTab) || $cTab === 'suchspecials'} active in{/if}">
            <form name="suchspecials" method="post" action="suchspecials.php">
                {$jtl_token}
                <div id="settings">
                    <div class="settings panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#suchspecials#}</h3>
                        </div>
                        <div class="panel-body">
                            <input type="hidden" name="suchspecials" value="1" />
                            <div class="item input-group">
                                <span class="input-group-addon">
                                    <label for="bestseller">{#bestseller#}</label>
                                </span>
                                <input class="form-control" name="bestseller" id="bestseller" type="text" value="{if isset($oSuchSpecials_arr[1])}{$oSuchSpecials_arr[1]}{/if}" />
                            </div>
                            <div class="item input-group">
                                <span class="input-group-addon">
                                    <label for="sonderangebote">{#specialOffers#}</label>
                                </span>
                                <input class="form-control" id="sonderangebote" name="sonderangebote" type="text" value="{if isset($oSuchSpecials_arr[2])}{$oSuchSpecials_arr[2]}{/if}" />
                            </div>
                            <div class="item input-group">
                                <span class="input-group-addon">
                                    <label for="neu_im_sortiment">{#newInAssortment#}</label>
                                </span>
                                <input class="form-control" id="neu_im_sortiment" name="neu_im_sortiment" type="text" value="{if isset($oSuchSpecials_arr[3])}{$oSuchSpecials_arr[3]}{/if}" />
                            </div>
                            <div class="item input-group">
                                <span class="input-group-addon">
                                    <label for="top_angebote">{#topOffers#}</label>
                                </span>
                                <input class="form-control" id="top_angebote" name="top_angebote" type="text" value="{if isset($oSuchSpecials_arr[4])}{$oSuchSpecials_arr[4]}{/if}" />
                            </div>
                            <div class="item input-group">
                                <span class="input-group-addon">
                                    <label for="in_kuerze_verfuegbar">{#shortTermAvailable#}</label>
                                </span>
                                <input class="form-control" id="in_kuerze_verfuegbar" name="in_kuerze_verfuegbar" type="text" value="{if isset($oSuchSpecials_arr[5])}{$oSuchSpecials_arr[5]}{/if}" />
                            </div>
                            <div class="item input-group">
                                <span class="input-group-addon">
                                    <label for="top_bewertet">{#topreviews#}</label>
                                </span>
                                <input class="form-control" id="top_bewertet" name="top_bewertet" type="text" value="{if isset($oSuchSpecials_arr[6])}{$oSuchSpecials_arr[6]}{/if}" />
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" value="{#suchspecialsSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#suchspecialsSave#}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div id="einstellungen" class="tab-pane fade {if isset($cTab) && $cTab === 'einstellungen'} active in{/if}">
            {include file='tpl_inc/config_section.tpl' config=$oConfig_arr name='einstellen' a='saveSettings' action='suchspecials.php' buttonCaption=#save# title='Einstellungen' tab='einstellungen'}
        </div>
    </div>
</div>

{include file='tpl_inc/footer.tpl'}