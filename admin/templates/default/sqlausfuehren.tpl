{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: shoptemplate.tpl, smarty template inc file
	
	tpl page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="sql"}
						<td bgcolor="#ffffff" style="border-color:#222222; border-width:1px; border-style:solid; border-top-width:0px; border-bottom-width:0px; border-left-width:0px;" valign="top" align="center" height="400"><br>
							<table cellspacing="0" cellpadding="0" width="96%">
								<tr><td class="content_header" align="center"><h3>{#executeSql#}</h3></td></tr>
								<tr><td class="content"><br>
									{#executeSqlDesc#}<br><br>
									<b><a href="http://wiki.jtl-software.de/index.php/3._EINRICHTUNG_VON_JTL-SHOP_V2#3.2.13_SQL_-_Ausf.C3.BChren">{#docu#}</a></b><br><br><br>
									<form name="login" method="post" action="sqlausfuehren.php" ENCTYPE="multipart/form-data">
									<input type="hidden" name="patch" value="1"><br><br>
									<strong>{#sqlFile#}:</strong>
									<input type="FILE" name="sql">
									<br>{$hinweis}<br><br>
									<input type="submit" value="{#executeSql#}">
									</form>
								</td></tr>
							</table><br>
						</td>

{include file='tpl_inc/footer.tpl'}