{includeMailTemplate template=header type=plain}

Hello {$Nachricht->cName},

I can only recommend the following product for you:

Please take a look: {$Artikel->cName} - {$ShopURL}/{$Artikel->cURL}

Thank you!

With best regards,
{$VonKunde->cVorname} {$VonKunde->cNachname}

{includeMailTemplate template=footer type=plain}