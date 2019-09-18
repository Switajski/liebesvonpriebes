<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

{literal}
<script type="text/javascript">
$(document).ready(function() {
	$(".edit").click(function() {
		var kWarenlager = $(this).attr("id").replace("btn_", "");
		
		if ($(".row_" + kWarenlager).css("display") == "none")
		    $(".row_" + kWarenlager).fadeIn();
		else
			$(".row_" + kWarenlager).fadeOut();
	});
});
</script>
{/literal}

<div id="content">

{if isset($cHinweis) && $cHinweis|count_characters > 0}
    <p class="box_success">{$cHinweis}</p>
{/if} 
{if isset($cFehler) && $cFehler|count_characters > 0}
    <p class="box_error">{$cFehler}</p>
{/if}

{if $oWarenlager_arr|@count > 0}
	<div class="category">{#warenlager#}</div>
	<form method="POST" action="warenlager.php">
	<input name="a" type="hidden" value="update" />
	<table class="list">
		<thead>
			<tr>
				<th class="checkext">{#watenlagerActive#}</th>
				<th>{#warenlagerIntern#}</th>
				<th>{#warenlagerDescInt#}</th>
				<th>{#warenlagerOption#}</th>
			</tr>
		</thead>
		<tbody>
			{foreach name=warenlager from=$oWarenlager_arr item=oWarenlager}
			<tr>
				<td class="checkext"><input name="kWarenlager[]" type="checkbox" value="{$oWarenlager->kWarenlager}"{if $oWarenlager->nAktiv == 1} checked{/if} /></td>
				<td class="tcenter large">{$oWarenlager->cName}</td>
				<td class="tcenter">{$oWarenlager->cBeschreibung}</td>
				<td class="tcenter"><a href="#" class="button edit" id="btn_{$oWarenlager->kWarenlager}">{#warenlagerDesignation#}</a></td>
			</tr>
			{foreach name=sprachen from=$oSprache_arr item=oSprache}
                {assign var="kSprache" value=$oSprache->kSprache}
			<tr class="hide row_{$oWarenlager->kWarenlager}">
                <td class="tcenter"><strong>{$oSprache->cNameDeutsch}</strong></td>
                <td class="tcenter large"><input name="cNameSprache[{$oWarenlager->kWarenlager}][{$oSprache->kSprache}]" type="text" value="{if isset($oWarenlager->cSpracheAssoc_arr[$kSprache])}{$oWarenlager->cSpracheAssoc_arr[$kSprache]}{/if}" class="large" /></td>
                <td colspan="2">&nbsp;</td>
			</tr>
			{/foreach}
			{/foreach}
		</tbody>
	</table>
    <div class="save_wrapper">
        <input name="update" type="submit" value="{#warenlagerUpdate#}" class="button orange" />
    </div>
	</form>
{/if}
	
</div>