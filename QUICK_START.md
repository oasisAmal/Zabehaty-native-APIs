# Quick Start Guide - Zabehaty Native APIs

## 🚀 Get Started in 5 Minutes

### Prerequisites
- Docker & Docker Compose (recommended)
- OR PHP 8.2+, Composer, MySQL (local setup)

### Option 1: Docker Setup (Recommended)

```bash
# 1. Clone the repository
git clone <repository-url>
cd ZabehatyNativeAPIs

# 2. Run the setup script
chmod +x setup.sh
./setup.sh

# 3. Choose option 1 (Docker)
# The script will handle everything automatically!

# 4. Access your application
# - API: http://localhost:8080
# - phpMyAdmin: http://localhost:8081
# - Mailpit: http://localhost:8025
```

### Option 2: Manual Docker Setup

```bash
# 1. Clone and navigate
git clone <repository-url>
cd ZabehatyNativeAPIs

# 2. Create .env file
cp .env.example .env

# 3. Start containers
docker-compose up -d --build

# 4. Setup Laravel
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate
docker-compose exec app php artisan country:db migrate --all
docker-compose exec app php artisan db:seed
```

### Option 3: Local Setup

```bash
# 1. Clone and navigate
git clone <repository-url>
cd ZabehatyNativeAPIs

# 2. Install dependencies
composer install
npm install

# 3. Create databases
mysql -u root -p
CREATE DATABASE zabehaty_default;
CREATE DATABASE zabehaty_ae;
CREATE DATABASE zabehaty_sa;
CREATE DATABASE zabehaty_om;
CREATE DATABASE zabehaty_kw;
CREATE DATABASE zabehaty_bh;

# 4. Setup Laravel
cp .env.example .env
# Edit .env with your database credentials
php artisan key:generate
php artisan migrate
php artisan country:db migrate --all
php artisan db:seed
php artisan serve
```

## 🧪 Test Your Setup

### Test API Endpoints

```bash
# Test login endpoint
curl -X POST http://localhost:8080/api/auth/login \
  -H "App-Country: AE" \
  -H "Content-Type: application/json" \
  -d '{"phone": "+971501234567", "password": "password"}'

# Test users endpoint
curl -X GET http://localhost:8080/api/users \
  -H "App-Country: AE" \
  -H "Accept: application/json"
```

### Check System Status

```bash
# Check Laravel status
php artisan about

# Check database connections
php artisan country:db status --all

# Check modules
php artisan module:list
```

## 📋 Important Notes

### Required Headers
All API requests must include:
```
App-Country: AE  # or SA, OM, KW, BH
Content-Type: application/json
Accept: application/json
```

### Supported Countries
- **AE** - United Arab Emirates
- **SA** - Saudi Arabia
- **OM** - Oman
- **KW** - Kuwait
- **BH** - Bahrain

### Next Steps
1. **Configure Integrations**: Update Twilio and Firebase credentials in `.env`
2. **Add Firebase Files**: Place service account JSON files in `storage/app/firebase/`
3. **Test Features**: Use the API endpoints to test authentication and user management
4. **Read Full Docs**: Check `documents/PROJECT_SETUP_GUIDE.md` for detailed information

## 🆘 Need Help?

- **Full Documentation**: `documents/PROJECT_SETUP_GUIDE.md`
- **Database Setup**: `documents/MULTI_COUNTRY_DATABASE_SETUP.md`
- **Logs**: Check `storage/logs/laravel.log` for errors
- **Telescope**: Visit `http://localhost:8080/telescope` (development only)

## 🔧 Common Commands

```bash
# Development
composer run dev          # Start with queue and logs
composer run test         # Run tests

# Database
php artisan country:db migrate --all
php artisan country:db status --all

# Cache
php artisan cache:clear
php artisan config:clear

# Modules
php artisan module:list
php artisan module:enable ModuleName
```

---

**Ready to code?** 🎉 Your Zabehaty Native APIs project is now set up and ready for development!
