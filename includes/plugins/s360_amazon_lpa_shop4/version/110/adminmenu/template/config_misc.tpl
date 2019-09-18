<script type="text/javascript">
    var s360_lpa_admin_url = '{$oPlugin->cAdminmenuPfadURL}';
</script>

<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">Versandarten-Ausschluss</h3></div>
    <div class="panel-body">
        <form method="post" id="lpa-misc-settings-form" action="{$pluginAdminUrl}cPluginTab=Einstellungen%20Sonstiges">
            {$s360_jtl_token}
            <input type="hidden" name="{$session_name}" value="{$session_id}" />
            <input type="hidden" name="kPlugin" value="{$oPlugin->kPlugin}" />

            <input type="hidden" name="Setting" value="1" />
            <input type="hidden" name="update_lpa_misc_settings" value="1" />

            <div class="col-xs-12">W&auml;hlen Sie hier die Versandarten aus, die im Amazon Pay-Checkout <b>ausgeschlossen</b> sein sollen.</div>
            {foreach item=deliverymethod from=$s360_lpa_config_misc.lpa_available_delivery_methods name=exclude}
                <div class="col-xs-12">
                    <input id="lpa_excluded_{$smarty.foreach.exclude.index}" type="checkbox" name="lpa_excluded_delivery_methods[]" value="{$deliverymethod.key}" {if $deliverymethod.isExcluded} checked{/if}> <label for="lpa_excluded_{$smarty.foreach.exclude.index}">{$deliverymethod.name}</label>
                </div>
            {/foreach}

            <div class="col-xs-12 save_wrapper">
                <button name="speichern" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Speichern</button>
            </div>
        </form>
    </div>
</div>