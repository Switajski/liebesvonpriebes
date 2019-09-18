{if isset($cType) && $cType->oItem_arr|@count > 0}
	<p>{$cName}</p>
	{foreach from=$cType->oItem_arr item=oItem}
		<a href="{$oItem->cUrl}" rel="{$oItem->cName|escape:'html'}" forward="{$oSearchResponse->oSuggest->nForwarding}">
			{if $oItem->cImageUrl|@strlen > 0}
				<div class="article_wrapper clearall">
					<div class="article_image">
						<img src="{$oItem->cImageUrl}" alt="{$oItem->cName|escape:'html'}" />
					</div>
					<div class="article_info">
						{$oItem->cName|regex_replace:"/($cSearch)/i":"<span class='jtl_match'>\$1</span>"}
					</div>
				</div>
			{else}
				{$oItem->cName|regex_replace:"/($cSearch)/i":"<span class='jtl_match'>\$1</span>"} {if $oItem->nCount > 0}<em class="count">({$oItem->nCount})</em>{/if}
			{/if}
		</a>
	{/foreach}
{/if}