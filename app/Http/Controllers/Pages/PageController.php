<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
        $request->validate([
            'app_name' => 'required|string|max:255',
            'sidebar_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'sidebar_display' => 'required|in:name_only,logo_only,logo_and_name',
            'sidebar_logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
            'items_per_page' => 'nullable|integer|min:5|max:100',
            'session_lifetime' => 'nullable|integer|min:30|max:1440',
            'maintenance_mode' => 'nullable|boolean',
        ]);

        \App\Models\IntegrationSetting::setSetting('application', 'app_name', $request->app_name);
        \App\Models\IntegrationSetting::setSetting('application', 'sidebar_name', $request->sidebar_name);
        \App\Models\IntegrationSetting::setSetting('application', 'app_url', $request->app_url);
        \App\Models\IntegrationSetting::setSetting('application', 'sidebar_display', $request->sidebar_display);
        
        // Handle logo upload
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
        }
        
        \App\Models\IntegrationSetting::setSetting('application', 'items_per_page', $request->items_per_page ?? 10);
        \App\Models\IntegrationSetting::setSetting('application', 'session_lifetime', $request->session_lifetime ?? 120);
        \App\Models\IntegrationSetting::setSetting('application', 'maintenance_mode', $request->has('maintenance_mode') ? 1 : 0);

        return redirect()->route('pages.general.application')->with('success', 'Application settings saved successfully');
    }

    // General Settings - Localization
    public function generalLocalization(): View
    {
        $settings = \App\Models\IntegrationSetting::getSettings('localization');
        return view('pages.general.localization', compact('settings'));
    }

    public function generalLocalizationStore(Request $request)
    {
        $request->validate([
            'locale' => 'required|string|in:en,ms,zh',
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

    // General Settings - Translation
    public function generalTranslation(): View
    {
        $lang = request('lang', 'en');
        $translations = \App\Models\IntegrationSetting::getSettings('translation_' . $lang);
        return view('pages.general.translation', compact('translations', 'lang'));
    }

    public function generalTranslationStore(Request $request)
    {
        $request->validate([
            'language' => 'required|in:en,ms,zh',
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
        $parliaments = \App\Models\Parliament::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.parliaments', compact('parliaments'));
    }

    public function masterDataParliamentsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:parliaments,code',
            'budget' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\Parliament::create($request->all());

        return redirect()->route('pages.master-data.parliaments')->with('success', 'Parliament created successfully');
    }

    public function masterDataParliamentsUpdate(Request $request, $id)
    {
        $parliament = \App\Models\Parliament::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:parliaments,code,' . $id,
            'budget' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $parliament->update($request->all());

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
        $duns = \App\Models\Dun::orderBy('created_at', 'desc')->get();
        return view('pages.master-data.duns', compact('duns'));
    }

    public function masterDataDunsStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'code' => 'required|string|max:255|unique:duns,code',
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        \App\Models\Dun::create($request->all());

        return redirect()->route('pages.master-data.duns')->with('success', 'DUN created successfully');
    }

    public function masterDataDunsUpdate(Request $request, $id)
    {
        $dun = \App\Models\Dun::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'code' => 'required|string|max:255|unique:duns,code,' . $id,
            'description' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
        ]);

        $dun->update($request->all());

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
        $users = \App\Models\User::with('residenCategory')->orderBy('created_at', 'desc')->get();
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
            'api_key' => 'nullable|string|max:255',
            'sender_id' => 'required|string|max:255',
        ]);

        foreach ($request->except('_token') as $key => $value) {
            if ($key === 'api_key' && empty($value)) {
                continue;
            }
            \App\Models\IntegrationSetting::setSetting('sms', $key, $value);
        }

        return redirect()->route('pages.integrations.sms')->with('success', 'SMS configuration saved successfully');
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
        $preProjects = \App\Models\PreProject::with([
            'residenCategory', 
            'agencyCategory', 
            'parliament', 
            'projectCategory',
            'division',
            'district',
            'parliamentLocation',
            'dun',
            'landTitleStatus',
            'implementingAgency',
            'implementationMethod',
            'projectOwnership'
        ])->orderBy('created_at', 'desc')->get();
        
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
            'preProjects',
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

    public function preProjectStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'residen_category_id' => 'nullable|exists:residen_categories,id',
            'agency_category_id' => 'nullable|exists:agency_categories,id',
            'parliament_dun_basic' => 'nullable|string',
            'project_category_id' => 'nullable|exists:project_categories,id',
            'project_scope' => 'nullable|string',
            'actual_project_cost' => 'nullable|numeric|min:0',
            'consultation_cost' => 'nullable|numeric|min:0',
            'lss_inspection_cost' => 'nullable|numeric|min:0',
            'sst' => 'nullable|numeric|min:0',
            'others_cost' => 'nullable|numeric|min:0',
            'implementation_period' => 'nullable|string|max:255',
            'division_id' => 'nullable|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'parliament_location_id' => 'nullable|exists:parliaments,id',
            'dun_id' => 'nullable|exists:duns,id',
            'site_layout' => 'nullable|in:Yes,No',
            'land_title_status_id' => 'nullable|exists:land_title_statuses,id',
            'consultation_service' => 'nullable|in:Yes,No',
            'implementing_agency_id' => 'nullable|exists:agency_categories,id',
            'implementation_method_id' => 'nullable|exists:implementation_methods,id',
            'project_ownership_id' => 'nullable|exists:project_ownerships,id',
            'jkkk_name' => 'nullable|string|max:255',
            'state_government_asset' => 'nullable|in:Yes,No',
            'bill_of_quantity' => 'nullable|in:Yes,No',
            'bill_of_quantity_attachment' => 'required_if:bill_of_quantity,Yes|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

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
        
        $data['status'] = 'Active';

        \App\Models\PreProject::create($data);

        return redirect()->route('pages.pre-project')->with('success', 'Pre-Project created successfully');
    }

    public function preProjectUpdate(Request $request, $id)
    {
        $preProject = \App\Models\PreProject::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'residen_category_id' => 'nullable|exists:residen_categories,id',
            'agency_category_id' => 'nullable|exists:agency_categories,id',
            'parliament_dun_basic' => 'nullable|string',
            'project_category_id' => 'nullable|exists:project_categories,id',
            'project_scope' => 'nullable|string',
            'actual_project_cost' => 'nullable|numeric|min:0',
            'consultation_cost' => 'nullable|numeric|min:0',
            'lss_inspection_cost' => 'nullable|numeric|min:0',
            'sst' => 'nullable|numeric|min:0',
            'others_cost' => 'nullable|numeric|min:0',
            'implementation_period' => 'nullable|string|max:255',
            'division_id' => 'nullable|exists:divisions,id',
            'district_id' => 'nullable|exists:districts,id',
            'parliament_location_id' => 'nullable|exists:parliaments,id',
            'dun_id' => 'nullable|exists:duns,id',
            'site_layout' => 'nullable|in:Yes,No',
            'land_title_status_id' => 'nullable|exists:land_title_statuses,id',
            'consultation_service' => 'nullable|in:Yes,No',
            'implementing_agency_id' => 'nullable|exists:agency_categories,id',
            'implementation_method_id' => 'nullable|exists:implementation_methods,id',
            'project_ownership_id' => 'nullable|exists:project_ownerships,id',
            'jkkk_name' => 'nullable|string|max:255',
            'state_government_asset' => 'nullable|in:Yes,No',
            'bill_of_quantity' => 'nullable|in:Yes,No',
            'bill_of_quantity_attachment' => 'required_if:bill_of_quantity,Yes|file|mimes:pdf,doc,docx,xls,xlsx|max:10240',
        ]);

        $data = $request->except(['parliament_dun_basic', 'bill_of_quantity_attachment']);
        
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
        
        // Calculate total cost
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
            'projectOwnership'
        ])->findOrFail($id);
        
        return response()->json($preProject);
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

    public function preProjectNoc(): View
    {
        return view('pages.pre-project-noc');
    }

    public function project(): View
    {
        return view('pages.project');
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
}
