# Quick Reference: URL Configuration

## 🚀 Quick Start

### Option 1: Automated Setup (Windows)
```bash
# Jalankan helper script
setup-url.bat
```

### Option 2: Manual Configuration

1. **Copy environment file:**
   ```bash
   copy .env.example .env
   ```

2. **Edit `.env` file:**
   ```env
   APP_URL=http://localhost/absensi-mifare/public
   ASSET_URL=
   ```

3. **Clear cache:**
   ```bash
   php artisan optimize:clear
   ```

---

## 📝 Common Configurations

### XAMPP Subdirectory
```env
APP_URL=http://localhost/absensi-mifare/public
ASSET_URL=http://localhost/absensi-mifare/public
```
**Also update** `public/.htaccess`:
```apache
RewriteBase /absensi-mifare/public/
```

### XAMPP Virtual Host
```env
APP_URL=http://absensi.local
ASSET_URL=
```
**Keep** `public/.htaccess` RewriteBase commented.

### Artisan Serve
```env
APP_URL=http://localhost:8000
ASSET_URL=
```

### Production (Root Domain)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://absensi.smkn10.sch.id
ASSET_URL=
```

### Production (Subdirectory)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://smkn10.sch.id/absensi
ASSET_URL=https://smkn10.sch.id/absensi
```

---

## 🔧 Troubleshooting

| Problem | Solution |
|---------|----------|
| Assets tidak load | 1. Cek `APP_URL` di `.env`<br>2. Run: `php artisan config:clear` |
| 404 pada routes | 1. Cek `.htaccess` ada di `public/`<br>2. Enable mod_rewrite Apache |
| Mixed content (HTTP/HTTPS) | 1. Set `APP_URL=https://...`<br>2. Run: `php artisan config:clear` |

---

## 📚 Documentation

- **Full Guide:** [URL_CONFIGURATION_GUIDE.md](URL_CONFIGURATION_GUIDE.md)
- **Environment Template:** [.env.example](.env.example)

---

## ✅ After Changing .env

Always run:
```bash
php artisan config:clear
php artisan cache:clear
```

Or use:
```bash
php artisan optimize:clear
```

---

**Last Updated:** 2025-12-26
