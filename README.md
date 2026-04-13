# Dogday Manager

A dog daycare management system built with **Laravel 13**, **Vue 3**, **Inertia.js**, and **Tailwind CSS**.

## Features

- **Staff portal** — manage owners, dogs, daily attendances, and booking requests
- **Owner portal** — owners can submit booking requests and track payments
- **Payment management** — Easypay gateway integration (sandbox/mock mode available)
- **Email notifications** — automated emails on booking approval/rejection
- **User management** — staff accounts with role-based access

## Tech Stack

- PHP 8.3 / Laravel 13
- Vue 3 + Inertia.js
- Tailwind CSS
- MySQL
- Vite

---

## Requirements

- PHP >= 8.3
- Composer
- Node.js >= 18
- MySQL

---

## Local Setup

### 1. Clone the repository

```bash
git clone https://github.com/Ricardo-Casal/dogday-manager.git
cd dogday-manager
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
npm install
```

### 4. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Then edit `.env` and set your database credentials:

```env
DB_DATABASE=dogday_manager
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### 5. Create the database

In MySQL, create the database:

```sql
CREATE DATABASE dogday_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run migrations

```bash
php artisan migrate
```

### 7. Create an admin/staff user

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'staff',
]);
```

### 8. Start the development servers

In two separate terminals:

```bash
# Terminal 1 — Laravel
php artisan serve
```

```bash
# Terminal 2 — Vite (frontend assets)
npm run dev
```

The app will be available at [http://localhost:8000](http://localhost:8000).

---

## Payments (Easypay)

Payments are integrated with [Easypay](https://www.easypay.pt/). By default the app runs in sandbox mode — no real transactions are made.

To use sandbox mode, set in `.env`:

```env
EASYPAY_SANDBOX=true
EASYPAY_ACCOUNT_ID=your_sandbox_account_id
EASYPAY_API_KEY=your_sandbox_api_key
```

If you just want to explore the UI without configuring Easypay, payments will still function in mock mode.

---

## Mail

By default, mail is set to `log` driver — all emails are written to `storage/logs/laravel.log` instead of being sent. No mail configuration is required for local development.

To use a real mail provider, update the `MAIL_*` variables in `.env`.
