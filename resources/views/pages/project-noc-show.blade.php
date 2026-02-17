@extends('layouts.app')

@section('title', 'NOC Details - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <span>NOC</span>
    <span class="breadcrumb-separator">›</span>
    <span>NOC</span>
    <span class="breadcrumb-separator">›</span>
    <span>{{ $noc->noc_number }}</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-project-tabs active="noc" />
        
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

            <div class="content-header">
                <div class="content-header-left">
                    <h3>NOC Details: {{ $noc->noc_number }}</h3>
                    <p class="content-description">View Notice of Change details and approval status</p>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('pages.project.noc') }}" class="btn btn-secondary" style="text-decoration: none;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">arrow_back</span>
                        Back to List
                    </a>
                    <button onclick="window.open('{{ route('pages.project.noc.print', $noc->id) }}', '_blank')" class="btn" style="background-color: #28a745; color: white;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">print</span>
                        Print
                    </button>
                </div>
            </div>

            <!-- NOC Information -->
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">NOC Information</h4>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div>
                        <div style="font-size: 11px; color: #666; margin-bottom: 4px;">NOC Number</div>
                        <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $noc->noc_number }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Parliament / DUN</div>
                        <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $noc->parliament?->name ?? $noc->dun?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #666; margin-bottom: 4px;">NOC Date</div>
                        <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $noc->noc_date->format('d/m/Y') }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Created By</div>
                        <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $noc->creator?->full_name ?? '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Status</div>
                        <div>
                            @if($noc->status === 'Waiting for Approval 1')
                                <span class="status-badge" style="background-color: #fff3cd; color: #856404;">Waiting for Approval 1</span>
                            @elseif($noc->status === 'Waiting for Approval 2')
                                <span class="status-badge" style="background-color: #cce5ff; color: #004085;">Waiting for Approval 2</span>
                            @elseif($noc->status === 'Approved')
                                <span class="status-badge status-active">Approved</span>
                            @elseif($noc->status === 'Rejected')
                                <span class="status-badge" style="background-color: #f8d7da; color: #721c24;">Rejected</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Summary -->
            @php
                $totalOriginal = 0;
                $totalNew = 0;
                foreach($projectEntries as $entry) {
                    $kosAsal = $entry->pivot->kos_asal ?? 0;
                    $kosBaru = $entry->pivot->kos_baru ?? 0;
                    $totalOriginal += $kosAsal;
                    $totalNew += $kosBaru > 0 ? $kosBaru : 0;
                }
                $budgetDifference = $totalOriginal - $totalNew;
            @endphp

            <!-- Budget Summary - 3 Boxes Only -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px;">
                <!-- Total NOC Box -->
                <div style="background: white; border: 1px solid #e0e0e0; border-top: 4px solid #007bff; border-radius: 8px; padding: 24px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 40px; color: #007bff; margin-bottom: 12px;">account_balance_wallet</span>
                    <div style="font-size: 11px; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">TOTAL NOC</div>
                    <div style="font-size: 24px; font-weight: 700; color: #333;">RM {{ number_format($totalOriginal, 2) }}</div>
                </div>

                <!-- Total Allocated Box -->
                <div style="background: white; border: 1px solid #e0e0e0; border-top: 4px solid #28a745; border-radius: 8px; padding: 24px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 40px; color: #28a745; margin-bottom: 12px;">payments</span>
                    <div style="font-size: 11px; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">TOTAL ALLOCATED</div>
                    <div style="font-size: 24px; font-weight: 700; color: #333;">RM {{ number_format($totalNew, 2) }}</div>
                </div>

                <!-- Remaining Budget Box -->
                <div style="background: white; border: 1px solid #e0e0e0; border-top: 4px solid #ffc107; border-radius: 8px; padding: 24px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 40px; color: #ffc107; margin-bottom: 12px;">account_balance</span>
                    <div style="font-size: 11px; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">REMAINING BUDGET NOC</div>
                    <div style="font-size: 24px; font-weight: 700; color: {{ $budgetDifference < 0 ? '#dc3545' : '#333' }};">RM {{ number_format($budgetDifference, 2) }}</div>
                </div>
            </div>

            <!-- Projects Table -->
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Project Changes</h4>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                        <thead>
                            <tr style="background-color: #f8f9fa;">
                                <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Tahun RTP</th>
                                <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">No Projek</th>
                                <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Nama Projek Asal</th>
                                <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Nama Projek Baru</th>
                                <th style="padding: 10px 8px; text-align: right; border: 1px solid #dee2e6; font-weight: 600;">Kos Asal (RM)</th>
                                <th style="padding: 10px 8px; text-align: right; border: 1px solid #dee2e6; font-weight: 600;">Kos Baru (RM)</th>
                                <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Agensi Asal</th>
                                <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Agensi Baru</th>
                                <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projectEntries as $entry)
                            <tr>
                                <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $entry->pivot->tahun_rtp ?? '-' }}</td>
                                <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $entry->pivot->no_projek ?? '-' }}</td>
                                <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $entry->pivot->nama_projek_asal ?? '-' }}</td>
                                <td style="padding: 8px; border: 1px solid #dee2e6;">
                                    @if($entry->pivot->nama_projek_baru)
                                        <span style="color: #007bff; font-weight: 600;">{{ $entry->pivot->nama_projek_baru }}</span>
                                    @else
                                        <span style="color: #999;">No change</span>
                                    @endif
                                </td>
                                <td style="padding: 8px; border: 1px solid #dee2e6; text-align: right;">{{ number_format($entry->pivot->kos_asal ?? 0, 2) }}</td>
                                <td style="padding: 8px; border: 1px solid #dee2e6; text-align: right;">
                                    @if($entry->pivot->kos_baru)
                                        <span style="color: #007bff; font-weight: 600;">{{ number_format($entry->pivot->kos_baru, 2) }}</span>
                                    @else
                                        <span style="color: #999;">No change</span>
                                    @endif
                                </td>
                                <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $entry->pivot->agensi_pelaksana_asal ?? '-' }}</td>
                                <td style="padding: 8px; border: 1px solid #dee2e6;">
                                    @if($entry->pivot->agensi_pelaksana_baru)
                                        <span style="color: #007bff; font-weight: 600;">{{ $entry->pivot->agensi_pelaksana_baru }}</span>
                                    @else
                                        <span style="color: #999;">No change</span>
                                    @endif
                                </td>
                                <td style="padding: 8px; border: 1px solid #dee2e6;">
                                    @php
                                        $nocNote = \App\Models\NocNote::find($entry->pivot->noc_note_id);
                                    @endphp
                                    {{ $nocNote?->name ?? '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" style="padding: 20px; text-align: center; border: 1px solid #dee2e6; color: #999;">No projects found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Attachments -->
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Attachments</h4>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- NOC Letter Attachment -->
                    <div>
                        <div style="font-size: 11px; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">NOC Letter</div>
                        @if($noc->noc_letter_attachment)
                            <a href="{{ asset('storage/' . $noc->noc_letter_attachment) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #007bff; font-size: 11px;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">description</span>
                                {{ basename($noc->noc_letter_attachment) }}
                            </a>
                        @else
                            <div style="padding: 8px 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; color: #999; font-size: 11px;">No attachment</div>
                        @endif
                    </div>

                    <!-- NOC Project List Attachment -->
                    <div>
                        <div style="font-size: 11px; color: #666; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">NOC Project List</div>
                        @if($noc->noc_project_list_attachment)
                            <a href="{{ asset('storage/' . $noc->noc_project_list_attachment) }}" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #007bff; font-size: 11px;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">description</span>
                                {{ basename($noc->noc_project_list_attachment) }}
                            </a>
                        @else
                            <div style="padding: 8px 12px; background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; color: #999; font-size: 11px;">No attachment</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Approval History -->
            @if($noc->status !== 'Draft')
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Approval History</h4>
                
                @if($noc->first_approver_id)
                <div style="padding: 12px; background-color: #f8f9fa; border-radius: 4px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <div style="font-size: 12px; font-weight: 600; color: #333;">First Approval</div>
                        <div style="font-size: 11px; color: #666;">{{ $noc->first_approved_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Approved by: {{ $noc->firstApprover?->full_name ?? '-' }}</div>
                    @if($noc->first_approval_remarks)
                    <div style="font-size: 11px; color: #666;">Remarks: {{ $noc->first_approval_remarks }}</div>
                    @endif
                </div>
                @endif

                @if($noc->second_approver_id)
                <div style="padding: 12px; background-color: #f8f9fa; border-radius: 4px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <div style="font-size: 12px; font-weight: 600; color: #333;">Second Approval</div>
                        <div style="font-size: 11px; color: #666;">{{ $noc->second_approved_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Approved by: {{ $noc->secondApprover?->full_name ?? '-' }}</div>
                    @if($noc->second_approval_remarks)
                    <div style="font-size: 11px; color: #666;">Remarks: {{ $noc->second_approval_remarks }}</div>
                    @endif
                </div>
                @else
                <div style="padding: 12px; background-color: #fff3cd; border-radius: 4px; border-left: 4px solid #ffc107;">
                    <div style="font-size: 11px; color: #856404;">Waiting for second approval...</div>
                </div>
                @endif
            </div>
            @endif

            <!-- Action Buttons -->
            @php
                $user = auth()->user();
                $firstApprover = \App\Models\IntegrationSetting::getSetting('application', 'first_approval_user');
                $secondApprover = \App\Models\IntegrationSetting::getSetting('application', 'second_approval_user');
                $canApproveFirst = $noc->status === 'Waiting for Approval 1' && $user->id == $firstApprover;
                $canApproveSecond = $noc->status === 'Waiting for Approval 2' && $user->id == $secondApprover;
            @endphp

            @if($canApproveFirst || $canApproveSecond)
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                @if($canApproveFirst || $canApproveSecond)
                <form method="POST" action="{{ route('pages.project.noc.approve', $noc->id) }}">
                    @csrf
                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #333; margin-bottom: 6px;">Approval Remarks (Optional)</label>
                        <textarea name="remarks" rows="3" style="width: 100%; padding: 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;" placeholder="Enter any remarks..."></textarea>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" class="btn" style="background-color: #28a745; color: white; display: flex; align-items: center; justify-content: center; gap: 6px;" onclick="return confirm('Are you sure you want to approve this NOC?')">
                            <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                            <span>Approve</span>
                        </button>
                        <button type="button" class="btn" style="background-color: #dc3545; color: white; display: flex; align-items: center; justify-content: center; gap: 6px;" onclick="if(confirm('Are you sure you want to reject this NOC?')) { this.form.action = '{{ route('pages.project.noc.reject', $noc->id) }}'; this.form.submit(); }">
                            <span class="material-symbols-outlined" style="font-size: 18px;">cancel</span>
                            <span>Reject</span>
                        </button>
                    </div>
                </form>
                @endif
            </div>
            @endif
        </div>
    </div>
@endsection
