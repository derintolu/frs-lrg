# React SPA Admin Pattern

**How shadcn/ui Components Replace Traditional PHP Admin Pages**

This guide documents the complete pattern for building React-based WordPress admin pages using the WordPress Plugin Boilerplate framework. This is the recommended approach for building modern, interactive admin interfaces.

---

## Table of Contents

1. [Overview](#overview)
2. [The Complete Flow](#the-complete-flow)
3. [Step-by-Step Implementation](#step-by-step-implementation)
4. [shadcn/ui Component Usage](#shadcnui-component-usage)
5. [API Communication](#api-communication)
6. [Routing Pattern](#routing-pattern)
7. [Traditional vs React SPA](#traditional-vs-react-spa)
8. [Best Practices](#best-practices)
9. [Common Patterns](#common-patterns)
10. [Multiplugin Architecture](#multiplugin-architecture)

---

## Overview

The WordPress Plugin Boilerplate supports **two admin page approaches**:

1. **Traditional PHP**: Menu → PHP Class → PHP Template (inline HTML)
2. **React SPA** (recommended): Menu → Mount Div → React App

**React SPA Advantages:**
- Modern UI with shadcn/ui components (accessible, beautiful, customizable)
- Client-side routing (no page reloads)
- Hot Module Replacement during development
- TypeScript type safety
- Component reusability across plugins
- Better performance (single page load)
- Easier testing and maintenance

---

## The Complete Flow

### High-Level Architecture

```
WordPress Admin
    ↓
Menu.php (registers admin page)
    ↓
admin_page() callback (renders mount div)
    ↓
Assets/Admin.php (enqueues React bundle)
    ↓
main.jsx (React entry point)
    ↓
RouterProvider (React Router)
    ↓
Page Components (with shadcn/ui)
```

### Detailed Data Flow

```
1. User clicks "LRH Portal" in WordPress admin sidebar
   └─ WordPress calls Menu.php::admin_page()

2. admin_page() renders mount point div
   └─ <div id="lrh-admin-root"></div>

3. WordPress triggers admin_enqueue_scripts hook
   └─ Assets/Admin.php::enqueue_script() checks current screen
      └─ If on allowed screen, enqueues React bundle
      └─ Passes data via wp_localize_script (window.lrhAdmin)

4. Browser loads React bundle (main.jsx)
   └─ Finds mount div: document.getElementById('lrh-admin-root')
   └─ Renders React app with RouterProvider

5. React Router handles navigation
   └─ User clicks "Partnerships" → router shows Partnerships page
   └─ No page reload, pure client-side routing

6. React components fetch data from REST API
   └─ Uses window.lrhAdmin.apiUrl + endpoints
   └─ Includes window.lrhAdmin.nonce for authentication
```

---

## Step-by-Step Implementation

### Step 1: Create Admin Menu with Mount Point

**File:** `includes/Admin/Menu.php`

```php
<?php

declare(strict_types=1);

namespace LendingResourceHub\Admin;

use LendingResourceHub\Traits\Base;

class Menu {
    use Base;

    private $parent_slug = 'lending-resource-hub';

    public function bootstrap() {
        add_action('admin_menu', array($this, 'menu'));
    }

    /**
     * Register the admin menu page
     */
    public function menu() {
        add_menu_page(
            __('Lending Resource Hub', 'lending-resource-hub'),
            __('LRH Portal', 'lending-resource-hub'),
            'manage_options',
            $this->parent_slug,
            array($this, 'admin_page'),  // Callback renders mount div
            'dashicons-groups',
            3
        );
    }

    /**
     * Render the React mount point
     * This is where React takes over completely
     */
    public function admin_page() {
        ?>
        <div id="lrh-admin-root"></div>
        <?php
    }
}
```

**Key Points:**
- `admin_page()` renders ONLY a mount div, nothing else
- React app will find this div and mount to it
- No PHP logic, no HTML, just the mount point
- Keep it simple and minimal

---

### Step 2: Configure Asset Enqueueing

**File:** `includes/Assets/Admin.php`

```php
<?php

declare(strict_types=1);

namespace LendingResourceHub\Assets;

use LendingResourceHub\Traits\Base;
use LendingResourceHub\Libs\Assets;

class Admin {
    use Base;

    const HANDLE = 'lrh-admin';
    const OBJ_NAME = 'lrhAdmin';
    const DEV_SCRIPT = 'src/admin/main.jsx';

    /**
     * Only load React app on these screens
     */
    private $allowed_screens = array(
        'toplevel_page_lending-resource-hub',
    );

    public function bootstrap() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_script'));
    }

    /**
     * Enqueue React bundle only on allowed screens
     *
     * @param string $screen Current admin screen ID
     */
    public function enqueue_script($screen) {
        if (in_array($screen, $this->allowed_screens, true)) {
            // Enqueue the React bundle
            Assets\enqueue_asset(
                LRH_DIR . '/assets/admin/dist',
                self::DEV_SCRIPT,
                $this->get_config()
            );

            // Pass data to React via window object
            wp_localize_script(self::HANDLE, self::OBJ_NAME, $this->get_data());
        }
    }

    /**
     * Script configuration
     */
    public function get_config() {
        return array(
            'dependencies' => array('react', 'react-dom'),
            'handle'       => self::HANDLE,
            'in-footer'    => true,
        );
    }

    /**
     * Data passed to React app via window.lrhAdmin
     */
    public function get_data() {
        $current_user = wp_get_current_user();

        return array(
            'isAdmin'   => is_admin(),
            'apiUrl'    => rest_url(LRH_ROUTE_PREFIX . '/'),  // e.g., /wp-json/lrh/v1/
            'nonce'     => wp_create_nonce('wp_rest'),
            'userId'    => $current_user->ID,
            'userName'  => $current_user->display_name,
            'userEmail' => $current_user->user_email,
            'userInfo'  => array(
                'username' => $current_user->user_login,
                'avatar'   => get_avatar_url($current_user->ID),
            ),
        );
    }
}
```

**Key Points:**
- Only enqueues on specific admin screens (performance optimization)
- Passes critical data to React via `window.lrhAdmin`
- Includes REST API URL and nonce for authentication
- User data available immediately without extra fetch

---

### Step 3: Create React Entry Point

**File:** `src/admin/main.jsx`

```javascript
import React from "react";
import ReactDOM from "react-dom/client";
import "./index.css";  // Tailwind CSS
import { RouterProvider } from "react-router-dom";
import { router } from "./routes";

// Find the mount point div
const el = document.getElementById("lrh-admin-root");

if (el) {
  // Mount React app
  ReactDOM.createRoot(el).render(
    <React.StrictMode>
      <RouterProvider router={router} />
    </React.StrictMode>
  );
}
```

**Key Points:**
- Finds mount div by ID
- Renders `RouterProvider` for client-side routing
- Uses React 18 `createRoot` API
- Wrapped in `StrictMode` for development warnings

---

### Step 4: Define Routes

**File:** `src/admin/routes.jsx`

```javascript
import { createHashRouter } from "react-router-dom";
import Dashboard from "./pages/dashboard";
import Partnerships from "./pages/partnerships";
import Leads from "./pages/leads";
import Settings from "./pages/settings";
import ErrorPage from "./pages/error/Error";

export const router = createHashRouter([
  {
    path: "/",
    errorElement: <ErrorPage />,
    children: [
      {
        path: "/",
        element: <Dashboard />,
      },
      {
        path: "partnerships",
        element: <Partnerships />,
      },
      {
        path: "leads",
        element: <Leads />,
      },
      {
        path: "settings",
        element: <Settings />,
      }
    ],
  },
]);
```

**Key Points:**
- Uses **hash-based routing** (`createHashRouter`)
- Hash routing works perfectly with WordPress admin URLs
- No server-side configuration needed
- Routes become `admin.php?page=lending-resource-hub#/partnerships`
- Error boundary with custom error page

**Why Hash Router?**
- WordPress admin URLs already have query params (`?page=...`)
- Hash routing (`#/route`) doesn't interfere with WordPress
- No need for server rewrites or `.htaccess` changes
- Works seamlessly within WordPress admin context

---

### Step 5: Build Admin Pages with shadcn/ui

**File:** `src/admin/pages/dashboard/index.jsx`

```jsx
import { useState, useEffect } from "react";
import {
  Card,
  CardContent,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { CheckCircle2, Circle } from "lucide-react";

export default function DashboardPage() {
  const [stats, setStats] = useState({
    activePartnerships: 0,
    pendingInvitations: 0,
    totalLeads: 0,
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      // Use window.lrhAdmin passed from PHP
      const response = await fetch(`${window.lrhAdmin.apiUrl}dashboard/stats`, {
        headers: {
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
      });
      const data = await response.json();
      if (data.success) {
        setStats(data.data.stats);
      }
    } catch (error) {
      console.error("Error fetching dashboard data:", error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="flex-1 space-y-4 p-8 pt-6">
      {/* Page Header */}
      <div className="flex items-center justify-between">
        <h2 className="text-3xl font-bold tracking-tight">
          Partnership Portal Dashboard
        </h2>
      </div>

      {/* Stats Cards with shadcn/ui Card component */}
      <div className="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
              Active Partnerships
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold text-blue-600">
              {loading ? "..." : stats.activePartnerships}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
              Pending Invitations
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold text-blue-600">
              {loading ? "..." : stats.pendingInvitations}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
              Total Leads
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold text-blue-600">
              {loading ? "..." : stats.totalLeads}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Quick Actions */}
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex gap-2 flex-wrap">
            <a href="#/partnerships">
              <Button>Manage Partnerships</Button>
            </a>
            <a href="#/leads">
              <Button variant="outline">View Leads</Button>
            </a>
            <a href="#/settings">
              <Button variant="outline">Settings</Button>
            </a>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
```

**Key Points:**
- Uses shadcn/ui components: `Card`, `Button`, etc.
- Tailwind CSS for layout and spacing
- Fetches data from REST API using `window.lrhAdmin`
- Clean, modern UI matching current design trends
- Fully accessible (shadcn/ui is built on Radix UI)

---

## shadcn/ui Component Usage

### Available Components

Both `frs-lrg` and `frs-wp-users` include 20+ shadcn/ui components:

**File:** `src/components/ui/`

```
accordion.tsx     - Collapsible content sections
alert.tsx         - Alert messages and notifications
avatar.jsx        - User avatars
badge.jsx         - Status badges and tags
button.tsx        - Primary UI buttons
calendar.jsx      - Date picker calendar
card.jsx          - Content cards
chart.jsx         - Data visualization charts
command.jsx       - Command palette
dialog.jsx        - Modal dialogs
dropdown-menu.jsx - Dropdown menus
form.jsx          - Form components
input.jsx         - Text inputs
label.jsx         - Form labels
popover.jsx       - Popover overlays
select.jsx        - Select dropdowns
separator.jsx     - Visual dividers
sheet.jsx         - Side panels
tabs.jsx          - Tabbed interfaces
toast.jsx         - Toast notifications
```

### Component Import Pattern

```jsx
import { Button } from "@/components/ui/button";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Dialog, DialogContent, DialogHeader } from "@/components/ui/dialog";
```

**Why `@/`?**
- Vite path alias configured in `vite.admin.config.js`
- `@` points to `src/` directory
- Clean imports regardless of file nesting depth
- No `../../../` relative path nightmares

**Vite Config:**
```javascript
resolve: {
  alias: {
    "@": path.resolve(__dirname, "./src"),
  },
}
```

### Example: Form with shadcn/ui

```jsx
import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";

export default function PartnershipForm() {
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const response = await fetch(`${window.lrhAdmin.apiUrl}partnerships`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
        body: JSON.stringify({ partner_email: email }),
      });

      const data = await response.json();
      if (data.success) {
        alert("Partnership invitation sent!");
        setEmail("");
      }
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setLoading(false);
    }
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>Invite Realtor Partner</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="email">Partner Email</Label>
            <Input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="realtor@example.com"
              required
            />
          </div>
          <Button type="submit" disabled={loading}>
            {loading ? "Sending..." : "Send Invitation"}
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}
```

**Key Points:**
- All form elements from shadcn/ui
- Fully accessible (ARIA attributes, keyboard navigation)
- Styled with Tailwind utility classes
- Consistent with modern design systems
- No custom CSS needed

---

## API Communication

### Pattern: Fetch with Nonce Authentication

```javascript
// GET request
const fetchData = async () => {
  const response = await fetch(`${window.lrhAdmin.apiUrl}partnerships`, {
    headers: {
      "X-WP-Nonce": window.lrhAdmin.nonce,
    },
  });
  const data = await response.json();
  return data;
};

// POST request
const createPartnership = async (partnerData) => {
  const response = await fetch(`${window.lrhAdmin.apiUrl}partnerships`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": window.lrhAdmin.nonce,
    },
    body: JSON.stringify(partnerData),
  });
  const data = await response.json();
  return data;
};

// PUT request
const updatePartnership = async (id, updates) => {
  const response = await fetch(`${window.lrhAdmin.apiUrl}partnerships/${id}`, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": window.lrhAdmin.nonce,
    },
    body: JSON.stringify(updates),
  });
  const data = await response.json();
  return data;
};

// DELETE request
const deletePartnership = async (id) => {
  const response = await fetch(`${window.lrhAdmin.apiUrl}partnerships/${id}`, {
    method: "DELETE",
    headers: {
      "X-WP-Nonce": window.lrhAdmin.nonce,
    },
  });
  const data = await response.json();
  return data;
};
```

### Custom Hook Pattern

**File:** `src/admin/hooks/useApi.js`

```javascript
import { useState, useCallback } from "react";

export function useApi() {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const request = useCallback(async (endpoint, options = {}) => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch(`${window.lrhAdmin.apiUrl}${endpoint}`, {
        ...options,
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": window.lrhAdmin.nonce,
          ...options.headers,
        },
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || "API request failed");
      }

      return data;
    } catch (err) {
      setError(err.message);
      throw err;
    } finally {
      setLoading(false);
    }
  }, []);

  return { request, loading, error };
}
```

**Usage:**

```javascript
import { useApi } from "@/admin/hooks/useApi";

function PartnershipsPage() {
  const { request, loading, error } = useApi();
  const [partnerships, setPartnerships] = useState([]);

  useEffect(() => {
    const fetchPartnerships = async () => {
      try {
        const data = await request("partnerships");
        setPartnerships(data.data);
      } catch (err) {
        console.error("Failed to fetch partnerships:", err);
      }
    };
    fetchPartnerships();
  }, [request]);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return <div>{/* Render partnerships */}</div>;
}
```

---

## Routing Pattern

### Hash-Based Routing

**Why Hash Router in WordPress Admin:**

1. **WordPress Admin URLs:** Already use query parameters
   - Example: `admin.php?page=lending-resource-hub`
   - Adding browser routing would conflict

2. **Hash Fragment:** Doesn't interfere with WordPress
   - Example: `admin.php?page=lending-resource-hub#/partnerships`
   - WordPress processes `?page=lending-resource-hub`
   - React Router processes `#/partnerships`

3. **No Server Configuration:** Works immediately
   - No `.htaccess` changes needed
   - No server rewrites required
   - No conflicts with WordPress routing

### Navigation

**Link Navigation:**

```jsx
import { Link } from "react-router-dom";

<Link to="/partnerships">View Partnerships</Link>
<Link to="/settings">Settings</Link>
```

**Programmatic Navigation:**

```jsx
import { useNavigate } from "react-router-dom";

function MyComponent() {
  const navigate = useNavigate();

  const handleSuccess = () => {
    navigate("/partnerships");
  };

  return <button onClick={handleSuccess}>Go to Partnerships</button>;
}
```

**Simple Anchor Tags:**

```jsx
<a href="#/partnerships">View Partnerships</a>
<a href="#/settings">Settings</a>
```

### Nested Routes

```javascript
export const router = createHashRouter([
  {
    path: "/",
    element: <ApplicationLayout />,
    errorElement: <ErrorPage />,
    children: [
      {
        path: "/",
        element: <Dashboard />,
      },
      {
        path: "partnerships",
        children: [
          {
            path: "",
            element: <PartnershipsList />,
          },
          {
            path: ":id",
            element: <PartnershipView />,
          },
          {
            path: ":id/edit",
            element: <PartnershipEdit />,
          }
        ],
      },
    ],
  },
]);
```

**Routes become:**
- `#/` - Dashboard
- `#/partnerships` - Partnerships list
- `#/partnerships/123` - View partnership #123
- `#/partnerships/123/edit` - Edit partnership #123

---

## Traditional vs React SPA

### Traditional PHP Approach

**When to Use:**
- Simple, read-only pages
- Heavy WordPress integration (custom fields, meta boxes)
- Server-side rendering requirements
- No interactivity needed

**Example Structure:**

```
includes/Admin/Menu.php
    ↓
includes/Admin/Dashboard.php (PHP class with render method)
    ↓
views/admin/dashboard.php (PHP template with inline HTML)
```

**Pros:**
- Familiar WordPress patterns
- Easy access to WordPress functions
- Simple implementation for basic pages

**Cons:**
- Full page reloads on navigation
- Difficult to build interactive UIs
- Hard to maintain complex state
- No HMR during development
- Inline styles and HTML mixed with PHP

---

### React SPA Approach (Recommended)

**When to Use:**
- Interactive, data-driven interfaces
- Modern UI with complex state
- Multiple related pages (dashboard, settings, etc.)
- Forms with real-time validation
- Data visualizations and charts

**Example Structure:**

```
includes/Admin/Menu.php (renders mount div)
    ↓
includes/Assets/Admin.php (enqueues React bundle)
    ↓
src/admin/main.jsx (React entry)
    ↓
src/admin/routes.jsx (routing)
    ↓
src/admin/pages/dashboard/index.jsx (shadcn/ui components)
```

**Pros:**
- Modern, accessible UI with shadcn/ui
- Client-side routing (no page reloads)
- Component reusability
- TypeScript type safety
- HMR during development
- Easy testing with React Testing Library
- Better performance after initial load

**Cons:**
- Larger initial bundle size
- Requires JavaScript knowledge
- More complex build setup
- Need to fetch data via REST API

---

## Best Practices

### 1. Component Organization

```
src/admin/
├── components/          # Reusable components
│   ├── Header.jsx
│   ├── Sidebar.jsx
│   └── DataTable.jsx
├── hooks/              # Custom hooks
│   ├── useApi.js
│   └── useAuth.js
├── pages/              # Route pages
│   ├── dashboard/
│   │   └── index.jsx
│   ├── partnerships/
│   │   ├── index.jsx
│   │   ├── PartnershipsList.jsx
│   │   └── PartnershipForm.jsx
│   └── settings/
│       └── index.jsx
├── utils/              # Helper functions
│   ├── api.js
│   └── formatters.js
├── main.jsx            # Entry point
├── routes.jsx          # Route definitions
└── index.css           # Global styles (Tailwind)
```

### 2. Data Fetching Pattern

```jsx
import { useState, useEffect } from "react";

function MyPage() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const fetchData = async () => {
      try {
        const response = await fetch(`${window.lrhAdmin.apiUrl}endpoint`, {
          headers: { "X-WP-Nonce": window.lrhAdmin.nonce },
        });
        const result = await response.json();
        setData(result.data);
      } catch (err) {
        setError(err.message);
      } finally {
        setLoading(false);
      }
    };
    fetchData();
  }, []);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;
  if (!data) return <div>No data</div>;

  return <div>{/* Render data */}</div>;
}
```

### 3. Error Handling

```jsx
import { useState } from "react";
import { Alert, AlertDescription } from "@/components/ui/alert";

function MyForm() {
  const [error, setError] = useState(null);

  const handleSubmit = async (data) => {
    try {
      setError(null);
      const response = await fetch(`${window.lrhAdmin.apiUrl}endpoint`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
        body: JSON.stringify(data),
      });

      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message || "Request failed");
      }

      // Success handling
    } catch (err) {
      setError(err.message);
    }
  };

  return (
    <div>
      {error && (
        <Alert variant="destructive">
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}
      {/* Form */}
    </div>
  );
}
```

### 4. TypeScript Usage

**Recommended:** Use TypeScript for better type safety

```typescript
// types.ts
interface Partnership {
  id: number;
  loan_officer_id: number;
  agent_id: number;
  status: "pending" | "active" | "declined";
  created_date: string;
}

interface ApiResponse<T> {
  success: boolean;
  data: T;
  message?: string;
}

// PartnershipsPage.tsx
import { useState, useEffect } from "react";

export default function PartnershipsPage() {
  const [partnerships, setPartnerships] = useState<Partnership[]>([]);
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    const fetchData = async () => {
      const response = await fetch(`${window.lrhAdmin.apiUrl}partnerships`, {
        headers: { "X-WP-Nonce": window.lrhAdmin.nonce },
      });
      const data: ApiResponse<Partnership[]> = await response.json();
      setPartnerships(data.data);
      setLoading(false);
    };
    fetchData();
  }, []);

  return <div>{/* Render with full type safety */}</div>;
}
```

---

## Common Patterns

### Pattern 1: Data Table with Actions

```jsx
import { useState, useEffect } from "react";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";

export default function PartnershipsList() {
  const [partnerships, setPartnerships] = useState([]);

  useEffect(() => {
    fetchPartnerships();
  }, []);

  const fetchPartnerships = async () => {
    const response = await fetch(`${window.lrhAdmin.apiUrl}partnerships`, {
      headers: { "X-WP-Nonce": window.lrhAdmin.nonce },
    });
    const data = await response.json();
    setPartnerships(data.data);
  };

  const handleDelete = async (id) => {
    if (!confirm("Delete this partnership?")) return;

    await fetch(`${window.lrhAdmin.apiUrl}partnerships/${id}`, {
      method: "DELETE",
      headers: { "X-WP-Nonce": window.lrhAdmin.nonce },
    });

    // Refresh list
    fetchPartnerships();
  };

  return (
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead>Partner</TableHead>
          <TableHead>Status</TableHead>
          <TableHead>Created</TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        {partnerships.map((p) => (
          <TableRow key={p.id}>
            <TableCell>{p.partner_name}</TableCell>
            <TableCell>
              <Badge variant={p.status === "active" ? "default" : "secondary"}>
                {p.status}
              </Badge>
            </TableCell>
            <TableCell>{new Date(p.created_date).toLocaleDateString()}</TableCell>
            <TableCell>
              <Button variant="ghost" size="sm" onClick={() => handleDelete(p.id)}>
                Delete
              </Button>
            </TableCell>
          </TableRow>
        ))}
      </TableBody>
    </Table>
  );
}
```

### Pattern 2: Modal Dialog Form

```jsx
import { useState } from "react";
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

export default function InvitePartnerDialog({ onSuccess }) {
  const [open, setOpen] = useState(false);
  const [email, setEmail] = useState("");
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const response = await fetch(`${window.lrhAdmin.apiUrl}partnerships`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
        body: JSON.stringify({ partner_email: email }),
      });

      const data = await response.json();
      if (data.success) {
        setOpen(false);
        setEmail("");
        onSuccess?.();
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button>Invite Partner</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Invite Realtor Partner</DialogTitle>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="space-y-2">
            <Label htmlFor="email">Partner Email</Label>
            <Input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </div>
          <Button type="submit" disabled={loading}>
            {loading ? "Sending..." : "Send Invitation"}
          </Button>
        </form>
      </DialogContent>
    </Dialog>
  );
}
```

### Pattern 3: Settings Page with Tabs

```jsx
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import GeneralSettings from "./GeneralSettings";
import IntegrationSettings from "./IntegrationSettings";
import NotificationSettings from "./NotificationSettings";

export default function SettingsPage() {
  return (
    <div className="p-8 space-y-4">
      <h2 className="text-3xl font-bold tracking-tight">Settings</h2>

      <Tabs defaultValue="general">
        <TabsList>
          <TabsTrigger value="general">General</TabsTrigger>
          <TabsTrigger value="integrations">Integrations</TabsTrigger>
          <TabsTrigger value="notifications">Notifications</TabsTrigger>
        </TabsList>

        <TabsContent value="general">
          <Card>
            <CardHeader>
              <CardTitle>General Settings</CardTitle>
              <CardDescription>
                Configure general plugin settings
              </CardDescription>
            </CardHeader>
            <CardContent>
              <GeneralSettings />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="integrations">
          <Card>
            <CardHeader>
              <CardTitle>Integrations</CardTitle>
              <CardDescription>
                Connect with external services
              </CardDescription>
            </CardHeader>
            <CardContent>
              <IntegrationSettings />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="notifications">
          <Card>
            <CardHeader>
              <CardTitle>Notifications</CardTitle>
              <CardDescription>
                Configure email notifications
              </CardDescription>
            </CardHeader>
            <CardContent>
              <NotificationSettings />
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
```

---

## Multiplugin Architecture

### Shared Component Library

For a multiplugin system, create a **shared component library** that all plugins can use:

**Strategy:**

```
wp-content/
├── plugins/
│   ├── shared-components/         # Shared component library
│   │   ├── src/
│   │   │   ├── components/
│   │   │   │   ├── ui/           # shadcn/ui components
│   │   │   │   ├── forms/        # Reusable forms
│   │   │   │   └── layouts/      # Layout components
│   │   │   └── hooks/            # Shared hooks
│   │   └── package.json
│   │
│   ├── frs-lrg/                  # Plugin 1
│   │   ├── src/admin/
│   │   └── package.json
│   │
│   ├── frs-wp-users/             # Plugin 2
│   │   ├── src/admin/
│   │   └── package.json
│   │
│   └── frs-partnership-portal/   # Plugin 3
│       ├── src/admin/
│       └── package.json
```

**Option 1: NPM Workspace (Monorepo)**

**Root `package.json`:**
```json
{
  "name": "frs-plugin-ecosystem",
  "private": true,
  "workspaces": [
    "plugins/shared-components",
    "plugins/frs-lrg",
    "plugins/frs-wp-users",
    "plugins/frs-partnership-portal"
  ]
}
```

**Plugin `package.json`:**
```json
{
  "dependencies": {
    "@frs/shared-components": "workspace:*"
  }
}
```

**Usage in Plugin:**
```javascript
import { Card, Button } from "@frs/shared-components/ui";
import { useApi } from "@frs/shared-components/hooks";
```

**Option 2: Private NPM Package**

Publish `shared-components` as private NPM package:

```json
{
  "name": "@frs/shared-components",
  "version": "1.0.0",
  "main": "dist/index.js",
  "module": "dist/index.esm.js"
}
```

Plugins install it:
```bash
npm install @frs/shared-components
```

### Shared State Management

For cross-plugin data sharing:

**Strategy 1: WordPress Options API**
```javascript
// Save state
await fetch(`${window.pluginAdmin.apiUrl}settings`, {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
    "X-WP-Nonce": window.pluginAdmin.nonce,
  },
  body: JSON.stringify({ key: "shared_data", value: data }),
});

// Load state
const response = await fetch(`${window.pluginAdmin.apiUrl}settings/shared_data`, {
  headers: { "X-WP-Nonce": window.pluginAdmin.nonce },
});
```

**Strategy 2: WordPress Transients API**
For temporary shared data (cached):

```php
// Plugin A sets data
set_transient('frs_shared_user_data', $data, HOUR_IN_SECONDS);

// Plugin B reads data
$data = get_transient('frs_shared_user_data');
```

**Strategy 3: Custom Database Table**
For complex shared data:

```php
// Shared table
CREATE TABLE wp_frs_shared_data (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    data_key varchar(255) NOT NULL,
    data_value longtext,
    plugin varchar(100),
    updated_at datetime,
    PRIMARY KEY (id),
    UNIQUE KEY data_key (data_key)
);
```

### Shared REST API Namespace

Create shared API namespace for cross-plugin endpoints:

```php
// In shared-components plugin or base plugin
namespace FRS\SharedAPI;

class Routes {
    private $namespace = 'frs-shared/v1';

    public function register_routes() {
        // Shared user endpoint
        register_rest_route($this->namespace, '/users/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_user'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);

        // Shared settings endpoint
        register_rest_route($this->namespace, '/settings', [
            'methods' => 'GET',
            'callback' => [$this, 'get_settings'],
            'permission_callback' => [$this, 'check_permissions'],
        ]);
    }
}
```

**All plugins access:**
```javascript
// frs-lrg
fetch(`/wp-json/frs-shared/v1/users/${userId}`);

// frs-wp-users
fetch(`/wp-json/frs-shared/v1/users/${userId}`);

// frs-partnership-portal
fetch(`/wp-json/frs-shared/v1/users/${userId}`);
```

### Design System Tokens

Share design tokens across all plugins:

**File:** `shared-components/src/styles/tokens.css`

```css
:root {
  /* Brand colors */
  --frs-primary: #2563eb;
  --frs-secondary: #2dd4da;
  --frs-accent: #f59e0b;

  /* Semantic colors */
  --frs-success: #10b981;
  --frs-warning: #f59e0b;
  --frs-error: #ef4444;
  --frs-info: #3b82f6;

  /* Spacing */
  --frs-space-xs: 0.25rem;
  --frs-space-sm: 0.5rem;
  --frs-space-md: 1rem;
  --frs-space-lg: 1.5rem;
  --frs-space-xl: 2rem;

  /* Border radius */
  --frs-radius-sm: 0.25rem;
  --frs-radius-md: 0.5rem;
  --frs-radius-lg: 0.75rem;

  /* Typography */
  --frs-font-sans: system-ui, -apple-system, sans-serif;
  --frs-font-mono: 'Courier New', monospace;
}
```

**Import in all plugins:**
```javascript
// src/admin/index.css
@import "@frs/shared-components/styles/tokens.css";
@import "tailwindcss/base";
@import "tailwindcss/components";
@import "tailwindcss/utilities";
```

---

## Summary

The **React SPA Admin Pattern** is the recommended approach for building modern WordPress plugin admin interfaces with this boilerplate:

**Key Benefits:**
1. Modern UI with shadcn/ui components (accessible, beautiful)
2. Client-side routing (no page reloads)
3. Hot Module Replacement during development
4. Component reusability across plugins
5. TypeScript type safety
6. Better performance after initial load

**Complete Flow:**
```
Menu.php → Mount Div
    ↓
Assets/Admin.php → Enqueue React Bundle + Pass Data
    ↓
main.jsx → Find Mount Div → Render App
    ↓
Router → Navigate Pages (No Reload)
    ↓
Pages → shadcn/ui Components → Fetch from REST API
```

**For Multiplugin Systems:**
- Create shared component library
- Use NPM workspaces or private packages
- Share design tokens and styles
- Unified REST API namespace
- Consistent user experience across all plugins

**This pattern enables building a complete ecosystem of interconnected WordPress plugins with modern, maintainable, and scalable architecture.**
