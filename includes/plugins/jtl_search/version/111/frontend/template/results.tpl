{if $cSearch_arr|@count > 0}
	<div id="result_set">
		<div class="result_row_wrapper clearall">
			<div class="result_row first">
				<p class="jtl-search-for">{$langVars.search_for}</p>
				<a href="#" rel="{$cSearch|escape:'html'}">{$cSearch}</a>
				{if $oSearchResponse->oSuggest->cSuggest|@count_characters > 0 && $oSearchResponse->oSuggest->nForwarding == 1}
					<p>{$langVars.did_you_mean}</p>
					<a href="#" rel="{$oSearchResponse->oSuggest->cSuggest|escape:'html'}">{$oSearchResponse->oSuggest->cSuggest}</a>
				{/if}
				{if isset($cSearch_arr.landingpage)}
					{include file=$cTemplatePath|cat:'result_item.tpl' cType=$cSearch_arr.landingpage cName=$langVars.suggested_pages}
				{/if}
				{if isset($cSearch_arr.query)}
					{include file=$cTemplatePath|cat:'result_item.tpl' cType=$cSearch_arr.query cName=$langVars.suggested_search_terms}
				{/if}
				{if isset($cSearch_arr.category)}
					{include file=$cTemplatePath|cat:'result_item.tpl' cType=$cSearch_arr.category cName=$langVars.suggested_categories}
				{/if}
				{if isset($cSearch_arr.manufacturer)}
					{include file=$cTemplatePath|cat:'result_item.tpl' cType=$cSearch_arr.manufacturer cName=$langVars.suggested_manufacturers}
				{/if}
			</div>
			{if isset($cSearch_arr.product)}
				<div class="result_row">
					{include file=$cTemplatePath|cat:'result_item.tpl' cType=$cSearch_arr.product cName=$langVars.suggested_products}
				</div>
			{/if}
		</div>
		{if $bBranding}
		<div class="result_copy"></div>
		{/if}
	</div>
{/if}