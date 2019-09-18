{includeMailTemplate template=header type=plain}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

Thank you for your order at {$Einstellungen.global.global_shopname}.

{if $Verfuegbarkeit_arr.cArtikelName_arr|@count > 0}
    {$Verfuegbarkeit_arr.cHinweis}
    {foreach from=$Verfuegbarkeit_arr.cArtikelName_arr item=cArtikelname}
        {$cArtikelname}

    {/foreach}
{/if}

Your order with the order number {$Bestellung->cBestellNr} consists of the following items:

{foreach name=pos from=$Bestellung->Positionen item=Position}
    {if $Position->nPosTyp==1}
        {if !empty($Position->kKonfigitem)} * {/if}{$Position->nAnzahl}x {$Position->cName} {if $Position->cArtNr}({$Position->cArtNr}){/if} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen=="Y" && $Position->cLieferstatus}

        Shipping time: {$Position->cLieferstatus}{/if}
        {foreach name=variationen from=$Position->WarenkorbPosEigenschaftArr item=WKPosEigenschaft}

            {$WKPosEigenschaft->cEigenschaftName}: {$WKPosEigenschaft->cEigenschaftWertName}{/foreach}
    {else}
        {$Position->nAnzahl}x {$Position->cName} - {$Position->cGesamtpreisLocalized[$NettoPreise]}{/if}
{/foreach}

{if $Einstellungen.global.global_steuerpos_anzeigen!="N"}{foreach name=steuerpositionen from=$Bestellung->Steuerpositionen item=Steuerposition}
    {$Steuerposition->cName}: {$Steuerposition->cPreisLocalized}
{/foreach}{/if}
{if isset($Bestellung->GuthabenNutzen) && $Bestellung->GuthabenNutzen==1}
    Voucher: -{$Bestellung->GutscheinLocalized}
{/if}

Total: {$Bestellung->WarensummeLocalized[0]}

Shipping time: {if isset($Bestellung->cEstimatedDeliveryEx)}{$Bestellung->cEstimatedDeliveryEx}{else}{$Bestellung->cEstimatedDelivery}{/if}


Your billing address:

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
{if $Kunde->cUSTID}VAT ID: {$Kunde->cUSTID}
{/if}

{if !empty($Bestellung->Lieferadresse->kLieferadresse)}
    Your shipping address:

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
{/if}

You have chosen the following payment option: {$Bestellung->cZahlungsartName}

{if $Bestellung->Zahlungsart->cModulId=="za_ueberweisung_jtl"}
    Please make the following banktransfer:
    Account owner:{$Firma->cKontoinhaber}
    Bank name:{$Firma->cBank}
    IBAN:{$Firma->cIBAN}
    BIC:{$Firma->cBIC}

    Purpose:{$Bestellung->cBestellNr}
    Total sum:{$Bestellung->WarensummeLocalized[0]}


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
{elseif $Bestellung->Zahlungsart->cModulId=="za_barzahlung_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_paypal_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_moneybookers_jtl"}
{elseif $Bestellung->Zahlungsart->cModulId=="za_billpay_invoice_jtl"}
	Please transfer the total amount to following account:
	Account Holder: {$Bestellung->Zahlungsinfo->cInhaber}
	Bank name: {$Bestellung->Zahlungsinfo->cBankName}
	IBAN: {$Bestellung->Zahlungsinfo->cIBAN}
	BIC: {$Bestellung->Zahlungsinfo->cBIC}
	Purpose: {$Bestellung->Zahlungsinfo->cVerwendungszweck}
{/if}

You will be notified of the subsequent status of your order separately.

{if !empty($oTrustedShopsBewertenButton->cURL)}
    Were you satisfied with your order? If so, we hope you'll take a minute to write a recommendation.
    {$oTrustedShopsBewertenButton->cURL}
{/if}


Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}