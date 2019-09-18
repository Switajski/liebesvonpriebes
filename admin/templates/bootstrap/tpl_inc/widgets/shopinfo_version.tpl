{if $oSubscription || $oVersion}
    <table class="table table-condensed table-hover table-blank">
        <tbody>
            {if $oSubscription}
                <tr>
                    <td width="50%">Subscription g&uuml;ltig bis</td>
                    <td width="50%" id="subscription">
                        {if $oSubscription->nDayDiff < 0}
                            <a href="https://jtl-url.de/subscription" target="_blank">Abgelaufen</a>
                        {else}
                            {$oSubscription->dDownloadBis_DE}
                        {/if}
                    </td>
                </tr>
            {/if}
            {if $oVersion}
                <tr>
                    <td width="50%"></td>
                    <td width="50%" id="version">
                        {if $bUpdateAvailable}
                            <span class="label label-info">Version {$strLatestVersion} {if $oVersion->build > 0}(Build: {$oVersion->build}){/if} verfügbar.</span>
                        {else}
                            <span class="label label-success">Du benutzt die aktuelle Version von JTL-Shop</span>
                        {/if}
                    </td>
                </tr>
            {/if}
        </tbody>
    </table>
{/if}