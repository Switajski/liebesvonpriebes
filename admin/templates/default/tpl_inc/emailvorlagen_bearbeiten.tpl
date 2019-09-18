{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{assign var="template" value=#template#}
{assign var="modify" value=#modify#}
{include file="tpl_inc/seite_header.tpl" cTitel="`$template` `$Emailvorlage->cName` `$modify`" cBeschreibung=#emailTemplateModifyHint#}
<div id="content">	 
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
	 
	 <form name="vorlagen_aendern" method="post" action="emailvorlagen.php" enctype="multipart/form-data">
		  <input type="hidden" name="{$session_name}" value="{$session_id}" />
		  <input type="hidden" name="Aendern" value="1" />
		{if isset($kPlugin) && $kPlugin > 0}
     	  <input type="hidden" name="kPlugin" value="{$kPlugin}" />
		{/if}
		  <input type="hidden" name="kEmailvorlage" value="{$Emailvorlage->kEmailvorlage}" />
		  
		  <div id="settings" class="settings">
                      {if $Emailvorlage->cModulId != 'core_jtl_anbieterkennzeichnung'}
		  		<p>
		  			<label for="cEmailActive">{#emailActive#}</label>
					<select name="cEmailActive" id="cEmailActive">
						<option value="Y"{if isset($Emailvorlage->cAktiv) && $Emailvorlage->cAktiv == "Y"} selected{/if}>Ja</option>
						<option value="N"{if isset($Emailvorlage->cAktiv) && $Emailvorlage->cAktiv == "N"} selected{/if}>Nein</option>
					</select>
				</p>
				
		  		<p>
		  			<label for="cEmailOut">{#emailOut#}</label>
		  			<input name="cEmailOut" type="text" value="{if isset($oEmailEinstellungAssoc_arr.cEmailOut)}{$oEmailEinstellungAssoc_arr.cEmailOut}{/if}" />
		  		</p>
		  		
		  		<p>
		  			<label for="cEmailSenderName">{#emailSenderName#}</label>
		  			<input name="cEmailSenderName" type="text" value="{if isset($oEmailEinstellungAssoc_arr.cEmailSenderName)}{$oEmailEinstellungAssoc_arr.cEmailSenderName}{/if}" />
		  		</p>
		  		
		  		<p>
		  			<label for="cEmailCopyTo">{#emailCopyTo#}</label>
		  			<input name="cEmailCopyTo" type="text" value="{if isset($oEmailEinstellungAssoc_arr.cEmailCopyTo)}{$oEmailEinstellungAssoc_arr.cEmailCopyTo}{/if}" />
		  		</p>
		  
		  
				<p> <label for="cMailTyp">{#mailType#}</label>
				<select name="cMailTyp" id="cMailTyp">
				<option value="text/html" {if $Emailvorlage->cMailTyp=="text/html"}selected{/if}>text/html</option>
				<option value="text" {if $Emailvorlage->cMailTyp=="text"}selected{/if}>text</option>
				</select></p>
				<p>
				<label for="nAKZ">{#emailAddAKZ#}</label>
				<select name="nAKZ">                
				<option value="0"{if $Emailvorlage->nAKZ == "0"} selected{/if}>{#no#}</option>
				<option value="1"{if $Emailvorlage->nAKZ == "1"} selected{/if}>{#yes#}</option>
				</select>
				</p>
				<p>
				<label for="nAFK">{#emailAddAGB#}</label>
				<select name="nAGB">
				<option value="0"{if $Emailvorlage->nAGB == "0"} selected{/if}>{#no#}</option>
				<option value="1"{if $Emailvorlage->nAGB == "1"} selected{/if}>{#yes#}</option>
				</select>
				</p>
				<p>
				<label for="nAFK">{#emailAddWRB#}</label>
				<select name="nWRB">
				<option value="0"{if $Emailvorlage->nWRB == "0"} selected{/if}>{#no#}</option>
				<option value="1"{if $Emailvorlage->nWRB == "1"} selected{/if}>{#yes#}</option>
				</select>
				</p>
                      {else}
                          <input type="hidden" name="cEmailActive" value="Y" />
                          <input type="hidden" name="cMailTyp" value="text/html" />
                      {/if}
				
				<div class="container box_info">
					 <h2>Platzhalter (Beispiel)</h2>
					 <div class="elem">
						  <div class="name">{ldelim}$Kunde->cAnrede{rdelim}</div>
						  <div class="for">m</div>
					 </div>
					 <div class="elem">
						  <div class="name">{ldelim}$Kunde->cAnredeLocalized{rdelim}</div>
						  <div class="for">Herr</div>
					 </div>
					 <div class="elem">
						  <div class="name">{ldelim}$Kunde->cVorname{rdelim}</div>
						  <div class="for">Max</div>
					 </div>
					 <div class="elem">
						  <div class="name">{ldelim}$Kunde->cNachname{rdelim}</div>
						  <div class="for">Mustermann</div>
					 </div>
					 <div class="elem">
						  <div class="name">{ldelim}$Firma->cName{rdelim}</div>
						  <div class="for">Muster GmbH</div>
					 </div>
				</div>
				{foreach name=sprachen from=$Sprachen item=sprache}
					 {assign var="kSprache" value=$sprache->kSprache}
					 <div class="category">{$sprache->cNameDeutsch}</div>
                                         {if $Emailvorlage->cModulId != 'core_jtl_anbieterkennzeichnung'}	
                                            <div class="item">
                                                     <div class="name">{#subject#}</div>
                                                     <div class="for">
	                                                     <input style="width:400px" type="text" name="cBetreff_{$kSprache}" id="cBetreff_{$kSprache}"  value="{if isset($Emailvorlagesprache[$kSprache]->cBetreff)}{$Emailvorlagesprache[$kSprache]->cBetreff|escape:'html'}{/if}" tabindex="1" />
                                                     </div>
                                            </div>
                                         {/if}
					 
					 <div class="item">
						  <div class="name">{#mailHtml#}</div>
						  <div class="for">
								<textarea class="codemirror smarty" id="cContentHtml_{$kSprache}" name="cContentHtml_{$kSprache}" style="width:99%" rows="20">{if isset($Emailvorlagesprache[$kSprache]->cContentHtml)}{$Emailvorlagesprache[$kSprache]->cContentHtml|escape:'html'}{/if}</textarea>
						  </div>
					 </div>
					 
					 <div class="item">
						  <div class="name">{#mailText#}</div>
						  <div class="for">
								<textarea class="codemirror smarty" id="cContentText_{$kSprache}" name="cContentText_{$kSprache}" style="width:99%" rows="20">{if isset($Emailvorlagesprache[$kSprache]->cContentText)}{$Emailvorlagesprache[$kSprache]->cContentText}{/if}</textarea>
						  </div>
					 </div>
					 
					 {if isset($Emailvorlagesprache[$kSprache]->cPDFS_arr) && $Emailvorlagesprache[$kSprache]->cPDFS_arr|@count > 0}
					 <div class="item">
						  <div class="name">
								{#currentFiles#} (<a href="emailvorlagen.php?kEmailvorlage={$Emailvorlage->kEmailvorlage}&kS={$kSprache}&a=pdfloeschen{if isset($kPlugin) && $kPlugin > 0}&kPlugin={$kPlugin}{/if}">{#deleteAll#}</a>)
						  </div>
						  <div class="for">
								{foreach name=pdfs from=$Emailvorlagesprache[$kSprache]->cPDFS_arr item=cPDF}
	 								{assign var="i" value=$smarty.foreach.pdfs.iteration-1}
	 								<div><span class="pdf">{$Emailvorlagesprache[$kSprache]->cDateiname_arr[$i]}.pdf</span></div>
								{/foreach}
						  </div>
					 </div>
					 {/if}
				{if $Emailvorlage->cModulId != 'core_jtl_anbieterkennzeichnung'}
                    {section name=anhaenge loop=4 start=1 step=1}
                         <div class="item">
                              <div class="name">{#pdf#} {$smarty.section.anhaenge.index}</div>
                              <div class="for">
                                  {assign var=loopdekr value="`$smarty.section.anhaenge.index-1`"}
                                    {#filename#} <input name="dateiname_{$smarty.section.anhaenge.index}_{$kSprache}" type="text" value="{if isset($Emailvorlagesprache[$kSprache]->cDateiname_arr[$loopdekr])}{$Emailvorlagesprache[$kSprache]->cDateiname_arr[$loopdekr]}{/if}"{if isset($cFehlerAnhang_arr[$kSprache][$smarty.section.anhaenge.index]) && $cFehlerAnhang_arr[$kSprache][$smarty.section.anhaenge.index] == 1} class="fieldfillout"{/if}> <input name="pdf_{$smarty.section.anhaenge.index}_{$kSprache}" type="file"  maxlength="2097152" />
                              </div>
                         </div>
                    {/section}
                {/if}
				{/foreach}
		  </div>
		  <div class="save_wrapper">
				<input type="submit" value="{#save#}" class="button orange" />
		  </div>
	 </form>
</div>