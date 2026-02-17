@extends('layouts.app')

@section('title', 'Project Cancel - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project Cancel</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-project-tabs active="cancel" />
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            <x-data-table
                title="Project Cancel"
                description="Manage cancelled projects."
                createButtonText=""
                createButtonRoute="#"
                searchPlaceholder="Search cancelled projects..."
                :columns="['Project Number', 'Year', 'Project Name', 'Parliament/DUN', 'Total Cost (RM)', 'Approval Date', 'Status', 'Actions']"
                :data="$cancelledProjects"
                :rowsPerPage="10"
            >
                @forelse($cancelledProjects as $project)
                <tr @if($project->status === 'NOC') style="background-color: #fff3cd;" @else style="background-color: #ffe6e6;" @endif>
                    <td>{{ $project->project_number }}</td>
                    <td>{{ $project->year }}</td>
                    <td>{{ $project->name }}</td>
                    <td>
                        @if($project->parliament)
                            {{ $project->parliament->name }}
                        @elseif($project->dun)
                            {{ $project->dun->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ number_format($project->total_cost ?? 0, 2) }}</td>
                    <td>{{ $project->approval_date ? \Carbon\Carbon::parse($project->approval_date)->format('d/m/Y') : '-' }}</td>
                    <td>
                        @if($project->status === 'NOC')
                            <span class="status-badge" style="background-color: #ffc107; color: #856404;">NOC</span>
                        @else
                            <span class="status-badge" style="background-color: #dc3545; color: white;">Projek Dibatalkan</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn action-view" title="View" onclick="viewProject({{ $project->id }})">
                                <span class="material-symbols-outlined">visibility</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">No cancelled projects found</td>
                </tr>
                @endforelse
            </x-data-table>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal-overlay" id="viewModal">
        <div class="modal-container" style="max-width: 900px; max-height: 90vh; overflow-y: auto;">
            <div class="modal-header">
                <h3 class="modal-title">View Project Details</h3>
                <button class="modal-close" onclick="closeViewModal()">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: calc(90vh - 140px); overflow-y: auto;">
                <!-- Basic Information Section -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Basic Information</h4>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Project Number:</div>
                        <div style="color: #333333; font-size: 12px; font-weight: 500;" id="view_project_number"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Project Year:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_project_year"></div>
                    </div>
                    
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
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Approval Date:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_approval_date"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Status:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_status"></div>
                    </div>
                </div>

                <!-- Cost of Project Section -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Cost of Project</h4>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Actual Project Cost:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_actual_project_cost"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Consultation Cost:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_consultation_cost"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">LSS Inspection Cost:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_lss_inspection_cost"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">SST:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_sst"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Others Cost:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_others_cost"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px; font-weight: 600;">Total Cost:</div>
                        <div style="color: #007bff; font-size: 12px; font-weight: 600;" id="view_total_cost"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Implementation Period:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_implementation_period"></div>
                    </div>
                </div>

                <!-- Project Location Section -->
                <div style="margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Project Location</h4>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Division:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_division"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">District:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_district"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Parliament (Location):</div>
                        <div style="color: #333333; font-size: 12px;" id="view_parliament_location"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">DUN (Location):</div>
                        <div style="color: #333333; font-size: 12px;" id="view_dun_location"></div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 10px; margin-bottom: 10px;">
                        <div style="color: #666666; font-size: 12px;">Site Layout:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_site_layout"></div>
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
                        <div style="color: #666666; font-size: 12px;">Consultation Service:</div>
                        <div style="color: #333333; font-size: 12px;" id="view_consultation_service"></div>
                    </div>
                    
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

                <!-- Project Changes Section -->
                <div style="margin-bottom: 20px;" id="project_changes_section">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">Project Changes</h4>
                    
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

                <!-- History Change Section -->
                <div style="margin-bottom: 20px;" id="history_change_section">
                    <h4 style="margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 2px solid #e0e0e0; color: #333333; font-size: 12px; font-weight: 600;">History Change</h4>
                    
                    <!-- Created NOC Sub-section -->
                    <div style="margin-bottom: 15px;">
                        <h5 style="margin: 0 0 10px 0; color: #333333; font-size: 11px; font-weight: 600;">Created NOC</h5>
                        <div id="created_noc_content">
                            <p style="color: #666666; font-size: 12px;">No NOC created</p>
                        </div>
                    </div>

                    <!-- Approval History Sub-section -->
                    <div style="margin-bottom: 15px;">
                        <h5 style="margin: 0 0 10px 0; color: #333333; font-size: 11px; font-weight: 600;">Approval History</h5>
                        <div id="approval_history_content">
                            <p style="color: #666666; font-size: 12px;">No approval history</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeViewModal()">Close</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function viewProject(id) {
    // Show modal
    document.getElementById('viewModal').style.display = 'flex';
    
    // Fetch project data
    fetch(`/pages/project/${id}`)
        .then(response => response.json())
        .then(data => {
            // Basic Information
            document.getElementById('view_project_number').textContent = data.project_number || '-';
            document.getElementById('view_project_year').textContent = data.project_year || '-';
            document.getElementById('view_name').textContent = data.name || '-';
            document.getElementById('view_residen').textContent = data.residen_category?.name || '-';
            document.getElementById('view_agency').textContent = data.agency_category?.name || '-';
            document.getElementById('view_parliament_dun').textContent = data.parliament?.name || data.dun?.name || '-';
            document.getElementById('view_project_category').textContent = data.project_category?.name || '-';
            document.getElementById('view_project_scope').textContent = data.project_scope || '-';
            document.getElementById('view_approval_date').textContent = data.approval_date ? new Date(data.approval_date).toLocaleDateString('en-GB') : '-';
            document.getElementById('view_status').textContent = data.status || '-';
            
            // Cost of Project
            document.getElementById('view_actual_project_cost').textContent = data.actual_project_cost ? 'RM ' + parseFloat(data.actual_project_cost).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';
            document.getElementById('view_consultation_cost').textContent = data.consultation_cost ? 'RM ' + parseFloat(data.consultation_cost).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';
            document.getElementById('view_lss_inspection_cost').textContent = data.lss_inspection_cost ? 'RM ' + parseFloat(data.lss_inspection_cost).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';
            document.getElementById('view_sst').textContent = data.sst ? 'RM ' + parseFloat(data.sst).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';
            document.getElementById('view_others_cost').textContent = data.others_cost ? 'RM ' + parseFloat(data.others_cost).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';
            document.getElementById('view_total_cost').textContent = data.total_cost ? 'RM ' + parseFloat(data.total_cost).toLocaleString('en-MY', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-';
            document.getElementById('view_implementation_period').textContent = data.implementation_period || '-';
            
            // Project Location
            document.getElementById('view_division').textContent = data.division?.name || '-';
            document.getElementById('view_district').textContent = data.district?.name || '-';
            document.getElementById('view_parliament_location').textContent = data.parliament_location?.name || '-';
            document.getElementById('view_dun_location').textContent = data.dun?.name || '-';
            document.getElementById('view_site_layout').textContent = data.site_layout || '-';
            document.getElementById('view_land_title_status').textContent = data.land_title_status?.name || '-';
            
            // Implementation Details
            document.getElementById('view_consultation_service').textContent = data.consultation_service || '-';
            document.getElementById('view_implementing_agency').textContent = data.implementing_agency?.name || '-';
            document.getElementById('view_implementation_method').textContent = data.implementation_method?.name || '-';
            document.getElementById('view_project_ownership').textContent = data.project_ownership?.name || '-';
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
            
            // Handle Project Changes
            const projectChangesSection = document.getElementById('project_changes_section');
            const projectChangesContent = document.getElementById('project_changes_content');
            
            if (data.noc_changes && data.noc_changes.length > 0) {
                let changesHtml = '<div style="overflow-x: auto;"><table style="width: 100%; border-collapse: collapse; font-size: 11px;">';
                changesHtml += '<thead><tr style="background-color: #f8f9fa;">';
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
            
            // Handle History Change
            const historyChangeSection = document.getElementById('history_change_section');
            const createdNocContent = document.getElementById('created_noc_content');
            const approvalHistoryContent = document.getElementById('approval_history_content');
            
            if (data.nocs && data.nocs.length > 0) {
                // Created NOC
                let nocHtml = '';
                data.nocs.forEach(noc => {
                    let ribbonColor = '#007bff';
                    let statusColor = '#007bff';
                    if (noc.status === 'Approved') {
                        ribbonColor = '#28a745';
                        statusColor = '#28a745';
                    } else if (noc.status === 'Rejected') {
                        ribbonColor = '#dc3545';
                        statusColor = '#dc3545';
                    } else if (noc.status === 'Draft') {
                        ribbonColor = '#6c757d';
                        statusColor = '#6c757d';
                    }
                    
                    nocHtml += '<div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px; margin-bottom: 10px;">';
                    nocHtml += '<div style="font-weight: 600; margin-bottom: 8px; color: #333333; font-size: 12px;">NOC: ' + noc.noc_number + '</div>';
                    nocHtml += '<div style="padding-left: 10px; border-left: 3px solid ' + ribbonColor + ';">';
                    nocHtml += '<div style="font-weight: 500; color: ' + statusColor + '; font-size: 11px; margin-bottom: 5px;">' + noc.status + '</div>';
                    nocHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                    nocHtml += '<div style="color: #666666; font-size: 11px;">Created Date:</div>';
                    nocHtml += '<div style="color: #333333; font-size: 11px;">' + new Date(noc.created_at).toLocaleDateString('en-GB') + '</div>';
                    nocHtml += '</div>';
                    nocHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px;">';
                    nocHtml += '<div style="color: #666666; font-size: 11px;">Created By:</div>';
                    nocHtml += '<div style="color: #333333; font-size: 11px;">';
                    if (noc.creator) {
                        const creatorName = noc.creator.full_name || noc.creator.username || noc.creator.email || '-';
                        const creatorLocation = noc.creator.parliament ? noc.creator.parliament.name : (noc.creator.dun ? noc.creator.dun.name : '');
                        nocHtml += creatorName + (creatorLocation ? ' (' + creatorLocation + ')' : '');
                    } else {
                        nocHtml += '-';
                    }
                    nocHtml += '</div>';
                    nocHtml += '</div>';
                    nocHtml += '</div>';
                    nocHtml += '</div>';
                });
                createdNocContent.innerHTML = nocHtml;
                
                // Approval History
                let approvalHtml = '';
                data.nocs.forEach(noc => {
                    if (noc.first_approved_at || noc.second_approved_at) {
                        approvalHtml += '<div style="padding: 10px; background-color: #f9f9f9; border-radius: 4px; margin-bottom: 10px;">';
                        approvalHtml += '<div style="font-weight: 600; margin-bottom: 8px; color: #333333; font-size: 12px;">NOC: ' + noc.noc_number + '</div>';
                        
                        if (noc.first_approved_at) {
                            approvalHtml += '<div style="margin-bottom: 10px; padding-left: 10px; border-left: 3px solid #28a745;">';
                            approvalHtml += '<div style="font-weight: 500; color: #28a745; font-size: 11px; margin-bottom: 5px;">First Approval</div>';
                            approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                            approvalHtml += '<div style="color: #666666; font-size: 11px;">Approved By:</div>';
                            approvalHtml += '<div style="color: #333333; font-size: 11px;">' + (noc.first_approver ? (noc.first_approver.full_name || noc.first_approver.username || noc.first_approver.email || '-') : '-') + '</div>';
                            approvalHtml += '</div>';
                            approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                            approvalHtml += '<div style="color: #666666; font-size: 11px;">Date:</div>';
                            approvalHtml += '<div style="color: #333333; font-size: 11px;">' + new Date(noc.first_approved_at).toLocaleDateString('en-GB') + '</div>';
                            approvalHtml += '</div>';
                            if (noc.first_approval_remarks) {
                                approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px;">';
                                approvalHtml += '<div style="color: #666666; font-size: 11px;">Remarks:</div>';
                                approvalHtml += '<div style="color: #333333; font-size: 11px;">' + noc.first_approval_remarks + '</div>';
                                approvalHtml += '</div>';
                            }
                            approvalHtml += '</div>';
                        }
                        
                        if (noc.second_approved_at) {
                            approvalHtml += '<div style="padding-left: 10px; border-left: 3px solid #007bff;">';
                            approvalHtml += '<div style="font-weight: 500; color: #007bff; font-size: 11px; margin-bottom: 5px;">Second Approval</div>';
                            approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                            approvalHtml += '<div style="color: #666666; font-size: 11px;">Approved By:</div>';
                            approvalHtml += '<div style="color: #333333; font-size: 11px;">' + (noc.second_approver ? (noc.second_approver.full_name || noc.second_approver.username || noc.second_approver.email || '-') : '-') + '</div>';
                            approvalHtml += '</div>';
                            approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px; margin-bottom: 3px;">';
                            approvalHtml += '<div style="color: #666666; font-size: 11px;">Date:</div>';
                            approvalHtml += '<div style="color: #333333; font-size: 11px;">' + new Date(noc.second_approved_at).toLocaleDateString('en-GB') + '</div>';
                            approvalHtml += '</div>';
                            if (noc.second_approval_remarks) {
                                approvalHtml += '<div style="display: grid; grid-template-columns: 100px 1fr; gap: 8px;">';
                                approvalHtml += '<div style="color: #666666; font-size: 11px;">Remarks:</div>';
                                approvalHtml += '<div style="color: #333333; font-size: 11px;">' + noc.second_approval_remarks + '</div>';
                                approvalHtml += '</div>';
                            }
                            approvalHtml += '</div>';
                        }
                        
                        approvalHtml += '</div>';
                    }
                });
                
                if (approvalHtml) {
                    approvalHistoryContent.innerHTML = approvalHtml;
                } else {
                    approvalHistoryContent.innerHTML = '<p style="color: #666666; font-size: 12px;">No approval history</p>';
                }
                
                historyChangeSection.style.display = 'block';
            } else {
                historyChangeSection.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading project details');
            closeViewModal();
        });
}

function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const viewModal = document.getElementById('viewModal');
    if (event.target === viewModal) {
        closeViewModal();
    }
});
</script>
@endpush
