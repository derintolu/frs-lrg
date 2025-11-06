# Common Development Tasks

Step-by-step guides for common development tasks in the Lending Resource Hub plugin.

---

## Table of Contents

- [Adding a Database Table](#adding-a-database-table)
- [Adding a REST API Endpoint](#adding-a-rest-api-endpoint)
- [Adding a React Component](#adding-a-react-component)
- [Adding a Gutenberg Block](#adding-a-gutenberg-block)
- [Adding an Integration](#adding-an-integration)

---

## Adding a Database Table

### Step 1: Create Migration File

**File:** `database/Migrations/YourTable.php`

```php
<?php
namespace LendingResourceHub\Database\Migrations;

use LendingResourceHub\Interfaces\Migration;

class YourTable implements Migration {
    public function up(): void {
        global $wpdb;
        $table_name = $wpdb->prefix . 'your_table_name';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            title varchar(255) NOT NULL,
            description text DEFAULT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            metadata longtext DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function down(): void {
        global $wpdb;
        $table_name = $wpdb->prefix . 'your_table_name';
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }
}
```

### Step 2: Create Eloquent Model

**File:** `includes/Models/YourModel.php`

```php
<?php
namespace LendingResourceHub\Models;

use WeDevs\ORM\Eloquent\Model;

class YourModel extends Model {
    protected $table = 'your_table_name';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'metadata',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Belongs to user
     */
    public function user() {
        return $this->belongsTo(\WP_User::class, 'user_id');
    }

    /**
     * Scope: Only active records
     */
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }
}
```

### Step 3: Run Migration

**File:** `includes/Core/Install.php`

```php
use LendingResourceHub\Database\Migrations\YourTable;

public static function activate(): void {
    // ... existing migrations
    (new YourTable())->up();

    // ...
}
```

### Step 4: Test Migration

```bash
# Deactivate and reactivate plugin
wp plugin deactivate frs-lrg
wp plugin activate frs-lrg

# Verify table exists
wp db query "SHOW TABLES LIKE 'wp_your_table_name'"

# Check table structure
wp db query "DESCRIBE wp_your_table_name"
```

---

## Adding a REST API Endpoint

### Step 1: Create Controller

**File:** `includes/Controllers/YourController.php`

```php
<?php
namespace LendingResourceHub\Controllers;

use LendingResourceHub\Models\YourModel;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class YourController {
    /**
     * Get all records for current user
     */
    public function index(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $user_id = get_current_user_id();

        $records = YourModel::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return new WP_REST_Response([
            'success' => true,
            'data' => $records,
        ], 200);
    }

    /**
     * Get single record
     */
    public function show(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param('id');
        $record = YourModel::find($id);

        if (!$record) {
            return new WP_Error('not_found', 'Record not found', ['status' => 404]);
        }

        // Check permissions
        if ($record->user_id !== get_current_user_id()) {
            return new WP_Error('forbidden', 'Access denied', ['status' => 403]);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => $record,
        ], 200);
    }

    /**
     * Create new record
     */
    public function store(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $user_id = get_current_user_id();

        // Validate input
        $title = sanitize_text_field($request->get_param('title'));
        if (empty($title)) {
            return new WP_Error('missing_title', 'Title is required', ['status' => 400]);
        }

        // Create record
        $record = YourModel::create([
            'user_id' => $user_id,
            'title' => $title,
            'description' => sanitize_textarea_field($request->get_param('description')),
            'status' => 'active',
        ]);

        return new WP_REST_Response([
            'success' => true,
            'data' => $record,
        ], 201);
    }

    /**
     * Update record
     */
    public function update(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param('id');
        $record = YourModel::find($id);

        if (!$record) {
            return new WP_Error('not_found', 'Record not found', ['status' => 404]);
        }

        // Check permissions
        if ($record->user_id !== get_current_user_id()) {
            return new WP_Error('forbidden', 'Access denied', ['status' => 403]);
        }

        // Update
        $record->update([
            'title' => sanitize_text_field($request->get_param('title')),
            'description' => sanitize_textarea_field($request->get_param('description')),
            'status' => sanitize_text_field($request->get_param('status')),
        ]);

        return new WP_REST_Response([
            'success' => true,
            'data' => $record,
        ], 200);
    }

    /**
     * Delete record
     */
    public function destroy(WP_REST_Request $request): WP_REST_Response|WP_Error {
        $id = (int) $request->get_param('id');
        $record = YourModel::find($id);

        if (!$record) {
            return new WP_Error('not_found', 'Record not found', ['status' => 404]);
        }

        // Check permissions
        if ($record->user_id !== get_current_user_id()) {
            return new WP_Error('forbidden', 'Access denied', ['status' => 403]);
        }

        $record->delete();

        return new WP_REST_Response([
            'success' => true,
            'message' => 'Record deleted',
        ], 200);
    }
}
```

### Step 2: Register Routes

**File:** `includes/Routes/api.php`

```php
use LendingResourceHub\Controllers\YourController;

// Add these routes
Route::get('/your-resource', [YourController::class, 'index'], 'is_user_logged_in');
Route::post('/your-resource', [YourController::class, 'store'], 'is_user_logged_in');
Route::get('/your-resource/(?P<id>\d+)', [YourController::class, 'show'], 'is_user_logged_in');
Route::put('/your-resource/(?P<id>\d+)', [YourController::class, 'update'], 'is_user_logged_in');
Route::delete('/your-resource/(?P<id>\d+)', [YourController::class, 'destroy'], 'is_user_logged_in');
```

### Step 3: Test Endpoints

```bash
# Flush rewrite rules
wp rewrite flush

# List routes
wp rest route list | grep your-resource

# Test GET endpoint
curl -X GET "http://hub21.local/wp-json/lrh/v1/your-resource" \
  -H "X-WP-Nonce: YOUR_NONCE"

# Test POST endpoint
curl -X POST "http://hub21.local/wp-json/lrh/v1/your-resource" \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: YOUR_NONCE" \
  -d '{"title": "Test Record", "description": "Test description"}'
```

---

## Adding a React Component

### Step 1: Create Component File

**File:** `src/frontend/components/YourComponent.tsx`

```tsx
import { useState, useEffect } from 'react';
import { api } from '@/services/api';

interface YourComponentProps {
  readonly userId: number;
  readonly onUpdate?: (record: YourRecord) => void;
}

interface YourRecord {
  id: number;
  title: string;
  description: string;
  status: string;
  created_at: string;
}

export function YourComponent({ userId, onUpdate }: YourComponentProps): JSX.Element {
  const [records, setRecords] = useState<YourRecord[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchRecords() {
      try {
        const data = await api.get<YourRecord[]>('/your-resource');
        setRecords(data);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error');
      } finally {
        setLoading(false);
      }
    }

    void fetchRecords();
  }, [userId]);

  const handleCreate = async (title: string, description: string) => {
    try {
      const newRecord = await api.post<YourRecord>('/your-resource', {
        title,
        description,
      });
      setRecords([...records, newRecord]);
      onUpdate?.(newRecord);
    } catch (err) {
      alert('Failed to create record');
    }
  };

  if (loading) return <LoadingSpinner />;
  if (error) return <ErrorMessage message={error} />;

  return (
    <div className="your-component">
      <h2 className="text-2xl font-bold mb-4">Your Records</h2>

      <ul className="space-y-2">
        {records.map((record) => (
          <li key={record.id} className="p-4 bg-white rounded-lg shadow">
            <h3 className="font-semibold">{record.title}</h3>
            <p className="text-gray-600">{record.description}</p>
          </li>
        ))}
      </ul>

      <button
        onClick={() => handleCreate('New Record', 'Description')}
        className="mt-4 btn-primary"
      >
        Add Record
      </button>
    </div>
  );
}
```

### Step 2: Export Component

**File:** `src/frontend/components/index.ts`

```typescript
export { YourComponent } from './YourComponent';
```

### Step 3: Use Component

```tsx
import { YourComponent } from '@/components/YourComponent';

export function Dashboard(): JSX.Element {
  const userId = window.lrhPortalConfig.userId;

  return (
    <div>
      <h1>Dashboard</h1>
      <YourComponent
        userId={userId}
        onUpdate={(record) => console.log('Record updated:', record)}
      />
    </div>
  );
}
```

---

## Adding a Gutenberg Block

### Step 1: Create Block Directory

```bash
mkdir -p blocks/your-block
cd blocks/your-block
```

### Step 2: Create block.json

**File:** `blocks/your-block/block.json`

```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "lrh/your-block",
  "title": "Your Block",
  "category": "lrh-blocks",
  "icon": "star-filled",
  "description": "Display your custom content",
  "supports": {
    "html": false,
    "align": ["wide", "full"]
  },
  "attributes": {
    "title": {
      "type": "string",
      "default": "Default Title"
    },
    "backgroundColor": {
      "type": "string",
      "default": "#ffffff"
    }
  },
  "editorScript": "file:./index.js",
  "editorStyle": "file:./editor.css",
  "style": "file:./style.css"
}
```

### Step 3: Create Block Editor Script

**File:** `blocks/your-block/index.js`

```jsx
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';

registerBlockType('lrh/your-block', {
  edit: ({ attributes, setAttributes }) => {
    const blockProps = useBlockProps();
    const { title, backgroundColor } = attributes;

    return (
      <>
        <InspectorControls>
          <PanelBody title="Settings">
            <TextControl
              label="Title"
              value={title}
              onChange={(value) => setAttributes({ title: value })}
            />
            <TextControl
              label="Background Color"
              value={backgroundColor}
              onChange={(value) => setAttributes({ backgroundColor: value })}
            />
          </PanelBody>
        </InspectorControls>

        <div {...blockProps} style={{ backgroundColor }}>
          <h2>{title}</h2>
          <p>This is your custom block (editor preview)</p>
        </div>
      </>
    );
  },

  save: () => {
    // Return null for dynamic blocks (PHP renders)
    return null;
  },
});
```

### Step 4: Register Block in PHP

**File:** `includes/Core/Blocks.php`

```php
public function registerBlocks(): void {
    $blocks_dir = LRH_PLUGIN_DIR . '/blocks/';

    // ... existing blocks

    // Register your block
    register_block_type($blocks_dir . 'your-block/block.json', [
        'render_callback' => [$this, 'renderYourBlock'],
    ]);
}

public function renderYourBlock(array $attributes, string $content): string {
    $title = esc_html($attributes['title'] ?? 'Default Title');
    $bg_color = esc_attr($attributes['backgroundColor'] ?? '#ffffff');

    ob_start();
    ?>
    <div class="your-block" style="background-color: <?php echo $bg_color; ?>;">
        <h2><?php echo $title; ?></h2>
        <p>This is your custom block (frontend render)</p>
    </div>
    <?php
    return ob_get_clean();
}
```

### Step 5: Build Block

```bash
npm run block:build
```

### Step 6: Test Block

1. Go to WordPress admin
2. Edit a page
3. Click "+" to add block
4. Search for "Your Block"
5. Add block and configure settings
6. Save page
7. View frontend to verify rendering

---

## Adding an Integration

### Step 1: Create Integration Class

**File:** `includes/Integrations/YourIntegration.php`

```php
<?php
namespace LendingResourceHub\Integrations;

use LendingResourceHub\Models\LeadSubmission;

class YourIntegration {
    public function __construct() {
        // Check if plugin is active
        if (!$this->isPluginActive()) {
            return;
        }

        // Hook into plugin events
        add_action('your_plugin_event', [$this, 'handleEvent'], 10, 2);
        add_filter('your_plugin_filter', [$this, 'filterData'], 10, 1);
    }

    /**
     * Check if external plugin is active
     */
    private function isPluginActive(): bool {
        return class_exists('YourPlugin\Main');
    }

    /**
     * Handle event from external plugin
     */
    public function handleEvent($arg1, $arg2): void {
        // Create lead or perform action
        $lead = LeadSubmission::create([
            'loan_officer_id' => $arg1->user_id ?? 0,
            'first_name' => $arg1->first_name,
            'last_name' => $arg1->last_name,
            'email' => $arg1->email,
            'lead_source' => 'YourIntegration',
            'status' => 'pending',
        ]);
    }

    /**
     * Filter data from external plugin
     */
    public function filterData($data): array {
        // Modify and return data
        $data['custom_field'] = 'custom_value';
        return $data;
    }
}
```

### Step 2: Register Integration

**File:** `includes/Core/Plugin.php`

```php
use LendingResourceHub\Integrations\YourIntegration;

public function loadIntegrations(): void {
    // ... existing integrations

    new YourIntegration();
}
```

---

## Related Documentation

- [02-architecture.md](./02-architecture.md) - System architecture
- [04-backend-patterns.md](./04-backend-patterns.md) - Backend development
- [05-frontend-patterns.md](./05-frontend-patterns.md) - Frontend development
- [06-security-standards.md](./06-security-standards.md) - Security best practices
