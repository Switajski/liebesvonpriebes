{includeMailTemplate template=header type=html}

Hello,<br>
Take a look at my wish list at {$Firma->cName}.<br>
<br>
<table cellpadding="5" cellspacing="0" border="0" width="100%">
    <tr>
        {foreach name=wunschlistepos from=$Wunschliste->CWunschlistePos_arr item=CWunschlistePos}
            <td class="column" align="left" valign="top" bgcolor="#ffffff">
                <a href="{$ShopURL}/{$CWunschlistePos->Artikel->cURL}">
                    <img src="{$ShopURL}/{$CWunschlistePos->Artikel->Bilder[0]->cPfadKlein}" style="border: 1px solid #bebcb7">
                </a><br>
                <br>
                <a href="{$ShopURL}/{$CWunschlistePos->Artikel->cURL}" style="color: #1E7EC8;">{$CWunschlistePos->cArtikelName}</a><br>
                {foreach name=eigenschaft from=$CWunschlistePos->CWunschlistePosEigenschaft_arr item=CWunschlistePosEigenschaft}
                    {if $CWunschlistePosEigenschaft->cFreifeldWert}
                        <strong>{$CWunschlistePosEigenschaft->cEigenschaftName}:<strong>{$CWunschlistePosEigenschaft->cFreifeldWert}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$smarty.foreach.eigenschaft.last}{/if}
                    {else}
                        <strong>{$CWunschlistePosEigenschaft->cEigenschaftName}:</strong> {$CWunschlistePosEigenschaft->cEigenschaftWertName}{if $CWunschlistePos->CWunschlistePosEigenschaft_arr|@count > 1 && !$smarty.foreach.eigenschaft.last}{/if}
                    {/if}
                {/foreach}
            </td>
            {if $smarty.foreach.wunschlistepos.iteration % 2 == 0}</tr>{if $smarty.foreach.wunschlistepos.iteration != 1}<tr>{/if}{/if}
        {/foreach}
    </tr>
</table><br>
<a href="{$ShopURL}/index.php?wlid={$Wunschliste->cURLID}">View all products</a><br>
<br>
Thank you.<br>
<strong>{$Kunde->cVorname} {$Kunde->cNachname}</strong>

{includeMailTemplate template=footer type=html}