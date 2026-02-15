@extends('layouts.app')

@section('title', 'General Settings - System Information - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>General</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Information</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-general-tabs active="system" />
        
        <div class="tabs-content">
            <div class="content-header">
                <div class="content-header-left">
                    <h3>System Information</h3>
                    <p class="content-description">View system and server information.</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 15px; font-size: 12px;">
                    <div style="color: #666666;">Application Name</div>
                    <div style="color: #333333; font-weight: 500;">{{ config('app.name', 'Monitoring System') }}</div>
                    
                    <div style="color: #666666;">Environment</div>
                    <div style="color: #333333; font-weight: 500;">{{ config('app.env', 'production') }}</div>
                    
                    <div style="color: #666666;">Debug Mode</div>
                    <div style="color: #333333; font-weight: 500;">{{ config('app.debug') ? 'Enabled' : 'Disabled' }}</div>
                    
                    <div style="color: #666666;">PHP Version</div>
                    <div style="color: #333333; font-weight: 500;">{{ PHP_VERSION }}</div>
                    
                    <div style="color: #666666;">Laravel Version</div>
                    <div style="color: #333333; font-weight: 500;">{{ app()->version() }}</div>
                    
                    <div style="color: #666666;">Timezone</div>
                    <div style="color: #333333; font-weight: 500;">{{ config('app.timezone', 'UTC') }}</div>
                    
                    <div style="color: #666666;">Database Connection</div>
                    <div style="color: #333333; font-weight: 500;">{{ config('database.default', 'mysql') }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
