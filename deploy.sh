#!/bin/bash

# ============================================================================
# Deployment Script untuk NiagaHoster
# ============================================================================
# Script ini akan melakukan deployment aplikasi Laravel ke NiagaHoster
# via SSH secara otomatis
# ============================================================================

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fungsi untuk print dengan warna
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# ============================================================================
# KONFIGURASI - EDIT BAGIAN INI
# ============================================================================

# SSH Configuration
SSH_USER="u889841415"
SSH_HOST="153.92.8.245"
SSH_PORT="65002"
SSH_PATH="/home/u889841415/public_html"

# Git Configuration
GIT_BRANCH="main"

# ============================================================================
# JANGAN EDIT DI BAWAH INI KECUALI ANDA TAHU APA YANG ANDA LAKUKAN
# ============================================================================

echo ""
echo "============================================================================"
echo "  🚀 Deployment Script - Absensi MIFARE"
echo "============================================================================"
echo ""

# Cek apakah konfigurasi sudah diubah
if [ "$SSH_USER" == "your_username" ]; then
    print_error "Konfigurasi SSH belum diubah!"
    print_info "Edit file deploy.sh dan ubah SSH_USER, SSH_HOST, SSH_PORT, dan SSH_PATH"
    exit 1
fi

# Konfirmasi deployment
print_warning "Anda akan melakukan deployment ke:"
echo "  Host: $SSH_HOST"
echo "  User: $SSH_USER"
echo "  Path: $SSH_PATH"
echo ""
read -p "Lanjutkan deployment? (y/n): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_info "Deployment dibatalkan"
    exit 0
fi

echo ""
print_info "Memulai deployment..."
echo ""

# ============================================================================
# STEP 1: Push ke Git Repository
# ============================================================================
print_info "Step 1: Push ke Git Repository"

if git diff-index --quiet HEAD --; then
    print_success "Tidak ada perubahan untuk di-commit"
else
    print_warning "Ada perubahan yang belum di-commit"
    read -p "Commit dan push perubahan? (y/n): " -n 1 -r
    echo ""

    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git add .
        read -p "Masukkan commit message: " commit_msg
        git commit -m "$commit_msg"
        git push origin $GIT_BRANCH
        print_success "Perubahan berhasil di-push"
    fi
fi

echo ""

# ============================================================================
# STEP 2: Deploy ke Server
# ============================================================================
print_info "Step 2: Deploy ke Server"

ssh -p $SSH_PORT $SSH_USER@$SSH_HOST << 'ENDSSH'
    cd $SSH_PATH

    echo "→ Git pull latest changes..."
    git pull origin main

    echo "→ Install/Update Composer dependencies..."
    composer install --optimize-autoloader --no-dev

    echo "→ Run database migrations..."
    php artisan migrate --force

    echo "→ Clear and cache config..."
    php artisan config:clear
    php artisan config:cache

    echo "→ Clear and cache routes..."
    php artisan route:clear
    php artisan route:cache

    echo "→ Clear and cache views..."
    php artisan view:clear
    php artisan view:cache

    echo "→ Set permissions..."
    chmod -R 755 storage bootstrap/cache

    echo "✓ Deployment completed!"
ENDSSH

if [ $? -eq 0 ]; then
    echo ""
    print_success "Deployment berhasil!"
    echo ""
    print_info "Aplikasi sudah di-update di server"
    print_info "Silakan cek: https://$SSH_HOST"
else
    echo ""
    print_error "Deployment gagal!"
    print_info "Cek error message di atas"
    exit 1
fi

echo ""
echo "============================================================================"
