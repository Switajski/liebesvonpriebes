{includeMailTemplate template=header type=plain}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

Thank you very much for registering in our online shop at {$ShopURL}

Please review your account details:

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

Using these account details you can log into your personal account in future and track the current status of your order.

We are happy to welcome you as a new customer. If you have any questions on our range or special products, please simply contact us.

We hope you will enjoy exploring our range of products.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}