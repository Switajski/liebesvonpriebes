{includeMailTemplate template=header type=plain}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

Thank you for your product rating. You can redeem your bonus credit of {$oBewertungGuthabenBonus->fGuthabenBonusLocalized} for any of your future purchases.

Yours sincerely,
{$Firma->cName}

{includeMailTemplate template=footer type=plain}