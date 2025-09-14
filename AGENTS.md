Project Overview

This project is a Proof of Concept (PoC) tipping service for waiters.
It is built as a monolithic Laravel 12 application with Inertia.js + Vue 3 (Vite, Tailwind).
The main goal is to validate the end-to-end flow of tipping via Stripe Payment Links, from registration to payment and dashboard reporting.

Key Features (PoC scope):
•	Email/password authentication (Laravel Breeze, no email verification).
•	Waiter profile with avatar, nickname, bio, reviews link, and public tipping page.
•	Public tipping page with quick amounts + custom amount, redirect to Stripe Payment Links.
•	QR code generator for public tipping URL.
•	Stripe integration in test mode, with webhook handling.
•	User dashboard with total tips, transaction list, and payout request flow.
•	Basic admin panel (users, tips, payout requests).
•	Email notifications (new tip, payout request).
•	VPS deployment with HTTPS (Cloudflare + Certbot).

Excluded from PoC: real payouts, multi-currency, mobile apps, advanced analytics, granular roles/permissions.

⸻

Build and Test Commands

Requirements
•	PHP 8.3+
•	Composer 2.x
•	Node.js 20+
•	MySQL/MariaDB
•	Nginx + PHP-FPM
•	Stripe Test Keys
•	Mailtrap (for dev emails)

Setup

# Clone repo
git clone <repo-url>
cd tipping-poc

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Run migrations + seed
php artisan migrate --seed

# Start dev server
php artisan serve
npm run dev

Testing

# Run PHPUnit tests
php artisan test

# Run Vue tests (if configured)
npm run test


⸻

Code Style Guidelines

PHP / Laravel
•	Follow PSR-12 code style.
•	Use Eloquent for queries, avoid raw SQL where possible.
•	Always store money as integers (cents) in DB.
•	Use Form Requests for validation, never validate directly in controllers.
•	Use Service classes or Actions for Stripe/webhook/business logic (avoid bloated controllers).
•	Use Policies for access control.

Vue / Inertia
•	Vue 3 with <script setup> syntax.
•	Use Composition API for state management.
•	Keep components small, reusable, and placed under resources/js/Components.
•	Pages live in resources/js/Pages.
•	Global state (if needed) should use Pinia (avoid Vuex).
•	Follow Tailwind utility-first approach, no inline styles.

Naming Conventions
•	Routes: kebab-case (/payout-requests not /payoutRequests).
•	Vue components: PascalCase.vue.
•	DB tables: plural snake_case (payout_requests).
•	DB columns: snake_case (created_at).
•	Commits: short, imperative, descriptive (e.g., fix: webhook validation bug).

⸻

Testing Instructions
•	Use PHPUnit for backend tests.
•	Write feature tests for main user flows:
•	Registration/login
•	Profile update
•	Tip creation via webhook
•	Payout request
•	Mock Stripe for unit tests, but use real Stripe test mode for E2E flows.
•	Run all tests before pushing to main branch.

⸻

Security Considerations
•	CSRF Protection: Always use @csrf in forms.
•	Stripe Webhooks: Validate signature with Stripe’s secret key.
•	Error Handling: Do not expose stack traces in production.
•	Uploads: Validate and store avatars securely (storage/app/public).
•	Passwords: Handled by Laravel’s built-in Hashing.
•	.env: Never commit to Git, store secrets in VPS environment.
•	HTTPS enforced via Cloudflare + Certbot.
•	Backups: DB dump daily via cron.
•	Logs: Centralize Stripe event logs + Laravel logs.

⸻

Best Practices for Agents
1.	Keep PoC scope minimal – don’t over-engineer, just ensure flows work.
2.	Document assumptions – if Stripe behavior or user story differs, write it down.
3.	Prefer convention over configuration – use Laravel defaults (auth, migrations, file storage).
4.	Use Git branches per feature, PR to main after review.
5.	Write tests first for critical flows (auth, tips, payouts).
6.	Log every Stripe interaction (for debugging and audit trail).
7.	Communicate blockers quickly – PoC must prioritize speed over polish.
