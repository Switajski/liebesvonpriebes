{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: newsletter_anzeigen.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

<div id="page">
	<div id="content">
		<form method="POST" action="newsletter.php">
		<div id="welcome" class="post">
			<h2 class="title"><span>{#newsletterhistory#}</span></h2>
			<div class="content">
				<p>{#newsletterdesc#}</p>
		    </div>
		</div>
		
		<div class="container">
			<table class="newsletter">				
				<tr>
					<td class="left"><b>{#newsletterdraftsubject#}</b>:</td>
					<td>{$oNewsletterHistory->cBetreff}</td>
				</tr>
				
				<tr>
					<td style="vertical-align: middle;"><b>{#newsletterdraftdate#}</b>:</td>
					<td>{$oNewsletterHistory->Datum}</td>
				</tr>
			</table>
			
			<p><h3>{#newsletterHtml#}:</h3></p>
			<p>{$oNewsletterHistory->cHTMLStatic}</p>
			<p>
				<input name="back" type="submit" value="{#newsletterback#}">
			</p>					
		</div>
		</form>		
	</div>
</div>