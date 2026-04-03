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
    <link rel="stylesheet" href="{{ asset('css/components/budget-box.css') }}">
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
                    <div class="weather-widget" id="weatherWidget">
                        <span class="weather-icon">☀️</span>
                        <span class="weather-temp">--°C</span>
                        <span class="weather-separator">|</span>
                        <span class="weather-desc">Loading...</span>
                        
                        <!-- Weather Tooltip -->
                        <div class="weather-tooltip" id="weatherTooltip">
                            <div class="weather-tooltip-header">
                                <h4 id="tooltipLocation">Sibu, Malaysia</h4>
                                <p id="tooltipDate">Monday, 23 February 2026</p>
                            </div>
                            
                            <div class="weather-tooltip-current">
                                <div class="tooltip-temp-large">
                                    <span class="tooltip-icon">☀️</span>
                                    <span class="tooltip-temp" id="tooltipTemp">24°C</span>
                                </div>
                                <div class="tooltip-condition">
                                    <p id="tooltipCondition">Clear</p>
                                    <p id="tooltipFeelsLike">Temperature 24°C</p>
                                </div>
                            </div>
                            
                            <div class="weather-tooltip-details">
                                <h5>Current Information</h5>
                                <div class="tooltip-detail-row">
                                    <div class="tooltip-detail">
                                        <span class="detail-label">Feels Like</span>
                                        <span class="detail-value" id="tooltipFeelsLikeValue">26°C</span>
                                    </div>
                                    <div class="tooltip-detail">
                                        <span class="detail-label">Humidity</span>
                                        <span class="detail-value" id="tooltipHumidity">70%</span>
                                    </div>
                                </div>
                                <div class="tooltip-detail-row">
                                    <div class="tooltip-detail">
                                        <span class="detail-label">Wind Speed</span>
                                        <span class="detail-value" id="tooltipWind">5 km/h</span>
                                    </div>
                                    <div class="tooltip-detail">
                                        <span class="detail-label">Pressure</span>
                                        <span class="detail-value" id="tooltipPressure">1013 hPa</span>
                                    </div>
                                </div>
                                <div class="tooltip-detail-row">
                                    <div class="tooltip-detail">
                                        <span class="detail-label">Visibility</span>
                                        <span class="detail-value" id="tooltipVisibility">10 km</span>
                                    </div>
                                    <div class="tooltip-detail">
                                        <span class="detail-label">UV Index</span>
                                        <span class="detail-value" id="tooltipUV">5</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="weather-tooltip-forecast">
                                <div class="forecast-header">
                                    <h5>3-Hour Forecast (2 Days)</h5>
                                    <div class="forecast-nav">
                                        <button class="forecast-nav-btn" id="forecastPrev">‹</button>
                                        <button class="forecast-nav-btn" id="forecastNext">›</button>
                                    </div>
                                </div>
                                <div class="forecast-scroll-container">
                                    <div class="forecast-scroll" id="forecastScroll">
                                        <!-- Forecast items will be populated by JavaScript -->
                                    </div>
                                </div>
                            </div>
                            
                            <div class="weather-tooltip-footer">
                                <p id="tooltipUpdated">Updated: 4 months ago</p>
                                <p class="footer-time" id="tooltipTime">02:05 PM</p>
                            </div>
                        </div>
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
    
    <script>
    // Weather Widget
    let lastWeatherUpdate = null;
    
    async function fetchWeather() {
        try {
            const response = await fetch('/api/weather');
            const data = await response.json();
            
            if (data.success) {
                const widget = document.getElementById('weatherWidget');
                const icon = widget.querySelector('.weather-icon');
                const temp = widget.querySelector('.weather-temp');
                const desc = widget.querySelector('.weather-desc');
                
                // Weather icon mapping
                const iconMap = {
                    'Clear': '☀️',
                    'Clouds': '☁️',
                    'Rain': '🌧️',
                    'Drizzle': '🌦️',
                    'Thunderstorm': '⛈️',
                    'Snow': '❄️',
                    'Mist': '🌫️',
                    'Fog': '🌫️',
                    'Haze': '🌫️'
                };
                
                const weatherIcon = iconMap[data.weather] || '🌤️';
                
                // Update widget
                icon.textContent = weatherIcon;
                temp.textContent = Math.round(data.temperature) + '°C';
                desc.textContent = data.description;
                
                // Update tooltip header
                document.getElementById('tooltipLocation').textContent = data.location + (data.country ? ', ' + data.country : '');
                
                // Format date in English
                const now = new Date();
                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                const dateStr = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
                document.getElementById('tooltipDate').textContent = dateStr;
                
                // Update current weather
                document.querySelector('.tooltip-temp-large .tooltip-icon').textContent = weatherIcon;
                document.getElementById('tooltipTemp').textContent = Math.round(data.temperature) + '°C';
                
                // Keep weather condition in English
                document.getElementById('tooltipCondition').textContent = data.description;
                document.getElementById('tooltipFeelsLike').textContent = 'Temperature ' + Math.round(data.temperature) + '°C';
                
                // Update details
                document.getElementById('tooltipFeelsLikeValue').textContent = Math.round(data.feels_like) + '°C';
                document.getElementById('tooltipHumidity').textContent = data.humidity + '%';
                document.getElementById('tooltipWind').textContent = data.wind_speed + ' km/j';
                document.getElementById('tooltipPressure').textContent = data.pressure + ' hPa';
                document.getElementById('tooltipVisibility').textContent = data.visibility + ' km';
                document.getElementById('tooltipUV').textContent = '5'; // Default UV index
                
                // Update forecast
                if (data.forecast && data.forecast.length > 0) {
                    const forecastScroll = document.getElementById('forecastScroll');
                    forecastScroll.innerHTML = '';
                    
                    const iconMap = {
                        'Clear': '☀️',
                        'Clouds': '☁️',
                        'Rain': '🌧️',
                        'Drizzle': '🌦️',
                        'Thunderstorm': '⛈️',
                        'Snow': '❄️',
                        'Mist': '🌫️',
                        'Fog': '🌫️',
                        'Haze': '🌫️'
                    };
                    
                    data.forecast.forEach(function(item) {
                        const forecastDate = new Date(item.time * 1000);
                        const hours = forecastDate.getHours().toString().padStart(2, '0');
                        const minutes = forecastDate.getMinutes().toString().padStart(2, '0');
                        const timeStr = hours + ':' + minutes;
                        
                        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        const dayStr = dayNames[forecastDate.getDay()];
                        
                        const weatherIcon = iconMap[item.weather] || '🌤️';
                        
                        const forecastItem = document.createElement('div');
                        forecastItem.className = 'forecast-item';
                        forecastItem.innerHTML = `
                            <div class="forecast-time">${timeStr}</div>
                            <div class="forecast-day">${dayStr}</div>
                            <div class="forecast-icon">${weatherIcon}</div>
                            <div class="forecast-temp">${Math.round(item.temp)}°C</div>
                            <div class="forecast-desc">${item.description}</div>
                            <div class="forecast-details">
                                <div class="forecast-detail">💧 ${item.humidity}%</div>
                                <div class="forecast-detail">💨 ${item.wind_speed} km/h</div>
                            </div>
                        `;
                        forecastScroll.appendChild(forecastItem);
                    });
                    
                    // Initialize forecast navigation
                    initForecastNavigation();
                }
                
                // Update footer
                lastWeatherUpdate = now;
                updateLastUpdatedTime();
                
                // Update time
                const hours = now.getHours().toString().padStart(2, '0');
                const minutes = now.getMinutes().toString().padStart(2, '0');
                const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
                const displayHours = now.getHours() > 12 ? now.getHours() - 12 : (now.getHours() === 0 ? 12 : now.getHours());
                document.getElementById('tooltipTime').textContent = displayHours.toString().padStart(2, '0') + ':' + minutes + ' ' + ampm;
            }
        } catch (error) {
            console.error('Weather fetch error:', error);
        }
    }
    
    function updateLastUpdatedTime() {
        if (!lastWeatherUpdate) return;
        
        const now = new Date();
        const diff = Math.floor((now - lastWeatherUpdate) / 1000); // seconds
        
        let timeText = '';
        if (diff < 60) {
            timeText = 'sebentar tadi';
        } else if (diff < 3600) {
            const minutes = Math.floor(diff / 60);
            timeText = minutes + ' minit yang lalu';
        } else if (diff < 86400) {
            const hours = Math.floor(diff / 3600);
            timeText = hours + ' jam yang lalu';
        } else {
            const days = Math.floor(diff / 86400);
            timeText = days + ' hari yang lalu';
        }
        
        document.getElementById('tooltipUpdated').textContent = 'Dikemas kini: ' + timeText;
    }
    
    // Weather tooltip hover functionality
    const weatherWidget = document.getElementById('weatherWidget');
    const weatherTooltip = document.getElementById('weatherTooltip');
    let tooltipTimeout;
    
    weatherWidget.addEventListener('mouseenter', function() {
        clearTimeout(tooltipTimeout);
        weatherTooltip.classList.add('show');
        updateLastUpdatedTime();
    });
    
    weatherWidget.addEventListener('mouseleave', function() {
        tooltipTimeout = setTimeout(function() {
            weatherTooltip.classList.remove('show');
        }, 200);
    });
    
    weatherTooltip.addEventListener('mouseenter', function() {
        clearTimeout(tooltipTimeout);
    });
    
    weatherTooltip.addEventListener('mouseleave', function() {
        weatherTooltip.classList.remove('show');
    });
    
    // Fetch weather on page load
    fetchWeather();
    
    // Refresh weather every 30 minutes
    setInterval(fetchWeather, 30 * 60 * 1000);
    
    // Update "last updated" time every minute
    setInterval(updateLastUpdatedTime, 60 * 1000);
    
    // Forecast navigation - Pagination style (show 3 items per page)
    function initForecastNavigation() {
        const forecastScroll = document.getElementById('forecastScroll');
        const prevBtn = document.getElementById('forecastPrev');
        const nextBtn = document.getElementById('forecastNext');
        
        if (!forecastScroll || !prevBtn || !nextBtn) return;
        
        let currentPage = 0;
        const itemsPerPage = 3;
        const itemWidth = 85; // width of each forecast item (updated)
        const gap = 8; // gap between items
        const pageWidth = (itemWidth * itemsPerPage) + (gap * (itemsPerPage - 1)); // 271px
        
        // Update button states
        function updateButtons() {
            const totalItems = forecastScroll.children.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            
            prevBtn.disabled = currentPage <= 0;
            nextBtn.disabled = currentPage >= totalPages - 1;
        }
        
        // Go to previous page (show previous 3 items)
        prevBtn.addEventListener('click', function() {
            if (currentPage > 0) {
                currentPage--;
                const scrollPosition = currentPage * pageWidth;
                forecastScroll.scrollTo({
                    left: scrollPosition,
                    behavior: 'smooth'
                });
                setTimeout(updateButtons, 300);
            }
        });
        
        // Go to next page (show next 3 items)
        nextBtn.addEventListener('click', function() {
            const totalItems = forecastScroll.children.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            
            if (currentPage < totalPages - 1) {
                currentPage++;
                const scrollPosition = currentPage * pageWidth;
                forecastScroll.scrollTo({
                    left: scrollPosition,
                    behavior: 'smooth'
                });
                setTimeout(updateButtons, 300);
            }
        });
        
        // Initial button state - wait for DOM to be fully rendered
        setTimeout(function() {
            updateButtons();
        }, 100);
    }
    </script>
</body>
</html>
