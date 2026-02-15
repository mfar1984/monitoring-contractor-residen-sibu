@extends('layouts.app')

@section('title', 'Master Data - Division - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Master Data</span>
    <span class="breadcrumb-separator">›</span>
    <span>Division</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-master-data-tabs active="division" />
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            <x-data-table
                title="Divisions"
                description="Manage divisions for the system."
                createButtonText="Create Division"
                createButtonRoute="#"
                searchPlaceholder="Search division..."
                :columns="['Name', 'Code', 'Description', 'Status', 'Actions']"
                :data="$divisions"
                :rowsPerPage="5"
            >
                @forelse($divisions as $division)
                <tr>
                    <td>{{ $division->name }}</td>
                    <td>{{ $division->code }}</td>
                    <td>{{ $division->description ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $division->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $division->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-edit" title="Edit" onclick="editDivision({{ $division->id }}, '{{ $division->name }}', '{{ $division->code }}', '{{ $division->description }}', '{{ $division->status }}')">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete" onclick="deleteDivision({{ $division->id }}, '{{ $division->name }}')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">No divisions found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" id="divisionModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create Division</h3>
                <button class="modal-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="divisionForm" method="POST" action="{{ route('pages.master-data.division.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="id" id="divisionId">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="code">Code <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="code" name="code" required>
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
document.addEventListener('DOMContentLoaded', function() {
    const createBtn = document.querySelector('.btn-primary');
    if (createBtn && createBtn.textContent.includes('Create Division')) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            openCreateModal();
        };
    }
});

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Division';
    document.getElementById('divisionForm').action = '{{ route("pages.master-data.division.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('divisionId').value = '';
    document.getElementById('name').value = '';
    document.getElementById('code').value = '';
    document.getElementById('description').value = '';
    document.getElementById('status').value = 'Active';
    document.getElementById('divisionModal').classList.add('show');
}

function editDivision(id, name, code, description, status) {
    document.getElementById('modalTitle').textContent = 'Edit Division';
    document.getElementById('divisionForm').action = '/pages/master-data/division/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('divisionId').value = id;
    document.getElementById('name').value = name;
    document.getElementById('code').value = code;
    document.getElementById('description').value = description || '';
    document.getElementById('status').value = status;
    document.getElementById('divisionModal').classList.add('show');
}

function closeModal() {
    document.getElementById('divisionModal').classList.remove('show');
}

function deleteDivision(id, name) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete "' + name + '"?';
    document.getElementById('deleteForm').action = '/pages/master-data/division/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('divisionModal').addEventListener('click', function(e) {
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
