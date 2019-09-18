{*
    Params:
    piechart    - piechart object
    headline    - string
    id          - string
    width       - string
    height      - string
*}

{if $piechart->getActive()}
    <div id="{$id}" style="width: {$width}; height: {$height};"></div>

    <script type="text/javascript">
    {literal}
    var chart;
    $(document).ready(function() {
        
        chart = new Highcharts.Chart({
            chart: {
    {/literal}
                renderTo: '{$id}',
    {literal}
                defaultSeriesType: 'line',
                backgroundColor: null,
                borderColor: '#CCC',
                borderWidth: 1,
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
    {/literal}
                text: '{$headline}'
    {literal}
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.percentage.toFixed(1) +' %'
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b>: '+ this.percentage.toFixed(1) +' %';
                        }
                    }
                }
            },
    {/literal}
            series:
                {$piechart->getSeriesJSON()}
    {literal}
        });
    });
    {/literal}
    </script>
{else}
    <div class="alert alert-info" role="alert">{#statisticNoData#}</div>
{/if}