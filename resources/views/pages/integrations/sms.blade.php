@extends('layouts.app')

@section('title', 'Integrations - SMS - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Integrations</span>
    <span class="breadcrumb-separator">›</span>
    <span>SMS</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.integrations.email') }}" class="tab-button">Email</a>
            <a href="{{ route('pages.integrations.sms') }}" class="tab-button active">SMS</a>
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
                    <h3>SMS Configuration (InfoBlast)</h3>
                    <p class="content-description">Configure InfoBlast API for sending SMS notifications.</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <form method="POST" action="{{ route('pages.integrations.sms.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="api_url">API URL <span style="color: #dc3545;">*</span></label>
                        <input type="url" id="api_url" name="api_url" value="{{ $settings['api_url'] ?? 'https://www.infoblast.com.my/infoblastv2/' }}" required>
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">InfoBlast API endpoint</small>
                    </div>

                    <div class="form-group">
                        <label for="username">Username <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="username" name="username" value="{{ $settings['username'] ?? '' }}" required>
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">InfoBlast account username</small>
                    </div>

                    <div class="form-group">
                        <label for="password">Password <span style="color: #dc3545;">*</span></label>
                        <input type="password" id="password" name="password" placeholder="{{ isset($settings['password']) && $settings['password'] ? '••••••••' : 'Enter InfoBlast password' }}" {{ isset($settings['password']) && $settings['password'] ? '' : 'required' }}>
                        @if(isset($settings['password']) && $settings['password'])
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Leave blank to keep current password</small>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="sender_id">Sender ID / Phone Number <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="sender_id" name="sender_id" placeholder="084330484" value="{{ $settings['sender_id'] ?? '' }}" required>
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Phone number or sender name that appears in SMS</small>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="openTestModal()">Test</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Test SMS Modal -->
    <div id="testSmsModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Test SMS Configuration</h3>
                <button class="modal-close" onclick="closeTestModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                <p style="color: #666666; font-size: 12px; margin-bottom: 15px;">Enter a phone number to send a test SMS and verify your InfoBlast configuration.</p>
                <form id="testSmsForm">
                    <div class="form-group">
                        <label for="test_phone">Test Phone Number <span style="color: #dc3545;">*</span></label>
                        <input type="tel" id="test_phone" name="test_phone" placeholder="+60123456789" required>
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Include country code (e.g., +60 for Malaysia)</small>
                    </div>
                    <div id="testResult" style="display: none; padding: 12px; border-radius: 4px; margin-top: 15px; font-size: 12px;"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeTestModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendTestSms()">Send Test</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function openTestModal() {
    document.getElementById('testSmsModal').classList.add('show');
    document.getElementById('testResult').style.display = 'none';
}

function closeTestModal() {
    document.getElementById('testSmsModal').classList.remove('show');
    document.getElementById('test_phone').value = '';
    document.getElementById('testResult').style.display = 'none';
}

function sendTestSms() {
    const testPhone = document.getElementById('test_phone').value;
    const testResult = document.getElementById('testResult');
    
    if (!testPhone) {
        testResult.style.display = 'block';
        testResult.style.backgroundColor = '#f8d7da';
        testResult.style.color = '#721c24';
        testResult.style.border = '1px solid #f5c6cb';
        testResult.textContent = 'Please enter a valid phone number.';
        return;
    }
    
    // Show loading
    testResult.style.display = 'block';
    testResult.style.backgroundColor = '#d1ecf1';
    testResult.style.color = '#0c5460';
    testResult.style.border = '1px solid #bee5eb';
    testResult.textContent = 'Sending test SMS...';
    
    // Send test SMS via API
    fetch('{{ route('pages.integrations.sms.test') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            test_phone: testPhone
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
        testResult.textContent = 'Error: ' + error.message;
    });
}

// Close modal when clicking outside
document.getElementById('testSmsModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeTestModal();
    }
});
</script>
@endsection
