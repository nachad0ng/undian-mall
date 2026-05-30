# Installation Guide - Mall Lucky Draw Management System

## Prerequisites

- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL 8.0+

## Step 1: Clone Repository

```bash
git clone https://github.com/nachad0ng/undian-mall.git
cd undian-mall
```

## Step 2: Install PHP Dependencies

```bash
composer install
```

## Step 3: Install Node Dependencies

```bash
npm install
```

## Step 4: Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

## Step 5: Create Database

```bash
# Using MySQL CLI
mysql -u root -e "CREATE DATABASE undian_mall CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Update .env file
DB_DATABASE=undian_mall
DB_USERNAME=root
DB_PASSWORD=your_password
```

## Step 6: Run Migrations & Seeders

```bash
# Run migrations
php artisan migrate

# Seed database with default roles, permissions, and users
php artisan db:seed
```

## Step 7: Build Frontend Assets

```bash
npm run dev
# or for production
npm run build
```

## Step 8: Start Development Server

```bash
php artisan serve
```

Application akan tersedia di `http://localhost:8000`

## Default Login Credentials

Setelah seeding, gunakan salah satu akun berikut:

### Super Admin
- **Email**: admin@example.com
- **Password**: password
- **Role**: Super Admin (akses penuh)

### Manager
- **Email**: manager@example.com
- **Password**: password
- **Role**: Manager

### Customer Service
- **Email**: customerservice@example.com
- **Password**: password
- **Role**: Customer Service

### Auditor
- **Email**: auditor@example.com
- **Password**: password
- **Role**: Auditor

## Directory Structure

```
undian-mall/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   └── Repositories/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── auth/
│   │   └── dashboard/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
├── config/
└── public/
```

## Verification

Setelah instalasi selesai, pastikan:

1. ✅ Database terkoneksi
2. ✅ Migrations berhasil dijalankan
3. ✅ Default users dan roles terseeding
4. ✅ Login page accessible di `/login`
5. ✅ Dashboard accessible setelah login

## Troubleshooting

### Database Connection Error
```bash
# Verify database configuration in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=undian_mall
DB_USERNAME=root
DB_PASSWORD=
```

### PHP Command Not Found
```bash
# Ensure PHP is in PATH or use full path
php -v
```

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Next Steps

Setelah instalasi selesai, aplikasi siap untuk STEP 2: Role & Permission Management.
