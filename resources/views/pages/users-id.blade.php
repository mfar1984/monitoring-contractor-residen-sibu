@extends('layouts.app')

@section('title', 'Users Id - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Users Id</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <button class="tab-button active" onclick="switchTab(event, 'residen')">Residen</button>
            <button class="tab-button" onclick="switchTab(event, 'agency')">Agency</button>
            <button class="tab-button" onclick="switchTab(event, 'parliament')">Member of Parliament</button>
            <button class="tab-button" onclick="switchTab(event, 'contractor')">Contractor</button>
        </div>
        
        <div class="tabs-content">
            <div id="residen" class="tab-pane active">
                <h3>Residen Users</h3>
                <p>Content for Residen users will be displayed here.</p>
            </div>
            
            <div id="agency" class="tab-pane">
                <h3>Agency Users</h3>
                <p>Content for Agency users will be displayed here.</p>
            </div>
            
            <div id="parliament" class="tab-pane">
                <h3>Member of Parliament Users</h3>
                <p>Content for Member of Parliament users will be displayed here.</p>
            </div>
            
            <div id="contractor" class="tab-pane">
                <h3>Contractor Users</h3>
                <p>Content for Contractor users will be displayed here.</p>
            </div>
        </div>
    </div>
    
    <script>
    function switchTab(event, tabId) {
        event.preventDefault();
        
        // Remove active class from all tabs and panes
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('active');
        });
        
        // Add active class to clicked tab and corresponding pane
        event.currentTarget.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    }
    </script>
@endsection
