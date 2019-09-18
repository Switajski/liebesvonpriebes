<div class="ppf-details">
    <div class="info">
        <p class="title">Zahlen Sie bequem und einfach in monatlichen Raten</p>
        <p class="desc">
            Ihre Ratenzahlung und den passenden Finzanzierungsplan k&ouml;nnen Sie im Rahmen des Bestellprozesses ausw&auml;hlen.<br/>
            Ihre Anfrage erfolgt komplett online und wird in wenigen Schritten hier im Shop abgeschlossen.
        </p>
        <p class="loan">Nettodarlehensbetrag: <span class="price">{gibPreisStringLocalized($transactionAmount->getValue())}</span></p>
    </div>

    {$col = 12}
    {if count($financingOptions) > 1}
        {$col = 6}
    {/if}

    <div class="row row-eq-height">
        {foreach $financingOptions as $fo}
            <div class="col-md-{$col}">
                <div class="table-responsive">
                    {include file="{$plugin->cFrontendPfad}template/presentment-table.tpl" financingOption=$fo}
                </div>
            </div>
        {/foreach}
    </div>
</div>