@extends('layouts.app')

@section('title', 'Master Data - UPKJ - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Master Data</span>
    <span class="breadcrumb-separator">›</span>
    <span>UPKJ</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-master-data-tabs active="upkj" />
        
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

            @if($errors->any())
            <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <x-data-table
                title="UPKJ Classifications"
                description="Manage UPKJ (Unjuran Perolehan Kerja Jabatan) classifications for contractor categories."
                createButtonText="Add UPKJ Classification"
                createButtonRoute="#"
                searchPlaceholder="Search UPKJ..."
                :columns="['Category', 'Class', 'Head', 'Subhead', 'Description', 'Status', 'Actions']"
                :data="$upkjClassifications"
                :rowsPerPage="10"
            >
                @forelse($upkjClassifications as $upkj)
                <tr>
                    <td>{{ $upkj->category }}</td>
                    <td>
                        <strong>{{ $upkj->class }}</strong>
                        @if($upkj->class_description)
                        <br><small style="color: #666;">{{ $upkj->class_description }}</small>
                        @endif
                    </td>
                    <td>
                        @if($upkj->head_code)
                        <strong>{{ $upkj->head_code }}</strong>
                        @if($upkj->head_name)
                        <br><small style="color: #666;">{{ $upkj->head_name }}</small>
                        @endif
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>
                        @if($upkj->subhead_code || $upkj->subhead_letter || $upkj->subhead_roman)
                        {{ $upkj->subhead_code }}
                        @if($upkj->subhead_letter) ({{ $upkj->subhead_letter }}) @endif
                        @if($upkj->subhead_roman) {{ $upkj->subhead_roman }} @endif
                        @else
                        <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>{{ Str::limit($upkj->description, 50) }}</td>
                    <td>
                        @if($upkj->status === 'active')
                        <span class="status-badge status-active">Active</span>
                        @else
                        <span class="status-badge status-suspended">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-edit" onclick="editUpkj({{ $upkj->id }})" title="Edit">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" onclick="deleteUpkj({{ $upkj->id }})" title="Delete">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #999;">No UPKJ classifications found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal-overlay" id="upkjModal">
        <div class="modal-container" style="max-width: 700px;">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Add UPKJ Classification</h3>
                <button class="modal-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form id="upkjForm" method="POST" action="{{ route('pages.master-data.upkj.store') }}">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="id" id="upkjId">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="category">Category <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="category" name="category" placeholder="e.g., Works, Electrical, Mechanical" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="class">Class</label>
                            <select id="class" name="class">
                                <option value="">--- Select ---</option>
                                @foreach($classes as $class)
                                <option value="{{ $class->class }}">{{ $class->class }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="class_description">Class Description</label>
                            <input type="text" id="class_description" name="class_description" placeholder="e.g., Above 200,000">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
                        <div class="form-group">
                            <label for="head_code">Head Code</label>
                            <select id="head_code" name="head_code">
                                <option value="">--- Select ---</option>
                                @foreach($headCodes as $head)
                                <option value="{{ $head->head_code }}">{{ $head->head_code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="head_name">Head Name</label>
                            <input type="text" id="head_name" name="head_name" placeholder="e.g., Civil Engineering">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="subhead_code">Subhead (Code)</label>
                            <select id="subhead_code" name="subhead_code">
                                <option value="">--- Select ---</option>
                                @foreach($subheadCodes as $code)
                                <option value="{{ $code }}">{{ $code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subhead_letter">Subhead (Letter)</label>
                            <select id="subhead_letter" name="subhead_letter">
                                <option value="">--- Select ---</option>
                                @foreach($subheadLetters as $letter)
                                <option value="{{ $letter }}">{{ $letter }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="subhead_roman">Subhead (Roman)</label>
                            <select id="subhead_roman" name="subhead_roman">
                                <option value="">--- Select ---</option>
                                @foreach($subheadRomans as $roman)
                                <option value="{{ $roman }}">{{ $roman }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Description <span style="color: #dc3545;">*</span></label>
                        <textarea id="description" name="description" rows="3" placeholder="Enter full description of the classification" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span style="color: #dc3545;">*</span></label>
                        <select id="status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
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
                    <p>Are you sure you want to delete this UPKJ classification?</p>
                    <p style="color: #dc3545; font-weight: 500;">This action cannot be undone.</p>
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
    const createBtn = document.getElementById('create-button');
    if (createBtn) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            openAddModal();
        };
    }
});

const upkjData = @json($upkjClassifications);

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add UPKJ Classification';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('upkjForm').action = '{{ route("pages.master-data.upkj.store") }}';
    document.getElementById('upkjForm').reset();
    document.getElementById('upkjId').value = '';
    document.getElementById('upkjModal').classList.add('show');
}

function editUpkj(id) {
    const upkj = upkjData.find(u => u.id === id);
    if (!upkj) return;

    document.getElementById('modalTitle').textContent = 'Edit UPKJ Classification';
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('upkjForm').action = `/pages/master-data/upkj/${id}`;
    document.getElementById('upkjId').value = upkj.id;
    document.getElementById('category').value = upkj.category || '';
    document.getElementById('class').value = upkj.class || '';
    document.getElementById('class_description').value = upkj.class_description || '';
    document.getElementById('head_code').value = upkj.head_code || '';
    document.getElementById('head_name').value = upkj.head_name || '';
    document.getElementById('subhead_code').value = upkj.subhead_code || '';
    document.getElementById('subhead_letter').value = upkj.subhead_letter || '';
    document.getElementById('subhead_roman').value = upkj.subhead_roman || '';
    document.getElementById('description').value = upkj.description || '';
    document.getElementById('status').value = upkj.status || 'active';
    
    document.getElementById('upkjModal').classList.add('show');
}

function deleteUpkj(id) {
    document.getElementById('deleteForm').action = `/pages/master-data/upkj/${id}`;
    document.getElementById('deleteModal').classList.add('show');
}

function closeModal() {
    document.getElementById('upkjModal').classList.remove('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

// Close modals on outside click
document.getElementById('upkjModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeModal();
    }
});

document.getElementById('deleteModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeDeleteModal();
    }
});
</script>
@endpush
