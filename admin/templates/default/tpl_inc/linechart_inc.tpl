{*
    Params:
    linechart   - linechart object
    headline    - string
    id          - string
    width       - string
    height      - string
    ylabel      - string
    href        - bool
    legend      - bool
    ymin        - string
*}

{config_load file="$lang.conf" section="statistics"}
 
{if $linechart->getActive()}
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
                marginRight: 20,
                marginBottom: 25,
                backgroundColor: null,
                borderColor: '#CCC',
                borderWidth: 1
            },
            title: {
                style: {
                    color: '#333'
                },
    {/literal}
                text: '{$headline}',
    {literal}
                x: -20 //center
            },
    {/literal}
    {if $href}
    {literal}
            plotOptions: {            
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function() {
                                location.href = this.options.url;
                            }
                        }
                    }
                }
            },
    {/literal}
    {/if}
    {literal}
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -10,
                y: 100,
                borderWidth: 0,
    {/literal}
    {if $legend}
                enabled: true
    {else}
                enabled: false
    {/if}
    {literal}
            },
    {/literal}
            xAxis:
                {$linechart->getAxisJSON()}
            ,
    {literal}
            yAxis: {
                title: {
                    style: {
                        color: '#333'
                    },
    {/literal}
                    text: '{$ylabel}'
    {literal}
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }],
    {/literal}
    {if isset($ymin) && $ymin|@count_characters > 0}
                min: {$ymin}
    {/if}
    {literal}
            },
    {/literal}
            series:
                {$linechart->getSeriesJSON()}
    {literal}
        });
    });
    {/literal}
    </script>
{else}
    <p class="box_info container">{#statisticNoData#}</p>
 {/if}