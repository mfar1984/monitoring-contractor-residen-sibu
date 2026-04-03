@extends('layouts.app')

@section('title', 'Master Data - Contractor - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Master Data</span>
    <span class="breadcrumb-separator">›</span>
    <span>Contractor</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-master-data-tabs active="contractor" />
        
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
                title="Contractor Companies"
                description="Manage contractor company list."
                createButtonText="Create Company"
                createButtonRoute="{{ route('pages.master-data.contractor.create') }}"
                searchPlaceholder="Search companies..."
                :columns="['Company Name', 'Registration No.', 'Code', 'UPKJ Class', 'Manpower', 'Status', 'Actions']"
                :data="$contractors"
                :rowsPerPage="10"
            >
                @forelse($contractors as $contractor)
                <tr>
                    <td>{{ $contractor->company_name }}</td>
                    <td>{{ $contractor->registration_number ?? '-' }}</td>
                    <td>{{ $contractor->code }}</td>
                    <td>{{ $contractor->upkj_class ?? '-' }}</td>
                    <td>{{ $contractor->manpower_total }}</td>
                    <td>
                        <span class="status-badge {{ $contractor->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $contractor->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('pages.master-data.contractor.edit', $contractor->id) }}" class="action-btn action-edit" title="Edit">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <button class="action-btn action-delete" title="Delete" onclick="deleteContractor({{ $contractor->id }}, '{{ $contractor->company_name }}')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No companies found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-container" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">Confirm Delete</h3>
                <button class="modal-close" onclick="closeDeleteModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p id="deleteMessage" style="margin: 0; color: #666666;"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                    <button type="submit" class="btn" style="background-color: #dc3545; color: white;">
                        <span class="material-symbols-outlined">delete</span>
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function deleteContractor(id, name) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete "' + name + '"?';
    document.getElementById('deleteForm').action = '/pages/master-data/contractor/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

// Close modal on outside click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endpush
