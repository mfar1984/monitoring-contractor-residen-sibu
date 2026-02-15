@extends('layouts.app')

@section('title', 'General Settings - Translation - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>General</span>
    <span class="breadcrumb-separator">›</span>
    <span>Translation</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-general-tabs active="translation" />
        
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

            <div style="display: grid; grid-template-columns: 200px 1fr; gap: 0;">
                <!-- Sidebar Tabs (Left) -->
                <div style="background: white; border: 1px solid #e0e0e0; border-top-left-radius: 8px; border-bottom-left-radius: 8px; border-right: none; padding: 10px;">
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <a href="{{ route('pages.general.translation', ['lang' => 'en']) }}" 
                           class="language-tab {{ (request('lang', 'en') == 'en') ? 'active' : '' }}"
                           style="padding: 12px 16px; text-decoration: none; color: #333; border-radius: 4px; transition: all 0.2s; display: block; {{ (request('lang', 'en') == 'en') ? 'background-color: #007bff; color: white;' : 'background-color: transparent;' }}">
                            English
                        </a>
                        <a href="{{ route('pages.general.translation', ['lang' => 'ms']) }}" 
                           class="language-tab {{ (request('lang') == 'ms') ? 'active' : '' }}"
                           style="padding: 12px 16px; text-decoration: none; color: #333; border-radius: 4px; transition: all 0.2s; display: block; {{ (request('lang') == 'ms') ? 'background-color: #007bff; color: white;' : 'background-color: transparent;' }}">
                            Bahasa Melayu
                        </a>
                        <a href="{{ route('pages.general.translation', ['lang' => 'zh']) }}" 
                           class="language-tab {{ (request('lang') == 'zh') ? 'active' : '' }}"
                           style="padding: 12px 16px; text-decoration: none; color: #333; border-radius: 4px; transition: all 0.2s; display: block; {{ (request('lang') == 'zh') ? 'background-color: #007bff; color: white;' : 'background-color: transparent;' }}">
                            中文 (Chinese)
                        </a>
                    </div>
                </div>

                <!-- Content Area (Right) -->
                <div style="background: white; border: 1px solid #e0e0e0; border-top-right-radius: 8px; border-bottom-right-radius: 8px; padding: 20px;">
                    <div style="margin-bottom: 8px;">
                        <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 3px;">
                            @if(request('lang', 'en') == 'en')
                                English to English
                            @elseif(request('lang') == 'ms')
                                English to Bahasa Melayu
                            @elseif(request('lang') == 'zh')
                                English to Chinese (中文)
                            @endif
                        </h2>
                        <p style="color: #666; font-size: 12px; margin: 0;">Translate application text to {{ request('lang', 'en') == 'en' ? 'English' : (request('lang') == 'ms' ? 'Bahasa Melayu' : 'Chinese') }}</p>
                    </div>

                    <form method="POST" action="{{ route('pages.general.translation.store') }}">
                        @csrf
                        <input type="hidden" name="language" value="{{ request('lang', 'en') }}">

                        <!-- Translation Fields -->
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            
                            <!-- Section: Sidebar Menu -->
                            <div style="margin-top: 2px; margin-bottom: 6px;">
                                <h3 style="font-size: 13px; font-weight: 600; color: #333; border-bottom: 1px solid #e0e0e0; padding-bottom: 3px; margin-bottom: 6px;">Sidebar Menu</h3>
                            </div>

                            <!-- Overview -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="Overview" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[overview]" value="{{ $translations['overview'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                            <!-- Project -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="Project" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[project]" value="{{ $translations['project'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                            <!-- Pre Project -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="Pre Project" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[pre_project]" value="{{ $translations['pre_project'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                            <!-- Contractor Analysis -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="Contractor Analysis" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[contractor_analysis]" value="{{ $translations['contractor_analysis'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                            <!-- System Settings -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="System Settings" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[system_settings]" value="{{ $translations['system_settings'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                            <!-- General -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="General" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[general]" value="{{ $translations['general'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                            <!-- Master Data -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="Master Data" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[master_data]" value="{{ $translations['master_data'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                            <!-- Group Roles -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="Group Roles" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[group_roles]" value="{{ $translations['group_roles'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                            <!-- Users ID -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="Users ID" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[users_id]" value="{{ $translations['users_id'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                            <!-- Integrations -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="Integrations" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[integrations]" value="{{ $translations['integrations'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                            <!-- Activity Log -->
                            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px; align-items: center;">
                                <input type="text" value="Activity Log" readonly style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; background-color: #f5f5f5; font-size: 11px;">
                                <input type="text" name="translations[activity_log]" value="{{ $translations['activity_log'] ?? '' }}" placeholder="Enter translation" style="width: 100%; padding: 5px 8px; border: 1px solid #e0e0e0; border-radius: 4px; font-size: 11px;">
                            </div>

                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 16px; border-top: 1px solid #e0e0e0;">
                            <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Reset</button>
                            <button type="submit" class="btn btn-primary">Save Translations</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
    .language-tab:hover {
        background-color: #f0f0f0 !important;
    }

    .language-tab.active:hover {
        background-color: #0056b3 !important;
    }
    </style>
@endsection
