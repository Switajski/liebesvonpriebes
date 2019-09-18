<script type="text/javascript">
$(document).ready(function() {ldelim}
    ioCall('getRemoteData', ['{$JTLURL_GET_DUK}', 'oDuk', 'widgets/duk_data.tpl', 'duk_data_wrapper']);
{rdelim});
</script>

<div class="widget-custom-data">
   <div id="duk_data_wrapper">
      <p class="ajax_preloader">Wird geladen...</p>
   </div>
</div>