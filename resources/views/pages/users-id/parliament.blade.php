@extends('layouts.app')

@section('title', 'Users ID - Parliament/DUN - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Users ID</span>
    <span class="breadcrumb-separator">›</span>
    <span>Parliament/DUN</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.users-id.residen') }}" class="tab-button">Residen</a>
            <a href="{{ route('pages.users-id.agency') }}" class="tab-button">Agency</a>
            <a href="{{ route('pages.users-id.parliament') }}" class="tab-button active">Parliament/DUN</a>
            <a href="{{ route('pages.users-id.contractor') }}" class="tab-button">Contractor</a>
        </div>
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            <x-data-table
                title="Parliament/DUN Users"
                description="Manage users for parliament and DUN constituencies."
                createButtonText="Create User"
                createButtonRoute="#"
                searchPlaceholder="Search users..."
                :columns="['Type', 'Category', 'Full Name', 'Username', 'Department', 'Contact', 'Status', 'Actions']"
                :data="$users"
                :rowsPerPage="5"
            >
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->parliament_id ? 'Parliament' : 'DUN' }}</td>
                    <td>{{ $user->parliament ? $user->parliament->name : ($user->dun ? $user->dun->name : '-') }}</td>
                    <td>{{ $user->full_name }}</td>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->department ?? '-' }}</td>
                    <td>{{ $user->contact_number ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $user->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $user->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-edit" title="Edit" onclick="editUser({{ $user->id }}, '{{ $user->parliament_id ? 'parliament' : 'dun' }}', {{ $user->parliament_id ?? 'null' }}, {{ $user->dun_id ?? 'null' }}, '{{ $user->full_name }}', '{{ $user->department }}', '{{ $user->contact_number }}', '{{ $user->email }}', '{{ $user->username }}')">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete" onclick="deleteUser({{ $user->id }}, '{{ $user->full_name }}')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">No users found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" id="userModal">
        <div class="modal-container" style="max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create User</h3>
                <button class="modal-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="userForm" method="POST" action="{{ route('pages.users-id.parliament.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="id" id="userId">
                
                <div class="modal-body" style="max-height: calc(90vh - 140px); overflow-y: auto;">
                    <div class="form-group">
                        <label>Type <span style="color: #dc3545;">*</span></label>
                        <div style="display: flex; gap: 20px; margin-top: 8px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="type" value="parliament" id="typeParliament" required onchange="toggleCategoryDropdown()">
                                <span>Parliament</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                <input type="radio" name="type" value="dun" id="typeDun" required onchange="toggleCategoryDropdown()">
                                <span>DUN</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="parliamentGroup" style="display: none;">
                        <label for="parliament_id">Parliament <span style="color: #dc3545;">*</span></label>
                        <select id="parliament_id" name="parliament_id">
                            <option value="">Select Parliament</option>
                            @foreach($parliaments as $parliament)
                            <option value="{{ $parliament->id }}">{{ $parliament->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="dunGroup" style="display: none;">
                        <label for="dun_id">DUN <span style="color: #dc3545;">*</span></label>
                        <select id="dun_id" name="dun_id">
                            <option value="">Select DUN</option>
                            @foreach($duns as $dun)
                            <option value="{{ $dun->id }}">{{ $dun->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="department">Department</label>
                            <input type="text" id="department" name="department">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email">
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="password">Password <span style="color: #dc3545;" id="passwordRequired">*</span></label>
                            <input type="password" id="password" name="password">
                            <small style="color: #666666; font-size: 11px;">Leave blank to keep current password (when editing)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password <span style="color: #dc3545;" id="passwordConfirmRequired">*</span></label>
                            <input type="password" id="password_confirmation" name="password_confirmation">
                        </div>
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
    if (createBtn && createBtn.textContent.includes('Create User')) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            openCreateModal();
        };
    }
});

function toggleCategoryDropdown() {
    const parliamentRadio = document.getElementById('typeParliament');
    const dunRadio = document.getElementById('typeDun');
    const parliamentGroup = document.getElementById('parliamentGroup');
    const dunGroup = document.getElementById('dunGroup');
    const parliamentSelect = document.getElementById('parliament_id');
    const dunSelect = document.getElementById('dun_id');
    
    if (parliamentRadio.checked) {
        parliamentGroup.style.display = 'block';
        dunGroup.style.display = 'none';
        parliamentSelect.required = true;
        dunSelect.required = false;
        dunSelect.value = '';
    } else if (dunRadio.checked) {
        parliamentGroup.style.display = 'none';
        dunGroup.style.display = 'block';
        parliamentSelect.required = false;
        dunSelect.required = true;
        parliamentSelect.value = '';
    }
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create User';
    document.getElementById('userForm').action = '{{ route("pages.users-id.parliament.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('userId').value = '';
    document.getElementById('typeParliament').checked = false;
    document.getElementById('typeDun').checked = false;
    document.getElementById('parliamentGroup').style.display = 'none';
    document.getElementById('dunGroup').style.display = 'none';
    document.getElementById('parliament_id').value = '';
    document.getElementById('dun_id').value = '';
    document.getElementById('full_name').value = '';
    document.getElementById('department').value = '';
    document.getElementById('contact_number').value = '';
    document.getElementById('email').value = '';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password').required = true;
    document.getElementById('password_confirmation').value = '';
    document.getElementById('password_confirmation').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('passwordConfirmRequired').style.display = 'inline';
    document.getElementById('userModal').classList.add('show');
}

function editUser(id, type, parliamentId, dunId, fullName, department, contactNumber, email, username) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('userForm').action = '/pages/users-id/parliament/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('userId').value = id;
    
    if (type === 'parliament') {
        document.getElementById('typeParliament').checked = true;
        document.getElementById('parliamentGroup').style.display = 'block';
        document.getElementById('dunGroup').style.display = 'none';
        document.getElementById('parliament_id').value = parliamentId;
        document.getElementById('parliament_id').required = true;
        document.getElementById('dun_id').required = false;
    } else {
        document.getElementById('typeDun').checked = true;
        document.getElementById('parliamentGroup').style.display = 'none';
        document.getElementById('dunGroup').style.display = 'block';
        document.getElementById('dun_id').value = dunId;
        document.getElementById('dun_id').required = true;
        document.getElementById('parliament_id').required = false;
    }
    
    document.getElementById('full_name').value = fullName;
    document.getElementById('department').value = department || '';
    document.getElementById('contact_number').value = contactNumber || '';
    document.getElementById('email').value = email || '';
    document.getElementById('username').value = username;
    document.getElementById('password').value = '';
    document.getElementById('password').required = false;
    document.getElementById('password_confirmation').value = '';
    document.getElementById('password_confirmation').required = false;
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('passwordConfirmRequired').style.display = 'none';
    document.getElementById('userModal').classList.add('show');
}

function closeModal() {
    document.getElementById('userModal').classList.remove('show');
}

function deleteUser(id, name) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete user "' + name + '"?';
    document.getElementById('deleteForm').action = '/pages/users-id/parliament/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('userModal').addEventListener('click', function(e) {
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
