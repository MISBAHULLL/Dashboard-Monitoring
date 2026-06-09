# Monitoring Task Product Owner (PO) - Trustmedis

[![Laravel](https://img.shields.io/badge/Laravel-13.x-red.svg)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-green.svg)](https://vuejs.org)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-3.x-purple.svg)](https://inertiajs.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-4.x-blue.svg)](https://tailwindcss.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4.svg)](https://www.php.net)

Aplikasi monitoring dan manajemen task berbasis web untuk Product Owner yang dikembangkan menggunakan stack modern Laravel, Vue.js, Inertia.js, dan TailwindCSS. Sistem ini dirancang untuk membantu tim dalam pencatatan, pemantauan, penugasan, dan evaluasi task dengan fitur-fitur lengkap seperti role-based access control, SLA tracking, notifikasi real-time, dashboard analytics, dan manajemen dokumen.

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Seeding Data](#-seeding-data)
- [Testing](#-testing)
- [Struktur Database](#-struktur-database)
- [Akun Default](#-akun-default)
- [Penggunaan](#-penggunaan)
- [Backup & Restore](#-backup--restore)
- [Troubleshooting](#-troubleshooting)
- [Lisensi](#-lisensi)

## ✨ Fitur Utama

### 🔐 Autentikasi & Otorisasi
- Login/Logout dengan Laravel Fortify
- Role-based access control (Admin & Member)
- Two-Factor Authentication (2FA)
- Password reset
- Session management

### 👥 Manajemen Master Data
- **Users**: Kelola akun admin dan member
- **Teams**: Kelola tim/divisi
- **Clients**: Kelola data klien
- **Task Templates**: Template task untuk mempercepat pembuatan task

### 📊 Monitoring Task
- **CRUD Task**: Tambah, lihat, edit, dan hapus task
- **Kanban Board**: Visualisasi task berdasarkan status (Open, In Progress, Revision, Completed, Cancelled)
- **Filter & Search**: Filter berdasarkan status, kategori, prioritas, client, team, dan assigned user
- **Bulk Actions**: Update status dan assignment untuk multiple tasks
- **Import/Export**: Import task dari CSV/Excel dan export data task
- **Task Assignment**: Tugaskan task kepada member
- **Task Comments**: Diskusi dan kolaborasi dengan fitur komentar dan reply
- **Pin Comments**: Admin dapat pin komentar penting

### 📈 Dashboard & Analytics
- **Admin Dashboard**: 
  - Statistik task (total, completed, in progress, overdue)
  - Chart distribusi task per status
  - Team performance metrics
  - Recent activities
  - Task trends
- **Member Dashboard**: 
  - Task yang ditugaskan
  - Task mendekati deadline
  - Task prioritas tinggi
  - Personal statistics

### ⏱️ SLA (Service Level Agreement) Tracking
- Konfigurasi SLA berdasarkan kategori task
- Perhitungan otomatis due date dan warning date
- Indikator status: On Track, Warning, Overdue, Completed On Time, Completed Late
- Notifikasi otomatis untuk task mendekati deadline

### 📄 Manajemen Dokumen
- Upload dan kelola dokumen terkait client
- Document versioning (riwayat versi dokumen)
- Link dokumen dengan multiple tasks
- Tipe dokumen: MOU, Addendum, Amendment, SPK, BAST, Invoice, dll
- Preview dan download dokumen

### 🔔 Sistem Notifikasi
- Notifikasi real-time untuk:
  - Task assignment
  - Perubahan status task
  - Komentar baru
  - Deadline mendekat
  - Task overdue
- Notification bell dengan badge counter
- Mark as read dan dismiss notification

### 📝 Activity Log
- Pencatatan otomatis aktivitas penting:
  - Create, update, delete data
  - Perubahan status task
  - Assignment task
  - Upload dokumen
- Filter berdasarkan user, modul, dan tipe aktivitas
- Audit trail lengkap

### 🔍 Fitur Pencarian
- Global search untuk task, client, team, dan user
- Search berdasarkan nama, deskripsi, dan tag

### 💾 Backup & Restore
- Backup database otomatis
- Download backup file
- Restore dari backup

## 🛠️ Teknologi yang Digunakan

### Backend
- **Laravel 13.x** - PHP Framework
- **Laravel Fortify** - Authentication
- **Laravel Wayfinder** - Routing untuk Inertia
- **Spatie Laravel Backup** - Database backup
- **Spatie Simple Excel** - Import/Export Excel
- **MySQL** - Database

### Frontend
- **Vue.js 3.x** - JavaScript Framework
- **Inertia.js 3.x** - SPA Framework
- **TailwindCSS 4.x** - CSS Framework
- **Reka UI** - Vue Component Library
- **VueUse** - Vue Composition Utilities
- **Lucide Icons** - Icon Library
- **ApexCharts** - Charts & Graphs
- **Vue Sonner** - Toast Notifications
- **TypeScript** - Type Safety

### Testing
- **Pest PHP** - PHP Testing Framework
- **Vitest** - JavaScript Testing Framework

### Development Tools
- **Vite** - Build Tool
- **Laravel Pint** - PHP Code Style
- **ESLint** - JavaScript Linter
- **Prettier** - Code Formatter
- **Concurrently** - Run multiple commands

## 💻 Persyaratan Sistem

Pastikan sistem Anda memenuhi persyaratan berikut:

- **PHP**: >= 8.3
- **Composer**: >= 2.x
- **Node.js**: >= 18.x
- **NPM**: >= 9.x
- **MySQL**: >= 8.0 atau MariaDB >= 10.3
- **Web Server**: Apache atau Nginx
- **PHP Extensions**:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - Zip

## 📥 Instalasi

### 1. Clone Repository

```bash
git clone <repository-url>
cd monitoring-task-po
```

### 2. Install Dependencies PHP

```bash
composer install
```

### 3. Install Dependencies JavaScript

```bash
npm install
```

### 4. Setup Environment

Copy file `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Setup Database

Buat database MySQL baru:

```sql
CREATE DATABASE monitoring_task_po;
```

Update konfigurasi database di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=monitoring_task_po
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 7. Jalankan Migration

```bash
php artisan migrate
```

### 8. Jalankan Seeder (Opsional)

Untuk mengisi database dengan data dummy:

```bash
php artisan db:seed
```

### 9. Create Storage Link

```bash
php artisan storage:link
```

### 10. Build Assets

Untuk development:

```bash
npm run dev
```

Untuk production:

```bash
npm run build
```

## ⚙️ Konfigurasi

### Konfigurasi Aplikasi

Edit file `.env` untuk menyesuaikan konfigurasi aplikasi:

```env
# Nama Aplikasi
APP_NAME="Monitoring Task PO"

# Environment (local/production)
APP_ENV=local

# Debug Mode (true/false)
APP_DEBUG=true

# URL Aplikasi
APP_URL=http://localhost:8000
```

### Konfigurasi Email (Opsional)

Untuk mengaktifkan fitur email notifikasi:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS="noreply@monitoring-task.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### Konfigurasi Queue

Untuk notifikasi background processing:

```env
QUEUE_CONNECTION=database
```

### Konfigurasi Session

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### Konfigurasi Cache

```env
CACHE_STORE=database
```

## 🚀 Menjalankan Aplikasi

### Development Mode

#### Cara 1: Menggunakan Script Dev (Rekomendasi)

Script ini akan menjalankan Laravel server, queue worker, dan Vite secara bersamaan:

```bash
composer run dev
```

#### Cara 2: Manual (3 Terminal)

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Queue Worker:**
```bash
php artisan queue:work
```

**Terminal 3 - Vite Dev Server:**
```bash
npm run dev
```

Aplikasi akan berjalan di `http://localhost:8000`

### Production Mode

#### Build Assets

```bash
npm run build
```

#### Optimize Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Jalankan Queue Worker

```bash
php artisan queue:work --daemon
```

#### Setup Web Server

Configure Apache atau Nginx untuk mengarah ke folder `public/` sebagai document root.

**Contoh Nginx:**

```nginx
server {
    listen 80;
    server_name monitoring-task.local;
    root /path/to/monitoring-task-po/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 🌱 Seeding Data

### Menjalankan Seeder

```bash
php artisan db:seed
```

### Seeder yang Tersedia

Anda dapat membuat seeder untuk:

1. **UserSeeder** - Create admin dan member accounts
2. **TeamSeeder** - Create teams
3. **ClientSeeder** - Create clients
4. **TaskSeeder** - Create sample tasks
5. **DocumentSeeder** - Create sample documents
6. **SlaConfigSeeder** - Create SLA configurations

### Membuat Seeder Custom

```bash
php artisan make:seeder UserSeeder
```

## 🧪 Testing

### Backend Testing (Pest)

Jalankan semua test:

```bash
php artisan test
```

Jalankan test dengan coverage:

```bash
php artisan test --coverage
```

Jalankan test spesifik:

```bash
php artisan test --filter TaskTest
```

### Frontend Testing (Vitest)

Jalankan test:

```bash
npm run test
```

Watch mode:

```bash
npm run test:watch
```

With coverage:

```bash
npm run test:coverage
```

### Linting & Formatting

**PHP (Laravel Pint):**

```bash
# Check
composer run lint:check

# Fix
composer run lint
```

**JavaScript (ESLint):**

```bash
# Check
npm run lint:check

# Fix
npm run lint
```

**Format (Prettier):**

```bash
# Check
npm run format:check

# Fix
npm run format
```

**Type Check (TypeScript):**

```bash
npm run types:check
```

### CI Check

Jalankan semua checks sebelum commit:

```bash
composer run ci:check
```

## 🗄️ Struktur Database

### Tabel Utama

1. **users** - Data user (admin & member)
2. **teams** - Data tim/divisi
3. **clients** - Data klien
4. **tasks** - Data task
5. **task_templates** - Template task
6. **task_comments** - Komentar pada task
7. **documents** - Data dokumen
8. **document_versions** - Versi dokumen
9. **document_task** - Pivot table dokumen dan task
10. **sla_configs** - Konfigurasi SLA
11. **notifications** - Notifikasi user
12. **activity_logs** - Log aktivitas
13. **release_date_logs** - Log perubahan release date
14. **tags** - Tags untuk task

### ERD (Entity Relationship Diagram)

```
users (1) --- (*) tasks [created_by]
users (1) --- (*) tasks [assigned_to]
teams (1) --- (*) tasks
clients (1) --- (*) tasks
clients (1) --- (*) documents
tasks (1) --- (*) task_comments
tasks (*) --- (*) documents [pivot: document_task]
documents (1) --- (*) document_versions
sla_configs (1) --- (*) tasks [via category]
users (1) --- (*) notifications
users (1) --- (*) activity_logs
tasks (1) --- (*) release_date_logs
tasks (*) --- (*) tags [polymorphic]
```

## 🔑 Akun Default

Setelah menjalankan seeder, gunakan akun berikut untuk login:

### Admin Account
- **Email**: admin@trustmedis.com
- **Password**: password
- **Role**: Admin
- **Akses**: Full access ke semua fitur

### Member Account
- **Email**: member@trustmedis.com
- **Password**: password
- **Role**: Member
- **Akses**: Terbatas pada task yang ditugaskan

> **Catatan**: Segera ubah password default setelah login pertama kali!

## 📖 Penggunaan

### Role Admin

Admin memiliki akses penuh untuk:

1. **Dashboard**
   - Lihat statistik keseluruhan task
   - Analisis performance tim
   - Monitor task trends

2. **Task Management**
   - Create, read, update, delete task
   - Assign task ke member
   - Update status task
   - Bulk actions (update status, assign multiple tasks)
   - Import task dari CSV/Excel
   - Export task

3. **Kanban Board**
   - Visualisasi task per status
   - Drag & drop untuk update status
   - Filter dan search task

4. **Master Data**
   - Kelola users (create, edit, delete, reset password)
   - Kelola teams
   - Kelola clients
   - Kelola task templates

5. **Documents**
   - Upload dokumen
   - Kelola versi dokumen
   - Link dokumen dengan task

6. **SLA Configuration**
   - Setup SLA per kategori task
   - Monitor SLA compliance

7. **Activity Log**
   - Lihat semua aktivitas sistem
   - Filter berdasarkan user dan modul

8. **Settings**
   - Profile management
   - Security settings (change password, 2FA)
   - Backup & restore

### Role Member

Member memiliki akses terbatas:

1. **Dashboard**
   - Lihat task yang ditugaskan
   - Monitor personal statistics

2. **My Tasks**
   - Lihat task yang assigned ke diri sendiri
   - Update status task sendiri (Progress, Revision, Completed)
   - Tambah komentar pada task

3. **Task Detail**
   - Lihat detail task
   - Lihat dokumen terkait
   - Diskusi via comments

4. **Notifications**
   - Terima notifikasi task assignment
   - Notifikasi perubahan status
   - Notifikasi komentar baru
   - Notifikasi deadline

5. **Settings**
   - Update profile
   - Change password
   - Enable 2FA

### Workflow Task

1. **Pembuatan Task**
   - Admin membuat task baru
   - Isi informasi: nama, kategori, prioritas, client, team
   - Set due date atau gunakan SLA otomatis
   - Upload dokumen terkait (opsional)

2. **Assignment**
   - Admin assign task ke member
   - Member menerima notifikasi

3. **Pengerjaan**
   - Member update status menjadi "In Progress"
   - Member menambahkan komentar untuk update progress
   - Admin dapat monitor melalui dashboard atau kanban

4. **Review**
   - Member update status menjadi "Completed" atau "Revision"
   - Admin melakukan review
   - Jika perlu revisi, status kembali ke "Revision"

5. **Completion**
   - Admin mengubah status menjadi "Completed"
   - Task masuk ke history
   - SLA tracking mencatat on time/late

## 💾 Backup & Restore

### Manual Backup

Backup database:

```bash
php artisan backup:run --only-db
```

Backup files dan database:

```bash
php artisan backup:run
```

### Scheduled Backup

Edit `app/Console/Kernel.php` atau `routes/console.php` untuk schedule otomatis:

```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:run --only-db')
    ->daily()
    ->at('02:00');
```

**Development:** Jalankan scheduler secara lokal:

```bash
php artisan schedule:work
```

**Production:** Setup cron job di server:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

> **Catatan**: 
> - `schedule:work` untuk testing lokal (runs every minute, watches for scheduled tasks)
> - `schedule:run` untuk production via cron job (executed by cron every minute)

### Restore Database

```bash
# Import dari backup SQL
mysql -u username -p database_name < backup.sql
```

### Backup Location

Backup disimpan di folder `storage/app/backups/`

### Clean Old Backups

```bash
php artisan backup:clean
```

## 🔧 Troubleshooting

### Error: "No application encryption key has been specified"

```bash
php artisan key:generate
```

### Error: "Class not found"

```bash
composer dump-autoload
```

### Error: Storage link tidak berfungsi

```bash
php artisan storage:link
```

### Error: Permission denied pada storage/logs

```bash
# Linux/Mac
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Atau
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
```

### Error: Vite not found atau assets tidak load

```bash
# Hapus node_modules dan install ulang
rm -rf node_modules package-lock.json
npm install
npm run dev
```

### Queue tidak berjalan

Pastikan queue worker running:

```bash
php artisan queue:work
```

Atau restart queue:

```bash
php artisan queue:restart
```

> **Perbedaan Queue Commands:**
> - `queue:work` - Menjalankan queue worker (process jobs in background)
> - `queue:listen` - Sama seperti work tapi restart otomatis setiap job
> - `queue:restart` - Gracefully restart semua queue workers
> - `queue:retry` - Retry failed jobs

### Database migration error

Reset dan migrate ulang:

```bash
php artisan migrate:fresh
```

Dengan seeding:

```bash
php artisan migrate:fresh --seed
```

### Clear Cache

```bash
# Clear semua cache
php artisan optimize:clear

# Atau clear satu per satu
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Error koneksi database

1. Pastikan MySQL service running
2. Cek konfigurasi `.env`:
   - DB_HOST
   - DB_PORT
   - DB_DATABASE
   - DB_USERNAME
   - DB_PASSWORD
3. Test koneksi:

```bash
php artisan tinker
DB::connection()->getPdo();
```

### NPM install error

Update npm:

```bash
npm install -g npm@latest
```

Clear npm cache:

```bash
npm cache clean --force
```

### Composer install error

Update composer:

```bash
composer self-update
```

Clear composer cache:

```bash
composer clear-cache
```

### Port sudah digunakan

Gunakan port lain:

```bash
php artisan serve --port=8001
```

## 📁 Struktur Folder

```
monitoring-task-po/
├── app/
│   ├── Actions/          # Fortify Actions
│   ├── Concerns/         # Shared Traits
│   ├── Http/
│   │   ├── Controllers/  # Controllers
│   │   ├── Middleware/   # Middleware
│   │   └── Requests/     # Form Requests
│   ├── Models/           # Eloquent Models
│   ├── Policies/         # Authorization Policies
│   ├── Providers/        # Service Providers
│   └── Services/         # Business Logic Services
├── bootstrap/
│   └── ssr/              # SSR Build Output
├── database/
│   ├── migrations/       # Database Migrations
│   ├── seeders/          # Database Seeders
│   └── factories/        # Model Factories
├── public/               # Public Assets
├── resources/
│   ├── css/              # CSS Files
│   ├── js/
│   │   ├── components/   # Vue Components
│   │   ├── layouts/      # Layout Components
│   │   ├── pages/        # Page Components
│   │   ├── lib/          # Utilities
│   │   └── types/        # TypeScript Types
│   └── views/            # Blade Templates
├── routes/
│   ├── web.php           # Web Routes
│   ├── api.php           # API Routes
│   └── console.php       # Console Commands
├── storage/
│   ├── app/              # Application Storage
│   ├── framework/        # Framework Storage
│   └── logs/             # Log Files
├── tests/                # Test Files
├── .env.example          # Environment Example
├── composer.json         # PHP Dependencies
├── package.json          # JS Dependencies
├── vite.config.ts        # Vite Configuration
└── tailwind.config.js    # Tailwind Configuration
```

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 Changelog

### Version 1.0.0 (Juni 2026)
- Initial release
- Full CRUD Task Management
- Role-based access control
- SLA Tracking
- Kanban Board
- Dashboard Analytics
- Document Management
- Notification System
- Activity Log
- Backup & Restore

## 📄 Lisensi

This project is licensed under the MIT License.

## 👥 Team

**Developed by**: Trustmedis Team  
**Project Period**: Februari 2026 - Juni 2026  
**Contact**: support@trustmedis.com

**Made with ❤️ using Laravel, Vue.js, Inertia.js, and TailwindCSS**
