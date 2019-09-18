{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

vielen Dank für Ihre Bestellung bei {$Einstellungen.global.global_shopname}.

{if $Verfuegbarkeit_arr.cArtikelName_arr|@count > 0}
{$Verfuegbarkeit_arr.cHinweis}
{foreach from=$Verfuegbarkeit_arr.cArtikelName_arr item=cArtikelname}
{$cArtikelname}

{/foreach}

{/if}
Ihre Bestellung mit Bestellnummer {$Bestellung->cBestellNr} umfasst folgende Positionen:

{foreach name=pos from=$Bestellung->Positionen item=Position}
{if $Position->nPosTyp==1}
{if !empty($Position->kKonfigitem)} * {/if}{$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{if isset($Position->Artikel->nErscheinendesProdukt) && $Position->Artikel->nErscheinendesProdukt}
Verfügbar ab: {$Position->Artikel->Erscheinungsdatum_de}{/if}{if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen=="Y" && $Position->cLieferstatus}

Lieferzeit: {$Position->cLieferstatus}{/if}
{foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}

{$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}{/foreach}
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

Lieferzeit: {if isset($Bestellung->cEstimatedDeliveryEx)}{$Bestellung->cEstimatedDeliveryEx}{else}{$Bestellung->cEstimatedDelivery}{/if}


Ihre Rechnungsadresse:

{if !empty($Kunde->cFirma)}{$Kunde->cFirma}{/if}
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

{if !empty($Bestellung->Lieferadresse->kLieferadresse)}
Ihre Lieferadresse:

{if !empty($Bestellung->Lieferadresse->cFirma)}{$Bestellung->Lieferadresse->cFirma}{/if}
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

{if $Bestellung->Zahlungsart->cModulId=="za_ueberweisung_jtl"}
Bitte führen Sie die folgende Überweisung durch:

Kontoinhaber:{$Firma->cKontoinhaber}
Bankinstitut:{$Firma->cBank}
IBAN:{$Firma->cIBAN}
BIC:{$Firma->cBIC}

Verwendungszweck:{$Bestellung->cBestellNr}
Gesamtsumme:{$Bestellung->WarensummeLocalized[0]}

{elseif $Bestellung->Zahlungsart->cModulId=="za_nachnahme_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_kreditkarte_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_rechnung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_lastschrift_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_barzahlung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_paypal_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_moneybookers_jtl"}
{/if}

{if isset($Zahlungsart->cHinweisText) && $Zahlungsart->cHinweisText|strlen > 0}  {$Zahlungsart->cHinweisText}


{/if}

{if $Bestellung->Zahlungsart->cModulId=="za_rechnung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_lastschrift_jtl"}
Wir belasten in Kürze folgendes Bankkonto um die fällige Summe:

Kontoinhaber: {$Bestellung->Zahlungsinfo->cInhaber}
IBAN: {$Bestellung->Zahlungsinfo->cIBAN}
BIC: {$Bestellung->Zahlungsinfo->cBIC}
Bank: {$Bestellung->Zahlungsinfo->cBankName}

{elseif $Bestellung->Zahlungsart->cModulId=="za_barzahlung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_paypal_jtl"}
Falls Sie Ihre Zahlung per PayPal noch nicht durchgeführt haben, nutzen Sie folgende Emailadresse als Empfänger: {$Einstellungen.zahlungsarten.zahlungsart_paypal_empfaengermail}
{elseif $Bestellung->Zahlungsart->cModulId=="za_moneybookers_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_billpay_invoice_jtl" || $Bestellung->Zahlungsart->cModulId=="za_billpay_rate_payment_jtl"}
Bitte überweisen Sie den Gesamtbetrag auf folgendes Konto:

Kontoinhaber: {$Bestellung->Zahlungsinfo->cInhaber}
Bankinstitut: {$Bestellung->Zahlungsinfo->cBankName}
IBAN: {$Bestellung->Zahlungsinfo->cIBAN}
BIC: {$Bestellung->Zahlungsinfo->cBIC}
Verwendungszweck: {$Bestellung->Zahlungsinfo->cVerwendungszweck}
{elseif $Bestellung->Zahlungsart->cModulId=="za_billpay_direct_debit_jtl"}
Vielen Dank, dass Sie sich beim Kauf der Ware für die BillPay Lastschrift entschieden haben.
Wir buchen den Rechnungsbetrag in den nächsten Tagen von dem bei der Bestellung angegebenen Konto ab.
{elseif $Bestellung->Zahlungsart->cModulId=="za_billpay_paylater_jtl"}
Vielen Dank, dass Sie sich für die Zahlung mit PayLater entschieden haben.
Die fälligen Beträge werden von dem bei der Bestellung angegebenen Konto abgebucht. 
Zusätzlich zu dieser Rechnung bekommen Sie von BillPay in Kürze einen Teilzahlungsplan mit detaillierten Informationen über Ihre Teilzahlung.
{/if}

Über den weiteren Verlauf Ihrer Bestellung werden wir Sie jeweils gesondert informieren.

{if !empty($oTrustedShopsBewertenButton->cURL)}
Waren Sie mit Ihrer Bestellung zufrieden? Dann würden wir uns über eine Empfehlung freuen ... es dauert auch nur eine Minute.
{$oTrustedShopsBewertenButton->cURL}
{/if}

Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}