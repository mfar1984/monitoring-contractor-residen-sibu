@extends('layouts.app')

@section('title', 'Master Data - Contractor - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Master Data</span>
    <span class="breadcrumb-separator">›</span>
    <span>Contractor</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-master-data-tabs active="contractor" />
        
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
            
            @if(session('import_errors'))
            <div style="padding: 10px; background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; border-radius: 4px; margin-bottom: 15px;">
                <strong>Import Errors Details:</strong>
                <ul style="margin: 10px 0 0 20px; padding: 0;">
                    @foreach(session('import_errors') as $error)
                        <li style="margin-bottom: 5px;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Action Buttons Row -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h2 style="margin: 0 0 5px 0; font-size: 14px; font-weight: 600; color: #333333;">Contractor Companies</h2>
                    <p style="margin: 0; font-size: 12px; color: #666666;">Manage contractor company list.</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <!-- Export Dropdown -->
                    <div style="position: relative;">
                        <button onclick="toggleExportDropdown()" class="btn" style="background-color: #28a745; color: white; display: flex; align-items: center; gap: 5px;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                            Export
                            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_drop_down</span>
                        </button>
                        <div id="exportDropdown" class="dropdown-menu" style="display: none; position: absolute; top: 100%; left: 0; margin-top: 5px; background: white; border: 1px solid #dee2e6; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); min-width: 180px; z-index: 1000;">
                            <a href="{{ route('pages.master-data.contractor.export') }}" style="display: flex; align-items: center; gap: 8px; padding: 10px 15px; color: #333; text-decoration: none; font-size: 12px; border-bottom: 1px solid #f0f0f0;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">description</span>
                                Export Company
                            </a>
                            <a href="{{ route('pages.master-data.contractor.export-upkj') }}" style="display: flex; align-items: center; gap: 8px; padding: 10px 15px; color: #333; text-decoration: none; font-size: 12px;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">badge</span>
                                Export UPKJ
                            </a>
                        </div>
                    </div>

                    <!-- Import Dropdown -->
                    <div style="position: relative;">
                        <button onclick="toggleImportDropdown()" class="btn" style="background-color: #17a2b8; color: white; display: flex; align-items: center; gap: 5px;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">upload</span>
                            Import
                            <span class="material-symbols-outlined" style="font-size: 16px;">arrow_drop_down</span>
                        </button>
                        <div id="importDropdown" class="dropdown-menu" style="display: none; position: absolute; top: 100%; left: 0; margin-top: 5px; background: white; border: 1px solid #dee2e6; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); min-width: 180px; z-index: 1000;">
                            <a href="javascript:void(0)" onclick="openImportCompanyModal()" style="display: flex; align-items: center; gap: 8px; padding: 10px 15px; color: #333; text-decoration: none; font-size: 12px; border-bottom: 1px solid #f0f0f0;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">description</span>
                                Import Company
                            </a>
                            <a href="javascript:void(0)" onclick="openImportUpkjModal()" style="display: flex; align-items: center; gap: 8px; padding: 10px 15px; color: #333; text-decoration: none; font-size: 12px;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">badge</span>
                                Import UPKJ
                            </a>
                        </div>
                    </div>

                    <!-- Create Button -->
                    <a href="{{ route('pages.master-data.contractor.create') }}" class="btn" style="background-color: #007bff; color: white; display: flex; align-items: center; gap: 5px; text-decoration: none;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                        Create Company
                    </a>
                </div>
            </div>

            <x-data-table
                title=""
                description=""
                createButtonText=""
                createButtonRoute=""
                searchPlaceholder="Search companies..."
                :columns="['Company Name', 'Registration No.', 'Code', 'UPKJ Class', 'Manpower', 'Status', 'Actions']"
                :data="$contractors"
                :rowsPerPage="10"
            >
                @forelse($contractors as $contractor)
                <tr>
                    <td>{{ $contractor->company_name }}</td>
                    <td>{{ $contractor->registration_number ?? '-' }}</td>
                    <td>{{ $contractor->code }}</td>
                    <td>{{ $contractor->upkj_class ?? '-' }}</td>
                    <td>{{ $contractor->manpower_total }}</td>
                    <td>
                        <span class="status-badge {{ $contractor->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $contractor->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('pages.master-data.contractor.edit', $contractor->id) }}" class="action-btn action-edit" title="Edit">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <button class="action-btn action-delete" title="Delete" onclick="deleteContractor({{ $contractor->id }}, '{{ $contractor->company_name }}')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px;">No companies found</td>
                </tr>
                @endforelse
            </x-data-table>
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

    <!-- Import Company Modal -->
    <div class="modal-overlay" id="importCompanyModal">
        <div class="modal-container" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">Import Contractor Companies</h3>
                <button class="modal-close" onclick="closeImportCompanyModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="importCompanyForm" method="POST" action="{{ route('pages.master-data.contractor.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 12px; margin-bottom: 20px; border-radius: 4px;">
                        <p style="margin: 0 0 8px 0; font-weight: 600; color: #1976D2;">Import Instructions:</p>
                        <ul style="margin: 0; padding-left: 20px; color: #666666; font-size: 12px;">
                            <li>Download the sample CSV file to see the correct format</li>
                            <li>Fill in your company data following the sample format</li>
                            <li>System will check if company exists (by code)</li>
                            <li>Existing companies will be updated with new data</li>
                            <li>New companies will be created</li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <a href="{{ route('pages.master-data.contractor.sample') }}" class="btn" style="background-color: #f5f5f5; color: #333; border: 1px solid #dee2e6; display: inline-flex; align-items: center; gap: 5px;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                            Download Sample CSV
                        </a>
                    </div>

                    <div class="form-group">
                        <label for="csv_file_company">Select CSV File <span class="required">*</span></label>
                        <input type="file" id="csv_file_company" name="csv_file" accept=".csv" required style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                        <span class="form-help">Only CSV files are accepted</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeImportCompanyModal()">Cancel</button>
                    <button type="submit" class="btn" style="background-color: #17a2b8; color: white;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">upload</span>
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Import UPKJ Modal -->
    <div class="modal-overlay" id="importUpkjModal">
        <div class="modal-container" style="max-width: 600px;">
            <div class="modal-header">
                <h3 class="modal-title">Import UPKJ Records</h3>
                <button class="modal-close" onclick="closeImportUpkjModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="importUpkjForm" method="POST" action="{{ route('pages.master-data.contractor.import-upkj') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div style="background-color: #e7f3ff; border-left: 4px solid #2196F3; padding: 12px; margin-bottom: 20px; border-radius: 4px;">
                        <p style="margin: 0 0 8px 0; font-weight: 600; color: #1976D2;">Import Instructions:</p>
                        <ul style="margin: 0; padding-left: 20px; color: #666666; font-size: 12px;">
                            <li>Download the sample CSV file to see the correct format</li>
                            <li>CSV must include Company Code and Company Name</li>
                            <li>System will match UPKJ records to companies by Code</li>
                            <li>Existing UPKJ records will be updated</li>
                            <li>New UPKJ records will be created</li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <a href="{{ route('pages.master-data.contractor.sample-upkj') }}" class="btn" style="background-color: #f5f5f5; color: #333; border: 1px solid #dee2e6; display: inline-flex; align-items: center; gap: 5px;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                            Download Sample CSV
                        </a>
                    </div>

                    <div class="form-group">
                        <label for="csv_file_upkj">Select CSV File <span class="required">*</span></label>
                        <input type="file" id="csv_file_upkj" name="csv_file" accept=".csv" required style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                        <span class="form-help">Only CSV files are accepted</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeImportUpkjModal()">Cancel</button>
                    <button type="submit" class="btn" style="background-color: #17a2b8; color: white;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">upload</span>
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function deleteContractor(id, name) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete "' + name + '"?';
    document.getElementById('deleteForm').action = '/pages/master-data/contractor/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

// Export Dropdown
function toggleExportDropdown() {
    const dropdown = document.getElementById('exportDropdown');
    const importDropdown = document.getElementById('importDropdown');
    
    // Close import dropdown if open
    importDropdown.style.display = 'none';
    
    // Toggle export dropdown
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

// Import Dropdown
function toggleImportDropdown() {
    const dropdown = document.getElementById('importDropdown');
    const exportDropdown = document.getElementById('exportDropdown');
    
    // Close export dropdown if open
    exportDropdown.style.display = 'none';
    
    // Toggle import dropdown
    dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
}

// Import Company Modal
function openImportCompanyModal() {
    document.getElementById('importDropdown').style.display = 'none';
    document.getElementById('importCompanyModal').classList.add('show');
}

function closeImportCompanyModal() {
    document.getElementById('importCompanyModal').classList.remove('show');
}

// Import UPKJ Modal
function openImportUpkjModal() {
    document.getElementById('importDropdown').style.display = 'none';
    document.getElementById('importUpkjModal').classList.add('show');
}

function closeImportUpkjModal() {
    document.getElementById('importUpkjModal').classList.remove('show');
}

// Close modals on outside click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

document.getElementById('importCompanyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImportCompanyModal();
    }
});

document.getElementById('importUpkjModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImportUpkjModal();
    }
});

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    const exportBtn = e.target.closest('button[onclick="toggleExportDropdown()"]');
    const importBtn = e.target.closest('button[onclick="toggleImportDropdown()"]');
    const exportDropdown = document.getElementById('exportDropdown');
    const importDropdown = document.getElementById('importDropdown');
    
    if (!exportBtn && !exportDropdown.contains(e.target)) {
        exportDropdown.style.display = 'none';
    }
    
    if (!importBtn && !importDropdown.contains(e.target)) {
        importDropdown.style.display = 'none';
    }
});

// Hover effect for dropdown items
document.querySelectorAll('.dropdown-menu a').forEach(item => {
    item.addEventListener('mouseenter', function() {
        this.style.backgroundColor = '#f8f9fa';
    });
    item.addEventListener('mouseleave', function() {
        this.style.backgroundColor = 'transparent';
    });
});
</script>
@endpush
