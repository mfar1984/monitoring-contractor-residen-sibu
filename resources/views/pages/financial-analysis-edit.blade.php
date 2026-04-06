@extends('layouts.app')

@section('title', 'Edit Financial Analysis - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Project</span>
    <span class="breadcrumb-separator">›</span>
    <a href="{{ route('pages.financial-analysis') }}" style="color: inherit; text-decoration: none;">Financial Analysis</a>
    <span class="breadcrumb-separator">›</span>
    <span>Edit</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-content">
            @if(session('success'))
            <div id="successMessage" style="padding: 10px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px;">
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

            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin: 0 0 20px 0; font-size: 14px; font-weight: 600;">Edit Financial Analysis</h2>

                <form method="POST" action="{{ route('pages.financial-analysis.update', $analysis->id) }}" id="analysisForm">
                    @csrf
                    @method('PUT')

                    <!-- Project Information Section (Read-only) -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #333;">Project Information</h3>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; border: 1px solid #e0e0e0;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
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
                            </div>
                        </div>
                    </div>

                    <!-- Contractor Evaluations -->
                    <div style="margin-bottom: 30px;">
                        <h3 style="font-size: 13px; font-weight: 600; margin-bottom: 15px; color: #333;">
                            Contractor Financial Evaluations ({{ $analysis->contractors->count() }} contractors)
                        </h3>
                        
                        @foreach($analysis->contractors as $contractor)
                        @php
                            // Check if contractor data is complete
                            $requiredFields = [
                                'registration_validity_date',
                                'current_contract_load',
                                'minimum_capital_requirement',
                                'fixed_deposit',
                                'credit_facility_balance',
                                'performance_record',
                                'meeting_decision',
                                'is_qualified',
                                'justification'
                            ];
                            
                            $missingFields = 0;
                            foreach ($requiredFields as $field) {
                                if (empty($contractor->$field) && $contractor->$field !== 0 && $contractor->$field !== false) {
                                    $missingFields++;
                                }
                            }
                            
                            // Check bank statements
                            $emptyBankStatements = $contractor->bankStatements->filter(function($stmt) {
                                return empty($stmt->ending_balance) && $stmt->ending_balance !== 0;
                            })->count();
                            
                            $missingFields += $emptyBankStatements;
                            
                            $isComplete = $missingFields === 0;
                        @endphp
                        
                        <div class="accordion-item" style="margin-bottom: 10px; border: 1px solid #e0e0e0; border-radius: 4px; overflow: hidden;">
                            <!-- Accordion Header -->
                            <div class="accordion-header" onclick="toggleAccordion({{ $loop->index }})" 
                                 style="background: {{ $isComplete ? '#f8f9fa' : '#fff3cd' }}; padding: 15px 20px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: background 0.2s;">
                                <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                                    <!-- Expand/Collapse Icon -->
                                    <span class="accordion-icon" id="icon-{{ $loop->index }}" style="font-size: 20px; color: #666; transition: transform 0.3s;">
                                        ▶
                                    </span>
                                    
                                    <!-- Contractor Info -->
                                    <div style="flex: 1;">
                                        <div style="font-size: 12px; font-weight: 600; color: #333; margin-bottom: 5px;">
                                            {{ $loop->iteration }}. {{ $contractor->contractor_name }}
                                        </div>
                                        <div style="font-size: 11px; color: #666; display: flex; gap: 20px;">
                                            <span>Reg: {{ $contractor->registration_number }}</span>
                                            <span>Class: {{ $contractor->contractor_class }}</span>
                                            <span>UPKJ: {{ $contractor->upkj_classifications }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Status Badge -->
                                    @if($isComplete)
                                        <span style="background: #28a745; color: white; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                            ✓ Complete
                                        </span>
                                    @else
                                        <span style="background: #dc3545; color: white; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: 500;">
                                            ⚠ {{ $missingFields }} field(s) missing
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Accordion Content -->
                            <div class="accordion-content" id="content-{{ $loop->index }}" 
                                 style="display: {{ $loop->first ? 'block' : 'none' }}; padding: 20px; background: white;">
                                <input type="hidden" name="contractors[{{ $loop->index }}][id]" value="{{ $contractor->id }}">

                                <!-- Financial Data Grid -->
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                    <!-- Registration Validity -->
                                    <div class="form-group">
                                        <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                            Registration Validity Date <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="date" name="contractors[{{ $loop->index }}][registration_validity_date]" 
                                               value="{{ $contractor->registration_validity_date?->format('Y-m-d') }}"
                                               style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                                    </div>

                                    <!-- Current Contract Load -->
                                    <div class="form-group">
                                        <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                            Current Contract Load (RM) <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="number" step="0.01" name="contractors[{{ $loop->index }}][current_contract_load]" 
                                               value="{{ $contractor->current_contract_load }}"
                                               style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                                    </div>

                                    <!-- Minimum Capital -->
                                    <div class="form-group">
                                        <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                            Minimum Capital Requirement (RM) <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="number" step="0.01" name="contractors[{{ $loop->index }}][minimum_capital_requirement]" 
                                               value="{{ $contractor->minimum_capital_requirement }}"
                                               style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                                    </div>

                                    <!-- Fixed Deposit -->
                                    <div class="form-group">
                                        <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                            Fixed Deposit (RM) <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="number" step="0.01" name="contractors[{{ $loop->index }}][fixed_deposit]" 
                                               value="{{ $contractor->fixed_deposit }}"
                                               style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                                    </div>

                                    <!-- Credit Facility Balance -->
                                    <div class="form-group">
                                        <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                            Credit Facility Balance (RM) <span style="color: #dc3545;">*</span>
                                        </label>
                                        <input type="number" step="0.01" name="contractors[{{ $loop->index }}][credit_facility_balance]" 
                                               value="{{ $contractor->credit_facility_balance }}"
                                               style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                                    </div>

                                    <!-- Additional Credit Facility -->
                                    <div class="form-group">
                                        <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                            Additional Credit Facility (RM)
                                        </label>
                                        <input type="number" step="0.01" name="contractors[{{ $loop->index }}][additional_credit_facility]" 
                                               value="{{ $contractor->additional_credit_facility }}"
                                               style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                                    </div>
                                </div>

                                <!-- Bank Statements -->
                                <div style="margin-top: 15px;">
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 10px; font-weight: 600;">
                                        Bank Statements (Last 5 Months) <span style="color: #dc3545;">*</span>
                                    </label>
                                    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px;">
                                        @foreach($contractor->bankStatements as $statement)
                                        <div>
                                            <input type="hidden" name="contractors[{{ $loop->parent->index }}][bank_statements][{{ $loop->index }}][id]" value="{{ $statement->id }}">
                                            <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">{{ $statement->month_year }}</label>
                                            <input type="number" step="0.01" 
                                                   name="contractors[{{ $loop->parent->index }}][bank_statements][{{ $loop->index }}][ending_balance]" 
                                                   value="{{ $statement->ending_balance }}"
                                                   class="bank-statement-input" 
                                                   data-contractor-index="{{ $loop->parent->index }}"
                                                   onchange="calculateAverage({{ $loop->parent->index }})"
                                                   style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px;">
                                        </div>
                                        @endforeach
                                    </div>
                                    <div style="margin-top: 10px; padding: 10px; background: #e3f2fd; border-radius: 4px;">
                                        <span style="font-size: 11px; color: #666;">3-Month Average:</span>
                                        <span id="average-{{ $loop->index }}" style="font-size: 12px; font-weight: 600; color: #007bff;">
                                            RM {{ number_format($contractor->three_month_average ?? 0, 2) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Performance Record -->
                                <div class="form-group" style="margin-top: 15px;">
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                        Performance Record <span style="color: #dc3545;">*</span>
                                    </label>
                                    <textarea name="contractors[{{ $loop->index }}][performance_record]" rows="2"
                                              style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px; resize: vertical;">{{ $contractor->performance_record }}</textarea>
                                </div>

                                <!-- Meeting Decision -->
                                <div class="form-group" style="margin-top: 15px;">
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                        Meeting Decision <span style="color: #dc3545;">*</span>
                                    </label>
                                    <textarea name="contractors[{{ $loop->index }}][meeting_decision]" rows="2"
                                              style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px; resize: vertical;">{{ $contractor->meeting_decision }}</textarea>
                                </div>

                                <!-- Qualified Status -->
                                <div style="margin-top: 15px;">
                                    <label style="font-size: 11px; color: #666; font-weight: 600; display: block; margin-bottom: 10px;">
                                        Qualified Status <span style="color: #dc3545;">*</span>
                                    </label>
                                    <div style="display: flex; gap: 20px;">
                                        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                                            <input type="radio" name="contractors[{{ $loop->index }}][is_qualified]" value="1" 
                                                   {{ $contractor->is_qualified === true ? 'checked' : '' }}
                                                   style="width: 18px; height: 18px;">
                                            <span style="font-size: 12px;">Qualified</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 5px; cursor: pointer;">
                                            <input type="radio" name="contractors[{{ $loop->index }}][is_qualified]" value="0" 
                                                   {{ $contractor->is_qualified === false ? 'checked' : '' }}
                                                   style="width: 18px; height: 18px;">
                                            <span style="font-size: 12px;">Not Qualified</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Justification -->
                                <div class="form-group" style="margin-top: 15px;">
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                        Justification <span style="color: #dc3545;">*</span>
                                    </label>
                                    <textarea name="contractors[{{ $loop->index }}][justification]" rows="2"
                                              style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px; resize: vertical;">{{ $contractor->justification }}</textarea>
                                </div>

                                <!-- Remarks -->
                                <div class="form-group" style="margin-top: 15px;">
                                    <label style="font-size: 11px; color: #666; display: block; margin-bottom: 5px;">
                                        Remarks
                                    </label>
                                    <textarea name="contractors[{{ $loop->index }}][remarks]" rows="2"
                                              style="width: 100%; padding: 8px 12px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 12px; resize: vertical;">{{ $contractor->remarks }}</textarea>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid #e0e0e0;">
                        <button type="button" onclick="window.location.href='{{ route('pages.financial-analysis.show', $analysis->id) }}'" class="btn btn-secondary">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span class="material-symbols-outlined" style="font-size: 16px;">save</span>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleAccordion(index) {
            const content = document.getElementById(`content-${index}`);
            const icon = document.getElementById(`icon-${index}`);
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.style.transform = 'rotate(90deg)';
            } else {
                content.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
            }
        }
        
        // Initialize first accordion as open
        document.addEventListener('DOMContentLoaded', function() {
            const firstIcon = document.getElementById('icon-0');
            if (firstIcon) {
                firstIcon.style.transform = 'rotate(90deg)';
            }
        });
        
        function calculateAverage(contractorIndex) {
            const inputs = document.querySelectorAll(`.bank-statement-input[data-contractor-index="${contractorIndex}"]`);
            const values = Array.from(inputs)
                .map(input => parseFloat(input.value) || 0)
                .filter(val => val !== 0);
            
            if (values.length === 0) {
                document.getElementById(`average-${contractorIndex}`).textContent = 'RM 0.00';
                return;
            }
            
            // Get last 3 values
            const last3 = values.slice(-3);
            const average = last3.reduce((sum, val) => sum + val, 0) / last3.length;
            
            document.getElementById(`average-${contractorIndex}`).textContent = 
                'RM ' + average.toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
