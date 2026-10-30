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
10. [Maintenance, Troubleshooting & Runbook](#10-maintenance-troubleshooting--runbook)

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

