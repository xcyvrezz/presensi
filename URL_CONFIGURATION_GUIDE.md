# URL Configuration Guide

Panduan lengkap untuk mengkonfigurasi Base URL pada aplikasi Absensi MIFARE agar dapat berjalan di berbagai environment (development, staging, production).

## 📋 Daftar Isi

- [Konsep Dasar](#konsep-dasar)
- [Konfigurasi Development](#konfigurasi-development)
- [Konfigurasi Production](#konfigurasi-production)
- [Troubleshooting](#troubleshooting)

---

## Konsep Dasar

Aplikasi Laravel menggunakan **dynamic URL generation** yang artinya semua URL, link, dan asset path dibuat secara otomatis berdasarkan konfigurasi `APP_URL` di file `.env`.

### File-file yang Terlibat:

1. **`.env`** - File konfigurasi utama (tidak di-commit ke Git)
2. **`.env.example`** - Template konfigurasi untuk referensi
3. **`config/app.php`** - File konfigurasi aplikasi (sudah dinamis)
4. **`public/.htaccess`** - Apache rewrite rules (sudah dioptimalkan)

---

## Konfigurasi Development

### 1. XAMPP dengan Subdirectory

Jika aplikasi berada di `D:\xampp\htdocs\absensi-mifare`:

**File `.env`:**
```env
APP_URL=http://localhost/absensi-mifare/public
ASSET_URL=http://localhost/absensi-mifare/public
```

**File `public/.htaccess`:**
Uncomment baris RewriteBase:
```apache
RewriteBase /absensi-mifare/public/
```

**Akses:**
```
http://localhost/absensi-mifare/public
```

---

### 2. XAMPP dengan Virtual Host (Root Domain)

**Setup Virtual Host** di `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    ServerName absensi.local
    DocumentRoot "D:/xampp/htdocs/absensi-mifare/public"
    <Directory "D:/xampp/htdocs/absensi-mifare/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Edit hosts file** `C:\Windows\System32\drivers\etc\hosts`:
```
127.0.0.1 absensi.local
```

**File `.env`:**
```env
APP_URL=http://absensi.local
ASSET_URL=
```

**File `public/.htaccess`:**
Pastikan RewriteBase di-comment:
```apache
# RewriteBase /absensi-mifare/public/
```

**Akses:**
```
http://absensi.local
```

---

### 3. Laravel Artisan Serve

**Jalankan server:**
```bash
php artisan serve
```

**File `.env`:**
```env
APP_URL=http://localhost:8000
ASSET_URL=
```

**Akses:**
```
http://localhost:8000
```

---

### 4. Laravel Valet (macOS/Linux)

**Setup Valet:**
```bash
cd D:\xampp\htdocs\absensi-mifare
valet link absensi
```

**File `.env`:**
```env
APP_URL=http://absensi.test
ASSET_URL=
```

**Akses:**
```
http://absensi.test
```

---

## Konfigurasi Production

### 1. Shared Hosting dengan cPanel

**Struktur folder:**
```
/home/username/
  ├─ public_html/           (Document Root)
  │   ├─ index.php          (symlink atau copy dari public/)
  │   ├─ .htaccess
  │   └─ assets/
  └─ absensi-mifare/        (aplikasi utama, di luar public_html)
      ├─ app/
      ├─ config/
      ├─ database/
      └─ ...
```

**File `.env`:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://absensi.smkn10.sch.id
ASSET_URL=
```

**File `public/.htaccess`:**
Pastikan RewriteBase di-comment atau sesuaikan:
```apache
# RewriteBase /
```

---

### 2. VPS/Dedicated Server dengan Nginx

**Nginx Config** (`/etc/nginx/sites-available/absensi`):
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name absensi.smkn10.sch.id;
    root /var/www/absensi-mifare/public;

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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**File `.env`:**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://absensi.smkn10.sch.id
ASSET_URL=
```

**Enable SSL dengan Let's Encrypt:**
```bash
sudo certbot --nginx -d absensi.smkn10.sch.id
```

---

### 3. Subdirectory di Production

Jika aplikasi diakses via `https://smkn10.sch.id/absensi`:

**File `.env`:**
```env
APP_URL=https://smkn10.sch.id/absensi
ASSET_URL=https://smkn10.sch.id/absensi
```

**File `public/.htaccess`:**
```apache
RewriteBase /absensi/
```

---

## Verifikasi Konfigurasi

### 1. Cek URL yang Dihasilkan

Buat route test di `routes/web.php`:
```php
Route::get('/test-url', function () {
    return [
        'APP_URL' => config('app.url'),
        'ASSET_URL' => config('app.asset_url'),
        'Current URL' => url()->current(),
        'Route URL' => route('login'),
        'Asset URL' => asset('css/app.css'),
    ];
});
```

Akses: `http://your-domain.com/test-url`

### 2. Clear Cache

Setiap kali mengubah `.env`, jalankan:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

Atau gunakan helper:
```bash
php artisan optimize:clear
```

---

## Troubleshooting

### ❌ Problem: Assets (CSS/JS) tidak load

**Solusi:**
1. Cek `APP_URL` di `.env` sudah sesuai dengan URL akses
2. Clear cache: `php artisan config:clear`
3. Cek `ASSET_URL` - boleh dikosongkan untuk menggunakan `APP_URL`
4. Pastikan folder `public/` dapat diakses

### ❌ Problem: 404 pada semua route kecuali homepage

**Solusi:**
1. Pastikan mod_rewrite Apache enabled:
   ```bash
   # Di Ubuntu/Debian:
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```
2. Pastikan `.htaccess` ada di folder `public/`
3. Pastikan `AllowOverride All` di virtual host configuration
4. Untuk Nginx, pastikan konfigurasi `try_files` sudah benar

### ❌ Problem: Mixed Content (HTTP/HTTPS)

Jika production menggunakan HTTPS tapi asset load via HTTP:

**Solusi:**
1. Set `APP_URL` ke `https://`:
   ```env
   APP_URL=https://yourdomain.com
   ```

2. Tambahkan di `app/Providers/AppServiceProvider.php`:
   ```php
   public function boot()
   {
       if ($this->app->environment('production')) {
           \URL::forceScheme('https');
       }
   }
   ```

3. Clear cache: `php artisan config:clear`

### ❌ Problem: URL salah saat di belakang proxy/load balancer

**Solusi:**
Tambahkan di `app/Http/Middleware/TrustProxies.php`:
```php
protected $proxies = '*';
```

---

## Best Practices

### ✅ DO:
- ✓ Selalu set `APP_URL` sesuai dengan URL akses sebenarnya
- ✓ Gunakan HTTPS di production
- ✓ Clear cache setiap kali mengubah `.env`
- ✓ Gunakan Laravel helpers: `url()`, `route()`, `asset()`
- ✓ Set `APP_DEBUG=false` di production
- ✓ Backup file `.env` secara terpisah (jangan commit ke Git)

### ❌ DON'T:
- ✗ Jangan hardcode URL di code
- ✗ Jangan commit file `.env` ke Git
- ✗ Jangan lupa trailing slash di `APP_URL` (salah: `http://example.com/`)
- ✗ Jangan gunakan IP address di `APP_URL` production

---

## Helper Functions

Laravel menyediakan helper functions untuk generate URL dinamis:

```php
// Base URL
url('/');                    // http://yourdomain.com
url('/about');              // http://yourdomain.com/about

// Named Routes
route('login');             // http://yourdomain.com/login
route('students.show', 1);  // http://yourdomain.com/students/1

// Assets
asset('css/app.css');       // http://yourdomain.com/css/app.css
asset('js/app.js');         // http://yourdomain.com/js/app.js

// Secure URL (force HTTPS)
secure_url('/');            // https://yourdomain.com
secure_asset('css/app.css'); // https://yourdomain.com/css/app.css
```

---

## Checklist Deployment

Sebelum deploy ke production:

- [ ] Update `APP_URL` di `.env` production
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate APP_KEY: `php artisan key:generate`
- [ ] Optimize autoloader: `composer install --optimize-autoloader --no-dev`
- [ ] Cache config: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache views: `php artisan view:cache`
- [ ] Set proper file permissions (755 untuk folder, 644 untuk file)
- [ ] Set `storage/` dan `bootstrap/cache/` writable
- [ ] Setup SSL/HTTPS
- [ ] Test semua URL dan asset loading

---

## Kontak Support

Jika masih mengalami masalah dengan konfigurasi URL:

1. Cek dokumentasi Laravel: https://laravel.com/docs/configuration
2. Cek file log: `storage/logs/laravel.log`
3. Enable debug mode sementara (development only): `APP_DEBUG=true`

---

**Dibuat:** 2025-12-26
**Versi:** 1.0
**Aplikasi:** Absensi MIFARE - SMK Negeri 10 Pandeglang
