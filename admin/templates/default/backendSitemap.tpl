{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section="backendSitemap"}
<div id="page">
        <div id="content">
        <div id="welcome" class="post">
			<h2 class="title"><span>{#backendSitemapTitle#}</span></h2>
		</div>
        <div class="container">
       <div id="sitemapBackendContent">
        {foreach name=linkgruppen from=$Linkgruppen item=linkgruppe}
			<ul class="linkgruppeSitemap">
				<li class="linkgruppeNameSitemap"><h4>{$linkgruppe->cName}</h4>
                {foreach name=links from=$linkgruppe->Links item=link}
				<li class="linkSitemap"><a href="{$link->cURL}?{$SID}">{$link->cLinkname}</a></li>
                {if $link->cLinkname=="Einstellungen"}
                {foreach name=einst from=$Sektionen item=Sektion}<li class="linkSektionSitemap"><a href="einstellungen.php?{$SID}&kSektion={$Sektion->kEinstellungenSektion}">{$Sektion->cName}</a></li>{/foreach}
                {/if}
                {if $link->cLinkname=="Zahlungsarten"}
                {foreach name=zahlungsarten from=$zahlungsarten item=zahlungsart}<li class="linkSektionSitemap"><a href="zahlungsarten.php?kZahlungsart={$zahlungsart->kZahlungsart}&{$SID}">{$zahlungsart->cName}</a></li>{/foreach}
                {/if}
                {/foreach}
			</ul>
          {/foreach}
          <div class="clearer"></div>
		</div>
			</div>

{include file='tpl_inc/footer.tpl'}