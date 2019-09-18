{*
-------------------------------------------------------------------------------
    JTL-Shop 3
    File: statistik_header.tpl, smarty template inc file
    
    page for JTL-Shop 3 
    Admin
    
    Author: daniel.boehemr@jtl-software.de, JTL-Software
    http://www.jtl-software.de
    
    Copyright (c) 2010 JTL-Software
-------------------------------------------------------------------------------
*}

{if $oStatJSON}
<script type="text/javascript" src="../includes/libs/flashchart/js/json/json2.js"></script>
<script type="text/javascript" src="../includes/libs/flashchart/js/swfobject.js"></script>
<script type="text/javascript">
swfobject.embedSWF("../includes/libs/flashchart/open-flash-chart.swf", "my_chart", "100%", "260", "9.0.0", "expressInstall.swf", null, {ldelim} wmode : 'transparent' {rdelim});
function open_flash_chart_data()
{ldelim}
   return JSON.stringify({$oStatJSON});
{rdelim}

function load_1()
{ldelim}
  tmp = findSWF("my_chart");
  x = tmp.load( JSON.stringify(data_1) );
{rdelim}
 
function load_2()
{ldelim}
  alert("loading data_2");
  tmp = findSWF("my_chart");
  x = tmp.load( JSON.stringify(data_2) );
{rdelim}
</script>
{/if}