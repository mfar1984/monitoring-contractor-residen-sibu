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
                    <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Created Date</div>
                    <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $transfer->created_at->format('d/m/Y H:i') }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Created By</div>
                    <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $transfer->creator?->full_name ?? '-' }}</div>
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
                <div>
                    <div style="font-size: 11px; color: #666; margin-bottom: 4px;">Total Projects</div>
                    <div style="font-size: 12px; font-weight: 600; color: #333;">{{ $transfer->projects->count() }} projects</div>
                </div>
            </div>
        </div>

        <!-- Attachment Section -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
            <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Application Letter</h4>
            @if($transfer->attachment_path)
                <a href="{{ route('pages.contractor-analysis.download', $transfer->id) }}" 
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 16px; background-color: white; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #007bff; font-size: 12px; font-weight: 600;">
                    <span class="material-symbols-outlined" style="font-size: 20px;">description</span>
                    <span>{{ basename($transfer->attachment_path) }}</span>
                    <span class="material-symbols-outlined" style="font-size: 18px; margin-left: 8px;">download</span>
                </a>
            @else
                <p style="color: #999; font-size: 12px;">No attachment available</p>
            @endif
        </div>

        <!-- Projects Table -->
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
            <h4 style="margin: 0 0 16px 0; font-size: 14px; font-weight: 600; color: #333;">Transferred Projects</h4>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 11px; background: white;">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">No.</th>
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Project Number</th>
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Project Name</th>
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Agency</th>
                            <th style="padding: 10px 8px; text-align: left; border: 1px solid #dee2e6; font-weight: 600;">Parliament/DUN</th>
                            <th style="padding: 10px 8px; text-align: right; border: 1px solid #dee2e6; font-weight: 600;">Total Cost (RM)</th>
                            <th style="padding: 10px 8px; text-align: center; border: 1px solid #dee2e6; font-weight: 600;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfer->projects as $index => $project)
                        <tr>
                            <td style="padding: 8px; border: 1px solid #dee2e6; text-align: center;">{{ $index + 1 }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $project->project_number }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $project->name }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $project->agencyCategory?->name ?? '-' }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6;">{{ $project->parliament?->name ?? $project->dun?->name ?? '-' }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6; text-align: right; font-weight: 600;">{{ number_format($project->total_cost, 2) }}</td>
                            <td style="padding: 8px; border: 1px solid #dee2e6; text-align: center;">
                                @if($project->status === 'Analysis Pending')
                                    <span class="status-badge" style="background-color: #fff3cd; color: #856404;">Analysis Pending</span>
                                @else
                                    <span class="status-badge status-active">{{ $project->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="padding: 20px; text-align: center; border: 1px solid #dee2e6; color: #999;">No projects found</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($transfer->projects->count() > 0)
                    <tfoot>
                        <tr style="background-color: #f8f9fa; font-weight: 600;">
                            <td colspan="5" style="padding: 10px 8px; border: 1px solid #dee2e6; text-align: right;">Total:</td>
                            <td style="padding: 10px 8px; border: 1px solid #dee2e6; text-align: right; font-weight: 700; color: #007bff;">
                                RM {{ number_format($transfer->projects->sum('total_cost'), 2) }}
                            </td>
                            <td style="padding: 10px 8px; border: 1px solid #dee2e6;"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

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
    </script>
@endsection
