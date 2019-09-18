<div id="trustedShopsCheckout" style="display: none;">
    <span id="tsCheckoutOrderNr">{$Bestellung->cBestellNr}</span>
    <span id="tsCheckoutBuyerEmail">{$Kunde_TS->cMail}</span>
    <span id="tsCheckoutOrderAmount">{$Bestellung->fGesamtsummeKundenwaehrung|string_format:"%.2f"}</span>
    <span id="tsCheckoutOrderCurrency">{$Bestellung->Waehrung->cISO}</span>
    <span id="tsCheckoutOrderPaymentType">{$Bestellung->Zahlungsart->cTSCode}</span>
    <!-- product reviews start -->
    <!-- for each product in the basket full set of data is required -->
    {foreach name=positionen from=$Warenkorb_Positionen_TS item=oPosition}
        {if $oPosition->nPosTyp == 1}
            <span class="tsCheckoutProductItem">
                <span class="tsCheckoutProductUrl">{$smarty.const.URL_SHOP}/{$oPosition->Artikel->cURL}</span>
                <span class="tsCheckoutProductImageUrl">{$smarty.const.URL_SHOP}/{$oPosition->Artikel->Bilder.0->cPfadGross}</span>
                <span class="tsCheckoutProductName">{$oPosition->Artikel->cName}</span>
                <span class="tsCheckoutProductSKU">{$oPosition->Artikel->cArtNr}</span>
                <span class="tsCheckoutProductGTIN">{$oPosition->Artikel->cBarcode}</span>
                <span class="tsCheckoutProductMPN">{$oPosition->Artikel->cHAN}</span>
                <span class="tsCheckoutProductBrand">{$oPosition->Artikel->cHersteller}</span>
            </span>
        {/if}
    {/foreach}
    <!-- product reviews end -->
</div>