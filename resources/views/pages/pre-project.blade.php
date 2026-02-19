@extends('layouts.app')

@section('title', 'Pre-Project - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <span>Pre-Project</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.pre-project') }}" class="tab-button active">Pre-Project</a>
        </div>
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            @php
                // Show Create button for Admin, Residen, Parliament and DUN users
                // Hide for Agency and Contractor users
                $isAdmin = $user->username === 'admin' || $user->role === 'admin';
                $canCreate = $isAdmin || $user->residen_category_id || $user->parliament_category_id || $user->dun_id;
                $createButtonText = $canCreate ? 'Create Pre-Project' : '';
            @endphp

            <x-data-table
                title="Pre-Project"
                description="Manage pre-project data and information."
                :createButtonText="$createButtonText"
                createButtonRoute="#"
                searchPlaceholder="Search pre-projects..."
                :columns="['Name', 'Agency', 'Parliament', 'Total Cost (RM)', 'Status', 'Completeness', 'Actions']"
                :data="$preProjects"
                :rowsPerPage="10"
            >
                @forelse($preProjects as $preProject)
                <tr @if($preProject->status === 'NOC') style="background-color: #ffe6e6;" @elseif($preProject->status === 'Waiting for Complete Form') style="background-color: #f8f9fa;" @elseif($preProject->status === 'Waiting for Approver 1') style="background-color: #fff3cd;" @elseif($preProject->status === 'Waiting for EPU Approval') style="background-color: #cce5ff;" @endif>
                    <td>{{ $preProject->name }}</td>
                    <td>{{ $preProject->agencyCategory ? $preProject->agencyCategory->name : '-' }}</td>
                    <td>{{ $preProject->parliament ? $preProject->parliament->name : '-' }}</td>
                    <td>{{ number_format($preProject->total_cost ?? 0, 2) }}</td>
                    <td>
                        @if($preProject->status === 'NOC')
                            <span class="status-badge" style="background-color: #dc3545; color: white;">NOC</span>
                        @elseif($preProject->status === 'Waiting for Complete Form')
                            <span class="status-badge" style="background-color: #6c757d; color: white;">Waiting for Complete Form</span>
                        @elseif($preProject->status === 'Waiting for Approver 1')
                            <span class="status-badge" style="background-color: #ffc107; color: #856404;">Waiting for Approver 1</span>
                        @elseif($preProject->status === 'Waiting for EPU Approval')
                            <span class="status-badge" style="background-color: #17a2b8; color: white;">Waiting for EPU Approval</span>
                        @elseif($preProject->status === 'Approved')
                            <span class="status-badge status-active">Approved</span>
                        @else
                            <span class="status-badge {{ $preProject->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                                {{ $preProject->status }}
                            </span>
                        @endif
                    </td>
                    <td>
                        @if(in_array($preProject->status, ['Waiting for Complete Form', 'Waiting for Approver 1', 'Waiting for EPU Approval']))
                            <span class="status-badge" style="background-color: {{ $preProject->completeness_color }}; color: white;">
                                {{ $preProject->completeness_percentage }}%
                            </span>
                        @else
                            <span style="color: #999;">N/A</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-view" title="View" onclick="viewPreProject({{ $preProject->id }})">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                            
                            @php
                                $preProjectApproversJson = \App\Models\IntegrationSetting::getSetting('approver', 'pre_project_approvers');
                                $preProjectApprovers = $preProjectApproversJson ? json_decode($preProjectApproversJson, true) : [];
                                $isApprover = in_array(auth()->id(), $preProjectApprovers);
                                $isParliamentUser = $user->parliament_category_id || $user->dun_id;
                            @endphp
                            
                            @if($preProject->status === 'Waiting for Complete Form' && $isParliamentUser)
                                <!-- Edit button for incomplete forms -->
                                <button class="action-btn action-edit" title="Edit" onclick="editPreProject({{ $preProject->id }})">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                
                                <!-- Submit to EPU button only if 100% complete -->
                                @if($preProject->completeness_percentage === 100)
                                    <form method="POST" action="{{ route('pages.pre-project.submit-to-epu', $preProject->id) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="action-btn action-approve" title="Submit to Approver">
                                            <span class="material-symbols-outlined">send</span>
                                        </button>
                                    </form>
                                @endif
                                
                                <button class="action-btn action-delete" title="Delete" onclick="deletePreProject({{ $preProject->id }}, '{{ $preProject->name }}')">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            @elseif($preProject->status === 'Waiting for Approver 1' && $isApprover)
                                <button class="action-btn action-approve" title="Approve" onclick="approvePreProject({{ $preProject->id }}, '{{ $preProject->name }}', '{{ $preProject->status }}')">
                                    <span class="material-symbols-outlined">check_circle</span>
                                </button>
                                <button class="action-btn action-reject" title="Reject" onclick="rejectPreProject({{ $preProject->id }}, '{{ $preProject->name }}')">
                                    <span class="material-symbols-outlined">cancel</span>
                                </button>
                            @elseif($preProject->status !== 'NOC' && $preProject->status !== 'Approved' && $preProject->status !== 'Waiting for EPU Approval' && $preProject->status !== 'Waiting for Approver 1')
                                <button class="action-btn action-edit" title="Edit" onclick="editPreProject({{ $preProject->id }})">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                <button class="action-btn action-delete" title="Delete" onclick="deletePreProject({{ $preProject->id }}, '{{ $preProject->name }}')">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            @else
                                <button class="action-btn" title="Edit" disabled style="opacity: 0.3; cursor: not-allowed;">
                                    <span class="material-symbols-outlined">edit</span>
                                </button>
                                <button class="action-btn" title="Delete" disabled style="opacity: 0.3; cursor: not-allowed;">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 20px;">No pre-projects found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-overlay" id="preProjectModal">
        <div class="modal-container" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Create Pre-Project</h3>
                <button class="modal-close" onclick="closeModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="preProjectForm" method="POST" action="{{ route('pages.pre-project.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="POST" id="formMethod">
                <input type="hidden" name="id" id="preProjectId">
                
                <div class="modal-body" style="max-height: calc(90vh - 140px); overflow-y: auto;">
                    <!-- Basic Information Section -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Basic Information</h4>
                        
                        <div class="form-group">
                            <label for="name">Project Name <span style="color: #dc3545;">*</span></label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="residen_category_id">Residen</label>
                                <select id="residen_category_id" name="residen_category_id">
                                    <option value="">Select Residen</option>
                                    @foreach($residenCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="agency_category_id">Agency</label>
                                <select id="agency_category_id" name="agency_category_id">
                                    <option value="">Select Agency</option>
                                    @foreach($agencyCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="parliament_dun_basic">Parliament / DUN</label>
                                <select id="parliament_dun_basic" name="parliament_dun_basic">
                                    <option value="">Select Parliament / DUN</option>
                                    <optgroup label="Parliament">
                                        @foreach($parliaments as $parliament)
                                        <option value="parliament_{{ $parliament->id }}">{{ $parliament->name }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="DUN">
                                        @foreach($duns as $dun)
                                        <option value="dun_{{ $dun->id }}">{{ $dun->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="project_category_id">Project Category</label>
                                <select id="project_category_id" name="project_category_id">
                                    <option value="">Select Project Category</option>
                                    @foreach($projectCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="project_scope">Project Scope</label>
                            <textarea id="project_scope" name="project_scope" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Cost of Project Section -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Cost of Project</h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="actual_project_cost">Actual Project Cost (RM)</label>
                                <input type="number" id="actual_project_cost" name="actual_project_cost" step="0.01" min="0" oninput="calculateTotal()">
                            </div>
                            
                            <div class="form-group">
                                <label for="consultation_cost">Consultation Cost (RM)</label>
                                <input type="number" id="consultation_cost" name="consultation_cost" step="0.01" min="0" oninput="calculateTotal()">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="lss_inspection_cost">LSS Inspection Cost (RM)</label>
                                <input type="number" id="lss_inspection_cost" name="lss_inspection_cost" step="0.01" min="0" oninput="calculateTotal()">
                            </div>
                            
                            <div class="form-group">
                                <label for="sst">SST (RM)</label>
                                <input type="number" id="sst" name="sst" step="0.01" min="0" oninput="calculateTotal()">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="others_cost">Others (RM)</label>
                                <input type="number" id="others_cost" name="others_cost" step="0.01" min="0" oninput="calculateTotal()">
                            </div>
                            
                            <div class="form-group">
                                <label for="total_cost_display">Total Cost (RM)</label>
                                <input type="text" id="total_cost_display" readonly style="background-color: #f5f5f5; font-weight: 600;">
                                <input type="hidden" id="total_cost" name="total_cost" value="0">
                            </div>
                        </div>
                    </div>

                    <!-- Project Location Section -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Project Location</h4>
                        
                        <div class="form-group">
                            <label for="implementation_period">Implementation Period</label>
                            <input type="text" id="implementation_period" name="implementation_period" placeholder="e.g., Jan 2026 - Dec 2026">
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="division_id">Division</label>
                                <select id="division_id" name="division_id">
                                    <option value="">Select Division</option>
                                    @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="district_id">District</label>
                                <select id="district_id" name="district_id">
                                    <option value="">Select District</option>
                                    @foreach($districts as $district)
                                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="parliament_location_id">Parliament</label>
                                <select id="parliament_location_id" name="parliament_location_id">
                                    <option value="">Select Parliament</option>
                                    @foreach($parliaments as $parliament)
                                    <option value="{{ $parliament->id }}">{{ $parliament->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="dun_id">DUN</label>
                                <select id="dun_id" name="dun_id">
                                    <option value="">Select DUN</option>
                                    @foreach($duns as $dun)
                                    <option value="{{ $dun->id }}">{{ $dun->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Site Information Section -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Site Information</h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label>Site Layout</label>
                                <div style="display: flex; gap: 20px; margin-top: 8px;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="site_layout" value="Yes" id="site_layout_yes">
                                        <span>Yes</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="site_layout" value="No" id="site_layout_no">
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Consultation Service</label>
                                <div style="display: flex; gap: 20px; margin-top: 8px;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="consultation_service" value="Yes" id="consultation_service_yes">
                                        <span>Yes</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="consultation_service" value="No" id="consultation_service_no">
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="land_title_status_id">Land Title Status</label>
                            <select id="land_title_status_id" name="land_title_status_id">
                                <option value="">Select Land Title Status</option>
                                @foreach($landTitleStatuses as $status)
                                <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Implementation Details Section -->
                    <div style="margin-bottom: 20px;">
                        <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Implementation Details</h4>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="implementing_agency_id">Implementing Agency</label>
                                <select id="implementing_agency_id" name="implementing_agency_id">
                                    <option value="">Select Agency</option>
                                    @foreach($agencyCategories as $agency)
                                    <option value="{{ $agency->id }}">{{ $agency->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="implementation_method_id">Implementation Method</label>
                                <select id="implementation_method_id" name="implementation_method_id">
                                    <option value="">Select Implementation Method</option>
                                    @foreach($implementationMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="project_ownership_id">Project Ownership</label>
                                <select id="project_ownership_id" name="project_ownership_id">
                                    <option value="">Select Project Ownership</option>
                                    @foreach($projectOwnerships as $ownership)
                                    <option value="{{ $ownership->id }}">{{ $ownership->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="jkkk_name">JKKK Name</label>
                                <input type="text" id="jkkk_name" name="jkkk_name">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label>State Government Asset</label>
                                <div style="display: flex; gap: 20px; margin-top: 8px;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="state_government_asset" value="Yes" id="state_government_asset_yes">
                                        <span>Yes</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="state_government_asset" value="No" id="state_government_asset_no">
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Bill of Quantity</label>
                                <div style="display: flex; gap: 20px; margin-top: 8px;">
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="bill_of_quantity" value="Yes" id="bill_of_quantity_yes">
                                        <span>Yes</span>
                                    </label>
                                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                                        <input type="radio" name="bill_of_quantity" value="No" id="bill_of_quantity_no">
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group" id="attachment_container" style="display: none;">
                            <label for="bill_of_quantity_attachment">Bill of Quantity Attachment <span style="color: #dc3545;">*</span></label>
                            <input type="file" id="bill_of_quantity_attachment" name="bill_of_quantity_attachment" accept=".pdf,.doc,.docx,.xls,.xlsx">
                            <small style="color: #666666; display: block; margin-top: 5px;">Accepted formats: PDF, DOC, DOCX, XLS, XLSX (Max: 10MB)</small>
                            <div id="current_attachment" style="margin-top: 10px; display: none;">
                                <span style="color: #666666;">Current file: </span>
                                <a id="current_attachment_link" href="#" target="_blank" style="color: #007bff;"></a>
                            </div>
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
        <div class="modal-container" style="max-width: 500px;">
            <div class="modal-header" style="background-color: #dc3545; color: white; border-radius: 8px 8px 0 0;">
                <h3 class="modal-title" style="color: white; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 24px; line-height: 1;">warning</span>
                    Confirm Delete
                </h3>
                <button class="modal-close" onclick="closeDeleteModal()" style="color: white;">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="deleteForm" method="POST" onsubmit="return validateDelete()">
                @csrf
                @method('DELETE')
                <div class="modal-body" style="padding: 24px;">
                    <!-- Warning Icon and Message -->
                    <div style="text-align: center; margin-bottom: 24px;">
                        <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background-color: #fee; border-radius: 50%; margin-bottom: 16px;">
                            <span class="material-symbols-outlined" style="font-size: 36px; color: #dc3545; line-height: 1;">delete_forever</span>
                        </div>
                        <p style="margin: 0 0 8px 0; color: #333333; font-size: 16px; font-weight: 600;">
                            Are you sure you want to delete this pre-project?
                        </p>
                        <p id="deleteMessage" style="margin: 0; color: #666666; font-size: 14px; font-weight: 500;"></p>
                    </div>
                    
                    <!-- Warning Box -->
                    <div style="background: linear-gradient(135deg, #fff3cd 0%, #ffe8a1 100%); border-left: 4px solid #ffc107; border-radius: 6px; padding: 16px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <span class="material-symbols-outlined" style="font-size: 24px; color: #856404; flex-shrink: 0; line-height: 1;">info</span>
                            <div>
                                <p style="margin: 0 0 4px 0; color: #856404; font-size: 13px; font-weight: 600;">
                                    This action cannot be undone
                                </p>
                                <p style="margin: 0; color: #856404; font-size: 12px;">
                                    A unique 6-character code has been generated. Please type it below to confirm deletion.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Code Confirmation Input -->
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="deleteConfirmId" style="display: block; margin-bottom: 10px; font-weight: 600; font-size: 13px; color: #333333; text-transform: uppercase; letter-spacing: 0.5px;">
                            Type Code to Confirm:
                        </label>
                        <div style="background-color: #f8f9fa; border: 2px solid #e0e0e0; border-radius: 6px; padding: 14px; margin-bottom: 12px; text-align: center;">
                            <div style="display: inline-flex; align-items: center; justify-content: center; gap: 10px;">
                                <span class="material-symbols-outlined" style="font-size: 20px; color: #666666; line-height: 1;">lock</span>
                                <span style="font-family: 'Courier New', monospace; font-size: 22px; font-weight: 700; color: #dc3545; letter-spacing: 4px;" id="deleteIdDisplay"></span>
                            </div>
                        </div>
                        <input 
                            type="text" 
                            id="deleteConfirmId" 
                            placeholder="Enter code here..." 
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 6px; font-size: 16px; font-family: 'Courier New', monospace; font-weight: 600; letter-spacing: 3px; text-transform: uppercase; text-align: center; transition: all 0.3s ease;"
                            autocomplete="off"
                            maxlength="6"
                            onfocus="this.style.borderColor='#007bff'; this.style.boxShadow='0 0 0 3px rgba(0,123,255,0.1)';"
                            onblur="this.style.borderColor='#e0e0e0'; this.style.boxShadow='none';"
                        >
                        <div id="deleteError" style="display: none; margin-top: 8px; padding: 8px 12px; background-color: #fee; border-left: 3px solid #dc3545; border-radius: 4px;">
                            <span style="color: #dc3545; font-size: 12px; display: flex; align-items: center; gap: 6px;">
                                <span class="material-symbols-outlined" style="font-size: 16px; line-height: 1;">error</span>
                                <span id="deleteErrorText"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="padding: 16px 24px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; font-weight: 500; border: none; background-color: #6c757d; color: white; border-radius: 6px; cursor: pointer; transition: all 0.2s ease;">
                        <span class="material-symbols-outlined" style="font-size: 18px; line-height: 1;">close</span>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" id="deleteSubmitBtn" class="btn" style="background-color: #dc3545; color: white; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; font-weight: 500; border: none; border-radius: 6px; opacity: 0.5; cursor: not-allowed; transition: all 0.3s ease;" disabled>
                        <span class="material-symbols-outlined" style="font-size: 18px; line-height: 1;">delete</span>
                        <span>Delete Pre-Project</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Missing Fields Modal -->
    <div class="modal-overlay" id="missingFieldsModal">
        <div class="modal-container" style="max-width: 500px;">
            <div class="modal-header" style="background-color: #dc3545; color: white; border-radius: 8px 8px 0 0;">
                <h3 class="modal-title" style="color: white; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 24px; line-height: 1;">warning</span>
                    Incomplete Data
                </h3>
                <button class="modal-close" onclick="closeMissingFieldsModal()" style="color: white;">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <p style="margin-bottom: 15px; color: #333;">The following required fields are missing:</p>
                <ul id="missingFieldsList" style="margin-left: 20px; color: #dc3545; margin-bottom: 15px;">
                    <!-- Missing fields will be populated here -->
                </ul>
                <p style="color: #666;">Please complete all required fields before submitting to EPU.</p>
            </div>
            <div class="modal-footer" style="padding: 16px 24px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; display: flex; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeMissingFieldsModal()">Close</button>
            </div>
        </div>
    </div>

    <!-- View Modal -->

    <div class="modal-overlay" id="viewModal">
        <div class="modal-container" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 class="modal-title">View Pre-Project Details</h3>
                <button class="modal-close" onclick="closeViewModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: calc(90vh - 140px); overflow-y: auto;">
                <!-- Basic Information Section -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Basic Information</h4>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Project Name:</div>
                        <div style="color: #333333; font-size: 12px; font-weight: 500;" id="view_name"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Residen:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_residen"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Agency:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_agency"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Parliament / DUN:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_parliament_dun"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Project Category:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_project_category"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Project Scope:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_project_scope"></div>
                    </div>
                </div>

                <!-- Cost of Project Section -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Cost of Project</h4>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Actual Project Cost:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_actual_cost"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Consultation Cost:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_consultation_cost"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">LSS Inspection Cost:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_lss_cost"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">SST:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_sst"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Others:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_others"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Total Cost:</div>
                        <div style="color: #333333; font-size: 12px; font-weight: 600;" id="view_total_cost"></div>
                    </div>
                </div>

                <!-- Project Location Section -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Project Location</h4>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Implementation Period:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_implementation_period"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Division:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_division"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">District:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_district"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Parliament:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_parliament_location"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">DUN:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_dun"></div>
                    </div>
                </div>

                <!-- Site Information Section -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Site Information</h4>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Site Layout:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_site_layout"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Consultation Service:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_consultation_service"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Land Title Status:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_land_title_status"></div>
                    </div>
                </div>

                <!-- Implementation Details Section -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Implementation Details</h4>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Implementing Agency:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_implementing_agency"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Implementation Method:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_implementation_method"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Project Ownership:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_project_ownership"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">JKKK Name:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_jkkk_name"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">State Government Asset:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_state_government_asset"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Bill of Quantity:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_bill_of_quantity"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;" id="view_attachment_row">
                        <div style="color: #666666; font-size: 12px;">Attachment:</div>
                        <div style="color: #333333; font-size: 12px;">
                            <a id="view_attachment_link" href="#" target="_blank" style="color: #007bff; text-decoration: none;">Download</a>
                        </div>
                    </div>
                </div>

                <!-- Approval History Section -->
                <div style="margin-bottom: 20px;" id="approval_history_section">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Approval History</h4>
                    
                    <div id="approval_history_content">
                        <p style="color: #666666; font-size: 12px; text-align: center; padding: 20px;">No approval history</p>
                    </div>
                </div>

                <!-- Project Changes Section (from NOC) -->
                <div style="margin-bottom: 20px;" id="project_changes_section">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Project Changes (from NOC)</h4>
                    
                    <div id="project_changes_content">
                        <p style="color: #666666; font-size: 12px; text-align: center; padding: 20px;">No changes recorded</p>
                    </div>
                </div>

                <!-- NOC Attachments Section -->
                <div style="margin-bottom: 20px;" id="noc_attachments_section">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">NOC Attachments</h4>
                    
                    <div id="noc_attachments_content">
                        <p style="color: #666666; font-size: 12px; text-align: center; padding: 20px;">No attachments</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeViewModal()">Close</button>
                <button type="button" class="btn btn-primary" onclick="printPreProject()">
                    <span class="material-symbols-outlined">print</span>
                    Print
                </button>
            </div>
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div class="modal-overlay" id="rejectModal">
        <div class="modal-container" style="max-width: 600px;">
            <div class="modal-header" style="background-color: #dc3545; color: white; border-radius: 8px 8px 0 0;">
                <h3 class="modal-title" style="color: white; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 24px; line-height: 1;">cancel</span>
                    Reject Pre-Project
                </h3>
                <button class="modal-close" onclick="closeRejectModal()" style="color: white;">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body" style="padding: 24px;">
                    <!-- Warning Icon and Message -->
                    <div style="text-align: center; margin-bottom: 24px;">
                        <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background-color: #fee; border-radius: 50%; margin-bottom: 16px;">
                            <span class="material-symbols-outlined" style="font-size: 36px; color: #dc3545; line-height: 1;">block</span>
                        </div>
                        <p style="margin: 0 0 8px 0; color: #333333; font-size: 16px; font-weight: 600;">
                            Are you sure you want to reject this pre-project?
                        </p>
                        <p id="rejectProjectName" style="margin: 0; color: #666666; font-size: 14px; font-weight: 500;"></p>
                    </div>
                    
                    <!-- Info Box -->
                    <div style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-left: 4px solid #2196f3; border-radius: 6px; padding: 16px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <span class="material-symbols-outlined" style="font-size: 24px; color: #1976d2; flex-shrink: 0; line-height: 1;">info</span>
                            <div>
                                <p style="margin: 0 0 8px 0; color: #1565c0; font-weight: 600; font-size: 12px;">What happens when you reject:</p>
                                <ul style="margin: 0; padding-left: 20px; color: #424242; font-size: 11px; line-height: 1.6;">
                                    <li>Status will change to "Waiting for Complete Form"</li>
                                    <li>Parliament/DUN user can edit and resubmit</li>
                                    <li>Your rejection reason will be recorded</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Rejection Remarks -->
                    <div class="form-group">
                        <label for="rejection_remarks" style="display: block; margin-bottom: 8px; color: #333; font-weight: 600; font-size: 12px;">
                            Rejection Reason <span style="color: #dc3545;">*</span>
                        </label>
                        <textarea 
                            id="rejection_remarks" 
                            name="rejection_remarks" 
                            rows="4" 
                            required
                            oninput="validateRejectForm()"
                            placeholder="Please provide a detailed reason for rejection (minimum 10 characters)..."
                            style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px; font-family: inherit; resize: vertical;"
                        ></textarea>
                        <small style="color: #666; font-size: 11px; display: block; margin-top: 5px;">
                            Minimum 10 characters required
                        </small>
                    </div>
                    
                    <!-- Error Message -->
                    <div id="rejectError" style="display: none; background-color: #fee; border: 1px solid #fcc; border-radius: 4px; padding: 10px; margin-top: 15px;">
                        <span style="color: #dc3545; font-size: 12px;" id="rejectErrorText"></span>
                    </div>
                </div>
                
                <div class="modal-footer" style="padding: 16px 24px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" id="rejectSubmitBtn" class="btn" style="background-color: #dc3545; color: white; opacity: 0.5; cursor: not-allowed;" disabled>
                        <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">cancel</span>
                        Reject Pre-Project
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Approve Confirmation Modal -->
    <div class="modal-overlay" id="approveModal">
        <div class="modal-container" style="max-width: 600px;">
            <div class="modal-header" style="background-color: #28a745; color: white; border-radius: 8px 8px 0 0;">
                <h3 class="modal-title" style="color: white; display: flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 24px; line-height: 1;">check_circle</span>
                    Approve Pre-Project
                </h3>
                <button class="modal-close" onclick="closeApproveModal()" style="color: white;">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body" style="padding: 24px;">
                    <!-- Success Icon and Message -->
                    <div style="text-align: center; margin-bottom: 24px;">
                        <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; background-color: #d4edda; border-radius: 50%; margin-bottom: 16px;">
                            <span class="material-symbols-outlined" style="font-size: 36px; color: #28a745; line-height: 1;">task_alt</span>
                        </div>
                        <p style="margin: 0 0 8px 0; color: #333333; font-size: 16px; font-weight: 600;">
                            Are you sure you want to approve this pre-project?
                        </p>
                        <p id="approveProjectName" style="margin: 0; color: #666666; font-size: 14px; font-weight: 500;"></p>
                    </div>
                    
                    <!-- Info Box -->
                    <div style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-left: 4px solid #28a745; border-radius: 6px; padding: 16px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <div style="display: flex; align-items: flex-start; gap: 12px;">
                            <span class="material-symbols-outlined" style="font-size: 24px; color: #155724; flex-shrink: 0; line-height: 1;">info</span>
                            <div>
                                <p style="margin: 0 0 8px 0; color: #155724; font-weight: 600; font-size: 12px;">What happens when you approve:</p>
                                <ul style="margin: 0; padding-left: 20px; color: #424242; font-size: 11px; line-height: 1.6;">
                                    <li>Status will change to "Waiting for EPU Approval"</li>
                                    <li>Your approval will be recorded with timestamp</li>
                                    <li>Approval remarks are optional but recommended</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Approval Remarks (Optional) -->
                    <div class="form-group">
                        <label for="approval_remarks" style="display: block; margin-bottom: 8px; color: #333; font-weight: 600; font-size: 12px;">
                            Approval Remarks <span style="color: #666; font-weight: normal;">(Optional)</span>
                        </label>
                        <textarea 
                            id="approval_remarks" 
                            name="approval_remarks" 
                            rows="4" 
                            placeholder="Add any comments or notes about this approval (optional)..."
                            style="width: 100%; padding: 10px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px; font-family: inherit; resize: vertical;"
                        ></textarea>
                        <small style="color: #666; font-size: 11px; display: block; margin-top: 5px;">
                            You can leave this blank if no remarks needed
                        </small>
                    </div>
                </div>
                
                <div class="modal-footer" style="padding: 16px 24px; background-color: #f8f9fa; border-radius: 0 0 8px 8px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-secondary" onclick="closeApproveModal()">Cancel</button>
                    <button type="submit" id="approveSubmitBtn" class="btn" style="background-color: #28a745; color: white;">
                        <span class="material-symbols-outlined" style="font-size: 16px; vertical-align: middle;">check_circle</span>
                        Approve Pre-Project
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
    if (createBtn && createBtn.textContent.includes('Create Pre-Project')) {
        createBtn.onclick = function(e) {
            e.preventDefault();
            openCreateModal();
        };
    }
    
    // Add event listeners for Bill of Quantity radio buttons
    const billYes = document.getElementById('bill_of_quantity_yes');
    const billNo = document.getElementById('bill_of_quantity_no');
    const attachmentContainer = document.getElementById('attachment_container');
    const attachmentInput = document.getElementById('bill_of_quantity_attachment');
    
    if (billYes) {
        billYes.addEventListener('change', function() {
            if (this.checked) {
                attachmentContainer.style.display = 'block';
                attachmentInput.required = true;
            }
        });
    }
    
    if (billNo) {
        billNo.addEventListener('change', function() {
            if (this.checked) {
                attachmentContainer.style.display = 'none';
                attachmentInput.required = false;
                attachmentInput.value = ''; // Clear file input
            }
        });
    }
});

function calculateTotal() {
    const actualCost = parseFloat(document.getElementById('actual_project_cost').value) || 0;
    const consultationCost = parseFloat(document.getElementById('consultation_cost').value) || 0;
    const lssCost = parseFloat(document.getElementById('lss_inspection_cost').value) || 0;
    const sst = parseFloat(document.getElementById('sst').value) || 0;
    const others = parseFloat(document.getElementById('others_cost').value) || 0;
    
    const total = actualCost + consultationCost + lssCost + sst + others;
    document.getElementById('total_cost_display').value = total.toFixed(2);
    document.getElementById('total_cost').value = total.toFixed(2); // Set hidden input value
}

function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Create Pre-Project';
    document.getElementById('preProjectForm').action = '{{ route("pages.pre-project.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('preProjectId').value = '';
    
    // Reset all form fields
    document.getElementById('name').value = '';
    document.getElementById('residen_category_id').value = '';
    document.getElementById('agency_category_id').value = '';
    document.getElementById('parliament_dun_basic').value = '';
    document.getElementById('project_category_id').value = '';
    document.getElementById('project_scope').value = '';
    document.getElementById('actual_project_cost').value = '';
    document.getElementById('consultation_cost').value = '';
    document.getElementById('lss_inspection_cost').value = '';
    document.getElementById('sst').value = '';
    document.getElementById('others_cost').value = '';
    document.getElementById('total_cost_display').value = '0.00';
    document.getElementById('total_cost').value = '0'; // Reset hidden input
    document.getElementById('implementation_period').value = '';
    document.getElementById('division_id').value = '';
    document.getElementById('district_id').value = '';
    document.getElementById('parliament_location_id').value = '';
    document.getElementById('dun_id').value = '';
    
    // Auto-select Parliament/DUN based on logged-in user
    @if($user->parliament_id)
        document.getElementById('parliament_dun_basic').value = 'parliament_{{ $user->parliament_id }}';
        document.getElementById('parliament_location_id').value = '{{ $user->parliament_id }}';
    @elseif($user->dun_id)
        document.getElementById('parliament_dun_basic').value = 'dun_{{ $user->dun_id }}';
        document.getElementById('dun_id').value = '{{ $user->dun_id }}';
    @endif
    document.getElementById('land_title_status_id').value = '';
    document.getElementById('implementing_agency_id').value = '';
    document.getElementById('implementation_method_id').value = '';
    document.getElementById('project_ownership_id').value = '';
    document.getElementById('jkkk_name').value = '';
    
    // Reset file input and hide attachment container
    document.getElementById('bill_of_quantity_attachment').value = '';
    document.getElementById('bill_of_quantity_attachment').required = false;
    document.getElementById('attachment_container').style.display = 'none';
    document.getElementById('current_attachment').style.display = 'none';
    
    // Reset radio buttons
    document.querySelectorAll('input[type="radio"]').forEach(radio => radio.checked = false);
    
    document.getElementById('preProjectModal').classList.add('show');
}

function editPreProject(id) {
    // Fetch pre-project data via AJAX with cache busting
    // Add timestamp to prevent browser caching old data
    const timestamp = new Date().getTime();
    fetch('/pages/pre-project/' + id + '/edit?_=' + timestamp, {
        cache: 'no-cache',
        headers: {
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache'
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log('Fetched data:', data); // Debug log
            document.getElementById('modalTitle').textContent = 'Edit Pre-Project';
            document.getElementById('preProjectForm').action = '/pages/pre-project/' + id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('preProjectId').value = id;
            
            // Populate form fields
            document.getElementById('name').value = data.name || '';
            document.getElementById('residen_category_id').value = data.residen_category_id || '';
            document.getElementById('agency_category_id').value = data.agency_category_id || '';
            
            // Set combined Parliament/DUN dropdown for Basic Information
            if (data.parliament_id) {
                document.getElementById('parliament_dun_basic').value = 'parliament_' + data.parliament_id;
            } else if (data.dun_basic_id) {
                document.getElementById('parliament_dun_basic').value = 'dun_' + data.dun_basic_id;
            } else {
                document.getElementById('parliament_dun_basic').value = '';
            }
            
            document.getElementById('project_category_id').value = data.project_category_id || '';
            document.getElementById('project_scope').value = data.project_scope || '';
            document.getElementById('actual_project_cost').value = data.actual_project_cost || '';
            document.getElementById('consultation_cost').value = data.consultation_cost || '';
            document.getElementById('lss_inspection_cost').value = data.lss_inspection_cost || '';
            document.getElementById('sst').value = data.sst || '';
            document.getElementById('others_cost').value = data.others_cost || '';
            document.getElementById('implementation_period').value = data.implementation_period || '';
            document.getElementById('division_id').value = data.division_id || '';
            document.getElementById('district_id').value = data.district_id || '';
            document.getElementById('parliament_location_id').value = data.parliament_location_id || '';
            document.getElementById('dun_id').value = data.dun_id || '';
            document.getElementById('land_title_status_id').value = data.land_title_status_id || '';
            document.getElementById('implementing_agency_id').value = data.implementing_agency_id || '';
            document.getElementById('implementation_method_id').value = data.implementation_method_id || '';
            document.getElementById('project_ownership_id').value = data.project_ownership_id || '';
            document.getElementById('jkkk_name').value = data.jkkk_name || '';
            
            // Set other radio buttons
            if (data.site_layout) {
                document.getElementById('site_layout_' + data.site_layout.toLowerCase()).checked = true;
            }
            if (data.consultation_service) {
                document.getElementById('consultation_service_' + data.consultation_service.toLowerCase()).checked = true;
            }
            if (data.state_government_asset) {
                document.getElementById('state_government_asset_' + data.state_government_asset.toLowerCase()).checked = true;
            }
            if (data.bill_of_quantity) {
                document.getElementById('bill_of_quantity_' + data.bill_of_quantity.toLowerCase()).checked = true;
            }
            
            // Handle file attachment
            if (data.bill_of_quantity_attachment) {
                document.getElementById('bill_of_quantity_attachment').required = false;
                document.getElementById('current_attachment').style.display = 'block';
                document.getElementById('current_attachment_link').href = '/storage/' + data.bill_of_quantity_attachment;
                document.getElementById('current_attachment_link').textContent = data.bill_of_quantity_attachment.split('/').pop();
            } else {
                document.getElementById('bill_of_quantity_attachment').required = false;
                document.getElementById('current_attachment').style.display = 'none';
            }
            
            // Show/hide attachment container based on bill_of_quantity value
            const attachmentContainer = document.getElementById('attachment_container');
            if (data.bill_of_quantity === 'Yes') {
                attachmentContainer.style.display = 'block';
                document.getElementById('bill_of_quantity_attachment').required = !data.bill_of_quantity_attachment;
            } else {
                attachmentContainer.style.display = 'none';
                document.getElementById('bill_of_quantity_attachment').required = false;
            }
            
            calculateTotal();
            document.getElementById('preProjectModal').classList.add('show');
        })
        .catch(error => {
            console.error('Error fetching pre-project data:', error);
            alert('Failed to load pre-project data');
        });
}

function closeModal() {
    document.getElementById('preProjectModal').classList.remove('show');
}

// Generate random 6-digit alphanumeric code
function generateDeleteCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Exclude similar looking characters (I, O, 0, 1)
    let code = '';
    for (let i = 0; i < 6; i++) {
        code += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return code;
}

function deletePreProject(id, name) {
    // Generate unique 6-digit code
    const deleteCode = generateDeleteCode();
    window.deleteConfirmCode = deleteCode;
    window.deletePreProjectId = id;
    
    // Update modal content
    document.getElementById('deleteMessage').textContent = name;
    document.getElementById('deleteIdDisplay').textContent = deleteCode;
    document.getElementById('deleteForm').action = '/pages/pre-project/' + id;
    document.getElementById('deleteConfirmId').value = '';
    document.getElementById('deleteError').style.display = 'none';
    document.getElementById('deleteSubmitBtn').disabled = true;
    document.getElementById('deleteSubmitBtn').style.opacity = '0.5';
    document.getElementById('deleteSubmitBtn').style.cursor = 'not-allowed';
    document.getElementById('deleteSubmitBtn').style.transform = 'scale(1)';
    
    // Show modal
    document.getElementById('deleteModal').classList.add('show');
    
    // Focus on input field
    setTimeout(() => {
        document.getElementById('deleteConfirmId').focus();
    }, 100);
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
    document.getElementById('deleteConfirmId').value = '';
    document.getElementById('deleteError').style.display = 'none';
    document.getElementById('deleteSubmitBtn').disabled = true;
    window.deleteConfirmCode = null;
}

function validateDelete() {
    const inputCode = document.getElementById('deleteConfirmId').value.trim().toUpperCase();
    const expectedCode = window.deleteConfirmCode;
    
    if (inputCode !== expectedCode) {
        document.getElementById('deleteErrorText').textContent = 'Code does not match. Please type the correct code.';
        document.getElementById('deleteError').style.display = 'block';
        return false;
    }
    
    return true;
}

// Enable/disable delete button based on input
document.addEventListener('DOMContentLoaded', function() {
    const deleteInput = document.getElementById('deleteConfirmId');
    const deleteBtn = document.getElementById('deleteSubmitBtn');
    
    if (deleteInput && deleteBtn) {
        deleteInput.addEventListener('input', function() {
            const inputCode = this.value.trim().toUpperCase();
            const expectedCode = window.deleteConfirmCode;
            
            // Auto-uppercase as user types
            this.value = inputCode;
            
            if (inputCode === expectedCode) {
                deleteBtn.disabled = false;
                deleteBtn.style.opacity = '1';
                deleteBtn.style.cursor = 'pointer';
                deleteBtn.style.transform = 'scale(1.02)';
                deleteBtn.style.boxShadow = '0 4px 12px rgba(220, 53, 69, 0.3)';
                document.getElementById('deleteError').style.display = 'none';
            } else {
                deleteBtn.disabled = true;
                deleteBtn.style.opacity = '0.5';
                deleteBtn.style.cursor = 'not-allowed';
                deleteBtn.style.transform = 'scale(1)';
                deleteBtn.style.boxShadow = 'none';
            }
        });
    }
});

function approvePreProject(id, name, status) {
    // Store ID and name for approval
    window.approvePreProjectId = id;
    window.approvePreProjectName = name;
    
    // Update modal content
    document.getElementById('approveProjectName').textContent = name;
    document.getElementById('approveForm').action = '/pages/pre-project/' + id + '/approve';
    document.getElementById('approval_remarks').value = '';
    
    // Show modal
    document.getElementById('approveModal').classList.add('show');
    
    // Focus on textarea
    setTimeout(() => {
        document.getElementById('approval_remarks').focus();
    }, 100);
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.remove('show');
    document.getElementById('approval_remarks').value = '';
    window.approvePreProjectId = null;
}

function rejectPreProject(id, name) {
    // Store ID and name for rejection
    window.rejectPreProjectId = id;
    window.rejectPreProjectName = name;
    
    // Update modal content
    document.getElementById('rejectProjectName').textContent = name;
    document.getElementById('rejectForm').action = '/pages/pre-project/' + id + '/reject';
    document.getElementById('rejection_remarks').value = '';
    document.getElementById('rejectError').style.display = 'none';
    document.getElementById('rejectSubmitBtn').disabled = true;
    document.getElementById('rejectSubmitBtn').style.opacity = '0.5';
    
    // Show modal
    document.getElementById('rejectModal').classList.add('show');
    
    // Focus on textarea
    setTimeout(() => {
        document.getElementById('rejection_remarks').focus();
    }, 100);
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('show');
    document.getElementById('rejection_remarks').value = '';
    document.getElementById('rejectError').style.display = 'none';
}

function validateRejectForm() {
    const remarks = document.getElementById('rejection_remarks').value.trim();
    const submitBtn = document.getElementById('rejectSubmitBtn');
    
    if (remarks.length < 10) {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
        return false;
    }
    
    submitBtn.disabled = false;
    submitBtn.style.opacity = '1';
    submitBtn.style.cursor = 'pointer';
    return true;
}

function viewPreProject(id) {
    // Store the current ID for printing
    currentPreProjectId = id;
    
    // Fetch pre-project data via AJAX with cache busting
    const timestamp = new Date().getTime();
    fetch('/pages/pre-project/' + id + '/edit?_=' + timestamp, {
        cache: 'no-cache',
        headers: {
            'Cache-Control': 'no-cache',
            'Pragma': 'no-cache'
        }
    })
        .then(response => response.json())
        .then(data => {
            console.log('Pre-project data:', data); // Debug
            
            // Basic Information
            document.getElementById('view_name').textContent = data.name || '-';
            document.getElementById('view_residen').textContent = data.residen_category ? data.residen_category.name : '-';
            document.getElementById('view_agency').textContent = data.agency_category ? data.agency_category.name : '-';
            
            // Parliament/DUN - use correct relationship names
            let parliamentDun = '-';
            if (data.parliament) {
                parliamentDun = data.parliament.name;
            } else if (data.dun_basic) {
                parliamentDun = data.dun_basic.name;
            }
            document.getElementById('view_parliament_dun').textContent = parliamentDun;
            
            document.getElementById('view_project_category').textContent = data.project_category ? data.project_category.name : '-';
            document.getElementById('view_project_scope').textContent = data.project_scope || '-';
            
            // Cost of Project - with thousand separator
            document.getElementById('view_actual_cost').textContent = data.actual_project_cost ? 'RM ' + parseFloat(data.actual_project_cost).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'RM 0.00';
            document.getElementById('view_consultation_cost').textContent = data.consultation_cost ? 'RM ' + parseFloat(data.consultation_cost).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'RM 0.00';
            document.getElementById('view_lss_cost').textContent = data.lss_inspection_cost ? 'RM ' + parseFloat(data.lss_inspection_cost).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'RM 0.00';
            document.getElementById('view_sst').textContent = data.sst ? 'RM ' + parseFloat(data.sst).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'RM 0.00';
            document.getElementById('view_others').textContent = data.others_cost ? 'RM ' + parseFloat(data.others_cost).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'RM 0.00';
            document.getElementById('view_total_cost').textContent = data.total_cost ? 'RM ' + parseFloat(data.total_cost).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'RM 0.00';
            
            // Project Location
            document.getElementById('view_implementation_period').textContent = data.implementation_period || '-';
            document.getElementById('view_division').textContent = data.division ? data.division.name : '-';
            document.getElementById('view_district').textContent = data.district ? data.district.name : '-';
            document.getElementById('view_parliament_location').textContent = data.parliament_location ? data.parliament_location.name : '-';
            document.getElementById('view_dun').textContent = data.dun ? data.dun.name : '-';
            
            // Site Information
            document.getElementById('view_site_layout').textContent = data.site_layout || '-';
            document.getElementById('view_consultation_service').textContent = data.consultation_service || '-';
            document.getElementById('view_land_title_status').textContent = data.land_title_status ? data.land_title_status.name : '-';
            
            // Implementation Details
            document.getElementById('view_implementing_agency').textContent = data.implementing_agency ? data.implementing_agency.name : '-';
            document.getElementById('view_implementation_method').textContent = data.implementation_method ? data.implementation_method.name : '-';
            document.getElementById('view_project_ownership').textContent = data.project_ownership ? data.project_ownership.name : '-';
            document.getElementById('view_jkkk_name').textContent = data.jkkk_name || '-';
            document.getElementById('view_state_government_asset').textContent = data.state_government_asset || '-';
            document.getElementById('view_bill_of_quantity').textContent = data.bill_of_quantity || '-';
            
            // Handle attachment
            const attachmentRow = document.getElementById('view_attachment_row');
            if (data.bill_of_quantity_attachment) {
                attachmentRow.style.display = 'grid';
                document.getElementById('view_attachment_link').href = '/storage/' + data.bill_of_quantity_attachment;
                document.getElementById('view_attachment_link').textContent = data.bill_of_quantity_attachment.split('/').pop();
            } else {
                attachmentRow.style.display = 'none';
            }
            
            // Handle Approval History
            const approvalHistorySection = document.getElementById('approval_history_section');
            const approvalHistoryContent = document.getElementById('approval_history_content');
            
            let hasApprovalHistory = false;
            let approvalHtml = '';
            
            // Submitted to EPU
            if (data.submitted_to_epu_at) {
                hasApprovalHistory = true;
                approvalHtml += '<div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px; margin-bottom: 10px;">';
                approvalHtml += '<div style="padding-left: 10px; border-left: 3px solid #17a2b8;">';
                approvalHtml += '<div style="font-weight: 500; color: #17a2b8; font-size: 11px; margin-bottom: 5px;">Submitted to EPU</div>';
                approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                approvalHtml += '<div style="color: #666666; font-size: 11px;">Submitted By:</div>';
                approvalHtml += '<div style="color: #333333; font-size: 11px;">' + (data.submitted_to_epu_by ? (data.submitted_to_epu_by.full_name || data.submitted_to_epu_by.username || data.submitted_to_epu_by.email || '-') : '-') + '</div>';
                approvalHtml += '</div>';
                approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px;">';
                approvalHtml += '<div style="color: #666666; font-size: 11px;">Date:</div>';
                approvalHtml += '<div style="color: #333333; font-size: 11px;">' + new Date(data.submitted_to_epu_at).toLocaleDateString('en-GB') + '</div>';
                approvalHtml += '</div>';
                approvalHtml += '</div>';
                approvalHtml += '</div>';
            }
            
            // First Approval
            if (data.first_approved_at) {
                hasApprovalHistory = true;
                approvalHtml += '<div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px; margin-bottom: 10px;">';
                approvalHtml += '<div style="padding-left: 10px; border-left: 3px solid #28a745;">';
                approvalHtml += '<div style="font-weight: 500; color: #28a745; font-size: 11px; margin-bottom: 5px;">First Approval</div>';
                approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                approvalHtml += '<div style="color: #666666; font-size: 11px;">Approved By:</div>';
                approvalHtml += '<div style="color: #333333; font-size: 11px;">' + (data.first_approver ? (data.first_approver.full_name || data.first_approver.username || data.first_approver.email || '-') : '-') + '</div>';
                approvalHtml += '</div>';
                approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                approvalHtml += '<div style="color: #666666; font-size: 11px;">Date:</div>';
                approvalHtml += '<div style="color: #333333; font-size: 11px;">' + new Date(data.first_approved_at).toLocaleDateString('en-GB') + '</div>';
                approvalHtml += '</div>';
                if (data.first_approval_remarks) {
                    approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px;">';
                    approvalHtml += '<div style="color: #666666; font-size: 11px;">Remarks:</div>';
                    approvalHtml += '<div style="color: #333333; font-size: 11px;">' + data.first_approval_remarks + '</div>';
                    approvalHtml += '</div>';
                }
                approvalHtml += '</div>';
                approvalHtml += '</div>';
            }
            
            // Second Approval
            if (data.second_approved_at) {
                hasApprovalHistory = true;
                approvalHtml += '<div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px; margin-bottom: 10px;">';
                approvalHtml += '<div style="padding-left: 10px; border-left: 3px solid #007bff;">';
                approvalHtml += '<div style="font-weight: 500; color: #007bff; font-size: 11px; margin-bottom: 5px;">Second Approval</div>';
                approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                approvalHtml += '<div style="color: #666666; font-size: 11px;">Approved By:</div>';
                approvalHtml += '<div style="color: #333333; font-size: 11px;">' + (data.second_approver ? (data.second_approver.full_name || data.second_approver.username || data.second_approver.email || '-') : '-') + '</div>';
                approvalHtml += '</div>';
                approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                approvalHtml += '<div style="color: #666666; font-size: 11px;">Date:</div>';
                approvalHtml += '<div style="color: #333333; font-size: 11px;">' + new Date(data.second_approved_at).toLocaleDateString('en-GB') + '</div>';
                approvalHtml += '</div>';
                if (data.second_approval_remarks) {
                    approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px;">';
                    approvalHtml += '<div style="color: #666666; font-size: 11px;">Remarks:</div>';
                    approvalHtml += '<div style="color: #333333; font-size: 11px;">' + data.second_approval_remarks + '</div>';
                    approvalHtml += '</div>';
                }
                approvalHtml += '</div>';
                approvalHtml += '</div>';
            }
            
            // Rejection
            if (data.rejected_at) {
                hasApprovalHistory = true;
                approvalHtml += '<div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px; margin-bottom: 10px;">';
                approvalHtml += '<div style="padding-left: 10px; border-left: 3px solid #dc3545;">';
                approvalHtml += '<div style="font-weight: 500; color: #dc3545; font-size: 11px; margin-bottom: 5px;">Rejected</div>';
                approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                approvalHtml += '<div style="color: #666666; font-size: 11px;">Rejected By:</div>';
                approvalHtml += '<div style="color: #333333; font-size: 11px;">' + (data.rejected_by ? (data.rejected_by.full_name || data.rejected_by.username || data.rejected_by.email || '-') : '-') + '</div>';
                approvalHtml += '</div>';
                approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                approvalHtml += '<div style="color: #666666; font-size: 11px;">Date:</div>';
                approvalHtml += '<div style="color: #333333; font-size: 11px;">' + new Date(data.rejected_at).toLocaleDateString('en-GB') + '</div>';
                approvalHtml += '</div>';
                if (data.rejection_remarks) {
                    approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px;">';
                    approvalHtml += '<div style="color: #666666; font-size: 11px;">Remarks:</div>';
                    approvalHtml += '<div style="color: #333333; font-size: 11px;">' + data.rejection_remarks + '</div>';
                    approvalHtml += '</div>';
                }
                approvalHtml += '</div>';
                approvalHtml += '</div>';
            }
            
            if (hasApprovalHistory) {
                approvalHistoryContent.innerHTML = approvalHtml;
                approvalHistorySection.style.display = 'block';
            } else {
                approvalHistorySection.style.display = 'none';
            }
            
            // Handle Project Changes (from NOC)
            const projectChangesSection = document.getElementById('project_changes_section');
            const projectChangesContent = document.getElementById('project_changes_content');
            
            if (data.noc_changes && data.noc_changes.length > 0) {
                let changesHtml = '<div style="overflow-x: auto;"><table style="width: 100%; border-collapse: collapse; font-size: 11px;">';
                changesHtml += '<thead><tr style="background-color: #f8f9fa;">';
                changesHtml += '<th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">NOC Number</th>';
                changesHtml += '<th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Tahun RTP</th>';
                changesHtml += '<th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">No Projek</th>';
                changesHtml += '<th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Nama Projek Asal</th>';
                changesHtml += '<th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Nama Projek Baru</th>';
                changesHtml += '<th style="padding: 10px 8px; text-align: right; border: 1px solid #dee2e6; font-weight: 600;">Kos Asal (RM)</th>';
                changesHtml += '<th style="padding: 10px 8px; text-align: right; border: 1px solid #dee2e6; font-weight: 600;">Kos Baru (RM)</th>';
                changesHtml += '<th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Agensi Asal</th>';
                changesHtml += '<th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Agensi Baru</th>';
                changesHtml += '<th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Catatan</th>';
                changesHtml += '</tr></thead><tbody>';
                
                data.noc_changes.forEach(change => {
                    changesHtml += '<tr>';
                    changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6;">' + (change.noc_number || '-') + '</td>';
                    changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6;">' + (change.tahun_rtp || '-') + '</td>';
                    changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6;">' + (change.no_projek || '-') + '</td>';
                    changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6;">' + (change.nama_projek_asal || '-') + '</td>';
                    
                    // Nama Projek Baru - highlight if changed
                    if (change.nama_projek_baru) {
                        changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6;"><span style="color: #007bff; font-weight: 600;">' + change.nama_projek_baru + '</span></td>';
                    } else {
                        changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6;"><span style="color: #999;">No change</span></td>';
                    }
                    
                    // Kos Asal
                    const kosAsal = change.kos_asal ? parseFloat(change.kos_asal).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00';
                    changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6; text-align: right;">' + kosAsal + '</td>';
                    
                    // Kos Baru - highlight if changed
                    if (change.kos_baru) {
                        const kosBaru = parseFloat(change.kos_baru).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                        changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6; text-align: right;"><span style="color: #007bff; font-weight: 600;">' + kosBaru + '</span></td>';
                    } else {
                        changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6; text-align: right;"><span style="color: #999;">No change</span></td>';
                    }
                    
                    // Agensi Asal
                    changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6;">' + (change.agensi_pelaksana_asal || '-') + '</td>';
                    
                    // Agensi Baru - highlight if changed
                    if (change.agensi_pelaksana_baru) {
                        changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6;"><span style="color: #007bff; font-weight: 600;">' + change.agensi_pelaksana_baru + '</span></td>';
                    } else {
                        changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6;"><span style="color: #999;">No change</span></td>';
                    }
                    
                    // Catatan
                    changesHtml += '<td style="padding: 8px; border: 1px solid #dee2e6;">' + (change.noc_note_name || '-') + '</td>';
                    changesHtml += '</tr>';
                });
                
                changesHtml += '</tbody></table></div>';
                projectChangesContent.innerHTML = changesHtml;
                projectChangesSection.style.display = 'block';
            } else {
                projectChangesSection.style.display = 'none';
            }
            
            // Handle NOC Attachments
            const nocAttachmentsSection = document.getElementById('noc_attachments_section');
            const nocAttachmentsContent = document.getElementById('noc_attachments_content');
            
            if (data.nocs && data.nocs.length > 0) {
                let attachmentsHtml = '';
                data.nocs.forEach(noc => {
                    attachmentsHtml += '<div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px; margin-bottom: 10px;">';
                    attachmentsHtml += '<div style="font-weight: 600; margin-bottom: 10px; color: #333333; font-size: 12px;">NOC: ' + noc.noc_number + '</div>';
                    attachmentsHtml += '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">';
                    
                    // NOC Letter Attachment
                    attachmentsHtml += '<div>';
                    attachmentsHtml += '<div style="font-size: 11px; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">NOC Letter</div>';
                    if (noc.noc_letter_attachment) {
                        const letterFileName = noc.noc_letter_attachment.split('/').pop();
                        attachmentsHtml += '<a href="/storage/' + noc.noc_letter_attachment + '" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #007bff; font-size: 11px;">';
                        attachmentsHtml += '<span class="material-symbols-outlined" style="font-size: 16px;">description</span>';
                        attachmentsHtml += letterFileName;
                        attachmentsHtml += '</a>';
                    } else {
                        attachmentsHtml += '<div style="padding: 8px 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; color: #999; font-size: 11px;">No attachment</div>';
                    }
                    attachmentsHtml += '</div>';
                    
                    // NOC Project List Attachment
                    attachmentsHtml += '<div>';
                    attachmentsHtml += '<div style="font-size: 11px; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">NOC Project List</div>';
                    if (noc.noc_project_list_attachment) {
                        const listFileName = noc.noc_project_list_attachment.split('/').pop();
                        attachmentsHtml += '<a href="/storage/' + noc.noc_project_list_attachment + '" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #007bff; font-size: 11px;">';
                        attachmentsHtml += '<span class="material-symbols-outlined" style="font-size: 16px;">description</span>';
                        attachmentsHtml += listFileName;
                        attachmentsHtml += '</a>';
                    } else {
                        attachmentsHtml += '<div style="padding: 8px 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; color: #999; font-size: 11px;">No attachment</div>';
                    }
                    attachmentsHtml += '</div>';
                    
                    attachmentsHtml += '</div>'; // Close grid
                    attachmentsHtml += '</div>'; // Close main div
                });
                nocAttachmentsContent.innerHTML = attachmentsHtml;
                nocAttachmentsSection.style.display = 'block';
            } else {
                nocAttachmentsSection.style.display = 'none';
            }
            
            document.getElementById('viewModal').classList.add('show');
        })
        .catch(error => {
            console.error('Error fetching pre-project data:', error);
            alert('Failed to load pre-project data');
        });
}

function closeViewModal() {
    document.getElementById('viewModal').classList.remove('show');
}

// Missing Fields Modal Functions
function showMissingFieldsModal(missingFields) {
    const modal = document.getElementById('missingFieldsModal');
    const list = document.getElementById('missingFieldsList');
    
    // Clear existing list
    list.innerHTML = '';
    
    // Populate missing fields
    missingFields.forEach(field => {
        const li = document.createElement('li');
        li.textContent = field;
        list.appendChild(li);
    });
    
    modal.classList.add('show');
}

function closeMissingFieldsModal() {
    document.getElementById('missingFieldsModal').classList.remove('show');
}

let currentPreProjectId = null;

function printPreProject() {
    if (currentPreProjectId) {
        window.open('/pages/pre-project/' + currentPreProjectId + '/print', '_blank');
    }
}

document.getElementById('preProjectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeViewModal();
    }
});

document.getElementById('missingFieldsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMissingFieldsModal();
    }
});

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

document.getElementById('approveModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApproveModal();
    }
});

// Show missing fields modal if validation fails
@if(session('missing_fields'))
    document.addEventListener('DOMContentLoaded', function() {
        showMissingFieldsModal(@json(session('missing_fields')));
    });
@endif

</script>
@endpush
