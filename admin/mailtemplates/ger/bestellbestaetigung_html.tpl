{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
vielen Dank für Ihre Bestellung bei {$Einstellungen.global.global_shopname}.<br>
<br>
{if $Verfuegbarkeit_arr.cArtikelName_arr|@count > 0}
{$Verfuegbarkeit_arr.cHinweis}
<table cellpadding="0" cellspacing="0" border="0">
    {foreach from=$Verfuegbarkeit_arr.cArtikelName_arr item=cArtikelname}
    <tr>
        <td width="18">&bull;</td>
        <td align="left" valign="middle">{$cArtikelname}</td>
    </tr>
    {/foreach}
</table><br>
{/if}
Ihre Bestellung mit Bestellnummer {$Bestellung->cBestellNr} umfasst folgende Positionen:<br>
<br>
{foreach name=pos from=$Bestellung->Positionen item=Position}
    <table cellpadding="10" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
        <tr>
            <td class="column" {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === "Y"}width="50%"{else}width="70%"{/if} align="left" valign="top">
                {if $Position->nPosTyp==1}
                    {if !empty($Position->kKonfigitem)} * {/if}<strong>{$Position->cName}</strong> {if $Position->cArtNr}({$Position->cArtNr}){/if}
                    {if isset($Position->Artikel->nErscheinendesProdukt) && $Position->Artikel->nErscheinendesProdukt}
                        <br>Verfügbar ab: <strong>{$Position->Artikel->Erscheinungsdatum_de}</strong>
                    {/if}
                    {if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen === "Y" && $Position->cLieferstatus}
                        <br><small>Lieferzeit: {$Position->cLieferstatus}</small>
                    {/if}<br>
                    {foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}
                        <br><strong>{$WKPosEigenschaft->cEigenschaftName}</strong>: {$WKPosEigenschaft->cEigenschaftWertName}
                    {/foreach}
                {else}
                    <strong>{$Position->cName}</strong>
                {/if}
            </td>
            <td class="column" width="10%" align="left" valign="top">
                <strong class="mobile-only">Anzahl:</strong> {$Position->nAnzahl}
            </td>
            {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === "Y" && isset($Position->cEinzelpreisLocalized)}
                <td class="column" width="20%" align="right" valign="top">
                    {$Position->cEinzelpreisLocalized[$NettoPreise]}
                </td>
            {/if}
            <td class="column" width="20%" align="right" valign="top">
                {$Position->cGesamtpreisLocalized[$NettoPreise]}
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
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-bottom: 1px dotted #929292;">
    <tr>
        <td class="column mobile-left" width="50%" align="left" valign="top">
            <strong>Lieferzeit:</strong>
        </td>
        <td class="column mobile-left" width="50%" align="right" valign="top">
            {if isset($Bestellung->cEstimatedDeliveryEx)}{$Bestellung->cEstimatedDeliveryEx}{else}{$Bestellung->cEstimatedDelivery}{/if}
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
                            {if !empty($Kunde->cFirma)}{$Kunde->cFirma}<br>{/if}
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
                            <strong>Email:</strong>
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
{if !empty($Bestellung->Lieferadresse->kLieferadresse)}
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
                                {if !empty($Bestellung->Lieferadresse->cFirma)}{$Bestellung->Lieferadresse->cFirma}<br>{/if}
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
{/if}
Sie haben folgende Zahlungsart gewählt: {$Bestellung->cZahlungsartName}<br>
<br>
{if $Bestellung->Zahlungsart->cModulId === "za_ueberweisung_jtl"}
    <strong>Bitte führen Sie die folgende Überweisung durch:</strong><br>
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
                                {$Firma->cKontoinhaber}
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
                                <strong>Bankinstitut:</strong>
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
                                {$Firma->cBank}
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
                                <strong>IBAN:</strong>
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
                                {$Firma->cIBAN}
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
                                <strong>BIC:</strong>
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
                                {$Firma->cBIC}
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
                                <strong>Verwendungszweck:</strong>
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
                                {$Bestellung->cBestellNr}
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
                                <strong>Gesamtsumme:</strong>
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
                                <strong>{$Bestellung->WarensummeLocalized[0]}</strong>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
{elseif $Bestellung->Zahlungsart->cModulId === "za_nachnahme_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId === "za_kreditkarte_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId === "za_rechnung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId === "za_lastschrift_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId === "za_barzahlung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId === "za_paypal_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId === "za_moneybookers_jtl"}
{/if}
{if isset($Zahlungsart->cHinweisText) && $Zahlungsart->cHinweisText|strlen > 0}
    {$Zahlungsart->cHinweisText}<br>
    <br>
{/if}
{if $Bestellung->Zahlungsart->cModulId === "za_rechnung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId === "za_lastschrift_jtl"}
    Wir belasten in Kürze folgendes Bankkonto um die fällige Summe:<br>
    <br>
    Kontoinhaber: {$Bestellung->Zahlungsinfo->cInhaber}<br>
    IBAN: {$Bestellung->Zahlungsinfo->cIBAN}<br>
    BIC: {$Bestellung->Zahlungsinfo->cBIC}<br>
    Bank: {$Bestellung->Zahlungsinfo->cBankName}<br>
    <br>
{elseif $Bestellung->Zahlungsart->cModulId === "za_barzahlung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId === "za_paypal_jtl"}
    Falls Sie Ihre Zahlung per PayPal noch nicht durchgeführt haben, nutzen Sie folgende Emailadresse als Empfänger: {$Einstellungen.zahlungsarten.zahlungsart_paypal_empfaengermail}
{elseif $Bestellung->Zahlungsart->cModulId === "za_moneybookers_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_billpay_invoice_jtl" || $Bestellung->Zahlungsart->cModulId=="za_billpay_rate_payment_jtl"}
    <strong>Bitte überweisen Sie den Gesamtbetrag auf folgendes Konto:</strong><br>
    <br>
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
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
                                <strong>Bankinstitut:</strong>
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
        <tr>
            <td class="column mobile-left" align="right" valign="top">
                <table cellpadding="0" cellspacing="6">
                    <tr>
                        <td>
                            <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                <strong>IBAN:</strong>
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
                                {$Bestellung->Zahlungsinfo->cIBAN}
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
                                <strong>BIC:</strong>
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
                                {$Bestellung->Zahlungsinfo->cBIC}
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
                                <strong>Verwendungszweck:</strong>
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
                                {$Bestellung->Zahlungsinfo->cVerwendungszweck}
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
{elseif $Bestellung->Zahlungsart->cModulId=="za_billpay_direct_debit_jtl"}
Vielen Dank, dass Sie sich beim Kauf der Ware für die BillPay Lastschrift entschieden haben.<br />
Wir buchen den Rechnungsbetrag in den nächsten Tagen von dem bei der Bestellung angegebenen Konto ab.<br />
<br />
{elseif $Bestellung->Zahlungsart->cModulId=="za_billpay_paylater_jtl"}
Vielen Dank, dass Sie sich für die Zahlung mit PayLater entschieden haben.<br />
Die fälligen Beträge werden von dem bei der Bestellung angegebenen Konto abgebucht.<br />
Zusätzlich zu dieser Rechnung bekommen Sie von BillPay in Kürze einen Teilzahlungsplan mit detaillierten Informationen über Ihre Teilzahlung.<br />
<br />
{/if}
Über den weiteren Verlauf Ihrer Bestellung werden wir Sie jeweils gesondert informieren.<br>
<br>
{if !empty($oTrustedShopsBewertenButton->cURL)}
    Waren Sie mit Ihrer Bestellung zufrieden? Dann würden wir uns über eine Empfehlung freuen ... es dauert auch nur eine Minute.<br>
    <a href="{$oTrustedShopsBewertenButton->cURL}"><img src="{$oTrustedShopsBewertenButton->cPicURL}" alt="Bewerten Sie uns!"></a><br><br>
{/if}<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}