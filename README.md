# Pharmax

Pharmacy management app: medicines, stock, purchases, point of sale, invoices, customers, and reports. The interface is Laravel 10 with Inertia and Vue 3. Almost every screen is a server-rendered Inertia page, not a public JSON API.

## Stack

- PHP 8.1+, Laravel 10, MySQL
- Vue 3, Inertia.js, Tailwind CSS, Vite
- Laravel Breeze (session login) and Sanctum (token table only; the UI does not use a token API)
- Spatie Laravel Permission
- DomPDF for invoices, Maatwebsite Excel for spreadsheet import and export

## Modules

| Area | Routes | Notes |
| --- | --- | --- |
| Dashboard | `/dashboard` | Counts, today's sales and purchases, best sellers |
| Medicines | `/medicines` | Categories, manufacturers, CSV import, barcode/QR page, MedEx lookup |
| Stock | `/stocks`, `/stocks/alerts`, `/stocks/reports` | Batch, expiry, low-stock and expiry scopes |
| Purchases | `/purchases` | Supplier purchases that can create stock |
| POS and invoices | `/pos`, `/invoices` | Discounts, dues, printable invoice |
| Customers | `/customers` | |
| Reports | `/reports/sales`, `/purchases`, `/profit-loss`, `/customer-dues` | Requires `view-reports` |
| Accounts | `/accounts` | Ledger accounts and transactions. There is no banks module; `invoices.bank_id` has no `banks` table |
| Users and menus | `/users`, `/menus` | Requires `manage-users` |
| Settings | `/settings` | Shop profile, mail, SMS (Twilio, Nexmo, or a custom HTTP gateway) |
| Backup | `/backup` | Requires `manage-system` |
| Data import/export | `/data-export` | Medicines, customers, invoices, stock. Requires `manage-data` |
| Terminal | `/terminal` | In-app command runner for `php`, `composer`, `node`, `npm`, and `git`. Requires `manage-system` |

Categories and manufacturers are full CRUD resources. Search helpers used by the UI (session cookie required):

- `GET /api/medicines/search?q=`
- `GET /api/customers/search?q=`
- `GET /api/medicines/{medicine}/stocks`
- `GET /api/manufacturers/search?q=` (at least 2 characters)
- `GET /api/medex/search?q=` and `GET /api/medex/product?url=` (proxies medex.com.bd)
- `POST /api/medicines/store-external`
- `POST /api/settings/test-email` and `POST /api/settings/test-sms`
- `GET /api/user` (Sanctum)

## Local setup

Requirements: PHP 8.1+, Composer, Node.js, MySQL.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Set `DB_*` in `.env`, create the database, then:

```bash
php artisan migrate --seed
npm run dev
php artisan serve
```

Open `http://localhost:8000`. The home route redirects to the dashboard, which requires a verified user.

`npm run build` builds the client bundle and the SSR bundle (`vite build --ssr`).

A browser installer is also available at `/install` until `storage/install.lock` exists. See [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md).

## Seeded logins

`database/seeders/UserSeeder.php` creates these users. Password for each is `password`.

| Role | Email |
| --- | --- |
| admin | admin@pharma.com |
| manager | manager@pharma.com |
| cashier | cashier@pharma.com |
| pharmacist | pharmacist@pharma.com |

The installer creates one admin from the form and uses a different permission list. Prefer one path: seed for local development, or the wizard for a fresh deploy. Running both piles two permission catalogs into the same tables.

## Access control

Roles from the seeder: `admin`, `manager`, `cashier`, `pharmacist`. Menus are stored in `menus` and filtered by role.

Only some controllers check a permission:

- customers → `manage-customers` (seeder)
- users and menus → `manage-users`
- reports → `view-reports`
- backup and terminal → `manage-system` (installer and `DemoDataSeeder`, not the base seeder list)
- data export → `manage-data` (same)

Medicines, stock, purchases, invoices, POS, accounts, and settings do not check a permission in the controller. `manage-banks` is seeded and shown on the menu form, but nothing uses it.

## Layout

```
app/Http/Controllers   Inertia controllers
app/Models             Eloquent models
app/Services           NotificationService, SimpleCommandService
database/migrations    Schema
database/seeders       Roles, menus, demo data
resources/js/Pages     Vue pages
routes/web.php         Authenticated UI
routes/install.php     Installer
routes/api.php         Sanctum /user plus settings tests
```

Email and SMS from application code go through `App\Helpers\NotificationHelper`. See [NOTIFICATION_USAGE.md](NOTIFICATION_USAGE.md).

## Tests

```bash
php artisan test
```

Feature coverage exists for auth, profile, medicines, invoices, and stock.
