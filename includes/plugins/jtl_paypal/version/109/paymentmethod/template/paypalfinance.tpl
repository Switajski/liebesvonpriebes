{if $error}
    <p class="alert alert-danger">{$error}</p>
    <a href="bestellvorgang.php?editZahlungsart=1" class="btn btn-primary btn-lg pull-right submit submit_once">
        {lang key="modifyPaymentOption" section="checkout"}
    </a>
{else}
    <div class="ppf-redirect-notice">
        <i class="fa fa-spinner fa-pulse"></i>
        <div class="header">Sie werden in K&uuml;rze weitergeleitet.</div>
        <div class="desc text-muted">Sollte keine Weiterleitung statt finden, klicken Sie bitte <a href="bestellvorgang.php?editZahlungsart=1">hier</a>.</div>
    </div>

    <style type="text/css">
        #content form input[type="submit"] { display: none }
    </style>

    <script type="text/javascript">
        // $(function() { $('.ppf-redirect-notice').closest('form').submit(); });
    </script>
{/if}