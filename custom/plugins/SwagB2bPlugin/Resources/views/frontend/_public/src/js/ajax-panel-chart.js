/**
 * global: Chart
 *
 * Ajax Panel chart Plugin allows to render a ChartJS canvas inside an ajax panel.
 *
 * Usage:
 * <canvas id="b2b-canvas" width="100%" height="40"></canvas>
 */
$.plugin('b2bAjaxPanelChart', {
    defaults: {

        chartId: 'b2b-canvas',

        chartDataUrlSelector: 'chart-url',

        chartUrl: '',

        chartHolder: '',

        chartConfig: [],

        drawed: false,

        errors: {

        }
    },

    init: function() {

        this.registerGlobalListeners();

        this.loadChartConfig();

        this.drawChart();

        this.loadAsyncChartData();
    },

    registerGlobalListeners: function() {
        var me = this;

        me.defaults.chartUrl = me.$el.data('chart-url');

        me._$form = me.$el.find('form');
    },

    loadAsyncChartData: function() {
        var me = this;

        $.ajax({
            type: me._$form.attr('method'),
            url: me.defaults.chartUrl,
            data: me._$form.serialize(),
            success: function(response) {

                var responseData = JSON.parse(response);
                var chartData = me.defaults.chartHolder.config.data;

                chartData.labels = responseData.labels;
                chartData.datasets = [];

                var color;
                var colorIndex = 0;
                var colorArray = [
                    'rgba(60, 167, 245, .5)',
                    'rgba(103, 192, 77, .5)',
                    'rgba(255, 225, 73, .5)',
                    'rgba(43, 43, 43, .5)',
                    'rgba(255, 72, 40, .5)',
                    'rgba(138, 91, 175, .5)'
                ];

                $.each(responseData.data, function(label, values) {
                    color = colorArray[colorIndex];

                    chartData.datasets.push({
                        label: label,
                        backgroundColor: color,
                        borderColor: color,
                        borderWidth: 1,
                        data: values
                    });

                    colorIndex++;
                });

                me.defaults.chartHolder.update(400);
            }
        });
    },

    drawChart: function() {
        var me = this,
            chartConfig = me.defaults.chartConfig,
            ctx = document.getElementById(me.defaults.chartId).getContext("2d");

        if(me.defaults.drawed) {
            return;
        }

        me.defaults.chartHolder = new Chart(ctx, chartConfig);

        me.defaults.drawed = true;
    },

    loadChartConfig: function()
    {
        var me = this,
            config = {
            type: 'line',
            data: {},
            options: {
                responsive: true,
                title:{
                    display: false,
                    text:''
                },
                tooltips: {
                    mode: 'index',
                    intersect: false
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                legend: {
                    onClick: function(event) {
                        event.stopPropagation();
                    }
                }
            }
        };

        me.defaults.chartConfig = config;
    },

    destroy: function() {
        var me = this;
        me._destroy();
    }
});