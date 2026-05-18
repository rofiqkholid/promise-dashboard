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