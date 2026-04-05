@extends('layouts.app')

@section('title', 'Edit Company - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>Master Data</span>
    <span class="breadcrumb-separator">›</span>
    <span>Contractor</span>
    <span class="breadcrumb-separator">›</span>
    <span>Edit Company</span>
@endsection

@section('content')
<style>
.page-tab { padding: 10px 15px; border: none; background: none; cursor: pointer; border-bottom: 2px solid transparent; color: #666; font-size: 12px; white-space: nowrap; }
.page-tab.active { border-bottom: 2px solid #007bff; color: #007bff; }
.page-tab:disabled { cursor: not-allowed; opacity: 0.5; color: #999; }
.page-tab-content { display: none; }
.page-tab-content.active { display: block; }
.section-header { background-color: #e0e7ff; color: #333; padding: 8px 12px; font-size: 12px; font-weight: 600; margin-bottom: 15px; }
.sub-section-header { background-color: #f0f4ff; color: #4a5568; padding: 6px 10px; font-size: 11px; font-weight: 600; margin-bottom: 12px; margin-top: 15px; border-left: 3px solid #007bff; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
.form-field { margin-bottom: 0; }
.form-field label { display: block; font-size: 12px; font-weight: 500; color: #333; margin-bottom: 5px; }
.form-field input, .form-field select, .form-field textarea { width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px; }
.form-field input[type="text"], .form-field input[type="email"], .form-field input[type="date"], .form-field select { height: 34px; }
.required { color: #dc3545; }
.table-container { overflow-x: auto; background-color: #f9f9f9; border: 1px solid #e0e0e0; }
.data-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.data-table thead { background-color: #e0e7ff; }
.data-table th { padding: 8px 12px; text-align: left; font-weight: 600; color: #333; }
.data-table td { padding: 8px 12px; background-color: white; border-top: 1px solid #e0e0e0; }
.data-table input { height: 28px; padding: 4px 8px; }
.data-table select { height: 28px; padding: 4px 8px; }
.data-table textarea { padding: 4px 8px; font-size: 11px; }
.btn-add { display: inline-flex; align-items: center; gap: 5px; height: 28px; padding: 0 12px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; margin-top: 10px; }
.btn-add:hover { background-color: #218838; }
.btn-delete { color: #dc3545; cursor: pointer; }
.btn-delete:hover { color: #c82333; }
.btn-select-classification { width: 100%; padding: 6px 8px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer; margin-top: 5px; }
.btn-select-classification:hover { background-color: #0056b3; }
.mini-table { width: 100%; border-collapse: collapse; font-size: 11px; border: 1px solid #e0e0e0; margin-top: 5px; }
.mini-table thead { background-color: #f5f5f5; }
.mini-table th { padding: 4px 6px; text-align: left; font-weight: 600; color: #333; border-bottom: 1px solid #e0e0e0; }
.mini-table td { padding: 4px 6px; background-color: white; border-bottom: 1px solid #e0e0e0; }
.mini-table-empty { text-align: center; color: #999; font-style: italic; padding: 8px; }
.modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1000; }
.modal-overlay.show { display: flex; align-items: center; justify-content: center; }
.modal-container { background-color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: 1px solid #e0e0e0; }
.modal-title { font-size: 14px; font-weight: 600; margin: 0; }
.modal-close { background: none; border: none; cursor: pointer; color: #666; padding: 0; }
.modal-close:hover { color: #333; }
.modal-body { padding: 20px; overflow-y: auto; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 15px 20px; border-top: 1px solid #e0e0e0; }
.btn { display: inline-flex; align-items: center; gap: 5px; height: 34px; padding: 0 16px; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; }
.btn-primary { background-color: #007bff; color: white; }
.btn-primary:hover { background-color: #0056b3; }
.btn-secondary { background-color: #6c757d; color: white; }
.btn-secondary:hover { background-color: #5a6268; }
</style>

<div class="tabs-container">
    <x-master-data-tabs active="contractor" />
    
    <div class="tabs-content">
        <div style="margin-bottom: 20px;">
            <h2 style="font-size: 14px; font-weight: 600; margin: 0 0 5px 0;">Edit Company</h2>
            <p style="font-size: 12px; color: #666; margin: 0;">Update contractor company information.</p>
        </div>

        @if($errors->any())
        <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Tab Navigation -->
        <div style="display: flex; gap: 5px; border-bottom: 1px solid #e0e0e0; margin-bottom: 20px; overflow-x: auto;">
            <button class="page-tab active" onclick="switchPageTab('company-details')" id="tab-company-details">Company Details</button>
            <button class="page-tab" onclick="switchPageTab('registration-details')" id="tab-registration-details">Registration Details</button>
            <button class="page-tab" disabled>Project Details <span style="font-size: 10px;">(Coming Soon)</span></button>
            <button class="page-tab" disabled>Performance Evaluation <span style="font-size: 10px;">(Coming Soon)</span></button>
            <button class="page-tab" disabled>Star Rating <span style="font-size: 10px;">(Coming Soon)</span></button>
            <button class="page-tab" disabled>Reports <span style="font-size: 10px;">(Coming Soon)</span></button>
        </div>

        <form method="POST" action="{{ route('pages.master-data.contractor.update', $contractor->id) }}" id="contractorEditForm">
            @csrf
            @method('PUT')

            <!-- Tab 1: Company Details -->
            <div class="page-tab-content active" id="content-company-details">
                <div class="section-header">Company Details</div>
                <div style="background-color: white; border: 1px solid #e0e0e0; padding: 15px; margin-bottom: 20px;">
                    
                    <!-- Basic Information -->
                    <div class="sub-section-header">Basic Information</div>
                    <div class="form-grid">
                        <div style="grid-column: span 2;">
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 8px;">Company Category <span class="required">*</span></label>
                            <div style="display: flex; gap: 20px;">
                                <label style="display: flex; align-items: center; gap: 5px; font-size: 12px;">
                                    <input type="radio" name="company_category" value="UPKJ" required {{ old('company_category', $contractor->company_category ?? 'UPKJ') == 'UPKJ' ? 'checked' : '' }} style="width: 18px; height: 18px;">
                                    <span>UPKJ</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 5px; font-size: 12px;">
                                    <input type="radio" name="company_category" value="Non-UPKJ" {{ old('company_category', $contractor->company_category) == 'Non-UPKJ' ? 'checked' : '' }} style="width: 18px; height: 18px;">
                                    <span>Non-UPKJ</span>
                                </label>
                            </div>
                        </div>
                        <div class="form-field">
                            <label>Company Name <span class="required">*</span></label>
                            <input type="text" name="company_name" required value="{{ old('company_name', $contractor->company_name) }}" placeholder="Enter company name">
                        </div>
                        <div class="form-field">
                            <label>Company Type <span class="required">*</span></label>
                            <select name="company_type" required>
                                <option value="">--- Please Select ---</option>
                                <option value="contractor" {{ old('company_type', $contractor->company_type) == 'contractor' ? 'selected' : '' }}>Contractor</option>
                                <option value="consultant" {{ old('company_type', $contractor->company_type) == 'consultant' ? 'selected' : '' }}>Consultant</option>
                                <option value="supplier" {{ old('company_type', $contractor->company_type) == 'supplier' ? 'selected' : '' }}>Supplier</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Code <span class="required">*</span></label>
                            <input type="text" name="code" required value="{{ old('code', $contractor->code) }}" placeholder="Enter code">
                        </div>
                        <div class="form-field">
                            <label>Date Established</label>
                            <input type="date" name="date_established" value="{{ old('date_established', $contractor->date_established ? $contractor->date_established->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <!-- Registration Information -->
                    <div class="sub-section-header">Registration Information</div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Registration Category <span class="required">*</span></label>
                            <select name="registration_category" required>
                                <option value="ALL" {{ old('registration_category', $contractor->registration_category) == 'ALL' ? 'selected' : '' }}>ALL</option>
                                <option value="Works" {{ old('registration_category', $contractor->registration_category) == 'Works' ? 'selected' : '' }}>Works</option>
                                <option value="Supplies & Services" {{ old('registration_category', $contractor->registration_category) == 'Supplies & Services' ? 'selected' : '' }}>Supplies & Services</option>
                                <option value="Electrical" {{ old('registration_category', $contractor->registration_category) == 'Electrical' ? 'selected' : '' }}>Electrical</option>
                                <option value="Mechanical" {{ old('registration_category', $contractor->registration_category) == 'Mechanical' ? 'selected' : '' }}>Mechanical</option>
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Registration No. <span class="required">*</span></label>
                            <input type="text" name="registration_number" required value="{{ old('registration_number', $contractor->registration_number) }}" placeholder="e.g., SA20180908">
                        </div>
                        <div class="form-field">
                            <label>Office Registration No. <span class="required">*</span></label>
                            <input type="text" name="office_registration_no" required value="{{ old('office_registration_no', $contractor->office_registration_no) }}" placeholder="e.g., RSBW/RTP - 10222">
                        </div>
                        <div class="form-field">
                            <label>Bumiputera Status</label>
                            <select name="bumiputera_status">
                                @php
                                    $bumistatus = strtolower(old('bumiputera_status', $contractor->bumiputera_status ?? ''));
                                @endphp
                                <option value="ALL" {{ $bumistatus == 'all' ? 'selected' : '' }}>ALL</option>
                                <option value="Yes" {{ $bumistatus == 'yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ $bumistatus == 'no' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="display: block; font-size: 12px; font-weight: 500; margin-bottom: 8px;">Registration Status</label>
                            <div style="display: flex; gap: 20px;">
                                <label style="display: flex; align-items: center; gap: 5px; font-size: 12px;">
                                    <input type="checkbox" name="registration_status_valid" value="1" {{ old('registration_status_valid', $contractor->registration_status_valid) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                                    <span>Valid</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 5px; font-size: 12px;">
                                    <input type="checkbox" name="registration_status_expired" value="1" {{ old('registration_status_expired', $contractor->registration_status_expired) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                                    <span>Expired / Disciplinary Action</span>
                                </label>
                            </div>
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="display: flex; align-items: center; gap: 5px; font-size: 12px;">
                                <input type="checkbox" name="rescue_contractor" value="1" {{ old('rescue_contractor', $contractor->rescue_contractor) ? 'checked' : '' }} style="width: 18px; height: 18px;">
                                <span>Rescue Contractor</span>
                            </label>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <div class="sub-section-header">Location Information</div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Division <span class="required">*</span></label>
                            <select name="division" id="divisionSelect" required onchange="filterDistricts()">
                                <option value="">Select Division</option>
                                <option value="ALL" {{ old('division', $contractor->division) == 'ALL' ? 'selected' : '' }}>ALL</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->name }}" data-id="{{ $division->id }}" {{ old('division', $contractor->division) == $division->name ? 'selected' : '' }}>
                                        {{ $division->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label>District <span class="required">*</span></label>
                            <select name="district" id="districtSelect" required>
                                <option value="">Select District</option>
                                <option value="ALL" {{ old('district', $contractor->district) == 'ALL' ? 'selected' : '' }}>ALL</option>
                                @foreach($districts as $district)
                                    <option value="{{ $district->name }}" data-division-id="{{ $district->division_id }}" data-division-name="{{ $district->division->name ?? '' }}" {{ old('district', $contractor->district) == $district->name ? 'selected' : '' }}>
                                        {{ $district->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Registered Location</label>
                            <input type="text" name="registered_location" value="{{ old('registered_location', $contractor->registered_location) }}" placeholder="e.g., SIBU">
                        </div>
                        <div class="form-field" style="grid-column: span 2;">
                            <label>Registered Address <span class="required">*</span></label>
                            <textarea name="registered_address" required rows="2" placeholder="Enter registered address">{{ old('registered_address', $contractor->registered_address) }}</textarea>
                        </div>
                        <div class="form-field">
                            <label>Postal Address</label>
                            <textarea name="postal_address" rows="2" placeholder="Enter postal address">{{ old('postal_address', $contractor->postal_address) }}</textarea>
                        </div>
                        <div class="form-field">
                            <label>Business Address</label>
                            <textarea name="business_address" rows="2" placeholder="Enter business address">{{ old('business_address', $contractor->business_address) }}</textarea>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="sub-section-header">Contact Information</div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" name="email" required value="{{ old('email', $contractor->email) }}" placeholder="e.g., company@example.com">
                        </div>
                        <div class="form-field">
                            <label>Telephone No. <span class="required">*</span></label>
                            <input type="text" name="telephone_no" required value="{{ old('telephone_no', $contractor->telephone_no) }}" placeholder="e.g., 0109772727">
                        </div>
                        <div class="form-field">
                            <label>Mobile No.</label>
                            <input type="text" name="mobile_no" value="{{ old('mobile_no', $contractor->mobile_no) }}" placeholder="e.g., 0109772727">
                        </div>
                        <div class="form-field">
                            <label>Fax No.</label>
                            <input type="text" name="fax_no" value="{{ old('fax_no', $contractor->fax_no) }}" placeholder="Enter fax number">
                        </div>
                        <div class="form-field">
                            <label>Contact Person <span class="required">*</span></label>
                            <input type="text" name="contact_person" required value="{{ old('contact_person', $contractor->contact_person) }}" placeholder="e.g., Ahmad Ishammudin">
                        </div>
                        <div class="form-field">
                            <label>Contact No.</label>
                            <input type="text" name="contact_no" value="{{ old('contact_no', $contractor->contact_no) }}" placeholder="e.g., 0178670308">
                        </div>
                    </div>
                </div>

                <!-- Company As Shareholder Section -->
                <div class="section-header">Company As Shareholder</div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">No.</th>
                                <th>Company Name</th>
                                <th style="width: 180px;">Registration No.</th>
                                <th style="width: 120px;">Shares (%)</th>
                                <th style="width: 80px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="shareholdersTableBody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px; color: #666;">No shareholders added yet. Click "Add Row" to add.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" onclick="addShareholderRow()" class="btn-add">
                    <span class="material-symbols-outlined" style="font-size: 14px;">add</span>
                    Add Row
                </button>

                <!-- Shareholders/Directors Section -->
                <div class="section-header" style="margin-top: 20px;">Shareholders / Partners / Proprietors / Directors</div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 60px;">No.</th>
                                <th>Name</th>
                                <th style="width: 180px;">IC Number</th>
                                <th style="width: 120px;">Shares (%)</th>
                                <th style="width: 80px; text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="directorsTableBody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px; color: #666;">No directors added yet. Click "Add Row" to add.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" onclick="addDirectorRow()" class="btn-add">
                    <span class="material-symbols-outlined" style="font-size: 14px;">add</span>
                    Add Row
                </button>

                <!-- Authorized Person Section -->
                <div class="section-header" style="margin-top: 20px;">Authorized Person for Procurement</div>
                <div style="background-color: white; border: 1px solid #e0e0e0; padding: 15px; margin-bottom: 20px;">
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Authorized Person Name</label>
                            <input type="text" name="authorized_person_name" value="{{ old('authorized_person_name', $contractor->authorized_person_name) }}" placeholder="e.g., FAYEZARAH BINTI OTHMAN">
                        </div>
                        <div class="form-field">
                            <label>IC Number</label>
                            <input type="text" name="authorized_person_ic" value="{{ old('authorized_person_ic', $contractor->authorized_person_ic) }}" placeholder="e.g., 831019-13-6002">
                        </div>
                    </div>
                </div>

                <!-- Total Manpower Section -->
                <div class="section-header">Total Manpower</div>
                <div class="table-container">
                    <table class="data-table">
                        <tbody>
                            <tr>
                                <td style="background-color: #e0e7ff; font-weight: 600; width: 50%;">Sole Proprietor</td>
                                <td><input type="number" name="manpower_sole_proprietor" min="0" value="{{ old('manpower_sole_proprietor', $contractor->manpower_sole_proprietor ?? 0) }}" onchange="calculateTotalManpower()" style="width: 120px;"></td>
                            </tr>
                            <tr>
                                <td style="background-color: #e0e7ff; font-weight: 600;">Management</td>
                                <td><input type="number" name="manpower_management" min="0" value="{{ old('manpower_management', $contractor->manpower_management ?? 0) }}" onchange="calculateTotalManpower()" style="width: 120px;"></td>
                            </tr>
                            <tr>
                                <td style="background-color: #e0e7ff; font-weight: 600;">Professional</td>
                                <td><input type="number" name="manpower_professional" min="0" value="{{ old('manpower_professional', $contractor->manpower_professional ?? 0) }}" onchange="calculateTotalManpower()" style="width: 120px;"></td>
                            </tr>
                            <tr>
                                <td style="background-color: #e0e7ff; font-weight: 600;">Sub-Professional</td>
                                <td><input type="number" name="manpower_sub_professional" min="0" value="{{ old('manpower_sub_professional', $contractor->manpower_sub_professional ?? 0) }}" onchange="calculateTotalManpower()" style="width: 120px;"></td>
                            </tr>
                            <tr>
                                <td style="background-color: #e0e7ff; font-weight: 600;">Competent Worker</td>
                                <td><input type="number" name="manpower_competent_worker" min="0" value="{{ old('manpower_competent_worker', $contractor->manpower_competent_worker ?? 0) }}" onchange="calculateTotalManpower()" style="width: 120px;"></td>
                            </tr>
                            <tr style="background-color: #fff3cd;">
                                <td style="font-weight: 700;">Overall Total</td>
                                <td><input type="number" name="manpower_total" readonly value="{{ old('manpower_total', $contractor->manpower_total ?? 0) }}" id="manpowerTotal" style="width: 120px; background-color: #f5f5f5; font-weight: 700;"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Registration Details -->
            <div class="page-tab-content" id="content-registration-details">
                <!-- UPKJ Registration Records Section -->
                <div class="section-header">UPKJ Registration Records</div>
                <div style="margin-bottom: 20px;">
                    <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Manage multiple UPKJ registration records with classifications for this contractor company.</p>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">No.</th>
                                    <th style="width: 150px;">Category</th>
                                    <th style="width: 200px;">Registration Status / Validity Period</th>
                                    <th style="width: 200px;">Bumiputera Status / Validity Period</th>
                                    <th style="width: 150px;">Certificate No.</th>
                                    <th>Classification</th>
                                    <th style="width: 80px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="upkjRecordsTableBody">
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px; color: #666;">
                                        No UPKJ records added yet. Click "Add UPKJ Record" to add.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" onclick="addUpkjRecordRow()" class="btn-add">
                        <span class="material-symbols-outlined" style="font-size: 14px;">add</span>
                        Add UPKJ Record
                    </button>
                </div>

                <div class="section-header">Status</div>
                <div style="background-color: white; border: 1px solid #e0e0e0; padding: 15px; margin-bottom: 20px;">
                    <div class="form-field">
                        <label>Status <span class="required">*</span></label>
                        <select name="status" required>
                            <option value="Active" {{ old('status', $contractor->status ?? 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status', $contractor->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="Pending" {{ old('status', $contractor->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Rejected" {{ old('status', $contractor->status) == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="Suspended" {{ old('status', $contractor->status) == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bottom Actions -->
            <div style="display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                <a href="{{ route('pages.master-data.contractor') }}" style="display: inline-flex; align-items: center; gap: 5px; height: 34px; padding: 0 16px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 12px;">
                    Cancel
                </a>
                <button type="submit" style="display: inline-flex; align-items: center; gap: 5px; height: 34px; padding: 0 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 12px; cursor: pointer;">
                    <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                    Update Company
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Classification Selection Modal -->
<div class="modal-overlay" id="classificationModal">
    <div class="modal-container" style="max-width: 900px;">
        <!-- Modal Header -->
        <div class="modal-header">
            <h3 class="modal-title">Select UPKJ Classification</h3>
            <button class="modal-close" onclick="closeClassificationModal()" type="button">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="modal-body">
            <!-- Search Input -->
            <div class="form-group" style="margin-bottom: 15px;">
                <input type="text" id="classificationSearch" placeholder="Search by class, head, subhead, or description..." 
                       style="width: 100%; height: 34px; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;"
                       onkeyup="searchClassifications()">
            </div>
            
            <!-- Classifications Table -->
            <div style="max-height: 400px; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 4px;">
                <table class="data-table" style="margin: 0;">
                    <thead style="position: sticky; top: 0; background-color: #e0e7ff; z-index: 1;">
                        <tr>
                            <th style="width: 80px;">Class</th>
                            <th style="width: 80px;">Head</th>
                            <th style="width: 120px;">Subhead</th>
                            <th>Description</th>
                            <th style="width: 100px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="classificationsTableBody">
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #666;">
                                Loading classifications...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeClassificationModal()">
                Cancel
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Task 9.2: Pass existing UPKJ records data to JavaScript
const existingUpkjRecords = @json($contractor->upkjRecords ?? []);
// Task 10.3: Pass old input data for form repopulation on validation failure
const oldUpkjRecords = @json(old('upkj', []));

// Pass existing shareholders and directors data to JavaScript
const existingShareholders = @json($shareholders ?? []);
const existingDirectors = @json($directors ?? []);

let shareholderCount = 0;
let directorCount = 0;

// Tab switching
function switchPageTab(tabName) {
    document.querySelectorAll('.page-tab').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelectorAll('.page-tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    document.getElementById('tab-' + tabName).classList.add('active');
    document.getElementById('content-' + tabName).classList.add('active');
}

// Add shareholder row
function addShareholderRow() {
    const tableBody = document.getElementById('shareholdersTableBody');
    if (tableBody.querySelector('td[colspan="5"]')) {
        tableBody.innerHTML = '';
    }
    
    shareholderCount++;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td style="text-align: center;">${shareholderCount}</td>
        <td><input type="text" name="shareholders[${shareholderCount}][name]" placeholder="Enter company name" style="width: 100%;"></td>
        <td><input type="text" name="shareholders[${shareholderCount}][registration_no]" placeholder="e.g., SA20180908" style="width: 100%;"></td>
        <td><input type="number" name="shareholders[${shareholderCount}][shares]" min="0" max="100" step="0.01" placeholder="0.00" style="width: 100%;"></td>
        <td style="text-align: center;">
            <span class="material-symbols-outlined btn-delete" onclick="this.closest('tr').remove(); reindexShareholderRows();" style="font-size: 16px;">delete</span>
        </td>
    `;
    tableBody.appendChild(row);
}

// Add director row
function addDirectorRow() {
    const tableBody = document.getElementById('directorsTableBody');
    if (tableBody.querySelector('td[colspan="5"]')) {
        tableBody.innerHTML = '';
    }
    
    directorCount++;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td style="text-align: center;">${directorCount}</td>
        <td><input type="text" name="directors[${directorCount}][name]" placeholder="e.g., AHMAD ISHAMMUDIN BIN DAHRI" style="width: 100%;"></td>
        <td><input type="text" name="directors[${directorCount}][ic_number]" placeholder="e.g., 890415-13-6231" style="width: 100%;"></td>
        <td><input type="number" name="directors[${directorCount}][shares]" min="0" max="100" step="0.01" placeholder="0.00" style="width: 100%;"></td>
        <td style="text-align: center;">
            <span class="material-symbols-outlined btn-delete" onclick="this.closest('tr').remove(); reindexDirectorRows();" style="font-size: 16px;">delete</span>
        </td>
    `;
    tableBody.appendChild(row);
}

// Reindex rows
function reindexShareholderRows() {
    const rows = document.querySelectorAll('#shareholdersTableBody tr');
    rows.forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
    });
}

function reindexDirectorRows() {
    const rows = document.querySelectorAll('#directorsTableBody tr');
    rows.forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
    });
}

// Calculate total manpower
function calculateTotalManpower() {
    const soleProprietor = parseInt(document.querySelector('input[name="manpower_sole_proprietor"]').value) || 0;
    const management = parseInt(document.querySelector('input[name="manpower_management"]').value) || 0;
    const professional = parseInt(document.querySelector('input[name="manpower_professional"]').value) || 0;
    const subProfessional = parseInt(document.querySelector('input[name="manpower_sub_professional"]').value) || 0;
    const competentWorker = parseInt(document.querySelector('input[name="manpower_competent_worker"]').value) || 0;
    
    const total = soleProprietor + management + professional + subProfessional + competentWorker;
    document.getElementById('manpowerTotal').value = total;
}

// Cascading Dropdown: Division → District
let allDistricts = [];

// Store all districts on page load
document.addEventListener('DOMContentLoaded', function() {
    const districtSelect = document.getElementById('districtSelect');
    const currentDistrictValue = districtSelect.value; // Save current selected value
    
    allDistricts = Array.from(districtSelect.options).map(option => ({
        value: option.value,
        text: option.textContent,
        divisionId: option.getAttribute('data-division-id'),
        divisionName: option.getAttribute('data-division-name')
    }));
    
    // Trigger filter on page load to show correct districts
    filterDistricts();
    
    // Restore the selected district value after filtering
    if (currentDistrictValue) {
        districtSelect.value = currentDistrictValue;
    }
});

function filterDistricts() {
    const divisionSelect = document.getElementById('divisionSelect');
    const districtSelect = document.getElementById('districtSelect');
    const selectedDivision = divisionSelect.value;
    
    // Get selected division ID from data attribute
    const selectedOption = divisionSelect.options[divisionSelect.selectedIndex];
    const selectedDivisionId = selectedOption ? selectedOption.getAttribute('data-id') : null;
    
    // Clear current district options
    districtSelect.innerHTML = '<option value="">Select District</option><option value="ALL">ALL</option>';
    
    // Show all districts if "ALL" or no division selected
    if (selectedDivision === 'ALL' || selectedDivision === '' || !selectedDivisionId) {
        allDistricts.forEach(district => {
            if (district.value !== '' && district.value !== 'ALL') {
                const option = document.createElement('option');
                option.value = district.value;
                option.textContent = district.text;
                option.setAttribute('data-division-id', district.divisionId);
                option.setAttribute('data-division-name', district.divisionName);
                districtSelect.appendChild(option);
            }
        });
        return;
    }
    
    // Filter districts based on selected division ID
    allDistricts.forEach(district => {
        if (district.value === '' || district.value === 'ALL') {
            return;
        }
        
        if (district.divisionId === selectedDivisionId) {
            const option = document.createElement('option');
            option.value = district.value;
            option.textContent = district.text;
            option.setAttribute('data-division-id', district.divisionId);
            option.setAttribute('data-division-name', district.divisionName);
            districtSelect.appendChild(option);
        }
    });
}

// UPKJ filtering (placeholder - needs API implementation)
function filterUpkjHeads() {
    const upkjClass = document.getElementById('upkj_class').value;
    // TODO: Implement AJAX call to get heads based on class
}

function filterUpkjSubheads() {
    const upkjHead = document.getElementById('upkj_head').value;
    // TODO: Implement AJAX call to get subheads based on head
}

// Refresh UPKJ dropdown when window regains focus (user returns from UPKJ Master Data tab)
let upkjRefreshEnabled = false;

// Enable refresh when user clicks "Add UPKJ Record" button
document.addEventListener('DOMContentLoaded', function() {
    const addUpkjBtn = document.querySelector('a[href*="master-data/upkj"]');
    if (addUpkjBtn) {
        addUpkjBtn.addEventListener('click', function() {
            upkjRefreshEnabled = true;
        });
    }
});

// Listen for window focus event
window.addEventListener('focus', function() {
    if (upkjRefreshEnabled) {
        refreshUpkjDropdown();
        upkjRefreshEnabled = false; // Reset flag
    }
});

// Function to refresh UPKJ Class dropdown
function refreshUpkjDropdown() {
    const upkjClassSelect = document.getElementById('upkj_class');
    const currentValue = upkjClassSelect.value;
    
    // Fetch updated UPKJ classes
    fetch('/api/upkj/classes')
        .then(response => response.json())
        .then(data => {
            // Clear current options except first one
            upkjClassSelect.innerHTML = '<option value="">Select Class</option>';
            
            // Add updated options
            data.forEach(upkjClass => {
                const option = document.createElement('option');
                option.value = upkjClass.class;
                option.textContent = upkjClass.class + ' - ' + upkjClass.class_description;
                upkjClassSelect.appendChild(option);
            });
            
            // Restore previous selection if still exists
            if (currentValue) {
                upkjClassSelect.value = currentValue;
            }
        })
        .catch(error => {
            console.error('Error refreshing UPKJ dropdown:', error);
        });
}

// ============================================================================
// UPKJ Records Management JavaScript Functions
// ============================================================================

let upkjRecordCount = 0;
let currentUpkjIndex = null;
let allClassifications = [];

/**
 * Task 5.1: Add UPKJ Record Row
 * Adds a new UPKJ record row to the table with all input fields
 */
function addUpkjRecordRow() {
    const tableBody = document.getElementById('upkjRecordsTableBody');
    
    // Remove empty message if exists
    if (tableBody.querySelector('td[colspan="7"]')) {
        tableBody.innerHTML = '';
    }
    
    upkjRecordCount++;
    const row = document.createElement('tr');
    row.setAttribute('data-upkj-index', upkjRecordCount);
    row.innerHTML = `
        <td style="text-align: center;">${upkjRecordCount}</td>
        <td>
            <select name="upkj[${upkjRecordCount}][category]" id="upkj_category_${upkjRecordCount}" required
                    style="width: 100%; height: 28px; padding: 4px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                <option value="">Select Category</option>
                <option value="Works">Works</option>
                <option value="Supplies & Services">Supplies & Services</option>
                <option value="Electrical">Electrical</option>
                <option value="Mechanical">Mechanical</option>
            </select>
        </td>
        <td>
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <select name="upkj[${upkjRecordCount}][registration_status]" required
                        style="width: 100%; height: 28px; padding: 4px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                    <option value="">Select Status</option>
                    <option value="Valid">Valid</option>
                    <option value="Expired">Expired</option>
                    <option value="Pending">Pending</option>
                </select>
                <input type="text" name="upkj[${upkjRecordCount}][validity_period]" 
                       placeholder="e.g., 31/07/2025 - 30/07/2027"
                       style="width: 100%; height: 28px; padding: 4px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;" hidden>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px;">
                    <input type="date" name="upkj[${upkjRecordCount}][validity_from]" 
                           placeholder="From"
                           onchange="updateValidityPeriod(${upkjRecordCount})"
                           style="width: 100%; height: 28px; padding: 4px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                    <input type="date" name="upkj[${upkjRecordCount}][validity_to]" 
                           placeholder="To"
                           onchange="updateValidityPeriod(${upkjRecordCount})"
                           style="width: 100%; height: 28px; padding: 4px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                </div>
            </div>
        </td>
        <td>
            <div style="display: flex; flex-direction: column; gap: 5px;">
                <select name="upkj[${upkjRecordCount}][bumiputera_status]"
                        style="width: 100%; height: 28px; padding: 4px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                    <option value="">Select Status</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
                <input type="text" name="upkj[${upkjRecordCount}][bumiputera_validity]" 
                       placeholder="e.g., 31/07/2025 - 30/07/2027"
                       style="width: 100%; height: 28px; padding: 4px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;" hidden>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px;">
                    <input type="date" name="upkj[${upkjRecordCount}][bumiputera_from]" 
                           placeholder="From"
                           onchange="updateBumiValidity(${upkjRecordCount})"
                           style="width: 100%; height: 28px; padding: 4px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                    <input type="date" name="upkj[${upkjRecordCount}][bumiputera_to]" 
                           placeholder="To"
                           onchange="updateBumiValidity(${upkjRecordCount})"
                           style="width: 100%; height: 28px; padding: 4px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                </div>
            </div>
        </td>
        <td>
            <input type="text" name="upkj[${upkjRecordCount}][certificate_no]" 
                   placeholder="e.g., UPK/J/W/017970"
                   style="width: 100%; height: 28px; padding: 4px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
        </td>
        <td>
            <div style="border: 1px solid #e0e0e0; border-radius: 4px;">
                <!-- Mini Classification Table -->
                <table class="mini-table">
                    <thead>
                        <tr>
                            <th style="font-size: 10px;">Class</th>
                            <th style="font-size: 10px;">Head</th>
                            <th style="font-size: 10px;">Subhead</th>
                            <th style="width: 30px; text-align: center; font-size: 10px;">✕</th>
                        </tr>
                    </thead>
                    <tbody id="classificationRows_${upkjRecordCount}">
                        <tr>
                            <td colspan="4" class="mini-table-empty">No classifications added</td>
                        </tr>
                    </tbody>
                </table>
                <!-- Select Classification Button -->
                <button type="button" onclick="openClassificationModal(${upkjRecordCount})" class="btn-select-classification">
                    <span class="material-symbols-outlined" style="font-size: 12px; vertical-align: middle;">add</span>
                    Select from UPKJ Classifications
                </button>
            </div>
        </td>
        <td style="text-align: center;">
            <span class="material-symbols-outlined btn-delete" onclick="deleteUpkjRow(${upkjRecordCount})" style="font-size: 16px;">delete</span>
        </td>
    `;
    tableBody.appendChild(row);
}

/**
 * Task 5.2: Delete UPKJ Row
 * Removes a UPKJ record row and re-indexes remaining rows
 */
function deleteUpkjRow(index) {
    const row = document.querySelector(`tr[data-upkj-index="${index}"]`);
    if (row) {
        row.remove();
        reindexUpkjRows();
    }
}

/**
 * Task 5.3: Reindex UPKJ Rows
 * Updates row numbers sequentially after deletion
 */
function reindexUpkjRows() {
    const rows = document.querySelectorAll('#upkjRecordsTableBody tr');
    rows.forEach((row, index) => {
        if (!row.querySelector('td[colspan]')) {
            row.querySelector('td:first-child').textContent = index + 1;
        }
    });
}

/**
 * Task 6.1: Open Classification Modal
 * Opens modal and fetches classifications filtered by category
 */
function openClassificationModal(upkjIndex) {
    // Get the selected category for this row
    const categorySelect = document.getElementById(`upkj_category_${upkjIndex}`);
    const selectedCategory = categorySelect.value;
    
    if (!selectedCategory) {
        alert('Please select a category first');
        return;
    }
    
    currentUpkjIndex = upkjIndex;
    
    // Clear search box
    document.getElementById('classificationSearch').value = '';
    
    // Show modal
    document.getElementById('classificationModal').classList.add('show');
    
    // Fetch classifications from API
    fetchClassifications(selectedCategory);
}

/**
 * Task 6.2: Close Classification Modal
 * Hides modal and clears search input
 */
function closeClassificationModal() {
    document.getElementById('classificationModal').classList.remove('show');
    document.getElementById('classificationSearch').value = '';
    document.getElementById('classificationsTableBody').innerHTML = '';
    currentUpkjIndex = null;
}

/**
 * Fetch classifications from API
 * Helper function for modal population
 */
function fetchClassifications(category) {
    const tbody = document.getElementById('classificationsTableBody');
    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #666;">Loading classifications...</td></tr>';
    
    fetch(`/api/upkj-classifications?category=${encodeURIComponent(category)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch classifications');
            }
            return response.json();
        })
        .then(data => {
            allClassifications = data;
            displayClassifications(data);
        })
        .catch(error => {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #dc3545;">Error loading classifications. Please try again.</td></tr>';
            console.error('Error fetching classifications:', error);
        });
}

/**
 * Display classifications in modal table
 * Helper function for modal display
 */
function displayClassifications(classifications) {
    const tbody = document.getElementById('classificationsTableBody');
    
    if (classifications.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #666;">No classifications found</td></tr>';
        return;
    }
    
    tbody.innerHTML = classifications.map(item => {
        const subhead = [item.subhead_code, item.subhead_letter, item.subhead_roman].filter(v => v).join(' ');
        return `
            <tr style="cursor: pointer;" onmouseover="this.style.backgroundColor='#f5f5f5'" onmouseout="this.style.backgroundColor='white'">
                <td style="padding: 8px 12px; font-size: 12px;">${item.class || '-'}</td>
                <td style="padding: 8px 12px; font-size: 12px;">${item.head_code || '-'}</td>
                <td style="padding: 8px 12px; font-size: 12px;">${subhead || '-'}</td>
                <td style="padding: 8px 12px; font-size: 12px;">${item.description || '-'}</td>
                <td style="padding: 8px 12px; text-align: center;">
                    <button type="button" onclick='selectClassification(${JSON.stringify(item).replace(/'/g, "&#39;")})' 
                            style="padding: 4px 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-size: 11px; cursor: pointer;">
                        Select
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Task 6.3: Search Classifications
 * Filters classifications based on search term
 */
function searchClassifications() {
    const searchTerm = document.getElementById('classificationSearch').value.toLowerCase();
    
    if (!searchTerm) {
        displayClassifications(allClassifications);
        return;
    }
    
    const filtered = allClassifications.filter(item => {
        const subhead = [item.subhead_code, item.subhead_letter, item.subhead_roman].filter(v => v).join(' ').toLowerCase();
        return (
            (item.class && item.class.toLowerCase().includes(searchTerm)) ||
            (item.head_code && item.head_code.toLowerCase().includes(searchTerm)) ||
            subhead.includes(searchTerm) ||
            (item.description && item.description.toLowerCase().includes(searchTerm))
        );
    });
    
    displayClassifications(filtered);
}

/**
 * Task 6.4: Select Classification
 * Adds selected classification to mini-table and closes modal
 */
function selectClassification(classification) {
    if (!currentUpkjIndex) return;
    
    addClassificationToMiniTable(currentUpkjIndex, classification);
    closeClassificationModal();
}

/**
 * Task 7.1: Add Classification to Mini-Table
 * Adds classification row to the mini-table with hidden inputs
 */
function addClassificationToMiniTable(upkjIndex, classification) {
    const tbody = document.getElementById(`classificationRows_${upkjIndex}`);
    
    // Remove "No classifications" message if exists
    if (tbody.querySelector('.mini-table-empty')) {
        tbody.innerHTML = '';
    }
    
    // Check for duplicates - must match ALL components (class, head, subhead, letter, roman)
    const existingRows = tbody.querySelectorAll('tr');
    for (let row of existingRows) {
        const existingClass = row.querySelector('input[name*="[class]"]')?.value;
        const existingHead = row.querySelector('input[name*="[head_code]"]')?.value;
        const existingSubhead = row.querySelector('input[name*="[subhead_code]"]')?.value;
        const existingLetter = row.querySelector('input[name*="[subhead_letter]"]')?.value || '';
        const existingRoman = row.querySelector('input[name*="[subhead_roman]"]')?.value || '';
        
        // Compare all components to determine if truly duplicate
        if (existingClass === classification.class && 
            existingHead === classification.head_code && 
            existingSubhead === classification.subhead_code &&
            existingLetter === (classification.subhead_letter || '') &&
            existingRoman === (classification.subhead_roman || '')) {
            alert('This classification is already added');
            return;
        }
    }
    
    // Build subhead string
    const subhead = [classification.subhead_code, classification.subhead_letter, classification.subhead_roman].filter(v => v).join(' ');
    
    // Count existing rows for unique naming
    const rowCount = tbody.querySelectorAll('tr').length;
    
    // Create new row
    const row = document.createElement('tr');
    row.innerHTML = `
        <td style="padding: 4px 6px;">
            ${classification.class || '-'}
            <input type="hidden" name="upkj[${upkjIndex}][classifications][${rowCount}][id]" value="${classification.id}">
            <input type="hidden" name="upkj[${upkjIndex}][classifications][${rowCount}][class]" value="${classification.class || ''}">
        </td>
        <td style="padding: 4px 6px;">
            ${classification.head_code || '-'}
            <input type="hidden" name="upkj[${upkjIndex}][classifications][${rowCount}][head_code]" value="${classification.head_code || ''}">
        </td>
        <td style="padding: 4px 6px;">
            ${subhead || '-'}
            <input type="hidden" name="upkj[${upkjIndex}][classifications][${rowCount}][subhead_code]" value="${classification.subhead_code || ''}">
            <input type="hidden" name="upkj[${upkjIndex}][classifications][${rowCount}][subhead_letter]" value="${classification.subhead_letter || ''}">
            <input type="hidden" name="upkj[${upkjIndex}][classifications][${rowCount}][subhead_roman]" value="${classification.subhead_roman || ''}">
            <input type="hidden" name="upkj[${upkjIndex}][classifications][${rowCount}][description]" value="${classification.description || ''}">
        </td>
        <td style="padding: 4px 6px; text-align: center;">
            <span class="material-symbols-outlined" onclick="deleteClassification(${upkjIndex}, this)" 
                  style="font-size: 14px; color: #dc3545; cursor: pointer;">close</span>
        </td>
    `;
    
    tbody.appendChild(row);
}

/**
 * Task 7.2: Delete Classification
 * Removes classification from mini-table
 */
function deleteClassification(upkjIndex, element) {
    const row = element.closest('tr');
    const tbody = document.getElementById(`classificationRows_${upkjIndex}`);
    
    row.remove();
    
    // Show "No classifications" message if table is empty
    if (tbody.querySelectorAll('tr').length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="mini-table-empty">No classifications added</td></tr>';
    }
}

/**
 * Task 6.5: Modal Outside Click Event Listener
 * Closes modal when clicking outside the modal container
 */
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('classificationModal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeClassificationModal();
            }
        });
    }
    
    // Task 9.1: Load existing UPKJ records on page load
    loadExistingUpkjRecords();
    
    // Load existing shareholders and directors on page load
    loadExistingShareholders();
    loadExistingDirectors();
    
    // Task 10.1: Add client-side validation on form submit
    const form = document.querySelector('form[action*="contractor"]');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!validateUpkjRecords()) {
                event.preventDefault();
                return false;
            }
        });
    }
});

/**
 * Task 10.1: Client-Side Validation
 * Validates UPKJ records before form submission
 */
function validateUpkjRecords() {
    const upkjRows = document.querySelectorAll('#upkjRecordsTableBody tr[data-upkj-index]');
    
    if (upkjRows.length === 0) {
        // No UPKJ records - this is valid (optional)
        return true;
    }
    
    let isValid = true;
    let errorMessages = [];
    
    upkjRows.forEach((row, index) => {
        const upkjIndex = row.getAttribute('data-upkj-index');
        const rowNumber = index + 1;
        
        // Validate category
        const categorySelect = document.getElementById(`upkj_category_${upkjIndex}`);
        if (!categorySelect || !categorySelect.value) {
            errorMessages.push(`Row ${rowNumber}: Category is required`);
            if (categorySelect) categorySelect.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            if (categorySelect) categorySelect.style.borderColor = '#e0e0e0';
        }
        
        // Validate registration status
        const statusSelect = row.querySelector(`select[name="upkj[${upkjIndex}][registration_status]"]`);
        if (!statusSelect || !statusSelect.value) {
            errorMessages.push(`Row ${rowNumber}: Registration status is required`);
            if (statusSelect) statusSelect.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            if (statusSelect) statusSelect.style.borderColor = '#e0e0e0';
        }
        
        // Validate classifications (at least one required)
        const classificationRows = document.querySelectorAll(`#classificationRows_${upkjIndex} tr:not(.mini-table-empty)`);
        if (classificationRows.length === 0) {
            errorMessages.push(`Row ${rowNumber}: At least one classification is required`);
            isValid = false;
        }
    });
    
    if (!isValid) {
        // Display error messages
        const errorHtml = `
            <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
                <strong>Validation Errors:</strong>
                <ul style="margin: 5px 0 0 20px; padding: 0;">
                    ${errorMessages.map(msg => `<li>${msg}</li>`).join('')}
                </ul>
            </div>
        `;
        
        // Find the Registration Details tab content
        const registrationTab = document.getElementById('content-registration-details');
        if (registrationTab) {
            // Remove existing error messages
            const existingErrors = registrationTab.querySelectorAll('.validation-error-message');
            existingErrors.forEach(el => el.remove());
            
            // Add new error message at the top
            const errorDiv = document.createElement('div');
            errorDiv.className = 'validation-error-message';
            errorDiv.innerHTML = errorHtml;
            registrationTab.insertBefore(errorDiv, registrationTab.firstChild);
            
            // Switch to Registration Details tab
            switchPageTab('registration-details');
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            // Fallback: show alert
            alert('Validation Errors:\n\n' + errorMessages.join('\n'));
        }
    }
    
    return isValid;
}

/**
 * Task 9.1: Load Existing UPKJ Records
 * Populates UPKJ records table with existing data from database
 * Task 10.3: Prioritizes old input data for form repopulation on validation failure
 */
function loadExistingUpkjRecords() {
    // Task 10.3: Prioritize old input data (from validation failure) over existing data
    let recordsToLoad = [];
    
    if (oldUpkjRecords && Object.keys(oldUpkjRecords).length > 0) {
        // Convert old input object to array
        recordsToLoad = Object.values(oldUpkjRecords);
    } else if (existingUpkjRecords && existingUpkjRecords.length > 0) {
        // Use existing records from database (including auto-migrated legacy data)
        recordsToLoad = existingUpkjRecords;
    }
    
    if (recordsToLoad.length === 0) {
        return;
    }
    
    // Loop through records and add them to the table
    recordsToLoad.forEach(record => {
        // Add new row
        addUpkjRecordRow();
        
        // Get the current upkjRecordCount (the row we just added)
        const currentIndex = upkjRecordCount;
        
        // Populate fields with existing data
        const categorySelect = document.getElementById(`upkj_category_${currentIndex}`);
        if (categorySelect && record.category) {
            categorySelect.value = record.category;
        }
        
        const statusSelect = document.querySelector(`select[name="upkj[${currentIndex}][registration_status]"]`);
        if (statusSelect && record.registration_status) {
            statusSelect.value = record.registration_status;
        }
        
        const validityInput = document.querySelector(`input[name="upkj[${currentIndex}][validity_period]"]`);
        if (validityInput && record.validity_period) {
            validityInput.value = record.validity_period;
            
            // Split and populate date fields
            if (record.validity_period.includes(' - ')) {
                const dates = record.validity_period.split(' - ');
                const fromDate = convertDDMMYYYYtoYYYYMMDD(dates[0].trim());
                const toDate = convertDDMMYYYYtoYYYYMMDD(dates[1].trim());
                
                const fromInput = document.querySelector(`input[name="upkj[${currentIndex}][validity_from]"]`);
                const toInput = document.querySelector(`input[name="upkj[${currentIndex}][validity_to]"]`);
                
                if (fromInput) fromInput.value = fromDate;
                if (toInput) toInput.value = toDate;
            }
        }
        
        const bumiStatusSelect = document.querySelector(`select[name="upkj[${currentIndex}][bumiputera_status]"]`);
        if (bumiStatusSelect && record.bumiputera_status) {
            // Capitalize first letter to match dropdown options (Yes/No)
            const capitalizedValue = record.bumiputera_status.charAt(0).toUpperCase() + record.bumiputera_status.slice(1).toLowerCase();
            bumiStatusSelect.value = capitalizedValue;
        }
        
        const bumiValidityInput = document.querySelector(`input[name="upkj[${currentIndex}][bumiputera_validity]"]`);
        if (bumiValidityInput && record.bumiputera_validity) {
            bumiValidityInput.value = record.bumiputera_validity;
            
            // Split and populate date fields
            if (record.bumiputera_validity.includes(' - ')) {
                const dates = record.bumiputera_validity.split(' - ');
                const fromDate = convertDDMMYYYYtoYYYYMMDD(dates[0].trim());
                const toDate = convertDDMMYYYYtoYYYYMMDD(dates[1].trim());
                
                const fromInput = document.querySelector(`input[name="upkj[${currentIndex}][bumiputera_from]"]`);
                const toInput = document.querySelector(`input[name="upkj[${currentIndex}][bumiputera_to]"]`);
                
                if (fromInput) fromInput.value = fromDate;
                if (toInput) toInput.value = toDate;
            }
        }
        
        const certificateInput = document.querySelector(`input[name="upkj[${currentIndex}][certificate_no]"]`);
        if (certificateInput && record.certificate_no) {
            certificateInput.value = record.certificate_no;
        }
        
        // Load classifications into mini-table
        if (record.classifications && Array.isArray(record.classifications)) {
            record.classifications.forEach(classification => {
                addClassificationToMiniTable(currentIndex, classification);
            });
        } else if (record.classifications && typeof record.classifications === 'object') {
            // Handle old input format (object instead of array)
            Object.values(record.classifications).forEach(classification => {
                addClassificationToMiniTable(currentIndex, classification);
            });
        }
    });
}

// ============================================================================
// Load Existing Shareholders and Directors Data on Page Load
// ============================================================================

/**
 * Load existing shareholders data into the table
 */
function loadExistingShareholders() {
    if (!existingShareholders || existingShareholders.length === 0) {
        return;
    }
    
    const tableBody = document.getElementById('shareholdersTableBody');
    
    existingShareholders.forEach((shareholder, index) => {
        // Remove "No shareholders" message if exists
        if (tableBody.querySelector('td[colspan="5"]')) {
            tableBody.innerHTML = '';
        }
        
        shareholderCount++;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td style="text-align: center;">${shareholderCount}</td>
            <td><input type="text" name="shareholders[${shareholderCount}][name]" value="${shareholder.name || ''}" placeholder="Enter company name" style="width: 100%;"></td>
            <td><input type="text" name="shareholders[${shareholderCount}][registration_no]" value="${shareholder.registration_no || ''}" placeholder="e.g., SA20180908" style="width: 100%;"></td>
            <td><input type="number" name="shareholders[${shareholderCount}][shares]" value="${shareholder.shares || 0}" min="0" max="100" step="0.01" placeholder="0.00" style="width: 100%;"></td>
            <td style="text-align: center;">
                <span class="material-symbols-outlined btn-delete" onclick="this.closest('tr').remove(); reindexShareholderRows();" style="font-size: 16px;">delete</span>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

/**
 * Load existing directors data into the table
 */
function loadExistingDirectors() {
    if (!existingDirectors || existingDirectors.length === 0) {
        return;
    }
    
    const tableBody = document.getElementById('directorsTableBody');
    
    existingDirectors.forEach((director, index) => {
        // Remove "No directors" message if exists
        if (tableBody.querySelector('td[colspan="5"]')) {
            tableBody.innerHTML = '';
        }
        
        directorCount++;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td style="text-align: center;">${directorCount}</td>
            <td><input type="text" name="directors[${directorCount}][name]" value="${director.name || ''}" placeholder="e.g., AHMAD ISHAMMUDIN BIN DAHRI" style="width: 100%;"></td>
            <td><input type="text" name="directors[${directorCount}][ic_number]" value="${director.ic_number || ''}" placeholder="e.g., 890415-13-6231" style="width: 100%;"></td>
            <td><input type="number" name="directors[${directorCount}][shares]" value="${director.shares || 0}" min="0" max="100" step="0.01" placeholder="0.00" style="width: 100%;"></td>
            <td style="text-align: center;">
                <span class="material-symbols-outlined btn-delete" onclick="this.closest('tr').remove(); reindexDirectorRows();" style="font-size: 16px;">delete</span>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

/**
 * Convert DD/MM/YYYY to YYYY-MM-DD for date input
 */
function convertDDMMYYYYtoYYYYMMDD(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.split('/');
    if (parts.length !== 3) return '';
    return `${parts[2]}-${parts[1]}-${parts[0]}`;
}

/**
 * Update validity period from date inputs
 */
function updateValidityPeriod(index) {
    const fromInput = document.querySelector(`input[name="upkj[${index}][validity_from]"]`);
    const toInput = document.querySelector(`input[name="upkj[${index}][validity_to]"]`);
    const hiddenInput = document.querySelector(`input[name="upkj[${index}][validity_period]"]`);
    
    if (fromInput && toInput && hiddenInput) {
        const fromDate = fromInput.value;
        const toDate = toInput.value;
        
        if (fromDate && toDate) {
            // Convert YYYY-MM-DD to DD/MM/YYYY
            const fromParts = fromDate.split('-');
            const toParts = toDate.split('-');
            const formatted = `${fromParts[2]}/${fromParts[1]}/${fromParts[0]} - ${toParts[2]}/${toParts[1]}/${toParts[0]}`;
            hiddenInput.value = formatted;
        }
    }
}

/**
 * Update bumiputera validity from date inputs
 */
function updateBumiValidity(index) {
    const fromInput = document.querySelector(`input[name="upkj[${index}][bumiputera_from]"]`);
    const toInput = document.querySelector(`input[name="upkj[${index}][bumiputera_to]"]`);
    const hiddenInput = document.querySelector(`input[name="upkj[${index}][bumiputera_validity]"]`);
    
    if (fromInput && toInput && hiddenInput) {
        const fromDate = fromInput.value;
        const toDate = toInput.value;
        
        if (fromDate && toDate) {
            // Convert YYYY-MM-DD to DD/MM/YYYY
            const fromParts = fromDate.split('-');
            const toParts = toDate.split('-');
            const formatted = `${fromParts[2]}/${fromParts[1]}/${fromParts[0]} - ${toParts[2]}/${toParts[1]}/${toParts[0]}`;
            hiddenInput.value = formatted;
        }
    }
}
</script>
@endsection
