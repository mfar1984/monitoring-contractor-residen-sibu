@extends('layouts.app')

@section('title', 'Integrations - Weather - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Integrations</span>
    <span class="breadcrumb-separator">›</span>
    <span>Weather</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.integrations.email') }}" class="tab-button">Email</a>
            <a href="{{ route('pages.integrations.sms') }}" class="tab-button">SMS</a>
            <a href="{{ route('pages.integrations.webhook') }}" class="tab-button">Webhook</a>
            <a href="{{ route('pages.integrations.api') }}" class="tab-button">API</a>
            <a href="{{ route('pages.integrations.weather') }}" class="tab-button active">Weather</a>
        </div>
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
            @endif

            <div class="content-header">
                <div class="content-header-left">
                    <h3>Weather Configuration (OpenWeatherMap)</h3>
                    <p class="content-description">Configure OpenWeatherMap API for weather data integration.</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <form method="POST" action="{{ route('pages.integrations.weather.store') }}" id="weatherForm" onsubmit="return validateForm(event)">
                    @csrf
                    <div class="form-group">
                        <label for="api_key">API Key <span style="color: #dc3545;">*</span></label>
                        <input type="password" id="api_key" name="api_key" placeholder="{{ isset($settings['api_key']) && $settings['api_key'] ? '••••••••' : 'Enter OpenWeatherMap API key' }}" {{ isset($settings['api_key']) && $settings['api_key'] ? '' : 'required' }}>
                        @if(isset($settings['api_key']) && $settings['api_key'])
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Leave blank to keep current API key. Get your API key from <a href="https://openweathermap.org/api" target="_blank" style="color: #007bff;">openweathermap.org</a></small>
                        @else
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Get your API key from <a href="https://openweathermap.org/api" target="_blank" style="color: #007bff;">openweathermap.org</a></small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="base_url">Base URL <span style="color: #dc3545;">*</span></label>
                        <input type="url" id="base_url" name="base_url" placeholder="https://api.openweathermap.org/data/2.5" value="{{ $settings['base_url'] ?? 'https://api.openweathermap.org/data/2.5' }}" required>
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">OpenWeatherMap API base URL</small>
                    </div>

                    <div class="form-group">
                        <label for="location">Default Location <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="location" name="location" placeholder="Kuching, MY" value="{{ $settings['location'] ?? '' }}" required onchange="detectCoordinates()">
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">City name and country code (e.g., Kuching, MY). Coordinates will be auto-detected.</small>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="latitude">Latitude</label>
                            <input type="text" id="latitude" name="latitude" placeholder="Auto-detected" value="{{ $settings['latitude'] ?? '' }}" readonly>
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Auto-detected from location</small>
                        </div>

                        <div class="form-group">
                            <label for="longitude">Longitude</label>
                            <input type="text" id="longitude" name="longitude" placeholder="Auto-detected" value="{{ $settings['longitude'] ?? '' }}" readonly>
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Auto-detected from location</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="units">Temperature Units <span style="color: #dc3545;">*</span></label>
                        <select id="units" name="units" required>
                            <option value="">Select Units</option>
                            <option value="metric" {{ ($settings['units'] ?? '') == 'metric' ? 'selected' : '' }}>Metric (Celsius)</option>
                            <option value="imperial" {{ ($settings['units'] ?? '') == 'imperial' ? 'selected' : '' }}>Imperial (Fahrenheit)</option>
                        </select>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="openTestModal()">Test</button>
                        <button type="submit" class="btn btn-primary" id="saveButton">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Test Weather Modal -->
    <div id="testWeatherModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Test Weather API Configuration</h3>
                <button class="modal-close" onclick="closeTestModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <p style="color: #666666; font-size: 12px; margin-bottom: 15px;">Test the OpenWeatherMap API connection to verify your configuration.</p>
                <div id="testResult" style="display: none; padding: 16px; border-radius: 4px; margin-top: 15px; font-size: 12px; line-height: 1.8; font-family: 'Courier New', monospace;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeTestModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendTestWeather()">Test Connection</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
let isDetecting = false;

// Auto-detect coordinates when page loads if location exists
document.addEventListener('DOMContentLoaded', function() {
    const location = document.getElementById('location').value;
    if (location && location.trim() !== '') {
        detectCoordinates();
    }
});

function detectCoordinates() {
    const location = document.getElementById('location').value;
    const latitudeField = document.getElementById('latitude');
    const longitudeField = document.getElementById('longitude');
    
    if (!location || location.trim() === '') {
        latitudeField.value = '';
        longitudeField.value = '';
        return Promise.resolve();
    }
    
    // Show loading state
    isDetecting = true;
    latitudeField.value = 'Detecting...';
    longitudeField.value = 'Detecting...';
    
    // Use OpenStreetMap Nominatim API
    return fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(location)}&format=json&limit=1`, {
        headers: {
            'User-Agent': 'MonitoringSystem/1.0'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat).toFixed(4);
                const lon = parseFloat(data[0].lon).toFixed(4);
                
                latitudeField.value = lat;
                longitudeField.value = lon;
            } else {
                latitudeField.value = '';
                longitudeField.value = '';
                alert('Location not found. Please check your location format (e.g., Kuching, MY)');
            }
            isDetecting = false;
        })
        .catch(error => {
            console.error('Error detecting coordinates:', error);
            latitudeField.value = '';
            longitudeField.value = '';
            alert('Failed to detect coordinates. Please try again.');
            isDetecting = false;
        });
}

function validateForm(event) {
    const latitudeField = document.getElementById('latitude');
    const longitudeField = document.getElementById('longitude');
    const locationField = document.getElementById('location');
    
    // If location is filled but coordinates are empty or still detecting
    if (locationField.value && locationField.value.trim() !== '') {
        if (!latitudeField.value || !longitudeField.value || 
            latitudeField.value === 'Detecting...' || longitudeField.value === 'Detecting...') {
            
            if (isDetecting) {
                alert('Please wait while coordinates are being detected...');
                event.preventDefault();
                return false;
            }
            
            // Try to detect coordinates one more time
            event.preventDefault();
            const saveButton = document.getElementById('saveButton');
            saveButton.disabled = true;
            saveButton.textContent = 'Detecting...';
            
            detectCoordinates().then(() => {
                saveButton.disabled = false;
                saveButton.textContent = 'Save';
                
                // Check if coordinates were detected
                if (latitudeField.value && longitudeField.value && 
                    latitudeField.value !== 'Detecting...' && longitudeField.value !== 'Detecting...') {
                    document.getElementById('weatherForm').submit();
                } else {
                    alert('Unable to detect coordinates. Please check your location format.');
                }
            });
            
            return false;
        }
    }
    
    return true;
}

function openTestModal() {
    document.getElementById('testWeatherModal').classList.add('show');
    document.getElementById('testResult').style.display = 'none';
}

function closeTestModal() {
    document.getElementById('testWeatherModal').classList.remove('show');
    document.getElementById('testResult').style.display = 'none';
}

function sendTestWeather() {
    const testResult = document.getElementById('testResult');
    
    // Show loading
    testResult.style.display = 'block';
    testResult.style.backgroundColor = '#d1ecf1';
    testResult.style.color = '#0c5460';
    testResult.style.border = '1px solid #bee5eb';
    testResult.innerHTML = 'Testing weather API connection...';
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value;
    
    // Send AJAX request
    fetch('{{ route("pages.integrations.weather.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            testResult.style.backgroundColor = '#d4edda';
            testResult.style.color = '#155724';
            testResult.style.border = '1px solid #c3e6cb';
            testResult.style.lineHeight = '1.8';
            testResult.innerHTML = data.message;
        } else {
            testResult.style.backgroundColor = '#f8d7da';
            testResult.style.color = '#721c24';
            testResult.style.border = '1px solid #f5c6cb';
            testResult.style.lineHeight = '1.8';
            testResult.innerHTML = data.message;
        }
    })
    .catch(error => {
        testResult.style.backgroundColor = '#f8d7da';
        testResult.style.color = '#721c24';
        testResult.style.border = '1px solid #f5c6cb';
        testResult.style.lineHeight = '1.8';
        testResult.innerHTML = 'Error: Failed to test weather API. Please check your configuration.';
        console.error('Error:', error);
    });
}

// Close modal when clicking outside
document.getElementById('testWeatherModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeTestModal();
    }
});
</script>
@endsection
