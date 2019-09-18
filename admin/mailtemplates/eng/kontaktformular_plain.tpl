{includeMailTemplate template=header type=plain}

Message:
{$Nachricht->cNachricht}

Contact person:
{if $Nachricht->cAnredeLocalized}{$Nachricht->cAnredeLocalized} {/if}{if $Nachricht->cVorname}{$Nachricht->cVorname} {/if}{if $Nachricht->cNachname}{$Nachricht->cNachname}{/if}
{if $Nachricht->cFirma}{$Nachricht->cFirma}{/if}

Email: {$Nachricht->cMail}
{if $Nachricht->cTel}Tel: {$Nachricht->cTel}{/if}
{if $Nachricht->cMobil}Mobile: {$Nachricht->cMobil}{/if}
{if $Nachricht->cFax}Fax: {$Nachricht->cFax}{/if}

{includeMailTemplate template=footer type=plain}