<p>Dear shop owner,</p>

<p>the customer {if empty($oKunde->cVorname) && empty($oKunde->cNachname)}{$oKunde->cMail}{else}{$oKunde->cVorname} {$oKunde->cNachname}{/if} has selected in the following checkboxoptions at {$cAnzeigeOrt}:</p>

<p>
	{assign var=kSprache value=$oSprache->kSprache}
	- {$oCheckBox->cName}, {$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}
</p>
