(() => {
    "use strict";

    var url = window.location.origin;

    function chartBar(data)
    {
        var ctx = document.getElementById("chart-bars").getContext("2d");

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: data.month,
                datasets: [{
                    label: "Sales",
                    tension: 0.4,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    backgroundColor: "#fff",
                    data: data.sales,
                    maxBarThickness: 6
                }, ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                        },
                        ticks: {
                            suggestedMin: 0,
                            suggestedMax: 500,
                            beginAtZero: true,
                            padding: 15,
                            font: {
                                size: 14,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                            color: "#fff"
                        },
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false
                        },
                        ticks: {
                            display: false
                        },
                    },
                },
            },
        });
    }
    function chartLine(data)
    {
        console.log(data);
        var ctx2 = document.getElementById("chart-line").getContext("2d");

        var gradientStroke1 = ctx2.createLinearGradient(0, 230, 0, 50);

        gradientStroke1.addColorStop(1, 'rgba(203,12,159,0.2)');
        gradientStroke1.addColorStop(0.2, 'rgba(72,72,176,0.0)');
        gradientStroke1.addColorStop(0, 'rgba(203,12,159,0)'); //purple colors

        var gradientStroke2 = ctx2.createLinearGradient(0, 230, 0, 50);

        gradientStroke2.addColorStop(1, 'rgba(20,23,39,0.2)');
        gradientStroke2.addColorStop(0.2, 'rgba(72,72,176,0.0)');
        gradientStroke2.addColorStop(0, 'rgba(20,23,39,0)'); //purple colors

        new Chart(ctx2, {
            type: "line",
            data: {
                labels: data.month,
                datasets: data.chart_line.tot_product,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#b2b9bf',
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#b2b9bf',
                            padding: 20,
                            font: {
                                size: 11,
                                family: "Open Sans",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });
    }

    async function ajaxChartBar() {
        await $.ajax({
            url: url+'/charts/bar',
            type: 'GET',
            async: true,
            success: function (res) {
                // console.log(res.chart_line.tot_product);
                //chart Bar
                chartBar(res);
                $("#custTotal").text(res.customer_sales);
                $("#custPercentBar").toggleClass('w-'+res.percent_customer);
                $("#custPercentBar").attr('aria-valuenow',res.percent_customer);
                $("#percentCustomerYear").text('+'+res.percent_customer+'%');
                $('#totalSales').text('Rp.'+res.total_sales);
                $("#prodTotal").text(res.product_sales);
                $("#prodPercentBar").toggleClass('w-'+res.percent_product);
                $("#prodPercentBar").attr('aria-valuenow',res.percent_product);
                //chart Line
                // chartLine(res);
            }
        });
    }
    async function ajaxChartLine() {
        await $.ajax({
            url: url+'/charts/line',
            type: 'GET',
            async: true,
            success: function (res) {
                //chart Line
                chartLine(res);
            }
        });
    }
    async function initChart() {
        await ajaxChartBar();
        // await ajaxChartLine();
    }
    initChart();
})();
