@extends('layouts.app')

@section('title', 'NOC (Notice of Change) - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <span>NOC</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-project-tabs active="noc" />
        
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
                title="NOC (Notice of Change)"
                description="Manage Notice of Change documents for pre-projects."
                createButtonText="Create NOC"
                createButtonRoute="{{ route('pages.project.noc.create') }}"
                searchPlaceholder="Search NOC..."
                :columns="['NOC Number', 'Parliament/DUN', 'Date', 'Projects', 'Status', 'Actions']"
                :data="$nocs"
                :rowsPerPage="10"
            >
                @forelse($nocs as $noc)
                <tr>
                    <td>{{ $noc->noc_number }}</td>
                    <td>{{ $noc->parliament?->name ?? $noc->dun?->name ?? '-' }}</td>
                    <td>{{ $noc->noc_date->format('d/m/Y') }}</td>
                    <td>{{ $noc->projects->count() }} projects</td>
                    <td>
                        @if($noc->status === 'Waiting for Approval 1')
                            <span class="status-badge" style="background-color: #fff3cd; color: #856404;">Waiting for Approval 1</span>
                        @elseif($noc->status === 'Waiting for Approval 2')
                            <span class="status-badge" style="background-color: #cce5ff; color: #004085;">Waiting for Approval 2</span>
                        @elseif($noc->status === 'Approved')
                            <span class="status-badge status-active">Approved</span>
                        @elseif($noc->status === 'Rejected')
                            <span class="status-badge" style="background-color: #f8d7da; color: #721c24;">Rejected</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-view" title="View" onclick="window.location.href='{{ route('pages.project.noc.show', $noc->id) }}'">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                            <button class="action-btn action-view" title="Print" onclick="window.open('{{ route('pages.project.noc.print', $noc->id) }}', '_blank')">
                                <span class="material-symbols-outlined">print</span>
                            </button>
                            @if($noc->status !== 'Approved')
                            <button class="action-btn action-delete" title="Delete" onclick="confirmDelete({{ $noc->id }})">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No NOC records found</td>
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
                <p>Are you sure you want to delete this NOC?</p>
                <p style="color: #666; font-size: 11px; margin-top: 10px;">
                    This will rollback all imported projects to Active status and delete the NOC permanently.
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
        function confirmDelete(nocId) {
            const modal = document.getElementById('deleteModal');
            const form = document.getElementById('deleteForm');
            form.action = `/pages/project/noc/${nocId}`;
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
