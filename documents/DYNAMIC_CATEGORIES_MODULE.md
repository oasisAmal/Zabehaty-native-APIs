# Dynamic Categories Module

## Overview

The Dynamic Categories module is a flexible and extensible system for managing dynamic category-specific content sections in the Zabehaty Native APIs application. It supports multiple section types (banners, products, shops, menu items, limited-time offers) with multi-language support, caching, and location-based filtering. Unlike the HomePage module, this module is category-specific and requires a `category_id` parameter to filter sections.

## Architecture

### Module Structure

The module follows Laravel Modules structure with clear separation of concerns:

```
Modules/DynamicCategories/
├── App/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── DynamicCategoriesController.php
│   │   └── Requests/
│   │       └── DynamicCategoriesIndexRequest.php
│   ├── Models/
│   │   ├── Attributes/
│   │   │   └── DynamicCategorySectionAttributes.php
│   │   ├── Relationships/
│   │   │   └── DynamicCategorySectionRelationships.php
│   │   ├── Scopes/
│   │   │   ├── DynamicCategorySectionScopes.php
│   │   │   └── MatchedDefaultAddressScope.php
│   │   ├── DynamicCategorySection.php
│   │   └── DynamicCategorySectionItem.php
│   ├── Providers/
│   │   ├── DynamicCategoriesServiceProvider.php
│   │   ├── RouteServiceProvider.php
│   │   └── EventServiceProvider.php
│   ├── Services/
│   │   ├── Builders/
│   │   │   ├── Factories/
│   │   │   │   └── SectionBuilderFactory.php
│   │   │   ├── Interfaces/
│   │   │   │   └── SectionBuilderInterface.php
│   │   │   ├── Sections/
│   │   │   │   ├── BannerSectionBuilder.php
│   │   │   │   ├── MenuItemsSectionBuilder.php
│   │   │   │   ├── DefaultSectionBuilder.php
│   │   │   │   ├── ProductSectionBuilder.php
│   │   │   │   └── ShopSectionBuilder.php
│   │   │   └── SectionBuilder.php
│   │   ├── Cache/
│   │   │   └── CacheService.php
│   │   └── DynamicCategoriesService.php
│   └── Transformers/
│       └── DynamicCategoriesResource.php
├── Database/
│   └── Migrations/
│       ├── create_dynamic_category_sections_table.php
│       └── create_dynamic_category_section_items_table.php
├── Enums/
│   └── DynamicCategorySectionType.php
├── Routes/
│   └── api.php
├── Config/
│   └── config.php
└── Lang/
    ├── en/
    │   └── messages.php
    └── ar/
        └── messages.php
```

## Database Schema

### `dynamic_category_sections` Table

Stores category-specific sections configuration:

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `category_id` | integer | Foreign key to categories table (required) |
| `emirate_id` | integer | Optional emirate filter |
| `region_ids` | json | Optional region IDs array |
| `title_en` | string | English title |
| `title_ar` | string | Arabic title |
| `title_image_ar_url` | string | Arabic title image URL |
| `title_image_en_url` | string | English title image URL |
| `background_image_url` | string | Section background image |
| `type` | string | Section type (indexed) |
| `display_type` | string | Display type for frontend rendering |
| `menu_type` | string | Menu type for navigation |
| `banner_size` | enum | Banner size: small, medium, large |
| `sorting` | smallint | Display order (default: 0) |
| `timestamps` | timestamps | Created/updated timestamps |

**Section Types:**
- `banners` - Banner carousel sections
- `menu_items` - Mixed menu items (products or shops)
- `shops` - Shop listing sections
- `products` - Product listing sections
- `limited_time_offers` - Special offer sections

**Key Differences from HomePage:**
- Requires `category_id` for filtering sections
- Includes `display_type` and `menu_type` fields for additional frontend configuration
- Sections are filtered by category before being returned

### `dynamic_category_section_items` Table

Stores items (polymorphic) associated with each section:

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `dynamic_category_section_id` | foreignId | References `dynamic_category_sections.id` |
| `menu_item_parent_id` | integer | Parent ID for grouping menu items (used by MenuItemsSectionBuilder) |
| `title_en` | string | English title for menu items |
| `title_ar` | string | Arabic title for menu items |
| `image_ar_url` | string | Arabic item image URL |
| `image_en_url` | string | English item image URL |
| `item_id` | bigint | Polymorphic item ID |
| `item_type` | string | Polymorphic item type |
| `external_link` | string | Optional external link URL |
| `sorting` | smallint | Item display order |
| `timestamps` | timestamps | Created/updated timestamps |

**Polymorphic Relations:**
- Can link to Product, Shop, or Category models
- Menu items sections use `menu_item_parent_id` to group related items

## Core Components

### 1. Models

#### DynamicCategorySection Model

Main model for category-specific sections with traits for:
- **CountryDatabaseTrait**: Multi-country database support
- **TraitLanguage**: Multi-language support
- **DynamicCategorySectionAttributes**: Custom attribute accessors
- **DynamicCategorySectionRelationships**: Model relationships
- **DynamicCategorySectionScopes**: Query scopes
- **MatchedDefaultAddressScope**: Location-based filtering

**Key Methods:**
```php
// Get ordered sections for a category
DynamicCategorySection::ordered()
    ->where('category_id', $categoryId)
    ->get();

// Get sections with items
DynamicCategorySection::with('items.item')->get();
```

#### DynamicCategorySectionItem Model

Polymorphic pivot model linking sections to items:

```php
// Get item relationship
$item->item; // Returns polymorphic model (Product, Shop, etc.)

// Get section relationship
$item->section; // Returns DynamicCategorySection
```

### 2. Services

#### DynamicCategoriesService

Main service orchestrating dynamic categories data building:

```php
public function getDynamicCategoriesData($request): array
{
    // 1. Extract category_id from request
    // 2. Check cache
    // 3. Build all sections for the category
    // 4. Store in cache
    // 5. Return data
}
```

**Responsibilities:**
- Cache management (includes `category_id` in cache key)
- Coordinating section builders
- Determining location context (resolves `emirate_id` and `region_id` from the authenticated user's default address, falling back to global `0` values)
- Country and language handling
- Category-specific filtering

**Key Difference from HomePage:**
- Requires `category_id` parameter
- Cache keys include `category_id` for category-specific caching
- Returns empty sections array if `category_id` is missing

#### SectionBuilder

Builds all category-specific sections using factory pattern, and preloads morph items without the heavy `MatchedDefaultAddressScope` to improve performance:

```php
public function buildAll(int $categoryId): array
{
    $dynamicCategorySections = DynamicCategorySection::ordered()
        ->where('category_id', $categoryId)
        ->has('items')
        ->with('items')
        ->get();

    $dynamicCategorySections->loadMorph('items.item', [
        Product::class => fn ($query) => $query->withoutGlobalScope(ProductMatchedDefaultAddressScope::class),
        Shop::class => fn ($query) => $query->withoutGlobalScope(ShopMatchedDefaultAddressScope::class),
        Category::class => fn ($query) => $query->withoutGlobalScope(CategoryMatchedDefaultAddressScope::class),
    ]);

    return $dynamicCategorySections
        ->map(fn ($section) => $this->buildSection($section))
        ->filter(fn ($section) => !empty($section['items']))
        ->values()
        ->toArray();
}
```

**Process:**
1. Fetch ordered sections filtered by `category_id` with items
2. `loadMorph` items to drop `MatchedDefaultAddressScope` on products/shops/categories
3. Uses factory to get appropriate builder
4. Builds each section data, filters empty sections
5. Returns array of sections with additional fields (`display_type`, `menu_type`)

#### SectionBuilderFactory

Factory pattern implementation for section builders:

```php
public function create($type): SectionBuilderInterface
{
    return match ($type) {
        DynamicCategorySectionType::BANNERS => new BannerSectionBuilder(),
        DynamicCategorySectionType::SHOPS => new ShopSectionBuilder(),
        DynamicCategorySectionType::MENU_ITEMS => new MenuItemsSectionBuilder(),
        DynamicCategorySectionType::PRODUCTS => new ProductSectionBuilder(),
        default => new DefaultSectionBuilder(),
    };
}
```

**Supported Builders:**
- `BannerSectionBuilder` - For banner sections
- `ShopSectionBuilder` - For shop sections
- `MenuItemsSectionBuilder` - For mixed menu items (products or shops)
- `ProductSectionBuilder` - For product and offer sections
- `DefaultSectionBuilder` - Fallback for unknown types

### 3. Section Builders

All section builders implement `SectionBuilderInterface`:

```php
interface SectionBuilderInterface
{
    public function build(DynamicCategorySection $dynamicCategorySection): array;
}
```

#### MenuItemsSectionBuilder

**Unique Feature:** This builder groups menu items by `menu_item_parent_id` and returns menu group data. It uses optimized database-level grouping for better performance.

Builds menu items sections by grouping items by parent ID using efficient database queries:

```php
public function build(DynamicCategorySection $dynamicCategorySection): array
{
    $menuGroupIds = $dynamicCategorySection->items()
        ->selectRaw('MIN(id) as id')
        ->groupBy('menu_item_parent_id')
        ->pluck('id');

    return $dynamicCategorySection->items()
        ->whereIn('id', $menuGroupIds)
        ->get()
        ->map(function ($menuGroup) {
            return new DynamicCategoryMenuResource($menuGroup);
        })
        ->filter()
        ->values()
        ->toArray();
}
```

**Performance Optimization:**
- Uses database-level `GROUP BY` with `MIN(id)` to get one item per `menu_item_parent_id`
- Compatible with MySQL's `only_full_group_by` mode
- Fetches only the required items (not all items), reducing memory usage
- Two efficient queries instead of loading all items into memory

**Response Format:**
Each item in the menu items section includes:
- `id`: The `menu_item_parent_id` value
- `title`: The menu item title (language-aware)
- `image_url`: Language-specific image URL (Arabic or English based on `App-Language` header)

**Example Response:**
```json
[
    {
        "id": 1,
        "title": "Menu Group 1",
        "image_url": "https://example.com/image-en.png"
    },
    {
        "id": 2,
        "title": "Menu Group 2",
        "image_url": "https://example.com/image-ar.png"
    }
]
```

#### ProductSectionBuilder

Builds product sections with pagination, reusing preloaded items and removing `MatchedDefaultAddressScope` when loading morphs. Filters out null items before taking the pagination limit to ensure the correct number of valid items are returned:

```php
public function build(DynamicCategorySection $dynamicCategorySection): array
{
    return $this->resolveItems($dynamicCategorySection)
        ->filter(function ($item) {
            return $item->item !== null;
        })
        ->take(Pagination::PER_PAGE)
        ->map(function ($item) {
            return new ProductCardResource($item->item);
        })
        ->values()
        ->toArray();
}
```

**Performance Note:** Filtering before `take()` ensures that if the first N items have null `item` relationships, the builder will continue searching through the collection to find the required number of valid items, rather than returning fewer items than requested.

#### ShopSectionBuilder

Builds shop sections, reusing preloaded items and removing `MatchedDefaultAddressScope` when loading morphs. Filters out null items before taking the pagination limit to ensure the correct number of valid items are returned:

```php
public function build(DynamicCategorySection $dynamicCategorySection): array
{
    return $this->resolveItems($dynamicCategorySection)
        ->filter(function ($item) {
            return $item->item !== null;
        })
        ->take(Pagination::PER_PAGE)
        ->map(function ($item) {
            return new ShopCardResource($item->item);
        })
        ->values()
        ->toArray();
}
```

**Performance Note:** Filtering before `take()` ensures that if the first N items have null `item` relationships, the builder will continue searching through the collection to find the required number of valid items, rather than returning fewer items than requested.

#### BannerSectionBuilder

Builds banner sections (returns raw banner data):

```php
public function build(DynamicCategorySection $dynamicCategorySection): array
{
    return $dynamicCategorySection->items()
        ->with('item')
        ->limit(Pagination::PER_PAGE)
        ->get()
        ->map(function ($item) {
            return new HomeBannerResource($item);
        })
        ->filter()
        ->toArray();
}
```

### 4. Caching

#### CacheService

Manages dynamic categories data caching with category-specific cache keys:

**Cache Keys:**
```php
"dynamic_categories:category_id:{categoryId}:emirate_id:{emirateId}:region_id:{regionId}:lang:{lang}"
```

**Methods:**
- `getDynamicCategoriesData(int $categoryId, int $emirateId, int $regionId, string $lang)` - Retrieve cached payload for a category/location/language tuple
- `storeDynamicCategoriesData(int $categoryId, int $emirateId, int $regionId, array $data, string $lang, ?int $ttl = null)` - Persist rendered data using the configured TTL fallback
- `clearDynamicCategoriesCache(int $categoryId, int $emirateId, int $regionId, ?string $lang = null)` - Clear cache for a specific category/location (all languages when `lang` is null)
- `clearAllDynamicCategoriesCache()` - Clear cache across all categories/emirates/regions and supported languages
- `isCacheEnabled()` - Check if caching toggle is turned on (`dynamiccategories.cache.enabled`)

**Cache Configuration:**
- Default TTL pulled from `dynamiccategories.cache.default_ttl` (3600 seconds by default)
- Cache disabled in local environment
- Cache enabled when `cache.default !== 'null'`

**Key Difference from HomePage:**
- Cache keys include `category_id` for category-specific caching
- Each category has its own cache entry

### 5. Transformers

#### DynamicCategoriesResource

API resource transformer:

```php
public function toArray(Request $request): array
{
    return [
        'sections' => $this->resource['sections'],
    ];
}
```

**Key Difference from HomePage:**
- No header data (only sections)
- Sections are pre-filtered by category

### 6. Validation

#### DynamicCategoriesIndexRequest

Form request validation for the index endpoint:

**Validation Rules:**
- `category_id`: Required, integer, must exist in `categories` table

**Example:**
```php
public function rules(): array
{
    return [
        'category_id' => ['required', 'integer', 'exists:categories,id'],
    ];
}
```

## API Endpoints

### Get Dynamic Categories Data

```
POST /api/dynamic-categories
```

**Authentication:** Required (`auth:api`)

**Headers:**
```
App-Country: AE
App-Platform: iOS
App-Version: 1.0.0
App-Language: en
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "category_id": 1
}
```

**Response:**
```json
{
    "status": "success",
    "message": null,
    "data": {
        "sections": [
            {
                "id": 1,
                "type": "menu_items",
                "title": "Featured Items",
                "title_image_url": "https://example.com/title.png",
                "background_image_url": "https://example.com/bg.png",
                "display_type": "grid",
                "menu_type": "horizontal",
                "banner_size": "",
                "sorting": 1,
                "items": [
                    {
                        "id": 1,
                        "title": "Menu Group 1",
                        "image_url": "https://example.com/menu-1-en.png"
                    },
                    {
                        "id": 2,
                        "title": "Menu Group 2",
                        "image_url": "https://example.com/menu-2-en.png"
                    }
                ]
            },
            {
                "id": 2,
                "type": "products",
                "title": "Featured Products",
                "title_image_url": null,
                "background_image_url": null,
                "display_type": "",
                "menu_type": "",
                "banner_size": "",
                "sorting": 2,
                "items": [
                    {
                        "id": 1,
                        "name": "Product Name",
                        ...
                    }
                ]
            }
        ]
    }
}
```

**Error Response (Missing category_id):**
```json
{
    "status": "error",
    "message": "The category ID is required.",
    "data": null
}
```

**Error Response (Invalid category_id):**
```json
{
    "status": "error",
    "message": "The selected category ID is invalid.",
    "data": null
}
```

**Error Response (Server Error):**
```json
{
    "status": "error",
    "message": "Failed to retrieve dynamic categories data",
    "data": null
}
```

## Usage Examples

The Dynamic Categories module can be managed through the backend admin interface. Below are examples of common operations:

### 1. Creating a Menu Items Section

**Backend Interface Steps:**
1. Navigate to **Dynamic Categories** → **Select a Category** → **Create New Section**
2. Fill in the section details:
   - **Category**: Select the target category
   - **Title (English)**: "Featured Items"
   - **Title (Arabic)**: "العناصر المميزة"
   - **Section Type**: Select "menu_items"
   - **Display Type**: Select display style (e.g., "grid", "list")
   - **Menu Type**: Select menu style (e.g., "horizontal", "vertical")
   - **Sorting**: Enter `1` (lower numbers appear first)
   - **Title Image (English)**: Upload or enter URL for English title image
   - **Title Image (Arabic)**: Upload or enter URL for Arabic title image
   - **Background Image**: Optional background image URL
   - **Emirate**: Optional - select specific emirate for location filtering
   - **Regions**: Optional - select specific regions (JSON array)

3. **Add Items to Section:**
   - Click "Add Items" or navigate to section items
   - For each menu item, set:
     - **Title (English)**: Menu item title in English
     - **Title (Arabic)**: Menu item title in Arabic
     - **Menu Item Parent ID**: Group ID for grouping related menu items (items with the same parent ID will be grouped together)
     - **Image (English)**: Custom image URL for English version
     - **Image (Arabic)**: Custom image URL for Arabic version
     - **External Link**: Optional external URL
     - **Item ID & Item Type**: Optional polymorphic item reference (Product, Shop, or Category)
   - Save items

**Result:** A menu items section will appear for the selected category showing grouped menu items. Items with the same `menu_item_parent_id` will be grouped together, and the builder will return one representative item per group with its title and image. The frontend will receive menu groups with `id` (parent ID), `title`, and `image_url` fields.

### 2. Creating a Product Section

**Backend Interface Steps:**
1. Navigate to **Dynamic Categories** → **Select a Category** → **Create New Section**
2. Fill in the section details:
   - **Category**: Select the target category
   - **Title (English)**: "Featured Products"
   - **Title (Arabic)**: "المنتجات المميزة"
   - **Section Type**: Select "products"
   - **Sorting**: Enter `2`
   - **Display Type**: Optional display configuration
   - **Menu Type**: Optional menu configuration

3. **Add Products:**
   - Click "Add Items"
   - Select products from the product list
   - Products will be displayed in the section
   - Save items

**Result:** A product section will appear for the selected category showing the selected products.

### 3. Creating a Banner Section

**Backend Interface Steps:**
1. Navigate to **Dynamic Categories** → **Select a Category** → **Create New Section**
2. Fill in the section details:
   - **Category**: Select the target category
   - **Title (English)**: "Category Banners"
   - **Title (Arabic)**: "بانرات الفئة"
   - **Section Type**: Select "banners"
   - **Banner Size**: Select "large", "medium", or "small"
   - **Sorting**: Enter `0` (to appear at the top)

3. **Add Banners:**
   - Click "Add Items"
   - Select banners from the banner list
   - Each banner will be displayed in the carousel
   - Save items

**Result:** A banner carousel section will appear for the selected category.

### 4. Category-Specific Filtering

**Key Feature:** All sections are automatically filtered by `category_id`. When a user requests dynamic categories data, only sections belonging to the specified category are returned.

**Backend Interface Steps:**
1. When creating a section, you must select a **Category**
2. The section will only appear when that specific category is requested
3. Multiple sections can belong to the same category
4. Sections are ordered by `sorting` within each category

**Result:** Each category has its own set of sections, providing category-specific content customization.

### 5. Location-Based Filtering

**Backend Interface Steps:**
1. Edit an existing section or create a new one
2. **For Emirate-Specific Sections:**
   - Select an **Emirate** from the dropdown
   - Section will only show for users in that emirate

3. **For Region-Specific Sections:**
   - Select multiple **Regions** (stored as JSON array)
   - Section will only show for users in those specific regions

4. **For Global Sections:**
   - Leave **Emirate** and **Regions** empty
   - Section will show for all users regardless of location (within the category)

**Result:** Sections are automatically filtered based on user's default address location AND category.

### 6. Menu Items Grouping

**Frontend Implementation:**
When receiving menu items sections, the frontend receives grouped menu items:

```javascript
section.items.forEach(menuGroup => {
    // Each menuGroup has: id, title, image_url
    renderMenuGroup(menuGroup);
});
```

**Backend Configuration:**
- Items are grouped by `menu_item_parent_id` field
- The builder uses optimized database queries to group items efficiently
- Each group represents a menu item with its own title and image
- Language-specific images are automatically selected based on `App-Language` header

### 7. Display Type and Menu Type

**Display Type:**
- Used for frontend rendering configuration
- Examples: "grid", "list", "carousel"
- Stored as string in database
- Returned in API response

**Menu Type:**
- Used for navigation/menu configuration
- Examples: "horizontal", "vertical", "dropdown"
- Stored as string in database
- Returned in API response

**Usage:**
```json
{
    "display_type": "grid",
    "menu_type": "horizontal"
}
```

The frontend can use these fields to determine how to render the section.

## Configuration

### Module Configuration

Located in `Modules/DynamicCategories/Config/config.php`:

```php
return [
    'cache' => [
        'enabled' => env('DYNAMIC_CATEGORIES_CACHE_ENABLED', false),
        'default_ttl' => env('DYNAMIC_CATEGORIES_CACHE_TTL', 3600),
    ],
];
```

### Environment Variables

```env
# Cache settings
DYNAMIC_CATEGORIES_CACHE_ENABLED=true
DYNAMIC_CATEGORIES_CACHE_TTL=3600
```

## Multi-Country Support

The module fully supports the multi-country database system:

- Uses `CountryDatabaseTrait` for automatic database switching
- Cache keys include `category_id`, language code, plus the resolved `emirate_id`/`region_id` pair so each category/location combination is isolated
- Sections can be filtered by emirate and region
- Each country has separate dynamic categories configuration

## Multi-Language Support

- Supports English and Arabic
- Title fields: `title_en`, `title_ar`
- Image fields: `image_en_url`, `image_ar_url`, `title_image_en_url`, `title_image_ar_url`
- Uses `TraitLanguage` for automatic language detection
- Cache keys include language code

## Location-Based Filtering

Sections can be filtered by location:

- **Emirate Filter**: `emirate_id` field
- **Region Filter**: `region_ids` JSON array
- **Global Sections**: When both are null, section shows for all locations (within the category)
- **Category Filter**: `category_id` field (required)

**Note:** The `MatchedDefaultAddressScope` is enabled in the model boot method for automatic location filtering.

## Caching Strategy

### Cache Flow

1. **Request received** → Extract `category_id` → Check cache
2. **Cache hit** → Return cached data
3. **Cache miss** → Build data → Store in cache → Return data

### Cache Invalidation

Cache should be cleared when:
- Dynamic category sections are updated
- Section items are added/removed
- Category-specific settings are changed

### Manual Cache Clear

```bash
# Clear all dynamic categories cache
php artisan cache:clear

# Or programmatically
$cacheService->clearAllDynamicCategoriesCache();
```

## Key Differences from HomePage Module

### 1. Category-Specific Filtering
- **DynamicCategories**: Requires `category_id` parameter, sections filtered by category
- **HomePage**: No category filtering, shows all sections

### 2. Request Method
- **DynamicCategories**: Uses `POST` method (to send `category_id` in body)
- **HomePage**: Uses `GET` method

### 3. Response Structure
- **DynamicCategories**: Only returns `sections` (no header)
- **HomePage**: Returns both `header` and `sections`

### 4. Additional Fields
- **DynamicCategories**: Includes `display_type` and `menu_type` fields
- **HomePage**: Does not include these fields

### 5. Menu Items Builder
- **DynamicCategories**: `MenuItemsSectionBuilder` supports mixed products/shops with `type` field
- **HomePage**: `CategorySectionBuilder` only supports categories

### 6. Cache Keys
- **DynamicCategories**: Cache keys include `category_id`
- **HomePage**: Cache keys do not include category

### 7. Enum Types
- **DynamicCategories**: Uses `DynamicCategorySectionType` with `MENU_ITEMS`
- **HomePage**: Uses `HomeSectionType` with `CATEGORIES`

## Best Practices

### 1. Section Ordering

Always set `sorting` value when creating sections:
```php
$section->sorting = 1; // Lower numbers appear first
```

### 2. Performance

- Use eager loading: `with('items.item')`
- Limit items per section (use pagination constants)
- Enable caching in production
- Use appropriate cache TTLs
- Filter by `category_id` early in the query
- **MenuItemsSectionBuilder** uses optimized database-level grouping with `MIN(id)` and `GROUP BY` for efficient menu item grouping
- Avoid loading all items into memory; use selective queries with `whereIn` when possible
- **ProductSectionBuilder and ShopSectionBuilder** filter out null items before applying pagination to ensure the correct number of valid items are returned, preventing scenarios where fewer items than requested are returned due to null relationships

### 3. Error Handling

The controller catches exceptions and returns user-friendly error messages:
```php
try {
    $dynamicCategoriesData = $this->dynamicCategoriesService->getDynamicCategoriesData($request);
    return responseSuccessData(DynamicCategoriesResource::make($dynamicCategoriesData));
} catch (\Exception $e) {
    return responseErrorMessage(
        __('dynamiccategories::messages.failed_to_retrieve_dynamic_categories_data'),
        500
    );
}
```

### 4. Validation

Always validate `category_id` in the request:
```php
'category_id' => ['required', 'integer', 'exists:categories,id']
```

### 5. Menu Items Sections

- Use `MenuItemsSectionBuilder` for menu item sections grouped by `menu_item_parent_id`
- Items are grouped at the database level for optimal performance
- Each menu group includes `id` (parent ID), `title`, and `image_url`
- Language-specific images are automatically selected based on request headers
- The builder uses two efficient queries: one to get group IDs, another to fetch the actual items

## Migration

To apply database changes:

```bash
# Run migration on all countries
php artisan country:db migrate --all

# Or run migration on specific country
php artisan country:db migrate --country=AE
```

## Troubleshooting

### Issue: Sections not appearing

**Solution:**
- Check if sections have items: `has('items.item')`
- Verify `category_id` is correct in the request
- Verify `sorting` values are set correctly
- Check if items exist and are not soft-deleted
- Clear cache: `php artisan cache:clear`
- Verify category exists in database

### Issue: Cache not working

**Solution:**
- Verify cache driver is configured: `config('cache.default')`
- Check environment: Cache is disabled in `local` environment
- Verify cache service: `$cacheService->isCacheEnabled()`
- Check cache key includes correct `category_id`

### Issue: Wrong language content

**Solution:**
- Check `App-Language` header is set correctly
- Verify language fields in database (`title_en`, `title_ar`)
- Clear cache for specific language and category

### Issue: Location filtering not working

**Solution:**
- Verify `MatchedDefaultAddressScope` is enabled in model boot method
- Verify `emirate_id` and `region_ids` are set correctly
- Check user's default address is set
- Verify category filtering is working correctly

### Issue: Menu items not grouped correctly

**Solution:**
- Verify `menu_item_parent_id` is set correctly for items that should be grouped together
- Check that section type is set to `menu_items`
- Verify `MenuItemsSectionBuilder` is being used
- Ensure MySQL `only_full_group_by` mode compatibility (the builder uses `MIN(id)` with `GROUP BY` to ensure compatibility)
- Check database indexes on `menu_item_parent_id` for better query performance

## Future Enhancements

### Planned Features

1. **Category Templates**: Pre-defined section templates for common category types
2. **A/B Testing**: Support for multiple section variants per category
3. **Analytics**: Track section performance per category
4. **Bulk Operations**: Manage sections across multiple categories
5. **Section Inheritance**: Inherit sections from parent categories
6. **Dynamic Pricing**: Category-specific pricing rules
7. **Advanced Filtering**: More granular filtering options

## Related Documentation

- `HOMEPAGE_MODULE.md` - HomePage module documentation
- `PROJECT_SETUP_GUIDE.md` - General project setup
- `MULTI_COUNTRY_DATABASE_SETUP.md` - Multi-country database configuration
- `GUEST_USER_SYSTEM.md` - Guest user system

---

**Note**: This module is designed to be flexible and extensible. Follow SOLID principles when extending functionality. The module is category-specific, making it ideal for providing customized content per category while maintaining the same flexible architecture as the HomePage module.


