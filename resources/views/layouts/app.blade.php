<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
        
        <style>
            /* Force dark theme for all routes */
            body, html {
                background-color: rgb(16, 24, 39) !important;
                color: rgb(243, 244, 246) !important;
            }
            .bg-white {
                background-color: rgb(31, 41, 55) !important;
            }
            .bg-gray-100 {
                background-color: rgb(16, 24, 39) !important;
            }
            .bg-gray-50 {
                background-color: rgb(31, 41, 55) !important;
            }
            header {
                background-color: rgb(31, 41, 55) !important;
                color: rgb(243, 244, 246) !important;
            }
            main {
                background-color: rgb(16, 24, 39) !important;
            }
            /* Override common light theme classes */
            .text-gray-900 {
                color: rgb(243, 244, 246) !important;
            }
            .text-black {
                color: rgb(243, 244, 246) !important;
            }
            .border-gray-200 {
                border-color: rgb(55, 65, 81) !important;
            }
            /* Cards and containers */
            .bg-white, .bg-gray-50, .bg-gray-100 {
                background-color: rgb(31, 41, 55) !important;
                color: rgb(243, 244, 246) !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased dark">
        <x-banner />

        <div class="min-h-screen bg-gray-100 dark:bg-gray-900" 
             style="background-color: rgb(16, 24, 39) !important;">
            
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow" 
                        style="background-color: rgb(31, 41, 55) !important;">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8" 
                         style="background-color: rgb(31, 41, 55) !important;">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main style="background-color: rgb(16, 24, 39) !important;">
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>