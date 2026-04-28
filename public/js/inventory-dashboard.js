window.inventoryDashboard = function() {
    const chartsMap = {};
    return {
        showInvFilter: false,
        invMonthYear: new Date().toISOString().slice(0, 7),
        invChartStore: {},
        invFiltersInitialized: false,

        kpis: [
            { id: 'total_value', label: 'Total Value', value: '0', unit: 'IDR', icon: 'fa-coins', iconBg: 'bg-primary-50 text-primary-600' },
            { id: 'total_stock', label: 'Total Stock', value: '0', unit: 'Item Part', icon: 'fa-cubes', iconBg: 'bg-slate-50 text-slate-600' },
            { id: 'material_in', label: 'In', value: '0', unit: 'Item Part', icon: 'fa-arrow-right-to-bracket', iconBg: 'bg-emerald-50 text-emerald-600' },
            { id: 'out_event', label: 'Out Event', value: '0', unit: 'Item Part', icon: 'fa-arrow-right-from-bracket', iconBg: 'bg-amber-50 text-amber-600' },
            { id: 'out_pp', label: 'Out PP', value: '0', unit: 'Item Part', icon: 'fa-industry', iconBg: 'bg-indigo-50 text-indigo-600' },
            { id: 'out_trial', label: 'Out Trial', value: '0', unit: 'Item Part', icon: 'fa-vial', iconBg: 'bg-rose-50 text-rose-600' },
        ],

        chartColors: {
            primary: '#0ea5e9', emerald: '#10b981', amber: '#f59e0b', rose: '#ef4444', indigo: '#6366f1'
        },

        commonTooltip() {
            return {
                enabled: true, usePointStyle: true,
                backgroundColor: '#ffffff', titleColor: '#1e293b', bodyColor: '#64748b',
                borderColor: '#e2e8f0', borderWidth: 1, padding: 12, displayColors: true, boxPadding: 6,
                callbacks: {
                    label: ctx => {
                        let label = ctx.dataset.label || '';
                        if (label) label += ': ';
                        if (ctx.parsed.y !== null) label += new Intl.NumberFormat().format(ctx.parsed.y) + ' Item';
                        return label;
                    }
                }
            };
        },

        async fetchInventoryData() {
            const params = new URLSearchParams({ month_year: this.invMonthYear });
            const customer = $('#invFilterCustomer').val();
            const model = $('#invFilterModel').val();
            const balance = $('#invFilterBalance').val();
            const usage = $('#invFilterUsage').val();
            if (customer) params.append('customer[]', customer);
            if (model) params.append('model[]', model);
            if (balance) params.append('status_balance[]', balance);
            if (usage) params.append('status_usage[]', usage);

            try {
                const basePath = window.APP_BASE_URL || '';
                const res = await fetch(`${basePath}/api/inventory-overview/data?${params}`);
                const data = await res.json();
                this.updateKpis(data.stats);
                this.renderCharts(data.charts);
                this.renderTables(data.tables);
                if (!this.invFiltersInitialized) { this.initInvFilters(); this.invFiltersInitialized = true; }
            } catch (e) { console.error('Inventory fetch error:', e); }
        },

        updateKpis(stats) {
            const fmt = v => new Intl.NumberFormat().format(v || 0);
            this.kpis[0].value = fmt(stats.total_stock_value);
            this.kpis[1].value = fmt(stats.total_stock);
            this.kpis[2].value = fmt(stats.material_in);
            this.kpis[3].value = fmt(stats.out_event);
            this.kpis[4].value = fmt(stats.out_pp);
            this.kpis[5].value = fmt(stats.out_trial);
        },

        renderCharts(charts) {
            const Chart = window.Chart;
            if (!Chart) return;
            const tt = this.commonTooltip();
            const commonLegend = { position: 'bottom', labels: { color: '#64748b', font: { size: 10 }, usePointStyle: true, pointStyle: 'rect', padding: 15 } };
            const commonInteraction = { mode: 'index', intersect: false };

            // Stock Status Chart
            const stockLabels = Object.keys(charts.stock_grouped).map(l => l.split('|'));
            const stockData = Object.values(charts.stock_grouped);
            this.updateInvChart('invStockChart', stockLabels,
                [stockData.map(d => d.critical), stockData.map(d => d.warning), stockData.map(d => d.over), stockData.map(d => d.safe)],
                () => {
                    chartsMap.invStockChart = new Chart(document.getElementById('invStockChart'), {
                        type: 'bar',
                        data: { labels: [], datasets: [
                            { label: 'Critical', data: [], backgroundColor: this.chartColors.rose, borderRadius: 2 },
                            { label: 'Warning', data: [], backgroundColor: this.chartColors.amber, borderRadius: 2 },
                            { label: 'Over', data: [], backgroundColor: this.chartColors.primary, borderRadius: 2 },
                            { label: 'Safe', data: [], backgroundColor: this.chartColors.emerald, borderRadius: 2 }
                        ]},
                        options: {
                            onClick: (e, el) => { if (el.length) { const i = el[0].index; const di = el[0].datasetIndex; const l = chartsMap.invStockChart.data.labels[i]; const ls = Array.isArray(l) ? l.join('|') : l; const st = chartsMap.invStockChart.data.datasets[di].label; openDrilldownModal('stock', ls, st); }},
                            onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                            interaction: commonInteraction, responsive: true, maintainAspectRatio: false,
                            scales: { x: { stacked: true, ticks: { font: { size: 11 } } }, y: { stacked: true, beginAtZero: true, ticks: { font: { size: 11 } } } },
                            plugins: { tooltip: tt, legend: commonLegend }
                        }
                    });
                    return chartsMap.invStockChart;
                }
            );

            // Usage by Model Chart
            const usageLabels = charts.usage_model.map(i => i.label.split('|'));
            this.updateInvChart('invUsageModelChart', usageLabels,
                [charts.usage_model.map(i => i.event), charts.usage_model.map(i => i.pp), charts.usage_model.map(i => i.trial)],
                () => {
                    chartsMap.invUsageModelChart = new Chart(document.getElementById('invUsageModelChart'), {
                        type: 'bar',
                        data: { labels: [], datasets: [
                            { label: 'Event', data: [], backgroundColor: this.chartColors.amber, borderRadius: 2 },
                            { label: 'PP', data: [], backgroundColor: this.chartColors.indigo, borderRadius: 2 },
                            { label: 'Trial', data: [], backgroundColor: this.chartColors.rose, borderRadius: 2 }
                        ]},
                        options: {
                            onClick: (e, el) => { if (el.length) { const i = el[0].index; const di = el[0].datasetIndex; const l = chartsMap.invUsageModelChart.data.labels[i]; const ls = Array.isArray(l) ? l.join('|') : l; const map = { 'Event': 'OUT-EVENT', 'PP': 'OUT-PP', 'Trial': 'OUT-TRIAL' }; openDrilldownModal('usage_model', ls, map[chartsMap.invUsageModelChart.data.datasets[di].label]); }},
                            onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                            interaction: commonInteraction, responsive: true, maintainAspectRatio: false,
                            scales: { x: { stacked: true, ticks: { font: { size: 11 } } }, y: { stacked: true, ticks: { font: { size: 11 } } } },
                            plugins: { tooltip: tt, legend: commonLegend }
                        }
                    });
                    return chartsMap.invUsageModelChart;
                }
            );

            // Maker Chart
            this.updateInvChart('invMakerChart', charts.maker.map(i => i.maker),
                [charts.maker.map(i => i.on_budget), charts.maker.map(i => i.near_loss), charts.maker.map(i => i.loss)],
                () => {
                    chartsMap.invMakerChart = new Chart(document.getElementById('invMakerChart'), {
                        type: 'bar',
                        data: { labels: [], datasets: [
                            { label: 'On Budget', data: [], backgroundColor: this.chartColors.emerald, borderRadius: 2 },
                            { label: 'Near Loss', data: [], backgroundColor: this.chartColors.amber, borderRadius: 2 },
                            { label: 'Loss', data: [], backgroundColor: this.chartColors.rose, borderRadius: 2 }
                        ]},
                        options: {
                            onClick: (e, el) => { if (el.length) { const i = el[0].index; const l = chartsMap.invMakerChart.data.labels[i]; const ls = Array.isArray(l) ? l.join('|') : l; const st = chartsMap.invMakerChart.data.datasets[el[0].datasetIndex].label; openDrilldownModal('maker', ls, st); }},
                            onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                            interaction: commonInteraction, responsive: true, maintainAspectRatio: false,
                            scales: { x: { stacked: true, ticks: { font: { size: 11 } } }, y: { stacked: true, ticks: { font: { size: 11 } } } },
                            plugins: { tooltip: tt, legend: commonLegend }
                        }
                    });
                    return chartsMap.invMakerChart;
                }
            );

            // Trendline Chart
            const trendData = charts.trendline || [];
            const dates = [...new Set(trendData.map(d => d.transaction_date))];
            const cats = [...new Set(trendData.map(d => d.category))];
            const colorKeys = [this.chartColors.primary, this.chartColors.emerald, this.chartColors.amber, this.chartColors.rose, this.chartColors.indigo];

            if (chartsMap.invTrendlineChart) {
                chartsMap.invTrendlineChart.data.labels = dates;
                chartsMap.invTrendlineChart.data.datasets = cats.map((cat, idx) => ({
                    label: cat.replace('OUT-', '').split(' ').map(w => w === 'PP' ? w : w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' '),
                    data: dates.map(d => (trendData.find(td => td.transaction_date === d && td.category === cat) || { total: 0 }).total),
                    borderColor: colorKeys[idx % colorKeys.length], backgroundColor: colorKeys[idx % colorKeys.length] + '1A',
                    fill: false, tension: 0.5, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2
                }));
                chartsMap.invTrendlineChart.update();
            } else if (document.getElementById('invTrendlineChart')) {
                chartsMap.invTrendlineChart = new Chart(document.getElementById('invTrendlineChart'), {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: cats.map((cat, idx) => ({
                            label: cat.replace('OUT-', '').split(' ').map(w => w === 'PP' ? w : w.charAt(0).toUpperCase() + w.slice(1).toLowerCase()).join(' '),
                            data: dates.map(d => (trendData.find(td => td.transaction_date === d && td.category === cat) || { total: 0 }).total),
                            borderColor: colorKeys[idx % colorKeys.length], backgroundColor: colorKeys[idx % colorKeys.length] + '1A',
                            fill: false, tension: 0.5, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2
                        }))
                    },
                    options: {
                        onClick: (e, el) => { if (el.length) { const i = el[0].index; const l = chartsMap.invTrendlineChart.data.labels[i]; const cat = chartsMap.invTrendlineChart.data.datasets[el[0].datasetIndex].label; const map = { 'In': 'IN', 'Event': 'OUT-EVENT', 'PP': 'OUT-PP', 'Trial': 'OUT-TRIAL' }; openDrilldownModal('trendline', l, map[cat] || cat); }},
                        onHover: (e, el) => { e.native.target.style.cursor = el[0] ? 'pointer' : 'default'; },
                        interaction: commonInteraction, responsive: true, maintainAspectRatio: false,
                        scales: {
                            x: { ticks: { font: { size: 11 } }, grid: { color: 'rgba(226, 232, 240, 0.6)' } },
                            y: { beginAtZero: true, ticks: { font: { size: 11 } }, grid: { color: 'rgba(226, 232, 240, 0.6)' } }
                        },
                        plugins: { tooltip: tt, legend: commonLegend }
                    }
                });
            }
        },

        updateInvChart(id, labels, datasets, createFn) {
            const chartMap = { invStockChart: 'invStockChart', invUsageModelChart: 'invUsageModelChart', invMakerChart: 'invMakerChart' };
            let chart = chartsMap[id];
            if (!chart && document.getElementById(id)) { chart = createFn(); }
            if (!chart) return;

            this.invChartStore[id] = this.invChartStore[id] || { page: 0, pageSize: 6 };
            this.invChartStore[id].labels = labels;
            this.invChartStore[id].datasets = datasets;
            this.invChartStore[id].page = 0;
            this.renderInvChartPage(id);
        },

        renderInvChartPage(id) {
            const store = this.invChartStore[id];
            const chart = chartsMap[id];
            if (!chart || !store) return;
            const start = store.page * store.pageSize;
            const end = start + store.pageSize;
            chart.data.labels = store.labels.slice(start, end);
            store.datasets.forEach((data, i) => { if (chart.data.datasets[i]) chart.data.datasets[i].data = data.slice(start, end); });
            chart.update();

            const btnPrev = (id === 'invUsageModelChart' || id === 'invMakerChart') ? 'invUsageChartPrev' : id + 'Prev';
            const btnNext = (id === 'invUsageModelChart' || id === 'invMakerChart') ? 'invUsageChartNext' : id + 'Next';
            const prev = document.getElementById(btnPrev); if (prev) prev.disabled = store.page <= 0;
            const next = document.getElementById(btnNext); if (next) next.disabled = end >= store.labels.length;
        },

        paginateInvChart(id, dir) {
            if (this.invChartStore[id]) { this.invChartStore[id].page += dir; this.renderInvChartPage(id); }
        },

        paginateInvActiveUsage(dir) {
            const activeId = document.getElementById('invContainerUsageModel').classList.contains('hidden') ? 'invMakerChart' : 'invUsageModelChart';
            this.paginateInvChart(activeId, dir);
        },

        switchInvUsageChart(type) {
            const isModel = type === 'model';
            $('#invUsageChartTitle').html('<span class="truncate">' + (isModel ? 'Usage by Models' : 'Supply by Makers') + '</span><span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 text-[8px] font-black text-slate-500 uppercase tracking-widest border border-slate-200/50 flex-shrink-0 whitespace-nowrap">Item Part</span>');
            $('#invContainerUsageModel').toggleClass('hidden', !isModel);
            $('#invContainerUsageMaker').toggleClass('hidden', isModel);
            $('#btnInvUsageModel').toggleClass('bg-white text-primary-600 shadow-sm', isModel).toggleClass('text-gray-500 hover:text-gray-700', !isModel);
            $('#btnInvUsageMaker').toggleClass('bg-white text-primary-600 shadow-sm', !isModel).toggleClass('text-gray-500 hover:text-gray-700', isModel);
            const activeId = isModel ? 'invUsageModelChart' : 'invMakerChart';
            if (this.invChartStore[activeId]) {
                const store = this.invChartStore[activeId];
                const end = store.page * store.pageSize + store.pageSize;
                $('#invUsageChartPrev').prop('disabled', store.page <= 0);
                $('#invUsageChartNext').prop('disabled', end >= store.labels.length);
            }
        },

        renderTables(tables) {
            const statusColors = {
                'Critical': 'bg-red-50 text-red-600 border-red-100', 'Warning': 'bg-amber-50 text-amber-600 border-amber-100',
                'Over': 'bg-primary-50 text-primary-600 border-primary-100', 'Safe': 'bg-emerald-50 text-emerald-600 border-emerald-100'
            };
            // Balance table
            const balanceTbody = $('#invBalanceTableBody');
            balanceTbody.empty();
            if (!tables.balance || tables.balance.length === 0) {
                balanceTbody.html('<tr><td colspan="4" class="p-8 text-center text-slate-400 italic text-[11px]">All items are within safe limits.</td></tr>');
            } else {
                tables.balance.slice(0, 15).forEach(row => {
                    const cc = statusColors[row.status] || statusColors['Safe'];
                    balanceTbody.append(`<tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-1.5 px-3"><p class="text-[11px] font-medium text-slate-700 tracking-tight leading-tight uppercase">${row.part_no} ${row.revision ? '- ' + row.revision : ''}</p><p class="text-[9px] text-slate-400 uppercase tracking-tighter">${row.model_name || '-'} | ${row.customer_code || '-'}</p></td>
                        <td class="py-1.5 px-2 text-right"><div class="text-[11px] font-medium text-slate-500 font-mono">${new Intl.NumberFormat().format(row.min_stock)}</div></td>
                        <td class="py-1.5 px-2 text-right"><div class="text-[11px] font-medium text-slate-800 font-mono">${new Intl.NumberFormat().format(row.current_stock_qty)}</div></td>
                        <td class="py-1.5 px-3 text-right"><span class="inline-flex px-1.5 py-0.5 rounded-xs text-[9px] font-medium ${cc} border uppercase leading-none">${row.status}</span></td>
                    </tr>`);
                });
            }

            // Usage table
            const usageTbody = $('#invUsageTableBody');
            usageTbody.empty();
            if (!tables.usage || tables.usage.length === 0) {
                usageTbody.html('<tr><td colspan="5" class="p-8 text-center text-slate-400 italic text-[11px]">No trial data available.</td></tr>');
            } else {
                tables.usage.slice(0, 15).forEach(row => {
                    const usageStatusColors = {
                        'Loss': 'bg-red-50 text-red-600 border-red-100',
                        'Near Loss': 'bg-amber-50 text-amber-600 border-amber-100',
                        'On Budget': 'bg-emerald-50 text-emerald-600 border-emerald-100'
                    };
                    const usc = usageStatusColors[row.status] || 'bg-slate-50 text-slate-600 border-slate-100';
                    usageTbody.append(`<tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-1.5 px-3 text-[11px] font-medium text-slate-700 uppercase tracking-tight">${row.part_no}</td>
                        <td class="py-1.5 px-2 text-[10px] text-slate-500 uppercase truncate max-w-[80px]">${row.supplier_name || '-'}</td>
                        <td class="py-1.5 px-2 text-[11px] font-medium text-slate-800 text-right font-mono">${new Intl.NumberFormat().format(row.out_trial)}</td>
                        <td class="py-1.5 px-2 text-[11px] font-medium ${row.gap < 0 ? 'text-red-500' : 'text-emerald-500'} text-right font-mono">${new Intl.NumberFormat().format(row.gap)}</td>
                        <td class="py-1.5 px-3 text-right"><span class="inline-flex px-1.5 py-0.5 rounded-xs text-[9px] font-medium ${usc} border uppercase leading-none">${row.status}</span></td>
                    </tr>`);
                });
            }

            // History table
            const historyTbody = $('#invHistoryTableBody');
            historyTbody.empty();
            if (!tables.history || tables.history.length === 0) {
                historyTbody.html('<tr><td colspan="4" class="p-8 text-center text-slate-400 italic text-[11px]">No recent activity.</td></tr>');
            } else {
                tables.history.slice(0, 15).forEach(row => {
                    const date = new Date(row.transaction_date);
                    const dateStr = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: '2-digit' });
                    const createdAt = new Date(row.created_at);
                    const timeStr = createdAt.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
                    historyTbody.append(`<tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="py-1.5 px-3"><p class="text-[11px] font-medium text-slate-700 tracking-tight leading-tight uppercase">${row.part_no} ${row.revision ? '- ' + row.revision : ''}</p><p class="text-[9px] text-slate-400 uppercase tracking-tighter">${row.model_name || '-'} | ${row.customer_code || '-'}</p></td>
                        <td class="py-1.5 px-2 text-center"><span class="px-1.5 py-0.5 rounded-xs text-[9px] font-medium bg-slate-100 text-slate-600 uppercase">${row.category}</span></td>
                        <td class="py-1.5 px-2 text-center whitespace-nowrap"><div class="text-[10px] text-slate-500">${dateStr} <span class="text-[9px] text-slate-400 font-mono ml-1">${timeStr}</span></div></td>
                        <td class="py-1.5 px-3 text-right"><div class="text-[11px] font-medium text-slate-800 font-mono">${new Intl.NumberFormat().format(row.qty_pcs)}</div></td>
                    </tr>`);
                });
            }
        },

        initInvFilters() {
            const self = this;
            const basePath = window.APP_BASE_URL || '';
            const baseUrl = `${basePath}/api/inventory-overview`;
            

            $('#invFilterModel').select2({
                dropdownParent: $('#invFilterModel').parent(), width: '100%', placeholder: 'All Models', allowClear: true,
                ajax: {
                    url: baseUrl + '/models', method: 'POST', dataType: 'json', delay: 250,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: p => ({ term: p.term, page: p.page, customer_id: $('#invFilterCustomer').val() }),
                    processResults: (data, p) => {
                        const results = data.results || [];
                        if ((!p.term || p.term === '') && (!p.page || p.page === 1)) results.unshift({ id: '', text: 'All Models' });
                        return { results, pagination: data.pagination };
                    }, cache: true
                }
            });

            $('#invFilterCustomer').select2({
                dropdownParent: $('#invFilterCustomer').parent(), width: '100%', placeholder: 'All Customers', allowClear: true,
                ajax: {
                    url: baseUrl + '/customers', method: 'POST', dataType: 'json', delay: 250,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: p => ({ term: p.term, page: p.page }),
                    processResults: (data, p) => {
                        const results = data.results || [];
                        if ((!p.term || p.term === '') && (!p.page || p.page === 1)) results.unshift({ id: '', text: 'All Customers' });
                        return { results, pagination: data.pagination };
                    }, cache: true
                }
            });

            $('#invFilterBalance').select2({
                dropdownParent: $('#invFilterBalance').parent(), width: '100%', placeholder: 'All Balance Status', allowClear: true,
                ajax: {
                    url: baseUrl + '/statuses/balance', method: 'GET', dataType: 'json',
                    processResults: data => ({ results: [{ id: '', text: 'All' }].concat(data.results || []) })
                }
            });

            $('#invFilterUsage').select2({
                dropdownParent: $('#invFilterUsage').parent(), width: '100%', placeholder: 'All Usage Status', allowClear: true,
                ajax: {
                    url: baseUrl + '/statuses/usage', method: 'GET', dataType: 'json',
                    processResults: data => ({ results: [{ id: '', text: 'All' }].concat(data.results || []) })
                }
            });

            $('#invFilterCustomer, #invFilterModel, #invFilterBalance, #invFilterUsage').on('change', () => self.fetchInventoryData());
        },

        resetInvFilters() {
            this.invMonthYear = new Date().toISOString().slice(0, 7);
            $('#invFilterCustomer').val(null).trigger('change.select2');
            $('#invFilterModel').val(null).trigger('change.select2');
            $('#invFilterBalance').val(null).trigger('change.select2');
            $('#invFilterUsage').val(null).trigger('change.select2');
            this.fetchInventoryData();
        }
    };
};
