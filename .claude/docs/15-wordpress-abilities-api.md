# WordPress Abilities API Integration

**Last Updated:** 2025-01-11
**Status:** ✅ Implemented (WordPress 6.9+)

## Overview

Both `frs-lrg` (Lending Resource Hub) and `frs-wp-users` (FRS User Profiles) now integrate with the WordPress Abilities API, exposing plugin functionality in a machine-readable format for AI agents, automation tools, and external integrations.

---

## What is the WordPress Abilities API?

The WordPress Abilities API is a first-class, cross-context functional API introduced in WordPress 6.9. It provides:

- **Discoverability** - All available abilities can be listed and inspected through standard interfaces
- **Interoperability** - A uniform schema allows unrelated components to work together seamlessly
- **Security-first** - Explicit permission controls govern who or what can invoke abilities
- **REST API exposure** - Automatic REST endpoints for external access

---

## frs-lrg Plugin Abilities

### Categories

**32 total abilities across 5 categories:**

1. **partnership-management** (5 abilities)
2. **lead-management** (4 abilities)
3. **portal-management** (5 abilities)
4. **property-data** (2 abilities)
5. **calendar-management** (2 abilities)

---

### Partnership Management (5 abilities)

#### `lrh/get-partnerships`
- **Description:** Retrieves a list of partnerships with optional filtering
- **Inputs:**
  - `status` (optional): `active`, `pending`, `declined`, `cancelled`
  - `loan_officer_id` (optional): Filter by loan officer
  - `agent_id` (optional): Filter by agent/realtor
  - `limit` (optional): Max results (1-100, default 10)
- **Output:** Array of partnership objects with id, loan_officer_id, agent_id, status, dates
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/get-partnerships/run`

#### `lrh/get-partnership`
- **Description:** Retrieves detailed information about a specific partnership
- **Inputs:**
  - `id` (required): Partnership ID
- **Output:** Full partnership object including custom_data, invite details
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/get-partnership/run`

#### `lrh/create-partnership`
- **Description:** Creates a new partnership between loan officer and realtor
- **Inputs:**
  - `loan_officer_id` (required)
  - `agent_id` (optional)
  - `partner_post_id` (optional)
  - `partner_email` (required)
  - `partner_name` (required)
  - `status` (optional): Default `pending`
- **Output:** Created partnership object
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/create-partnership/run`

#### `lrh/update-partnership`
- **Description:** Updates an existing partnership
- **Inputs:**
  - `id` (required)
  - `agent_id` (optional)
  - `partner_post_id` (optional)
  - `status` (optional)
- **Output:** Updated partnership object
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/update-partnership/run`

#### `lrh/delete-partnership`
- **Description:** Permanently deletes a partnership ⚠️ DESTRUCTIVE
- **Inputs:**
  - `id` (required)
- **Output:** `{ success: true, id: <partnership_id> }`
- **Permission:** `manage_options` (admin only)
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/delete-partnership/run`

---

### Lead Management (4 abilities)

#### `lrh/get-leads`
- **Description:** Retrieves lead submissions with filtering
- **Inputs:**
  - `status` (optional): `new`, `contacted`, `qualified`, `converted`, `closed`
  - `partnership_id` (optional)
  - `loan_officer_id` (optional)
  - `agent_id` (optional)
  - `lead_source` (optional)
  - `limit` (optional): 1-100, default 10
- **Output:** Array of lead objects
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/get-leads/run`

#### `lrh/get-lead`
- **Description:** Retrieves detailed information about a specific lead
- **Inputs:**
  - `id` (required): Lead submission ID
- **Output:** Full lead object including property details
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/get-lead/run`

#### `lrh/create-lead`
- **Description:** Creates a new lead submission
- **Inputs:**
  - `first_name` (required)
  - `last_name` (required)
  - `email` (required)
  - `partnership_id` (optional)
  - `loan_officer_id` (optional)
  - `agent_id` (optional)
  - `lead_source` (optional)
  - `phone`, `loan_amount`, `property_value`, `property_address` (optional)
- **Output:** Created lead object
- **Permission:** `read` (public - allows form submissions)
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/create-lead/run`

#### `lrh/update-lead-status`
- **Description:** Updates lead status
- **Inputs:**
  - `id` (required)
  - `status` (required): `new`, `contacted`, `qualified`, `converted`, `closed`
- **Output:** Updated lead object
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/update-lead-status/run`

---

### Portal Management (5 abilities)

#### `lrh/get-page-assignments`
- **Description:** Retrieves page assignments for users
- **Inputs:**
  - `user_id` (optional)
  - `page_id` (optional)
  - `limit` (optional): 1-100, default 20
- **Output:** Array of page assignment objects
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/get-page-assignments/run`

#### `lrh/assign-page`
- **Description:** Assigns a portal page to a user
- **Inputs:**
  - `user_id` (required)
  - `page_id` (required)
- **Output:** Assignment object
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/assign-page/run`

#### `lrh/unassign-page`
- **Description:** Removes a page assignment
- **Inputs:**
  - `user_id` (required)
  - `page_id` (required)
- **Output:** `{ success: true }`
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/unassign-page/run`

#### `lrh/get-portal-tools`
- **Description:** Lists available portal tools and features
- **Inputs:**
  - `user_role` (optional): `loan_officer`, `realtor_partner`, `all` (default)
- **Output:** Array of tool objects (id, title, description, category, url, roles)
- **Permission:** `read` (public)
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/get-portal-tools/run`

#### `lrh/get-portal-config`
- **Description:** Retrieves portal configuration for a user
- **Inputs:**
  - `user_id` (optional): Defaults to current user
- **Output:** Portal config (portal_slug, role, branding, features)
- **Permission:** `read`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/get-portal-config/run`

---

### Property Data (2 abilities)

#### `lrh/lookup-property`
- **Description:** Looks up property information via Rentcast API
- **Inputs:**
  - `address` (required)
  - `city` (optional)
  - `state` (optional)
  - `zipcode` (optional)
- **Output:** Property details (bedrooms, bathrooms, square_feet, year_built, etc.)
- **Permission:** `read` (public)
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/lookup-property/run`
- **Note:** Requires Rentcast API key configured in plugin settings

#### `lrh/get-property-valuation`
- **Description:** Gets estimated property value via Rentcast API
- **Inputs:**
  - `address` (required)
  - `city` (optional)
  - `state` (optional)
  - `zipcode` (optional)
- **Output:** Valuation data (price, price_low, price_high, rent_estimate)
- **Permission:** `read` (public)
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/get-property-valuation/run`
- **Note:** Requires Rentcast API key configured in plugin settings

---

### Calendar Management (2 abilities)

#### `lrh/check-availability`
- **Description:** Checks calendar availability for appointments
- **Inputs:**
  - `user_id` (optional): Defaults to current user
  - `date` (required): YYYY-MM-DD format
- **Output:** Available time slots for the specified date
- **Permission:** `read`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/check-availability/run`
- **Note:** Requires FluentBooking plugin

#### `lrh/get-bookings`
- **Description:** Retrieves calendar bookings
- **Inputs:**
  - `user_id` (optional): Defaults to current user
  - `start_date` (optional): YYYY-MM-DD
  - `end_date` (optional): YYYY-MM-DD
  - `limit` (optional): 1-100, default 20
- **Output:** Array of booking objects
- **Permission:** `edit_posts`
- **REST:** `/wp-json/wp-abilities/v1/abilities/lrh/get-bookings/run`
- **Note:** Requires FluentBooking plugin

---

## frs-wp-users Plugin Abilities

### Categories

**14 total abilities across 4 categories:**

1. **user-management** (5 abilities)
2. **profile-management** (4 abilities)
3. **role-management** (3 abilities)
4. **sync-operations** (2 abilities)

---

### User Management (5 abilities)

#### `frs-users/get-users`
- **Description:** Retrieves a list of users with filtering
- **Inputs:**
  - `role` (optional): `loan_officer`, `realtor_partner`, `administrator`, `subscriber`
  - `search` (optional): Search by name or email
  - `limit` (optional): 1-100, default 20
- **Output:** Array of user objects
- **Permission:** `list_users`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/get-users/run`

#### `frs-users/get-user`
- **Description:** Retrieves detailed user information
- **Inputs:**
  - `id` (required): User ID
- **Output:** Full user object
- **Permission:** `list_users`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/get-user/run`

#### `frs-users/create-user`
- **Description:** Creates a new user account
- **Inputs:**
  - `username` (required)
  - `email` (required)
  - `password` (optional): Auto-generated if omitted
  - `first_name`, `last_name` (optional)
  - `role` (optional): Default `subscriber`
- **Output:** Created user object
- **Permission:** `create_users`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/create-user/run`

#### `frs-users/update-user`
- **Description:** Updates user account details
- **Inputs:**
  - `id` (required)
  - `email`, `first_name`, `last_name` (optional)
- **Output:** Updated user object
- **Permission:** `edit_users`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/update-user/run`

#### `frs-users/delete-user`
- **Description:** Permanently deletes a user ⚠️ DESTRUCTIVE
- **Inputs:**
  - `id` (required)
  - `reassign` (optional): User ID to reassign posts to
- **Output:** `{ success: true, id: <user_id> }`
- **Permission:** `delete_users`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/delete-user/run`
- **Note:** Cannot delete your own account

---

### Profile Management (4 abilities)

#### `frs-users/get-profile`
- **Description:** Retrieves user profile information
- **Inputs:**
  - `user_id` (optional): Defaults to current user
- **Output:** Profile data (bio, phone, company, website, avatar_url)
- **Permission:** `read`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/get-profile/run`

#### `frs-users/update-profile`
- **Description:** Updates user profile information
- **Inputs:**
  - `user_id` (optional): Defaults to current user
  - `display_name`, `bio`, `phone`, `company`, `website` (optional)
- **Output:** `{ success: true, user_id: <id> }`
- **Permission:** `edit_user` (can edit specified user)
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/update-profile/run`

#### `frs-users/get-profile-fields`
- **Description:** Lists available profile fields and their configuration
- **Inputs:** None
- **Output:** Array of field objects (name, label, type, required)
- **Permission:** `read`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/get-profile-fields/run`

#### `frs-users/update-avatar`
- **Description:** Updates user avatar/profile picture
- **Inputs:**
  - `user_id` (optional): Defaults to current user
  - `avatar_url` (required): URL of new avatar image
- **Output:** `{ success: true, user_id: <id>, avatar_url: <url> }`
- **Permission:** `edit_user`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/update-avatar/run`

---

### Role Management (3 abilities)

#### `frs-users/get-user-roles`
- **Description:** Retrieves all roles assigned to a user
- **Inputs:**
  - `user_id` (required)
- **Output:** `{ user_id: <id>, roles: [array of role names] }`
- **Permission:** `list_users`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/get-user-roles/run`

#### `frs-users/assign-role`
- **Description:** Assigns a role to a user
- **Inputs:**
  - `user_id` (required)
  - `role` (required): `loan_officer`, `realtor_partner`, `subscriber`, `administrator`
- **Output:** `{ success: true, user_id: <id>, role: <role> }`
- **Permission:** `promote_users`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/assign-role/run`

#### `frs-users/remove-role`
- **Description:** Removes a role from a user
- **Inputs:**
  - `user_id` (required)
  - `role` (required)
- **Output:** `{ success: true, user_id: <id>, role: <role> }`
- **Permission:** `promote_users`
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/remove-role/run`
- **Note:** Users must have at least one role

---

### Sync Operations (2 abilities)

#### `frs-users/trigger-sync`
- **Description:** Manually triggers webhook-based synchronization
- **Inputs:**
  - `user_id` (optional): For single user sync
  - `sync_type` (optional): `full`, `incremental` (default), `single`
- **Output:** Sync results (users_synced, timestamp)
- **Permission:** `manage_options` (admin only)
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/trigger-sync/run`

#### `frs-users/get-sync-status`
- **Description:** Retrieves status of last synchronization
- **Inputs:** None
- **Output:** Last sync details (type, time, users_synced, next_sync_due)
- **Permission:** `manage_options` (admin only)
- **REST:** `/wp-json/wp-abilities/v1/abilities/frs-users/get-sync-status/run`

---

## REST API Access

### Discovery Endpoints

```bash
# List all categories
GET /wp-json/wp-abilities/v1/categories

# List all abilities
GET /wp-json/wp-abilities/v1/abilities

# Get specific ability details
GET /wp-json/wp-abilities/v1/abilities/{ability-name}
```

### Execution Endpoint

```bash
# Execute an ability
POST /wp-json/wp-abilities/v1/abilities/{ability-name}/run
Content-Type: application/json

{
  "param1": "value1",
  "param2": "value2"
}
```

### Authentication

All REST API endpoints require WordPress authentication:

```bash
# Using Basic Auth (requires Application Passwords)
curl -u 'username:app-password' \
  https://hub21.local/wp-json/wp-abilities/v1/abilities

# Using nonce (for same-origin requests)
X-WP-Nonce: <nonce-value>
```

---

## File Structure

### frs-lrg
```
includes/Abilities/
├── AbilitiesRegistry.php    # Main registry
├── Categories.php            # Category definitions
├── PartnershipAbilities.php # Partnership CRUD
├── LeadAbilities.php         # Lead management
├── PortalAbilities.php       # Portal management
├── PropertyAbilities.php     # Rentcast integration
└── CalendarAbilities.php     # FluentBooking integration
```

### frs-wp-users
```
includes/Abilities/
├── AbilitiesRegistry.php    # Main registry
├── Categories.php            # Category definitions
├── UserAbilities.php         # User CRUD
├── ProfileAbilities.php      # Profile management
├── RoleAbilities.php         # Role management
└── SyncAbilities.php         # Webhook sync
```

---

## Security

### Permission Levels

- **Public (`read`):** Can be executed by any authenticated user
  - `lrh/create-lead` - Public lead form submissions
  - `lrh/lookup-property` - Property lookup tool
  - `lrh/get-portal-tools` - Portal tools list
  - `frs-users/get-profile` - View profiles

- **Editor (`edit_posts`):** Standard user operations
  - Most partnership, lead, and portal management abilities
  - `lrh/get-bookings`, `frs-users/update-profile`

- **Admin (`manage_options`):** Sensitive operations
  - `lrh/delete-partnership` - Destructive partnership deletion
  - `frs-users/delete-user` - User account deletion
  - `frs-users/trigger-sync` - Manual sync operations

- **User Management (`list_users`, `create_users`, `edit_users`, `delete_users`):**
  - All frs-users user management abilities

- **Role Management (`promote_users`):**
  - `frs-users/assign-role`, `frs-users/remove-role`

### Input Validation

All abilities use JSON Schema for automatic input validation:
- Type checking (string, integer, boolean)
- Format validation (email, uri, date)
- Enum constraints for predefined values
- Required field enforcement
- Additional properties blocking

### Output Validation

All abilities validate their output against defined schemas, ensuring consistent data structures for AI agents and integrations.

---

## Next Steps

### MCP Adapter (Upcoming)

An MCP (Model Context Protocol) adapter will be created to:
1. Expose WordPress Abilities API via MCP protocol
2. Allow Claude Desktop and other AI tools to directly invoke abilities
3. Provide context about available operations and data structures

### Theme Integration (Future)

Theme-level abilities may be added for:
- Page content management
- Navigation structure
- Theme settings
- Custom post types

---

## Troubleshooting

### Abilities Not Showing

If abilities aren't appearing:

```bash
# Flush rewrite rules
wp rewrite flush

# Check if abilities are registered
wp eval "echo 'Abilities: ' . count(wp_get_abilities());"

# Verify plugin is active
wp plugin list
```

### Permission Errors

If REST API returns 403 Forbidden:
- Verify user has required capability
- Check permission_callback in ability definition
- Ensure user is authenticated

### Missing Dependencies

Some abilities require external plugins:
- **Property abilities:** Require Rentcast API key in `lrh_rentcast_api_key` option
- **Calendar abilities:** Require FluentBooking plugin active

---

## References

- [WordPress Abilities API Documentation](https://developer.wordpress.org/news/2025/11/introducing-the-wordpress-abilities-api/)
- [WordPress 6.9 Release Notes](https://wordpress.org/news/2025/12/wordpress-6-9/)
- [Model Context Protocol (MCP)](https://modelcontextprotocol.io/)
