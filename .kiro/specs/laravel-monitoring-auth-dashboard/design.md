# Design Document: Laravel Monitoring Authentication Dashboard

## Overview

This system is a Laravel 12.x application that provides database-based authentication with a structured dashboard. The system uses MySQL for data storage, Argon2id for password hashing, and Blade templates for view rendering. The design emphasizes clean code organization with clear component separation and a hierarchical navigation menu structure.

## Seni Bina

### Architecture Pattern

The system follows Laravel's MVC (Model-View-Controller) pattern:

- **Model**: Manages data logic and database interactions
- **View**: Blade templates for UI rendering
- **Controller**: Handles business logic and request flow

### Layer Structure

```
┌─────────────────────────────────────┐
│     View Layer (Blade)              │
│  - Main layout with components      │
│  - Login form                       │
│  - Dashboard components             │
│  - Navigation menu                  │
│  - Under construction pages         │
└─────────────────────────────────────┘
              ↕
┌─────────────────────────────────────┐
│    Controller Layer                 │
│  - AuthController                   │
│  - DashboardController              │
│  - PageController                   │
└─────────────────────────────────────┘
              ↕
┌─────────────────────────────────────┐
│    Middleware Layer                 │
│  - Authenticate                     │
│  - RedirectIfAuthenticated          │
└─────────────────────────────────────┘
              ↕
┌─────────────────────────────────────┐
│    Model Layer                      │
│  - User Model                       │
└─────────────────────────────────────┘
              ↕
┌─────────────────────────────────────┐
│    Database Layer                   │
│  - MySQL (monitoring)               │
└─────────────────────────────────────┘
```

### Folder Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   └── LoginController.php
│   │   ├── Dashboard/
│   │   │   └── DashboardController.php
│   │   └── Pages/
│   │       └── PageController.php
│   └── Middleware/
│       └── Authenticate.php
├── Models/
│   └── User.php
└── View/
    └── Components/
        ├── Layout/
        │   ├── Header.php
        │   ├── Sidebar.php
        │   └── Footer.php
        ├── Dashboard/
        │   └── MainContent.php
        └── Pages/
            └── UnderConstruction.php

resources/
├── views/
│   ├── components/
│   │   ├── layout/
│   │   │   ├── header.blade.php
│   │   │   ├── sidebar.blade.php
│   │   │   └── footer.blade.php
│   │   ├── dashboard/
│   │   │   └── main-content.blade.php
│   │   └── pages/
│   │       └── under-construction.blade.php
│   ├── layouts/
│   │   ├── app.blade.php
│   │   └── guest.blade.php
│   ├── auth/
│   │   └── login.blade.php
│   ├── dashboard/
│   │   └── index.blade.php
│   └── pages/
│       ├── overview.blade.php
│       ├── general.blade.php
│       ├── master-data.blade.php
│       ├── group-roles.blade.php
│       ├── users-id.blade.php
│       ├── integrations.blade.php
│       └── activity-log.blade.php
└── css/
    └── app.css

database/
├── migrations/
│   └── 2024_create_users_table.php
└── seeders/
    └── UserSeeder.php

.kiro/
└── steering/
    ├── coding-standards.md
    └── component-structure.md
```

## Components and Interfaces

### 1. User Model

**Responsibility**: Represents user entity and handles authentication

**Attributes**:
- `id`: Integer (primary key)
- `username`: String (unique)
- `password`: String (hashed with Argon2id)
- `created_at`: Timestamp
- `updated_at`: Timestamp

**Methods**:
```php
class User extends Authenticatable
{
    // Define hashing algorithm
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
    
    // Configure Argon2id hashing
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($user) {
            if ($user->password) {
                $user->password = Hash::make($user->password, [
                    'driver' => 'argon2id',
                ]);
            }
        });
    }
}
```

### 2. LoginController

**Responsibility**: Handles user authentication process

**Methods**:

```php
class LoginController extends Controller
{
    // Display login form
    public function showLoginForm(): View
    
    // Process login attempt
    public function login(Request $request): RedirectResponse
    {
        // Validate input
        // Attempt authentication
        // If successful: regenerate session, redirect to dashboard
        // If failed: redirect back with error
    }
    
    // Process logout
    public function logout(Request $request): RedirectResponse
    {
        // Log out user
        // Invalidate session
        // Redirect to login
    }
}
```

### 3. DashboardController

**Responsibility**: Handles dashboard display

**Methods**:

```php
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    // Display main dashboard
    public function index(): View
    {
        return view('dashboard.index');
    }
}
```

### 4. PageController

**Responsibility**: Handles navigation to various system pages

**Methods**:

```php
class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    // Display Overview page
    public function overview(): View
    {
        return view('pages.overview');
    }
    
    // Display General settings page
    public function general(): View
    {
        return view('pages.general');
    }
    
    // Display Master Data page
    public function masterData(): View
    {
        return view('pages.master-data');
    }
    
    // Display Group Roles page
    public function groupRoles(): View
    {
        return view('pages.group-roles');
    }
    
    // Display Users Id page
    public function usersId(): View
    {
        return view('pages.users-id');
    }
    
    // Display Integrations page
    public function integrations(): View
    {
        return view('pages.integrations');
    }
    
    // Display Activity Log page
    public function activityLog(): View
    {
        return view('pages.activity-log');
    }
}
```

### 5. Authenticate Middleware

**Responsibility**: Protects routes requiring authentication

**Logic**:
```php
class Authenticate extends Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        return $next($request);
    }
}
```

### 6. Layout Components

#### Header Component

**Responsibility**: Displays topbar with user information

```php
class Header extends Component
{
    public function render(): View
    {
        return view('components.layout.header');
    }
}
```

**Template (header.blade.php)**:
```blade
<header class="topbar">
    <div class="topbar-content">
        <div class="logo">Monitoring System</div>
        <div class="user-info">
            <span>{{ Auth::user()->username }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>
</header>
```

#### Sidebar Component

**Responsibility**: Displays hierarchical navigation menu

```php
class Sidebar extends Component
{
    public function render(): View
    {
        return view('components.layout.sidebar');
    }
}
```

**Template (sidebar.blade.php)**:
```blade
<aside class="sidebar">
    <nav class="sidebar-nav">
        <ul class="menu-list">
            <!-- Overview menu item -->
            <li class="menu-item">
                <a href="{{ route('pages.overview') }}" class="menu-link">
                    Overview
                </a>
            </li>
            
            <!-- Separator line -->
            <li class="menu-separator"></li>
            
            <!-- System Settings expandable menu -->
            <li class="menu-item expandable">
                <a href="#" class="menu-link" onclick="toggleSubmenu(event)">
                    <span>System Settings</span>
                    <span class="expand-icon">▼</span>
                </a>
                <ul class="submenu">
                    <li class="submenu-item">
                        <a href="{{ route('pages.general') }}" class="submenu-link">
                            General
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="{{ route('pages.master-data') }}" class="submenu-link">
                            Master Data
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="{{ route('pages.group-roles') }}" class="submenu-link">
                            Group Roles
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="{{ route('pages.users-id') }}" class="submenu-link">
                            Users Id
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="{{ route('pages.integrations') }}" class="submenu-link">
                            Integrations
                        </a>
                    </li>
                    <li class="submenu-item">
                        <a href="{{ route('pages.activity-log') }}" class="submenu-link">
                            Activity Log
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
            menuItem.classList.toggle('expanded');
        }
    </script>
</aside>
```

#### Footer Component

**Responsibility**: Displays footer at the bottom

```php
class Footer extends Component
{
    public function render(): View
    {
        return view('components.layout.footer');
    }
}
```

**Template (footer.blade.php)**:
```blade
<footer class="footer">
    <div class="footer-content">
        <p>&copy; 2024 Monitoring System. All Rights Reserved.</p>
    </div>
</footer>
```

### 7. Under Construction Component

**Responsibility**: Displays placeholder page for incomplete features

```php
class UnderConstruction extends Component
{
    public string $pageName;
    
    public function __construct(string $pageName = 'This Page')
    {
        $this->pageName = $pageName;
    }
    
    public function render(): View
    {
        return view('components.pages.under-construction');
    }
}
```

**Template (under-construction.blade.php)**:
```blade
<div class="under-construction">
    <div class="under-construction-content">
        <div class="construction-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
        </div>
        <p class="construction-text">Page Under Construction</p>
    </div>
</div>
```

### 8. Page Templates

All navigation pages (Overview, General, Master Data, Group Roles, Users Id, Integrations, Activity Log) use the same structure:

**Example (pages/overview.blade.php)**:
```blade
@extends('layouts.app')

@section('title', 'Overview')

@section('content')
    <x-pages.under-construction pageName="Overview" />
@endSection
```

### 9. Main Layout

**Template (layouts/app.blade.php)**:
```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Monitoring System')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="app-container">
        <x-layout.header />
        
        <div class="main-wrapper">
            <x-layout.sidebar />
            
            <main class="content-area">
                @yield('content')
            </main>
        </div>
        
        <x-layout.footer />
    </div>
</body>
</html>
```

## Data Models

### Users Table

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Constraints**:
- `username` must be unique
- `password` must be hashed using Argon2id
- Both fields are required (NOT NULL)

### Database Configuration

**File: config/database.php**

```php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'monitoring'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', 'root'),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => null,
    ],
],
```

**File: .env**

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitoring
DB_USERNAME=root
DB_PASSWORD=root
```

### Hashing Configuration

**File: config/hashing.php**

```php
return [
    'driver' => 'argon2id',
    
    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
    ],
];
```

## Routes

**File: routes/web.php**

```php
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Pages\PageController;

// Guest routes (no authentication required)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Protected routes (authentication required)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Page routes
    Route::get('/pages/overview', [PageController::class, 'overview'])->name('pages.overview');
    Route::get('/pages/general', [PageController::class, 'general'])->name('pages.general');
    Route::get('/pages/master-data', [PageController::class, 'masterData'])->name('pages.master-data');
    Route::get('/pages/group-roles', [PageController::class, 'groupRoles'])->name('pages.group-roles');
    Route::get('/pages/users-id', [PageController::class, 'usersId'])->name('pages.users-id');
    Route::get('/pages/integrations', [PageController::class, 'integrations'])->name('pages.integrations');
    Route::get('/pages/activity-log', [PageController::class, 'activityLog'])->name('pages.activity-log');
});

// Redirect root to dashboard or login
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});
```

## CSS Styling

**File: resources/css/app.css**

```css
/* Reset and base styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-size: 12px;
    font-family: Arial, sans-serif;
}

/* Layout container */
.app-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Header/Topbar */
.topbar {
    background-color: #2c3e50;
    color: white;
    padding: 15px 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.topbar-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Main wrapper (sidebar + content) */
.main-wrapper {
    display: flex;
    flex: 1;
}

/* Sidebar */
.sidebar {
    width: 250px;
    background-color: #34495e;
    color: white;
    padding: 20px 0;
}

.sidebar-nav .menu-list {
    list-style: none;
}

.sidebar-nav .menu-item {
    padding: 0;
}

.sidebar-nav .menu-link {
    color: white;
    text-decoration: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
}

.sidebar-nav .menu-link:hover {
    background-color: #2c3e50;
}

/* Menu separator */
.menu-separator {
    height: 1px;
    background-color: #2c3e50;
    margin: 10px 0;
}

/* Expandable menu */
.menu-item.expandable .submenu {
    display: none;
    list-style: none;
    background-color: #2c3e50;
}

.menu-item.expandable.expanded .submenu {
    display: block;
}

.menu-item.expandable.expanded .expand-icon {
    transform: rotate(180deg);
}

.expand-icon {
    transition: transform 0.3s ease;
    font-size: 10px;
}

/* Submenu */
.submenu-item {
    padding: 0;
}

.submenu-link {
    color: white;
    text-decoration: none;
    display: block;
    padding: 8px 20px 8px 40px;
}

.submenu-link:hover {
    background-color: #1a252f;
}

/* Content area */
.content-area {
    flex: 1;
    padding: 20px;
    background-color: #ecf0f1;
}

/* Footer */
.footer {
    background-color: #2c3e50;
    color: white;
    padding: 15px 20px;
    text-align: center;
}

/* Login form */
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #ecf0f1;
}

.login-box {
    background: white;
    padding: 30px;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

.btn-primary {
    background-color: #3498db;
    color: white;
}

.btn-primary:hover {
    background-color: #2980b9;
}

.error-message {
    color: #e74c3c;
    margin-bottom: 10px;
}

/* Under Construction Page */
.under-construction {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
    width: 100%;
}

.under-construction-content {
    text-align: center;
}

.construction-icon {
    color: #95a5a6;
    margin-bottom: 20px;
}

.construction-icon svg {
    width: 80px;
    height: 80px;
}

.construction-text {
    font-size: 12px;
    color: #7f8c8d;
}
```



## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: Valid Credentials Authentication

*For any* user with valid credentials (correct username and password), when credentials are submitted through the login form, the system should authenticate the user and redirect to the dashboard page.

**Validates: Requirements 1.1**

### Property 2: Invalid Credentials Rejection

*For any* invalid credentials (non-existent username or incorrect password), when credentials are submitted through the login form, the system should reject authentication, display an error message, and keep the user on the login page.

**Validates: Requirements 1.2**

### Property 3: Argon2id Password Hashing

*For any* user password stored in the database, the password should be hashed using the Argon2id algorithm, and the resulting hash should contain the "$argon2id$" algorithm identifier.

**Validates: Requirements 1.5**

### Property 4: Dashboard Layout Completeness

*For any* dashboard page view, the generated HTML should contain all required layout elements: header (topbar), sidebar (navigation menu), main content area, and footer.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4**

### Property 5: Maximum Font Size Constraint

*For any* element in the application, the font size used should not exceed 12px, whether set directly or through CSS.

**Validates: Requirements 3.1**

### Property 6: Session Creation After Login

*For any* user who successfully logs in, the system should create a valid session for that user, and this session should be usable to access protected pages.

**Validates: Requirements 5.1**

### Property 7: Route Protection with Middleware

*For any* protected dashboard route, when accessed without a valid authentication session, the system should redirect the user to the login page.

**Validates: Requirements 5.2, 5.4**

### Property 8: Session Termination After Logout

*For any* user who logs out, the system should terminate that user's session, and attempts to access protected pages after logout should result in redirection to the login page.

**Validates: Requirements 5.3**

### Property 9: Database Connection Error Handling

*For any* situation where database connection fails, the system should catch the exception and display an appropriate error message to the user or log.

**Validates: Requirements 6.3**

### Property 10: Expandable Menu Interaction

*For any* expandable menu item (System Settings), when clicked, the system should toggle the visibility of its submenu items.

**Validates: Requirements 7.4**

### Property 11: Under Construction Pages for All Menu Items

*For any* System Settings submenu page (General, Master Data, Group Roles, Users Id, Integrations, Activity Log), when navigated to, the system should display the Under Construction component.

**Validates: Requirements 8.2**

### Property 12: English Language for All UI Text

*For any* user interface element, all displayed text should be in English (no non-English characters or words in UI labels, buttons, menu items, and static content).

**Validates: Requirements 9.1, 9.3, 9.4**

### Property 13: English Language for Error Messages

*For any* error condition that displays a message to the user, the error message should be in English.

**Validates: Requirements 9.2**

## Error Handling

### Authentication Errors

**Scenario**: Invalid login credentials
- **Detection**: Laravel validation and authentication attempt fails
- **Response**: Redirect back to login form with error message
- **Message**: "Invalid credentials. Please try again."
- **Logging**: Log failed login attempts with timestamp and username

**Scenario**: Empty form fields
- **Detection**: Laravel validation
- **Response**: Redirect back with validation messages
- **Message**: "Username and password fields are required."

### Database Errors

**Scenario**: Database connection fails
- **Detection**: PDO Exception or QueryException
- **Response**: Display generic error page
- **Message**: "Database connection error. Please contact administrator."
- **Logging**: Log full error details for debugging

**Scenario**: Database query fails
- **Detection**: QueryException
- **Response**: Rollback transaction if any, display error message
- **Message**: "Error processing request. Please try again."
- **Logging**: Log query and parameters for debugging

### Session Errors

**Scenario**: Session expired
- **Detection**: Authentication middleware
- **Response**: Redirect to login page
- **Message**: "Your session has expired. Please log in again."

**Scenario**: Unauthorized access
- **Detection**: Authentication middleware
- **Response**: Redirect to login page
- **Message**: "Please log in to access this page."

### General Error Handling Strategy

1. **Catch All Exceptions**: Use Laravel's global exception handler
2. **Log Errors**: All errors are logged with sufficient context
3. **User-Friendly Messages**: Don't expose technical details to users
4. **Graceful Degradation**: System should remain functional even if certain components fail
5. **Transaction Rollback**: Ensure data integrity by rolling back failed transactions

## Testing Strategy

### Dual Testing Approach

This system will use a combination of unit tests and property-based tests for comprehensive coverage:

- **Unit Tests**: Validate specific examples, edge cases, and error conditions
- **Property-Based Tests**: Validate universal properties across all inputs

Both are complementary and necessary for comprehensive coverage. Unit tests catch concrete bugs, while property tests verify general correctness.

### Property-Based Testing Configuration

**Library**: For Laravel/PHP, we will use **Pest PHP** with the **Pest Property Testing** plugin or **PHPUnit** with **Eris** (property-based testing library for PHP).

**Configuration**:
- Each property test should run a minimum of **100 iterations**
- Each test must be tagged with a comment referencing the design document property
- Tag format: `// Feature: laravel-monitoring-auth-dashboard, Property {number}: {property_text}`

**Example Property-Based Tests**:

```php
use function Pest\property;

// Feature: laravel-monitoring-auth-dashboard, Property 1: Valid Credentials Authentication
test('valid credentials authenticate and redirect to dashboard', function () {
    property()
        ->forAll(
            Generator::user() // Generator for user with valid credentials
        )
        ->runs(100)
        ->then(function ($user) {
            $response = $this->post('/login', [
                'username' => $user->username,
                'password' => 'password', // Original password before hashing
            ]);
            
            $response->assertRedirect('/dashboard');
            $this->assertAuthenticatedAs($user);
        });
});

// Feature: laravel-monitoring-auth-dashboard, Property 2: Invalid Credentials Rejection
test('invalid credentials are rejected with error', function () {
    property()
        ->forAll(
            Generator::invalidCredentials() // Generator for invalid credentials
        )
        ->runs(100)
        ->then(function ($credentials) {
            $response = $this->post('/login', $credentials);
            
            $response->assertRedirect('/login');
            $response->assertSessionHasErrors();
            $this->assertGuest();
        });
});

// Feature: laravel-monitoring-auth-dashboard, Property 3: Argon2id Password Hashing
test('passwords are hashed with argon2id', function () {
    property()
        ->forAll(
            Generator::string() // Generator for random passwords
        )
        ->runs(100)
        ->then(function ($password) {
            $user = User::create([
                'username' => 'testuser_' . uniqid(),
                'password' => $password,
            ]);
            
            // Check that password is hashed with Argon2id
            expect($user->password)->toContain('$argon2id$');
            expect(Hash::check($password, $user->password))->toBeTrue();
        });
});

// Feature: laravel-monitoring-auth-dashboard, Property 11: Under Construction Pages
test('all submenu pages display under construction component', function () {
    property()
        ->forAll(
            Generator::elements([
                'pages.general',
                'pages.master-data',
                'pages.group-roles',
                'pages.users-id',
                'pages.integrations',
                'pages.activity-log'
            ])
        )
        ->runs(100)
        ->then(function ($route) {
            $user = User::factory()->create();
            $this->actingAs($user);
            
            $response = $this->get(route($route));
            
            $response->assertStatus(200);
            $response->assertSee('Page Under Construction');
            $response->assertSee('construction-icon');
        });
});

// Feature: laravel-monitoring-auth-dashboard, Property 12: English Language UI
test('all UI text is in English', function () {
    property()
        ->forAll(
            Generator::elements([
                'login',
                'dashboard',
                'pages.overview',
                'pages.general'
            ])
        )
        ->runs(100)
        ->then(function ($route) {
            if ($route !== 'login') {
                $user = User::factory()->create();
                $this->actingAs($user);
            }
            
            $response = $this->get(route($route));
            $content = $response->getContent();
            
            // Check for common non-English words (Malay in this case)
            $nonEnglishWords = ['Sistem', 'Pemantauan', 'Papan', 'Pemuka', 'Keluar'];
            foreach ($nonEnglishWords as $word) {
                expect($content)->not->toContain($word);
            }
        });
});
```

### Unit Tests

**Unit Test Focus**:
1. **Specific Examples**: Test specific login scenarios with known data
2. **Edge Cases**: Test empty input, very long strings, special characters
3. **Error Conditions**: Test database error handling, session expiration
4. **Component Integration**: Test interactions between controller, model, and middleware

**Example Unit Tests**:

```php
test('login form is displayed', function () {
    $response = $this->get('/login');
    
    $response->assertStatus(200);
    $response->assertViewIs('auth.login');
});

test('empty credentials are rejected', function () {
    $response = $this->post('/login', [
        'username' => '',
        'password' => '',
    ]);
    
    $response->assertSessionHasErrors(['username', 'password']);
});

test('dashboard requires authentication', function () {
    $response = $this->get('/dashboard');
    
    $response->assertRedirect('/login');
});

test('logout terminates session', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->post('/logout');
    
    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('dashboard displays all layout components', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->get('/dashboard');
    
    $response->assertStatus(200);
    $response->assertSee('topbar'); // Header
    $response->assertSee('sidebar'); // Sidebar
    $response->assertSee('content-area'); // Content
    $response->assertSee('footer'); // Footer
});

test('sidebar displays navigation menu structure', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->get('/dashboard');
    
    $response->assertSee('Overview');
    $response->assertSee('System Settings');
    $response->assertSee('General');
    $response->assertSee('Master Data');
    $response->assertSee('Group Roles');
    $response->assertSee('Users Id');
    $response->assertSee('Integrations');
    $response->assertSee('Activity Log');
});

test('under construction component displays correctly', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $response = $this->get(route('pages.overview'));
    
    $response->assertStatus(200);
    $response->assertSee('Page Under Construction');
    $response->assertSee('construction-icon');
});
```

### Test Coverage

**Components to Test**:
1. ✅ LoginController (login, logout)
2. ✅ DashboardController (index)
3. ✅ PageController (all page methods)
4. ✅ User Model (password hashing)
5. ✅ Authenticate Middleware (route protection)
6. ✅ Layout Components (header, sidebar, footer)
7. ✅ Under Construction Component
8. ✅ Login form validation
9. ✅ Session management
10. ✅ Error handling
11. ✅ Navigation menu structure
12. ✅ Language localization

**Coverage Metrics**:
- Target code coverage: 80% minimum
- All critical routes must have tests
- All correctness properties must have property-based tests
- All error cases must have unit tests

### Test Environment

**Test Database Configuration**:
```php
// phpunit.xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

**Test Setup**:
- Use in-memory database (SQLite) for tests
- Run migrations before each test
- Clean up data after each test
- Use factories to generate test data

### Test Automation

**CI/CD Integration**:
- Run all tests on every commit
- Block merge if tests fail
- Run property-based tests with 100 iterations minimum
- Generate code coverage reports

**Test Commands**:
```bash
# Run all tests
php artisan test

# Run tests with coverage
php artisan test --coverage

# Run property-based tests only
php artisan test --filter property

# Run unit tests only
php artisan test --filter unit
```
