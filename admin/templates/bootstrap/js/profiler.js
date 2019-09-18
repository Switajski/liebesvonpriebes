/**
 * creates a highcharts donut from plugin profiler data
 *
 * @param categories
 * @param data
 * @param renderTo
 */
createDonut = function (categories, data, renderTo) {
    var hookData = [],
        fileData = [],
        i,
        j,
        dataLen = data.length,
        drillDataLen,
        brightness;
    // Build the data arrays
    for (i = 0; i < dataLen; i += 1) {
        // add hook data
        hookData.push({
            name: categories[i],
            y: data[i].y,
            color: data[i].color
        });
        // add plugin file data
        drillDataLen = data[i].drilldown.data.length;
        for (j = 0; j < drillDataLen; j += 1) {
            brightness = ((j + 1) * 0.05);
            fileData.push({
                name: data[i].drilldown.categories[j],
                y: data[i].drilldown.data[j],
                runcount: data[i].drilldown.runcount[j],
                color: Highcharts.Color(data[i].color).brighten(brightness).get()
            });
        }
    }

    // Create the chart
    $('#' + renderTo).highcharts({
        chart: {
            type: 'pie',
            width: 1000
        },
        title: {
            text: 'Laufzeit pro Hook und Plugin'
        },
        yAxis: {
            title: {
                text: 'Anteil an Laufzeit'
            }
        },
        plotOptions: {
            pie: {
                shadow: false,
                center: ['50%', '50%']
            }
        },
        tooltip: {
            valueSuffix: 'ms',
            formatter: function () {
                var string = '<strong>' + this.key + '</strong><br />' +
                    this.series.name + ': ' + this.point.y + 'ms <br />' +
                    this.percentage.toFixed(2) + '%';
                if (this.point.options.runcount) {
                    string += ', ' + this.point.runcount + ' Aufruf' + (this.point.runcount > 1 ? 'e' : '');
                }

                return string;
            }
        },
        series: [{
            name: 'kombinierte Laufzeit',
            data: hookData,
            size: '60%',
            dataLabels: {
                formatter: function () {
                    // display only if larger than 10%
                    return this.percentage > 10 ? this.point.name : null;
                },
                color: 'white',
                distance: -25
            }
        }, {
            name: 'Laufzeit',
            data: fileData,
            size: '80%',
            innerSize: '60%',
            dataLabels: {
                formatter: function () {
                    // display only if larger than 10%
                    return this.percentage > 10 ? this.point.name : null;
                }
            }
        }]
    });
};

$(document).ready(function () {
    //create donut when panel is first opened
    $('.panel-heading').click(function () {
        var id = $(this).attr('data-idx'),
            collapsedElement = $('#profile-' + id),
            closed = collapsedElement.hasClass('in'),
            hasChart = (typeof $('#profile-pie-chart' + id).attr('data-highcharts-chart') !== 'undefined'),
            pie = window.pies[id];
        if (closed === false && hasChart === false) {
            createDonut(pie.categories, pie.data, pie.target);
        }
    });
});