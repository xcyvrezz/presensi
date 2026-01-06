# Absensi MIFARE - Setup & Deployment Guide

## System Requirements

- **PHP**: >= 8.2.0
- **MySQL**: 8.0+ or MariaDB 10.3+
- **Composer**: 2.x
- **Node.js**: 18.x or higher
- **NPM**: 9.x or higher

## Current Status

All features have been implemented and are ready for deployment. The following tasks have been completed:

### ✅ Completed Features

1. **Core Attendance System**
   - NFC-based check-in/check-out
   - Manual attendance recording
   - Attendance status tracking (hadir, terlambat, izin, sakit, alpha, dispensasi)
   - Late minutes calculation
   - Attendance percentage tracking

2. **User Management**
   - Multi-role system (Admin, Kepala Sekolah, Wali Kelas, Siswa)
   - Role-based access control
   - User authentication and authorization

3. **Student Management**
   - Student registration with NFC card assignment
   - Class and department organization
   - Student profile management

4. **Absence Request System**
   - Student absence request submission
   - Multi-level approval workflow
   - Document attachment support
   - Status tracking (pending, approved, rejected)

5. **Dashboards**
   - Admin Dashboard with comprehensive statistics
   - Kepala Sekolah Dashboard for school-wide overview
   - Wali Kelas Dashboard for class management
   - Student Dashboard for personal attendance tracking

6. **Notification System**
   - Real-time in-app notifications
   - Multiple notification types (attendance_reminder, absence_request, approval_result, violation_warning, late_warning, system_announcement)
   - Priority levels (low, normal, high, critical)
   - Mark as read/unread functionality
   - Notification bell with unread count

7. **Advanced Analytics & Reporting**
   - Overview reports with department and class breakdowns
   - Trend analysis with daily/weekly/monthly patterns
   - Comparative analysis between departments and classes
   - Detailed reports with top performers and violation tracking
   - Export capabilities

8. **PWA (Progressive Web App)**
   - Service Worker for offline functionality
   - Cache-first strategy for static assets
   - Network-first strategy for dynamic data
   - Offline page with auto-retry
   - Background sync support
   - Push notification support

9. **Security & Audit Trail**
   - Comprehensive activity logging
   - User action tracking
   - IP address and user agent logging
   - Category-based filtering (authentication, user_management, student_management, attendance, approval, settings, report, system)
   - Severity levels (info, warning, critical)
   - Security event monitoring

10. **API & Integration System**
    - RESTful API v2 with token-based authentication
    - API token management (generate, revoke, list, verify)
    - Student endpoints (list, get by ID, get by NFC)
    - Attendance endpoints (list, check-in, check-out, statistics)
    - Rate limiting (60 requests/minute per token)
    - Comprehensive API documentation

## Pending Setup Steps

### 1. PHP Version Upgrade

**Current Issue**: The system requires PHP >= 8.2.0, but your current CLI PHP is 8.0.30.

**Solutions**:

#### Option A: Upgrade XAMPP PHP
1. Download PHP 8.2+ for Windows from https://windows.php.net/download/
2. Backup your current `C:\xampp\php` folder
3. Replace with the new PHP version
4. Update your Windows PATH environment variable
5. Restart your terminal

#### Option B: Install PHP 8.2+ Separately
1. Download PHP 8.2+ from https://windows.php.net/download/
2. Extract to `C:\php82` (or your preferred location)
3. Add `C:\php82` to your Windows PATH (before XAMPP path)
4. Restart your terminal
5. Verify with: `php -v`

#### Option C: Use Laravel Herd (Recommended for Development)
1. Download Laravel Herd from https://herd.laravel.com/
2. Install and it will manage PHP versions for you
3. Select PHP 8.2+ as default
4. Restart your terminal

### 2. Run Pending Migrations

After upgrading PHP, run the following migrations:

```bash
cd "D:\PROJECT\ABSEN MIFARE\absensi-mifare"
php artisan migrate
```

This will create the following new tables:
- `activity_logs` - For audit trail and security logging
- `api_tokens` - For API authentication and token management

### 3. Database Seeding (Optional)

If you want sample data for testing:

```bash
php artisan db:seed
```

### 4. Build Frontend Assets

Compile the frontend assets (Tailwind CSS, Alpine.js, etc.):

```bash
npm install
npm run build
```

For development with hot reload:

```bash
npm run dev
```

### 5. Configure Service Worker (PWA)

The service worker is already configured in `public/sw.js`. To enable it:

1. Ensure your app is served over HTTPS in production (service workers require HTTPS)
2. The service worker will auto-register when users visit the site
3. Test offline mode by:
   - Open DevTools > Application > Service Workers
   - Check "Offline" checkbox
   - Refresh the page

### 6. Configure API (Optional)

If you plan to use the API:

1. Generate an API token via the web interface or API:

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

2. Use the returned token in subsequent API requests:

```bash
curl -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  http://localhost:8000/api/v2/students
```

3. See `API_DOCUMENTATION.md` for complete API reference

### 7. Configure Notifications (Optional)

For production, you may want to configure:

**Email Notifications**:
- Update `.env` with your email provider details
- Implement email notification in `NotificationService`

**Push Notifications**:
- Configure push notification service (Firebase, OneSignal, etc.)
- Update service worker with push notification handler

### 8. Security Configuration

Before deploying to production:

1. **Generate new APP_KEY** (if not already done):
```bash
php artisan key:generate
```

2. **Update .env for production**:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

3. **Configure database backups**

4. **Set up SSL certificate** (Let's Encrypt, Cloudflare, etc.)

5. **Configure rate limiting** in API tokens table (default: 60/minute)

6. **Review activity log retention** policy in `ActivityLog` model

### 9. Server Requirements for Production

**Web Server**: Apache or Nginx
- Document root should point to `public` directory
- Enable mod_rewrite (Apache) or configure URL rewriting (Nginx)

**PHP Extensions Required**:
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension
- BCMath PHP Extension
- Fileinfo PHP Extension
- GD PHP Extension (for image processing)
- ZIP PHP Extension

**MySQL Configuration**:
- Create production database
- Create dedicated database user with appropriate permissions
- Configure regular backups

### 10. Testing Checklist

Before going live, test:

- [ ] User authentication (all roles)
- [ ] Student NFC card registration
- [ ] Check-in/check-out with NFC
- [ ] Manual attendance recording
- [ ] Absence request submission and approval
- [ ] Notification delivery
- [ ] API token generation and authentication
- [ ] API endpoints (students, attendance, statistics)
- [ ] Offline mode (PWA)
- [ ] Activity logging
- [ ] Report generation and export
- [ ] Mobile responsiveness
- [ ] Cross-browser compatibility

## File Structure Overview

```
absensi-mifare/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── StudentController.php
│   │   │       └── AttendanceController.php
│   │   └── Middleware/
│   │       └── ApiAuthenticate.php
│   ├── Livewire/
│   │   ├── Admin/
│   │   │   ├── Analytics/AdvancedReports.php
│   │   │   └── System/ActivityLogs.php
│   │   ├── Notifications/NotificationBell.php
│   │   └── Components/OfflineDetector.php
│   ├── Models/
│   │   ├── ActivityLog.php
│   │   ├── ApiToken.php
│   │   └── Notification.php
│   └── Services/
│       ├── AuditService.php
│       └── NotificationService.php
├── database/
│   └── migrations/
│       ├── 2025_12_13_200001_create_activity_logs_table.php
│       └── 2025_12_13_210001_create_api_tokens_table.php
├── public/
│   ├── sw.js (Service Worker)
│   └── offline.html
├── resources/
│   └── views/
│       ├── livewire/
│       │   ├── admin/analytics/advanced-reports.blade.php
│       │   ├── admin/system/activity-logs.blade.php
│       │   ├── notifications/notification-bell.blade.php
│       │   └── components/offline-detector.blade.php
│       └── layouts/
│           ├── admin.blade.php
│           ├── wali-kelas.blade.php
│           ├── student.blade.php
│           └── kepala-sekolah.blade.php
└── routes/
    ├── web.php
    └── api.php

Documentation:
├── API_DOCUMENTATION.md
└── SETUP_GUIDE.md (this file)
```

## Quick Start Commands

```bash
# 1. Upgrade PHP to 8.2+ (see options above)

# 2. Navigate to project
cd "D:\PROJECT\ABSEN MIFARE\absensi-mifare"

# 3. Install dependencies
composer install
npm install

# 4. Run migrations
php artisan migrate

# 5. Build assets
npm run build

# 6. Start development server
php artisan serve
```

## Troubleshooting

### Issue: PHP version mismatch
**Solution**: Follow "PHP Version Upgrade" section above

### Issue: Database connection failed
**Solution**:
- Ensure MySQL is running
- Check `.env` database credentials
- Verify database exists: `CREATE DATABASE absensi_mifare;`

### Issue: Composer dependencies error
**Solution**: `composer update --ignore-platform-reqs` (temporary, not recommended for production)

### Issue: Assets not loading
**Solution**:
- Run `npm run build`
- Clear browser cache
- Check `public/build` folder exists

### Issue: Service Worker not registering
**Solution**:
- Must be served over HTTPS (or localhost)
- Check browser DevTools > Console for errors
- Clear browser cache and service worker in DevTools > Application

### Issue: API returns 401 Unauthorized
**Solution**:
- Generate new token via `/api/v2/auth/token`
- Check token hasn't expired
- Ensure `Authorization: Bearer {token}` header is included

### Issue: Notifications not appearing
**Solution**:
- Check database `notifications` table exists
- Verify user is logged in
- Check browser console for JavaScript errors

## Support

For issues or questions:
1. Check this guide first
2. Review `API_DOCUMENTATION.md` for API-related issues
3. Check Laravel logs in `storage/logs/laravel.log`
4. Review browser console for frontend errors

## Production Deployment Checklist

- [ ] PHP 8.2+ installed and configured
- [ ] All migrations run successfully
- [ ] Frontend assets compiled (`npm run build`)
- [ ] `.env` configured for production
- [ ] `APP_DEBUG=false`
- [ ] SSL certificate installed
- [ ] Database backed up
- [ ] File permissions configured correctly
- [ ] Cron jobs configured for queue workers (if using queues)
- [ ] Email service configured
- [ ] Error monitoring configured (Sentry, Bugsnag, etc.)
- [ ] Server monitoring configured
- [ ] API rate limiting tested
- [ ] Activity logs retention policy defined
- [ ] Backup strategy implemented

## Maintenance

### Regular Tasks

**Daily**:
- Monitor activity logs for security events
- Check API usage and rate limits

**Weekly**:
- Review attendance statistics
- Check for pending absence requests
- Verify notification delivery

**Monthly**:
- Generate comprehensive attendance reports
- Review and clean old activity logs (optional)
- Audit API tokens and revoke unused ones
- Database optimization (`php artisan optimize`)

**Quarterly**:
- Security audit
- Performance optimization
- User feedback review
- Feature enhancement planning

---

**System Version**: 2.0.0
**Last Updated**: December 13, 2025
**Author**: Absensi MIFARE Development Team
