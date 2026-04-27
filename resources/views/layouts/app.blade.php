<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 Custom Styles (from promise-drawing) -->
    <style>
        .select2-container { vertical-align: middle; }
        .select2-container--default .select2-selection--single {
            position: relative; border-radius: 1px; height: 2.375rem; border: 1px solid #d1d5db;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 2.25rem; padding-left: 0.75rem; color: #3f3f3f; font-size: 0.875rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            top: 0; right: 15px; height: 60%; width: 2.5rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border: 0; width: 100%; height: 100%;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-repeat: no-repeat; background-position: center; background-size: 1.25rem;
        }
        .select2-search--dropdown { padding: 0; padding-bottom: 0.5rem; }
        .select2-search--dropdown .select2-search__field {
            border-radius: 1px; border: 1px solid #d1d5db; color: #595959; font-size: 0.975rem;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field:focus,
        .select2-container--default .select2-search--dropdown .select2-search__field:focus-visible {
            outline: none; box-shadow: none; border: 1px solid #d1d5db;
        }
        .select2-results__option {
            font-size: 0.775rem; font-weight: 500; color: #595959; padding: 0.5rem 0.75rem; border-radius: 1px;
        }
        .select2-container--default .select2-results__option[aria-selected="true"]:not(.select2-results__option--highlighted),
        .select2-container--default .select2-results__option--selected:not(.select2-results__option--highlighted) {
            background-color: transparent !important; color: #0284c7 !important;
        }
        .select2-container--default .select2-results__option--highlighted {
            background-color: #e0f2fe !important; color: #0284c7 !important;
        }
        .select2-container--default .select2-dropdown {
            border: none; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); border-radius: 1px;
            overflow: hidden; background-color: white; padding: 0.5rem;
        }
        .select2-results__options { padding: 0 0.25rem 0 0; }
        .select2-results__options::-webkit-scrollbar { width: 5px; }
        .select2-results__options::-webkit-scrollbar-track { background: transparent; }
        .select2-results__options::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
        .select2-results__options::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
        .select2-container--default .select2-selection--single .select2-selection__rendered:empty { display: none; }
        .filter-pill { display: inline-flex; align-items: center; gap: 6px; background-color: #E0E7FF; border-radius: 9999px; padding: 4px 10px; font-size: 0.875rem; font-weight: 500; color: #3730A3; }
        .filter-pill-remove { background: none; border: none; cursor: pointer; padding: 0; margin-left: 2px; color: #4338CA; line-height: 1; }
        .filter-pill-remove:hover { color: #C7D2FE; }
        /* Fix Select2 z-index inside modal */
        .select2-container--open { z-index: 9999 !important; }
    </style>

    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-black font-sans antialiased min-h-screen">

    @include('components.header')

    <div class="pt-16">
        @yield('content')
    </div>

    <!-- jQuery + Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });</script>

    @stack('scripts')

</body>
</html>
