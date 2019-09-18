{includeMailTemplate template=header type=plain}

Hello {$Kunde->cVorname},

Please find attached a voucher worth {$Neukunde->fGuthaben} for {$Firma->cName}.

By the way, I'm recommending you as part of {$Firma->cName}'s customer recommendation program.

Yours sincerely,
{$Bestandskunde->cVorname} {$Bestandskunde->cNachname}

{includeMailTemplate template=footer type=plain}
