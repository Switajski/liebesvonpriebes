{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: bilder.tpl, smarty template inc file
	
	page for JTL-Shop 3
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="bilder"}

{include file="tpl_inc/seite_header.tpl" cTitel=#bilder# cBeschreibung=#bilderDesc# cDokuURL=#bilderURL#}
<div id="content">
	
	{if isset($hinweis) && $hinweis|count_characters > 0}
		<p class="box_success">{$hinweis}</p>
	{/if}
	{if isset($fehler) && $fehler|count_characters > 0}
		<p class="box_error">{$fehler}</p>
	{/if}
	
	<div class="container">
		<form method="post" action="bilder.php">
		<input type="hidden" name="{$session_name}" value="{$session_id}">
		<input type="hidden" name="speichern" value="1">
		<div id="settings">
         <div class="category">Bildgr&ouml;&szlig;en</div>
            
         <table class="list">
            <thead>
               <tr>
                  <th class="tleft">Typ</th>
                  <th class="tcenter">Mini</th>
                  <th class="tcenter">Klein</th>
                  <th class="tcenter">Normal</th>
                  <th class="tcenter">Gro&ouml;&szlig;</th>
               </tr>
            </thead>
            <tbody>
               <tr>
                  <td></td>
                  <td class="tcenter"><small>(H&ouml;he x Breite)</small></td>
                  <td class="tcenter"><small>(H&ouml;he x Breite)</small></td>
                  <td class="tcenter"><small>(H&ouml;he x Breite)</small></td>
                  <td class="tcenter"><small>(H&ouml;he x Breite)</small></td>
               </tr>
               <tr>
                  <td class="tleft">Kategorien</td>
                  <td class="tcenter"></td>
                  <td class="tcenter"></td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_kategorien_hoehe" value="{$oConfig.bilder_kategorien_hoehe}" /> x <input type="text" name="bilder_kategorien_breite" value="{$oConfig.bilder_kategorien_breite}" />
                  </td>
                  <td class="tcenter"></td>
               </tr>
               
               <tr>
                  <td class="tleft">Variationen</td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_variationen_mini_hoehe" value="{$oConfig.bilder_variationen_mini_hoehe}" /> x <input type="text" name="bilder_variationen_mini_breite" value="{$oConfig.bilder_variationen_mini_breite}" />
                  </td>
                  <td class="tcenter"></td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_variationen_hoehe" value="{$oConfig.bilder_variationen_hoehe}" /> x <input type="text" name="bilder_variationen_breite" value="{$oConfig.bilder_variationen_breite}" />
                  </td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_variationen_gross_hoehe" value="{$oConfig.bilder_variationen_gross_hoehe}" /> x <input type="text" name="bilder_variationen_gross_breite" value="{$oConfig.bilder_variationen_gross_breite}" />
                  </td>
               </tr>
               
               <tr>
                  <td class="tleft">Produkte</td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_artikel_mini_hoehe" value="{$oConfig.bilder_artikel_mini_hoehe}" /> x <input type="text" name="bilder_artikel_mini_breite" value="{$oConfig.bilder_artikel_mini_breite}" />
                  </td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_artikel_klein_hoehe" value="{$oConfig.bilder_artikel_klein_hoehe}" /> x <input type="text" name="bilder_artikel_klein_breite" value="{$oConfig.bilder_artikel_klein_breite}" />
                  </td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_artikel_normal_hoehe" value="{$oConfig.bilder_artikel_normal_hoehe}" /> x <input type="text" name="bilder_artikel_normal_breite" value="{$oConfig.bilder_artikel_normal_breite}" />
                  </td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_artikel_gross_hoehe" value="{$oConfig.bilder_artikel_gross_hoehe}" /> x <input type="text" name="bilder_artikel_gross_breite" value="{$oConfig.bilder_artikel_gross_breite}" />
                  </td>
               </tr>
               
               <tr>
                  <td class="tleft">Hersteller</td>
                  <td class="tcenter"></td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_hersteller_klein_hoehe" value="{$oConfig.bilder_hersteller_klein_hoehe}" /> x <input type="text" name="bilder_hersteller_klein_breite" value="{$oConfig.bilder_hersteller_klein_breite}" />
                  </td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_hersteller_normal_hoehe" value="{$oConfig.bilder_hersteller_normal_hoehe}" /> x <input type="text" name="bilder_hersteller_normal_breite" value="{$oConfig.bilder_hersteller_normal_breite}" />
                  </td>
                  <td class="tcenter"></td>
               </tr>
               
               <tr>
                  <td class="tleft">Merkmale</td>
                  <td class="tcenter"></td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_merkmal_klein_hoehe" value="{$oConfig.bilder_merkmal_klein_hoehe}" /> x <input type="text" name="bilder_merkmal_klein_breite" value="{$oConfig.bilder_merkmal_klein_breite}" />
                  </td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_merkmal_normal_hoehe" value="{$oConfig.bilder_merkmal_normal_hoehe}" /> x <input type="text" name="bilder_merkmal_normal_breite" value="{$oConfig.bilder_merkmal_normal_breite}" />
                  </td>
                  <td class="tcenter"></td>
               </tr>
               
               <tr>
                  <td class="tleft">Merkmalwerte</td>
                  <td class="tcenter"></td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_merkmalwert_klein_hoehe" value="{$oConfig.bilder_merkmalwert_klein_hoehe}" /> x <input type="text" name="bilder_merkmalwert_klein_breite" value="{$oConfig.bilder_merkmalwert_klein_breite}" />
                  </td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_merkmalwert_normal_hoehe" value="{$oConfig.bilder_merkmalwert_normal_hoehe}" /> x <input type="text" name="bilder_merkmalwert_normal_breite" value="{$oConfig.bilder_merkmalwert_normal_breite}" />
                  </td>
                  <td class="tcenter"></td>
               </tr>
               
               <tr>
                  <td class="tleft">Konfiggruppe</td>
                  <td class="tcenter"></td>
                  <td class="widthheight tcenter">
                     <input type="text" name="bilder_konfiggruppe_klein_hoehe" value="{$oConfig.bilder_konfiggruppe_klein_hoehe}" /> x <input type="text" name="bilder_konfiggruppe_klein_breite" value="{$oConfig.bilder_konfiggruppe_klein_breite}" />
                  </td>
                  <td class="tcenter"></td>
                  <td class="tcenter"></td>
               </tr>
               
            </tbody>
         </table>
      
			{foreach name=conf from=$oConfig_arr item=cnf}
            {if $cnf->kEinstellungenConf == 267 || $cnf->kEinstellungenConf == 268 || $cnf->kEinstellungenConf == 269 || $cnf->kEinstellungenConf == 1135 || $cnf->kEinstellungenConf == 1421 || $cnf->kEinstellungenConf == 172 || $cnf->kEinstellungenConf == 161  || $cnf->kEinstellungenConf == 1483  || $cnf->kEinstellungenConf == 1484 || $cnf->kEinstellungenConf == 1485}
               {if $cnf->cConf=="Y"}
                  <div class="item{if isset($cSuche) && $cnf->kEinstellungenConf == $cSuche} highlight{/if}">
                     <div class="name">
                        <label for="{$cnf->cWertName}">
                           {$cnf->cName} <span class="sid">{$cnf->kEinstellungenConf} &raquo;</span>
                        </label>
                     </div>
                     <div class="for">
                        {if $cnf->cInputTyp=="selectbox"}
                           <select name="{$cnf->cWertName}" id="{$cnf->cWertName}">
                              {foreach name=selectfor from=$cnf->ConfWerte item=wert}
                                 <option value="{$wert->cWert}" {if $cnf->gesetzterWert==$wert->cWert}selected{/if}>{$wert->cName}</option>
                              {/foreach}
                           </select>
                        {elseif $cnf->cInputTyp=="pass"}
                           <input type="password" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" /> 
                        {elseif $cnf->cInputTyp=="color"}
                           <div id="{$cnf->cWertName}" style="display:inline-block">
                              <div style="background-color: {$cnf->gesetzterWert}" class="colorSelector"></div>
                           </div>
                           <input type="hidden" name="{$cnf->cWertName}" class="{$cnf->cWertName}_data" value="{$cnf->gesetzterWert}" />
                           <script type="text/javascript">
                              $('#{$cnf->cWertName}').ColorPicker({ldelim}
                              color: '{$cnf->gesetzterWert}',
                              onShow: function (colpkr) {ldelim}
                              $(colpkr).fadeIn(500);
                              return false;
                              {rdelim},
                              onHide: function (colpkr) {ldelim}
                              $(colpkr).fadeOut(500);
                              return false;
                              {rdelim},
                              onChange: function (hsb, hex, rgb) {ldelim}
                              $('#{$cnf->cWertName} div').css('backgroundColor', '#' + hex);
                              $('.{$cnf->cWertName}_data').val('#' + hex);
                              {rdelim}
                              {rdelim});
                           </script>
                        {else}
                           <input type="text" name="{$cnf->cWertName}" id="{$cnf->cWertName}" value="{$cnf->gesetzterWert}" tabindex="1" /> 
                        {/if}
                        
                        {if $cnf->cBeschreibung}
                           <div class="help" ref="{$cnf->kEinstellungenConf}" title="{$cnf->cBeschreibung}"></div>
                        {/if}
                     </div>
                  </div> 
               {else}
                  <div class="category">
                     {$cnf->cName}
                     <div class="right">
                        <p class="sid">{$cnf->kEinstellungenConf}</p>
                        {if isset($cnf->cSektionsPfad) && $cnf->cSektionsPfad|count_characters > 0}
                           <p class="path"><strong>{#settingspath#}:</strong> {$cnf->cSektionsPfad}</p>
                        {/if}
                     </div>
                  </div>
               {/if}
            {/if}
			{/foreach}
		</div>
		
		<p class="submit"><input name="speichern" type="submit" value="{#bilderSave#}" class="button orange" /></p>
		</form>
	</div>		
</div>


{include file='tpl_inc/footer.tpl'}