# SHARES System - Technology Stack Inventory

## Overview
**Project:** Smart Health Assessment & Resource System (SHAReS)
**Type:** Full-stack web application with AI integration
**Primary Purpose:** BMI Malnutrition Monitoring with AI-powered meal planning

---

## Backend Stack

### Framework & Language
- **PHP:** ^8.2
- **Laravel:** ^12.0 (latest version)
- **Laravel Tinker:** ^2.10.1 (CLI tool for interactive Shell)

### Key Backend Libraries

3. **Development & Testing** (require-dev)
   - Laravel Pail: ^1.2.2 - Real-time log viewing
   - Laravel Pint: ^1.13 - Code formatter/linter
   - Laravel Sail: ^1.41 - Docker dev environment
   - PHPUnit: ^11.5.3 - Testing framework
   - Faker: ^1.23 - Test data generation
   - Mockery: ^1.6 - Mocking library

---

## Frontend Stack

### Build Tools & Package Management
- **Node.js:** >=21.0.0
- **npm:** >=10.0.0
- **Vite:** ^7.0.4 - Modern build tool
- **Tailwind CSS:** ^4.1.16 - Utility-first CSS framework
- **@tailwindcss/vite:** ^4.0.0 - Tailwind integration for Vite
- **laravel-vite-plugin:** ^2.0.0 - Laravel ↔ Vite integration

### Frontend Frameworks & Libraries
1. **Alpine.js:** ^3.15.1 - Lightweight JavaScript framework for interactivity
2. **Axios:** ^1.11.0 - HTTP client for API requests (modern fetch wrapper)

### Development Dependencies
- **Concurrently:** ^9.0.1 - Run multiple npm scripts simultaneously

---

## CDN Resources (Loaded via CDN)

### CSS & Icons
- **Bootstrap:** 5.3.0 (via CDN: jsdelivr.net)
  - `/dist/css/bootstrap.min.css`
  
- **Font Awesome:** 6.4.0 (via CDN: cloudflare.com)
  - `/css/all.min.css`
  - Usage: `fas`, `fab` icon classes throughout parent views

### JavaScript Libraries
- **jQuery:** 3.7.1 (via CDN: code.jquery.com)
  - Used for legacy script support and some interactive components

- **Bootstrap JS Bundle:** 5.3.0 (via CDN: jsdelivr.net)
  - `/dist/js/bootstrap.bundle.min.js`

- **SweetAlert2:** 11.x (via CDN: jsdelivr.net)
  - `/dist/sweetalert2.min.css` (CSS)
  - Used for elegant modal dialogs and alerts

---

## Custom Local Assets

### CSS Files (Parent Section - All Updated to #4DA84E Theme)
1. **profile.css** - Parent profile page
2. **parent-assessments.css** - Assessment history/modals
3. **children.css** - Children management
4. **link-child.css** - QR code child linking
5. **meal-plan-pdf.css** - PDF export styling
6. **meal-plans.css** - Meal plan generation
7. **parent-dashboard.css** - Main dashboard
8. **parent-profile.css** - Personal profile
9. **view-meal-plans.css** - Saved meal plans browser

### JavaScript Files
- **dashboard.js** - Main dashboard interactions
- **dashboard-modal.js** - Modal management
- **modal-cleanup.js** - Modal cleanup utilities

---

## Data & ML Components

### Python Modules (Separate from main app)
Located in `/LLM/` and `/RandomForest/` directories

1. **LLM Directory** - AI/LLM integration
   - `nutrition_ai.py` - AI nutrition advisor
   - `nutrition_chain.py` - LLM chain for nutrition planning
   - `feeding_program_chain.py` - Feeding program logic
   - `fastapi_app.py` - FastAPI server for AI endpoints
   - Supporting: `data_manager.py`, `db.py`, `embedding_utils.py`

2. **RandomForest Directory** - Malnutrition prediction
   - `malnutrition_model.py` - ML model for malnutrition detection
   - `personalized_treatment_planner.py` - Treatment planning
   - `api_server.py` - REST API for RF model
   - `data_manager.py` - Data handling

---

## Custom Configuration

### Laravel Config Files
Located in `/config/`:
- `app.php` - Application settings
- `auth.php` - Authentication configuration
- `cache.php` - Caching driver (likely Redis/File)
- `dashboard.php` - Custom dashboard config
- `database.php` - Database connections
- `dompdf.php` - PDF generation settings
- `filesystems.php` - Storage configuration
- `logging.php` - Log channels
- `mail.php` - Email configuration
- `navigation.php` - Sidebar navigation
- `patient.php` - Patient-related config
- `queue.php` - Job queue configuration
- `services.php` - Third-party services
- `session.php` - Session configuration

---

## Architecture Highlights

### CSS Architecture
- **Primary Framework:** Custom vanilla CSS with CSS custom properties (`:root` variables)
- **Design System:** Unified #4DA84E green color theme
- **Responsive Breakpoints:** Mobile-first with breakpoints at 768px, 992px, 1024px, 1200px, 1536px, 1600px
- **Layout:** Heavy use of Flexbox and CSS Grid

### Component Libraries
- Bootstrap utilities + custom components
- Font Awesome icons
- SweetAlert2 for modal dialogs

### State Management
- Server-side (Laravel sessions + blade templates)
- Client-side (Alpine.js for lightweight interactivity)

### API Communication
- Axios for AJAX requests
- CSRF protection via meta tag injection
- RESTful endpoints built with Laravel routing

---

## Development Commands

```bash
# Development server
npm run dev

# Production build
npm run build

# Run Laravel dev server + all services
composer dev
# Includes: PHP server, Queue worker, Log viewer, Vite dev server

# Run queued jobs
npm run queue

# Clear logs
npm run logs

# Fresh installation with sample data
composer fresh-install

# Reset caches
composer reset

# Database refresh with seed data
composer fresh

# Run tests
composer test
```

---

## Notable Integrations

### External Services
- **Email:** Configured in `mail.php` (likely SMTP or Mailgun)
- **PDF Generation:** DomPDF for meal plan exports

### Security Features
- CSRF token protection (meta tag approach)
- Session timeout with warning (5-min inactivity threshold)
- Database query logging capability
- Role-based access control (Admin, Nutritionist, Parent)

### Data Persistence
- **Database:** Configured in `database.php` (likely MySQL/SQLite)
- **Storage:** Public uploads directory + Laravel storage filesystem
- **Caching:** Configured caching driver for performance

---

## Performance Optimizations Detected

1. **Link Preload/Prefetch:**
   - Preconnect to: cdn.jsdelivr.net, cdnjs.cloudflare.com
   - DNS prefetch to: code.jquery.com
   - Asset preloading for critical images

2. **Vite Build Tool:**
   - Code splitting
   - Hot module replacement (HMR) in dev
   - Optimized production bundles

3. **CSS Optimization:**
   - Tailwind CSS with purge (production)
   - Custom property cascading for theming

4. **Asset Versioning:**
   - Cache busting via `filemtime()` for CSS/JS files

---

## Environment Requirements

- **Node.js:** 21+
- **npm:** 10+
- **PHP:** 8.2+
- **MySQL/SQLite:** For data persistence
- **Optional:** Python 3.8+ (for AI/ML modules)

---

## Recent Tech Decisions

1. **Vite over Laravel Mix:** Modern, faster build tool
2. **Tailwind CSS 4:** Latest version with improved performance
3. **Alpine.js:** Lightweight alternative to Vue/React for this use case
4. **Laravel 12:** Latest framework features and security patches
5. **Bootstrap 5.3 + Custom CSS:** Hybrid approach (framework baseline + customization)
6. **SweetAlert2:** Better UX for modals vs Bootstrap modals

---

## Recommendations for Future Updates

| Technology | Current | Consideration |
|-----------|---------|---|
| Bootstrap | 5.3.0 | Consider removing if migrating fully to Tailwind CSS |
| jQuery | 3.7.1 | Phase out - use native DOM APIs or Alpine.js |
| Font Awesome | 6.4.0 | Keep (good maintenance, lightweight) |
| Laravel | 12.0 | Follow LTS releases (currently tracking bleeding edge) |
| Tailwind CSS | 4.1.16 | Stable and current |
| Node.js | 21+ | Stay within LTS when possible for production |

---

## File Structure Summary

```
Root
├── app/                    → Laravel application code
├── config/                 → Configuration files
├── database/              → Migrations, factories, seeders
├── LLM/                   → Python AI/LLM modules
├── RandomForest/          → Python ML model modules
├── public/
│   ├── css/
│   │   └── parent/        → 9 parent section CSS files (#4DA84E themed)
│   ├── js/                → Custom JavaScript
│   ├── uploads/           → User uploads
│   └── img/               → Images & assets
├── resources/
│   ├── css/               → Tailwind + Laravel Vite CSS
│   ├── js/                → Alpine.js + Vite JS
│   └── views/             → Blade templates
├── routes/                → API & web routing
├── storage/               → Logs, cache, uploads
├── tests/                 → PHPUnit tests
├── vendor/                → Composer dependencies
├── node_modules/          → npm dependencies
├── package.json           → Frontend dependencies
└── composer.json          → Backend dependencies
```

---

**Generated:** Current Session
**Status:** Color standardization (#4DA84E) ✅ COMPLETE | Tech audit ✅ COMPLETE
