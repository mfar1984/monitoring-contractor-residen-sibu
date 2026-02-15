@extends('layouts.app')

@section('title', 'NOC (Notice of Change) - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <span>Pre-Project</span>
    <span class="breadcrumb-separator">›</span>
    <span>NOC</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.pre-project') }}" class="tab-button">Pre-Project</a>
            <a href="{{ route('pages.pre-project.noc') }}" class="tab-button active">NOC (Notice of Change)</a>
        </div>
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            <x-data-table
                title="NOC (Notice of Change)"
                description="Manage Notice of Change documents and records."
                createButtonText="Create NOC"
                createButtonRoute="#"
                searchPlaceholder="Search NOC..."
                :columns="['NOC Number', 'Project Name', 'Change Type', 'Date', 'Status', 'Actions']"
                :data="[]"
                :rowsPerPage="10"
            >
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No NOC records found</td>
                </tr>
            </x-data-table>
        </div>
    </div>
@endsection
