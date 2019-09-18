{includeMailTemplate template=header type=plain}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

we are happy to inform you that you may use the following coupon ({$Kupon->AngezeigterName}) in our online shop:

{if $Kupon->cKuponTyp=="standard"}Value of coupon: {$Kupon->cLocalizedWert} {if $Kupon->cWertTyp=="prozent"}discount{/if}{/if}{if $Kupon->cKuponTyp=="versandkupon"}>You will get free shipping with this coupon!
    This coupon is valid for the following shipping countries: {$Kupon->cLieferlaender|upper}{/if}

Coupon code: {$Kupon->cCode}

Valid from {$Kupon->cGueltigAbLong}{if $Kupon->dGueltigBis != 0} until {$Kupon->cGueltigBisLong}{/if}

{if $Kupon->fMindestbestellwert>0}Minimum order value: {$Kupon->cLocalizedMBW}

{else}There is no minimum order value!

{/if}{if $Kupon->nVerwendungenProKunde>1}You may use this coupon {$Kupon->nVerwendungenProKunde} times in our shop.

{elseif $Kupon->nVerwendungenProKunde==0}You may use this coupon more often in our shop.

{/if}{if $Kupon->nVerwendungen>0}Please note that this coupon is only valid for a limited time, so be quick.

{/if}{if count($Kupon->Kategorien)>0}This coupon can be used for products from the following categories:


    {foreach name=art from=$Kupon->Kategorien item=Kategorie}
        {$Kategorie->cName} >
        {$Kategorie->cURL}
    {/foreach}{/if}

{if count($Kupon->Artikel)>0}This coupon can be used for the following products:


    {foreach name=art from=$Kupon->Artikel item=Artikel}
        {$Artikel->cName} >
        {$Artikel->cURL}
    {/foreach}{/if}

You need to type in the coupon code in the checkout process to use it.

Enjoy your next purchase in our shop.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}