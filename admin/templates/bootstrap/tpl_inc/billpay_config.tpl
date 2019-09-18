<div class="plain-list">
{if isset($oItem->cFehler) && $oItem->cFehler|strlen > 0}
    <div class="alert alert-danger">{$oItem->cFehler}</div>
{else}
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Kauf auf Rechnung</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>Status</dt>
                <dd>
                    {if $oItem->oRechnung->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Aktiv</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Inaktiv</span>
                    {/if}
                </dd>

                {if $oItem->oRechnung->bAktiv}
                    <dt>Mindestbestellwert</dt>
                    <dd>{$oItem->oRechnung->cValMin} &euro;</dd>
                    <dt>Maximaler Bestellwert</dt>
                    <dd>{$oItem->oRechnung->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Kauf auf Rechnung B2B</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>Status</dt>
                <dd>
                    {if $oItem->oRechnungB2B->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Aktiv</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Inaktiv</span>
                    {/if}
                </dd>

                {if $oItem->oRechnungB2B->bAktiv}
                    <dt>Mindestbestellwert</dt>
                    <dd>{$oItem->oRechnungB2B->cValMin} &euro;</dd>
                    <dt>Maximaler Bestellwert</dt>
                    <dd>{$oItem->oRechnungB2B->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Lastschriftverfahren</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>Status</dt>
                <dd>
                    {if $oItem->oLastschrift->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Aktiv</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Inaktiv</span>
                    {/if}
                </dd>

                {if $oItem->oLastschrift->bAktiv}
                    <dt>Mindestbestellwert</dt>
                    <dd>{$oItem->oLastschrift->cValMin} &euro;</dd>
                    <dt>Maximaler Bestellwert</dt>
                    <dd>{$oItem->oLastschrift->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Ratenzahlung</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>Status</dt>
                <dd>
                    {if $oItem->oRatenzahlung->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Aktiv</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Inaktiv</span>
                    {/if}
                </dd>

                {if $oItem->oRatenzahlung->bAktiv}
                    <dt>Mindestbestellwert</dt>
                    <dd>{$oItem->oRatenzahlung->cValMin} &euro;</dd>
                    <dt>Maximaler Bestellwert</dt>
                    <dd>{$oItem->oRatenzahlung->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>


    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Paylater</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>Status</dt>
                <dd>
                    {if $oItem->oPaylater->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Aktiv</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Inaktiv</span>
                    {/if}
                </dd>

                {if $oItem->oPaylater->bAktiv}
                    <dt>Mindestbestellwert</dt>
                    <dd>{$oItem->oPaylater->cValMin} &euro;</dd>
                    <dt>Maximaler Bestellwert</dt>
                    <dd>{$oItem->oPaylater->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Paylater B2B</h3></div>
        <div class="panel-body">
            <dl class="dl-horizontal">
                <dt>Status</dt>
                <dd>
                    {if $oItem->oPaylaterB2B->bAktiv}
                        <span class="label label-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Aktiv</span>
                    {else}
                        <span class="label label-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Inaktiv</span>
                    {/if}
                </dd>

                {if $oItem->oPaylaterB2B->bAktiv}
                    <dt>Mindestbestellwert</dt>
                    <dd>{$oItem->oPaylaterB2B->cValMin} &euro;</dd>
                    <dt>Maximaler Bestellwert</dt>
                    <dd>{$oItem->oPaylaterB2B->cValMax} &euro;</dd>
                {/if}
            </dl>
        </div>
    </div>


{/if}
</div>