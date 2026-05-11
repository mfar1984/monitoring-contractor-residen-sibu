@extends('layouts.app')

@section('title', 'Create Contractor Analysis Transfer - Monitoring System')

@push('styles')
<style>
    .project-selection-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 11px;
        background: white;
    }
    
    .project-selection-table th {
        background-color: #f8f9fa;
        padding: 12px 10px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border: 1px solid #dee2e6;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .project-selection-table td {
        padding: 10px;
        border: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    .project-selection-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .project-selection-table input[type="radio"],
    .project-selection-table input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #007bff;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-active {
        background-color: #d4edda;
        color: #155724;
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
    
    .empty-state {
        padding: 40px 20px;
        text-align: center;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        color: #666;
        font-size: 12px;
    }
    
    .upkj-description {
        font-size: 11px;
        color: #666;
        font-style: italic;
        margin-top: 3px;
    }
    
    .expired-contractor {
        background-color: #ffe6e6 !important;
        opacity: 0.7;
    }
    
    .expired-contractor input[type="checkbox"]:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
    
    .contractor-table-wrapper {
        overflow-x: auto;
        display: block;
        width: 100%;
        margin-top: 15px;
    }
    
    .contractor-table-wrapper .project-selection-table {
        min-width: 1800px;
        width: 100%;
    }
    
    .expired-contractor {
        background-color: #ffe6e6 !important;
    }
    
    .expired-contractor td {
        opacity: 0.7;
    }
</style>
@endpush

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Contractor Analysis</span>
    <span class="breadcrumb-separator">›</span>
    <span>Create Transfer</span>
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
                <h3>Create Contractor Analysis Transfer</h3>
                <p class="content-description">Transfer a project for contractor analysis and selection based on UPKJ classifications</p>
            </div>
        </div>
        
        <div style="border-top: 1px solid #e0e0e0; margin-bottom: 24px;"></div>

        <form method="POST" action="{{ route('pages.contractor-analysis.store') }}" id="transferForm">
            @csrf

            <!-- Project Selection Section -->
            <div style="margin-bottom: 24px;">
                <h4 style="font-size: 13px; font-weight: 600; color: #333; margin-bottom: 12px;">
                    Select Project <span style="color: #dc3545;">*</span>
                </h4>
                <p style="font-size: 11px; color: #666; margin-bottom: 15px;">
                    Select ONE project to transfer for contractor analysis. Only Active projects are available for transfer.
                </p>

                @if($availableProjects->count() > 0)
                <table class="project-selection-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">Select</th>
                            <th>Project Number</th>
                            <th>Project Name</th>
                            <th>Agency</th>
                            <th>Total Cost (RM)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($availableProjects as $project)
                        <tr>
                            <td style="text-align: center;">
                                <input type="radio" 
                                       name="project_id" 
                                       value="{{ $project->id }}" 
                                       class="project-radio"
                                       onchange="updateProjectSelection()">
                            </td>
                            <td>{{ $project->project_number }}</td>
                            <td>{{ $project->name }}</td>
                            <td>{{ $project->agencyCategory?->name ?? '-' }}</td>
                            <td style="text-align: right; font-weight: 600;">{{ number_format($project->total_cost, 2) }}</td>
                            <td>
                                <span class="status-badge status-active">{{ $project->status }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div id="projectSelectionSummary" class="selection-summary" style="display: none;">
                    <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 16px;">check_circle</span>
                    <span id="selectedProjectName">No project selected</span>
                </div>
                @else
                <div style="padding: 20px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; text-align: center;">
                    <p style="margin: 0; color: #856404; font-size: 12px;">
                        No projects available for transfer. All projects may already be in analysis or have been transferred.
                    </p>
                </div>
                @endif
            </div>

            <!-- UPKJ Filter Section (Cascade Multi-Select) -->
            <div class="upkj-filter-section">
                <h4 style="font-size: 13px; font-weight: 600; color: #333; margin-bottom: 12px;">
                    UPKJ Classification Filter <span style="color: #dc3545;">*</span>
                </h4>
                <p style="font-size: 11px; color: #666; margin-bottom: 15px;">
                    Select UPKJ classifications using cascade dropdown: Category → Class → Head → Subhead. 
                    You can select MULTIPLE values in each level. Hold Ctrl/Cmd to select multiple. At least ONE filter must be selected.
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

                    <!-- Subhead Filter (Optional) -->
                    <div class="form-group">
                        <label for="upkj_subheads">Subhead (Optional)</label>
                        <select id="upkj_subheads" name="upkj_subheads[]" multiple size="5" onchange="onSubheadChange()" disabled style="width: 100%; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px; font-size: 12px;">
                        </select>
                        <span class="form-help" style="font-size: 11px; color: #666; margin-top: 5px; display: block;">Hold Ctrl/Cmd to select multiple</span>
                    </div>
                </div>

                <!-- Selected Filters Display -->
                <div id="selectedFiltersDisplay" style="margin-top: 15px; padding: 10px; background: #e7f3ff; border: 1px solid #007bff; border-radius: 4px; display: none;">
                    <strong style="font-size: 12px;">Selected Filters:</strong>
                    <div id="selectedFiltersContent" style="font-size: 11px; margin-top: 5px; color: #004085;"></div>
                </div>

                <div style="margin-top: 15px;">
                    <button type="button" id="applyFilterBtn" onclick="applyUpkjFilter()" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;">
                        <span class="material-symbols-outlined" style="vertical-align: middle; font-size: 16px;">filter_list</span>
                        Apply Filter
                        <span id="loadingSpinner" class="loading-spinner" style="display: none;"></span>
                    </button>
                    <button type="button" onclick="resetUpkjFilter()" style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; margin-left: 10px;">
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
                    Select one or more contractors for analysis. Contractors are filtered based on your UPKJ classification selection.
                </p>

                <div id="contractorTableContainer">
                    <!-- Contractor table will be populated here via JavaScript -->
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
                    Create Transfer
                </button>
                <a 
                    href="{{ route('pages.contractor-analysis') }}" 
                    style="padding: 10px 20px; background-color: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; text-decoration: none; display: inline-block;"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        // UPKJ Classifications data from controller (for Category and Class only)
        const upkjData = @json($upkjClassifications);
        
        // Cascade dropdown functions for MULTI-SELECT with DYNAMIC API calls
        function onCategoryChange() {
            const categoriesSelect = document.getElementById('upkj_categories');
            const classesSelect = document.getElementById('upkj_classes');
            const headsSelect = document.getElementById('upkj_heads');
            const subheadsSelect = document.getElementById('upkj_subheads');
            
            // Get selected categories (array)
            const selectedCategories = Array.from(categoriesSelect.selectedOptions).map(opt => opt.value);
            
            // Reset dependent dropdowns
            classesSelect.innerHTML = '';
            headsSelect.innerHTML = '';
            subheadsSelect.innerHTML = '';
            classesSelect.disabled = true;
            headsSelect.disabled = true;
            subheadsSelect.disabled = true;
            
            if (selectedCategories.length === 0) {
                updateSelectedFiltersDisplay();
                return;
            }
            
            // Get unique classes for ALL selected categories (from local data)
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
                option.dataset.description = item.class_description;
                classesSelect.appendChild(option);
            });
            
            classesSelect.disabled = false;
            updateSelectedFiltersDisplay();
        }
        
        async function onClassChange() {
            const categoriesSelect = document.getElementById('upkj_categories');
            const classesSelect = document.getElementById('upkj_classes');
            const headsSelect = document.getElementById('upkj_heads');
            const subheadsSelect = document.getElementById('upkj_subheads');
            
            // Get selected categories and classes (arrays)
            const selectedCategories = Array.from(categoriesSelect.selectedOptions).map(opt => opt.value);
            const selectedClasses = Array.from(classesSelect.selectedOptions).map(opt => opt.value);
            
            // Reset dependent dropdowns
            headsSelect.innerHTML = '';
            subheadsSelect.innerHTML = '';
            headsSelect.disabled = true;
            subheadsSelect.disabled = true;
            
            if (selectedClasses.length === 0) {
                updateSelectedFiltersDisplay();
                return;
            }
            
            // Show loading state
            headsSelect.innerHTML = '<option>Loading...</option>';
            headsSelect.disabled = true;
            
            try {
                // Fetch available heads from API (only heads with contractors)
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
                
                if (!response.ok) {
                    throw new Error('Failed to fetch heads');
                }
                
                const heads = await response.json();
                
                // Clear loading state
                headsSelect.innerHTML = '';
                
                if (heads.length === 0) {
                    headsSelect.innerHTML = '<option>No heads available</option>';
                    headsSelect.disabled = true;
                } else {
                    // Populate heads dropdown with class prefix
                    heads.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.head_code;
                        // Display with class prefix: "Class II - VIIA - Electrical - Electrical Works (Building)"
                        option.textContent = item.display_name;
                        option.dataset.name = item.head_name;
                        // Store class and category as data attributes for filtering
                        option.setAttribute('data-class', item.class);
                        option.setAttribute('data-category', item.category);
                        headsSelect.appendChild(option);
                    });
                    headsSelect.disabled = false;
                }
            } catch (error) {
                console.error('Error fetching heads:', error);
                headsSelect.innerHTML = '<option>Error loading heads</option>';
                headsSelect.disabled = true;
            }
            
            updateSelectedFiltersDisplay();
        }
        
        async function onHeadChange() {
            const categoriesSelect = document.getElementById('upkj_categories');
            const classesSelect = document.getElementById('upkj_classes');
            const headsSelect = document.getElementById('upkj_heads');
            const subheadsSelect = document.getElementById('upkj_subheads');
            
            // Get selected values (arrays)
            const selectedCategories = Array.from(categoriesSelect.selectedOptions).map(opt => opt.value);
            const selectedHeads = Array.from(headsSelect.selectedOptions).map(opt => opt.value);
            
            // CRITICAL: Get classes from selected heads only (not from class dropdown)
            const selectedHeadClasses = Array.from(headsSelect.selectedOptions).map(opt => opt.getAttribute('data-class'));
            const uniqueHeadClasses = [...new Set(selectedHeadClasses)]; // Remove duplicates
            
            // Reset subhead dropdown
            subheadsSelect.innerHTML = '';
            subheadsSelect.disabled = true;
            
            if (selectedHeads.length === 0) {
                updateSelectedFiltersDisplay();
                return;
            }
            
            // Show loading state
            subheadsSelect.innerHTML = '<option>Loading...</option>';
            subheadsSelect.disabled = true;
            
            try {
                // Fetch available subheads from API (only subheads with contractors)
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
                
                if (!response.ok) {
                    throw new Error('Failed to fetch subheads');
                }
                
                const subheads = await response.json();
                
                // Clear loading state
                subheadsSelect.innerHTML = '';
                
                if (subheads.length === 0) {
                    subheadsSelect.innerHTML = '<option>No subheads available</option>';
                    subheadsSelect.disabled = true;
                } else {
                    // Populate subheads dropdown with class and head prefix
                    subheads.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.subhead_value;
                        // Display with class and head prefix: "Class I - VIIA - 1 - Building Electrical Works"
                        const displayText = 'Class ' + item.class + ' - ' + item.head_code + ' - ' + item.subhead_value + (item.description ? ' - ' + item.description : '');
                        option.textContent = displayText;
                        option.dataset.description = item.description;
                        // Store class, category, and head as data attributes
                        option.setAttribute('data-class', item.class);
                        option.setAttribute('data-category', item.category);
                        option.setAttribute('data-head', item.head_code);
                        subheadsSelect.appendChild(option);
                    });
                    subheadsSelect.disabled = false;
                }
            } catch (error) {
                console.error('Error fetching subheads:', error);
                subheadsSelect.innerHTML = '<option>Error loading subheads</option>';
                subheadsSelect.disabled = true;
            }
            
            updateSelectedFiltersDisplay();
        }
        
        // Update selected filters display
        function updateSelectedFiltersDisplay() {
            const categoriesSelect = document.getElementById('upkj_categories');
            const classesSelect = document.getElementById('upkj_classes');
            const headsSelect = document.getElementById('upkj_heads');
            const subheadsSelect = document.getElementById('upkj_subheads');
            const display = document.getElementById('selectedFiltersDisplay');
            const content = document.getElementById('selectedFiltersContent');
            
            const selectedCategories = Array.from(categoriesSelect.selectedOptions).map(opt => opt.value);
            const selectedClasses = Array.from(classesSelect.selectedOptions).map(opt => opt.textContent);
            const selectedHeads = Array.from(headsSelect.selectedOptions).map(opt => opt.textContent);
            const selectedSubheads = Array.from(subheadsSelect.selectedOptions).map(opt => opt.textContent);
            
            let filterText = [];
            if (selectedCategories.length > 0) {
                filterText.push('<strong>Categories:</strong> ' + selectedCategories.join(', '));
            }
            if (selectedClasses.length > 0) {
                filterText.push('<strong>Classes:</strong> ' + selectedClasses.join(', '));
            }
            if (selectedHeads.length > 0) {
                filterText.push('<strong>Heads:</strong> ' + selectedHeads.join(', '));
            }
            if (selectedSubheads.length > 0) {
                filterText.push('<strong>Subheads:</strong> ' + selectedSubheads.join(', '));
            }
            
            if (filterText.length > 0) {
                content.innerHTML = filterText.join('<br>');
                display.style.display = 'block';
            } else {
                display.style.display = 'none';
            }
        }
        
        function onSubheadChange() {
            updateSelectedFiltersDisplay();
        }
        
        function resetUpkjFilter() {
            const categoriesSelect = document.getElementById('upkj_categories');
            const classesSelect = document.getElementById('upkj_classes');
            const headsSelect = document.getElementById('upkj_heads');
            const subheadsSelect = document.getElementById('upkj_subheads');
            
            // Clear all selections
            categoriesSelect.selectedIndex = -1;
            classesSelect.innerHTML = '';
            headsSelect.innerHTML = '';
            subheadsSelect.innerHTML = '';
            
            classesSelect.disabled = true;
            headsSelect.disabled = true;
            subheadsSelect.disabled = true;
            
            // Hide displays
            document.getElementById('selectedFiltersDisplay').style.display = 'none';
            document.getElementById('contractorListSection').classList.remove('show');
            document.getElementById('contractorTableContainer').innerHTML = '';
        }

        // Update project selection summary
        function updateProjectSelection() {
            const selectedRadio = document.querySelector('.project-radio:checked');
            const summary = document.getElementById('projectSelectionSummary');
            const projectName = document.getElementById('selectedProjectName');
            
            if (selectedRadio) {
                const row = selectedRadio.closest('tr');
                const name = row.cells[2].textContent;
                const number = row.cells[1].textContent;
                
                summary.style.display = 'block';
                projectName.textContent = `Selected: ${number} - ${name}`;
            } else {
                summary.style.display = 'none';
                projectName.textContent = 'No project selected';
            }
            
            validateForm();
        }

        // Apply UPKJ filter and fetch contractors
        async function applyUpkjFilter() {
            const categoriesSelect = document.getElementById('upkj_categories');
            const classesSelect = document.getElementById('upkj_classes');
            const headsSelect = document.getElementById('upkj_heads');
            const subheadsSelect = document.getElementById('upkj_subheads');
            
            // Get selected values as arrays
            const categories = Array.from(categoriesSelect.selectedOptions).map(opt => opt.value);
            const classes = Array.from(classesSelect.selectedOptions).map(opt => opt.value);
            const heads = Array.from(headsSelect.selectedOptions).map(opt => opt.value);
            const subheads = Array.from(subheadsSelect.selectedOptions).map(opt => opt.value);
            
            // Validate at least one filter is selected
            if (categories.length === 0 && classes.length === 0 && heads.length === 0 && subheads.length === 0) {
                alert('Please select at least one UPKJ classification filter (Category, Class, Head, or Subhead).');
                return;
            }
            
            // Show loading state
            const applyBtn = document.getElementById('applyFilterBtn');
            const spinner = document.getElementById('loadingSpinner');
            applyBtn.disabled = true;
            spinner.style.display = 'inline-block';
            
            try {
                // Make AJAX request to API with arrays
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
                        subheads: subheads
                    })
                });
                
                if (!response.ok) {
                    throw new Error('Failed to fetch contractors');
                }
                
                const contractors = await response.json();
                
                // Display contractors
                displayContractors(contractors);
                
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to fetch contractors. Please try again.');
            } finally {
                // Hide loading state
                applyBtn.disabled = false;
                spinner.style.display = 'none';
            }
        }

        // Display contractors in table
        function displayContractors(contractors) {
            const container = document.getElementById('contractorTableContainer');
            const section = document.getElementById('contractorListSection');
            
            if (contractors.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <span class="material-symbols-outlined" style="font-size: 48px; color: #999; display: block; margin-bottom: 10px;">search_off</span>
                        <p style="margin: 0;">No contractors found matching the selected UPKJ classifications.</p>
                        <p style="margin: 5px 0 0 0; font-size: 11px;">Try selecting different UPKJ filters.</p>
                    </div>
                `;
                section.classList.add('show');
                return;
            }
            
            let tableHTML = `
                <div class="contractor-table-wrapper">
                    <table class="project-selection-table">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">
                                    <input type="checkbox" id="selectAllContractors" onclick="toggleSelectAllContractors(this)">
                                </th>
                                <th style="width: 250px;">Company Name</th>
                                <th style="width: 150px;">Category</th>
                                <th style="width: 150px;">Certificate No.</th>
                                <th style="width: 200px;">UPKJ Classification</th>
                                <th style="width: 120px;">Expired UPKJ</th>
                                <th style="width: 150px;">Registration Number</th>
                                <th style="width: 180px;">Owner Information</th>
                                <th style="width: 250px;">Address</th>
                                <th style="width: 120px;">Telephone Number</th>
                                <th style="width: 180px;">Email</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            contractors.forEach(contractor => {
                // Check if contractor or any UPKJ record is expired
                const isExpired = contractor.is_expired || contractor.upkj_records.some(record => record.is_expired);
                
                // Group UPKJ records by category for display
                contractor.upkj_records.forEach((record, index) => {
                    const rowClass = (isExpired || record.is_expired) ? 'expired-contractor' : '';
                    const checkboxDisabled = (isExpired || record.is_expired) ? 'disabled' : '';
                    
                    // Only show checkbox on first row for this contractor
                    const showCheckbox = index === 0;
                    const rowspan = contractor.upkj_records.length;
                    
                    // Expired display
                    const expiredDisplay = record.is_expired ? `Expired: ${record.validity_period || '-'}` : '-';
                    
                    // Safe data access with fallback to dash
                    const companyName = contractor.company_name || '-';
                    const category = record.category || '-';
                    const certificateNo = record.certificate_no || '-';
                    const classifications = record.classifications || 'No classifications';
                    const registrationNumber = contractor.registration_number || '-';
                    const ownerName = contractor.authorized_person_name || '-';
                    const address = contractor.registered_address || '-';
                    const telephone = contractor.telephone_no || '-';
                    const email = contractor.email || '-';
                    
                    tableHTML += `
                        <tr class="${rowClass}">
                            ${showCheckbox ? `
                            <td style="text-align: center;" rowspan="${rowspan}">
                                <input type="checkbox" 
                                       name="contractor_ids[]" 
                                       value="${contractor.id}" 
                                       class="contractor-checkbox"
                                       ${checkboxDisabled}
                                       onchange="updateContractorSelection()"
                                       ${(isExpired || record.is_expired) ? 'title="This contractor has expired UPKJ registration"' : ''}>
                            </td>
                            <td style="font-weight: 600;" rowspan="${rowspan}">${companyName}</td>
                            ` : ''}
                            <td style="font-size: 11px; font-weight: 600; background-color: #f0f4ff;">${category}</td>
                            <td style="font-size: 11px;">${certificateNo}</td>
                            <td style="font-size: 11px;">${classifications}</td>
                            <td style="font-size: 11px; color: ${record.is_expired ? '#dc3545' : '#666'}; font-weight: ${record.is_expired ? '600' : 'normal'};">
                                ${expiredDisplay}
                            </td>
                            ${showCheckbox ? `
                            <td rowspan="${rowspan}">${registrationNumber}</td>
                            <td style="font-size: 11px;" rowspan="${rowspan}">${ownerName}</td>
                            <td style="font-size: 11px;" rowspan="${rowspan}">${address}</td>
                            <td rowspan="${rowspan}">${telephone}</td>
                            <td style="font-size: 11px;" rowspan="${rowspan}">${email}</td>
                            ` : ''}
                        </tr>
                    `;
                });
            });
            
            tableHTML += `
                        </tbody>
                    </table>
                </div>
            `;
            
            container.innerHTML = tableHTML;
            section.classList.add('show');
            
            // Reset contractor selection summary
            updateContractorSelection();
        }

        // Toggle select all contractors (skip expired ones)
        function toggleSelectAllContractors(checkbox) {
            const checkboxes = document.querySelectorAll('.contractor-checkbox:not([disabled])');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateContractorSelection();
        }

        // Update contractor selection summary
        function updateContractorSelection() {
            const checkboxes = document.querySelectorAll('.contractor-checkbox:checked:not([disabled])');
            const count = checkboxes.length;
            const summary = document.getElementById('contractorSelectionSummary');
            const countDisplay = document.getElementById('selectedContractorCount');
            const selectAllCheckbox = document.getElementById('selectAllContractors');
            
            if (count > 0) {
                summary.style.display = 'block';
                countDisplay.textContent = count;
            } else {
                summary.style.display = 'none';
            }

            // Update select all checkbox state (only count non-disabled checkboxes)
            const allCheckboxes = document.querySelectorAll('.contractor-checkbox:not([disabled])');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = count === allCheckboxes.length && count > 0;
            }
            
            validateForm();
        }

        // Validate form before submission
        function validateForm() {
            const selectedProject = document.querySelector('.project-radio:checked');
            const selectedContractors = document.querySelectorAll('.contractor-checkbox:checked:not([disabled])').length;
            const submitButton = document.getElementById('submitButton');
            
            const isValid = selectedProject && selectedContractors > 0;
            submitButton.disabled = !isValid;
            
            if (isValid) {
                submitButton.style.opacity = '1';
                submitButton.style.cursor = 'pointer';
            } else {
                submitButton.style.opacity = '0.6';
                submitButton.style.cursor = 'not-allowed';
            }
        }

        // Form submission validation
        document.getElementById('transferForm').addEventListener('submit', function(e) {
            const selectedProject = document.querySelector('.project-radio:checked');
            const selectedContractors = document.querySelectorAll('.contractor-checkbox:checked:not([disabled])').length;
            
            if (!selectedProject) {
                e.preventDefault();
                alert('Please select a project to transfer.');
                return false;
            }
            
            if (selectedContractors === 0) {
                e.preventDefault();
                alert('Please select at least one active contractor for analysis.');
                return false;
            }
            
            // Validate UPKJ filter was applied
            const contractorSection = document.getElementById('contractorListSection');
            if (!contractorSection.classList.contains('show')) {
                e.preventDefault();
                alert('Please apply UPKJ filter to load contractors before submitting.');
                return false;
            }
            
            return true;
        });
    </script>
@endsection
