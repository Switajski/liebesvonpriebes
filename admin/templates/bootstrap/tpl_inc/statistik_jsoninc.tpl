{if $oStatJSON}
    <script type='text/javascript' src='{$shopURL}/includes/libs/flashchart/js/json/json2.js'></script>
    <script type='text/javascript' src='{$shopURL}/includes/libs/flashchart/js/swfobject.js'></script>
    <script type='text/javascript'>
        swfobject.embedSWF('{$shopURL}/includes/libs/flashchart/open-flash-chart.swf', 'my_chart', '100%', '260', '9.0.0', 'expressInstall.swf', null, {ldelim}
            wmode: 'transparent' {rdelim});
        function open_flash_chart_data() {ldelim}
            return JSON.stringify({$oStatJSON});
        {rdelim}
    </script>
{/if}