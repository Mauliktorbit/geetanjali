# Geetanjali Jewellers

A Laravel-based web application for **Geetanjali Jewellers**, a Kundan Jewellery manufacturing and wholesale business.

## Description

This project provides the application foundation for Geetanjali Jewellers:

- Public frontend structure (Blade + Bootstrap 5)
- Admin area structure under `/admin`
- Central brand and theme configuration
- Standard Laravel authentication-ready User model
- MySQL database connection for local XAMPP development

Business modules (catalogue, inventory, wholesale orders, billing, etc.) will be added when requirements are provided.

## Technology

- Laravel 12
- PHP 8.2+
- Blade
- Bootstrap 5
- JavaScript
- Vite
- MySQL (MariaDB via XAMPP)

## Requirements

- PHP 8.2 or higher (with extensions: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON, BCMath, Fileinfo)
- Composer
- Node.js 20+ and NPM
- MySQL / MariaDB (XAMPP recommended on Windows)

## Installation

From the project root:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell, if `cp` is unavailable:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Create the MySQL database (XAMPP example):

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS geetanjali_jewellers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Then run:

```bash
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

### XAMPP Apache (optional)

You can also serve via Apache using:

`http://localhost/Geetanjali/public`

Update `APP_URL` in `.env` accordingly.

## Environment

Sensitive values belong in `.env` only. `.env.example` contains safe placeholders.

Key settings:

| Setting | Purpose |
|---------|---------|
| `APP_NAME` | Application display name |
| `APP_URL` | Base URL |
| `DB_*` | Database connection |
| `MAIL_*` | Mail placeholders |
| `FILESYSTEM_DISK` | File storage disk |
| `BRAND_*` | Brand name / tagline |

Brand colors live in `config/brand.php` and CSS variables in `resources/css/app.css`.

## Project Structure (foundation)

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   └── Frontend/
│   ├── Middleware/
│   └── Requests/
├── Models/
├── Services/
└── Providers/

resources/
├── views/
│   ├── layouts/
│   ├── components/
│   ├── frontend/
│   ├── admin/
│   └── errors/
├── css/
└── js/

routes/
├── web.php
└── api.php
```

## Development

```bash
npm run dev
php artisan serve
```

Or use Laravel's combined script (if available):

```bash
composer run dev
```

## Suggested Initial Commit

```text
Initial Laravel setup for Geetanjali Jewellers
```

## License

Proprietary — Geetanjali Jewellers.
