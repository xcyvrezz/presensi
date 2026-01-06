# Absensi MIFARE - Smart Attendance System

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red.svg" alt="Laravel 12.x">
  <img src="https://img.shields.io/badge/Livewire-3.x-purple.svg" alt="Livewire 3.x">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License MIT">
</p>

## About Absensi MIFARE

Absensi MIFARE is a comprehensive, modern attendance management system designed for educational institutions. Built with Laravel 12 and Livewire 3, it provides seamless NFC-based attendance tracking with support for offline-first Progressive Web App capabilities.

### Key Features

- **NFC-Based Attendance**: Quick check-in/check-out using MIFARE cards
- **Multi-Role Dashboard**: Tailored interfaces for Admin, Principal, Teachers, and Students
- **Absence Management**: Digital absence request submission and approval workflow
- **Real-time Notifications**: Instant alerts for attendance events and approvals
- **Advanced Analytics**: Comprehensive reporting with trends and insights
- **Progressive Web App**: Offline-capable mobile experience
- **RESTful API**: Full API for third-party integrations
- **Audit Trail**: Complete activity logging for security and compliance

## Technology Stack

### Backend
- **Laravel 12.42** - PHP Framework
- **Livewire 3.7** - Full-stack framework for Laravel
- **MySQL 8.0+** - Database
- **Laravel Sanctum** - API authentication (custom token system)

### Frontend
- **Tailwind CSS 3.x** - Utility-first CSS framework
- **Alpine.js 3.x** - Lightweight JavaScript framework
- **Chart.js** - Data visualization (ready to integrate)

### Features
- **Progressive Web App (PWA)** with Service Worker
- **Offline Mode** with cache-first strategy
- **Real-time Notifications**
- **API v2** with Bearer token authentication

## System Requirements

- PHP >= 8.2.0
- MySQL >= 8.0 or MariaDB >= 10.3
- Composer 2.x
- Node.js >= 18.x
- NPM >= 9.x

## Quick Start

### 1. Clone Repository

```bash
git clone <repository-url>
cd absensi-mifare
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_mifare
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Setup

```bash
php artisan migrate
php artisan db:seed  # Optional: Load sample data
```

### 5. Build Assets

```bash
npm run build
```

### 6. Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Default Login Credentials

After seeding the database:

**Admin**:
- Email: `admin@example.com`
- Password: `password`

**Kepala Sekolah (Principal)**:
- Email: `kepsek@example.com`
- Password: `password`

**Wali Kelas (Teacher)**:
- Email: `walikelas@example.com`
- Password: `password`

**Siswa (Student)**:
- Email: `student@example.com`
- Password: `password`

**Note**: Change these credentials immediately in production!

## User Roles

### 1. Admin
- Full system access
- User management
- Student registration
- Class & department management
- System settings
- Activity logs
- Advanced analytics
- API token management

### 2. Kepala Sekolah (Principal)
- School-wide attendance overview
- Department statistics
- Approval management
- Reports and analytics
- Student data viewing

### 3. Wali Kelas (Class Teacher)
- Class attendance management
- Manual attendance entry
- Absence request approval/rejection
- Class statistics
- Student management for assigned class

### 4. Siswa (Student)
- Personal attendance history
- Absence request submission
- Attendance statistics
- Notification viewing

## Core Modules

### 1. Attendance System
- NFC card-based check-in/check-out
- Manual attendance recording
- Late arrival tracking
- Status management (hadir, terlambat, izin, sakit, alpha, dispensasi)
- Geolocation tracking (optional)

### 2. Absence Management
- Digital absence request submission
- Document attachment support
- Multi-level approval workflow
- Request tracking and history
- Automatic notification on status changes

### 3. Analytics & Reporting
- Real-time attendance statistics
- Department and class comparisons
- Trend analysis (daily, weekly, monthly)
- Top performers and violation tracking
- Exportable reports

### 4. Notification System
- In-app notifications
- Multiple notification types
- Priority levels (low, normal, high, critical)
- Action-based routing
- Read/unread management

### 5. Security & Audit
- Complete activity logging
- User action tracking
- IP address logging
- Category and severity filtering
- Security event monitoring

### 6. API Integration
- RESTful API v2
- Token-based authentication
- Student endpoints
- Attendance endpoints
- Rate limiting (60 req/min)
- Comprehensive documentation

## API Usage

### Generate API Token

```bash
curl -X POST http://localhost:8000/api/v2/auth/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password",
    "token_name": "My Application",
    "abilities": ["*"],
    "expires_in_days": 30
  }'
```

### Use API Token

```bash
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  http://localhost:8000/api/v2/students
```

See `API_DOCUMENTATION.md` for complete API reference.

## Progressive Web App

The system is PWA-enabled with:
- **Service Worker** for offline functionality
- **Cache-first strategy** for static assets
- **Network-first strategy** for dynamic content
- **Offline fallback page**
- **Auto-sync** when connection restored
- **Installable** on mobile devices

### Testing PWA

1. Open Chrome DevTools > Application > Service Workers
2. Check "Offline" to simulate offline mode
3. Refresh page - should still load from cache
4. Uncheck "Offline" - automatic reconnection

## Project Structure

```
absensi-mifare/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/      # API Controllers
│   │   ├── Middleware/           # Custom Middleware
│   │   └── Livewire/             # Livewire Components
│   ├── Models/                   # Eloquent Models
│   └── Services/                 # Service Layer
├── database/
│   ├── migrations/               # Database Migrations
│   └── seeders/                  # Database Seeders
├── public/
│   ├── sw.js                     # Service Worker
│   └── offline.html              # Offline Page
├── resources/
│   ├── views/                    # Blade Templates
│   │   ├── layouts/              # Layout Files
│   │   └── livewire/             # Livewire Views
│   └── js/                       # JavaScript
├── routes/
│   ├── web.php                   # Web Routes
│   └── api.php                   # API Routes
├── API_DOCUMENTATION.md          # API Docs
├── FEATURE_SUMMARY.md            # Feature Summary
└── SETUP_GUIDE.md                # Setup Guide
```

## Documentation

- **[Setup Guide](SETUP_GUIDE.md)** - Complete installation and deployment guide
- **[Feature Summary](FEATURE_SUMMARY.md)** - Detailed feature documentation
- **[API Documentation](API_DOCUMENTATION.md)** - RESTful API reference

## Development

### Run Development Server

```bash
php artisan serve
```

### Watch for Asset Changes

```bash
npm run dev
```

### Run Tests

```bash
php artisan test
```

### Code Style

```bash
./vendor/bin/pint  # Laravel Pint for code formatting
```

## Production Deployment

### 1. Environment Configuration

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### 2. Optimize Application

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 3. Build Production Assets

```bash
npm run build
```

### 4. Set Permissions

```bash
chmod -R 755 storage bootstrap/cache
```

### 5. Setup Cron Job

Add to crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

See `SETUP_GUIDE.md` for complete deployment checklist.

## Security

### Reporting Security Vulnerabilities

If you discover a security vulnerability, please email security@example.com. All security vulnerabilities will be promptly addressed.

### Security Features

- **Token-based API authentication** with SHA-256 hashing
- **Activity logging** for all critical operations
- **IP address tracking**
- **Rate limiting** on API endpoints
- **CSRF protection** on all forms
- **SQL injection protection** via Eloquent ORM
- **XSS protection** via Blade templating

## Browser Support

- **Chrome** 90+ (recommended)
- **Firefox** 88+
- **Safari** 14+
- **Edge** 90+

PWA features require modern browsers with Service Worker support.

## Mobile Support

- **iOS** 14+ (Safari)
- **Android** 10+ (Chrome)

Optimized for mobile screens (responsive design).

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## Troubleshooting

### Common Issues

**Issue**: PHP version error
- **Solution**: Upgrade to PHP 8.2+ (see SETUP_GUIDE.md)

**Issue**: Database connection failed
- **Solution**: Check `.env` credentials and ensure MySQL is running

**Issue**: Assets not loading
- **Solution**: Run `npm run build` and clear browser cache

**Issue**: Service Worker not registering
- **Solution**: Must be served over HTTPS (or localhost)

See `SETUP_GUIDE.md` for more troubleshooting tips.

## Changelog

### Version 2.0.0 (December 13, 2025)

**New Features**:
- Added notification system with 6 notification types
- Implemented advanced analytics with 4 report types
- Added PWA support with offline mode
- Implemented security audit trail
- Added RESTful API v2 with token authentication
- Integrated notification bell in all layouts
- Added offline detector component

**Improvements**:
- Enhanced mobile responsiveness
- Optimized database queries
- Improved code organization with service layers
- Updated documentation

**Technical**:
- Upgraded to Laravel 12.42
- Upgraded to Livewire 3.7
- Added Service Worker for PWA
- Implemented API middleware
- Added activity logging

### Version 1.0.0 (Initial Release)

**Core Features**:
- NFC-based attendance system
- Multi-role user management
- Absence request workflow
- Basic reporting

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- **Laravel Framework** - Taylor Otwell and the Laravel team
- **Livewire** - Caleb Porzio
- **Tailwind CSS** - Adam Wathan and team
- **Alpine.js** - Caleb Porzio

## Support

For support, please:
1. Check the documentation in `SETUP_GUIDE.md`
2. Review `API_DOCUMENTATION.md` for API issues
3. Check `FEATURE_SUMMARY.md` for feature details
4. Contact the system administrator

---

**Project**: Absensi MIFARE - Smart Attendance System
**Version**: 2.0.0
**Institution**: SMK Negeri 10 Pandeglang
**Last Updated**: December 13, 2025

Built with ❤️ using Laravel, Livewire, and Tailwind CSS
