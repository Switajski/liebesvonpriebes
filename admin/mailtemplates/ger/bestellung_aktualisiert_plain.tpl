{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

Ihre Bestellung bei {$Einstellungen.global.global_shopname} wurde aktualisiert.

Ihre Bestellung mit Bestellnummer {$Bestellung->cBestellNr} umfasst folgende Positionen:

{foreach name=pos from=$Bestellung->Positionen item=Position}

{if $Position->nPosTyp==1}
{$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen=="Y" && $Position->cLieferstatus}

Lieferzeit: {$Position->cLieferstatus}{/if}
{foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}

{$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}{/foreach}
{if $Position->cSeriennummer|strlen > 0}
Seriennummer: {$Position->cSeriennummer}
{/if}
{if $Position->dMHD|strlen > 0}
Mindesthaltbarkeitsdatum: {$Position->dMHD_de}
{/if}
{if $Position->cChargeNr|strlen > 0}
Charge: {$Position->cChargeNr}
{/if}
{else}
{$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{/if}
{/foreach}

{if $Einstellungen.global.global_steuerpos_anzeigen!="N"}{foreach name=steuerpositionen from=$Bestellung->Steuerpositionen item=Steuerposition}
{$Steuerposition->cName}: {$Steuerposition->cPreisLocalized}
{/foreach}{/if}
{if isset($Bestellung->GuthabenNutzen) && $Bestellung->GuthabenNutzen==1}
Gutschein: -{$Bestellung->GutscheinLocalized}
{/if}

Gesamtsumme: {$Bestellung->WarensummeLocalized[0]}


Ihre Rechnungsadresse:

{$Kunde->cAnredeLocalized} {$Kunde->cVorname} {$Kunde->cNachname}
{$Kunde->cStrasse} {$Kunde->cHausnummer}
{if $Kunde->cAdressZusatz}{$Kunde->cAdressZusatz}
{/if}{$Kunde->cPLZ} {$Kunde->cOrt}
{if $Kunde->cBundesland}{$Kunde->cBundesland}
{/if}{$Kunde->cLand}
{if $Kunde->cTel}Tel: {$Kunde->cTel}
{/if}{if $Kunde->cMobil}Mobil: {$Kunde->cMobil}
{/if}{if $Kunde->cFax}Fax: {$Kunde->cFax}
{/if}
Email: {$Kunde->cMail}
{if $Kunde->cUSTID}UstID: {$Kunde->cUSTID}
{/if}

{if $Bestellung->Lieferadresse->kLieferadresse>0}
Ihre Lieferadresse:

{$Bestellung->Lieferadresse->cAnredeLocalized} {$Bestellung->Lieferadresse->cVorname} {$Bestellung->Lieferadresse->cNachname}
{$Bestellung->Lieferadresse->cStrasse} {$Bestellung->Lieferadresse->cHausnummer}
{if $Bestellung->Lieferadresse->cAdressZusatz}{$Bestellung->Lieferadresse->cAdressZusatz}
{/if}{$Bestellung->Lieferadresse->cPLZ} {$Bestellung->Lieferadresse->cOrt}
{if $Bestellung->Lieferadresse->cBundesland}{$Bestellung->Lieferadresse->cBundesland}
{/if}{$Bestellung->Lieferadresse->cLand}
{if $Bestellung->Lieferadresse->cTel}Tel: {$Bestellung->Lieferadresse->cTel}
{/if}{if $Bestellung->Lieferadresse->cMobil}Mobil: {$Bestellung->Lieferadresse->cMobil}
{/if}{if $Bestellung->Lieferadresse->cFax}Fax: {$Bestellung->Lieferadresse->cFax}
{/if}{if $Bestellung->Lieferadresse->cMail}Email: {$Bestellung->Lieferadresse->cMail}
{/if}
{else}
Lieferadresse ist gleich Rechnungsadresse.
{/if}

Sie haben folgende Zahlungsart gewählt: {$Bestellung->cZahlungsartName}

{if isset($Zahlungsart->cHinweisText) && $Zahlungsart->cHinweisText|strlen > 0}  {$Zahlungsart->cHinweisText}


{/if}

{if $Bestellung->Zahlungsart->cModulId=="za_rechnung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_lastschrift_jtl"}
Wir belasten in Kürze folgendes Bankkonto um die fällige Summe:

Kontoinhaber: {$Bestellung->Zahlungsinfo->cInhaber}
KontoNr: {$Bestellung->Zahlungsinfo->cKontoNr}
BLZ: {$Bestellung->Zahlungsinfo->cBLZ}
Bank: {$Bestellung->Zahlungsinfo->cBankName}

{elseif $Bestellung->Zahlungsart->cModulId=="za_barzahlung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_paypal_jtl"}
Falls Sie Ihre Zahlung per PayPal noch nicht durchgeführt haben, nutzen Sie folgende E-Mailadresse als Empfänger: {$Einstellungen.zahlungsarten.zahlungsart_paypal_empfaengermail}
{elseif $Bestellung->Zahlungsart->cModulId=="za_moneybookers_jtl"}
{/if}

Über den weiteren Verlauf Ihrer Bestellung werden wir Sie jeweils gesondert informieren.

{if !empty($oTrustedShopsBewertenButton->cURL)}
Waren Sie mit Ihrer Bestellung zufrieden? Dann würden wir uns über eine Empfehlung freuen ... es dauert auch nur eine Minute.
{$oTrustedShopsBewertenButton->cURL}
{/if}

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}