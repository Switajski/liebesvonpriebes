<script type="text/javascript" src="{$currentTemplateDir}js/jquery.jclock.js"></script>
<script type="text/javascript">
$(function($) {ldelim}
   $('#clock_time').jclock({ldelim}
      format: '%H:%M:%S',
   {rdelim});
   $('#clock_date').jclock({ldelim}
      format: '%A, %d. %B %Y',
   {rdelim});
{rdelim});
</script>

<div class="widget-custom-data nospacing">
   <div class="clock">
      <p id="clock_time"></p>
      <p id="clock_date"></p>
   </div>
</div>