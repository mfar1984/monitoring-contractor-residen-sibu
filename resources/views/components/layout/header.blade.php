<header class="topbar">
    <div class="topbar-content">
        <div class="topbar-welcome">
            <span>Welcome, {{ Auth::user()->full_name }}</span>
            <span class="topbar-separator">|</span>
            <span id="live-datetime"></span>
        </div>
        <div class="topbar-actions">
            <!-- Notification Bell with Badge -->
            <div class="topbar-icon-wrapper">
                <button class="topbar-icon notification-icon" onclick="toggleNotificationDropdown(event)">
                    <span class="material-symbols-outlined">notifications</span>
                    <span class="notification-badge">5</span>
                </button>
                <div class="icon-dropdown-menu" id="notificationDropdownMenu">
                    <div class="dropdown-header">
                        <h4>Notifications</h4>
                        <span class="badge-count">5 New</span>
                    </div>
                    <div class="notification-list">
                        <a href="#" class="notification-item unread">
                            <span class="material-symbols-outlined notif-icon">check_circle</span>
                            <div class="notif-content">
                                <p class="notif-title">Pre-Project Approved</p>
                                <p class="notif-text">Your pre-project "Road Upgrade" has been approved</p>
                                <span class="notif-time">5 minutes ago</span>
                            </div>
                        </a>
                        <a href="#" class="notification-item unread">
                            <span class="material-symbols-outlined notif-icon">description</span>
                            <div class="notif-content">
                                <p class="notif-title">NOC Submitted</p>
                                <p class="notif-text">NOC/2026/001 has been submitted for approval</p>
                                <span class="notif-time">1 hour ago</span>
                            </div>
                        </a>
                        <a href="#" class="notification-item unread">
                            <span class="material-symbols-outlined notif-icon">warning</span>
                            <div class="notif-content">
                                <p class="notif-title">Budget Alert</p>
                                <p class="notif-text">Budget allocation for DUN Bawang Assan is running low</p>
                                <span class="notif-time">3 hours ago</span>
                            </div>
                        </a>
                        <a href="#" class="notification-item">
                            <span class="material-symbols-outlined notif-icon">person_add</span>
                            <div class="notif-content">
                                <p class="notif-title">New User Added</p>
                                <p class="notif-text">John Doe has been added to the system</p>
                                <span class="notif-time">Yesterday</span>
                            </div>
                        </a>
                        <a href="#" class="notification-item">
                            <span class="material-symbols-outlined notif-icon">update</span>
                            <div class="notif-content">
                                <p class="notif-title">System Update</p>
                                <p class="notif-text">System maintenance scheduled for tonight</p>
                                <span class="notif-time">2 days ago</span>
                            </div>
                        </a>
                    </div>
                    <div class="dropdown-footer">
                        <a href="#" class="view-all-link">View All Notifications</a>
                    </div>
                </div>
            </div>
            
            <!-- Shortcut Icon -->
            <div class="topbar-icon-wrapper">
                <button class="topbar-icon" onclick="toggleShortcutDropdown(event)">
                    <span class="material-symbols-outlined">apps</span>
                </button>
                <div class="icon-dropdown-menu shortcut-menu" id="shortcutDropdownMenu">
                    <div class="dropdown-header">
                        <h4>Quick Access</h4>
                    </div>
                    <div class="shortcut-grid">
                        <a href="{{ route('pages.overview') }}" class="shortcut-item" style="background-color: #e3f2fd;">
                            <span class="material-symbols-outlined" style="color: #1976d2;">dashboard</span>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('pages.pre-project') }}" class="shortcut-item" style="background-color: #e8f5e9;">
                            <span class="material-symbols-outlined" style="color: #388e3c;">event</span>
                            <span>Pre-Project</span>
                        </a>
                        <a href="{{ route('pages.project') }}" class="shortcut-item" style="background-color: #f3e5f5;">
                            <span class="material-symbols-outlined" style="color: #7b1fa2;">construction</span>
                            <span>Project</span>
                        </a>
                        <a href="{{ route('pages.project.noc') }}" class="shortcut-item" style="background-color: #fff3e0;">
                            <span class="material-symbols-outlined" style="color: #f57c00;">description</span>
                            <span>NOC</span>
                        </a>
                        <a href="{{ route('pages.master-data') }}" class="shortcut-item" style="background-color: #fff9c4;">
                            <span class="material-symbols-outlined" style="color: #f9a825;">database</span>
                            <span>Master Data</span>
                        </a>
                        <a href="{{ route('pages.users-id') }}" class="shortcut-item" style="background-color: #fce4ec;">
                            <span class="material-symbols-outlined" style="color: #c2185b;">group</span>
                            <span>Users</span>
                        </a>
                        <a href="{{ route('pages.contractor-analysis') }}" class="shortcut-item" style="background-color: #e1f5fe;">
                            <span class="material-symbols-outlined" style="color: #0277bd;">business</span>
                            <span>Contractor</span>
                        </a>
                        <a href="{{ route('pages.activity-log') }}" class="shortcut-item" style="background-color: #e0f2f1;">
                            <span class="material-symbols-outlined" style="color: #00695c;">history</span>
                            <span>Activity Log</span>
                        </a>
                        <a href="{{ route('pages.general.system') }}" class="shortcut-item" style="background-color: #eceff1;">
                            <span class="material-symbols-outlined" style="color: #455a64;">settings</span>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Help Icon -->
            <div class="topbar-icon-wrapper">
                <button class="topbar-icon" onclick="toggleHelpDropdown(event)">
                    <span class="material-symbols-outlined">help</span>
                </button>
                <div class="icon-dropdown-menu help-menu" id="helpDropdownMenu">
                    <div class="dropdown-header">
                        <h4>Help & Support</h4>
                    </div>
                    <div class="help-list">
                        <a href="#" class="help-item">
                            <span class="material-symbols-outlined">quiz</span>
                            <span>FAQs</span>
                        </a>
                        <a href="#" class="help-item" onclick="openHelpdeskModal(); return false;">
                            <span class="material-symbols-outlined">support_agent</span>
                            <span>Helpdesk</span>
                        </a>
                        <a href="#" class="help-item">
                            <span class="material-symbols-outlined">new_releases</span>
                            <span>Release Notes</span>
                        </a>
                        <a href="#" class="help-item">
                            <span class="material-symbols-outlined">menu_book</span>
                            <span>User Guide</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- User Dropdown -->
            <div class="user-dropdown">
                <button class="user-dropdown-toggle" onclick="toggleUserDropdown(event)">
                    <span class="material-symbols-outlined user-icon">account_circle</span>
                    <span class="user-email">{{ Auth::user()->email }}</span>
                    <span class="material-symbols-outlined dropdown-arrow">expand_more</span>
                </button>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <a href="{{ route('pages.profile') }}" class="dropdown-item">
                        <span class="material-symbols-outlined">person</span>
                        Profile
                    </a>
                    <a href="{{ route('pages.settings') }}" class="dropdown-item">
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

<!-- Helpdesk Modal -->
<div id="helpdeskModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3 style="margin: 0; font-size: 16px; font-weight: 600; color: #333;">Contact Helpdesk</h3>
            <button type="button" class="modal-close" onclick="closeHelpdeskModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="helpdeskForm">
                <div class="form-group">
                    <label for="helpdesk_subject">Subject <span class="required">*</span></label>
                    <input type="text" id="helpdesk_subject" name="subject" placeholder="Enter subject" required>
                </div>
                
                <div class="form-group">
                    <label for="helpdesk_category">Category <span class="required">*</span></label>
                    <select id="helpdesk_category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="technical">Technical Issue</option>
                        <option value="account">Account Problem</option>
                        <option value="feature">Feature Request</option>
                        <option value="bug">Bug Report</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="helpdesk_message">Message <span class="required">*</span></label>
                    <textarea id="helpdesk_message" name="message" rows="5" placeholder="Describe your issue..." required></textarea>
                </div>
                
                <div style="background-color: #f8f9fa; padding: 12px; border-radius: 4px; margin-bottom: 16px;">
                    <p style="font-size: 11px; color: #666; margin: 0;">
                        <strong>Contact Information:</strong><br>
                        Tel: 084-330202 / 082-318963 / 082-321963<br>
                        Email: khairuni90@sarawak.gov.my
                    </p>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeHelpdeskModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitHelpdesk()">Submit Request</button>
        </div>
    </div>
</div>

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

// Toggle Notification Dropdown
function toggleNotificationDropdown(event) {
    event.stopPropagation();
    closeAllDropdowns();
    const menu = document.getElementById('notificationDropdownMenu');
    menu.classList.toggle('show');
}

// Toggle Shortcut Dropdown
function toggleShortcutDropdown(event) {
    event.stopPropagation();
    closeAllDropdowns();
    const menu = document.getElementById('shortcutDropdownMenu');
    menu.classList.toggle('show');
}

// Toggle Help Dropdown
function toggleHelpDropdown(event) {
    event.stopPropagation();
    closeAllDropdowns();
    const menu = document.getElementById('helpDropdownMenu');
    menu.classList.toggle('show');
}

// Toggle User Dropdown
function toggleUserDropdown(event) {
    event.stopPropagation();
    closeAllDropdowns();
    const menu = document.getElementById('userDropdownMenu');
    menu.classList.toggle('show');
}

// Close all dropdowns
function closeAllDropdowns() {
    const dropdowns = document.querySelectorAll('.icon-dropdown-menu, .user-dropdown-menu');
    dropdowns.forEach(dropdown => {
        dropdown.classList.remove('show');
    });
}

// Helpdesk Modal Functions
function openHelpdeskModal() {
    closeAllDropdowns();
    document.getElementById('helpdeskModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeHelpdeskModal() {
    document.getElementById('helpdeskModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('helpdeskForm').reset();
}

function submitHelpdesk() {
    const form = document.getElementById('helpdeskForm');
    if (form.checkValidity()) {
        // Here you would normally send the data to the server
        alert('Helpdesk request submitted successfully! We will contact you soon.');
        closeHelpdeskModal();
    } else {
        form.reportValidity();
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    closeAllDropdowns();
});

// Close helpdesk modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('helpdeskModal');
    if (event.target === modal) {
        closeHelpdeskModal();
    }
});

// Close helpdesk modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('helpdeskModal');
        if (modal.style.display === 'flex') {
            closeHelpdeskModal();
        }
    }
});
</script>
