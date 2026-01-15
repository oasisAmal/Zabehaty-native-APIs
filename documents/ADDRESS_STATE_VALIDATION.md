# Address State Validation System

## Overview

The Address State Validation system ensures that mobile app users always have valid address context for content visibility. When address changes affect content visibility (either from the admin panel or user actions), the backend automatically detects these changes and returns specific status codes and messages to guide the mobile app's user flow.

## Architecture

The system uses middleware-based validation that intercepts API requests and evaluates the user's address state. It compares the current address state with a cached state to detect changes. When address state issues are detected, the middleware returns specific HTTP status codes and messages that guide the mobile app to handle the situation appropriately.

### Key Components

1. **AddressStateEvaluationService**: Evaluates user address state and detects changes via cache comparison
2. **AddressStateMiddleware**: Intercepts API requests and validates address state before allowing access to content endpoints
3. **Cache-Based State Tracking**: Stores last known address state to detect changes between requests

## How It Works

1. **Request Interception**: Middleware runs before controller execution on each API request
2. **User Check**: Verifies user is authenticated and registered (not guest)
3. **Route Exclusion**: Skips validation for excluded routes (address management, auth, public endpoints)
4. **State Evaluation**: Compares current address state with cached state
5. **Response or Continue**: Returns error response if state invalid, otherwise continues to controller

### State Detection Flow

```
First Request:
- No cache exists → Create cache with current state → Request proceeds

Subsequent Requests:
- Compare current state with cache
  - If different → Return status code with action
  - If same → Request proceeds (cache not updated during evaluation)

Address Modifications (via UserAddressService):
- Create/Update/Delete/SetDefault → Update cache immediately

Admin Updates Address Externally:
- Next mobile request detects change → Returns response
```

## API Response Format

When address state validation fails, the API returns:

```json
{
    "status": "error",
    "message": "Address state message",
    "data": {
        "action": "SELECT_ADDRESS|CREATE_ADDRESS|RELOAD_HOME"
    }
}
```

## Response Types

### 1. No Default Address (427 - SELECT_ADDRESS)

**Scenario**: User has active addresses but none is set as default.

**Response:**
```json
{
    "status": "error",
    "message": "Please select a default address",
    "data": {
        "action": "SELECT_ADDRESS"
    }
}
```

**HTTP Status Code**: `427` (Unassigned - Custom usage for address selection required)

**Mobile App Behavior:**
- Show address selection screen
- Allow user to:
  - Select an existing address, or
  - Create a new address
- After address is selected, retry the original request

### 2. No Active Addresses (428 - CREATE_ADDRESS)

**Scenario**: User has no active addresses (all deleted or inactive).

**Response:**
```json
{
    "status": "error",
    "message": "Please create an address",
    "data": {
        "action": "CREATE_ADDRESS"
    }
}
```

**HTTP Status Code**: `428` (Precondition Required)

**Mobile App Behavior:**
- Redirect to create address flow (map location screen)
- After address is created, retry the original request

### 3. Default Address Changed (429 - RELOAD_HOME)

**Scenario**: Different address becomes default (detected by comparing current default with cached state).

**Response:**
```json
{
    "status": "error",
    "message": "Default address has changed. Please reload home screen",
    "data": {
        "action": "RELOAD_HOME"
    }
}
```

**HTTP Status Code**: `429` (Too Many Requests - Custom usage for state change)

**Mobile App Behavior:**
- Clear all cached content
- Reload home screen
- Load content based on new address context
- Retry the original request if needed

### 4. Default Address Location Changed (429 - RELOAD_HOME)

**Scenario**: City (emirate_id) or region (region_id) of default address changed.

**Response:**
```json
{
    "status": "error",
    "message": "Address location has changed. Please reload home screen",
    "data": {
        "action": "RELOAD_HOME"
    }
}
```

**HTTP Status Code**: `429` (Too Many Requests - Custom usage for state change)

**Mobile App Behavior:**
- Clear all cached content
- Reload home screen
- Load content based on new location context
- Retry the original request if needed

## Route Exclusions

The middleware automatically skips validation for the following routes:

- **Address Management Routes**: `/api/addresses/*`
  - Allows users to create, update, delete, and set default addresses without validation conflicts

- **Auth Routes**: `/api/auth/*`
  - Login, register, OTP verification, etc.

- **Public App Routes**: `/api/app/*`
  - Public configuration and settings endpoints

## Implementation Details

### Cache-Based State Tracking

The system uses Laravel cache to track address state:

- **Cache Key**: `address_state_{user_id}`
- **Cache Data**: 
  ```php
  [
      'default_address_id' => int|null,
      'emirate_id' => int|null,
      'region_id' => int|null,
  ]
  ```
- **Cache TTL**: 24 hours
- **Cache Updates**: Only updated when addresses are modified (create, update, delete, set default)

### State Comparison Logic

1. **First Request**: No cache exists → Create cache with current state → Proceed
2. **Subsequent Requests**: 
   - Compare `default_address_id` → If changed → Return `RELOAD_HOME`
   - Compare `emirate_id` and `region_id` → If changed → Return `RELOAD_HOME`
   - If no changes → Proceed (cache not updated during evaluation)
3. **Address Modifications**: Cache is updated immediately after:
   - Creating a new address
   - Updating an existing address
   - Deleting an address
   - Setting a default address

### Cache Update Strategy

The cache is **only updated when addresses are modified**, not during evaluation:

- **During Evaluation**: Cache is read-only for comparison purposes
- **On Address Changes**: Cache is updated immediately after:
  - `UserAddressService::create()` - After creating a new address
  - `UserAddressService::update()` - After updating an address
  - `UserAddressService::delete()` - After deleting an address
  - `UserAddressService::setDefault()` - After setting a default address
- **First Request**: If cache doesn't exist and state is valid, cache is created for future comparisons

This approach ensures:
- More efficient: No unnecessary cache writes during evaluation
- Accurate change detection: Cache reflects actual address state
- External updates detected: Admin system changes are detected on next request

### Edge Cases

1. **Guest Users**: Validation is skipped (guests don't have address requirements)
2. **Concurrent Requests**: Each request evaluates independently, cache updates are atomic
3. **Soft Deletes**: System checks `is_active` flag, not just existence
4. **First Login**: New users without addresses - cache will be created on first valid request
5. **Cache Miss**: If cache doesn't exist, create it with current state and proceed (only on first request)
6. **External Updates**: Admin system updates address → Next mobile request detects change via cache comparison
7. **Cache Not Updated During Evaluation**: Cache is only updated when addresses are modified, ensuring efficient operation

### Error Handling Best Practices

1. **Always Check Status Code**: Verify both HTTP status code and `data.action` field
2. **User Feedback**: Display the `message` field to inform users what action is needed
3. **Retry Logic**: After handling the action, retry the original request
4. **Cache Management**: Clear relevant caches when `RELOAD_HOME` action is received
5. **State Persistence**: Save the action type if user needs to complete it later

## Testing Scenarios

### Test Cases

1. **No Default Address**
   - Setup: User with active addresses but no default
   - Expected: 427 with `SELECT_ADDRESS` action

2. **No Active Addresses**
   - Setup: User with no active addresses
   - Expected: 428 with `CREATE_ADDRESS` action

3. **Default Address Changed**
   - Setup: User changes default address via admin panel
   - Expected: 429 with `RELOAD_HOME` action on next request

4. **Location Changed**
   - Setup: Admin updates default address location (city/region)
   - Expected: 429 with `RELOAD_HOME` action on next request

5. **Valid State**
   - Setup: User with valid default address
   - Expected: Request proceeds normally

6. **Guest User**
   - Setup: Guest user making request
   - Expected: Request proceeds (no validation)

7. **Address Endpoints**
   - Setup: Request to `/api/addresses/*`
   - Expected: Request proceeds (excluded from validation)

8. **Auth Endpoints**
   - Setup: Request to `/api/auth/*`
   - Expected: Request proceeds (excluded from validation)

## Language Support

All messages are available in English and Arabic:

- `address_state_no_default`: "Please select a default address" / "يرجى اختيار عنوان افتراضي"
- `address_state_no_active`: "Please create an address" / "يرجى إنشاء عنوان"
- `address_state_default_changed`: "Default address has changed. Please reload home screen" / "تم تغيير العنوان الافتراضي. يرجى إعادة تحميل الشاشة الرئيسية"
- `address_state_location_changed`: "Address location has changed. Please reload home screen" / "تم تغيير موقع العنوان. يرجى إعادة تحميل الشاشة الرئيسية"

## Performance Considerations

- **Lightweight Evaluation**: Single query for default address (already loaded via `$user->defaultAddress` relationship)
- **Cache Operations**: Fast cache read operations during evaluation (write only on address modifications)
- **Cache TTL**: 24 hours (not refreshed on each request, only updated when addresses change)
- **Efficient Updates**: Cache is only written when addresses are actually modified, not on every evaluation
- **Excluded Routes**: No overhead for address management, auth, or public routes
- **Guest Users**: Skip evaluation entirely
- **No Database Writes**: Pure cache-based state tracking

## Integration with Visibility System

This system works seamlessly with the existing visibility scopes:

- **MatchedDefaultAddressScope**: Filters content based on `emirate_id` and `region_id`
- **Content Visibility**: Products, shops, categories, and dynamic sections are filtered based on default address
- **Address Changes**: When address changes affect visibility, the middleware ensures users reload content appropriately

## Troubleshooting

### Common Issues

1. **Cache Not Updating**
   - Check cache driver configuration
   - Verify cache key format: `address_state_{user_id}`

2. **False Positives**
   - Ensure cache is being updated after address changes
   - Check for concurrent request issues

3. **Missing Responses**
   - Verify middleware is registered in `bootstrap/app.php`
   - Check route exclusions are correct

4. **Guest User Issues**
   - Ensure guest users are properly identified
   - Verify `isGuest()` method works correctly

## Related Documentation

- [Visibility and Checkout Settings](./VISIBILITY_AND_CHECKOUT_SETTINGS.md)
- [Homepage Module](./HOMEPAGE_MODULE.md)
- [Dynamic Categories Module](./DYNAMIC_CATEGORIES_MODULE.md)
- [Dynamic Shops Module](./DYNAMIC_SHOPS_MODULE.md)

## Support

For questions or issues related to address state validation, please contact the backend team.
