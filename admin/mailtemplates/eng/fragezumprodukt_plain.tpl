{includeMailTemplate template=header type=plain}

Shop: {$Einstellungen.global.global_shopname}

Product: {$Artikel->cName}

Email address of customer: {$Nachricht->cMail}

Question: {$Nachricht->cNachricht}

{if !empty($Nachricht->cAnredeLocalized) && !empty($Nachricht->cVorname)
|| !empty($Nachricht->cAnredeLocalized) && !empty($Nachricht->cNachname)
|| !empty($Nachricht->cAnredeLocalized) && !empty($Nachricht->cVorname) && !empty($Nachricht->cNachname)}
    Customer data:
    {if !empty($Nachricht->cAnredeLocalized)}{$Nachricht->cAnredeLocalized} {/if}
    {if !empty($Nachricht->cVorname)}{$Nachricht->cVorname} {/if}
    {if !empty($Nachricht->cNachname)}{$Nachricht->cNachname}{/if}
    {if !empty($Nachricht->cFirma)}{$Nachricht->cFirma}{/if}
{/if}

Email: {$Nachricht->cMail}
{if !empty($Nachricht->cTel)}Tel: {$Nachricht->cTel}{/if}
{if !empty($Nachricht->cMobil)}Mobil: {$Nachricht->cMobil}{/if}
{if !empty($Nachricht->cFax)}Fax: {$Nachricht->cFax}{/if}

{includeMailTemplate template=footer type=plain}