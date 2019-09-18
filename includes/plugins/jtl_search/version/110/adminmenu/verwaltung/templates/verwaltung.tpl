<div>
	{if $cBaseCssURL|strlen > 0}
		<link media="screen" href="{$cBaseCssURL}" type="text/css" rel="stylesheet" />
	{/if}

	{foreach from=$cStatusModulAssoc_arr item=cStatusModulAssoc}
		<div class="jtlsearch_status_box panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">{$cStatusModulAssoc.cName}</h3>
			</div>
			<div class="jtlsearch_inner">
				{if $cStatusModulAssoc.cCssURL|strlen > 0}
					<link media="screen" href="{$cStatusModulAssoc.cCssURL}" type="text/css" rel="stylesheet" />
				{/if}
				<div class="jtlsearch_wrapper">
					{$cStatusModulAssoc.cContent}
				</div>
			</div>
		</div>
	{/foreach}
	<script type="text/javascript">
		$(function () {ldelim}
			$('.jtlsearch_wrapper').each(function () {ldelim}
				var heightActionColumn = $(this).children('.jtlsearch_actioncolumn').height(),
					heightInfoColumn = $(this).children('.jtlsearch_infocolumn').height();
				if (heightActionColumn > heightInfoColumn) {ldelim}
					$(this).children('.jtlsearch_infocolumn').height(heightActionColumn);
				{rdelim} else if (heightActionColumn > 0) {ldelim}
					$(this).children('.jtlsearch_actioncolumn').height(heightInfoColumn);
				{rdelim}
			{rdelim});
		{rdelim});
	</script>
</div>