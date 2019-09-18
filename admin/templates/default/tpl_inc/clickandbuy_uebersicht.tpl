{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: clickandbuy_uebersicht.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehmer@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}
{include file="tpl_inc/seite_header.tpl" cTitel=#cap# cBeschreibung="ClickandBuy managed f&uuml;r Sie die Abrechnung Ihrer Angebote via Lastschriftverfahren, &Uuml;berweisung, Giropay, Sofort&uuml;berweisung, Kreditkarten und 50 lokalen und nationalen Bezahlverfahren weltweit."}
    
{if isset($hinweis) && $hinweis|count_characters > 0}			
    <p class="box_success">{$hinweis}</p>
{/if}
{if isset($fehler) && $fehler|count_characters > 0}			
    <p class="box_error">{$fehler}</p>
{/if}
    
    <div id="cap">
        
        <div class="logo">
            <img src="gfx/ClickandBuy/clickandbuy2.png" width="180" height="112" alt="ClickandBuy" title="ClickandBuy bietet Ihnen weltweit sicheres und einfaches Bezahlen im Internet. Ob per Abbuchung von Ihrem Bankkonto, Kreditkarte oder ClickandBuy Guthaben: W&auml;hlen Sie einfach Ihr bevorzugtes Zahlungsmittel aus." />
        </div>
        
        <div class="special">
            <strong>Mehr Geld verdienen und mehr Umsatz machen!</strong><br />
            Sicher, schnell und einfach Zahlungen empfangen!<br />
            <ul class="actions">
               <li class="moreinfo"><a href="http://www.clickandbuy.com/DE_de/agb.html">Mehr Infos zu ClickandBuy!</a></li> 
               <li class="register"><a href="https://eu.clickandbuy.com/cgi-bin/register.pl?_show=merchantnew&lang=de&Nation=DE&00N200000014o7g=JTL-Shop">ClickandBuy-Registrierung!</a></li>
               <li class="desc">Nach der Registrierung werden Ihre Zugangs- und Konfigurationsdaten an Ihre E-Mail Adresse verschickt.</li>
            </ul>
        </div>
        
        <br />
        
        <div class="beschreibung">
            <br /><br />
            F&uuml;r weitere Informationen oder R&uuml;ckfragen zu ClickandBuy kontaktieren Sie uns unter:<br />
            Tel.:  +49 221 177 38 700<br />
            oder via email <a href="mailto:sales@clickandbuy.com">sales@clickandbuy.com</a>
            <br /><br />

        </div>                
        
    </div>
</div>