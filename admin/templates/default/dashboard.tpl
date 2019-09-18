{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: login.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: JTL-Software-GmbH
	http://www.jtl-software.de
	
	Copyright (c) 2007 JTL-Software
    

-------------------------------------------------------------------------------
*}

{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="login"}
{config_load file="$lang.conf" section="shopupdate"}

{if permission('DASHBOARD_VIEW')}
	<script type="text/javascript" src="../includes/libs/flashchart/js/json/json2.js"></script>
	<script type="text/javascript" src="../includes/libs/flashchart/js/swfobject.js"></script>
	<script type="text/javascript">
	function slideToggle() {ldelim}
		if ($('#settings').is(':hidden'))
		{ldelim}
			xajax_getAvailableWidgetsAjax();
			$('#settings').slideDown('fast');
			$('.column_wrapper').slideUp('fast');
		{rdelim}
		else
		{ldelim}
			$('#settings').slideUp('fast');
			$('.column_wrapper').slideDown('fast');
		{rdelim}
	{rdelim}
	
	function registerWidgetSettings() {ldelim}
		$('.widget_item a.add').click(function() {ldelim}
			var kWidget = $(this).attr('ref');
			myCallback = xajax.callback.create();
			myCallback.onComplete = function(obj) {ldelim}
				window.location.href='index.php?kWidget=' + kWidget;
			{rdelim}
			xajax.call('addWidgetAjax', {ldelim} parameters: [kWidget], callback: myCallback, context: this {rdelim} );
		{rdelim});
	{rdelim}
	
	$(function() {ldelim}
		xajax_truncateJtllog();
	{rdelim});
	</script>
	
	{include file="tpl_inc/seite_header.tpl" cTitel=#dashboard# cBeschreibung="Einstellungen" cClass="tright" onClick="slideToggle();return false;"}
	<div id="content" class="nomargin clearall">
		
		{if $bInstallExists && !$updateMessage}
			<p class="container box_error">{#deleteInstallDir#}</p>
		{/if}
      
		{if isset($bTemplateDiffers) && $bTemplateDiffers && !$updateMessage}
			<p class="container box_error">{#templateDiffers#}</p>
		{/if}
      
      
		
		<div class="widget_settings_wrapper" id="settings">
			<div class="widget_settings">			
				{foreach from=$oAvailableWidget_arr item=oAvailableWidget}
					<div class="widget_item">
						<p class="title">{$oAvailableWidget->cTitle}</p>
						<p class="desc">{$oAvailableWidget->cDescription}</p>
						<a href="#" class="add" ref="{$oAvailableWidget->kWidget}"></a>
					</div>
				{/foreach}
				{if $oAvailableWidget_arr|@count == 0}
					<div class="widget_item">
						<p class="title">Keine weiteren Widgets vorhanden.</p>
					</div>
				{/if}
			</div>
		</div>
		
		<div class="column_wrapper clear">
			{include file="tpl_inc/widget_container.tpl" eContainer="left"}
			{include file="tpl_inc/widget_container.tpl" eContainer="center"}
			{include file="tpl_inc/widget_container.tpl" eContainer="right"}
		</div>
	</div>
	
	<script type="text/javascript" src="{$currentTemplateDir}js/inettuts.js"></script>
{else}
	{include file="tpl_inc/seite_header.tpl" cTitel=#dashboard#}
	<br />
	<p class="box_info">Es stehen keine weiteren Informationen zur Verf&uuml;gung</p>
{/if}

{include file='tpl_inc/footer.tpl'}
