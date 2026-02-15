<nav class="sidebar-nav">
    <ul class="menu-list">
        <!-- Overview menu item -->
        <li class="menu-item">
            <a href="{{ route('pages.overview') }}" class="menu-link">
                <span class="material-symbols-outlined menu-icon">bar_chart</span>
                <span>{{ \App\Helpers\TranslationHelper::trans('overview', 'Overview') }}</span>
            </a>
        </li>
        
        <!-- Project expandable menu -->
        <li class="menu-item expandable">
            <a href="#" class="menu-link" onclick="toggleSubmenu(event)">
                <span class="material-symbols-outlined menu-icon">folder</span>
                <span>{{ \App\Helpers\TranslationHelper::trans('project', 'Project') }}</span>
                <span class="material-symbols-outlined expand-icon">chevron_right</span>
            </a>
            <ul class="submenu">
                <li class="submenu-item">
                    <a href="{{ route('pages.pre-project') }}" class="submenu-link">
                        {{ \App\Helpers\TranslationHelper::trans('pre_project', 'Pre Project') }}
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('pages.project') }}" class="submenu-link">
                        {{ \App\Helpers\TranslationHelper::trans('project', 'Project') }}
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('pages.contractor-analysis') }}" class="submenu-link">
                        {{ \App\Helpers\TranslationHelper::trans('contractor_analysis', 'Contractor Analysis') }}
                    </a>
                </li>
            </ul>
        </li>
        
        <!-- Separator line -->
        <li class="menu-separator"></li>
        
        <!-- System Settings expandable menu -->
        <li class="menu-item expandable">
            <a href="#" class="menu-link" onclick="toggleSubmenu(event)">
                <span class="material-symbols-outlined menu-icon">settings</span>
                <span>{{ \App\Helpers\TranslationHelper::trans('system_settings', 'System Settings') }}</span>
                <span class="material-symbols-outlined expand-icon">chevron_right</span>
            </a>
            <ul class="submenu">
                <li class="submenu-item">
                    <a href="{{ route('pages.general') }}" class="submenu-link">
                        {{ \App\Helpers\TranslationHelper::trans('general', 'General') }}
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('pages.master-data') }}" class="submenu-link">
                        {{ \App\Helpers\TranslationHelper::trans('master_data', 'Master Data') }}
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('pages.group-roles') }}" class="submenu-link">
                        {{ \App\Helpers\TranslationHelper::trans('group_roles', 'Group Roles') }}
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('pages.users-id') }}" class="submenu-link">
                        {{ \App\Helpers\TranslationHelper::trans('users_id', 'Users ID') }}
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('pages.integrations') }}" class="submenu-link">
                        {{ \App\Helpers\TranslationHelper::trans('integrations', 'Integrations') }}
                    </a>
                </li>
                <li class="submenu-item">
                    <a href="{{ route('pages.activity-log') }}" class="submenu-link">
                        {{ \App\Helpers\TranslationHelper::trans('activity_log', 'Activity Log') }}
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</nav>

<script>
    function toggleSubmenu(event) {
        event.preventDefault();
        const menuItem = event.currentTarget.parentElement;
        const expandIcon = event.currentTarget.querySelector('.expand-icon');
        
        menuItem.classList.toggle('expanded');
        
        // Toggle icon between chevron_right and expand_more
        if (menuItem.classList.contains('expanded')) {
            expandIcon.textContent = 'expand_more';
        } else {
            expandIcon.textContent = 'chevron_right';
        }
    }
</script>
