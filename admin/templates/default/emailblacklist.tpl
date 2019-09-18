{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: emailblacklist.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{config_load file="$lang.conf" section="emailblacklist"}
{include file='tpl_inc/header.tpl'}
{include file="tpl_inc/seite_header.tpl" cTitel=#emailblacklist# cBeschreibung=#emailblacklistDesc# cDokuURL=#emailblacklistURL#}
<div id="content">	
	{if isset($hinweis) && $hinweis|count_characters > 0}			
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}			
		<p class="box_error">{$fehler}</p>
	{/if}
   
   <form method="post" action="emailblacklist.php">
      <input type="hidden" name="{$session_name}" value="{$session_id}" />
      <input type="hidden" name="einstellungen" value="1" />
      <input type="hidden" name="emailblacklist" value="1" />
	
      <div id="settings" class="container">
         {foreach name=conf from=$oConfig_arr item=oConfig}
               {if $oConfig->cConf == "Y"}
                  <div class="item">
                     <div class="name">
                        <label for="{$oConfig->cWertName}">{$oConfig->cName}
                     </div>
                     <div class="for">
                        {if $oConfig->cInputTyp=="selectbox"}
                           <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}" class="combo"> 
                           {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                              <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                           {/foreach}
                           </select> 
                        {else}
                           <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
                        {/if}
                        
                        {if $oConfig->cBeschreibung}
                           <div class="help" ref="{$oConfig->kEinstellungenConf}" title="{$oConfig->cBeschreibung}"></div>
                        {/if}
                        
                     </div>
                  </div>
               {else}
                  {if $oConfig->cName}<div class="category">{$oConfig->cName}</div>{/if}
               {/if}
            {/foreach}
      </div>
         
      <div class="container">
            <div class="category">{#emailblacklistEmail#} {#emailblacklistSeperator#}</div>
            
            <div class="container">
               <textarea name="cEmail" cols="100" rows="20">{if isset($oEmailBlacklist_arr)}{foreach name=emailblacklist from=$oEmailBlacklist_arr item=oEmailBlacklist}{$oEmailBlacklist->cEmail}{if !$smarty.foreach.emailblacklist.last};{/if}{/foreach}{/if}</textarea>
            </div>
            
            <div class="save_wrapper">
               <input name="loeschen" type="submit" value="{#emailblacklistSave#}" class="button orange" />
            </div>
      </div>
   </form>
</div>
{include file='tpl_inc/footer.tpl'}