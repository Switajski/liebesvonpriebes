<div class="widget-custom-data">
    <table class="table table-condensed table-hover table-blank">
        <tbody>
            <tr>
                <td width="50%">Shopversion</td>
                <td width="50%" id="current_shop_version">{$strFileVersion} {if $strMinorVersion != '0'}(Build: {$strMinorVersion}){/if}</td>
            </tr>
            <tr>
                <td width="50%">Templateversion</td>
                <td width="50%" id="current_tpl_version">{$strTplVersion}</td>
            </tr>
            <tr>
                <td width="50%">Datenbankversion</td>
                <td width="50%">{$strDBVersion}</td>
            </tr>
            <tr>
                <td width="50%">Datenbank zuletzt aktualisiert</td>
                <td width="50%">{$strUpdated}</td>
            </tr>
        </tbody>
    </table>
    <div id="version_data_wrapper">
        <p class="text-center ajax_preloader update">Wird geladen...</p>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        ioCall('getShopInfo', ['widgets/shopinfo_version.tpl', 'version_data_wrapper']);
    });
</script>
