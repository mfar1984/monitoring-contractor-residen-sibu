@extends('layouts.app')

@section('title', 'Integrations - Webhook - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Integrations</span>
    <span class="breadcrumb-separator">›</span>
    <span>Webhook</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.integrations.email') }}" class="tab-button">Email</a>
            <a href="{{ route('pages.integrations.sms') }}" class="tab-button">SMS</a>
            <a href="{{ route('pages.integrations.webhook') }}" class="tab-button active">Webhook</a>
            <a href="{{ route('pages.integrations.api') }}" class="tab-button">API</a>
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
                    <h3>Webhook Configuration</h3>
                    <p class="content-description">Configure webhook endpoints for external integrations.</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <form method="POST" action="{{ route('pages.integrations.webhook.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="webhook_url">Webhook URL <span style="color: #dc3545;">*</span></label>
                        <input type="url" id="webhook_url" name="webhook_url" placeholder="https://example.com/webhook" value="{{ $settings['webhook_url'] ?? '' }}" required>
                    </div>

                    <div class="form-group">
                        <label for="webhook_secret">Secret Key</label>
                        <input type="password" id="webhook_secret" name="webhook_secret" placeholder="{{ isset($settings['webhook_secret']) && $settings['webhook_secret'] ? '••••••••' : 'Enter secret key for webhook verification' }}" value="">
                        @if(isset($settings['webhook_secret']) && $settings['webhook_secret'])
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Leave blank to keep current secret key</small>
                        @else
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Optional: Used for webhook signature verification</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="webhook_events">Events (comma-separated)</label>
                        <input type="text" id="webhook_events" name="webhook_events" placeholder="user.created, project.updated" value="{{ $settings['webhook_events'] ?? '' }}">
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Optional: Specify which events trigger the webhook</small>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="openTestModal()">Test</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Test Webhook Modal -->
    <div id="testWebhookModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Test Webhook Configuration</h3>
                <button class="modal-close" onclick="closeTestModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <p style="color: #666666; font-size: 12px; margin-bottom: 15px;">Send a test payload to your webhook endpoint to verify the configuration.</p>
                <div id="testResult" style="display: none; padding: 12px; border-radius: 4px; margin-top: 15px; font-size: 12px;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeTestModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendTestWebhook()">Send Test</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function openTestModal() {
    document.getElementById('testWebhookModal').classList.add('show');
    document.getElementById('testResult').style.display = 'none';
}

function closeTestModal() {
    document.getElementById('testWebhookModal').classList.remove('show');
    document.getElementById('testResult').style.display = 'none';
}

function sendTestWebhook() {
    const testResult = document.getElementById('testResult');
    
    // Show loading
    testResult.style.display = 'block';
    testResult.style.backgroundColor = '#d1ecf1';
    testResult.style.color = '#0c5460';
    testResult.style.border = '1px solid #bee5eb';
    testResult.textContent = 'Sending test webhook...';
    
    // Note: Test functionality is disabled for now
    setTimeout(() => {
        testResult.style.backgroundColor = '#fff3cd';
        testResult.style.color = '#856404';
        testResult.style.border = '1px solid #ffeaa7';
        testResult.textContent = 'Test functionality is currently disabled. Please save your configuration first.';
    }, 1000);
}

// Close modal when clicking outside
document.getElementById('testWebhookModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeTestModal();
    }
});
</script>
@endsection
