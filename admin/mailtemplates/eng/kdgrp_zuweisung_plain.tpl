{includeMailTemplate template=header type=plain}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

You now belong to our customer group: {$Kundengruppe->cName} in our webshop {$Einstellungen.global.global_shopname} ({$URL_SHOP}), which entitles you to different price conditions {if $Kundengruppe->fRabatt>0}(for example {$Kundengruppe->fRabatt|replace:".":","}% global discount){/if}.

If you have any questions on our range or special products, please simply contact us.

We hope you will enjoy exploring our range of products.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}