{includeMailTemplate template=header type=plain}

Hallo{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},<br>
<br>
wir freuen uns, Ihnen mitteilen zu dürfen, dass das Produkt {$Artikel->cName} ab sofort wieder bei uns erhältlich ist.<br>
<br>
Über diesen Link kommen Sie direkt zum Produkt in unserem Onlineshop: {$ShopURL}/{$Artikel->cURL}.<br>
<br>
Mit freundlichem Gruß,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}