# Sistem Informasi Manajemen Perpustakaan — RBTI UNDIP

Sistem informasi manajemen perpustakaan untuk **Ruang Baca Teknik Industri (RBTI), Fakultas Teknik, Universitas Diponegoro**, dengan dukungan multi-peran (**Pengunjung**, **Anggota**, dan **Admin**).

## Fitur Utama

- **Portal Masuk & Keamanan (`register.php`, `forgot-password.php`, `reset-password.php`)**
  - Masuk sebagai Pengunjung (buku tamu), Anggota (NIM & password), atau Admin.
  - Pendaftaran anggota baru dengan verifikasi admin.
  - Hashing password (`password_hash` / `password_verify`) dengan kompatibilitas akun demo lama.
  - Alur reset password berbasis token sekali pakai (SHA-256, berlaku 30 menit).
  - Kontrol akses berbasis peran (RBAC) di seluruh halaman dan endpoint API (`auth.php`).
- **Katalog & Pencarian Koleksi (OPAC)**
  - Katalog koleksi (Buku Sirkulasi, Buku Referensi, Kerja Praktik, Tugas Akhir, Tesis).
  - Pencarian buku real-time berdasarkan judul, penulis, atau genre beserta status ketersediaan stok.
- **Sirkulasi Anggota (`peminjaman_buku_pengguna.php`, `index.php`)**
  - Pengajuan peminjaman, perpanjangan masa pinjam (maksimal 2 kali), dan pengembalian buku.
  - Ringkasan aktivitas peminjaman dan notifikasi persetujuan/penolakan langsung di dashboard anggota.
- **Panel Administrasi (`admin_dashboard.php`, `manajemen_buku.php`, `manajemen-user.php`, `peminjaman_buku.php`)**
  - Dashboard metrik operasional real-time (total buku, anggota pending, permintaan sirkulasi, pinjaman aktif).
  - CRUD buku & impor massal CSV (`upload_spreadsheet.php`).
  - Persetujuan atau penolakan pendaftaran anggota baru.
  - Konfirmasi atau penolakan peminjaman, perpanjangan, dan pengembalian buku dengan pembaruan stok atomik dan notifikasi otomatis.

## Cara Menjalankan

### Opsi 1: Docker (Direkomendasikan — Apache + PHP 8.2 + MariaDB)

```bash
docker build -t rbti-perpustakaan .
docker run -p 10000:10000 rbti-perpustakaan
```

Buka `http://localhost:10000/register.php`. Database `rbti` dan akun demo diimpor otomatis dari `rbti.sql` dan `deploy/demo_seed.sql`.

### Opsi 2: XAMPP / Local PHP + MySQL/MariaDB

1. Salin `.env.example` dan sesuaikan variabel lingkungan (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_URL`) bila berbeda dari default (`localhost`, `3306`, `rbti`, `root`, password kosong).
2. Jalankan `composer install`.
3. Impor skema database `rbti.sql` (dan opsional `deploy/demo_seed.sql` untuk data contoh) ke MySQL/MariaDB.
4. Akses aplikasi melalui Apache/PHP di `http://localhost/manajemen-perpustakaan/register.php`.

### Akun Demo (Jika `deploy/demo_seed.sql` Diimpor)

| Peran | Kredensial | Password |
|---|---|---|
| Admin | `Admin` | `1234` |
| Anggota (NIM) | `21070000000001` | `demo123` |
| Pengunjung | Nama & email bebas | — |

> **Catatan Reset Password Lokal**: Saat berjalan tanpa SMTP (`MAIL_TRANSPORT` tidak disetel ke `sendmail`), tautan reset password dicatat secara lokal di `logs/mail_outbox.log`.
