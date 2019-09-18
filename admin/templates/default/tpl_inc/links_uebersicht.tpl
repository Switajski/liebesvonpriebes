{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: links.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}
<script type="text/javascript">
{literal}
function confirmDelete() {
	 return confirm('Möchten Sie den Link wirklich löschen?');
}
{/literal}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#links# cBeschreibung=#linksDesc# cDokuURL=#linksUrl#}
<div id="content">

	 {if isset($hinweis) && $hinweis|count_characters > 0}			
		  <p class="box_success">{$hinweis}</p>
	 {/if}
	 {if isset($fehler) && $fehler|count_characters > 0}			
		  <p class="box_error">{$fehler}</p>
	 {/if}
	 
	 <div class="container">
		  <a class="button add" href="links.php?neuelinkgruppe=1&{$SID}">{#newLinkGroup#}</a>
	 </div>

	 <table class="list">
		  {foreach name=linkgruppen from=$linkgruppen item=linkgruppe}
		  <thead>
		  <tr>
				<th class="tleft">{#linkGruop#}: {$linkgruppe->cName}</th>
				<th class="tcenter"><a href="links.php?kLinkgruppe={$linkgruppe->kLinkgruppe}" class="button edit">{#modify#}</a></th>
				<th class="tcenter"><a href="links.php?addlink={$linkgruppe->kLinkgruppe}" class="button add">{#addLink#}</a></th>
				<th class="tcenter"><a href="links.php?delconfirmlinkgruppe={$linkgruppe->kLinkgruppe}" class="button remove">{#linkGruop#} {#delete#}</a></th>
		  </tr>
		  </thead>
		  <tbody>
		  <tr>
				<td colspan="5">{#linkGruopTemplatename#}: <strong>{$linkgruppe->cTemplatename}</strong></td>
		  </tr>

          {include file="tpl_inc/links_uebersicht_item.tpl" list=$linkgruppe->links id=$linkgruppe->kLinkgruppe}

		  </tbody>
		  {/foreach}
	 </table>
	 <div class="container">
		  <a class="button add" href="links.php?neuelinkgruppe=1&{$SID}">{#newLinkGroup#}</a>
	 </div>
</div>