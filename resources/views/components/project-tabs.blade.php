@props(['active' => 'project'])

<div class="tabs-header">
    <a href="{{ route('pages.project') }}" class="tab-button {{ $active === 'project' ? 'active' : '' }}">Project</a>
    <a href="{{ route('pages.project.noc') }}" class="tab-button {{ $active === 'noc' ? 'active' : '' }}">NOC</a>
    <a href="{{ route('pages.project-cancel') }}" class="tab-button {{ $active === 'cancel' ? 'active' : '' }}">Project Cancel</a>
</div>
