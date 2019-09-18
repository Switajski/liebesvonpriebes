{includeMailTemplate template=header type=plain}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

As requested, we have closed your account {$Kunde->cMail} effective today.

If you were not satisfied with our services, we would be grateful to let us know so that we can improve our services.

Should you want to purchase from us later again, just register and create a new account with us.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}