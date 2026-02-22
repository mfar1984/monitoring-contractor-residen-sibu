@extends('layouts.app')

@section('title', 'Create Contractor Analysis Transfer - Monitoring System')

@push('styles')
<style>
    .project-selection-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 11px;
        background: white;
    }
    
    .project-selection-table th {
        background-color: #f8f9fa;
        padding: 12px 10px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border: 1px solid #dee2e6;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .project-selection-table td {
        padding: 10px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    .project-selection-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .project-selection-table input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #007bff;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-active {
        background-color: #d4edda;
        color: #155724;
    }
    
    .file-upload-section {
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 30px 20px;
        margin-top: 20px;
        text-align: center;
    }
    
    .file-upload-section.has-file {
        border-color: #28a745;
        background: #d4edda;
    }
    
    .file-upload-icon {
        font-size: 48px;
        color: #007bff;
        margin-bottom: 15px;
        display: block;
    }
    
    .file-upload-label {
        font-size: 12px;
        font-weight: 600;
        color: #333;
        margin-bottom: 10px;
        display: block;
    }
    
    .file-upload-hint {
        font-size: 11px;
        color: #666;
        margin-top: 10px;
    }
    
    .file-input-wrapper {
        position: relative;
        display: inline-block;
        margin-top: 10px;
    }
    
    .file-input-wrapper input[type="file"] {
        position: absolute;
        left: -9999px;
    }
    
    .file-input-button {
        background-color: #007bff;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 500;
        display: inline-block;
    }
    
    .file-input-button:hover {
        background-color: #0056b3;
    }
    
    .file-name-display {
        margin-top: 12px;
        font-size: 12px;
        color: #333;
        font-weight: 600;
    }
    
    .selection-summary {
        background: #e7f3ff;
        border: 1px solid #007bff;
        border-radius: 4px;
        padding: 12px 16px;
        margin-top: 15px;
        font-size: 12px;
        color: #004085;
        font-weight: 600;
    }
</style>
@endpush

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Contractor Analysis</span>
    <span class="breadcrumb-separator">›</span>
    <span>Create Transfer</span>
@endsection

@section('content')
    @if($errors->any())
    <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
        <div class="content-header" style="margin-bottom: 24px;">
            <div class="content-header-left">
                <h3>Create Contractor Analysis Transfer</h3>
                <p class="content-description">Transfer multiple projects for contractor analysis and selection</p>
            </div>
        </div>
        
        <div style="border-top: 1px solid #e0e0e0; margin-bottom: 24px;"></div>

        <form method="POST" action="{{ route('pages.contractor-analysis.store') }}" id="transferForm" enctype="multipart/form-data">
            @csrf

            <!-- Project Selection Section -->
            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 13px; font-weight: 600; color: #333; margin-bottom: 12px;">
                    Select Projects <span style="color: #dc3545;">*</span>
                </h4>
                <p style="font-size: 11px; color: #666; margin-bottom: 15px;">
                    Select one or more projects to transfer for contractor analysis. Only Active projects are available for transfer.
                </p>

                @if($availableProjects->count() > 0)
                <table class="project-selection-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">
                                <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                            </th>
                            <th>Project Number</th>
                            <th>Project Name</th>
                            <th>Agency</th>
                            <th>Total Cost (RM)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($availableProjects as $project)
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" 
                                       name="project_ids[]" 
                                       value="{{ $project->id }}" 
                                       class="project-checkbox"
                                       onchange="updateSelectionSummary()">
                            </td>
                            <td>{{ $project->project_number }}</td>
                            <td>{{ $project->name }}</td>
                            <td>{{ $project->agencyCategory?->name ?? '-' }}</td>
                            <td style="text-align: right; font-weight: 600;">{{ number_format($project->total_cost, 2) }}</td>
                            <td>
                                <span class="status-badge status-active">{{ $project->status }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div id="selectionSummary" class="selection-summary" style="display: none;">
                    <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 16px;">check_circle</span>
                    <span id="selectedCount">0</span> project(s) selected
                </div>
                @else
                <div style="padding: 20px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; text-align: center;">
                    <p style="margin: 0; color: #856404; font-size: 12px;">
                        No projects available for transfer. All projects may already be in analysis or have been transferred.
                    </p>
                </div>
                @endif
            </div>

            <!-- Attachment Upload Section -->
            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 13px; font-weight: 600; color: #333; margin-bottom: 12px;">
                    Application Letter / Surat Permohonan <span style="color: #dc3545;">*</span>
                </h4>
                <p style="font-size: 11px; color: #666; margin-bottom: 15px;">
                    Upload the official application letter for this transfer request.
                </p>

                <div class="file-upload-section" id="fileUploadSection">
                    <span class="material-symbols-outlined file-upload-icon">upload_file</span>
                    <div class="file-input-wrapper">
                        <input type="file" 
                               id="attachment" 
                               name="attachment" 
                               accept=".pdf,.doc,.docx" 
                               required
                               onchange="handleFileSelect(this)">
                        <label for="attachment" class="file-input-button">
                            Choose File
                        </label>
                    </div>
                    <div class="file-upload-hint">
                        Accepted formats: PDF, DOC, DOCX (Max size: 5MB)
                    </div>
                    <div id="fileNameDisplay" class="file-name-display" style="display: none;"></div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button 
                    type="submit" 
                    id="submitButton" 
                    disabled
                    style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"
                >
                    Create Transfer
                </button>
                <a 
                    href="{{ route('pages.contractor-analysis') }}" 
                    style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; text-decoration: none; display: inline-block;"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.project-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateSelectionSummary();
        }

        function updateSelectionSummary() {
            const checkboxes = document.querySelectorAll('.project-checkbox:checked');
            const count = checkboxes.length;
            const summary = document.getElementById('selectionSummary');
            const countDisplay = document.getElementById('selectedCount');
            const selectAllCheckbox = document.getElementById('selectAll');
            
            if (count > 0) {
                summary.style.display = 'block';
                countDisplay.textContent = count;
            } else {
                summary.style.display = 'none';
            }

            // Update select all checkbox state
            const allCheckboxes = document.querySelectorAll('.project-checkbox');
            selectAllCheckbox.checked = count === allCheckboxes.length && count > 0;
            
            // Enable/disable submit button
            validateForm();
        }

        function handleFileSelect(input) {
            const fileUploadSection = document.getElementById('fileUploadSection');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            
            if (input.files && input.files.length > 0) {
                let allFilesValid = true;
                let fileNames = [];
                let totalSize = 0;
                
                // Check each file
                for (let i = 0; i < input.files.length; i++) {
                    const file = input.files[i];
                    const fileSize = file.size / 1024 / 1024; // Convert to MB
                    totalSize += fileSize;
                    
                    if (fileSize > 5) {
                        alert(`File "${file.name}" exceeds 5MB limit. Please choose smaller files.`);
                        input.value = '';
                        fileUploadSection.classList.remove('has-file');
                        fileNameDisplay.style.display = 'none';
                        validateForm();
                        return;
                    }
                    
                    fileNames.push(file.name);
                }
                
                fileUploadSection.classList.add('has-file');
                
                // Display file names
                if (fileNames.length === 1) {
                    fileNameDisplay.textContent = '📎 ' + fileNames[0];
                } else {
                    fileNameDisplay.innerHTML = '📎 ' + fileNames.length + ' files selected:<br>' + 
                        fileNames.map(name => '• ' + name).join('<br>');
                }
                fileNameDisplay.style.display = 'block';
            } else {
                fileUploadSection.classList.remove('has-file');
                fileNameDisplay.style.display = 'none';
            }
            
            validateForm();
        }

        function validateForm() {
            const selectedProjects = document.querySelectorAll('.project-checkbox:checked').length;
            const fileInput = document.getElementById('attachment');
            const submitButton = document.getElementById('submitButton');
            
            const isValid = selectedProjects > 0 && fileInput.files.length > 0;
            submitButton.disabled = !isValid;
        }

        // Form submission validation
        document.getElementById('transferForm').addEventListener('submit', function(e) {
            const selectedProjects = document.querySelectorAll('.project-checkbox:checked').length;
            const fileInput = document.getElementById('attachment');
            
            if (selectedProjects === 0) {
                e.preventDefault();
                alert('Please select at least one project to transfer.');
                return false;
            }
            
            if (fileInput.files.length === 0) {
                e.preventDefault();
                alert('Please upload at least one application letter.');
                return false;
            }
            
            return true;
        });
    </script>
@endsection
