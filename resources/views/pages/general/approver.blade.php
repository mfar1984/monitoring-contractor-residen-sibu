@extends('layouts.app')

@section('title', 'General Settings - Approver - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>General</span>
    <span class="breadcrumb-separator">›</span>
    <span>Approver</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-general-tabs active="approver" />
        
        <div class="tabs-content">
            @if(session('success'))
            <div style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
                {{ session('success') }}
            </div>
            @endif

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
                    <h3>Approver Settings</h3>
                    <p class="content-description">Configure approval workflow for Pre-Project and NOC submissions.</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <form method="POST" action="{{ route('pages.general.approver.store') }}">
                    @csrf
                    
                    <!-- Pre-Project Approval Settings -->
                    <div style="margin-bottom: 32px;">
                        <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Pre-Project Approval Settings</h4>
                        <p style="color: #666; font-size: 12px; margin-bottom: 16px;">Configure multiple approvers for Pre-Project submissions (1st layer approval)</p>
                        
                        <div class="form-group">
                            <label for="pre_project_approvers">Pre-Project Approvers <span style="color: #dc3545;">*</span></label>
                            <select id="pre_project_approvers" name="pre_project_approvers[]" multiple required style="height: 200px;">
                                @foreach($residenUsers as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, $preProjectApprovers ?? []) ? 'selected' : '' }}>
                                        {{ $user->full_name }} ({{ $user->residenCategory->name ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                            <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">
                                Hold Ctrl (Windows) or Cmd (Mac) to select multiple approvers. Selected users can approve Pre-Project submissions.
                            </small>
                        </div>
                    </div>

                    <!-- NOC Approval Settings -->
                    <div style="border-top: 1px solid #e0e0e0; padding-top: 24px;">
                        <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">NOC Approval Settings</h4>
                        <p style="color: #666; font-size: 12px; margin-bottom: 16px;">Configure two-level approval workflow for Notice of Change (NOC) submissions</p>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label for="first_approval_user">First Approval <span style="color: #dc3545;">*</span></label>
                                <select id="first_approval_user" name="first_approval_user" required>
                                    <option value="">Select First Approver</option>
                                    @foreach($residenUsers as $user)
                                        <option value="{{ $user->id }}" {{ ($settings['first_approval_user'] ?? '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }} ({{ $user->residenCategory->name ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">First level approver for NOC submissions</small>
                            </div>

                            <div class="form-group">
                                <label for="second_approval_user">Second Approval <span style="color: #dc3545;">*</span></label>
                                <select id="second_approval_user" name="second_approval_user" required>
                                    <option value="">Select Second Approver</option>
                                    @foreach($residenUsers as $user)
                                        <option value="{{ $user->id }}" {{ ($settings['second_approval_user'] ?? '') == $user->id ? 'selected' : '' }}>
                                            {{ $user->full_name }} ({{ $user->residenCategory->name ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                                <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Second level approver for NOC submissions</small>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Reset</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
