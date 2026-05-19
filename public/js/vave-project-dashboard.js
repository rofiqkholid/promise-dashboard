window.vaveProjectDashboard = function () {
    const chartsMap = {};
    return {
        showVaveFilter: false,
        vaveFiltersInitialized: false,
        ebdVersionsList: [],
        yearsList: Array.from({length: 6}, (_, i) => (new Date().getFullYear() - i).toString()),
        filter: {
            mode: 'monthly',
            period: new Date().toISOString().slice(0, 7), // YYYY-MM
            customer_id: '',
            model_id: '',
            ebd_version: ''
        },
        kpis: {
            totalBenefit: '0',
            savingWeight: '0.000',
            savingRate: '0.0',
            meritItems: '0'
        },
        items: [],
        chartType: 'benefit',
        currentChartRes: null,
        currentMeritModels: null,
        currentMeritPareto: null,
        
        initVaveFilters() {
            const self = this;
            const basePath = window.APP_BASE_URL || '';
            
            this.$watch('showVaveFilter', (val) => {
                if (val && !this.vaveFiltersInitialized) {
                    this.$nextTick(() => {
                        $('#vaveFilterCustomer').select2({
                            dropdownParent: $('#vaveFilterCustomer').parent(),
                            width: '100%', placeholder: 'Select Customer', allowClear: true,
                            ajax: {
                                url: `${basePath}/api/customers`, dataType: 'json', delay: 250,
                                data: params => ({ q: params.term, page: params.page || 1 }),
                                processResults: data => ({ results: data.results || [], pagination: { more: data.pagination ? data.pagination.more : false } })
                            }
                        }).on('change', function() {
                            self.filter.customer_id = $(this).val() || '';
                            $('#vaveFilterModel').val(null).trigger('change');
                            self.fetchVaveData();
                        });

                        $('#vaveFilterModel').select2({
                            dropdownParent: $('#vaveFilterModel').parent(),
                            width: '100%', placeholder: 'Select Model', allowClear: true,
                            ajax: {
                                url: `${basePath}/api/models`, dataType: 'json', delay: 250,
                                data: params => ({ q: params.term, page: params.page || 1, customer_ids: self.filter.customer_id ? [self.filter.customer_id] : [] }),
                                processResults: data => ({ results: data.results || [], pagination: { more: data.pagination ? data.pagination.more : false } })
                            }
                        }).on('change', function() {
                            self.filter.model_id = $(this).val() || '';
                            self.fetchVaveData();
                        });
                        
                        this.vaveFiltersInitialized = true;
                    });
                }
            });
        },

        onModeChange() {
            if (this.filter.mode === 'monthly') {
                if (this.filter.period && this.filter.period.length === 4) {
                    const currentMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');
                    this.filter.period = `${this.filter.period}-${currentMonth}`;
                } else if (!this.filter.period) {
                    this.filter.period = new Date().toISOString().slice(0, 7);
                }
            } else {
                if (this.filter.period && this.filter.period.includes('-')) {
                    this.filter.period = this.filter.period.split('-')[0];
                } else if (!this.filter.period) {
                    this.filter.period = new Date().getFullYear().toString();
                }
            }
            this.fetchVaveData();
        },

        resetVaveFilter() {
            this.filter.mode = 'monthly';
            this.filter.period = new Date().toISOString().slice(0, 7);
            this.filter.customer_id = '';
            this.filter.model_id = '';
            this.filter.ebd_version = '';
            if (this.vaveFiltersInitialized) {
                $('#vaveFilterCustomer').val(null).trigger('change');
                $('#vaveFilterModel').val(null).trigger('change');
            }
            this.fetchVaveData();
        },

        async fetchVaveData() {
            try {
                this.initVaveFilters();

                const basePath = window.APP_BASE_URL || '';

                if (this.ebdVersionsList.length === 0) {
                    const resVersions = await fetch(`${basePath}/api/vave-project/ebd-versions`).then(r => r.json());
                    this.ebdVersionsList = resVersions || [];
                }
                
                let year = new Date().getFullYear();
                let month = '';
                
                if (this.filter.period) {
                    if (this.filter.period.includes('-')) {
                        const parts = this.filter.period.split('-');
                        year = parseInt(parts[0], 10);
                        if (this.filter.mode === 'monthly') {
                            month = parseInt(parts[1], 10);
                        }
                    } else {
                        year = parseInt(this.filter.period, 10);
                        month = '';
                    }
                }

                const params = new URLSearchParams({
                    mode: this.filter.mode,
                    year: year
                });
                if (month) params.append('month', month);
                
                if (this.filter.customer_id) params.append('customer_id', this.filter.customer_id);
                if (this.filter.model_id) params.append('model_id', this.filter.model_id);
                if (this.filter.ebd_version) params.append('ebd_version', this.filter.ebd_version);

                const [chartRes, paretoRes] = await Promise.all([
                    fetch(`${basePath}/api/vave-project/chart-data?${params}`).then(r => r.json()),
                    fetch(`${basePath}/api/vave-project/pareto-data?${params}`).then(r => r.json())
                ]);

                this.currentChartRes = chartRes;
                this.updateKpis(chartRes.kpi);
                this.items = (chartRes.items || []).filter(item => item.gap_benefit_idr > 0);
                
                const meritModels = { labels: [], idr: [], kg: [], merit: [], loss: [], plan_cost: [] };
                if (chartRes.models) {
                    chartRes.models.labels.forEach((label, i) => {
                        if (chartRes.models.idr[i] > 0) {
                            meritModels.labels.push(label);
                            meritModels.idr.push(chartRes.models.idr[i]);
                            meritModels.kg.push(chartRes.models.kg[i]);
                            meritModels.merit.push(chartRes.models.merit[i]);
                            meritModels.loss.push(chartRes.models.loss[i]);
                            meritModels.plan_cost.push(chartRes.models.plan_cost[i]);
                        }
                    });
                }
                
                this.currentMeritModels = meritModels;
                this.currentMeritPareto = (paretoRes.pareto || []).filter(p => p.gap_benefit_idr > 0);
                
                this.renderCharts();
            } catch (e) {
                console.error("Error fetching VAVE data:", e);
            }
        },

        switchChartType(type) {
            this.chartType = type;
            this.renderCharts();
        },

        updateKpis(kpi) {
            if (!kpi) return;
            const fmt = v => new Intl.NumberFormat('id-ID').format(v || 0);
            this.kpis.totalBenefit = fmt(kpi.gap_benefit_idr);
            this.kpis.savingWeight = (kpi.gap_kg_total || 0).toFixed(3);
            this.kpis.savingRate = (kpi.saving_rate || 0).toFixed(1);
            this.kpis.meritItems = fmt(kpi.merit_count);
        },

        renderCharts() {
            const Chart = window.Chart;
            if (!Chart) return;
            
            // Global Chart.js Font & Color configurations
            Chart.defaults.font.family = 'Outfit';
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#64748b';
            
            const meritModels = this.currentMeritModels || { labels: [], idr: [], kg: [], merit: [], loss: [], plan_cost: [] };
            const meritPareto = this.currentMeritPareto || [];
            const chartRes = this.currentChartRes || {};

            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom', 
                        labels: { 
                            color: '#64748b',
                            font: { family: 'Outfit', size: 14 },
                            usePointStyle: true,
                            boxWidth: 12,
                            padding: 20
                        } 
                    }
                }
            };
            
            const formatFn = (v) => v === 0 ? '' : (Math.abs(v) >= 1000000 ? (v/1000000).toFixed(1) + 'M' : (v/1000).toFixed(0) + 'k');
            
            const tickFormatFn = (v) => {
                if (v === 0) return '0';
                if (Math.abs(v) >= 1000000000) return (v / 1000000000).toFixed(1).replace(/\.0$/, '') + 'B';
                if (Math.abs(v) >= 1000000) return (v / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
                if (Math.abs(v) >= 1000) return (v / 1000).toFixed(0) + 'k';
                return v;
            };

            // Benefit Chart Data Preparation
            let labels = [];
            let data = [];
            let color = '#10b981'; // default emerald for benefit
            let isCurrency = true;
            let chartTypeParam = this.filter.mode === 'monthly' ? 'bar' : 'line';
            let bgOpacity = this.filter.mode === 'monthly' ? 'ff' : '40';
            
            if (this.chartType === 'benefit') {
                color = '#10b981'; isCurrency = true;
                if (this.filter.mode === 'monthly') { labels = meritModels.labels; data = meritModels.idr; }
                else if (this.filter.mode === 'yearly') {
                    labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    data = new Array(12).fill(0);
                    (chartRes.trend || []).forEach(t => { if (parseFloat(t.gap_benefit_idr) > 0) data[t.month_num - 1] = parseFloat(t.gap_benefit_idr); });
                }
                else {
                    labels = (chartRes.comparison || []).map(c => c.year);
                    data = (chartRes.comparison || []).map(c => c.gap_benefit_idr);
                }
            } else if (this.chartType === 'weight') {
                color = '#3b82f6'; isCurrency = false;
                if (this.filter.mode === 'monthly') { labels = meritModels.labels; data = meritModels.kg; }
                else if (this.filter.mode === 'yearly') {
                    labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                    data = new Array(12).fill(0);
                    (chartRes.trend || []).forEach(t => { if (parseFloat(t.gap_benefit_idr) > 0) data[t.month_num - 1] = parseFloat(t.gap_kg_total); });
                }
                else {
                    labels = (chartRes.comparison || []).map(c => c.year);
                    data = (chartRes.comparison || []).map(c => c.gap_kg_total);
                }
            } else if (this.chartType === 'efficiency') {
                color = '#f59e0b'; isCurrency = false; chartTypeParam = 'bar'; bgOpacity = 'ff';
                labels = meritModels.labels;
                data = meritModels.idr.map((idr, i) => (idr / (meritModels.plan_cost[i] || 1)) * 100);
            }

            const ctxBenefit = document.getElementById('vaveBenefitChart');
            if (ctxBenefit) {
                if (chartsMap.vaveBenefit) chartsMap.vaveBenefit.destroy();
                
                const isHorizontal = this.chartType === 'efficiency';
                const optionsScales = isHorizontal ? {
                    x: { beginAtZero: true, ticks: { maxTicksLimit: 5, font: { size: 12, family: 'Outfit' }, callback: isCurrency ? tickFormatFn : (v => v) } },
                    y: { ticks: { font: { size: 12, family: 'Outfit' } }, grid: { display: false } }
                } : {
                    y: { beginAtZero: true, ticks: { maxTicksLimit: 5, font: { size: 12, family: 'Outfit' }, callback: isCurrency ? tickFormatFn : (v => v) } },
                    x: { ticks: { font: { size: 12, family: 'Outfit' } }, grid: { display: false } }
                };

                chartsMap.vaveBenefit = new Chart(ctxBenefit, {
                    type: chartTypeParam,
                    plugins: [ChartDataLabels],
                    data: {
                        labels: labels,
                        datasets: [{
                            label: this.chartType === 'benefit' ? 'Benefit' : (this.chartType === 'weight' ? 'Weight' : 'Efficiency'),
                            data: data,
                            pointStyle: chartTypeParam === 'line' ? 'circle' : 'rect',
                            backgroundColor: chartTypeParam === 'line' ? color + '1A' : color,
                            borderColor: chartTypeParam === 'line' ? color : 'transparent',
                            borderWidth: chartTypeParam === 'line' ? 3 : 0,
                            fill: chartTypeParam === 'line',
                            tension: 0.4,
                            pointBackgroundColor: color,
                            pointBorderColor: color,
                            pointRadius: chartTypeParam === 'line' ? 4 : 0,
                            borderRadius: 2,
                            datalabels: {
                                display: (ctx) => ctx.dataset.data[ctx.dataIndex] !== 0,
                                color: '#ffffff', 
                                backgroundColor: 'rgba(0, 0, 0, 0.5)', 
                                borderRadius: 0,
                                font: {size: 12, weight: 'bold', family: 'Outfit'},
                                padding: { top: 2, bottom: 2, left: 4, right: 4 },
                                formatter: isCurrency ? formatFn : (v => v === 0 ? '' : v.toFixed(1))
                            }
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: isHorizontal ? 'y' : 'x',
                        plugins: {
                            ...commonOptions.plugins,
                            legend: {
                                ...commonOptions.plugins?.legend,
                                display: true
                            }
                        },
                        scales: optionsScales
                    }
                });
            }

            // Pareto Chart
            const ctxPareto = document.getElementById('vaveParetoChart');
            if (ctxPareto) {
                if (chartsMap.vavePareto) chartsMap.vavePareto.destroy();
                chartsMap.vavePareto = new Chart(ctxPareto, {
                    type: 'bar',
                    plugins: [ChartDataLabels],
                    data: {
                        labels: meritPareto.map(p => p.label),
                        datasets: [
                            {
                                type: 'line',
                                label: 'Cumulative %',
                                data: meritPareto.map(p => p.cumulative_pct),
                                pointStyle: 'circle',
                                borderColor: '#f59e0b', backgroundColor: '#f59e0b',
                                yAxisID: 'y1', tension: 0.4, borderWidth: 2, pointBackgroundColor: '#f59e0b', pointBorderColor: '#f59e0b',
                                datalabels: {
                                    display: (ctx) => ctx.dataset.data[ctx.dataIndex] !== 0,
                                    anchor: 'end', align: 'top', color: '#ffffff', backgroundColor: 'rgba(0, 0, 0, 0.5)', borderRadius: 0, padding: { top: 2, bottom: 2, left: 4, right: 4 }, font: {size: 12, weight:'bold', family: 'Outfit'},
                                    formatter: v => v.toFixed(0) + '%'
                                }
                            },
                            {
                                type: 'bar',
                                label: 'Benefit',
                                data: meritPareto.map(p => p.gap_benefit_idr),
                                pointStyle: 'rect',
                                backgroundColor: '#10b981', yAxisID: 'y', borderRadius: 2,
                                datalabels: {
                                    display: (ctx) => ctx.dataset.data[ctx.dataIndex] !== 0,
                                    color: '#ffffff', backgroundColor: 'rgba(0, 0, 0, 0.5)', borderRadius: 0, font: {size: 12, weight: 'bold', family: 'Outfit'},
                                    padding: { top: 2, bottom: 2, left: 4, right: 4 },
                                    formatter: formatFn
                                }
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: { beginAtZero: true, ticks: { maxTicksLimit: 5, font: { size: 12, family: 'Outfit' }, callback: tickFormatFn } },
                            y1: { position: 'right', beginAtZero: true, max: 120, grid: { drawOnChartArea: false }, ticks: { maxTicksLimit: 5, font: { size: 12, family: 'Outfit' }, callback: v => v + '%' } },
                            x: { ticks: { font: { size: 12, family: 'Outfit' } }, grid: { display: false } }
                        }
                    }
                });
            }
        }
    }
}
