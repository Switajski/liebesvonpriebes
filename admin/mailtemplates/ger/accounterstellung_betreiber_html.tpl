{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{elseif $Kunde->cAnrede == "m"}geehrter{else}geehrte(r){/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
wunschgemäß haben wir für Sie in unserem Onlineshop unter
<strong><a href="{$ShopURL}" target="_blank" style="color: #313131;">{$ShopURL}</a></strong> ein Kundenkonto für Sie eingerichtet.<br>
<br>
Zur Kontrolle hier noch einmal Ihre Kundendaten:<br>
<br>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td class="column mobile-left" width="20%" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Anschrift:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" width="80%" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Kunde->cAnredeLocalized} {$Kunde->cVorname} {$Kunde->cNachname}<br>
							{$Kunde->cStrasse} {$Kunde->cHausnummer}<br>
							{if $Kunde->cAdressZusatz}{$Kunde->cAdressZusatz}{/if}<br>
							{$Kunde->cPLZ} {$Kunde->cOrt}<br>
							{if $Kunde->cBundesland}{$Kunde->cBundesland}{/if}<br>
							<font style="text-transform: uppercase;">{$Kunde->cLand}</font>
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{if $Kunde->cTel}
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Telefon:</strong>
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
							{$Kunde->cTel}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{/if}
	{if $Kunde->cMobil}
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Mobil:</strong>
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
							{$Kunde->cMobil}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{/if}
	{if $Kunde->cFax}
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Fax:</strong>
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
							{$Kunde->cFax}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{/if}
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Email:</strong>
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
							{$Kunde->cMail}
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
							<strong>Passwort:</strong>
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
							{$Kunde->cPasswortKlartext}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{if $Kunde->cUSTID}
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>UstID:</strong>
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
							{$Kunde->cUSTID}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{/if}
</table><br>
Mit diesen Daten können Sie sich ab sofort in Ihrem persönlichen
Kundenkonto anmelden und den aktuellen Status Ihrer Bestellungen
verfolgen.<br>
<br>
Wir freuen uns sehr, Sie als neuen Kunden bei uns begrüßen zu dürfen.
Wenn sie Fragen zu unserem Angebot oder speziellen Produkten haben,
nehmen Sie einfach Kontakt mit uns auf.<br>
<br>
Wir wünschen Ihnen viel Spaß beim Stöbern in unserem Sortiment.<br>
<br>
Mit freundlichem Gruß,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}