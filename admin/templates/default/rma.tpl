{*
-------------------------------------------------------------------------------
	JTL-Shop 3
	File: rma.tpl, smarty template inc file
	
	page for JTL-Shop 3 
	Admin
	
	Author: daniel.boehmer@jtl-software.de, JTL-Software
	http://www.jtl-software.de
	
	Copyright (c) 2009 JTL-Software
-------------------------------------------------------------------------------
*}

{include file='tpl_inc/header.tpl'}

{config_load file="$lang.conf" section="rma"}

<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

<script type="text/javascript">
{literal}
function setRMAStatus(kRMA, nStatus)
{
    myCallback = xajax.callback.create();
	myCallback.onComplete = function(obj) {
        data = obj.context.response;
		console.log(data);
    }
    xajax.call('setRMAStatusAjax', { parameters: [kRMA, nStatus], callback: myCallback, context: this } );
	return false;
}
{/literal}
</script>

{include file="tpl_inc/seite_header.tpl" cTitel=#rma# cBeschreibung=#rmaDesc# cDokuURL=#rmaURL#}
<div id="content">
	
{if isset($cHinweis) && $cHinweis|count_characters > 0}
    <p class="box_success">{$cHinweis}</p>
{/if}
{if isset($cFehler) && $cFehler|count_characters > 0}
    <p class="box_error">{$cFehler}</p>
{/if}

{if !$noModule}
    
    <div class="container">
        
        <div class="tabber">
				 
			<!--
			 * * * * * * * * * 
			 * �bersicht RMA *
			 * * * * * * * * * 
			-->
        	<div class="tabbertab{if isset($cTab) && $cTab == 'overview'} tabbertabdefault{/if}">
        
        		<h2>{#rmaOverview#}</h2>
        
        		{include file='tpl_inc/rma_overview.tpl'}
        
        	</div>
        	
        	<!--
			 * * * * * * * * * * 
			 * Grundverwaltung *
			 * * * * * * * * * * 
			-->
			<div class="tabbertab{if isset($cTab) && $cTab == 'reason'} tabbertabdefault{/if}">
        
        		<h2>{#rmaReasonManagement#}</h2>
        
        		{include file='tpl_inc/rma_reason.tpl'}
        
        	</div>
			
			<!--
			 * * * * * * * * * * 
			 * Statusverwaltung *
			 * * * * * * * * * * 
			-->
			<div class="tabbertab{if isset($cTab) && $cTab == 'status'} tabbertabdefault{/if}">
        
        		<h2>{#rmaStatusManagement#}</h2>
        
        		{include file='tpl_inc/rma_status.tpl'}
        
        	</div>
			
			<!--
			 * * * * * * * * * 
			 * Einstellungen *
			 * * * * * * * * * 
			-->
			<div class="tabbertab{if isset($cTab) && $cTab == 'config'} tabbertabdefault{/if}">
        
        		<h2>{#rmaConfig#}</h2>
        
        		{include file='tpl_inc/rma_config.tpl'}
        
        	</div>
        
        </div>
        
    </div>
{else}
    <p class="box_error">{#noModuleAvailable#}</p>
{/if}
</div>

{include file='tpl_inc/footer.tpl'}