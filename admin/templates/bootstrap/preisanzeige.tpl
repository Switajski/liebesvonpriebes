{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="einstellungen"}
{config_load file="$lang.conf" section="preisanzeige"}

{include file='tpl_inc/seite_header.tpl' cTitel=#priceNotification# cBeschreibung=#information# cDokuURL=#priceURL#}
<div id="content" class="container-fluid">
    <div class="alert alert-warning">
        <i class="fa fa-warning"></i> {#priceNotificationDeprecated#}
    </div>
    <div class="alert alert-info">
        {#priceInfo#}
    </div>
    {if $oPreisanzeigeConf_arr|@count > 0 && $cSektion_arr|@count > 0}
        <form name="einstellen" method="post" action="preisanzeige.php">
            {$jtl_token}
            <input name="update" type="hidden" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Preisanzeige</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                    <thead>
                    <tr>
                        <th class="tleft">{#priceIn#}</th>
                        <th>{#priceToGrafik#}</th>
                        <th>Farbe</th>
                        <th>Gr&ouml;&szlig;e</th>
                        <th>{#font#}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach name=preisanzeige from=$cSektion_arr item=cSektion}
                        <tr>
                            <td>
                                {$cSektion}
                            </td>
                            <td class="tcenter">
                                <select name="{$oPreisanzeigeConf_arr[$cSektion][2]->cName}" class="form-control">
                                    <option value="Y" {if isset($oPreisanzeigeConf_arr[$cSektion][2]->cWert) && $oPreisanzeigeConf_arr[$cSektion][2]->cWert === 'Y'}selected{/if}>{#yes#}</option>
                                    <option value="N" {if isset($oPreisanzeigeConf_arr[$cSektion][2]->cWert) && $oPreisanzeigeConf_arr[$cSektion][2]->cWert === 'N'}selected{/if}>{#no#}</option>
                                </select>
                            </td>
                            <td class="tcenter">
                                <div id="{$oPreisanzeigeConf_arr[$cSektion][0]->cName}" style="display:inline-block">
                                    <div style="background-color: {$oPreisanzeigeConf_arr[$cSektion][0]->cWert}" class="colorSelector"></div>
                                </div>
                                <input type="hidden" name="{$oPreisanzeigeConf_arr[$cSektion][0]->cName}" class="form control {$oPreisanzeigeConf_arr[$cSektion][0]->cName}_data" value="{$oPreisanzeigeConf_arr[$cSektion][0]->cWert}" />
                                <script type="text/javascript">
                                    $('#{$oPreisanzeigeConf_arr[$cSektion][0]->cName}').ColorPicker({ldelim}
                                        color:    '{$oPreisanzeigeConf_arr[$cSektion][0]->cWert}',
                                        onShow:   function (colpkr) {ldelim}
                                            $(colpkr).fadeIn(500);
                                            return false;
                                        {rdelim},
                                        onHide:   function (colpkr) {ldelim}
                                            $(colpkr).fadeOut(500);
                                            return false;
                                        {rdelim},
                                        onChange: function (hsb, hex, rgb) {ldelim}
                                            $('#{$oPreisanzeigeConf_arr[$cSektion][0]->cName} div').css('backgroundColor', '#' + hex);
                                            $('.{$oPreisanzeigeConf_arr[$cSektion][0]->cName}_data').val('#' + hex);
                                        {rdelim}
                                    {rdelim});
                                </script>
                            </td>
                            <td class="tcenter">
                                <input type="text" name="{$oPreisanzeigeConf_arr[$cSektion][1]->cName}" class="form-control" value="{if isset($oPreisanzeigeConf_arr[$cSektion][1]->cWert)}{$oPreisanzeigeConf_arr[$cSektion][1]->cWert}{/if}" size="3" />
                            </td>
                            <td class="tcenter">
                                <select name="{$oPreisanzeigeConf_arr[$cSektion][3]->cName}" class="form-control">
                                    <option value="">&nbsp;</option>
                                    {foreach from=$cFont_arr item=font}
                                        <option value="{$font}" {if isset($oPreisanzeigeConf_arr[$cSektion][3]->cWert) && $oPreisanzeigeConf_arr[$cSektion][3]->cWert == $font}selected{/if}>{$font}</option>
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                </div>
                <div class="panel-footer">
                    <button name="speichern" type="submit" value="{#savePreferences#}" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
                </div>
            </div>
        </form>
    {/if}
</div>
{include file='tpl_inc/footer.tpl'}