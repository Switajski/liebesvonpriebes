{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: trustedshops_uebersicht.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehemr@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software

-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#trustedshops# cDokuURL=#trustedshopsURL#}

<script type="text/javascript">
function checkBuyerProtView(elem)
{ldelim}
    switch(elem.selectedIndex)
    {ldelim}
        case 0: // CLASSIC
            document.getElementById("p_wsUser").style.display = "none";
            document.getElementById("p_wsPassword").style.display = "none";
            break;
            
        case 1: // EXCELLENCE
            document.getElementById("p_wsUser").style.display = "block";
            document.getElementById("p_wsPassword").style.display = "block";
            break;
    {rdelim}
{rdelim}
</script>

<div id="content">
    <div class="box_info">
        <p><a href="trustedshops.php?whatis=1{$session_name}={$session_id}">{#tsWhatIs#}</a></p>
        <!--<p><a href="http://www.trustedshops.de/shopbetreiber/mitgliedschaft_partner.html?shopsw=JTL" target="_blank">{#tsRegShop#}</a></p>-->
    </div>
   
    {if $bSOAP}
    
        {if isset($hinweis) && $hinweis|count_characters > 0}			
            <p class="box_success">{$hinweis}</p>
        {/if}
        {if isset($fehler) && $fehler|count_characters > 0}			
            <p class="box_error">{$fehler}</p>
        {/if}

        <div class="container">
            <form name="sprache" method="post" action="trustedshops.php">
            <p class="txtCenter">
            <label for="{#changeLanguage#}">{#changeLanguage#}:</strong></label>
                <input type="hidden" name="sprachwechsel" value="1">
                 <select id="{#changeLanguage#}" name="cISOSprache" class="selectBox" onchange="javascript:document.sprache.submit();">
                 {foreach name=sprachen from=$Sprachen item=sprache}
                     <option value="{$sprache->cISOSprache}" {if $sprache->cISOSprache==$smarty.session.TrustedShops->oSprache->cISOSprache}selected{/if}>{$sprache->cNameSprache}</option>
                 {/foreach}
                 </select>
                </p>
            </form>

            <form name="einstellen" method="post" action="trustedshops.php">
            <input type="hidden" name="{$session_name}" value="{$session_id}" />
            <input type="hidden" name="kaeuferschutzeinstellungen" value="1" />
            <div class="settings">
                {foreach name=conf from=$oConfig_arr item=oConfig}
                    {if $oConfig->cWertName != "trustedshops_kundenbewertung_anzeigen"}
                        {if $oConfig->cConf == "Y"}
                            <p><label for="{$oConfig->cWertName}">{$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}" style="vertical-align:middle; cursor:help;" /></label>
                        {/if}
                        {if $oConfig->cInputTyp=="selectbox"}
                            <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"> 
                            {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                            {/foreach}
                            </select>
                        {elseif $oConfig->cInputTyp=="listbox"}
                            <select name="{$oConfig->cWertName}[]" id="{$oConfig->cWertName}"> 
                            {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                <option value="{$wert->kKundengruppe}" {foreach name=werte from=$oConfig->gesetzterWert item=gesetzterWert}{if $gesetzterWert->cWert == $wert->kKundengruppe}selected{/if}{/foreach}>{$wert->cName}</option>
                            {/foreach}
                            </select>
                        {else}
                            <input type="text" name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"  value="{$oConfig->gesetzterWert}" tabindex="1" /></p>
                        {/if}
                        {else}
                            {if $oConfig->cName}<div class="category">{$oConfig->cName}</div>{/if}
                        {/if}
                    {/if}
                {/foreach}

                     <p><label for="tsId">Trusted Shops K&auml;uferschutz Typ <img src="{$currentTemplateDir}gfx/help.png" alt="Trusted Shops K&auml;uferschutzvariante." title="Trusted Shops K&auml;uferschutzvariante." style="vertical-align:middle; cursor:help;" /></label>
                     <select name="eType" id="eType" onChange="javascript:checkBuyerProtView(this);">
                         <option value="CLASSIC"{if isset($oZertifikat->eType) && $oZertifikat->eType == "CLASSIC"} selected{/if}>CLASSIC</option>
                         <option value="EXCELLENCE"{if isset($oZertifikat->eType) && $oZertifikat->eType == "EXCELLENCE"} selected{/if}>EXCELLENCE</option>
                     </select></p>

                     <p><label for="tsId">Trusted Shops ID (tsId) <img src="{$currentTemplateDir}gfx/help.png" alt="Die vom Shopbetreiber eingegebene Zertifikats-ID." title="Die vom Shopbetreiber eingegebene Zertifikats-ID." style="vertical-align:middle; cursor:help;" /></label>
                     <input type="text" name="tsId" id="tsId"  value="{if isset($oZertifikat->cTSID)}{$oZertifikat->cTSID}{/if}" tabindex="1" /></p>

                     <p id="p_wsUser"><label for="wsUser">WebService User (wsUser) <img src="{$currentTemplateDir}gfx/help.png" alt="Der vom Shopbetreiber eingegebene Benutzername" title="Der vom Shopbetreiber eingegebene Benutzername" style="vertical-align:middle; cursor:help;" /></label>
                     <input type="text" name="wsUser" id="wsUser"  value="{if isset($oZertifikat->cWSUser)}{$oZertifikat->cWSUser}{/if}" tabindex="1" /></p>

                     <p id="p_wsPassword"><label for="wsPassword">WebService Passwort (wsPassword) <img src="{$currentTemplateDir}gfx/help.png" alt="Das vom Shopbetreiber eingegebene Passwort" title="Das vom Shopbetreiber eingegebene Passwort" style="vertical-align:middle; cursor:help;" /></label>
                     <input type="text" name="wsPassword" id="wsPassword"  value="{if isset($oZertifikat->cWSPasswort)}{$oZertifikat->cWSPasswort}{/if}" tabindex="1" /></p>
            </div>

            {if isset($oZertifikat->nAktiv) && $oZertifikat->nAktiv|count_characters > 0 && $oZertifikat->nAktiv == 0}<p><div style="text-align: center;"><strong><font color="red">{#tsDeaktiviated#}</font></strong></div></p>{/if}
            <p class="submit"><input name="saveSettings" type="submit" value="{#settingsSave#}" class="button orange" /> <input name="delZertifikat" type="submit" value="{#tsDelCertificate#}" class="button orange" /></p>
            </form>

            {if isset($oZertifikat->eType) && $oZertifikat && $oZertifikat->eType == $TS_BUYERPROT_EXCELLENCE && $Einstellungen.trustedshops.trustedshops_nutzen == "Y"}
                <form method="POST" action="trustedshops.php" class="container">
                <input type="hidden" name="{$session_name}" value="{$session_id}" />
                <input type="hidden" name="kaeuferschutzupdate" value="1" />

                <input name="tsupdate" type="submit" value="{#updateProduct#}" class="button reset" />
                </form>
            {/if}

            {if isset($oKaeuferschutzProdukteDB->item) && $oKaeuferschutzProdukteDB->item|@count > 0}
                <table>
                    <tr>
                        <th class="th-1">{#tsProduct#}</th>
                        <th class="th-2">{#tsCoverage#}</th>
                        <th class="th-3">{#tsCurrency#}</th>

                    </tr>
                {foreach name=kaeuferschutzprodukte from=$oKaeuferschutzProdukteDB->item item=oKaeuferschutzProdukt}
                    <tr class="tab_bg{$smarty.foreach.kaeuferschutzprodukte.iteration%2}">                    
                        <td class="TD1">{$oKaeuferschutzProdukt->cProduktID}</td>
                        <td class="TD2">{$oKaeuferschutzProdukt->nWert}</td>
                        <td class="TD3">{$oKaeuferschutzProdukt->cWaehrung}</td>
                    </tr>
                {/foreach}
                </table>
            {/if}
        </div>   

        <br />

        {if $bAllowfopen || $bCURL}
        <div class="container">
            <div class="settings">
                <form method="POST" action="trustedshops.php">
                <input type="hidden" name="{$session_name}" value="{$session_id}" />
                <input type="hidden" name="kundenbewertungeinstellungen" value="1" />

                <div class="box_info">
                {assign var=sessionSprachISO value="`$smarty.session.TrustedShops->oSprache->cISOSprache`"}
                <p><a href="trustedshops.php?whatisrating=1{$session_name}={$session_id}" style="text-decoration: underline;">{#tsWhatIsRating#}</a></p>
                {if $Sprachen[$sessionSprachISO]->cURLKundenBewertung|count_characters > 0}
                    <p><a href="{$Sprachen[$sessionSprachISO]->cURLKundenBewertung}" target="_blank" style="text-decoration: underline;">{#tsRatingForm#}</a></p>
                {/if}                
                </div>

                <div class="category">{#tsRatingConfig#}</div>

                {foreach name=conf from=$oConfig_arr item=oConfig}
                    {if $oConfig->cWertName == "trustedshops_kundenbewertung_anzeigen"}
                        {if $oConfig->cConf == "Y"}
                            <p><label for="{$oConfig->cWertName}">{$oConfig->cName} {if $oConfig->cBeschreibung}<img src="{$currentTemplateDir}gfx/help.png" alt="{$oConfig->cBeschreibung}" title="{$oConfig->cBeschreibung}"{/if} style="vertical-align:middle; cursor:help;" /></label>
                        {/if}
                        {if $oConfig->cInputTyp=="selectbox"}
                            <select name="{$oConfig->cWertName}" id="{$oConfig->cWertName}"> 
                            {foreach name=selectfor from=$oConfig->ConfWerte item=wert}
                                <option value="{$wert->cWert}" {if $oConfig->gesetzterWert == $wert->cWert}selected{/if}>{$wert->cName}</option>
                            {/foreach}
                            </select>
                        {/if}
                    {/if}
                {/foreach}

                <p><label for="tsId">Trusted Shops ID (tsId) <img src="{$currentTemplateDir}gfx/help.png" alt="Die vom Shopbetreiber eingegebene Zertifikats-ID." title="Die vom Shopbetreiber eingegebene Zertifikats-ID." style="vertical-align:middle; cursor:help;" /></label>
                <input type="text" name="tsId" id="tsId"  value="{if isset($oTrustedShopsKundenbewertung->cTSID)}{$oTrustedShopsKundenbewertung->cTSID}{/if}" tabindex="1" /></p>

                <p class="submit"><input type="submit" value="{#settingsSave#}" class="button orange" /></p>                
                </form> 

                <form method="POST" action="trustedshops.php">
                <input type="hidden" name="{$session_name}" value="{$session_id}" />
                <input type="hidden" name="kundenbewertungupdate" value="1" />
                {if isset($oTrustedShopsKundenbewertung->cTSID) && isset($oTrustedShopsKundenbewertung->nStatus) && $oTrustedShopsKundenbewertung->cTSID|count_characters > 0 && $oTrustedShopsKundenbewertung->nStatus == 1}
                    <input name="tsKundenbewertungDeActive" type="submit" value="{#tsRatingDeActivate#}" />
                {elseif isset($oTrustedShopsKundenbewertung->cTSID) && isset($oTrustedShopsKundenbewertung->nStatus) && $oTrustedShopsKundenbewertung->cTSID|count_characters > 0 && $oTrustedShopsKundenbewertung->nStatus == 0}
                    <input name="tsKundenbewertungActive" type="submit" value="{#tsRatingActivate#}" />
                {/if}     
                </form>

                {if isset($Sprachen[$sessionSprachISO]->cURLKundenBewertungUebersicht) && $Sprachen[$sessionSprachISO]->cURLKundenBewertungUebersicht|count_characters > 0}
                    <br /><br /><strong><a href="{$Sprachen[$sessionSprachISO]->cURLKundenBewertungUebersicht}" target="_blank" style="text-decoration: underline;">{#tsRatingOverview#}</a></strong>            
                {/if}               
            </div>
        </div>
        {else}
            <p class="box_error">{#tsNoTSCustomerRatingError#}:<br /><br />{#noCURLAndFopenError#}</p>
        {/if}
        
    {else}
        <p class="box_error">{#tsNoTSError#}:<br /><br />{#noSOAPError#}</p>
    {/if}
    
</div>

<script type="text/javascript">
if(document.getElementById("eType").selectedIndex == 0)
{ldelim}
    document.getElementById("p_wsUser").style.display = "none";
    document.getElementById("p_wsPassword").style.display = "none";
{rdelim}
else if(document.getElementById("eType").selectedIndex == 1)
{ldelim}
    document.getElementById("p_wsUser").style.display = "block";
    document.getElementById("p_wsPassword").style.display = "block";
{rdelim}
</script>