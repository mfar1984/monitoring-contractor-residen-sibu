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
            <a href="{{ route('pages.pre-project.noc') }}" class="tab-button">NOC (Notice of Change)</a>
        </div>
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            <x-data-table
                title="Pre-Project"
                description="Manage pre-project data and information."
                createButtonText="Create Pre-Project"
                createButtonRoute="#"
                searchPlaceholder="Search pre-projects..."
                :columns="['Name', 'Agency', 'Parliament', 'Total Cost (RM)', 'Status', 'Actions']"
                :data="$preProjects"
                :rowsPerPage="10"
            >
                @forelse($preProjects as $preProject)
                <tr>
                    <td>{{ $preProject->name }}</td>
                    <td>{{ $preProject->agencyCategory ? $preProject->agencyCategory->name : '-' }}</td>
                    <td>{{ $preProject->parliament ? $preProject->parliament->name : '-' }}</td>
                    <td>{{ number_format($preProject->total_cost ?? 0, 2) }}</td>
                    <td>
                        <span class="status-badge {{ $preProject->status === 'Active' ? 'status-active' : 'status-suspended' }}">
                            {{ $preProject->status }}
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-view" title="View" onclick="viewPreProject({{ $preProject->id }})">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                            <button class="action-btn action-edit" title="Edit" onclick="editPreProject({{ $preProject->id }})">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button class="action-btn action-delete" title="Delete" onclick="deletePreProject({{ $preProject->id }}, '{{ $preProject->name }}')">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
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
    document.getElementById('implementation_period').value = '';
    document.getElementById('division_id').value = '';
    document.getElementById('district_id').value = '';
    document.getElementById('parliament_location_id').value = '';
    document.getElementById('dun_id').value = '';
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
    // Fetch pre-project data via AJAX
    fetch('/pages/pre-project/' + id + '/edit')
        .then(response => response.json())
        .then(data => {
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

function deletePreProject(id, name) {
    document.getElementById('deleteMessage').textContent = 'Are you sure you want to delete pre-project "' + name + '"?';
    document.getElementById('deleteForm').action = '/pages/pre-project/' + id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

function viewPreProject(id) {
    // Store the current ID for printing
    currentPreProjectId = id;
    
    // Fetch pre-project data via AJAX
    fetch('/pages/pre-project/' + id + '/edit')
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
</script>
@endpush
