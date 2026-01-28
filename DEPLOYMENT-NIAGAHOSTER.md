# 🚀 Panduan Deployment ke NiagaHoster

## Informasi Aplikasi
- **Nama**: Absensi MIFARE - SMK Negeri 10 Pandeglang
- **Framework**: Laravel 11
- **Database**: MySQL
- **PHP Version**: >= 8.2

---

## 📋 Persiapan Sebelum Deploy

### 1. Informasi yang Dibutuhkan
Siapkan informasi berikut dari NiagaHoster:
- [ ] Domain/subdomain (contoh: absensi.smkn10.sch.id)
- [ ] Username SSH
- [ ] Password SSH atau SSH Key
- [ ] Host SSH (biasanya: domainanda.com atau IP server)
- [ ] Port SSH (biasanya: 22 atau 65002)
- [ ] Database Name
- [ ] Database Username
- [ ] Database Password
- [ ] Database Host (biasanya: localhost)

### 2. Akses yang Diperlukan
- ✅ Akses SSH (untuk deployment otomatis)
- ✅ Akses cPanel/Plesk
- ✅ Akses phpMyAdmin atau MySQL

---

## 🎯 Metode Deployment

### Metode 1: Deployment via SSH (RECOMMENDED)

#### Langkah 1: Test Koneksi SSH
```bash
ssh -p 65002 username@domain.com
```

#### Langkah 2: Clone Repository
```bash
cd public_html
git clone https://github.com/xcyvrezz/presensi.git .
```

#### Langkah 3: Install Dependencies
```bash
composer install --optimize-autoloader --no-dev
```

#### Langkah 4: Setup Environment
```bash
cp .env.example .env
nano .env
```

Edit file .env dengan konfigurasi production:
```env
APP_NAME="Absensi MIFARE - SMK Negeri 10 Pandeglang"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=username_database_anda
DB_PASSWORD=password_database_anda

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

#### Langkah 5: Generate Application Key
```bash
php artisan key:generate
```

#### Langkah 6: Setup Database
```bash
php artisan migrate --force
php artisan db:seed --force
```

#### Langkah 7: Setup Storage & Cache
```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

#### Langkah 8: Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
```

---

### Metode 2: Deployment Manual via cPanel

#### Langkah 1: Upload Files
1. Buka cPanel → File Manager
2. Masuk ke folder `public_html`
3. Upload semua file project (kecuali folder `node_modules` dan `.git`)
4. Extract jika dalam bentuk ZIP

#### Langkah 2: Setup Composer
1. Buka Terminal di cPanel
2. Jalankan:
```bash
cd public_html
composer install --optimize-autoloader --no-dev
```

#### Langkah 3: Setup Environment
1. Copy file `.env.example` menjadi `.env`
2. Edit file `.env` sesuai konfigurasi production
3. Generate key: `php artisan key:generate`

#### Langkah 4: Setup Database
1. Buat database baru di cPanel → MySQL Databases
2. Catat nama database, username, dan password
3. Import database atau jalankan migration:
```bash
php artisan migrate --force
php artisan db:seed --force
```

#### Langkah 5: Setup Document Root
1. Buka cPanel → Domains
2. Set Document Root ke: `public_html/public`
3. Atau buat symlink jika tidak bisa ubah document root

---

## 🔧 Konfigurasi Khusus NiagaHoster

### 1. Setup Document Root
Jika menggunakan subdomain atau addon domain:
- Document Root harus mengarah ke folder `public`
- Contoh: `/home/username/public_html/public`

### 2. Setup .htaccess
Pastikan file `.htaccess` di folder `public` sudah ada dan berisi:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 3. PHP Version
- Pastikan PHP version minimal 8.2
- Set di cPanel → Select PHP Version

### 4. PHP Extensions Required
Pastikan extension berikut aktif:
- ✅ BCMath
- ✅ Ctype
- ✅ Fileinfo
- ✅ JSON
- ✅ Mbstring
- ✅ OpenSSL
- ✅ PDO
- ✅ Tokenizer
- ✅ XML
- ✅ cURL
- ✅ GD
- ✅ ZIP

---

## 🔐 Keamanan Production

### 1. Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
```

### 2. File Permissions
```bash
chmod -R 755 storage bootstrap/cache
chmod 644 .env
```

### 3. Disable Directory Listing
Tambahkan di `.htaccess`:
```apache
Options -Indexes
```

---

## 📝 Troubleshooting

### Error: 500 Internal Server Error
- Cek file `.env` sudah benar
- Cek permissions folder `storage` dan `bootstrap/cache`
- Cek error log di `storage/logs/laravel.log`

### Error: Database Connection
- Cek kredensial database di `.env`
- Pastikan database sudah dibuat
- Cek DB_HOST (biasanya `localhost`)

### Error: Mix Manifest Not Found
- Jalankan: `npm run build` di local
- Upload folder `public/build` ke server

### Error: Storage Link
- Jalankan: `php artisan storage:link`
- Atau buat symlink manual

---

## 🔄 Update Aplikasi

### Via SSH:
```bash
cd public_html
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📞 Support

Jika ada masalah:
1. Cek log error di `storage/logs/laravel.log`
2. Hubungi support NiagaHoster untuk masalah server
3. Dokumentasi Laravel: https://laravel.com/docs

---

**Dibuat dengan ❤️ menggunakan Claude Code**
