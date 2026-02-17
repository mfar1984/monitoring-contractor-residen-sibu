<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $appSettings = \App\Models\IntegrationSetting::getSettings('application');
        $appName = $appSettings['app_name'] ?? config('app.name', 'Monitoring System');
        $sidebarName = $appSettings['sidebar_name'] ?? $appName;
        $sidebarDisplay = $appSettings['sidebar_display'] ?? 'name_only';
        $sidebarLogo = $appSettings['sidebar_logo'] ?? null;
    @endphp
    <title>@yield('title', $appName)</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/tabs.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/table.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/pagination.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/buttons.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/forms.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/content-header.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/modal.css') }}">
    @stack('styles')
</head>
<body>
    <div class="app-container">
        <div class="header-area">
            <!-- Logo (spans 2 rows) -->
            <div class="logo-area">
                <div class="logo-container">
                    @if($sidebarDisplay === 'logo_only' || $sidebarDisplay === 'logo_and_name')
                        @if($sidebarLogo)
                            <img src="{{ asset('storage/' . $sidebarLogo) }}" alt="Logo" style="width: 40px; height: 40px; object-fit: contain;">
                        @else
                            <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="20" cy="20" r="18" fill="#007bff" stroke="#0056b3" stroke-width="2"/>
                                <text x="20" y="26" font-family="Arial" font-size="16" font-weight="bold" fill="white" text-anchor="middle">MS</text>
                            </svg>
                        @endif
                    @endif
                    
                    @if($sidebarDisplay === 'name_only' || $sidebarDisplay === 'logo_and_name')
                        <span class="logo-text">{{ $sidebarName }}</span>
                    @endif
                </div>
            </div>
            
            <!-- Header -->
            <div class="header-area-content">
                <x-layout.header />
            </div>
            
            <!-- Breadcrumb -->
            <div class="breadcrumb-area-content">
                <div class="breadcrumb-container">
                    <div class="breadcrumb">
                        @yield('breadcrumb', 'Dashboard')
                    </div>
                </div>
            </div>
        </div>
        
        <div class="main-area">
            <div class="sidebar-area">
                <x-layout.sidebar />
            </div>
            <div class="content-area-wrapper">
                <main class="content-area">
                    @yield('alerts')
                    @yield('content')
                </main>
                <x-layout.footer />
            </div>
        </div>
    </div>
    
    @yield('scripts')
    @stack('scripts')
</body>
</html>
