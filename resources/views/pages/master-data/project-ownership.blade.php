@extends('layouts.app')

@section('title', 'Master Data - Project Ownership - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Master Data</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project Ownership</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-master-data-tabs active="project-ownership" />
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            <x-data-table
                title="Project Ownership"
                description="Manage project ownership categories."
                createButtonText="Create Ownership"
                createButtonRoute="#"
                searchPlaceholder="Search ownership..."
                :columns="['Name', 'Code', 'Description', 'Status', 'Actions']"
                :data="$ownerships"
                :rowsPerPage="5"
            >
                @forelse($ownerships as $ownership)
                <tr>
                    <td>{{ $ownership->name }}</td>
                    <td>{{ $ownership->code }}</td>
                    <td>{{ $ownership->description ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $ownership->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $ownership->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-edit" title="Edit" onclick="editOwnership({{ $ownership->id }}, '{{ $ownership->name }}', '{{ $ownership->code }}', '{{ $ownership->description }}', '{{ $ownership->status }}')">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete" onclick="deleteOwnership({{ $ownership->id }}, '{{ $ownership->name }}')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">No ownership found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" id="ownershipModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create Ownership</h3>
                <button class="modal-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="ownershipForm" method="POST" action="{{ route('pages.master-data.project-ownership.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="id" id="ownershipId">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="name" name="name" required>
                        <span class="form-error" id="nameError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="code">Code <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="code" name="code" required>
                        <span class="form-error" id="codeError"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status <span style="color: #dc3545;">*</span></label>
                        <select id="status" name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="material-symbols-outlined">save</span>
                        Save
                    </button>
                </div>
            </form>
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
// Override create button click
document.addEventListener('DOMContentLoaded', function() {
    const createBtn = document.querySelector('.btn-primary');
    if (createBtn && createBtn.textContent.includes('Create Ownership')) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            openCreateModal();
        };
    }
});

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Ownership';
    document.getElementById('ownershipForm').action = '{{ route("pages.master-data.project-ownership.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('ownershipId').value = '';
    document.getElementById('name').value = '';
    document.getElementById('code').value = '';
    document.getElementById('description').value = '';
    document.getElementById('status').value = 'Active';
    document.getElementById('ownershipModal').classList.add('show');
}

function editOwnership(id, name, code, description, status) {
    document.getElementById('modalTitle').textContent = 'Edit Ownership';
    document.getElementById('ownershipForm').action = '/pages/master-data/project-ownership/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('ownershipId').value = id;
    document.getElementById('name').value = name;
    document.getElementById('code').value = code;
    document.getElementById('description').value = description || '';
    document.getElementById('status').value = status;
    document.getElementById('ownershipModal').classList.add('show');
}

function closeModal() {
    document.getElementById('ownershipModal').classList.remove('show');
}

function deleteOwnership(id, name) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete "' + name + '"?';
    document.getElementById('deleteForm').action = '/pages/master-data/project-ownership/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

// Close modal when clicking outside
document.getElementById('ownershipModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endpush
