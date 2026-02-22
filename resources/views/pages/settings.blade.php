@extends('layouts.app')

@section('title', 'Settings - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>Settings</span>
@endsection

@section('content')
    <div class="tabs-container">
        <div class="tabs-header">
            <a href="{{ route('pages.profile') }}" class="tab-button {{ request()->routeIs('pages.profile') ? 'active' : '' }}">Profile</a>
            <a href="{{ route('pages.settings') }}" class="tab-button {{ request()->routeIs('pages.settings') ? 'active' : '' }}">Settings</a>
        </div>
        
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
                    <h3>User Settings</h3>
                    <p class="content-description">Manage your preferences and account settings.</p>
                </div>
            </div>

            <!-- Account Information Section -->
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #333333;">Account Information</h4>
                <p style="margin: 0 0 20px 0; font-size: 12px; color: #666666;">Your account details and user category.</p>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 16px; font-size: 12px;">
                    <div style="color: #666666; font-weight: 600;">Username:</div>
                    <div style="color: #333333;">{{ $user->username }}</div>

                    <div style="color: #666666; font-weight: 600;">Full Name:</div>
                    <div style="color: #333333;">{{ $user->full_name }}</div>

                    <div style="color: #666666; font-weight: 600;">Email:</div>
                    <div style="color: #333333;">{{ $user->email ?? '-' }}</div>

                    <div style="color: #666666; font-weight: 600;">Contact Number:</div>
                    <div style="color: #333333;">{{ $user->contact_number ?? '-' }}</div>

                    <div style="color: #666666; font-weight: 600;">Department:</div>
                    <div style="color: #333333;">{{ $user->department ?? '-' }}</div>

                    <div style="color: #666666; font-weight: 600;">User Category:</div>
                    <div style="color: #333333;">
                        @if($user->residen_category_id)
                            <span style="background-color: #007bff; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Residen</span>
                        @elseif($user->agency_category_id)
                            <span style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Agency</span>
                        @elseif($user->parliament_id)
                            <span style="background-color: #ffc107; color: #333333; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Parliament</span>
                        @elseif($user->dun_id)
                            <span style="background-color: #17a2b8; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">DUN</span>
                        @elseif($user->contractor_category_id)
                            <span style="background-color: #6c757d; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Contractor</span>
                        @else
                            <span style="color: #999999;">-</span>
                        @endif
                    </div>

                    <div style="color: #666666; font-weight: 600;">Account Created:</div>
                    <div style="color: #333333;">{{ $user->created_at->format('d M Y, h:i A') }}</div>

                    <div style="color: #666666; font-weight: 600;">Last Updated:</div>
                    <div style="color: #333333;">{{ $user->updated_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>

            <!-- Display Preferences Section -->
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-bottom: 20px;">
                <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #333333;">Localization & Display Preferences</h4>
                <p style="margin: 0 0 20px 0; font-size: 12px; color: #666666;">Customize language, timezone, and display formats.</p>
                
                <form method="POST" action="{{ route('pages.settings.update') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="locale">Language</label>
                        <select id="locale" name="locale">
                            <option value="en" {{ ($settings['locale'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                            <option value="ms" {{ ($settings['locale'] ?? 'en') == 'ms' ? 'selected' : '' }}>Bahasa Melayu</option>
                            <option value="zh" {{ ($settings['locale'] ?? 'en') == 'zh' ? 'selected' : '' }}>中文 (Chinese)</option>
                        </select>
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Select your preferred language for the interface</small>
                    </div>

                    <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select id="timezone" name="timezone">
                            <option value="Asia/Kuala_Lumpur" {{ ($settings['timezone'] ?? 'Asia/Kuala_Lumpur') == 'Asia/Kuala_Lumpur' ? 'selected' : '' }}>Asia/Kuala Lumpur (GMT+8)</option>
                            <option value="Asia/Singapore" {{ ($settings['timezone'] ?? '') == 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (GMT+8)</option>
                            <option value="Asia/Bangkok" {{ ($settings['timezone'] ?? '') == 'Asia/Bangkok' ? 'selected' : '' }}>Asia/Bangkok (GMT+7)</option>
                            <option value="UTC" {{ ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC (GMT+0)</option>
                        </select>
                        <small style="color: #666666; font-size: 11px; display: block; margin-top: 4px;">Your timezone for displaying dates and times</small>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="date_format">Date Format</label>
                            <select id="date_format" name="date_format">
                                <option value="d/m/Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY ({{ date('d/m/Y') }})</option>
                                <option value="m/d/Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY ({{ date('m/d/Y') }})</option>
                                <option value="Y-m-d" {{ ($settings['date_format'] ?? 'd/m/Y') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD ({{ date('Y-m-d') }})</option>
                                <option value="d M Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'd M Y' ? 'selected' : '' }}>DD MMM YYYY ({{ date('d M Y') }})</option>
                                <option value="d F Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'd F Y' ? 'selected' : '' }}>DD MMMM YYYY ({{ date('d F Y') }})</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="time_format">Time Format</label>
                            <select id="time_format" name="time_format">
                                <option value="H:i:s" {{ ($settings['time_format'] ?? 'H:i:s') == 'H:i:s' ? 'selected' : '' }}>24-hour ({{ date('H:i:s') }})</option>
                                <option value="h:i A" {{ ($settings['time_format'] ?? 'H:i:s') == 'h:i A' ? 'selected' : '' }}>12-hour ({{ date('h:i A') }})</option>
                                <option value="h:i:s A" {{ ($settings['time_format'] ?? 'H:i:s') == 'h:i:s A' ? 'selected' : '' }}>12-hour with seconds ({{ date('h:i:s A') }})</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="currency">Currency</label>
                            <select id="currency" name="currency">
                                <option value="MYR" {{ ($settings['currency'] ?? 'MYR') == 'MYR' ? 'selected' : '' }}>MYR - Malaysian Ringgit (RM)</option>
                                <option value="USD" {{ ($settings['currency'] ?? 'MYR') == 'USD' ? 'selected' : '' }}>USD - US Dollar ($)</option>
                                <option value="SGD" {{ ($settings['currency'] ?? 'MYR') == 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar (S$)</option>
                                <option value="EUR" {{ ($settings['currency'] ?? 'MYR') == 'EUR' ? 'selected' : '' }}>EUR - Euro (€)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="items_per_page">Items Per Page</label>
                            <select id="items_per_page" name="items_per_page">
                                <option value="10" {{ ($settings['items_per_page'] ?? 10) == 10 ? 'selected' : '' }}>10</option>
                                <option value="25" {{ ($settings['items_per_page'] ?? 10) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ ($settings['items_per_page'] ?? 10) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ ($settings['items_per_page'] ?? 10) == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Reset</button>
                        <button type="submit" class="btn btn-primary">Save Preferences</button>
                    </div>
                </form>
            </div>

            <!-- Security Settings Section -->
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <h4 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #333333;">Security Settings</h4>
                <p style="margin: 0 0 20px 0; font-size: 12px; color: #666666;">Manage your account security preferences.</p>
                
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 16px; font-size: 12px; margin-bottom: 20px;">
                    <div style="color: #666666; font-weight: 600;">Last Password Change:</div>
                    <div style="color: #333333;">{{ $user->updated_at->format('d M Y, h:i A') }}</div>

                    <div style="color: #666666; font-weight: 600;">Account Status:</div>
                    <div style="color: #333333;">
                        @if($user->status == 'Active')
                            <span style="background-color: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Active</span>
                        @else
                            <span style="background-color: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Inactive</span>
                        @endif
                    </div>
                </div>

                <div style="padding: 20px; background-color: #f8f9fa; border-radius: 4px; border-left: 4px solid #007bff;">
                    <div style="display: flex; align-items: start; gap: 12px;">
                        <span class="material-symbols-outlined" style="font-size: 24px; color: #007bff;">info</span>
                        <div>
                            <p style="font-size: 12px; font-weight: 600; color: #333333; margin: 0 0 4px 0;">Security Tips</p>
                            <ul style="font-size: 11px; color: #666666; margin: 0; padding-left: 20px;">
                                <li>Change your password regularly</li>
                                <li>Use a strong password with letters, numbers, and symbols</li>
                                <li>Never share your password with anyone</li>
                                <li>Log out when using shared computers</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
