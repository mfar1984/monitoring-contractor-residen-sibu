@extends('layouts.app')

@section('title', 'Contractor Analysis Transfer - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <span>Contractor Analysis</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.contractor-analysis') }}" class="tab-button active">Contractor Analysis</a>
        </div>
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
            @endif

            <x-data-table
                title="Contractor Analysis Transfer"
                description="Manage project transfers for contractor analysis and selection."
                createButtonText="Create Transfer"
                createButtonRoute="{{ route('pages.contractor-analysis.create') }}"
                searchPlaceholder="Search transfer number..."
                :columns="['Transfer Number', 'Agency', 'Projects', 'Status', 'Created Date', 'Actions']"
                :data="$transfers"
                :rowsPerPage="10"
            >
                @forelse($transfers as $transfer)
                <tr>
                    <td>
                        <a href="{{ route('pages.contractor-analysis.show', $transfer->id) }}" style="color: #007bff; text-decoration: none;">
                            {{ $transfer->transfer_number }}
                        </a>
                    </td>
                    <td>{{ $transfer->agency?->name ?? '-' }}</td>
                    <td>{{ $transfer->projects->count() }} projects</td>
                    <td>
                        @if($transfer->status === 'Draft')
                            <span class="status-badge" style="background-color: #f5f5f5; color: #666;">Draft</span>
                        @elseif($transfer->status === 'Submitted')
                            <span class="status-badge" style="background-color: #cce5ff; color: #004085;">Submitted</span>
                        @elseif($transfer->status === 'In Analysis')
                            <span class="status-badge" style="background-color: #fff3cd; color: #856404;">In Analysis</span>
                        @elseif($transfer->status === 'Completed')
                            <span class="status-badge status-active">Completed</span>
                        @endif
                    </td>
                    <td>{{ $transfer->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-view" title="View" onclick="window.location.href='{{ route('pages.contractor-analysis.show', $transfer->id) }}'">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                            <button class="action-btn action-view" title="Download Attachment" onclick="window.location.href='{{ route('pages.contractor-analysis.download', $transfer->id) }}'">
                                <span class="material-symbols-outlined">download</span>
                            </button>
                            @if($transfer->status === 'Draft')
                            <button class="action-btn action-delete" title="Delete" onclick="confirmDelete({{ $transfer->id }})">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No transfer records found</td>
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
                <p>Are you sure you want to delete this transfer?</p>
                <p style="color: #666; font-size: 11px; margin-top: 10px;">
                    This will rollback all transferred projects to Active status and delete the transfer permanently.
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
        function confirmDelete(transferId) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            form.action = `/pages/contractor-analysis/${transferId}`;
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
    </script>
@endsection
