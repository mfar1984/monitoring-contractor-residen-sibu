@extends('layouts.guest')

@section('title', 'Login - Monitoring System')

@section('content')
<div class="login-container">
    <div class="login-box">
        <h2>Monitoring System</h2>
        
        @if ($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="{{ old('username') }}" 
                    required 
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</div>
@endsection
