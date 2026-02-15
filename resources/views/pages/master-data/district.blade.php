@extends('layouts.app')

@section('title', 'Master Data - District - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Master Data</span>
    <span class="breadcrumb-separator">›</span>
    <span>District</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-master-data-tabs active="district" />
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            <x-data-table
                title="Districts"
                description="Manage districts for the system."
                createButtonText="Create District"
                createButtonRoute="#"
                searchPlaceholder="Search district..."
                :columns="['Division', 'Name', 'Code', 'Description', 'Status', 'Actions']"
                :data="$districts"
                :rowsPerPage="5"
            >
                @forelse($districts as $district)
                <tr>
                    <td>{{ $district->division->name ?? '-' }}</td>
                    <td>{{ $district->name }}</td>
                    <td>{{ $district->code }}</td>
                    <td>{{ $district->description ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $district->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $district->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-edit" title="Edit" onclick="editDistrict({{ $district->id }}, {{ $district->division_id }}, '{{ $district->name }}', '{{ $district->code }}', '{{ $district->description }}', '{{ $district->status }}')">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete" onclick="deleteDistrict({{ $district->id }}, '{{ $district->name }}')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No districts found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" id="districtModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create District</h3>
                <button class="modal-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="districtForm" method="POST" action="{{ route('pages.master-data.district.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="id" id="districtId">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="division_id">Division <span style="color: #dc3545;">*</span></label>
                        <select id="division_id" name="division_id" required>
                            <option value="">Select Division</option>
                            @foreach($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
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
    if (createBtn && createBtn.textContent.includes('Create District')) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            openCreateModal();
        };
    }
});

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create District';
    document.getElementById('districtForm').action = '{{ route("pages.master-data.district.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('districtId').value = '';
    document.getElementById('division_id').value = '';
    document.getElementById('name').value = '';
    document.getElementById('code').value = '';
    document.getElementById('description').value = '';
    document.getElementById('status').value = 'Active';
    document.getElementById('districtModal').classList.add('show');
}

function editDistrict(id, divisionId, name, code, description, status) {
    document.getElementById('modalTitle').textContent = 'Edit District';
    document.getElementById('districtForm').action = '/pages/master-data/district/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('districtId').value = id;
    document.getElementById('division_id').value = divisionId;
    document.getElementById('name').value = name;
    document.getElementById('code').value = code;
    document.getElementById('description').value = description || '';
    document.getElementById('status').value = status;
    document.getElementById('districtModal').classList.add('show');
}

function closeModal() {
    document.getElementById('districtModal').classList.remove('show');
}

function deleteDistrict(id, name) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete "' + name + '"?';
    document.getElementById('deleteForm').action = '/pages/master-data/district/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('districtModal').addEventListener('click', function(e) {
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
