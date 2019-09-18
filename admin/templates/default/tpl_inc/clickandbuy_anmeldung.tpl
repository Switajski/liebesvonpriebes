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
{include file="tpl_inc/seite_header.tpl" cTitel=#cap#}
<div id="content">
   
    {if isset($hinweis) && $hinweis|count_characters > 0}			
        <p class="box_success">{$hinweis}</p>
    {/if}
    {if isset($fehler) && $fehler|count_characters > 0}			
        <p class="box_error">{$fehler}</p>
    {/if}

    <div id="cap">
        
        <div class="special2">
            <h2>Konditionen und Anmeldung zur Nutzung des Zahlungssystems ClickandBuy:</h2><br />
            Nach Abschluss der Registrierung k&ouml;nnen Sie Ihre Verk&auml;ufe &uuml;ber Ihren <strong>JTL-Shop</strong> mit ClickandBuy abrechnen lassen. Klicken Sie unten folgend auf den Button &quot;ClickandBuy-Registrierung&quot;.<br /><br />
            F&uuml;r die Abrechnung der Forderungen des Anbieters gegen&uuml;ber Nutzern, die &uuml;ber ClickandBuy abgerechnet werden, hat der Anbieter folgende Provision zu zahlen:<br /><br />
            <p style="text-align: center; font-weight: bold;">1,9 % + 0,35 &euro; pro Transaktion</p>
            <p style="text-align: center; font-weight: bold;">Keine Anmeldegeb&uuml;hr, Keine Grundgeb&uuml;hr, Keine Fixkosten!!!</p>
        </div>
        
        <br />
        
        <div class="beschreibung">
            ClickandBuy berechnet f&uuml;r Stornierungen durch das Call Center pauschal 6 &euro; pro Fall.<br /> 
            F&uuml;r die Ausbuchung eines Nutzers, von dem die verursachten Betr&auml;ge nicht beigebracht werden k&ouml;nnen, werden 6 &euro; berechnet.<br /> 
            ClickandBuy erh&auml;lt zur Deckung der Service-/Support- und Registrierungs- bzw. Call Center-Kosten pro registriertem Nutzer, der kostenpflichtige Angebote des Anbieters nutzt, 0,05 &euro; pro Jahr.<br />
            Die Provision f&auml;llt als aufwandsbezogene Geb&uuml;hr bei jeder Transaktion mit Nutzern von ClickandBuy an und wird von etwaigen Beanstandungen, Einwendungen oder Einreden der Nutzer im Verh&auml;ltnis zum Anbieter, einer Nichteinbringlichkeit der Forderung sowie etwaigen R&uuml;ckerstattungen seitens ClickandBuy an den Nutzer nicht ber&uuml;hrt.<br />
            Alle vorstehend genannten Verg&uuml;tungen (Provisionsanteile von ClickandBuy inkl. Transaktionsgeb&uuml;hren sowie die aufwandsbezogenen Verg&uuml;tungen) verstehen sich netto zuz&uuml;glich der gesetzlichen Mehrwertsteuer.
            <br /><br />
            Nach der Freischaltung bei ClickandBuy werden durch <strong>JTL-Shop</strong> die Einstellungen von <strong>JTL-Shop</strong> in der Shopsystem-Software. Dann k&ouml;nnen Sie Ihre Angebote in Ihrem Shop auch durch ClickandBuy bezahlen lassen!
            <br /><br />
            {*<h2><a href="clickandbuy.php?register=1{$session_name}={$session_id}" target="_blank">ClickandBuy-Registrierung</a></h2>*}
            <h2><a href="https://eu.clickandbuy.com/cgi-bin/register.pl?_show=merchantnew&lang=de&Nation=DE&00N200000014o7g=JTL-Shop " target="_blank">ClickandBuy-Registrierung</a></h2>
        </div>
        
        <br />
        
        <a href="clickandbuy.php?{$session_name}={$session_id}">{#capBack#}</a>
        
    </div>
</div>