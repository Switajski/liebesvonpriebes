{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
Ihre Bestellung bei {$Einstellungen.global.global_shopname} wurde aktualisiert.<br>
<br>
Ihre Bestellung mit Bestellnummer {$Bestellung->cBestellNr} umfasst folgende Positionen:<br>
<br>
{foreach name=pos from=$Bestellung->Positionen item=Position}
    <table cellpadding="10" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
        <tr>
            <td class="column" {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen=="Y"}width="50%"{else}width="70%"{/if} align="left" valign="top">
                {if $Position->nPosTyp==1}
                    <strong>{$Position->cName} ({$Position->cArtNr})</strong>
                    {if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen=="Y" && $Position->cLieferstatus}
                        <br><small>Lieferzeit: {$Position->cLieferstatus}</small>
                    {/if}<br>
                    {foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}
                        <br><strong>{$WKPosEigenschaft->cEigenschaftName}</strong>: {$WKPosEigenschaft->cEigenschaftWertName}
                    {/foreach}
                    
                    {* Seriennummer *}
                    {if $Position->cSeriennummer|strlen > 0}
                        <br>Seriennummer: {$Position->cSeriennummer}
                    {/if}

                    {* MHD *}
                    {if $Position->dMHD|strlen > 0}
                        <br>Mindesthaltbarkeitsdatum: {$Position->dMHD_de}
                    {/if}

                    {* Charge *}
                    {if $Position->cChargeNr|strlen > 0}
                        <br>Charge: {$Position->cChargeNr}
                    {/if}
                {else}
                    <strong>{$Position->cName}</strong>
                {/if}
            </td>
            <td class="column" width="10%" align="left" valign="top">
                <strong class="mobile-only">Anzahl:</strong> {$Position->nAnzahl}
            </td>
            {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen=="Y"}
                <td class="column" width="20%" align="right" valign="top">
                    <span class="standard">{$Position->cEinzelpreisLocalized[$NettoPreise]}</span>
                </td>
            {/if}
            <td class="column" width="20%" align="right" valign="top">
                <span class="standard">{$Position->cGesamtpreisLocalized[$NettoPreise]}</span>
            </td>
        </tr>
    </table>
{/foreach}
<table cellpadding="10" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
    {if $Einstellungen.global.global_steuerpos_anzeigen!="N"}
        {foreach name=steuerpositionen from=$Bestellung->Steuerpositionen item=Steuerposition}
            <tr>
                <td align="right" valign="top">
                    {$Steuerposition->cName}:
                </td>
                <td width="90" align="right" valign="top">
                    {$Steuerposition->cPreisLocalized}
                </td>
            </tr>
        {/foreach}
    {/if}
    {if isset($Bestellung->GuthabenNutzen) && $Bestellung->GuthabenNutzen==1}
        <tr>
            <td align="right" valign="top">
                Gutschein:
            </td>
            <td width="90" align="right" valign="top">
                <strong>-{$Bestellung->GutscheinLocalized}</strong>
            </td>
        </tr>
    {/if}
    <tr>
        <td align="right" valign="top">
            <strong>Gesamtsumme:</strong>
        </td>
        <td width="90" align="right" valign="top">
            <strong>{$Bestellung->WarensummeLocalized[0]}</strong>
        </td>
    </tr>
</table><br>
<strong>Ihre Rechnungsadresse:</strong><br>
<br>
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
    <tr>
        <td class="column mobile-left" width="25%" align="right" valign="top">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            <strong>Anschrift:</strong>
                        </font>
                    </td>
                </tr>
            </table>
        </td>
        <td class="column" width="80%" align="left" valign="top" bgcolor="#ffffff">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            {$Kunde->cAnredeLocalized} {$Kunde->cVorname} {$Kunde->cNachname}<br>
                            {$Kunde->cStrasse} {$Kunde->cHausnummer}<br>
                            {if $Kunde->cAdressZusatz}{$Kunde->cAdressZusatz}<br>{/if}
                            {$Kunde->cPLZ} {$Kunde->cOrt}<br>
                            {if $Kunde->cBundesland}{$Kunde->cBundesland}<br>{/if}
                            <font style="text-transform: uppercase;">{$Kunde->cLand}</font>
                        </font>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    {if $Kunde->cTel}
    <tr>
        <td class="column mobile-left" align="right" valign="top">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            <strong>Telefon:</strong>
                        </font>
                    </td>
                </tr>
            </table>
        </td>
        <td class="column" align="left" valign="top" bgcolor="#ffffff">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            {$Kunde->cTel}
                        </font>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    {/if}
    {if $Kunde->cMobil}
    <tr>
        <td class="column mobile-left" align="right" valign="top">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            <strong>Mobil:</strong>
                        </font>
                    </td>
                </tr>
            </table>
        </td>
        <td class="column" align="left" valign="top" bgcolor="#ffffff">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            {$Kunde->cMobil}
                        </font>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    {/if}
    {if $Kunde->cFax}
    <tr>
        <td class="column mobile-left" align="right" valign="top">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            <strong>Fax:</strong>
                        </font>
                    </td>
                </tr>
            </table>
        </td>
        <td class="column" align="left" valign="top" bgcolor="#ffffff">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            {$Kunde->cFax}
                        </font>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    {/if}
    <tr>
        <td class="column mobile-left" align="right" valign="top">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            <strong>E-Mail:</strong>
                        </font>
                    </td>
                </tr>
            </table>
        </td>
        <td class="column" align="left" valign="top" bgcolor="#ffffff">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            {$Kunde->cMail}
                        </font>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    {if $Kunde->cUSTID}
    <tr>
        <td class="column mobile-left" align="right" valign="top">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            <strong>UstID:</strong>
                        </font>
                    </td>
                </tr>
            </table>
        </td>
        <td class="column" align="left" valign="top" bgcolor="#ffffff">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td>
                        <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                            {$Kunde->cUSTID}
                        </font>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    {/if}
    <tr>
        <td colspan="2" class="column" align="right" valign="top">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td></td>
                </tr>
            </table>
        </td>
    </tr>
</table><br>
{if $Bestellung->Lieferadresse->kLieferadresse>0}
    <strong>Ihre Lieferadresse:</strong><br>
    <br>
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
        <tr>
            <td class="column mobile-left" width="25%" align="right" valign="top">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                <strong>Anschrift:</strong>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="column" width="80%" align="left" valign="top" bgcolor="#ffffff">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Bestellung->Lieferadresse->cAnredeLocalized} {$Bestellung->Lieferadresse->cVorname} {$Bestellung->Lieferadresse->cNachname}<br>
                                {$Bestellung->Lieferadresse->cStrasse} {$Bestellung->Lieferadresse->cHausnummer}<br>
                                {if $Bestellung->Lieferadresse->cAdressZusatz}{$Bestellung->Lieferadresse->cAdressZusatz}<br>{/if}
                                {$Bestellung->Lieferadresse->cPLZ} {$Bestellung->Lieferadresse->cOrt}<br>
                                {if $Bestellung->Lieferadresse->cBundesland}{$Bestellung->Lieferadresse->cBundesland}<br>{/if}
                                <font style="text-transform: uppercase;">{$Bestellung->Lieferadresse->cLand}</font>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        {if $Bestellung->Lieferadresse->cTel}
        <tr>
            <td class="column mobile-left" align="right" valign="top">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                <strong>Telefon:</strong>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="column" align="left" valign="top" bgcolor="#ffffff">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Bestellung->Lieferadresse->cTel}
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        {/if}
        {if $Bestellung->Lieferadresse->cMobil}
        <tr>
            <td class="column mobile-left" align="right" valign="top">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                <strong>Mobil:</strong>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="column" align="left" valign="top" bgcolor="#ffffff">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Bestellung->Lieferadresse->cMobil}
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        {/if}
        {if $Bestellung->Lieferadresse->cFax}
        <tr>
            <td class="column mobile-left" align="right" valign="top">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                <strong>Fax:</strong>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="column" align="left" valign="top" bgcolor="#ffffff">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Bestellung->Lieferadresse->cFax}
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        {/if}
        {if $Bestellung->Lieferadresse->cMail}
        <tr>
            <td class="column mobile-left" align="right" valign="top">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                <strong>E-Mail:</strong>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="column" align="left" valign="top" bgcolor="#ffffff">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Bestellung->Lieferadresse->cMail}
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        {/if}
    <tr>
        <td colspan="2" class="column" align="right" valign="top">
            <table cellpadding="0" cellspacing="6">
                <tr>
                    <td></td>
                </tr>
            </table>
        </td>
    </tr>
    </table><br>
{else}
    Lieferadresse ist gleich Rechnungsadresse.<br>
    <br>
{/if}
Sie haben folgende Zahlungsart gewählt: {$Bestellung->cZahlungsartName}<br>
<br>
{if $Bestellung->Zahlungsart->cModulId=="za_rechnung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_lastschrift_jtl"}
    <strong>Wir belasten in Kürze folgendes Bankkonto um die fällige Summe:</strong><br>
    <br>
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
        <tr>
            <td class="column mobile-left" width="20%" align="right" valign="top">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                <strong>Kontoinhaber:</strong>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="column" width="80%" align="left" valign="top" bgcolor="#ffffff">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Bestellung->Zahlungsinfo->cInhaber}
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="column mobile-left" align="right" valign="top">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                <strong>KontoNr:</strong>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="column" align="left" valign="top" bgcolor="#ffffff">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Bestellung->Zahlungsinfo->cKontoNr}
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="column mobile-left" align="right" valign="top">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                <strong>BLZ:</strong>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="column" align="left" valign="top" bgcolor="#ffffff">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Bestellung->Zahlungsinfo->cBLZ}
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="column mobile-left" align="right" valign="top">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                <strong>Bank:</strong>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="column" align="left" valign="top" bgcolor="#ffffff">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Bestellung->Zahlungsinfo->cBankName}
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
{elseif $Bestellung->Zahlungsart->cModulId=="za_barzahlung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_paypal_jtl"}
    Falls Sie Ihre Zahlung per PayPal noch nicht durchgeführt haben, nutzen Sie folgende Emailadresse als Empfänger: {$Einstellungen.zahlungsarten.zahlungsart_paypal_empfaengermail}<br>
    <br>
{elseif $Bestellung->Zahlungsart->cModulId=="za_moneybookers_jtl"}
{/if}

{if isset($Zahlungsart->cHinweisText) && $Zahlungsart->cHinweisText|strlen > 0}
    {$Zahlungsart->cHinweisText}<br>
    <br>
{/if}
Über den weiteren Verlauf Ihrer Bestellung werden wir Sie jeweils gesondert informieren.

{if $oTrustedShopsBewertenButton->cURL|strlen > 0}
    <br><br>
    Waren Sie mit Ihrer Bestellung zufrieden? Dann würden wir uns über eine Empfehlung freuen ... es dauert auch nur eine Minute.<br>
    <a href="{$oTrustedShopsBewertenButton->cURL}"><img src="{$oTrustedShopsBewertenButton->cPicURL}" alt="Bewerten Sie uns!"></a>
{/if}<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}