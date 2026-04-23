import './bootstrap';
import Chart from 'chart.js/auto';
import * as helpers from 'chart.js/helpers';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.Chart = Chart;
window.Chart.helpers = helpers;
window.ChartDataLabels = ChartDataLabels;
Alpine.start();

Chart.defaults.font.family = "'Outfit', sans-serif";

document.addEventListener('DOMContentLoaded', function() {
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 1000,
            easing: 'easeOutQuad'
        },
        animations: {
            y: {
                from: (ctx) => {
                    if (ctx.type === 'data' && ctx.chart.scales && ctx.chart.scales.y) {
                        return ctx.chart.chartArea ? ctx.chart.chartArea.bottom : 0;
                    }
                    return undefined;
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    maxTicksLimit: 4
                },
                border: {
                    display: false
                }
            }
        }
    };

    const dummyData = [12, 19, 3, 5, 2, 3];
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];

    const colors = [
        '#3b82f6', // blue
        '#10b981', // green
        '#f59e0b', // yellow/orange
        '#ef4444', // red
        '#8b5cf6', // purple
    ];

    for (let i = 1; i <= 20; i++) {
        const ctx = document.getElementById(`chart${i}`);
        if (ctx) {
            const color = colors[i % colors.length];
            // Add a subtle fill for the line charts (warna tipis)
            const bgColor = (i % 2 === 0) ? color + '40' : color; 

            new Chart(ctx, {
                type: i % 2 === 0 ? 'line' : 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: `Dataset ${i}`,
                        data: Array.from({length: 6}, () => Math.floor(Math.random() * 85) + 10),
                        backgroundColor: bgColor,
                        borderColor: color,
                        borderWidth: (i % 2 === 0) ? 2 : 0,
                        fill: (i % 2 === 0)
                    }]
                },
                options: commonOptions
            });
        }
    }
});
