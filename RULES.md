# Aturan Bisnis & Rule Sistem — Mall Lucky Draw

Dokumen ini merangkum rule yang berlaku di sistem, terutama sistem **Point Exchange** (penukaran poin berbasis per-hadiah).

---

## 1. Awalan Umum

### 1.1 Multi-Tenant
- Semua tenant berlaku sebagai tempat transaksi.
- Tidak ada pengecualian tenant — semua tenant mencatat struk yang eligible untuk tukar poin.

### 1.2 Customer
- 1 customer **boleh ikut banyak hadiah** dalam satu periode.
- Syarat: menggunakan struk yang berbeda (bukan struk yang sudah dipakai untuk hadiah lain).
- Poin tersimpan per hadiah: poin untuk Mobil ≠ poin untuk Motor.

### 1.3 CS (Customer Service)
- CS adalah pihak yang memproses penukaran poin.
- CS merekam `cs_id` saat memproses struk.

---

## 2. Sistem Poin (Refaktor: Per-Hadiah, Bukan Per-Event)

### 2.1 Alur Transaksi

```
Customer belanja → dapat struk (Purchase)
    ↓
Customer datang ke CS, serahkan struk
    ↓
Customer pilih hadiah yang mau diikuti
    ↓
Sistem hitung poin berdasarkan rule hadiah yang dipilih
    ↓
Sistem rekam penukaran → kunci struk → update saldo poin
```

### 2.2 Formula Perhitungan Poin

```
total_poin_struk = FLOOR(total_belanja / nominal_per_poin) + bonus_poin
```

- `nominal_per_poin` dibaca dari kolom `nominal_per_poin` pada hadiah yang dipilih.
- Jika histori perubahan nominal dibutuhkan di masa depan, histori dapat ditambahkan
  tanpa mengubah kontrak penukaran saat ini.
- Sisa nominal yang **tidak genap kelipatan** → **hangus** (tidak carry-over ke poin berikutnya).

### 2.3 Bonus Poin dari Tipe Pembayaran

- Bonus poin **dihitung per struk** yang memenuhi syarat (bukan per transaksi penukaran).
- Dihitung jika struk memiliki `payment_type_id` yang match dengan `bonus_point_rules` aktif di periode tersebut.
- Formula akhir:

```
poin_dari_nominal = FLOOR(amount / nominal_per_poin)
total_poin = poin_dari_nominal + (bonus_poin jika ada match)
```

### 2.4 Contoh Konkret

| Hadiah | Nominal Per Poin | Struk | Poin Nominal | Bonus (TUNAI) | Bonus (KARTU_MEGA) | Total (Tunai) | Total (Kartu) |
|---|---|---|---|---|---|---|---|
| Mobil | Rp1.000.000 | Rp5.500.000 | 5 (sisa 500.000 hangus) | 0 | — | 5 | — |
| Motor | Rp250.000 | Rp1.000.000 | 4 | 0 | — | 4 | — |
| Kulkas | Rp500.000 | Rp2.750.000 | 5 (sisa 250.000 hangus) | 0 | — | 5 | — |
| Sepeda | Rp1.000.000 | Rp3.500.000 | 3 (sisa 500.000 hangus) | — | +2 | — | 5 |

> Catatan: Jika struk menggunakan KARTU_MEGA dan ada rule bonus 2 poin untuk KARTU_MEGA di periode tersebut, total = 3 + 2 = 5.

---

## 3. Validasi Penting

### 3.1 Struk
| Aturan | Keterangan |
|---|---|
| **1 struk = 1 hadiah** | Setelah struk digunakan untuk satu hadiah, struk terkunci (`status_tukar = 'sudah'`). |
| **Tidak boleh dipakai ulang** | Struk yang sudah ada di `point_redemptions` atau `status_tukar = 'sudah'` tidak boleh dipakai lagi. |
| **Tanggal belanja valid** | `purchased_at` harus berada di antara `start_at` dan `end_at` periode. |
| **Tipe pembayaran dicatat** | `payment_type_id` di struk (opsional) — jika ada, dicek untuk bonus poin. |

### 3.2 Hadiah
| Aturan | Keterangan |
|---|---|
| **Status aktif** | Hadiah harus `active_for_exchange = true`. |
| **Satu periode** | Hadiah dan struk harus berasal dari periode yang sama (`prize.raffle_period_id == purchase.raffle_period_id`). |
| **Nominal per poin tersedia** | Hadiah harus memiliki `nominal_per_poin`. |

### 3.3 Periode & Waktu
| Aturan | Keterangan |
|---|---|
| **Tanggal tukar valid** | Waktu penukaran harus berada di antara `exchange_start_at` dan `exchange_end_at`. |
| **Rentang belanja** | Struk harus dibeli di dalam rentang `start_at`–`end_at` periode. |
| **Saat ini disamakan** | `exchange_start_at` dan `exchange_start_at` default sama dengan `start_at`/`end_at`, tapi bisa dipisah nanti. |

### 3.4 Poin yang Sudah Tercatat
| Aturan | Keterangan |
|---|---|
| **Final** | Setelah poin tercatat di `point_redemptions`, tidak bisa diubah atau dibatalkan. |
| **Tidak bisa ganti hadiah** | Customer tidak bisa mengganti pilihan hadiah setelah transaksi sukses. |
| **Tidak ada edit/cancel** | Tidak ada endpoint edit atau cancel untuk transaksi penukaran yang sudah sukses. |

---

## 4. Skema Database (Point-Related)

### 4.1 Tabel `purchases` (Struk)
| Kolom | Keterangan |
|---|---|
| `id` | PK |
| `raffle_period_id` | FK ke periode |
| `customer_id` | FK ke customer |
| `tenant_id` | FK ke tenant |
| `entered_by` | FK ke user (petugas yang input) |
| `receipt_number` | Nomor struk |
| `purchased_at` | Tanggal belanja |
| `amount` | Nominal belanja (Rp) |
| `payment_type_id` | FK ke payment_types (opsional) |
| `status_tukar` | `'belum'` atau `'sudah'` |
| `created_at`, `updated_at` | Timestamp |

### 4.2 Tabel `prizes` (Hadiah)
| Kolom | Keterangan |
|---|---|
| `id` | PK |
| `raffle_period_id` | FK ke periode |
| `name` | Nama hadiah |
| `nominal_per_poin` | Kelipatan Rp per 1 poin |
| `active_for_exchange` | Boolean — boleh tidak untuk tukar poin |
| `status` | `'active'` / `'inactive'` |
| `kuota` | Jumlah unit (opsional) |
| `sequence` | Urutan tampil |

### 4.3 Tabel `payment_types` (Tipe Pembayaran)
| Kolom | Keterangan |
|---|---|
| `id` | PK |
| `code` | Kode unik: `TUNAI`, `KARTU_MEGA`, `KARTU_MALL`, `DEBIT_LAIN` |
| `name` | Nama tampilan |
| `description` | Keterangan |
| `is_active` | Boolean |

### 4.4 Tabel `bonus_point_rules`
| Kolom | Keterangan |
|---|---|
| `id` | PK |
| `raffle_period_id` | FK ke periode |
| `payment_type_id` | FK ke payment_types |
| `bonus_poin` | Jumlah bonus poin (contoh: 2) |
| `is_active` | Boolean |

> **Catatan:** Satu periode bisa punya beberapa bonus rules untuk tipe pembayaran berbeda.

### 4.5 Tabel `point_redemptions` (Transaksi Penukaran)
| Kolom | Keterangan |
|---|---|
| `id` | PK |
| `customer_id` | FK ke customer |
| `raffle_period_id` | FK ke periode |
| `prize_id` | FK ke hadiah |
| `purchase_id` | FK ke struk (unik — satu struk hanya satu baris) |
| `cs_id` | FK ke user (petugas CS) |
| `redeemed_at` | Waktu tukar |
| `nominal_struk` | Nominal struk yang ditukar |
| `total_poin_didapat` | Hasil kalkulasi |
| `status` | `'success'` / `'failed'` / `'rejected'` |
| `notes` | Catatan (opsional) |

> **Constraint unik:** `purchase_id` harus unik — satu struk hanya boleh muncul sekali di sini.

### 4.6 Tabel `customer_point_balances` (Saldo)
| Kolom | Keterangan |
|---|---|
| `id` | PK |
| `customer_id` | FK ke customer |
| `raffle_period_id` | FK ke periode |
| `prize_id` | FK ke hadiah |
| `total_poin` | Total poin milik customer untuk hadiah tersebut |

> **Composite unique:** `(customer_id, raffle_period_id, prize_id)`.

### 4.7 Histori Rule Poin (Rencana Masa Depan)
> Belum digunakan pada implementasi saat ini. Jika histori diperlukan, tabel ini dapat ditambahkan kemudian.

---

## 5. Endpoint API (Point Exchange)

### 5.1 Daftar Hadiah Aktif untuk Periode
```
GET /admin/periods/{period}/active-prizes
```
Response JSON berisi daftar hadiah dengan `nominal_per_poin` efektif dan flag `can_exchange`.

### 5.2 Penukaran Poin
```
POST /admin/point-exchange
Content-Type: application/json

{
  "customer_id": 123,
  "purchase_id": 456,
  "prize_id": 789,
  "cs_id": 10,        // opsional
  "notes": "..."      // opsional
}
```
Response sukses (201):
```json
{
  "success": true,
  "message": "Penukaran poin berhasil: 5 poin untuk Sepeda.",
  "redemption": { ... },
  "calculation": {
    "nominal_per_poin": 1000000,
    "points_from_amount": 3,
    "bonus_points": 2,
    "total_points": 5,
    "unused_remainder": 500000
  },
  "bonus": {
    "payment_type_name": "Kartu Kredit Bank Mega",
    "payment_code": "KARTU_MEGA",
    "bonus_points": 2
  }
}
```
Response gagal (422):
```json
{
  "success": false,
  "message": "Struk sudah pernah ditukar poinnya."
}
```

### 5.3 Saldo Poin Customer
```
GET /admin/customers/{customer}/point-balances
```
Response berisi saldo per hadiah untuk periode aktif.

### 5.4 History Penukaran (Admin)
```
GET /admin/point-exchange
```
DataTables server-side. Kolom: customer, struk, hadiah, nominal, poin, CS, waktu.

### 5.5 Detail Transaksi
```
GET /admin/point-exchange/{redemption}
```
Detail satu transaksi penukaran.

---

## 6. Hak Akses (Permission)

| Permission | Akses |
|---|---|
| `manage-prizes` | CRUD hadiah, melihat/daftar hadiah aktif, history penukaran poin |
| `manage-users` | CRUD user, melihat saldo poin customer |

- Semua endpoint point-exchange dilindungi middleware `auth` + `permission`.
- `admin.point-exchange.store` → `manage-prizes`.
- `admin.customers.point-balances` → `manage-users`.
- `admin.point-exchange.history` dan `show` → `manage-prizes` atau `manage-users`.

---

## 7. Status & Konstanta

### 7.1 Status Periode
- `draft` — belum aktif
- `active` — berjalan
- `inactive` — tidak aktif
- `closed` — ditutup

### 7.2 Status Drawing
- `pending` — belum mulai
- `in_progress` — sedang berlangsung
- `completed` — selesai

### 7.3 Status Struk
- `belum` — belum ditukar
- `sudah` — sudah ditukar (dikunci)

### 7.4 Status Penukaran
- `success` — berhasil
- `failed` — gagal
- `rejected` — ditolak

### 7.5 Status Hadiah
- `active` — aktif
- `inactive` — tidak aktif

---

## 8. Perbedaan dengan Sistem Lama (Coupon)

| Aspek | Sistem Lama (Coupon) | Sistem Baru (Poin) |
|---|---|---|
| Unit | Kupon per transaksi | Poin per hadiah |
| Konfigurasi | 1 event = 1 threshold + unit | 1 hadiah = 1 nominal_per_poin |
| Bonus | Tidak ada | Bonus poin dari tipe pembayaran |
| Struk | Bisa dapat multi kupon | 1 struk = 1 hadiah, dikunci setelah pakai |
| Saldo | Tidak ada | `customer_point_balances` per hadiah |
| Fleksibilitas | Rule tidak berubah di tengah jalan | Nominal aktif dikelola dari master hadiah |
| Carry-over | — | Sisa nominal hangus (tidak carry-over) |

---

## 9. Flowchart Singkat

```
[Customer Belanja] → [Struk (Purchase)] → [CS Proses]
                                               │
                                               ├─ [Pilih Hadiah]
                                               │
                                               ├─ [Cek Validasi]
                                               │   ├─ Struk belum ditukar?
                                               │   ├─ Tanggal belanja valid?
                                               │   ├─ Tanggal tukar valid?
                                               │   └─ Hadiah & struk satu periode?
                                               │
                                               ├─ [Hitung Poin]
                                               │   ├─ nominal_per_poin dari Prize
                                               │   ├─ FLOOR(amount / nominal_per_poin)
                                               │   └─ + bonus_poin (jika payment_type match)
                                               │
                                               ├─ [Simpan PointRedemption]
                                               │   └─ 1 struk = 1 hadiah
                                               │
                                               ├─ [Kunci Struk] → status_tukar = 'sudah'
                                               │
                                               └─ [Update Saldo] → customer_point_balances
```

---

## 10. Catatan Implementasi

1. **Tidak ada tabel detail banyak struk** — karena 1 struk = 1 hadiah, tabel `point_redemptions` cukup 1 baris per struk (kolom `purchase_id` sebagai unique).
2. **Nominal poin dikelola dari hadiah** — Form master hadiah menyimpan `nominal_per_poin` yang digunakan saat penukaran.
3. **Bonus per struk** — Bonus poin dihitung per struk, bukan per transaksi penukaran.
4. **Sisa hangus** — Sisa nominal yang tidak genap kelipatan tidak di-carry-over.
5. **Update skema** — Kolom `exchange_start_at` dan `exchange_end_at` ditambahkan di `raffle_periods` untuk antisipasi pemisahan rentang belanja dan tukar di masa depan (saat ini disamakan).
