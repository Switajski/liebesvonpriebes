<script type="text/javascript">
$(document).ready(function () {ldelim}
    if(document.getElementById("kupon").selectedIndex == 0) {ldelim}
        document.getElementById('fGuthaben').disabled = false;
        document.getElementById('nBonuspunkte').disabled = false;
    {rdelim} else {ldelim}
        document.getElementById('fGuthaben').disabled = true;
        document.getElementById('nBonuspunkte').disabled = true;
    {rdelim}
{rdelim});
function selectCheck(selectBox) {ldelim}
    if(selectBox.selectedIndex == 0) {ldelim}
        document.getElementById('fGuthaben').disabled = false;
        document.getElementById('nBonuspunkte').disabled = false;
        document.getElementById('fGuthaben').value = '';
        document.getElementById('nBonuspunkte').value = '';
    {rdelim} else {ldelim}
        document.getElementById('fGuthaben').disabled = true;
        document.getElementById('nBonuspunkte').disabled = true;
        document.getElementById('fGuthaben').value = '';
        document.getElementById('nBonuspunkte').value = '';
    {rdelim}
{rdelim}

function checkInput(inputField, cFeld) {ldelim}
    document.getElementById('kupon').disabled = true;
    document.getElementById('kupon').selectedIndex = 0;
    if(cFeld === 'fGuthaben') {ldelim}
        document.getElementById('nBonuspunkte').disabled = true;
    {rdelim} else {ldelim}
        document.getElementById('fGuthaben').disabled = true;
        inputField.disabled = false;
    {rdelim}
{rdelim}

function clearInput(inputField) {ldelim}
    if(inputField.value.length == 0)  {ldelim}
        document.getElementById('kupon').disabled = false;
        document.getElementById('fGuthaben').disabled = false;
        document.getElementById('nBonuspunkte').disabled = false;
    {rdelim}
{rdelim}
</script>

<div id="page">
    <div id="content" class="container-fluid">
        <div id="welcome" class="panel panel-default post">
            <div class="panel-heading">
                <h3 class="panel-title">{#umfrageEnter#}</h3>
            </div>
            <div class="panel-body">
                <form name="umfrage" method="post" action="umfrage.php">
                    {$jtl_token}
                    <input type="hidden" name="umfrage" value="1" />
                    <input type="hidden" name="umfrage_speichern" value="1" />
                    <input type="hidden" name="tab" value="umfrage" />
                    <input type="hidden" name="s1" value="{if !empty($s1)}{$s1}{else}0{/if}" />
                    {if isset($oUmfrage->kUmfrage) && $oUmfrage->kUmfrage > 0}
                        <input type="hidden" name="umfrage_edit_speichern" value="1" />
                        <input type="hidden" name="kUmfrage" value="{$oUmfrage->kUmfrage}" />
                    {/if}
                    <table class="kundenfeld table" id="formtable">
                        <tr>
                            <td><label for="cName">{#umfrageName#}</label></td>
                            <td><input class="form-control" id="cName" name="cName" type="text"  value="{if isset($oUmfrage->cName)}{$oUmfrage->cName}{/if}" /></td>
                        </tr>
                        <tr>
                            <td><label for="cSeo">{#umfrageSeo#}</label></td>
                            <td><input class="form-control" id="cSeo" name="cSeo" type="text"  value="{if isset($oUmfrage->cSeo)}{$oUmfrage->cSeo}{/if}" /></td>
                        </tr>
                        <tr>
                            <td><label for="kKundengruppe">{#umfrageCustomerGrp#}</label></td>
                            <td>
                                <select id="kKundengruppe" name="kKundengruppe[]" multiple="multiple" class="combo form-control">
                                    <option value="-1" {if isset($oUmfrage->kKundengruppe_arr)}{foreach name=kundengruppen from=$oUmfrage->kKundengruppe_arr item=kKundengruppe}{if $kKundengruppe == '-1'}selected{/if}{/foreach}{/if}>Alle</option>
                                {foreach name=kundengruppen from=$oKundengruppe_arr item=oKundengruppe}
                                    <option value="{$oKundengruppe->kKundengruppe}" {if isset($oUmfrage->kKundengruppe_arr)}{foreach name=kkundengruppen from=$oUmfrage->kKundengruppe_arr item=kKundengruppe}{if $oKundengruppe->kKundengruppe == $kKundengruppe}selected{/if}{/foreach}{/if}>{$oKundengruppe->cName}</option>
                                {/foreach}
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="dGueltigVon">{#umfrageValidation#}</label></td>
                            <td>
                                <input class="form-control" id="dGueltigVon" name="dGueltigVon" type="text"  value="{if isset($oUmfrage->dGueltigVon_de) && $oUmfrage->dGueltigVon_de|strlen > 0}{$oUmfrage->dGueltigVon_de}{else}{$smarty.now|date_format:'%d.%m.%Y %H:%M'}{/if}" style="width: 150px;" />
                                <label for="dGueltigBis">{#umfrageTo#}</label>
                                <input class="form-control" id="dGueltigBis" name="dGueltigBis" type="text"  value="{if isset($oUmfrage->dGueltigBis_de)}{$oUmfrage->dGueltigBis_de}{/if}" style="width: 150px;" />
                            </td>
                        </tr>
                        <tr>
                            <td><label for="nAktiv">{#umfrageActive#}:</label></td>
                            <td>
                                <select id="nAktiv" name="nAktiv" class="combo form-control" style="width: 80px;">
                                    <option value="1"{if isset($oUmfrage->nAktiv) && $oUmfrage->nAktiv == 1} selected{/if}>Ja</option>
                                    <option value="0"{if isset($oUmfrage->nAktiv) && $oUmfrage->nAktiv == 0} selected{/if}>Nein</option>
                                </select>
                            </td>
                        </tr>
                        {if $oKupon_arr|@count > 0 && $oKupon_arr}
                            <tr>
                                <td><label for="kupon">{#umfrageCoupon#}:</label></td>
                                <td valign="top">
                                    <select id="kupon" name="kKupon" class="combo form-control" onchange="selectCheck(this);" style="width: 180px;">
                                        <option value="0"{if isset($oUmfrage->kKupon) && $oUmfrage->kKupon == 0} selected{/if} index=0>{#umfrageNoCoupon#}</option>
                                        {foreach name=kupon from=$oKupon_arr item=oKupon}
                                            <option value="{$oKupon->kKupon}"{if isset($oUmfrage->kKupon) && $oKupon->kKupon == $oUmfrage->kKupon} selected{/if}>{$oKupon->cName}</option>
                                        {/foreach}
                                    </select>
                                </td>
                            </tr>
                        {/if}
                        <tr>
                            <td><label for="fGuthaben">{#umfrageCredits#}:</label></td>
                            <td><input class="form-control" name="fGuthaben" id="fGuthaben" type="text"  value="{if isset($oUmfrage->fGuthaben)}{$oUmfrage->fGuthaben}{/if}" onclick="checkInput(this,'fGuthaben');" onblur="clearInput(this);" /></td>
                            <input id="nBonuspunkte" type="hidden" />{*placeholder to avoid js errors*}
                        </tr>
                        <tr>
                            <td><label for="cBeschreibung">{#umfrageText#}:</label></td>
                            <td><textarea id="cBeschreibung" class="ckeditor" name="cBeschreibung" rows="15" cols="60">{if isset($oUmfrage->cBeschreibung)}{$oUmfrage->cBeschreibung}{/if}</textarea></td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="panel-footer">
                <div class="btn-group">
                    <button class="btn btn-primary" name="speichern" type="button" value="{#umfrageSave#}" onclick="document.umfrage.submit();"><i class="fa fa-save"></i> {#umfrageSave#}</button>
                    <a class="btn btn-default" href="umfrage.php"><i class="fa fa-angle-double-left"></i> Zur&uuml;ck</a>
                </div>
            </div>
        </div>
    </div>
</div>