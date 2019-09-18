<div class="container-fluid">
    {if isset($errorMessage) && $errorMessage|@count_characters > 0}
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-triangle"></i> {$errorMessage}
        </div>
    {/if}

    <h2>Konfiguration</h2>

    <table class="table" id="paypal-test-credentials">
        <thead>
            <th>Zahlungsart</th>
            <th class="text-center">Modus</th>
            <th class="text-center">Zugangsdaten</th>
            <th class="text-center">Verkn&uuml;pft<br><small class="text-muted">Mit Versandart</small></th>
        </thead>
        <tbody>
            <tr class="basic">
                <td>Basic</td>
                <td class="payment-modus text-center"><i class="fa fa-spinner fa-spin"></i></td>
                <td class="payment-state text-center">
                    <i class="fa fa-spinner fa-spin"></i>
                </td>
                <td class="payment-linked text-center"><i class="fa fa-spinner fa-spin"></i></td>
            </tr>
            <tr class="express">
                <td>Express</td>
                <td class="payment-modus text-center"><i class="fa fa-spinner fa-spin"></i></td>
                <td class="payment-state text-center">
                    <i class="fa fa-spinner fa-spin"></i>
                </td>
                <td class="payment-linked text-center"><i class="fa fa-spinner fa-spin"></i></td>
            </tr>
            <tr class="plus">
                <td>PLUS</td>
                <td class="payment-modus text-center"><i class="fa fa-spinner fa-spin"></i></td>
                <td class="payment-state text-center"><i class="fa fa-spinner fa-spin"></i></td>
                <td class="payment-linked text-center"><i class="fa fa-spinner fa-spin"></i></td>
            </tr>
            <tr class="finance">
                <td>Ratenzahlung</td>
                <td class="payment-modus text-center"><i class="fa fa-spinner fa-spin"></i></td>
                <td class="payment-state text-center"><i class="fa fa-spinner fa-spin"></i></td>
                <td class="payment-linked text-center"><i class="fa fa-spinner fa-spin"></i></td>
            </tr>
        </tbody>
    </table>

    <h2>TLS 1.2 Unterst&uuml;tzung pr&uuml;fen</h2>

    <p>
        Pr&uuml;fen Sie, ob Ihr System eine mit TLS 1.2 verschl&uuml;sselte Verbindung &uuml;ber HTTP/1.1 zu PayPal aufbauen kann.
    </p>

    <form id="paypal-test-credentials" method="post" action="{$post_url}">
        <div class="btn-group" role="group">
            <button class="btn btn-default" name="security" value="basic">Jetzt pr&uuml;fen</button>
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

<script type="text/javascript">
    var url = '{$post_url}';
    var payments = ['basic', 'express', 'plus', 'finance'];

    $(payments).each(function(i, item) {
        check_payment(item);
    });

    function check_payment(type) {
        $.ajax({
            dataType: "json",
            url: url+'&validate=' + type,
            success: function(data) {
                var error = data.msg || 'Ung&uuml;ltig';

                var label_type = data['status'] == 'success' ? 'G&uuml;ltig' : error;
                var class_state = data['status'] == 'success' ? 'success' : 'danger';
                var state = '<small class="label label-'+class_state+'">'+label_type+'</small>';

                var label_linked = data['linked'] ? 'Ja' : 'Nein';
                var class_linked = data['linked'] ? 'success' : 'danger';
                var linked = '<small class="label label-'+class_linked+'">'+label_linked+'</small>';

                var modus = '<small class="label label-info">'+data['modus'].toUpperCase()+'</small>';

                $('tr.' + data['type'] + ' td.payment-state').html(state);
                $('tr.' + data['type'] + ' td.payment-linked').html(linked); 
                $('tr.' + data['type'] + ' td.payment-modus').html(modus);
            }
        });
    }
</script>