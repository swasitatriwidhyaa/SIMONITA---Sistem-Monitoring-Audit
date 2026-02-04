# Sistem Monitoring Audit Internal - PTPN 1 Regional 7

![Dashboard Preview](public/image/audit-bg.jpg)
*(Ganti link gambar di atas dengan screenshot dashboard asli kamu nanti)*

## 📖 Deskripsi
Aplikasi berbasis web ini dikembangkan untuk mendigitalisasi proses monitoring dan pelaporan audit internal di lingkungan **PT Perkebunan Nusantara 1 Regional 7**. Sistem ini bertujuan untuk mempermudah Auditor dan Unit Kerja (Auditee) dalam mengelola jadwal, memantau temuan (findings), dan memastikan kepatuhan terhadap standar perusahaan.

Sistem ini mendukung berbagai jenis standar audit seperti **ISO 9001:2015, ISO 14001, SMK3, SMAP, Sistem Jaminan Halal**, dan lain-lain.

## 🚀 Fitur Unggulan

### 1. Dashboard Interaktif
* Visualisasi data dengan Grafik Donut (Open, Process, Closed).
* Statistik jumlah audit secara real-time.
* **Deadline Reminder:** Notifikasi daftar audit yang mendekati tenggat waktu.
* **Quick Actions:** Akses cepat untuk membuat jadwal atau laporan.

### 2. Manajemen Audit (Auditor & Auditee)
* **Role Auditor:** Dapat membuat jadwal audit baru, memverifikasi laporan, dan menutup status audit (Close).
* **Role Unit (Auditee):** Dapat mengajukan permintaan audit dan melihat riwayat audit unit mereka.

### 3. Riwayat & Filter Data
* Filter data audit berdasarkan **Tahun**, **Bulan**, dan **Jenis Standar**.
* Pencatatan riwayat audit yang terstruktur per Unit Kerja.

### 4. Standar Audit Terintegrasi
Mendukung berbagai standar kepatuhan:
* ISO 9001:2015 (Sistem Manajemen Mutu)
* ISO 14001:2015 (Lingkungan)
* ISO 45001:2018 (K3)
* SMAP (ISO 37001 - Anti Penyuapan)
* SMK3
* Sistem Jaminan Halal (SJH)

---

## 🛠️ Teknologi yang Digunakan
* **Framework:** Laravel 10/11 (PHP)
* **Database:** PostgreSQL
* **Frontend:** Blade Templating, Bootstrap 5
* **Icons:** Bootstrap Icons
* **Server Environment:** Laragon / XAMPP

---

## ⚙️ Cara Instalasi (Localhost)

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer lokal:

1.  **Clone Repository**
    ```bash
    git clone [https://github.com/username-anda/nama-repo.git](https://github.com/username-anda/nama-repo.git)
    cd nama-repo
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Setup Environment**
    * Duplikat file `.env.example` menjadi `.env`.
    * Atur koneksi database di file `.env`:
    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=nama_database_kamu
    DB_USERNAME=postgres
    DB_PASSWORD=password_kamu
    ```

4.  **Generate Key & Migrate**
    ```bash
    php artisan key:generate
    php artisan migrate
    ```

5.  **Seeding Data (Penting!)**
    Jalankan perintah ini untuk membuat akun otomatis bagi 17 Unit Kerja PTPN 1 Regional 7:
    ```bash
    php artisan db:seed --class=UnitSeeder
    ```

6.  **Jalankan Aplikasi**
    ```bash
    npm run build
    php artisan serve
    ```
    Buka browser dan akses: `http://localhost:8000`

---

## 🔐 Akun Demo (Login)

Berikut adalah akun default yang digenerate oleh Seeder.

### 🏢 Akun Unit Kerja (Auditee)
Password untuk semua akun unit: **`12345678`**

| Unit Kerja | Email Login |
| :--- | :--- |
| **Unit Kedaton** | `kedaton@ptpn7.com` |
| **Unit Way Berulu** | `wayberulu@ptpn7.com` |
| **Unit Way Lima** | `waylima@ptpn7.com` |
| **Unit Bergen** | `bergen@ptpn7.com` |
| **Unit Baturaja** | `baturaja@ptpn7.com` |
| **Unit Pagaralam** | `pagaralam@ptpn7.com` |
| **Kantor Regional** | `kantorregional@ptpn7.com` |
| *(dan unit lainnya...)* | |

### 👨‍💼 Akun Auditor (Admin)
*(Jika belum dibuat di seeder, silakan registrasi manual atau buat lewat Tinker)*:
* **Email:** `admin@ptpn7.com` (Contoh)
* **Password:** `password`

---

## 📝 Catatan Pengembang
Projek ini dibuat sebagai bagian dari Kerja Praktik / Magang di PTPN 1 Regional 7.
* **Developer:** [Nama Kamu]
* **Instansi Asal:** Universitas Lampung
* **Tahun:** 2025

---"# Sistem-Audit" 
