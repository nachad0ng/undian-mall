# Mall Lucky Draw Management System

Aplikasi sistem manajemen undian berhadiah untuk mall dengan fitur multi-tenant, kurasi coupon otomatis, dan mesin pengundian terintegrasi.

## Teknologi

- Laravel 11
- Blade Template
- Bootstrap 5
- jQuery
- MySQL
- Spatie Laravel Permission
- Tabler.io (Free Version)
- DataTables
- Select2
- SweetAlert2

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/nachad0ng/undian-mall.git
cd undian-mall
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Setup Database

```bash
# Buat database
mysql -u root -e "CREATE DATABASE undian_mall CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed data
php artisan db:seed
```

### 5. Generate Assets

```bash
npm run dev
```

### 6. Run Server

```bash
php artisan serve
```

Akses aplikasi di `http://localhost:8000`

## Login Credentials (Default)

- **Email**: admin@example.com
- **Password**: password

## Development Roadmap

- [x] STEP 1: Project Setup
- [ ] STEP 2: Role & Permission Management
- [ ] STEP 3: User Management
- [ ] STEP 4: Tenant Management
- [ ] STEP 5: Tenant Coupon Rule
- [ ] STEP 6: Customer Management
- [ ] STEP 7: Lucky Draw Period
- [ ] STEP 8: Master Prize
- [ ] STEP 9: Receipt Exchange
- [ ] STEP 10: Coupon Generation
- [ ] STEP 11: Approval Workflow
- [ ] STEP 12: Lucky Draw Engine
- [ ] STEP 13: Winner Management & Re-Draw
- [ ] STEP 14: Reporting
- [ ] STEP 15: Audit & Fraud Monitoring
- [ ] STEP 16: Final Refactoring & Optimization

## Project Structure

```
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
│   ├── css/
│   └── js/
├── routes/
├── public/
└── tests/
```

## License

MIT License
