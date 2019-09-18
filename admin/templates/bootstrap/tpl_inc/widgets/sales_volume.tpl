<div class="widget-custom-data">
    <div class="widget-custom-data">
        {if $linechart}
            {include file='tpl_inc/linechart_inc.tpl' linechart=$linechart headline="" id='linechart_sales_volume' width='100%' height='320px' ylabel="Umsatz" href=false ymin=0 legend=false}
        {else}
            <div class="widget-container"><div class="alert alert-info">F&uuml;r den aktuellen Monat liegen noch keine Statistiken vor.</div></div>
        {/if}
    </div>
</div>