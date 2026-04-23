<header class="fixed top-0 left-0 right-0 z-40 flex justify-between items-center py-2 px-6 bg-white border-b border-gray-200 transition-colors duration-300">
    <div class="flex items-center gap-2 sm:gap-3">
        <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 -ml-2 text-gray-500 hover:text-gray-700:text-gray-200 focus:outline-none">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
        <div class="flex flex-col">
            <h1 class="titlePromise text-[1.5rem] font-semibold text-gray-700 leading-none">Promise</h1>
            <p class="hidden sm:block text-[0.7rem] text-gray-400 mt-1">Project Management Integrated System Engineering</p>
        </div>
    </div>

    <div class="flex items-center space-x-2 sm:space-x-4">

        @if(request()->is('all-dashboard') || request()->is('/'))
        <!-- Manual Navigation Buttons -->
        <div x-data class="hidden md:flex items-center mr-2">
            <div class="flex items-center border border-gray-200 rounded p-0.5">
                <button @click="$dispatch('prev-section')" class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-100 transition-colors text-gray-500" title="Previous Chart">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </button>
                <button @click="$dispatch('next-section')" class="w-7 h-7 flex items-center justify-center rounded hover:bg-gray-100 transition-colors text-gray-500" title="Next Chart">
                    <i class="fa-solid fa-chevron-right text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Configurasi Filter Button -->
        <button x-data @click="$dispatch('open-filter-modal')" class="hidden md:flex items-center justify-center w-9 h-9 rounded-full hover:bg-gray-100 transition-colors duration-200 focus:outline-none text-gray-500 mr-2" title="Configurasi Filter">
            <i class="fa-solid fa-gear text-xl hover:rotate-90 transition-all duration-300"></i>
        </button>
        @endif

        <!-- Real-time Clock Component -->
        <div class="hidden md:flex items-center border-r border-gray-200 pr-5 mr-5 text-xl font-medium text-gray-600 tabular-nums"
            x-data="{
                dateStr: '',
                timeStr: '',
                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                },
                updateTime() {
                    const now = new Date();
                    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    const dayName = days[now.getDay()];
                    const day = String(now.getDate()).padStart(2, '0');
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const year = now.getFullYear();
                    
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    const seconds = String(now.getSeconds()).padStart(2, '0');
                    
                    this.dateStr = `${dayName}, ${day}/${month}/${year}`;
                    this.timeStr = `${hours}:${minutes}:${seconds}`;
                }
            }">
            <span x-text="dateStr"></span>
            <div class="mx-3 h-5 border-l border-gray-200"></div>
            <span x-text="timeStr" class="inline-block w-[90px] text-center"></span>
        </div>



        <!-- Apps Grid Menu -->
        <div x-data="{ appsDropdownOpen: false }" class="relative ml-1 sm:ml-2 flex-shrink-0">
            <button @click="appsDropdownOpen = !appsDropdownOpen"
                class="flex items-center justify-center w-9 h-9 rounded-full hover:bg-gray-100 transition-colors duration-200 focus:outline-none text-gray-500" title="Apps Menu">
                <i class="fa-solid fa-grip text-xl"></i>
            </button>

            <div x-show="appsDropdownOpen"
                @click.away="appsDropdownOpen = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-100 p-4 z-50 origin-top-right"
                style="display: none;">

                <div class="grid grid-cols-4 gap-1">
                    <a href="{{ env('APP_DRAWING_URL') }}"
                        class="flex flex-col items-center justify-center p-2 rounded-xl hover:bg-gray-50 transition-all duration-200 group text-center">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center bg-indigo-50 text-indigo-600 mb-2 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fa-solid fa-pen-ruler text-lg"></i>
                        </div>
                        <span class="text-[0.65rem] font-semibold text-gray-700">Drawing</span>
                    </a>

                    <a href="{{ env('APP_INVENTORY_URL') }}"
                        class="flex flex-col items-center justify-center p-2 rounded-xl hover:bg-gray-50 transition-all duration-200 group text-center">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 mb-2 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fa-solid fa-boxes-stacked text-lg"></i>
                        </div>
                        <span class="text-[0.65rem] font-semibold text-gray-700">Inventory</span>
                    </a>

                    <a href="{{ env('APP_NPC_URL') }}"
                        class="flex flex-col items-center justify-center p-2 rounded-xl hover:bg-gray-50 transition-all duration-200 group text-center">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center bg-purple-50 text-purple-600 mb-2 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fa-solid fa-users-gear text-lg"></i>
                        </div>
                        <span class="text-[0.65rem] font-semibold text-gray-700">NPC</span>
                    </a>

                    <a href="{{ env('APP_ALL_DASHBOARD_URL') }}"
                        class="flex flex-col items-center justify-center p-2 rounded-xl hover:bg-gray-50 transition-all duration-200 group text-center">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center bg-teal-50 text-teal-600 mb-2 group-hover:scale-110 transition-transform shadow-sm">
                            <i class="fa-solid fa-chart-pie text-lg"></i>
                        </div>
                        <span class="text-[0.65rem] font-semibold text-gray-700 leading-tight">All Dashboard</span>
                    </a>
                </div>
            </div>
        </div>

        <div x-data="{ userDropdownOpen: false }" class="relative ml-1 sm:ml-2 flex-shrink-0">

            <button @click="userDropdownOpen = !userDropdownOpen"
                class="flex items-center space-x-2 p-1 sm:p-1.5 rounded-full hover:bg-gray-100 transition-colors duration-200 focus:outline-none cursor-pointer">

                <div class="hidden sm:flex flex-col text-right ml-1">
                    <span class="text-sm font-semibold text-gray-700 leading-tight">{{ Auth::user()->name ?? 'Guest' }}</span>
                    <span class="text-[0.65rem] text-gray-500">{{ Auth::user()->department->code ?? '' }}</span>
                </div>

                <div class="relative w-8 h-8 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center text-gray-500">
                    <i class="fa-solid fa-circle-user text-2xl"></i>
                </div>

                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 hidden sm:block pr-1 transition-transform duration-200"
                    :class="{'rotate-180': userDropdownOpen}"></i>
            </button>

            <div x-show="userDropdownOpen"
                @click.away="userDropdownOpen = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-1.5 mr-1.5 w-48 bg-white rounded-lg shadow-xl py-1 z-50 origin-top-right"
                style="display: none;">

                <div class="px-4 py-2 border-b border-gray-100">
                    <div class="hidden sm:flex flex-col text-left mb-2">
                        <span class="text-sm font-semibold text-gray-700">{{ Auth::user()->name ?? 'Guest'}}</span>
                        <span class="text-[0.65rem] text-gray-500">{{ Auth::user()->email ?? '' }}</span>
                    </div>

                    <a href="{{ env('APP_DRAWING_URL') }}"
                        class="flex items-center px-2 py-1.5 text-sm rounded-md transition-colors text-gray-700 hover:bg-gray-100">
                        <i class="fa-solid fa-pen-ruler w-5"></i>
                        <span class="ml-2">Drawing</span>
                    </a>

                    <a href="{{ env('APP_INVENTORY_URL') }}"
                        class="flex items-center px-2 py-1.5 text-sm rounded-md transition-colors mt-1 text-gray-700 hover:bg-gray-100">
                        <i class="fa-solid fa-boxes-stacked w-5"></i>
                        <span class="ml-2">Inventory</span>
                    </a>

                    <a href="{{ env('APP_NPC_URL') }}"
                        class="flex items-center px-2 py-1.5 text-sm rounded-md transition-colors mt-1 text-gray-700 hover:bg-gray-100">
                        <i class="fa-solid fa-users-gear w-5"></i>
                        <span class="ml-2">NPC</span>
                    </a>
                </div>
                <div class="px-1 py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors duration-200 cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket w-5"></i>
                            <span class="ml-2">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>