<center>
	<table class="main" cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td>
				<table class="marking" cellpadding="15" cellspacing="0" border="0" width="100%">
					<tr>
						<td align="center" valign="top">
							<table class="sub" cellpadding="0" cellspacing="0" border="0" width="570">
								<tr>
									<td align="right" valign="top">
										<font color="#313131" face="Helvetica, Arial, sans-serif" size="2" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 18px;">
											<b>{$Firma->cName}</b><br>
											{if $Firma->cUnternehmer|strlen>0}{$Firma->cUnternehmer}{/if}<br>
											{$Firma->cStrasse} {$Firma->cHausnummer}<br>
											{$Firma->cPLZ} {$Firma->cOrt}<br>
											{$Firma->cLand}<br>
											Tel.: {$Firma->cTel}<br>
											{if $Firma->cFax|strlen>0}Fax.: {$Firma->cFax}{/if}<br>
											<a href="{$Firma->cWWW}" target="_blank" style="color: #313131;">{$Firma->cWWW}</a><br>
											{$Firma->cUSTID}
										</font>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</center>