{includeMailTemplate template=header type=html}

<table cellpadding="5" cellspacing="0" border="0" width="100%">
	<tr>
		<td class="column mobile-left" width="25%" align="right" valign="top">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td align="right" valign="top">
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Shop:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td align="left" valign="top">
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Einstellungen.global.global_shopname}
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
					<td align="right" valign="top">
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Frage zu folgendem Produkt:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td align="left" valign="top">
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Artikel->cName}
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
					<td align="right" valign="top">
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>E-Mail-Adresse des Kunden:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td align="left" valign="top">
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Nachricht->cMail}
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
					<td align="right" valign="top">
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Frage:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td align="left" valign="top">
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Nachricht->cNachricht}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
    {if !empty($Nachricht->cAnredeLocalized) && !empty($Nachricht->cVorname)
		|| !empty($Nachricht->cAnredeLocalized) && !empty($Nachricht->cNachname) || !empty($Nachricht->cFirma)
		|| !empty($Nachricht->cAnredeLocalized) && !empty($Nachricht->cVorname) && !empty($Nachricht->cNachname)}
		<tr>
			<td class="column mobile-left" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
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
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {if !empty($Nachricht->cAnredeLocalized)}{$Nachricht->cAnredeLocalized} {/if}
                                {if !empty($Nachricht->cVorname)}{$Nachricht->cVorname} {/if}
                                {if !empty($Nachricht->cNachname)}{$Nachricht->cNachname}{/if}
								{if !empty($Nachricht->cAnredeLocalized) && !empty($Nachricht->cVorname)
									&& !empty($Nachricht->cNachname)}
									<br>
                                {/if}
                                {if !empty($Nachricht->cFirma)}{$Nachricht->cFirma}{/if}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
    {/if}
	<tr>
		<td class="column mobile-left" align="right" valign="top">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td align="right" valign="top">
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>E-Mail:</strong>
						</font>
					</td>
				</tr>
			</table>
		</td>
		<td class="column" align="left" valign="top" bgcolor="#ffffff">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td align="left" valign="top">
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							{$Nachricht->cMail}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
    {if !empty($Nachricht->cTel)}
		<tr>
			<td class="column mobile-left" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
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
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Nachricht->cTel}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
    {/if}
    {if !empty($Nachricht->cMobil)}
		<tr>
			<td class="column mobile-left" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
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
						<td align="left" valign="top">
							<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
                                {$Nachricht->cMobil}
							</font>
						</td>
					</tr>
				</table>
			</td>
		</tr>
    {/if}
    {if !empty($Nachricht->cFax)}
		<tr>
			<td class="column mobile-left" align="right" valign="top">
				<table cellpadding="0" cellspacing="0">
					<tr>
						<td align="right" valign="top">
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
						<td align="left" valign="top">
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