{include file='tpl_inc/seite_header.tpl' cTitel=#trustedshops# cDokuURL=#trustedshopsURL#}
<div id="content" class="container-fluid">
    {if $bSOAP}
        <div class="block">
            <form name="sprache" method="post" action="trustedshops.php">
            {$jtl_token}
                <input type="hidden" name="sprachwechsel" value="1" />
                <div class="p25 left input-group">
                    <span class="input-group-addon">
                        <label for="ts-change-language">{#changeLanguage#}:</label>
                    </span>
                    <span class="input-group-wrap last">
                        <select id="ts-change-language" name="cISOSprache" class="form-control selectBox" onchange="document.sprache.submit();">
                            {foreach name=sprachen from=$Sprachen item=sprache}
                                <option value="{$sprache->cISOSprache}" {if $sprache->cISOSprache==$smarty.session.TrustedShops->oSprache->cISOSprache}selected{/if}>{$sprache->cNameSprache}</option>
                            {/foreach}
                        </select>
                    </span>
                </div>
            </form>
        </div>

        <form name="einstellen" method="post" action="trustedshops.php">
            {$jtl_token}
            <input type="hidden" name="kaeuferschutzeinstellungen" value="1" />
            <div class="settings panel panel-default">
                {assign var=open value=false}
                {foreach name=conf from=$oConfig_arr item=oConfig}
                    {if $oConfig->cWertName !== 'trustedshops_kundenbewertung_anzeigen'}
                        {if $oConfig->cConf === 'Y'}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="{$oConfig->cWertName}">{$oConfig->cName}</label>
                                </span>
                                <span class="input-group-wrap">
                                    {if $oConfig->cInputTyp === 'selectbox'}
                                        <select class="form-control" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}">
                                            {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                                <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                            {/foreach}
                                        </select>
                                    {elseif $oConfig->cInputTyp === 'listbox'}
                                        <select class="form-control" name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}">
                                            {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                                <option value="{$wert->kKundengruppe}" {foreach name=werte from=$oConfig->gesetzterWert item=gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                                            {/foreach}
                                        </select>
                                    {elseif $oConfig->cInputTyp === 'number'}
                                        <input class="form-control" type="number" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                                    {else}
                                        <input class="form-control" type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" value="{if isset($oConfig->gesetzterWert)}{$oConfig->gesetzterWert}{/if}" tabindex="1" />
                                    {/if}
                                </span>
                                {if $oConfig->cBeschreibung}
                                    <span class="input-group-addon">{getHelpDesc cDesc=$oConfig->cBeschreibung}</span>
                                {/if}
                            </div>
                        {else}
                            {if $oConfig->cName}<div class="panel-heading"><h3 class="panel-title">{$oConfig->cName}</h3></div><div class="panel-body">{/if}
                        {/if}
                    {/if}
                {/foreach}

                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="eType">Trusted Shops K&auml;uferschutz Typ</label>
                    </span>
                    <span class="input-group-wrap">
                        <select class="form-control" name="eType" id="eType">
                            <option value="EXCELLENCE"{if isset($oZertifikat->eType) && $oZertifikat->eType === 'EXCELLENCE'} selected{/if}>
                                EXCELLENCE
                            </option>
                        </select>
                    </span>
                    <span class="input-group-addon">{getHelpDesc cDesc="Trusted Shops K&auml;uferschutzvariante."}</span>
                </div>
                <div class="input-group">
                    <span class="input-group-addon">
                        <label for="tsId">Trusted Shops ID (tsId)</label>
                    </span>
                    <input class="form-control" type="text" name="tsId" id="tsId" value="{if isset($oZertifikat->cTSID)}{$oZertifikat->cTSID}{/if}" tabindex="1" />
                    <span class="input-group-addon">{getHelpDesc cDesc="Die vom Shopbetreiber eingegebene Zertifikats-ID."}</span>
                </div>

                <div id="p_wsUser" class="input-group">
                    <span class="input-group-addon">
                        <label for="wsUser">WebService User (wsUser)</label>
                    </span>
                    <input class="form-control" type="text" name="wsUser" id="wsUser" value="{if isset($oZertifikat->cWSUser)}{$oZertifikat->cWSUser}{/if}" tabindex="1" />
                    <span class="input-group-addon">{getHelpDesc cDesc="Der vom Shopbetreiber eingegebene Benutzername"}</span>
                </div>

                <div class="input-group" id="p_wsPassword">
                    <span class="input-group-addon">
                        <label for="wsPassword">WebService Passwort (wsPassword)</label>
                    </span>
                    <input class="form-control" type="text" name="wsPassword" id="wsPassword" value="{if isset($oZertifikat->cWSPasswort)}{$oZertifikat->cWSPasswort}{/if}" tabindex="1" />
                    <span class="input-group-addon">{getHelpDesc cDesc="Das vom Shopbetreiber eingegebene Passwort"}</span>
                </div>
                {if isset($oZertifikat->nAktiv) && $oZertifikat->nAktiv|strlen > 0 && $oZertifikat->nAktiv == 0}
                    <div class="alert alert-danger">{#tsDeaktiviated#}</div>
                {/if}
                <input type="hidden" name="kSprache" value="0" />
            </div>
            <div class="panel-footer">
                <div class="btn-group">
                    <button name="saveSettings" type="submit" value="{#settingsSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#settingsSave#}</button>
                    <button name="delZertifikat" type="submit" value="{#tsDelCertificate#}" class="btn btn-danger"><i class="fa fa-trash"></i> {#tsDelCertificate#}</button>
                </div>
            </div>
        </div>
        </form>

        {if isset($oZertifikat->eType) && $oZertifikat && $oZertifikat->eType == $TS_BUYERPROT_EXCELLENCE && $Einstellungen.trustedshops.trustedshops_nutzen === 'Y'}
            <form method="post" action="trustedshops.php" class="container-fluid">
                {$jtl_token}
                <input type="hidden" name="kaeuferschutzupdate" value="1" />
                <button name="tsupdate" type="submit" value="{#updateProduct#}" class="btn btn-default button reset"><i class="fa fa-refresh"></i> {#updateProduct#}</button>
                <br />
                <br />
            </form>
        {/if}
        {if isset($oKaeuferschutzProdukteDB->item) && $oKaeuferschutzProdukteDB->item|@count > 0}
            <table class="table">
                <tr>
                    <th class="th-1">{#tsProduct#}</th>
                    <th class="th-2">{#tsCoverage#}</th>
                    <th class="th-3">{#tsCurrency#}</th>
                </tr>
                {foreach name=kaeuferschutzprodukte from=$oKaeuferschutzProdukteDB->item item=oKaeuferschutzProdukt}
                    <tr class="tab_bg{$smarty.foreach.kaeuferschutzprodukte.iteration%2}">
                        <td class="TD1">{$oKaeuferschutzProdukt->cProduktID}</td>
                        <td class="TD2">{$oKaeuferschutzProdukt->nWert}</td>
                        <td class="TD3">{$oKaeuferschutzProdukt->cWaehrung}</td>
                    </tr>
                {/foreach}
            </table>
        {/if}
        {if $bAllowfopen || $bCURL}
            <div class="alert alert-info">
                {assign var=sessionSprachISO value=$smarty.session.TrustedShops->oSprache->cISOSprache}
                <p>
                    <a href="trustedshops.php?whatisrating=1">{#tsWhatIsRating#}</a>
                </p>
                {if $Sprachen[$sessionSprachISO]->cURLKundenBewertung|strlen > 0}
                    <p>
                        <a href="{$Sprachen[$sessionSprachISO]->cURLKundenBewertung}" target="_blank"><i class="fa fa-external-link"></i> {#tsRatingForm#}</a>
                    </p>
                {/if}
            </div>
            <div class="settings">
                <form method="post" action="trustedshops.php">
                    {$jtl_token}
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">{#tsRatingConfig#}</h3>
                        </div>
                        <div class="panel-body">
                            <input type="hidden" name="kundenbewertungeinstellungen" value="1" />
                            {foreach name=conf from=$oConfig_arr item=oConfig}
                                {if $oConfig->cConf === 'Y' && $oConfig->cWertName === 'trustedshops_kundenbewertung_anzeigen'}
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <label for="{$oConfig->cWertName}">{$oConfig->cName}</label>
                                        </span>
                                        <span class="input-group-wrap">
                                            {if $oConfig->cInputTyp === 'selectbox'}
                                                <select class="form-control" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}">
                                                    {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                                        <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                                                    {/foreach}
                                                </select>
                                            {/if}
                                        </span>
                                        {if $oConfig->cBeschreibung}
                                            <span class="input-group-addon">{getHelpDesc cDesc=$oConfig->cBeschreibung}</span>
                                        {/if}
                                    </div>
                                {/if}
                            {/foreach}

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <label for="kb-tsId">Trusted Shops ID (tsId)</label>
                                </span>
                                <input class="form-control" type="text" name="kb-tsId" id="kb-tsId" value="{if isset($oTrustedShopsKundenbewertung->cTSID)}{$oTrustedShopsKundenbewertung->cTSID}{/if}" tabindex="1" />
                                <span class="input-group-addon">{getHelpDesc cDesc="Die vom Shopbetreiber eingegebene Zertifikats-ID"}</span>
                            </div>
                            {if isset($Sprachen[$sessionSprachISO]->cURLKundenBewertungUebersicht) && $Sprachen[$sessionSprachISO]->cURLKundenBewertungUebersicht|strlen > 0}
                                <strong><a href="{$Sprachen[$sessionSprachISO]->cURLKundenBewertungUebersicht}" target="_blank" style="text-decoration: underline;">{#tsRatingOverview#}</a></strong>
                            {/if}
                        </div>
                        <div class="panel-footer">
                            <button type="submit" value="{#settingsSave#}" class="btn btn-primary"><i class="fa fa-save"></i> {#settingsSave#}</button>
                        </div>
                    </div>
                </form>
                {if isset($oTrustedShopsKundenbewertung->cTSID) && isset($oTrustedShopsKundenbewertung->nStatus)}
                    <div class="">
                        <form method="post" action="trustedshops.php">
                            {$jtl_token}
                            <input type="hidden" name="kundenbewertungupdate" value="1" />
                            {if isset($oTrustedShopsKundenbewertung->cTSID) && isset($oTrustedShopsKundenbewertung->nStatus) && $oTrustedShopsKundenbewertung->cTSID|strlen > 0 && $oTrustedShopsKundenbewertung->nStatus == 1}
                                <button class="btn btn-default" name="tsKundenbewertungDeActive" type="submit">{#tsRatingDeActivate#}</button>
                            {elseif isset($oTrustedShopsKundenbewertung->cTSID) && isset($oTrustedShopsKundenbewertung->nStatus) && $oTrustedShopsKundenbewertung->cTSID|strlen > 0 && $oTrustedShopsKundenbewertung->nStatus == 0}
                                <button class="btn btn-default" name="tsKundenbewertungActive" type="submit">{#tsRatingActivate#}</button>
                            {/if}
                        </form>
                    </div>
                {/if}
            </div>
        {else}
            <div class="alert alert-danger">{#tsNoTSCustomerRatingError#}:<br /><br />{#noCURLAndFopenError#}</div>
        {/if}
    {else}
        <div class="alert alert-danger">{#tsNoTSError#}:<br /><br />{#noSOAPError#}</div>
    {/if}
</div>