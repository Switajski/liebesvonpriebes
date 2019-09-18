{includeMailTemplate template=header type=html}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
As requested, we have closed your account {$Kunde->cMail} effective today.<br>
<br>
If you were not satisfied with our services, we would be grateful to let us know so that we can improve our services.<br>
<br>
Should you want to purchase from us later again, just register and create a new account with us.<br>
<br>
Yours sincerely,<br>
<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}