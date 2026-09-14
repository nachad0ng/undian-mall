# Mall Lucky Draw Management System

Aplikasi sistem manajemen undian berhadiah untuk mall dengan fitur multi-tenant, mesin pengundian terintegrasi, dan **sistem penukaran poin berbasis per-hadiah** (bukan per-event).

## Status Proyek

| Tahap | Status | Keterangan |
|---|---|---|
| STEP 1 | ✅ Selesai | Project setup: Laravel 11, Auth, Spatie Permission, Dashboard |
| STEP 2 | ✅ Selesai | Role & Permission Management — Admin Panel CRUD roles/permissions |
| STEP 3 | ✅ Selesai | User Management — CRUD user + assign role |
| STEP 4 | ✅ Selesai | Tenant Management — CRUD tenant + toggle status |
| STEP 6 | ✅ Selesai | Customer Management |
| STEP 7 | ✅ Selesai | Lucky Draw Period — CRUD periode + status |
| STEP 8 | ✅ Selesai | Master Prize — CRUD hadiah (prize) |
| STEP 9 | ✅ Selesai | **Point Exchange / Receipt Exchange** — sistem poin per-hadiah |
| STEP 10 | ✅ Selesai | Point Drawing — pengundian berbobot poin dan pencatatan pemenang |
| STEP 11 | ✅ Selesai | Audit Log — pencatatan redemption dan drawing |
| STEP 12–14 | ✅ Selesai | Reporting, audit viewer, publikasi pemenang, dan hardening operasional |

## Teknologi

- Laravel 11 (PHP 8.2+)
- Blade Template + Bootstrap 5 (Tabler.io Free)
- jQuery + DataTables + Select2 + SweetAlert2
- MySQL / PostgreSQL
- Spatie Laravel Permission
- ViteJS (asset build)
- Yajra DataTables (server-side)

## Aturan Bisnis & Rule Sistem (PENTING)

### A. Rule Umum Sistem

1. **Multi-tenant** — Semua tenant berlaku untuk semua transaksi; tidak ada pengecualian tenant.
2. **1 customer boleh ikut banyak hadiah** dalam satu periode, asalkan menggunakan struk berbeda (bukan struk yang sudah dipakai).
3. **Semua tenant** mencatat transaksi belanja → struk → eligible untuk tukar poin.
4. **CS (Customer Service)** adalah pihak yang memproses penukaran poin saat customer datang ke counter.

### B. Sistem Poin (Refactor: per-Hadiah, bukan per-Event)

Alur transaksi:

1. Customer belanja di tenant → dapat struk (purchase).
2. Customer datang ke CS, menyerahkan struk untuk ditukar poin.
3. Customer **memilih** mau ikut undian hadiah yang mana.
4. Sistem hitung poin berdasarkan **rule milik hadiah yang dipilih** (bukan rule umum event).
5. Poin tersimpan **terpisah per hadiah** (poin Mobil ≠ poin Motor, walau dari struk nominal sama).
6. Struk **dikunci** setelah pertama kali dipakai (exchange_status = 'sudah').

#### Formula Perhitungan Poin

```
total_poin_struk = FLOOR(total_belanja / nominal_per_poin) + bonus_poin
```

- Sisa nominal yang **tidak genap kelipatan** → **hangus** (tidak carry-over).
- `nominal_per_poin` berasal dari hadiah yang dipilih dan disimpan di master `prizes`.
- Histori perubahan nominal dapat ditambahkan kemudian tanpa mengubah alur penukaran saat ini.

#### Bonus Poin dari Tipe Pembayaran

- Tipe pembayaran direkam di struk (`payment_type_id` di tabel `purchases`).
- Bonus poin dihitung **per struk** yang memenuhi syarat, bukan per transaksi penukaran.
- Rule bonus: tabel `bonus_point_rules` (periode_id + payment_type_id + bonus_poin).
- Formula: `total_poin = poin_dari_nominal + bonus_poin (jika match rule aktif)`.

#### Contoh:

| Hadiah | Nominal Per Poin | Struk | Poin Dari Nominal | Bonus (TUNAI) | Total Poin |
|---|---|---|---|---|---|
| Mobil | Rp1.000.000 | Rp5.500.000 | 5 (sisa 500.000 hangus) | 0 | 5 |
| Motor | Rp250.000 | Rp1.000.000 | 4 | 0 | 4 |
| Kulkas | Rp500.000 | Rp2.750.000 | 5 (sisa 250.000 hangus) | 0 | 5 |
| Sepeda | Rp1.000.000 | Rp3.500.000 | 3 (sisa 500.000 hangus) | +2 (KARTU_MEGA) | 5 |

### C. Validasi Penting

1. **1 struk = 1 hadiah** — begitu struk digunakan untuk satu hadiah, struk terkunci dan tidak bisa dipakai lagi (exchange_status = 'sudah').
2. **Struk yang sudah dipakai tidak boleh dipakai ulang** — dicegah oleh exchange_status dan unique constraint di `point_redemptions.purchase_id`.
3. **Tanggal belanja struk** harus masuk rentang `start_at`–`end_at` periode.
4. **Tanggal tukar** harus masuk rentang `exchange_start_at`–`exchange_end_at` (saat ini disamakan dengan periode belanja, tapi kolom terpisah tersedia).
5. **Hadiah yang dipilih** harus `active_for_exchange = true` dan berada di periode yang sama dengan struk.
6. **Setelah poin tercatat, tidak bisa diubah** — tidak ada edit/cancel untuk transaksi penukaran yang sudah sukses.
7. **Poin final** — customer tidak bisa mengganti pilihan hadiah setelah poin tercatat.

### D. Status & Periode

- **Periode (RafflePeriod)** punya dua rentang:
  - `start_at`–`end_at` → rentang struk dianggap sah.
  - `exchange_start_at`–`exchange_end_at` → rentang boleh tukar struk ke CS.
  - Saat ini keduanya disamakan, tapi skema mendukung pemisahan di masa depan.

- Status periode: `draft`, `active`, `inactive`, `closed`.
- Status drawing: `pending`, `in_progress`, `completed`.

### E. Tipe Pembayaran (Payment Type)

Master data tipe pembayaran:

| Code | Keterangan |
|---|---|
| `TUNAI` | Tunai |
| `KARTU_MEGA` | Kartu Kredit Bank Mega |
| `KARTU_MALL` | Kartu Mall |
| `DEBIT_LAIN` | Debit Bank Lain |

Bonus poin diberikan jika struk menggunakan tipe pembayaran yang memiliki rule bonus aktif di periode tersebut.

## Struktur Modul

### 1. Autentikasi & Authorization (Spatie Permission)

- User, Role, Permission.
- Middleware: `auth`, `permission:<name>`.
- Default roles: Super Admin, Manager, Customer Service, Auditor.

### 2. Master Data

- **Periode Undian (RafflePeriod)** — CRUD, status, tanggal belanja & tukar.
- **Tenant** — CRUD, toggle status, jumlah transaksi.
- **Hadiah (Prize)** — CRUD, nominal_per_poin, active_for_exchange, kuota, urutan.
- **Tipe Pembayaran (PaymentType)** — CRUD, code, name, description, is_active.

### 3. Transaksi & Poin

- **Purchase (Struk)** — merekam belanja: customer, tenant, receipt_number, nominal, payment_type_id, exchange_status.
- **PointRedemption** — 1 baris = 1 struk ditukar untuk 1 hadiah. Kolom: customer_id, periode_id, hadiah_id, struk_id, cs_id, tanggal_tukar, nominal_struk, total_poin_didapat, status.
- **CustomerPointBalance** — rekap per customer per periode per hadiah (total_poin).
- **BonusPointRule** — bonus poin per tipe pembayaran per periode.

### 4. Pengundian

- **Drawing & Winner** — pengundian berbobot poin per hadiah dan pencatatan pemenang.
- **AuditLog** — histori immutable untuk redemption dan drawing.

## Alur Sistem Saat Ini

- 1 Periode punya banyak hadiah.
- Setiap hadiah punya `nominal_per_poin` sendiri.
- Customer pilih hadiah → sistem hitung poin berdasarkan rule hadiah tersebut.
- Poin tersimpan per hadiah.
- Bonus poin dari tipe pembayaran.
- 1 struk = 1 hadiah, struk dikunci setelah pakai.
- Saldo poin menjadi pool pengundian per hadiah.
- Setiap poin menjadi bobot peluang dan pemenang dicatat ke drawing.

## Database Schema (Ringkasan)

```
raffle_periods        — id, code, name, description, start_at, end_at,
                         exchange_start_at, exchange_end_at, status,
                         drawing_status, created_by, timestamps

customers             — id, name, phone, identity_number, email, address, timestamps

tenants               — id, code, name, unit_number, phone, status, timestamps

prizes                — id, raffle_period_id, name, description, gambar_url,
                         kuota, nominal_per_poin, sequence, status,
                         active_for_exchange, timestamps

payment_types         — id, code, name, description, is_active, timestamps

purchases             — id, raffle_period_id, customer_id, tenant_id, entered_by,
                         receipt_number, purchased_at, amount,
                         payment_type_id, exchange_status, notes, timestamps

point_redemptions     — id, customer_id, raffle_period_id, prize_id, purchase_id,
                         cs_id, redeemed_at, nominal_struk, nominal_per_poin_snapshot,
                         poin_dari_nominal, poin_bonus_pembayaran, bonus_rule_id_snapshot,
                         payment_type_code_snapshot, payment_type_name_snapshot,
                         total_poin_didapat,
                         status, notes, timestamps

customer_point_balances — id, customer_id, raffle_period_id, prize_id, total_poin, timestamps

bonus_point_rules     — id, raffle_period_id, payment_type_id, bonus_poin,
                         is_active, timestamps
```

## API Endpoints (Admin)

### Master Data
```
GET    /admin/raffle-periods              — list periode
GET    /admin/raffle-periods/create       — form create
POST   /admin/raffle-periods              — store
GET    /admin/raffle-periods/{id}/edit    — form edit
PUT    /admin/raffle-periods/{id}         — update
POST   /admin/raffle-periods/{id}/toggle-status — toggle status
DELETE /admin/raffle-periods/{id}         — delete

GET    /admin/tenants                     — list tenant
GET    /admin/tenants/create              — form create
POST   /admin/tenants                     — store
GET    /admin/tenants/{id}/edit           — form edit
PUT    /admin/tenants/{id}                — update
POST   /admin/tenants/{id}/toggle-status  — toggle status
DELETE /admin/tenants/{id}                — delete

GET    /admin/prizes                      — list hadiah
GET    /admin/prizes/create               — form create
POST   /admin/prizes                      — store
GET    /admin/prizes/{id}/edit            — form edit
PUT    /admin/prizes/{id}                 — update
POST   /admin/prizes/{id}/move            — reorder
DELETE /admin/prizes/{id}                 — delete

GET    /admin/payment-types               — list tipe pembayaran
GET    /admin/payment-types/create        — form create
POST   /admin/payment-types               — store
GET    /admin/payment-types/{id}/edit     — form edit
PUT    /admin/payment-types/{id}          — update
POST   /admin/payment-types/{id}/toggle-status — toggle status
DELETE /admin/payment-types/{id}          — delete

GET    /admin/bonus-point-rules            — daftar aturan bonus pembayaran
POST   /admin/bonus-point-rules            — store aturan bonus
PUT    /admin/bonus-point-rules/{id}       — update aturan bonus
DELETE /admin/bonus-point-rules/{id}       — delete aturan bonus
POST   /admin/bonus-point-rules/{id}/toggle-status — toggle status
```

### Point Exchange
```
GET    /admin/periods/{period}/active-prizes     — daftar hadiah aktif (JSON)
GET    /admin/customers/search                   — cari customer (JSON)
POST   /admin/customers/quick-store              — buat customer dari form exchange
GET    /admin/customers/{customer}/purchases     — daftar struk belum ditukar (JSON)
POST   /admin/point-exchange                      — tukar poin (customer_id, purchase_id, prize_id)
GET    /admin/customers/{customer}/point-balances — saldo poin customer per hadiah
GET    /admin/point-exchange                      — history (DataTables)
GET    /admin/point-exchange/{redemption}         — detail transaksi
```

### Drawing Berbasis Poin
```
POST   /admin/prizes/{prize}/draw                 — undi customer berdasarkan saldo poin hadiah
GET    /admin/prizes/{prize}/draw-preview         — preview pool dan kesiapan drawing
POST   /admin/winners/{winner}/publish            — publikasikan pemenang
POST   /admin/winners/{winner}/unpublish          — batalkan publikasi pemenang
```

Setiap poin menjadi bobot peluang. Satu customer hanya dapat menang sekali untuk hadiah yang sama. Jumlah pemenang mengikuti `prizes.quantity`, dan drawing kedua untuk hadiah yang sama ditolak.

Halaman publik pemenang tersedia di `GET /winners` dan hanya menampilkan pemenang dengan status publikasi aktif.

### Admin & User Management
```
GET    /admin/users                — list user
GET    /admin/users/create         — form create
POST   /admin/users                — store
GET    /admin/users/{id}/edit      — form edit
PUT    /admin/users/{id}           — update
DELETE /admin/users/{id}           — delete

GET    /admin/roles                — list roles
GET    /admin/roles/create         — form create
POST   /admin/roles                — store
GET    /admin/roles/{id}/edit      — form edit
PUT    /admin/roles/{id}           — update
DELETE /admin/roles/{id}           — delete

GET    /admin/permissions          — list permissions
GET    /admin/permissions/create   — form create
POST   /admin/permissions          — store
GET    /admin/permissions/{id}/edit — form edit
PUT    /admin/permissions/{id}     — update
DELETE /admin/permissions/{id}     — delete
```

## Hak Akses (Permission)

| Permission | Keterangan |
|---|---|
| `manage-roles` | CRUD roles |
| `manage-permissions` | CRUD permissions |
| `manage-users` | CRUD users + lihat saldo poin |
| `manage-periods` | CRUD periode |
| `manage-tenants` | CRUD tenant |
| `manage-prizes` | CRUD hadiah + manajemen poin exchange |
| `manage-customers` | CRUD customer dan pencarian customer |
| `manage-payment-types` | CRUD tipe pembayaran |

Permission `manage-draws` diperlukan untuk menjalankan drawing. Redemption dan drawing mencatat audit log.

## Login Default

| Role | Email | Password |
|---|---|---|
| Super Admin | admin@example.com | password |
| Manager | manager@example.com | password |
| Customer Service | customerservice@example.com | password |
| Auditor | auditor@example.com | password |

## Instalasi Cepat

```bash
git clone https://github.com/nachad0ng/undian-mall.git
cd undian-mall
composer install
npm install
cp .env.example .env
php artisan key:generate
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan migrate
php artisan db:seed
npm run dev
php artisan serve
```

Akses: `http://localhost:8000`

### Data Demo untuk Pengujian Drawing

`php artisan db:seed` juga membuat data redemption demo melalui `PointRedemptionSeeder`:

- 20 redemption sukses dan saldo poin per hadiah.
- 5 customer eligible untuk setiap hadiah pada periode `MALL-2026`.
- Data audit untuk setiap redemption.
- Nomor struk demo menggunakan pola `DEMO-MALL-2026-...`.

Setelah login sebagai `admin@example.com` dengan password `password`, buka menu Hadiah, pilih detail hadiah, lalu gunakan panel **Pengundian Berbasis Poin** untuk melihat pool dan menjalankan draw.

## Struktur Project

```
undian-mall/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── RoleController.php
│   │   │   │   ├── PermissionController.php
│   │   │   │   ├── UserController.php
│   │   │   │   ├── TenantController.php
│   │   │   │   ├── PrizeController.php
│   │   │   │   ├── RafflePeriodController.php
│   │   │   │   ├── PaymentTypeController.php
│   │   │   ├── PointExchangeController.php
│   │   │   ├── DrawingController.php
│   │   │   ├── AuditLogController.php
│   │   │   └── ReportController.php
│   │   │   ├── AuthController.php
│   │   │   └── DashboardController.php
│   │   ├── Requests/
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Customer.php
│   │   ├── Tenant.php
│   │   ├── RafflePeriod.php
│   │   ├── Prize.php
│   │   ├── Purchase.php
│   │   ├── PaymentType.php
│   │   ├── BonusPointRule.php
│   │   ├── PointRedemption.php
│   │   ├── CustomerPointBalance.php
│   │   ├── Drawing.php
│   │   └── AuditLog.php
│   ├── Exports/
│   │   ├── PointRedemptionsExport.php
│   │   ├── CustomerPointBalancesExport.php
│   │   ├── WinnersExport.php
│   │   ├── AuditLogsExport.php
│   │   └── ExportHelper.php
│   └── Services/
│       ├── PointCalculationService.php
│       ├── PointRedemptionService.php
│       └── PointDrawingService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── admin/
│   │   │   ├── roles/
│   │   │   ├── permissions/
│   │   │   ├── users/
│   │   │   ├── tenants/
│   │   │   │   ├── prizes/
│   │   │   │   ├── raffle-periods/
│   │   │   │   ├── payment-types/
│   │   │   │   ├── point-exchange/
│   │   │   │   ├── reports/
│   │   └── layouts/
│   ├── css/
│   └── js/
├── routes/
│   └── web.php
├── public/
└── tests/
    ├── Unit/
    │   └── PointCalculationTest.php
    ├── Feature/
      ├── PointRedemptionIntegrationTest.php
      ├── PointDrawingIntegrationTest.php
      ├── AuditLogViewerTest.php
      └── ReportExportTest.php
```

## Testing

```bash
php vendor/bin/phpunit
# atau spesifik:
php vendor/bin/phpunit tests/Unit/PointCalculationTest.php
php vendor/bin/phpunit tests/Feature/PointRedemptionIntegrationTest.php
```

Test yang ada:
- **PointCalculationTest** (Unit): 9 test — poin dari nominal, sisa hangus, bonus pembayaran, edge case.
- **PointRedemptionIntegrationTest** (Feature): test success, snapshot kalkulasi, kunci struk, validasi periode, dan saldo.
- **PointDrawingIntegrationTest** (Feature): weighted drawing, winner unik, dan pencegahan drawing ganda.
- **AuditLogViewerTest** (Feature): filter audit dan pembatasan permission.
- **ReportExportTest** (Feature): 20 test — export laporan ke Excel + halaman laporan terpisah dengan DataTables.
- Status saat ini: `92 tests, 428 assertions` lulus dengan `vendor/bin/phpunit`.

## Laporan & Export Excel

Sistem menyediakan 4 halaman laporan terpisah, masing-masing dengan form filter, tombol **Load Data** untuk menampilkan hasil di DataTables, dan tombol **Unduh Excel** yang aktif setelah data dimuat.

### Permission Akses Laporan

| Permission | Akses |
|---|---|
| `view-reports` | Akses laporan Poin Redemption, Saldo Poin Customer, dan Pemenang (Manager, CS) |
| `view-audit-logs` | Akses laporan Audit Log (Auditor saja) |
| `manage-prizes` | Full export access (Super Admin, Manager) |
| `manage-users` | Full export access (Super Admin, Manager) |

### Endpoint Laporan (4 Menu Terpisah)

```
GET /admin/reports                              — Dashboard daftar 4 laporan
GET /admin/reports/point-redemptions            — Laporan Poin Redemption (form filter + DataTables)
GET /admin/reports/point-redemptions/data       — Endpoint DataTables (JSON)
GET /admin/reports/point-redemptions/export     — Export ke Excel
GET /admin/reports/customer-point-balances      — Laporan Saldo Poin Customer
GET /admin/reports/customer-point-balances/data — Endpoint DataTables
GET /admin/reports/customer-point-balances/export — Export ke Excel
GET /admin/reports/winners                      — Laporan Pemenang Undian
GET /admin/reports/winners/data                 — Endpoint DataTables
GET /admin/reports/winners/export              — Export ke Excel
GET /admin/reports/audit-logs                   — Laporan Audit Log (Auditor only)
GET /admin/reports/audit-logs/data              — Endpoint DataTables
GET /admin/reports/audit-logs/export            — Export ke Excel (Auditor only)
```

### Filter yang Tersedia

- **Point Redemption**: periode, hadiah, rentang tanggal
- **Customer Point Balances**: periode, hadiah
- **Winners**: periode, hadiah, rentang tanggal, status publikasi
- **Audit Logs**: action, user, rentang tanggal

### Konvensi UI Laporan

Setiap halaman laporan mengikuti pola berikut:
1. **Form filter** — pilih kriteria laporan
2. **Load Data** — tombol untuk memuat data ke DataTables (belum ada query ke database sampai tombol diklik)
3. **DataTables** — tabel hasil yang ditampilkan dengan pagination
4. **Unduh Excel** — tombol export yang baru muncul setelah data dimuat

## Todo

Semua todo roadmap saat ini sudah selesai.

- [x] **Sinkronkan dokumentasi API dan skema**
  - Tambahkan endpoint customer: `customers/search`, `customers/quick-store`, dan `customers/{customer}/purchases`.
  - Dokumentasikan modul `bonus-point-rules` dan permission `manage-customers` serta `manage-payment-types`.
  - Dokumentasikan field snapshot pada `point_redemptions`: `nominal_per_poin_snapshot`, `poin_dari_nominal`, `poin_bonus_pembayaran`, `bonus_rule_id_snapshot`, `payment_type_code_snapshot`, dan `payment_type_name_snapshot`.
  - Samakan istilah status struk menjadi `exchange_status` di README dan RULES.
  - Perbaiki typo `exchange_start_at` yang tertulis dua kali di RULES.

- [x] **Tambahkan test snapshot kalkulasi**
  - Pastikan nilai snapshot tersimpan saat redemption sukses.
  - Pastikan histori tetap dapat dibaca walaupun rule hadiah atau bonus pembayaran berubah.

- [x] **Integrasikan poin dengan drawing**
  - Tentukan pool peserta berdasarkan `customer_point_balances` per hadiah.
  - Hubungkan drawing dan winner dengan peserta berbasis poin.
  - Tambahkan validasi kuota dan pencegahan drawing ganda.

- [x] **Bangun approval dan audit workflow**
  - Approval manual tidak digunakan: redemption sukses bersifat atomic dan final.
  - Audit log tersedia untuk redemption dan drawing.

- [x] **Reporting & Excel Export**
  - 4 halaman laporan terpisah: Poin Redemption, Saldo Poin Customer, Pemenang, Audit Log
  - Form filter + tombol **Load Data** → DataTables → tombol **Unduh Excel**
  - Export menggunakan `phpoffice/phpspreadsheet ^3.0`
  - Endpoint `/admin/reports/*/data` (DataTables JSON) dan `/admin/reports/*/export` (Excel)
- [x] **Publikasi pemenang**
  - Tambahkan workflow publish/unpublish pemenang.
  - Sediakan tampilan daftar pemenang yang siap ditampilkan ke publik.
- [x] **Hardening operasional drawing**
  - Tambahkan validasi periode dan status hadiah sebelum drawing.
  - Tambahkan endpoint preview pool sebelum drawing.
  - Row lock pada hadiah mencegah drawing ganda bersamaan; hasil drawing diaudit.

## Lisensi

MIT License
