# The Fibonacci Gallery — Technical Architecture & Developer Maintenance Manual

> **Document Version:** 1.0.0  
> **Target Audience:** Full-Stack Developers, Maintainers, Technical Leads  
> **Repository:** `The-Fibonacci-Gallery`  
> **Domains:**
> - Frontend: `https://the-fibonacci-gallery.com`
> - Backend API: `https://back.the-fibonacci-gallery.com`

---

## Table of Contents
1. [System Overview & High-Level Architecture](#1-system-overview--high-level-architecture)
2. [Technology Stack](#2-technology-stack)
3. [Repository Structure](#3-repository-structure)
4. [Environment Setup & Local Development](#4-environment-setup--local-development)
5. [Environment Variables Reference](#5-environment-variables-reference)
6. [Database Schema & Data Models](#6-database-schema--data-models)
7. [Authentication, Authorization & Security](#7-authentication-authorization--security)
8. [Core Modules & Business Logic](#8-core-modules--business-logic)
   - 8.1 [Course Player & Video Progress](#81-course-player--video-progress)
   - 8.2 [NOWPayments Cryptocurrency Payment Flow](#82-nowpayments-cryptocurrency-payment-flow)
   - 8.3 [Coupons & Dynamic Discount System](#83-coupons--dynamic-discount-system)
   - 8.4 [Certificate Generation & Public Verification](#84-certificate-generation--public-verification)
   - 8.5 [Affiliate & Referral System](#85-affiliate--referral-system)
   - 8.6 [Admin Management Portal](#86-admin-management-portal)
9. [Frontend Design & Development Conventions](#9-frontend-design--development-conventions)
10. [CI/CD Workflow & Deployment Manual](#10-cicd-workflow--deployment-manual)
   - 10.1 [Overview & High-Level Pipeline](#101-overview--high-level-pipeline)
   - 10.2 [Branch Protection Rules](#102-branch-protection-rules)
   - 10.3 [Workflows Architecture (`ci.yml` & `deploy.yml`)](#103-workflows-architecture-ciyml--deployyml)
   - 10.4 [Repository Secrets & Variables](#104-repository-secrets--variables)
   - 10.5 [What the Deploy Does and Does Not Touch](#105-what-the-deploy-does-and-does-not-touch)
   - 10.6 [Daily Developer Workflow](#106-daily-developer-workflow)
   - 10.7 [Database Migrations in Production](#107-database-migrations-in-production)
   - 10.8 [First-Time Setup & Disaster Recovery](#108-first-time-setup--disaster-recovery)
   - 10.9 [Post-Deploy Verification Checklist](#109-post-deploy-verification-checklist)
   - 10.10 [Rollback Procedures](#1010-rollback-procedures)
   - 10.11 [CI/CD Troubleshooting Runbook](#1011-cicd-troubleshooting-runbook)
   - 10.12 [Known Limitations & Hardening Recommendations](#1012-known-limitations--hardening-recommendations)
   - 10.13 [Infrastructure Security Invariants](#1013-infrastructure-security-invariants)
11. [Maintenance, Troubleshooting & Runbook](#11-maintenance-troubleshooting--runbook)

---

## 1. System Overview & High-Level Architecture

**The Fibonacci Gallery** is an institutional-grade trading education platform. It provides specialized trading masterclasses (such as *The Big Five Strategy*, Fibonacci confluence, and capital preservation), on-demand protected video lessons, automated course progress tracking, cryptocurrency checkout via NOWPayments, verifiable completion certificates with PDF & QR-code generation, an affiliate referral engine, and an administrative management panel.

### Architectural Diagram

```
                             +-------------------------------+
                             |       End-User Browser        |
                             |  (Student / Visitor / Admin)  |
                             +---------------+---------------+
                                             |
                                    HTTPS    | (Vite SPA)
                                             v
                      +---------------------------------------------+
                      |               Frontend (SPA)                |
                      |   Vue 3 + Vite + Tailwind CSS v4 + Pinia   |
                      |       TanStack Query v5 + TanStack Form     |
                      +----------------------+----------------------+
                                             |
                              REST API (JSON)| Bearer Token (Passport)
                                             v
                      +---------------------------------------------+
                      |            Backend API (Laravel 12)         |
                      |   PHP 8.2+ | Route / Controller / Service   |
                      +-------+--------------------+----------------+
                              |                    |
            +-----------------+                    +----------------+
            |                                                       |
            v                                                       v
+-----------------------+                               +-----------------------+
|  PostgreSQL Database  |                               | Third-Party Services  |
| - Users & Roles       |                               | - NOWPayments (Crypto)|
| - Courses & Videos    |                               | - SMTP Mail Server    |
| - Orders & Enrollments|                               | - DomPDF & QR Codes   |
| - Certificates        |                               | - TradingView Feeds   |
| - Affiliates & Payouts|                               | - Google Analytics 4  |
+-----------------------+                               +-----------------------+
```

---

## 2. Technology Stack

### Frontend (`/client`)
- **Core Framework:** Vue 3.5+ (Composition API with `<script setup lang="ts">`)
- **Language:** TypeScript 6.0+ (`vue-tsc` strict checking)
- **Build Tool:** Vite 8.0+
- **Styling:** Tailwind CSS v4.2+ (utilizing `@theme` tokens, mobile-first design)
- **UI Components:** Shadcn Vue primitive abstractions based on Radix Vue / Reka UI
- **Server State & Caching:** TanStack Vue Query (`@tanstack/vue-query` v5)
- **Forms & Validation:** TanStack Vue Form (`@tanstack/vue-form` v1) with Zod schema adapter
- **Client State Management:** Pinia v3 & `js-cookie`
- **Video Player:** Plyr v3.8+ with custom skinning and tokenized streaming
- **Icons:** `@phosphor-icons/vue`
- **Notifications:** `vue-sonner` (Toast alerts)
- **SEO & Tracking:** Dynamic meta injection, JSON-LD Organization structured data, GA4 page views

### Backend (`/server`)
- **Core Framework:** Laravel 12.0+
- **Runtime:** PHP 8.2 or PHP 8.3+
- **Database Engine:** PostgreSQL 14+ (`pdo_pgsql`)
- **Authentication & OAuth:** Laravel Passport 13.7+ (Personal Access Tokens & Bearer authentication)
- **Authorization & Roles:** Spatie Laravel Permission 7.4+ (`super_admin`, `admin`, `student`)
- **Payment Processing:** NOWPayments REST API + IPN Webhook with HMAC-SHA512 verification
- **Document Generation:** Barryvdh Laravel DomPDF 3.1+ (PDF generation) and SimpleSoftwareIO / Chillerlan QR Code
- **Mailing:** Laravel Mail via SMTP (TLS/SSL on port 465) with Mailable classes
- **Testing Suite:** Pest PHP 3.8+ and Laravel Pail

---

## 3. Repository Structure

The project is structured as a decoupled monorepo containing both the frontend client and the backend server.

```
The-Fibonacci-Gallery/
├── client/                     # Frontend Vue 3 + TypeScript SPA
│   ├── public/                 # Static public assets (favicons, manifest)
│   ├── src/
│   │   ├── api/                # Axios instance & HTTP client configuration
│   │   ├── assets/             # Brand logos, SVGs, styles (main.css)
│   │   ├── components/         # Reusable UI & Shadcn components
│   │   │   └── ui/             # Dialogs, Buttons, Inputs, Tables, Badges, etc.
│   │   ├── composables/        # Business logic & query/mutation composables
│   │   ├── constants/          # Static constants & site config
│   │   ├── layouts/            # Public Layout, Student Layout, AdminLayout
│   │   ├── lib/                # Utility helpers (cn, formatting)
│   │   ├── pages/              # Fallback / Error views (NotFoundPage.vue)
│   │   ├── router/             # Vue Router definition, auth & admin navigation guards
│   │   ├── schemas/            # Zod validation schemas (login, checkout, admin forms)
│   │   ├── stores/             # Pinia global stores (auth, user state)
│   │   ├── types/              # TypeScript interfaces & types (strictly separated)
│   │   ├── utils/              # Error formatters, token handling, string utils
│   │   └── views/              # Page views (Home, CoursePlayer, Admin pages, etc.)
│   ├── package.json            # Node dependencies and scripts
│   ├── tsconfig.json           # TypeScript configuration
│   └── vite.config.ts          # Vite build and plugin configurations
│
├── server/                     # Backend Laravel 12 REST API
│   ├── app/
│   │   ├── Console/            # Custom Artisan commands
│   │   ├── Http/
│   │   │   ├── Controllers/    # Public & student controllers (Auth, Payment, Player)
│   │   │   │   └── Admin/      # Protected admin controllers (Courses, Orders, Payouts)
│   │   │   ├── Middleware/     # Auth, Role, and Request sanitization middleware
│   │   │   └── Requests/       # FormRequest validation classes
│   │   ├── Mail/               # Mailable classes (CourseCertificateMail, ForgotPasswordMail)
│   │   ├── Models/             # Eloquent models (Course, User, Order, Certificate, etc.)
│   │   ├── Policies/           # Authorization policies
│   │   ├── Providers/          # App, Auth, and Passport service providers
│   │   └── Services/           # Encapsulated domain logic:
│   │       ├── AffiliateService.php
│   │       ├── CertificateService.php
│   │       └── NowPaymentsService.php
│   ├── bootstrap/              # App bootstrap and service registration
│   ├── config/                 # Application config (cors, auth, services, database)
│   ├── database/
│   │   ├── factories/          # Test model factories
│   │   ├── migrations/         # PostgreSQL database migrations
│   │   └── seeders/            # Database seeders (Roles, Initial Admin, Courses)
│   ├── routes/
│   │   ├── api.php             # All REST API endpoints (Public, Protected, Admin)
│   │   └── console.php         # Artisan scheduled tasks
│   ├── storage/                # Uploaded files, generated certificates, logs
│   └── composer.json           # PHP dependencies and autoload settings
│
├── claude.md                   # Core development rules and architectural invariants
└── DEVELOPER_DOCUMENTATION.md  # This document
```

---

## 4. Environment Setup & Local Development

### Prerequisites
- **Node.js:** v20.19.0 or v22.12.0+ (with npm v10+)
- **PHP:** v8.2 or v8.3+ with extensions: `pdo_pgsql`, `mbstring`, `openssl`, `curl`, `gd`, `zip`, `bcmath`
- **Composer:** v2.7+
- **PostgreSQL:** v14 or higher running locally or in Docker
- **Git**

---

### Backend Setup (`/server`)

1. **Navigate to the server directory:**
   ```bash
   cd server
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Configure Environment:**
   ```bash
   cp .env.example .env
   ```
   *Open `.env` and configure your database credentials, NOWPayments API keys, and mail credentials.*

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Run Automated Project Initialization (`php artisan init`):**
   ```bash
   php artisan init
   ```
   > [!TIP]
   > The custom `php artisan init` command handles full backend provisioning in a single automated step:
   > - Runs fresh database migrations (`migrate:fresh`)
   > - Seeds initial database records (`db:seed` for roles, admin user, and course curricula)
   > - Generates Passport OAuth encryption keys (`passport:keys --force`)
   > - Automatically registers personal access OAuth clients for all authentication providers

6. **Create Public Storage Symlink:**
   ```bash
   php artisan storage:link
   ```

7. **Start Local Development Server:**
   ```bash
   php artisan serve --port=8000
   ```

---

### Frontend Setup (`/client`)

1. **Navigate to the client directory:**
   ```bash
   cd client
   ```

2. **Install NPM dependencies:**
   ```bash
   npm install
   ```

3. **Configure Environment:**
   Create `.env` or `.env.local`:
   ```env
   VITE_API_URL=http://127.0.0.1:8000/api
   VITE_GA_MEASUREMENT_ID=
   ```

4. **Run the Vite Development Server:**
   ```bash
   npm run dev
   ```
   *The frontend application will be accessible at `http://localhost:5173`.*

5. **Type Check & Linting:**
   ```bash
   npm run type-check
   npm run format
   ```

---

## 5. Environment Variables Reference

### Backend (`server/.env`)

| Variable | Description | Example / Default |
|---|---|---|
| `APP_NAME` | Name of the platform | `The Fibonacci Gallery` |
| `APP_ENV` | Application environment (`local`, `production`) | `local` |
| `APP_KEY` | 32-character AES encryption key | Generated via `artisan key:generate` |
| `APP_DEBUG` | Verbose debug error pages (keep `false` in prod) | `false` |
| `APP_URL` | Base URL of the backend API | `https://back.the-fibonacci-gallery.com` |
| `FRONTEND_URL` | Base URL of the client SPA (for CORS & links) | `https://the-fibonacci-gallery.com` |
| `DB_CONNECTION` | Database driver | `pgsql` |
| `DB_HOST` | Database host | `127.0.0.1` |
| `DB_PORT` | Database port | `5432` |
| `DB_DATABASE` | PostgreSQL database name | `the_fibonacci_gallery` |
| `DB_USERNAME` | PostgreSQL username | `postgres` |
| `DB_PASSWORD` | PostgreSQL user password | `your_secure_password` |
| `FILESYSTEM_DISK` | Storage disk for thumbnails & certificates | `public` or `local` |
| `MAIL_MAILER` | Mail driver (`smtp`) | `smtp` |
| `MAIL_HOST` | SMTP server host | `mail.the-fibonacci-gallery.com` |
| `MAIL_PORT` | SMTP server port | `465` (SSL) or `587` (TLS) |
| `MAIL_USERNAME` | Authenticated email address | `noreply@the-fibonacci-gallery.com` |
| `MAIL_PASSWORD` | SMTP password | `your_smtp_password` |
| `MAIL_ENCRYPTION` | Encryption type | `ssl` or `tls` |
| `MAIL_FROM_ADDRESS` | Outgoing sender address | `noreply@the-fibonacci-gallery.com` |
| `MAIL_FROM_NAME` | Sender display name | `The Fibonacci Gallery` |
| `NOWPAYMENTS_API_KEY` | API Key provided by NOWPayments dashboard | `YOUR_NOWPAYMENTS_KEY` |
| `NOWPAYMENTS_IPN_SECRET` | Secret key used to verify webhook signatures | `YOUR_IPN_SECRET` |
| `NOWPAYMENTS_API_URL` | API endpoint for NOWPayments | `https://api.nowpayments.io/v1` |

### Frontend (`client/.env`)

| Variable | Description | Example |
|---|---|---|
| `VITE_API_URL` | Root URL for backend REST API endpoints | `https://back.the-fibonacci-gallery.com/api` |
| `VITE_GA_MEASUREMENT_ID` | Google Analytics 4 tracking ID | `G-MS1H3VGWEQ` |

---

## 6. Database Schema & Data Models

The database uses PostgreSQL with relational integrity, foreign key constraints, cascading deletes, and UUIDs for external IDs.

```
+---------------+        1:N       +---------------+        1:N       +-------------------+
|    Course     |----------------->|    Chapter    |----------------->|    CourseVideo    |
+---------------+                  +---------------+                  +-------------------+
        |                                                                       |
        | 1:N                                                                   | 1:N
        v                                                                       v
+---------------+        1:N       +---------------+                  +-------------------+
|  Enrollment   |<-----------------|     User      |----------------->|   VideoProgress   |
+---------------+                  +---------------+                  +-------------------+
        ^                                  |
        | 1:1                              | 1:N
+---------------+                  +---------------+
|     Order     |                  |  Certificate  |
+---------------+                  +---------------+
```

### Key Models Breakdown

1. **`User` (`users`)**:
   - Fields: `id`, `uuid`, `name`, `email`, `password`, `referral_code`, `referred_by_id`, `is_banned`, `timestamps`.
   - Relationships: Has roles via Spatie (`admin`, `super_admin`, `student`), `enrollments()`, `orders()`, `videoProgress()`, `certificates()`, `affiliateCommissions()`.

2. **`Course` (`courses`)**:
   - Fields: `id`, `uuid`, `title`, `slug`, `description`, `price`, `discount_price`, `thumbnail_url`, `difficulty_level`, `is_published`, `order`.
   - Relationships: `hasMany(Chapter::class)`, `hasMany(Enrollment::class)`.

3. **`Chapter` (`chapters`)**:
   - Fields: `id`, `uuid`, `course_id`, `title`, `description`, `order`.
   - Relationships: `belongsTo(Course::class)`, `hasMany(CourseVideo::class)`.

4. **`CourseVideo` (`course_videos`)**:
   - Fields: `id`, `uuid`, `chapter_id`, `title`, `video_url`, `duration_seconds`, `is_free_preview`, `order`.
   - Relationships: `belongsTo(Chapter::class)`, `hasMany(VideoProgress::class)`.

5. **`VideoProgress` (`video_progress`)**:
   - Fields: `id`, `uuid`, `user_id`, `course_video_id`, `watch_time_seconds`, `watch_percentage`, `is_completed`, `completed_at`.
   - Invariant: A video is marked completed once the watch percentage surpasses 90%.

6. **`Order` (`orders`)**:
   - Fields: `id`, `uuid`, `user_id`, `course_id`, `coupon_id`, `amount`, `actually_paid`, `pay_currency`, `pay_address`, `nowpayments_payment_id`, `nowpayments_order_id`, `status` (`waiting`, `confirming`, `confirmed`, `finished`, `failed`, `expired`), `expires_at`, `paid_at`.

7. **`Enrollment` (`enrollments`)**:
   - Fields: `id`, `uuid`, `user_id`, `course_id`, `coupon_id`, `type` (`paid`, `free`, `coupon`, `manual`), `enrolled_at`.

8. **`Coupon` (`coupons`)**:
   - Fields: `id`, `uuid`, `course_id` (nullable for global), `code`, `discount_percentage`, `max_uses`, `times_used`, `expires_at`, `is_active`.

9. **`Certificate` (`certificates`)**:
   - Fields: `id`, `uuid`, `user_id`, `course_id`, `certificate_number` (e.g. `FG-2026-XXXXX`), `pdf_path`, `issued_at`.

10. **`AffiliateCommission` & `AffiliatePayout`**:
    - Manage affiliate earnings (calculated upon paid orders) and student payout withdrawal requests (`pending`, `approved`, `completed`, `rejected`).

> [!IMPORTANT]
> **Database Invariant During Development:**  
> Avoid creating sequential patch migrations during feature development. Modify the original migration file and execute `php artisan init` (or `php artisan migrate:fresh --seed`). In production, generate standard versioned migration files (`php artisan make:migration ...`).

---

## 7. Authentication, Authorization & Security

### Token Architecture
- Authentication uses **Laravel Passport** personal access tokens.
- On `/api/login` or `/api/register`, the server verifies credentials and returns a Bearer token and user payload.
- The client stores the token in an HTTP-accessible cookie (`access_token`) and user metadata in `localStorage.getItem('auth')`.
- All requests created through the centralized Axios client automatically attach:
  ```http
  Authorization: Bearer <access_token>
  ```

### Role-Based Access Control (RBAC)
- Implemented via `spatie/laravel-permission`.
- Defined roles:
  - `super_admin`: Unrestricted administrative rights.
  - `admin`: Curriculum management, order tracking, payout moderation, and user management.
  - `student`: Default role for all self-registered users.
- Protected endpoints use the middleware stack:
  ```php
  Route::middleware(['auth:api', 'role:admin|super_admin'])->prefix('admin')->group(...);
  ```

### Password Reset Lifecycle
1. User requests reset via `/api/forgot-password` with email.
2. Server generates a cryptographically secure random token, stores its hash with a 60-minute expiration in `password_reset_tokens`, and dispatches `ForgotPasswordMail`.
3. Link directs user to `/reset-password?token={token}&email={email}`.
4. Token is validated via `/api/verify-reset-token` before showing the reset form.
5. Password is updated via `/api/reset-password`, invalidating previous tokens.

---

## 8. Core Modules & Business Logic

### 8.1 Course Player & Video Progress
- **Access Verification:** When accessing `/courses/{uuid}/player`, the server verifies an active `Enrollment` for the authenticated user.
- **Signed Playback Tokens:** Video URLs are protected. The player queries `/videos/{uuid}/token` to retrieve a temporary playback authorization token.
- **Heartbeat & Progress Sync:** The Plyr player emits time updates every 5–10 seconds to `/api/progress/{uuid}`:
  - Updates `watch_time_seconds` and `watch_percentage`.
  - When `watch_percentage >= 90%`, marks `is_completed = true` and records `completed_at`.
  - When all videos in all chapters of a course are marked completed, the system triggers certificate creation via `CertificateService`.

### 8.2 NOWPayments Cryptocurrency Payment Flow
NOWPayments provides non-custodial crypto checkout supporting BTC, ETH, USDT (ERC20/TRC20/Solana), and other major tokens.

#### Checkout Sequence
1. Student selects a cryptocurrency on `/checkout/:uuid` and applies any promotional coupon.
2. Frontend calls `/api/payments/initiate/{courseUuid}`:
   - Verifies user is not already enrolled.
   - Calculates the net amount after coupon deduction.
   - **Zero-dollar exemption:** If a 100% coupon is applied, payment is bypassed and enrollment is provisioned immediately.
   - Otherwise, an `Order` record is generated with status `waiting`, and NOWPayments API endpoint `/payment` is invoked.
   - Receives deposit address (`pay_address`), cryptocurrency amount (`pay_amount`), and transaction expiry.
3. Student transfers funds to the provided address.
4. Client polls `/api/payments/status/{orderUuid}` for updates while on the payment page.
5. **Instant Payment Notification (IPN) Webhook:**
   - NOWPayments sends POST requests to `https://back.the-fibonacci-gallery.com/api/payments/webhook`.
   - The payload contains an `x-nowpayments-sig` header.
   - `PaymentController@webhook` verifies the signature using `hash_hmac('sha512', $payload, $secret)`.
   - If verified and status is `finished`:
     - Updates order `status = 'finished'`, sets `paid_at = now()`.
     - Automatically creates an `Enrollment` record (`type = 'paid'`).
     - Triggers affiliate commission via `AffiliateService::processCommissionForOrder($order)`.

### 8.3 Coupons & Dynamic Discount System
- Handled via `CouponController` (user) and `Admin\CouponController` (admin).
- Coupons can be course-specific or platform-wide.
- Support fixed or percentage discounts.
- Validate:
  - Expiration dates (`expires_at > now()`).
  - Active flag (`is_active = true`).
  - Max redemption limit (`times_used < max_uses`).
- Public promotional banner queries `/api/coupons/homepage` to showcase active announcements.

### 8.4 Certificate Generation & Public Verification
1. Upon completing 100% of a course, `CertificateService::generateCertificate($user, $course)` is called.
2. A unique identifier is constructed: `FG-{YEAR}-{RANDOM_HEX}`.
3. A QR code is generated pointing to:
   `https://the-fibonacci-gallery.com/certificates/verify/{certificateNumber}`
4. DomPDF renders a high-resolution, branded PDF certificate and persists it to disk.
5. The system dispatches `CourseCertificateMail` to the student with the PDF attached.
6. Public verification is accessible without authentication at `/certificates/verify/:certificateNumber`, returning the recipient's name, course title, issuance timestamp, and authenticity badge.

### 8.5 Affiliate & Referral System
- Each registered student receives a unique `referral_code`.
- When a new visitor signs up via `?ref={referral_code}`, `referred_by_id` is linked to their profile.
- When the referred user completes a course purchase:
  - `AffiliateService` computes the defined commission percentage (e.g. 10%–20%).
  - An `AffiliateCommission` record is created in `approved` status.
- From the student portal (`/affiliate`), users can view earnings, balance, and submit withdrawal requests with their crypto address.
- Admins inspect, approve, and finalize payouts via `/admin/payouts`.

### 8.6 Admin Management Portal
Located under route prefix `/admin` (accessible only to `admin` and `super_admin` roles):
- **Dashboard:** Revenue metrics, order volume, enrollments, student completion analytics.
- **Courses Management:** Course authoring, thumbnail upload/crop, pricing, publishing toggle.
- **Curriculum Builder:** Chapters and Videos CRUD with drag-and-drop ordering (`/chapters/reorder` and `/videos/reorder`).
- **Orders & Enrollments:** Transaction auditing, payment statuses, and manual user enrollment.
- **Student Moderation:** Student directory with instant ban/unban controls.
- **Coupons Management:** Creation, usage limit tracking, and instant toggle.
- **Affiliate Payouts:** Multi-step payout verification (Approve -> Complete / Reject).

---

## 9. Frontend Design & Development Conventions

All future frontend modifications must strictly adhere to the following architecture rules defined in `claude.md`:

1. **Path Aliasing:**  
   Always use `@/` alias (resolves to `/client/src`). Relative paths (`./` or `../`) are forbidden.
2. **Separation of Concerns:**  
   - `.vue` files must contain only layout templates and minimal UI bindings.
   - All state, data fetching, mutations, and side-effects must reside in composables inside `src/composables/`.
3. **TypeScript Types:**  
   - Never declare inline types in components or composables.
   - Dedicated type definitions must live in `src/types/{feature}Type.ts`.
   - Check `@/types/CommonTypes` first before creating new types.
4. **Form Handling & Validation:**  
   - Never write ad-hoc validation or use native HTML validation attributes (`required`, `pattern`).
   - Use **Zod** schemas defined in `src/schemas/{form}Schemas.ts`.
   - Connect forms using **TanStack Vue Form** with the Zod adapter.
5. **Data Fetching:**  
   - Use TanStack Query (`useQuery`, `useMutation`) for all server communication.
   - Avoid manual `ref` server data caching.
6. **Styling & Theming:**  
   - Tailwind CSS v4 only. Never write inline styles or hardcoded hex colors.
   - Use theme classes defined in `main.css` (`@theme`).
   - All UI layouts must be designed **mobile-first** and scaled up using `sm:`, `md:`, `lg:`.
7. **Modals & Dialogs:**  
   - Never use browser native popups (`window.alert`, `window.confirm`). Always use Shadcn Vue `AlertDialog` or `Dialog`.

---

## 10. CI/CD Workflow & Deployment Manual

> **Developer Documentation.** Last updated 5 November 2026.

### 10.1 Overview & High-Level Pipeline

Every change reaches production through a pull request. Merging into `main` triggers a deploy that builds the Vue frontend on a GitHub runner, uploads the frontend and the Laravel backend to cPanel over **one single SSH connection**, then executes Composer, database migrations, and a cache clear on the server.

```
feature branch ──► pull request ──► CI build check ──► merge to main ──► Deploy workflow ──► cPanel
```

| Component | Detail |
|---|---|
| **Repository** | One private monorepo on GitHub |
| **Frontend** | `client/` (Vue 3 + TypeScript, built with Vite) |
| **Backend** | `server/` (Laravel 12 REST API) |
| **Hosting** | cPanel shared hosting, frontend and backend on the same account |
| **Workflows** | `.github/workflows/ci.yml` and `.github/workflows/deploy.yml` |

---

### 10.2 Branch Protection Rules

The ruleset named **Protect main** (`Settings` -> `Rules` -> `Rulesets`) targets the default branch:
- **Require a pull request before merging:** Required approvals are `0` while one person works on the project. Raise it to `1` when a second reviewer exists.
- **Require status checks to pass before merging:** The `build` status check (produced by the CI workflow) must pass before merging.
- **The bypass list is empty:** Nobody, including repository administrators, can push straight to `main`.

> [!NOTE]
> This was verified by attempting a direct push, which GitHub rejected with:  
> `GH013: Repository rule violations found` — *Changes must be made through a pull request.*

- If the **Merge** button is available while the CI check is still running, the `build` status check is missing from the ruleset. Add it back.
- For an emergency fix, open a pull request that reverts the problematic change. **Do not add a bypass.**

---

### 10.3 Workflows Architecture (`ci.yml` & `deploy.yml`)

#### 1. Continuous Integration (`ci.yml`)
- **Trigger:** `pull_request` targeting `main`.
- **Job:** `build` runs on `ubuntu-latest`:
  1. Checks out repository.
  2. Sets up Node.js 20 with `npm` caching from `client/package-lock.json`.
  3. Executes `npm ci` and `npm run build` inside `client/`.
- Reads `VITE_API_URL` and `VITE_GA_MEASUREMENT_ID` from repository variables.
- Uses **no secrets, no SSH, and uploads nothing** — an invalid or broken pull request cannot touch the server.
- Proves that the frontend compiles cleanly. (Backend testing can be added as a subsequent check).

#### 2. Automated Deployment (`deploy.yml`)
- **Triggers:** `push` to `main` and `workflow_dispatch` (manual run).
- **Branch Guard:** Has the guard `if: github.ref == 'refs/heads/main'`, ensuring the manual *Run workflow* button cannot deploy an unmerged branch.
- **Single Job:** All deployment steps execute sequentially inside one job so that every step shares a single multiplexed SSH connection.

| Step | Action | Description |
|---|---|---|
| **1** | **Checkout** | Fetches the repository source code. |
| **2** | **Set up Node** | Sets up Node.js 20 with npm caching from `client/package-lock.json`. |
| **3** | **Build frontend** | Runs `npm ci` then `npm run build` in `client/` using production `VITE_` variables. |
| **4** | **Set up SSH** | Writes the deploy key to `~/.ssh/deploy_key` and creates `~/.ssh/config` with host alias `cpanel`. |
| **5** | **Upload frontend build** | Executes `rsync -avz --delete`, excluding `.htaccess`, from `client/dist/` to `$FRONTEND_PATH`. |
| **6** | **Upload backend files** | Executes `rsync -avz` (without delete), excluding `.env`, `storage`, `vendor`, `node_modules`, and `.git`, from `server/` to `$BACKEND_PATH`. |
| **7** | **Update backend** | Over SSH inside the backend directory: runs `php ~/composer.phar install --no-dev --optimize-autoloader --no-interaction`, then `php artisan migrate --force`, and `php artisan optimize:clear`. |

#### Why There is Only One SSH Connection
The host firewall strictly limits how many new SSH handshakes it accepts in a short time frame. Opening multiple concurrent connections triggers rate limits and timeouts. The workflow configures SSH with:
```ssh-config
ControlMaster auto
ControlPath ~/.ssh/control-%C
ControlPersist 10m
```
Every subsequent `rsync` and `ssh` call reuses the existing socket via the `cpanel` alias. **Any new step added in the future must use `cpanel` and must not open a separate connection.**

---

### 10.4 Repository Secrets & Variables

Configured at repository level under **Settings -> Secrets and variables -> Actions**.

| Name | Type | Purpose |
|---|---|---|
| `SSH_HOST` | Secret | Server hostname or IP address |
| `SSH_USER` | Secret | cPanel account username |
| `SSH_PORT` | Secret | SSH port (usually `22`) |
| `SSH_PRIVATE_KEY` | Secret | Full private deploy key (including `BEGIN` and `END` headers) |
| `BACKEND_PATH` | Secret | Absolute server path to the Laravel root folder |
| `FRONTEND_PATH` | Secret | Absolute server path to the frontend public document root |
| `VITE_API_URL` | Variable | Backend API base URL baked into the client bundle at build time |
| `VITE_GA_MEASUREMENT_ID` | Variable | Google Analytics 4 Measurement ID baked into client bundle |

> [!IMPORTANT]
> **Configuration Rules:**
> - Path secrets must be **absolute** and contain **no trailing whitespace or newlines**. A stray newline once broke the rsync target directory.
> - `VITE_` variables end up in the client-side JavaScript bundle. **Never store secret keys or tokens in `VITE_` variables.**
> - Vite bakes these values at build time. Changing a repository variable has no effect until a new deploy workflow runs.

---

### 10.5 What the Deploy Does and Does Not Touch

| Item | Deployment Behavior |
|---|---|
| **Server `.env`** | **Never overwritten.** Maintained independently on the server. |
| **`storage/`** | **Never touched.** User uploads, logs, and analytics credentials are preserved. |
| **`vendor/`** | **Not uploaded.** Built and optimized directly on the server by Composer. |
| **Deleted Backend Files** | **Not deleted automatically.** The backend rsync runs without `--delete`. Remove obsolete files manually in cPanel. |
| **Frontend Assets** | **Mirrored with `--delete`.** Old hashed JavaScript/CSS chunks are automatically cleaned up. |
| **Frontend `.htaccess`** | **Preserved on server.** Excluded from rsync so routing rules are never overwritten. Edit by hand. |
| **Database** | **Migrations run automatically.** Executed via `php artisan migrate --force`. |

> [!NOTE]
> Backend exclude patterns have no leading slash (e.g. `storage`, `vendor`), meaning they match at any directory depth. Any folder named `vendor` or `storage` anywhere under `server/` is automatically skipped.

---

### 10.6 Daily Developer Workflow

```bash
# 1. Start from up-to-date main
git checkout main
git pull

# 2. Create a feature branch
git checkout -b feature/short-description

# 3. Implement code changes following conventions
git add .
git commit -m "Describe the change"
git push -u origin feature/short-description
```

1. Open a **Pull Request** into `main` on GitHub.
2. Wait for the **`CI / build`** check to turn green.
3. Merge the pull request.
4. Navigate to the **Actions** tab and monitor the **Deploy to cPanel** workflow run.
5. Perform post-deploy verification (Section 10.9), then delete the feature branch.

> [!WARNING]
> Never attempt to commit directly to `main`. Pushes will be rejected by GitHub branch protection rules.

---

### 10.7 Database Migrations in Production

- **New Migration Files Only:** Every schema modification must be a new timestamped migration file (`php artisan make:migration ...`). Never edit an existing migration that has already run in production.
- **Never run destructive commands:** Running `migrate:fresh` or `migrate:refresh` on production is strictly forbidden as it wipes all live data.
- **Take a backup first:** Always take a database snapshot in cPanel before running migrations that drop, rename, or transform existing columns.
- **Deployment Execution Order:** Backend files are uploaded *before* migrations execute. Ensure migrations are backward-compatible (e.g. add new nullable columns first, then clean up deprecated columns in a subsequent deployment).
- **Reverting Code vs Database:** Reverting code via Git does not revert database schema changes. Write a corrective rollback migration or restore from backup.

---

### 10.8 First-Time Setup & Disaster Recovery

If deploying to a fresh server or restoring from disaster:

1. **Generate SSH Keypair:**
   ```bash
   ssh-keygen -t ed25519 -C "github-actions-deploy" -f deploy_key
   ```
2. **Authorize in cPanel:**  
   Go to **Security -> SSH Access -> Import Key**. Paste the public key (`deploy_key.pub`), then click **Manage** and **Authorize**.
3. **Verify Connectivity:**
   ```bash
   ssh -i deploy_key -p PORT USER@HOST
   ```
4. **Configure GitHub Secrets & Variables:**  
   Add the 6 secrets and 2 variables defined in Section 10.4. Paste the complete private key into `SSH_PRIVATE_KEY`.
5. **Secure Local Keys:** Delete the local key files from your machine and never commit them to version control.
6. **Ensure Composer is available:** Install Composer on the server home path as `~/composer.phar`.
7. **Configure Branch Ruleset:** Enable the *Protect main* ruleset with required CI status checks.

---

### 10.9 Post-Deploy Verification Checklist

- [ ] GitHub Actions workflow completed with a green checkmark.
- [ ] Hard refresh the browser (`Ctrl + Shift + R` or `Cmd + Shift + R`) to bypass browser cache.
- [ ] Test a deep route (e.g., `/explore` or `/courses/{uuid}`) to verify SPA rewrite rules in `.htaccess`.
- [ ] Check API data fetching on public and student dashboard pages.
- [ ] Log in as administrator and confirm admin panel loads correctly.
- [ ] Inspect `server/storage/logs/laravel.log` on the server for any unhandled exceptions.

---

### 10.10 Rollback Procedures

- **Application Code Rollback:**  
  Open a pull request reverting the problematic merge commit, merge it into `main`. The deploy pipeline will automatically build and restore the previous codebase.
- **Manual Pipeline Trigger:**  
  Go to **Actions -> Deploy to cPanel -> Run workflow** on `main`.
- **Database Rollback:**  
  Database state is not rolled back automatically. Either restore from your cPanel database backup or apply a targeted corrective migration.

---

### 10.11 CI/CD Troubleshooting Runbook

| Symptom | Likely Cause | Solution |
|---|---|---|
| **Connection timed out during rsync or ssh** | Host firewall rate-limiting or blocking GitHub runner IP addresses | Ensure the single multiplexed SSH connection config is intact. Request hosting support to allow GitHub Actions CIDR blocks or whitelist in firewall (CSF, Imunify360, cPHulk). |
| **Permission denied (`publickey`)** | Key not authorized or private key secret malformed | Authorize the public key in cPanel SSH Access. Re-paste the entire private key including `-----BEGIN...` and `-----END...` lines. |
| **rsync fails with bad remote path** | Spaces or stray newlines in `BACKEND_PATH` or `FRONTEND_PATH` | Edit the secret in GitHub Settings and remove any trailing newline or spaces. |
| **npm ci fails on peer dependency error** | Zod peer version conflict with TanStack form adapter | Ensure `.npmrc` with `legacy-peer-deps=true` is present in the repository root or client directory. |
| **`composer: command not found`** | Composer binary is not in server system `$PATH` | The deploy script expects `php ~/composer.phar`. Install or link Composer in the user home directory. |
| **404 error when reloading deep SPA URLs** | Missing or corrupted `.htaccess` in frontend document root | Verify `.htaccess` exists on server with proper rewrite rules to `index.html`. |
| **Old assets / Old API URL after deploy** | Browser cache, or `VITE_` variable updated after build | Perform a hard browser refresh. Re-trigger the deployment workflow after modifying any `VITE_` variable. |
| **Merge button clickable while CI is running** | `build` job is not set as a required check | Add the `build` status check under Ruleset settings. |
| **Direct push rejected with `GH013`** | Branch protection working as designed | Expected behavior. Open a pull request from a feature branch. |

---

### 10.12 Known Limitations & Hardening Recommendations

1. **Frontend Rsync Excludes:**  
   The frontend `--delete` flag removes unrecognized files. Ensure `.well-known` (AutoSSL certificate renewals) and `cgi-bin` are excluded:
   ```bash
   rsync -avz --delete --exclude='.htaccess' --exclude='.well-known' --exclude='cgi-bin' client/dist/ cpanel:$FRONTEND_PATH/
   ```
2. **Concurrency Control:**  
   Prevent overlapping deploys from rapid consecutive merges by adding concurrency management to `deploy.yml`:
   ```yaml
   concurrency:
     group: deploy-production
     cancel-in-progress: false
   ```
3. **Backend CI Test Suite:**  
   Expand `ci.yml` to include a backend verification job running `composer install` and `php artisan test` / `pest`.
4. **Maintenance Mode Window:**  
   For heavy migrations, wrap Step 7 with `php artisan down` and `php artisan up` to prevent end-user traffic during database schema updates.
5. **Orphaned Backend Files:**  
   Because backend rsync runs without `--delete`, deleting a file from Git will leave the old file on the server until manually removed.

---

### 10.13 Infrastructure Security Invariants

- **Never commit `.env` files, GA4 service account credentials (`storage/app/analytics/`), or private SSH keys.**
- The deploy SSH key has shell access to the cPanel user account. Ensure mandatory 2FA on all GitHub administrator accounts.
- Carefully audit changes to `.github/workflows/` — merged workflows execute with full access to repository secrets.
- **Key Compromise Response:** If the deploy key is ever leaked or suspected compromised, immediately delete it from cPanel SSH Access, generate a new keypair, update `SSH_PRIVATE_KEY` in GitHub Secrets, and authorize the new public key.
- Periodically rotate database, application, and mail credentials.

---

## 11. Maintenance, Troubleshooting & Runbook

### Common Issues & Solutions

#### 1. "Unauthenticated" / 401 Errors on Protected Routes
- **Cause:** Missing or unreadable Passport encryption keys, or incorrect auth middleware guard (`auth:api` must be used; do not use `auth:sanctum`).
- **Solution:**
  ```bash
  cd server
  php artisan passport:keys
  chmod 600 storage/oauth-*.key
  ```
- Verify `access_token` cookie is present in browser requests and properly attached as `Authorization: Bearer <token>`.

#### 2. NOWPayments IPN Webhook Not Updating Orders
- **Symptom:** Order remains in `waiting` status after customer sends payment.
- **Checklist:**
  - Verify `NOWPAYMENTS_IPN_SECRET` in `.env` matches the IPN secret key configured in the NOWPayments merchant account.
  - Inspect `/server/storage/logs/laravel.log` for signature mismatch warnings.
  - Test connectivity and ensure the `/api/payments/webhook` route is publicly reachable without CSRF or auth middleware.
  - The fallback synchronization (`PaymentController@checkStatus`) will reconcile status when the user views the order status page.

#### 3. Emails Not Sending (Password Reset / Certificates)
- **Note:** All transactional emails (password reset links and completion certificates) are sent synchronously via `Mail::to(...)->send(...)`.
- **Checklist:**
  - Verify SMTP credentials in `server/.env` (`MAIL_HOST`, `MAIL_PORT=465` or `587`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION=ssl`).
  - Ensure the mail host subdomain (e.g. `mail.the-fibonacci-gallery.com`) is set to **DNS Only (Grey Cloud)** in Cloudflare rather than Proxied, as Cloudflare proxies only HTTP/HTTPS traffic and blocks direct SMTP ports.
  - Check `server/storage/logs/laravel.log` for SMTP connection or authentication errors.

#### 4. Video Files / Thumbnails Throw 404
- **Solution:** Re-link the public storage directory:
  ```bash
  php artisan storage:link
  ```
- Ensure the `server/storage` and `server/bootstrap/cache` directories are writable by the PHP process:
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```

#### 5. Clearing System Caches After Updates
Whenever code changes, environment variables, or config changes are deployed, run:
```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

---

## Summary Contacts & Ownership

For escalated infrastructure queries or provider API credentials:
- **Technical Lead / Author:** Nahom Abraham
- **Platform:** The Fibonacci Gallery
- **Primary Domain:** `https://the-fibonacci-gallery.com`
- **Backend API:** `https://back.the-fibonacci-gallery.com`

