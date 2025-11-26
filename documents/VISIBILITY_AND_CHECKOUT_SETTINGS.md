# Visibility and Checkout Settings

## Overview

The Visibility and Checkout Settings system controls which Products, Shops, and Categories are visible to users based on their default address location. The system implements a layered visibility model where entities must have visibility enabled at multiple levels to be accessible.

## Architecture

### Scope Implementation

The system uses Laravel Eloquent Global Scopes to automatically filter entities based on user location:

- **MatchedDefaultAddressScope** - Applied to Products, Shops, and Categories models
- **Location-based filtering** - Uses user's default address (emirate and region)
- **Layered visibility rules** - Enforces visibility at multiple entity levels

### Module Structure

```
Modules/
├── Products/
│   └── App/Models/Scopes/
│       └── MatchedDefaultAddressScope.php
├── Shops/
│   └── App/Models/Scopes/
│       └── MatchedDefaultAddressScope.php
└── Categories/
    └── App/Models/Scopes/
        └── MatchedDefaultAddressScope.php
```

## Database Schema

### Visibility Tables

All visibility tables follow a similar structure:

#### `product_visibilities` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `product_id` | foreignId | References `products.id` |
| `emirate_id` | integer | Emirate identifier |
| `region_ids` | json | Array of region IDs (nullable) |
| `timestamps` | timestamps | Created/updated timestamps |

#### `shop_visibilities` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `shop_id` | foreignId | References `shops.id` |
| `emirate_id` | integer | Emirate identifier |
| `region_ids` | json | Array of region IDs (nullable) |
| `timestamps` | timestamps | Created/updated timestamps |

#### `category_visibilities` Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `category_id` | foreignId | References `categories.id` |
| `emirate_id` | integer | Emirate identifier |
| `region_ids` | json | Array of region IDs (nullable) |
| `timestamps` | timestamps | Created/updated timestamps |

### Visibility Logic

**Emirate Level:**
- `emirate_id` must match the user's default address emirate
- Required for all visibility checks

**Region Level:**
- `region_ids` can be:
  - `NULL` - Visible to all regions in the emirate
  - `[1, 2, 3]` - Visible only to specific regions
- If `region_ids` is `NULL`, the entity is visible to all regions in that emirate
- If `region_ids` contains specific IDs, the user's `region_id` must be in the array

## Visibility Rules

### Product Visibility

A product is visible in a city or region **only if** all of the following are enabled:

1. **Product – Region/City**: Product must have visibility record matching user's emirate and region
2. **Shop – Region/City**: Product's shop must have visibility record matching user's emirate and region
3. **Category – Region/City**: Product's category must have visibility record matching user's emirate and region

**Implementation:**
```php
$builder->where(function (Builder $query) use ($visibilityConstraint) {
    $query->whereHas('productVisibilities', $visibilityConstraint)
        ->whereHas('shop', function (Builder $shopQuery) use ($visibilityConstraint) {
            $shopQuery->whereHas('shopVisibilities', $visibilityConstraint);
        })
        ->whereHas('category', function (Builder $categoryQuery) use ($visibilityConstraint) {
            $categoryQuery->whereHas('categoryVisibilities', $visibilityConstraint);
        });
});
```

**Example:**
- User in Dubai (Emirate: 1, Region: 5)
- Product has visibility: Emirate 1, Region 5 ✓
- Shop has visibility: Emirate 1, Region NULL (all regions) ✓
- Category has visibility: Emirate 1, Region 5 ✓
- **Result**: Product is visible ✓

### Shop Visibility

A shop is visible in a city or region **only if** all of the following are enabled:

1. **Shop – Region/City**: Shop must have visibility record matching user's emirate and region
2. **Category – Region/City**: At least one of the shop's categories must have visibility record matching user's emirate and region

**Implementation:**
```php
$builder->where(function (Builder $query) use ($visibilityConstraint) {
    $query->whereHas('shopVisibilities', $visibilityConstraint)
        ->whereHas('categories', function (Builder $categoryQuery) use ($visibilityConstraint) {
            $categoryQuery->whereHas('categoryVisibilities', $visibilityConstraint);
        });
});
```

**Example:**
- User in Abu Dhabi (Emirate: 2, Region: 10)
- Shop has visibility: Emirate 2, Region 10 ✓
- Shop has Category A: Emirate 2, Region NULL ✓
- **Result**: Shop is visible ✓

### Category Visibility

A category is visible in a city or region **only if** both of the following are enabled:

1. **Category – Region**: Category must have visibility record matching user's emirate
2. **Category – City**: Category must have visibility record matching user's region (or NULL for all regions)

**Implementation:**
```php
$builder->whereHas('categoryVisibilities', $this->buildVisibilityConstraint($defaultAddress));
```

**Example:**
- User in Sharjah (Emirate: 3, Region: 15)
- Category has visibility: Emirate 3, Region 15 ✓
- **Result**: Category is visible ✓

## Core Components

### MatchedDefaultAddressScope

Base scope class that implements location-based filtering:

**Location:**
- `Modules/Products/App/Models/Scopes/MatchedDefaultAddressScope.php`
- `Modules/Shops/App/Models/Scopes/MatchedDefaultAddressScope.php`
- `Modules/Categories/App/Models/Scopes/MatchedDefaultAddressScope.php`

**Key Methods:**

#### `apply(Builder $builder, Model $model): void`

Main scope application method:

```php
public function apply(Builder $builder, Model $model): void
{
    $user = auth('api')->user();
    if (! $user) {
        return; // No filtering for unauthenticated users
    }

    $defaultAddress = $user->defaultAddress;
    if (! $defaultAddress) {
        return; // No filtering if user has no default address
    }

    $visibilityConstraint = $this->buildVisibilityConstraint($defaultAddress);
    
    // Apply entity-specific visibility rules
    // (varies by entity type)
}
```

**Behavior:**
- Only applies to authenticated users (`auth('api')`)
- Requires user to have a default address
- Returns early if user or address is missing (no filtering applied)

#### `buildVisibilityConstraint(object $defaultAddress): Closure`

Builds the reusable visibility constraint closure:

```php
private function buildVisibilityConstraint(object $defaultAddress): Closure
{
    return static function (Builder $query) use ($defaultAddress) {
        $query->where('emirate_id', $defaultAddress->emirate_id)
            ->where(function (Builder $regionQuery) use ($defaultAddress) {
                $regionQuery->whereNull('region_ids');

                if ($defaultAddress->region_id !== null) {
                    $regionQuery->orWhereJsonContains('region_ids', (int) $defaultAddress->region_id);
                }
            });
    };
}
```

**Logic:**
1. Matches `emirate_id` with user's default address emirate
2. Checks region visibility:
   - If `region_ids` is `NULL` → visible to all regions in emirate
   - If `region_ids` contains user's `region_id` → visible to that specific region
   - If user has no `region_id` → only matches `NULL` region_ids

## Usage

### Enabling the Scope

The scope is currently **commented out** in model boot methods. To enable:

**Products:**
```php
// Modules/Products/App/Models/Product.php
protected static function booted()
{
    parent::booted();
    static::addGlobalScope(new ActiveScope());
    static::addGlobalScope(new MatchedDefaultAddressScope()); // Uncomment this
}
```

**Shops:**
```php
// Modules/Shops/App/Models/Shop.php
protected static function booted()
{
    parent::booted();
    static::addGlobalScope(new ActiveScope());
    static::addGlobalScope(new MatchedDefaultAddressScope()); // Uncomment this
}
```

**Categories:**
```php
// Modules/Categories/App/Models/Category.php
protected static function booted()
{
    parent::booted();
    static::addGlobalScope(new ActiveScope());
    static::addGlobalScope(new MatchedDefaultAddressScope()); // Uncomment this
}
```

### Querying Without Scope

To query entities without location filtering:

```php
// Remove scope for specific query
Product::withoutGlobalScope(MatchedDefaultAddressScope::class)->get();

// Or use withoutGlobalScopes to remove all scopes
Product::withoutGlobalScopes()->get();
```

### Manual Visibility Check

Check if an entity is visible to a specific address:

```php
$user = auth('api')->user();
$defaultAddress = $user->defaultAddress;

// Check product visibility
$product = Product::find(1);
$isVisible = $product->productVisibilities()
    ->where('emirate_id', $defaultAddress->emirate_id)
    ->where(function ($q) use ($defaultAddress) {
        $q->whereNull('region_ids')
            ->orWhereJsonContains('region_ids', $defaultAddress->region_id);
    })
    ->exists();
```

## Troubleshooting

### Issue: Products not appearing

**Possible Causes:**
1. Scope not enabled in model boot method
2. User has no default address
3. Missing visibility records at any level (Product, Shop, or Category)
4. Emirate/region mismatch

**Solution:**
```php
// Check if scope is enabled
$product = Product::find(1);
dd($product->toSql()); // Check generated SQL

// Check user's default address
$user = auth('api')->user();
dd($user->defaultAddress);

// Check visibility records
$product = Product::withoutGlobalScope(MatchedDefaultAddressScope::class)->find(1);
dd($product->productVisibilities);
```

### Issue: Scope applying when it shouldn't

**Solution:**
```php
// Remove scope for specific query
Product::withoutGlobalScope(MatchedDefaultAddressScope::class)->get();
```

### Issue: Performance issues

**Solution:**
- Add database indexes:
  ```sql
  CREATE INDEX idx_product_visibilities_emirate ON product_visibilities(emirate_id);
  CREATE INDEX idx_product_visibilities_region ON product_visibilities((CAST(region_ids AS CHAR(255) ARRAY)));
  ```
- Use eager loading to avoid N+1 queries
- Consider caching visibility results for frequently accessed entities

## Related Documentation

- `ACTIVE_SCOPE.md` - Active scope documentation
- `PROJECT_SETUP_GUIDE.md` - General project setup
- `MULTI_COUNTRY_DATABASE_SETUP.md` - Multi-country database configuration

---

**Note**: The visibility system is designed to enforce strict access control. Always test visibility rules thoroughly before enabling scopes in production.

