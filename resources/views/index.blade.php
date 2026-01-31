<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel View Analyzer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800" x-data="{ tab: 'used', openNamespaces: [] }">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h1 class="text-xl font-bold text-gray-900">View Analyzer</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <button @click="openNamespaces = []" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Collapse All</button>
                    <div class="text-sm text-gray-500">
                        v1.0.0
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Views</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_views'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Used Views</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['used_views'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Unused Views</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['unused_views'] }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="text-sm font-medium text-gray-500 uppercase tracking-wider">Controllers Scanned</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_controllers'] }}</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="tab = 'used'" 
                        :class="tab === 'used' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Used Views (Controller Map)
                    </button>
                    <button @click="tab = 'unused'" 
                        :class="tab === 'unused' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Unused Views
                        <span :class="tab === 'unused' ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-900'" class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium inline-block">
                            {{ count($unusedViews) }}
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content: Used Views -->
            <div x-show="tab === 'used'" class="space-y-6">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-gray-900">Controller Usage Map</h2>
                        <span class="px-2 py-1 text-xs font-medium bg-gray-200 text-gray-600 rounded-full">Hierarchy</span>
                    </div>
                    <div class="p-6">
                        @php $nsIndex = 0; @endphp
                        @foreach($controllerMap->groupBy('namespace')->sortKeys() as $namespace => $controllers)
                            @php $nsId = 'ns-' . $nsIndex++; @endphp
                            <div class="mb-6 last:mb-0" x-data="{ isOpen: true }" x-init="openNamespaces.push('{{ $nsId }}')">
                                <div class="flex items-center space-x-2 mb-2 cursor-pointer select-none group" @click="isOpen = !isOpen">
                                    <svg class="h-4 w-4 text-gray-400 transform transition-transform duration-200" :class="{ 'rotate-90': isOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                    <svg class="h-5 w-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <h3 class="font-bold text-gray-800 group-hover:text-indigo-600">{{ $namespace }}</h3>
                                    <span class="text-xs text-gray-400">({{ count($controllers) }} controllers)</span>
                                </div>

                                <div class="ml-4 pl-4 border-l border-gray-200 space-y-4" x-show="isOpen" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                                    @foreach($controllers as $controller)
                                        <div class="relative" x-data="{ isCtrlOpen: false }">
                                            <div class="flex items-center space-x-2 text-gray-700 font-medium cursor-pointer hover:text-indigo-600 select-none" @click="isCtrlOpen = !isCtrlOpen">
                                                <span class="text-gray-400">├─</span>
                                                <svg class="h-4 w-4 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                                </svg>
                                                <span>{{ $controller['controller'] }}</span>
                                                @if(!empty($controller['actions']))
                                                    <span class="text-[10px] bg-gray-100 px-1.5 py-0.5 rounded text-gray-500">{{ count($controller['actions']) }} actions</span>
                                                @endif
                                            </div>

                                            <div x-show="isCtrlOpen" x-cloak class="mt-2">
                                                @if(empty($controller['actions']))
                                                    <div class="ml-8 text-sm text-gray-400 flex items-center">
                                                        <span>└─ (no actions detected)</span>
                                                    </div>
                                                @else
                                                    <div class="ml-6 space-y-2">
                                                        @foreach($controller['actions'] as $action)
                                                            <div class="relative pl-6 border-l border-gray-200 ml-2">
                                                                <div class="flex items-center space-x-2 text-sm text-gray-600">
                                                                    <span class="absolute -left-[1px] top-1/2 -mt-px w-2 h-px bg-gray-200"></span>
                                                                    <svg class="h-3 w-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                                    </svg>
                                                                    <span class="font-semibold">{{ $action['action'] }}</span>
                                                                </div>

                                                                @if(!empty($action['views']))
                                                                    <div class="ml-5 mt-1 space-y-1">
                                                                        @foreach($action['views'] as $view)
                                                                            <div class="flex items-center space-x-2 text-xs text-gray-500">
                                                                                <span class="text-gray-300">└─</span>
                                                                                <svg class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                                                </svg>
                                                                                <span class="font-mono bg-gray-100 px-1 py-0.5 rounded">{{ $view }}</span>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Tab Content: Unused Views -->
            <div x-show="tab === 'unused'" x-cloak>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-red-50 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-red-800">Unused Views Detected</h2>
                        <span class="px-2 py-1 text-xs font-medium bg-red-200 text-red-800 rounded-full">Potentially Safe to Delete</span>
                    </div>
                    
                    @if(count($unusedViews) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">View Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Path</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Modified</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($unusedViews as $view)
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <svg class="h-4 w-4 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span class="text-sm font-mono font-medium text-gray-900">{{ $view->viewName }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $view->filePath);
                                                @endphp
                                                <span class="text-sm text-gray-500 font-mono">{{ $relativePath }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $view->toArray()['file_size_human'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $view->lastModified->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12 text-green-600">
                            <svg class="h-16 w-16 mx-auto text-green-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-lg font-medium">Clean Workspace!</h3>
                            <p class="mt-1">All registered views are currently being used in your application.</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>
</body>
</html>
