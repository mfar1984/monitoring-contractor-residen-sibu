@extends('layouts.app')

@section('title', 'Create Contractor Selection - Monitoring System')

@push('styles')
<style>
    .upkj-filter-section {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-top: 24px;
    }
    
    .filter-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .contractor-list-section {
        margin-top: 24px;
        display: none;
    }
    
    .contractor-list-section.show {
        display: block;
    }
    
    .contractor-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        font-size: 11px;
        background: white;
    }
    
    .contractor-table th {
        background-color: #f8f9fa;
        padding: 12px 10px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border: 1px solid #dee2e6;
    }
    
    .contractor-table td {
        padding: 10px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    .contractor-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .contractor-table input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #007bff;
    }
    
    .expired-contractor {
        background-color: #ffe6e6 !important;
        opacity: 0.7;
    }
    
    .expired-contractor input[type="checkbox"]:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
    
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-left: 8px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .selection-summary {
        background: #e7f3ff;
        border: 1px solid #007bff;
        border-radius: 4px;
        padding: 12px 16px;
        margin-top: 15px;
        font-size: 12px;
        color: #004085;
        font-weight: 600;
    }
</style>
@endpush

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Drawing Lots</span>
    <span class="breadcrumb-separator">›</span>
    <span>Contractor Selections</span>
    <span class="breadcrumb-separator">›</span>
    <span>Create Selection</span>
@endsection

@section('content')
    @if($errors->any())
    <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin-bottom: 15px;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
        <div class="content-header" style="margin-bottom: 24px;">
            <div class="content-header-left">
                <h3>Create Contractor Selection</h3>
                <p class="content-description">Generate contractor selection list with fingerprint (proof of data at specific date)</p>
            </div>
        </div>
        
        <div style="border-top: 1px solid #e0e0e0; margin-bottom: 24px;"></div>

        <form method="POST" action="{{ route('pages.contractor-selections.store') }}" id="selectionForm">
            @csrf

            <!-- UPKJ Filter Section -->
            <div class="upkj-filter-section">
                <h4 style="font-size: 13px; font-weight: 600; color: #333; margin-bottom: 12px;">
                    UPKJ Classification Filter <span style="color: #dc3545;">*</span>
                </h4>
                <p style="font-size: 11px; color: #666; margin-bottom: 15px;">
                    Select UPKJ classifications using cascade dropdown. Hold Ctrl/Cmd to select multiple values.
                </p>

                <div class="filter-row">
                    <!-- Category Filter -->
                    <div class="form-group">
                        <label for="upkj_categories">Category</label>
                        <select id="upkj_categories" name="upkj_categories[]" multiple size="5" onchange="onCategoryChange()" style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px; font-size: 12px;">
                            @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                            @endforeach
                        </select>
                        <span class="form-help" style="font-size: 11px; color: #666; margin-top: 5px; display: block;">Hold Ctrl/Cmd to select multiple</span>
                    </div>

                    <!-- Class Filter -->
                    <div class="form-group">
                        <label for="upkj_classes">Class</label>
                        <select id="upkj_classes" name="upkj_classes[]" multiple size="5" onchange="onClassChange()" disabled style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px; font-size: 12px;">
                        </select>
                        <span class="form-help" style="font-size: 11px; color: #666; margin-top: 5px; display: block;">Hold Ctrl/Cmd to select multiple</span>
                    </div>
                </div>

                <div class="filter-row">
                    <!-- Head Filter -->
                    <div class="form-group">
                        <label for="upkj_heads">Head</label>
                        <select id="upkj_heads" name="upkj_heads[]" multiple size="5" onchange="onHeadChange()" disabled style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px; font-size: 12px;">
                        </select>
                        <span class="form-help" style="font-size: 11px; color: #666; margin-top: 5px; display: block;">Hold Ctrl/Cmd to select multiple</span>
                    </div>

                    <!-- Subhead Filter -->
                    <div class="form-group">
                        <label for="upkj_subheads">Subhead (Optional)</label>
                        <select id="upkj_subheads" name="upkj_subheads[]" multiple size="5" disabled style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px; font-size: 12px;">
                        </select>
                        <span class="form-help" style="font-size: 11px; color: #666; margin-top: 5px; display: block;">Hold Ctrl/Cmd to select multiple</span>
                    </div>
                </div>

                <!-- Division and District Filters (NEW) -->
                <div class="filter-row">
                    <!-- Division Filter -->
                    <div class="form-group">
                        <label for="division_id">Division (Optional)</label>
                        <select id="division_id" name="division_id" onchange="onDivisionChange()" style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px; font-size: 12px;">
                            <option value="">All Divisions</option>
                            @foreach($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- District Filter -->
                    <div class="form-group">
                        <label for="district_id">District (Optional)</label>
                        <select id="district_id" name="district_id" disabled style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px; font-size: 12px;">
                            <option value="">All Districts</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 15px;">
                    <button type="button" id="applyFilterBtn" onclick="applyFilter()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
                        <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 16px;">filter_list</span>
                        Apply Filter
                        <span id="loadingSpinner" class="loading-spinner" style="display: none;"></span>
                    </button>
                    <button type="button" onclick="resetFilter()" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; margin-left: 10px;">
                        <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 16px;">refresh</span>
                        Reset Filter
                    </button>
                </div>
            </div>

            <!-- Contractor List Section -->
            <div class="contractor-list-section" id="contractorListSection">
                <h4 style="font-size: 13px; font-weight: 600; color: #333; margin-bottom: 12px;">
                    Select Contractors <span style="color: #dc3545;">*</span>
                </h4>
                <p style="font-size: 11px; color: #666; margin-bottom: 15px;">
                    Select contractors to include in this selection. This will create a fingerprint (snapshot) of contractor data at current date/time.
                </p>

                <div id="contractorTableContainer">
                    <!-- Contractor table will be populated here -->
                </div>

                <div id="contractorSelectionSummary" class="selection-summary" style="display: none;">
                    <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 16px;">check_circle</span>
                    <span id="selectedContractorCount">0</span> contractor(s) selected
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button 
                    type="submit" 
                    id="submitButton" 
                    disabled
                    style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"
                >
                    <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 16px;">save</span>
                    Create Selection
                </button>
                <a 
                    href="{{ route('pages.contractor-selections') }}" 
                    style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; text-decoration: none; display: inline-block;"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        // UPKJ Classifications data
        const upkjData = @json($upkjClassifications);
        
        // Districts data (for filtering by division)
        const allDistricts = @json($districts);
        
        // Cascade dropdown for UPKJ
        function onCategoryChange() {
            const categoriesSelect = document.getElementById('upkj_categories');
            const classesSelect = document.getElementById('upkj_classes');
            const headsSelect = document.getElementById('upkj_heads');
            const subheadsSelect = document.getElementById('upkj_subheads');
            
            const selectedCategories = Array.from(categoriesSelect.selectedOptions).map(opt => opt.value);
            
            // Reset dependent dropdowns
            classesSelect.innerHTML = '';
            headsSelect.innerHTML = '';
            subheadsSelect.innerHTML = '';
            classesSelect.disabled = true;
            headsSelect.disabled = true;
            subheadsSelect.disabled = true;
            
            if (selectedCategories.length === 0) return;
            
            // Get unique classes for selected categories
            // CRITICAL: Use category + class as key to avoid overwriting duplicate class names
            const classesMap = new Map();
            upkjData.forEach(item => {
                if (selectedCategories.includes(item.category) && item.class) {
                    // Use category-class combination as unique key
                    const key = item.category + '-' + item.class;
                    if (!classesMap.has(key)) {
                        classesMap.set(key, {
                            class: item.class,
                            category: item.category,
                            class_description: item.class_description || ''
                        });
                    }
                }
            });
            
            // Populate classes dropdown with category prefix
            classesMap.forEach(item => {
                const option = document.createElement('option');
                option.value = item.class;
                option.textContent = item.class + ' - ' + item.category + (item.class_description ? ' - ' + item.class_description : '');
                classesSelect.appendChild(option);
            });
            
            classesSelect.disabled = false;
        }
        
        async function onClassChange() {
            const categoriesSelect = document.getElementById('upkj_categories');
            const classesSelect = document.getElementById('upkj_classes');
            const headsSelect = document.getElementById('upkj_heads');
            const subheadsSelect = document.getElementById('upkj_subheads');
            
            const selectedCategories = Array.from(categoriesSelect.selectedOptions).map(opt => opt.value);
            const selectedClasses = Array.from(classesSelect.selectedOptions).map(opt => opt.value);
            
            // Reset dependent dropdowns
            headsSelect.innerHTML = '';
            subheadsSelect.innerHTML = '';
            headsSelect.disabled = true;
            subheadsSelect.disabled = true;
            
            if (selectedClasses.length === 0) return;
            
            headsSelect.innerHTML = '<option>Loading...</option>';
            
            try {
                const response = await fetch('/api/upkj/available-heads', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        categories: selectedCategories,
                        classes: selectedClasses
                    })
                });
                
                const heads = await response.json();
                headsSelect.innerHTML = '';
                
                if (heads.length === 0) {
                    headsSelect.innerHTML = '<option>No heads available</option>';
                } else {
                    heads.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.head_code;
                        // Display with class prefix: "Class II - VIIA - Electrical - Electrical Works (Building)"
                        option.textContent = item.display_name;
                        // Store class and category as data attributes for filtering
                        option.setAttribute('data-class', item.class);
                        option.setAttribute('data-category', item.category);
                        headsSelect.appendChild(option);
                    });
                    headsSelect.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                headsSelect.innerHTML = '<option>Error loading heads</option>';
            }
        }
        
        async function onHeadChange() {
            const categoriesSelect = document.getElementById('upkj_categories');
            const classesSelect = document.getElementById('upkj_classes');
            const headsSelect = document.getElementById('upkj_heads');
            const subheadsSelect = document.getElementById('upkj_subheads');
            
            const selectedCategories = Array.from(categoriesSelect.selectedOptions).map(opt => opt.value);
            const selectedHeads = Array.from(headsSelect.selectedOptions).map(opt => opt.value);
            
            // CRITICAL: Get classes from selected heads only (not from class dropdown)
            const selectedHeadClasses = Array.from(headsSelect.selectedOptions).map(opt => opt.getAttribute('data-class'));
            const uniqueHeadClasses = [...new Set(selectedHeadClasses)]; // Remove duplicates
            
            subheadsSelect.innerHTML = '';
            subheadsSelect.disabled = true;
            
            if (selectedHeads.length === 0) return;
            
            subheadsSelect.innerHTML = '<option>Loading...</option>';
            
            try {
                const response = await fetch('/api/upkj/available-subheads', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        categories: selectedCategories,
                        classes: uniqueHeadClasses, // Use classes from selected heads only
                        heads: selectedHeads
                    })
                });
                
                const subheads = await response.json();
                subheadsSelect.innerHTML = '';
                
                if (subheads.length === 0) {
                    subheadsSelect.innerHTML = '<option>No subheads available</option>';
                } else {
                    subheads.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.subhead_value;
                        // Display with class and head prefix: "Class I - VIIA - 1 - Building Electrical Works"
                        const displayText = 'Class ' + item.class + ' - ' + item.head_code + ' - ' + item.subhead_value + (item.description ? ' - ' + item.description : '');
                        option.textContent = displayText;
                        // Store class, category, and head as data attributes
                        option.setAttribute('data-class', item.class);
                        option.setAttribute('data-category', item.category);
                        option.setAttribute('data-head', item.head_code);
                        subheadsSelect.appendChild(option);
                    });
                    subheadsSelect.disabled = false;
                }
            } catch (error) {
                console.error('Error:', error);
                subheadsSelect.innerHTML = '<option>Error loading subheads</option>';
            }
        }
        
        // Division/District cascade
        function onDivisionChange() {
            const divisionSelect = document.getElementById('division_id');
            const districtSelect = document.getElementById('district_id');
            
            const selectedDivision = divisionSelect.value;
            
            // Reset district dropdown
            districtSelect.innerHTML = '<option value="">All Districts</option>';
            districtSelect.disabled = true;
            
            if (!selectedDivision) return;
            
            // Filter districts by division
            const filteredDistricts = allDistricts.filter(d => d.division_id == selectedDivision);
            
            if (filteredDistricts.length > 0) {
                filteredDistricts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;
                    districtSelect.appendChild(option);
                });
                districtSelect.disabled = false;
            }
        }
        
        // Apply filter and fetch contractors
        async function applyFilter() {
            const categoriesSelect = document.getElementById('upkj_categories');
            const classesSelect = document.getElementById('upkj_classes');
            const headsSelect = document.getElementById('upkj_heads');
            const subheadsSelect = document.getElementById('upkj_subheads');
            const divisionSelect = document.getElementById('division_id');
            const districtSelect = document.getElementById('district_id');
            
            const categories = Array.from(categoriesSelect.selectedOptions).map(opt => opt.value);
            const classes = Array.from(classesSelect.selectedOptions).map(opt => opt.value);
            const heads = Array.from(headsSelect.selectedOptions).map(opt => opt.value);
            const subheads = Array.from(subheadsSelect.selectedOptions).map(opt => opt.value);
            const divisionId = divisionSelect.value;
            const districtId = districtSelect.value;
            
            // Validate at least one UPKJ filter
            if (categories.length === 0 && classes.length === 0 && heads.length === 0 && subheads.length === 0) {
                alert('Please select at least one UPKJ classification filter.');
                return;
            }
            
            // Show loading
            const applyBtn = document.getElementById('applyFilterBtn');
            const spinner = document.getElementById('loadingSpinner');
            applyBtn.disabled = true;
            spinner.style.display = 'inline-block';
            
            try {
                const response = await fetch('/api/contractors/filter-by-upkj', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        categories: categories,
                        classes: classes,
                        heads: heads,
                        subheads: subheads,
                        division_id: divisionId,
                        district_id: districtId
                    })
                });
                
                const contractors = await response.json();
                displayContractors(contractors);
                
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to fetch contractors. Please try again.');
            } finally {
                applyBtn.disabled = false;
                spinner.style.display = 'none';
            }
        }
        
        // Display contractors in table
        function displayContractors(contractors) {
            const container = document.getElementById('contractorTableContainer');
            const section = document.getElementById('contractorListSection');
            
            if (contractors.length === 0) {
                container.innerHTML = '<div style="padding: 20px; text-align: center; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; color: #666;">No contractors found matching the selected filters.</div>';
                section.classList.add('show');
                return;
            }
            
            let tableHTML = `
                <table class="contractor-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">
                                <input type="checkbox" 
                                       id="selectAllCheckbox" 
                                       onchange="toggleSelectAll()"
                                       style="width: 18px; height: 18px; cursor: pointer; accent-color: #007bff;">
                            </th>
                            <th>Company Name</th>
                            <th>Registration No</th>
                            <th>Category</th>
                            <th>UPKJ Class</th>
                            <th>UPKJ Head</th>
                            <th>UPKJ Subhead</th>
                            <th>UPK Expiry</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            contractors.forEach(contractor => {
                const isExpired = contractor.is_expired;
                const rowClass = isExpired ? 'expired-contractor' : '';
                const disabled = isExpired ? 'disabled' : '';
                
                tableHTML += `
                    <tr class="${rowClass}">
                        <td style="text-align: center;">
                            <input type="checkbox" 
                                   name="contractor_ids[]" 
                                   value="${contractor.id}" 
                                   class="contractor-checkbox"
                                   ${disabled}
                                   onchange="updateContractorSelection()">
                        </td>
                        <td>${contractor.company_name || '-'}</td>
                        <td>${contractor.registration_number || '-'}</td>
                        <td>${contractor.upkj_category || '-'}</td>
                        <td>${contractor.upkj_class || '-'}</td>
                        <td>${contractor.upkj_head || '-'}</td>
                        <td>${contractor.upkj_subhead || '-'}</td>
                        <td>${contractor.upk_expiry_date || '-'}</td>
                        <td>
                            <span class="status-badge ${contractor.status === 'Active' ? 'status-active' : ''}" style="${contractor.status !== 'Active' ? 'background-color: #6c757d; color: white;' : ''}">
                                ${contractor.status}
                            </span>
                            ${isExpired ? '<br><span style="color: #dc3545; font-size: 10px;">EXPIRED</span>' : ''}
                        </td>
                    </tr>
                `;
            });
            
            tableHTML += `
                    </tbody>
                </table>
            `;
            
            container.innerHTML = tableHTML;
            section.classList.add('show');
            updateContractorSelection();
        }
        
        // Toggle select all contractors
        function toggleSelectAll() {
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const contractorCheckboxes = document.querySelectorAll('.contractor-checkbox:not([disabled])');
            
            contractorCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
            
            updateContractorSelection();
        }
        
        // Update contractor selection summary
        function updateContractorSelection() {
            const selectedCount = document.querySelectorAll('.contractor-checkbox:checked:not([disabled])').length;
            const totalCount = document.querySelectorAll('.contractor-checkbox:not([disabled])').length;
            const summary = document.getElementById('contractorSelectionSummary');
            const countSpan = document.getElementById('selectedContractorCount');
            const submitBtn = document.getElementById('submitButton');
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            
            countSpan.textContent = selectedCount;
            
            // Update Select All checkbox state
            if (selectAllCheckbox) {
                if (selectedCount === 0) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                } else if (selectedCount === totalCount) {
                    selectAllCheckbox.checked = true;
                    selectAllCheckbox.indeterminate = false;
                } else {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = true;
                }
            }
            
            if (selectedCount > 0) {
                summary.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            } else {
                summary.style.display = 'none';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
                submitBtn.style.cursor = 'not-allowed';
            }
        }
        
        // Reset filter
        function resetFilter() {
            document.getElementById('upkj_categories').selectedIndex = -1;
            document.getElementById('upkj_classes').innerHTML = '';
            document.getElementById('upkj_heads').innerHTML = '';
            document.getElementById('upkj_subheads').innerHTML = '';
            document.getElementById('upkj_classes').disabled = true;
            document.getElementById('upkj_heads').disabled = true;
            document.getElementById('upkj_subheads').disabled = true;
            
            document.getElementById('division_id').value = '';
            document.getElementById('district_id').innerHTML = '<option value="">All Districts</option>';
            document.getElementById('district_id').disabled = true;
            
            document.getElementById('contractorListSection').classList.remove('show');
            document.getElementById('contractorTableContainer').innerHTML = '';
        }
    </script>
@endsection
