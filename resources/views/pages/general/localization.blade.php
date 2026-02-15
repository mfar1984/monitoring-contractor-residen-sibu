@extends('layouts.app')

@section('title', 'General Settings - Localization - Monitoring System')

@section('breadcrumb')
    <span class="material-symbols-outlined breadcrumb-icon">home</span>
    <span class="breadcrumb-separator">›</span>
    <span>System Settings</span>
    <span class="breadcrumb-separator">›</span>
    <span>General</span>
    <span class="breadcrumb-separator">›</span>
    <span>Localization</span>
@endsection

@section('content')
    <div class="tabs-container">
        <x-general-tabs active="localization" />
        
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
                    <h3>Localization Settings</h3>
                    <p class="content-description">Configure language, timezone, and date format settings.</p>
                </div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0;">
                <form method="POST" action="{{ route('pages.general.localization.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="locale">Default Language <span style="color: #dc3545;">*</span></label>
                        <select id="locale" name="locale" required>
                            <option value="en" {{ ($settings['locale'] ?? 'en') == 'en' ? 'selected' : '' }}>English</option>
                            <option value="ms" {{ ($settings['locale'] ?? '') == 'ms' ? 'selected' : '' }}>Bahasa Melayu</option>
                            <option value="zh" {{ ($settings['locale'] ?? '') == 'zh' ? 'selected' : '' }}>中文 (Chinese)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="timezone">Timezone <span style="color: #dc3545;">*</span></label>
                        <select id="timezone" name="timezone" required>
                            <option value="UTC" {{ ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="Asia/Kuala_Lumpur" {{ ($settings['timezone'] ?? 'Asia/Kuala_Lumpur') == 'Asia/Kuala_Lumpur' ? 'selected' : '' }}>Asia/Kuala Lumpur (GMT+8)</option>
                            <option value="Asia/Singapore" {{ ($settings['timezone'] ?? '') == 'Asia/Singapore' ? 'selected' : '' }}>Asia/Singapore (GMT+8)</option>
                            <option value="Asia/Bangkok" {{ ($settings['timezone'] ?? '') == 'Asia/Bangkok' ? 'selected' : '' }}>Asia/Bangkok (GMT+7)</option>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="date_format">Date Format</label>
                            <select id="date_format" name="date_format">
                                <option value="Y-m-d" {{ ($settings['date_format'] ?? '') == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD (2026-02-15)</option>
                                <option value="d/m/Y" {{ ($settings['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY (15/02/2026)</option>
                                <option value="d-m-Y" {{ ($settings['date_format'] ?? '') == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY (15-02-2026)</option>
                                <option value="m/d/Y" {{ ($settings['date_format'] ?? '') == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY (02/15/2026)</option>
                                <option value="d M Y" {{ ($settings['date_format'] ?? '') == 'd M Y' ? 'selected' : '' }}>DD MMM YYYY (15 Feb 2026)</option>
                                <option value="d F Y" {{ ($settings['date_format'] ?? '') == 'd F Y' ? 'selected' : '' }}>DD MMMM YYYY (15 February 2026)</option>
                                <option value="d-M-Y" {{ ($settings['date_format'] ?? '') == 'd-M-Y' ? 'selected' : '' }}>DD-MMM-YYYY (15-Feb-2026)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="time_format">Time Format</label>
                            <select id="time_format" name="time_format">
                                <option value="H:i:s" {{ ($settings['time_format'] ?? 'H:i:s') == 'H:i:s' ? 'selected' : '' }}>24-hour (14:30:00)</option>
                                <option value="h:i:s A" {{ ($settings['time_format'] ?? '') == 'h:i:s A' ? 'selected' : '' }}>12-hour (02:30:00 PM)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="currency">Currency</label>
                        <select id="currency" name="currency">
                            <option value="MYR" {{ ($settings['currency'] ?? 'MYR') == 'MYR' ? 'selected' : '' }}>MYR - Malaysian Ringgit (RM)</option>
                            <option value="USD" {{ ($settings['currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD - US Dollar ($)</option>
                            <option value="SGD" {{ ($settings['currency'] ?? '') == 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar (S$)</option>
                            <option value="EUR" {{ ($settings['currency'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR - Euro (€)</option>
                        </select>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                        <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Reset</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
