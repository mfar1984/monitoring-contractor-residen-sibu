@extends('layouts.app')

@section('title', 'Create Financial Analysis - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <a href="{{ route('pages.financial-analysis') }}" style="color: inherit; text-decoration: none;">Financial Analysis</a>
    <span class="breadcrumb-separator">›</span>
    <span>Create</span>
@endsection

@section('content')
    <div class="tabs-container">
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

            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin: 0 0 20px 0; font-size: 14px; font-weight: 600;">Create Financial Analysis</h2>

                <form method="POST" action="{{ route('pages.financial-analysis.store', $transfer->id) }}">
                    @csrf

                    <!-- Project Information Section (Read-only) -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #333;">Project Information</h3>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #e0e0e0;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div>
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Transfer Number</label>
                                    <div style="font-size: 12px; color: #333;">{{ $transfer->transfer_number }}</div>
                                </div>
                                <div>
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Agency</label>
                                    <div style="font-size: 12px; color: #333;">{{ $transfer->agency->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Project</label>
                                    <div style="font-size: 12px; color: #333;">{{ $transfer->projects->first()->name ?? '-' }}</div>
                                </div>
                                <div>
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Contractors</label>
                                    <div style="font-size: 12px; color: #333;">{{ $transfer->contractors->count() }} contractors</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Committee Section -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #333;">Approval Committee</h3>
                        <div id="committeeContainer">
                            @foreach($defaultPositions as $index => $position)
                            <div class="committee-row" style="display: grid; grid-template-columns: 2fr 2fr 2fr 50px; gap: 10px; margin-bottom: 10px; align-items: end;">
                                <div class="form-group" style="margin: 0;">
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                        Position <span style="color: #dc3545;">*</span>
                                    </label>
                                    <input type="text" name="approval_committee[{{ $index }}][position]" value="{{ $position }}" required style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                                </div>
                                <div class="form-group" style="margin: 0;">
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Name</label>
                                    <input type="text" name="approval_committee[{{ $index }}][name]" style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                                </div>
                                <div class="form-group" style="margin: 0;">
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Department</label>
                                    <input type="text" name="approval_committee[{{ $index }}][department]" style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                                </div>
                                <div>
                                    @if($index > 0)
                                    <button type="button" class="btn-danger" onclick="removeCommitteeRow(this)" style="padding: 8px 12px; font-size: 12px;">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <button type="button" onclick="addCommitteeRow()" class="btn-secondary" style="margin-top: 10px;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">add</span>
                            Add Committee Member
                        </button>
                    </div>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                        <button type="button" onclick="window.location.href='{{ route('pages.financial-analysis') }}'" class="btn btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                            Create Analysis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let committeeIndex = {{ count($defaultPositions) }};

        function addCommitteeRow() {
            const container = document.getElementById('committeeContainer');
            const row = document.createElement('div');
            row.className = 'committee-row';
            row.style.cssText = 'display: grid; grid-template-columns: 2fr 2fr 2fr 50px; gap: 10px; margin-bottom: 10px; align-items: end;';
            row.innerHTML = `
                <div class="form-group" style="margin: 0;">
                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                        Position <span style="color: #dc3545;">*</span>
                    </label>
                    <input type="text" name="approval_committee[${committeeIndex}][position]" required style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Name</label>
                    <input type="text" name="approval_committee[${committeeIndex}][name]" style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">Department</label>
                    <input type="text" name="approval_committee[${committeeIndex}][department]" style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                </div>
                <div>
                    <button type="button" class="btn-danger" onclick="removeCommitteeRow(this)" style="padding: 8px 12px; font-size: 12px;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                    </button>
                </div>
            `;
            container.appendChild(row);
            committeeIndex++;
        }

        function removeCommitteeRow(button) {
            button.closest('.committee-row').remove();
        }
    </script>
@endsection
