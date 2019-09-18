<div class="widget-custom-data widget-help">

    <div class="row text-center">
        <div class="col-xs-6 border-right">
            <a href="https://guide.jtl-software.de/jtl/JTL-Shop" target="_blank">
                <i class="fa fa-book text-four-times text-info"></i>
                <h4>Dokumentation</h4>
            </a>
        </div>
        <div class="col-xs-6">
            <a href="https://forum.jtl-software.de" target="_blank">
                <i class="fa fa-comments-o text-four-times text-info"></i>
                <h4>Community-Forum</h4>
            </a>
        </div>
    </div>
    <hr>
    <ul class="linklist">
        <li id="help_data_wrapper">
            <p class="ajax_preloader">Wird geladen...</p>
        </li>
    </ul>
</div>

<script type="text/javascript">
    $(document).ready(function () {ldelim}
        ioCall('getRemoteData', ['{$JTLURL_GET_SHOPHELP}', 'oHelp_arr', 'widgets/help_data.tpl', 'help_data_wrapper']);
    {rdelim});
</script>