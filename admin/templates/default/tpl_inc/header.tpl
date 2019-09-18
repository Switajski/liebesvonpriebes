<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE-edge,chrome=1" />
        <meta http-equiv="Content-Type" content="text/html; charset={$JTL_CHARSET}" />
        <meta http-equiv="language" content="deutsch, de" />
        <meta name="author" content="JTL-Software GmbH" />
        <meta name="robots" content="noarchive" />
        <meta name="robots" content="nofollow" />
        <meta name="robots" content="noindex" />
        <meta name="date" content="" />
        <title>JTL-Shop3 Administration</title>
        <link type="image/x-icon" href="favicon.ico" rel="icon" />
        <link type="image/x-icon" href="favicon.ico" rel="shortcut icon" />

	    {$admin_css}
	    {*<link rel="stylesheet" type="text/css" href="{$currentTemplateDir}css/base.css" media="screen" />*}
	    {*<link rel="stylesheet" type="text/css" href="{$currentTemplateDir}css/layout.css" media="screen" />*}
	    {*<link rel="stylesheet" type="text/css" href="{$currentTemplateDir}css/widgets.css" media="screen" />*}
	    {*<link rel="stylesheet" type="text/css" href="{$currentTemplateDir}css/menu.css" media="screen" />*}
	    {*<link rel="stylesheet" type="text/css" href="{$currentTemplateDir}css/colorpicker.css"  media="screen" />*}
	    {*<link rel="stylesheet" type="text/css" href="{$currentTemplateDir}css/clickareas.css"  media="screen" />*}
	    {*<link rel="stylesheet" type="text/css" href="{$currentTemplateDir}css/jtlshop2_admin_default.css" media="screen" />*}
	    {*<link rel="stylesheet" type="text/css" href="{$currentTemplateDir}css/jquery-ui-1.8.9.custom.css"  media="screen" />*}
	    {*<link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}lib/codemirror.css" />*}
	    {*<link type="text/css" rel="stylesheet" href="{$PFAD_CODEMIRROR}addon/display/fullscreen.css" />*}

	    <!--[if lte IE 8]>
	    <link rel="stylesheet" title="Standard" href="{$currentTemplateDir}css/lteIE8.css" type="text/css" media="all" />
	    <![endif]-->

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script>
            // Prevent auto-execution of scripts when no explicit dataType was provided (See gh-2432)
            jQuery.ajaxPrefilter( function( s ) {
                if ( s.crossDomain ) {
                    s.contents.script = false;
                }
            } );
        </script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
        <script src="//code.jquery.com/jquery-migrate-1.2.1.min.js"></script>

	    {$admin_js}

        {*<script type="text/javascript" src="{$currentTemplateDir}js/jquery.ui.datepicker-de.js"></script>*}
        {*<script type="text/javascript" src="{$currentTemplateDir}js/jquery-ui-timepicker-addon.js"></script>*}
        {*<script type="text/javascript" src="{$currentTemplateDir}js/jquery.tooltip.js"></script>*}

        {*<script type="text/javascript" src="{$currentTemplateDir}js/highcharts.min.js"></script>*}

        {*<script type="text/javascript" src="{$currentTemplateDir}js/clickareas.js"></script>*}
        {*<script type="text/javascript" src="{$currentTemplateDir}js/tab.js"></script>*}
        {*<script type="text/javascript" src="{$currentTemplateDir}js/colorpicker.js"></script>*}
        {*<script type="text/javascript" src="{$currentTemplateDir}js/global.js"></script>*}

	    {*<script type="text/javascript" src="{$PFAD_CODEMIRROR}lib/codemirror.js"></script>*}
	    {*<script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/css/css.js"></script>*}
	    {*<script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/javascript/javascript.js"></script>*}
	    {*<script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/xml/xml.js"></script>*}
	    {*<script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/php/php.js"></script>*}
	    {*<script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/htmlmixed/htmlmixed.js"></script>*}
	    {*<script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/smarty/smarty.js"></script>*}
	    {*<script type="text/javascript" src="{$PFAD_CODEMIRROR}mode/smartymixed/smartymixed.js"></script>*}
	    {*<script type="text/javascript" src="{$PFAD_CODEMIRROR}addon/display/fullscreen.js"></script>*}
        {*<script type="text/javascript" src="{$currentTemplateDir}js/codemirror_init.js"></script>*}
	    {if isset($xajax_javascript)}
            {$xajax_javascript}
	    {/if}
    </head>

    <body>

        <script type="text/javascript">
            {literal}
$(document).ready(function() {
	$('.help').each(function() {
		var id = $(this).attr('ref');
		var tooltip = $('<div></div>').text($(this).attr('title')).addClass('tooltip').attr('id', 'help' + id);
		$('body').append(tooltip);
		$(this).attr('title', '');
		$(this).bind('mouseenter', function(e){
			var offset = $(this).offset();
			$('#help' + id).css({
				left: offset.left - $('#help' + id).outerWidth() + $(this).outerWidth() + 5,
				top: offset.top - (($('#help' + id).outerHeight() - $(this).outerHeight()) / 2)
			}).fadeIn(200);
		}).bind('mouseleave', function(e){
			$('#help' + id).hide();
		});
	});
});
            {/literal}
        </script>

        {if $account}
            <div id="header">
                <div class="logo"></div>
                <div class="misc">
                    <fieldset>
                        <a class="button shop" href="{$URL_SHOP}" title="Zum Shop">&nbsp;</a>
                    {if permission('DASHBOARD_VIEW')}<a class="button dashboard" href="index.php">Dashboard</a>{/if}
                    <a class="button" href="logout.php">Abmelden</a>
                    {if permission('SETTINGS_SEARCH_VIEW')}
                        <form method="POST" action="einstellungen.php">
                            <input type="hidden" name="{$session_name}" value="{$session_id}" />
                            <input type="hidden" name="einstellungen_suchen" value="1" />
                            <input type="hidden" name="kSektion" value="{if isset($kEinstellungenSektion)}{$kEinstellungenSektion}{/if}" />
                            <input name="cSuche" type="text" value="" /><input type="submit" class="search_submit" value="{#confSearch#}" />
                        </form>
                    {/if}
                </fieldset>
            </div>
        </div>
        <div id="menu_wrapper">
            <div id="menu">
                <ul id="menu" class="topmenu">
                    {foreach name=linkobergruppen from=$oLinkOberGruppe_arr item=oLinkOberGruppe}
                        <li class="topmenu {if $smarty.foreach.linkobergruppen.first}topfirst{elseif $smarty.foreach.linkobergruppen.last}toplast{/if}"><a href="#" class="parent">{$oLinkOberGruppe->cName}</a>
                            <ul>
                                {foreach name=linkuntergruppen from=$oLinkOberGruppe->oLinkGruppe_arr item=oLinkGruppe}
                                    <li {if $smarty.foreach.linkuntergruppen.first}class="subfirst"{/if}><a href="#"><span>{$oLinkGruppe->cName}</span></a>
                                        {if $oLinkGruppe->oLink_arr|@count > 0}
                                            <ul>
                                                {foreach name=linkgruppenlinks from=$oLinkGruppe->oLink_arr item=oLink}
                                                    <li class="{if $smarty.foreach.linkgruppenlinks.first}subfirst {if !$oLink->cRecht|permission}noperm{/if}{/if}"><a href="{$oLink->cURL}">{$oLink->cLinkname}</a></li>
                                                {/foreach}
                                            </ul>
                                        {/if}
                                    </li>
                                {/foreach}
                                {foreach name=linkuntergruppenlinks from=$oLinkOberGruppe->oLink_arr item=oLink}
                                    <li class="{if $smarty.foreach.linkuntergruppenlinks.first}subfirst{/if} {if !$oLink->cRecht|permission}noperm{/if}"><a href="{$oLink->cURL}">{$oLink->cLinkname}</a></li>
                                {/foreach}
                            </ul>
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
        <div id="content_wrapper">
        {/if}
