# Timezone System Guide

## How Timezone Works in the System

### Supported Countries and Timezones
- **AE** - United Arab Emirates (Asia/Dubai)
- **SA** - Saudi Arabia (Asia/Riyadh)
- **OM** - Oman (Asia/Muscat)
- **KW** - Kuwait (Asia/Kuwait)
- **BH** - Bahrain (Asia/Bahrain)

## How the System Works

### 1. Automatic Operation
When sending a request with `App-Country` header:

```bash
# UAE request - automatically sets Asia/Dubai
curl -H "App-Country: AE" https://your-domain.com/api/users

# Saudi request - automatically sets Asia/Riyadh
curl -H "App-Country: SA" https://your-domain.com/api/users
```

### 2. Middleware Works Automatically
```php
// In CountryMiddleware
TimezoneService::setTimezone($countryCode);  // Sets timezone
DatabaseConnectionService::setConnection($countryCode);  // Sets database
```

### 3. Result
- ✅ Timezone is set automatically
- ✅ PHP timezone is updated automatically
- ✅ Carbon timezone is updated automatically
- ✅ No need to modify code in Controllers

## Usage in Code

### Basic Usage
```php
public function index()
{
    // Current time in correct timezone automatically
    $currentTime = now(); // Carbon instance in correct timezone
    
    return response()->json([
        'current_time' => $currentTime->format('Y-m-d H:i:s'),
        'timezone' => TimezoneService::getCurrentTimezone(),
        'country' => request()->header('App-Country')
    ]);
}
```

### Using TimezoneService
```php
use App\Services\Common\TimezoneService;

// Get timezone for specific country
$uaeTimezone = TimezoneService::getTimezone('AE'); // "Asia/Dubai"
$saudiTimezone = TimezoneService::getTimezone('SA'); // "Asia/Riyadh"

// Get current timezone
$currentTimezone = TimezoneService::getCurrentTimezone();

// Set timezone manually (for testing)
TimezoneService::setTimezone('AE');
```

### In Models
```php
class User extends Model
{
    // Dates automatically display in correct timezone
    // No additional code needed!
    
    public function getCreatedAtAttribute($value)
    {
        return $this->asDateTime($value); // In correct timezone
    }
}
```

## API Endpoints

### Get Timezone Information
```php
Route::get('/timezone/info', function() {
    return response()->json([
        'timezone' => TimezoneService::getCurrentTimezone(),
        'current_time' => now()->toISOString(),
        'country' => request()->header('App-Country'),
    ]);
});

// Get all timezones
Route::get('/timezone/all', function() {
    $timezones = [];
    foreach (['AE', 'SA', 'OM', 'KW', 'BH'] as $country) {
        $timezones[$country] = TimezoneService::getTimezone($country);
    }
    return response()->json($timezones);
});
```

## Testing

```php
use App\Services\Common\TimezoneService;

public function testTimezoneSwitching()
{
    // Test UAE timezone
    TimezoneService::setTimezone('AE');
    $uaeTime = now();
    
    // Test Saudi timezone
    TimezoneService::setTimezone('SA');
    $saudiTime = now();
    
    // Times should be different
    $this->assertNotEquals($uaeTime->format('H:i'), $saudiTime->format('H:i'));
}

public function testAllTimezones()
{
    $expectedTimezones = [
        'AE' => 'Asia/Dubai',
        'SA' => 'Asia/Riyadh',
        'OM' => 'Asia/Muscat',
        'KW' => 'Asia/Kuwait',
        'BH' => 'Asia/Bahrain',
    ];
    
    foreach ($expectedTimezones as $country => $expectedTimezone) {
        $actualTimezone = TimezoneService::getTimezone($country);
        $this->assertEquals($expectedTimezone, $actualTimezone);
    }
}
```

## System Summary

### How it works:
1. **Request comes** with `App-Country` header (AE, SA, OM, KW, BH)
2. **System automatically**:
   - Sets correct timezone via `TimezoneService`
   - Updates PHP timezone
   - Updates Carbon timezone
3. **Code works normally** - no modifications needed!
4. **All dates and times** in correct timezone

### Benefits:
- ✅ **Fully Automatic** - no need to set timezone manually
- ✅ **Seamless Integration** - works with existing code
- ✅ **Multi-Country Support** - handles 5 countries automatically
- ✅ **Developer Friendly** - simple methods available
- ✅ **Production Ready** - comprehensive error handling

### Usage:
```php
// Just send App-Country header and everything works automatically!
curl -H "App-Country: AE" https://your-domain.com/api/users
curl -H "App-Country: SA" https://your-domain.com/api/users
curl -H "App-Country: OM" https://your-domain.com/api/users
```

**No need to modify code in Controllers!** The system handles everything automatically.