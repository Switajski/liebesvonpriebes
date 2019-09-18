{includeMailTemplate template=header type=html}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
As requested we have created an account for you in our online shop at <strong><a href="{$ShopURL}" target="_blank" style="color: #313131;">{$ShopURL}</a></strong>.<br>
<br>
Please review your account details:<br>
<br>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td class="column mobile-left" width="20%" align="right" valign="top">
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
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Password:</strong>
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
Using these account details you can log into your personal account in
future and track the current status of your order.<br>
<br>
We are happy to welcome you as a new customer. If you have any
questions on our range or special products, please simply contact us.<br>
<br>
We hope you will enjoy exploring our range of products.<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}