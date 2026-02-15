@props(['active' => 'residen'])

<div class="tabs-header">
    <a href="{{ route('pages.master-data.residen') }}" class="tab-button {{ $active === 'residen' ? 'active' : '' }}">Residen</a>
    <a href="{{ route('pages.master-data.agency') }}" class="tab-button {{ $active === 'agency' ? 'active' : '' }}">Agency</a>
    <a href="{{ route('pages.master-data.parliaments') }}" class="tab-button {{ $active === 'parliaments' ? 'active' : '' }}">Parliament</a>
    <a href="{{ route('pages.master-data.duns') }}" class="tab-button {{ $active === 'duns' ? 'active' : '' }}">DUN</a>
    <a href="{{ route('pages.master-data.contractor') }}" class="tab-button {{ $active === 'contractor' ? 'active' : '' }}">Contractor</a>
    <a href="{{ route('pages.master-data.status') }}" class="tab-button {{ $active === 'status' ? 'active' : '' }}">Status</a>
    <a href="{{ route('pages.master-data.project-category') }}" class="tab-button {{ $active === 'project-category' ? 'active' : '' }}">Project Category</a>
    <a href="{{ route('pages.master-data.division') }}" class="tab-button {{ $active === 'division' ? 'active' : '' }}">Division</a>
    <a href="{{ route('pages.master-data.district') }}" class="tab-button {{ $active === 'district' ? 'active' : '' }}">District</a>
    <a href="{{ route('pages.master-data.land-title-status') }}" class="tab-button {{ $active === 'land-title-status' ? 'active' : '' }}">Land Title Status</a>
    <a href="{{ route('pages.master-data.project-ownership') }}" class="tab-button {{ $active === 'project-ownership' ? 'active' : '' }}">Project Ownership</a>
    <a href="{{ route('pages.master-data.implementation-method') }}" class="tab-button {{ $active === 'implementation-method' ? 'active' : '' }}">Implementation Method</a>
</div>
