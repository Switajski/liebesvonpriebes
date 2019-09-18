{includeMailTemplate template=header type=html}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
Thank you very much for registering in our online shop at <a href="{$ShopURL}" target="_blank"><strong>{$ShopURL}</strong></a><br>
<br>
Please review your account details:<br>
<br>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td class="column mobile-left" width="25%" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Address:</strong>
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
							{$Kunde->cAnredeLocalized} {$Kunde->cVorname} {$Kunde->cNachname}<br>
							{$Kunde->cStrasse} {$Kunde->cHausnummer}<br>
							{if $Kunde->cAdressZusatz}{$Kunde->cAdressZusatz}<br>{/if}
							{$Kunde->cPLZ} {$Kunde->cOrt}<br>
							{if $Kunde->cBundesland}{$Kunde->cBundesland}<br>{/if}
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
							<strong>Tel:</strong>
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
							<strong>Mobile:</strong>
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
	{if $Kunde->cUSTID}
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>VAT ID:</strong>
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
Using these account details you can log into your personal account in future and track the current status of your order.<br>
<br>
We are happy to welcome you as a new customer. If you have any questions on our range or special products, please simply contact us.<br>
<br>
We hope you will enjoy exploring our range of products.<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}