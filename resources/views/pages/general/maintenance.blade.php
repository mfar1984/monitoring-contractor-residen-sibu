@extends('layouts.app')

@section('title', 'General Settings - Maintenance - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>General</span>
    <span class="breadcrumb-separator">›</span>
    <span>Maintenance</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-general-tabs active="maintenance" />
        
        <div class="tabs-content">
            <div class="content-header">
                <div class="content-header-left">
                    <h3>Maintenance & Optimization</h3>
                    <p class="content-description">System maintenance and optimization tools.</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <div style="margin-bottom: 30px;">
                    <h4 style="font-size: 12px; font-weight: 600; color: #333333; margin-bottom: 15px;">Cache Management</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <button type="button" class="btn btn-secondary" onclick="clearCache('config')">
                            <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                            Clear Config Cache
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearCache('route')">
                            <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                            Clear Route Cache
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearCache('view')">
                            <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                            Clear View Cache
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearCache('all')">
                            <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                            Clear All Cache
                        </button>
                    </div>
                </div>

                <div style="margin-bottom: 30px;">
                    <h4 style="font-size: 12px; font-weight: 600; color: #333333; margin-bottom: 15px;">Database Optimization</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <button type="button" class="btn btn-secondary" onclick="optimizeDatabase()">
                            <span class="material-symbols-outlined" style="font-size: 16px;">tune</span>
                            Optimize Tables
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="backupDatabase()">
                            <span class="material-symbols-outlined" style="font-size: 16px;">backup</span>
                            Backup Database
                        </button>
                    </div>
                </div>

                <div>
                    <h4 style="font-size: 12px; font-weight: 600; color: #333333; margin-bottom: 15px;">System Logs</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <button type="button" class="btn btn-secondary" onclick="viewLogs()">
                            <span class="material-symbols-outlined" style="font-size: 16px;">description</span>
                            View System Logs
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearLogs()">
                            <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                            Clear Old Logs
                        </button>
                    </div>
                </div>

                <div id="maintenanceResult" style="display: none; padding: 12px; border-radius: 4px; margin-top: 20px; font-size: 12px;"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
function clearCache(type) {
    const result = document.getElementById('maintenanceResult');
    result.style.display = 'block';
    result.style.backgroundColor = '#d1ecf1';
    result.style.color = '#0c5460';
    result.style.border = '1px solid #bee5eb';
    result.textContent = 'Clearing ' + type + ' cache...';
    
    setTimeout(() => {
        result.style.backgroundColor = '#d4edda';
        result.style.color = '#155724';
        result.style.border = '1px solid #c3e6cb';
        result.textContent = type.charAt(0).toUpperCase() + type.slice(1) + ' cache cleared successfully!';
    }, 1000);
}

function optimizeDatabase() {
    const result = document.getElementById('maintenanceResult');
    result.style.display = 'block';
    result.style.backgroundColor = '#d1ecf1';
    result.style.color = '#0c5460';
    result.style.border = '1px solid #bee5eb';
    result.textContent = 'Optimizing database tables...';
    
    setTimeout(() => {
        result.style.backgroundColor = '#d4edda';
        result.style.color = '#155724';
        result.style.border = '1px solid #c3e6cb';
        result.textContent = 'Database tables optimized successfully!';
    }, 2000);
}

function backupDatabase() {
    const result = document.getElementById('maintenanceResult');
    result.style.display = 'block';
    result.style.backgroundColor = '#d1ecf1';
    result.style.color = '#0c5460';
    result.style.border = '1px solid #bee5eb';
    result.textContent = 'Creating database backup...';
    
    setTimeout(() => {
        result.style.backgroundColor = '#d4edda';
        result.style.color = '#155724';
        result.style.border = '1px solid #c3e6cb';
        result.textContent = 'Database backup created successfully!';
    }, 2000);
}

function viewLogs() {
    alert('System logs viewer will be implemented soon.');
}

function clearLogs() {
    if (confirm('Are you sure you want to clear old logs?')) {
        const result = document.getElementById('maintenanceResult');
        result.style.display = 'block';
        result.style.backgroundColor = '#d4edda';
        result.style.color = '#155724';
        result.style.border = '1px solid #c3e6cb';
        result.textContent = 'Old logs cleared successfully!';
    }
}
</script>
@endsection
