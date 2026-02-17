# Dynamic Categories Module

## Overview

The Dynamic Categories module is a flexible and extensible system for managing dynamic category-specific content sections in the Zabehaty Native APIs application. It supports multiple section types (banners, products, shops, menu items, limited-time offers) with multi-language support, caching, and location-based filtering. Unlike the HomePage module, this module is category-specific and requires a `category_id` parameter to filter sections. The current implementation uses Query Builder for section data fetching to optimize performance while preserving visibility rules.

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
│   │   ├── Queries/
│   │   │   └── DynamicCategoryQuery.php
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
| `emirate_ids` | json | Optional emirate IDs array |
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
| `is_all_menu_item` | boolean | When true, indicates this menu item should show all items (used in Products/Shops filtering) |
| `sorting` | smallint | Item display order |
| `timestamps` | timestamps | Created/updated timestamps |

**Polymorphic Relations:**
- Can link to Product, Shop, or Category models
- Menu items sections use `menu_item_parent_id` to group related items

**is_all_menu_item Field:**
- When set to `true` on a `DynamicCategorySectionItem`, it indicates that when filtering Products or Shops by `dynamic_category_section_id` or `dynamic_category_menu_id`, all items should be returned instead of filtering by the section ID or menu item parent ID
- Used in `ProductIndexRequest` and `ShopIndexRequest` to automatically detect if a menu item should show all items
- When `is_all_menu_item` is `true`, the Products/Shops services skip both the `dynamic_category_section_id` and `menu_item_parent_id` filters, returning all available items

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

Builds all category-specific sections using the factory pattern and Query Builder. The section query is encapsulated in `DynamicCategoryQuery` for readability.

```php
public function buildAll(int $categoryId): array
{
    $sections = DB::table('dynamic_category_sections')
        ->select([...])
        ->where('category_id', $categoryId)
        ->whereExists(...)
        ->orderBy('sorting')
        ->get();

    return $sections
        ->map(fn ($section) => $this->buildSection((array) $section))
        ->filter(fn ($section) => !empty($section['items']))
        ->values()
        ->toArray();
}
```

**Process:**
1. Fetch ordered sections filtered by `category_id` via Query Builder
2. Apply address-based filtering for sections
3. Uses factory to get appropriate builder
4. Builds each section data, filters empty sections
5. Returns array of sections with additional fields (`display_type`, `menu_type`, `has_more_items`)

**Section Response Structure:**
Each section includes a `has_more_items` boolean field indicating whether there are more items available beyond the pagination limit. This allows the frontend to implement "Load More" functionality or pagination controls.

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
    public function build(array $dynamicCategorySection): array;
    
    public function hasMoreItems(array $dynamicCategorySection): bool;
}
```

**hasMoreItems Method:**
Each section builder implements `hasMoreItems()` to determine if there are additional items beyond the pagination limit. This method:
- Returns `true` if there are more items available than the pagination limit
- Returns `false` if all items fit within the pagination limit
- Is used by `SectionBuilder` to populate the `has_more_items` field in the API response

**Shared Query/Visibility Helpers:**
- Builders reuse a shared trait (`UsesDynamicCategoriesQueryBuilder`) to avoid duplication.
- The trait provides the country-aware DB connection, default address resolution, and visibility helpers that filter by the user's default address (emirate + region) and optional `is_visible` on visibility tables (`product_visibilities`, `shop_visibilities`, `category_visibilities`).
- **`applyIsVisibleVisibility`**: JOIN-based; used when the main query already has the entity table. Joins the visibility table and filters by `emirate_id`, `region_ids` (JSON contains), and `is_visible = 1`. Used by `ShopSectionBuilder` and `ProductSectionBuilder` with parameters: `(query, visibilityTable, visibilityFkColumn, mainEntityIdColumn, defaultAddress)`.
- **`applyIsVisibleVisibilityExists`**: WHERE EXISTS subquery; used when the main query must not be joined (e.g. banner section with OR branches per item type). Same filters. Used by `BannerSectionBuilder` for product/shop/category items.
- Legacy helpers (`applyVisibilityExists`, category/shop through products/shop categories) remain available for region-based visibility (emirate + region_ids null or contains).

#### MenuItemsSectionBuilder

**Unique Feature:** This builder groups menu items by `menu_item_parent_id` and returns menu group data. It uses optimized database-level grouping for better performance.

Builds menu items sections by grouping items by parent ID using Query Builder:

```php
public function build(array $dynamicCategorySection): array
{
    $menuGroupIds = DB::table('dynamic_category_section_items')
        ->where('dynamic_category_section_id', $dynamicCategorySection['id'])
        ->selectRaw('MIN(id) as id')
        ->groupBy('menu_item_parent_id')
        ->pluck('id');

    return DB::table('dynamic_category_section_items')
        ->where('dynamic_category_section_id', $dynamicCategorySection['id'])
        ->whereIn('id', $menuGroupIds)
        ->get()
        ->map(fn ($menuGroup) => [
            'id' => $menuGroup->menu_item_parent_id,
            'name' => $menuGroup->title,
            'image_url' => $menuGroup->image_url,
        ])
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

**hasMoreItems Implementation:**
```php
public function hasMoreItems(DynamicCategorySection $dynamicCategorySection): bool
{
    return $dynamicCategorySection->items()->count() > Pagination::PER_PAGE;
}
```

Checks if the total number of menu items exceeds `Pagination::PER_PAGE` (typically 20 items).

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

Builds product sections via Query Builder and applies full visibility rules (product visibility via `applyIsVisibleVisibility` on `product_visibilities` with emirate, region, and `is_visible = 1`; plus shop and category visibility as applicable). It also mirrors the product active conditions for price/approval/department and calculates derived fields like price and discount.

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

**hasMoreItems Implementation:**
```php
public function hasMoreItems(DynamicCategorySection $dynamicCategorySection): bool
{
    return $this->resolveItems($dynamicCategorySection)->filter(function ($item) {
        return $item->item !== null;
    })->count() > 20;
}
```

Filters out null items before counting to ensure accurate pagination indication. Returns `true` if there are more than 20 valid (non-null) product items.

#### ShopSectionBuilder

Builds shop sections via Query Builder and applies shop visibility via `applyIsVisibleVisibility` on `shop_visibilities` (emirate, region, `is_visible = 1`) and category visibility rules so shops are shown only when both visibility layers pass.

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

**hasMoreItems Implementation:**
```php
public function hasMoreItems(DynamicCategorySection $dynamicCategorySection): bool
{
    return $this->resolveItems($dynamicCategorySection)->filter(function ($item) {
        return $item->item !== null;
    })->count() > 20;
}
```

Filters out null items before counting to ensure accurate pagination indication. Returns `true` if there are more than 20 valid (non-null) shop items.

#### BannerSectionBuilder

Builds banner sections via Query Builder. If a banner is linked to a product/shop/category, it is returned only when the linked item passes its visibility rules (using `applyIsVisibleVisibilityExists` for direct product/shop/category visibility with emirate, region, and `is_visible = 1`). Unlinked banners remain visible.

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

**hasMoreItems Implementation:**
```php
public function hasMoreItems(DynamicCategorySection $dynamicCategorySection): bool
{
    return $dynamicCategorySection->items()->count() > Pagination::PER_PAGE;
}
```

Checks if the total number of banner items exceeds `Pagination::PER_PAGE` (typically 20 items).

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
                "has_more_items": false,
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
                "has_more_items": true,
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
     - **Is All Menu Item**: Set to `true` if this menu item should show all products/shops when selected (see "Show All Items Feature" section)
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

### 7. Show All Items Feature (is_all_menu_item)

**Overview:**
The `is_all_menu_item` field allows menu items to control filtering behavior when querying Products or Shops by `dynamic_category_section_id` or `dynamic_category_menu_id`. When a menu item has `is_all_menu_item = true`, it signals that all items should be returned instead of filtering by the specific section ID or menu item parent ID.

**How It Works:**

1. **In Products/Shops Index Requests:**
   - When `dynamic_category_menu_id` is provided, the request automatically checks if there's a `DynamicCategorySectionItem` with that `menu_item_parent_id` and `is_all_menu_item = true`
   - If found, the `is_all_menu_item` flag is set to `true` in the request data

2. **In Products/Shops Services:**
   - When filtering by `dynamic_category_section_id`, the service checks the `is_all_menu_item` flag
     - If `is_all_menu_item` is `true`, the filter by `dynamic_category_section_id` is **skipped**, returning all available products/shops
     - If `is_all_menu_item` is `false` (or not set), the filter **is applied**, showing only items that belong to that specific section
   - When filtering by `dynamic_category_menu_id`, the service checks the `is_all_menu_item` flag
     - If `is_all_menu_item` is `true`, the filter by `menu_item_parent_id` is **skipped**, returning all available products/shops
     - If `is_all_menu_item` is `false` (or not set), the filter **is applied**, showing only items that belong to that specific menu item parent

**Example Implementation:**

```php
// In ProductIndexRequest/ShopIndexRequest
protected function prepareForValidation(): void
{
    $isAllMenuItem = false;
    if ($this->dynamic_category_menu_id) {
        $isAllMenuItem = DynamicCategorySectionItem::where('menu_item_parent_id', $this->dynamic_category_menu_id)
            ->where('is_all_menu_item', true)
            ->exists();
    }
    $this->merge([
        'is_all_menu_item' => $isAllMenuItem,
    ]);
}

// In ProductsService/ShopsService
->when(isset($filters['dynamic_category_section_id']) && $filters['dynamic_category_section_id'] && !$filters['is_all_menu_item'], function (Builder $query) use ($filters) {
    return $query->whereHas('dynamicCategorySectionItems', function (Builder $subQuery) use ($filters) {
        $subQuery->where('dynamic_category_section_id', $filters['dynamic_category_section_id']);
    });
})->when(isset($filters['dynamic_category_menu_id']) && $filters['dynamic_category_menu_id'] && !$filters['is_all_menu_item'], function (Builder $query) use ($filters) {
    return $query->whereHas('dynamicCategorySectionItems', function (Builder $subQuery) use ($filters) {
        $subQuery->where('menu_item_parent_id', $filters['dynamic_category_menu_id']);
    });
})
```

**Use Cases:**
- **Specific Section/Menu Item**: Set `is_all_menu_item = false` (or leave unset) to show only items belonging to that section or menu group
- **Show All Items**: Set `is_all_menu_item = true` to show all available products/shops when filtering by that section or menu item

**Backend Configuration:**
1. Navigate to the menu item in `dynamic_category_section_items`
2. Set `is_all_menu_item` to `true` for menu items that should show all items
3. Leave `is_all_menu_item` as `false` (or `null`) for menu items that should filter by section ID or parent ID

**Result:** 
- When filtering by `dynamic_category_section_id` with `is_all_menu_item = true`, users will see all available products/shops instead of section-filtered results
- When filtering by `dynamic_category_menu_id` with `is_all_menu_item = true`, users will see all available products/shops instead of menu-item-filtered results

### 8. Display Type and Menu Type

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
- **Data Seeding Commands**: All seeding commands use `forCountry('ae')` to ensure data is stored in the correct country database connection
  - `Shop::forCountry('ae')` - Query shops from UAE database
  - `Product::forCountry('ae')` - Query products from UAE database
  - `Category::forCountry('ae')` - Query categories from UAE database
  - `DynamicCategorySection::forCountry('ae')` - Query/create sections in UAE database
  - `DynamicCategorySectionItem::forCountry('ae')->insert()` - Insert items in UAE database

## Multi-Language Support

- Supports English and Arabic
- Title fields: `title_en`, `title_ar`
- Image fields: `image_en_url`, `image_ar_url`, `title_image_en_url`, `title_image_ar_url`
- Uses `TraitLanguage` for automatic language detection
- Cache keys include language code

## Location-Based Filtering

Sections can be filtered by location:

- **Emirate Filter**: `emirate_ids` JSON array on the section
- **Region Filter**: `region_ids` JSON array on the section
- **Global Sections**: When both are null, section shows for all locations (within the category)
- **Category Filter**: `category_id` field (required)
- **Item visibility**: Product, shop, and category items are filtered by the user's default address using the visibility tables; builders use `applyIsVisibleVisibility` / `applyIsVisibleVisibilityExists` with `emirate_id`, `region_ids` (JSON contains), and `is_visible = 1`.

**Note:** Dynamic Categories applies visibility rules directly in the builders for category responses, so model scopes are not required for these endpoints. Other parts of the system may still rely on the model scopes.

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
- **DynamicCategories**: Includes `display_type`, `menu_type`, and `has_more_items` fields
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

- Query Builder is used for sections and items to minimize ORM overhead
- Limit items per section (use pagination constants)
- Enable caching in production
- Use appropriate cache TTLs
- Filter by `category_id` early in the query
- **MenuItemsSectionBuilder** uses optimized database-level grouping with `MIN(id)` and `GROUP BY` for efficient menu item grouping
- Avoid loading all items into memory; use selective queries with `whereIn` when possible
- Sections with no visible items are removed before returning the response

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
- Use `is_all_menu_item` field to control whether a menu item shows all products/shops or filters by section ID or parent ID when selected

### 6. Pagination and hasMoreItems

The `has_more_items` field indicates whether there are additional items beyond the pagination limit:

- **Frontend Usage**: Use `has_more_items` to show/hide "Load More" buttons or pagination controls
- **Implementation**: Each section builder implements `hasMoreItems()` method that checks if total items exceed the pagination limit
- **Pagination Limits**:
  - `ProductSectionBuilder` and `ShopSectionBuilder`: 20 items (hardcoded)
  - `BannerSectionBuilder` and `MenuItemsSectionBuilder`: `Pagination::PER_PAGE` (typically 20)
- **Null Item Handling**: `ProductSectionBuilder` and `ShopSectionBuilder` filter out null items before counting to ensure accurate pagination indication
- **Response Field**: The `has_more_items` boolean is included in each section's response data

**Example Frontend Implementation:**
```javascript
section.items.forEach(item => {
    renderItem(item);
});

if (section.has_more_items) {
    showLoadMoreButton(section.id);
} else {
    hideLoadMoreButton(section.id);
}
```

## Data Seeding Commands

The DynamicCategories module includes an Artisan command for seeding sample data for performance testing and development purposes.

### StoreDynamicCategorySectionsCommand

**Command:** `dynamiccategories:store-sections`

Creates sample DynamicCategories sections with items for testing and performance measurement.

**Usage:**
```bash
# Basic usage (default: 50 sections per category, 100 items per section, 10 categories)
docker compose exec app php artisan dynamiccategories:store-sections

# Custom number of sections, items, and categories
docker compose exec app php artisan dynamiccategories:store-sections --sections=100 --items-per-section=200 --categories=20

# Force recreate items (delete existing items before creating new ones)
docker compose exec app php artisan dynamiccategories:store-sections --force
```

**Options:**
- `--sections`: Number of sections to create per category (default: 50)
- `--items-per-section`: Number of items to add per section (default: 100)
- `--categories`: Number of categories to process (default: 10)
- `--force`: Recreate items even if they exist

**Features:**
- **Multi-Country Support**: Uses `forCountry('ae')` for all database operations to ensure data is stored in the correct country database
- **Idempotent**: Can be run multiple times safely (unless `--force` is used)
- **Batch Inserts**: Uses batch inserts (500 items per chunk) for better performance
- **Automatic Location Data**: Automatically sets `emirate_ids` and `region_ids` to include all emirates and regions
- **Section Types**: Creates sections for all types: `menu_items`, `products`, `shops`, `banners`
- **Menu Items Support**: Creates menu item groups with `is_all_menu_item` flag support

**What It Creates:**
- Sections for each category (limited by `--categories` option)
- Each section type includes appropriate items:
  - **Menu Items**: Creates menu groups with `menu_item_parent_id` and optional `is_all_menu_item` flag
  - **Products**: Links random products to the section
  - **Shops**: Links random shops to the section
  - **Banners**: Creates banner sections
- All sections are configured with proper `emirate_ids` and `region_ids` for location filtering

**Example:**
```bash
# Create 20 sections per category with 50 items each for 5 categories
docker compose exec app php artisan dynamiccategories:store-sections --sections=20 --items-per-section=50 --categories=5
```

**Important Notes:**
- Requires existing data: shops, products, and categories must exist in the database
- Uses `forCountry('ae')` to ensure data is stored in the UAE (AE) database connection
- All models use `forCountry('ae')` for queries and inserts to maintain multi-country database integrity
- The `is_all_menu_item` field is conditionally set based on database schema (gracefully handles if column doesn't exist)

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

### Issue: Products/Shops filtering by section or menu item not working as expected

**Solution:**
- Verify `is_all_menu_item` is set correctly on the `DynamicCategorySectionItem` record
- Check that `dynamic_category_section_id` matches the section ID in the database (if filtering by section)
- Check that `dynamic_category_menu_id` matches the `menu_item_parent_id` in the database (if filtering by menu item)
- If `is_all_menu_item = true`, products/shops should return all items (no filtering by section ID or menu item parent ID)
- If `is_all_menu_item = false` (or null), products/shops should be filtered by `dynamic_category_section_id` or `menu_item_parent_id` respectively
- Verify the `prepareForValidation()` method in `ProductIndexRequest`/`ShopIndexRequest` is correctly detecting the flag
- Check that the service is checking `!$filters['is_all_menu_item']` before applying both the `dynamic_category_section_id` and `dynamic_category_menu_id` filters

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


