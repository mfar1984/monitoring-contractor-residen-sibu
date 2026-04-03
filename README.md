# Laravel Project Management & Monitoring System

A comprehensive project management and monitoring system built for Residen Sibu, Sarawak, Malaysia. This system implements a structured workflow for managing government projects from initial proposal through approval, execution, and change management, with multi-year budget tracking and category-based multi-tenancy for data isolation.

## 🎯 System Overview

The system manages the complete lifecycle of government projects through three main stages:

1. **Pre-Project** - Initial project proposals with data collection and two-level approval
2. **Project** - Approved projects transferred from pre-projects for execution
3. **NOC (Notice of Change)** - Change requests for approved projects with approval workflow

## 🏗️ System Architecture

### Category-Based Multi-Tenancy

The system implements a unique **Category-Based Multi-Tenancy** architecture where data isolation is achieved through user category assignments rather than traditional tenant_id columns. Each user belongs to ONE organizational category, and data access is automatically filtered based on that category.

#### User Categories

**1. RESIDEN (Administrator)**
- **Access Level:** Full access to ALL data across the system
- **Special Privileges:**
  - Exclusive access to System Settings
  - Can be assigned as Pre-Project Approvers
  - Can be assigned as NOC First/Second Approvers
  - Can transfer approved Pre-Projects to Projects
- **Database Field:** `users.residen_category_id` → `residen_categories.id`

**2. AGENCY (Government Agencies)**
- **Access Level:** Isolated by agency (DID, JKR, JBAB, Sarawak Waterboard, etc.)
- **Capabilities:**
  - View and manage Pre-Projects for their agency
  - View and manage Projects for their agency
  - View NOCs containing their agency's projects
- **Restrictions:** Cannot access System Settings or other agencies' data
- **Database Field:** `users.agency_category_id` → `agency_categories.id`

**3. PARLIAMENT (Member of Parliament)**
- **Access Level:** Isolated by parliamentary constituency
- **Capabilities:**
  - Create and manage Pre-Projects for their parliament
  - Create NOCs for their parliament's projects
  - View projects for their parliament
- **Restrictions:** Cannot access System Settings or other parliaments' data
- **Database Field:** `users.parliament_id` → `parliaments.id`

**4. DUN (State Assembly Member)**
- **Access Level:** Isolated by DUN (State Assembly) constituency
- **Capabilities:**
  - Create and manage Pre-Projects for their DUN
  - Create NOCs for their DUN's projects
  - View projects for their DUN
- **Restrictions:** Cannot access System Settings or other DUNs' data
- **Database Field:** `users.dun_id` → `duns.id`

**5. CONTRACTOR (Contractor Companies)**
- **Access Level:** Isolated by contractor company (Future Implementation)
- **Capabilities:** View-only access to projects assigned to their company
- **Restrictions:** Cannot create or edit Pre-Projects/Projects
- **Database Field:** `users.contractor_category_id` → `contractor_categories.id`

## 📋 Project Workflow

### Stage 1: Pre-Project Creation

**Who can create:** Parliament/DUN users

**Process:**
1. User creates Pre-Project with basic information
2. System auto-assigns `parliament_id` or `dun_id` based on logged-in user
3. Initial status: `Active`
4. Data completeness is tracked (required fields must be 100% complete for submission)

**Data Completeness Tracking:**
- System calculates completeness percentage based on required fields
- Visual indicators: Green (81-100%), Yellow (51-80%), Red (0-50%)
- Required fields include: project scope, category, implementation period, division, district, land title status, implementing agency, implementation method, project ownership

### Stage 2: Submit to EPU

**Who can submit:** Parliament/DUN users (project owner)

**Requirements:**
- Data completeness must be 100%
- All required fields must be filled
- Status must be `Active`

**Process:**
1. User clicks "Submit to EPU" button
2. System validates data completeness
3. If complete: Status changes to `Submitted to EPU`
4. If incomplete: Show error message with missing fields

### Stage 3: Two-Level Approval

**Who can approve:** Residen users assigned as approvers in Application Settings

**Approval Flow:**
```
Active (Initial)
    ↓ (User submits to EPU)
Submitted to EPU
    ↓ (First Approver reviews)
Pending First Approval
    ↓ (First Approver approves)
Pending Second Approval
    ↓ (Second Approver approves)
Approved (Final)
```

**Rejection Flow:**
```
Any Pending Status
    ↓ (Any Approver rejects)
Rejected
```

**Approval Settings:**
- First Approver: Configured in System Settings → Application Settings
- Second Approver: Configured in System Settings → Application Settings
- Both approvers must be Residen users

### Stage 4: Transfer to Project

**Who can transfer:** Residen users (Admin)

**Requirements:**
- Pre-Project status must be `Approved`
- Pre-Project must not already be transferred

**Process:**
1. Admin selects approved pre-projects to transfer
2. System generates unique `project_number` (format: PROJ/YYYY/###)
3. System copies all data from pre_project to project
4. Pre-Project status changes to `Transferred`
5. Project status set to `Active`

### Stage 5: NOC (Notice of Change)

**Who can create:** Parliament/DUN users

**Purpose:** Request changes to approved projects

**Process:**
1. User creates NOC document with unique NOC number (format: NOC/YYYY/###)
2. User imports existing projects or adds new project entries
3. User specifies changes:
   - Original Project Name → New Project Name (optional)
   - Original Cost → New Cost (optional)
   - Original Agency → New Agency (optional)
   - NOC Note (reason for change) - required
4. User submits NOC for approval
5. Two-level approval process (same as Pre-Project)
6. When approved, changes are applied to projects

**NOC Status Flow:**
```
Draft
    ↓ (User submits)
Pending First Approval
    ↓ (First Approver approves)
Pending Second Approval
    ↓ (Second Approver approves)
Approved (Final)
```

## 💰 Multi-Year Budget Tracking

### Budget Allocation System

The system implements comprehensive multi-year budget allocation and tracking for Parliament and DUN constituencies.

**Budget Tables:**
- `parliament_budgets` - Budget allocations per Parliament per fiscal year
- `dun_budgets` - Budget allocations per DUN per fiscal year

**Budget Structure:**
- Each constituency can have different budget allocations for different fiscal years
- Unique constraint: One budget per constituency per year
- Cascade delete: Deleting constituency deletes all its budgets

**Example:**
```
Parliament: Sibu
- 2024: RM 5,000,000.00
- 2025: RM 5,500,000.00
- 2026: RM 6,000,000.00
```

### Budget Tracking in Pre-Projects

**Cost Components:**
- `actual_project_cost` - Main project cost
- `consultation_cost` - Consultation fees
- `lss_inspection_cost` - LSS inspection cost
- `sst` - Sales and Service Tax
- `others_cost` - Other miscellaneous costs
- `total_cost` - Sum of all cost components
- `original_project_cost` - Original budget allocation

**Budget Monitoring:**
- Real-time budget calculations
- Visual indicators for budget status (green = within budget, red = over budget)
- Budget warnings (not blocking - user can proceed with justification)
- Budget difference tracking (remaining/exceeded)

### Budget Tracking in NOCs

**NOC Budget Structure:**
- `kos_asal` - Original cost (from imported project)
- `kos_baru` - New cost (if changed)

**Budget Calculation Logic:**
1. **Import Project:** Budget impact = None (just tracking)
2. **Change Cost:** Budget impact = Difference between kos_baru and kos_asal
3. **Cancel Project:** Budget impact = Budget freed (kos_asal returned to pool)

**Real-Time Budget Summary:**
- Total Original Budget
- Total New Budget
- Budget Difference (remaining/exceeded)
- Visual indicator (purple gradient box, turns red if over budget)

## 🏢 UPKJ Contractor Management

### UPKJ Registration System

**Features:**
- Multiple UPKJ registration records per contractor
- Classification system: Class-Head-Subhead-Letter-Roman
- Certificate number and Bumiputera validity tracking
- Legacy data migration support

**Classification Format:**
- Class: A, B, C, D, E, F, G
- Head: I, II, III, IV, V, VI, VII, VIII, IX, X
- Subhead: 1-99
- Letter: (a), (b), (c), etc.
- Roman: (i), (ii), (iii), etc.

**Example Classifications:**
- E-II-1(a)
- E-II-1(b)
- B-I-5(c)(ii)

**Legacy Data Migration:**
- Command: `php artisan upkj:migrate-legacy`
- Parses complex legacy formats
- Handles multiple classes and heads
- Supports multi-line subhead format
- Success rate: 86.6% (363 out of 419 contractors)

## 🛠️ Technology Stack

### Backend
- **Framework:** Laravel 12.x
- **PHP Version:** ^8.2
- **Database:** MySQL
- **Authentication:** Laravel built-in authentication
- **Excel Import/Export:** maatwebsite/excel ^3.1

### Frontend
- **Template Engine:** Blade
- **CSS:** Custom component-based CSS
- **JavaScript:** Vanilla JavaScript
- **Icons:** Material Symbols

### Development Tools
- **Package Manager:** Composer
- **Testing:** PHPUnit ^11.5.3
- **Code Style:** Laravel Pint ^1.24
- **Development Server:** Laravel Sail ^1.41

## 📦 Installation

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL
- Node.js & NPM

### Setup Instructions

1. **Clone the repository**
```bash
git clone <repository-url>
cd <project-directory>
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment configuration**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

5. **Run migrations**
```bash
php artisan migrate
```

6. **Seed master data** (optional)
```bash
php artisan db:seed
```

7. **Build assets**
```bash
npm run build
```

8. **Start development server**
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## 🗄️ Database Structure

### Core Tables

**Users & Categories:**
- `users` - User accounts with category assignments
- `residen_categories` - Administrator categories
- `agency_categories` - Government agency categories
- `parliaments` - Parliamentary constituencies
- `duns` - DUN (State Assembly) constituencies
- `contractor_categories` - Contractor company categories

**Project Management:**
- `pre_projects` - Pre-project proposals
- `projects` - Approved projects
- `nocs` - Notice of Change documents
- `noc_project` - Pivot table for NOC-Project relationships

**Budget Tracking:**
- `parliament_budgets` - Parliament budget allocations per year
- `dun_budgets` - DUN budget allocations per year

**Master Data:**
- `project_categories` - Project category types
- `divisions` - Administrative divisions
- `districts` - Administrative districts
- `land_title_statuses` - Land title status types
- `implementation_methods` - Implementation method types
- `project_ownerships` - Project ownership types
- `noc_notes` - NOC change reason types
- `status_masters` - Status definitions

**UPKJ Contractor:**
- `contractor_upkj_records` - UPKJ registration records
- `upkj_classifications` - UPKJ classification master data

**System:**
- `integration_settings` - System settings and configurations
- `languages` - Language settings

### Key Relationships

**Pre-Project Relationships:**
- Belongs to Parliament
- Belongs to DUN (via `dun_basic_id`)
- Belongs to Agency Category
- Belongs to Project Category
- Has one Project (when transferred)

**Project Relationships:**
- Belongs to Pre-Project
- Belongs to Parliament
- Belongs to DUN (via `dun_basic_id`)
- Belongs to Agency Category
- Belongs to many NOCs (through pivot table)

**NOC Relationships:**
- Belongs to Parliament
- Belongs to DUN (via `dun_id`)
- Belongs to many Projects (through pivot table)
- Has First Approver (User)
- Has Second Approver (User)

## 🔑 Key Features

### 1. Category-Based Data Isolation
- Automatic data filtering based on user category
- Residen users see all data
- Agency/Parliament/DUN users see only their organization's data
- Enforced at controller level for security

### 2. Two-Level Approval Workflow
- Configurable first and second approvers
- Approval history tracking
- Rejection with remarks
- Status-based access control

### 3. Data Completeness Tracking
- Real-time completeness percentage calculation
- Visual indicators (color-coded badges)
- Required field validation before submission
- Missing field identification

### 4. Multi-Year Budget Management
- Budget allocation per constituency per fiscal year
- Real-time budget tracking and calculations
- Budget warnings (not blocking)
- Budget change management through NOCs

### 5. UPKJ Contractor Management
- Multiple UPKJ registrations per contractor
- Complex classification system
- Legacy data migration support
- Duplicate classification prevention

### 6. Comprehensive Audit Trail
- Created by / Updated by tracking
- Approval timestamps and remarks
- Submission timestamps
- Transfer timestamps

### 7. System Settings Management
- Application settings (approvers, general config)
- Integration settings (Email, SMS, Webhook, API, Weather)
- Translation management
- Localization settings
- Maintenance mode

## 🔒 Security Features

### Authentication & Authorization
- Laravel built-in authentication
- Category-based access control
- Role-based permissions
- System Settings restricted to Residen users only

### Data Security
- Automatic encryption for sensitive settings (API keys, passwords)
- CSRF protection on all forms
- SQL injection prevention through Eloquent ORM
- XSS protection through Blade templating

### Password Security
- Bcrypt/Argon2 hashing
- Automatic salt generation
- Secure session management

## 📱 User Interface

### Design Principles
- Clean and professional design
- Consistent component-based styling
- Responsive layout
- Material Symbols icons
- Color-coded status indicators

### Reusable Components
- Data Table with search and pagination
- Form inputs with validation
- Status badges
- Action buttons
- Tab navigation
- Modal dialogs

### Color Scheme
- Primary Blue: #007bff
- Success Green: #28a745
- Danger Red: #dc3545
- Warning Yellow: #ffc107
- Text: #333333
- Light Text: #666666
- Border: #e0e0e0
- Background: #ffffff

## 🧪 Testing

### Run Tests
```bash
php artisan test
```

### Test Coverage
- Feature tests for main workflows
- Unit tests for complex logic
- Database tests for relationships
- Authorization tests for access control

## 📝 Development Guidelines

### Code Standards
- Follow Laravel best practices
- Use Blade components for reusability
- Keep controllers thin, use services for business logic
- Validate all inputs
- Use route model binding where applicable
- Always use Eloquent relationships instead of manual joins

### Database Safety Rules
**NEVER run these commands without explicit permission:**
- `php artisan db:wipe`
- `php artisan migrate:fresh`
- `DROP DATABASE`
- `TRUNCATE TABLE`

**Safe operations:**
- `php artisan migrate` - Run new migrations only
- `php artisan migrate:rollback --step=1` - Rollback last migration

### Git Workflow
**Excluded from repository:**
- `/.kiro` folder
- `/.vscode` folder
- `/.well-known` folder
- `/*.md` files (except README.md)

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests
5. Submit a pull request

### Code Review Process
- All changes must pass tests
- Follow existing code style
- Update documentation if needed
- Get approval from maintainers

## 📄 License

This project is proprietary software developed for Residen Sibu, Sarawak, Malaysia.

## 👥 Credits

**Developed by:** KF Legacy Resources
**Contact:** faizan@kflegacyresources.com
**Git Username:** mfar1984

## 📞 Support

For technical support or questions, please contact:
- Email: faizan@kflegacyresources.com
- System Administrator: Residen Sibu IT Department

## 🔄 Version History

### Current Version
- Laravel 12.x
- PHP 8.2+
- Multi-year budget tracking
- UPKJ contractor management
- Category-based multi-tenancy
- Two-level approval workflow

---

**Note:** This system is designed specifically for Residen Sibu's project management needs and implements Sarawak government project workflow requirements.
