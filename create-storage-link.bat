@echo off
echo Creating storage link using Directory Junction...
echo.

cd /d "%~dp0"

if exist "public\storage" (
    echo Storage link already exists!
    exit /b 0
)

if not exist "storage\app\public" (
    echo Creating storage\app\public directory...
    mkdir "storage\app\public"
)

echo Creating junction from public\storage to storage\app\public...
mklink /J "public\storage" "storage\app\public"

if %errorlevel% equ 0 (
    echo.
    echo ✓ Storage link created successfully!
    echo You can now access uploaded files via:
    echo http://localhost:8000/storage/your-file.jpg
) else (
    echo.
    echo × Failed to create storage link!
    echo Please run this batch file as Administrator
)

echo.
pause
