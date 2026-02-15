@extends('layouts.app')

@section('title', 'Integrations - API - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Integrations</span>
    <span class="breadcrumb-separator">›</span>
    <span>API</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.integrations.email') }}" class="tab-button">Email</a>
            <a href="{{ route('pages.integrations.sms') }}" class="tab-button">SMS</a>
            <a href="{{ route('pages.integrations.webhook') }}" class="tab-button">Webhook</a>
            <a href="{{ route('pages.integrations.api') }}" class="tab-button active">API</a>
            <a href="{{ route('pages.integrations.weather') }}" class="tab-button">Weather</a>
        </div>
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->any())
            <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="content-header">
                <div class="content-header-left">
                    <h3>API Configuration</h3>
                    <p class="content-description">Manage API keys and access tokens for external services.</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <form method="POST" action="{{ route('pages.integrations.api.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="api_endpoint">API Endpoint <span style="color: #dc3545;">*</span></label>
                        <input type="url" id="api_endpoint" name="api_endpoint" placeholder="https://api.example.com" value="{{ $settings['api_endpoint'] ?? '' }}" required>
                    </div>

                    <div class="form-group">
                        <label for="api_key">API Key <span style="color: #dc3545;">*</span></label>
                        <input type="password" id="api_key" name="api_key" placeholder="{{ isset($settings['api_key']) && $settings['api_key'] ? '••••••••' : 'Enter API key' }}" {{ isset($settings['api_key']) && $settings['api_key'] ? '' : 'required' }}>
                        @if(isset($settings['api_key']) && $settings['api_key'])
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Leave blank to keep current API key</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="api_secret">API Secret</label>
                        <input type="password" id="api_secret" name="api_secret" placeholder="{{ isset($settings['api_secret']) && $settings['api_secret'] ? '••••••••' : 'Enter API secret (if required)' }}" value="">
                        @if(isset($settings['api_secret']) && $settings['api_secret'])
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Leave blank to keep current API secret</small>
                        @endif
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="openTestModal()">Test</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Test API Modal -->
    <div id="testApiModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Test API Configuration</h3>
                <button class="modal-close" onclick="closeTestModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <p style="color: #666666; font-size: 12px; margin-bottom: 15px;">Test the API connection to verify your configuration.</p>
                <div id="testResult" style="display: none; padding: 12px; border-radius: 4px; margin-top: 15px; font-size: 12px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeTestModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendTestApi()">Test Connection</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function openTestModal() {
    document.getElementById('testApiModal').classList.add('show');
    document.getElementById('testResult').style.display = 'none';
}

function closeTestModal() {
    document.getElementById('testApiModal').classList.remove('show');
    document.getElementById('testResult').style.display = 'none';
}

function sendTestApi() {
    const testResult = document.getElementById('testResult');
    
    // Show loading
    testResult.style.display = 'block';
    testResult.style.backgroundColor = '#d1ecf1';
    testResult.style.color = '#0c5460';
    testResult.style.border = '1px solid #bee5eb';
    testResult.textContent = 'Testing API connection...';
    
    // Note: Test functionality is disabled for now
    setTimeout(() => {
        testResult.style.backgroundColor = '#fff3cd';
        testResult.style.color = '#856404';
        testResult.style.border = '1px solid #ffeaa7';
        testResult.textContent = 'Test functionality is currently disabled. Please save your configuration first.';
    }, 1000);
}

// Close modal when clicking outside
document.getElementById('testApiModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeTestModal();
    }
});
</script>
@endsection
