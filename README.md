# Sistem Monitoring Audit Internal - PTPN 1 Regional 7

![Dashboard Preview](public/image/audit-bg.jpg)
*(Ganti link gambar di atas dengan screenshot dashboard asli kamu nanti)*


Sistem Informasi Audit Internal adalah aplikasi berbasis web yang digunakan untuk membantu proses pelaksanaan audit internal perusahaan secara **digital, terpusat, dan paperless**.

Aplikasi ini dikembangkan menggunakan **Laravel 12** dan **PHP 8.2**, serta dirancang khusus untuk mempermudah kolaborasi antara **Auditor** dan **Auditee** dalam mengelola temuan audit, tindak lanjut, serta pelaporan.

---

## 📌 Fitur Utama

### 👤 Auditor
- Login sistem
- Mengelola standar audit
- Membuat audit baru
- Menambahkan temuan audit
- Edit/hapus temuan
- Verifikasi bukti perbaikan
- Reopen atau Close temuan
- Export laporan PDF
- Export laporan Excel
- Melihat riwayat audit

### 👤 Auditee
- Login sistem
- Melihat daftar audit
- Melihat temuan audit
- Memberikan respon/tindak lanjut
- Upload bukti (evidence)
- Memperbarui status perbaikan

---

## 🏗️ Teknologi yang Digunakan

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 12 |
| Bahasa | PHP 8.2+ |
| Database | MySQL / MariaDB |
| Frontend | Blade + Bootstrap |
| Export PDF | barryvdh/laravel-dompdf |
| Export Excel | maatwebsite/excel |
| Auth | Laravel Authentication |

---

## ⚙️ System Requirements

### Server
- PHP ≥ 8.2
- Composer
- MySQL/MariaDB
- Apache/Nginx
- Node.js (optional untuk asset build)

### Client
- Browser modern (Chrome/Edge/Firefox)
- Koneksi internet

---

## 🚀 Cara Instalasi

Ikuti langkah berikut untuk menjalankan project di lokal:

### 1️⃣ Clone Repository
```bash
git clone <url-repository>
cd nama-project
