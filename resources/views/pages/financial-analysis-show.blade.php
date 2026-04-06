@extends('layouts.app')

@section('title', 'Financial Analysis Detail - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <a href="{{ route('pages.financial-analysis') }}" style="color: inherit; text-decoration: none;">Financial Analysis</a>
    <span class="breadcrumb-separator">›</span>
    <span>Detail</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-content">
            @if(session('success'))
            <div id="successMessage" style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('error') }}
            </div>
            @endif

            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <!-- Header with Actions -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 14px; font-weight: 600;">Financial Analysis Detail</h2>
                    <div style="display: flex; gap: 10px;">
                        @if($analysis->status === 'Draft' && auth()->user()->agency_category_id)
                        <button onclick="window.location.href='{{ route('pages.financial-analysis.edit', $analysis->id) }}'" class="btn btn-primary">
                            <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                            Edit
                        </button>
                        <form method="POST" action="{{ route('pages.financial-analysis.submit', $analysis->id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="background-color: #6c757d;" onclick="return confirm('Submit this analysis for approval?')">
                                <span class="material-symbols-outlined" style="font-size: 16px;">send</span>
                                Submit for Approval
                            </button>
                        </form>
                        @endif

                        @if($analysis->status === 'Submitted' && auth()->user()->residen_category_id)
                        <form method="POST" action="{{ route('pages.financial-analysis.approve', $analysis->id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="background-color: #28a745;" onclick="return confirm('Approve this financial analysis?')">
                                <span class="material-symbols-outlined" style="font-size: 16px;">check_circle</span>
                                Approve
                            </button>
                        </form>
                        <button onclick="showRejectModal()" class="btn btn-danger">
                            <span class="material-symbols-outlined" style="font-size: 16px;">cancel</span>
                            Reject
                        </button>
                        @endif

                        <button onclick="window.location.href='{{ route('pages.financial-analysis.export-excel', $analysis->id) }}'" class="btn btn-primary" style="background-color: #28a745;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">download</span>
                            Export to Excel
                        </button>

                        <button onclick="window.location.href='{{ route('pages.financial-analysis.export-pdf', $analysis->id) }}'" class="btn btn-primary" style="background-color: #dc3545;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">picture_as_pdf</span>
                            Export to PDF
                        </button>

                        <button onclick="window.location.href='{{ route('pages.financial-analysis') }}'" class="btn btn-secondary">
                            <span class="material-symbols-outlined" style="font-size: 16px;">list</span>
                            Back to List
                        </button>
                    </div>
                </div>

                <!-- Status Badge -->
                <div style="margin-bottom: 20px;">
                    @if($analysis->status === 'Draft')
                        <span class="status-badge" style="background-color: #f5f5f5; color: #666;">Draft</span>
                    @elseif($analysis->status === 'Submitted')
                        <span class="status-badge" style="background-color: #fff3cd; color: #856404;">Submitted</span>
                    @elseif($analysis->status === 'Approved')
                        <span class="status-badge status-active">Approved</span>
                    @elseif($analysis->status === 'Rejected')
                        <span class="status-badge" style="background-color: #f8d7da; color: #721c24;">Rejected</span>
                    @endif
                </div>

                <!-- Project Information -->
                <div style="margin-bottom: 30px;">
                    <h3 style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #333;">Project Information</h3>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #e0e0e0;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Transfer Number</label>
                                <div style="font-size: 12px; color: #333;">{{ $analysis->transfer->transfer_number }}</div>
                            </div>
                            <div>
                                <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Project Number</label>
                                <div style="font-size: 12px; color: #333;">{{ $analysis->project_number ?? '-' }}</div>
                            </div>
                            <div>
                                <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Project Name</label>
                                <div style="font-size: 12px; color: #333;">{{ $analysis->project_name ?? '-' }}</div>
                            </div>
                            <div>
                                <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Agency</label>
                                <div style="font-size: 12px; color: #333;">{{ $analysis->agency_name ?? '-' }}</div>
                            </div>
                            <div>
                                <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Department Budget</label>
                                <div style="font-size: 12px; color: #333;">RM {{ number_format($analysis->department_budget ?? 0, 2) }}</div>
                            </div>
                            <div>
                                <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">District</label>
                                <div style="font-size: 12px; color: #333;">{{ $analysis->district_name ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contractor Summary -->
                <div style="margin-bottom: 30px;">
                    <h3 style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #333;">Contractor Summary</h3>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                        <div style="background: #e3f2fd; padding: 15px; border-radius: 4px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 600; color: #1976d2;">{{ $analysis->getContractorCount() }}</div>
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">Total Contractors</div>
                        </div>
                        <div style="background: #e8f5e9; padding: 15px; border-radius: 4px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 600; color: #388e3c;">{{ $analysis->getQualifiedCount() }}</div>
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">Qualified</div>
                        </div>
                        <div style="background: #ffebee; padding: 15px; border-radius: 4px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 600; color: #d32f2f;">{{ $analysis->getNotQualifiedCount() }}</div>
                            <div style="font-size: 11px; color: #666; margin-top: 5px;">Not Qualified</div>
                        </div>
                    </div>
                </div>

                <!-- Contractor Evaluations -->
                <div style="margin-bottom: 30px;">
                    <h3 style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #333;">Contractor Evaluations</h3>
                    
                    @foreach($analysis->contractors as $contractor)
                    <div style="background: {{ $contractor->is_qualified === true ? '#e8f5e9' : ($contractor->is_qualified === false ? '#ffebee' : '#f8f9fa') }}; padding: 20px; border-radius: 4px; border: 1px solid #e0e0e0; margin-bottom: 15px;">
                        <!-- Contractor Header -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="font-size: 12px; font-weight: 600; margin: 0; color: #007bff;">
                                {{ $contractor->contractor_name }}
                            </h4>
                            @if($contractor->is_qualified === true)
                                <span class="status-badge status-active">Qualified</span>
                            @elseif($contractor->is_qualified === false)
                                <span class="status-badge" style="background-color: #f8d7da; color: #721c24;">Not Qualified</span>
                            @endif
                        </div>

                        <!-- Basic Info -->
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px; font-size: 11px;">
                            <div>
                                <span style="color: #666;">Registration:</span>
                                <span style="color: #333;">{{ $contractor->registration_number }}</span>
                            </div>
                            <div>
                                <span style="color: #666;">Class:</span>
                                <span style="color: #333;">{{ $contractor->contractor_class }}</span>
                            </div>
                            <div>
                                <span style="color: #666;">Validity:</span>
                                <span style="color: {{ $contractor->isRegistrationExpired() ? '#dc3545' : '#333' }};">
                                    {{ $contractor->registration_validity_date?->format('d/m/Y') ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <!-- Financial Data -->
                        <div style="background: white; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 11px;">
                                <div>
                                    <span style="color: #666;">Current Contract Load:</span>
                                    <span style="color: #333; font-weight: 600;">RM {{ number_format($contractor->current_contract_load ?? 0, 2) }}</span>
                                </div>
                                <div>
                                    <span style="color: #666;">Minimum Capital:</span>
                                    <span style="color: #333; font-weight: 600;">RM {{ number_format($contractor->minimum_capital_requirement ?? 0, 2) }}</span>
                                </div>
                                <div>
                                    <span style="color: #666;">Fixed Deposit:</span>
                                    <span style="color: #333; font-weight: 600;">RM {{ number_format($contractor->fixed_deposit ?? 0, 2) }}</span>
                                </div>
                                <div>
                                    <span style="color: #666;">Credit Facility:</span>
                                    <span style="color: #333; font-weight: 600;">RM {{ number_format($contractor->credit_facility_balance ?? 0, 2) }}</span>
                                </div>
                                <div>
                                    <span style="color: #666;">3-Month Average:</span>
                                    <span style="color: #007bff; font-weight: 600;">RM {{ number_format($contractor->three_month_average ?? 0, 2) }}</span>
                                </div>
                                <div>
                                    <span style="color: #666;">Total Capacity:</span>
                                    <span style="color: #28a745; font-weight: 600;">RM {{ number_format($contractor->getTotalFinancialCapacity(), 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bank Statements -->
                        @if($contractor->bankStatements->count() > 0)
                        <div style="background: white; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                            <div style="font-size: 11px; font-weight: 600; margin-bottom: 10px; color: #666;">Bank Statements:</div>
                            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; font-size: 11px;">
                                @foreach($contractor->bankStatements as $statement)
                                <div>
                                    <div style="color: #666;">{{ $statement->month_year }}</div>
                                    <div style="color: #333; font-weight: 600;">RM {{ number_format($statement->ending_balance ?? 0, 2) }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Performance & Decision -->
                        @if($contractor->performance_record || $contractor->meeting_decision || $contractor->justification || $contractor->remarks)
                        <div style="background: white; padding: 15px; border-radius: 4px;">
                            @if($contractor->performance_record)
                            <div style="margin-bottom: 10px;">
                                <div style="font-size: 11px; color: #666; margin-bottom: 5px;">Performance Record:</div>
                                <div style="font-size: 12px; color: #333;">{{ $contractor->performance_record }}</div>
                            </div>
                            @endif
                            @if($contractor->meeting_decision)
                            <div style="margin-bottom: 10px;">
                                <div style="font-size: 11px; color: #666; margin-bottom: 5px;">Meeting Decision:</div>
                                <div style="font-size: 12px; color: #333;">{{ $contractor->meeting_decision }}</div>
                            </div>
                            @endif
                            @if($contractor->justification)
                            <div style="margin-bottom: 10px;">
                                <div style="font-size: 11px; color: #666; margin-bottom: 5px;">Justification:</div>
                                <div style="font-size: 12px; color: #333;">{{ $contractor->justification }}</div>
                            </div>
                            @endif
                            @if($contractor->remarks)
                            <div>
                                <div style="font-size: 11px; color: #666; margin-bottom: 5px;">Remarks:</div>
                                <div style="font-size: 12px; color: #333;">{{ $contractor->remarks }}</div>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <!-- Approval Committee -->
                @if($analysis->approvals->count() > 0)
                <div style="margin-bottom: 30px;">
                    <h3 style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #333;">Approval Committee</h3>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #e0e0e0;">
                        @foreach($analysis->approvals as $approval)
                        <div style="padding: 10px 0; {{ !$loop->last ? 'border-bottom: 1px solid #e0e0e0;' : '' }}">
                            <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $approval->position }}</div>
                            @if($approval->name)
                            <div style="font-size: 11px; color: #666; margin-top: 3px;">{{ $approval->name }}</div>
                            @endif
                            @if($approval->department)
                            <div style="font-size: 11px; color: #666;">{{ $approval->department }}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Audit Trail -->
                <div style="margin-bottom: 30px;">
                    <h3 style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #333;">Audit Trail</h3>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #e0e0e0; font-size: 11px;">
                        <div style="margin-bottom: 10px;">
                            <span style="color: #666;">Created by:</span>
                            <span style="color: #333;">{{ $analysis->creator->full_name }} on {{ $analysis->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @if($analysis->submitted_at)
                        <div style="margin-bottom: 10px;">
                            <span style="color: #666;">Submitted by:</span>
                            <span style="color: #333;">{{ $analysis->submitter->full_name }} on {{ $analysis->submitted_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($analysis->approved_at)
                        <div style="margin-bottom: 10px;">
                            <span style="color: #666;">Approved by:</span>
                            <span style="color: #333;">{{ $analysis->approver->full_name }} on {{ $analysis->approved_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if($analysis->rejected_at)
                        <div style="margin-bottom: 10px;">
                            <span style="color: #666;">Rejected by:</span>
                            <span style="color: #333;">{{ $analysis->rejector->full_name }} on {{ $analysis->rejected_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>
                            <span style="color: #666;">Rejection Remarks:</span>
                            <span style="color: #dc3545;">{{ $analysis->rejection_remarks }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Reject Financial Analysis</h3>
                <button class="modal-close" onclick="closeRejectModal()">&times;</button>
            </div>
            <form method="POST" action="{{ route('pages.financial-analysis.reject', $analysis->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                            Rejection Remarks <span style="color: #dc3545;">*</span>
                        </label>
                        <textarea name="rejection_remarks" rows="4" required
                                  style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px; resize: vertical;"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showRejectModal() {
            document.getElementById('rejectModal').style.display = 'flex';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('rejectModal');
            if (event.target === modal) {
                closeRejectModal();
            }
        }

        // Auto-scroll to success message
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    </script>
@endsection
