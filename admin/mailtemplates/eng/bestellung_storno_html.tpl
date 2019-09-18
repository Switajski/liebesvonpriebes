{includeMailTemplate template=header type=html}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
Your order at {$Einstellungen.global.global_shopname} has been cancelled.
<strong>Order number:</strong> {$Bestellung->cBestellNr}<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}