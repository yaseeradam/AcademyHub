# 🌐 Flutter Web Login Troubleshooting

## The Issue
Flutter Web has CORS (Cross-Origin Resource Sharing) restrictions when connecting to APIs.

## ✅ Quick Fix Steps

### 1. **Start Laravel Server**
```bash
cd c:\laragon\www\academyhub-laravel
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. **Verify Laravel is Running**
Open browser and go to: `http://localhost:8000`
- Should show Laravel welcome page
- If not, Laravel isn't running properly

### 3. **Test API Directly**
Open browser and go to: `http://localhost:8000/api/login`
- Should show "Method Not Allowed" (this is good - means API is accessible)
- If shows connection error, there's a server issue

### 4. **Run Flutter Web**
```bash
cd academyhub_app
flutter run -d chrome
```

### 5. **Check Browser Console**
1. Open Chrome DevTools (F12)
2. Go to Console tab
3. Try to login
4. Look for error messages

## 🔍 Common Issues & Solutions

### **Issue 1: "Cannot connect to server"**
**Solution**: Make sure Laravel is running on `http://localhost:8000`

### **Issue 2: CORS Error in Console**
```
Access to XMLHttpRequest at 'http://localhost:8000/api/login' from origin 'http://localhost:xxxxx' has been blocked by CORS policy
```
**Solution**: Your Laravel CORS is already configured correctly, but try restarting Laravel server.

### **Issue 3: "Connection refused"**
**Solution**: 
1. Check if port 8000 is free
2. Try different port: `php artisan serve --port=8080`
3. Update Flutter constants.dart to match

### **Issue 4: Database not seeded**
**Solution**:
```bash
cd c:\laragon\www\academyhub-laravel
php artisan migrate:fresh --seed
```

## 🧪 Test API Manually

### Using Browser (GET requests)
- `http://localhost:8000/api/user` (should show "Unauthenticated")

### Using curl (POST requests)
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"admin@academyhub.local\",\"password\":\"password\"}"
```

Should return:
```json
{
  "token": "1|xxxxx...",
  "user": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@academyhub.local",
    "role": "admin"
  }
}
```

## 📱 Alternative: Use Mobile/Desktop

If web continues to have issues:

### **Android Emulator**
```bash
flutter run -d android
```
Update constants.dart: `http://10.0.2.2:8000/api`

### **Physical Device**
1. Find your IP: `ipconfig` (Windows) or `ifconfig` (Mac/Linux)
2. Update constants.dart: `http://YOUR_IP:8000/api`
3. Ensure Laravel serves on all interfaces: `--host=0.0.0.0`

### **Desktop**
```bash
flutter run -d windows  # or macos/linux
```
Use: `http://localhost:8000/api`

## 🔧 Debug Steps

### 1. **Check Flutter Console Output**
Look for these debug messages:
```
Attempting login to: http://localhost:8000/api/login
Email: admin@academyhub.local
Login response: {token: ..., user: ...}
Login successful for user: Admin User
```

### 2. **Check Browser Network Tab**
1. Open DevTools → Network tab
2. Try login
3. Look for `/login` request
4. Check if it's red (failed) or green (success)
5. Click on it to see response

### 3. **Common Error Messages**
- **"Cannot connect to server"** → Laravel not running
- **"Network error"** → CORS or connection issue
- **"Invalid credentials"** → Wrong email/password or database not seeded
- **"422 Validation Error"** → Email format issue

## ✅ Success Checklist

- [ ] Laravel server running on `http://localhost:8000`
- [ ] Can access Laravel welcome page in browser
- [ ] Database is migrated and seeded
- [ ] Flutter Web app loads without errors
- [ ] Browser console shows no CORS errors
- [ ] Login attempt shows debug messages in Flutter console

## 🎯 Expected Flow

1. **Enter credentials**: `admin@academyhub.local` / `password`
2. **See loading spinner** on login button
3. **Check console** for debug messages
4. **Success**: Navigate to admin dashboard with real data
5. **Failure**: Error message with specific details

Try these steps and let me know what error messages you see in the browser console and Flutter debug output!