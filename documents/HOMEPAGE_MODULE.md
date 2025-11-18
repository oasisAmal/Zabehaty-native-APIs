# HomePage Module

## Overview

The HomePage module is a flexible and extensible system for managing dynamic homepage content in the Zabehaty Native APIs application. It supports multiple section types (banners, products, categories, shops, limited-time offers) with multi-language support, caching, and location-based filtering.

## Architecture

### Module Structure

The module follows Laravel Modules structure with clear separation of concerns:

```
Modules/HomePage/
├── App/
│   ├── Http/Controllers/
│   │   └── HomePageController.php
│   ├── Models/
│   │   ├── Attributes/
│   │   │   └── HomePageAttributes.php
│   │   ├── Relationships/
│   │   │   └── HomePageRelationships.php
│   │   ├── Scopes/
│   │   │   ├── HomePageScopes.php
│   │   │   └── MatchedDefaultAddressScope.php
│   │   ├── HomePage.php
│   │   └── HomePageItem.php
│   ├── Providers/
│   │   ├── HomePageServiceProvider.php
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
│   │   │   │   ├── CategorySectionBuilder.php
│   │   │   │   ├── DefaultSectionBuilder.php
│   │   │   │   ├── ProductSectionBuilder.php
│   │   │   │   └── ShopSectionBuilder.php
│   │   │   ├── HeaderBuilder.php
│   │   │   └── SectionBuilder.php
│   │   ├── Cache/
│   │   │   └── CacheService.php
│   │   └── HomePageService.php
│   └── Transformers/
│       └── HomePageResource.php
├── Database/
│   └── Migrations/
│       ├── 2025_11_03_120924_create_home_page_table.php
│       └── 2025_11_03_120931_create_home_page_items_table.php
├── Enums/
│   └── HomeSectionType.php
├── Routes/
│   └── api.php
└── Config/
    └── config.php
```

## Database Schema

### `home_page` Table

Stores homepage sections configuration:

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `emirate_id` | integer | Optional emirate filter |
| `region_ids` | json | Optional region IDs array |
| `title_en` | string | English title |
| `title_ar` | string | Arabic title |
| `title_image_ar_url` | string | Arabic title image URL |
| `title_image_en_url` | string | English title image URL |
| `background_image_url` | string | Section background image |
| `type` | string | Section type (indexed) |
| `banner_size` | enum | Banner size: small, medium, large |
| `sorting` | smallint | Display order (default: 0) |
| `timestamps` | timestamps | Created/updated timestamps |

**Section Types:**
- `banners` - Banner carousel sections
- `categories` - Category grid sections
- `shops` - Shop listing sections
- `products` - Product listing sections
- `limited_time_offers` - Special offer sections

### `home_page_items` Table

Stores items (polymorphic) associated with each section:

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `home_page_id` | foreignId | References `home_page.id` |
| `image_ar_url` | string | Arabic item image URL |
| `image_en_url` | string | English item image URL |
| `item_id` | bigint | Polymorphic item ID |
| `item_type` | string | Polymorphic item type |
| `timestamps` | timestamps | Created/updated timestamps |

**Polymorphic Relations:**
- Can link to any model (Product, Category, Shop, Banner, etc.)

## Core Components

### 1. Models

#### HomePage Model

Main model for homepage sections with traits for:
- **CountryDatabaseTrait**: Multi-country database support
- **TraitLanguage**: Multi-language support
- **HomePageAttributes**: Custom attribute accessors
- **HomePageRelationships**: Model relationships
- **HomePageScopes**: Query scopes

**Key Methods:**
```php
// Get ordered sections
HomePage::ordered()->get();

// Get sections with items
HomePage::with('items.item')->get();
```

#### HomePageItem Model

Polymorphic pivot model linking sections to items:

```php
// Get item relationship
$homePageItem->item; // Returns polymorphic model (Product, Category, etc.)
```

### 2. Services

#### HomePageService

Main service orchestrating homepage data building:

```php
public function getHomePageData($request): array
{
    // 1. Check cache
    // 2. Build header data
    // 3. Build all sections
    // 4. Store in cache
    // 5. Return data
}
```

**Responsibilities:**
- Cache management
- Coordinating header and section builders
- Determining location context (resolves `emirate_id` and `region_id` from the authenticated user's default address, falling back to global `0` values)
- Country and language handling

#### HeaderBuilder

Builds homepage header data:

```php
public function build(): array
{
    return [
        'background_url' => $this->getBackgroundUrl(),
        'main_categories' => $this->getMainCategories(),
        'story_section_available' => $this->storySectionAvailable(),
        'user_stories' => $this->getUserStories(),
    ];
}
```

**Data Sources:**
- Settings table for background image
- MainCategory model for categories
- Settings for story section availability

#### SectionBuilder

Builds all homepage sections using factory pattern:

```php
public function buildAll(): array
{
    return HomePage::ordered()
        ->has('items.item')
        ->with('items.item')
        ->get()
        ->map(function ($homePage) {
            return $this->buildSection($homePage);
        })->toArray();
}
```

**Process:**
1. Fetches ordered sections with items
2. Uses factory to get appropriate builder
3. Builds each section data
4. Returns array of sections

#### SectionBuilderFactory

Factory pattern implementation for section builders:

```php
public function create($type): SectionBuilderInterface
{
    return match ($type) {
        HomeSectionType::BANNERS => new BannerSectionBuilder(),
        HomeSectionType::SHOPS => new ShopSectionBuilder(),
        HomeSectionType::CATEGORIES => new CategorySectionBuilder(),
        HomeSectionType::PRODUCTS => new ProductSectionBuilder(),
        default => new DefaultSectionBuilder(),
    };
}
```

**Supported Builders:**
- `BannerSectionBuilder` - For banner sections
- `ShopSectionBuilder` - For shop sections
- `CategorySectionBuilder` - For category sections
- `ProductSectionBuilder` - For product and offer sections
- `DefaultSectionBuilder` - Fallback for unknown types

### 3. Section Builders

All section builders implement `SectionBuilderInterface`:

```php
interface SectionBuilderInterface
{
    public function build(HomePage $homePage): array;
}
```

#### ProductSectionBuilder

Builds product sections with pagination:

```php
public function build(HomePage $homePage): array
{
    return $homePage->items()
        ->with('item')
        ->limit(Pagination::PER_PAGE)
        ->get()
        ->map(function ($item) {
            return new ProductCardResource($item->item);
        })->toArray();
}
```

#### CategorySectionBuilder

Builds category sections:

```php
public function build(HomePage $homePage): array
{
    return $homePage->items()
        ->with('item')
        ->limit(Pagination::PER_PAGE)
        ->get()
        ->map(function ($item) {
            return new CategoryCardResource($item->item);
        })->toArray();
}
```

#### BannerSectionBuilder

Builds banner sections (returns raw banner data):

```php
public function build(HomePage $homePage): array
{
    return $homePage->items()
        ->with('item')
        ->get()
        ->map(function ($item) {
            return $item->item;
        })->toArray();
}
```

#### ShopSectionBuilder

Builds shop sections (returns raw shop data):

```php
public function build(HomePage $homePage): array
{
    return $homePage->items()
        ->with('item')
        ->get()
        ->map(function ($item) {
            return $item->item;
        })->toArray();
}
```

### 4. Caching

#### CacheService

Manages homepage data caching:

**Cache Keys:**
```php
"homepage:emirate_id:{emirateId}:region_id:{regionId}:lang:{lang}"
```

**Methods:**
- `getHomePageData(int $emirateId, int $regionId, string $lang)` - Retrieve cached payload for a location/language tuple
- `storeHomePageData(int $emirateId, int $regionId, array $data, string $lang, ?int $ttl = null)` - Persist rendered data using the configured TTL fallback
- `clearHomePageCache(int $emirateId, int $regionId, ?string $lang = null)` - Clear cache for a specific location (all languages when `lang` is null)
- `clearAllHomePageCache()` - Clear cache across all emirates/regions and supported languages
- `isCacheEnabled()` - Check if caching toggle is turned on (`homepage.cache.enabled`)

**Cache Configuration:**
- Default TTL pulled from `homepage.cache.default_ttl` (3600 seconds by default)
- Cache disabled in local environment
- Cache enabled when `cache.default !== 'null'`

### 5. Transformers

#### HomePageResource

API resource transformer:

```php
public function toArray(Request $request): array
{
    return [
        'header' => $this->resource['header'],
        'sections' => $this->resource['sections'],
    ];
}
```

## API Endpoints

### Get Homepage Data

```
GET /api/home-page
```

**Authentication:** Required (`auth:api`)

**Headers:**
```
App-Country: AE
App-Platform: iOS
App-Version: 1.0.0
Authorization: Bearer {token}
```

**Response:**
```json
{
    "status": "success",
    "message": null,
    "data": {
        "header": {
            "background_url": "https://example.com/bg.jpg",
            "main_categories": [
                {
                    "slug": "meat",
                    "name": "Meat",
                    "icon_url": "https://example.com/icon.png"
                }
            ],
            "story_section_available": true,
            "user_stories": []
        },
        "sections": [
            {
                "id": 1,
                "type": "banners",
                "title": "Featured Banners",
                "title_image_url": "https://example.com/title.png",
                "background_image_url": "https://example.com/bg.png",
                "sorting": 1,
                "items": [...]
            },
            {
                "id": 2,
                "type": "products",
                "title": "Featured Products",
                "title_image_url": null,
                "background_image_url": null,
                "sorting": 2,
                "items": [...]
            }
        ]
    }
}
```

**Error Response:**
```json
{
    "status": "error",
    "message": "Failed to retrieve homepage data",
    "data": null
}
```

## Usage Examples

The HomePage module can be managed through the backend admin interface at [https://testing.zabehaty.uae.zabe7ti.website/backend/home-pages](https://testing.zabehaty.uae.zabe7ti.website/backend/home-pages). Below are examples of common operations:

### 1. Creating a Product Section

**Backend Interface Steps:**
1. Navigate to **Home Pages** → **Create New Section**
2. Fill in the section details:
   - **Title (English)**: "Featured Products"
   - **Title (Arabic)**: "المنتجات المميزة"
   - **Section Type**: Select "products"
   - **Sorting**: Enter `1` (lower numbers appear first)
   - **Title Image (English)**: Upload or enter URL for English title image
   - **Title Image (Arabic)**: Upload or enter URL for Arabic title image
   - **Background Image**: Optional background image URL
   - **Emirate**: Optional - select specific emirate for location filtering
   - **Regions**: Optional - select specific regions (JSON array)

3. **Add Items to Section:**
   - Click "Add Items" or navigate to section items
   - Select products from the product list
   - For each product, you can optionally set:
     - **Image (English)**: Custom image URL for English version
     - **Image (Arabic)**: Custom image URL for Arabic version
   - Save items

**Result:** A product section will appear on the homepage showing the selected products.

### 2. Creating a Banner Section

**Backend Interface Steps:**
1. Navigate to **Home Pages** → **Create New Section**
2. Fill in the section details:
   - **Title (English)**: "Main Banners"
   - **Title (Arabic)**: "البانرات الرئيسية"
   - **Section Type**: Select "banners"
   - **Banner Size**: Select "large", "medium", or "small"
   - **Sorting**: Enter `0` (to appear at the top)
   - **Background Image**: Optional background image URL

3. **Add Banners:**
   - Click "Add Items"
   - Select banners from the banner list
   - Each banner will be displayed in the carousel
   - Save items

**Result:** A banner carousel section will appear on the homepage.

### 3. Creating a Category Section

**Backend Interface Steps:**
1. Navigate to **Home Pages** → **Create New Section**
2. Fill in the section details:
   - **Title (English)**: "Shop by Category"
   - **Title (Arabic)**: "تسوق حسب الفئة"
   - **Section Type**: Select "categories"
   - **Sorting**: Enter `2`

3. **Add Categories:**
   - Click "Add Items"
   - Select categories from the category list
   - Categories will be displayed in a grid format
   - Save items

**Result:** A category grid section will appear on the homepage.

### 4. Creating a Limited Time Offers Section

**Backend Interface Steps:**
1. Navigate to **Home Pages** → **Create New Section**
2. Fill in the section details:
   - **Title (English)**: "Limited Time Offers"
   - **Title (Arabic)**: "عروض محدودة الوقت"
   - **Section Type**: Select "limited_time_offers"
   - **Sorting**: Enter `3`

3. **Add Products:**
   - Click "Add Items"
   - Select products that are on special offer
   - These products will be highlighted as limited-time offers
   - Save items

**Note:** Limited time offers use the same builder as products but are marked with a special type for frontend display.

### 5. Managing Section Order

**Backend Interface Steps:**
1. Navigate to **Home Pages** → **List All Sections**
2. Use drag-and-drop or edit the **Sorting** field for each section
3. Lower sorting numbers appear first on the homepage
4. Save changes

**Example Sorting:**
- Banner section: `sorting = 0` (appears first)
- Product section: `sorting = 1`
- Category section: `sorting = 2`
- Offers section: `sorting = 3`

### 6. Location-Based Filtering

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
   - Section will show for all users regardless of location

**Result:** Sections are automatically filtered based on user's default address location.

### 7. Editing Section Items

**Backend Interface Steps:**
1. Navigate to **Home Pages** → Select a section
2. Click on **Items** tab or **Manage Items**
3. **Add New Item:**
   - Click "Add Item"
   - Select item type (Product, Category, Shop, Banner, etc.)
   - Choose the specific item from the list
   - Optionally set custom images for English and Arabic
   - Save

4. **Remove Item:**
   - Click delete/remove on the item
   - Confirm deletion

5. **Reorder Items:**
   - Use drag-and-drop or edit item order
   - Items are displayed in the order they appear in the list

### 8. Multi-Language Content

**Backend Interface Steps:**
1. When creating or editing a section, fill in both language fields:
   - **Title (English)**: English title text
   - **Title (Arabic)**: Arabic title text
   - **Title Image (English)**: English title image URL
   - **Title Image (Arabic)**: Arabic title image URL

2. For section items:
   - **Image (English)**: English version image URL
   - **Image (Arabic)**: Arabic version image URL

**Result:** The homepage will automatically display content in the user's selected language based on the `App-Language` header.

### 9. Clearing Cache After Changes

**Important:** After making changes in the backend, clear the homepage cache:

**Backend Interface:**
- Look for a "Clear Cache" button in the Home Pages section
- Or use the cache management tool

**Via Command Line:**
```bash
# Clear all homepage cache
php artisan cache:clear

# Or programmatically
use Modules\HomePage\App\Services\Cache\CacheService;
$cacheService = app(CacheService::class);
$cacheService->clearAllHomePageCache();
```

**Result:** Changes will be immediately visible on the mobile app after cache is cleared.

### 10. Section Visibility Rules

**Backend Interface:**
- Sections with **no items** will not appear on the homepage
- Sections are automatically filtered by location if `emirate_id` or `region_ids` are set
- Sections are ordered by the `sorting` field (ascending)

**Best Practices:**
- Always ensure sections have at least one item before publishing
- Use appropriate sorting values to control display order
- Test location filtering with different user addresses
- Clear cache after making changes to see updates immediately

## Configuration

### Module Configuration

Located in `Modules/HomePage/Config/config.php`:

```php
return [
    'cache' => [
        'enabled' => env('HOMEPAGE_CACHE_ENABLED', true),
        'default_ttl' => env('HOMEPAGE_CACHE_TTL', 3600),
    ],
    'sections' => [
        'products' => [
            'limit' => 10,
            'show_price' => true,
        ],
        // ...
    ],
];
```

### Environment Variables

```env
# Cache settings
HOMEPAGE_CACHE_ENABLED=true
HOMEPAGE_CACHE_TTL=3600
```

## Multi-Country Support

The module fully supports the multi-country database system:

- Uses `CountryDatabaseTrait` for automatic database switching
- Cache keys include language code plus the resolved `emirate_id`/`region_id` pair so each country/location combination is isolated
- Sections can be filtered by emirate and region
- Each country has separate homepage configuration

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
- **Global Sections**: When both are null, section shows for all locations

**Note:** The `MatchedDefaultAddressScope` is currently commented out in the model boot method but can be enabled for automatic location filtering.

## Caching Strategy

### Cache Flow

1. **Request received** → Check cache
2. **Cache hit** → Return cached data
3. **Cache miss** → Build data → Store in cache → Return data

### Cache Invalidation

Cache should be cleared when:
- Homepage sections are updated
- Section items are added/removed
- Settings affecting header are changed
- Main categories are updated

### Manual Cache Clear

```bash
# Clear all homepage cache
php artisan cache:clear

# Or programmatically
$cacheService->clearAllHomePageCache();
```

## Best Practices

### 1. Section Ordering

Always set `sorting` value when creating sections:
```php
$homePage->sorting = 1; // Lower numbers appear first
```

### 2. Performance

- Use eager loading: `with('items.item')`
- Limit items per section (use pagination constants)
- Enable caching in production
- Use appropriate cache TTLs

### 3. Error Handling

The controller catches exceptions and returns user-friendly error messages:
```php
try {
    $homePageData = $this->homePageService->getHomePageData($request);
    return responseSuccessData(HomePageResource::make($homePageData));
} catch (\Exception $e) {
    return responseErrorMessage(
        __('homepage::messages.failed_to_retrieve_homepage_data'),
        500
    );
}
```

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
- Verify `sorting` values are set correctly
- Check if items exist and are not soft-deleted
- Clear cache: `php artisan cache:clear`

### Issue: Cache not working

**Solution:**
- Verify cache driver is configured: `config('cache.default')`
- Check environment: Cache is disabled in `local` environment
- Verify cache service: `$cacheService->isCacheEnabled()`

### Issue: Wrong language content

**Solution:**
- Check `App-Language` header is set correctly
- Verify language fields in database (`title_en`, `title_ar`)
- Clear cache for specific language

### Issue: Location filtering not working

**Solution:**
- Enable `MatchedDefaultAddressScope` in model boot method
- Verify `emirate_id` and `region_ids` are set correctly
- Check user's default address is set

## Future Enhancements

### Planned Features

1. **Banner Section Builder**: Complete implementation with BannerCardResource
2. **Shop Section Builder**: Complete implementation with ShopCardResource
3. **Location-Based Filtering**: Enable MatchedDefaultAddressScope
4. **User Stories**: Implement user stories functionality
5. **Section Templates**: Pre-defined section templates
6. **A/B Testing**: Support for multiple homepage variants
7. **Analytics**: Track section performance

## Related Documentation

- `PROJECT_SETUP_GUIDE.md` - General project setup
- `MULTI_COUNTRY_DATABASE_SETUP.md` - Multi-country database configuration
- `GUEST_USER_SYSTEM.md` - Guest user system

---

**Note**: This module is designed to be flexible and extensible. Follow SOLID principles when extending functionality.

