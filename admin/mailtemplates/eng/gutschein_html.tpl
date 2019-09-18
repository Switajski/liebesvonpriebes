{includeMailTemplate template=header type=html}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
We are happy to inform you that a voucher has been deposited for you on your account.<br>
<br>
<strong>Value of voucher:</strong> {$Gutschein->cLocalizedWert}<br>
<br>
Reason for issuing the voucher: {$Gutschein->cGrund}<br>
<br>
You can redeem this voucher simply with your next order. The voucher value is then subtracted from your purchase value.<br>
<br>
Enjoy your next purchase in our shop.<br>
<br>
Yours sincerely,<br>
{$Firma->cName}