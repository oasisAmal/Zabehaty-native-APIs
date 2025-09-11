# Multi-Country Database Setup Guide

## Overview
This Laravel application supports multiple countries with separate databases for each country. The system automatically switches database connections based on the `App-Country` header in API requests.

## Supported Countries
- **AE** - United Arab Emirates
- **SA** - Saudi Arabia  
- **OM** - Oman
- **KW** - Kuwait
- **BH** - Bahrain

## Database Configuration

### Environment Variables
Add the following to your `.env` file:

```env
# Default Database Connection
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=zabehaty_default
DB_USERNAME=root
DB_PASSWORD=

# United Arab Emirates Database
DB_AE_HOST=127.0.0.1
DB_AE_PORT=3306
DB_AE_DATABASE=zabehaty_ae
DB_AE_USERNAME=root
DB_AE_PASSWORD=

# Saudi Arabia Database
DB_SA_HOST=127.0.0.1
DB_SA_PORT=3306
DB_SA_DATABASE=zabehaty_sa
DB_SA_USERNAME=root
DB_SA_PASSWORD=

# Oman Database
DB_OM_HOST=127.0.0.1
DB_OM_PORT=3306
DB_OM_DATABASE=zabehaty_om
DB_OM_USERNAME=root
DB_OM_PASSWORD=

# Kuwait Database
DB_KW_HOST=127.0.0.1
DB_KW_PORT=3306
DB_KW_DATABASE=zabehaty_kw
DB_KW_USERNAME=root
DB_KW_PASSWORD=

# Bahrain Database
DB_BH_HOST=127.0.0.1
DB_BH_PORT=3306
DB_BH_DATABASE=zabehaty_bh
DB_BH_USERNAME=root
DB_BH_PASSWORD=
```

## How It Works

### 1. Country Middleware
The `CountryMiddleware` automatically:
- Validates the `App-Country` header
- Sets the appropriate database connection
- Adds `app_country_code` to the request

### 2. Database Connection Service
The `DatabaseConnectionService` handles:
- Switching database connections
- Connection validation
- Connection management

### 3. Country Database Trait
Models using `CountryDatabaseTrait` automatically:
- Use the correct database connection
- Handle country-specific queries
- Provide helper methods for country operations

## Usage Examples

### API Requests
All API requests must include the `App-Country` header:

```bash
curl -H "App-Country: AE" https://your-domain.com/api/users
curl -H "App-Country: SA" https://your-domain.com/api/users
```

### Model Usage
```php
// Automatic connection based on request
$users = User::all(); // Uses current country's database

// Explicit country connection
$users = User::forCountry('AE')->get();
$user = User::findForCountry(1, 'SA');

// Create for specific country
$user = User::createForCountry(['name' => 'John'], 'OM');
```

### Database Helpers
```php
use App\Helpers\DatabaseHelpers;

// Execute operation for specific country
DatabaseHelpers::forCountry('AE', function() {
    return User::count();
});

// Get current country code
$countryCode = DatabaseHelpers::getCurrentCountryCode();

// Check connection status
$status = DatabaseHelpers::getConnectionsStatus();
```

## Commands

### Database Management
```bash
# Migrate all countries
php artisan country:db migrate --all

# Migrate specific country
php artisan country:db migrate --country=AE

# Seed specific country
php artisan country:db seed --country=SA --seeder=UserSeeder

# Check database status
php artisan country:db status --all

# Fresh migrate specific country
php artisan country:db fresh --country=OM
```

## Database Setup

### 1. Create Databases
Create separate databases for each country:
```sql
CREATE DATABASE zabehaty_ae;
CREATE DATABASE zabehaty_sa;
CREATE DATABASE zabehaty_om;
CREATE DATABASE zabehaty_kw;
CREATE DATABASE zabehaty_bh;
```

### 2. Run Migrations
```bash
# Migrate all countries
php artisan country:db migrate --all

# Or migrate individually
php artisan country:db migrate --country=AE
php artisan country:db migrate --country=SA
# ... etc
```

### 3. Seed Data
```bash
# Seed specific country
php artisan country:db seed --country=AE --seeder=CountrySeeder
```

## Model Implementation

### Adding Country Support to Models
```php
<?php

namespace App\Models;

use App\Traits\CountryDatabaseTrait;
use Illuminate\Database\Eloquent\Model;

class YourModel extends Model
{
    use CountryDatabaseTrait;
    
    // Your model code...
}
```

### Controller Usage
```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\DatabaseHelpers;

class UserController extends Controller
{
    public function index()
    {
        // Automatically uses current country's database
        $users = User::all();
        
        return response()->json($users);
    }
    
    public function show($id)
    {
        // Explicit country query
        $user = User::findForCountry($id, request()->get('app_country_code'));
        
        return response()->json($user);
    }
}
```

## Error Handling

The system handles various error scenarios:
- Invalid country codes
- Database connection failures
- Missing country headers
- Connection timeouts

## Performance Considerations

1. **Connection Pooling**: Each country maintains its own connection pool
2. **Caching**: Consider implementing country-specific caching
3. **Load Balancing**: Can be extended to use different database servers per country
4. **Monitoring**: Monitor connection status and performance per country

## Security

1. **Header Validation**: All country headers are validated
2. **Connection Isolation**: Each country's data is completely isolated
3. **Access Control**: Implement proper authentication and authorization
4. **Audit Logging**: Log all database operations with country context

## Troubleshooting

### Common Issues

1. **Connection Errors**
   ```bash
   php artisan country:db status --all
   ```

2. **Migration Issues**
   ```bash
   php artisan country:db migrate --country=AE --force
   ```

3. **Model Connection Issues**
   - Ensure models use `CountryDatabaseTrait`
   - Check middleware is properly registered
   - Verify `App-Country` header is sent

### Debug Mode
Enable debug mode to see connection switching:
```php
// In your controller or service
\Log::info('Current connection: ' . \DB::connection()->getName());
\Log::info('Country code: ' . request()->get('app_country_code'));
```
