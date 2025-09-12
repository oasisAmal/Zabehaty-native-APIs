# Project Setup Guide - Zabehaty Native APIs

## Overview
This project is a Laravel API that supports multiple countries with separate databases for each country. The system uses Laravel Modules and includes integrations with SMS, WhatsApp, and FCM services.

## System Requirements

### Basic Requirements
- **PHP**: 8.2 or later
- **Composer**: 2.0 or later
- **Node.js**: 18 or later
- **NPM**: 8 or later
- **MySQL**: 8.0 or later
- **Redis**: 6.0 or later (optional)

### Docker Requirements
- **Docker**: 20.10 or later
- **Docker Compose**: 2.0 or later

## Setup Methods

### Method 1: Docker Setup (Recommended)

#### 1. Clone the Project
```bash
git clone <repository-url>
cd ZabehatyNativeAPIs
```

#### 2. Environment Variables Setup
```bash
# Create .env file from example
cp .env.example .env

# Or create new .env file
touch .env
```

#### 3. Basic Environment Variables Setup
Add the following content to your `.env` file:

```env
# Application
APP_NAME="Zabehaty Native APIs"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8080

# Database - Default Connection
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=zabehaty_default
DB_USERNAME=root
DB_PASSWORD=root

# Multi-Country Databases
DB_AE_HOST=db
DB_AE_PORT=3306
DB_AE_DATABASE=zabehaty_ae
DB_AE_USERNAME=root
DB_AE_PASSWORD=root

DB_SA_HOST=db
DB_SA_PORT=3306
DB_SA_DATABASE=zabehaty_sa
DB_SA_USERNAME=root
DB_SA_PASSWORD=root

DB_OM_HOST=db
DB_OM_PORT=3306
DB_OM_DATABASE=zabehaty_om
DB_OM_USERNAME=root
DB_OM_PASSWORD=root

DB_KW_HOST=db
DB_KW_PORT=3306
DB_KW_DATABASE=zabehaty_kw
DB_KW_USERNAME=root
DB_KW_PASSWORD=root

DB_BH_HOST=db
DB_BH_PORT=3306
DB_BH_DATABASE=zabehaty_bh
DB_BH_USERNAME=root
DB_BH_PASSWORD=root

# Cache
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Twilio (WhatsApp)
TWILIO_ACCOUNT_SID_ZABEHATY_APP=your_twilio_account_sid
TWILIO_AUTH_TOKEN_ZABEHATY_APP=your_twilio_auth_token
TWILIO_WHATSAPP_NUMBER_ZABEHATY_APP=your_whatsapp_number

TWILIO_ACCOUNT_SID_HALAL_APP=your_twilio_account_sid
TWILIO_AUTH_TOKEN_HALAL_APP=your_twilio_auth_token
TWILIO_WHATSAPP_NUMBER_HALAL_APP=your_whatsapp_number

# Firebase (FCM)
FIREBASE_PROJECT_ID_ZABEHATY_APP=zabehaty-98bce
FIREBASE_PROJECT_ID_HALAL_APP=halal-bca9c
```

#### 4. Run Docker
```bash
# Build and run containers
docker-compose up -d --build

# Or just run containers
docker-compose up -d
```

#### 5. Setup Laravel inside Container
```bash
# Enter application container
docker-compose exec app bash

# Install dependencies
composer install

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Create databases for countries
php artisan country:db migrate --all

# Run seeders
php artisan db:seed
```

#### 6. Access the Application
- **Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Mailpit**: http://localhost:8025

### Method 2: Local Setup (Without Docker)

#### 1. Clone the Project
```bash
git clone <repository-url>
cd ZabehatyNativeAPIs
```

#### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

#### 3. Database Setup
```sql
-- Create databases
CREATE DATABASE zabehaty_default;
CREATE DATABASE zabehaty_ae;
CREATE DATABASE zabehaty_sa;
CREATE DATABASE zabehaty_om;
CREATE DATABASE zabehaty_kw;
CREATE DATABASE zabehaty_bh;
```

#### 4. Environment Variables Setup
```bash
cp .env.example .env
```

أضف المحتوى التالي إلى ملف `.env`:

```env
# Application
APP_NAME="Zabehaty Native APIs"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8000

# Database - Default Connection
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zabehaty_default
DB_USERNAME=root
DB_PASSWORD=your_password

# Multi-Country Databases
DB_AE_HOST=127.0.0.1
DB_AE_PORT=3306
DB_AE_DATABASE=zabehaty_ae
DB_AE_USERNAME=root
DB_AE_PASSWORD=your_password

DB_SA_HOST=127.0.0.1
DB_SA_PORT=3306
DB_SA_DATABASE=zabehaty_sa
DB_SA_USERNAME=root
DB_SA_PASSWORD=your_password

DB_OM_HOST=127.0.0.1
DB_OM_PORT=3306
DB_OM_DATABASE=zabehaty_om
DB_OM_USERNAME=root
DB_OM_PASSWORD=your_password

DB_KW_HOST=127.0.0.1
DB_KW_PORT=3306
DB_KW_DATABASE=zabehaty_kw
DB_KW_USERNAME=root
DB_KW_PASSWORD=your_password

DB_BH_HOST=127.0.0.1
DB_BH_PORT=3306
DB_BH_DATABASE=zabehaty_bh
DB_BH_USERNAME=root
DB_BH_PASSWORD=your_password

# Cache
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Mail
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Twilio (WhatsApp)
TWILIO_ACCOUNT_SID_ZABEHATY_APP=your_twilio_account_sid
TWILIO_AUTH_TOKEN_ZABEHATY_APP=your_twilio_auth_token
TWILIO_WHATSAPP_NUMBER_ZABEHATY_APP=your_whatsapp_number

TWILIO_ACCOUNT_SID_HALAL_APP=your_twilio_account_sid
TWILIO_AUTH_TOKEN_HALAL_APP=your_twilio_auth_token
TWILIO_WHATSAPP_NUMBER_HALAL_APP=your_whatsapp_number

# Firebase (FCM)
FIREBASE_PROJECT_ID_ZABEHATY_APP=zabehaty-98bce
FIREBASE_PROJECT_ID_HALAL_APP=halal-bca9c
```

#### 5. Setup Laravel
```bash
# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Create databases for countries
php artisan country:db migrate --all

# Run seeders
php artisan db:seed

# Start the application
php artisan serve
```

## External Integrations Setup

### 1. Firebase (FCM)
```bash
# Create Firebase directory
mkdir -p storage/app/firebase

# Add Firebase Service Account files
# Place Firebase JSON files in:
# storage/app/firebase/firebase-service-zabehaty-app-account.json
# storage/app/firebase/firebase-service-halal-app-account.json
```

### 2. Twilio (WhatsApp)
- Register an account with Twilio
- Get Account SID and Auth Token
- Add phone numbers in environment variables

### 3. SMS Services
- SMS Global: Add credentials in `config/integrations-credentials.php`
- SMS Country: Add credentials in `config/integrations-credentials.php`

## Project Structure

### Modules
The project uses Laravel Modules with the following modules:
- **Auth**: Authentication and authorization management
- **Users**: User management
- **Notifications**: Notification management

### Important Files
- `app/Enums/`: Enums used in the project
- `app/Helpers/`: General helpers
- `app/Services/`: External integration services
- `app/Traits/`: Traits used in models
- `config/integrations-credentials.php`: Integration credentials

## Useful Commands

### Database Management
```bash
# Migrate all countries
php artisan country:db migrate --all

# Migrate specific country
php artisan country:db migrate --country=AE

# Run seeder for specific country
php artisan country:db seed --country=AE --seeder=UserSeeder

# Check database status
php artisan country:db status --all

# Fresh migrate specific country
php artisan country:db fresh --country=AE
```

### Module Management
```bash
# Enable module
php artisan module:enable ModuleName

# Disable module
php artisan module:disable ModuleName

# Create new module
php artisan module:make ModuleName
```

### Development Commands
```bash
# Run application with Queue and Logs
composer run dev

# Run tests
composer run test

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## API Usage

### Required Headers
```bash
# All API requests need:
App-Country: AE  # or SA, OM, KW, BH
Content-Type: application/json
Accept: application/json
```

### Usage Examples
```bash
# Login
curl -X POST http://localhost:8080/api/auth/login \
  -H "App-Country: AE" \
  -H "Content-Type: application/json" \
  -d '{"phone": "+971501234567", "password": "password"}'

# Get users
curl -X GET http://localhost:8080/api/users \
  -H "App-Country: AE" \
  -H "Authorization: Bearer your_token"
```

## Troubleshooting

### Common Issues

#### 1. Database Connection Error
```bash
# Check connection status
php artisan country:db status --all

# Check database configuration
php artisan config:show database
```

#### 2. Module Error
```bash
# Reload modules
php artisan module:list

# Enable module
php artisan module:enable ModuleName
```

#### 3. Permission Error (Docker)
```bash
# Fix file permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

#### 4. Composer Error
```bash
# Reinstall dependencies
composer install --no-cache
composer dump-autoload
```

### System Status Check
```bash
# Check Laravel status
php artisan about

# Check module status
php artisan module:list

# Check database status
php artisan country:db status --all
```

## Development Tips

### 1. Using Docker
- Use Docker for local development to avoid environment issues
- Use `docker-compose exec app bash` to enter the container

### 2. Database Management
- Use `country:db` commands to manage databases
- Make sure to add `App-Country` header in all requests

### 3. Development with Modules
- Use `php artisan module:make` to create new modules
- Make sure to enable modules in `modules_statuses.json`

### 4. Testing
- Use `composer run test` to run tests
- Make sure to set up test environment correctly

## Support and Help

### Reference Files
- `documents/MULTI_COUNTRY_DATABASE_SETUP.md`: Multi-country database setup guide
- `README.md`: General project information

### Contact
- If you encounter issues, check logs in `storage/logs/`
- Use `php artisan telescope` to monitor requests (in development environment)

---

**Note**: Make sure to update this guide when adding new features or changing project settings.
