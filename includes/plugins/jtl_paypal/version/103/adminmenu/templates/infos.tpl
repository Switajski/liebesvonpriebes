<div class="container-fluid">

    <h2>Anmelden. Fertig. Starten.</h2>
    <blockquote>
        <p>
            Dieses Plugin bringt <a href="https://www.paypal.com/de/webapps/mpp/paypal-plus">PayPal PLUS</a>, <a href="https://www.paypal.com/de/webapps/mpp/express-checkout">PayPal Express</a> sowie PayPal Basis in Ihren Shop. <br>
        </p>
        <h3>PayPal PLUS</h3>
        PayPal PLUS lädt im normalen Bestellvorgang im Schritt Zahlungsart eine eigene "Payment-Wall". In der Payment-Wall können Ihre Kunden mit 4 PayPal-Zahlungsarten (PayPal, Kreditkarte, Lastschrift, Rechnung) und bis zu 5 weitere Zahlungsarten bezahlen. 
        <h3>PayPal Express</h3>
        <p>
            Ermöglichen auch Sie Ihren Kunden die bequeme Abkürzung auf dem Bezahlweg: Nur ein Klick auf den PayPal Express-Button direkt neben dem Zur-Kasse-Button - schon sind Ihre Kunden auf der PayPal-Bezahlseite.
        </p>
        <h3>PayPal Basis</h3>
        <p> 
            Ihre Kunden gehen wie gewohnt zur Kasse und können als Bezahlmethode PayPal wählen. Genau wie bei PayPal Express werden bei PayPal Basis alle relevanten Kundendaten Über eine API-Schnittstelle übermittelt.
        </p> 
    </blockquote>

    {if isset($errorMessage) && $errorMessage|@count_characters > 0}
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-triangle"></i> {$errorMessage}
    </div>
    {/if}

    {*
    <a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" class="btn btn-primary" target="_blank">PayPal freischalten</a>
    <a href="https://www.sandbox.paypal.com/de/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" class="btn btn-default" target="_blank">Sandbox</a>
    <br /><br />
    *}
    <p>
        <a href="http://jtl-url.de/paypaldocs" class="btn btn-primary" target="_blank"><i class="fa fa-file-pdf-o"></i> Integrationshandbuch zu diesem Plugin lesen</a>
    </p>
    
    <h2>Zugangsdaten für PayPal Express / Basis abrufen</h2>
    <p>
        <a href="https://www.paypal.com/us/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" class="btn btn-primary" target="_blank">Live-Zugangsdaten</a>
        <a href="https://www.sandbox.paypal.com/de/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true" class="btn btn-default" target="_blank">Sandbox-Zugangsdaten</a>
    </p>
    
    <h2>Konfiguration validieren</h2>

    <p>
        Durch Klick auf einen der nachfolgenden Buttons wird ein Test-Aufruf mit den von Ihnen hinterlegten Zugangsdaten an PayPal-Server abgesetzt.
    </p>

    {assign var="type" value=""}
    {assign var="class" value="default"}
    {if isset($results) && $results !== null}
        {assign var="class" value=$results.status}
        {assign var="type" value=$results.type}
    {/if}
    <form id="paypal-test-credentials" method="post" action="{$post_url}">
        <div class="btn-group" role="group">
            <button class="btn btn-{if $type == 'basic'}{$class}{else}default{/if}" name="validate" value="basic">Paypal Basic</button>
            <button class="btn btn-{if $type == 'express'}{$class}{else}default{/if}" name="validate" value="express">Paypal Express</button>
            <button class="btn btn-{if $type == 'plus'}{$class}{else}default{/if}" name="validate" value="plus">Paypal PLUS</button>
        </div>
    </form>

    {if isset($results) && $results !== null}
        <br /><br />
        <div id="paypal2-test-results">
            {if isset($results.status) && $results.status === 'success'}
                <div class="alert alert-success" role="alert"><i class="fa fa-check"></i> Zugangsdaten erfolgreich validiert.</div>
            {else}
                <div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-circle"></i> Zugangsdaten fehlerhaft. Fehlermeldung: {$results.msg}</div>
            {/if}
        </div>
    {/if}

</div>