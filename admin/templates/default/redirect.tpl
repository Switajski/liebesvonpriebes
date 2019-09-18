{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{include file='tpl_inc/header.tpl'} 
{include file="tpl_inc/seite_header.tpl" cTitel="Weiterleitungen" cBeschreibung="Legen Sie fest welche nicht gefundenen Webseiten (404) weitergeleitet werden sollen" cDokuURL=""}

<script type="text/javascript" src="templates/default/js/checkAllMSG.js"></script>

<script>
    {literal}
$(document).ready(function() {
	init_simple_search(function(type, res) {
		$('input.simple_search').val(res.cUrl)
	});

    $('.showEditor').click(function() {
        $('input.cToUrl').removeClass('simple_search');
        $(this).parent().find('input.cToUrl').addClass('simple_search');
        show_simple_search($(this).attr('id'));
        return false;
	});

	$('button.expandable').click(function() {
		var rel = $(this).attr('rel');
		$('tr.expandable[rel="'+rel+'"]').toggle();
	});
	
	$(".import").click(function() {
		if ($(".csvimport").css("display") == "none")
		    $(".csvimport").fadeIn();
		else
			$(".csvimport").fadeOut();
	});
});
    {/literal}
</script>

<div id="content">
    {if $cFehler|count_characters > 0}
        <div class="box_error">{$cFehler}</div>
    {/if}
    {if $cHinweis|count_characters > 0}
        <div class="box_success">{$cHinweis}</div>
    {/if}

    <div class="container">
        {if $oRedirect_arr|@count > 0}
            <!-- Einstellungen -->
            <form action="redirect.php" method="post">
                <div id="settings">
                    <div class="category">Einstellungen</div>
                    <div class="item">
                        <div class="name">
                            <label for="cSortierFeld"> Sortierfeld</span></label>
                        </div>
                        <div class="for">
                            <select name="cSortierFeld">
                                <option value="cFromUrl" {if $cSortierFeld == 'cFromUrl'} selected="selected"{/if}>Url</option>
                                <option value="cToUrl" {if $cSortierFeld == 'cToUrl'} selected="selected" {/if}>Wird weitergeleitet nach</option>
                                <option value="nCount" {if $cSortierFeld == 'nCount'} selected="selected"{/if}>Aufrufe</option>
                                {*<option value="dFirst" {if $cSortierFeld == 'dFirst'} selected="selected" {/if}>Erster Aufruf</option>*}
                                {*<option value="dLast" {if $cSortierFeld == 'dLast'} selected="selected" {/if}>Letzter Aufruf</option>*}
                            </select>
                        </div>
                    </div>
                    <div class="item">
                        <div class="name">
                            <label for="cSortierung"> Sortierung</label>
                        </div>
                        <div class="for">
                            <select name="cSortierung">
                                <option value="DESC" {if $cSortierung == 'DESC'} selected="selected" {/if} >Absteigend</option>
                                <option value="ASC" {if $cSortierung == 'ASC'}selected="selected"{/if}>Aufsteigend</option>
                            </select>
                        </div>
                    </div>
                    <div class="item">
                        <div class="name">
                            <label for="nAnzahlProSeite"> Eintr&auml;ge pro Seite</span></label>
                        </div>
                        <div class="for">
                            <input type="text" value="{$nAnzahlProSeite}" name="nAnzahlProSeite" />
                        </div>
                    </div>
                    <div class="item">
                        <div class="name">
                            <label for="bUmgeleiteteUrls"> Filter</span></label>
                        </div>
                        <div class="for">
                            <select name="bUmgeleiteteUrls">
                                <option value="0"  {if $bUmgeleiteteUrls == '0'} selected="selected" {/if}>alle</option>
                                <option value="1" {if $bUmgeleiteteUrls == '1'} selected="selected" {/if}>nur umgeleitet</option>
                                <option value="2" {if $bUmgeleiteteUrls == '2'} selected="selected" {/if}>nur ohne Umleitung</option>
                            </select>
                        </div>
                    </div>
                    <div class="item">
                        <div class="name">
                            <label for="cSuchbegriff"> Suchbegriff</span></label>
                        </div>
                        <div class="for">
                            <input type="text" value="{$cSuchbegriff}" name="cSuchbegriff" />
                        </div>
                    </div>
                    <div class="save_wrapper">
                        <input type="submit" name="config" value="Aktualisieren" class="button orange">
                    </div>
                </div>
                <br> 
            </form>
                <!--  Pagination Oben -->
                {if $oBlaetterNavi->nAktiv == 1}
                    <div class="block clearall">
                        <div class="pages tleft">
                            <span class="pageinfo">Eintrag: <strong>{$oBlaetterNavi->nVon}</strong>
                                - {$oBlaetterNavi->nBis} von {$oBlaetterNavi->nAnzahl}
                            </span> 
                            <a class="back" href="redirect.php?s1={$oBlaetterNavi->nVoherige}&{$cParams}">&laquo;</a> 
                            {if $oBlaetterNavi->nAnfang != 0}
                                <a href="redirect.php?s1={$oBlaetterNavi->nAnfang}&{$cParams}">{$oBlaetterNavi->nAnfang}</a> ... 
                            {/if} 
                            {foreach name=blaetternavi from=$oBlaetterNavi->nBlaetterAnzahl_arr item=Blatt} 
                                <a class="page {if $oBlaetterNavi->nAktuelleSeite == $Blatt}active{/if}" href="redirect.php?s1={$Blatt}&{$cParams}">{$Blatt}</a>
                            {/foreach} 
                            {if $oBlaetterNavi->nEnde != 0} 
                                ... <a class="page" href="redirect.php?s1={$oBlaetterNavi->nEnde}&{$cParams}">{$oBlaetterNavi->nEnde}</a>
                            {/if}
                            <a class="next" href="redirect.php?s1={$oBlaetterNavi->nNaechste}&{$cParams}">&raquo;</a>
                        </div>
                    </div>
                {/if}
                
            <button class="button blue import">CSV Import durchf&uuml;hren</button>
            <div class="csvimport" style="display: none;">
                <form method="post" enctype="multipart/form-data">
                    <input name="a" type="hidden" value="csvimport" />
                    
                    <br />                    
                    <table>
                        <tbody>
                            <tr>
                                <td>Datei:</td>
                                <td><input name="cFile" type="file" /></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><input name="submit" type="submit" class="btn blue" value="Importieren" /></td>
                            </tr>                            
                        </tbody>
                    </table>
                </form>
            </div>

            <form method="post">
                <input name="a" type="hidden" value="new" />
                
                <div class="container">
                    <div id="settings">
                        <div class="category">Neue Weiterleitung</div>
                        <div class="item">
                            <strong>Quell Url:</strong> <input name="cSource" type="text" style="width: 25%;" placeholder="Quell Url" value="{if isset($cPost_arr.cSource)}{$cPost_arr.cSource}{/if}" />
                            <strong>Ziel Url:</strong> <input name="cDestination" type="text" class="cToUrl" style="width: 25%;" placeholder="Ziel Url" value="{if isset($cPost_arr.cDestination)}{$cPost_arr.cDestination}{/if}" />
                            <a href="#" class="button edit showEditor" id="article">Artikel</a>
                            <a href="#" class="button edit showEditor" id="manufacturer">Hersteller</a>
                            <a href="#" class="button edit showEditor" id="categories">Kategorien</a>
                            <input name="submit" type="submit" value="Speichern" class="button blue" />
                        </div>
                    </div>
                </div>
            </form>

            <!-- Inhalt -->
            <form action="redirect.php?s1={$oBlaetterNavi->nAktuelleSeite}" method="post">        
                <div class="container">
                    <table class="list">
                        <thead>
                        <th class="tcenter" style="width:24px"></th>
                        <th class="tleft" style="width:33%;">Url</th>
                        <th class="tleft">Wird weitergeleitet nach</th>
                        <th class="tcenter">Aufrufe</th>
                        {*<th class="tcenter">Erster Aufruf</th>*}
                        {*<th class="tcenter">Letzter Aufruf</th>*}
                        <th class="tcenter">Optionen</th>
                        </thead>
                        <tbody>
                            {foreach from=$oRedirect_arr item="oRedirect"}
                                <tr>
                                    <td class="tcenter"><input type="checkbox" name="redirect[]" value="{$oRedirect->kRedirect}" /></td>
                                    <td class="tleft"><a href="{$oRedirect->cFromUrl}" target="_blank">{$oRedirect->cFromUrl|truncate:52:"..."}</a></td>
                                    <td class="tleft">
                                        <input type="text" class="cToUrl" value="{$oRedirect->cToUrl}" style="width:58%;" name="url[{$oRedirect->kRedirect}]" />
                                        <a href="#" class="button edit showEditor" id="article">Artikel</a>
                                        <a href="#" class="button edit showEditor" id="manufacturer">Hersteller</a>
                                        <a href="#" class="button edit showEditor" id="categories">Kategorien</a>
                                    </td>
                                    <td class="tcenter">{$oRedirect->oRedirectReferer_arr|@count}</td>
                                    {*<td class="tcenter">$oRedirect->dFirst|date_format:"%d.%m.%Y %H:%M:%S"</td>*}
                                    {*<td class="tcenter">$oRedirect->dLast|date_format:"%d.%m.%Y %H:%M:%S"</td>*}
                                    <td class="tcenter">
                                        <button type="button" class="button down expandable" rel="{$oRedirect->kRedirect}">Details</button>
                                    </td>
                                </tr>

                                {if $oRedirect->oRedirectReferer_arr|@count > 0}
                                    <tr class="hidden expandable" rel="{$oRedirect->kRedirect}">
                                        <td></td>
                                        <td colspan="5">
                                            <table class="innertable">
                                                <thead>
                                                    <tr>
                                                        <th class="tleft">Verweis</th>
                                                        <th class="tcenter" width="200">Datum</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {foreach from=$oRedirect->oRedirectReferer_arr item="oRedirectReferer"}
                                                        <tr>
                                                            <td class="tleft">
                                                                {if $oRedirectReferer->kBesucherBot > 0}
                                                                    {if $oRedirectReferer->cBesucherBotName|@count_characters > 0}
                                                                        {$oRedirectReferer->cBesucherBotName}
                                                                    {else}
                                                                        {$oRedirectReferer->cBesucherBotAgent}
                                                                    {/if}
                                                                    (Bot)
                                                                {elseif $oRedirectReferer->cRefererUrl|@count_characters > 0}
                                                                    <a href="{$oRedirectReferer->cRefererUrl}" target="_blank">{$oRedirectReferer->cRefererUrl}</a>
                                                                {else}
                                                                    <i>Direkteinstieg</i>
                                                                {/if}
                                                            </td>
                                                            <td class="tcenter">
                                                                {$oRedirectReferer->dDate|date_format:"%d.%m.%Y %H:%M:%S"}
                                                            </td>
                                                        </tr>
                                                    {/foreach}
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                {/if}
                            {/foreach}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5"><label for="ALLMSGS"><input name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);" /> Alle ausw&auml;hlen</label></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="save_wrapper" style="position:relative">
                    <div style="position:absolute;top:10px;left:0">
                        <input type="submit" name="delete" value="Auswahl l&ouml;schen" class="button blue" />
                        <input type="submit" name="delete_all" value="Alle ohne Weiterleitung l&ouml;schen" class="button blue" />
                    </div>
                    <input type="submit" value="Speichern" class="button orange" />
                </div>
            </form>

            {include file="tpl_inc/single_search_browser.tpl"}

            <!--  Pagination Unten -->
            {if $oBlaetterNavi->nAktiv == 1}
                <div class="block clearall">
                    <div class="pages tleft">
                        <span class="pageinfo">Eintrag: <strong>{$oBlaetterNavi->nVon}</strong> - {$oBlaetterNavi->nBis} von {$oBlaetterNavi->nAnzahl}</span>
                        <a class="back"href="redirect.php?s1={$oBlaetterNavi->nVoherige}">&laquo;</a> 
                        {if $oBlaetterNavi->nAnfang != 0}
                            <a href="redirect.php?s1={$oBlaetterNavi->nAnfang}&{$cParams}">{$oBlaetterNavi->nAnfang}</a>... 
                        {/if} 
                        {foreach name=blaetternavi from=$oBlaetterNavi->nBlaetterAnzahl_arr item=Blatt} 
                            <a class="page {if $oBlaetterNavi->nAktuelleSeite == $Blatt}active{/if}" href="redirect.php?s1={$Blatt}&{$cParams}">{$Blatt}</a> 
                        {/foreach}
                        {if
						$oBlaetterNavi->nEnde != 0} ... <a class="page" href="redirect.php?s1={$oBlaetterNavi->nEnde}&{$cParams}">{$oBlaetterNavi->nEnde}</a>
                    {/if} 
                    <a class="next" href="redirect.php?s1={$oBlaetterNavi->nNaechste}&{$cParams}">&raquo;</a>
                </div>
            </div>
        {/if}
        {else}
            <button class="button blue import">CSV Import durchf&uuml;hren</button>
            <div class="csvimport" style="display: none;">
                <form method="post" enctype="multipart/form-data">
                    <input name="a" type="hidden" value="csvimport" />
                    
                    <br />                    
                    <table>
                        <tbody>
                            <tr>
                                <td>Datei:</td>
                                <td><input name="cFile" type="file" /></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td><input name="submit" type="submit" class="btn blue" value="Importieren" /></td>
                            </tr>                            
                        </tbody>
                    </table>
                </form>
            </div>
        
            <form method="post">
                <input name="a" type="hidden" value="new" />
                
                <div class="container">
                    <div id="settings">
                        <div class="category">Neue Weiterleitung</div>
                        <div class="item">
                            <strong>Quell Url:</strong> <input name="cSource" type="text" style="width: 25%;" placeholder="Quell Url" value="{$cPost_arr.cSource}" />
                            <strong>Ziel Url:</strong> <input name="cDestination" type="text" class="cToUrl" style="width: 25%;" placeholder="Ziel Url" value="{$cPost_arr.cDestination}" />
                            <a href="#" class="button edit showEditor" id="article">Artikel</a>
                            <a href="#" class="button edit showEditor" id="manufacturer">Hersteller</a>
                            <a href="#" class="button edit showEditor" id="categories">Kategorien</a>
                            <input name="submit" type="submit" value="Speichern" class="button blue" />
                        </div>
                    </div>
                </div>
            </form>
            <p class="box_info">
                Zurzeit liegen keine Daten vor.
            </p>
            
            {include file="tpl_inc/single_search_browser.tpl"}
            {/if}
            </div>
        </div>

        {include file='tpl_inc/footer.tpl'}
