{includeMailTemplate template=header type=html}

<table cellpadding="10" cellspacing="0" border="0" width="100%">
	<tr>
		<td class="column mobile-left" width="25%" align="right" valign="top">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Nachricht:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Nachricht->cNachricht|nl2br}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Anfrage von:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{if $Nachricht->cAnredeLocalized}{$Nachricht->cAnredeLocalized} {/if}{if $Nachricht->cVorname}{$Nachricht->cVorname} {/if}{if $Nachricht->cNachname}{$Nachricht->cNachname}{/if}
							{if $Nachricht->cFirma}<br>{$Nachricht->cFirma}{/if}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="0">
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
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Nachricht->cMail}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{if $Nachricht->cTel}
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="0">
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
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Nachricht->cTel}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{/if}
	{if $Nachricht->cMobil}
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="0">
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
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Nachricht->cMobil}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{/if}
	{if $Nachricht->cFax}
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="0">
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
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Nachricht->cFax}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	{/if}
</table>

{includeMailTemplate template=footer type=html}