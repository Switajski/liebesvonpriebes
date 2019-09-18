<link media="screen" href="{$cBaseCssURL}" type="text/css" rel="stylesheet" />
<div id="JTLSearch_testperiod">
	{if $bStartedTestperiod}
		Testperiode erfolgreich gestartet!
		<script type="text/javascript">
			$(document).ready(function () {ldelim}
				window.location.href = 'plugin.php?kPlugin={$oPlugin->kPlugin}';
			{rdelim});
		</script>
	{else}
		{foreach from=$oForm->getErrorMessages() item=cErrorMessage}
			<div class="box_error alert alert-danger">{$cErrorMessage}</div>
		{/foreach}
		{$oForm->getFormStartHTML()}
		{$oForm->getHiddenElements()}
		<div class="setting">
			<div class="input-group">
				<span class="input-group-addon">{$oForm->getLabelHTML(cCode)}</span>
				{$oForm->getElementHTML(cCode)}
			</div>
			<div class="save_wrapper">{$oForm->getElementHTML(btn_serverinfo)}</div>
		</div>
		{$oForm->getFormEndHTML()}
	{/if}
</div>