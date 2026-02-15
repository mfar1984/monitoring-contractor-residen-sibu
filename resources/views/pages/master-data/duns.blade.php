@extends('layouts.app')

@section('title', 'Master Data - DUN - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Master Data</span>
    <span class="breadcrumb-separator">›</span>
    <span>DUN</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-master-data-tabs active="duns" />
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            <x-data-table
                title="DUN (Dewan Undangan Negeri)"
                description="Manage DUN constituencies."
                createButtonText="Create DUN"
                createButtonRoute="#"
                searchPlaceholder="Search DUN..."
                :columns="['Name', 'Budget (RM)', 'Code', 'Description', 'Status', 'Actions']"
                :data="$duns"
                :rowsPerPage="5"
            >
                @forelse($duns as $dun)
                <tr>
                    <td>{{ $dun->name }}</td>
                    <td>{{ number_format($dun->budget, 2) }}</td>
                    <td>{{ $dun->code }}</td>
                    <td>{{ $dun->description ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $dun->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $dun->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-edit" title="Edit" onclick="editDun({{ $dun->id }}, '{{ $dun->name }}', {{ $dun->budget }}, '{{ $dun->code }}', '{{ $dun->description }}', '{{ $dun->status }}')">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete" onclick="deleteDun({{ $dun->id }}, '{{ $dun->name }}')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No DUN found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" id="dunModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create DUN</h3>
                <button class="modal-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="dunForm" method="POST" action="{{ route('pages.master-data.duns.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="id" id="dunId">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="budget">Budget (RM) <span style="color: #dc3545;">*</span></label>
                            <input type="text" id="budget" name="budget" required pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        
                        <div class="form-group">
                            <label for="code">Code <span style="color: #dc3545;">*</span></label>
                            <input type="text" id="code" name="code" required>
                        </div>
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
    if (createBtn && createBtn.textContent.includes('Create DUN')) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            openCreateModal();
        };
    }
});

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create DUN';
    document.getElementById('dunForm').action = '{{ route("pages.master-data.duns.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('dunId').value = '';
    document.getElementById('name').value = '';
    document.getElementById('budget').value = '';
    document.getElementById('code').value = '';
    document.getElementById('description').value = '';
    document.getElementById('status').value = 'Active';
    document.getElementById('dunModal').classList.add('show');
}

function editDun(id, name, budget, code, description, status) {
    document.getElementById('modalTitle').textContent = 'Edit DUN';
    document.getElementById('dunForm').action = '/pages/master-data/duns/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('dunId').value = id;
    document.getElementById('name').value = name;
    document.getElementById('budget').value = budget;
    document.getElementById('code').value = code;
    document.getElementById('description').value = description || '';
    document.getElementById('status').value = status;
    document.getElementById('dunModal').classList.add('show');
}

function closeModal() {
    document.getElementById('dunModal').classList.remove('show');
}

function deleteDun(id, name) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete "' + name + '"?';
    document.getElementById('deleteForm').action = '/pages/master-data/duns/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('dunModal').addEventListener('click', function(e) {
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
