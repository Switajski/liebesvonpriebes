{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: links_loesch_confirm.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file="tpl_inc/seite_header.tpl" cTitel=#deleteLinkGroup#}
<div id="content">	
	<div class="container">
		<form method="POST" action="links.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}" />
		<input type="hidden" name="loesch_linkgruppe" value="1" />
		<input type="hidden" name="kLinkgruppe" value="{$oLinkgruppe->kLinkgruppe}" />
		
		<div class="box_error">
			<p><strong>Vorsicht</strong>: Alle Links innerhalb dieser Linkgruppe werden ebenfalls gel&ouml;scht</p>
			<p>Wollen Sie wirklich die Linkgruppe "<strong>{$oLinkgruppe->cName}</strong>" l&ouml;schen?</p>
		</div>
		
		<input name="loeschConfirmJaSubmit" type="submit" value="{#loeschlinkgruppeYes#}" class="button orange" />
		<input name="loeschConfirmNeinSubmit" type="submit" value="{#loeschlinkgruppeNo#}" class="button orange" />
		</form>
	</div>
</div>