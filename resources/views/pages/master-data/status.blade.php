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
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

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
                            <button class="action-btn action-edit" title="Edit" onclick="editStatus({{ $status->id }}, '{{ $status->name }}', '{{ $status->code }}', '{{ $status->color }}', '{{ $status->description }}', '{{ $status->status }}')">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete" onclick="deleteStatus({{ $status->id }}, '{{ $status->name }}')">
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

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" id="statusModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create Status</h3>
                <button class="modal-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="statusForm" method="POST" action="{{ route('pages.master-data.status.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="id" id="statusId">
                
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
                        <label for="color">Color <span style="color: #dc3545;">*</span></label>
                        <input type="color" id="color" name="color" value="#007bff" required>
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
    const createBtn = document.getElementById('create-button');
    if (createBtn) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            openCreateModal();
        };
    }
});

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Status';
    document.getElementById('statusForm').action = '{{ route("pages.master-data.status.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('statusId').value = '';
    document.getElementById('name').value = '';
    document.getElementById('code').value = '';
    document.getElementById('color').value = '#007bff';
    document.getElementById('description').value = '';
    document.getElementById('status').value = 'Active';
    document.getElementById('statusModal').classList.add('show');
}

function editStatus(id, name, code, color, description, status) {
    document.getElementById('modalTitle').textContent = 'Edit Status';
    document.getElementById('statusForm').action = '/pages/master-data/status/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('statusId').value = id;
    document.getElementById('name').value = name;
    document.getElementById('code').value = code;
    document.getElementById('color').value = color;
    document.getElementById('description').value = description || '';
    document.getElementById('status').value = status;
    document.getElementById('statusModal').classList.add('show');
}

function closeModal() {
    document.getElementById('statusModal').classList.remove('show');
}

function deleteStatus(id, name) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete "' + name + '"?';
    document.getElementById('deleteForm').action = '/pages/master-data/status/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('statusModal').addEventListener('click', function(e) {
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
