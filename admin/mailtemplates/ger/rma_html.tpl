{includeMailTemplate template=header type=html}

vielen Dank für Ihre Warenrücksendung bei {$Einstellungen.global.global_shopname}.<br>
<br>
Ihre Warenrücksendung mit der RMA Nummer {$oRMA->cRMANumber} umfasst folgende Positionen:<br>
<br>
{if isset($oRMA->oRMAArtikel_arr) && $oRMA->oRMAArtikel_arr|@count > 0}
        <table cellpadding="5" cellspacing="0" border="0" width="100%">
            <tr>
                <td align="right" valign="top">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="85%" align="left" valign="top">
                                <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                    <strong>Artikel</strong>
                                </font>
                            </td>
                            <td align="left" valign="top">
                                <font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                    <strong>Anzahl</strong>
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
Sobald Ihre Rücksendung eintrifft, werden wir eine Erstattung über den Warenwert veranlassen. Dieser Betrag wird auf das bei Ihrer Bestellung genutzte Bankkonto zurückgebucht. Die Erstattung kann einige Tage dauern. Wir bitten um Ihr Verständnis.<br>
<br>
Sollten Sie noch weitere Fragen haben, zögern Sie nicht, uns zu schreiben.

{includeMailTemplate template=footer type=html}