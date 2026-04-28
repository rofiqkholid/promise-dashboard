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
        @open-filter-modal.window="showFilterModal = true">
        
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

        <!-- Drawing Wrapper -->
        <div class="lg:absolute inset-0 flex flex-col min-h-0 w-full" x-show="activeSection === 'drawing'" x-transition.opacity.duration.700ms>

            <!-- Top Row: Title & 5 KPI Cards -->
            <div class="flex-none grid grid-cols-1 lg:grid-cols-3 gap-3 mb-3">
                
                <!-- Left Group: Title, Total, Upload, Download (Matches Upload Monitoring) -->
                <div class="lg:col-span-2 flex flex-col xl:flex-row gap-3 xl:items-center">
                    <!-- Title Block -->
                    <div class="flex flex-col justify-center px-2 w-full xl:w-auto flex-shrink-0 mr-2">
                        <h2 class="text-lg xl:text-xl font-bold text-gray-800 leading-none mb-1 whitespace-nowrap">Overview Drawing</h2>
                        <p class="text-[11px] xl:text-xs text-gray-500 leading-tight whitespace-nowrap">A quick glimpse of your drawing metrics.</p>
                    </div>
                    
                    <div class="flex-1 bg-white p-3 flex items-center border border-gray-200 min-w-0">
                        <div class="bg-blue-50 text-blue-500 p-2 rounded mr-3 flex-shrink-0">
                            <i class="fa-solid fa-file-lines text-lg mx-1"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-500 font-bold mb-0.5 tracking-wide truncate">Total Files</p>
                            <h3 class="text-xl font-bold text-gray-800 leading-tight" x-text="documents.toLocaleString('id-ID')">0</h3>
                        </div>
                    </div>
                    
                    <div class="flex-1 bg-white p-3 flex items-center border border-gray-200 min-w-0">
                        <div class="bg-green-50 text-green-500 p-2 rounded mr-3 flex-shrink-0">
                            <i class="fa-solid fa-cloud-arrow-up text-lg mx-0.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-500 font-bold mb-0.5 tracking-wide truncate">Upload</p>
                            <h3 class="text-xl font-bold text-gray-800 leading-tight" x-text="uploads.toLocaleString('id-ID')">0</h3>
                        </div>
                    </div>
                    
                    <div class="flex-1 bg-white p-3 flex items-center border border-gray-200 min-w-0">
                        <div class="bg-yellow-50 text-yellow-500 p-2 rounded mr-3 flex-shrink-0">
                            <i class="fa-solid fa-cloud-arrow-down text-lg mx-0.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-500 font-bold mb-0.5 tracking-wide truncate">Download</p>
                            <h3 class="text-xl font-bold text-gray-800 leading-tight" x-text="downloads.toLocaleString('id-ID')">0</h3>
                        </div>
                    </div>
                </div>

                <!-- Right Group: User Active, Server Storage (Matches Phase Status) -->
                <div class="lg:col-span-1 flex flex-col xl:flex-row gap-3 xl:items-center">
                    <div class="flex-1 bg-white p-3 flex items-center border border-gray-200 min-w-0">
                        <div class="bg-red-50 text-red-500 p-2 rounded mr-3 flex-shrink-0">
                            <i class="fa-solid fa-user-group text-lg"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-500 font-bold mb-0.5 tracking-wide truncate">User Active</p>
                            <h3 class="text-xl font-bold text-gray-800 leading-tight" x-text="users.toLocaleString('id-ID')">0</h3>
                        </div>
                    </div>
                    
                    <div class="flex-1 bg-white p-3 flex items-center border border-gray-200 min-w-0">
                        <div class="bg-purple-50 text-purple-500 p-2 rounded mr-3 flex-shrink-0">
                            <i class="fa-solid fa-server text-lg mx-0.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm text-gray-500 font-bold mb-0.5 tracking-wide truncate">Server Storage</p>
                            <h3 class="text-sm font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis"><span x-text="diskUsed">0</span> / <span x-text="diskTotal">0</span></h3>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Middle Row: Upload Monitoring & Phase Status -->
            <div class="flex-[1.5] grid grid-cols-1 lg:grid-cols-3 gap-3 mb-3 min-h-0">
                <!-- Upload Monitoring -->
                <div class="lg:col-span-2 border border-gray-200 bg-white p-4 flex flex-col relative min-h-[300px] lg:min-h-0">
                    <div class="flex-none flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                        <h3 class="font-bold text-gray-700 text-lg">Upload Monitoring</h3>
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-gray-400 mr-2" x-text="monitoringAllData.length > 0 ? (monitoringPage + 1) + '/' + Math.ceil(monitoringAllData.length / 7) : ''"></span>
                            <button @click="prevMonitoringPage()" class="w-7 h-7 flex items-center justify-center border border-gray-200 hover:bg-gray-100 transition-colors disabled:opacity-30 disabled:cursor-not-allowed" :disabled="monitoringPage === 0">
                                <i class="fa-solid fa-chevron-left text-xs text-gray-500"></i>
                            </button>
                            <button @click="nextMonitoringPage()" class="w-7 h-7 flex items-center justify-center border border-gray-200 hover:bg-gray-100 transition-colors disabled:opacity-30 disabled:cursor-not-allowed" :disabled="(monitoringPage + 1) * 7 >= monitoringAllData.length">
                                <i class="fa-solid fa-chevron-right text-xs text-gray-500"></i>
                            </button>
                        </div>
                    </div>
                    <div class="relative flex-1 w-full min-h-0">
                        <canvas id="drawingMonitoringChart"></canvas>
                    </div>
                </div>

                <!-- Phase Status -->
                <div class="lg:col-span-1 border border-gray-200 bg-white p-4 flex flex-col relative min-h-[300px] lg:min-h-0">
                    <h3 class="font-bold border-b border-gray-100 pb-2 mb-2 text-gray-700 flex items-center justify-between text-lg flex-none">
                        <div> Phase Status</div>
                    </h3>
                    <div class="relative flex-1 w-full flex items-center justify-center min-h-0 mt-2">
                        <canvas id="drawingPhaseChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Trend, Eco Impact, Activity Log -->
            <div class="flex-1 grid grid-cols-1 lg:grid-cols-3 gap-3 min-h-0 mb-6 lg:mb-0">
                <div class="lg:col-span-2 grid grid-cols-1 lg:grid-cols-2 gap-3 min-h-0">
                    <!-- Trend Upload & Download -->
                    <div class="border border-gray-200 bg-white p-4 flex flex-col relative min-h-[280px] lg:min-h-0">
                    <div class="flex-none flex items-center justify-between font-bold border-b border-gray-100 pb-2 mb-2 text-gray-700 text-lg">
                        <div class="flex items-center"> Trend Upload & Download</div>
                        <div x-data="{
                                open: false
                            }"
                            class="relative w-24">

                            <button @click="open = !open" @click.outside="open = false" type="button"
                                class="flex items-center justify-between w-full px-2 py-1 text-sm font-medium text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 focus:outline-none">
                                <span x-text="selectedYear"></span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                            </button>

                            <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-full bg-white border border-gray-100 shadow-xl py-1 z-50">
                                <?php $currentY = date('Y'); ?>
                                @for($i = $currentY; $i >= $currentY - 5; $i--)
                                <button @click="open = false; fetchTrendData('{{$i}}')" type="button"
                                    class="group flex items-center justify-between w-full px-3 py-1.5 text-sm text-left hover:bg-blue-50 transition-colors duration-150">
                                    <span class="text-gray-700 group-hover:text-blue-600 font-medium"
                                        :class="selectedYear == '{{$i}}' ? 'text-blue-600 font-bold' : ''">
                                        {{$i}}

                                    </span>
                                    <i x-show="selectedYear == '{{$i}}'" class="fa-solid fa-check text-blue-600 text-xs"></i>
                                </button>
                                @endfor
                            </div>
                        </div>
                    </div>
                    <div class="relative flex-1 w-full mt-2 min-h-0">
                        <canvas id="drawingTrendChart"></canvas>
                    </div>
                </div>

                    <!-- Eco Impact custom widget -->
                    <div class="border border-gray-200 bg-white p-3 lg:p-5 flex flex-col relative min-h-[280px] lg:min-h-0 font-sans">
                    <h3 class="flex-none text-sm lg:text-lg font-bold text-gray-800 mb-2 lg:mb-1 xl:mb-2 flex justify-between items-start border-b border-gray-100 pb-2">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-leaf text-emerald-500"></i>
                            <span>Eco Impact</span>
                        </div>
                    </h3>

                    <div class="flex-1 w-full flex flex-col lg:flex-row items-center justify-center gap-4 lg:gap-2 xl:gap-4 py-2 lg:py-0">
                        <div class="relative w-36 h-36 lg:w-32 lg:h-32 xl:w-40 xl:h-40 flex-shrink-0 flex items-center justify-center mx-auto lg:mx-0">
                            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="42" stroke="currentColor" stroke-width="10" fill="transparent"
                                    class="text-emerald-50" stroke-dasharray="264" stroke-dashoffset="0"
                                    stroke-linecap="round" />
                                <circle id="ecoProgressCircle" cx="50" cy="50" r="42" stroke="currentColor" stroke-width="10" fill="transparent"
                                    class="text-emerald-500 transition-all duration-1000 ease-out" stroke-dasharray="264" stroke-dashoffset="264"
                                    stroke-linecap="round" />
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                <span class="text-[9px] lg:text-[8px] xl:text-[10px] font-semibold text-gray-400 leading-tight mb-1" x-text="(Math.min(tree, 1) * 100).toFixed(1) + '%'">0%</span>
                                <span class="text-[8px] lg:text-[7px] xl:text-[9px] text-gray-400 leading-tight">towards 1 Tree</span>
                                <i class="fa-solid fa-seedling text-2xl lg:text-xl xl:text-2xl text-emerald-600 my-1 filter drop-border "></i>
                                <div class="flex flex-col leading-tight mt-1">
                                    <span class="text-xs lg:text-xs xl:text-sm font-bold text-gray-800" x-text="tree.toFixed(5)">0</span>
                                    <span class="text-[8px] lg:text-[7px] xl:text-[8px] text-gray-400 uppercase tracking-wide">Trees Saved</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 lg:gap-2 xl:gap-3 w-full lg:flex-1 justify-center">
                            <div class="flex items-center p-1.5 lg:p-1 xl:p-1.5 rounded bg-green-50 border border-green-100">
                                <div class="w-8 h-8 lg:w-7 lg:h-7 xl:w-9 xl:h-9 flex-shrink-0 rounded flex items-center justify-center text-green-600">
                                    <i class="fa-solid fa-scroll text-sm lg:text-xs xl:text-base"></i>
                                </div>
                                <div class="flex items-center gap-1.5 lg:gap-1 xl:gap-2 min-w-0 flex-1 ml-2 lg:ml-1.5 xl:ml-2">
                                    <span class="text-xs lg:text-[10px] xl:text-sm font-bold text-gray-800" x-text="paper.toLocaleString()">0</span>
                                    <span class="text-[10px] lg:text-[9px] xl:text-xs text-green-700 font-medium">Paper</span>
                                </div>
                            </div>
                            <div class="flex items-center p-1.5 lg:p-1 xl:p-1.5 rounded bg-yellow-50 border border-yellow-100">
                                <div class="w-8 h-8 lg:w-7 lg:h-7 xl:w-9 xl:h-9 flex-shrink-0 rounded flex items-center justify-center text-yellow-500">
                                    <i class="fa-solid fa-coins text-sm lg:text-xs xl:text-base"></i>
                                </div>
                                <div class="flex items-center gap-1.5 lg:gap-1 xl:gap-2 min-w-0 flex-1 ml-2 lg:ml-1.5 xl:ml-2">
                                    <span class="text-xs lg:text-[10px] xl:text-sm font-bold text-gray-800">Rp <span x-text="harga.toLocaleString('id-ID')">0</span></span>
                                    <span class="text-[10px] lg:text-[9px] xl:text-xs text-yellow-700 font-medium">Cost</span>
                                </div>
                            </div>
                            <div class="flex items-center p-1.5 lg:p-1 xl:p-1.5 rounded bg-cyan-50 border border-cyan-100">
                                <div class="w-8 h-8 lg:w-7 lg:h-7 xl:w-9 xl:h-9 flex-shrink-0 rounded flex items-center justify-center text-cyan-600">
                                    <i class="fa-solid fa-wind text-sm lg:text-xs xl:text-base"></i>
                                </div>
                                <div class="flex items-center gap-1.5 lg:gap-1 xl:gap-2 min-w-0 flex-1 ml-2 lg:ml-1.5 xl:ml-2">
                                    <span class="text-xs lg:text-[10px] xl:text-sm font-bold text-gray-800" x-text="co2.toFixed(3) + ' Kg'">0 Kg</span>
                                    <span class="text-[10px] lg:text-[9px] xl:text-xs text-cyan-700 font-medium">CO2</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Activity Log -->
                <div class="lg:col-span-1 border border-gray-200 bg-white p-3 lg:p-4 flex flex-col overflow-hidden min-h-[280px] lg:min-h-0">
                    <h3 class="flex-none text-sm lg:text-lg font-bold text-gray-800 mb-2 flex justify-between items-start border-b border-gray-100 pb-2">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-newspaper text-gray-500"></i>
                            <span>Activity Log</span>
                        </div>
                    </h3>
                    <div class="flex-1 overflow-y-auto pr-1 lg:pr-2 min-h-0 divide-y divide-gray-100">
                        <template x-for="act in recentActivity.slice(0, 10)" :key="act.id">
                            <div class="py-1.5 lg:py-2 px-1 flex space-x-2 lg:space-x-3 hover:bg-gray-50 transition-all duration-300">
                                <div class="flex-shrink-0 pt-1">
                                    <i class="fa-solid fa-cloud-arrow-up text-green-500 w-4 lg:w-5 text-center text-xs lg:text-base"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <p class="text-xs lg:text-sm text-gray-800">
                                            <strong x-text="act.user_name">System</strong> upload new document.
                                        </p>
                                    </div>
                                    <p class="text-[10px] lg:text-xs text-gray-500 mt-0.5" x-text="new Date(act.created_at).toLocaleString('id-ID', {day: 'numeric', month:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit'}).replace(/\./g,':')"></p>
                                    <p class="mt-0.5 lg:mt-1 text-[10px] lg:text-[12px] text-gray-600 font-mono truncate">
                                        <span x-text="[act.meta?.customer_code, act.meta?.model_name, act.meta?.part_no, act.meta?.doctype_group, act.meta?.part_group_code].filter(Boolean).join(' - ')"></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

        </div>

        <!-- Inventory Wrapper -->
        <div class="lg:absolute inset-0 flex flex-col min-h-0 w-full gap-2" x-show="activeSection === 'inventory'" x-transition.opacity.duration.700ms style="display: none;"
             x-data="inventoryDashboard()" x-init="fetchInventoryData()">

            {{-- Header & KPIs --}}
            <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4">
                <div class="flex-none">
                    <h2 class="text-lg xl:text-xl font-bold text-gray-800 leading-none mb-1">Inventory Overview</h2>
                    <p class="text-[11px] xl:text-xs text-gray-500 leading-tight">Stock monitoring and transaction analytics</p>
                </div>
                <div class="flex-1 flex flex-col md:flex-row gap-2 items-stretch lg:justify-end min-w-[100%] xl:min-w-[750px]">
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-2 flex-1">
                        <template x-for="kpi in kpis" :key="kpi.id">
                            <div class="bg-white px-2.5 py-2 rounded-xs border border-gray-200 flex items-center gap-2.5 h-[52px]">
                                <div class="w-9 h-9 rounded-xs flex items-center justify-center text-base shrink-0" :class="kpi.iconBg">
                                    <i class="fa-solid" :class="kpi.icon"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[10px] font-bold text-slate-600 uppercase tracking-widest leading-none mb-1 whitespace-nowrap" x-text="kpi.label"></p>
                                    <h3 class="text-sm font-bold text-slate-900 leading-none tracking-tight whitespace-nowrap">
                                        <span x-text="kpi.value"></span> <span class="text-[9px] text-slate-400 font-medium ml-0.5" x-text="kpi.unit"></span>
                                    </h3>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="shrink-0 flex items-stretch">
                        <button @click="showInvFilter = !showInvFilter" title="Toggle Filters" class="group flex items-center justify-center w-full md:w-[52px] h-[52px] md:h-auto bg-white border border-slate-200 rounded-xs transition-all hover:bg-slate-50">
                            <i class="fa-solid fa-filter text-slate-400 group-hover:text-primary-500 transition-colors text-sm"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Filter Card --}}
            <div x-show="showInvFilter" class="bg-white rounded-xs border border-slate-200 p-4">
                <div class="flex flex-col lg:flex-row gap-4 lg:items-end">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 flex-1">
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-medium text-slate-700 uppercase tracking-widest">Period</label>
                            <input type="month" id="inv_month_picker" x-model="invMonthYear" @change="fetchInventoryData()" class="w-full text-xs font-medium border border-slate-200 bg-white text-slate-900 rounded-xs h-[40px] px-3 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all focus:border-primary-500">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-medium text-slate-700 uppercase tracking-widest">Customer</label>
                            <select id="invFilterCustomer" class="w-full text-xs"></select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-medium text-slate-700 uppercase tracking-widest">Model</label>
                            <select id="invFilterModel" class="w-full text-xs"></select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-medium text-slate-700 uppercase tracking-widest">Balance Status</label>
                            <select id="invFilterBalance" class="w-full text-xs"></select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-medium text-slate-700 uppercase tracking-widest">Usage Status</label>
                            <select id="invFilterUsage" class="w-full text-xs"></select>
                        </div>
                    </div>
                    <div class="flex gap-2 pt-2 xl:pt-0">
                        <button type="button" @click="resetInvFilters()" class="h-[40px] px-6 bg-slate-100 hover:bg-slate-200 rounded-xs text-[10px] font-bold text-slate-600 uppercase tracking-widest transition-all">Reset Filters</button>
                    </div>
                </div>
            </div>

            {{-- Charts & Tables 3-Column Layout --}}
            <div class="flex flex-col lg:flex-row gap-2 flex-1 min-h-0">
                {{-- Column 1: Stock Status + Balance Warnings --}}
                <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">
                    <div class="chart-card bg-white p-2 lg:p-2.5 rounded-xs border border-gray-200 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                        <div class="flex-none flex justify-between items-center mb-1">
                            <h3 class="text-sm lg:text-base font-semibold text-gray-800 flex items-center min-w-0 pr-2">

                                <span class="truncate">Stock Status</span>
                                <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 text-[8px] font-black text-slate-500 uppercase tracking-widest border border-slate-200/50 flex-shrink-0 whitespace-nowrap">Item Part</span>
                            </h3>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button id="invStockChartPrev" @click="paginateInvChart('invStockChart', -1)" disabled class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                <button id="invStockChartNext" @click="paginateInvChart('invStockChart', 1)" disabled class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
                            </div>
                        </div>
                        <div class="relative w-full flex-1 min-h-0"><canvas id="invStockChart"></canvas></div>
                    </div>
                    <div class="table-container bg-white p-2 lg:p-2.5 rounded-xs border border-gray-200 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                        <div class="flex-none flex justify-between items-center mb-1">
                            <h3 class="text-sm lg:text-base font-semibold text-gray-800 flex items-center">
                                Balance Warnings
                            </h3>
                        </div>
                        <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 rounded-xs">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/80 sticky top-0 z-10 backdrop-blur-md">
                                    <tr>
                                        <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Part No</th>
                                        <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Min</th>
                                        <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Actual</th>
                                        <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="invBalanceTableBody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Column 2: Usage by Model/Maker + Material Usage Table --}}
                <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">
                    <div class="chart-card bg-white p-2 lg:p-2.5 rounded-xs border border-gray-200 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                        <div class="flex-none flex justify-between items-center mb-1">
                            <h3 id="invUsageChartTitle" class="text-sm lg:text-base font-semibold text-gray-800 flex items-center min-w-0 pr-2">

                                <span class="truncate">Usage by Models</span>
                                <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 text-[8px] font-black text-slate-500 uppercase tracking-widest border border-slate-200/50 flex-shrink-0 whitespace-nowrap">Item Part</span>
                            </h3>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <div class="flex bg-gray-100 p-0.5 rounded-xs">
                                    <button type="button" @click="switchInvUsageChart('model')" id="btnInvUsageModel" class="px-2 py-1 rounded-xs text-[9px] font-bold uppercase transition-all bg-white text-primary-600 shadow-sm">Model</button>
                                    <button type="button" @click="switchInvUsageChart('maker')" id="btnInvUsageMaker" class="px-2 py-1 rounded-xs text-[9px] font-bold uppercase transition-all text-gray-500 hover:text-gray-700">Maker</button>
                                </div>
                                <div class="flex items-center gap-1 border-l border-gray-200 pl-2">
                                    <button id="invUsageChartPrev" @click="paginateInvActiveUsage(-1)" disabled class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                    <button id="invUsageChartNext" @click="paginateInvActiveUsage(1)" disabled class="w-6 h-6 flex items-center justify-center rounded-xs bg-gray-100 hover:bg-gray-200 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="relative w-full flex-1 min-h-0">
                            <div id="invContainerUsageModel" class="h-full"><canvas id="invUsageModelChart"></canvas></div>
                            <div id="invContainerUsageMaker" class="h-full hidden"><canvas id="invMakerChart"></canvas></div>
                        </div>
                    </div>
                    <div class="table-container bg-white p-2 lg:p-2.5 rounded-xs border border-gray-200 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                        <div class="flex-none flex justify-between items-center mb-1">
                            <h3 class="text-sm lg:text-base font-semibold text-gray-800 flex items-center">
                                Material Usage Detail
                            </h3>
                        </div>
                        <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 rounded-xs">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/80 sticky top-0 z-10 backdrop-blur-md">
                                    <tr>
                                        <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Part No</th>
                                        <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Supplier</th>
                                        <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Actual</th>
                                        <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Gap</th>
                                        <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="invUsageTableBody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Column 3: Transaction Trend + Recent Activity --}}
                <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">
                    <div class="chart-card bg-white p-2 lg:p-2.5 rounded-xs border border-gray-200 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                        <div class="flex-none flex justify-between items-center mb-1">
                            <h3 class="text-sm lg:text-base font-semibold text-gray-800 flex items-center min-w-0">

                                <span class="truncate">Transaction Trend</span>
                                <span class="ml-2 px-1.5 py-0.5 rounded-xs bg-slate-100 text-[8px] font-black text-slate-500 uppercase tracking-widest border border-slate-200/50 flex-shrink-0 whitespace-nowrap">Item Part</span>
                            </h3>
                        </div>
                        <div class="relative w-full flex-1 min-h-0"><canvas id="invTrendlineChart"></canvas></div>
                    </div>
                    <div class="table-container bg-white p-2 lg:p-2.5 rounded-xs border border-gray-200 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                        <div class="flex-none flex justify-between items-center mb-1">
                            <h3 class="text-sm lg:text-base font-semibold text-gray-800 flex items-center">
                                Recent Activity
                            </h3>
                        </div>
                        <div class="overflow-y-auto flex-1 custom-scrollbar border border-gray-100 rounded-xs">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50/80 sticky top-0 z-10 backdrop-blur-md">
                                    <tr>
                                        <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest">Part No</th>
                                        <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-center">Type</th>
                                        <th class="py-2 px-2 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-center">Date</th>
                                        <th class="py-2 px-3 text-[9px] font-bold text-slate-500 uppercase tracking-widest text-right">Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="invHistoryTableBody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- End of Grid Wrapper -->
</main>
@endsection


@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>window.APP_BASE_URL = "{{ url('') }}";</script>
<script src="{{ asset('js/inventory-dashboard.js') }}"></script>

<script>

    window.drawingDashboardData = function() {
        return {
            baseUrl: "{{url('api')}}",

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
</script>
{{-- Drilldown Modal --}}
<div id="drilldownModal" class="fixed inset-0 z-50 hidden" aria-modal="true">
    <div class="absolute inset-0 bg-black/40" onclick="closeDrilldownModal()"></div>
    <div class="absolute right-0 top-0 bottom-0 w-full max-w-lg bg-white shadow-2xl flex flex-col transform transition-transform duration-300 translate-x-full" id="drilldownPanel">
        {{-- Header --}}
        <div class="flex-none flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-gray-50">
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <p class="text-[10px] font-bold text-primary-500 uppercase tracking-widest">Detail Explorer</p>
                    <span id="drilldownCountBadge" class="px-1.5 py-0.5 rounded-full bg-primary-100 text-primary-600 text-[9px] font-bold">0</span>
                </div>
                <h2 id="drilldownTitle" class="text-base font-bold text-gray-800 truncate max-w-[300px]">Loading...</h2>
            </div>
            <button onclick="closeDrilldownModal()" class="w-8 h-8 flex items-center justify-center rounded-xs bg-gray-200 hover:bg-gray-300 text-gray-600 transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
        {{-- Loader --}}
        <div id="drilldownLoader" class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <i class="fa-solid fa-spinner fa-spin text-2xl text-primary-400 mb-3"></i>
                <p class="text-[11px] text-slate-400">Fetching data...</p>
            </div>
        </div>
        {{-- Content --}}
        <div id="drilldownContent" class="flex-1 flex-col hidden min-h-0">
            {{-- Quick Filters (Segmented Control Style) --}}
            <div id="drilldownLegendContainer" class="px-5 py-3 border-b border-gray-100 bg-gray-50/30">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Filter by Status</p>
                </div>
                <div id="drilldownLegendButtons" class="inline-flex p-1 bg-gray-100 rounded-lg gap-1">
                    {{-- Buttons injected by JS --}}
                </div>
            </div>

            <div class="px-5 py-3 border-b border-gray-100 bg-white sticky top-0 z-20 flex flex-col md:flex-row gap-3 items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-[9px] font-semibold text-slate-400 uppercase whitespace-nowrap tracking-wider">Show</span>
                    <select id="drilldownPageSize" onchange="resetDrilldownAndFetch()" class="h-8 bg-gray-50 border border-gray-200 rounded-xs text-[11px] px-2 focus:ring-1 focus:ring-primary-500 outline-none cursor-pointer">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-[9px] font-semibold text-slate-400 uppercase whitespace-nowrap tracking-wider">entries</span>
                </div>
                <div class="relative w-full md:w-60">
                    <input type="text" id="drilldownSearch" placeholder="Search Part No..." class="w-full h-8 pl-9 pr-4 bg-gray-50 border border-gray-200 rounded-xs text-[11px] focus:outline-none focus:ring-1 focus:ring-primary-500 transition-all placeholder:text-gray-400">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-[9px]"></i>
                    </div>
                </div>
            </div>
            <div class="flex-1 relative min-h-0">
                {{-- Partial Table Loader --}}
                <div id="drilldownTableLoader" class="hidden absolute inset-0 bg-white/60 z-30 flex items-center justify-center backdrop-blur-[1px] transition-all">
                    <div class="flex flex-col items-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-xl text-primary-500 mb-2"></i>
                        <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wider">Updating...</span>
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
                    <button onclick="changeDrilldownPage(-1)" id="ddPrev" class="w-7 h-7 flex items-center justify-center rounded-xs bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
                        <i class="fa-solid fa-chevron-left text-[10px]"></i>
                    </button>
                    <div class="px-2 text-[10px] font-bold text-slate-600">
                        Page <span id="ddCurrentPage">1</span>
                    </div>
                    <button onclick="changeDrilldownPage(1)" id="ddNext" class="w-7 h-7 flex items-center justify-center rounded-xs bg-white border border-gray-200 text-gray-500 hover:bg-gray-50 disabled:opacity-30 disabled:cursor-not-allowed transition-all">
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
            { key: 'qty_pcs', label: 'Quantity (PCS)', cls: 'py-2 px-3 text-right font-mono' },
            { key: 'date', label: 'Date', cls: 'py-2 px-3 text-right' }
        ]
    };

    const STATUS_BADGE = {
        'Critical':   'bg-rose-100 text-rose-700',
        'Warning':    'bg-amber-100 text-amber-700',
        'Over':       'bg-blue-100 text-blue-700',
        'Safe':       'bg-emerald-100 text-emerald-700',
        'Loss':       'bg-rose-100 text-rose-700',
        'Near Loss':  'bg-amber-100 text-amber-700',
        'On Budget':  'bg-emerald-100 text-emerald-700',
        'OUT-EVENT':  'bg-amber-100 text-amber-700',
        'OUT-PP':     'bg-indigo-100 text-indigo-700',
        'OUT-TRIAL':  'bg-rose-100 text-rose-700',
        'IN':         'bg-emerald-100 text-emerald-700',
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
                `<th class="${c.cls} text-[9px] font-bold text-slate-500 uppercase tracking-widest">${c.label}</th>`
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
                            ? `<span class="inline-block px-1.5 py-0.5 rounded-xs text-[9px] font-bold uppercase ${badgeCls}">${val}</span>`
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
        btn.className = `legend-btn px-4 py-1.5 rounded-md text-[10px] font-bold uppercase transition-all duration-200 ${
            isActive 
            ? 'bg-white text-primary-600 shadow-sm' 
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
