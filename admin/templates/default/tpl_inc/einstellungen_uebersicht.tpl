{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{assign var="cTitel" value=#preferences#|cat:": "|cat:$Sektion->cName}
{if isset($cSearch) && $cSearch|count_characters  > 0}
	{assign var="cTitel" value=$cSearch}
{/if}

{include file="tpl_inc/seite_header.tpl" cTitel=#preferences# cBeschreibung=#preferencesDesc# cDokuURL=#preferencesURL#}
<div id="content">
	 <table class="list">
		  <tbody>
				{foreach name=einst from=$Sektionen item=Sektion}
				<tr>
					 <td>{$Sektion->cName}</td>
					 <td>{$Sektion->anz} {#preferences#}</td>
					 <td><a href="einstellungen.php?{$SID}&kSektion={$Sektion->kEinstellungenSektion}" class="button edit">{#configure#}</a></td>
				</tr>
				{/foreach}
		  </tbody>
	 </table>
</div>