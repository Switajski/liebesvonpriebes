{includeMailTemplate template=header type=plain}

Hallo {$Nachricht->cName},

anbei kann ich dir nur den folgenden Artikel empfehlen:

Schau ihn dir doch mal an: {$Artikel->cName} - {$ShopURL}/{$Artikel->cURL}

Vielen Dank.

Mit freundlichem Gruß,
{$VonKunde->cVorname} {$VonKunde->cNachname}

{includeMailTemplate template=footer type=plain}