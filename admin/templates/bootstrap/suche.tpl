{strip}
<div class="grid">
    {if isset($settings)}
        {foreach $settings as $setting}
            <div class="grid-item">
                <h2>{$setting->cName} <small>{$setting->cSektionsPfad}</small></h2>
                <ul>
                    {foreach $setting->oEinstellung_arr as $s}
                        <li>
                            <a href="einstellungen.php?cSuche={$s->kEinstellungenConf}&einstellungen_suchen=1" class="value">
                                <p>{$s->cName} (Einstellungsnr.: {$s->kEinstellungenConf})</p>
                                <small>{$s->cBeschreibung}</small>
                            </a>
                        </li>
                    {/foreach}
                </ul>
            </div>
        {/foreach}
    {elseif isset($shippings)}
        <div class="grid-item">
            <h2><a href="versandarten.php" class="value">Versandartenübersicht</a></h2>
            <ul>
                {foreach $shippings as $shipping}
                    <li>
                        <form method="post" action="versandarten.php">
                            {$jtl_token}
                            <input type="hidden" name="edit" value="{$shipping->kVersandart}">
                            <button type="submit" class="btn btn-link">{$shipping->cName}</button>
                        </form>
                    </li>
                {/foreach}
            </ul>
        </div>
    {elseif isset($paymentMethods)}
        <div class="grid-item">
            <h2><a href="zahlungsarten.php" class="value">Zahlungsartenübersicht</a></h2>
            <ul>
                {foreach $paymentMethods as $paymentMethod}
                    <li>
                        <a href="zahlungsarten.php?kZahlungsart={$paymentMethod->kZahlungsart}&token={$smarty.session.jtl_token}" class="value">
                            <p>{$paymentMethod->cName}</p>
                        </a>
                    </li>
                {/foreach}
            </ul>
        </div>
    {/if}
</div>
{/strip}