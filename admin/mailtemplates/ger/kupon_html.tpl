{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
wir freuen uns Ihnen mitteilen zu dürfen, dass in unserem Onlineshop folgenden Kupon ({$Kupon->AngezeigterName}) verwenden dürfen:<br>
<br>
{if $Kupon->cKuponTyp=="standard"}
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td class="column mobile-left" width="25%" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Kuponwert:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Kupon->cLocalizedWert} {if $Kupon->cWertTyp=="prozent"}Rabatt auf den gesamten Einkauf{/if}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Kuponcode:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Kupon->cCode}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Mindestbestellwert:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{if $Kupon->fMindestbestellwert>0}{$Kupon->cLocalizedMBW}{else}Es gibt keinen Mindestbestellwert!{/if}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table><br>
{/if}
{if $Kupon->cKuponTyp=="versandkupon"}
	Mit diesem Kupon können Sie versandkostenfrei bei uns einkaufen!<br>
	Er gilt für folgende Lieferländer: {$Kupon->cLieferlaender|upper}<br>
	<br>
{/if}

Gültig vom {$Kupon->cGueltigAbLong}{if $Kupon->dGueltigBis != 0} bis {$Kupon->cGueltigBisLong}{/if}<br>
<br>
{if $Kupon->nVerwendungenProKunde>1}
	Sie dürfen diesen Kupon bei insgesamt {$Kupon->nVerwendungenProKunde} Einkäufen bei uns nutzen.<br>
	<br>
{elseif $Kupon->nVerwendungenProKunde==0}
	Sie dürfen diesen Kupon bei beliebig vielen Einkäufen bei uns nutzen.<br>
	<br>
{/if}

{if $Kupon->nVerwendungen>0}
	Bitte beachten Sie, dass dieser Kupon auf eine maximale Verwendungsanzahl hat.<br>
	<br>
{/if}

{if count($Kupon->Kategorien)>0}
	Der Kupon gilt für folgende Kategorien:<br>
    {foreach name=art from=$Kupon->Kategorien item=Kategorie}
        <a href="{$Kategorie->cURL}">{$Kategorie->cName}</a><br>
    {/foreach}
{/if}
<br>
{if count($Kupon->Artikel)>0}Der Kupon gilt für folgende Artikel:<br>
    {foreach name=art from=$Kupon->Artikel item=Artikel}
        <a href="{$Artikel->cURL}">{$Artikel->cName}</a><br>
    {/foreach}
{/if}<br>
<br>
Sie lösen den Kupon ein, indem Sie beim Bestellvorgang den Kuponcode in das vorgesehene Feld eintragen.<br>
<br>
Viel Spaß bei Ihrem nächsten Einkauf in unserem Shop.<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}