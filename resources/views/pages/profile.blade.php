@extends('layouts.app')

@section('title', 'Profile - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Profile</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.profile') }}" class="tab-button {{ request()->routeIs('pages.profile') ? 'active' : '' }}">Profile</a>
            <a href="{{ route('pages.settings') }}" class="tab-button {{ request()->routeIs('pages.settings') ? 'active' : '' }}">Settings</a>
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
                    <h3>My Profile</h3>
                    <p class="content-description">View and update your personal information.</p>
                </div>
            </div>

            <!-- Basic Information Section -->
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #333333;">Basic Information</h4>
                <p style="margin: 0 0 20px 0; font-size: 12px; color: #666666;">Update your personal details.</p>
                
                <form method="POST" action="{{ route('pages.profile.update') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="{{ $user->username }}" disabled style="background-color: #f5f5f5; cursor: not-allowed;">
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Username cannot be changed</small>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $user->full_name) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email <span style="color: #dc3545;">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" placeholder="e.g., +60123456789">
                    </div>

                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department" value="{{ old('department', $user->department) }}" placeholder="e.g., IT Department">
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Reset</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>

            <!-- Change Password Section -->
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #333333;">Change Password</h4>
                <p style="margin: 0 0 20px 0; font-size: 12px; color: #666666;">Update your password to keep your account secure.</p>
                
                <form method="POST" action="{{ route('pages.profile.update') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Required to change password</small>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password (min. 8 characters)">
                    </div>

                    <div class="form-group">
                        <label for="new_password_confirmation">Confirm New Password</label>
                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" placeholder="Re-enter new password">
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Reset</button>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
