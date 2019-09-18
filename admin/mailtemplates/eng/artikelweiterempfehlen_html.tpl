{includeMailTemplate template=header type=html}

Hello {$Nachricht->cName},<br>
<br>
I can only recommend the following product for you:<br>
<br>
Please take a look: <a href="{$ShopURL}/{$Artikel->cURL}">{$Artikel->cName}</a><br>
<br>
Thank you!<br>
<br>
With best regards,<br>
{$VonKunde->cVorname} {$VonKunde->cNachname}

{includeMailTemplate template=footer type=html}