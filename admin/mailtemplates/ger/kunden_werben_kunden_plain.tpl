{includeMailTemplate template=header type=plain}

Hallo {$Kunde->cVorname},

anbei bekommst du ein Guthaben von {$Neukunde->fGuthaben} für {$Firma->cName}.

Übrigens, ich werbe Dich im Rahmen der {$Firma->cName} Kunden werben Kunden Aktion.

Viele Grüße,
{$Bestandskunde->cVorname} {$Bestandskunde->cNachname}

{includeMailTemplate template=footer type=plain}
