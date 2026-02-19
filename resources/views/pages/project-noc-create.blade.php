@extends('layouts.app')

@section('title', 'Create NOC - Monitoring System')

@push('styles')
<style>
    .top-row-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 15px;
        margin-bottom: 20px;
        align-items: stretch;
    }
    
    .form-with-budget {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }
    
    .form-main-content {
        flex: 1;
    }
    
    .date-field-container {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 16px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }
    
    .date-field-container .form-group {
        margin-bottom: 12px;
    }
    
    .date-field-container .action-buttons {
        display: flex;
        gap: 8px;
        margin-top: auto;
    }
    
    .budget-item {
        background: white;
        border: 1px solid #e0e0e0;
        padding: 16px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        height: 100%;
        min-height: 90px;
        position: relative;
        overflow: hidden;
    }
    
    .budget-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    
    .budget-item.total-noc::before {
        background: linear-gradient(90deg, #007bff 0%, #0056b3 100%);
    }
    
    .budget-item.total-allocated::before {
        background: linear-gradient(90deg, #28a745 0%, #1e7e34 100%);
    }
    
    .budget-item.remaining::before {
        background: linear-gradient(90deg, #ffc107 0%, #ff9800 100%);
    }
    
    .budget-label {
        font-size: 11px;
        color: #666;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    
    .budget-value {
        font-size: 20px;
        font-weight: 700;
        letter-spacing: -0.5px;
        color: #333;
    }
    
    .budget-item .material-symbols-outlined {
        color: #667eea;
    }
    
    .budget-item.remaining {
        border-color: #667eea;
    }
    
    .noc-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 11px;
    }
    
    .noc-table th {
        background-color: #f8f9fa;
        padding: 10px 8px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border: 1px solid #dee2e6;
    }
    
    .noc-table td {
        padding: 8px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    .noc-table input,
    .noc-table select {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #ced4da;
        border-radius: 3px;
        font-size: 11px;
    }
    
    .noc-table input:read-only {
        background-color: #e9ecef;
        color: #6c757d;
    }
    
    .noc-table input:disabled,
    .noc-table select:disabled {
        background-color: #f5f5f5;
        color: #999;
        cursor: not-allowed;
        border-color: #e0e0e0;
    }
    
    .noc-table input:focus,
    .noc-table select:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
    }
    
    .action-buttons-top {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn-import {
        background-color: #28a745;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-import:hover {
        background-color: #218838;
    }
    
    .btn-add-new {
        background-color: #007bff;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-add-new:hover {
        background-color: #0056b3;
    }
    
    .btn-delete-row {
        background-color: #dc3545;
        color: white;
        padding: 4px 8px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        font-size: 11px;
    }
    
    .btn-delete-row:hover {
        background-color: #c82333;
    }
    
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .modal-overlay.show {
        display: flex;
    }
    
    .modal-container {
        background-color: white;
        border-radius: 8px;
        max-width: 600px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .modal-header {
        padding: 16px 20px;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    
    .modal-close {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        color: #666;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .project-select-item {
        padding: 12px;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .project-select-item:hover {
        background-color: #f8f9fa;
        border-color: #007bff;
    }
    
    .project-select-item.selected {
        background-color: #e7f3ff;
        border-color: #007bff;
    }
    
    .project-select-item input[type="checkbox"] {
        margin-right: 10px;
    }
    
    .project-select-item input[type="checkbox"]:disabled {
        cursor: not-allowed;
    }
    
    .project-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .project-name {
        font-size: 12px;
        font-weight: 600;
        color: #333;
    }
    
    .project-cost {
        font-size: 12px;
        font-weight: 600;
        color: #007bff;
    }
    
    .modal-footer {
        padding: 16px 20px;
        border-top: 1px solid #dee2e6;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .form-group label {
        font-size: 11px;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        display: block;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-group input[type="date"] {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        font-size: 14px;
        color: #333;
        font-weight: 600;
    }
    
    .form-group input[type="date"]:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }
</style>
@endpush

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <span>NOC</span>
    <span class="breadcrumb-separator">›</span>
    <span>NOC</span>
    <span class="breadcrumb-separator">›</span>
    <span>Create</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-project-tabs active="noc" />
        
        <div class="tabs-content">
            @if($errors->any())
            <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="content-header">
                <div class="content-header-left">
                    <h3>Create NOC (Notice of Change)</h3>
                    <p class="content-description">Create a new Notice of Change document</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <form method="POST" action="{{ route('pages.project.noc.store') }}" id="nocForm" enctype="multipart/form-data">
                    @csrf

                    <!-- Top Row: NOC Date + 3 Budget Boxes -->
                    <div class="top-row-grid">
                        <!-- Column 1: NOC Date + Buttons -->
                        <div class="date-field-container">
                            <div class="form-group" style="margin-bottom: 12px;">
                                <label for="noc_date">NOC Date <span style="color: #dc3545;">*</span></label>
                                <input type="date" id="noc_date" name="noc_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button type="button" class="btn-import" onclick="openImportModal()" style="flex: 1;">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">file_download</span>
                                    Import
                                </button>
                                <button type="button" class="btn-add-new" onclick="addNewProjectRow()" style="flex: 1;">
                                    <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                                    Add New
                                </button>
                            </div>
                        </div>

                        <!-- Column 2: TOTAL NOC -->
                        <div class="budget-item total-noc" id="budgetBox1" style="display: none;">
                            <span class="material-symbols-outlined" style="font-size: 24px; color: #007bff; margin-bottom: 8px;">account_balance_wallet</span>
                            <div class="budget-label">TOTAL NOC</div>
                            <div class="budget-value" id="totalBudget">RM 0.00</div>
                        </div>

                        <!-- Column 3: TOTAL ALLOCATED -->
                        <div class="budget-item total-allocated" id="budgetBox2" style="display: none;">
                            <span class="material-symbols-outlined" style="font-size: 24px; color: #28a745; margin-bottom: 8px;">payments</span>
                            <div class="budget-label">TOTAL ALLOCATED</div>
                            <div class="budget-value" id="totalAllocated">RM 0.00</div>
                        </div>

                        <!-- Column 4: REMAINING BUDGET NOC -->
                        <div class="budget-item remaining" id="budgetBox3" style="display: none;">
                            <span class="material-symbols-outlined" style="font-size: 24px; color: #ffc107; margin-bottom: 8px;">account_balance</span>
                            <div class="budget-label">REMAINING BUDGET NOC</div>
                            <div class="budget-value" id="remainingBudget">RM 0.00</div>
                        </div>
                    </div>

                    <!-- Budget Warning Message (Over Budget) -->
                    <div id="budgetWarning" style="display: none; background-color: white; border: 1px solid #e0e0e0; border-left: 3px solid #dc3545; padding: 12px 16px; border-radius: 4px; margin-bottom: 15px; font-size: 12px;">
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #dc3545; flex-shrink: 0; margin-top: 1px;">error</span>
                            <div style="line-height: 1.5;">
                                <strong style="display: block; margin-bottom: 4px; color: #333;">Budget Exceeded</strong>
                                <span style="color: #666;">Total allocated amount exceeds available budget. Please import more projects or reduce allocated amounts.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Empty Row Warning Message -->
                    <div id="emptyRowWarning" style="display: none; background-color: white; border: 1px solid #e0e0e0; border-left: 3px solid #dc3545; padding: 12px 16px; border-radius: 4px; margin-bottom: 15px; font-size: 12px;">
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #dc3545; flex-shrink: 0; margin-top: 1px;">warning</span>
                            <div style="line-height: 1.5;">
                                <strong style="display: block; margin-bottom: 4px; color: #333;">Empty Rows Detected</strong>
                                <span style="color: #666;">Please delete empty rows (rows without New Cost) before creating NOC.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Budget Info Message (Remaining Budget) -->
                    <div id="budgetInfo" style="display: none; background-color: white; border: 1px solid #e0e0e0; border-left: 3px solid #ffc107; padding: 12px 16px; border-radius: 4px; margin-bottom: 15px; font-size: 12px;">
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #ffc107; flex-shrink: 0; margin-top: 1px;">info</span>
                            <div style="line-height: 1.5;">
                                <strong style="display: block; margin-bottom: 4px; color: #333;">Budget Not Fully Allocated</strong>
                                <span style="color: #666;">NOC can only be created when remaining budget is RM 0.00. Please allocate all budget to projects.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <table class="noc-table" id="projectsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 80px;">RTP Year <span style="color: #dc3545;">*</span></th>
                                        <th style="width: 100px;">Project No</th>
                                        <th style="width: 150px;">Current Project Name</th>
                                        <th style="width: 150px;">New Project Name</th>
                                        <th style="width: 120px;">Current Cost (RM)</th>
                                        <th style="width: 120px;">New Cost (RM)</th>
                                        <th style="width: 120px;">Implementing Agency</th>
                                        <th style="width: 150px;">New Implementing Agency</th>
                                        <th style="width: 150px;">Notes <span style="color: #dc3545;">*</span></th>
                                        <th style="width: 60px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="projectsTableBody">
                                    <tr>
                                        <td colspan="10" style="text-align: center; padding: 40px; color: #999;">
                                            <span class="material-symbols-outlined" style="font-size: 48px; color: #ddd;">inbox</span>
                                            <div style="margin-top: 10px;">No projects added yet. Click "Import Project" or "Add New Project" to begin.</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                    <!-- Attachments Section -->
                    <div style="background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-top: 24px;">
                        <h4 style="font-size: 13px; font-weight: 600; color: #333; margin: 0 0 16px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                            Attachments (Required)
                        </h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <!-- NOC Letter Attachment -->
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #333; margin-bottom: 8px; display: block; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Notice of Change Letter <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="file" 
                                       name="noc_letter_attachment" 
                                       id="nocLetterFile"
                                       accept=".pdf"
                                       required
                                       style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;"
                                       onchange="updateFileName('nocLetterFile', 'nocLetterFileName')">
                                <div id="nocLetterFileName" style="font-size: 11px; color: #666; margin-top: 6px;">
                                    <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">description</span>
                                    Accepted: PDF only (Max: 5MB)
                                </div>
                            </div>

                            <!-- NOC Project List Attachment -->
                            <div>
                                <label style="font-size: 11px; font-weight: 600; color: #333; margin-bottom: 8px; display: block; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Notice of Change List Project <span style="color: #dc3545;">*</span>
                                </label>
                                <input type="file" 
                                       name="noc_project_list_attachment" 
                                       id="nocProjectListFile"
                                       accept=".pdf"
                                       required
                                       style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;"
                                       onchange="updateFileName('nocProjectListFile', 'nocProjectListFileName')">
                                <div id="nocProjectListFileName" style="font-size: 11px; color: #666; margin-top: 6px;">
                                    <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">description</span>
                                    Accepted: PDF only (Max: 5MB)
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <a href="{{ route('pages.project.noc') }}" class="btn btn-secondary" style="text-decoration: none;">Cancel</a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Create NOC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Project Modal -->
    <div class="modal-overlay" id="importModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Import Existing Projects</h3>
                <button class="modal-close" onclick="closeImportModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body">
                @forelse($projects as $project)
                <div class="project-select-item" onclick="toggleProjectSelection({{ $project->id }})">
                    <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
                        <input type="checkbox" 
                               class="project-checkbox" 
                               data-project-id="{{ $project->id }}"
                               data-project-number="{{ $project->project_number }}"
                               data-project-year="{{ $project->project_year }}"
                               data-project-name="{{ $project->name }}"
                               data-project-cost="{{ $project->total_cost }}"
                               data-project-agency="{{ $project->agencyCategory->name ?? '' }}"
                               data-project-agency-id="{{ $project->agencyCategory->id ?? '' }}">
                        <div class="project-info" style="flex: 1;">
                            <span class="project-name">{{ $project->name }}</span>
                            <span class="project-cost">RM {{ number_format($project->total_cost, 2) }}</span>
                        </div>
                    </label>
                </div>
                @empty
                <div style="text-align: center; padding: 40px; color: #999;">
                    <span class="material-symbols-outlined" style="font-size: 48px; color: #ddd;">inbox</span>
                    <div style="margin-top: 10px;">No projects available to import</div>
                </div>
                @endforelse
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeImportModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="importSelectedProjects()">
                    <span class="material-symbols-outlined" style="font-size: 16px;">check</span>
                    Import Selected
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let rowCounter = 0;
let totalOriginalBudget = 0;
let importedProjectIds = []; // Track imported project IDs

function openImportModal() {
    // Update checkbox states based on imported projects
    updateCheckboxStates();
    document.getElementById('importModal').classList.add('show');
}

function closeImportModal() {
    // Uncheck all checkboxes when closing modal
    document.querySelectorAll('.project-checkbox:checked').forEach(checkbox => {
        checkbox.checked = false;
        checkbox.closest('.project-select-item').classList.remove('selected');
    });
    document.getElementById('importModal').classList.remove('show');
}

function updateCheckboxStates() {
    // Disable checkboxes for already imported projects
    document.querySelectorAll('.project-checkbox').forEach(checkbox => {
        const projectId = String(checkbox.dataset.projectId); // Convert to string for comparison
        if (importedProjectIds.includes(projectId)) {
            checkbox.disabled = true;
            checkbox.checked = false;
            checkbox.closest('.project-select-item').style.opacity = '0.5';
            checkbox.closest('.project-select-item').style.cursor = 'not-allowed';
        } else {
            checkbox.disabled = false;
            checkbox.closest('.project-select-item').style.opacity = '1';
            checkbox.closest('.project-select-item').style.cursor = 'pointer';
        }
    });
}

function toggleProjectSelection(projectId) {
    const checkbox = document.querySelector(`input[data-project-id="${projectId}"]`);
    
    // Don't allow selection if already imported
    if (checkbox.disabled) {
        return;
    }
    
    checkbox.checked = !checkbox.checked;
    
    const item = checkbox.closest('.project-select-item');
    if (checkbox.checked) {
        item.classList.add('selected');
    } else {
        item.classList.remove('selected');
    }
}

function importSelectedProjects() {
    const selectedCheckboxes = document.querySelectorAll('.project-checkbox:checked:not(:disabled)');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one project');
        return;
    }
    
    selectedCheckboxes.forEach(checkbox => {
        const projectId = String(checkbox.dataset.projectId);
        const projectNumber = checkbox.dataset.projectNumber;
        const projectYear = checkbox.dataset.projectYear;
        const projectName = checkbox.dataset.projectName;
        const projectCost = parseFloat(checkbox.dataset.projectCost);
        const projectAgency = checkbox.dataset.projectAgency;
        const projectAgencyId = checkbox.dataset.projectAgencyId;
        
        // Check if already imported
        if (importedProjectIds.includes(projectId)) {
            return;
        }
        
        addProjectRow(projectId, projectNumber, projectName, projectCost, projectAgency, projectAgencyId, projectYear, false);
        
        // Add to imported projects list
        importedProjectIds.push(projectId);
        
        // Add to total budget
        totalOriginalBudget += projectCost;
        
        // Uncheck after import
        checkbox.checked = false;
        checkbox.closest('.project-select-item').classList.remove('selected');
    });
    
    updateBudgetSummary();
    closeImportModal();
}

function addNewProjectRow() {
    addProjectRow(null, '', '', 0, '', '', '', true);
    // Run validation immediately after adding new row
    updateBudgetSummary();
}

function addProjectRow(projectId, projectNumber, projectName, projectCost, projectAgency, projectAgencyId, projectYear, isNew) {
    const tbody = document.getElementById('projectsTableBody');
    
    // Remove empty state if exists
    const emptyRow = tbody.querySelector('tr td[colspan="10"]');
    if (emptyRow) {
        emptyRow.parentElement.remove();
    }
    
    rowCounter++;
    const rowId = `row-${rowCounter}`;
    
    // Use only the year value from project_year
    let rtpYear = '';
    if (projectYear && !isNew) {
        rtpYear = projectYear;
    }
    
    const row = document.createElement('tr');
    row.id = rowId;
    row.dataset.projectId = projectId; // Store project ID in row
    row.innerHTML = `
        <td>
            <input type="text" name="projects[${rowCounter}][tahun_rtp]" value="${rtpYear}" required ${isNew ? '' : 'readonly style="background-color: #f5f5f5;"'}>
        </td>
        <td>
            <input type="text" name="projects[${rowCounter}][no_projek]" value="${projectNumber}" ${isNew ? '' : 'readonly'} ${isNew ? 'disabled' : ''} placeholder="${isNew ? 'N/A' : ''}">
        </td>
        <td>
            <input type="text" name="projects[${rowCounter}][nama_projek_asal]" value="${projectName}" ${isNew ? 'disabled' : 'readonly'} placeholder="${isNew ? 'N/A' : ''}">
            ${projectId ? `<input type="hidden" name="projects[${rowCounter}][project_id]" value="${projectId}">` : ''}
        </td>
        <td>
            <input type="text" name="projects[${rowCounter}][nama_projek_baru]" placeholder="${isNew ? 'Enter new project name' : 'Leave empty if no change'}" ${isNew ? 'required' : ''}>
        </td>
        <td>
            <input type="number" step="0.01" name="projects[${rowCounter}][kos_asal]" value="${projectCost}" ${isNew ? 'disabled' : 'readonly'} class="kos-asal-input" placeholder="${isNew ? 'N/A' : ''}">
        </td>
        <td>
            <input type="number" step="0.01" name="projects[${rowCounter}][kos_baru]" placeholder="${isNew ? 'Enter new cost' : 'Leave empty if no change'}" class="kos-baru-input" onchange="updateBudgetSummary()" ${isNew ? 'required' : ''}>
        </td>
        <td>
            <input type="text" name="projects[${rowCounter}][agensi_pelaksana_asal]" value="${projectAgency}" ${isNew ? 'disabled' : 'readonly'} placeholder="${isNew ? 'N/A' : ''}">
        </td>
        <td>
            <select name="projects[${rowCounter}][agensi_pelaksana_baru]" ${isNew ? 'required' : ''}>
                <option value="">${isNew ? 'Select Agency *' : 'Select Agency'}</option>
                @foreach($agencies as $agency)
                    <option value="{{ $agency->name }}">{{ $agency->name }}</option>
                @endforeach
            </select>
        </td>
        <td>
            <select name="projects[${rowCounter}][noc_note_id]" required>
                <option value="">Select Note</option>
                @foreach($nocNotes as $note)
                    <option value="{{ $note->id }}">{{ $note->name }}</option>
                @endforeach
            </select>
        </td>
        <td style="text-align: center;">
            <button type="button" class="btn-delete-row" onclick="deleteRow('${rowId}', ${projectCost}, '${projectId}')">
                <span class="material-symbols-outlined" style="font-size: 14px;">delete</span>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    
    // Show budget boxes if hidden
    document.getElementById('budgetBox1').style.display = 'block';
    document.getElementById('budgetBox2').style.display = 'block';
    document.getElementById('budgetBox3').style.display = 'block';
}

function deleteRow(rowId, originalCost, projectId) {
    if (confirm('Are you sure you want to delete this project?')) {
        // Subtract from total budget if it was an imported project
        if (originalCost > 0) {
            totalOriginalBudget -= originalCost;
        }
        
        // Remove from imported projects list
        if (projectId && projectId !== 'null' && projectId !== 'undefined') {
            const projectIdStr = String(projectId); // Convert to string for consistency
            const index = importedProjectIds.indexOf(projectIdStr);
            if (index > -1) {
                importedProjectIds.splice(index, 1);
            }
        }
        
        document.getElementById(rowId).remove();
        
        // Check if table is empty
        const tbody = document.getElementById('projectsTableBody');
        if (tbody.children.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="10" style="text-align: center; padding: 40px; color: #999;">
                        <span class="material-symbols-outlined" style="font-size: 48px; color: #ddd;">inbox</span>
                        <div style="margin-top: 10px;">No projects added yet. Click "Import Project" or "Add New Project" to begin.</div>
                    </td>
                </tr>
            `;
            document.getElementById('budgetBox1').style.display = 'none';
            document.getElementById('budgetBox2').style.display = 'none';
            document.getElementById('budgetBox3').style.display = 'none';
            totalOriginalBudget = 0;
        }
        
        updateBudgetSummary();
    }
}

function updateBudgetSummary() {
    let totalAllocated = 0;
    
    // Calculate total allocated from Kos Baru inputs
    document.querySelectorAll('.kos-baru-input').forEach(input => {
        const value = parseFloat(input.value) || 0;
        if (value > 0) {
            totalAllocated += value;
        }
    });
    
    const remaining = totalOriginalBudget - totalAllocated;
    
    // Update display
    document.getElementById('totalBudget').textContent = 'RM ' + totalOriginalBudget.toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('totalAllocated').textContent = 'RM ' + totalAllocated.toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('remainingBudget').textContent = 'RM ' + remaining.toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    
    // Change color if over budget
    const remainingElement = document.getElementById('remainingBudget');
    if (remaining < 0) {
        remainingElement.style.color = '#dc3545';
    } else {
        remainingElement.style.color = '#333';
    }
    
    // Check for empty rows (rows without New Cost)
    const hasEmptyRows = checkForEmptyRows();
    
    // Show/hide warning/info messages and disable/enable submit button
    const warningDiv = document.getElementById('budgetWarning');
    const infoDiv = document.getElementById('budgetInfo');
    const emptyRowWarning = document.getElementById('emptyRowWarning');
    const submitBtn = document.getElementById('submitBtn');
    
    // Button validation logic
    if (remaining < 0) {
        // Over budget - show error
        warningDiv.style.display = 'block';
        infoDiv.style.display = 'none';
        emptyRowWarning.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
    } else if (hasEmptyRows) {
        // Has empty rows - show warning and disable button
        warningDiv.style.display = 'none';
        infoDiv.style.display = 'none';
        emptyRowWarning.style.display = 'block';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
        submitBtn.title = 'Please delete empty rows or fill in New Cost';
    } else if (remaining > 0) {
        // Still have remaining budget - show info and disable button
        warningDiv.style.display = 'none';
        infoDiv.style.display = 'block';
        emptyRowWarning.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
        submitBtn.title = 'NOC can only be created when remaining budget is RM 0.00';
    } else {
        // Remaining budget is exactly RM 0.00 and no empty rows - enable button
        warningDiv.style.display = 'none';
        infoDiv.style.display = 'none';
        emptyRowWarning.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
        submitBtn.title = '';
    }
}

// Check for empty rows (rows without New Cost)
function checkForEmptyRows() {
    const tbody = document.getElementById('projectsTableBody');
    
    // Skip if table is empty (showing empty state message)
    const emptyStateRow = tbody.querySelector('tr td[colspan="10"]');
    if (emptyStateRow) {
        return false; // No actual rows, so no empty rows
    }
    
    const rows = tbody.querySelectorAll('tr');
    let hasEmpty = false;
    
    rows.forEach(row => {
        const kosBaru = row.querySelector('.kos-baru-input');
        const kosAsal = row.querySelector('.kos-asal-input');
        
        // Only check rows that have kos-baru-input field
        if (kosBaru && kosAsal) {
            const kosBaruValue = parseFloat(kosBaru.value) || 0;
            const kosAsalValue = parseFloat(kosAsal.value) || 0;
            
            // Check if this is a new project row (kosAsal input is disabled)
            const isNewProject = kosAsal.disabled || kosAsalValue === 0;
            
            // For new projects, check if New Cost is empty or 0
            // Also check if value is not a valid number (e.g., placeholder text)
            if (isNewProject) {
                const hasValidCost = kosBaru.value.trim() !== '' && 
                                    !isNaN(parseFloat(kosBaru.value)) && 
                                    parseFloat(kosBaru.value) > 0;
                
                if (!hasValidCost) {
                    // This is a new project row with no valid cost - EMPTY
                    hasEmpty = true;
                }
            }
        }
    });
    
    return hasEmpty;
}

// File upload handler
function updateFileName(inputId, displayId) {
    const input = document.getElementById(inputId);
    const display = document.getElementById(displayId);
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileSize = file.size / 1024 / 1024; // Convert to MB
        
        // Validate file size (5MB max)
        if (fileSize > 5) {
            alert('File size exceeds 5MB. Please choose a smaller file.');
            input.value = '';
            display.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">description</span> Accepted: PDF only (Max: 5MB)';
            return;
        }
        
        // Validate file type
        if (file.type !== 'application/pdf') {
            alert('Only PDF files are allowed.');
            input.value = '';
            display.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">description</span> Accepted: PDF only (Max: 5MB)';
            return;
        }
        
        // Display selected file name
        display.innerHTML = '<span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle; color: #28a745;">check_circle</span> ' + file.name + ' (' + fileSize.toFixed(2) + ' MB)';
        display.style.color = '#28a745';
    }
}

// Event delegation for real-time validation on input fields
document.getElementById('projectsTableBody').addEventListener('input', function(e) {
    if (e.target.classList.contains('kos-baru-input')) {
        updateBudgetSummary();
    }
});

// Also trigger validation on blur (when user leaves the field)
document.getElementById('projectsTableBody').addEventListener('blur', function(e) {
    if (e.target.classList.contains('kos-baru-input')) {
        updateBudgetSummary();
    }
}, true);

// Form validation
document.getElementById('nocForm').addEventListener('submit', function(e) {
    const tbody = document.getElementById('projectsTableBody');
    const hasProjects = tbody.querySelector('tr:not([colspan])');
    
    if (!hasProjects) {
        e.preventDefault();
        alert('Please add at least one project');
        return false;
    }
    
    // Check for over budget
    let totalAllocated = 0;
    document.querySelectorAll('.kos-baru-input').forEach(input => {
        const value = parseFloat(input.value) || 0;
        if (value > 0) {
            totalAllocated += value;
        }
    });
    
    const remaining = totalOriginalBudget - totalAllocated;
    
    if (remaining < 0) {
        e.preventDefault();
        alert('Budget exceeded! Total Allocated (RM ' + totalAllocated.toLocaleString('en-MY', {minimumFractionDigits: 2}) + ') cannot exceed Total NOC Budget (RM ' + totalOriginalBudget.toLocaleString('en-MY', {minimumFractionDigits: 2}) + '). Please import more projects or reduce the allocated amounts.');
        return false;
    }
    
    // Validate attachments
    const nocLetterFile = document.getElementById('nocLetterFile');
    const nocProjectListFile = document.getElementById('nocProjectListFile');
    
    if (!nocLetterFile.files || nocLetterFile.files.length === 0) {
        e.preventDefault();
        alert('Please upload Notice of Change Letter (PDF)');
        return false;
    }
    
    if (!nocProjectListFile.files || nocProjectListFile.files.length === 0) {
        e.preventDefault();
        alert('Please upload Notice of Change List Project (PDF)');
        return false;
    }
});

// Close modal when clicking outside
document.getElementById('importModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImportModal();
    }
});
</script>
@endpush
