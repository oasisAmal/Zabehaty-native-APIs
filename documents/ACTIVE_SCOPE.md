# Active Scope

## Overview

The Active Scope system filters entities (Products, Shops, and Categories) to show only active and valid records. Each entity type has specific activation criteria that must be met for the entity to be considered "active" and visible in the system.

## Architecture

### Scope Implementation

The system uses Laravel Eloquent Global Scopes to automatically filter inactive entities:

- **ActiveScope** - Applied to Products, Shops, and Categories models
- **Entity-specific rules** - Each entity type has unique activation criteria
- **Automatic filtering** - Inactive entities are automatically excluded from queries

### Module Structure

```
Modules/
├── Products/
│   └── App/Models/Scopes/
│       └── ActiveScope.php
├── Shops/
│   └── App/Models/Scopes/
│       └── ActiveScope.php
└── Categories/
    └── App/Models/Scopes/
        └── ActiveScope.php
```

## Active Scope Rules

### Product Active Scope

A product is considered active **only if** all of the following conditions are met:

1. **Has Department**: Product must have an associated department
2. **Is Active**: `products.is_active` must be `true`
3. **Shop Validation**: Product must either:
   - Have no shop (`shop_id` is `NULL`), OR
   - Have a valid shop (shop exists and is active)
4. **Is Approved**: `products.is_approved` must be `true`
5. **Has Price or Sub-Products**: Product must either:
   - Have a price greater than 0 (`products.price > 0`), OR
   - Have at least one sub-product

**Implementation:**
```php
public function apply(Builder $builder, Model $model)
{
    return $builder->whereHas('department')
        ->where('products.is_active', true)
        ->where(function (Builder $q) {
            $q->whereNull('products.shop_id')
                ->orWhereHas('shop');
        })
        ->where('products.is_approved', true)
        ->where(function (Builder $q) {
            $q->where('products.price', '>', 0)
                ->orWhereHas('subProducts');
        });
}
```

**Location:** `Modules/Products/App/Models/Scopes/ActiveScope.php`

**Activation Criteria:**
- ✓ Has department relationship
- ✓ `is_active = true`
- ✓ Either no shop OR shop exists
- ✓ `is_approved = true`
- ✓ Either `price > 0` OR has sub-products

### Shop Active Scope

A shop is considered active **only if**:

1. **Is Active**: `shops.is_active` must be `true`

**Implementation:**
```php
public function apply(Builder $builder, Model $model)
{
    return $builder->where('shops.is_active', true);
}
```

**Location:** `Modules/Shops/App/Models/Scopes/ActiveScope.php`

**Activation Criteria:**
- ✓ `is_active = true`

### Category Active Scope

A category is considered active **only if**:

1. **Is Active**: `categories.is_active` must be `true`

**Implementation:**
```php
public function apply(Builder $builder, Model $model)
{
    return $builder->where('categories.is_active', true);
}
```

**Location:** `Modules/Categories/App/Models/Scopes/ActiveScope.php`

**Activation Criteria:**
- ✓ `is_active = true`

## Database Schema

### Products Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `is_active` | boolean | Product active status |
| `is_approved` | boolean | Product approval status |
| `price` | decimal | Product price (can be 0 if has sub-products) |
| `shop_id` | foreignId | Optional shop reference (nullable) |
| `department_id` | foreignId | Required department reference |
| `timestamps` | timestamps | Created/updated timestamps |

**Required Relationships:**
- `department` - BelongsTo Category (required)
- `shop` - BelongsTo Shop (optional)
- `subProducts` - HasMany SubProduct (optional)

### Shops Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `is_active` | boolean | Shop active status |
| `timestamps` | timestamps | Created/updated timestamps |

### Categories Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `is_active` | boolean | Category active status |
| `timestamps` | timestamps | Created/updated timestamps |

## Usage

### Enabling the Scope

The scope is **enabled by default** in model boot methods:

**Products:**
```php
// Modules/Products/App/Models/Product.php
protected static function booted()
{
    parent::booted();
    static::addGlobalScope(new ActiveScope()); // Enabled
}
```

**Shops:**
```php
// Modules/Shops/App/Models/Shop.php
protected static function booted()
{
    parent::booted();
    static::addGlobalScope(new ActiveScope()); // Enabled
}
```

**Categories:**
```php
// Modules/Categories/App/Models/Category.php
protected static function booted()
{
    parent::booted();
    static::addGlobalScope(new ActiveScope()); // Enabled
}
```

### Querying Without Scope

To query entities including inactive ones:

```php
// Remove scope for specific query
Product::withoutGlobalScope(ActiveScope::class)->get();

// Or use withoutGlobalScopes to remove all scopes
Product::withoutGlobalScopes()->get();

// Get only inactive products
Product::withoutGlobalScope(ActiveScope::class)
    ->where('is_active', false)
    ->get();
```

### Checking Active Status

**Check if product is active:**
```php
$product = Product::find(1);
$isActive = $product->is_active 
    && $product->is_approved 
    && $product->department 
    && ($product->price > 0 || $product->subProducts->isNotEmpty())
    && ($product->shop_id === null || $product->shop);
```

**Check if shop is active:**
```php
$shop = Shop::find(1);
$isActive = $shop->is_active;
```

**Check if category is active:**
```php
$category = Category::find(1);
$isActive = $category->is_active;
```

## Best Practices

### 1. Product Activation

- **Always set department** when creating products
- **Set `is_approved = true`** after admin approval
- **Either set price OR add sub-products** (not both required, but at least one)
- **Validate shop exists** if `shop_id` is set

### 2. Data Integrity

- **Use database constraints** to ensure required relationships
- **Set default values** for boolean fields:
  ```php
  $table->boolean('is_active')->default(false);
  $table->boolean('is_approved')->default(false);
  ```

### 3. Performance

- **Index active status fields**:
  ```sql
  CREATE INDEX idx_products_is_active ON products(is_active);
  CREATE INDEX idx_products_is_approved ON products(is_approved);
  CREATE INDEX idx_shops_is_active ON shops(is_active);
  CREATE INDEX idx_categories_is_active ON categories(is_active);
  ```

### 4. Testing

- **Test all activation criteria** for products
- **Test edge cases**: NULL shop_id, price = 0, etc.
- **Test with and without scope** to verify filtering

## Activation Workflow

### Product Activation Flow

1. **Create Product** → `is_active = false`, `is_approved = false`
2. **Set Department** → Required for activation
3. **Set Price or Add Sub-Products** → Required for activation
4. **Set Shop (Optional)** → If set, shop must exist
5. **Admin Approval** → Set `is_approved = true`
6. **Activate** → Set `is_active = true`
7. **Product is Active** → Visible in system

### Shop Activation Flow

1. **Create Shop** → `is_active = false`
2. **Activate** → Set `is_active = true`
3. **Shop is Active** → Visible in system

### Category Activation Flow

1. **Create Category** → `is_active = false`
2. **Activate** → Set `is_active = true`
3. **Category is Active** → Visible in system

## Common Scenarios

### Scenario 1: Deactivating a Product

```php
$product = Product::find(1);
$product->is_active = false;
$product->save();

// Product will no longer appear in queries
Product::all(); // Excludes this product
```

### Scenario 2: Bulk Activation

```php
// Activate multiple products
Product::withoutGlobalScope(ActiveScope::class)
    ->whereIn('id', [1, 2, 3])
    ->update(['is_active' => true]);
```

### Scenario 3: Finding Inactive Products

```php
// Get all inactive products
$inactiveProducts = Product::withoutGlobalScope(ActiveScope::class)
    ->where('is_active', false)
    ->get();
```

### Scenario 4: Product with Missing Requirements

```php
// Check why product is not active
$product = Product::withoutGlobalScope(ActiveScope::class)->find(1);

$issues = [];
if (!$product->department) {
    $issues[] = 'Missing department';
}
if (!$product->is_active) {
    $issues[] = 'Not active';
}
if (!$product->is_approved) {
    $issues[] = 'Not approved';
}
if ($product->price <= 0 && $product->subProducts->isEmpty()) {
    $issues[] = 'No price or sub-products';
}
if ($product->shop_id && !$product->shop) {
    $issues[] = 'Invalid shop';
}
```

## Troubleshooting

### Issue: Products not appearing

**Possible Causes:**
1. `is_active = false`
2. `is_approved = false`
3. Missing department
4. No price and no sub-products
5. Invalid shop reference

**Solution:**
```php
// Check product status
$product = Product::withoutGlobalScope(ActiveScope::class)->find(1);
dd([
    'is_active' => $product->is_active,
    'is_approved' => $product->is_approved,
    'has_department' => $product->department !== null,
    'has_price' => $product->price > 0,
    'has_sub_products' => $product->subProducts->isNotEmpty(),
    'shop_valid' => $product->shop_id === null || $product->shop !== null,
]);
```

### Issue: Scope not applying

**Solution:**
- Verify scope is enabled in model boot method
- Check if scope was removed: `withoutGlobalScope(ActiveScope::class)`
- Clear config cache: `php artisan config:clear`

### Issue: Performance issues

**Solution:**
- Add database indexes on `is_active` and `is_approved` columns
- Use eager loading for relationships:
  ```php
  Product::with(['department', 'shop', 'subProducts'])->get();
  ```

## Related Documentation

- `VISIBILITY_AND_CHECKOUT_SETTINGS.md` - Visibility scope documentation
- `PROJECT_SETUP_GUIDE.md` - General project setup
- `MULTI_COUNTRY_DATABASE_SETUP.md` - Multi-country database configuration

---

**Note**: The Active Scope is enabled by default and automatically filters inactive entities. Always use `withoutGlobalScope()` when you need to query inactive records for administrative purposes.

