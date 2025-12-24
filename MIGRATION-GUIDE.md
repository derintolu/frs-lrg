no# WordPress Plugin Boilerplate - Migration Guide

## Table of Contents
1. [Overview](#overview)
2. [Migration System Architecture](#migration-system-architecture)
3. [Migration Interface](#migration-interface)
4. [Database Connection](#database-connection)
5. [Creating Schema Migrations](#creating-schema-migrations)
6. [Creating Data Migrations](#creating-data-migrations)
7. [Running Migrations](#running-migrations)
8. [Available Schema Builder Methods](#available-schema-builder-methods)
9. [Data Migration Patterns](#data-migration-patterns)
10. [Seeders](#seeders)
11. [Best Practices](#best-practices)

---

## Overview

This WordPress Plugin Boilerplate uses **prappo/wp-eloquent** (based on Laravel 8.9's Illuminate Database) for database operations. This provides a powerful, expressive ORM and schema builder for managing database tables and migrations.

### Key Components:
- **Migration Interface**: Contract that all migrations must implement
- **Capsule Manager**: Laravel's database manager for schema operations
- **Schema Facade**: Provides static access to schema builder methods
- **Eloquent ORM**: ActiveRecord-style ORM for data operations
- **Install Class**: Orchestrates migration execution on plugin activation

---

## Migration System Architecture

### Directory Structure
```
database/
├── Migrations/
│   ├── Accounts.php           # Schema migration example
│   ├── Partnerships.php       # Schema migration
│   ├── LeadSubmissions.php    # Schema migration
│   ├── PageAssignments.php    # Schema migration
│   └── MigrateOldData.php     # Data migration example
└── Seeders/
    └── Accounts.php           # Seeder example
```

### How It Works

1. **Plugin Activation**: When plugin is activated, `register_activation_hook` triggers `Install::init()`
2. **Install Class**: Located in `includes/Core/Install.php`, orchestrates setup
3. **Migration Execution**: `install_tables()` method calls `up()` on each migration class
4. **Data Seeding**: `insert_data()` method runs seeders after migrations

**File: `lending-resource-hub.php`**
```php
register_activation_hook( __FILE__, array( Install::get_instance(), 'init' ) );
```

**File: `includes/Core/Install.php`**
```php
private function install_tables() {
    // Schema migrations
    Accounts::up();
    Partnerships::up();
    LeadSubmissions::up();
    PageAssignments::up();

    // Data migrations
    MigrateOldData::up();
}
```

---

## Migration Interface

All migration classes must implement the `Migration` interface.

**File: `includes/Interfaces/Migration.php`**
```php
<?php
namespace LendingResourceHub\Interfaces;

interface Migration {
    /**
     * Perform actions when migrating up.
     * Creates tables or modifies schema.
     */
    public static function up();

    /**
     * Perform actions when migrating down.
     * Reverses the changes made in up().
     */
    public static function down();
}
```

### Key Points:
- Both methods are **static**
- `up()` creates/modifies database structure
- `down()` reverses the changes (rollback)
- down() is typically not used in WordPress (no built-in rollback mechanism)

---

## Database Connection

The database connection is initialized using WordPress's global `$wpdb` connection.

**File: `libs/db.php`**
```php
<?php
namespace LendingResourceHub\Libs\DatabaseConnection;

use Prappo\WpEloquent\Application;

// Boot Eloquent with WordPress database connection
Application::bootWp();
```

This file is loaded via `vendor/autoload.php` and bootstraps the Eloquent ORM to use WordPress's existing database connection, including the table prefix.

---

## Creating Schema Migrations

Schema migrations create or modify database tables using the Schema Builder.

### Basic Structure

**File: `database/Migrations/ExampleTable.php`**
```php
<?php
namespace LendingResourceHub\Database\Migrations;

use LendingResourceHub\Interfaces\Migration;
use Prappo\WpEloquent\Database\Capsule\Manager as Capsule;
use Prappo\WpEloquent\Database\Schema\Blueprint;
use Prappo\WpEloquent\Support\Facades\Schema;

class ExampleTable implements Migration {

    private static $table = 'example_table';

    public static function up() {
        // Check if table already exists (prevents duplicates)
        if ( Capsule::schema()->hasTable( self::$table ) ) {
            return;
        }

        // Create the table
        Capsule::schema()->create(
            self::$table,
            function ( Blueprint $table ) {
                // Define columns here
                $table->id();
                $table->string('name');
                $table->timestamps();
            }
        );
    }

    public static function down() {
        // Drop the table if it exists
        Schema::dropIfExists( self::$table );
    }
}
```

### Real Example: Partnerships Table

**File: `database/Migrations/Partnerships.php`**
```php
<?php
namespace LendingResourceHub\Database\Migrations;

use LendingResourceHub\Interfaces\Migration;
use Prappo\WpEloquent\Database\Capsule\Manager as Capsule;
use Prappo\WpEloquent\Database\Schema\Blueprint;
use Prappo\WpEloquent\Support\Facades\Schema;

class Partnerships implements Migration {

    private static $table = 'partnerships';

    public static function up() {
        if ( Capsule::schema()->hasTable( self::$table ) ) {
            return;
        }

        Capsule::schema()->create(
            self::$table,
            function ( Blueprint $table ) {
                // Primary key
                $table->id();

                // Foreign keys
                $table->unsignedBigInteger( 'loan_officer_id' );
                $table->unsignedBigInteger( 'agent_id' )->nullable();
                $table->unsignedBigInteger( 'partner_post_id' )->nullable();

                // Data columns
                $table->string( 'partner_email' );
                $table->string( 'partner_name' )->nullable();
                $table->string( 'status', 20 )->default( 'pending' );
                $table->string( 'invite_token', 64 )->nullable();

                // Date columns
                $table->dateTime( 'invite_sent_date' )->nullable();
                $table->dateTime( 'accepted_date' )->nullable();

                // JSON/Text storage
                $table->longText( 'custom_data' )->nullable();

                // Timestamps
                $table->dateTime( 'created_date' )->nullable();
                $table->dateTime( 'updated_date' )->nullable();

                // Performance indexes
                $table->index( 'loan_officer_id' );
                $table->index( 'agent_id' );
                $table->index( 'partner_post_id' );
                $table->index( 'partner_email' );
                $table->index( 'status' );
                $table->index( 'invite_token' );
            }
        );
    }

    public static function down() {
        Schema::dropIfExists( self::$table );
    }
}
```

---

## Creating Data Migrations

Data migrations move or transform data between tables. They use both Eloquent ORM and raw WordPress `$wpdb` queries.

### Key Principles:
1. Use a flag to prevent re-running (WordPress options API)
2. Check if source tables exist before migrating
3. Check for existing data to prevent duplicates
4. Use proper escaping and prepared statements
5. Log completion for debugging

### Real Example: Migrating Old Plugin Data

**File: `database/Migrations/MigrateOldData.php`**
```php
<?php
namespace LendingResourceHub\Database\Migrations;

use LendingResourceHub\Interfaces\Migration;

class MigrateOldData implements Migration {

    public static function up() {
        global $wpdb;

        // Define table names
        $old_partnerships_table = $wpdb->prefix . 'frs_partnerships';
        $new_partnerships_table = $wpdb->prefix . 'partnerships';

        // Check if migration already completed
        $migration_flag = get_option( 'lrh_data_migration_completed' );
        if ( $migration_flag ) {
            return;
        }

        // Check if old table exists
        if ( self::table_exists( $old_partnerships_table ) ) {
            // Get all records from old table
            $partnerships = $wpdb->get_results(
                "SELECT * FROM {$old_partnerships_table}"
            );

            if ( ! empty( $partnerships ) ) {
                foreach ( $partnerships as $partnership ) {
                    // Check if record already migrated
                    $existing = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT id FROM {$new_partnerships_table}
                            WHERE loan_officer_id = %d
                            AND partner_email = %s",
                            $partnership->loan_officer_id,
                            $partnership->partner_email
                        )
                    );

                    // Insert if not exists
                    if ( ! $existing ) {
                        $wpdb->insert(
                            $new_partnerships_table,
                            array(
                                'loan_officer_id'  => $partnership->loan_officer_id,
                                'partner_email'    => $partnership->partner_email,
                                'partner_name'     => $partnership->partner_name,
                                'status'           => $partnership->status,
                                'created_date'     => $partnership->created_date,
                            ),
                            array( '%d', '%s', '%s', '%s', '%s' )
                        );
                    }
                }
            }
        }

        // Mark migration as complete
        update_option( 'lrh_data_migration_completed', true );

        // Log completion
        error_log( 'LRH: Data migration completed' );
    }

    public static function down() {
        // Data migrations are typically not reversible
        // Only schema migrations can be reversed
    }

    /**
     * Check if a table exists.
     */
    private static function table_exists( $table_name ) {
        global $wpdb;
        $table = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
        );
        return $table === $table_name;
    }
}
```

---

## Running Migrations

### Automatic Execution (Recommended)

Migrations run automatically on plugin activation. Add your migration to the Install class:

**File: `includes/Core/Install.php`**
```php
private function install_tables() {
    // Add your migration here
    YourNewTable::up();
}
```

### Manual Execution (For Development)

You can manually trigger migrations during development:

```php
// In any controller or admin page
use LendingResourceHub\Database\Migrations\YourMigration;

// Run the migration
YourMigration::up();

// Rollback (use with caution!)
YourMigration::down();
```

### Triggering Migrations After Updates

If you need to run migrations after a plugin update (not just activation):

```php
// In your main plugin class or Install.php
add_action( 'plugins_loaded', function() {
    $current_version = get_option( 'lrh_db_version', '0' );

    if ( version_compare( $current_version, '1.1.0', '<' ) ) {
        // Run new migrations
        NewTable::up();

        // Update version
        update_option( 'lrh_db_version', '1.1.0' );
    }
});
```

---

## Available Schema Builder Methods

Based on Laravel 8.9 schema builder. Full documentation: [Laravel 8 Migrations](https://laravel.com/docs/8.x/migrations)

### Column Types

```php
// Auto-incrementing IDs
$table->id();                              // BIGINT UNSIGNED AUTO_INCREMENT
$table->increments('id');                  // INT UNSIGNED AUTO_INCREMENT
$table->bigIncrements('id');               // BIGINT UNSIGNED AUTO_INCREMENT

// Strings
$table->string('name');                    // VARCHAR(255)
$table->string('name', 100);               // VARCHAR(100)
$table->text('description');               // TEXT
$table->mediumText('content');             // MEDIUMTEXT
$table->longText('data');                  // LONGTEXT
$table->char('code', 4);                   // CHAR(4)

// Numbers
$table->integer('count');                  // INT
$table->tinyInteger('status');             // TINYINT
$table->smallInteger('value');             // SMALLINT
$table->bigInteger('large_number');        // BIGINT
$table->unsignedInteger('count');          // INT UNSIGNED
$table->unsignedBigInteger('user_id');     // BIGINT UNSIGNED
$table->decimal('amount', 8, 2);           // DECIMAL(8,2)
$table->float('rate', 8, 2);               // FLOAT(8,2)
$table->double('precise', 15, 8);          // DOUBLE(15,8)

// Booleans
$table->boolean('active');                 // TINYINT(1)

// Dates & Times
$table->date('birth_date');                // DATE
$table->dateTime('created_at');            // DATETIME
$table->timestamp('updated_at');           // TIMESTAMP
$table->timestamps();                      // created_at + updated_at
$table->time('alarm_time');                // TIME
$table->year('year');                      // YEAR

// Binary
$table->binary('data');                    // BLOB

// JSON
$table->json('options');                   // JSON
$table->jsonb('data');                     // JSONB (PostgreSQL)

// Special Types
$table->uuid('identifier');                // UUID
$table->ipAddress('visitor');              // IP address
$table->macAddress('device');              // MAC address
```

### Column Modifiers

```php
// Nullability
$table->string('name')->nullable();        // Allow NULL
$table->string('name')->nullable(false);   // NOT NULL (default)

// Default Values
$table->string('status')->default('active');
$table->integer('count')->default(0);
$table->boolean('active')->default(true);

// Uniqueness
$table->string('email')->unique();
$table->string('username')->unique('unique_username');

// Comments
$table->string('name')->comment('User full name');

// Character Set (MySQL)
$table->string('name')->charset('utf8mb4');
$table->string('name')->collation('utf8mb4_unicode_ci');

// After (position in table)
$table->string('new_column')->after('existing_column');

// Unsigned (numbers only)
$table->integer('count')->unsigned();

// Auto-increment
$table->integer('id')->autoIncrement();

// First position
$table->string('new_column')->first();
```

### Indexes

```php
// Primary Key
$table->primary('id');
$table->primary(['id', 'user_id']);        // Composite primary key

// Unique Indexes
$table->unique('email');
$table->unique(['email', 'user_id'], 'unique_user_email');

// Regular Indexes
$table->index('user_id');
$table->index(['user_id', 'status'], 'idx_user_status');

// Foreign Keys (for InnoDB)
$table->foreign('user_id')
      ->references('id')
      ->on('users')
      ->onDelete('cascade')
      ->onUpdate('cascade');

// Drop Indexes
$table->dropPrimary('table_id_primary');
$table->dropUnique('table_email_unique');
$table->dropIndex('table_user_id_index');
$table->dropForeign('table_user_id_foreign');
```

### Table Operations

```php
// Check if table exists
if ( Capsule::schema()->hasTable('users') ) {
    // Table exists
}

// Check if column exists
if ( Capsule::schema()->hasColumn('users', 'email') ) {
    // Column exists
}

// Rename table
Schema::rename('old_table', 'new_table');

// Drop table
Schema::drop('table_name');
Schema::dropIfExists('table_name');

// Modify existing table
Capsule::schema()->table('users', function (Blueprint $table) {
    $table->string('new_column')->nullable();
    $table->dropColumn('old_column');
    $table->renameColumn('old_name', 'new_name');
});
```

---

## Data Migration Patterns

### Pattern 1: Using WordPress $wpdb (Recommended for Data Migrations)

Best for:
- Migrating data from old tables
- Bulk operations
- Complex queries
- Direct database operations

```php
public static function up() {
    global $wpdb;

    $old_table = $wpdb->prefix . 'old_table';
    $new_table = $wpdb->prefix . 'new_table';

    // Check migration flag
    if ( get_option( 'my_migration_completed' ) ) {
        return;
    }

    // Get all records
    $records = $wpdb->get_results("SELECT * FROM {$old_table}");

    foreach ( $records as $record ) {
        // Check if already migrated
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$new_table} WHERE old_id = %d",
                $record->id
            )
        );

        if ( ! $exists ) {
            // Insert with proper escaping
            $wpdb->insert(
                $new_table,
                array(
                    'old_id' => $record->id,
                    'name'   => $record->name,
                    'email'  => $record->email,
                ),
                array( '%d', '%s', '%s' )
            );
        }
    }

    // Mark as complete
    update_option( 'my_migration_completed', true );
}
```

### Pattern 2: Using Eloquent ORM

Best for:
- Creating new records
- Model relationships
- Data validation
- Business logic

```php
use LendingResourceHub\Models\Users;
use LendingResourceHub\Models\Accounts;

public static function up() {
    // Check flag
    if ( get_option( 'eloquent_migration_completed' ) ) {
        return;
    }

    // Get WordPress users
    $wp_users = get_users();

    foreach ( $wp_users as $wp_user ) {
        // Check if account exists
        $exists = Accounts::where('user_id', $wp_user->ID)->exists();

        if ( ! $exists ) {
            // Create using Eloquent
            Accounts::create([
                'user_id'    => $wp_user->ID,
                'email'      => $wp_user->user_email,
                'first_name' => $wp_user->first_name,
                'last_name'  => $wp_user->last_name,
                'created_at' => current_time('mysql'),
            ]);
        }
    }

    update_option( 'eloquent_migration_completed', true );
}
```

### Pattern 3: Mixed Approach (Complex Data Transformations)

Best for:
- Data transformations
- Combining multiple sources
- Complex business logic

```php
public static function up() {
    global $wpdb;

    if ( get_option( 'complex_migration_completed' ) ) {
        return;
    }

    // Get data using raw SQL
    $results = $wpdb->get_results("
        SELECT
            p.id,
            p.partner_email,
            u.ID as user_id,
            u.display_name
        FROM {$wpdb->prefix}partnerships p
        LEFT JOIN {$wpdb->users} u ON p.partner_email = u.user_email
    ");

    foreach ( $results as $row ) {
        // Transform and create using Eloquent
        Partnerships::updateOrCreate(
            ['id' => $row->id],
            [
                'partner_email' => $row->partner_email,
                'agent_id'      => $row->user_id,
                'partner_name'  => $row->display_name,
                'updated_date'  => current_time('mysql'),
            ]
        );
    }

    update_option( 'complex_migration_completed', true );
}
```

---

## Seeders

Seeders populate tables with initial or test data.

### Basic Structure

**File: `database/Seeders/ExampleSeeder.php`**
```php
<?php
namespace LendingResourceHub\Database\Seeders;

use LendingResourceHub\Models\ExampleModel;

class ExampleSeeder {

    private static $table = 'example_table';

    public static function run() {
        // Sample data
        $data = [
            [
                'name'  => 'Example 1',
                'email' => 'example1@test.com',
            ],
            [
                'name'  => 'Example 2',
                'email' => 'example2@test.com',
            ],
        ];

        // Insert each record
        foreach ( $data as $item ) {
            // Check if already exists
            if ( ! ExampleModel::where('email', $item['email'])->exists() ) {
                ExampleModel::create($item);
            }
        }
    }
}
```

### Real Example: Accounts Seeder

**File: `database/Seeders/Accounts.php`**
```php
<?php
namespace LendingResourceHub\Database\Seeders;

use LendingResourceHub\Models\Accounts;

class Accounts {

    private static $table = 'accounts';

    public static function run() {
        $accounts = array(
            array(
                'user_id'    => '1',
                'host'       => 'localhost',
                'port'       => 3306,
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'email'      => 'john@example.com',
                'name'       => 'John Doe',
                'password'   => wp_hash_password('password'),
                'created_at' => gmdate( 'Y-m-d H:i:s' ),
                'updated_at' => gmdate( 'Y-m-d H:i:s' ),
            ),
        );

        foreach ( $accounts as $account ) {
            if ( ! Accounts::where('user_id', $account['user_id'])->exists() ) {
                Accounts::create($account);
            }
        }
    }
}
```

### Running Seeders

**File: `includes/Core/Install.php`**
```php
private function insert_data() {
    // Run seeders after migrations
    Accounts::run();
    ExampleSeeder::run();
}
```

---

## Best Practices

### 1. Always Check for Existing Tables/Data

```php
// For schema migrations
if ( Capsule::schema()->hasTable( self::$table ) ) {
    return;
}

// For data migrations
if ( get_option( 'migration_flag' ) ) {
    return;
}

// For seeders
if ( ! Model::where('key', $value)->exists() ) {
    Model::create($data);
}
```

### 2. Use Prepared Statements

```php
// ❌ NEVER do this (SQL injection risk)
$wpdb->query("SELECT * FROM table WHERE email = '{$email}'");

// ✅ ALWAYS use prepare
$wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM table WHERE email = %s",
        $email
    )
);
```

### 3. Use Migration Flags for Data Migrations

```php
// Set flag after completion
update_option( 'my_plugin_migration_v1_completed', true );

// Check flag before running
if ( get_option( 'my_plugin_migration_v1_completed' ) ) {
    return;
}
```

### 4. Log Important Operations

```php
// Log migration start
error_log( 'LRH: Starting data migration' );

// Log migration completion
error_log( 'LRH: Data migration completed successfully' );

// Log errors
error_log( 'LRH: Migration error - ' . $wpdb->last_error );
```

### 5. Use Indexes for Performance

```php
// Add indexes for frequently queried columns
$table->index( 'user_id' );
$table->index( 'email' );
$table->index( 'status' );
$table->index( 'created_date' );

// Composite indexes for multi-column queries
$table->index( ['user_id', 'status'], 'idx_user_status' );
```

### 6. Handle Nullable Columns Properly

```php
// Use nullable() for optional fields
$table->string( 'middle_name' )->nullable();

// Set default values where appropriate
$table->string( 'status' )->default( 'active' );
$table->integer( 'count' )->default( 0 );
```

### 7. Use Transactions for Data Integrity (Optional)

```php
use Prappo\WpEloquent\Support\Facades\DB;

DB::beginTransaction();
try {
    // Multiple operations
    Model1::create($data1);
    Model2::create($data2);

    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    error_log('Migration error: ' . $e->getMessage());
}
```

### 8. Table Naming Conventions

- Use lowercase with underscores (snake_case)
- Use plural names: `users`, `posts`, `partnerships`
- WordPress prefix is added automatically via $wpdb
- Keep names descriptive but concise

### 9. Version Your Migrations

```php
// Use version numbers in option flags
update_option( 'lrh_migration_v1_0_0', true );
update_option( 'lrh_migration_v1_1_0', true );

// Check versions before running
$current_version = get_option( 'lrh_db_version', '1.0.0' );
if ( version_compare( $current_version, '1.1.0', '<' ) ) {
    // Run new migrations
}
```

### 10. Don't Mix Schema and Data Operations

```php
// ✅ GOOD: Separate migrations
class CreateUsersTable implements Migration {
    public static function up() {
        // Only create schema
        Capsule::schema()->create('users', function($table) {
            // columns...
        });
    }
}

class MigrateUserData implements Migration {
    public static function up() {
        // Only migrate data
        // ...
    }
}

// ❌ BAD: Mixed operations
class CreateAndSeedUsers implements Migration {
    public static function up() {
        // Creating table AND inserting data
        // Makes it hard to maintain
    }
}
```

---

## Step-by-Step Guide: Creating a Data Migration

Let's walk through creating a complete data migration from scratch.

### Scenario
You need to migrate user preference data from WordPress user_meta into a new `user_preferences` table.

### Step 1: Create the Schema Migration

**File: `database/Migrations/UserPreferences.php`**
```php
<?php
namespace LendingResourceHub\Database\Migrations;

use LendingResourceHub\Interfaces\Migration;
use Prappo\WpEloquent\Database\Capsule\Manager as Capsule;
use Prappo\WpEloquent\Database\Schema\Blueprint;
use Prappo\WpEloquent\Support\Facades\Schema;

class UserPreferences implements Migration {

    private static $table = 'user_preferences';

    public static function up() {
        if ( Capsule::schema()->hasTable( self::$table ) ) {
            return;
        }

        Capsule::schema()->create(
            self::$table,
            function ( Blueprint $table ) {
                $table->id();
                $table->unsignedBigInteger( 'user_id' );
                $table->string( 'preference_key' );
                $table->longText( 'preference_value' )->nullable();
                $table->dateTime( 'created_at' )->nullable();
                $table->dateTime( 'updated_at' )->nullable();

                // Indexes
                $table->index( 'user_id' );
                $table->index( 'preference_key' );
                $table->unique( ['user_id', 'preference_key'], 'unique_user_pref' );
            }
        );
    }

    public static function down() {
        Schema::dropIfExists( self::$table );
    }
}
```

### Step 2: Create the Model

**File: `includes/Models/UserPreferences.php`**
```php
<?php
namespace LendingResourceHub\Models;

use Prappo\WpEloquent\Database\Eloquent\Model;

class UserPreferences extends Model {

    protected $table = 'user_preferences';

    protected $fillable = [
        'user_id',
        'preference_key',
        'preference_value',
        'created_at',
        'updated_at',
    ];
}
```

### Step 3: Create the Data Migration

**File: `database/Migrations/MigrateUserPreferencesData.php`**
```php
<?php
namespace LendingResourceHub\Database\Migrations;

use LendingResourceHub\Interfaces\Migration;
use LendingResourceHub\Models\UserPreferences;

class MigrateUserPreferencesData implements Migration {

    public static function up() {
        // Check if already run
        if ( get_option( 'lrh_user_prefs_migrated' ) ) {
            return;
        }

        // Get all users
        $users = get_users( array( 'fields' => 'ID' ) );

        // Preference keys to migrate
        $meta_keys = [
            'notification_enabled',
            'email_frequency',
            'theme_preference',
        ];

        $migrated_count = 0;

        foreach ( $users as $user_id ) {
            foreach ( $meta_keys as $key ) {
                // Get meta value
                $value = get_user_meta( $user_id, $key, true );

                if ( ! empty( $value ) ) {
                    // Create or update preference
                    UserPreferences::updateOrCreate(
                        [
                            'user_id'        => $user_id,
                            'preference_key' => $key,
                        ],
                        [
                            'preference_value' => maybe_serialize( $value ),
                            'updated_at'       => current_time( 'mysql' ),
                        ]
                    );

                    $migrated_count++;
                }
            }
        }

        // Mark as complete
        update_option( 'lrh_user_prefs_migrated', true );
        update_option( 'lrh_user_prefs_count', $migrated_count );

        // Log
        error_log( sprintf(
            'LRH: Migrated %d user preferences',
            $migrated_count
        ) );
    }

    public static function down() {
        // Not reversible
    }
}
```

### Step 4: Add to Install Class

**File: `includes/Core/Install.php`**
```php
private function install_tables() {
    // Existing migrations...

    // New migrations
    UserPreferences::up();              // Schema
    MigrateUserPreferencesData::up();   // Data
}
```

### Step 5: Test the Migration

1. Deactivate the plugin
2. Activate the plugin (triggers migration)
3. Check the database:
```sql
-- Check if table was created
SHOW TABLES LIKE 'wp_user_preferences';

-- Check data was migrated
SELECT * FROM wp_user_preferences LIMIT 10;
```

4. Verify the flag:
```php
// In any admin page or debug script
var_dump( get_option( 'lrh_user_prefs_migrated' ) );
```

---

## Common Issues and Solutions

### Issue 1: Migration Runs Multiple Times

**Problem**: Migration creates duplicate data on each plugin activation.

**Solution**: Always use flags and existence checks.

```php
// Check flag
if ( get_option( 'migration_completed' ) ) {
    return;
}

// Check for existing data
if ( Model::where('unique_field', $value)->exists() ) {
    continue;
}

// Set flag at end
update_option( 'migration_completed', true );
```

### Issue 2: Table Prefix Not Applied

**Problem**: Tables created without WordPress prefix.

**Solution**: Use `$wpdb->prefix` or let Eloquent handle it.

```php
// ✅ Eloquent handles prefix automatically
private static $table = 'my_table';  // Becomes wp_my_table

// ✅ Manual with $wpdb
$table = $wpdb->prefix . 'my_table';
```

### Issue 3: Migration Fails Silently

**Problem**: No error messages when migration fails.

**Solution**: Add error logging and check $wpdb->last_error.

```php
$result = $wpdb->insert( $table, $data );

if ( $result === false ) {
    error_log( 'Migration error: ' . $wpdb->last_error );
}
```

### Issue 4: Foreign Key Constraints

**Problem**: Can't delete or update records due to foreign keys.

**Solution**: Set proper cascade rules or avoid foreign keys in WordPress.

```php
// Option 1: Don't use foreign keys (WordPress convention)
$table->unsignedBigInteger( 'user_id' );  // Just store the ID

// Option 2: Use cascading deletes
$table->foreign( 'user_id' )
      ->references( 'id' )
      ->on( 'users' )
      ->onDelete( 'cascade' );
```

---

## Additional Resources

- **Laravel 8 Migrations**: https://laravel.com/docs/8.x/migrations
- **Laravel 8 Eloquent**: https://laravel.com/docs/8.x/eloquent
- **WP Eloquent GitHub**: https://github.com/prappo/wp-eloquent
- **WordPress $wpdb**: https://developer.wordpress.org/reference/classes/wpdb/

---

## Summary

This boilerplate provides a robust migration system that:

1. Uses Laravel's Eloquent ORM for powerful database operations
2. Implements the Migration interface for consistency
3. Runs migrations automatically on plugin activation
4. Supports both schema and data migrations
5. Includes safeguards against duplicate migrations
6. Provides seeders for initial data
7. Follows WordPress and Laravel best practices

**Key Takeaways**:
- All migrations implement the `Migration` interface
- Use `Capsule::schema()` for schema operations
- Use `Schema` facade for drop operations
- Mix `$wpdb` and Eloquent based on needs
- Always use flags for data migrations
- Always check for existing data
- Log important operations
- Test thoroughly before deploying

