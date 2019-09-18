<div class="alert alert-info">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_create_description}</div>

<div class="alert alert-warning lpa-error-message" id="lpa-error-packstation" style="display:none;">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_packstation_disallowed}</div>
<div class="alert alert-danger lpa-error-message" id="lpa-error-technical" style="display:none;">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_technical_error}</div>
{if isset($fehlendeAngaben) && 
    (isset($fehlendeAngaben.vorname) 
    || isset($fehlendeAngaben.nachname) 
    || isset($fehlendeAngaben.strasse) 
    || isset($fehlendeAngaben.hausnummer)
    || isset($fehlendeAngaben.plz)
    || isset($fehlendeAngaben.ort))}
    <div class="alert alert-danger" id="lpa-error-address">{$oPlugin->oPluginSprachvariableAssoc_arr.lpa_address_error}</div>
    {/if}
{* This is basically the form for the account registration, however, it ONLY shows required fields and prefills name and email with the data from amazon *}
<div class="panel panel-default">
    <div class="panel-body">
        <form class="form" name="create_account" id="lpa-create-account-form" method="post" action="{$lpa_create_url_localized}">
            <div id="addressBookWidgetDiv" style="display:inline-block; width: 100%; height: 300px;">
            </div>
            <script type="text/javascript">
                {literal}
                    var addressBookInitFunc = function () {
                        new OffAmazonPayments.Widgets.AddressBook({
                            sellerId: '{/literal}{if isset($lpa_seller_id)}{$lpa_seller_id}{/if}{literal}',
                            onOrderReferenceCreate: function (orderReference) {
                                window.currentOrderReference = orderReference;
                                $('#lpa-orid-input').val(orderReference.getAmazonOrderReferenceId());
                            },
                            onAddressSelect: function (orderReference) {
                                lpa_addressSelectedOnCreate(window.currentOrderReference);
                            },
                            design: {
                                designMode: 'responsive'
                            },
                            onError: function (error) {
                                // your error handling code
                                console.log(error.getErrorCode() + ': ' + error.getErrorMessage());
                            }
                        }).bind("addressBookWidgetDiv");
                    };
                    if (typeof window.lpaCallbacks === "undefined") {
                        window.lpaCallbacks = [];
                    }
                    window.lpaCallbacks.push(addressBookInitFunc);
                {/literal}
            </script>

            <br />

            {* hidden fields set by the amazon address widget or during login *}
            {$jtl_token}
            <input type="hidden" name="verification_code" value="{$lpa_login_verification_code}" />
            <input type="hidden" name="amazon_id" value="{$lpa_login_amazon_id}" />
            <input type="hidden" name="editRechnungsadresse" value="0" />

            <input type="hidden" name="vorname" value="{if isset($lpa_first_name)}{$lpa_first_name}{elseif isset($Kunde)}{$Kunde->cVorname}{/if}" id="firstName" />
            <input type="hidden" name="nachname" value="{if isset($lpa_last_name)}{$lpa_last_name}{elseif isset($Kunde)}{$Kunde->cNachname}{/if}" id="lastName" />
            <input type="hidden" name="strasse" value="{if isset($Kunde)}{$Kunde->cStrasse}{/if}" id="street" />
            <input type="hidden" name="hausnummer" value="{if isset($Kunde)}{$Kunde->cHausnummer}{/if}" id="streetnumber" />
            <input type="hidden" name="land" value="{if isset($Kunde) && (!$Kunde->cLand)}{$Einstellungen.kunden.kundenregistrierung_standardland}{elseif isset($Kunde)}{$Kunde->cLand}{/if}" id="country" />
            <input type="hidden" name="plz" value="{if isset($Kunde)}{$Kunde->cPLZ}{/if}" id="plz" />
            <input type="hidden" name="ort" value="{if isset($Kunde)}{$Kunde->cOrt}{/if}" id="city" class="city_input" />
            <input type="hidden" name="email" value="{if isset($lpa_email)}{$lpa_email}{elseif isset($Kunde)}{$Kunde->cMail}{/if}" id="email" />
            {if $Einstellungen.kunden.kundenregistrierung_abfragen_firma!="Y"}
                {* Firma can be set by amazon as well, but it is not an always required field *}
                <input type="hidden" name="firma" value="{if isset($Kunde)}{$Kunde->cFirma}{/if}" id="firm" />
            {/if}
            {if $Einstellungen.kunden.kundenregistrierung_abfragen_tel!="Y"}
                {* Tel can be set by amazon as well, but it is not an always required field *}
                <input type="hidden" name="tel" value="{if isset($Kunde)}{$Kunde->cTel}{/if}" id="tel" />
            {/if}
            {if $Einstellungen.kunden.kundenregistrierung_abfragen_anrede!="N" || $Einstellungen.kunden.kundenregistrierung_abfragen_titel!="N" || $Einstellungen.kunden.kundenregistrierung_abfragen_firma!="N" || $Einstellungen.kunden.kundenregistrierung_abfragen_firmazusatz!="N" || $Einstellungen.kunden.kundenregistrierung_abfragen_adresszusatz!="N" || $Einstellungen.kunden.kundenregistrierung_abfragen_bundesland!="N" || $Einstellungen.kunden.kundenregistrierung_abfragen_ustid!="N"}
                <fieldset class="col-xs-12">


                    {if !$lpa_template_mobile}<legend>{lang key="address" section="account data"}</legend>{/if}
                    <div class="row">
                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_anrede!="N"}
                            <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.anrede>0} has-error{/if}">
                                <label class="control-label" for="salutation">{lang key="salutation" section="account data"}:</label>
                                <select class="form-control" name="anrede" id="salutation" required>
                                    <option value="" selected="selected">{lang key="pleaseChoose" section="global"}</option>
                                    <option value="w" {if isset($Kunde) && $Kunde->cAnrede == "w"}selected="selected"{/if}>{$Anrede_w}</option>
                                    <option value="m" {if isset($Kunde) && $Kunde->cAnrede == "m"}selected="selected"{/if}>{$Anrede_m}</option>
                                </select>
                                {if isset($fehlendeAngaben) && $fehlendeAngaben.anrede>0}<div class="alert alert-danger">{lang key="fillOut" section="global"}</div>{/if}
                            </div>
                        {/if}

                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_titel!="N" && $Einstellungen.kunden.kundenregistrierung_abfragen_titel=="Y"}
                            <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.titel>0} has-error{/if}">
                                <label class="control-label" for="title">{lang key="title" section="account data"}:</label>
                                <input class="form-control" type="text" name="titel" value="{$Kunde->cTitel}" id="title" required />
                                {if isset($fehlendeAngaben) && $fehlendeAngaben.titel>0}<div class="alert alert-danger">{lang key="fillOut" section="global"}</div>{/if}
                            </div>
                        {/if}
                    </div>

                    <div class="row">

                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_firma != 'N' && $Einstellungen.kunden.kundenregistrierung_abfragen_firma=="Y"}
                            <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.firma>0} has-error{/if}">
                                <label class="control-label" for="firm">{lang key="firm" section="account data"}:</label>
                                <input class="form-control" type="text" name="firma" value="{if isset($Kunde)}{$Kunde->cFirma}{/if}" id="firm" required />
                                {if isset($fehlendeAngaben) && $fehlendeAngaben.firma>0}<div class="alert alert-danger">{lang key="fillOut" section="global"}</div>{/if}
                            </div>
                        {/if}

                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_firmazusatz != 'N' && $Einstellungen.kunden.kundenregistrierung_abfragen_firmazusatz=="Y"}
                            <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.firmazusatz>0} has-error{/if}">
                                <label class="control-label" for="firmext">{lang key="firmext" section="account data"}:</label>
                                <input class="form-control" type="text" name="firmazusatz" value="{if isset($Kunde)}{$Kunde->cZusatz}{/if}" id="firm2" required />
                                {if isset($fehlendeAngaben) && $fehlendeAngaben.firmazusatz>0}<div class="alert alert-danger">{lang key="fillOut" section="global"}</div>{/if}
                            </div>
                        {/if}
                    </div>

                    <div class="row">

                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_adresszusatz!="N" && $Einstellungen.kunden.kundenregistrierung_abfragen_adresszusatz=="Y"}
                            <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.adresszusatz>0} has-error{/if}">
                                <label class="control-label" for="street2">{lang key="street2" section="account data"}:</label>
                                <input class="form-control" type="text" name="adresszusatz" value="{if isset($Kunde)}{$Kunde->cAdressZusatz}{/if}" id="street2" required />
                                {if isset($fehlendeAngaben) && $fehlendeAngaben.adresszusatz>0}<div class="alert alert-danger">{lang key="fillOut" section="global"}</div>{/if}
                            </div>
                        {/if}
                    </div>

                    <div class="row">
                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_bundesland!="N" && $Einstellungen.kunden.kundenregistrierung_abfragen_bundesland=="Y"}
                            <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.bundesland>0} has-error{/if}">
                                <label class="control-label" for="state">{lang key="state" section="account data"}:</label>
                                <input class="form-control" type="text" title="{lang key=pleaseChoose}" name="bundesland" value="{if isset($Kunde)}{$Kunde->cBundesland}{/if}" id="state" required />
                                {if isset($fehlendeAngaben) && $fehlendeAngaben.bundesland>0}<div class="alert alert-danger">{lang key="fillOut" section="global"}</div>{/if}
                            </div>
                        {/if}
                    </div>

                    <div class="row">
                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_ustid!="N"} 
                            {if $Einstellungen.kunden.kundenregistrierung_abfragen_ustid=="Y"}
                            <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.ustid>0} has-error{/if}">
                                <label class="control-label" for="ustid">{lang key="ustid" section="account data"}:</label>
                                <input class="form-control" type="text" name="ustid" value="{if isset($Kunde)}{$Kunde->cUSTID}{/if}" id="ustid" required />
                                {if isset($fehlendeAngaben) && $fehlendeAngaben.ustid>0}
                                    <div class="alert alert-danger">
                                        {if $fehlendeAngaben.ustid==1}{lang key="fillOut" section="global"}
                                        {elseif $fehlendeAngaben.ustid==2}{lang key="ustIDCaseTwo" section="global"}. {if $fehlendeAngaben.ustid_err|count > 0 && $fehlendeAngaben.ustid_err != false}{lang key="ustIDCaseTwoB" section="global"}: {$fehlendeAngaben.ustid_err}{/if}
                                        {elseif $fehlendeAngaben.ustid==5}{lang key="ustIDCaseFive" section="global"}.{/if}
                                    </div>
                                {/if}
                            </div>
                            {else}
                                {* Workaround to avoid notice when USTID is optional - simply post an empty string *}
                                 <input class="form-control" type="hidden" name="ustid" value="" id="ustid"/>
                            {/if}
                        {/if}
                    </div>
                </fieldset>
            {/if}
            {if $Einstellungen.kunden.kundenregistrierung_abfragen_fax=="Y" || $Einstellungen.kunden.kundenregistrierung_abfragen_tel=="Y" || $Einstellungen.kunden.kundenregistrierung_abfragen_mobil=="Y" || $Einstellungen.kunden.kundenregistrierung_abfragen_www=="Y" || $Einstellungen.kunden.kundenregistrierung_abfragen_geburtstag=="Y"}
                <fieldset class="col-xs-12">
                    {if !$lpa_template_mobile}<legend>{lang key="contactInformation" section="account data"}</legend>{/if}


                    <div class="row">
                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_tel!="N" && $Einstellungen.kunden.kundenregistrierung_abfragen_tel=="Y"}
                            <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.tel>0} has-error{/if}">
                                <label class="control-label" for="tel">{lang key="tel" section="account data"}:</label>
                                <input type="text" class="form-control" name="tel" value="{if isset($Kunde)}{$Kunde->cTel}{/if}" id="tel" required />
                                {if isset($fehlendeAngaben) && $fehlendeAngaben.tel>0}
                                    <div class="alert alert-danger">{if $fehlendeAngaben.tel==1}{lang key="fillOut" section="global"}{elseif $fehlendeAngaben.tel==2}{lang key="invalidTel" section="global"}{/if}</div>
                                {/if}
                            </div>
                        {/if}
                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_fax!="N" && $Einstellungen.kunden.kundenregistrierung_abfragen_fax=="Y"}
                            <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.fax>0} has-error{/if}">
                                <label class="control-label" for="fax">{lang key="fax" section="account data"}:</label>
                                <input type="text" class="form-control" name="fax" value="{if isset($Kunde)}{$Kunde->cFax}{/if}" id="fax" required />
                                {if isset($fehlendeAngaben) && $fehlendeAngaben.fax>0}
                                    <div class="alert alert-danger">
                                        {if $fehlendeAngaben.fax==1}{lang key="fillOut" section="global"}
                                        {elseif $fehlendeAngaben.fax==2}{lang key="invalidTel" section="global"}
                                        {/if}
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    </div>
                    <div class="row">
                        {if $Einstellungen.kunden.kundenregistrierung_abfragen_mobil!="N" && $Einstellungen.kunden.kundenregistrierung_abfragen_mobil=="Y"}
                            <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.mobil>0} has-error{/if}">
                                <label class="control-label" for="mobile">{lang key="mobile" section="account data"}:</label>
                                <input type="text" class="form-control" name="mobil" value="{if isset($Kunde)}{$Kunde->cMobil}{/if}" id="mobile" required />
                                {if isset($fehlendeAngaben) && $fehlendeAngaben.mobil>0}
                                    <div class="alert alert-danger">
                                {if $fehlendeAngaben.mobil==1}{lang key="fillOut" section="global"}{elseif $fehlendeAngaben.mobil==2}{lang key="invalidTel" section="global"}{/if}
                            </div>
                        {/if}
                    </div>
                {/if}

                {if $Einstellungen.kunden.kundenregistrierung_abfragen_www!="N" && $Einstellungen.kunden.kundenregistrierung_abfragen_www=="Y"}
                    <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.www>0} has-error{/if}">
                        <label class="control-label" for="www">{lang key="www" section="account data"}:</label>
                        <input type="text" name="www" class="form-control" value="{if isset($Kunde)}{$Kunde->cWWW}{/if}" id="www" required />
                        {if isset($fehlendeAngaben) && $fehlendeAngaben.www>0}<div class="alert alert-danger">{lang key="fillOut" section="global"}</div>{/if}
                    </div>
                {/if}
            </div>
            <div class="row">
                {if $Einstellungen.kunden.kundenregistrierung_abfragen_geburtstag!="N" && $Einstellungen.kunden.kundenregistrierung_abfragen_geburtstag=="Y"}
                    <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.geburtstag>0} has-error{/if}">
                        <label class="control-label" for="birthday">{lang key="birthday" section="account data"}:</label>
                        <input type="text" class="form-control" name="geburtstag" value="{if isset($Kunde)}{$Kunde->dGeburtstag}{/if}" id="birthday" required class="birthday" />
                        {if isset($fehlendeAngaben) && $fehlendeAngaben.geburtstag>0}
                            <div class="alert alert-danger">
                    {if $fehlendeAngaben.geburtstag==1}{lang key="fillOut" section="global"}{elseif $fehlendeAngaben.geburtstag==2}{lang key="invalidDateformat" section="global"}{elseif $fehlendeAngaben.geburtstag==3}{lang key="invalidDate" section="global"}{/if}
                </div>
            {/if}
        </div>

        {if $lpa_shop3_compatibility === "1"}
            <script type="text/javascript">
                jQuery(document).ready(function (){ldelim}
                        $('input.birthday').simpleDatepicker({ldelim}startdate: '01.01.1900', chosendate: '{if isset($Kunde)}{$Kunde->dGeburtstag}{/if}', x: 0, y: $('input.birthday').outerHeight(){rdelim});
                {rdelim});
            </script>
        {/if}
    {/if}
</div>
</fieldset>
{/if}

{if $lpaEinstellungen.kundenfeld.kundenfeld_anzeigen == "Y" && $oKundenfeld_arr|@count > 0}
    <fieldset class="col-xs-12">
        <div class="row">
            {foreach name=kundenfeld from=$oKundenfeld_arr item=oKundenfeld}
                {if $oKundenfeld->nPflicht == 1}
                    {if ($oKundenfeld->nEditierbar >= 0 && $smarty.session.Kunde->kKunde == 0) || ($oKundenfeld->nEditierbar == 1 && $smarty.session.Kunde->kKunde > 0)}
                        {assign var=kKundenfeld value=$oKundenfeld->kKundenfeld}
                        <div class="col-xs-12 col-md-6 form-group required{if isset($fehlendeAngaben) && $fehlendeAngaben.custom[$kKundenfeld]>0} has-error{/if}">
                            <label class="control-label" for="custom_{$oKundenfeld->kKundenfeld}">{$oKundenfeld->cName}:</label>
                            {if $oKundenfeld->cTyp != "auswahl"}
                                <input type="text" class="form-control" name="custom_{$oKundenfeld->kKundenfeld}" id="custom_{$oKundenfeld->kKundenfeld}" value="{if $step == 'formular' || 'unregistriert bestellen'}{$cKundenattribut_arr[$kKundenfeld]->cWert}{else}{$Kunde->cKundenattribut_arr[$kKundenfeld]->cWert}{/if}" required />

                                {if isset($fehlendeAngaben) && $fehlendeAngaben.custom[$kKundenfeld]>0}
                                    <div class="alert alert-danger">
                        {if $fehlendeAngaben.custom[$kKundenfeld] == 1}{lang key="fillOut" section="global"}{elseif $fehlendeAngaben.custom[$kKundenfeld] == 2}{lang key="invalidDateformat" section="global"}{elseif $fehlendeAngaben.custom[$kKundenfeld] == 3}{lang key="invalidDate" section="global"}{elseif $fehlendeAngaben.custom[$kKundenfeld] == 4}{lang key="invalidInteger" section="global"}{/if}
                    </div>
                {/if}

            {else}
                <select name="custom_{$oKundenfeld->kKundenfeld}" class="form-control" required>
                    {foreach name=select from=$oKundenfeld->oKundenfeldWert_arr item=oKundenfeldWert}
                        <option value="{$oKundenfeldWert->cWert}">{$oKundenfeldWert->cWert}</option>
                    {/foreach}
                </select>
            {/if}
        </div>
    {/if}
{/if}
{/foreach}

</div>
</fieldset>
{/if}

{if !isset($smarty.session.cPlausi_arr)}
    {assign var=plausiArr value=array()}
{else}
    {assign var=plausiArr value=$smarty.session.cPlausi_arr}
{/if}
{if !isset($cPost_arr)}
    {assign var=postArr value=array()}
{else}
    {assign var=postArr value=$cPost_arr}
{/if}
{hasCheckBoxForLocation bReturn="bCheckBox" nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$postArr}
{if $bCheckBox}
    <div class="col-xs-12">
        {if $lpa_shop3_compatibility === "1"}
            <ul style="list-style-type: none;">
                {getCheckBoxForLocation nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr  cPost_arr=$postArr}
            </ul>
        {else}
            <hr>
            {include file='snippets/checkbox.tpl' nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$postArr}
            <hr>
        {/if}
    </div>
{/if}

<input type="submit" class="submit submit_once btn btn-primary" id="lpa-create-submit" style="display:none;" value="{lang key="sendCustomerData" section="account data"}" />
</form>
</div>
</div>