@extends('layouts.app')

@section('content')

<!-- Main Content -->
<main class="p-2 px-8 bg-gray-100 flex flex-col lg:overflow-hidden lg:h-[calc(100vh-4rem)] min-h-[calc(100vh-4rem)]"
    @mousemove.window="resetIdleTimer()"
    @keypress.window="resetIdleTimer()"
    @touchstart.window="resetIdleTimer()"
    @scroll.window="resetIdleTimer()"
    @click.window="resetIdleTimer()"
    @prev-section.window="toggleSection()"
    @next-section.window="toggleSection()"
    x-data="{ 
            activeSection: 'drawing',
            idleTimeout: null,
            screensaverInterval: null,
            isScreensaverPaused: false,
            
            init() { 
                this.resetIdleTimer();
                this.$watch('isScreensaverPaused', value => {
                    if (value) {
                        if (this.screensaverInterval) { clearInterval(this.screensaverInterval); this.screensaverInterval = null; }
                        if (this.idleTimeout) { clearTimeout(this.idleTimeout); this.idleTimeout = null; }
                    } else {
                        this.resetIdleTimer();
                    }
                });
            },
            
            resetIdleTimer() {
                if (this.screensaverInterval) {
                    clearInterval(this.screensaverInterval);
                    this.screensaverInterval = null;
                }
                if (this.idleTimeout) {
                    clearTimeout(this.idleTimeout);
                }
                if (!this.isScreensaverPaused) {
                    this.idleTimeout = setTimeout(() => {
                        this.startScreensaver();
                    }, 5000); // Restored to 5s per user request
                }
            },
            
            startScreensaver() {
                if (this.isScreensaverPaused) return;
                this.toggleSection();
                this.screensaverInterval = setInterval(() => {
                    if (!this.isScreensaverPaused) {
                        this.toggleSection();
                    } else {
                        clearInterval(this.screensaverInterval);
                        this.screensaverInterval = null;
                    }
                }, 5000);
            },
            
            toggleSection() {
                this.activeSection = this.activeSection === 'drawing' ? 'inventory' : 'drawing';
            }
        }">
    <div class="relative flex-1 w-full min-h-0 flex flex-col"
        x-data="drawingDashboardData()" x-init="loadFilterOptions().then(() => fetchData())"
        @open-filter-modal.window="if(activeSection === 'drawing') showFilterModal = true">
        
        <!-- Filter Modal -->
        <div x-show="showFilterModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-transition.opacity>
                <div @click.away="showFilterModal = false" class="bg-white shadow-xl flex flex-col overflow-visible" style="width: 70vw; min-height: 50vh;">
                    <div class="flex items-center justify-between px-10 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            Filter Data
                        </h3>
                        <button @click="showFilterModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                    <div class="px-10 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-start">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Date End</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fa-solid fa-calendar-days text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="date" id="filter_date_end" style="height: 2.375rem; border: 1px solid #d1d5db; border-radius: 1px; font-size: 0.875rem; color: #3f3f3f;" class="block w-full focus:ring-0 focus:outline-none py-1.5 pl-9 pr-3">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Customer</label>
                                <div class="relative">
                                    <select id="filter_customer" class="w-full text-xs"></select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Model</label>
                                <div class="relative">
                                    <select id="filter_model" class="w-full text-xs"></select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Part Group</label>
                                <div class="relative">
                                    <select id="filter_part_group" class="w-full text-xs"></select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Status</label>
                                <div class="relative">
                                    <select id="filter_status" class="w-full text-xs"></select>
                                </div>
                            </div>
                        </div>
                        <div class="w-full flex justify-between items-center mt-3 pt-2 border-t border-gray-200">
                            <div id="filterPillContainer" class="flex-grow pr-6 text-xs flex flex-wrap gap-1"></div>
                            <div class="flex space-x-2">
                                <button type="button" @click="isScreensaverPaused = !isScreensaverPaused" class="px-3 py-1.5 text-xs font-medium border border-gray-200 hover:bg-gray-50 flex items-center justify-center min-w-[140px]" :class="isScreensaverPaused ? 'bg-orange-50 text-orange-600 border-orange-200 hover:bg-orange-100' : 'text-gray-700'">
                                    <i class="fa-solid" :class="isScreensaverPaused ? 'fa-play mr-2' : 'fa-pause mr-2'"></i> 
                                    <span x-text="isScreensaverPaused ? 'Resume Screensaver' : 'Pause Screensaver'"></span>
                                </button>
                                <button type="button" @click="resetFilters()" class="px-3 py-1.5 text-xs font-medium border border-gray-200 hover:bg-gray-50 text-gray-700">Reset</button>
                                <button type="button" @click="applyFilters()" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600  hover:bg-blue-700 min-w-[120px]">
                                    <i class="fa-solid fa-check mr-2"></i> Apply Filter
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @include('components.dashboard-drawing')
        @include('components.dashboard-inventory')

    </div> <!-- End of Grid Wrapper -->
</main>
@endsection


@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>window.APP_BASE_URL = "{{ url('') }}";</script>
<script src="{{ asset('js/inv-material-dashboard.js') }}"></script>

<script src="{{ asset('js/drawing-dashboard.js') }}"></script>
{{-- Drilldown Modal --}}
<div id="drilldownModal" class="fixed inset-0 z-50 hidden" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" onclick="closeDrilldownModal()"></div>
    <div class="absolute right-0 top-0 bottom-0 w-full max-w-lg bg-white shadow-2xl flex flex-col transform transition-transform duration-300 translate-x-full" id="drilldownPanel">
        {{-- Header --}}
        <div class="flex-none flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-gray-50">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <p class="text-[12px] font-semibold text-gray-500 tracking-widest">Detail Explorer</p>
                    <span id="drilldownCountBadge" class="px-1.5 py-0.5 bg-primary-100 text-primary-600 text-[10px] font-normal">0</span>
                </div>
                <h2 id="drilldownTitle" class="text-base font-bold text-gray-800 truncate max-w-[300px]">Loading...</h2>
            </div>
            <button onclick="closeDrilldownModal()" class="w-8 h-8 flex items-center justify-center border border-gray-200 bg-gray-200 hover:bg-gray-300 text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
        {{-- Loader --}}
        <div id="drilldownLoader" class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary-400 mb-3"></i>
                <p class="text-[10px] text-slate-400 tracking-wider font-semibold">Fetching data...</p>
            </div>
        </div>
        {{-- Content --}}
        <div id="drilldownContent" class="flex-1 flex-col hidden min-h-0">
            {{-- Quick Filters (Segmented Control Style) --}}
            <div id="drilldownLegendContainer" class="px-5 py-3 border-b border-gray-100 bg-gray-50/30">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[11px] font-semibold text-gray-600 tracking-widest">Filter by Status</p>
                </div>
                <div id="drilldownLegendButtons" class="inline-flex p-1 bg-gray-100 gap-1">
                    {{-- Buttons injected by JS --}}
                </div>
            </div>

            <div class="px-5 py-3 border-b border-gray-100 bg-white sticky top-0 z-20 flex flex-col md:flex-row gap-3 items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-semibold text-slate-400 whitespace-nowrap tracking-wider">Show</span>
                    <select id="drilldownPageSize" onchange="resetDrilldownAndFetch()" class="h-8 bg-gray-50 border border-gray-200 text-[11px] px-2 focus:ring-1 focus:ring-primary-500 outline-none cursor-pointer">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-[9px] font-semibold text-slate-400 whitespace-nowrap tracking-wider">entries</span>
                </div>
                <div class="relative w-full md:w-60">
                    <input type="text" id="drilldownSearch" placeholder="Search Part No..." class="w-full h-8 pl-9 pr-4 bg-gray-50 border border-gray-200 text-[11px] focus:outline-none transition-all placeholder:text-gray-400">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-[9px]"></i>
                    </div>
                </div>
            </div>
            <div class="flex-1 relative min-h-0">
                {{-- Partial Table Loader --}}
                <div id="drilldownTableLoader" class="hidden absolute inset-0 bg-white/60 z-30 flex items-center justify-center transition-all">
                    <div class="flex flex-col items-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-xl text-primary-500 mb-2"></i>
                        <span class="text-[10px] font-medium text-slate-500 tracking-wider">Updating...</span>
                    </div>
                </div>

                <div class="h-full overflow-y-auto custom-scrollbar">
                    <table class="w-full text-left text-[11px]">
                        <thead id="drilldownHead" class="bg-gray-50 sticky top-0 z-10">
                        </thead>
                        <tbody id="drilldownBody" class="divide-y divide-slate-100">
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- Pagination Footer --}}
            <div class="flex-none px-5 py-3 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
                <div class="text-[10px] text-slate-500">
                    Showing <span id="ddPageStart">0</span>-<span id="ddPageEnd">0</span> of <span id="ddTotal">0</span>
                </div>
                <div class="flex items-center gap-1">
                    <button onclick="changeDrilldownPage(-1)" id="ddPrev" class="w-7 h-7 flex items-center justify-center bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <div class="px-2 text-[10px] font-bold text-slate-600">
                        Page <span id="ddCurrentPage">1</span>
                    </div>
                    <button onclick="changeDrilldownPage(1)" id="ddNext" class="w-7 h-7 flex items-center justify-center bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>

    const drilldownUrl = "{{ url('api/inventory-overview/drilldown') }}";
    const DRILLDOWN_COLS = {
        stock: [
            { key: 'part_no',   label: 'Part No',      cls: 'text-left py-2 px-3' },
            { key: 'stock',     label: 'Stock',         cls: 'text-right py-2 px-2' },
            { key: 'min_stock', label: 'Min',           cls: 'text-right py-2 px-2' },
            { key: 'unit',      label: 'Unit',          cls: 'text-center py-2 px-2' },
            { key: 'status',    label: 'Status',        cls: 'text-center py-2 px-3' },
        ],
        usage_model: [
            { key: 'part_no',   label: 'Part No',       cls: 'text-left py-2 px-3' },
            { key: 'category',  label: 'Category',      cls: 'text-center py-2 px-2' },
            { key: 'qty_pcs',   label: 'Qty (pcs)',     cls: 'text-right py-2 px-2' },
            { key: 'date',      label: 'Date',          cls: 'text-center py-2 px-3' },
        ],
        maker: [
            { key: 'part_no', label: 'Part Number', cls: 'py-2 px-3' },
            { key: 'model', label: 'Model', cls: 'py-2 px-2' },
            { key: 'rank', label: 'Rank', cls: 'py-2 px-2 text-center' },
            { key: 'usage', label: 'Usage (PCS)', cls: 'py-2 px-2 text-right' },
            { key: 'gap', label: 'Gap', cls: 'py-2 px-2 text-right' },
            { key: 'status', label: 'Status', cls: 'py-2 px-3 text-right' }
        ],
        trendline: [
            { key: 'part_no', label: 'Part Number', cls: 'py-2 px-3' },
            { key: 'category', label: 'Category', cls: 'py-2 px-2 text-center' },
            { key: 'qty_pcs', label: 'Quantity (PCS)', cls: 'py-2 px-3 text-right' },
            { key: 'date', label: 'Date', cls: 'py-2 px-3 text-right' }
        ]
    };

    const STATUS_BADGE = {
        'Critical':   'bg-rose-50 text-rose-700 border border-rose-200',
        'Warning':    'bg-amber-50 text-amber-700 border border-amber-200',
        'Over':       'bg-blue-50 text-blue-700 border border-blue-200',
        'Safe':       'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'Loss':       'bg-rose-50 text-rose-700 border border-rose-200',
        'Near Loss':  'bg-amber-50 text-amber-700 border border-amber-200',
        'On Budget':  'bg-emerald-50 text-emerald-700 border border-emerald-200',
        'OUT-EVENT':  'bg-amber-50 text-amber-700 border border-amber-200',
        'OUT-PP':     'bg-indigo-50 text-indigo-700 border border-indigo-200',
        'OUT-TRIAL':  'bg-rose-50 text-rose-700 border border-rose-200',
        'IN':         'bg-emerald-50 text-emerald-700 border border-emerald-200',
    };

    let drilldownPage = 1;
    let drilldownCurrentType = '';
    let drilldownCurrentLabel = '';
    let drilldownCurrentStatus = '';
    let searchDebounceTimer;

    window.openDrilldownModal = function(chartType, label, status = null) {
        drilldownCurrentType = chartType;
        drilldownCurrentLabel = label;
        drilldownCurrentStatus = status || '';
        drilldownPage = 1;

        const modal  = document.getElementById('drilldownModal');
        const panel  = document.getElementById('drilldownPanel');
        const searchInput = document.getElementById('drilldownSearch');

        if(searchInput) searchInput.value = '';

        modal.classList.remove('hidden');
        requestAnimationFrame(() => panel.classList.remove('translate-x-full'));
        
        document.getElementById('drilldownTitle').textContent = 'Loading...';
        document.getElementById('drilldownCountBadge').textContent = '0';

        renderDrilldownLegend(chartType, drilldownCurrentStatus);
        fetchDrilldownData(true);
    };

    function fetchDrilldownData(isInitial = false) {
        const my = document.getElementById('inv_month_picker')?.value || new Date().toISOString().slice(0, 7);
        const loader = document.getElementById('drilldownLoader');
        const tableLoader = document.getElementById('drilldownTableLoader');
        const content = document.getElementById('drilldownContent');
        const search = document.getElementById('drilldownSearch').value;
        const pageSize = document.getElementById('drilldownPageSize').value;
        
        if (isInitial) {
            loader.classList.remove('hidden');
            content.classList.add('hidden');
            content.classList.remove('flex');
        } else {
            tableLoader.classList.remove('hidden');
        }

        $.get(drilldownUrl, { 
            chart: drilldownCurrentType, 
            label: drilldownCurrentLabel, 
            status: drilldownCurrentStatus, 
            month_year: my,
            search: search,
            page: drilldownPage,
            pageSize: pageSize
        })
        .done(function(res) {
            document.getElementById('drilldownTitle').textContent = res.title;
            const cols = DRILLDOWN_COLS[res.chart] || [];
            const tbody = document.getElementById('drilldownBody');
            
            // Header
            document.getElementById('drilldownHead').innerHTML = '<tr>' + cols.map(c =>
                `<th class="${c.cls} text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100">${c.label}</th>`
            ).join('') + '</tr>';

            // Body
            if (!res.data || res.data.length === 0) {
                tbody.innerHTML = `<tr><td colspan="${cols.length}" class="py-10 text-center text-slate-400 italic text-[11px]">No data found.</td></tr>`;
            } else {
                tbody.innerHTML = res.data.map(row => {
                    return '<tr class="hover:bg-slate-50 transition-colors border-b border-gray-50">' + cols.map(c => {
                        const val = row[c.key] ?? '-';
                        const badgeCls = (c.key === 'status' || c.key === 'category') ? STATUS_BADGE[val] : null;
                        const cell = badgeCls
                            ? `<span class="inline-flex items-center justify-center w-16 py-0.5 text-[11px] font-bold ${badgeCls}">${val}</span>`
                            : `<span class="${c.key === 'part_no' ? 'font-medium text-slate-700' : 'text-slate-500'}">${val}</span>`;
                        return `<td class="${c.cls}">${cell}</td>`;
                    }).join('') + '</tr>';
                }).join('');
            }

            // Pagination Stats
            const total = res.total;
            const start = (drilldownPage - 1) * pageSize + 1;
            const end = Math.min(drilldownPage * pageSize, total);
            
            document.getElementById('drilldownCountBadge').textContent = total;
            document.getElementById('ddTotal').textContent = total;
            document.getElementById('ddPageStart').textContent = total === 0 ? 0 : start;
            document.getElementById('ddPageEnd').textContent = end;
            document.getElementById('ddCurrentPage').textContent = drilldownPage;
            
            document.getElementById('ddPrev').disabled = drilldownPage <= 1;
            document.getElementById('ddNext').disabled = end >= total;

            if (isInitial) {
                loader.classList.add('hidden');
                content.classList.remove('hidden');
                content.classList.add('flex');
                content.style.flexDirection = 'column';
            } else {
                tableLoader.classList.add('hidden');
            }
        });
    }

    window.resetDrilldownAndFetch = function() {
        drilldownPage = 1;
        fetchDrilldownData();
    };

    window.changeDrilldownPage = function(dir) {
        drilldownPage += dir;
        fetchDrilldownData();
        document.querySelector('#drilldownContent .overflow-y-auto').scrollTop = 0;
    };

    function renderDrilldownLegend(type, activeStatus) {
        const container = document.getElementById('drilldownLegendButtons');
        container.innerHTML = '';
        
        const legends = {
            'stock': ['Critical', 'Warning', 'Over', 'Safe'],
            'usage_model': ['OUT-EVENT', 'OUT-PP', 'OUT-TRIAL'],
            'maker': ['On Budget', 'Near Loss', 'Loss'],
            'trendline': ['IN', 'OUT-EVENT', 'OUT-PP', 'OUT-TRIAL']
        };

        const currentLegends = legends[type] || [];
        
        const allBtn = createLegendBtn('All', activeStatus === '');
        allBtn.onclick = () => { drilldownCurrentStatus = ''; drilldownPage = 1; updateLegendActive(allBtn); fetchDrilldownData(); };
        container.appendChild(allBtn);

        currentLegends.forEach(leg => {
            const isActive = leg === activeStatus;
            const btn = createLegendBtn(leg.replace('OUT-', ''), isActive);
            btn.onclick = () => { drilldownCurrentStatus = leg; drilldownPage = 1; updateLegendActive(btn); fetchDrilldownData(); };
            container.appendChild(btn);
        });
    }

    function createLegendBtn(label, isActive) {
        const btn = document.createElement('button');
        btn.className = `legend-btn px-4 py-1.5 text-[10px] font-bold transition-all duration-200 ${
            isActive 
            ? 'bg-white text-primary-600' 
            : 'text-slate-500 hover:text-slate-700'
        }`;
        btn.textContent = label;
        return btn;
    }

    function updateLegendActive(activeBtn) {
        $(activeBtn).siblings().removeClass('bg-white text-primary-600 shadow-sm')
            .addClass('text-slate-500');
        $(activeBtn).removeClass('text-slate-500')
            .addClass('bg-white text-primary-600 shadow-sm');
    }

    document.addEventListener('DOMContentLoaded', () => {
        $('#drilldownSearch').on('input', function() {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                resetDrilldownAndFetch();
            }, 400);
        });
    });

    window.closeDrilldownModal = function() {
        const panel = document.getElementById('drilldownPanel');
        panel.classList.add('translate-x-full');
        setTimeout(() => document.getElementById('drilldownModal').classList.add('hidden'), 300);
    };

    // Close on Escape key
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrilldownModal(); });
</script>
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard-custom.css') }}">
@endpush

