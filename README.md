# TPUIRF Platform

Platform penelitian preferensi antarmuka pengguna untuk mengumpulkan pilihan visual responden beserta data interaksi secara terstruktur.

TPUIRF membantu peneliti menjalankan rangkaian eksperimen UI, mencatat waktu dan pola interaksi responden, menyimpan konfigurasi preferensi akhir, serta mengekspor dataset untuk analisis lanjutan atau penelitian jurnal.

## Alur Penelitian

Responden menjalani alur berikut:

1. Masuk menggunakan nama dan tanggal lahir.
2. Melengkapi profil demografis. Usia dihitung otomatis dari tanggal lahir.
3. Membaca informasi penelitian dan memberikan persetujuan.
4. Menyelesaikan enam tugas preferensi secara berurutan.
5. Meninjau atau mengubah pilihan dari tugas sebelumnya bila diperlukan.
6. Mengisi evaluasi akhir.
7. Peneliti mengekspor data untuk dianalisis.

### Enam Tugas Preferensi

| Tugas | Aspek yang diuji | Pilihan |
| --- | --- | --- |
| 1 | Tema | Mode terang dan mode gelap |
| 2 | Tipografi | Keluarga font dan ukuran kecil, sedang, atau besar |
| 3 | Kepadatan tata letak | Rapat, sedang, atau renggang |
| 4 | Posisi navigasi | Atas, bilah samping kiri, atau bawah |
| 5 | Gaya ikon | Garis, penuh, atau dua warna |
| 6 | Gaya desain | Minimal, material, glassmorphism, neumorphism, dan brutalis |

Contoh antarmuka ditampilkan sebagai komponen UI langsung dan responsif. Pada tugas gaya desain, responden dapat membuka contoh melalui panel yang dapat diperluas dan pratinjau layar penuh.

## Pencatatan Interaksi

Pelacak interaksi mencatat data berikut:

- UUID dan nomor urut setiap event;
- waktu event pada perangkat dan waktu penerimaan server;
- durasi aktif, durasi diam, dan durasi total eksperimen;
- klik pada setiap komponen dan opsi;
- jarak waktu dari event sebelumnya;
- jarak waktu antarklik dan komponen yang diklik sebelumnya;
- pergantian pilihan;
- sesi scroll, arah, jarak, durasi, kedalaman, dan perubahan arah;
- ukuran viewport dan metode input;
- durasi membuka contoh gaya desain;
- pilihan akhir dan jumlah perubahan pada setiap tugas.

Event dikirim secara batch dan disimpan sementara di browser apabila pengiriman belum berhasil. Backend menangani pengiriman ulang secara idempoten serta merapikan bentrok nomor urut tanpa membuang event asli.

## Fitur Utama

- Antarmuka berbahasa Indonesia dan responsif.
- Dukungan mode terang dan gelap.
- Dropdown adaptif yang terbuka ke atas ketika ruang bawah tidak cukup.
- Eksperimen dapat dilanjutkan kembali setelah halaman ditutup.
- Draft pilihan dipulihkan dari event terakhir.
- Pilihan tugas sebelumnya dapat ditinjau dan direvisi.
- Profil responden dengan usia otomatis dari tanggal lahir.
- Peran responden, peneliti, dan admin.
- Dashboard analitik untuk peneliti.
- Ekspor CSV, XLSX, dan JSON.
- Dataset event mentah, ringkasan tugas, transisi, preferensi, training, testing, dan gabungan.
- Identitas responden dipseudonimkan pada ekspor event penelitian.

## Teknologi

- PHP 8.3 atau lebih baru
- Laravel 13
- MySQL atau MariaDB
- Blade dan Alpine.js
- Tailwind CSS 4
- Vite 8
- OpenSpout untuk ekspor XLSX
- PHPUnit 12
- Python untuk modul analisis dan machine learning tambahan

## Persyaratan Sistem

Pastikan perangkat memiliki:

- PHP `>= 8.3`;
- Composer;
- ekstensi PHP `intl`, `pdo_mysql`, `mbstring`, `xml`, `curl`, `zip`, dan `fileinfo`;
- MySQL 8+ atau MariaDB;
- Node.js `^20.19.0` atau `>=22.12.0`;
- npm.

Periksa ekstensi `intl` dengan:

```bash
php -m | grep -i intl
```

Jika belum aktif, pasang atau aktifkan ekstensi `intl` pada `php.ini`. Konfigurasi PHP CLI dan PHP-FPM dapat memakai berkas yang berbeda, jadi restart PHP-FPM setelah mengubah konfigurasi server web.

## Instalasi

Clone repository dan pasang dependensi:

```bash
git clone https://github.com/arifianilhamnrr/tpuirf-platform.git
cd tpuirf-platform

composer install
npm install
cp .env.example .env
php artisan key:generate
```

Buat database MySQL atau MariaDB:

```sql
CREATE DATABASE db_tpuirf
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Sesuaikan koneksi pada `.env`:

```dotenv
APP_NAME=TPUIRF
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_tpuirf
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

Jalankan migrasi dan seeder untuk lingkungan pengembangan:

```bash
php artisan migrate --seed
```

Seeder berisi akun serta data contoh untuk pengembangan. Jangan menjalankan atau memakai kredensial seeder pada lingkungan produksi.

## Menjalankan Aplikasi

Cara paling ringkas untuk menjalankan server, queue, log, dan Vite secara bersamaan:

```bash
composer run dev
```

Atau jalankan server backend dan frontend secara terpisah:

```bash
php artisan serve
```

```bash
npm run dev
```

Aplikasi tersedia secara default di `http://127.0.0.1:8000`.

Untuk menghasilkan aset produksi:

```bash
npm run build
```

## Konfigurasi Eksperimen

Konfigurasi utama berada di [`config/experiment.php`](config/experiment.php). Nilai berikut dapat diubah melalui `.env`:

```dotenv
EXPERIMENT_PROTOCOL_VERSION=TPUIRF-PREF-v2
EXPERIMENT_CONSENT_VERSION=1.0
EXPERIMENT_IDLE_THRESHOLD_SECONDS=30
EXPERIMENT_MAX_EVENT_BATCH_SIZE=100
```

Naikkan versi protokol ketika definisi tugas, opsi, atau metode pengumpulan data berubah secara material. Dengan demikian, hasil dari protokol berbeda tidak tercampur tanpa sengaja.

## Pengujian

Jalankan seluruh pengujian backend:

```bash
composer test
```

Atau langsung melalui Artisan:

```bash
php artisan test
```

Validasi build frontend:

```bash
npm run build
```

Pengujian mencakup alur enam tugas, kepemilikan eksperimen, validasi urutan tugas, idempotensi event, pemulihan bentrok nomor urut, revisi preferensi, pemulihan draft, profil responden, evaluasi, dan ekspor pseudonim.

## Struktur Direktori

```text
app/
├── Application/          # Service dan kontrak use case
├── Domain/               # DTO dan kontrak repository
├── Infrastructure/       # Model Eloquent dan implementasi repository
└── Presentation/         # Controller, middleware, dan request validation

config/experiment.php     # Definisi protokol dan tugas penelitian
database/migrations/      # Skema database aplikasi dan penelitian
python/                   # Modul analisis/ML tambahan
resources/views/          # Halaman Blade dan komponen UI
routes/web.php            # Rute responden, peneliti, dan admin
tests/Feature/            # Pengujian alur dan backend penelitian
```

## Tabel Penelitian Utama

- `experiments`: sesi dan progres eksperimen;
- `experiment_tasks`: urutan, status, durasi, serta pilihan setiap tugas;
- `interaction_events`: event interaksi mentah;
- `participant_preferences`: konfigurasi preferensi akhir;
- `trust_evaluations`: penilaian dan umpan balik akhir;
- `user_profiles`: karakteristik responden.

## Ekspor Dataset

Peneliti atau admin dapat mengekspor data dalam format CSV, XLSX, atau JSON. Jenis dataset yang tersedia:

- `raw-events`;
- `task-summary`;
- `transitions`;
- `preferences`;
- `training`;
- `testing`;
- `full`.

Nilai yang berpotensi dibaca sebagai formula spreadsheet diamankan sebelum ditulis ke CSV atau XLSX.

## Privasi dan Penggunaan Penelitian

Project ini mengumpulkan tanggal lahir, data demografis, pilihan UI, serta jejak interaksi. Sebelum digunakan pada responden nyata:

- sesuaikan lembar persetujuan dengan protokol dan persetujuan etik penelitian;
- batasi akses dashboard dan file ekspor;
- gunakan HTTPS;
- nonaktifkan `APP_DEBUG` pada produksi;
- jangan commit `.env`, dump database, log, atau dataset responden;
- tetapkan kebijakan retensi dan penghapusan data;
- simpan backup database secara terenkripsi;
- audit kembali apakah setiap variabel memang diperlukan untuk tujuan penelitian.

Dokumen rencana implementasi yang lebih terperinci tersedia di [`PLAN.md`](PLAN.md).
