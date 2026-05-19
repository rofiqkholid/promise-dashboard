        <!-- Inventory Wrapper -->
        <div class="lg:absolute inset-0 flex flex-col min-h-0 w-full gap-2" x-show="activeSection === 'inventory'" x-transition.opacity.duration.700ms style="display: none;"
            x-data="inventoryDashboard()" x-init="fetchInventoryData()"
            @open-filter-modal.window="if(activeSection === 'inventory') showInvFilter = true">

            {{-- Header & KPIs --}}
            <div class="flex-none flex flex-col lg:flex-row gap-2 pb-1">
                {{-- Column 1 Header: Title & Total Value --}}
                <div class="w-full lg:w-1/3 flex items-center justify-between min-w-0">
                    <div class="flex flex-col justify-center px-2 flex-shrink-0 mr-2">
                        <h2 class="text-lg xl:text-xl font-bold text-gray-800 leading-none mb-1 whitespace-nowrap">Inv. Material Overview</h2>
                        <p class="text-[11px] xl:text-xs text-gray-500 leading-tight whitespace-nowrap">Stock monitoring and transaction analytics</p>
                    </div>
                    <!-- Total Value KPI Card -->
                    <div class="bg-white p-3 flex items-center border border-gray-200 flex-1 min-w-0">
                        <div class="p-2 rounded bg-blue-50 text-blue-600 mr-2 flex-shrink-0">
                            <i class="fa-solid fa-coins text-lg mx-0.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 font-bold mb-0.5 tracking-wide truncate">Total Value</p>
                            <h3 class="text-sm lg:text-base font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                                <span x-text="kpis[0].value"></span> <span class="text-[9px] text-gray-400 font-medium ml-0.5" x-text="kpis[0].unit"></span>
                            </h3>
                        </div>
                    </div>
                </div>
                
                {{-- Column 2 Header: Total Stock, In, Out Event --}}
                <div class="w-full lg:w-1/3 grid grid-cols-3 gap-2">
                    <!-- Total Stock -->
                    <div class="bg-white p-3 flex items-center border border-gray-200 min-w-0">
                        <div class="p-2 rounded bg-purple-50 text-purple-600 mr-2 flex-shrink-0">
                            <i class="fa-solid fa-cubes text-lg mx-0.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 font-bold mb-0.5 tracking-wide truncate">Total Stock</p>
                            <h3 class="text-sm lg:text-base font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                                <span x-text="kpis[1].value"></span> <span class="text-[9px] text-gray-400 font-medium ml-0.5" x-text="kpis[1].unit"></span>
                            </h3>
                        </div>
                    </div>
                    <!-- In -->
                    <div class="bg-white p-3 flex items-center border border-gray-200 min-w-0">
                        <div class="p-2 rounded bg-emerald-50 text-emerald-600 mr-2 flex-shrink-0">
                            <i class="fa-solid fa-arrow-right-to-bracket text-lg mx-0.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 font-bold mb-0.5 tracking-wide truncate">In</p>
                            <h3 class="text-sm lg:text-base font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                                <span x-text="kpis[2].value"></span> <span class="text-[9px] text-gray-400 font-medium ml-0.5" x-text="kpis[2].unit"></span>
                            </h3>
                        </div>
                    </div>
                    <!-- Out Event -->
                    <div class="bg-white p-3 flex items-center border border-gray-200 min-w-0">
                        <div class="p-2 rounded bg-amber-50 text-amber-600 mr-2 flex-shrink-0">
                            <i class="fa-solid fa-arrow-right-from-bracket text-lg mx-0.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 font-bold mb-0.5 tracking-wide truncate">Out Event</p>
                            <h3 class="text-sm lg:text-base font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                                <span x-text="kpis[3].value"></span> <span class="text-[9px] text-gray-400 font-medium ml-0.5" x-text="kpis[3].unit"></span>
                            </h3>
                        </div>
                    </div>
                </div>
                
                {{-- Column 3 Header: Out PP, Out Trial --}}
                <div class="w-full lg:w-1/3 grid grid-cols-2 gap-2">
                    <!-- Out PP -->
                    <div class="bg-white p-3 flex items-center border border-gray-200 min-w-0">
                        <div class="p-2 rounded bg-indigo-50 text-indigo-600 mr-2 flex-shrink-0">
                            <i class="fa-solid fa-industry text-lg mx-0.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 font-bold mb-0.5 tracking-wide truncate">Out PP</p>
                            <h3 class="text-sm lg:text-base font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                                <span x-text="kpis[4].value"></span> <span class="text-[9px] text-gray-400 font-medium ml-0.5" x-text="kpis[4].unit"></span>
                            </h3>
                        </div>
                    </div>
                    <!-- Out Trial -->
                    <div class="bg-white p-3 flex items-center border border-gray-200 min-w-0">
                        <div class="p-2 rounded bg-rose-50 text-rose-600 mr-2 flex-shrink-0">
                            <i class="fa-solid fa-vial text-lg mx-0.5"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs text-gray-500 font-bold mb-0.5 tracking-wide truncate">Out Trial</p>
                            <h3 class="text-sm lg:text-base font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                                <span x-text="kpis[5].value"></span> <span class="text-[9px] text-gray-400 font-medium ml-0.5" x-text="kpis[5].unit"></span>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Inventory Filter Modal --}}
            <div x-show="showInvFilter" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-transition.opacity>
                <div @click.away="showInvFilter = false" class="bg-white shadow-xl flex flex-col overflow-visible" style="width: 70vw; min-height: 50vh;">
                    <div class="flex items-center justify-between px-10 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                            Filter Inventory Data
                        </h3>
                        <button @click="showInvFilter = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                    <div class="px-10 py-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2 items-start">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Period</label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <i class="fa-solid fa-calendar-days text-gray-400 text-sm"></i>
                                    </div>
                                    <input type="month" id="inv_month_picker" x-model="invMonthYear" @change="fetchInventoryData()" style="height: 2.375rem; border: 1px solid #d1d5db; border-radius: 1px; font-size: 0.875rem; color: #3f3f3f;" class="block w-full focus:ring-0 focus:outline-none py-1.5 pl-9 pr-3">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Customer</label>
                                <div class="relative">
                                    <select id="invFilterCustomer" class="w-full text-xs"></select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Model</label>
                                <div class="relative">
                                    <select id="invFilterModel" class="w-full text-xs"></select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Balance Status</label>
                                <div class="relative">
                                    <select id="invFilterBalance" class="w-full text-xs"></select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-0.5">Usage Status</label>
                                <div class="relative">
                                    <select id="invFilterUsage" class="w-full text-xs"></select>
                                </div>
                            </div>
                        </div>
                        <div class="w-full flex justify-end items-center mt-3 pt-2 border-t border-gray-200 space-x-2">
                            <button type="button" @click="resetInvFilters()" class="px-3 py-1.5 text-xs font-medium border border-gray-200 hover:bg-gray-50 text-gray-700">Reset</button>
                            <button type="button" @click="showInvFilter = false; fetchInventoryData()" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 min-w-[120px]">
                                <i class="fa-solid fa-check mr-2"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Charts & Tables 3-Column Layout --}}
            <div class="flex flex-col lg:flex-row gap-2 flex-1 min-h-0">
                {{-- Column 1: Stock Status + Balance Warnings --}}
                <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">

                    <div class="border border-gray-200 bg-white p-3 lg:p-4 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                        <div class="flex-none flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                            <h3 class="font-bold text-gray-700 text-lg flex items-center min-w-0 pr-2">
                                <span class="truncate">Stock Status</span>
                                <span class="ml-2 px-1.5 py-0.5 bg-slate-100 text-[11px] font-semibold text-slate-500 tracking-widest border border-slate-200/50 flex-shrink-0 whitespace-nowrap">Item Part</span>
                            </h3>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button id="invStockChartPrev" @click="paginateInvChart('invStockChart', -1)" disabled class="w-7 h-7 flex items-center justify-center bg-white border border-gray-200 hover:bg-gray-100 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                <button id="invStockChartNext" @click="paginateInvChart('invStockChart', 1)" disabled class="w-7 h-7 flex items-center justify-center bg-white border border-gray-200 hover:bg-gray-100 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
                            </div>
                        </div>
                        <div class="relative w-full flex-1 min-h-0"><canvas id="invStockChart"></canvas></div>
                    </div>
                    <div class="border border-gray-200 bg-white p-3 lg:p-4 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                        <div class="flex-none flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                            <h3 class="font-bold text-gray-700 text-lg flex items-center">
                                Balance Warnings
                            </h3>
                        </div>
                        <div class="overflow-y-auto flex-1 custom-scrollbar pr-1 lg:pr-2">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th class="py-2 px-3 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100">Part No</th>
                                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest text-right border-b border-gray-100">Min</th>
                                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest text-right border-b border-gray-100">Actual</th>
                                        <th class="py-2 px-3 text-xs font-bold text-slate-500 tracking-widest text-right border-b border-gray-100">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="invBalanceTableBody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Column 2: Usage by Model/Maker + Material Usage Table --}}
                <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">

                    <div class="border border-gray-200 bg-white p-3 lg:p-4 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                        <div class="flex-none flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                            <h3 id="invUsageChartTitle" class="font-bold text-gray-700 text-lg flex items-center min-w-0 pr-2">
                                <span class="truncate">Usage by Models</span>
                                <span class="ml-2 px-1.5 py-0.5 bg-slate-100 text-[11px] font-semibold text-slate-500 tracking-widest border border-slate-200/50 flex-shrink-0 whitespace-nowrap">Item Part</span>
                            </h3>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <div class="flex bg-gray-100 p-0.5">
                                    <button type="button" @click="switchInvUsageChart('model')" id="btnInvUsageModel" class="px-2 py-1 text-[11px] font-semibold transition-all bg-white text-primary-600">Model</button>
                                    <button type="button" @click="switchInvUsageChart('maker')" id="btnInvUsageMaker" class="px-2 py-1 text-[11px] font-semibold transition-all text-gray-500 hover:text-gray-700">Maker</button>
                                </div>
                                <div class="flex items-center gap-1 border-l border-gray-200 pl-2">
                                    <button id="invUsageChartPrev" @click="paginateInvActiveUsage(-1)" disabled class="w-7 h-7 flex items-center justify-center bg-white border border-gray-200 hover:bg-gray-100 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-left text-[10px]"></i></button>
                                    <button id="invUsageChartNext" @click="paginateInvActiveUsage(1)" disabled class="w-7 h-7 flex items-center justify-center bg-white border border-gray-200 hover:bg-gray-100 text-gray-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"><i class="fa-solid fa-chevron-right text-[10px]"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="relative w-full flex-1 min-h-0">
                            <div id="invContainerUsageModel" class="h-full"><canvas id="invUsageModelChart"></canvas></div>
                            <div id="invContainerUsageMaker" class="h-full hidden"><canvas id="invMakerChart"></canvas></div>
                        </div>
                    </div>
                    <div class="border border-gray-200 bg-white p-3 lg:p-4 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                        <div class="flex-none flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                            <h3 class="font-bold text-gray-700 text-lg flex items-center">
                                Material Usage Detail
                            </h3>
                        </div>
                        <div class="overflow-y-auto flex-1 custom-scrollbar pr-1 lg:pr-2">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th class="py-2 px-3 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100">Part No</th>
                                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100">Supplier</th>
                                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest text-right border-b border-gray-100">Actual</th>
                                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest text-right border-b border-gray-100">Gap</th>
                                        <th class="py-2 px-3 text-xs font-bold text-slate-500 tracking-widest text-right border-b border-gray-100">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="invUsageTableBody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Column 3: Transaction Trend + Recent Activity --}}
                <div class="w-full lg:w-1/3 flex flex-col gap-2 h-full min-h-0">

                    <div class="border border-gray-200 bg-white p-3 lg:p-4 flex flex-col relative h-[250px] lg:h-auto lg:flex-[55] min-h-0">
                        <div class="flex-none flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                            <h3 class="font-bold text-gray-700 text-lg flex items-center min-w-0">
                                <span class="truncate">Transaction Trend</span>
                                <span class="ml-2 px-1.5 py-0.5 bg-slate-100 text-[11px] font-semibold text-slate-500 tracking-widest border border-slate-200/50 flex-shrink-0 whitespace-nowrap">Item Part</span>
                            </h3>
                        </div>
                        <div class="relative w-full flex-1 min-h-0"><canvas id="invTrendlineChart"></canvas></div>
                    </div>
                    <div class="border border-gray-200 bg-white p-3 lg:p-4 flex flex-col relative h-[320px] lg:h-auto lg:flex-[45] min-h-0">
                        <div class="flex-none flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                            <h3 class="font-bold text-gray-700 text-lg flex items-center">
                                Recent Activity
                            </h3>
                        </div>
                        <div class="overflow-y-auto flex-1 custom-scrollbar pr-1 lg:pr-2">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 sticky top-0 z-10">
                                    <tr>
                                        <th class="py-2 px-3 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100">Part No</th>
                                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest text-center border-b border-gray-100">Type</th>
                                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest text-center border-b border-gray-100">Date</th>
                                        <th class="py-2 px-3 text-xs font-bold text-slate-500 tracking-widest text-right border-b border-gray-100">Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="invHistoryTableBody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>