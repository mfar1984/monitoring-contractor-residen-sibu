<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\StorePreProjectRequest;
use App\Http\Requests\UpdatePreProjectRequest;

class PageController extends Controller
{
    public function overview(): View
    {
        return view('pages.overview');
    }

    // General Settings - System Information
    public function generalSystem(): View
    {
        return view('pages.general.system');
    }

    // General Settings - Application Settings
    public function generalApplication(): View
    {
        $settings = \App\Models\IntegrationSetting::getSettings('application');
        return view('pages.general.application', compact('settings'));
    }

    public function generalApplicationStore(Request $request)
    {
        // Check if this is a logo-only upload (no other fields present)
        $isLogoOnlyUpload = ($request->hasFile('login_logo') || $request->hasFile('sidebar_logo') || $request->hasFile('login_background')) 
            && !$request->has('app_name');

        // Handle login logo removal
        if ($request->has('remove_login_logo')) {
            $oldLogo = \App\Models\IntegrationSetting::getSetting('application', 'login_logo');
            if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                \Storage::disk('public')->delete($oldLogo);
            }
            \App\Models\IntegrationSetting::setSetting('application', 'login_logo', null);
            return redirect()->route('pages.general.application')->with('success', 'Login logo removed successfully');
        }

        // Handle login background removal
        if ($request->has('remove_login_background')) {
            $oldBg = \App\Models\IntegrationSetting::getSetting('application', 'login_background');
            if ($oldBg && \Storage::disk('public')->exists($oldBg)) {
                \Storage::disk('public')->delete($oldBg);
            }
            \App\Models\IntegrationSetting::setSetting('application', 'login_background', null);
            return redirect()->route('pages.general.application')->with('success', 'Login background removed successfully');
        }

        // Handle sidebar logo removal
        if ($request->has('remove_sidebar_logo')) {
            $oldLogo = \App\Models\IntegrationSetting::getSetting('application', 'sidebar_logo');
            if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                \Storage::disk('public')->delete($oldLogo);
            }
            \App\Models\IntegrationSetting::setSetting('application', 'sidebar_logo', null);
            return redirect()->route('pages.general.application')->with('success', 'Sidebar logo removed successfully');
        }

        // Validate based on request type
        if ($isLogoOnlyUpload) {
            // Logo-only upload validation
            $request->validate([
                'login_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
                'login_background' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
                'sidebar_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            ]);
        } else {
            // Full form validation
            $request->validate([
                'app_name' => 'required|string|max:255',
                'sidebar_name' => 'required|string|max:255',
                'app_url' => 'required|url',
                'sidebar_display' => 'required|in:name_only,logo_only,logo_and_name',
                'login_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
                'login_background' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
                'sidebar_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
                'items_per_page' => 'nullable|integer|min:5|max:100',
                'session_lifetime' => 'nullable|integer|min:30|max:1440',
                'maintenance_mode' => 'nullable|boolean',
            ]);
        }

        // Handle login logo upload
        if ($request->hasFile('login_logo')) {
            // Delete old logo if exists
            $oldLogo = \App\Models\IntegrationSetting::getSetting('application', 'login_logo');
            if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                \Storage::disk('public')->delete($oldLogo);
            }
            
            // Store new logo
            $file = $request->file('login_logo');
            $filename = 'login_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('logos', $filename, 'public');
            \App\Models\IntegrationSetting::setSetting('application', 'login_logo', $path);
            
            // If logo-only upload, return early
            if ($isLogoOnlyUpload) {
                return redirect()->route('pages.general.application')->with('success', 'Login logo uploaded successfully');
            }
        }

        // Handle login background upload
        if ($request->hasFile('login_background')) {
            // Delete old background if exists
            $oldBg = \App\Models\IntegrationSetting::getSetting('application', 'login_background');
            if ($oldBg && \Storage::disk('public')->exists($oldBg)) {
                \Storage::disk('public')->delete($oldBg);
            }
            
            // Store new background
            $file = $request->file('login_background');
            $filename = 'login_bg_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('backgrounds', $filename, 'public');
            \App\Models\IntegrationSetting::setSetting('application', 'login_background', $path);
            
            // If logo-only upload, return early
            if ($isLogoOnlyUpload) {
                return redirect()->route('pages.general.application')->with('success', 'Login background uploaded successfully');
            }
        }
        
        // Handle sidebar logo upload
        if ($request->hasFile('sidebar_logo')) {
            // Delete old logo if exists
            $oldLogo = \App\Models\IntegrationSetting::getSetting('application', 'sidebar_logo');
            if ($oldLogo && \Storage::disk('public')->exists($oldLogo)) {
                \Storage::disk('public')->delete($oldLogo);
            }
            
            // Store new logo
            $file = $request->file('sidebar_logo');
            $filename = 'sidebar_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('logos', $filename, 'public');
            \App\Models\IntegrationSetting::setSetting('application', 'sidebar_logo', $path);
            
            // If logo-only upload, return early
            if ($isLogoOnlyUpload) {
                return redirect()->route('pages.general.application')->with('success', 'Sidebar logo uploaded successfully');
            }
        }
        
        // Save other settings (only if not logo-only upload)
        if (!$isLogoOnlyUpload) {
            \App\Models\IntegrationSetting::setSetting('application', 'app_name', $request->app_name);
            \App\Models\IntegrationSetting::setSetting('application', 'sidebar_name', $request->sidebar_name);
            \App\Models\IntegrationSetting::setSetting('application', 'app_url', $request->app_url);
            \App\Models\IntegrationSetting::setSetting('application', 'sidebar_display', $request->sidebar_display);
            \App\Models\IntegrationSetting::setSetting('application', 'items_per_page', $request->items_per_page ?? 10);
            \App\Models\IntegrationSetting::setSetting('application', 'session_lifetime', $request->session_lifetime ?? 120);
            \App\Models\IntegrationSetting::setSetting('application', 'maintenance_mode', $request->has('maintenance_mode') ? 1 : 0);
        }

        return redirect()->route('pages.general.application')->with('success', 'Application settings saved successfully');
    }

    // General Settings - Localization
    public function generalLocalization(): View
    {
        $settings = \App\Models\IntegrationSetting::getSettings('localization');
        $languages = \App\Models\Language::getActiveLanguages();
        return view('pages.general.localization', compact('settings', 'languages'));
    }

    public function generalLocalizationStore(Request $request)
    {
        $request->validate([
            'locale' => 'required|string|exists:languages,code',
            'timezone' => 'required|string',
            'date_format' => 'nullable|string',
            'time_format' => 'nullable|string',
            'currency' => 'nullable|string',
        ]);

        \App\Models\IntegrationSetting::setSetting('localization', 'locale', $request->locale);
        \App\Models\IntegrationSetting::setSetting('localization', 'timezone', $request->timezone);
        \App\Models\IntegrationSetting::setSetting('localization', 'date_format', $request->date_format ?? 'd/m/Y');
        \App\Models\IntegrationSetting::setSetting('localization', 'time_format', $request->time_format ?? 'H:i:s');
        \App\Models\IntegrationSetting::setSetting('localization', 'currency', $request->currency ?? 'MYR');

        return redirect()->route('pages.general.localization')->with('success', 'Localization settings saved successfully');
    }

    // General Settings - Maintenance
    public function generalMaintenance(): View
    {
        return view('pages.general.maintenance');
    }

    // General Settings - Approver
    public function generalApprover(): View
    {
        $settings = \App\Models\IntegrationSetting::getSettings('application');
        $residenUsers = \App\Models\User::whereNotNull('residen_category_id')
            ->where('status', 'Active')
            ->with('residenCategory')
            ->orderBy('full_name')
            ->get();
        
        // Get pre-project approvers
        $preProjectApproversJson = \App\Models\IntegrationSetting::getSetting('approver', 'pre_project_approvers');
        $preProjectApprovers = $preProjectApproversJson ? json_decode($preProjectApproversJson, true) : [];
        
        return view('pages.general.approver', compact('settings', 'residenUsers', 'preProjectApprovers'));
    }

    public function generalApproverStore(Request $request)
    {
        $request->validate([
            'pre_project_approvers' => 'required|array|min:1',
            'pre_project_approvers.*' => 'exists:users,id',
            'first_approval_user' => 'required|exists:users,id',
            'second_approval_user' => 'required|exists:users,id',
        ]);

        // Save pre-project approvers as JSON
        \App\Models\IntegrationSetting::setSetting('approver', 'pre_project_approvers', json_encode($request->pre_project_approvers));
        
        // Save NOC approval settings
        \App\Models\IntegrationSetting::setSetting('application', 'first_approval_user', $request->first_approval_user);
        \App\Models\IntegrationSetting::setSetting('application', 'second_approval_user', $request->second_approval_user);

        return redirect()->route('pages.general.approver')->with('success', 'Approver settings saved successfully');
    }

    // General Settings - Translation
    public function generalTranslation(): View
    {
        $lang = request('lang', 'en');
        $languages = \App\Models\Language::getActiveLanguages();
        
        // Validate that the requested language exists
        if (!$languages->contains('code', $lang)) {
            $lang = 'en';
        }
        
        $translations = \App\Models\IntegrationSetting::getSettings('translation_' . $lang);
        return view('pages.general.translation', compact('translations', 'lang', 'languages'));
    }

    public function generalTranslationStore(Request $request)
    {
        $request->validate([
            'language' => 'required|string|exists:languages,code',
            'translations' => 'required|array',
        ]);

        // Store translations for the selected language
        foreach ($request->translations as $key => $value) {
            if (!empty($value)) {
                \App\Models\IntegrationSetting::setSetting(
                    'translation_' . $request->language,
                    $key,
                    $value
                );
            }
        }

        return redirect()
            ->route('pages.general.translation', ['lang' => $request->language])
            ->with('success', 'Translations saved successfully');
    }

    // Language Management
    public function addLanguage(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:255',
        ]);

        \App\Models\Language::create([
            'code' => strtolower($request->code),
            'name' => $request->name,
            'is_default' => false,
            'status' => 'Active'
        ]);

        return redirect()->route('pages.general.localization')->with('success', 'Language added successfully');
    }

    public function deleteLanguage($id)
    {
        $language = \App\Models\Language::findOrFail($id);
        
        // Prevent deletion of default languages
        if ($language->is_default) {
            return redirect()->route('pages.general.localization')->with('error', 'Cannot delete default language');
        }

        $language->delete();

        return redirect()->route('pages.general.localization')->with('success', 'Language deleted successfully');
    }

    public function masterData(): RedirectResponse
    {
        return redirect()->route('pages.master-data.residen');
    }

    public function masterDataResiden(): View
    {
        $categories = \App\Models\ResidenCategory::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.residen', compact('categories'));
    }

    public function masterDataResidenStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:residen_categories,code',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\ResidenCategory::create($request->all());

        return redirect()->route('pages.master-data.residen')->with('success', 'Category created successfully');
    }

    public function masterDataResidenUpdate(Request $request, $id)
    {
        $category = \App\Models\ResidenCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:residen_categories,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $category->update($request->all());

        return redirect()->route('pages.master-data.residen')->with('success', 'Category updated successfully');
    }

    public function masterDataResidenDelete($id)
    {
        $category = \App\Models\ResidenCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('pages.master-data.residen')->with('success', 'Category deleted successfully');
    }

    public function masterDataAgency(): View
    {
        $categories = \App\Models\AgencyCategory::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.agency', compact('categories'));
    }

    public function masterDataAgencyStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:agency_categories,code',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\AgencyCategory::create($request->all());

        return redirect()->route('pages.master-data.agency')->with('success', 'Category created successfully');
    }

    public function masterDataAgencyUpdate(Request $request, $id)
    {
        $category = \App\Models\AgencyCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:agency_categories,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $category->update($request->all());

        return redirect()->route('pages.master-data.agency')->with('success', 'Category updated successfully');
    }

    public function masterDataAgencyDelete($id)
    {
        $category = \App\Models\AgencyCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('pages.master-data.agency')->with('success', 'Category deleted successfully');
    }

    public function masterDataParliament(): View
    {
        $categories = \App\Models\ParliamentCategory::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.parliament', compact('categories'));
    }

    public function masterDataParliamentStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'code' => 'required|string|max:255|unique:parliament_categories,code',
            'type' => 'required|in:DUN,Parliament',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\ParliamentCategory::create($request->all());

        return redirect()->route('pages.master-data.parliament')->with('success', 'Category created successfully');
    }

    public function masterDataParliamentUpdate(Request $request, $id)
    {
        $category = \App\Models\ParliamentCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'code' => 'required|string|max:255|unique:parliament_categories,code,' . $id,
            'type' => 'required|in:DUN,Parliament',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $category->update($request->all());

        return redirect()->route('pages.master-data.parliament')->with('success', 'Category updated successfully');
    }

    public function masterDataParliamentDelete($id)
    {
        $category = \App\Models\ParliamentCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('pages.master-data.parliament')->with('success', 'Category deleted successfully');
    }

    public function masterDataContractor(): View
    {
        $categories = \App\Models\ContractorCategory::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.contractor', compact('categories'));
    }

    public function masterDataContractorStore(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:contractor_categories,code',
            'registration_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\ContractorCategory::create($request->all());

        return redirect()->route('pages.master-data.contractor')->with('success', 'Category created successfully');
    }

    public function masterDataContractorUpdate(Request $request, $id)
    {
        $category = \App\Models\ContractorCategory::findOrFail($id);

        $request->validate([
            'company_name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:contractor_categories,code,' . $id,
            'registration_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $category->update($request->all());

        return redirect()->route('pages.master-data.contractor')->with('success', 'Category updated successfully');
    }

    public function masterDataContractorDelete($id)
    {
        $category = \App\Models\ContractorCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('pages.master-data.contractor')->with('success', 'Category deleted successfully');
    }

    public function masterDataStatus(): View
    {
        $statuses = \App\Models\StatusMaster::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.status', compact('statuses'));
    }

    public function masterDataProjectCategory(): View
    {
        $categories = \App\Models\ProjectCategory::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.project-category', compact('categories'));
    }

    public function masterDataProjectCategoryStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:project_categories,code',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\ProjectCategory::create($request->all());

        return redirect()->route('pages.master-data.project-category')->with('success', 'Category created successfully');
    }

    public function masterDataProjectCategoryUpdate(Request $request, $id)
    {
        $category = \App\Models\ProjectCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:project_categories,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $category->update($request->all());

        return redirect()->route('pages.master-data.project-category')->with('success', 'Category updated successfully');
    }

    public function masterDataProjectCategoryDelete($id)
    {
        $category = \App\Models\ProjectCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('pages.master-data.project-category')->with('success', 'Category deleted successfully');
    }

    public function masterDataDivision(): View
    {
        $divisions = \App\Models\Division::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.division', compact('divisions'));
    }

    public function masterDataDivisionStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:divisions,code',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\Division::create($request->all());

        return redirect()->route('pages.master-data.division')->with('success', 'Division created successfully');
    }

    public function masterDataDivisionUpdate(Request $request, $id)
    {
        $division = \App\Models\Division::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:divisions,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $division->update($request->all());

        return redirect()->route('pages.master-data.division')->with('success', 'Division updated successfully');
    }

    public function masterDataDivisionDelete($id)
    {
        $division = \App\Models\Division::findOrFail($id);
        $division->delete();

        return redirect()->route('pages.master-data.division')->with('success', 'Division deleted successfully');
    }

    public function masterDataDistrict(): View
    {
        $districts = \App\Models\District::with('division')->orderBy('created_at', 'desc')->get();
        $divisions = \App\Models\Division::where('status', 'Active')->orderBy('name')->get();
        return view('pages.master-data.district', compact('districts', 'divisions'));
    }

    public function masterDataDistrictStore(Request $request)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:districts,code',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\District::create($request->all());

        return redirect()->route('pages.master-data.district')->with('success', 'District created successfully');
    }

    public function masterDataDistrictUpdate(Request $request, $id)
    {
        $district = \App\Models\District::findOrFail($id);

        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:districts,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $district->update($request->all());

        return redirect()->route('pages.master-data.district')->with('success', 'District updated successfully');
    }

    public function masterDataDistrictDelete($id)
    {
        $district = \App\Models\District::findOrFail($id);
        $district->delete();

        return redirect()->route('pages.master-data.district')->with('success', 'District deleted successfully');
    }

    public function masterDataParliaments(): View
    {
        $parliaments = \App\Models\Parliament::with('budgets')->orderBy('created_at', 'desc')->get();
        return view('pages.master-data.parliaments', compact('parliaments'));
    }

    public function masterDataParliamentsStore(\App\Http\Requests\StoreParliamentRequest $request)
    {
        // Create the Parliament record
        $parliament = \App\Models\Parliament::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        // Create budget entries for each year
        foreach ($request->budgets as $budgetEntry) {
            \App\Models\ParliamentBudget::create([
                'parliament_id' => $parliament->id,
                'year' => $budgetEntry['year'],
                'budget' => $budgetEntry['budget'],
            ]);
        }

        return redirect()->route('pages.master-data.parliaments')->with('success', 'Parliament created successfully');
    }

    public function masterDataParliamentsUpdate(\App\Http\Requests\UpdateParliamentRequest $request, $id)
        {
            $parliament = \App\Models\Parliament::findOrFail($id);

            // Update the Parliament record
            $parliament->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            // Delete existing budget entries
            \App\Models\ParliamentBudget::where('parliament_id', $parliament->id)->delete();

            // Create new budget entries from the budgets array
            foreach ($request->budgets as $budgetEntry) {
                \App\Models\ParliamentBudget::create([
                    'parliament_id' => $parliament->id,
                    'year' => $budgetEntry['year'],
                    'budget' => $budgetEntry['budget'],
                ]);
            }

            return redirect()->route('pages.master-data.parliaments')->with('success', 'Parliament updated successfully');
        }


    public function masterDataParliamentsDelete($id)
    {
        $parliament = \App\Models\Parliament::findOrFail($id);
        $parliament->delete();

        return redirect()->route('pages.master-data.parliaments')->with('success', 'Parliament deleted successfully');
    }

    public function masterDataDuns(): View
    {
        $duns = \App\Models\Dun::with('budgets')->orderBy('created_at', 'desc')->get();
        return view('pages.master-data.duns', compact('duns'));
    }

    public function masterDataDunsStore(\App\Http\Requests\StoreDunRequest $request)
    {
        // Create the DUN record
        $dun = \App\Models\Dun::create([
            'parliament_id' => $request->parliament_id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        // Create budget entries for each year
        foreach ($request->budgets as $budgetEntry) {
            \App\Models\DunBudget::create([
                'dun_id' => $dun->id,
                'year' => $budgetEntry['year'],
                'budget' => $budgetEntry['budget'],
            ]);
        }

        return redirect()->route('pages.master-data.duns')->with('success', 'DUN created successfully');
    }

    public function masterDataDunsUpdate(\App\Http\Requests\UpdateDunRequest $request, $id)
    {
        $dun = \App\Models\Dun::findOrFail($id);

        // Update the DUN record
        $dun->update([
            'parliament_id' => $request->parliament_id,
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        // Delete existing budget entries
        \App\Models\DunBudget::where('dun_id', $dun->id)->delete();

        // Create new budget entries from the budgets array
        foreach ($request->budgets as $budgetEntry) {
            \App\Models\DunBudget::create([
                'dun_id' => $dun->id,
                'year' => $budgetEntry['year'],
                'budget' => $budgetEntry['budget'],
            ]);
        }

        return redirect()->route('pages.master-data.duns')->with('success', 'DUN updated successfully');
    }

    public function masterDataDunsDelete($id)
    {
        $dun = \App\Models\Dun::findOrFail($id);
        $dun->delete();

        return redirect()->route('pages.master-data.duns')->with('success', 'DUN deleted successfully');
    }

    public function masterDataLandTitleStatus(): View
    {
        $landTitleStatuses = \App\Models\LandTitleStatus::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.land-title-status', compact('landTitleStatuses'));
    }

    public function masterDataLandTitleStatusStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:land_title_statuses,code',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\LandTitleStatus::create($request->all());

        return redirect()->route('pages.master-data.land-title-status')->with('success', 'Land Title Status created successfully');
    }

    public function masterDataLandTitleStatusUpdate(Request $request, $id)
    {
        $landTitleStatus = \App\Models\LandTitleStatus::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:land_title_statuses,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $landTitleStatus->update($request->all());

        return redirect()->route('pages.master-data.land-title-status')->with('success', 'Land Title Status updated successfully');
    }

    public function masterDataLandTitleStatusDelete($id)
    {
        $landTitleStatus = \App\Models\LandTitleStatus::findOrFail($id);
        $landTitleStatus->delete();

        return redirect()->route('pages.master-data.land-title-status')->with('success', 'Land Title Status deleted successfully');
    }

    public function groupRoles(): View
    {
        return view('pages.group-roles');
    }

    public function usersId(): RedirectResponse
    {
        return redirect()->route('pages.users-id.residen');
    }

    public function usersIdResiden(): View
    {
        $users = \App\Models\User::with('residenCategory')->whereNotNull('residen_category_id')->orderBy('created_at', 'desc')->get();
        $categories = \App\Models\ResidenCategory::where('status', 'Active')->orderBy('name')->get();
        return view('pages.users-id.residen', compact('users', 'categories'));
    }

    public function usersIdResidenStore(Request $request)
    {
        $request->validate([
            'residen_category_id' => 'required|exists:residen_categories,id',
            'full_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        \App\Models\User::create([
            'residen_category_id' => $request->residen_category_id,
            'full_name' => $request->full_name,
            'department' => $request->department,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'username' => $request->username,
            'password' => $request->password,
            'role' => 'User',
            'status' => 'Active',
        ]);

        return redirect()->route('pages.users-id.residen')->with('success', 'User created successfully');
    }

    public function usersIdResidenUpdate(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $request->validate([
            'residen_category_id' => 'required|exists:residen_categories,id',
            'full_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'residen_category_id' => $request->residen_category_id,
            'full_name' => $request->full_name,
            'department' => $request->department,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('pages.users-id.residen')->with('success', 'User updated successfully');
    }

    public function usersIdResidenDelete($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();

        return redirect()->route('pages.users-id.residen')->with('success', 'User deleted successfully');
    }

    public function usersIdAgency(): View
    {
        $users = \App\Models\User::with('agencyCategory')->whereNotNull('agency_category_id')->orderBy('created_at', 'desc')->get();
        $categories = \App\Models\AgencyCategory::where('status', 'Active')->orderBy('name')->get();
        return view('pages.users-id.agency', compact('users', 'categories'));
    }

    public function usersIdAgencyStore(Request $request)
    {
        $request->validate([
            'agency_category_id' => 'required|exists:agency_categories,id',
            'full_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        \App\Models\User::create([
            'agency_category_id' => $request->agency_category_id,
            'full_name' => $request->full_name,
            'department' => $request->department,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'username' => $request->username,
            'password' => $request->password,
            'role' => 'User',
            'status' => 'Active',
        ]);

        return redirect()->route('pages.users-id.agency')->with('success', 'User created successfully');
    }

    public function usersIdAgencyUpdate(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $request->validate([
            'agency_category_id' => 'required|exists:agency_categories,id',
            'full_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'agency_category_id' => $request->agency_category_id,
            'full_name' => $request->full_name,
            'department' => $request->department,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('pages.users-id.agency')->with('success', 'User updated successfully');
    }

    public function usersIdAgencyDelete($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();

        return redirect()->route('pages.users-id.agency')->with('success', 'User deleted successfully');
    }

    public function usersIdParliament(): View
    {
        $users = \App\Models\User::with(['parliament', 'dun'])
            ->where(function($query) {
                $query->whereNotNull('parliament_id')
                      ->orWhereNotNull('dun_id');
            })
            ->orderBy('created_at', 'desc')
            ->get();
        $parliaments = \App\Models\Parliament::where('status', 'Active')->orderBy('name')->get();
        $duns = \App\Models\Dun::where('status', 'Active')->orderBy('name')->get();
        return view('pages.users-id.parliament', compact('users', 'parliaments', 'duns'));
    }

    public function usersIdParliamentStore(Request $request)
    {
        $request->validate([
            'type' => 'required|in:parliament,dun',
            'parliament_id' => 'required_if:type,parliament|nullable|exists:parliaments,id',
            'dun_id' => 'required_if:type,dun|nullable|exists:duns,id',
            'full_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        \App\Models\User::create([
            'parliament_id' => $request->type === 'parliament' ? $request->parliament_id : null,
            'dun_id' => $request->type === 'dun' ? $request->dun_id : null,
            'full_name' => $request->full_name,
            'department' => $request->department,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'username' => $request->username,
            'password' => $request->password,
            'role' => 'User',
            'status' => 'Active',
        ]);

        return redirect()->route('pages.users-id.parliament')->with('success', 'User created successfully');
    }

    public function usersIdParliamentUpdate(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $request->validate([
            'type' => 'required|in:parliament,dun',
            'parliament_id' => 'required_if:type,parliament|nullable|exists:parliaments,id',
            'dun_id' => 'required_if:type,dun|nullable|exists:duns,id',
            'full_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'parliament_id' => $request->type === 'parliament' ? $request->parliament_id : null,
            'dun_id' => $request->type === 'dun' ? $request->dun_id : null,
            'full_name' => $request->full_name,
            'department' => $request->department,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('pages.users-id.parliament')->with('success', 'User updated successfully');
    }

    public function usersIdParliamentDelete($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();

        return redirect()->route('pages.users-id.parliament')->with('success', 'User deleted successfully');
    }

    public function usersIdContractor(): View
    {
        $users = \App\Models\User::with('contractorCategory')->whereNotNull('contractor_category_id')->orderBy('created_at', 'desc')->get();
        $categories = \App\Models\ContractorCategory::where('status', 'Active')->orderBy('company_name')->get();
        return view('pages.users-id.contractor', compact('users', 'categories'));
    }

    public function usersIdContractorStore(Request $request)
    {
        $request->validate([
            'contractor_category_id' => 'required|exists:contractor_categories,id',
            'full_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        \App\Models\User::create([
            'contractor_category_id' => $request->contractor_category_id,
            'full_name' => $request->full_name,
            'department' => $request->department,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'username' => $request->username,
            'password' => $request->password,
            'role' => 'User',
            'status' => 'Active',
        ]);

        return redirect()->route('pages.users-id.contractor')->with('success', 'User created successfully');
    }

    public function usersIdContractorUpdate(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $request->validate([
            'contractor_category_id' => 'required|exists:contractor_categories,id',
            'full_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = [
            'contractor_category_id' => $request->contractor_category_id,
            'full_name' => $request->full_name,
            'department' => $request->department,
            'contact_number' => $request->contact_number,
            'email' => $request->email,
            'username' => $request->username,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('pages.users-id.contractor')->with('success', 'User updated successfully');
    }

    public function usersIdContractorDelete($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();

        return redirect()->route('pages.users-id.contractor')->with('success', 'User deleted successfully');
    }

    public function integrations(): RedirectResponse
    {
        return redirect()->route('pages.integrations.email');
    }

    public function integrationsEmail(): View
    {
        $settings = \App\Models\IntegrationSetting::getSettings('email');
        return view('pages.integrations.email', compact('settings'));
    }

    public function integrationsSms(): View
    {
        $settings = \App\Models\IntegrationSetting::getSettings('sms');
        return view('pages.integrations.sms', compact('settings'));
    }

    public function integrationsWebhook(): View
    {
        $settings = \App\Models\IntegrationSetting::getSettings('webhook');
        return view('pages.integrations.webhook', compact('settings'));
    }

    public function integrationsApi(): View
    {
        $settings = \App\Models\IntegrationSetting::getSettings('api');
        return view('pages.integrations.api', compact('settings'));
    }

    public function integrationsWeather(): View
    {
        $settings = \App\Models\IntegrationSetting::getSettings('weather');
        return view('pages.integrations.weather', compact('settings'));
    }

    public function integrationsEmailStore(Request $request)
    {
        $request->validate([
            'smtp_host' => 'required|string|max:255',
            'smtp_port' => 'required|integer',
            'smtp_encryption' => 'required|in:tls,ssl,none',
            'smtp_username' => 'required|string|max:255',
            'smtp_password' => 'nullable|string',
            'smtp_from_email' => 'required|email|max:255',
            'smtp_from_name' => 'required|string|max:255',
        ]);

        foreach ($request->except('_token') as $key => $value) {
            // Skip empty password field (keep existing password)
            if ($key === 'smtp_password' && empty($value)) {
                continue;
            }
            \App\Models\IntegrationSetting::setSetting('email', $key, $value);
        }

        return redirect()->route('pages.integrations.email')->with('success', 'Email configuration saved successfully');
    }

    public function integrationsSmsStore(Request $request)
    {
        $request->validate([
            'api_url' => 'required|url|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
            'sender_id' => 'required|string|max:255',
        ]);

        foreach ($request->except('_token') as $key => $value) {
            if ($key === 'password' && empty($value)) {
                continue;
            }
            \App\Models\IntegrationSetting::setSetting('sms', $key, $value);
        }

        return redirect()->route('pages.integrations.sms')->with('success', 'SMS configuration saved successfully');
    }

    public function integrationsSmsTest(Request $request)
    {
        $request->validate([
            'test_phone' => 'required|string|max:20',
        ]);

        $smsService = new \App\Services\InfoBlastSmsService();
        $result = $smsService->sendTestSms($request->test_phone);

        return response()->json($result);
    }

    public function integrationsWebhookStore(Request $request)
    {
        $request->validate([
            'webhook_url' => 'required|url|max:255',
            'webhook_secret' => 'nullable|string|max:255',
            'webhook_events' => 'nullable|string',
        ]);

        foreach ($request->except('_token') as $key => $value) {
            if ($key === 'webhook_secret' && empty($value)) {
                continue;
            }
            \App\Models\IntegrationSetting::setSetting('webhook', $key, $value);
        }

        return redirect()->route('pages.integrations.webhook')->with('success', 'Webhook configuration saved successfully');
    }

    public function integrationsApiStore(Request $request)
    {
        $request->validate([
            'api_key' => 'nullable|string|max:255',
            'api_secret' => 'nullable|string|max:255',
            'api_endpoint' => 'required|url|max:255',
        ]);

        foreach ($request->except('_token') as $key => $value) {
            if (in_array($key, ['api_key', 'api_secret']) && empty($value)) {
                continue;
            }
            \App\Models\IntegrationSetting::setSetting('api', $key, $value);
        }

        return redirect()->route('pages.integrations.api')->with('success', 'API configuration saved successfully');
    }

    public function integrationsWeatherStore(Request $request)
    {
        $request->validate([
            'api_key' => 'nullable|string|max:255',
            'base_url' => 'required|url|max:255',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'units' => 'required|in:metric,imperial',
        ]);

        foreach ($request->except('_token') as $key => $value) {
            if ($key === 'api_key' && empty($value)) {
                continue;
            }
            \App\Models\IntegrationSetting::setSetting('weather', $key, $value);
        }

        return redirect()->route('pages.integrations.weather')->with('success', 'Weather configuration saved successfully');
    }

    public function integrationsEmailTest(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            // Get email settings
            $settings = \App\Models\IntegrationSetting::getSettings('email');
            
            // Check if configuration exists
            if (empty($settings['smtp_host']) || empty($settings['smtp_username']) || empty($settings['smtp_password'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email configuration is incomplete. Please save your SMTP settings first.'
                ], 400);
            }

            // Configure mail settings
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $settings['smtp_host'],
                'mail.mailers.smtp.port' => $settings['smtp_port'] ?? 587,
                'mail.mailers.smtp.encryption' => $settings['smtp_encryption'] ?? 'tls',
                'mail.mailers.smtp.username' => $settings['smtp_username'],
                'mail.mailers.smtp.password' => $settings['smtp_password'],
                'mail.mailers.smtp.timeout' => 10,
                'mail.from.address' => $settings['smtp_from_email'] ?? 'noreply@example.com',
                'mail.from.name' => $settings['smtp_from_name'] ?? 'Monitoring System',
            ]);

            // Clear any cached mail config
            app()->forgetInstance('mail.manager');
            app()->forgetInstance(\Illuminate\Mail\Mailer::class);

            // Send test email with proper error handling
            \Illuminate\Support\Facades\Mail::raw(
                "This is a test email from Monitoring System.\n\nIf you received this email, your SMTP configuration is working correctly.\n\nSMTP Server: " . $settings['smtp_host'] . "\nFrom: " . ($settings['smtp_from_email'] ?? 'noreply@example.com'),
                function ($message) use ($request) {
                    $message->to($request->test_email)
                            ->subject('Test Email - Monitoring System');
                }
            );

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully! Please check your inbox at ' . $request->test_email
            ]);

        } catch (\Symfony\Component\Mailer\Exception\TransportException $e) {
            $errorMessage = $e->getMessage();
            
            // Parse error message for better user feedback
            if (stripos($errorMessage, 'authentication') !== false || 
                stripos($errorMessage, 'username') !== false || 
                stripos($errorMessage, 'password') !== false ||
                stripos($errorMessage, '535') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication failed. Please check your SMTP username and password.'
                ], 400);
            } elseif (stripos($errorMessage, 'connection') !== false || 
                      stripos($errorMessage, 'could not connect') !== false ||
                      stripos($errorMessage, 'timed out') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not connect to SMTP server. Please check your host and port settings.'
                ], 400);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'SMTP Error: ' . $errorMessage
                ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Email test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function integrationsWeatherTest(Request $request)
    {
        try {
            // Get weather settings
            $settings = \App\Models\IntegrationSetting::getSettings('weather');
            
            // Check if configuration exists
            if (empty($settings['api_key']) || empty($settings['base_url'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Weather configuration is incomplete. Please save your API key and base URL first.'
                ], 400);
            }

            // Use latitude/longitude if available, otherwise use location name
            $url = $settings['base_url'] . '/weather?';
            
            if (!empty($settings['latitude']) && !empty($settings['longitude'])) {
                $url .= 'lat=' . $settings['latitude'] . '&lon=' . $settings['longitude'];
            } elseif (!empty($settings['location'])) {
                $url .= 'q=' . urlencode($settings['location']);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide either a location or coordinates.'
                ], 400);
            }
            
            $url .= '&appid=' . $settings['api_key'];
            $url .= '&units=' . ($settings['units'] ?? 'metric');

            // Make API request
            $response = \Illuminate\Support\Facades\Http::timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                
                $units = ($settings['units'] ?? 'metric') === 'metric' ? '°C' : '°F';
                $speedUnit = ($settings['units'] ?? 'metric') === 'metric' ? 'm/s' : 'mph';
                
                $location = $data['name'] ?? 'Unknown';
                $temp = round($data['main']['temp'] ?? 0, 1);
                $feelsLike = round($data['main']['feels_like'] ?? 0, 1);
                $humidity = $data['main']['humidity'] ?? 'N/A';
                $pressure = $data['main']['pressure'] ?? 'N/A';
                $windSpeed = round($data['wind']['speed'] ?? 0, 1);
                $visibility = isset($data['visibility']) ? round($data['visibility'] / 1000, 1) : 'N/A';
                $description = ucfirst($data['weather'][0]['description'] ?? 'N/A');
                $uvIndex = 'N/A'; // UV Index requires separate API call
                
                $message = '<div style="font-weight: bold; margin-bottom: 12px;">Weather API test successful!</div>';
                $message .= '<div style="display: grid; grid-template-columns: 150px auto; gap: 8px; font-family: monospace; font-size: 12px;">';
                $message .= '<div>Location</div><div>: ' . htmlspecialchars($location) . '</div>';
                $message .= '<div>Temperature</div><div>: ' . $temp . $units . '</div>';
                $message .= '<div>Feels Like</div><div>: ' . $feelsLike . $units . '</div>';
                $message .= '<div>Humidity</div><div>: ' . $humidity . '%</div>';
                $message .= '<div>Wind Speed</div><div>: ' . $windSpeed . ' ' . $speedUnit . '</div>';
                $message .= '<div>Pressure</div><div>: ' . $pressure . ' hPa</div>';
                $message .= '<div>Visibility</div><div>: ' . $visibility . ' km</div>';
                $message .= '<div>UV Index</div><div>: ' . $uvIndex . '</div>';
                $message .= "<div>Today's Forecast</div><div>: " . htmlspecialchars($description) . "</div>";
                $message .= '</div>';
                
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                $error = $response->json();
                $errorMessage = $error['message'] ?? 'Unknown error';
                
                // Parse common errors
                if (stripos($errorMessage, 'invalid api key') !== false || $response->status() === 401) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid API key. Please check your OpenWeatherMap API key.'
                    ], 400);
                } elseif (stripos($errorMessage, 'city not found') !== false || $response->status() === 404) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Location not found. Please check your location or coordinates.'
                    ], 400);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'API Error: ' . $errorMessage
                    ], 400);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Weather test failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to test weather API: ' . $e->getMessage()
            ], 500);
        }
    }

    public function activityLog(): View
    {
        return view('pages.activity-log');
    }

    public function preProject(): View
    {
        $user = auth()->user();
        
        // Filter pre-projects based on user's parliament_id, dun_id, or agency_category_id
        $query = \App\Models\PreProject::with([
            'residenCategory', 
            'agencyCategory', 
            'parliament',
            'dunBasic',
            'projectCategory',
            'division',
            'district',
            'parliamentLocation',
            'dun',
            'landTitleStatus',
            'implementingAgency',
            'implementationMethod',
            'projectOwnership'
        ]);
        
        // Apply access control filter
        if ($user->parliament_id) {
            // User under Parliament - only show pre-projects for their Parliament
            $query->where('parliament_id', $user->parliament_id);
        } elseif ($user->dun_id) {
            // User under DUN - only show pre-projects for their DUN
            $query->where('dun_basic_id', $user->dun_id);
        } elseif ($user->agency_category_id) {
            // User under Agency - only show pre-projects for their Agency
            $query->where('agency_category_id', $user->agency_category_id);
        }
        // Admin/Residen users see all pre-projects (no filter)
        
        $preProjects = $query->orderBy('created_at', 'desc')->get();
        
        // Add completeness data to each pre-project
        foreach ($preProjects as $preProject) {
            $preProject->completeness_percentage = $preProject->getCompletenessPercentage();
            $preProject->completeness_color = $preProject->getCompletenessBadgeColor();
        }
        
        // Get budget information for the user
        $budgetService = new \App\Services\BudgetCalculationService();
        $budgetInfo = $budgetService->getUserBudgetInfo($user);
        
        // Get Residen budget info if user is Residen
        $residenBudgetInfo = null;
        if ($user->residen_category_id) {
            $residenBudgetInfo = $budgetService->getResidenBudgetInfo($user);
        }
        
        // Get pre-project approvers
        $preProjectApproversJson = \App\Models\IntegrationSetting::getSetting('approver', 'pre_project_approvers');
        $preProjectApprovers = $preProjectApproversJson ? json_decode($preProjectApproversJson, true) : [];
        $isPreProjectApprover = in_array($user->id, $preProjectApprovers);
        
        $residenCategories = \App\Models\ResidenCategory::where('status', 'Active')->orderBy('name')->get();
        $agencyCategories = \App\Models\AgencyCategory::where('status', 'Active')->orderBy('name')->get();
        $parliaments = \App\Models\Parliament::where('status', 'Active')->orderBy('name')->get();
        $projectCategories = \App\Models\ProjectCategory::where('status', 'Active')->orderBy('name')->get();
        $divisions = \App\Models\Division::where('status', 'Active')->orderBy('name')->get();
        $districts = \App\Models\District::where('status', 'Active')->orderBy('name')->get();
        $duns = \App\Models\Dun::where('status', 'Active')->orderBy('name')->get();
        $landTitleStatuses = \App\Models\LandTitleStatus::where('status', 'Active')->orderBy('name')->get();
        $implementationMethods = \App\Models\ImplementationMethod::where('status', 'Active')->orderBy('name')->get();
        $projectOwnerships = \App\Models\ProjectOwnership::where('status', 'Active')->orderBy('name')->get();
        
        return view('pages.pre-project', compact(
            'user',
            'preProjects',
            'budgetInfo',
            'residenBudgetInfo',
            'isPreProjectApprover',
            'residenCategories',
            'agencyCategories',
            'parliaments',
            'projectCategories',
            'divisions',
            'districts',
            'duns',
            'landTitleStatuses',
            'implementationMethods',
            'projectOwnerships'
        ));
    }

    public function preProjectStore(StorePreProjectRequest $request)
    {
        $data = $request->except(['parliament_dun_basic', 'bill_of_quantity_attachment']);
        
        // Handle file upload
        if ($request->hasFile('bill_of_quantity_attachment')) {
            $file = $request->file('bill_of_quantity_attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pre-projects/bill-of-quantity', $filename, 'public');
            $data['bill_of_quantity_attachment'] = $path;
        }
        
        // Parse combined Parliament/DUN dropdown value for Basic Information
        if ($request->parliament_dun_basic) {
            $parts = explode('_', $request->parliament_dun_basic);
            if (count($parts) === 2) {
                $type = $parts[0]; // 'parliament' or 'dun'
                $id = $parts[1];
                
                if ($type === 'parliament') {
                    $data['parliament_id'] = $id;
                    $data['dun_basic_id'] = null;
                } elseif ($type === 'dun') {
                    $data['dun_basic_id'] = $id;
                    $data['parliament_id'] = null;
                }
            }
        } else {
            $data['parliament_id'] = null;
            $data['dun_basic_id'] = null;
        }
        
        // Calculate total cost
        $data['total_cost'] = ($request->actual_project_cost ?? 0) + 
                              ($request->consultation_cost ?? 0) + 
                              ($request->lss_inspection_cost ?? 0) + 
                              ($request->sst ?? 0) + 
                              ($request->others_cost ?? 0);
        
        $data['status'] = 'Waiting for Approval';

        \App\Models\PreProject::create($data);

        return redirect()->route('pages.pre-project')->with('success', 'Pre-Project created successfully');
    }

    public function preProjectUpdate(UpdatePreProjectRequest $request, $id)
    {
        $preProject = \App\Models\PreProject::findOrFail($id);

        // CRITICAL: Validate actual_project_cost does not exceed original_project_cost
        // Only validate if original_project_cost is set and greater than 0
        if (!empty($preProject->original_project_cost) && $preProject->original_project_cost > 0 && !empty($request->actual_project_cost)) {
            if ($request->actual_project_cost > $preProject->original_project_cost) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'actual_project_cost' => 'Actual Project Cost (RM ' . number_format($request->actual_project_cost, 2) . 
                        ') cannot exceed original budget of RM ' . number_format($preProject->original_project_cost, 2) . 
                        '. This cost comes from a cancelled project and cannot be increased.'
                    ]);
            }
        }

        $data = $request->except(['parliament_dun_basic', 'bill_of_quantity_attachment', 'total_cost']);
        
        // Handle file upload
        if ($request->hasFile('bill_of_quantity_attachment')) {
            // Delete old file if exists
            if ($preProject->bill_of_quantity_attachment && \Storage::disk('public')->exists($preProject->bill_of_quantity_attachment)) {
                \Storage::disk('public')->delete($preProject->bill_of_quantity_attachment);
            }
            
            $file = $request->file('bill_of_quantity_attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pre-projects/bill-of-quantity', $filename, 'public');
            $data['bill_of_quantity_attachment'] = $path;
        }
        
        // Parse combined Parliament/DUN dropdown value for Basic Information
        if ($request->parliament_dun_basic) {
            $parts = explode('_', $request->parliament_dun_basic);
            if (count($parts) === 2) {
                $type = $parts[0]; // 'parliament' or 'dun'
                $id = $parts[1];
                
                if ($type === 'parliament') {
                    $data['parliament_id'] = $id;
                    $data['dun_basic_id'] = null;
                } elseif ($type === 'dun') {
                    $data['dun_basic_id'] = $id;
                    $data['parliament_id'] = null;
                }
            }
        } else {
            $data['parliament_id'] = null;
            $data['dun_basic_id'] = null;
        }
        
        // CRITICAL: Auto-calculate total cost (READ ONLY field)
        // User cannot edit total_cost directly - it's calculated from components
        $data['total_cost'] = ($request->actual_project_cost ?? 0) + 
                              ($request->consultation_cost ?? 0) + 
                              ($request->lss_inspection_cost ?? 0) + 
                              ($request->sst ?? 0) + 
                              ($request->others_cost ?? 0);

        $preProject->update($data);

        return redirect()->route('pages.pre-project')->with('success', 'Pre-Project updated successfully');
    }

    public function preProjectDelete($id)
    {
        $preProject = \App\Models\PreProject::findOrFail($id);
        $preProject->delete();

        return redirect()->route('pages.pre-project')->with('success', 'Pre-Project deleted successfully');
    }

    // Pre-Project Approval Methods
    public function preProjectApprove(Request $request, $id)
    {
        $preProject = \App\Models\PreProject::findOrFail($id);
        $user = auth()->user();
        
        // Get pre-project approvers from settings
        $preProjectApproversJson = \App\Models\IntegrationSetting::getSetting('approver', 'pre_project_approvers');
        $preProjectApprovers = $preProjectApproversJson ? json_decode($preProjectApproversJson, true) : [];
        
        // Check if current user is an authorized approver
        if (!in_array($user->id, $preProjectApprovers)) {
            return redirect()->back()->with('error', 'You are not authorized to approve this Pre-Project');
        }
        
        // Pre-Project only has ONE approval level - directly move to EPU Approval
        if (in_array($preProject->status, ['Waiting for Approval', 'Waiting for Approver 1'])) {
            $preProject->update([
                'status' => 'Waiting for EPU Approval',
                'first_approver_id' => $user->id,
                'first_approved_at' => now(),
                'first_approval_remarks' => $request->approval_remarks,
            ]);
            return redirect()->route('pages.pre-project')->with('success', 'Pre-Project approved successfully. Now waiting for EPU approval.');
            
        } else {
            return redirect()->back()->with('error', 'This Pre-Project cannot be approved at this stage');
        }
    }

    public function preProjectReject(Request $request, $id)
    {
        $preProject = \App\Models\PreProject::findOrFail($id);
        $user = auth()->user();
        
        // Get pre-project approvers from settings
        $preProjectApproversJson = \App\Models\IntegrationSetting::getSetting('approver', 'pre_project_approvers');
        $preProjectApprovers = $preProjectApproversJson ? json_decode($preProjectApproversJson, true) : [];
        
        // Check if current user is an authorized approver
        if (!in_array($user->id, $preProjectApprovers)) {
            return redirect()->back()->with('error', 'You are not authorized to reject this Pre-Project');
        }
        
        // Check if Pre-Project is in approval stage
        if (!in_array($preProject->status, ['Waiting for Approval', 'Waiting for Approver 1'])) {
            return redirect()->back()->with('error', 'This Pre-Project cannot be rejected at this stage');
        }
        
        // Validate rejection remarks
        $request->validate([
            'rejection_remarks' => 'required|string|min:10|max:500'
        ], [
            'rejection_remarks.required' => 'Please provide a reason for rejection',
            'rejection_remarks.min' => 'Rejection reason must be at least 10 characters',
            'rejection_remarks.max' => 'Rejection reason cannot exceed 500 characters'
        ]);
        
        // Return to "Waiting for Complete Form" status so Parliament user can edit and resubmit
        $preProject->update([
            'status' => 'Waiting for Complete Form',
            'rejection_remarks' => $request->rejection_remarks,
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'first_approver_id' => null,
            'first_approved_at' => null,
            'second_approver_id' => null,
            'second_approved_at' => null,
            'submitted_to_epu_at' => null,
            'submitted_to_epu_by' => null
        ]);
        
        return redirect()->route('pages.pre-project')->with('success', 'Pre-Project rejected. Parliament user can now edit and resubmit.');
    }

    /**
     * Submit Pre-Project to EPU (First Approval)
     * 
     * @param int $id Pre-Project ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function preProjectSubmitToEpu($id)
    {
        $preProject = \App\Models\PreProject::findOrFail($id);
        $user = auth()->user();
        
        // Authorization: Only Member of Parliament users can submit
        if (!$user->parliament_category_id && !$user->dun_id) {
            return redirect()->back()->with('error', 'You are not authorized to submit Pre-Projects');
        }
        
        // Status check: Must be "Waiting for Complete Form"
        if ($preProject->status !== 'Waiting for Complete Form') {
            return redirect()->back()->with('error', 'This Pre-Project cannot be submitted at this stage');
        }
        
        // Validate data completeness
        if (!$preProject->isDataComplete()) {
            $missingFields = $preProject->getMissingRequiredFields();
            return redirect()->back()
                ->with('error', 'Pre-Project data is incomplete')
                ->with('missing_fields', $missingFields);
        }
        
        // Update status to "Waiting for Approver 1"
        $preProject->update([
            'status' => 'Waiting for Approver 1',
            'submitted_to_epu_at' => now(),
            'submitted_to_epu_by' => $user->id,
        ]);
        
        return redirect()->route('pages.pre-project')
            ->with('success', 'Pre-Project submitted successfully. Waiting for Approver 1.');
    }

    public function preProjectEdit($id)
    {
        $preProject = \App\Models\PreProject::with([
            'residenCategory',
            'agencyCategory',
            'parliament',
            'dunBasic',
            'projectCategory',
            'division',
            'district',
            'parliamentLocation',
            'dun',
            'landTitleStatus',
            'implementingAgency',
            'implementationMethod',
            'projectOwnership',
            'firstApprover',
            'secondApprover',
            'rejectedBy',
            'submittedToEpuBy',
            'project' // Load project relationship to check if transferred
        ])->findOrFail($id);
        
        // Check if this pre-project was created from NOC (exists in noc_project table as nama_projek_baru)
        $nocEntry = \DB::table('noc_project')
            ->where('nama_projek_baru', $preProject->name)
            ->first();
        
        // If found in NOC, get the kos_baru value
        $isFromNoc = $nocEntry !== null;
        $nocBudget = $isFromNoc ? $nocEntry->kos_baru : null;
        
        // Get NOC changes if this pre-project has been transferred to a project
        $nocChanges = [];
        $nocs = [];
        
        if ($preProject->project) {
            // Load NOCs through the project relationship
            $project = \App\Models\Project::with([
                'nocs.creator.parliament',
                'nocs.creator.dun',
                'nocs.firstApprover',
                'nocs.secondApprover'
            ])->find($preProject->project->id);
            
            if ($project && $project->nocs) {
                foreach ($project->nocs as $noc) {
                    // Get ALL entries from noc_project table for this NOC (including new projects without project_id)
                    $allEntries = \DB::table('noc_project')
                        ->where('noc_id', $noc->id)
                        ->get();
                    
                    foreach ($allEntries as $pivotData) {
                        $nocNote = \App\Models\NocNote::find($pivotData->noc_note_id);
                        $nocChanges[] = [
                            'noc_number' => $noc->noc_number,
                            'tahun_rtp' => $pivotData->tahun_rtp,
                            'no_projek' => $pivotData->no_projek,
                            'nama_projek_asal' => $pivotData->nama_projek_asal,
                            'nama_projek_baru' => $pivotData->nama_projek_baru,
                            'kos_asal' => $pivotData->kos_asal,
                            'kos_baru' => $pivotData->kos_baru,
                            'agensi_pelaksana_asal' => $pivotData->agensi_pelaksana_asal,
                            'agensi_pelaksana_baru' => $pivotData->agensi_pelaksana_baru,
                            'noc_note_name' => $nocNote ? $nocNote->name : null,
                        ];
                    }
                }
                
                // Add NOC data to response
                $nocs = $project->nocs;
            }
        }
        
        $preProject->noc_changes = $nocChanges;
        $preProject->nocs = $nocs;
        $preProject->is_from_noc = $isFromNoc;
        $preProject->noc_budget = $nocBudget;
        
        return response()->json($preProject);
    }

    public function preProjectBudgetInfo(Request $request)
    {
        $user = auth()->user();
        $year = $request->input('year', now()->year);
        
        $budgetService = new \App\Services\BudgetCalculationService();
        $budgetInfo = $budgetService->getUserBudgetInfo($user, $year);
        
        return response()->json($budgetInfo);
    }

    public function preProjectPrint($id): View
    {
        $preProject = \App\Models\PreProject::with([
            'residenCategory',
            'agencyCategory',
            'parliament',
            'dunBasic',
            'projectCategory',
            'division',
            'district',
            'parliamentLocation',
            'dun',
            'landTitleStatus',
            'implementingAgency',
            'implementationMethod',
            'projectOwnership'
        ])->findOrFail($id);
        
        return view('pages.pre-project-print', compact('preProject'));
    }

    public function projectNoc(): View
    {
        $user = auth()->user();
        
        // Filter NOCs based on user's Parliament/DUN/Agency
        $nocsQuery = \App\Models\Noc::with(['parliament', 'dun', 'creator', 'projects']);
        
        if ($user->parliament_id) {
            // Parliament user - show NOCs for their Parliament
            $nocsQuery->where('parliament_id', $user->parliament_id);
        } elseif ($user->dun_id) {
            // DUN user - show NOCs for their DUN
            $nocsQuery->where('dun_id', $user->dun_id);
        } elseif ($user->agency_category_id) {
            // Agency user - show NOCs that contain projects from their Agency
            // Check through noc_project pivot table for agency match
            $nocsQuery->whereHas('projects', function($query) use ($user) {
                $query->where('agency_category_id', $user->agency_category_id);
            });
        }
        // Admin/Residen users see all NOCs (no filter)
        
        $nocs = $nocsQuery->orderBy('created_at', 'desc')->get();
        
        return view('pages.project-noc', compact('nocs'));
    }

    public function projectNocCreate(): View
    {
        $user = auth()->user();
        
        // Get NOC Notes for dropdown
        $nocNotes = \App\Models\NocNote::where('status', 'Active')->orderBy('name')->get();
        
        // Get Agencies for dropdown
        $agencies = \App\Models\AgencyCategory::where('status', 'Active')->orderBy('name')->get();
        
        // Get available projects using Noc::getAvailableProjects()
        $projects = \App\Models\Noc::getAvailableProjects($user);
        
        return view('pages.project-noc-create', compact('projects', 'nocNotes', 'agencies'));
    }

    public function projectNocStore(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'noc_date' => 'required|date',
            'projects' => 'required|array|min:1',
            'projects.*.tahun_rtp' => 'required|string',
            'projects.*.no_projek' => 'nullable|string',
            'projects.*.noc_note_id' => 'required|exists:noc_notes,id',
            'noc_letter_attachment' => 'required|file|mimes:pdf|max:5120',
            'noc_project_list_attachment' => 'required|file|mimes:pdf|max:5120',
        ]);

        // Handle file uploads
        $nocLetterPath = null;
        $nocProjectListPath = null;
        
        if ($request->hasFile('noc_letter_attachment')) {
            $nocLetterPath = $request->file('noc_letter_attachment')->store('noc_attachments', 'public');
        }
        
        if ($request->hasFile('noc_project_list_attachment')) {
            $nocProjectListPath = $request->file('noc_project_list_attachment')->store('noc_attachments', 'public');
        }

        // Determine Parliament/DUN from the first imported project (not from user)
        $parliamentId = null;
        $dunId = null;
        
        // Loop through projects to find the first imported project
        foreach ($request->projects as $projectData) {
            if (isset($projectData['project_id']) && $projectData['project_id']) {
                // Get the project to retrieve its parliament/dun
                $project = \App\Models\Project::find($projectData['project_id']);
                if ($project) {
                    $parliamentId = $project->parliament_id;
                    $dunId = $project->dun_id;
                    break; // Use the first imported project's parliament/dun
                }
            }
        }

        $noc = \App\Models\Noc::create([
            'noc_number' => \App\Models\Noc::generateNocNumber(),
            'parliament_id' => $parliamentId,
            'dun_id' => $dunId,
            'noc_date' => $request->noc_date,
            'created_by' => auth()->id(),
            'status' => 'Waiting for Approval 1',
            'noc_letter_attachment' => $nocLetterPath,
            'noc_project_list_attachment' => $nocProjectListPath,
        ]);

        // Process each project (both imported and new)
        foreach ($request->projects as $projectData) {
            $projectId = $projectData['project_id'] ?? null;
            
            // If this is an imported project, attach it
            if ($projectId) {
                $noc->projects()->attach($projectId, [
                    'tahun_rtp' => $projectData['tahun_rtp'],
                    'no_projek' => $projectData['no_projek'] ?? null,
                    'nama_projek_asal' => $projectData['nama_projek_asal'] ?? null,
                    'nama_projek_baru' => $projectData['nama_projek_baru'] ?? null,
                    'kos_asal' => $projectData['kos_asal'] ?? null,
                    'kos_baru' => $projectData['kos_baru'] ?? null,
                    'agensi_pelaksana_asal' => $projectData['agensi_pelaksana_asal'] ?? null,
                    'agensi_pelaksana_baru' => $projectData['agensi_pelaksana_baru'] ?? null,
                    'noc_note_id' => $projectData['noc_note_id'],
                ]);
                
                // Get the project
                $project = \App\Models\Project::find($projectId);
                
                // Update project status based on NOC note
                $nocNote = \App\Models\NocNote::find($projectData['noc_note_id']);
                if ($nocNote && $project) {
                    // Update project status to the NOC note name
                    $project->update(['status' => $nocNote->name]);
                    
                    // CRITICAL: Update related pre-project status to "NOC" to exclude from budget calculation
                    // This ensures pre-projects are marked as "NOC" and excluded from budget tracking
                    if ($project->pre_project_id) {
                        $preProject = \App\Models\PreProject::find($project->pre_project_id);
                        if ($preProject) {
                            $preProject->update(['status' => 'NOC']);
                        }
                    }
                }
            } else {
                // This is a new project - create a record in pivot table without project_id
                \DB::table('noc_project')->insert([
                    'noc_id' => $noc->id,
                    'project_id' => null,
                    'tahun_rtp' => $projectData['tahun_rtp'],
                    'no_projek' => $projectData['no_projek'] ?? null,
                    'nama_projek_asal' => $projectData['nama_projek_asal'] ?? null,
                    'nama_projek_baru' => $projectData['nama_projek_baru'] ?? null,
                    'kos_asal' => $projectData['kos_asal'] ?? null,
                    'kos_baru' => $projectData['kos_baru'] ?? null,
                    'agensi_pelaksana_asal' => $projectData['agensi_pelaksana_asal'] ?? null,
                    'agensi_pelaksana_baru' => $projectData['agensi_pelaksana_baru'] ?? null,
                    'noc_note_id' => $projectData['noc_note_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('pages.project.noc')->with('success', 'NOC created successfully. Pre-projects will be created after final approval.');
    }

    public function projectNocShow($id): View
    {
        $noc = \App\Models\Noc::with([
            'parliament', 
            'dun', 
            'creator', 
            'firstApprover', 
            'secondApprover',
        ])->findOrFail($id);
        
        // Get ALL project entries (including new projects without project_id)
        $projectEntries = $noc->getAllProjectEntries();
        
        return view('pages.project-noc-show', compact('noc', 'projectEntries'));
    }

    public function projectNocSubmit($id)
        {
            $noc = \App\Models\Noc::findOrFail($id);

            // This method is no longer needed since NOC is created with "Waiting for Approval 1" status
            // But keep it for backward compatibility
            if ($noc->status !== 'Waiting for Approval 1') {
                return redirect()->back()->with('error', 'NOC has already been submitted');
            }

            // Update all imported projects status to 'NOC'
            foreach ($noc->projects as $project) {
                $project->update(['status' => 'NOC']);
            }

            // Process NOC changes and create pre-projects for EPU approval
            $nocToPreProjectService = app(\App\Services\NocToPreProjectService::class);
            $createdPreProjects = $nocToPreProjectService->processNocSubmission($noc);

            // Log created pre-projects for tracking
            if (count($createdPreProjects) > 0) {
                \Illuminate\Support\Facades\Log::info("NOC {$noc->noc_number} created " . count($createdPreProjects) . " pre-project records for EPU approval");
            }

            $message = 'NOC is waiting for approval';
            if (count($createdPreProjects) > 0) {
                $message .= '. ' . count($createdPreProjects) . ' project change(s) sent to Pre-Project for EPU approval.';
            }

            return redirect()->route('pages.project.noc.show', $id)->with('success', $message);
        }

    public function projectNocApprove(Request $request, $id)
    {
        $noc = \App\Models\Noc::findOrFail($id);
        $user = auth()->user();
        
        $firstApprover = \App\Models\IntegrationSetting::getSetting('application', 'first_approval_user');
        $secondApprover = \App\Models\IntegrationSetting::getSetting('application', 'second_approval_user');

        if ($noc->status === 'Waiting for Approval 1' && $user->id == $firstApprover) {
            $noc->update([
                'status' => 'Waiting for Approval 2',
                'first_approver_id' => $user->id,
                'first_approved_at' => now(),
                'first_approval_remarks' => $request->remarks,
            ]);
            return redirect()->back()->with('success', 'NOC approved (First Approval)');
        }

        if ($noc->status === 'Waiting for Approval 2' && $user->id == $secondApprover) {
            $noc->update([
                'status' => 'Approved',
                'second_approver_id' => $user->id,
                'second_approved_at' => now(),
                'second_approval_remarks' => $request->remarks,
            ]);
            
            // AUTOMATICALLY CREATE PRE-PROJECTS FROM NOC DATA AFTER FINAL APPROVAL
            $nocService = new \App\Services\NocToPreProjectService();
            $createdPreProjects = $nocService->processNocSubmission($noc);
            
            // Log the created pre-projects
            \Log::info('NOC approved with pre-projects created', [
                'noc_id' => $noc->id,
                'noc_number' => $noc->noc_number,
                'pre_projects_created' => count($createdPreProjects),
            ]);
            
            return redirect()->back()->with('success', 'NOC approved (Final Approval). ' . count($createdPreProjects) . ' pre-project(s) created successfully.');
        }

        return redirect()->back()->with('error', 'You are not authorized to approve this NOC');
    }

    public function projectNocReject(Request $request, $id)
    {
        $noc = \App\Models\Noc::findOrFail($id);
        
        $noc->update([
            'status' => 'Rejected',
            'first_approval_remarks' => $request->remarks,
        ]);

        // Rollback all imported projects status to 'Active'
        foreach ($noc->projects as $project) {
            $project->update(['status' => 'Active']);
            
            // CRITICAL: Rollback related pre-project status to "Approved"
            // This ensures pre-projects are included back in budget calculation when NOC is rejected
            if ($project->pre_project_id) {
                $preProject = \App\Models\PreProject::find($project->pre_project_id);
                if ($preProject) {
                    $preProject->update(['status' => 'Approved']);
                }
            }
        }

        return redirect()->back()->with('success', 'NOC rejected');
    }

    public function projectNocPrint($id): View
    {
        $noc = \App\Models\Noc::with([
            'parliament', 
            'dun', 
            'creator', 
            'firstApprover', 
            'secondApprover',
        ])->findOrFail($id);
        
        // Get ALL project entries (including new projects without project_id)
        $projectEntries = $noc->getAllProjectEntries();
        
        return view('pages.project-noc-print', compact('noc', 'projectEntries'));
    }

    public function projectNocDelete($id)
    {
        $noc = \App\Models\Noc::findOrFail($id);
        
        // Only allow deletion of NOCs that are not yet approved
        if ($noc->status === 'Approved') {
            return redirect()->back()->with('error', 'Approved NOCs cannot be deleted');
        }

        // Rollback all imported projects status to 'Active'
        foreach ($noc->projects as $project) {
            $project->update(['status' => 'Active']);
            
            // CRITICAL: Rollback related pre-project status to "Approved"
            // This ensures pre-projects are included back in budget calculation
            if ($project->pre_project_id) {
                $preProject = \App\Models\PreProject::find($project->pre_project_id);
                if ($preProject) {
                    $preProject->update(['status' => 'Approved']);
                }
            }
        }

        // Delete attachments
        if ($noc->noc_letter_attachment && \Storage::disk('public')->exists($noc->noc_letter_attachment)) {
            \Storage::disk('public')->delete($noc->noc_letter_attachment);
        }
        
        if ($noc->noc_project_list_attachment && \Storage::disk('public')->exists($noc->noc_project_list_attachment)) {
            \Storage::disk('public')->delete($noc->noc_project_list_attachment);
        }

        // Delete NOC (cascade will delete pivot table entries)
        $noc->delete();

        return redirect()->route('pages.project.noc')->with('success', 'NOC deleted successfully. All imported projects have been reverted to Active status.');
    }

    public function project(): View
    {
        $user = auth()->user();
        
        // Filter projects based on user's Parliament/DUN/Agency
        $projectsQuery = \App\Models\Project::query();
        
        // Apply access control filter
        if ($user->parliament_id) {
            // User under Parliament - only show projects for their Parliament
            $projectsQuery->where('parliament_id', $user->parliament_id);
        } elseif ($user->dun_id) {
            // User under DUN - only show projects for their DUN
            $projectsQuery->where('dun_basic_id', $user->dun_id);
        } elseif ($user->agency_category_id) {
            // User under Agency - only show projects for their Agency
            $projectsQuery->where('agency_category_id', $user->agency_category_id);
        }
        // Admin/Residen users see all projects (no filter)
        
        // Exclude projects with status "NOC" and "Projek Dibatalkan" - they should appear in Project Cancel tab
        $projects = $projectsQuery
            ->whereNotIn('status', ['NOC', 'Projek Dibatalkan'])
            ->with(['parliament', 'dun'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('pages.project', compact('projects'));
    }

    public function projectShow($id)
    {
        $project = \App\Models\Project::with([
            'parliament',
            'dun',
            'dunBasic',
            'projectCategory',
            'division',
            'district',
            'parliamentLocation',
            'landTitleStatus',
            'agencyCategory',
            'implementingAgency',
            'implementationMethod',
            'projectOwnership',
            'residenCategory',
            'nocs' => function($query) {
                $query->with([
                    'creator.parliament', 
                    'creator.dun', 
                    'parliament', 
                    'dun',
                    'firstApprover',  // Load actual first approver
                    'secondApprover'  // Load actual second approver
                ])
                ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);
        
        // Get ALL NOC entries for NOCs that contain this project
        // This includes both imported projects and "Add New" projects in the same NOC
        $nocIds = \DB::table('noc_project')
            ->where('project_id', $id)
            ->pluck('noc_id')
            ->unique();
        
        $nocChanges = \DB::table('noc_project')
            ->join('nocs', 'noc_project.noc_id', '=', 'nocs.id')
            ->leftJoin('noc_notes', 'noc_project.noc_note_id', '=', 'noc_notes.id')
            ->whereIn('noc_project.noc_id', $nocIds)
            ->select(
                'nocs.id as noc_id',
                'nocs.noc_number',
                'nocs.noc_date',
                'nocs.status as noc_status',
                'nocs.created_at as noc_created_at',
                'noc_project.*',
                'noc_notes.name as noc_note_name'
            )
            ->orderBy('nocs.created_at', 'desc')
            ->orderBy('noc_project.id', 'asc')
            ->get();

        
        $project->noc_changes = $nocChanges;
        
        // Add approver user objects to each NOC for JavaScript access
        foreach ($project->nocs as $noc) {
            $noc->first_approver_user = $noc->firstApprover;
            $noc->second_approver_user = $noc->secondApprover;
        }
        
        return response()->json($project);
    }

    public function projectEdit($id)
    {
        // Return project data as JSON for modal display
        $project = Project::with([
            'parliament',
            'dun',
            'residenCategory',
            'agencyCategory',
            'projectCategory',
            'division',
            'district',
            'parliamentLocation',
            'landTitleStatus',
            'implementingAgency',
            'implementationMethod',
            'projectOwnership',
            'nocs' => function($query) {
                $query->with([
                    'creator.parliament',
                    'creator.dun',
                    'firstApprover',
                    'secondApprover'
                ]);
            }
        ])->findOrFail($id);
        
        // Get NOC changes for this project
        $nocChanges = [];
        foreach ($project->nocs as $noc) {
            $changes = \DB::table('noc_project')
                ->where('noc_id', $noc->id)
                ->where('project_id', $project->id)
                ->get();
            
            foreach ($changes as $change) {
                $nocChanges[] = [
                    'noc_id' => $noc->id,
                    'noc_number' => $noc->noc_number,
                    'tahun_rtp' => $change->tahun_rtp,
                    'no_projek' => $change->no_projek,
                    'nama_projek_asal' => $change->nama_projek_asal,
                    'nama_projek_baru' => $change->nama_projek_baru,
                    'kos_asal' => $change->kos_asal,
                    'kos_baru' => $change->kos_baru,
                    'agensi_pelaksana_asal' => $change->agensi_pelaksana_asal,
                    'agensi_pelaksana_baru' => $change->agensi_pelaksana_baru,
                    'noc_note_id' => $change->noc_note_id,
                    'noc_note_name' => $change->noc_note_id ? \App\Models\NocNote::find($change->noc_note_id)?->name : null,
                ];
            }
        }
        
        $projectData = $project->toArray();
        $projectData['noc_changes'] = $nocChanges;
        
        return response()->json($projectData);
    }

    public function projectCancel(): View
    {
        $user = auth()->user();
        
        // Filter cancelled projects based on user's parliament_id, dun_id, or agency_category_id
        $query = Project::with([
            'parliament',
            'dun',
            'agencyCategory',
            'projectCategory',
            'division',
            'district'
        ])
        ->whereIn('status', ['Projek Dibatalkan', 'NOC']);
        
        // Apply access control filter
        if ($user->parliament_id) {
            // User under Parliament - only show projects for their Parliament
            $query->where('parliament_id', $user->parliament_id);
        } elseif ($user->dun_id) {
            // User under DUN - only show projects for their DUN
            $query->where('dun_basic_id', $user->dun_id);
        } elseif ($user->agency_category_id) {
            // User under Agency - only show projects for their Agency
            $query->where('agency_category_id', $user->agency_category_id);
        }
        // Admin/Residen users see all cancelled projects (no filter)
        
        $cancelledProjects = $query->orderBy('created_at', 'desc')->get();
        
        return view('pages.project-cancel', compact('cancelledProjects', 'user'));
    }

    public function projectTransferCreate(): View
    {
        $user = auth()->user();
        
        // Get pre-projects with status "Waiting for EPU Approval" that haven't been transferred yet
        $preProjectsQuery = \App\Models\PreProject::query()
            ->whereDoesntHave('project')
            ->where('status', 'Waiting for EPU Approval');
        
        // Filter by user's Parliament/DUN
        if ($user->parliament_id) {
            $preProjectsQuery->where('parliament_id', $user->parliament_id);
        } elseif ($user->dun_id) {
            $preProjectsQuery->where('dun_id', $user->dun_id);
        }
        
        $preProjects = $preProjectsQuery->orderBy('created_at', 'desc')->get();
        
        return view('pages.project-transfer', compact('preProjects'));
    }

    public function projectTransferStore(Request $request)
    {
        $request->validate([
            'pre_project_id' => 'required|exists:pre_projects,id',
            'project_number' => 'required|string|max:255',
            'project_year' => 'required|string|max:4',
        ]);

        $preProject = \App\Models\PreProject::findOrFail($request->pre_project_id);
        
        // Check if already transferred
        $transferService = new \App\Services\ProjectTransferService();
        if (!$transferService->canTransfer($preProject)) {
            return redirect()->route('pages.project.transfer.create')
                ->with('error', 'Pre-Project ini sudah ditransfer ke Project.');
        }

        try {
            $project = $transferService->transfer(
                $preProject,
                $request->project_number,
                $request->project_year
            );
            
            return redirect()->route('pages.project')
                ->with('success', 'Pre-Project berjaya ditransfer ke Project. No Projek: ' . $project->project_number);
        } catch (\Exception $e) {
            \Log::error('Failed to transfer pre-project to project', [
                'pre_project_id' => $preProject->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('pages.project.transfer.create')
                ->with('error', 'Gagal transfer Pre-Project. Sila cuba lagi.');
        }
    }

    public function contractorAnalysis(): View
    {
        return view('pages.contractor-analysis');
    }

    public function masterDataProjectOwnership(): View
    {
        $ownerships = \App\Models\ProjectOwnership::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.project-ownership', compact('ownerships'));
    }

    public function masterDataProjectOwnershipStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:project_ownerships,code',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\ProjectOwnership::create($request->all());

        return redirect()->route('pages.master-data.project-ownership')->with('success', 'Project Ownership created successfully');
    }

    public function masterDataProjectOwnershipUpdate(Request $request, $id)
    {
        $ownership = \App\Models\ProjectOwnership::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:project_ownerships,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $ownership->update($request->all());

        return redirect()->route('pages.master-data.project-ownership')->with('success', 'Project Ownership updated successfully');
    }

    public function masterDataProjectOwnershipDelete($id)
    {
        $ownership = \App\Models\ProjectOwnership::findOrFail($id);
        $ownership->delete();

        return redirect()->route('pages.master-data.project-ownership')->with('success', 'Project Ownership deleted successfully');
    }

    public function masterDataImplementationMethod(): View
    {
        $methods = \App\Models\ImplementationMethod::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.implementation-method', compact('methods'));
    }

    public function masterDataImplementationMethodStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:implementation_methods,code',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\ImplementationMethod::create($request->all());

        return redirect()->route('pages.master-data.implementation-method')->with('success', 'Implementation Method created successfully');
    }

    public function masterDataImplementationMethodUpdate(Request $request, $id)
    {
        $method = \App\Models\ImplementationMethod::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:implementation_methods,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $method->update($request->all());

        return redirect()->route('pages.master-data.implementation-method')->with('success', 'Implementation Method updated successfully');
    }

    public function masterDataImplementationMethodDelete($id)
    {
        $method = \App\Models\ImplementationMethod::findOrFail($id);
        $method->delete();

        return redirect()->route('pages.master-data.implementation-method')->with('success', 'Implementation Method deleted successfully');
    }

    // Master Data - NOC Note
    public function masterDataNocNote(): View
    {
        $notes = \App\Models\NocNote::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.noc-note', compact('notes'));
    }

    public function masterDataNocNoteStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:noc_notes,code',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\NocNote::create($request->all());

        return redirect()->route('pages.master-data.noc-note')->with('success', 'NOC Note created successfully');
    }

    public function masterDataNocNoteUpdate(Request $request, $id)
    {
        $note = \App\Models\NocNote::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:noc_notes,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $note->update($request->all());

        return redirect()->route('pages.master-data.noc-note')->with('success', 'NOC Note updated successfully');
    }

    public function masterDataNocNoteDelete($id)
    {
        $note = \App\Models\NocNote::findOrFail($id);
        $note->delete();

        return redirect()->route('pages.master-data.noc-note')->with('success', 'NOC Note deleted successfully');
    }
}
