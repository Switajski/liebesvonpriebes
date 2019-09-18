{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: emailvorlagen_uebersicht.tpl, smarty template inc file

	admin page for JTL-Shop 3

	http://www.jtl-software.de

	Copyright (c) 2008 JTL-Software

-------------------------------------------------------------------------------
*}

{include file="tpl_inc/seite_header.tpl" cTitel=#emailTemplates# cBeschreibung=#emailTemplatesHint# cDokuURL=#emailTemplateURL#}
<div id="content">
	 <p class="box_info">
		  {#testmailsGoToEmail#}
		  <strong>
		  {if $Einstellungen.emails.email_master_absender}
				{$Einstellungen.emails.email_master_absender}
		  {else}
				{#noMasterEmailSpecified#}
		  {/if}
		  </strong>
	 </p>
	 
	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
	
	 <div class="category">{#emailTemplates#}</div>
	 <table class="list">
		  <thead>
				<tr>
					 <th class="tleft">{#template#}</th>
					 <th>{#type#}</th>
					 <th>{#active#}</th>
					 <th>{#options#}</th>
				</tr>
		  </thead>
		  <tbody>
				{foreach name=emailvorlagen from=$emailvorlagen item=emailvorlage}
					 <tr>
						  <td>{$emailvorlage->cName}</td>
						  <td class="tcenter">{$emailvorlage->cMailTyp}</td>
						  <td class="tcenter">
								{if $emailvorlage->cAktiv == 'Y'}
									 <span class="success">aktiv</span>
								{else}
									 {if $emailvorlage->nFehlerhaft == 1}
										  <span class="error">fehlerhaft</span>
									 {else}
										  <span class="error">inaktiv</span>
									 {/if}
								{/if}
						  </td>
						  <td class="tcenter">
								<a href="emailvorlagen.php?{$SID}&preview={$emailvorlage->kEmailvorlage}" class="button mail">{#testmail#}</a>
								<a href="emailvorlagen.php?{$SID}&kEmailvorlage={$emailvorlage->kEmailvorlage}" class="button edit">{#modify#}</a>
								<a href="emailvorlagen.php?{$SID}&resetConfirm={$emailvorlage->kEmailvorlage}" class="button reset">{#resetEmailTemplate#}</a>
						  </td>
					 </tr>
				{/foreach}
		  </tbody>
	 </table>
	 
{if isset($oPluginEmailvorlage_arr) && $oPluginEmailvorlage_arr|count > 0}
	<table class="list">
		<thead>
			<tr>
				<th class="tleft">{#template#}</th>
				<th>{#type#}</th>
				<th>{#active#}</th>
				<th>{#options#}</th>
			</tr>
		</thead>
		<tbody>
		{foreach name=emailvorlagen from=$oPluginEmailvorlage_arr item=oPluginEmailvorlage}
			<tr>
				<td>{$oPluginEmailvorlage->cName}</td>
				<td class="tcenter">{$oPluginEmailvorlage->cMailTyp}</td>
				<td class="tcenter">
				{if $oPluginEmailvorlage->cAktiv == 'Y'}
					<span class="success">aktiv</span>
				{else}
					 {if $emailvorlage->nFehlerhaft == 1}
						  <span class="error">fehlerhaft</span>
					 {else}
						  <span class="error">inaktiv</span>
					 {/if}
				{/if}
				</td>
				<td class="tcenter">
					<a href="emailvorlagen.php?{$SID}&preview={$oPluginEmailvorlage->kEmailvorlage}&kPlugin={$oPluginEmailvorlage->kPlugin}" class="button mail">{#testmail#}</a>
					<a href="emailvorlagen.php?{$SID}&kEmailvorlage={$oPluginEmailvorlage->kEmailvorlage}&kPlugin={$oPluginEmailvorlage->kPlugin}" class="button edit">{#modify#}</a>
					<a href="emailvorlagen.php?{$SID}&resetConfirm={$oPluginEmailvorlage->kEmailvorlage}&kPlugin={$oPluginEmailvorlage->kPlugin}" class="button reset">{#resetEmailTemplate#}</a>
				</td>
			</tr>
		{/foreach}
		</tbody>
	</table>	
{/if}
</div>