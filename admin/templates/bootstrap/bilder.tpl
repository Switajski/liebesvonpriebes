{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="bilder"}

{include file='tpl_inc/seite_header.tpl' cTitel=#bilder# cBeschreibung=#bilderDesc# cDokuURL=#bilderURL#}
<div id="content">
    <form method="post" action="bilder.php">
        {$jtl_token}
        <input type="hidden" name="speichern" value="1">
        <div id="settings">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Bildgr&ouml;&szlig;en</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table table-images">
                        <thead>
                        <tr>
                            <th class="tleft">Typ</th>
                            <th class="tcenter">Mini <small>(Breite x H&ouml;he)</small></th>
                            <th class="tcenter">Klein <small>(Breite x H&ouml;he)</small></th>
                            <th class="tcenter">Normal <small>(Breite x H&ouml;he)</small></th>
                            <th class="tcenter">Gro&szlig; <small>(Breite x H&ouml;he)</small></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="tleft">Kategorien</td>
                            <td class="tcenter"></td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_kategorien_breite" value="{$oConfig.bilder_kategorien_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_kategorien_hoehe" value="{$oConfig.bilder_kategorien_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                        </tr>

                        <tr>
                            <td class="tleft">Variationen</td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_mini_breite" value="{$oConfig.bilder_variationen_mini_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_mini_hoehe" value="{$oConfig.bilder_variationen_mini_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_breite" value="{$oConfig.bilder_variationen_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_hoehe" value="{$oConfig.bilder_variationen_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_gross_breite" value="{$oConfig.bilder_variationen_gross_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_variationen_gross_hoehe" value="{$oConfig.bilder_variationen_gross_hoehe}" />
                            </td>
                        </tr>

                        <tr>
                            <td class="tleft">Produkte</td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_mini_breite" value="{$oConfig.bilder_artikel_mini_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_mini_hoehe" value="{$oConfig.bilder_artikel_mini_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_klein_breite" value="{$oConfig.bilder_artikel_klein_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_klein_hoehe" value="{$oConfig.bilder_artikel_klein_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_normal_breite" value="{$oConfig.bilder_artikel_normal_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_normal_hoehe" value="{$oConfig.bilder_artikel_normal_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_gross_breite" value="{$oConfig.bilder_artikel_gross_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_artikel_gross_hoehe" value="{$oConfig.bilder_artikel_gross_hoehe}" />
                            </td>
                        </tr>

                        <tr>
                            <td class="tleft">Hersteller</td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_hersteller_klein_breite" value="{$oConfig.bilder_hersteller_klein_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_hersteller_klein_hoehe" value="{$oConfig.bilder_hersteller_klein_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_hersteller_normal_breite" value="{$oConfig.bilder_hersteller_normal_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_hersteller_normal_hoehe" value="{$oConfig.bilder_hersteller_normal_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                        </tr>

                        <tr>
                            <td class="tleft">Merkmale</td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_merkmal_klein_breite" value="{$oConfig.bilder_merkmal_klein_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_merkmal_klein_hoehe" value="{$oConfig.bilder_merkmal_klein_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_merkmal_normal_breite" value="{$oConfig.bilder_merkmal_normal_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_merkmal_normal_hoehe" value="{$oConfig.bilder_merkmal_normal_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                        </tr>

                        <tr>
                            <td class="tleft">Merkmalwerte</td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_merkmalwert_klein_breite" value="{$oConfig.bilder_merkmalwert_klein_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_merkmalwert_klein_hoehe" value="{$oConfig.bilder_merkmalwert_klein_hoehe}" />
                            </td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_merkmalwert_normal_breite" value="{$oConfig.bilder_merkmalwert_normal_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_merkmalwert_normal_hoehe" value="{$oConfig.bilder_merkmalwert_normal_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                        </tr>

                        <tr>
                            <td class="tleft">Konfiggruppe</td>
                            <td class="tcenter"></td>
                            <td class="widthheight tcenter">
                                <input size="4" class="form-control left" type="number" name="bilder_konfiggruppe_klein_breite" value="{$oConfig.bilder_konfiggruppe_klein_breite}" />
                                <span class="cross-sign left">x</span>
                                <input size="4" class="form-control left" type="number" name="bilder_konfiggruppe_klein_hoehe" value="{$oConfig.bilder_konfiggruppe_klein_hoehe}" />
                            </td>
                            <td class="tcenter"></td>
                            <td class="tcenter"></td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            {assign var=open value=false}
            {foreach name=conf from=$oConfig_arr item=cnf}
            {if $cnf->kEinstellungenConf == 267 || $cnf->kEinstellungenConf == 268 || $cnf->kEinstellungenConf == 269 || $cnf->kEinstellungenConf == 1135 || $cnf->kEinstellungenConf == 1421 || $cnf->kEinstellungenConf == 172 || $cnf->kEinstellungenConf == 161  || $cnf->kEinstellungenConf == 1483  || $cnf->kEinstellungenConf == 1484 || $cnf->kEinstellungenConf == 1485}
                {if $cnf->cConf === 'Y'}
                    <div class="input-group item{if isset($cSuche) && $cnf->kEinstellungenConf == $cSuche} highlight{/if}">
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
                        {elseif $cnf->cInputTyp === 'number'}
                            <input class="form-control" type="number" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                        {elseif $cnf->cInputTyp === 'color'}
                            <span class="input-group-colorpicker-wrap">
                            <div id="{$cnf->cWertName}" style="display:inline-block">
                                <div style="background-color: {$cnf->gesetzterWert}" class="colorSelector"></div>
                            </div>
                            <input type="hidden" name="{$cnf->cWertName}" class="{$cnf->cWertName}_data" value="{$cnf->gesetzterWert}" />
                            <script type="text/javascript">
                                $('#{$cnf->cWertName}').ColorPicker({ldelim}
                                    color:    '{$cnf->gesetzterWert}',
                                    onShow:   function (colpkr) {ldelim}
                                        $(colpkr).fadeIn(500);
                                        return false;
                                    {rdelim},
                                    onHide:   function (colpkr) {ldelim}
                                        $(colpkr).fadeOut(500);
                                        return false;
                                    {rdelim},
                                    onChange: function (hsb, hex, rgb) {ldelim}
                                        $('#{$cnf->cWertName} div').css('backgroundColor', '#' + hex);
                                        $('.{$cnf->cWertName}_data').val('#' + hex);
                                    {rdelim}
                                {rdelim});
                            </script>
                            </span>
                        {else}
                            <input class="form-control" type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" />
                        {/if}

                        <span class="input-group-addon">
                            {if $cnf->cBeschreibung}
                                {getHelpDesc cDesc=$cnf->cBeschreibung cID=$cnf->kEinstellungenConf}
                            {/if}
                        </span>
                    </div>
                {else}
                    {if $open}</div></div>{/if}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{$cnf->cName}
                            <span class="pull-right">{getHelpDesc cID=$cnf->kEinstellungenConf}</span>
                            {if isset($cnf->cSektionsPfad) && $cnf->cSektionsPfad|strlen > 0}
                                <span class="path"><strong>{#settingspath#}:</strong> {$cnf->cSektionsPfad}</span>
                            {/if}
                            </h3>
                        </div>
                        <div class="panel-body">
                        {assign var=open value=true}
                {/if}
            {/if}
            {/foreach}
            {if $open}
                </div><!-- /.panel-body -->
            </div><!-- /.panel -->
            {/if}
            <p class="submit">
                <button name="speichern" type="submit" value="{#bilderSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#bilderSave#}</button>
            </p>
        </div>
    </form>
</div>
{include file='tpl_inc/footer.tpl'}