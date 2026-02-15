@extends('layouts.app')

@section('title', 'Contractor Users - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Users Id</span>
    <span class="breadcrumb-separator">›</span>
    <span>Contractor</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.users-id.residen') }}" class="tab-button">Residen</a>
            <a href="{{ route('pages.users-id.agency') }}" class="tab-button">Agency</a>
            <a href="{{ route('pages.users-id.parliament') }}" class="tab-button">Member of Parliament</a>
            <a href="{{ route('pages.users-id.contractor') }}" class="tab-button active">Contractor</a>
        </div>
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            <x-data-table
                title="Contractor Users"
                description="Content for Contractor users will be displayed here."
                createButtonText="Create User"
                createButtonRoute="#"
                searchPlaceholder="Search users..."
                :columns="['Username', 'Full Name', 'Contractor', 'Department', 'Status', 'Actions']"
                :data="$users"
                :rowsPerPage="5"
            >
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->username }}</td>
                    <td>{{ $user->full_name ?? '-' }}</td>
                    <td>{{ $user->contractorCategory->company_name ?? '-' }}</td>
                    <td>{{ $user->department ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $user->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $user->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            @if($user->status === 'Active')
                            <button class="action-btn action-suspend" title="Suspend">
                                <span class="material-symbols-outlined">block</span>
                            </button>
                            @else
                            <button class="action-btn action-activate" title="Activate">
                                <span class="material-symbols-outlined">check_circle</span>
                            </button>
                            @endif
                            <button class="action-btn action-edit" title="Edit" onclick='editUser(@json($user))'>
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete" onclick="deleteUser({{ $user->id }}, '{{ $user->username }}')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No users found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" id="userModal">
        <div class="modal-container" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create User</h3>
                <button class="modal-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="userForm" method="POST" action="{{ route('pages.users-id.contractor.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="id" id="userId">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="contractor_category_id">Contractor <span style="color: #dc3545;">*</span></label>
                        <select id="contractor_category_id" name="contractor_category_id" required>
                            <option value="">Select Contractor List</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="full_name" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department</label>
                        <input type="text" id="department" name="department">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email">
                        </div>
                    </div>
                    
                    <hr style="margin: 20px 0; border: none; border-top: 1px solid #e0e0e0;">
                    
                    <div class="form-group">
                        <label for="username">Username <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="password">Password <span style="color: #dc3545;" id="passwordRequired">*</span></label>
                            <input type="password" id="password" name="password">
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;" id="passwordHint">Leave blank to keep current</small>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="password_confirmation">Confirm Password <span style="color: #dc3545;" id="confirmRequired">*</span></label>
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

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create User';
    document.getElementById('userForm').action = '{{ route("pages.users-id.contractor.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('userId').value = '';
    document.getElementById('contractor_category_id').value = '';
    document.getElementById('full_name').value = '';
    document.getElementById('department').value = '';
    document.getElementById('contact_number').value = '';
    document.getElementById('email').value = '';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password_confirmation').value = '';
    document.getElementById('password').required = true;
    document.getElementById('password_confirmation').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('confirmRequired').style.display = 'inline';
    document.getElementById('passwordHint').style.display = 'none';
    document.getElementById('userModal').classList.add('show');
}

function editUser(user) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('userForm').action = '/pages/users-id/contractor/' + user.id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('userId').value = user.id;
    document.getElementById('contractor_category_id').value = user.contractor_category_id || '';
    document.getElementById('full_name').value = user.full_name || '';
    document.getElementById('department').value = user.department || '';
    document.getElementById('contact_number').value = user.contact_number || '';
    document.getElementById('email').value = user.email || '';
    document.getElementById('username').value = user.username;
    document.getElementById('password').value = '';
    document.getElementById('password_confirmation').value = '';
    document.getElementById('password').required = false;
    document.getElementById('password_confirmation').required = false;
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('confirmRequired').style.display = 'none';
    document.getElementById('passwordHint').style.display = 'block';
    document.getElementById('userModal').classList.add('show');
}

function closeModal() {
    document.getElementById('userModal').classList.remove('show');
}

function deleteUser(id, username) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete user "' + username + '"?';
    document.getElementById('deleteForm').action = '/pages/users-id/contractor/' + id;
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
