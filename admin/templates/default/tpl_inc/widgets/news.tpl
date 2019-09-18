<script type="text/javascript">
$(document).ready(function() {ldelim}
   xajax_getRemoteDataAjax('{$JTLURL_GET_SHOPNEWS}', 'oNews_arr', 'widgets/news_data.tpl', 'news_data_wrapper', null, null, true);
{rdelim});
</script>

<div class="widget-custom-data">
   <div id="news_data_wrapper">
      <p class="ajax_preloader">Wird geladen...</p>
   </div>
</div>