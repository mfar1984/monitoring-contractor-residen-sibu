@extends('layouts.app')

@section('title', 'Financial Analysis - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <span>Financial Analysis</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.financial-analysis') }}" class="tab-button active">Financial Analysis</a>
        </div>
        
        <div class="tabs-content">
            @if(session('success'))
            <div id="successMessage" style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            @if(session('info'))
            <div id="infoMessage" style="padding: 10px; background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('info') }}
            </div>
            @endif

            @if(session('error'))
            <div id="errorMessage" style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
            @endif

            <x-data-table
                title="Financial Analysis"
                description="Comprehensive financial evaluation of contractors for project awards."
                createButtonText="{{ auth()->user()->agency_category_id ? 'Create Financial Analysis' : '' }}"
                createButtonRoute="{{ auth()->user()->agency_category_id ? route('pages.financial-analysis.select-transfer') : '#' }}"
                searchPlaceholder="Search transfer number or project name..."
                :columns="['Transfer Number', 'Project Name', 'Agency', 'Contractors', 'Status', 'Created Date', 'Actions']"
                :data="$analyses"
                :rowsPerPage="10"
            >
                @forelse($analyses as $analysis)
                <tr>
                    <td>
                        <a href="{{ route('pages.financial-analysis.show', $analysis->id) }}" style="color: #007bff; text-decoration: none;">
                            {{ $analysis->transfer->transfer_number }}
                        </a>
                    </td>
                    <td>{{ $analysis->project_name ?? '-' }}</td>
                    <td>{{ $analysis->agency_name ?? '-' }}</td>
                    <td>{{ $analysis->getContractorCount() }} contractors</td>
                    <td>
                        @if($analysis->status === 'Draft')
                            <span class="status-badge" style="background-color: #f5f5f5; color: #666;">Draft</span>
                        @elseif($analysis->status === 'Submitted')
                            <span class="status-badge" style="background-color: #fff3cd; color: #856404;">Submitted</span>
                        @elseif($analysis->status === 'Approved')
                            <span class="status-badge status-active">Approved</span>
                        @elseif($analysis->status === 'Rejected')
                            <span class="status-badge" style="background-color: #f8d7da; color: #721c24;">Rejected</span>
                        @endif
                    </td>
                    <td>{{ $analysis->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-view" title="View" onclick="window.location.href='{{ route('pages.financial-analysis.show', $analysis->id) }}'">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                            @if($analysis->status === 'Draft' && auth()->user()->agency_category_id)
                            <button class="action-btn action-edit" title="Edit" onclick="window.location.href='{{ route('pages.financial-analysis.edit', $analysis->id) }}'">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete" onclick="confirmDelete({{ $analysis->id }})">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No financial analysis records found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this financial analysis?</p>
                <p style="color: #666; font-size: 11px; margin-top: 10px;">
                    This will permanently delete the analysis and all related evaluation data.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(analysisId) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            form.action = `/pages/financial-analysis/${analysisId}`;
            modal.style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }

        // Auto-scroll to success/error message on page load
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            const infoMessage = document.getElementById('infoMessage');
            
            if (successMessage || errorMessage || infoMessage) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    </script>
@endsection
