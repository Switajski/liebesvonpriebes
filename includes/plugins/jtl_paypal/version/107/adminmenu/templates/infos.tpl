<div class="container-fluid">
    {if isset($errorMessage) && $errorMessage|@count_characters > 0}
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-triangle"></i> {$errorMessage}
        </div>
    {/if}

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
            <button class="btn btn-basic btn-{if $type == 'basic'}{$class}{else}default{/if}" name="validate" value="basic">Basic</button>
            <button class="btn btn-express btn-{if $type == 'express'}{$class}{else}default{/if}" name="validate" value="express">Express</button>
            <button class="btn btn-plus btn-{if $type == 'plus'}{$class}{else}default{/if}" name="validate" value="plus">PLUS</button>
            <button class="btn btn-finance btn-{if $type == 'finance'}{$class}{else}default{/if}" name="validate" value="finance">Ratenzahlung</button>
        </div>
    </form>

    {if isset($results) && $results !== null}
        <br />
        <div id="paypal2-test-results">
            {if isset($results.status) && $results.status === 'success'}
                <div class="alert alert-success" role="alert"><i class="fa fa-check"></i> Zugangsdaten erfolgreich validiert.</div>
            {else}
                <div class="alert alert-danger" role="alert"><i class="fa fa-exclamation-circle"></i> Zugangsdaten fehlerhaft.{if isset($results.msg)}Fehlermeldung: {$results.msg}{/if}</div>
            {/if}
        </div>
    {/if}

    <h2>TLS 1.2 Unterstützung prüfen</h2>

    <p>
        Prüfen Sie, ob Ihr System eine mit TLS 1.2 verschlüsselte Verbindung über HTTP/1.1 zu PayPal aufbauen kann.
    </p>

    <form id="paypal-test-credentials" method="post" action="{$post_url}">
        <div class="btn-group" role="group">
            <button class="btn btn-default" name="security" value="basic">Jetzt prüfen</button>
            <a href="https://www.paypal-knowledge.com/infocenter/index?page=content&id=FAQ1913&expand=true&locale=de_DE" target="_blank" class="btn btn-default">Weitere Informationen</a>
        </div>
    </form>

    {if isset($tlsResponse)}
        <br />
        <div id="paypal2-security">
            {if empty($tlsResponse)}
                <div class="alert alert-success" role="alert"><i class="fa fa-check"></i> Verbindung wurde efolgreich hergestellt.</div>
            {else}
                <div class="alert alert-danger" role="alert">
                    <h4> Fehlerhaft - bitte setzen Sie sich mit Ihrem Hoster in Verbindung.</h4>
                    <p>{$tlsResponse}</p>
                </div>
            {/if}
        </div>
    {/if}

    <br />

    <p>
        <a href="http://jtl-url.de/paypaldocs" class="btn btn-primary" target="_blank"><i class="fa fa-file-pdf-o"></i> Integrationshandbuch zu diesem Plugin lesen</a>
    </p>
</div>