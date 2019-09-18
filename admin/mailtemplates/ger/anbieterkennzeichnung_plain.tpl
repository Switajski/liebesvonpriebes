{$Firma->cName}
{if $Firma->cUnternehmer|strlen>0}{$Firma->cUnternehmer}{/if}
{$Firma->cStrasse} {$Firma->cHausnummer}
{$Firma->cPLZ} {$Firma->cOrt}
{$Firma->cLand}
Tel.: {$Firma->cTel}
{if $Firma->cFax|strlen>0}Fax.: {$Firma->cFax}{/if}
{$Firma->cWWW}
{$Firma->cUSTID}