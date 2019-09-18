{includeMailTemplate template=header type=html}

Thank you for your merchandise return to {$Einstellungen.global.global_shopname}.<br>
<br>
Your merchandise return with the number {$oRMA->cRMANumber} includes the following items:<br>
<br>
{if isset($oRMA->oRMAArtikel_arr) && $oRMA->oRMAArtikel_arr|@count > 0}
        <table cellpadding="5" cellspacing="0" border="0" width="100%">
            <tr>
                <td align="right" valign="top">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="85%" align="left" valign="top">
                                <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                    <strong>Product</strong>
                                </font>
                            </td>
                            <td align="left" valign="top">
                                <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                    <strong>Quantity</strong>
                                </font>
                            </td>
                        </tr>
                        {foreach name=artikel from=$oRMA->oRMAArtikel_arr item=oRMAArtikel}
                        <tr>
                            <td align="left" valign="top">
                                <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                    {$oRMAArtikel->cArtikelName}
                                </font>
                            </td>
                            <td align="left" valign="top">
                                <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                    {$oRMAArtikel->fAnzahl}
                                </font>
                            </td>
                        </tr>
                        {/foreach}
                    </table>
                </td>
            </tr>
        </table>
{/if}<br>
<br>
Once your return is received, we will arrange a refund of the goods' value. This amount will be reimbursed to the bank account used for your order. The refund may take a few days. We apologize for any inconvenience.
<br>
If you have further questions, please do not hesitate to contact us.

{includeMailTemplate template=footer type=html}