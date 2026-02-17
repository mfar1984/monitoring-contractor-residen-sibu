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
                            @foreach($languages as $language)
                                <option value="{{ $language->code }}" {{ ($settings['locale'] ?? 'en') == $language->code ? 'selected' : '' }}>
                                    {{ $language->name }}
                                </option>
                            @endforeach
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

            <!-- Language Management Section -->
            <div style="background: white; padding: 24px; border-radius: 8px; border: 1px solid #e0e0e0; margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 4px 0;">Manage Languages</h3>
                        <p style="color: #666; font-size: 12px; margin: 0;">Add or remove custom languages for translation</p>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('addLanguageModal').style.display='flex'">
                        Add New Language
                    </button>
                </div>

                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e0e0e0;">
                            <th style="text-align: left; padding: 12px; font-size: 12px; font-weight: 600; color: #666;">Language Code</th>
                            <th style="text-align: left; padding: 12px; font-size: 12px; font-weight: 600; color: #666;">Language Name</th>
                            <th style="text-align: left; padding: 12px; font-size: 12px; font-weight: 600; color: #666;">Type</th>
                            <th style="text-align: left; padding: 12px; font-size: 12px; font-weight: 600; color: #666;">Status</th>
                            <th style="text-align: center; padding: 12px; font-size: 12px; font-weight: 600; color: #666;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($languages as $language)
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                            <td style="padding: 12px; font-size: 12px;">{{ $language->code }}</td>
                            <td style="padding: 12px; font-size: 12px;">{{ $language->name }}</td>
                            <td style="padding: 12px; font-size: 12px;">
                                @if($language->is_default)
                                    <span style="background-color: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Default</span>
                                @else
                                    <span style="background-color: #f5f5f5; color: #666; padding: 4px 8px; border-radius: 4px; font-size: 11px;">Custom</span>
                                @endif
                            </td>
                            <td style="padding: 12px; font-size: 12px;">
                                <span style="background-color: {{ $language->status == 'Active' ? '#d4edda' : '#f8d7da' }}; color: {{ $language->status == 'Active' ? '#155724' : '#721c24' }}; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                    {{ $language->status }}
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center;">
                                @if(!$language->is_default)
                                    <form method="POST" action="{{ route('pages.general.localization.language.delete', $language->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this language?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #dc3545; cursor: pointer; font-size: 12px; text-decoration: underline;">
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <span style="color: #999; font-size: 12px;">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Language Modal -->
    <div id="addLanguageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 24px; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="font-size: 18px; font-weight: 600; margin: 0 0 16px 0;">Add New Language</h3>
            
            <form method="POST" action="{{ route('pages.general.localization.language.add') }}">
                @csrf
                <div class="form-group">
                    <label for="language_code">Language Code <span style="color: #dc3545;">*</span></label>
                    <input type="text" id="language_code" name="code" required placeholder="e.g., fr, de, ja" maxlength="10" style="text-transform: lowercase;">
                    <small style="color: #666; font-size: 11px;">Use 2-3 letter ISO language code (e.g., fr for French, de for German)</small>
                </div>

                <div class="form-group">
                    <label for="language_name">Language Name <span style="color: #dc3545;">*</span></label>
                    <input type="text" id="language_name" name="name" required placeholder="e.g., Français, Deutsch, 日本語">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addLanguageModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Language</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Close modal when clicking outside
    document.getElementById('addLanguageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
    </script>
@endsection
