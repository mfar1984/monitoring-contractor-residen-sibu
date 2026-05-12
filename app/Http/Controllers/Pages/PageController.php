<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\StorePreProjectRequest;
use App\Http\Requests\UpdatePreProjectRequest;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function overview(): View
    {
        return view('pages.overview');
    }

    // Profile Page
    public function profile(): View
    {
        $user = auth()->user();
        return view('pages.profile', compact('user'));
    }

    // Profile Update
    public function profileUpdate(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'contact_number' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:255',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // Update basic info
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->contact_number = $request->contact_number;
        $user->department = $request->department;

        // Update password if provided
        if ($request->filled('current_password')) {
            if (!\Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            $user->password = \Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->route('pages.profile')->with('success', 'Profile updated successfully');
    }

    // Settings Page
    public function settings(): View
    {
        $user = auth()->user();
        
        // Get user-specific settings
        $settings = \App\Models\IntegrationSetting::getSettings('user_' . $user->id);
        
        return view('pages.settings', compact('user', 'settings'));
    }

    // Settings Update
    public function settingsUpdate(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'locale' => 'nullable|string|in:en,ms,zh',
            'timezone' => 'nullable|string',
            'items_per_page' => 'nullable|integer|min:10|max:100',
            'date_format' => 'nullable|string|in:d/m/Y,m/d/Y,Y-m-d,d M Y,d F Y',
            'time_format' => 'nullable|string|in:H:i:s,h:i A,h:i:s A',
            'currency' => 'nullable|string|in:MYR,USD,SGD,EUR',
        ]);

        // Save localization preferences
        if ($request->has('locale')) {
            \App\Models\IntegrationSetting::setSetting('user_' . $user->id, 'locale', $request->locale);
            // Apply locale immediately
            app()->setLocale($request->locale);
            session(['locale' => $request->locale]);
        }
        if ($request->has('timezone')) {
            \App\Models\IntegrationSetting::setSetting('user_' . $user->id, 'timezone', $request->timezone);
        }
        if ($request->has('currency')) {
            \App\Models\IntegrationSetting::setSetting('user_' . $user->id, 'currency', $request->currency);
        }

        // Save display preferences
        if ($request->has('items_per_page')) {
            \App\Models\IntegrationSetting::setSetting('user_' . $user->id, 'items_per_page', $request->items_per_page);
        }
        if ($request->has('date_format')) {
            \App\Models\IntegrationSetting::setSetting('user_' . $user->id, 'date_format', $request->date_format);
        }
        if ($request->has('time_format')) {
            \App\Models\IntegrationSetting::setSetting('user_' . $user->id, 'time_format', $request->time_format);
        }
        
        return redirect()->route('pages.settings')->with('success', 'Settings updated successfully');
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

    // Export translations to CSV
    public function generalTranslationExport(Request $request)
    {
        $lang = $request->get('lang', 'en');
        $language = \App\Models\Language::where('code', $lang)->first();
        
        if (!$language) {
            return redirect()->back()->with('error', 'Language not found');
        }

        $translations = \App\Models\IntegrationSetting::getSettings('translation_' . $lang);
        
        // Define all translation keys in correct order
        $translationKeys = [
            'overview' => 'Overview',
            'project' => 'Project',
            'pre_project' => 'Pre Project',
            'drawing_lots' => 'Drawing Lots',
            'contractor_analysis' => 'Contractor Analysis',
            'financial_analysis' => 'Financial Analysis',
            'system_settings' => 'System Settings',
            'general' => 'General',
            'master_data' => 'Master Data',
            'group_roles' => 'Group Roles',
            'users_id' => 'Users ID',
            'integrations' => 'Integrations',
            'activity_log' => 'Activity Log',
        ];

        // Create CSV content
        $filename = 'translations_' . $lang . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($translationKeys, $translations, $language) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header row
            fputcsv($file, ['English', $language->name]);
            
            // Data rows
            foreach ($translationKeys as $key => $englishText) {
                fputcsv($file, [
                    $englishText,
                    $translations[$key] ?? ''
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Import translations from CSV
    public function generalTranslationImport(Request $request)
    {
        $request->validate([
            'language' => 'required|string|exists:languages,code',
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $lang = $request->language;
        
        try {
            $file = $request->file('file');
            $handle = fopen($file->getRealPath(), 'r');
            
            // Define translation keys mapping (English text => key)
            $keyMapping = [
                'Overview' => 'overview',
                'Project' => 'project',
                'Pre Project' => 'pre_project',
                'Drawing Lots' => 'drawing_lots',
                'Contractor Analysis' => 'contractor_analysis',
                'Financial Analysis' => 'financial_analysis',
                'System Settings' => 'system_settings',
                'General' => 'general',
                'Master Data' => 'master_data',
                'Group Roles' => 'group_roles',
                'Users ID' => 'users_id',
                'Integrations' => 'integrations',
                'Activity Log' => 'activity_log',
            ];
            
            $rowNumber = 0;
            $imported = 0;
            
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $rowNumber++;
                
                // Skip header row
                if ($rowNumber === 1) {
                    continue;
                }
                
                if (count($row) >= 2) {
                    $englishText = trim($row[0] ?? '');
                    $translation = trim($row[1] ?? '');
                    
                    // Find the key for this English text
                    if (isset($keyMapping[$englishText]) && !empty($translation)) {
                        $key = $keyMapping[$englishText];
                        
                        \App\Models\IntegrationSetting::setSetting(
                            'translation_' . $lang,
                            $key,
                            $translation
                        );
                        
                        $imported++;
                    }
                }
            }
            
            fclose($handle);
            
            return redirect()
                ->route('pages.general.translation', ['lang' => $lang])
                ->with('success', "Translations imported successfully ($imported items)");
        } catch (\Exception $e) {
            return redirect()
                ->route('pages.general.translation', ['lang' => $lang])
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // Language Management
    public function addLanguage(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:255',
        ]);

        $languageCode = strtolower($request->code);

        \App\Models\Language::create([
            'code' => $languageCode,
            'name' => $request->name,
            'is_default' => false,
            'status' => 'Active'
        ]);

        // Automatically create translation entries for all menu items in correct order
        $translationType = 'translation_' . $languageCode;
        
        // Define all translation keys in the correct order (matching sidebar menu structure)
        $translationKeys = [
            'overview',
            'project',
            'pre_project',
            'drawing_lots',
            'contractor_analysis',
            'financial_analysis',
            'system_settings',
            'general',
            'master_data',
            'group_roles',
            'users_id',
            'integrations',
            'activity_log',
        ];

        // Create empty translation entries for each key
        foreach ($translationKeys as $key) {
            \App\Models\IntegrationSetting::setSetting($translationType, $key, '');
        }

        return redirect()->route('pages.general.localization')->with('success', 'Language added successfully. Please go to Translation page to add translations.');
    }

    public function deleteLanguage($id)
    {
        $language = \App\Models\Language::findOrFail($id);
        
        // Prevent deletion of default languages
        if ($language->is_default) {
            return redirect()->route('pages.general.localization')->with('error', 'Cannot delete default language');
        }

        // Delete all translation entries for this language
        $translationType = 'translation_' . $language->code;
        \App\Models\IntegrationSetting::where('type', $translationType)->delete();

        // Delete the language record
        $language->delete();

        return redirect()->route('pages.general.localization')->with('success', 'Language and all its translations deleted successfully');
    }

    // General Settings - Legal
    public function generalLegal(): View
    {
        $settings = \App\Models\IntegrationSetting::getSettings('legal');
        return view('pages.general.legal', compact('settings'));
    }

    public function generalLegalStore(Request $request)
    {
        $request->validate([
            'disclaimer' => 'nullable|string',
            'privacy' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        \App\Models\IntegrationSetting::setSetting('legal', 'disclaimer', $request->disclaimer);
        \App\Models\IntegrationSetting::setSetting('legal', 'privacy', $request->privacy);
        \App\Models\IntegrationSetting::setSetting('legal', 'terms', $request->terms);

        return redirect()->route('pages.general.legal')->with('success', 'Legal information saved successfully');
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
        $contractors = \App\Models\ContractorCategory::orderBy('created_at', 'desc')->get();
        $upkjClasses = \App\Models\ContractorCategory::getUpkjClasses();
        
        return view('pages.master-data.contractor', compact('contractors', 'upkjClasses'));
    }

    public function masterDataContractorCreate(): View
    {
        $upkjClasses = \App\Models\ContractorCategory::getUpkjClasses();
        
        // Get divisions and districts from database
        $divisions = \App\Models\Division::where('status', 'Active')->orderBy('name')->get();
        $districts = \App\Models\District::where('status', 'Active')->orderBy('name')->get();
        
        return view('pages.master-data.contractor-create', compact('upkjClasses', 'divisions', 'districts'));
    }

    public function masterDataContractorStore(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:contractor_categories,code',
            'registration_number' => 'required|string|max:255',
            'company_type' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone_no' => 'nullable|string|max:255',
            'mobile_no' => 'nullable|string|max:255',
            'upkj_class' => 'nullable|string|max:255',
            'upkj_head' => 'nullable|string|max:255',
            'upkj_subhead' => 'nullable|string',
            'shareholders' => 'nullable|array',
            'shareholders.*.name' => 'required_with:shareholders|string|max:255',
            'shareholders.*.registration_no' => 'nullable|string|max:255',
            'shareholders.*.shares' => 'nullable|numeric|min:0|max:100',
            'directors' => 'nullable|array',
            'directors.*.name' => 'required_with:directors|string|max:255',
            'directors.*.ic_number' => 'nullable|string|max:255',
            'directors.*.shares' => 'nullable|numeric|min:0|max:100',
            'manpower_sole_proprietor' => 'nullable|integer|min:0',
            'manpower_management' => 'nullable|integer|min:0',
            'manpower_professional' => 'nullable|integer|min:0',
            'manpower_sub_professional' => 'nullable|integer|min:0',
            'manpower_competent_worker' => 'nullable|integer|min:0',
            'status' => 'required|in:Active,Inactive,Pending,Rejected,Suspended',
            
            // UPKJ records validation
            'upkj' => 'nullable|array',
            'upkj.*.category' => 'required_with:upkj|string|in:Works,Supplies & Services,Electrical,Mechanical',
            'upkj.*.registration_status' => 'required_with:upkj|string|in:Valid,Expired,Pending',
            'upkj.*.validity_period' => 'nullable|string|max:255',
            'upkj.*.bumiputera_status' => 'nullable|string|in:Yes,No',
            'upkj.*.bumiputera_validity' => 'nullable|string|max:255',
            'upkj.*.certificate_no' => 'nullable|string|max:255',
            'upkj.*.classifications' => 'required_with:upkj|array|min:1',
            'upkj.*.classifications.*.class' => 'required|string',
            'upkj.*.classifications.*.head_code' => 'required|string',
            'upkj.*.classifications.*.subhead_code' => 'nullable|string',
        ]);

        $data = $request->except(['shareholders', 'directors', 'upkj']);
        
        // Handle shareholders data (combine both tables into one JSON field)
        $shareholdersData = [];
        if ($request->has('shareholders')) {
            foreach ($request->shareholders as $shareholder) {
                $shareholdersData[] = [
                    'type' => 'company',
                    'name' => $shareholder['name'],
                    'registration_no' => $shareholder['registration_no'] ?? null,
                    'shares' => $shareholder['shares'] ?? 0,
                ];
            }
        }
        if ($request->has('directors')) {
            foreach ($request->directors as $director) {
                $shareholdersData[] = [
                    'type' => 'individual',
                    'name' => $director['name'],
                    'ic_number' => $director['ic_number'] ?? null,
                    'shares' => $director['shares'] ?? 0,
                ];
            }
        }
        $data['shareholders_data'] = $shareholdersData;
        
        // Calculate total manpower
        $data['manpower_total'] = ($request->manpower_sole_proprietor ?? 0) +
                                   ($request->manpower_management ?? 0) +
                                   ($request->manpower_professional ?? 0) +
                                   ($request->manpower_sub_professional ?? 0) +
                                   ($request->manpower_competent_worker ?? 0);

        DB::beginTransaction();
        try {
            $contractor = \App\Models\ContractorCategory::create($data);

            // Create UPKJ records if provided
            if ($request->has('upkj') && is_array($request->upkj)) {
                foreach ($request->upkj as $upkjData) {
                    // Build classifications array
                    $classifications = [];
                    if (isset($upkjData['classifications']) && is_array($upkjData['classifications'])) {
                        foreach ($upkjData['classifications'] as $classification) {
                            $classifications[] = [
                                'id' => $classification['id'] ?? null,
                                'class' => $classification['class'] ?? null,
                                'class_description' => $classification['class_description'] ?? null,
                                'head_code' => $classification['head_code'] ?? null,
                                'head_name' => $classification['head_name'] ?? null,
                                'subhead_code' => $classification['subhead_code'] ?? null,
                                'subhead_letter' => $classification['subhead_letter'] ?? null,
                                'subhead_roman' => $classification['subhead_roman'] ?? null,
                                'description' => $classification['description'] ?? null,
                            ];
                        }
                    }

                    \App\Models\ContractorUpkjRecord::create([
                        'contractor_category_id' => $contractor->id,
                        'category' => $upkjData['category'],
                        'registration_status' => $upkjData['registration_status'],
                        'validity_period' => $upkjData['validity_period'] ?? null,
                        'bumiputera_status' => $upkjData['bumiputera_status'] ?? null,
                        'bumiputera_validity' => $upkjData['bumiputera_validity'] ?? null,
                        'certificate_no' => $upkjData['certificate_no'] ?? null,
                        'classifications' => $classifications,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('pages.master-data.contractor')->with('success', 'Company created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Failed to create company: ' . $e->getMessage());
        }
    }

    public function masterDataContractorUpdate(Request $request, $id)
    {
        $contractor = \App\Models\ContractorCategory::findOrFail($id);

        $request->validate([
            'company_name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:contractor_categories,code,' . $id,
            'registration_number' => 'required|string|max:255',
            'company_type' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telephone_no' => 'nullable|string|max:255',
            'mobile_no' => 'nullable|string|max:255',
            'upkj_class' => 'nullable|string|max:255',
            'upkj_head' => 'nullable|string|max:255',
            'upkj_subhead' => 'nullable|string',
            'shareholders' => 'nullable|array',
            'shareholders.*.name' => 'required_with:shareholders|string|max:255',
            'shareholders.*.registration_no' => 'nullable|string|max:255',
            'shareholders.*.shares' => 'nullable|numeric|min:0|max:100',
            'directors' => 'nullable|array',
            'directors.*.name' => 'required_with:directors|string|max:255',
            'directors.*.ic_number' => 'nullable|string|max:255',
            'directors.*.shares' => 'nullable|numeric|min:0|max:100',
            'manpower_sole_proprietor' => 'nullable|integer|min:0',
            'manpower_management' => 'nullable|integer|min:0',
            'manpower_professional' => 'nullable|integer|min:0',
            'manpower_sub_professional' => 'nullable|integer|min:0',
            'manpower_competent_worker' => 'nullable|integer|min:0',
            'status' => 'required|in:Active,Inactive,Pending,Rejected,Suspended',
            
            // UPKJ records validation
            'upkj' => 'nullable|array',
            'upkj.*.category' => 'required_with:upkj|string|in:Works,Supplies & Services,Electrical,Mechanical',
            'upkj.*.registration_status' => 'required_with:upkj|string|in:Valid,Expired,Pending',
            'upkj.*.validity_period' => 'nullable|string|max:100',
            'upkj.*.bumiputera_status' => 'nullable|string|in:Yes,No',
            'upkj.*.bumiputera_validity' => 'nullable|string|max:100',
            'upkj.*.certificate_no' => 'nullable|string|max:100',
            'upkj.*.classifications' => 'required_with:upkj|array|min:1',
        ], [
            'upkj.*.category.required_with' => 'Category is required for each UPKJ record',
            'upkj.*.registration_status.required_with' => 'Registration status is required for each UPKJ record',
            'upkj.*.classifications.required_with' => 'Each UPKJ record must have at least one classification',
            'upkj.*.classifications.min' => 'Each UPKJ record must have at least one classification',
        ]);

        \DB::beginTransaction();
        try {
            $data = $request->except(['shareholders', 'directors', 'upkj']);
            
            // Handle shareholders data (combine both tables into one JSON field)
            $shareholdersData = [];
            if ($request->has('shareholders')) {
                foreach ($request->shareholders as $shareholder) {
                    $shareholdersData[] = [
                        'type' => 'company',
                        'name' => $shareholder['name'],
                        'registration_no' => $shareholder['registration_no'] ?? null,
                        'shares' => $shareholder['shares'] ?? 0,
                    ];
                }
            }
            if ($request->has('directors')) {
                foreach ($request->directors as $director) {
                    $shareholdersData[] = [
                        'type' => 'individual',
                        'name' => $director['name'],
                        'ic_number' => $director['ic_number'] ?? null,
                        'shares' => $director['shares'] ?? 0,
                    ];
                }
            }
            $data['shareholders_data'] = !empty($shareholdersData) ? $shareholdersData : null;
            
            // Calculate total manpower
            $data['manpower_total'] = ($request->manpower_sole_proprietor ?? 0) +
                                       ($request->manpower_management ?? 0) +
                                       ($request->manpower_professional ?? 0) +
                                       ($request->manpower_sub_professional ?? 0) +
                                       ($request->manpower_competent_worker ?? 0);

            $contractor->update($data);

            // Delete existing UPKJ records
            $contractor->upkjRecords()->delete();

            // Create new UPKJ records
            if ($request->filled('upkj')) {
                foreach ($request->upkj as $upkjData) {
                    $contractor->upkjRecords()->create([
                        'category' => $upkjData['category'],
                        'registration_status' => $upkjData['registration_status'],
                        'validity_period' => $upkjData['validity_period'] ?? null,
                        'bumiputera_status' => $upkjData['bumiputera_status'] ?? null,
                        'bumiputera_validity' => $upkjData['bumiputera_validity'] ?? null,
                        'certificate_no' => $upkjData['certificate_no'] ?? null,
                        'classifications' => $upkjData['classifications'] ?? [],
                    ]);
                }
            }

            \DB::commit();

            return redirect()->route('pages.master-data.contractor')->with('success', 'Company updated successfully');

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update company: ' . $e->getMessage());
        }
    }

    public function masterDataContractorEdit($id): View
    {
        $contractor = \App\Models\ContractorCategory::with('upkjRecords')->findOrFail($id);
        $upkjClasses = \App\Models\ContractorCategory::getUpkjClasses();
        
        // Get divisions and districts from database with relationships
        $divisions = \App\Models\Division::where('status', 'Active')->orderBy('name')->get();
        $districts = \App\Models\District::with('division')->where('status', 'Active')->orderBy('name')->get();
        
        // Separate shareholders data into companies and individuals
        $shareholders = [];
        $directors = [];
        foreach ($contractor->getShareholders() as $shareholder) {
            if (($shareholder['type'] ?? 'individual') === 'company') {
                $shareholders[] = $shareholder;
            } else {
                $directors[] = $shareholder;
            }
        }
        
        // AUTO-MIGRATE: If no UPKJ records exist but legacy data exists, parse and create UPKJ records
        if ($contractor->upkjRecords->isEmpty() && !empty($contractor->upkj_subhead)) {
            $this->migrateLegacyUpkjData($contractor);
            // Reload contractor with new UPKJ records
            $contractor = \App\Models\ContractorCategory::with('upkjRecords')->findOrFail($id);
        }
        
        return view('pages.master-data.contractor-edit', compact('contractor', 'upkjClasses', 'divisions', 'districts', 'shareholders', 'directors'));
    }
    
    /**
     * Migrate legacy UPKJ data to new UPKJ records structure
     * Parses complex legacy format and creates proper UPKJ records
     * 
     * IMPORTANT: This method preserves ALL subhead codes from legacy data,
     * even if they don't exist in upkj_classifications table
     * 
     * Format examples:
     * "E         III     1(a),1(b),1(c)"  - TAB or multiple spaces
     * "EX I 1,2(a)(i),2(a)(ii)"           - Single space between CLASS and HEAD
     * " F II 1(a),1(b)"                   - Leading space
     */
    private function migrateLegacyUpkjData($contractor)
    {
        if (empty($contractor->upkj_subhead)) {
            return;
        }
        
        try {
            // Parse legacy subhead format
            $lines = explode("\n", trim($contractor->upkj_subhead));
            $classifications = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Strategy: Find the last space before the comma-separated list
                // This handles both "E III 1(a),1(b)" and "EX I 1,2(a)(i)"
                
                // First, find where the subheads start (look for comma or end of string)
                // The pattern is: CLASS HEAD SUBHEADS
                // We need to split into 3 parts intelligently
                
                // Method: Split by whitespace, then figure out which parts are CLASS, HEAD, SUBHEADS
                $parts = preg_split('/\s+/', $line, 3);
                
                if (count($parts) >= 3) {
                    $class = trim($parts[0]);
                    $head = trim($parts[1]);
                    $subheads = trim($parts[2]);
                } elseif (count($parts) == 2) {
                    // Edge case: "EX I" with no subheads? Skip
                    continue;
                } else {
                    continue;
                }
                
                // Split subheads by comma
                $subheadList = array_map('trim', explode(',', $subheads));
                
                foreach ($subheadList as $subhead) {
                    if (empty($subhead)) continue;
                    
                    // Try to get classification details from database
                    $classificationData = \App\Models\UpkjClassification::where('class', $class)
                        ->where('head_code', $head)
                        ->where('subhead_code', $subhead)
                        ->first();
                    
                    if ($classificationData) {
                        // Found in database - use full details
                        $classifications[] = [
                            'id' => $classificationData->id,
                            'class' => $classificationData->class,
                            'head_code' => $classificationData->head_code,
                            'subhead_code' => $classificationData->subhead_code,
                            'description' => $classificationData->head_name ?? '',
                            'subhead_roman' => $classificationData->subhead_roman ?? '',
                            'subhead_letter' => $classificationData->subhead_letter ?? '',
                        ];
                    } else {
                        // Not found in database - preserve legacy data anyway
                        // This ensures NO data loss during migration
                        $classifications[] = [
                            'id' => null,
                            'class' => $class,
                            'head_code' => $head,
                            'subhead_code' => $subhead,
                            'description' => '',
                            'subhead_roman' => '',
                            'subhead_letter' => '',
                        ];
                    }
                }
            }
            
            // Create UPKJ record with all classifications
            if (!empty($classifications)) {
                $contractor->upkjRecords()->create([
                    'category' => 'Works', // Default category
                    'registration_status' => 'Valid',
                    'validity_period' => '',
                    'bumiputera_status' => null,
                    'bumiputera_validity' => null,
                    'certificate_no' => $contractor->upk_license_no ?? '',
                    'classifications' => $classifications,
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the page load
            \Log::error('Failed to migrate legacy UPKJ data for contractor ' . $contractor->id . ': ' . $e->getMessage());
        }
    }

    public function masterDataContractorDelete($id)
    {
        $contractor = \App\Models\ContractorCategory::findOrFail($id);
        
        // Check if contractor has users
        if ($contractor->users()->count() > 0) {
            return redirect()->route('pages.master-data.contractor')
                ->with('error', 'Cannot delete company with associated users');
        }
        
        $contractor->delete();

        return redirect()->route('pages.master-data.contractor')->with('success', 'Company deleted successfully');
    }

    /**
     * Export contractor companies to CSV
     */
    public function masterDataContractorExport()
    {
        $contractors = \App\Models\ContractorCategory::with('upkjRecords')->get();
        
        $filename = 'contractor_companies_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($contractors) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV Headers
            fputcsv($file, [
                'Code',
                'Company Name',
                'Registration Number',
                'Office Registration No',
                'Email',
                'Telephone No',
                'Mobile No',
                'Fax No',
                'Contact Person',
                'Contact No',
                'Date Established',
                'Company Type',
                'Company Category',
                'Registration Category',
                'Division',
                'District',
                'Bumiputera Status',
                'Rescue Contractor',
                'Authorized Person Name',
                'Authorized Person IC',
                'UPK License No',
                'UPK Expiry Date',
                'Registered Address',
                'Postal Address',
                'Business Address',
                'Registered Location',
                'City',
                'State',
                'Postcode',
                'Manpower Sole Proprietor',
                'Manpower Management',
                'Manpower Professional',
                'Manpower Sub Professional',
                'Manpower Competent Worker',
                'Manpower Total',
                'Company Shareholders',
                'Individual Shareholders',
                'Description',
                'Status'
            ]);
            
            // Data rows
            foreach ($contractors as $contractor) {
                // Parse shareholders data
                $shareholders = $contractor->getShareholders();
                $companyShareholders = [];
                $individualShareholders = [];
                
                foreach ($shareholders as $shareholder) {
                    if (($shareholder['type'] ?? 'individual') === 'company') {
                        // Company shareholder: Name (RegNo, Shares%)
                        $companyShareholders[] = sprintf(
                            '%s (%s, %s%%)',
                            $shareholder['name'] ?? '',
                            $shareholder['registration_no'] ?? '',
                            $shareholder['shares'] ?? '0'
                        );
                    } else {
                        // Individual shareholder: Name (IC, Shares%)
                        $individualShareholders[] = sprintf(
                            '%s (%s, %s%%)',
                            $shareholder['name'] ?? '',
                            $shareholder['ic_number'] ?? '',
                            $shareholder['shares'] ?? '0'
                        );
                    }
                }
                
                // Join with pipe separator
                $companyShareholdersStr = implode('|', $companyShareholders);
                $individualShareholdersStr = implode('|', $individualShareholders);
                
                fputcsv($file, [
                    $contractor->code,
                    $contractor->company_name,
                    $contractor->registration_number,
                    $contractor->office_registration_no,
                    $contractor->email,
                    $contractor->telephone_no,
                    $contractor->mobile_no,
                    $contractor->fax_no,
                    $contractor->contact_person,
                    $contractor->contact_no,
                    $contractor->date_established ? $contractor->date_established->format('Y-m-d') : '',
                    $contractor->company_type,
                    $contractor->company_category,
                    $contractor->registration_category,
                    $contractor->division,
                    $contractor->district,
                    $contractor->bumiputera_status,
                    $contractor->rescue_contractor ? 'Yes' : 'No',
                    $contractor->authorized_person_name,
                    $contractor->authorized_person_ic,
                    $contractor->upk_license_no,
                    $contractor->upk_expiry_date ? $contractor->upk_expiry_date->format('Y-m-d') : '',
                    $contractor->registered_address,
                    $contractor->postal_address,
                    $contractor->business_address,
                    $contractor->registered_location,
                    $contractor->registered_address_city,
                    $contractor->registered_address_state,
                    $contractor->registered_address_postcode,
                    $contractor->manpower_sole_proprietor,
                    $contractor->manpower_management,
                    $contractor->manpower_professional,
                    $contractor->manpower_sub_professional,
                    $contractor->manpower_competent_worker,
                    $contractor->manpower_total,
                    $companyShareholdersStr,
                    $individualShareholdersStr,
                    $contractor->description,
                    $contractor->status
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import contractor companies from CSV
     */
    public function masterDataContractorImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB
        ]);
        
        try {
            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');
            
            // Skip BOM if present
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }
            
            // Read header row
            $header = fgetcsv($handle);
            
            $imported = 0;
            $updated = 0;
            $errors = [];
            
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map CSV columns to array
                $data = array_combine($header, $row);
                
                // Validate required fields
                if (empty($data['Code']) || empty($data['Company Name'])) {
                    $errors[] = "Row skipped: Code and Company Name are required";
                    continue;
                }
                
                // Check if contractor exists by code
                $contractor = \App\Models\ContractorCategory::where('code', $data['Code'])->first();
                
                // Parse shareholders data from pipe-separated format
                $shareholdersData = [];
                
                // Parse Company Shareholders: Name (RegNo, Shares%)|Name (RegNo, Shares%)
                if (!empty($data['Company Shareholders'])) {
                    $companyShareholdersList = explode('|', $data['Company Shareholders']);
                    foreach ($companyShareholdersList as $shareholderStr) {
                        $shareholderStr = trim($shareholderStr);
                        if (empty($shareholderStr)) continue;
                        
                        // Parse format: Name (RegNo, Shares%)
                        if (preg_match('/^(.+?)\s*\(([^,]+),\s*(\d+(?:\.\d+)?)%\)$/', $shareholderStr, $matches)) {
                            $shareholdersData[] = [
                                'type' => 'company',
                                'name' => trim($matches[1]),
                                'registration_no' => trim($matches[2]),
                                'shares' => trim($matches[3]),
                            ];
                        }
                    }
                }
                
                // Parse Individual Shareholders: Name (IC, Shares%)|Name (IC, Shares%)
                if (!empty($data['Individual Shareholders'])) {
                    $individualShareholdersList = explode('|', $data['Individual Shareholders']);
                    foreach ($individualShareholdersList as $shareholderStr) {
                        $shareholderStr = trim($shareholderStr);
                        if (empty($shareholderStr)) continue;
                        
                        // Parse format: Name (IC, Shares%)
                        if (preg_match('/^(.+?)\s*\(([^,]+),\s*(\d+(?:\.\d+)?)%\)$/', $shareholderStr, $matches)) {
                            $shareholdersData[] = [
                                'type' => 'individual',
                                'name' => trim($matches[1]),
                                'ic_number' => trim($matches[2]),
                                'shares' => trim($matches[3]),
                            ];
                        }
                    }
                }
                
                // Prepare data for insert/update
                $contractorData = [
                    'company_name' => $data['Company Name'],
                    'registration_number' => $data['Registration Number'] ?? null,
                    'office_registration_no' => $data['Office Registration No'] ?? null,
                    'email' => $data['Email'] ?? null,
                    'telephone_no' => $data['Telephone No'] ?? null,
                    'mobile_no' => $data['Mobile No'] ?? null,
                    'fax_no' => $data['Fax No'] ?? null,
                    'contact_person' => $data['Contact Person'] ?? null,
                    'contact_no' => $data['Contact No'] ?? null,
                    'date_established' => !empty($data['Date Established']) ? $data['Date Established'] : null,
                    'company_type' => $data['Company Type'] ?? 'contractor',
                    'company_category' => $data['Company Category'] ?? null,
                    'registration_category' => $data['Registration Category'] ?? null,
                    'division' => $data['Division'] ?? null,
                    'district' => $data['District'] ?? null,
                    'bumiputera_status' => $data['Bumiputera Status'] ?? null,
                    'rescue_contractor' => ($data['Rescue Contractor'] ?? 'No') === 'Yes' ? 1 : 0,
                    'authorized_person_name' => $data['Authorized Person Name'] ?? null,
                    'authorized_person_ic' => $data['Authorized Person IC'] ?? null,
                    'upk_license_no' => $data['UPK License No'] ?? null,
                    'upk_expiry_date' => !empty($data['UPK Expiry Date']) ? $data['UPK Expiry Date'] : null,
                    'registered_address' => $data['Registered Address'] ?? null,
                    'postal_address' => $data['Postal Address'] ?? null,
                    'business_address' => $data['Business Address'] ?? null,
                    'registered_location' => $data['Registered Location'] ?? null,
                    'registered_address_city' => $data['City'] ?? null,
                    'registered_address_state' => $data['State'] ?? null,
                    'registered_address_postcode' => $data['Postcode'] ?? null,
                    'manpower_sole_proprietor' => $data['Manpower Sole Proprietor'] ?? 0,
                    'manpower_management' => $data['Manpower Management'] ?? 0,
                    'manpower_professional' => $data['Manpower Professional'] ?? 0,
                    'manpower_sub_professional' => $data['Manpower Sub Professional'] ?? 0,
                    'manpower_competent_worker' => $data['Manpower Competent Worker'] ?? 0,
                    'manpower_total' => $data['Manpower Total'] ?? 0,
                    'shareholders_data' => $shareholdersData, // Add shareholders data
                    'description' => $data['Description'] ?? null,
                    'status' => $data['Status'] ?? 'Active',
                ];
                
                if ($contractor) {
                    // Update existing contractor
                    $contractor->update($contractorData);
                    $updated++;
                } else {
                    // Create new contractor
                    $contractorData['code'] = $data['Code'];
                    \App\Models\ContractorCategory::create($contractorData);
                    $imported++;
                }
            }
            
            fclose($handle);
            
            $message = "Import completed: {$imported} new companies created, {$updated} companies updated";
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " rows skipped due to errors.";
            }
            
            return redirect()->route('pages.master-data.contractor')->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->route('pages.master-data.contractor')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download sample CSV file
     */
    public function masterDataContractorSample()
    {
        $filename = 'contractor_sample.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV Headers
            fputcsv($file, [
                'Code',
                'Company Name',
                'Registration Number',
                'Office Registration No',
                'Email',
                'Telephone No',
                'Mobile No',
                'Fax No',
                'Contact Person',
                'Contact No',
                'Date Established',
                'Company Type',
                'Company Category',
                'Registration Category',
                'Division',
                'District',
                'Bumiputera Status',
                'Rescue Contractor',
                'Authorized Person Name',
                'Authorized Person IC',
                'UPK License No',
                'UPK Expiry Date',
                'Registered Address',
                'Postal Address',
                'Business Address',
                'Registered Location',
                'City',
                'State',
                'Postcode',
                'Manpower Sole Proprietor',
                'Manpower Management',
                'Manpower Professional',
                'Manpower Sub Professional',
                'Manpower Competent Worker',
                'Manpower Total',
                'Company Shareholders',
                'Individual Shareholders',
                'Description',
                'Status'
            ]);
            
            // Sample data rows
            fputcsv($file, [
                'CONT001',
                'ABC Construction Sdn Bhd',
                'ROC123456',
                'OFFICE789',
                'abc@construction.com',
                '082-123456',
                '019-1234567',
                '082-654321',
                'Ahmad bin Ali',
                '019-7654321',
                '2020-01-15',
                'contractor',
                'G7',
                'Class A',
                'Sibu',
                'Sibu',
                'Yes',
                'No',
                'Ahmad bin Ali',
                '800101-13-5678',
                'UPK12345',
                '2025-12-31',
                'No. 123, Jalan Maju, 96000 Sibu, Sarawak',
                'P.O. Box 456, 96000 Sibu, Sarawak',
                'No. 123, Jalan Maju, 96000 Sibu, Sarawak',
                'Sibu',
                'Sibu',
                'Sarawak',
                '96000',
                '0',
                '5',
                '10',
                '15',
                '20',
                '50',
                'XYZ Holdings Sdn Bhd (ROC987654, 60%)|ABC Ventures (ROC456789, 40%)',
                'Ahmad bin Ali (800101-13-5678, 50%)|Siti binti Hassan (850202-13-1234, 50%)',
                'General construction company',
                'Active'
            ]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export UPKJ records to CSV
     */
    public function masterDataContractorExportUpkj()
    {
        $upkjRecords = \App\Models\ContractorUpkjRecord::with('contractor')->get();
        
        $filename = 'contractor_upkj_records_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() use ($upkjRecords) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV Headers
            fputcsv($file, [
                'Company Code',
                'Company Name',
                'Category',
                'Registration Status',
                'Validity Period',
                'Bumiputera Status',
                'Bumiputera Validity',
                'Certificate No',
                'Classifications'
            ]);
            
            // Data rows
            foreach ($upkjRecords as $record) {
                // Get classifications (already array from model cast)
                $classifications = $record->classifications;
                $classificationStr = '';
                
                if (is_array($classifications) && !empty($classifications)) {
                    $classItems = [];
                    foreach ($classifications as $class) {
                        // Format: Class-HeadCode-SubheadCode (e.g., F-I-1, F-I-2bi)
                        $classCode = $class['class'] ?? '';
                        $headCode = $class['head_code'] ?? '';
                        $subheadCode = $class['subhead_code'] ?? '';
                        
                        // Add roman numeral if exists
                        if (!empty($class['subhead_roman'])) {
                            $subheadCode .= $class['subhead_roman'];
                        }
                        
                        // Add letter if exists
                        if (!empty($class['subhead_letter'])) {
                            $subheadCode .= $class['subhead_letter'];
                        }
                        
                        // Build classification code
                        if (!empty($classCode) && !empty($headCode) && !empty($subheadCode)) {
                            $classItems[] = $classCode . '-' . $headCode . '-' . $subheadCode;
                        }
                    }
                    $classificationStr = implode(', ', $classItems);
                }
                
                fputcsv($file, [
                    '="' . ($record->contractor->code ?? '') . '"', // Force as text to prevent Excel date conversion
                    $record->contractor->company_name ?? '',
                    $record->category,
                    $record->registration_status,
                    $record->validity_period,
                    $record->bumiputera_status,
                    $record->bumiputera_validity,
                    $record->certificate_no,
                    $classificationStr
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Import UPKJ records from CSV
     */
    public function masterDataContractorImportUpkj(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);
        
        try {
            $file = $request->file('csv_file');
            $handle = fopen($file->getRealPath(), 'r');
            
            // Skip BOM if present
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }
            
            // Read header row
            $header = fgetcsv($handle);
            
            $imported = 0;
            $updated = 0;
            $errors = [];
            
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Map CSV columns to array
                $data = array_combine($header, $row);
                
                // Validate required fields
                if (empty($data['Company Code']) || empty($data['Category'])) {
                    $errors[] = "Row skipped: Company Code and Category are required";
                    continue;
                }
                
                // Clean company code - remove Excel text formula if present
                // Excel text formula format: ="CODE"
                $companyCode = $data['Company Code'];
                if (preg_match('/^="(.+)"$/', $companyCode, $matches)) {
                    $companyCode = $matches[1];
                }
                
                // Handle Excel date conversion issue
                // Excel converts codes in multiple formats:
                // 1. "1/96" → "Jan-96" (month/year)
                // 2. "2/75" → "Feb-75" (month/year)
                // 3. "11/88" → "Nov-88" (month/year)
                // 4. "3-2" → "3-Feb" (day-month)
                
                $isExcelConverted = false;
                $originalCodeHint = '';
                
                // Check format: Jan-96, Feb-75, Nov-88 (Month-Year)
                if (preg_match('/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)-(\d+)$/i', $companyCode, $matches)) {
                    $isExcelConverted = true;
                    $monthMap = [
                        'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6,
                        'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
                    ];
                    $month = $monthMap[ucfirst(strtolower($matches[1]))];
                    $year = $matches[2];
                    $originalCodeHint = "Possible original: {$month}/{$year}";
                }
                
                // Check format: 3-Feb, 1-Jan (Day-Month)
                if (preg_match('/^(\d+)-(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)$/i', $companyCode, $matches)) {
                    $isExcelConverted = true;
                    $monthMap = [
                        'Jan' => 1, 'Feb' => 2, 'Mar' => 3, 'Apr' => 4, 'May' => 5, 'Jun' => 6,
                        'Jul' => 7, 'Aug' => 8, 'Sep' => 9, 'Oct' => 10, 'Nov' => 11, 'Dec' => 12
                    ];
                    $day = $matches[1];
                    $month = $monthMap[ucfirst(strtolower($matches[2]))];
                    $originalCodeHint = "Possible original: {$day}-{$month} or {$day}/{$month}";
                }
                
                if ($isExcelConverted) {
                    $errors[] = "Row skipped: Company Code '{$companyCode}' is Excel date-converted. {$originalCodeHint}. Please re-export CSV using the fixed export function.";
                    continue;
                }
                
                // Find contractor by code
                $contractor = \App\Models\ContractorCategory::where('code', $companyCode)->first();
                
                if (!$contractor) {
                    // Check if this might be an Excel date conversion issue
                    if (preg_match('/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)/i', $companyCode)) {
                        $errors[] = "Company with code '{$companyCode}' not found. This looks like Excel date conversion. Please format Company Code column as TEXT in Excel.";
                    } else {
                        $errors[] = "Company with code '{$companyCode}' not found in database.";
                    }
                    continue;
                }
                
                // Parse classifications from format: F-I-1, F-I-2bi, F-II-5a
                $classifications = [];
                if (!empty($data['Classifications']) && trim($data['Classifications']) !== '') {
                    $classItems = array_map('trim', explode(',', $data['Classifications']));
                    
                    foreach ($classItems as $item) {
                        if (empty($item)) {
                            continue; // Skip empty items
                        }
                        
                        // Parse format: Class-HeadCode-SubheadCode (e.g., F-I-1, F-I-2bi)
                        $parts = explode('-', $item);
                        
                        if (count($parts) >= 3) {
                            $classCode = $parts[0];
                            $headCode = $parts[1];
                            $subheadFull = $parts[2];
                            
                            // Extract subhead number, roman, and letter
                            // CORRECT format: number + roman + letter
                            // Examples: "1" -> 1, "2ia" -> 2 + i + a, "2iia" -> 2 + ii + a, "5iia" -> 5 + ii + a
                            preg_match('/^(\d+)([ivx]*)([a-z]*)$/i', $subheadFull, $matches);
                            
                            $subheadCode = $matches[1] ?? '';
                            $subheadRoman = $matches[2] ?: null;
                            $subheadLetter = $matches[3] ?: null;
                            
                            // Find matching UPKJ classification in master data
                            $upkjClass = \App\Models\UpkjClassification::where('class', $classCode)
                                ->where('head_code', $headCode)
                                ->where('subhead_code', $subheadCode)
                                ->where(function($q) use ($subheadLetter) {
                                    if ($subheadLetter) {
                                        $q->where('subhead_letter', $subheadLetter);
                                    } else {
                                        $q->whereNull('subhead_letter');
                                    }
                                })
                                ->where(function($q) use ($subheadRoman) {
                                    if ($subheadRoman) {
                                        $q->where('subhead_roman', $subheadRoman);
                                    } else {
                                        $q->whereNull('subhead_roman');
                                    }
                                })
                                ->first();
                            
                            if ($upkjClass) {
                                $classifications[] = [
                                    'class' => $upkjClass->class,
                                    'head_code' => $upkjClass->head_code,
                                    'head_name' => $upkjClass->head_name,
                                    'description' => $upkjClass->description,
                                    'subhead_code' => $upkjClass->subhead_code,
                                    'subhead_roman' => $upkjClass->subhead_roman,
                                    'subhead_letter' => $upkjClass->subhead_letter,
                                    'class_description' => $upkjClass->class_description
                                ];
                            } else {
                                // If not found in master data, store as-is
                                $classifications[] = [
                                    'class' => $classCode,
                                    'head_code' => $headCode,
                                    'head_name' => '',
                                    'description' => '',
                                    'subhead_code' => $subheadCode,
                                    'subhead_roman' => $subheadRoman,
                                    'subhead_letter' => $subheadLetter,
                                    'class_description' => ''
                                ];
                            }
                        }
                    }
                }
                
                // CRITICAL: Only update if we have valid classifications
                // If Classifications column is empty, skip this record to preserve existing data
                if (empty($classifications)) {
                    $errors[] = "Row skipped for '{$data['Company Code']}' - '{$data['Category']}': No valid classifications found. Classifications column: '" . ($data['Classifications'] ?? 'EMPTY') . "'";
                    continue;
                }
                
                // CRITICAL: Remove duplicates from classifications array
                // This prevents duplicate classifications from being saved
                $uniqueClassifications = [];
                $seen = [];
                
                foreach ($classifications as $classification) {
                    $key = sprintf(
                        '%s-%s-%s-%s-%s',
                        $classification['class'] ?? '',
                        $classification['head_code'] ?? '',
                        $classification['subhead_code'] ?? '',
                        $classification['subhead_letter'] ?? '',
                        $classification['subhead_roman'] ?? ''
                    );
                    
                    if (!isset($seen[$key])) {
                        $seen[$key] = true;
                        $uniqueClassifications[] = $classification;
                    }
                }
                
                // Prepare UPKJ data
                $upkjData = [
                    'contractor_category_id' => $contractor->id,
                    'category' => $data['Category'],
                    'registration_status' => $data['Registration Status'] ?? 'Valid',
                    'validity_period' => $data['Validity Period'] ?? null,
                    'bumiputera_status' => $data['Bumiputera Status'] ?? null,
                    'bumiputera_validity' => $data['Bumiputera Validity'] ?? null,
                    'certificate_no' => $data['Certificate No'] ?? null,
                    'classifications' => $uniqueClassifications // Store as array, not json_encode
                ];
                
                // Check if UPKJ record exists
                $upkjRecord = \App\Models\ContractorUpkjRecord::where('contractor_category_id', $contractor->id)
                    ->where('category', $data['Category'])
                    ->first();
                
                if ($upkjRecord) {
                    // Update existing
                    $upkjRecord->update($upkjData);
                    $updated++;
                } else {
                    // Create new
                    \App\Models\ContractorUpkjRecord::create($upkjData);
                    $imported++;
                }
            }
            
            fclose($handle);
            
            $message = "Import completed: {$imported} new UPKJ records created, {$updated} records updated";
            if (!empty($errors)) {
                $message .= ". " . count($errors) . " rows skipped due to errors.";
                
                // Store errors in session for display
                session()->flash('import_errors', $errors);
            }
            
            return redirect()->route('pages.master-data.contractor')->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->route('pages.master-data.contractor')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download sample UPKJ CSV file
     */
    public function masterDataContractorSampleUpkj()
    {
        $filename = 'contractor_upkj_sample.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // CSV Headers
            fputcsv($file, [
                'Company Code',
                'Company Name',
                'Category',
                'Registration Status',
                'Validity Period',
                'Bumiputera Status',
                'Bumiputera Validity',
                'Certificate No',
                'Classifications'
            ]);
            
            // Sample data rows
            fputcsv($file, [
                'CONT001',
                'ABC Construction Sdn Bhd',
                'Works',
                'Valid',
                '01/01/2024 - 31/12/2025',
                'Yes',
                '01/01/2024 - 31/12/2025',
                'CERT-WORKS-001',
                'F-I-1, F-I-2ai, F-I-2bi, F-II-5a, F-IV-1a'
            ]);
            
            fputcsv($file, [
                'CONT001',
                'ABC Construction Sdn Bhd',
                'Electrical',
                'Valid',
                '01/01/2024 - 31/12/2025',
                'No',
                '',
                'CERT-ELEC-001',
                'I-VIIA-1, II-VIIA-2'
            ]);
            
            fputcsv($file, [
                'CONT002',
                'XYZ Engineering Sdn Bhd',
                'Mechanical',
                'Expired',
                '01/01/2023 - 31/12/2023',
                'No',
                '',
                'CERT-MECH-001',
                'A-VIIB-1, B-VIIB-2'
            ]);
            
            fputcsv($file, [
                'CONT002',
                'XYZ Engineering Sdn Bhd',
                'Supplies & Services',
                'Valid',
                '01/06/2024 - 31/05/2026',
                'Yes',
                '01/06/2024 - 31/05/2026',
                'CERT-SUPPLY-001',
                'S1-1, S2-1'
            ]);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function masterDataStatus(): View
    {
        $statuses = \App\Models\StatusMaster::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.status', compact('statuses'));
    }

    public function masterDataStatusStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:status_master,code',
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\StatusMaster::create([
            'name' => $request->name,
            'code' => $request->code,
            'color' => $request->color,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('pages.master-data.status')->with('success', 'Status created successfully');
    }

    public function masterDataStatusUpdate(Request $request, $id)
    {
        $status = \App\Models\StatusMaster::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:status_master,code,' . $id,
            'color' => 'required|string|max:7',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $status->update([
            'name' => $request->name,
            'code' => $request->code,
            'color' => $request->color,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return redirect()->route('pages.master-data.status')->with('success', 'Status updated successfully');
    }

    public function masterDataStatusDelete($id)
    {
        $status = \App\Models\StatusMaster::findOrFail($id);
        $status->delete();

        return redirect()->route('pages.master-data.status')->with('success', 'Status deleted successfully');
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
            $user = auth()->user();
            $parliament = \App\Models\Parliament::findOrFail($id);

            // Update the Parliament record
            $parliament->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            // CRITICAL: Only Residen users can update budgets
            if ($user->residen_category_id) {
                // Delete existing budget entries
                \App\Models\ParliamentBudget::where('parliament_id', $parliament->id)->delete();

                // Create new budget entries from the budgets array
                if ($request->has('budgets') && is_array($request->budgets)) {
                    foreach ($request->budgets as $budgetEntry) {
                        \App\Models\ParliamentBudget::create([
                            'parliament_id' => $parliament->id,
                            'year' => $budgetEntry['year'],
                            'budget' => $budgetEntry['budget'],
                        ]);
                    }
                }
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
        $user = auth()->user();
        $dun = \App\Models\Dun::findOrFail($id);

        // Update the DUN record
        $dun->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        // CRITICAL: Only Residen users can update budgets
        if ($user->residen_category_id) {
            // Delete existing budget entries
            \App\Models\DunBudget::where('dun_id', $dun->id)->delete();

            // Create new budget entries from the budgets array
            if ($request->has('budgets') && is_array($request->budgets)) {
                foreach ($request->budgets as $budgetEntry) {
                    \App\Models\DunBudget::create([
                        'dun_id' => $dun->id,
                        'year' => $budgetEntry['year'],
                        'budget' => $budgetEntry['budget'],
                    ]);
                }
            }
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
            return response()->json([
                'success' => false,
                'message' => 'Failed to test weather API: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getWeather()
    {
        try {
            // Get weather settings from integration_settings table
            $apiKey = \App\Models\IntegrationSetting::getSetting('weather', 'api_key');
            $baseUrl = \App\Models\IntegrationSetting::getSetting('weather', 'base_url', 'https://api.openweathermap.org/data/2.5');
            $location = \App\Models\IntegrationSetting::getSetting('weather', 'location', 'Kuching, MY');
            $latitude = \App\Models\IntegrationSetting::getSetting('weather', 'latitude');
            $longitude = \App\Models\IntegrationSetting::getSetting('weather', 'longitude');
            $units = \App\Models\IntegrationSetting::getSetting('weather', 'units', 'metric');
            
            // Check if API key is configured
            if (empty($apiKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Weather API key not configured'
                ], 400);
            }
            
            // Build current weather API URL
            $currentUrl = $baseUrl . '/weather?';
            
            // Use latitude/longitude if available, otherwise use location name
            if (!empty($latitude) && !empty($longitude)) {
                $currentUrl .= 'lat=' . $latitude . '&lon=' . $longitude;
                $forecastUrl = $baseUrl . '/forecast?lat=' . $latitude . '&lon=' . $longitude;
            } else {
                $currentUrl .= 'q=' . urlencode($location);
                $forecastUrl = $baseUrl . '/forecast?q=' . urlencode($location);
            }
            
            $currentUrl .= '&appid=' . $apiKey . '&units=' . $units;
            $forecastUrl .= '&appid=' . $apiKey . '&units=' . $units;
            
            // Make API requests with timeout
            $currentResponse = \Illuminate\Support\Facades\Http::timeout(10)->get($currentUrl);
            $forecastResponse = \Illuminate\Support\Facades\Http::timeout(10)->get($forecastUrl);
            
            if ($currentResponse->successful()) {
                $currentData = $currentResponse->json();
                $forecastData = [];
                
                // Process forecast data if available
                if ($forecastResponse->successful()) {
                    $forecast = $forecastResponse->json();
                    
                    // Get forecast for next 2 days (16 entries = 48 hours / 3-hour intervals)
                    if (isset($forecast['list'])) {
                        $forecastList = array_slice($forecast['list'], 0, 16);
                        
                        foreach ($forecastList as $item) {
                            $forecastData[] = [
                                'time' => $item['dt'],
                                'temp' => round($item['main']['temp'] ?? 0, 1),
                                'weather' => $item['weather'][0]['main'] ?? 'Clear',
                                'description' => ucfirst($item['weather'][0]['description'] ?? 'clear sky'),
                                'icon' => $item['weather'][0]['icon'] ?? '01d',
                                'humidity' => $item['main']['humidity'] ?? 0,
                                'wind_speed' => round(($item['wind']['speed'] ?? 0) * 3.6, 1),
                            ];
                        }
                    }
                }
                
                return response()->json([
                    'success' => true,
                    'temperature' => round($currentData['main']['temp'] ?? 0, 1),
                    'weather' => $currentData['weather'][0]['main'] ?? 'Clear',
                    'description' => ucfirst($currentData['weather'][0]['description'] ?? 'clear sky'),
                    'feels_like' => round($currentData['main']['feels_like'] ?? 0, 1),
                    'humidity' => $currentData['main']['humidity'] ?? 0,
                    'wind_speed' => round(($currentData['wind']['speed'] ?? 0) * 3.6, 1), // Convert m/s to km/h
                    'pressure' => $currentData['main']['pressure'] ?? 0,
                    'visibility' => round(($currentData['visibility'] ?? 0) / 1000, 1), // Convert meters to km
                    'location' => $currentData['name'] ?? $location,
                    'country' => $currentData['sys']['country'] ?? '',
                    'forecast' => $forecastData,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch weather data'
                ], 400);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Weather service unavailable'
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
        $parliamentId = $request->input('parliament_id');
        $dunId = $request->input('dun_id');
        
        $budgetService = new \App\Services\BudgetCalculationService();
        
        // If Residen user and parliament_id or dun_id is provided, calculate budget for that constituency
        if ($user->residen_category_id && ($parliamentId || $dunId)) {
            $budgetInfo = $budgetService->getBudgetForConstituency($parliamentId, $dunId, $year);
        } else {
            // For Parliament/DUN users, use their assigned constituency
            $budgetInfo = $budgetService->getUserBudgetInfo($user, $year);
        }
        
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
            return redirect()->route('pages.project.transfer.create')
                ->with('error', 'Gagal transfer Pre-Project. Sila cuba lagi.');
        }
    }

    public function contractorSelections(): View
    {
        $user = auth()->user();
        
        // Only Residen users can access
        if (!$user->residen_category_id) {
            abort(403, 'Unauthorized access. Only Residen users can access Contractor Selections.');
        }
        
        // Get all contractor selections with relationships
        $selections = \App\Models\ContractorSelection::with(['division', 'district', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('pages.contractor-selections', compact('selections'));
    }

    public function contractorSelectionsCreate(): View
    {
        $user = auth()->user();
        
        // Only Residen users can access
        if (!$user->residen_category_id) {
            abort(403, 'Unauthorized access. Only Residen users can access Contractor Selections.');
        }
        
        // Get UPKJ data with cascade structure (Category → Class → Head → Subhead)
        // Get distinct categories
        $categories = DB::table('upkj_classifications')
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');
        
        // Get all UPKJ classifications for cascade filtering
        $upkjClassifications = DB::table('upkj_classifications')
            ->select('category', 'class', 'class_description', 'head_code', 'head_name', 
                     'subhead_code', 'subhead_letter', 'subhead_roman', 'description')
            ->orderBy('category')
            ->orderBy('class')
            ->orderBy('head_code')
            ->orderBy('subhead_code')
            ->get();
        
        // Get divisions and districts for additional filters
        $divisions = \App\Models\Division::where('status', 'Active')
            ->orderBy('name')
            ->get();
        
        $districts = \App\Models\District::where('status', 'Active')
            ->orderBy('name')
            ->get();
        
        return view('pages.contractor-selections-create', compact('upkjClassifications', 'categories', 'divisions', 'districts'));
    }

    public function contractorSelectionsStore(Request $request)
    {
        $user = auth()->user();
        
        // Only Residen users can create
        if (!$user->residen_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $validated = $request->validate([
            'upkj_categories' => 'nullable|array',
            'upkj_classes' => 'nullable|array',
            'upkj_heads' => 'nullable|array',
            'upkj_subheads' => 'nullable|array',
            'division_id' => 'nullable|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'contractor_ids' => 'required|array|min:1',
            'contractor_ids.*' => 'exists:contractor_categories,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Generate selection number
            $selectionNumber = \App\Models\ContractorSelection::generateSelectionNumber();
            
            // Create contractor selection record
            $selection = \App\Models\ContractorSelection::create([
                'selection_number' => $selectionNumber,
                'division_id' => $validated['division_id'] ?? null,
                'district_id' => $validated['district_id'] ?? null,
                'upkj_categories' => $validated['upkj_categories'] ?? [],
                'upkj_classes' => $validated['upkj_classes'] ?? [],
                'upkj_heads' => $validated['upkj_heads'] ?? [],
                'upkj_subheads' => $validated['upkj_subheads'] ?? [],
                'created_by' => $user->id,
                'status' => 'Active',
                'generated_at' => now(),
            ]);
            
            // Attach contractors with their current data (fingerprint)
            foreach ($validated['contractor_ids'] as $contractorId) {
                $contractor = \App\Models\ContractorCategory::find($contractorId);
                
                if ($contractor) {
                    // Get selected (YES/NO) and reason from request
                    $selected = $request->input("selected_{$contractorId}"); // YES, NO, or null
                    $reason = $request->input("reason_{$contractorId}");
                    
                    $selection->contractors()->attach($contractorId, [
                        'company_name' => $contractor->company_name,
                        'registration_number' => $contractor->registration_number,
                        'upkj_class' => $contractor->upkj_class,
                        'upkj_head' => $contractor->upkj_head,
                        'upkj_subhead' => $contractor->upkj_subhead,
                        'upk_expiry_date' => $contractor->upk_expiry_date,
                        'status_at_generation' => $contractor->status,
                        'snapshot_data' => json_encode($contractor->toArray()),
                        'selected' => $selected, // YES, NO, or null
                        'reason' => $reason,
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('pages.contractor-selections')
                ->with('success', 'Contractor selection created successfully with ' . count($validated['contractor_ids']) . ' contractor(s).');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create contractor selection', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Failed to create contractor selection. Please try again.');
        }
    }

    public function contractorAnalysis(): View
    {
        $user = auth()->user();
        
        // Only Residen users can access
        if (!$user->residen_category_id) {
            abort(403, 'Unauthorized access. Only Residen users can access Contractor Analysis.');
        }
        
        $transfers = \App\Models\ContractorAnalysisTransfer::with(['agency', 'creator', 'projects', 'contractors'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('pages.contractor-analysis', compact('transfers'));
    }

    public function contractorAnalysisCreate(): View
    {
        $user = auth()->user();
        
        // Only Residen users can create
        if (!$user->residen_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        // Get available projects (Active status, not already in analysis)
        $availableProjects = Project::where('status', 'Active')
            ->whereDoesntHave('contractorAnalysisTransfers')
            ->with(['agencyCategory', 'parliament', 'dunBasic'])
            ->orderBy('project_number')
            ->get();
        
        // Get UPKJ data with cascade structure (Category → Class → Head → Subhead)
        // Get distinct categories
        $categories = DB::table('upkj_classifications')
            ->select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');
        
        // Get all UPKJ classifications for cascade filtering
        $upkjClassifications = DB::table('upkj_classifications')
            ->select('category', 'class', 'class_description', 'head_code', 'head_name', 
                     'subhead_code', 'subhead_letter', 'subhead_roman', 'description')
            ->orderBy('category')
            ->orderBy('class')
            ->orderBy('head_code')
            ->orderBy('subhead_code')
            ->get();
        
        return view('pages.contractor-analysis-create', compact(
            'availableProjects',
            'categories',
            'upkjClassifications'
        ));
    }

    public function contractorAnalysisStore(Request $request)
    {
        \Log::info('=== CONTRACTOR ANALYSIS STORE START ===');
        \Log::info('Request Data:', $request->all());
        
        $user = auth()->user();
        \Log::info('User ID:', ['user_id' => $user->id, 'residen_category_id' => $user->residen_category_id]);
        
        // Only Residen users can create
        if (!$user->residen_category_id) {
            \Log::warning('Unauthorized access attempt by user: ' . $user->id);
            abort(403, 'Unauthorized access.');
        }
        
        // Validation
        \Log::info('Starting validation...');
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'upkj_categories' => 'nullable|array',
            'upkj_classes' => 'nullable|array',
            'upkj_heads' => 'nullable|array',
            'upkj_subheads' => 'nullable|array',
            'contractor_ids' => 'required|array|min:1',
            'contractor_ids.*' => 'exists:contractor_categories,id',
        ], [
            'project_id.required' => 'Please select a project',
            'contractor_ids.required' => 'Please select at least one contractor',
            'contractor_ids.min' => 'Please select at least one contractor',
        ]);
        \Log::info('Validation passed');
        
        // Validate at least one UPKJ filter is provided
        $hasUpkjFilter = !empty($request->upkj_categories) || 
                        !empty($request->upkj_classes) || 
                        !empty($request->upkj_heads) || 
                        !empty($request->upkj_subheads);
        
        if (!$hasUpkjFilter) {
            \Log::warning('No UPKJ filter provided');
            return redirect()->back()
                ->withInput()
                ->withErrors(['upkj_filter' => 'Please select at least one UPKJ classification filter (Category, Class, Head, or Subhead)']);
        }
        
        DB::beginTransaction();
        try {
            \Log::info('Transaction started');
            
            // Get project to determine agency
            $project = Project::findOrFail($validated['project_id']);
            \Log::info('Project found:', ['project_id' => $project->id, 'agency_id' => $project->agency_category_id]);
            
            // Create transfer with arrays
            $transfer = \App\Models\ContractorAnalysisTransfer::create([
                'transfer_number' => \App\Models\ContractorAnalysisTransfer::generateTransferNumber(),
                'agency_category_id' => $project->agency_category_id,
                'created_by' => $user->id,
                'status' => 'Draft',
                'upkj_categories' => $request->upkj_categories ?? null,
                'upkj_classes' => $request->upkj_classes ?? null,
                'upkj_heads' => $request->upkj_heads ?? null,
                'upkj_subheads' => $request->upkj_subheads ?? null,
            ]);
            \Log::info('Transfer created:', ['transfer_id' => $transfer->id, 'transfer_number' => $transfer->transfer_number]);
            
            // Attach project (single project)
            $transfer->projects()->attach($validated['project_id']);
            \Log::info('Project attached to transfer');
            
            // Attach contractors (multiple)
            $transfer->contractors()->attach($validated['contractor_ids']);
            \Log::info('Contractors attached:', ['count' => count($validated['contractor_ids'])]);
            
            // Update project status to "Analysis Pending"
            $project->update(['status' => 'Analysis Pending']);
            \Log::info('Project status updated to Analysis Pending');
            
            DB::commit();
            \Log::info('Transaction committed successfully');
            \Log::info('Redirecting to contractor-analysis list with success message');
            
            return redirect()->route('pages.contractor-analysis')
                ->with('success', 'Contractor Analysis Transfer created successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Transaction failed:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create transfer: ' . $e->getMessage());
        }
    }

    public function contractorAnalysisShow($id): View
    {
        $user = auth()->user();
        
        // Only Residen users can view
        if (!$user->residen_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $transfer = \App\Models\ContractorAnalysisTransfer::with([
            'agency',
            'creator',
            'projects.agencyCategory',
            'projects.parliament',
            'projects.dunBasic',
            'contractors.upkjRecords'
        ])->findOrFail($id);
        
        return view('pages.contractor-analysis-show', compact('transfer'));
    }

    public function contractorAnalysisDelete($id)
    {
        $user = auth()->user();
        
        // Only Residen users can delete
        if (!$user->residen_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $transfer = \App\Models\ContractorAnalysisTransfer::findOrFail($id);
        
        // Only Draft transfers can be deleted
        if ($transfer->status !== 'Draft') {
            return redirect()->back()
                ->with('error', 'Only Draft transfers can be deleted.');
        }
        
        DB::beginTransaction();
        try {
            // Get project before deletion
            $project = $transfer->projects()->first();
            
            // Delete transfer (cascade deletes pivot entries)
            $transfer->delete();
            
            // Rollback project status to Active
            if ($project) {
                $project->update(['status' => 'Active']);
            }
            
            DB::commit();
            
            return redirect()->route('pages.contractor-analysis')
                ->with('success', 'Transfer deleted successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete transfer: ' . $e->getMessage());
        }
    }

    public function contractorAnalysisDownload($id)
    {
        $user = auth()->user();
        $service = new \App\Services\ContractorAnalysisTransferService();
        
        $transfer = \App\Models\ContractorAnalysisTransfer::findOrFail($id);
        
        try {
            return $service->downloadAttachment($transfer, $user);
        } catch (\Exception $e) {
            abort(403, $e->getMessage());
        }
    }

    /**
     * Financial Analysis List Page
     */
    public function financialAnalysis(): View
    {
        $user = auth()->user();
        
        // Only Agency and Residen users can access
        if (!$user->agency_category_id && !$user->residen_category_id) {
            abort(403, 'Unauthorized access. Only Agency and Residen users can access Financial Analysis.');
        }
        
        $query = \App\Models\FinancialAnalysis::with([
            'transfer.projects',
            'transfer.agency',
            'creator',
            'contractors'
        ]);
        
        // Apply category filter
        if ($user->agency_category_id) {
            // Agency users see only their agency's analyses
            $query->whereHas('transfer', function($q) use ($user) {
                $q->where('agency_category_id', $user->agency_category_id);
            });
        }
        // Residen users see all (no filter)
        
        $analyses = $query->orderBy('created_at', 'desc')->get();
        
        return view('pages.financial-analysis', compact('analyses'));
    }

    /**
     * Select Transfer Page for Financial Analysis
     */
    public function financialAnalysisSelectTransfer(): View
    {
        $user = auth()->user();
        
        // Only Agency users can access
        if (!$user->agency_category_id) {
            abort(403, 'Unauthorized access. Only Agency users can create Financial Analysis.');
        }
        
        // Get transfers for this agency that don't have financial analysis yet
        $transfers = \App\Models\ContractorAnalysisTransfer::with(['projects', 'contractors'])
            ->where('agency_category_id', $user->agency_category_id)
            ->whereDoesntHave('financialAnalysis')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('pages.financial-analysis-select-transfer', compact('transfers'));
    }

    /**
     * Financial Analysis Create Form
     */
    public function financialAnalysisCreate($transferId): View
    {
        $user = auth()->user();
        
        // Only Agency users can create
        if (!$user->agency_category_id) {
            abort(403, 'Unauthorized access. Only Agency users can create Financial Analysis.');
        }
        
        $transfer = \App\Models\ContractorAnalysisTransfer::with([
            'projects.agencyCategory',
            'projects.district',
            'projects.projectCategory',
            'contractors.upkjRecords'
        ])->findOrFail($transferId);
        
        // Verify transfer is assigned to user's agency
        if ($transfer->agency_category_id !== $user->agency_category_id) {
            abort(403, 'Unauthorized access. This transfer is not assigned to your agency.');
        }
        
        // Check if analysis already exists
        $existingAnalysis = \App\Models\FinancialAnalysis::where('contractor_analysis_transfer_id', $transferId)->first();
        if ($existingAnalysis) {
            return redirect()->route('pages.financial-analysis.show', $existingAnalysis->id)
                ->with('info', 'Financial Analysis already exists for this transfer.');
        }
        
        // Get default approval committee positions
        $defaultPositions = [
            'Residen Bahagian Sibu',
            'Jurutera Bahagian (JKR)',
            'Jurutera Bahagian (Pengairan & Saliran)',
            'Jurutera Bahagian (Bekalan Air)',
            'Setiausaha (Majlis Perbandaran)',
            'Setiausaha (Majlis Daerah)',
            'Pengerusi Jawatankuasa Perolehan RTP 2024',
            'Ahli Jawatankuasa Perolehan RTP 2024',
            'Pegawai Daerah (Sibu)',
            'Pegawai Daerah (Kanowit)',
            'Pegawai Daerah (Selangau)',
            'Unit Integriti dan Ombudsman',
            'Setiausaha Majlis Luar Bandar',
        ];
        
        return view('pages.financial-analysis-create', compact('transfer', 'defaultPositions'));
    }

    /**
     * Store Financial Analysis
     */
    public function financialAnalysisStore(Request $request, $transferId)
    {
        $user = auth()->user();
        
        // Only Agency users can create
        if (!$user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $transfer = \App\Models\ContractorAnalysisTransfer::findOrFail($transferId);
        
        // Verify transfer is assigned to user's agency
        if ($transfer->agency_category_id !== $user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        // Validation
        $validated = $request->validate([
            'approval_committee' => 'required|array|min:1',
            'approval_committee.*.position' => 'required|string',
            'approval_committee.*.name' => 'nullable|string',
            'approval_committee.*.department' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        try {
            // Create financial analysis
            $analysis = \App\Models\FinancialAnalysis::create([
                'contractor_analysis_transfer_id' => $transferId,
                'created_by' => $user->id,
                'status' => 'Draft',
            ]);
            
            // Auto-populate project information
            $analysis->populateFromTransfer($transfer);
            $analysis->save();
            
            // Create approval committee records
            foreach ($request->approval_committee as $index => $committee) {
                \App\Models\FinancialAnalysisApproval::create([
                    'financial_analysis_id' => $analysis->id,
                    'position' => $committee['position'],
                    'name' => $committee['name'] ?? null,
                    'department' => $committee['department'] ?? null,
                    'display_order' => $index + 1,
                ]);
            }
            
            // Create contractor evaluation records
            // CRITICAL: Eager load upkjRecords to populate Class and UPKJ data
            $contractors = $transfer->contractors()->with('upkjRecords')->get();
            foreach ($contractors as $index => $contractor) {
                $evaluation = \App\Models\FinancialAnalysisContractor::create([
                    'financial_analysis_id' => $analysis->id,
                    'contractor_category_id' => $contractor->id,
                    'display_order' => $index + 1,
                ]);
                
                // Auto-populate contractor basic info
                $evaluation->populateFromContractor($contractor);
                $evaluation->save();
                
                // Create 5 bank statement entries (last 5 months)
                for ($i = 4; $i >= 0; $i--) {
                    $monthDate = now()->subMonths($i)->startOfMonth();
                    \App\Models\FinancialAnalysisBankStatement::create([
                        'financial_analysis_contractor_id' => $evaluation->id,
                        'month_year' => $monthDate->format('M-y'),
                        'month_date' => $monthDate,
                        'display_order' => 5 - $i,
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('pages.financial-analysis.edit', $analysis->id)
                ->with('success', 'Financial Analysis created successfully. Please enter financial data for each contractor.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create Financial Analysis: ' . $e->getMessage());
        }
    }

    /**
     * Show Financial Analysis Detail
     */
    public function financialAnalysisShow($id): View
    {
        $user = auth()->user();
        
        // Only Agency and Residen users can access
        if (!$user->agency_category_id && !$user->residen_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $analysis = \App\Models\FinancialAnalysis::with([
            'transfer.projects',
            'transfer.agency',
            'creator',
            'submitter',
            'approver',
            'rejector',
            'contractors.contractor',
            'contractors.bankStatements',
            'approvals'
        ])->findOrFail($id);
        
        // Verify access
        if ($user->agency_category_id) {
            if ($analysis->transfer->agency_category_id !== $user->agency_category_id) {
                abort(403, 'Unauthorized access.');
            }
        }
        
        return view('pages.financial-analysis-show', compact('analysis'));
    }

    /**
     * Edit Financial Analysis Form
     */
    public function financialAnalysisEdit($id): View
    {
        $user = auth()->user();
        
        // Only Agency users can edit
        if (!$user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $analysis = \App\Models\FinancialAnalysis::with([
            'transfer.projects',
            'contractors.contractor',
            'contractors.bankStatements',
            'approvals'
        ])->findOrFail($id);
        
        // Verify access
        if ($analysis->transfer->agency_category_id !== $user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        // Verify status is Draft
        if ($analysis->status !== 'Draft') {
            return redirect()->route('pages.financial-analysis.show', $id)
                ->with('error', 'Only Draft analyses can be edited.');
        }
        
        return view('pages.financial-analysis-edit', compact('analysis'));
    }

    /**
     * Update Financial Analysis
     */
    public function financialAnalysisUpdate(Request $request, $id)
    {
        $user = auth()->user();
        
        // Only Agency users can update
        if (!$user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $analysis = \App\Models\FinancialAnalysis::findOrFail($id);
        
        // Verify access
        if ($analysis->transfer->agency_category_id !== $user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        // Verify status is Draft
        if ($analysis->status !== 'Draft') {
            return redirect()->back()->with('error', 'Only Draft analyses can be edited.');
        }
        
        // Validation
        $validated = $request->validate([
            'contractors' => 'required|array',
            'contractors.*.id' => 'required|exists:financial_analysis_contractors,id',
            'contractors.*.registration_validity_date' => 'nullable|date',
            'contractors.*.current_contract_load' => 'nullable|numeric|min:0',
            'contractors.*.performance_record' => 'nullable|string',
            'contractors.*.minimum_capital_requirement' => 'nullable|numeric|min:0',
            'contractors.*.fixed_deposit' => 'nullable|numeric|min:0',
            'contractors.*.credit_facility_balance' => 'nullable|numeric|min:0',
            'contractors.*.additional_credit_facility' => 'nullable|numeric|min:0',
            'contractors.*.meeting_decision' => 'nullable|string',
            'contractors.*.justification' => 'nullable|string',
            'contractors.*.is_qualified' => 'nullable|boolean',
            'contractors.*.remarks' => 'nullable|string',
            'contractors.*.bank_statements' => 'nullable|array',
            'contractors.*.bank_statements.*.id' => 'required|exists:financial_analysis_bank_statements,id',
            'contractors.*.bank_statements.*.ending_balance' => 'nullable|numeric',
        ]);
        
        DB::beginTransaction();
        try {
            foreach ($request->contractors as $contractorData) {
                $contractor = \App\Models\FinancialAnalysisContractor::findOrFail($contractorData['id']);
                
                // Update contractor evaluation
                $contractor->update([
                    'registration_validity_date' => $contractorData['registration_validity_date'] ?? null,
                    'current_contract_load' => $contractorData['current_contract_load'] ?? null,
                    'performance_record' => $contractorData['performance_record'] ?? null,
                    'minimum_capital_requirement' => $contractorData['minimum_capital_requirement'] ?? null,
                    'fixed_deposit' => $contractorData['fixed_deposit'] ?? null,
                    'credit_facility_balance' => $contractorData['credit_facility_balance'] ?? null,
                    'additional_credit_facility' => $contractorData['additional_credit_facility'] ?? null,
                    'meeting_decision' => $contractorData['meeting_decision'] ?? null,
                    'justification' => $contractorData['justification'] ?? null,
                    'is_qualified' => $contractorData['is_qualified'] ?? null,
                    'remarks' => $contractorData['remarks'] ?? null,
                ]);
                
                // Update bank statements
                if (isset($contractorData['bank_statements'])) {
                    foreach ($contractorData['bank_statements'] as $statementData) {
                        $statement = \App\Models\FinancialAnalysisBankStatement::findOrFail($statementData['id']);
                        $statement->update([
                            'ending_balance' => $statementData['ending_balance'] ?? null,
                        ]);
                    }
                }
                
                // Recalculate three-month average
                $contractor->three_month_average = $contractor->calculateThreeMonthAverage();
                $contractor->save();
            }
            
            DB::commit();
            
            return redirect()->route('pages.financial-analysis.edit', $id)
                ->with('success', 'Financial Analysis updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update Financial Analysis: ' . $e->getMessage());
        }
    }

    /**
     * Submit Financial Analysis for Approval
     */
    public function financialAnalysisSubmit($id)
    {
        $user = auth()->user();
        
        // Only Agency users can submit
        if (!$user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $analysis = \App\Models\FinancialAnalysis::findOrFail($id);
        
        // Verify access
        if ($analysis->transfer->agency_category_id !== $user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        // Verify status is Draft
        if ($analysis->status !== 'Draft') {
            return redirect()->back()->with('error', 'Only Draft analyses can be submitted.');
        }
        
        // Verify has contractors
        if ($analysis->contractors()->count() === 0) {
            return redirect()->back()->with('error', 'Cannot submit analysis with no contractors.');
        }
        
        // Update status
        $analysis->update([
            'status' => 'Submitted',
            'submitted_by' => $user->id,
            'submitted_at' => now(),
        ]);
        
        return redirect()->route('pages.financial-analysis.show', $id)
            ->with('success', 'Financial Analysis submitted for approval successfully.');
    }

    /**
     * Approve Financial Analysis
     */
    public function financialAnalysisApprove($id)
    {
        $user = auth()->user();
        
        // Only Residen users can approve
        if (!$user->residen_category_id) {
            abort(403, 'Unauthorized access. Only Residen users can approve.');
        }
        
        $analysis = \App\Models\FinancialAnalysis::findOrFail($id);
        
        // Verify status is Submitted
        if ($analysis->status !== 'Submitted') {
            return redirect()->back()->with('error', 'Only Submitted analyses can be approved.');
        }
        
        // Update status
        $analysis->update([
            'status' => 'Approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
        
        return redirect()->route('pages.financial-analysis.show', $id)
            ->with('success', 'Financial Analysis approved successfully.');
    }

    /**
     * Reject Financial Analysis
     */
    public function financialAnalysisReject(Request $request, $id)
    {
        $user = auth()->user();
        
        // Only Residen users can reject
        if (!$user->residen_category_id) {
            abort(403, 'Unauthorized access. Only Residen users can reject.');
        }
        
        $analysis = \App\Models\FinancialAnalysis::findOrFail($id);
        
        // Verify status is Submitted
        if ($analysis->status !== 'Submitted') {
            return redirect()->back()->with('error', 'Only Submitted analyses can be rejected.');
        }
        
        // Validate rejection remarks
        $request->validate([
            'rejection_remarks' => 'required|string',
        ]);
        
        // Update status
        $analysis->update([
            'status' => 'Rejected',
            'rejected_by' => $user->id,
            'rejected_at' => now(),
            'rejection_remarks' => $request->rejection_remarks,
        ]);
        
        return redirect()->route('pages.financial-analysis.show', $id)
            ->with('success', 'Financial Analysis rejected.');
    }

    /**
     * Delete Financial Analysis
     */
    public function financialAnalysisDelete($id)
    {
        $user = auth()->user();
        
        // Only Agency users can delete
        if (!$user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $analysis = \App\Models\FinancialAnalysis::findOrFail($id);
        
        // Verify access
        if ($analysis->transfer->agency_category_id !== $user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        // Verify status is Draft
        if ($analysis->status !== 'Draft') {
            return redirect()->back()->with('error', 'Only Draft analyses can be deleted.');
        }
        
        // Delete analysis (cascade deletes related records)
        $analysis->delete();
        
        return redirect()->route('pages.financial-analysis')
            ->with('success', 'Financial Analysis deleted successfully.');
    }

    public function financialAnalysisExportExcel($id)
    {
        $user = auth()->user();
        
        // Only Agency and Residen users can export
        if (!$user->agency_category_id && !$user->residen_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $analysis = \App\Models\FinancialAnalysis::with([
            'transfer.projects',
            'contractors.contractor',
            'contractors.bankStatements',
            'approvals'
        ])->findOrFail($id);
        
        // Verify access for Agency users
        if ($user->agency_category_id && $analysis->transfer->agency_category_id !== $user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $exporter = new \App\Services\FinancialAnalysisExcelExport();
        $filepath = $exporter->export($analysis);
        
        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    public function financialAnalysisExportPdf($id)
    {
        $user = auth()->user();
        
        // Only Agency and Residen users can export
        if (!$user->agency_category_id && !$user->residen_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $analysis = \App\Models\FinancialAnalysis::with([
            'transfer.projects',
            'contractors.contractor',
            'contractors.bankStatements',
            'approvals'
        ])->findOrFail($id);
        
        // Verify access for Agency users
        if ($user->agency_category_id && $analysis->transfer->agency_category_id !== $user->agency_category_id) {
            abort(403, 'Unauthorized access.');
        }
        
        $pdf = \PDF::loadView('pages.financial-analysis-pdf', compact('analysis'));
        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'financial_analysis_' . $analysis->id . '_' . date('YmdHis') . '.pdf';
        
        return $pdf->download($filename);
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

    // Master Data - UPKJ Classifications
    public function masterDataUpkj(): View
    {
        $upkjClassifications = \App\Models\UpkjClassification::orderBy('category')
            ->orderBy('class')
            ->orderBy('head_code')
            ->orderBy('subhead_code')
            ->get();

        // Get unique values for dropdowns
        $classes = \App\Models\UpkjClassification::getClasses();
        $headCodes = \App\Models\UpkjClassification::getHeadCodes();
        $subheadCodes = \App\Models\UpkjClassification::getSubheadCodes();
        $subheadLetters = \App\Models\UpkjClassification::getSubheadLetters();
        $subheadRomans = \App\Models\UpkjClassification::getSubheadRomans();

        return view('pages.master-data.upkj', compact(
            'upkjClassifications',
            'classes',
            'headCodes',
            'subheadCodes',
            'subheadLetters',
            'subheadRomans'
        ));
    }

    public function masterDataUpkjStore(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'class' => 'nullable|string|max:255',
            'class_description' => 'nullable|string|max:255',
            'head_code' => 'nullable|string|max:255',
            'head_name' => 'nullable|string|max:255',
            'subhead_code' => 'nullable|string|max:255',
            'subhead_letter' => 'nullable|string|max:255',
            'subhead_roman' => 'nullable|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        \App\Models\UpkjClassification::create($request->all());

        return redirect()->route('pages.master-data.upkj')->with('success', 'UPKJ Classification created successfully');
    }

    public function masterDataUpkjUpdate(Request $request, $id)
    {
        $upkj = \App\Models\UpkjClassification::findOrFail($id);

        $request->validate([
            'category' => 'required|string|max:255',
            'class' => 'nullable|string|max:255',
            'class_description' => 'nullable|string|max:255',
            'head_code' => 'nullable|string|max:255',
            'head_name' => 'nullable|string|max:255',
            'subhead_code' => 'nullable|string|max:255',
            'subhead_letter' => 'nullable|string|max:255',
            'subhead_roman' => 'nullable|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        $upkj->update($request->all());

        return redirect()->route('pages.master-data.upkj')->with('success', 'UPKJ Classification updated successfully');
    }

    public function masterDataUpkjDelete($id)
    {
        $upkj = \App\Models\UpkjClassification::findOrFail($id);
        $upkj->delete();

        return redirect()->route('pages.master-data.upkj')->with('success', 'UPKJ Classification deleted successfully');
    }

    /**
     * Get all UPKJ classes (API endpoint)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpkjClasses()
    {
        $classes = DB::table('upkj_classifications')
            ->select('class', 'class_description')
            ->distinct()
            ->orderBy('class')
            ->get();
        
        return response()->json($classes);
    }
    
    /**
     * Get UPKJ heads for a specific class (API endpoint)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpkjHeads(Request $request)
    {
        $class = $request->input('class');
        
        if (!$class) {
            return response()->json([]);
        }
        
        $heads = \App\Models\ContractorCategory::getUpkjHeads($class);
        
        return response()->json($heads);
    }
    
    /**
     * Get UPKJ subheads for a specific class and head (API endpoint)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpkjSubheads(Request $request)
    {
        $class = $request->input('class');
        $head = $request->input('head');
        
        if (!$class || !$head) {
            return response()->json([]);
        }
        
        $subheads = \App\Models\ContractorCategory::getUpkjSubheads($class, $head);
        
        return response()->json($subheads);
    }
}
