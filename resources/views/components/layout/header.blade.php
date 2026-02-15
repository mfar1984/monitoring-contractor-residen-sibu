<header class="topbar">
    <div class="topbar-content">
        <div class="topbar-welcome">
            <span>Welcome, {{ Auth::user()->username }}</span>
            <span class="topbar-separator">|</span>
            <span id="live-datetime"></span>
        </div>
        <div class="topbar-actions">
            <!-- Notification Bell with Badge -->
            <div class="topbar-icon notification-icon">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-badge">5</span>
            </div>
            
            <!-- Shortcut Icon -->
            <div class="topbar-icon">
                <span class="material-symbols-outlined">apps</span>
            </div>
            
            <!-- Help Icon -->
            <div class="topbar-icon">
                <span class="material-symbols-outlined">help</span>
            </div>
            
            <!-- User Dropdown -->
            <div class="user-dropdown">
                <button class="user-dropdown-toggle" onclick="toggleUserDropdown(event)">
                    <span class="material-symbols-outlined user-icon">account_circle</span>
                    <span class="user-email">{{ Auth::user()->email }}</span>
                    <span class="material-symbols-outlined dropdown-arrow">expand_more</span>
                </button>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <a href="#" class="dropdown-item">
                        <span class="material-symbols-outlined">person</span>
                        Profile
                    </a>
                    <a href="#" class="dropdown-item">
                        <span class="material-symbols-outlined">settings</span>
                        Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item logout-item">
                            <span class="material-symbols-outlined">logout</span>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
function updateDateTime() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    };
    const dateTimeString = now.toLocaleString('en-US', options);
    document.getElementById('live-datetime').textContent = dateTimeString;
}

updateDateTime();
setInterval(updateDateTime, 1000);

function toggleUserDropdown(event) {
    event.stopPropagation();
    const menu = document.getElementById('userDropdownMenu');
    menu.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('userDropdownMenu');
    if (menu && menu.classList.contains('show')) {
        menu.classList.remove('show');
    }
});
</script>
