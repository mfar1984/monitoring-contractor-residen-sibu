@extends('layouts.app')

@section('title', 'Master Data - Member of Parliament - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Master Data</span>
    <span class="breadcrumb-separator">›</span>
    <span>Member of Parliament</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.master-data.residen') }}" class="tab-button">Residen</a>
            <a href="{{ route('pages.master-data.agency') }}" class="tab-button">Agency</a>
            <a href="{{ route('pages.master-data.parliament') }}" class="tab-button active">Member of Parliament</a>
            <a href="{{ route('pages.master-data.contractor') }}" class="tab-button">Contractor</a>
            <a href="{{ route('pages.master-data.status') }}" class="tab-button">Status</a>
            <a href="{{ route('pages.master-data.project-category') }}" class="tab-button">Project Category</a>
            <a href="{{ route('pages.master-data.division') }}" class="tab-button">Division</a>
            <a href="{{ route('pages.master-data.district') }}" class="tab-button">District</a>
        </div>
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            <x-data-table
                title="Parliament Categories"
                description="Manage parliament categories (DUN Nangka, DUN Bawang Assan, Parlimen Sibu, etc.)."
                createButtonText="Create Category"
                createButtonRoute="#"
                searchPlaceholder="Search parliament..."
                :columns="['Name', 'Code', 'Type', 'Description', 'Status', 'Actions']"
                :data="$categories"
                :rowsPerPage="5"
            >
                @forelse($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->code }}</td>
                    <td>
                        <span class="status-badge status-active">
                            {{ $category->type }}
                        </span>
                    </td>
                    <td>{{ $category->description ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $category->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $category->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-edit" title="Edit" onclick="editCategory({{ $category->id }}, '{{ $category->name }}', '{{ $category->budget ?? 0 }}', '{{ $category->code }}', '{{ $category->type }}', '{{ $category->description }}', '{{ $category->status }}')">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete" onclick="deleteCategory({{ $category->id }}, '{{ $category->name }}')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No parliament categories found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" id="categoryModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create Category</h3>
                <button class="modal-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="categoryForm" method="POST" action="{{ route('pages.master-data.parliament.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="id" id="categoryId">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="budget">Budget (RM) <span style="color: #dc3545;">*</span></label>
                            <input type="text" id="budget" name="budget" pattern="[0-9]+" title="Only digits are allowed" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        
                        <div class="form-group">
                            <label for="code">Code <span style="color: #dc3545;">*</span></label>
                            <input type="text" id="code" name="code" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="type">Type <span style="color: #dc3545;">*</span></label>
                        <select id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="DUN">DUN</option>
                            <option value="Parliament">Parliament</option>
                        </select>
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
    if (createBtn && createBtn.textContent.includes('Create Category')) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            openCreateModal();
        };
    }
});

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Category';
    document.getElementById('categoryForm').action = '{{ route("pages.master-data.parliament.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('categoryId').value = '';
    document.getElementById('name').value = '';
    document.getElementById('budget').value = '';
    document.getElementById('code').value = '';
    document.getElementById('type').value = '';
    document.getElementById('description').value = '';
    document.getElementById('status').value = 'Active';
    document.getElementById('categoryModal').classList.add('show');
}

function editCategory(id, name, budget, code, type, description, status) {
    document.getElementById('modalTitle').textContent = 'Edit Category';
    document.getElementById('categoryForm').action = '/pages/master-data/parliament/' + id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('categoryId').value = id;
    document.getElementById('name').value = name;
    document.getElementById('budget').value = budget;
    document.getElementById('code').value = code;
    document.getElementById('type').value = type;
    document.getElementById('description').value = description || '';
    document.getElementById('status').value = status;
    document.getElementById('categoryModal').classList.add('show');
}

function closeModal() {
    document.getElementById('categoryModal').classList.remove('show');
}

function deleteCategory(id, name) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete "' + name + '"?';
    document.getElementById('deleteForm').action = '/pages/master-data/parliament/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('categoryModal').addEventListener('click', function(e) {
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
