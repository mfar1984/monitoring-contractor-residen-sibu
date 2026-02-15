@props(['active' => 'system'])

<div class="tabs-header">
    <a href="{{ route('pages.general.system') }}" class="tab-button {{ $active === 'system' ? 'active' : '' }}">System Information</a>
    <a href="{{ route('pages.general.application') }}" class="tab-button {{ $active === 'application' ? 'active' : '' }}">Application Settings</a>
    <a href="{{ route('pages.general.localization') }}" class="tab-button {{ $active === 'localization' ? 'active' : '' }}">Localization</a>
    <a href="{{ route('pages.general.maintenance') }}" class="tab-button {{ $active === 'maintenance' ? 'active' : '' }}">Maintenance</a>
</div>
