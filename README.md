# Sinapsis Backend API

Sinapsis Backend adalah RESTful API yang dibangun menggunakan framework **Laravel 12** untuk mendukung aplikasi Sinapsis. Repositori ini menyediakan endpoint API untuk otentikasi, manajemen catatan (notes), folder, tag, tautan catatan (note links), unggahan lampiran (attachments), serta alat bantu belajar berbasis AI (study tools) dengan WebSocket real-time.

---

## ⚡ Tech Stack

*   **Runtime:** PHP >= 8.2
*   **Framework:** Laravel 12
*   **Database:** PostgreSQL (Direkomendasikan) atau SQLite
*   **Otentikasi:** Laravel Sanctum (Token-based) & Laravel Socialite (Google OAuth)
*   **Real-time / WebSockets:** Laravel Reverb & Laravel Echo
*   **Asset Bundler:** Vite & Tailwind CSS 4
*   **Testing:** Pest PHP 3
*   **Code Style Formatter:** Laravel Pint

---

## 🚀 Panduan Instalasi & Setup

Berikut adalah langkah-langkah untuk menyiapkan dan menjalankan proyek di lingkungan lokal Anda.

### 1. Prasyarat (Prerequisites)
Pastikan Anda sudah menginstal perangkat lunak berikut di perangkat Anda:
*   PHP >= 8.2 (Disarankan PHP 8.3) dengan ekstensi yang diperlukan (seperti `pdo_pgsql`, `openssl`, `mbstring`, dll.)
*   Composer (Dependency manager untuk PHP)
*   Node.js (LTS) & npm (Untuk asset compilation & concurrently runner)
*   Database Server (PostgreSQL atau SQLite)

## 💻 Cara Menjalankan Aplikasi

Kami menyediakan runner concurrent untuk menjalankan semua service dev server sekaligus.

### 1. Menjalankan Server Pengembangan (Lengkap)
Untuk menjalankan **Laravel server**, **Queue listener**, dan **Vite dev server** secara bersamaan dalam satu terminal, gunakan perintah:
```bash
git clone https://github.com/emrsyah/sinapsis_backend.git
cd sinapsis_backend
```

Kemudian install dependency untuk laravel:

```bash
composer install
```

### 2. Menjalankan Server Secara Terpisah
Jika Anda ingin menjalankannya secara terpisah di terminal yang berbeda:

*   **Menjalankan Laravel API Server:**
    ```bash
    php artisan serve
    ```
    API akan dapat diakses secara default di: [http://127.0.0.1:8000](http://127.0.0.1:8000)

*   **Menjalankan Laravel Reverb (WebSocket / Real-time):**
    Diperlukan jika aplikasi frontend membutuhkan sinkronisasi real-time (seperti fitur kolaborasi atau deteksi ketikan):
    ```bash
    php artisan reverb:start
    ```

*   **Menjalankan Queue Worker:**
    Diperlukan untuk memproses job di latar belakang (seperti AI study tools generation):
    ```bash
    php artisan queue:listen --tries=1
    ```

