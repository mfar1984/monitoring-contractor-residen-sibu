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
                    <td>
                        @php
                            $totalBudget = $dun->budgets->sum('budget');
                        @endphp
                        {{ number_format($totalBudget, 2) }}
                    </td>
                    <td>{{ $dun->code }}</td>
                    <td>{{ $dun->description ?? '-' }}</td>
                    <td>
                        <span class="status-badge {{ $dun->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $dun->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-edit" title="Edit" onclick="editDun({{ $dun->id }})">
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
                    
                    <div class="form-group">
                        <label for="code">Code <span style="color: #dc3545;">*</span></label>
                        <input type="text" id="code" name="code" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Budget Allocation <span style="color: #dc3545;">*</span></label>
                        <div id="budget-entries" style="display: flex; flex-direction: column; gap: 10px;">
                            <!-- Budget rows will be added here dynamically -->
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
let budgetRowIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    const createBtn = document.querySelector('.btn-primary');
    if (createBtn && createBtn.textContent.includes('Create DUN')) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            openCreateModal();
        };
    }
});

function createBudgetRow(index, year = '', budget = '') {
    const row = document.createElement('div');
    row.className = 'budget-row';
    row.style.cssText = 'display: grid; grid-template-columns: 150px 1fr auto auto; gap: 10px; align-items: start;';
    row.dataset.index = index;
    
    const currentYear = new Date().getFullYear();
    const yearOptions = [];
    for (let y = 2024; y <= 2030; y++) {
        yearOptions.push(`<option value="${y}" ${year == y ? 'selected' : ''}>${y}</option>`);
    }
    
    row.innerHTML = `
        <div class="form-group" style="margin: 0;">
            <select name="budgets[${index}][year]" required style="height: 34px;">
                <option value="">Year</option>
                ${yearOptions.join('')}
            </select>
        </div>
        <div class="form-group" style="margin: 0;">
            <input type="number" 
                   name="budgets[${index}][budget]" 
                   placeholder="Budget Amount (RM)" 
                   required 
                   min="0" 
                   step="0.01"
                   value="${budget}"
                   style="height: 34px;">
        </div>
        <button type="button" 
                class="btn action-btn add-budget-btn" 
                onclick="addBudgetRow()"
                title="Add Year"
                style="height: 34px; width: 34px; padding: 5px; display: flex; align-items: center; justify-content: center; background-color: #28a745; color: white; border: none;">
            <span class="material-symbols-outlined" style="font-size: 18px;">add</span>
        </button>
        <button type="button" 
                class="btn action-btn action-delete delete-budget-btn" 
                onclick="removeBudgetRow(${index})"
                title="Delete"
                style="height: 34px; width: 34px; padding: 5px; display: flex; align-items: center; justify-content: center; background-color: #dc3545; color: white; border: none;">
            <span class="material-symbols-outlined" style="font-size: 18px; color: white;">remove</span>
        </button>
    `;
    
    return row;
}

function addBudgetRow() {
    const container = document.getElementById('budget-entries');
    const row = createBudgetRow(budgetRowIndex++);
    container.appendChild(row);
    updateDeleteButtons();
}

function removeBudgetRow(index) {
    const container = document.getElementById('budget-entries');
    const row = container.querySelector(`[data-index="${index}"]`);
    if (row && container.children.length > 1) {
        row.remove();
        updateDeleteButtons();
    }
}

function updateDeleteButtons() {
    const container = document.getElementById('budget-entries');
    const deleteButtons = container.querySelectorAll('.delete-budget-btn');
    const shouldDisable = container.children.length === 1;
    
    deleteButtons.forEach(btn => {
        btn.disabled = shouldDisable;
        if (shouldDisable) {
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
        } else {
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
        }
    });
}

function clearBudgetRows() {
    const container = document.getElementById('budget-entries');
    container.innerHTML = '';
    budgetRowIndex = 0;
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create DUN';
    document.getElementById('dunForm').action = '{{ route("pages.master-data.duns.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('dunId').value = '';
    document.getElementById('name').value = '';
    document.getElementById('code').value = '';
    document.getElementById('description').value = '';
    document.getElementById('status').value = 'Active';
    
    // Clear and add one budget row
    clearBudgetRows();
    addBudgetRow();
    
    document.getElementById('dunModal').classList.add('show');
}

function editDun(id) {
    // Find dun data from the table
    const duns = @json($duns);
    const dun = duns.find(d => d.id === id);
    
    if (!dun) {
        alert('DUN data not found');
        return;
    }
    
    document.getElementById('modalTitle').textContent = 'Edit DUN';
    document.getElementById('dunForm').action = '/pages/master-data/duns/' + dun.id;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('dunId').value = dun.id;
    document.getElementById('name').value = dun.name;
    document.getElementById('code').value = dun.code;
    document.getElementById('description').value = dun.description || '';
    document.getElementById('status').value = dun.status;
    
    // Clear and add budget rows from existing budgets
    clearBudgetRows();
    const container = document.getElementById('budget-entries');
    
    if (dun.budgets && dun.budgets.length > 0) {
        dun.budgets.forEach(budget => {
            const row = createBudgetRow(budgetRowIndex++, budget.year, budget.budget);
            container.appendChild(row);
        });
    } else {
        // If no budgets, add one empty row
        addBudgetRow();
    }
    
    updateDeleteButtons();
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
