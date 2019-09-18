{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: plugin_uebersicht.tpl, smarty template inc file

    page for JTL-Shop 3
    Admin

    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de

    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
{assign var=cPlugin value=#plugin#}
{include file="tpl_inc/seite_header.tpl" cTitel="`$cPlugin`: `$oPlugin->cName`" cBeschreibung=$oPlugin->cBeschreibung}
<div id="content">

	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
    
    <div class="block">
        <p><strong>{#pluginAuthor#}:</strong> {$oPlugin->cAutor}</p>
        <p><strong>{#pluginHomepage#}:</strong> <a href="{$oPlugin->cURL}" target="_blank">{$oPlugin->cURL}</a></p>
    </div>

    <div class="container">

    {if $oPlugin->oPluginAdminMenu_arr|@count > 0}
        <div class="tabber">
        {foreach name="pluginadminmenutabs" from=$oPlugin->oPluginAdminMenu_arr item=oPluginAdminMenu}
            {if $oPluginAdminMenu->nConf == 0}
                <div class="tabbertab{if isset($defaultTabbertab) && ($defaultTabbertab == $oPluginAdminMenu->kPluginAdminMenu || $defaultTabbertab == $oPluginAdminMenu->cName)} tabbertabdefault{/if}">
                     <h2>{$oPluginAdminMenu->cName}</h2>
                    {requirePluginCustomlink cPfad=$oPlugin->cAdminmenuPfad cDateiname=$oPluginAdminMenu->cDateiname}
                </div>
            {else}
                <div class="tabbertab{if isset($defaultTabbertab) && ($defaultTabbertab == $oPluginAdminMenu->kPluginAdminMenu || $defaultTabbertab == $oPluginAdminMenu->cName)} tabbertabdefault{/if}" id="{$oPluginAdminMenu->cName}">
                    <h2>{$oPluginAdminMenu->cName}</h2>

                    <div id="settings">
                        <form method="post" action="plugin.php">
                        <input type="hidden" name="{$session_name}" value="{$session_id}" />
                        <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />
                        <input type="hidden" name="kPluginAdminMenu" value="{$oPluginAdminMenu->kPluginAdminMenu}" />
                        <input type="hidden" name="Setting" value="1" />
                        {foreach name="plugineinstellungenconf" from=$oPlugin->oPluginEinstellungConf_arr item=oPluginEinstellungConf}                        
                            {if $oPluginAdminMenu->kPluginAdminMenu == $oPluginEinstellungConf->kPluginAdminMenu}
                                {foreach name="plugineinstellungen" from=$oPlugin->oPluginEinstellung_arr item=oPluginEinstellung}
                                    {if $oPluginEinstellung->cName == $oPluginEinstellungConf->cWertName}
                                        {assign var=cEinstellungWert value=$oPluginEinstellung->cWert}
                                    {/if}
                                {/foreach}
                                {if $oPluginEinstellungConf->cConf == "N"}
                                    <div class="category {if $smarty.foreach.plugineinstellungenconf.index == 0}first{/if}">
                                        {$oPluginEinstellungConf->cName}
                                        {if $oPluginEinstellungConf->cBeschreibung|@count_characters > 0}
                                            <div class="help" ref="{$oPluginEinstellungConf->kPluginEinstellungenConf}" title="{$oPluginEinstellungConf->cBeschreibung}"></div>
                                        {/if}
                                    </div>
                                {else}
                                    <div class="item">
                                        <div class="name">
                                            <label>{$oPluginEinstellungConf->cName}</label>
                                        </div>
                                        <div class="for">
                                            {if $oPluginEinstellungConf->cInputTyp === "selectbox"}
                                                <select name="{$oPluginEinstellungConf->cWertName}" class="combo">
                                                {foreach name="plugineinstellungenconfwerte" from=$oPluginEinstellungConf->oPluginEinstellungenConfWerte_arr item=oPluginEinstellungenConfWerte}
                                                    <option value="{$oPluginEinstellungenConfWerte->cWert}"{if $cEinstellungWert == $oPluginEinstellungenConfWerte->cWert} selected{/if}>{$oPluginEinstellungenConfWerte->cName}</option>
                                                {/foreach}
                                                </select>
                                            {elseif $oPluginEinstellungConf->cInputTyp === "password"}
                                            	<input name="{$oPluginEinstellungConf->cWertName}" type="password" value="{$cEinstellungWert}" />
                                            {elseif $oPluginEinstellungConf->cInputTyp === "textarea"}
	                                            <textarea name="{$oPluginEinstellungConf->cWertName}">{$cEinstellungWert}</textarea>
                                            {elseif $oPluginEinstellungConf->cInputTyp === "checkbox"}
	                                            <input type="checkbox" name="{$oPluginEinstellungConf->cWertName}"{if $cEinstellungWert === 'on'} checked="checked"{/if}>
                                            {elseif $oPluginEinstellungConf->cInputTyp === "radio"}
	                                                <div class="radio-wrap">
		                                                {foreach name="plugineinstellungenconfwerte" from=$oPluginEinstellungConf->oPluginEinstellungenConfWerte_arr item=oPluginEinstellungenConfWerte}
			                                                <input type="radio" name="{$oPluginEinstellungConf->cWertName}[]" value="{$oPluginEinstellungenConfWerte->cWert}"{if $cEinstellungWert == $oPluginEinstellungenConfWerte->cWert} checked="checked"{/if} /> {$oPluginEinstellungenConfWerte->cName} <br />
		                                                {/foreach}
	                                                </div>
                                            {else}
                                                <input name="{$oPluginEinstellungConf->cWertName}" type="text" value="{$cEinstellungWert}" />
                                            {/if}
                                            {if $oPluginEinstellungConf->cBeschreibung|@count_characters > 0}
                                                <div class="help" ref="{$oPluginEinstellungConf->kPluginEinstellungenConf}" title="{$oPluginEinstellungConf->cBeschreibung}"></div>
                                            {/if}
                                        </div>
                                    </div>
                                {/if}
                            {/if}
                        {/foreach}
                        <div class="save_wrapper">
                            <input name="speichern" type="submit" value="{#pluginSettingSave#}" class="button orange" />
                        </div>
                        </form>
                    </div>

                </div>
            {/if}
        {/foreach}
        </div>
    {else}
        <p class="box_info">{#noDataAvailable#}</p>
    {/if}

    </div>
</div>