{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: preisanzeige.tpl, smarty template inc file

	admin page for JTL-Shop 3

	Copyright (c) 2008 JTL-Software
    

-------------------------------------------------------------------------------
*}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="einstellungen"}
{config_load file="$lang.conf" section="preisanzeige"}

{include file="tpl_inc/seite_header.tpl" cTitel=#priceNotification# cBeschreibung=#information# cDokuURL=#priceURL#}
<div id="content">
<p class="box_info">
	 {#priceInfo#}
</p>

{if isset($hinweis) && $hinweis|count_characters > 0}			
	 <p class="box_success">{$hinweis}</p>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}			
	 <p class="box_error">{$fehler}</p>
{/if}


{if $oPreisanzeigeConf_arr|@count > 0 && $cSektion_arr|@count > 0}
<div class="container">
	 <form name="einstellen" method="post" action="preisanzeige.php">
	 <input type="hidden" name="{$session_name}" value="{$session_id}">
	 <input name="update" type="hidden" value="1">
	 <table class="list">
		  <thead>
		  <tr>
				<th class="tleft">{#priceIn#}</th>
				<th>{#priceToGrafik#}</th>
				<th>Farbe</th>
				<th>Gr&ouml;&szlig;e</th>
				<th>{#font#}</th>
		  </tr>
		  </thead>
		  <tbody>
		  {foreach name=preisanzeige from=$cSektion_arr item=cSektion}
		  <tr>
				<td>
					 {$cSektion}
				</td>
				<td class="tcenter">
					 <select name="{$oPreisanzeigeConf_arr[$cSektion][2]->cName}">
						  <option value="Y" {if $oPreisanzeigeConf_arr[$cSektion][2]->cWert == "Y"}selected{/if}>{#yes#}</option>
						  <option value="N" {if $oPreisanzeigeConf_arr[$cSektion][2]->cWert == "N"}selected{/if}>{#no#}</option>
					 </select>
				</td>
				<td class="tcenter">					 
					 <div id="{$oPreisanzeigeConf_arr[$cSektion][0]->cName}" style="display:inline-block">
						 <div style="background-color: {$oPreisanzeigeConf_arr[$cSektion][0]->cWert}" class="colorSelector"></div>
					 </div>
					 <input type="hidden" name="{$oPreisanzeigeConf_arr[$cSektion][0]->cName}" class="{$oPreisanzeigeConf_arr[$cSektion][0]->cName}_data" value="{$oPreisanzeigeConf_arr[$cSektion][0]->cWert}" />
					 <script type="text/javascript">
						 $('#{$oPreisanzeigeConf_arr[$cSektion][0]->cName}').ColorPicker({ldelim}
							 color: '{$oPreisanzeigeConf_arr[$cSektion][0]->cWert}',
							 onShow: function (colpkr) {ldelim}
								 $(colpkr).fadeIn(500);
								 return false;
							 {rdelim},
							 onHide: function (colpkr) {ldelim}
								 $(colpkr).fadeOut(500);
								 return false;
							 {rdelim},
							 onChange: function (hsb, hex, rgb) {ldelim}
								 $('#{$oPreisanzeigeConf_arr[$cSektion][0]->cName} div').css('backgroundColor', '#' + hex);
								 $('.{$oPreisanzeigeConf_arr[$cSektion][0]->cName}_data').val('#' + hex);
							 {rdelim}
						 {rdelim});
					 </script>
				</td>
				<td class="tcenter">
					 <input type="text" name="{$oPreisanzeigeConf_arr[$cSektion][1]->cName}" value="{$oPreisanzeigeConf_arr[$cSektion][1]->cWert}" size="3" />
				</td>
				<td class="tcenter">
					 <select name="{$oPreisanzeigeConf_arr[$cSektion][3]->cName}">
						  <option value="">&nbsp;</option>
						  {foreach from=$cFont_arr item=font}
								<option value="{$font}" {if $oPreisanzeigeConf_arr[$cSektion][3]->cWert == $font}selected{/if}>{$font}</option>
						  {/foreach}                    
					 </select>
				</td>
		  </tr>    
		  {/foreach}
		  </tbody>
	 </table>
	 <div class="save_wrapper">
		  <input name="speichern" type="submit" value="{#savePreferences#}" class="button orange" />
	 </div>
	 </form>
</div>
{/if}   
{include file='tpl_inc/footer.tpl'}