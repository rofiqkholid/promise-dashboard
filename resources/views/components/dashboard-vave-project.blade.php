<!-- VA/VE Project Wrapper -->
<div class="lg:absolute inset-0 flex flex-col min-h-0 w-full gap-2" x-show="activeSection === 'vave_project'" x-transition.opacity.duration.700ms style="display: none;"
     x-data="vaveProjectDashboard()" x-init="fetchVaveData()"
     @open-filter-modal.window="if(activeSection === 'vave_project') showVaveFilter = true">

    {{-- Header & KPIs --}}
    <div class="flex-none flex flex-col xl:flex-row gap-2">
        <div class="flex flex-col justify-center px-2 w-full xl:w-auto flex-shrink-0 mr-2">
            <h2 class="text-lg xl:text-xl font-bold text-gray-800 leading-none mb-1 whitespace-nowrap">Project Model Vave Analysis</h2>
            <p class="text-[11px] xl:text-xs text-gray-500 leading-tight whitespace-nowrap">Gap Benefit: (Plan - Act Kg) x Price * Qty In</p>
        </div>
        
        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-2">
            <!-- KPIs -->
            <div class="flex-1 bg-white p-3 flex items-center border border-gray-200 min-w-0">
                <div class="p-2 rounded mr-3 flex-shrink-0 bg-blue-50 text-blue-600">
                    <i class="fa-solid fa-money-bill-wave text-lg mx-0.5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 font-bold mb-0.5 tracking-wide truncate">Total Benefit</p>
                    <h3 class="text-xl font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                        Rp <span x-text="kpis.totalBenefit">0</span> <span class="text-[10px] text-gray-400 font-medium ml-0.5">IDR</span>
                    </h3>
                </div>
            </div>
            
            <div class="flex-1 bg-white p-3 flex items-center border border-gray-200 min-w-0">
                <div class="p-2 rounded mr-3 flex-shrink-0 bg-blue-50 text-blue-600">
                    <i class="fa-solid fa-weight-hanging text-lg mx-0.5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 font-bold mb-0.5 tracking-wide truncate">Saving Weight</p>
                    <h3 class="text-xl font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                        <span x-text="kpis.savingWeight">0.000</span> <span class="text-[10px] text-gray-400 font-medium ml-0.5">KG</span>
                    </h3>
                </div>
            </div>

            <div class="flex-1 bg-white p-3 flex items-center border border-gray-200 min-w-0">
                <div class="p-2 rounded mr-3 flex-shrink-0 bg-orange-50 text-orange-500">
                    <i class="fa-solid fa-percent text-lg mx-0.5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 font-bold mb-0.5 tracking-wide truncate">Saving Rate</p>
                    <h3 class="text-xl font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                        <span x-text="kpis.savingRate">0.0</span> <span class="text-[10px] text-gray-400 font-medium ml-0.5">%</span>
                    </h3>
                </div>
            </div>
            
            <div class="flex-1 bg-white p-3 flex items-center border border-gray-200 min-w-0">
                <div class="p-2 rounded mr-3 flex-shrink-0 bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-arrow-up text-lg mx-0.5"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-gray-500 font-bold mb-0.5 tracking-wide truncate">Merit Items</p>
                    <h3 class="text-xl font-bold text-gray-800 leading-tight whitespace-nowrap overflow-hidden text-ellipsis">
                        <span x-text="kpis.meritItems">0</span> <span class="text-[10px] text-gray-400 font-medium ml-0.5">PART</span>
                    </h3>
                </div>
            </div>
        </div>
        
        <!-- Filter Button removed as per user request to use global gear icon -->
    </div>

    {{-- Filter Modal --}}
    <div x-show="showVaveFilter" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-transition.opacity>
        <div @click.away="showVaveFilter = false" class="bg-white shadow-xl flex flex-col overflow-visible" style="width: 70vw; min-height: 50vh;">
            <div class="flex items-center justify-between px-10 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                    Filter VA/VE Project Data
                </h3>
                <button @click="showVaveFilter = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="px-10 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-start">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Mode</label>
                        <select x-model="filter.mode" @change="onModeChange()" style="height: 2.375rem; border-radius: 1px; border: 1px solid #d1d5db; padding-left: 0.75rem; padding-right: 2.5rem; color: #3f3f3f; font-size: 0.875rem; background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25rem;" class="w-full appearance-none focus:ring-0 focus:outline-none bg-white">
                            <option value="monthly">Monthly View</option>
                            <option value="yearly">Yearly Trend</option>
                            <option value="comparison">Yearly Comparison</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Period</label>
                        <div class="relative">
                            <!-- Year Selector Dropdown (visible when mode is not monthly) -->
                            <select x-show="filter.mode !== 'monthly'" x-model="filter.period" @change="fetchVaveData()" style="height: 2.375rem; border-radius: 1px; border: 1px solid #d1d5db; padding-left: 0.75rem; padding-right: 2.5rem; color: #3f3f3f; font-size: 0.875rem; background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25rem;" class="w-full appearance-none focus:ring-0 focus:outline-none bg-white">
                                <template x-for="y in yearsList" :key="y">
                                    <option :value="y" x-text="y" :selected="y === filter.period"></option>
                                </template>
                            </select>
                            
                            <!-- Month Picker Input (visible when mode is monthly) -->
                            <div x-show="filter.mode === 'monthly'" class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fa-solid fa-calendar-days text-gray-400 text-sm"></i>
                                </div>
                                <input type="month" x-model="filter.period" @change="fetchVaveData()" style="height: 2.375rem; border: 1px solid #d1d5db; border-radius: 1px; font-size: 0.875rem; color: #3f3f3f; padding-left: 2.25rem;" class="block w-full focus:ring-0 focus:outline-none bg-white">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Customer</label>
                        <div class="relative">
                            <select id="vaveFilterCustomer" class="w-full text-xs"></select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">Model</label>
                        <div class="relative">
                            <select id="vaveFilterModel" class="w-full text-xs"></select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-0.5">EBD Version</label>
                        <select x-model="filter.ebd_version" @change="fetchVaveData()" style="height: 2.375rem; border-radius: 1px; border: 1px solid #d1d5db; padding-left: 0.75rem; padding-right: 2.5rem; color: #3f3f3f; font-size: 0.875rem; background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e&quot;); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25rem;" class="w-full appearance-none focus:ring-0 focus:outline-none bg-white">
                            <option value="">All Versions</option>
                            <template x-for="v in ebdVersionsList" :key="v">
                                <option :value="v" x-text="v"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="w-full flex justify-end items-center mt-3 pt-2 border-t border-gray-200 space-x-2">
                    <button type="button" @click="resetVaveFilter()" class="px-3 py-1.5 text-xs font-medium border border-gray-200 hover:bg-gray-50 text-gray-700">Reset</button>
                    <button type="button" @click="showVaveFilter = false; fetchVaveData()" class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 min-w-[120px]">
                        <i class="fa-solid fa-check mr-2"></i> Apply Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts 2-Column Layout --}}
    <div class="flex flex-col lg:flex-row gap-2 min-h-0 lg:flex-[58]">
        {{-- Benefit by Model --}}
        <div class="w-full lg:w-1/2 border border-gray-200 bg-white p-3 flex flex-col min-h-0">
            <div class="flex-none flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                <h3 class="font-bold text-gray-700 text-lg flex items-center gap-2">
                    <span x-text="chartType === 'benefit' ? 'Benefit by Model' : (chartType === 'weight' ? 'Weight by Model' : 'Efficiency by Model')">Benefit by Model</span>
                    <span class="text-[10px] bg-gray-100 text-gray-500 px-1 py-0.5 border border-gray-200" x-text="chartType === 'benefit' ? 'IDR' : (chartType === 'weight' ? 'KG' : '%')">IDR</span>
                </h3>
                <div class="flex bg-gray-100 p-0.5">
                    <button @click="switchChartType('benefit')" :class="chartType === 'benefit' ? 'bg-white text-emerald-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-2 py-1 text-[10px] font-bold transition-all">Benefit</button>
                    <button @click="switchChartType('weight')" :class="chartType === 'weight' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-2 py-1 text-[10px] font-bold transition-all">Weight</button>
                    <button @click="switchChartType('efficiency')" :class="chartType === 'efficiency' ? 'bg-white text-amber-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'" class="px-2 py-1 text-[10px] font-bold transition-all">Efficiency</button>
                </div>
            </div>
            <div class="relative w-full flex-1 min-h-0"><canvas id="vaveBenefitChart"></canvas></div>
        </div>
        
        {{-- Pareto Analysis --}}
        <div class="w-full lg:w-1/2 border border-gray-200 bg-white p-3 flex flex-col min-h-0">
            <div class="flex-none flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
                <h3 class="font-bold text-gray-700 text-lg flex items-center gap-2">
                    Pareto Analysis <span class="text-[10px] bg-gray-100 text-gray-500 px-1 py-0.5 border border-gray-200">Contribution</span>
                </h3>
            </div>
            <div class="relative w-full flex-1 min-h-0"><canvas id="vaveParetoChart"></canvas></div>
        </div>
    </div>

    {{-- Bottom Table --}}
    <div class="border border-gray-200 bg-white p-3 lg:p-4 flex flex-col min-h-[200px] lg:flex-[42] overflow-hidden">
        <div class="flex-none flex items-center justify-between border-b border-gray-100 pb-2 mb-2">
            <h3 class="font-bold text-gray-700 text-lg flex items-center gap-2">
                Detailed VAVE Analysis (Project Model)
            </h3>
            <button class="px-2 py-1 text-[10px] font-semibold text-emerald-600 border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 flex items-center gap-1 transition-colors">
                <i class="fa-regular fa-file-excel"></i> Export Excel
            </button>
        </div>
        <div class="flex-1 overflow-auto custom-scrollbar">
            <table class="w-full text-left text-[11px]">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="py-2 px-3 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100 text-left">Part No</th>
                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100 text-left">Model</th>
                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100 text-center">EBD Version</th>
                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100 text-right">Plan (Kg)</th>
                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100 text-right">Actual (Kg)</th>
                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100 text-right">Gap (Kg)</th>
                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100 text-right">IDR/Kg</th>
                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100 text-right">Qty In</th>
                        <th class="py-2 px-2 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100 text-right">Benefit</th>
                        <th class="py-2 px-3 text-xs font-bold text-slate-500 tracking-widest border-b border-gray-100 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-gray-600">
                    <template x-if="items.length === 0">
                        <tr>
                            <td colspan="10" class="py-6 text-center text-xs text-slate-400">No data available in table</td>
                        </tr>
                    </template>
                    <template x-for="item in items" :key="item.part_no">
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-2 px-3 text-xs font-medium text-slate-700" x-text="item.part_no"></td>
                            <td class="py-2 px-2 text-left"><span class="inline-flex items-center justify-center px-2 py-1 text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 leading-none uppercase" x-text="item.model_name"></span></td>
                            <td class="py-2 px-2 text-center">
                                <span class="inline-flex items-center justify-center px-2 py-1 text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200 leading-none uppercase" x-text="item.ebd_version || '-'"></span>
                            </td>
                            <td class="py-2 px-2 text-right text-xs text-slate-500" x-text="item.plan_kg.toFixed(3)"></td>
                            <td class="py-2 px-2 text-right text-xs text-slate-500" x-text="item.actual_kg.toFixed(3)"></td>
                            <td class="py-2 px-2 text-right text-xs font-bold" :class="(item.plan_kg - item.actual_kg) >= 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="(item.plan_kg - item.actual_kg).toFixed(3)"></td>
                            <td class="py-2 px-2 text-right text-xs text-slate-500" x-text="'Rp ' + item.idr_per_kg.toLocaleString('id-ID')"></td>
                            <td class="py-2 px-2 text-right text-xs text-slate-700 font-bold" x-text="item.qty_usage.toLocaleString('id-ID')"></td>
                            <td class="py-2 px-2 text-right text-xs font-bold" :class="item.gap_benefit_idr >= 0 ? 'text-emerald-600' : 'text-rose-600'" x-text="new Intl.NumberFormat('id-ID').format(item.gap_benefit_idr)"></td>
                            <td class="py-2 px-3 text-center">
                                <span class="inline-flex items-center justify-center w-16 py-1 text-[10px] font-bold border leading-none uppercase"
                                      :class="item.gap_benefit_idr >= 0 ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-rose-50 text-rose-600 border-rose-200'"
                                      x-text="item.gap_benefit_idr >= 0 ? 'MERIT' : 'LOSS'"></span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>
