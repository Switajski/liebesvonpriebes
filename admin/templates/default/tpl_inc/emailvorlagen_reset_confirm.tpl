{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: emailvorlagen_reset_confirm.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file="tpl_inc/seite_header.tpl" cTitel=#emailTemplates#}
<div id="content">    
    <form method="POST" action="emailvorlagen.php">
        <input type="hidden" name="{$session_name}" value="{$session_id}" />
        <input type="hidden" name="resetEmailvorlage" value="1" />
	{if isset($kPlugin) && $kPlugin > 0}
		<input type="hidden" name="kPlugin" value="{$kPlugin}" />
	{/if}
        <input type="hidden" name="kEmailvorlage" value="{$oEmailvorlage->kEmailvorlage}" />		
        
        <div class="box_error">
            <p><strong>Vorsicht</strong>: Ihre Emailvorlage wird zur&uuml;ckgesetzt!</p>
            <p>Wollen Sie wirklich die Emailvorlage "<b>{$oEmailvorlage->cName}</b>" zur&uuml;cksetzen?</p>
        </div>
        
        <input name="resetConfirmJaSubmit" type="submit" value="{#resetEmailvorlageYes#}" class="button orange" />
        <input name="resetConfirmNeinSubmit" type="submit" value="{#resetEmailvorlageNo#}" class="button orange" />
    </form>
</div>