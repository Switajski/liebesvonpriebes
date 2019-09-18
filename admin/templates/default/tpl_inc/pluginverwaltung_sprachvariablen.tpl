{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: pluginverwaltung_sprachvariablen.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
<script type="text/javascript">
function ackCheck(kPluginSprachvariable, kPlugin)
{ldelim}
    var bCheck = confirm("Wollen Sie Ihre Sprachvariablen wirklich wieder auf den Installationszustand zurücksetzen? *Vorsicht* Alle bisherigen editierten Sprachvariablen, gehen für diese eine Variable verloren.");
    
    if(bCheck)
        window.location.href = "pluginverwaltung.php?pluginverwaltung_sprachvariable=1&kPlugin=" + kPlugin + "&kPluginSprachvariable=" + kPluginSprachvariable;
{rdelim}
</script>
 
{include file="tpl_inc/seite_header.tpl" cTitel=#pluginverwaltung# cBeschreibung=#pluginverwaltungDesc#}
<div id="content">
    
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
       
    <div class="container">
    
        {if $oPluginSprachvariable_arr|@count > 0 && $oPluginSprachvariable_arr}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            <input type="hidden" name="{$session_name}" value="{$session_id}" />
            <input type="hidden" name="pluginverwaltung_sprachvariable" value="1" />
            <input type="hidden" name="kPlugin" value="{$kPlugin}" />
            
            <div class="category">{#pluginverwaltungLocales#}</div>
            <table class="list">
                <thead>
                    <tr>
                        <th class="tleft">{#pluginName#}</th>
                        <th class="tleft">{#pluginDesc#}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach name="pluginsprachvariablen" from=$oPluginSprachvariable_arr item=oPluginSprachvariable}
                        <tr>
                            <td><strong>{$oPluginSprachvariable->cName}</strong></td>
                            <td>{$oPluginSprachvariable->cBeschreibung}</td>                        
                        </tr>

                        {foreach name="sprachen" from=$oSprache_arr item=oSprache}
                            <tr>
                                <td>{$oSprache->cNameDeutsch}</td>
                                <td>
                                    {assign var=cISOSprache value=$oSprache->cISO|upper}
                                    {if $oPluginSprachvariable->oPluginSprachvariableSprache_arr[$cISOSprache]|count_characters > 0}
                                        <input style="width: 300px;" name="{$oPluginSprachvariable->kPluginSprachvariable}_{$cISOSprache}" type="text" value="{$oPluginSprachvariable->oPluginSprachvariableSprache_arr[$cISOSprache]}" />
                                    {else}
                                        <input style="width: 300px;" name="{$oPluginSprachvariable->kPluginSprachvariable}_{$cISOSprache}" type="text" value="" />
                                    {/if}
                                 </td>                                     
                            </tr>                                                        
                        {/foreach}
                        <tr>
                            <td>&nbsp;</td>
                            <td><a href="javascript:ackCheck({$oPluginSprachvariable->kPluginSprachvariable}, {$kPlugin})" class="button reset">{#pluginLocalesStd#}</a></td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
            <div class="save_wrapper">
                <input name="speichern" type="submit" value="{#pluginBtnSave#}" class="button orange" />
            </div>
        </form>
        {/if}
    </div>
</div>