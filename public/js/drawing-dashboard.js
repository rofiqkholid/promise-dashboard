    window.drawingDashboardData = function() {
        return {
            baseUrl: window.APP_BASE_URL + "/api",

            users: 0,
            uploads: 0,
            downloads: 0,
            documents: 0,

            paper: 0,
            harga: 0,
            tree: 0,
            co2: 0,

            diskTotal: '0 GB',
            diskUsed: '0 GB',

            recentActivity: [],
            selectedYear: '2026',

            showFilterModal: false,
            selectedCustomers: [],
            selectedModels: [],
            selectedPartGroup: [],
            select2Initialized: false,
            
            getFilterParams() {
                const params = new URLSearchParams();
                const dateEnd = document.getElementById('filter_date_end')?.value;
                if (dateEnd) params.append('date_end', dateEnd);
                
                this.selectedCustomers.forEach(c => params.append('customer[]', c.text));
                this.selectedModels.forEach(m => params.append('model[]', m.text));
                this.selectedPartGroup.forEach(p => params.append('part_group[]', p.text));
                
                if (this.select2Initialized) {
                    const statusData = $('#filter_status').select2('data');
                    const statusVal = statusData && statusData[0] ? statusData[0].text : 'ALL';
                    if (statusVal && statusVal !== 'ALL') {
                        params.append('project_status', statusVal);
                    }
                }
                
                params.append('year', this.selectedYear);
                return params.toString();
            },
            
            async loadFilterOptions() {
                // Select2 will be initialized when modal opens
                this.$watch('showFilterModal', (val) => {
                    if (val && !this.select2Initialized) {
                        this.$nextTick(() => this.initSelect2());
                    }
                });
            },
            
            initSelect2() {
                const self = this;
                
                // Customer Select2
                $('#filter_customer').select2({
                    dropdownParent: $('#filter_customer').parent(),
                    width: '100%',
                    placeholder: 'Select Customer...',
                    allowClear: true,
                    ajax: {
                        url: `${this.baseUrl}/customers`,
                        dataType: 'json',
                        delay: 250,
                        data: (params) => ({
                            q: params.term,
                            page: params.page || 1
                        }),
                        processResults: (data, params) => ({
                            results: data.results || [],
                            pagination: { more: data.pagination ? data.pagination.more : false }
                        })
                    }
                }).on('change', function() {
                    const data = $(this).select2('data')[0];
                    if (data && data.id) {
                        if (!self.selectedCustomers.find(x => x.id === data.id)) {
                            self.selectedCustomers.push({ id: data.id, text: data.text });
                            self.renderFilterPills();
                        }
                        $(this).val(null).trigger('change.select2');
                    }
                });
                
                // Model Select2
                $('#filter_model').select2({
                    dropdownParent: $('#filter_model').parent(),
                    width: '100%',
                    placeholder: 'Select Model...',
                    allowClear: true,
                    ajax: {
                        url: `${this.baseUrl}/models`,
                        dataType: 'json',
                        delay: 250,
                        data: (params) => ({
                            q: params.term,
                            page: params.page || 1,
                            customer_ids: self.selectedCustomers.map(item => item.id)
                        }),
                        processResults: (data, params) => ({
                            results: data.results || [],
                            pagination: { more: data.pagination ? data.pagination.more : false }
                        })
                    }
                }).on('change', function() {
                    const d = $(this).select2('data')[0];
                    if (d && d.id) {
                        if (!self.selectedModels.find(x => x.id === d.id)) {
                            self.selectedModels.push({ id: d.id, text: d.text });
                            self.renderFilterPills();
                        }
                        $(this).val(null).trigger('change.select2');
                    }
                });
                
                // Part Group Select2
                $('#filter_part_group').select2({
                    dropdownParent: $('#filter_part_group').parent(),
                    width: '100%',
                    placeholder: 'Select Part Group...',
                    allowClear: true,
                    ajax: {
                        url: `${this.baseUrl}/part-group`,
                        dataType: 'json',
                        delay: 250,
                        data: (params) => ({
                            q: params.term,
                            page: params.page || 1
                        }),
                        processResults: (data, params) => ({
                            results: data.results || [],
                            pagination: { more: data.pagination ? data.pagination.more : (params.page * 10) < data.total_count }
                        })
                    }
                }).on('change', function() {
                    const d = $(this).select2('data')[0];
                    if (d && d.text) {
                        if (!self.selectedPartGroup.find(x => x.text === d.text)) {
                            self.selectedPartGroup.push({ id: d.id || d.text, text: d.text });
                        }
                        self.renderFilterPills();
                        $(this).val(null).trigger('change.select2');
                    }
                });
                
                // Status Select2
                $('#filter_status').select2({
                    dropdownParent: $('#filter_status').parent(),
                    width: '100%',
                    placeholder: 'Select Status',
                    ajax: {
                        url: `${this.baseUrl}/status`,
                        dataType: 'json',
                        data: (params) => ({
                            q: params.term
                        }),
                        processResults: (data) => {
                            let res = data.results || [];
                            res.unshift({ id: 'ALL', text: 'ALL' });
                            return { results: res };
                        }
                    }
                });
                
                this.select2Initialized = true;
            },
            
            renderFilterPills() {
                const container = document.getElementById('filterPillContainer');
                container.innerHTML = '';
                const self = this;
                
                const createPill = (type, item, stateKey) => {
                    const span = document.createElement('span');
                    span.className = 'inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs font-medium mr-1 mb-1';
                    span.innerHTML = `<span class="font-normal mr-1">${type}:</span><span>${item.text}</span><button type="button" class="ml-1 hover:text-blue-600 focus:outline-none"><i class="fa-solid fa-times fa-xs"></i></button>`;
                    span.querySelector('button').addEventListener('click', () => {
                        const arr = self[stateKey];
                        const idx = arr.findIndex(x => (x.id || x.text) === (item.id || item.text));
                        if (idx > -1) arr.splice(idx, 1);
                        self.renderFilterPills();
                    });
                    container.appendChild(span);
                };
                
                this.selectedCustomers.forEach(item => createPill('Customer', item, 'selectedCustomers'));
                this.selectedModels.forEach(item => createPill('Model', item, 'selectedModels'));
                this.selectedPartGroup.forEach(item => createPill('Part Group', item, 'selectedPartGroup'));
            },
            
            resetFilters() {
                this.selectedCustomers = [];
                this.selectedModels = [];
                this.selectedPartGroup = [];
                document.getElementById('filter_date_end').value = '';
                if (this.select2Initialized) {
                    $('#filter_customer').val(null).trigger('change.select2');
                    $('#filter_model').val(null).trigger('change.select2');
                    $('#filter_part_group').val(null).trigger('change.select2');
                    $('#filter_status').val(null).trigger('change.select2');
                }
                this.renderFilterPills();
                this.applyFilters();
            },
            
            applyFilters() {
                this.showFilterModal = false;
                this.fetchData();
            },

            async fetchData() {
                try {
                    const qs = this.getFilterParams();

                    // Start all non-chart fetches
                    const corePromises = [
                        fetch(`${this.baseUrl}/active-users-count`).then(r => r.json()).then(d => this.users = d.count),
                        fetch(`${this.baseUrl}/upload-count`).then(r => r.json()).then(d => this.uploads = d.count),
                        fetch(`${this.baseUrl}/download-count`).then(r => r.json()).then(d => this.downloads = d.count),
                        fetch(`${this.baseUrl}/doc-count`).then(r => r.json()).then(d => this.documents = d.count),
                        fetch(`${this.baseUrl}/get-save-env?${qs}`).then(r => r.json()).then(d => {
                            this.paper = d.paper || 0;
                            this.harga = d.harga || 0;
                            this.tree = d.save_tree || 0;
                            this.co2 = d.co2_reduced || 0;

                            // Animate eco progress circle
                            this.$nextTick(() => {
                                const circle = document.getElementById('ecoProgressCircle');
                                if (circle) {
                                    const treeProgress = Math.min(this.tree, 1);
                                    const circumference = 264;
                                    const offset = circumference * (1 - treeProgress);
                                    circle.style.transition = 'stroke-dashoffset 1.5s ease-out';
                                    requestAnimationFrame(() => {
                                        circle.setAttribute('stroke-dashoffset', offset);
                                    });
                                }
                            });
                        }),
                        fetch(`${this.baseUrl}/log-data-activity?${qs}`).then(r => r.json()).then(d => {
                            this.recentActivity = d.data || [];
                        })
                    ];

                    // Wait for core data
                    await Promise.all(corePromises);

                    // Initialize charts and wait for them
                    await this.initDrawingCharts();

                    // All ready! Dispatch event to hide preloader
                    window.dispatchEvent(new CustomEvent('dashboard-ready'));

                } catch (e) {
                    console.error("Initialization error:", e);
                    // Still hide loader on error so user isn't stuck
                    window.dispatchEvent(new CustomEvent('dashboard-ready'));
                }
            },

            monitoringAllData: [],
            monitoringPage: 0,

            prevMonitoringPage() {
                if (this.monitoringPage > 0) {
                    this.monitoringPage--;
                    this.renderMonitoringPage();
                }
            },

            nextMonitoringPage() {
                if ((this.monitoringPage + 1) * 7 < this.monitoringAllData.length) {
                    this.monitoringPage++;
                    this.renderMonitoringPage();
                }
            },

            renderMonitoringPage() {
                const Chart = window.Chart;
                if (!Chart) return;

                const ctx = document.getElementById('drawingMonitoringChart');
                if (!ctx) return;

                const pageData = this.monitoringAllData.slice(this.monitoringPage * 7, (this.monitoringPage + 1) * 7);
                if (pageData.length === 0) {
                    let existChart = Chart.getChart('drawingMonitoringChart');
                    if (existChart) {
                        existChart.data.labels = [];
                        existChart.data.datasets.forEach(ds => ds.data = []);
                        existChart.update();
                    }
                    return;
                }

                let labels = [];
                let planCount = [];
                let actualCount = [];
                let percentages = [];
                pageData.forEach(item => {
                    labels.push([`${item.customer_name}-${item.model}`, `${item.project_status}-${item.part_group}`]);
                    planCount.push(item.plan_count);
                    actualCount.push(item.actual_count);
                    percentages.push(item.percentage);
                });

                const maxCount = Math.max(...planCount, ...actualCount, 10);
                const suggestedMax = Math.ceil(maxCount * 1.3);

                let existChart = Chart.getChart('drawingMonitoringChart');
                if (existChart) {
                    existChart.data.labels = labels;
                    existChart.data.datasets[0].data = percentages;
                    existChart.data.datasets[1].data = planCount;
                    existChart.data.datasets[2].data = actualCount;
                    existChart.options.scales.y.suggestedMax = suggestedMax;
                    existChart.update();
                    return;
                }

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: 'Percentage',
                                data: percentages,
                                type: 'line',
                                borderColor: '#f59e0b',
                                backgroundColor: '#f59e0b',
                                borderWidth: 2,
                                pointRadius: 6,
                                pointBackgroundColor: '#f59e0b',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                tension: 0.3,
                                fill: false,
                                yAxisID: 'y1'
                            },
                            {
                                label: 'Plan Count',
                                data: planCount,
                                backgroundColor: '#3b82f6',
                                yAxisID: 'y',
                                animation: { delay: 200 }
                            },
                            {
                                label: 'Actual Count',
                                data: actualCount,
                                backgroundColor: '#10b981',
                                yAxisID: 'y',
                                animation: { delay: 400 }
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 700,
                            easing: 'easeOutQuad'
                        },
                        animations: {
                            y: {
                                from: (ctx) => {
                                    if (ctx.type === 'data' && ctx.chart.chartArea) {
                                        return ctx.chart.chartArea.bottom;
                                    }
                                    return undefined;
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    font: { family: 'Outfit', size: 14 }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                min: 0,
                                suggestedMax: suggestedMax,
                                title: {
                                    display: true,
                                    text: 'Count',
                                    font: { family: 'Outfit', weight: 'bold', size: 12 }
                                },
                                grid: { color: '#f3f4f6' },
                                ticks: {
                                    maxTicksLimit: 4,
                                    font: { family: 'Outfit' }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                min: 0,
                                max: 120,
                                title: {
                                    display: true,
                                    text: 'Percentage',
                                    font: { family: 'Outfit', weight: 'bold', size: 12 }
                                },
                                grid: { drawOnChartArea: false },
                                ticks: {
                                    maxTicksLimit: 4,
                                    font: { family: 'Outfit' },
                                    callback: (v) => v > 100 ? null : v
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: {
                                    font: { family: 'Outfit', weight: '400', size: 14 },
                                    autoSkip: false,
                                    maxRotation: 0,
                                    minRotation: 0
                                }
                            }
                        }
                    },
                    plugins: [{
                        id: 'customLabels',
                        afterDatasetsDraw(chart) {
                            const { ctx, data } = chart;
                            if (!ctx) return;
                            ctx.save();
                            data.datasets.forEach((dataset, datasetIndex) => {
                                if (!chart.isDatasetVisible(datasetIndex)) return;
                                const meta = chart.getDatasetMeta(datasetIndex);
                                meta.data.forEach((point, index) => {
                                    const value = dataset.data[index];
                                    if (value === null || value === undefined) return;
                                    const labelText = datasetIndex === 0 ? Math.round(value) + '%' : value;
                                    ctx.font = 'bold 14px Outfit';
                                    ctx.fillStyle = '#4e535cff';
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'bottom';
                                    if (point.x && point.y) {
                                        const offset = datasetIndex === 0 ? 10 : 4;
                                        ctx.fillText(labelText, point.x, point.y - offset);
                                    }
                                });
                            });
                            ctx.restore();
                        }
                    }]
                });
            },

            async fetchTrendData(year) {
                this.selectedYear = year;
                await this.initTrendChart();
            },

            async initTrendChart() {
                const Chart = window.Chart;
                if (!Chart) return;

                const qs = this.getFilterParams();
                const cacheBuster = new Date().getTime();
                try {
                    const r = await fetch(`${this.baseUrl}/log-data?${qs}&t=${cacheBuster}`);
                    const d = await r.json();
                    const ctx = document.getElementById('drawingTrendChart');
                    if (ctx && d.data) {
                        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        const uploads = new Array(12).fill(0);
                        const downloads = new Array(12).fill(0);

                        d.data.forEach(item => {
                            let idx = item.month - 1;
                            uploads[idx] = item.upload_count;
                            downloads[idx] = item.download_count;
                        });

                        let existTrend = Chart.getChart('drawingTrendChart');
                        if (existTrend) {
                            existTrend.data.datasets[0].data = uploads;
                            existTrend.data.datasets[1].data = downloads;
                            existTrend.update();
                            return;
                        }

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [{
                                        label: 'Uploads',
                                        data: uploads,
                                        borderColor: '#10b981',
                                        backgroundColor: '#10b981',
                                        borderWidth: 2,
                                        tension: 0.4
                                    },
                                    {
                                        label: 'Downloads',
                                        data: downloads,
                                        borderColor: '#f59e0b',
                                        backgroundColor: '#f59e0b',
                                        borderWidth: 2,
                                        tension: 0.4,
                                        animation: {
                                            delay: 200
                                        }
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: {
                                    duration: 500,
                                    easing: 'easeOutQuad'
                                },
                                animations: {
                                    y: {
                                        from: (ctx) => {
                                            if (ctx.type === 'data' && ctx.chart.chartArea) {
                                                return ctx.chart.chartArea.bottom;
                                            }
                                            return undefined;
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            padding: 20,
                                            usePointStyle: true,
                                            boxWidth: 8,
                                            font: {
                                                family: 'Outfit',
                                                size: 14
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: '#f3f4f6'
                                        },
                                        ticks: {
                                            maxTicksLimit: 4,
                                            font: {
                                                family: 'Outfit'
                                            }
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false
                                        },
                                        ticks: {
                                            font: {
                                                family: 'Outfit'
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                } catch (e) {
                    console.error("Error fetching trend data:", e);
                }
            },

            async initDrawingCharts() {
                const Chart = window.Chart;
                if (!Chart) return;
                
                const qs = this.getFilterParams();

                await Promise.all([
                    this.initTrendChart(),

                    fetch(`${this.baseUrl}/upload-phase-status?${qs}`)
                    .then(r => r.json())
                    .then(d => {
                        const ctx = document.getElementById('drawingPhaseChart');
                        if (ctx && d.data) {
                            let statusCounts = {};
                            d.data.forEach(item => {
                                statusCounts[item.project_status] = (statusCounts[item.project_status] || 0) + parseInt(item.total, 10);
                            });
                            let labels = Object.keys(statusCounts);
                            let data = Object.values(statusCounts);

                            let existPhase = Chart.getChart('drawingPhaseChart');
                            if (existPhase) {
                                existPhase.data.labels = labels;
                                existPhase.data.datasets[0].data = data;
                                existPhase.update();
                                return;
                            }

                            new Chart(ctx, {
                                type: 'pie',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        data: data,
                                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'],
                                        borderWidth: 1,
                                        borderColor: '#ffffff',
                                        animation: {
                                            duration: 1000,
                                            easing: 'easeOutQuad'
                                        }
                                    }]
                                },
                                plugins: [window.ChartDataLabels],
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    animation: {
                                        animateRotate: true,
                                        animateScale: true
                                    },
                                    plugins: {
                                        datalabels: {
                                            color: '#fff',
                                            formatter: (value, ctx) => {
                                                let sum = 0;
                                                let dataArr = ctx.chart.data.datasets[0].data;
                                                dataArr.map(data => {
                                                    sum += Number(data);
                                                });
                                                let percentageNum = Math.round((value * 100 / sum));
                                                return percentageNum > 0 ? percentageNum + "%" : null;
                                            },
                                            font: {
                                                family: 'Outfit',
                                                weight: 'bold'
                                            }
                                        },
                                        legend: {
                                            position: 'bottom',
                                            labels: {
                                                padding: 30,
                                                boxWidth: 10,
                                                usePointStyle: true,
                                                font: {
                                                    family: 'Outfit',
                                                    size: 14
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }),

                    fetch(`${this.baseUrl}/disk-space`)
                    .then(r => r.json())
                    .then(d => {
                        if (d.status === 'success') {
                            this.diskTotal = d.total || '1,000 GB';
                            this.diskUsed = d.used || '875 GB';
                        }
                    }),

                    fetch(`${this.baseUrl}/upload-monitoring-data?${qs}`)
                    .then(r => r.json())
                    .then(d => {
                        if (d.data) {
                            this.monitoringAllData = d.data || [];
                            this.monitoringPage = 0;
                            this.renderMonitoringPage();
                        }
                    })
                ]);

                // Extra small delay for layout to settle and initial animations to trigger
                await new Promise(resolve => setTimeout(resolve, 400));
            }
        }
    }