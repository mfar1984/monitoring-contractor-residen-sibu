@extends('layouts.app')

@section('title', 'Master Data - Status - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Master Data</span>
    <span class="breadcrumb-separator">›</span>
    <span>Status</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-master-data-tabs active="status" />
        
        <div class="tabs-content">
            <x-data-table
                title="Status Master"
                description="Manage status options for the system."
                createButtonText="Create Status"
                createButtonRoute="#"
                searchPlaceholder="Search status..."
                :columns="['Name', 'Code', 'Color', 'Description', 'Status', 'Actions']"
                :data="$statuses"
                :rowsPerPage="5"
            >
                @forelse($statuses as $status)
                <tr>
                    <td>{{ $status->name }}</td>
                    <td>{{ $status->code }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 20px; height: 20px; background-color: {{ $status->color }}; border-radius: 3px; border: 1px solid #e0e0e0;"></div>
                            <span>{{ $status->color }}</span>
                        </div>
                    </td>
                    <td>{{ $status->description ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $status->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $status->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-edit" title="Edit">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No status found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>
@endsection
