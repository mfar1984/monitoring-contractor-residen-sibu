@extends('layouts.app')

@section('title', 'Transfer Details - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Contractor Analysis</span>
    <span class="breadcrumb-separator">›</span>
    <span>{{ $transfer->transfer_number }}</span>
@endsection

@section('content')
    <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
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

        <div class="content-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-start;">
            <div class="content-header-left">
                <h3>Transfer Details: {{ $transfer->transfer_number }}</h3>
                <p class="content-description">View contractor analysis transfer details</p>
            </div>
            <div style="display: flex; gap: 10px;">
                @php
                    $financialAnalysis = \App\Models\FinancialAnalysis::where('contractor_analysis_transfer_id', $transfer->id)->first();
                @endphp
                
                @if($financialAnalysis)
                    <a href="{{ route('pages.financial-analysis.show', $financialAnalysis->id) }}" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">assessment</span>
                        View Financial Analysis
                    </a>
                @endif
                
                <a href="{{ route('pages.contractor-analysis') }}" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; text-decoration: none; display: inline-block;">
                    Back to List
                </a>
                @if($transfer->status === 'Draft')
                <button onclick="confirmDelete()" style="padding: 10px 20px; background-color: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
                    Delete
                </button>
                @endif
            </div>
        </div>

        <div style="border-top: 1px solid #e0e0e0; margin-bottom: 24px;"></div>

        <!-- Transfer Information -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
            <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Transfer Information</h4>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div>
                    <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Transfer Number</div>
                    <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $transfer->transfer_number }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Agency</div>
                    <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $transfer->agency?->name ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Created By</div>
                    <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $transfer->creator?->full_name ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Created Date</div>
                    <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $transfer->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Status</div>
                    <div>
                        @if($transfer->status === 'Draft')
                            <span class="status-badge" style="background-color: #f5f5f5; color: #666;">Draft</span>
                        @elseif($transfer->status === 'Submitted')
                            <span class="status-badge" style="background-color: #cce5ff; color: #004085;">Submitted</span>
                        @elseif($transfer->status === 'In Analysis')
                            <span class="status-badge" style="background-color: #fff3cd; color: #856404;">In Analysis</span>
                        @elseif($transfer->status === 'Completed')
                            <span class="status-badge status-active">Completed</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- UPKJ Filter Criteria -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
            <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">UPKJ Filter Criteria</h4>
            
            @if($transfer->hasUpkjFilter())
                <div style="background: white; padding: 16px; border-radius: 4px; border: 1px solid #dee2e6;">
                    <div style="font-size: 12px; color: #333; line-height: 1.6;">
                        {{ $transfer->getFormattedUpkjFilter() }}
                    </div>
                </div>
            @else
                <p style="color: #999; font-size: 12px; margin: 0;">No UPKJ filter applied</p>
            @endif
        </div>

        <!-- Project Information (Single Project) -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
            <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Project Information</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; background: white;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Project Number</th>
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Project Name</th>
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Agency</th>
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Parliament/DUN</th>
                            <th style="padding: 10px 8px; text-align: right; border: 1px solid #dee2e6; font-weight: 600;">Total Cost (RM)</th>
                            <th style="padding: 10px 8px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $project = $transfer->projects->first();
                        @endphp
                        @if($project)
                        <tr>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $project->project_number }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $project->name }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $project->agencyCategory?->name ?? '-' }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $project->parliament?->name ?? $project->dunBasic?->name ?? '-' }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6; text-align: right; font-weight: 600;">{{ number_format($project->total_cost, 2) }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6; text-align: center;">
                                @if($project->status === 'Analysis Pending')
                                    <span class="status-badge" style="background-color: #fff3cd; color: #856404;">Analysis Pending</span>
                                @else
                                    <span class="status-badge status-active">{{ $project->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @else
                        <tr>
                            <td colspan="6" style="padding: 20px; text-align: center; border: 1px solid #dee2e6; color: #999;">No project found</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Contractors Section -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
            <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Selected Contractors</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; background: white;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">No.</th>
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Company Name</th>
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Registration Number</th>
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">UPKJ Classifications</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfer->contractors as $index => $contractor)
                        <tr>
                            <td style="padding: 8px; border: 1px solid #dee2e6; text-align: center;">{{ $index + 1 }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $contractor->company_name }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $contractor->registration_number ?? '-' }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">
                                @if($contractor->upkjRecords->count() > 0)
                                    @foreach($contractor->upkjRecords as $record)
                                        <div style="margin-bottom: 4px;">
                                            <span style="font-weight: 600;">{{ $record->category }}:</span>
                                            {{ $record->getFormattedClassifications() }}
                                        </div>
                                    @endforeach
                                @else
                                    <span style="color: #999;">No UPKJ classifications</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="padding: 20px; text-align: center; border: 1px solid #dee2e6; color: #999;">No contractors selected</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Project Details Accordion (Optional - for detailed view) -->
        @php
            $project = $transfer->projects->first();
        @endphp
        @if($project)
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
            <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Detailed Project Information</h4>
            
            <div class="accordion-item" style="border: 1px solid #dee2e6; border-radius: 4px; background: white;">
                <div class="accordion-header" onclick="toggleAccordion(0)" style="padding: 12px 16px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; background: #ffffff; border-radius: 4px;">
                    <div>
                        <span style="font-weight: 600; font-size: 12px; color: #333;">{{ $project->project_number }}</span>
                        <span style="font-size: 11px; color: #666; margin-left: 10px;">{{ $project->name }}</span>
                    </div>
                    <span class="material-symbols-outlined accordion-icon" id="icon-0" style="font-size: 20px; color: #666; transition: transform 0.3s;">
                        expand_more
                    </span>
                </div>
                
                <div class="accordion-content" id="content-0" style="display: none; padding: 16px; border-top: 1px solid #dee2e6;">

            <!-- Basic Information -->
            <div style="margin-bottom: 20px;">
                <h5 style="margin: 0 0 12px 0; padding-bottom: 8px; border-bottom: 1px solid #dee2e6; color: #333; font-size: 12px; font-weight: 600;">Basic Information</h5>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Project Number:</div>
                    <div style="color: #333; font-size: 11px; font-weight: 500;">{{ $project->project_number }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Project Year:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->project_year }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Project Name:</div>
                    <div style="color: #333; font-size: 11px; font-weight: 500;">{{ $project->name }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Residen:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->residenCategory?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Agency:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->agencyCategory?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Parliament / DUN:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->parliament?->name ?? $project->dunBasic?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Project Category:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->projectCategory?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Project Scope:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->project_scope ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Approval Date:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->approval_date ? $project->approval_date->format('d/m/Y') : '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Status:</div>
                    <div style="color: #333; font-size: 11px;">
                        @if($project->status === 'Analysis Pending')
                            <span class="status-badge" style="background-color: #fff3cd; color: #856404;">Analysis Pending</span>
                        @else
                            <span class="status-badge status-active">{{ $project->status }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Cost of Project -->
            <div style="margin-bottom: 20px;">
                <h5 style="margin: 0 0 12px 0; padding-bottom: 8px; border-bottom: 1px solid #dee2e6; color: #333; font-size: 12px; font-weight: 600;">Cost of Project</h5>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Actual Project Cost:</div>
                    <div style="color: #333; font-size: 11px;">RM {{ number_format($project->actual_project_cost ?? 0, 2) }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Consultation Cost:</div>
                    <div style="color: #333; font-size: 11px;">RM {{ number_format($project->consultation_cost ?? 0, 2) }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">LSS Inspection Cost:</div>
                    <div style="color: #333; font-size: 11px;">RM {{ number_format($project->lss_inspection_cost ?? 0, 2) }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">SST:</div>
                    <div style="color: #333; font-size: 11px;">RM {{ number_format($project->sst ?? 0, 2) }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Others Cost:</div>
                    <div style="color: #333; font-size: 11px;">RM {{ number_format($project->others_cost ?? 0, 2) }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px; font-weight: 600;">Total Cost:</div>
                    <div style="color: #007bff; font-size: 11px; font-weight: 600;">RM {{ number_format($project->total_cost, 2) }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Implementation Period:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->implementation_period ?? '-' }}</div>
                </div>
            </div>

            <!-- Project Location -->
            <div style="margin-bottom: 20px;">
                <h5 style="margin: 0 0 12px 0; padding-bottom: 8px; border-bottom: 1px solid #dee2e6; color: #333; font-size: 12px; font-weight: 600;">Project Location</h5>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Division:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->division?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">District:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->district?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Parliament (Location):</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->parliamentLocation?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">DUN (Location):</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->dun?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Site Layout:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->site_layout ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Land Title Status:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->landTitleStatus?->name ?? '-' }}</div>
                </div>
            </div>

            <!-- Implementation Details -->
            <div style="margin-bottom: 0;">
                <h5 style="margin: 0 0 12px 0; padding-bottom: 8px; border-bottom: 1px solid #dee2e6; color: #333; font-size: 12px; font-weight: 600;">Implementation Details</h5>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Consultation Service:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->consultation_service ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Implementing Agency:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->implementingAgency?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Implementation Method:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->implementationMethod?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Project Ownership:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->projectOwnership?->name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">JKKK Name:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->jkkk_name ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">State Government Asset:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->state_government_asset ?? '-' }}</div>
                </div>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Bill of Quantity:</div>
                    <div style="color: #333; font-size: 11px;">{{ $project->bill_of_quantity ?? '-' }}</div>
                </div>
                
                @if($project->bill_of_quantity_attachment)
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 8px; margin-bottom: 8px;">
                    <div style="color: #666; font-size: 11px;">Attachment:</div>
                    <div style="color: #333; font-size: 11px;">
                        <a href="{{ asset('storage/' . $project->bill_of_quantity_attachment) }}" target="_blank" style="color: #007bff; text-decoration: none;">
                            <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">download</span>
                            Download
                        </a>
                    </div>
                </div>
                @endif
            </div>
                </div>
            </div>
        </div>
        @endif

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this transfer?</p>
                <p style="color: #666; font-size: 11px; margin-top: 10px;">
                    This will rollback all transferred projects to Active status and delete the transfer permanently.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteForm" method="POST" action="{{ route('pages.contractor-analysis.delete', $transfer->id) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'flex';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }

        // Accordion toggle function
        function toggleAccordion(index) {
            const content = document.getElementById('content-' + index);
            const icon = document.getElementById('icon-' + index);
            
            if (content.style.display === 'none' || content.style.display === '') {
                content.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
            } else {
                content.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>
@endsection
