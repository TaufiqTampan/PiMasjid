<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="pimasjid">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- PWA Primary Meta Tags -->
        <meta name="application-name" content="MasjidVision">
        <meta name="description" content="Sistem Manajemen Masjid Digital dengan Transparansi Keuangan">
        <meta name="theme-color" content="#10b981">
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="/manifest.json">
        
        <!-- Mobile Web App Meta Tags -->
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="MasjidVision">
        @php
            $favicon = \Illuminate\Support\Facades\Cache::remember('global_favicon', 60*60, function () {
                return \App\Models\Setting::where('key', 'favicon_path')->value('value');
            });
        @endphp
        <link rel="icon" type="image/x-icon" href="{{ $favicon ?? '/favicon.ico' }}">
        <link rel="apple-touch-icon" href="{{ $favicon ?? '/icon.svg' }}">
        
        <!-- Microsoft Windows Meta Tags -->
        <meta name="msapplication-TileColor" content="#10b981">
        <meta name="msapplication-config" content="/browserconfig.xml">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
