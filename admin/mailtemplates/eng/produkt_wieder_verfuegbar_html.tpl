{includeMailTemplate template=header type=html}

Dear{if !empty($Benachrichtigung->cVorname)} {$Benachrichtigung->cVorname}{/if}{if !empty($Benachrichtigung->cNachname)} {$Benachrichtigung->cNachname}{/if},<br>
<br>
We're happy to inform you that our product {$Artikel->cName} is once again available in our online shop.<br>
<br>
Link to product: <a href="{$ShopURL}/{$Artikel->cURL}">{$ShopURL}/{$Artikel->cURL}</a><br>
<br>
Yours sincerely,<br>
{$Einstellungen.global.global_shopname}

{includeMailTemplate template=footer type=html}