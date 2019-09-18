{includeMailTemplate template=header type=html}

Dear {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
we are happy to tell you that you may use the following coupon ({$Kupon->AngezeigterName}) in our online shop:<br>
<br>
{if $Kupon->cKuponTyp=="standard"}
<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td class="column mobile-left" width="25%" align="right" valign="top">
			<table cellpadding="0" cellspacing="6">
				<tr>
					<td>
						<font color="#313131" face="Helvetica, Arial, sans-serif" size="3" style="color: #313131; font-family: Helvetica, Arial, sans-serif; font-size: 15px; line-height: 18px;">
							<strong>Value of coupon:</strong>
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
							{$Kupon->cLocalizedWert} {if $Kupon->cWertTyp=="prozent"}discount{/if}
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
							<strong>Coupon code:</strong>
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
							<strong>Minimum order value:</strong>
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
							{if $Kupon->fMindestbestellwert>0}{$Kupon->cLocalizedMBW}{else}There is no minimum order value!{/if}
						</font>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table><br>
{/if}
{if $Kupon->cKuponTyp=="versandkupon"}
	You will get free shipping with this coupon!<br>
    This coupon is valid for the following shipping countries: {$Kupon->cLieferlaender|upper}<br>
	<br>
{/if}

Valid from {$Kupon->cGueltigAbLong}{if $Kupon->dGueltigBis != 0} until {$Kupon->cGueltigBisLong}{/if}<br>
<br>
{if $Kupon->nVerwendungenProKunde>1}
	You may use this coupon {$Kupon->nVerwendungenProKunde} times in our shop.<br>
	<br>
{elseif $Kupon->nVerwendungenProKunde==0}
	You may use this coupon more often in our shop.<br>
	<br>
{/if}

{if $Kupon->nVerwendungen>0}
	Please note that this coupon is only valid for a limited time, so be quick.<br>
	<br>
{/if}

{if count($Kupon->Kategorien)>0}
	This coupon can be used for products from the following categories:<br>
    {foreach name=art from=$Kupon->Kategorien item=Kategorie}
        <a href="{$Kategorie->cURL}">{$Kategorie->cName}</a><br>
    {/foreach}
{/if}
<br>
{if count($Kupon->Artikel)>0}This coupon can be used for the following products:<br>
    {foreach name=art from=$Kupon->Artikel item=Artikel}
        <a href="{$Artikel->cURL}">{$Artikel->cName}</a><br>
    {/foreach}
{/if}<br>
<br>
You need to type in the coupon code in the checkout process to use it.<br>
<br>
Enjoy your next purchase in our shop.<br>
<br>
Yours sincerely,<br>
{$Firma->cName}

{includeMailTemplate template=footer type=html}