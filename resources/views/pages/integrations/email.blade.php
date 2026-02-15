@extends('layouts.app')

@section('title', 'Integrations - Email - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Integrations</span>
    <span class="breadcrumb-separator">›</span>
    <span>Email</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.integrations.email') }}" class="tab-button active">Email</a>
            <a href="{{ route('pages.integrations.sms') }}" class="tab-button">SMS</a>
            <a href="{{ route('pages.integrations.webhook') }}" class="tab-button">Webhook</a>
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
                    <h3>Email Configuration (SMTP)</h3>
                    <p class="content-description">Configure SMTP settings for sending emails from the system.</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <form method="POST" action="{{ route('pages.integrations.email.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="smtp_host">SMTP Host <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="smtp_host" name="smtp_host" placeholder="smtp.gmail.com" value="{{ $settings['smtp_host'] ?? '' }}" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="smtp_port">SMTP Port <span style="color: #dc3545;">*</span></label>
                            <input type="number" id="smtp_port" name="smtp_port" placeholder="587" value="{{ $settings['smtp_port'] ?? '' }}" required>
                        </div>

                        <div class="form-group">
                            <label for="smtp_encryption">Encryption <span style="color: #dc3545;">*</span></label>
                            <select id="smtp_encryption" name="smtp_encryption" required>
                                <option value="">Select Encryption</option>
                                <option value="tls" {{ ($settings['smtp_encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ ($settings['smtp_encryption'] ?? '') == 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="smtp_username">SMTP Username <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="smtp_username" name="smtp_username" placeholder="your-email@example.com" value="{{ $settings['smtp_username'] ?? '' }}" required>
                    </div>

                    <div class="form-group">
                        <label for="smtp_password">SMTP Password <span style="color: #dc3545;">*</span></label>
                        <input type="password" id="smtp_password" name="smtp_password" placeholder="{{ isset($settings['smtp_password']) && $settings['smtp_password'] ? '••••••••' : 'Enter SMTP password' }}" {{ isset($settings['smtp_password']) && $settings['smtp_password'] ? '' : 'required' }}>
                        @if(isset($settings['smtp_password']) && $settings['smtp_password'])
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Leave blank to keep current password</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="smtp_from_email">From Email <span style="color: #dc3545;">*</span></label>
                        <input type="email" id="smtp_from_email" name="smtp_from_email" placeholder="noreply@example.com" value="{{ $settings['smtp_from_email'] ?? '' }}" required>
                    </div>

                    <div class="form-group">
                        <label for="smtp_from_name">From Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="smtp_from_name" name="smtp_from_name" placeholder="Monitoring System" value="{{ $settings['smtp_from_name'] ?? '' }}" required>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="openTestModal()">Test</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Test Email Modal -->
    <div id="testEmailModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Test Email Configuration</h3>
                <button class="modal-close" onclick="closeTestModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <p style="color: #666666; font-size: 12px; margin-bottom: 15px;">Enter an email address to send a test email and verify your SMTP configuration.</p>
                <form id="testEmailForm">
                    <div class="form-group">
                        <label for="test_email">Test Email Address <span style="color: #dc3545;">*</span></label>
                        <input type="email" id="test_email" name="test_email" placeholder="test@example.com" required>
                    </div>
                    <div id="testResult" style="display: none; padding: 12px; border-radius: 4px; margin-top: 15px; font-size: 12px;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeTestModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendTestEmail()">Send Test</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function openTestModal() {
    document.getElementById('testEmailModal').classList.add('show');
    document.getElementById('testResult').style.display = 'none';
}

function closeTestModal() {
    document.getElementById('testEmailModal').classList.remove('show');
    document.getElementById('test_email').value = '';
    document.getElementById('testResult').style.display = 'none';
}

function sendTestEmail() {
    const testEmail = document.getElementById('test_email').value;
    const testResult = document.getElementById('testResult');
    
    if (!testEmail) {
        testResult.style.display = 'block';
        testResult.style.backgroundColor = '#f8d7da';
        testResult.style.color = '#721c24';
        testResult.style.border = '1px solid #f5c6cb';
        testResult.textContent = 'Please enter a valid email address.';
        return;
    }
    
    // Show loading
    testResult.style.display = 'block';
    testResult.style.backgroundColor = '#d1ecf1';
    testResult.style.color = '#0c5460';
    testResult.style.border = '1px solid #bee5eb';
    testResult.textContent = 'Sending test email...';
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                      document.querySelector('input[name="_token"]')?.value;
    
    // Send AJAX request
    fetch('{{ route("pages.integrations.email.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            test_email: testEmail
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            testResult.style.backgroundColor = '#d4edda';
            testResult.style.color = '#155724';
            testResult.style.border = '1px solid #c3e6cb';
            testResult.textContent = data.message;
        } else {
            testResult.style.backgroundColor = '#f8d7da';
            testResult.style.color = '#721c24';
            testResult.style.border = '1px solid #f5c6cb';
            testResult.textContent = data.message;
        }
    })
    .catch(error => {
        testResult.style.backgroundColor = '#f8d7da';
        testResult.style.color = '#721c24';
        testResult.style.border = '1px solid #f5c6cb';
        testResult.textContent = 'Error: Failed to send test email. Please check your configuration.';
        console.error('Error:', error);
    });
}

// Close modal when clicking outside
document.getElementById('testEmailModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeTestModal();
    }
});
</script>
@endsection
