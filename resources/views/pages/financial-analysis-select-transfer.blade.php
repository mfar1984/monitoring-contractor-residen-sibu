@extends('layouts.app')

@section('title', 'Select Transfer - Financial Analysis')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <span>Financial Analysis</span>
    <span class="breadcrumb-separator">›</span>
    <span>Select Transfer</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-content">
            @if(session('error'))
            <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
            @endif

            <x-data-table
                title="Select Contractor Analysis Transfer"
                description="Select a transfer to create financial analysis."
                createButtonText=""
                createButtonRoute="#"
                searchPlaceholder="Search transfer number or project name..."
                :columns="['Transfer Number', 'Project Name', 'Contractors', 'Created Date', 'Action']"
                :data="$transfers"
                :rowsPerPage="10"
            >
                @forelse($transfers as $transfer)
                <tr>
                    <td>{{ $transfer->transfer_number }}</td>
                    <td>{{ $transfer->projects->first()?->name ?? '-' }}</td>
                    <td>{{ $transfer->contractors->count() }} contractors</td>
                    <td>{{ $transfer->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-view" title="Create Analysis" onclick="window.location.href='{{ route('pages.financial-analysis.create', $transfer->id) }}'">
                                <span class="material-symbols-outlined">add_circle</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">
                        No available transfers found for your agency.
                    </td>
                </tr>
                @endforelse
            </x-data-table>

            <div style="margin-top: 20px;">
                <a href="{{ route('pages.financial-analysis') }}" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; text-decoration: none; display: inline-block;">
                    Back to List
                </a>
            </div>
        </div>
    </div>
@endsection
