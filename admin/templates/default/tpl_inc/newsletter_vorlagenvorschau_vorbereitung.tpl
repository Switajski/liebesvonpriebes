{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: newsletter_vorlagenvorschau.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#newsletterhistory# cBeschreibung=#newsletterdesc#}
<div id="content">

   {if isset($oSmartyError) && is_object($oSmartyError)}
      <div class="box_error">
         <p><strong>Ihre Newslettervorlage ist fehlerhaft.</strong></p>
         <p><i>{$oSmartyError->cText}</i></p>
      </div>
   {/if}

   <form method="POST" action="newsletter.php">
   <input name="tab" type="hidden" value="newslettervorlagen" />
   <table class="newsletter">				
      <tr>
         <td><b>{#newsletterdraftsubject#}</b>:</td>
         <td>{$oNewsletterVorlage->cBetreff}</td>
      </tr>
      
      <tr>
         <td style="vertical-align: middle;"><b>{#newsletterdraftdate#}</b>:</td>
         <td>{$oNewsletterVorlage->Datum}</td>
      </tr>
   </table>
   
   <p><h3>{#newsletterHtml#}:</h3></p>
   <div style="text-align: center;"><iframe src="{$cURL}" width="100%" height="500"></iframe></div><br />
   <p><h3>{#newsletterText#}:</h3></p>
   <div style="text-align: center;"><textarea style="width: 100%; height: 300px;" readonly>{$oNewsletterVorlage->cInhaltText}</textarea></div>
   <p><input name="back" type="submit" value="{#newsletterback#}"></p>
   </form>
</div>
