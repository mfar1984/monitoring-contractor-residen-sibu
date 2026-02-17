@extends('layouts.guest')

@section('title', 'Login - Monitoring System')

@section('content')
@php
    $loginLogo = \App\Models\IntegrationSetting::getSetting('application', 'login_logo');
    $loginBg = \App\Models\IntegrationSetting::getSetting('application', 'login_background');
@endphp
<div class="login-container">
    <!-- Left Side - Image Section (70%) -->
    <div class="login-image-section" @if($loginBg) style="background-image: url('{{ asset('storage/' . $loginBg) }}'); background-size: cover; background-position: center;" @endif>
        <!-- Background image only, no content -->
    </div>

    <!-- Separator Line with Pulse Dot -->
    <div class="login-separator">
        <div class="separator-dot"></div>
    </div>

    <!-- Right Side - Login Form Section (30%) -->
    <div class="login-form-section">
        <div class="login-box">
            <div class="login-header">
                <div class="logo-circle">
                    @if($loginLogo)
                        <img src="{{ asset('storage/' . $loginLogo) }}" alt="Logo">
                    @else
                        <span class="material-symbols-outlined">monitoring</span>
                    @endif
                </div>
            </div>
            
            @if ($errors->any())
                <div class="error-message">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                
                <div class="form-group">
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">person</span>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="{{ old('username') }}" 
                            placeholder="Username"
                            required 
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-wrapper">
                        <span class="material-symbols-outlined input-icon">lock</span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Password"
                            required
                        >
                    </div>
                </div>

                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary">
                    Access
                </button>
            </form>

            <div class="login-footer">
                <p class="copyright">© 2026 Pejabat Residen Bahagian Sibu</p>
                <p class="footer-links">Privacy / Terms / Disclaimer</p>
            </div>
        </div>
    </div>
</div>

<script>
// Add loading state on form submit
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.btn-primary');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Logging in...';
});
</script>
@endsection
