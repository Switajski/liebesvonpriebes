<tfoot>
    {if $NettoPreise}
        <tr class="total-net">
            {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'}
                <td class="hidden-xs"></td>
            {/if}
            <td class="text-right" colspan="2"><span class="price_label"><strong>{lang key="totalSum" section="global"} ({lang key="net" section="global"}):</strong></span></td>
            <td class="text-right price-col" colspan="{if $tplscope === 'cart'}4{else}3{/if}"><strong class="price total-sum">{$WarensummeLocalized[$NettoPreise]}</strong></td>
        </tr>
    {/if}

    {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && $Steuerpositionen|@count > 0}
        {foreach name=steuerpositionen from=$Steuerpositionen item=Steuerposition}
            <tr class="tax">
                {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'}
                    <td class="hidden-xs"></td>
                {/if}
                <td class="text-right" colspan="2"><span class="tax_label">{$Steuerposition->cName}:</span></td>
                <td class="text-right price-col" colspan="{if $tplscope === 'cart'}4{else}3{/if}"><span class="tax_label">{$Steuerposition->cPreisLocalized}</span></td>
            </tr>
        {/foreach}
    {/if}

    {if isset($smarty.session.Bestellung->GuthabenNutzen) && $smarty.session.Bestellung->GuthabenNutzen == 1}
         <tr class="customer-credit">
             {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'}
                 <td class="hidden-xs"></td>
             {/if}
             <td class="text-right" colspan="2">{lang key="useCredit" section="account data"}</td>
             <td class="text-right" colspan="{if $tplscope === 'cart'}4{else}3{/if}">{$smarty.session.Bestellung->GutscheinLocalized}</td>
         </tr>
    {/if}
    
    <tr class="subtotal warning">
        {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'}
            <td class="hidden-xs"></td>
        {/if}
        <td class="text-right" colspan="2"><strong>Summe:</strong></td>
        <td class="text-right" colspan="{if $tplscope === 'cart'}4{else}3{/if}"><strong>{$subtotalAmount}</strong></td>
    </tr>
    
    <tr class="placeholder">
        <td colspan="999"><span class="invisible">placeholder</span></td>
    </tr>
    
    <tr class="tax">
        {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'}
            <td class="hidden-xs"></td>
        {/if}
        <td class="text-right" colspan="2">Finanzierungskosten:</td>
        <td class="text-right" colspan="{if $tplscope === 'cart'}4{else}3{/if}">{$financingAmount}</td>
    </tr>

    <tr class="total info">
        {if $Einstellungen.kaufabwicklung.warenkorb_produktbilder_anzeigen === 'Y'}
            <td class="hidden-xs"></td>
        {/if}
        <td class="text-right" colspan="2">
            <div class="price_label"><strong>{lang key="totalSum" section="global"}:</strong></div>
            <div><small>Inkl. Finanzierungskosten:</small></div>
        </td>
        <td class="text-right price-col vcenter" colspan="{if $tplscope === 'cart'}4{else}3{/if}"><strong class="price total-sum">{$WarensummeLocalized[0]}</strong></td>
    </tr>
</tfoot>