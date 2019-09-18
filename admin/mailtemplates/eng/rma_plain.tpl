{includeMailTemplate template=header type=plain}

Thank you for your merchandise return to {$Einstellungen.global.global_shopname}.

Your merchandise return with the number {$oRMA->cRMANumber} includes the following items:

{if isset($oRMA->oRMAArtikel_arr) && $oRMA->oRMAArtikel_arr|@count > 0}
    {foreach name=artikel from=$oRMA->oRMAArtikel_arr item=oRMAArtikel}
        Product: {$oRMAArtikel->cArtikelName}
        Quantity: {$oRMAArtikel->fAnzahl}
    {/foreach}
{/if}

Once your return is received, we will arrange a refund of the goods' value. This amount will be reimbursed to the bank account used for your order. The refund may take a few days. We apologize for any inconvenience.

If you have further questions, please do not hesitate to contact us.

{includeMailTemplate template=footer type=plain}