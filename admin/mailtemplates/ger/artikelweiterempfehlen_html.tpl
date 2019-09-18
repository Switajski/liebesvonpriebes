{includeMailTemplate template=header type=html}

Hallo {$Nachricht->cName},<br>
<br>
anbei kann ich dir nur den folgenden Artikel empfehlen:<br>
<br>
Schau ihn dir doch mal an: <a href="{$ShopURL}/{$Artikel->cURL}">{$Artikel->cName}</a><br>
<br>
Vielen Dank.<br>
<br>
Mit freundlichem Gruﬂ,<br>
{$VonKunde->cVorname} {$VonKunde->cNachname}

{includeMailTemplate template=footer type=html}