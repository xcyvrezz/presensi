@echo off
REM ============================================================================
REM URL Configuration Helper
REM Absensi MIFARE - SMK Negeri 10 Pandeglang
REM ============================================================================

echo.
echo ============================================================================
echo   URL Configuration Helper - Absensi MIFARE
echo ============================================================================
echo.

REM Check if .env exists
if not exist ".env" (
    echo [INFO] File .env tidak ditemukan.
    echo [INFO] Menyalin .env.example ke .env...
    copy .env.example .env
    echo [SUCCESS] File .env berhasil dibuat!
    echo.
)

echo Pilih environment Anda:
echo.
echo 1. Development - XAMPP Subdirectory (http://localhost/absensi-mifare/public)
echo 2. Development - XAMPP Virtual Host (http://absensi.local)
echo 3. Development - Artisan Serve (http://localhost:8000)
echo 4. Production - Domain/VPS
echo 5. Custom - Input manual
echo 0. Cancel
echo.

set /p choice="Pilihan Anda (0-5): "

if "%choice%"=="0" goto :cancel
if "%choice%"=="1" goto :xampp_subdir
if "%choice%"=="2" goto :xampp_vhost
if "%choice%"=="3" goto :artisan_serve
if "%choice%"=="4" goto :production
if "%choice%"=="5" goto :custom

echo [ERROR] Pilihan tidak valid!
goto :end

:xampp_subdir
echo.
echo [INFO] Konfigurasi untuk XAMPP Subdirectory...
set APP_URL=http://localhost/absensi-mifare/public
set ASSET_URL=http://localhost/absensi-mifare/public
set REWRITE_BASE_NEEDED=yes
goto :apply_config

:xampp_vhost
echo.
set /p domain="Masukkan domain virtual host (default: absensi.local): "
if "%domain%"=="" set domain=absensi.local
echo [INFO] Konfigurasi untuk Virtual Host: %domain%...
set APP_URL=http://%domain%
set ASSET_URL=
set REWRITE_BASE_NEEDED=no
goto :apply_config

:artisan_serve
echo.
set /p port="Masukkan port (default: 8000): "
if "%port%"=="" set port=8000
echo [INFO] Konfigurasi untuk Artisan Serve...
set APP_URL=http://localhost:%port%
set ASSET_URL=
set REWRITE_BASE_NEEDED=no
goto :apply_config

:production
echo.
set /p prod_url="Masukkan URL production (contoh: https://absensi.smkn10.sch.id): "
if "%prod_url%"=="" (
    echo [ERROR] URL tidak boleh kosong!
    goto :end
)
echo [INFO] Konfigurasi untuk Production...
set APP_URL=%prod_url%
set ASSET_URL=
set REWRITE_BASE_NEEDED=no

echo.
set /p set_prod_env="Set APP_ENV=production dan APP_DEBUG=false? (y/n): "
if /i "%set_prod_env%"=="y" (
    set SET_PRODUCTION=yes
) else (
    set SET_PRODUCTION=no
)
goto :apply_config

:custom
echo.
set /p custom_url="Masukkan APP_URL: "
if "%custom_url%"=="" (
    echo [ERROR] URL tidak boleh kosong!
    goto :end
)
set /p custom_asset="Masukkan ASSET_URL (kosongkan jika sama dengan APP_URL): "
echo [INFO] Konfigurasi custom...
set APP_URL=%custom_url%
set ASSET_URL=%custom_asset%
set REWRITE_BASE_NEEDED=ask
goto :apply_config

:apply_config
echo.
echo ============================================================================
echo   Konfigurasi yang akan diterapkan:
echo ============================================================================
echo   APP_URL     : %APP_URL%
if "%ASSET_URL%"=="" (
    echo   ASSET_URL   : ^(otomatis menggunakan APP_URL^)
) else (
    echo   ASSET_URL   : %ASSET_URL%
)
echo ============================================================================
echo.

set /p confirm="Lanjutkan? (y/n): "
if /i not "%confirm%"=="y" goto :cancel

REM Update .env file
echo [INFO] Mengupdate file .env...

REM Backup .env
copy .env .env.backup.%date:~-4%%date:~-7,2%%date:~-10,2%_%time:~0,2%%time:~3,2%%time:~6,2%

REM Update APP_URL
powershell -Command "(gc .env) -replace '^APP_URL=.*', 'APP_URL=%APP_URL%' | Out-File -encoding ASCII .env"

REM Update ASSET_URL
if "%ASSET_URL%"=="" (
    powershell -Command "(gc .env) -replace '^ASSET_URL=.*', 'ASSET_URL=' | Out-File -encoding ASCII .env"
) else (
    powershell -Command "(gc .env) -replace '^ASSET_URL=.*', 'ASSET_URL=%ASSET_URL%' | Out-File -encoding ASCII .env"
)

REM Set production environment if needed
if "%SET_PRODUCTION%"=="yes" (
    echo [INFO] Mengatur environment production...
    powershell -Command "(gc .env) -replace '^APP_ENV=.*', 'APP_ENV=production' | Out-File -encoding ASCII .env"
    powershell -Command "(gc .env) -replace '^APP_DEBUG=.*', 'APP_DEBUG=false' | Out-File -encoding ASCII .env"
)

echo [SUCCESS] File .env berhasil diupdate!
echo [INFO] Backup disimpan di: .env.backup.*

REM Handle RewriteBase
if "%REWRITE_BASE_NEEDED%"=="yes" (
    echo.
    echo [WARNING] Untuk subdirectory, Anda mungkin perlu uncomment RewriteBase
    echo [WARNING] di file public\.htaccess dan sesuaikan path-nya.
    echo.
    set /p edit_htaccess="Buka file .htaccess sekarang? (y/n): "
    if /i "%edit_htaccess%"=="y" notepad public\.htaccess
)

REM Clear Laravel cache
echo.
echo [INFO] Membersihkan cache Laravel...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo [SUCCESS] Cache berhasil dibersihkan!

REM Generate APP_KEY if empty
findstr /C:"APP_KEY=" .env | findstr /C:"APP_KEY=base64:" >nul
if errorlevel 1 (
    echo.
    echo [INFO] APP_KEY belum di-generate.
    set /p gen_key="Generate APP_KEY sekarang? (y/n): "
    if /i "!gen_key!"=="y" php artisan key:generate
)

echo.
echo ============================================================================
echo   Konfigurasi selesai!
echo ============================================================================
echo.
echo Akses aplikasi di: %APP_URL%
echo.
echo Catatan:
echo - Jika menggunakan Virtual Host, pastikan sudah setup di:
echo   * Apache vhosts config
echo   * Windows hosts file
echo - Untuk production, jangan lupa:
echo   * Setup SSL/HTTPS
echo   * Set file permissions
echo   * Run: composer install --optimize-autoloader --no-dev
echo   * Run: php artisan config:cache
echo.
echo Dokumentasi lengkap: URL_CONFIGURATION_GUIDE.md
echo.

goto :end

:cancel
echo.
echo [INFO] Konfigurasi dibatalkan.
echo.
goto :end

:end
pause
