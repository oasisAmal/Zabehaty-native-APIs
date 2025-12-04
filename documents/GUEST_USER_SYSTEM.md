# Guest User System

## Overview

A guest user system has been added to the application, allowing guests to add products to their cart but preventing them from creating orders until they register properly.

## New Features

### 1. `is_guest` Column in Users Table

- Added `is_guest` column of type boolean
- Default value: `false` (registered user)
- When user is created as guest, the value is set to `true`
- When guest registers, the value changes to `false`

### 2. New Methods in User Model

```php
// Check if user is a guest
$user->isGuest(); // true/false

// Check if user is registered
$user->isRegistered(); // true/false

// Create a guest user
User::createGuest(['name' => 'Guest User']);
```

### 3. New API Endpoints

#### Create Guest User
```
POST /api/auth/create-guest
```

**Request Body:**
```json
{}
```

**Note:** No data required - creates a guest user with default values.

#### Register (Works for both new users and guest conversion)
```
POST /api/auth/register
Authorization: Bearer {token} // Optional - if provided, converts guest to registered
```

**Request Body:**
```json
{
    "name": "John Doe",
    "mobile": "1234567890",
    "email": "john@example.com",
    "password": "password123"
}
```

**Note:** 
- **No token**: Creates a new registered user
- **Token + Guest user**: Converts guest to registered user
- **Token + Registered user**: Returns error "User is already registered"

### 4. New Middleware

#### `require-registered`
Prevents guests from accessing certain endpoints (like creating orders).

```php
Route::group(['middleware' => ['auth:api', 'require-registered']], function () {
    Route::post('orders/create', 'createOrder');
});
```

#### `CheckGuestModeMiddleware` (Automatic)
Automatically loaded on all API routes. Enforces guest mode settings by checking if guest mode is enabled in the Settings table (`key = 'guest_mode'`). 

**How it works:**
- Registered in `bootstrap/app.php` as a global API middleware
- Automatically runs on all API routes (no need to add manually)
- If a guest user tries to access any API route when guest mode is disabled:
  - The guest user is force deleted
  - Returns a 401 error with message "guest_mode_not_allowed"

**Registration:**
```php
// bootstrap/app.php
$middleware->api(prepend: [
    // ... other middleware
    CheckGuestModeMiddleware::class,
]);
```

### 5. Guest Mode Setting

A new setting has been added to control guest mode functionality:

- **Key**: `guest_mode`
- **Type**: Boolean
- **Default**: `false` (if not set)
- **Location**: `settings` table

**How it works:**
- When `guest_mode = true`: Guest users can access API routes normally
- When `guest_mode = false`: Guest users are blocked and deleted when accessing any API route (automatically enforced by `CheckGuestModeMiddleware`)

**To enable/disable guest mode:**
```php
// Enable guest mode
Settings::updateOrCreate(
    ['key' => 'guest_mode'],
    ['value' => true]
);

// Disable guest mode
Settings::updateOrCreate(
    ['key' => 'guest_mode'],
    ['value' => false]
);
```

## How to Use

### 1. Add Product to Cart (Allowed for Guests)

```php
// In Controller
public function addToCart(Request $request)
{
    $user = $request->user();
    
    if (!$user) {
        // Create a guest user if no user is authenticated
        $user = User::createGuest(['name' => 'Guest User']);
    }

    // Add product to cart
    // Cart::add($user->id, $request->product_id, $request->quantity);

    return response()->json([
        'success' => true,
        'message' => 'Item added to cart successfully',
        'data' => [
            'user_id' => $user->id,
            'is_guest' => $user->isGuest(),
        ]
    ]);
}
```

### 2. Create Order (Registered Users Only)

```php
// In Controller
public function createOrder(Request $request)
{
    // Middleware will handle the registered user check
    $user = $request->user();
    
    // Create order
    // $order = Order::create([...]);

    return response()->json([
        'success' => true,
        'message' => 'Order created successfully',
    ]);
}
```

### 3. Routes Configuration

```php
// Cart operations (allowed for guests)
// Note: CheckGuestModeMiddleware runs automatically on all API routes
Route::group(['middleware' => ['auth-optional:api']], function () {
    Route::post('add-to-cart', 'addToCart');
    Route::get('cart', 'getCart');
});

// Order operations (registered users only)
Route::group(['middleware' => ['auth:api', 'require-registered']], function () {
    Route::post('orders/create', 'createOrder');
});
```

## Workflow

### 1. Guest User
1. Visits the app without logging in
2. System creates a guest user with `is_guest = true`
3. Can add products to cart
4. When trying to create an order, registration is required
5. After registration, `is_guest` changes to `false`
6. Can now create orders

### 2. Registered User
1. Logs in normally (existing users have `is_guest = false` by default)
2. Can add products to cart and create orders

## Migration

To apply changes to the database:

```bash
# Run migration on all countries
php artisan country:db migrate --all

# Or run migration on specific country
php artisan country:db migrate --country=AE
```

## Messages

The following messages have been added:

### Arabic
- `guest_created_successfully`: "تم إنشاء حساب ضيف بنجاح"
- `guest_registered_successfully`: "تم تسجيل الضيف بنجاح"
- `guest_cannot_create_order`: "يجب التسجيل أولاً"
- `guest_mode_not_allowed`: "وضع الضيف غير مسموح به"

### English
- `guest_created_successfully`: "Guest account created successfully"
- `guest_registered_successfully`: "Guest registered successfully"
- `guest_cannot_create_order`: "Registration required"
- `guest_mode_not_allowed`: "Guest mode is not allowed"

## Usage Examples

### Create Guest User
```bash
curl -X POST http://localhost:8080/api/auth/create-guest \
  -H "Content-Type: application/json" \
  -H "App-Country: AE" \
  -d '{}'
```

### Register (Convert Guest to Registered)
```bash
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -H "App-Country: AE" \
  -H "Authorization: Bearer {token}" \
  -d '{
    "name": "John Doe",
    "mobile": "1234567890",
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Register (New User)
```bash
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -H "App-Country: AE" \
  -d '{
    "name": "John Doe",
    "mobile": "1234567890",
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Register (Already Registered User - Error)
```bash
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -H "App-Country: AE" \
  -H "Authorization: Bearer {registered_user_token}" \
  -d '{
    "name": "John Doe",
    "mobile": "1234567890",
    "email": "john@example.com",
    "password": "password123"
  }'
```

**Response:**
```json
{
    "success": false,
    "message": "User is already registered"
}
```

### Add Product to Cart (Guest)
```bash
curl -X POST http://localhost:8080/api/orders/add-to-cart \
  -H "Content-Type: application/json" \
  -H "App-Country: AE" \
  -d '{"product_id": 1, "quantity": 2}'
```

### Create Order (Registered User)
```bash
curl -X POST http://localhost:8080/api/orders/create \
  -H "Content-Type: application/json" \
  -H "App-Country: AE" \
  -H "Authorization: Bearer {token}" \
  -d '{"items": [{"product_id": 1, "quantity": 2}], "total": 100}'
```

## Important Notes

1. **Multi-Country Databases**: The system works with the multi-country database system
2. **Security**: Guests cannot access sensitive data
3. **Flexibility**: The system can be customized according to application needs
4. **Compatibility**: The system is compatible with the current system and doesn't affect registered users
5. **Default Behavior**: New users are created as registered users (`is_guest = false`) by default
6. **Guest Creation**: Guest users are explicitly created with `is_guest = true` when needed
7. **Guest Mode Setting**: The `guest_mode` setting controls whether guest users can access the application. When disabled, guest users are automatically deleted when they try to access any API route
8. **Automatic Middleware**: `CheckGuestModeMiddleware` is automatically loaded on all API routes via `bootstrap/app.php`. No need to manually add it to route groups

## Troubleshooting

### Issue: Guest cannot add products to cart
**Solution**: Make sure to use `auth-optional:api` middleware

### Issue: Registered user cannot create orders
**Solution**: Make sure `is_guest` = `false` in the database (this is the default value)

### Issue: Migration failed
**Solution**: Make sure to run the command on Docker:
```bash
docker-compose exec app php artisan country:db migrate --all
```

### Issue: Guest user gets deleted when accessing API routes
**Solution**: Check if `guest_mode` setting is enabled in the Settings table. If `guest_mode = false`, guest users will be automatically deleted when accessing any API route (enforced by the automatic `CheckGuestModeMiddleware`).

### Issue: Guest mode setting not working
**Solution**: 
1. Verify the setting exists in the `settings` table with `key = 'guest_mode'`
2. Ensure the value is set correctly (`true` or `false`)
3. Verify `CheckGuestModeMiddleware` is registered in `bootstrap/app.php` in the API middleware prepend array
