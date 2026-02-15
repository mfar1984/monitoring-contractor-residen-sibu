@extends('layouts.app')

@section('title', 'General Settings - Application Settings - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>General</span>
    <span class="breadcrumb-separator">›</span>
    <span>Application Settings</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-general-tabs active="application" />
        
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
                    <h3>Application Settings</h3>
                    <p class="content-description">Configure application-wide settings.</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <form method="POST" action="{{ route('pages.general.application.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="app_name">Application Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="app_name" name="app_name" value="{{ $settings['app_name'] ?? config('app.name', 'Monitoring System') }}" required>
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">This will appear in browser title and page headers</small>
                    </div>

                    <div class="form-group">
                        <label for="sidebar_name">Sidebar Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="sidebar_name" name="sidebar_name" value="{{ $settings['sidebar_name'] ?? $settings['app_name'] ?? config('app.name', 'Monitoring System') }}" required>
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">This will appear in the sidebar logo area</small>
                    </div>

                    <div class="form-group">
                        <label for="app_url">Application URL <span style="color: #dc3545;">*</span></label>
                        <input type="url" id="app_url" name="app_url" value="{{ $settings['app_url'] ?? config('app.url', 'http://localhost') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="sidebar_display">Sidebar Display <span style="color: #dc3545;">*</span></label>
                        <select id="sidebar_display" name="sidebar_display" required>
                            <option value="name_only" {{ ($settings['sidebar_display'] ?? 'name_only') == 'name_only' ? 'selected' : '' }}>Name Only</option>
                            <option value="logo_only" {{ ($settings['sidebar_display'] ?? '') == 'logo_only' ? 'selected' : '' }}>Logo Only</option>
                            <option value="logo_and_name" {{ ($settings['sidebar_display'] ?? '') == 'logo_and_name' ? 'selected' : '' }}>Logo + Name</option>
                        </select>
                        <small style="color: #666666; display: block; margin-top: 5px;">Choose how to display the sidebar branding.</small>
                    </div>

                    <div class="form-group" id="logo-upload-group">
                        <label for="sidebar_logo">Sidebar Logo</label>
                        <input type="file" id="sidebar_logo" name="sidebar_logo" accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                        <small style="color: #666666; display: block; margin-top: 5px;">Upload logo (PNG, JPG, SVG). Max size: 2MB. Recommended: 40x40px.</small>
                        @if(isset($settings['sidebar_logo']) && $settings['sidebar_logo'])
                        <div style="margin-top: 10px;">
                            <img src="{{ asset('storage/' . $settings['sidebar_logo']) }}" alt="Current Logo" style="max-width: 100px; max-height: 100px; border: 1px solid #e0e0e0; border-radius: 4px; padding: 5px;">
                            <p style="font-size: 11px; color: #666666; margin-top: 5px;">Current logo</p>
                        </div>
                        @endif
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="items_per_page">Items Per Page</label>
                            <input type="number" id="items_per_page" name="items_per_page" value="{{ $settings['items_per_page'] ?? 10 }}" min="5" max="100">
                        </div>

                        <div class="form-group">
                            <label for="session_lifetime">Session Lifetime (minutes)</label>
                            <input type="number" id="session_lifetime" name="session_lifetime" value="{{ $settings['session_lifetime'] ?? 120 }}" min="30" max="1440">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="maintenance_mode" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }} style="margin-right: 8px;">
                            Enable Maintenance Mode
                        </label>
                        <small style="color: #666666; display: block; margin-top: 5px;">When enabled, only administrators can access the system.</small>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Reset</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarDisplay = document.getElementById('sidebar_display');
    const logoUploadGroup = document.getElementById('logo-upload-group');
    
    function toggleLogoUpload() {
        const value = sidebarDisplay.value;
        if (value === 'logo_only' || value === 'logo_and_name') {
            logoUploadGroup.style.display = 'block';
        } else {
            logoUploadGroup.style.display = 'none';
        }
    }
    
    sidebarDisplay.addEventListener('change', toggleLogoUpload);
    toggleLogoUpload(); // Initial check
});
</script>
@endsection
