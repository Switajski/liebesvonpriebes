{capture name="testfailed"}
    <a class="label label-warning" href="systemcheck.php" title="Mehr Informationen im Systemcheck">
        <i class="fa fa-exclamation-triangle" aria-hidden="true"></i><span class="sr-only">Warnung</span>
    </a>
{/capture}
{capture name="testpassed"}
    <span class="label label-success">
        <i class="fa fa-check" aria-hidden="true"></i><span class="sr-only">OK</span>
    </span>
{/capture}


<div class="widget-custom-data">
    <table class="table table-condensed table-hover table-blank">
        <tbody>
            <tr>
                    <td>Maximale PHP Ausf&uuml;hrungszeit</td>
                    <td>{$maxExecutionTime}</td>
                    <td class="text-right">
                        {if $bMaxExecutionTime} 
                            {$smarty.capture.testpassed}
                        {else}
                            {$smarty.capture.testfailed}
                        {/if}
                    </td>
            </tr>
            <tr>
                    <td>PHP-Speicherlimit</td>
                    <td>{$memoryLimit}</td>
                    <td class="text-right">
                        {if $bMemoryLimit} 
                            {$smarty.capture.testpassed}
                        {else}
                            {$smarty.capture.testfailed}
                        {/if}
                    </td>
            </tr>
            <tr>
                    <td>Maximale PHP &Uuml;bertragungsgr&ouml;&szlig;e (FILE)</td>
                    <td>{$maxFilesize}</td>
                    <td class="text-right">
                        {if $bMaxFilesize} 
                            {$smarty.capture.testpassed}
                        {else}
                            {$smarty.capture.testfailed}
                        {/if}
                    </td>
            </tr>
            <tr>
                    <td>Maximale PHP &Uuml;bertragungsgr&ouml;&szlig;e (POST)</td>
                    <td>{$postMaxSize}</td>
                    <td class="text-right">
                        {if $bPostMaxSize} 
                            {$smarty.capture.testpassed}
                        {else}
                            {$smarty.capture.testfailed}
                        {/if}
                    </td>
            </tr>
            <tr>
                    <td>allow_url_fopen aktiviert</td>
                    <td>{if $bAllowUrlFopen}ja{else}nein{/if}</td>
                    <td class="text-right">
                        {if $bAllowUrlFopen} 
                            {$smarty.capture.testpassed}
                        {else}
                            {$smarty.capture.testfailed}
                        {/if}
                    </td>
            </tr>
        </tbody>
    </table>
</div>