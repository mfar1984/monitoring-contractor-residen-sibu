@extends('layouts.app')

@section('title', 'Contractor Selections - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Drawing Lots</span>
    <span class="breadcrumb-separator">›</span>
    <span>Contractor Selections</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-content">
            @if(session('success'))
            <div id="successMessage" style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div id="errorMessage" style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
            @endif

            <x-data-table
                title="Contractor Selections"
                description="Manage contractor selection process for projects."
                createButtonText="Create Selection"
                createButtonRoute="{{ route('pages.contractor-selections.create') }}"
                searchPlaceholder="Search..."
                :columns="['Selection Number', 'Division', 'District', 'Contractors', 'Generated Date', 'Actions']"
                :data="$selections"
                :rowsPerPage="10"
            >
                @forelse($selections as $selection)
                <tr>
                    <td>
                        <a href="#" style="color: #007bff; text-decoration: none;">
                            {{ $selection->selection_number }}
                        </a>
                    </td>
                    <td>{{ $selection->division?->name ?? '-' }}</td>
                    <td>{{ $selection->district?->name ?? '-' }}</td>
                    <td>{{ $selection->contractors->count() }} contractors</td>
                    <td>{{ $selection->generated_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-view" title="View">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">
                        <div style="color: #666;">
                            <span class="material-symbols-outlined" style="font-size: 48px; opacity: 0.3;">inbox</span>
                            <p style="margin-top: 10px;">No contractor selections found</p>
                            <p style="font-size: 11px; color: #999;">Click "Create Selection" to generate a new contractor selection</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <script>
        // Auto-scroll to success/error message on page load
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            
            if (successMessage || errorMessage) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    </script>
@endsection
