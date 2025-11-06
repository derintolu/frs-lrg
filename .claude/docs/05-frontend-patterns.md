# Frontend Patterns

Complete guide to React, TypeScript, and frontend development patterns for the Lending Resource Hub plugin.

---

## Table of Contents

- [React Component Structure](#react-component-structure)
- [TypeScript Patterns](#typescript-patterns)
- [State Management with Jotai](#state-management-with-jotai)
- [Portal Rendering](#portal-rendering)
- [API Integration](#api-integration)
- [Form Handling](#form-handling)
- [Routing](#routing)

---

## React Component Structure

### Functional Components with Hooks

**ALWAYS use functional components with hooks. NEVER use class components.**

```tsx
import { useState, useEffect } from 'react';

interface DashboardProps {
  readonly userId: number;
  readonly userName: string;
}

export function Dashboard({ userId, userName }: DashboardProps): JSX.Element {
  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchStats() {
      try {
        const response = await fetch(`/wp-json/lrh/v1/dashboard/stats?user_id=${userId}`);
        const data = await response.json();
        setStats(data);
      } catch (err) {
        setError('Failed to load dashboard stats');
      } finally {
        setLoading(false);
      }
    }

    void fetchStats();
  }, [userId]);

  if (loading) return <LoadingSpinner />;
  if (error) return <ErrorMessage message={error} />;
  if (!stats) return null;

  return (
    <div className="dashboard">
      <h1>Welcome, {userName}</h1>
      <StatsCards stats={stats} />
      <RecentActivity userId={userId} />
    </div>
  );
}
```

### Component File Structure

```
components/
├── Dashboard/
│   ├── Dashboard.tsx          # Main component
│   ├── StatsCards.tsx         # Sub-component
│   ├── RecentActivity.tsx     # Sub-component
│   └── index.ts               # Barrel export
```

**Barrel Export:**

```typescript
// components/Dashboard/index.ts
export { Dashboard } from './Dashboard';
export { StatsCards } from './StatsCards';
export { RecentActivity } from './RecentActivity';
```

**Usage:**

```typescript
// Instead of multiple imports
import { Dashboard } from './components/Dashboard/Dashboard';
import { StatsCards } from './components/Dashboard/StatsCards';

// Use barrel export
import { Dashboard, StatsCards } from './components/Dashboard';
```

---

## TypeScript Patterns

### Proper Typing

**Interface for Props:**

```typescript
interface UserProfileProps {
  readonly userId: number;
  readonly onUpdate?: (profile: UserProfile) => void;
  readonly className?: string;
}

interface UserProfile {
  readonly id: number;
  readonly name: string;
  readonly email: string;
  readonly avatar_url: string;
  readonly bio?: string;
}

export function UserProfile({ userId, onUpdate, className }: UserProfileProps): JSX.Element {
  // Component implementation
}
```

**Type for Component State:**

```typescript
interface DashboardState {
  stats: DashboardStats | null;
  loading: boolean;
  error: string | null;
}

type DashboardStats = {
  partnerships: number;
  leads: number;
  pages: number;
  conversion_rate: number;
};

export function Dashboard(): JSX.Element {
  const [state, setState] = useState<DashboardState>({
    stats: null,
    loading: true,
    error: null,
  });

  // ...
}
```

### Window Global Types

```typescript
// src/frontend/types/global.d.ts
declare global {
  interface Window {
    lrhPortalConfig: {
      userId: number;
      userName: string;
      userEmail: string;
      userRole: string;
      apiUrl: string;
      restNonce: string;
    };
  }
}

export {};
```

### Event Handler Types

```typescript
import type { FormEvent, ChangeEvent, MouseEvent } from 'react';

export function LoginForm(): JSX.Element {
  const [email, setEmail] = useState<string>('');

  const handleChange = (e: ChangeEvent<HTMLInputElement>): void => {
    setEmail(e.target.value);
  };

  const handleSubmit = (e: FormEvent<HTMLFormElement>): void => {
    e.preventDefault();
    // Handle form submission
  };

  const handleClick = (e: MouseEvent<HTMLButtonElement>): void => {
    e.preventDefault();
    // Handle button click
  };

  return (
    <form onSubmit={handleSubmit}>
      <input type="email" value={email} onChange={handleChange} />
      <button type="submit" onClick={handleClick}>Submit</button>
    </form>
  );
}
```

---

## State Management with Jotai

### Why Jotai?

- **Lightweight** - Tiny bundle size (~3KB)
- **Simple API** - Just atoms and hooks
- **TypeScript-first** - Full type inference
- **No boilerplate** - No reducers or actions

### Defining Atoms

```typescript
// src/frontend/store/atoms.ts
import { atom } from 'jotai';

// Primitive atoms
export const userIdAtom = atom<number>(0);
export const userNameAtom = atom<string>('');
export const userEmailAtom = atom<string>('');

// Derived atom (computed)
export const userInitialsAtom = atom((get) => {
  const name = get(userNameAtom);
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase();
});

// Async atom (data fetching)
export const partnershipsAtom = atom(async (get) => {
  const userId = get(userIdAtom);
  const response = await fetch(`/wp-json/lrh/v1/partnerships?user_id=${userId}`);
  return response.json();
});
```

### Using Atoms in Components

```typescript
import { useAtom, useAtomValue, useSetAtom } from 'jotai';
import { userNameAtom, userInitialsAtom, partnershipsAtom } from '@/store/atoms';

export function Header(): JSX.Element {
  // Read and write
  const [userName, setUserName] = useAtom(userNameAtom);

  // Read only
  const userInitials = useAtomValue(userInitialsAtom);

  // Write only
  const setUserName = useSetAtom(userNameAtom);

  return (
    <header>
      <h1>Welcome, {userName}</h1>
      <div className="avatar">{userInitials}</div>
    </header>
  );
}

export function PartnershipList(): JSX.Element {
  const partnerships = useAtomValue(partnershipsAtom);

  return (
    <ul>
      {partnerships.map((p) => (
        <li key={p.id}>{p.name}</li>
      ))}
    </ul>
  );
}
```

---

## Portal Rendering

### Main Entry Point

**File:** `src/frontend/main.tsx`

```tsx
import React from 'react';
import ReactDOM from 'react-dom/client';
import { PortalApp } from './PortalApp';
import './index.css';

// Get config from WordPress
const config = window.lrhPortalConfig;

if (!config) {
  console.error('LRH Portal config not found');
} else {
  // Mount React app
  const rootElement = document.getElementById('lrh-portal-root');
  if (rootElement) {
    ReactDOM.createRoot(rootElement).render(
      <React.StrictMode>
        <PortalApp config={config} />
      </React.StrictMode>
    );
  }
}
```

### Portal App Component

**File:** `src/frontend/PortalApp.tsx`

```tsx
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { Provider as JotaiProvider } from 'jotai';
import { Dashboard } from './components/Dashboard';
import { Partnerships } from './components/Partnerships';
import { Leads } from './components/Leads';
import { Settings } from './components/Settings';

interface PortalAppProps {
  config: {
    userId: number;
    userName: string;
    userEmail: string;
    userRole: string;
    apiUrl: string;
    restNonce: string;
  };
}

export function PortalApp({ config }: PortalAppProps): JSX.Element {
  return (
    <JotaiProvider>
      <BrowserRouter basename="/portal">
        <Routes>
          <Route path="/" element={<Dashboard config={config} />} />
          <Route path="/partnerships" element={<Partnerships config={config} />} />
          <Route path="/leads" element={<Leads config={config} />} />
          <Route path="/settings" element={<Settings config={config} />} />
        </Routes>
      </BrowserRouter>
    </JotaiProvider>
  );
}
```

---

## API Integration

### API Service

**File:** `src/frontend/services/api.ts`

```typescript
class ApiService {
  private baseUrl: string;
  private nonce: string;

  constructor(baseUrl: string, nonce: string) {
    this.baseUrl = baseUrl;
    this.nonce = nonce;
  }

  private async request<T>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> {
    const url = `${this.baseUrl}${endpoint}`;
    const headers = {
      'Content-Type': 'application/json',
      'X-WP-Nonce': this.nonce,
      ...options.headers,
    };

    const response = await fetch(url, {
      ...options,
      headers,
    });

    if (!response.ok) {
      throw new Error(`API error: ${response.statusText}`);
    }

    return response.json();
  }

  async get<T>(endpoint: string): Promise<T> {
    return this.request<T>(endpoint, { method: 'GET' });
  }

  async post<T>(endpoint: string, data: unknown): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async put<T>(endpoint: string, data: unknown): Promise<T> {
    return this.request<T>(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async delete<T>(endpoint: string): Promise<T> {
    return this.request<T>(endpoint, { method: 'DELETE' });
  }
}

// Export singleton
export const api = new ApiService(
  window.lrhPortalConfig.apiUrl,
  window.lrhPortalConfig.restNonce
);
```

### Using API Service

```typescript
import { api } from '@/services/api';

export function usePartnerships(userId: number) {
  const [partnerships, setPartnerships] = useState<Partnership[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    async function fetchPartnerships() {
      try {
        const data = await api.get<Partnership[]>(`/partnerships?user_id=${userId}`);
        setPartnerships(data);
      } catch (err) {
        setError(err instanceof Error ? err.message : 'Unknown error');
      } finally {
        setLoading(false);
      }
    }

    void fetchPartnerships();
  }, [userId]);

  return { partnerships, loading, error };
}
```

---

## Form Handling

### Using React Hook Form + Zod

```typescript
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';

const partnershipSchema = z.object({
  email: z.string().email('Invalid email address'),
  message: z.string().min(10, 'Message must be at least 10 characters'),
});

type PartnershipFormData = z.infer<typeof partnershipSchema>;

export function PartnershipInviteForm(): JSX.Element {
  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<PartnershipFormData>({
    resolver: zodResolver(partnershipSchema),
  });

  const onSubmit = async (data: PartnershipFormData) => {
    try {
      await api.post('/partnerships', data);
      alert('Invitation sent!');
    } catch (err) {
      alert('Failed to send invitation');
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      <div>
        <label htmlFor="email">Email</label>
        <input
          id="email"
          type="email"
          {...register('email')}
          className="input-field"
        />
        {errors.email && <p className="error">{errors.email.message}</p>}
      </div>

      <div>
        <label htmlFor="message">Message</label>
        <textarea
          id="message"
          {...register('message')}
          className="input-field"
        />
        {errors.message && <p className="error">{errors.message.message}</p>}
      </div>

      <button type="submit" disabled={isSubmitting}>
        {isSubmitting ? 'Sending...' : 'Send Invitation'}
      </button>
    </form>
  );
}
```

---

## Routing

### Client-Side Routing with React Router

```tsx
import { BrowserRouter, Routes, Route, Link, useNavigate } from 'react-router-dom';

export function App(): JSX.Element {
  return (
    <BrowserRouter basename="/portal">
      <Layout>
        <Routes>
          <Route path="/" element={<Dashboard />} />
          <Route path="/partnerships" element={<Partnerships />} />
          <Route path="/partnerships/:id" element={<PartnershipDetail />} />
          <Route path="/leads" element={<Leads />} />
          <Route path="/settings" element={<Settings />} />
          <Route path="*" element={<NotFound />} />
        </Routes>
      </Layout>
    </BrowserRouter>
  );
}

function Navigation(): JSX.Element {
  return (
    <nav>
      <Link to="/">Dashboard</Link>
      <Link to="/partnerships">Partnerships</Link>
      <Link to="/leads">Leads</Link>
      <Link to="/settings">Settings</Link>
    </nav>
  );
}

function PartnershipDetail(): JSX.Element {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const handleBack = () => {
    navigate('/partnerships');
  };

  return (
    <div>
      <button onClick={handleBack}>Back</button>
      <h1>Partnership {id}</h1>
    </div>
  );
}
```

---

## Related Documentation

- [01-development-workflow.md](./01-development-workflow.md) - Dev server for HMR
- [02-architecture.md](./02-architecture.md) - System architecture
- [03-css-styling.md](./03-css-styling.md) - Tailwind CSS styling
- [07-common-tasks.md](./07-common-tasks.md) - Adding components
