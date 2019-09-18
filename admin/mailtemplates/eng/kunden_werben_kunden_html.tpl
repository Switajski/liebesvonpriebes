{includeMailTemplate template=header type=html}

Hello {$Kunde->cVorname},<br><br>

Please find attached a voucher worth {$Neukunde->fGuthaben} for {$Firma->cName}.<br><br>

By the way, I'm recommending you as part of {$Firma->cName}'s customer recommendation program.<br><br>

Yours sincerely,<br>
{$Bestandskunde->cVorname} {$Bestandskunde->cNachname}

{includeMailTemplate template=footer type=html}