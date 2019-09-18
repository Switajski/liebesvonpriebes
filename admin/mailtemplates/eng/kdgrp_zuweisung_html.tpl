{includeMailTemplate template=header type=html}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
You now belong to our customer group: {$Kundengruppe->cName} in our webshop <a href="{$URL_SHOP}">{$Einstellungen.global.global_shopname}</a>,  which entitles you to different price conditions {if $Kundengruppe->fRabatt>0}(for example {$Kundengruppe->fRabatt|replace:".":","}% global discount){/if}.<br>
<br>
If you have any questions on our range or special products, please simply contact us.<br>
<br>
We hope you will enjoy exploring our range of products.<br>
<br>
Yours sincerely,<br>
<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}