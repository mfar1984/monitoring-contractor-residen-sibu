<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Pages\PageController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Guest routes (no authentication required)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Protected routes (authentication required)
Route::middleware([\App\Http\Middleware\Authenticate::class, 'maintenance'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Page routes
    Route::get('/pages/overview', [PageController::class, 'overview'])->name('pages.overview');
    
    // General Settings routes
    Route::get('/pages/general', function() {
        return redirect()->route('pages.general.system');
    })->name('pages.general');
    Route::get('/pages/general/system', [PageController::class, 'generalSystem'])->name('pages.general.system');
    Route::get('/pages/general/application', [PageController::class, 'generalApplication'])->name('pages.general.application');
    Route::post('/pages/general/application', [PageController::class, 'generalApplicationStore'])->name('pages.general.application.store');
    Route::get('/pages/general/localization', [PageController::class, 'generalLocalization'])->name('pages.general.localization');
    Route::post('/pages/general/localization', [PageController::class, 'generalLocalizationStore'])->name('pages.general.localization.store');
    Route::get('/pages/general/maintenance', [PageController::class, 'generalMaintenance'])->name('pages.general.maintenance');
    Route::get('/pages/general/translation', [PageController::class, 'generalTranslation'])->name('pages.general.translation');
    Route::get('/pages/master-data', [PageController::class, 'masterData'])->name('pages.master-data');
    Route::get('/pages/master-data/residen', [PageController::class, 'masterDataResiden'])->name('pages.master-data.residen');
    Route::post('/pages/master-data/residen', [PageController::class, 'masterDataResidenStore'])->name('pages.master-data.residen.store');
    Route::put('/pages/master-data/residen/{id}', [PageController::class, 'masterDataResidenUpdate'])->name('pages.master-data.residen.update');
    Route::delete('/pages/master-data/residen/{id}', [PageController::class, 'masterDataResidenDelete'])->name('pages.master-data.residen.delete');
    Route::get('/pages/master-data/agency', [PageController::class, 'masterDataAgency'])->name('pages.master-data.agency');
    Route::post('/pages/master-data/agency', [PageController::class, 'masterDataAgencyStore'])->name('pages.master-data.agency.store');
    Route::put('/pages/master-data/agency/{id}', [PageController::class, 'masterDataAgencyUpdate'])->name('pages.master-data.agency.update');
    Route::delete('/pages/master-data/agency/{id}', [PageController::class, 'masterDataAgencyDelete'])->name('pages.master-data.agency.delete');
    Route::get('/pages/master-data/parliament', [PageController::class, 'masterDataParliament'])->name('pages.master-data.parliament');
    Route::post('/pages/master-data/parliament', [PageController::class, 'masterDataParliamentStore'])->name('pages.master-data.parliament.store');
    Route::put('/pages/master-data/parliament/{id}', [PageController::class, 'masterDataParliamentUpdate'])->name('pages.master-data.parliament.update');
    Route::delete('/pages/master-data/parliament/{id}', [PageController::class, 'masterDataParliamentDelete'])->name('pages.master-data.parliament.delete');
    Route::get('/pages/master-data/contractor', [PageController::class, 'masterDataContractor'])->name('pages.master-data.contractor');
    Route::post('/pages/master-data/contractor', [PageController::class, 'masterDataContractorStore'])->name('pages.master-data.contractor.store');
    Route::put('/pages/master-data/contractor/{id}', [PageController::class, 'masterDataContractorUpdate'])->name('pages.master-data.contractor.update');
    Route::delete('/pages/master-data/contractor/{id}', [PageController::class, 'masterDataContractorDelete'])->name('pages.master-data.contractor.delete');
    Route::get('/pages/master-data/status', [PageController::class, 'masterDataStatus'])->name('pages.master-data.status');
    Route::get('/pages/master-data/project-category', [PageController::class, 'masterDataProjectCategory'])->name('pages.master-data.project-category');
    Route::post('/pages/master-data/project-category', [PageController::class, 'masterDataProjectCategoryStore'])->name('pages.master-data.project-category.store');
    Route::put('/pages/master-data/project-category/{id}', [PageController::class, 'masterDataProjectCategoryUpdate'])->name('pages.master-data.project-category.update');
    Route::delete('/pages/master-data/project-category/{id}', [PageController::class, 'masterDataProjectCategoryDelete'])->name('pages.master-data.project-category.delete');
    Route::get('/pages/master-data/division', [PageController::class, 'masterDataDivision'])->name('pages.master-data.division');
    Route::post('/pages/master-data/division', [PageController::class, 'masterDataDivisionStore'])->name('pages.master-data.division.store');
    Route::put('/pages/master-data/division/{id}', [PageController::class, 'masterDataDivisionUpdate'])->name('pages.master-data.division.update');
    Route::delete('/pages/master-data/division/{id}', [PageController::class, 'masterDataDivisionDelete'])->name('pages.master-data.division.delete');
    Route::get('/pages/master-data/district', [PageController::class, 'masterDataDistrict'])->name('pages.master-data.district');
    Route::post('/pages/master-data/district', [PageController::class, 'masterDataDistrictStore'])->name('pages.master-data.district.store');
    Route::put('/pages/master-data/district/{id}', [PageController::class, 'masterDataDistrictUpdate'])->name('pages.master-data.district.update');
    Route::delete('/pages/master-data/district/{id}', [PageController::class, 'masterDataDistrictDelete'])->name('pages.master-data.district.delete');
    Route::get('/pages/master-data/parliaments', [PageController::class, 'masterDataParliaments'])->name('pages.master-data.parliaments');
    Route::post('/pages/master-data/parliaments', [PageController::class, 'masterDataParliamentsStore'])->name('pages.master-data.parliaments.store');
    Route::put('/pages/master-data/parliaments/{id}', [PageController::class, 'masterDataParliamentsUpdate'])->name('pages.master-data.parliaments.update');
    Route::delete('/pages/master-data/parliaments/{id}', [PageController::class, 'masterDataParliamentsDelete'])->name('pages.master-data.parliaments.delete');
    Route::get('/pages/master-data/duns', [PageController::class, 'masterDataDuns'])->name('pages.master-data.duns');
    Route::post('/pages/master-data/duns', [PageController::class, 'masterDataDunsStore'])->name('pages.master-data.duns.store');
    Route::put('/pages/master-data/duns/{id}', [PageController::class, 'masterDataDunsUpdate'])->name('pages.master-data.duns.update');
    Route::delete('/pages/master-data/duns/{id}', [PageController::class, 'masterDataDunsDelete'])->name('pages.master-data.duns.delete');
    Route::get('/pages/master-data/land-title-status', [PageController::class, 'masterDataLandTitleStatus'])->name('pages.master-data.land-title-status');
    Route::post('/pages/master-data/land-title-status', [PageController::class, 'masterDataLandTitleStatusStore'])->name('pages.master-data.land-title-status.store');
    Route::put('/pages/master-data/land-title-status/{id}', [PageController::class, 'masterDataLandTitleStatusUpdate'])->name('pages.master-data.land-title-status.update');
    Route::delete('/pages/master-data/land-title-status/{id}', [PageController::class, 'masterDataLandTitleStatusDelete'])->name('pages.master-data.land-title-status.delete');
    Route::get('/pages/master-data/project-ownership', [PageController::class, 'masterDataProjectOwnership'])->name('pages.master-data.project-ownership');
    Route::post('/pages/master-data/project-ownership', [PageController::class, 'masterDataProjectOwnershipStore'])->name('pages.master-data.project-ownership.store');
    Route::put('/pages/master-data/project-ownership/{id}', [PageController::class, 'masterDataProjectOwnershipUpdate'])->name('pages.master-data.project-ownership.update');
    Route::delete('/pages/master-data/project-ownership/{id}', [PageController::class, 'masterDataProjectOwnershipDelete'])->name('pages.master-data.project-ownership.delete');
    Route::get('/pages/master-data/implementation-method', [PageController::class, 'masterDataImplementationMethod'])->name('pages.master-data.implementation-method');
    Route::post('/pages/master-data/implementation-method', [PageController::class, 'masterDataImplementationMethodStore'])->name('pages.master-data.implementation-method.store');
    Route::put('/pages/master-data/implementation-method/{id}', [PageController::class, 'masterDataImplementationMethodUpdate'])->name('pages.master-data.implementation-method.update');
    Route::delete('/pages/master-data/implementation-method/{id}', [PageController::class, 'masterDataImplementationMethodDelete'])->name('pages.master-data.implementation-method.delete');
    Route::get('/pages/group-roles', [PageController::class, 'groupRoles'])->name('pages.group-roles');
    Route::get('/pages/users-id', [PageController::class, 'usersId'])->name('pages.users-id');
    Route::get('/pages/users-id/residen', [PageController::class, 'usersIdResiden'])->name('pages.users-id.residen');
    Route::post('/pages/users-id/residen', [PageController::class, 'usersIdResidenStore'])->name('pages.users-id.residen.store');
    Route::put('/pages/users-id/residen/{id}', [PageController::class, 'usersIdResidenUpdate'])->name('pages.users-id.residen.update');
    Route::delete('/pages/users-id/residen/{id}', [PageController::class, 'usersIdResidenDelete'])->name('pages.users-id.residen.delete');
    Route::get('/pages/users-id/agency', [PageController::class, 'usersIdAgency'])->name('pages.users-id.agency');
    Route::post('/pages/users-id/agency', [PageController::class, 'usersIdAgencyStore'])->name('pages.users-id.agency.store');
    Route::put('/pages/users-id/agency/{id}', [PageController::class, 'usersIdAgencyUpdate'])->name('pages.users-id.agency.update');
    Route::delete('/pages/users-id/agency/{id}', [PageController::class, 'usersIdAgencyDelete'])->name('pages.users-id.agency.delete');
    Route::get('/pages/users-id/parliament', [PageController::class, 'usersIdParliament'])->name('pages.users-id.parliament');
    Route::post('/pages/users-id/parliament', [PageController::class, 'usersIdParliamentStore'])->name('pages.users-id.parliament.store');
    Route::put('/pages/users-id/parliament/{id}', [PageController::class, 'usersIdParliamentUpdate'])->name('pages.users-id.parliament.update');
    Route::delete('/pages/users-id/parliament/{id}', [PageController::class, 'usersIdParliamentDelete'])->name('pages.users-id.parliament.delete');
    Route::get('/pages/users-id/contractor', [PageController::class, 'usersIdContractor'])->name('pages.users-id.contractor');
    Route::post('/pages/users-id/contractor', [PageController::class, 'usersIdContractorStore'])->name('pages.users-id.contractor.store');
    Route::put('/pages/users-id/contractor/{id}', [PageController::class, 'usersIdContractorUpdate'])->name('pages.users-id.contractor.update');
    Route::delete('/pages/users-id/contractor/{id}', [PageController::class, 'usersIdContractorDelete'])->name('pages.users-id.contractor.delete');
    Route::get('/pages/integrations', [PageController::class, 'integrations'])->name('pages.integrations');
    Route::get('/pages/integrations/email', [PageController::class, 'integrationsEmail'])->name('pages.integrations.email');
    Route::post('/pages/integrations/email', [PageController::class, 'integrationsEmailStore'])->name('pages.integrations.email.store');
    Route::get('/pages/integrations/sms', [PageController::class, 'integrationsSms'])->name('pages.integrations.sms');
    Route::post('/pages/integrations/sms', [PageController::class, 'integrationsSmsStore'])->name('pages.integrations.sms.store');
    Route::get('/pages/integrations/webhook', [PageController::class, 'integrationsWebhook'])->name('pages.integrations.webhook');
    Route::post('/pages/integrations/webhook', [PageController::class, 'integrationsWebhookStore'])->name('pages.integrations.webhook.store');
    Route::get('/pages/integrations/api', [PageController::class, 'integrationsApi'])->name('pages.integrations.api');
    Route::post('/pages/integrations/api', [PageController::class, 'integrationsApiStore'])->name('pages.integrations.api.store');
    Route::get('/pages/integrations/weather', [PageController::class, 'integrationsWeather'])->name('pages.integrations.weather');
    Route::post('/pages/integrations/weather', [PageController::class, 'integrationsWeatherStore'])->name('pages.integrations.weather.store');
    Route::post('/pages/integrations/email/test', [PageController::class, 'integrationsEmailTest'])->name('pages.integrations.email.test');
    Route::post('/pages/integrations/weather/test', [PageController::class, 'integrationsWeatherTest'])->name('pages.integrations.weather.test');
    Route::get('/pages/activity-log', [PageController::class, 'activityLog'])->name('pages.activity-log');
    
    // Pre-Project, Project, Contractor Analysis routes
    Route::get('/pages/pre-project', [PageController::class, 'preProject'])->name('pages.pre-project');
    Route::post('/pages/pre-project', [PageController::class, 'preProjectStore'])->name('pages.pre-project.store');
    Route::get('/pages/pre-project/{id}/edit', [PageController::class, 'preProjectEdit'])->name('pages.pre-project.edit');
    Route::get('/pages/pre-project/{id}/print', [PageController::class, 'preProjectPrint'])->name('pages.pre-project.print');
    Route::put('/pages/pre-project/{id}', [PageController::class, 'preProjectUpdate'])->name('pages.pre-project.update');
    Route::delete('/pages/pre-project/{id}', [PageController::class, 'preProjectDelete'])->name('pages.pre-project.delete');
    Route::get('/pages/pre-project/noc', [PageController::class, 'preProjectNoc'])->name('pages.pre-project.noc');
    Route::get('/pages/project', [PageController::class, 'project'])->name('pages.project');
    Route::get('/pages/contractor-analysis', [PageController::class, 'contractorAnalysis'])->name('pages.contractor-analysis');
});

// Redirect root to dashboard or login
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});
