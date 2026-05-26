# 🔗 Laravel Backend Integration Guide

## Overview
Your Flutter app is now configured to connect to your Laravel MyAcademy backend and use the same authentication and data.

## 🚀 Quick Setup

### 1. Start Your Laravel Server
```bash
# In your Laravel project directory
cd c:\laragon\www\myacademy-laravel
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. Update Flutter API URL (if needed)
In `lib/core/constants.dart`, the API URL is set to:
```dart
static const String baseUrl = 'http://127.0.0.1:8000/api';
```

**For different setups:**
- **Same machine**: `http://127.0.0.1:8000/api` ✅ (current)
- **Physical device**: `http://YOUR_IP:8000/api` (replace YOUR_IP with your computer's IP)
- **Android emulator**: `http://10.0.2.2:8000/api`

### 3. Run Flutter App
```bash
cd myacademy_app
flutter run
```

## 🔐 Authentication Flow

### Login Process
1. **Flutter app** sends credentials to `/api/login`
2. **Laravel** validates and returns JWT token + user data
3. **Flutter** stores token and user info
4. **All subsequent requests** include `Authorization: Bearer {token}`

### Demo Accounts (from Laravel seeders)
- **Admin**: `admin@myacademy.local` / `password`
- **Teacher**: `teacher@myacademy.local` / `password`
- **Bursar**: `bursar@myacademy.local` / `password`

## 📊 Data Integration

### What's Connected
✅ **Authentication**: Login/logout with Laravel Sanctum
✅ **User Data**: Real user profiles and roles
✅ **Students**: Live student data from your database
✅ **Error Handling**: Laravel validation errors displayed
✅ **Offline Support**: Cached data when offline

### API Endpoints Used
- `POST /api/login` - Authentication
- `GET /api/user` - Current user info
- `GET /api/students` - Student list (paginated)
- `POST /api/logout` - Logout
- `GET /api/students/{id}/report-card` - Student reports

## 🎯 What You'll See

### Admin Dashboard
- **Real student count** from your database
- **Live student list** with names, classes, admission numbers
- **Role-based access** (admin sees everything)
- **Error handling** with retry functionality

### Teacher Dashboard
- **Filtered data** based on teacher permissions
- **Student management** for assigned classes
- **Real-time sync** with Laravel backend

### Data Flow
```
Flutter App ↔ Laravel API ↔ MySQL Database
     ↓              ↓              ↓
  UI Layer    Business Logic   Data Storage
```

## 🔧 Network Configuration

### For Physical Devices
1. **Find your computer's IP**:
   ```bash
   ipconfig  # Windows
   ifconfig  # Mac/Linux
   ```

2. **Update constants.dart**:
   ```dart
   static const String baseUrl = 'http://192.168.1.100:8000/api';
   ```

3. **Ensure Laravel allows external connections**:
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

### For Android Emulator
```dart
static const String baseUrl = 'http://10.0.2.2:8000/api';
```

## 🛠️ Troubleshooting

### Connection Issues
1. **Check Laravel server is running**:
   - Visit `http://127.0.0.1:8000` in browser
   - Should show Laravel welcome page

2. **Test API directly**:
   ```bash
   curl -X POST http://127.0.0.1:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{"email":"admin@myacademy.local","password":"password"}'
   ```

3. **Check Flutter console** for error messages

### Common Errors

#### "Network Error"
- Laravel server not running
- Wrong IP address in constants.dart
- Firewall blocking connection

#### "Invalid Credentials"
- Check if demo data is seeded in Laravel
- Run: `php artisan db:seed --force`

#### "Connection Refused"
- Use correct IP for your setup
- Ensure Laravel serves on `0.0.0.0` not just `127.0.0.1`

## 📱 Features Working

### ✅ Currently Integrated
- **Login/Logout** with Laravel Sanctum
- **User Authentication** and role management
- **Student Data** fetching and display
- **Error Handling** with user-friendly messages
- **Offline Caching** for better UX
- **Loading States** and retry functionality

### 🔄 Data Sync
- **Online**: Direct API calls to Laravel
- **Offline**: Cached data from local SQLite
- **Auto-retry**: Failed requests queued for later
- **Real-time**: Live data when connected

## 🎉 Success Indicators

When everything works correctly, you'll see:
1. **Splash screen** → **Onboarding** → **Login**
2. **Login with Laravel credentials** works
3. **Real student data** appears in admin dashboard
4. **User name and role** from Laravel database
5. **Smooth navigation** between screens

## 🔐 Security Features

- **JWT Token Authentication** via Laravel Sanctum
- **Secure token storage** in device keychain
- **Automatic token refresh** handling
- **Role-based access control** matching Laravel
- **HTTPS ready** for production deployment

Your Flutter app is now a true mobile client for your Laravel MyAcademy system! 🎊