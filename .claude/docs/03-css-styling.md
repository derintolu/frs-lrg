# CSS & Styling

Complete guide to CSS styling patterns, Tailwind CSS usage, and responsive design in the Lending Resource Hub plugin.

---

## Table of Contents

- [CSS Transform Scale Math](#css-transform-scale-math)
- [Tailwind CSS Patterns](#tailwind-css-patterns)
- [Responsive Hiding Patterns](#responsive-hiding-patterns)
- [Breakpoint System](#breakpoint-system)
- [Overflow Behavior](#overflow-behavior)
- [Custom CSS Guidelines](#custom-css-guidelines)

---

## CSS Transform Scale Math

### The Formula

When scaling elements to fit containers, use this exact formula:

```
scale = targetWidth ÷ actualWidth
```

**Example:**
- Container width: 375px (mobile)
- Element actual width: 1200px (desktop card)
- Scale: `375 ÷ 1200 = 0.3125`

### Why This Formula Works

```css
/* Element is 1200px wide */
.card {
  width: 1200px;
}

/* Scale it down to fit 375px container */
.card-scaled {
  width: 1200px;
  transform: scale(0.3125); /* 375 ÷ 1200 */
}

/* Result: Visual width = 1200 × 0.3125 = 375px */
```

### Common Mistakes

#### ❌ WRONG - Guessing the scale

```css
.card {
  transform: scale(0.5); /* Why 0.5? No reason. Won't fit correctly. */
}
```

#### ❌ WRONG - Using percentages

```css
.card {
  transform: scale(50%); /* Invalid - scale() takes decimals, not percentages */
}
```

#### ✅ CORRECT - Calculate the exact scale

```css
.card {
  /* Container: 375px, Element: 1200px */
  transform: scale(0.3125); /* 375 ÷ 1200 = 0.3125 */
}
```

### Responsive Scale Example

```tsx
// React component with responsive scaling
import { useState, useEffect, useRef } from 'react';

interface ScaledCardProps {
  readonly children: React.ReactNode;
  readonly baseWidth: number; // Original card width (e.g., 1200)
}

export function ScaledCard({ children, baseWidth }: ScaledCardProps): JSX.Element {
  const [scale, setScale] = useState<number>(1);
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const updateScale = () => {
      if (!containerRef.current) return;

      const containerWidth = containerRef.current.offsetWidth;
      const newScale = containerWidth / baseWidth;

      // Only scale down, never scale up
      setScale(Math.min(newScale, 1));
    };

    updateScale();
    window.addEventListener('resize', updateScale);
    return () => window.removeEventListener('resize', updateScale);
  }, [baseWidth]);

  return (
    <div ref={containerRef} className="w-full overflow-hidden">
      <div
        style={{
          width: `${baseWidth}px`,
          transform: `scale(${scale})`,
          transformOrigin: 'top left',
        }}
      >
        {children}
      </div>
    </div>
  );
}

// Usage
<ScaledCard baseWidth={1200}>
  <div className="bg-white p-8 rounded-lg shadow-lg">
    <h2 className="text-2xl font-bold">Card Title</h2>
    <p>Card content that will scale proportionally</p>
  </div>
</ScaledCard>
```

### Verifying Scale in Browser

**DevTools method:**

1. Open browser DevTools
2. Select the scaled element
3. Check **Computed** tab
4. Look for `transform: scale(...)`
5. Calculate: `containerWidth ÷ actualWidth`
6. Compare with computed scale value

**Example:**
```
Container width: 375px
Element width: 1200px
Expected scale: 375 ÷ 1200 = 0.3125
Computed: matrix(0.3125, 0, 0, 0.3125, 0, 0) ✓ Correct!
```

---

## Tailwind CSS Patterns

### Installation & Configuration

**File:** `tailwind.config.js`

```javascript
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './src/**/*.{js,jsx,ts,tsx}',
    './blocks/**/*.{js,jsx}',
    './views/**/*.php',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#2563eb',
          foreground: '#ffffff',
        },
        secondary: {
          DEFAULT: '#2dd4da',
          foreground: '#ffffff',
        },
        accent: {
          DEFAULT: '#f59e0b',
          foreground: '#000000',
        },
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui'],
        heading: ['Poppins', 'ui-sans-serif', 'system-ui'],
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
        '128': '32rem',
      },
    },
  },
  plugins: [],
};
```

### Using Tailwind Classes

**Utility-First Approach:**

```tsx
// ✅ GOOD - Utility classes
export function Card(): JSX.Element {
  return (
    <div className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
      <h2 className="text-2xl font-bold text-gray-900 mb-4">
        Card Title
      </h2>
      <p className="text-gray-600 leading-relaxed">
        Card content with proper spacing and typography.
      </p>
    </div>
  );
}

// ❌ BAD - Inline styles (harder to maintain)
export function Card(): JSX.Element {
  return (
    <div style={{
      backgroundColor: 'white',
      borderRadius: '0.5rem',
      boxShadow: '0 4px 6px rgba(0,0,0,0.1)',
      padding: '1.5rem'
    }}>
      <h2 style={{ fontSize: '1.5rem', fontWeight: 'bold', marginBottom: '1rem' }}>
        Card Title
      </h2>
    </div>
  );
}
```

### Custom Tailwind Utilities

**File:** `src/frontend/index.css`

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

/* Custom component classes */
@layer components {
  .btn-primary {
    @apply bg-primary text-primary-foreground px-6 py-3 rounded-lg font-semibold;
    @apply hover:bg-primary/90 transition-colors;
    @apply focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2;
  }

  .card {
    @apply bg-white rounded-lg shadow-md p-6;
    @apply hover:shadow-lg transition-shadow;
  }

  .input-field {
    @apply w-full px-4 py-2 border border-gray-300 rounded-lg;
    @apply focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent;
    @apply placeholder:text-gray-400;
  }
}

/* Custom utilities */
@layer utilities {
  .text-balance {
    text-wrap: balance;
  }

  .scroll-smooth-fast {
    scroll-behavior: smooth;
    scroll-padding-top: 2rem;
  }
}
```

**Usage:**

```tsx
import { Button } from '@/components/ui/button';

export function LoginForm(): JSX.Element {
  return (
    <form className="card max-w-md mx-auto">
      <h2 className="text-2xl font-bold mb-6">Login</h2>

      <input
        type="email"
        placeholder="Email"
        className="input-field mb-4"
      />

      <input
        type="password"
        placeholder="Password"
        className="input-field mb-6"
      />

      <button type="submit" className="btn-primary w-full">
        Log In
      </button>
    </form>
  );
}
```

---

## Responsive Hiding Patterns

### CRITICAL RULE

**THIS IS A RECURRING ISSUE - READ CAREFULLY:**

When hiding/showing elements based on screen size, there is ONLY ONE correct pattern.

### ✅ CORRECT PATTERNS (ALWAYS USE THESE)

**Hide on mobile, show on desktop:**

```tsx
<div className="grid grid-cols-3 gap-4 max-md:hidden">
  {/* Visible on screens ≥768px (desktop), hidden on <768px (mobile) */}
</div>
```

Pattern: `[display-type] max-md:hidden`

**Show on mobile, hide on desktop:**

```tsx
<div className="flex flex-col gap-2 md:hidden">
  {/* Visible on screens <768px (mobile), hidden on ≥768px (desktop) */}
</div>
```

Pattern: `[display-type] md:hidden`

### ❌ NEVER USE THESE (THEY DON'T WORK)

```tsx
// ❌ WRONG - Element stays hidden at ALL screen sizes
<div className="hidden md:grid md:grid-cols-3 gap-4">

// ❌ WRONG - Same problem
<div className="hidden md:flex">

// ❌ WRONG - Same problem
<div className="hidden md:block">
```

### Why These Don't Work

**Technical explanation:**

```css
/* hidden class applies display: none at ALL breakpoints */
.hidden {
  display: none; /* Applies to all screen sizes */
}

/* md:grid tries to override at 768px+ */
@media (min-width: 768px) {
  .md\:grid {
    display: grid; /* Tries to override, but... */
  }
}

/* Problem: Both rules have same specificity, so `hidden` wins */
/* Result: Element stays hidden on ALL screen sizes */
```

**The correct approach:**

```css
/* Start with display type (applies to all sizes) */
.grid {
  display: grid;
}

/* Hide BELOW 768px */
@media (max-width: 767px) {
  .max-md\:hidden {
    display: none;
  }
}

/* Result: Visible on desktop, hidden on mobile ✓ */
```

### Real-World Examples

**Desktop Navigation, Mobile Hamburger:**

```tsx
export function Header(): JSX.Element {
  return (
    <header className="bg-white shadow-md">
      <div className="container mx-auto px-4 py-4 flex items-center justify-between">
        <Logo />

        {/* Desktop Navigation - Hidden on mobile */}
        <nav className="flex gap-6 max-md:hidden">
          <a href="/dashboard">Dashboard</a>
          <a href="/partnerships">Partnerships</a>
          <a href="/leads">Leads</a>
        </nav>

        {/* Mobile Hamburger - Hidden on desktop */}
        <button className="flex flex-col gap-1 md:hidden">
          <span className="block w-6 h-0.5 bg-gray-900"></span>
          <span className="block w-6 h-0.5 bg-gray-900"></span>
          <span className="block w-6 h-0.5 bg-gray-900"></span>
        </button>
      </div>
    </header>
  );
}
```

**Dashboard Cards - Different Layouts:**

```tsx
export function Dashboard(): JSX.Element {
  return (
    <div className="container mx-auto px-4 py-8">
      {/* Desktop: 3 columns, Mobile: 1 column */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <StatCard title="Partnerships" value={12} />
        <StatCard title="Leads" value={48} />
        <StatCard title="Pages" value={5} />
      </div>

      {/* Desktop: Side-by-side chart and table */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <LeadsChart />
        <RecentLeads />
      </div>

      {/* Desktop-only advanced filters */}
      <div className="flex gap-4 mt-8 max-md:hidden">
        <DateRangePicker />
        <StatusFilter />
        <SourceFilter />
      </div>

      {/* Mobile-only simple filter */}
      <div className="flex flex-col gap-2 mt-8 md:hidden">
        <select className="input-field">
          <option>All Time</option>
          <option>Last 7 Days</option>
          <option>Last 30 Days</option>
        </select>
      </div>
    </div>
  );
}
```

---

## Breakpoint System

### CRITICAL: Use `md:` (768px) for Desktop

**ALWAYS use `md:` (768px) for desktop breakpoints, NEVER `lg:` (1024px).**

### Tailwind Breakpoints

```css
/* Default (mobile-first) */
/* 0px - 639px */

sm: 640px   /* Small tablets (portrait) */
md: 768px   /* Tablets (landscape) & Laptops - DEFAULT for desktop */
lg: 1024px  /* Large desktops only (rarely used) */
xl: 1280px  /* Extra large screens (rarely used) */
2xl: 1536px /* Very large screens (rarely used) */
```

### Why `md:` (768px) Not `lg:` (1024px)

**Most laptop screens:**
- 1366×768 (most common laptop resolution)
- 1920×1080 (full HD laptops)
- 1440×900 (MacBook Air)

**Problem with `lg:` (1024px):**
- Using `lg:` means desktop layout only shows at 1024px+
- Users with 768px-1023px screens see mobile layout on laptop
- This feels broken and confusing

**Solution:**
- Use `md:` (768px) as the tablet-to-desktop transition
- Users expect desktop layout at 768px+, not 1024px+
- Industry standard for responsive design

### Breakpoint Examples

```tsx
export function ResponsiveComponent(): JSX.Element {
  return (
    <div>
      {/* Font sizes: mobile → tablet → desktop */}
      <h1 className="text-2xl sm:text-3xl md:text-4xl lg:text-5xl">
        Responsive Heading
      </h1>

      {/* Padding: mobile → desktop */}
      <div className="p-4 md:p-8">
        Content with responsive padding
      </div>

      {/* Grid columns: 1 → 2 → 3 */}
      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        <Card />
        <Card />
        <Card />
      </div>

      {/* Flexbox direction: column → row */}
      <div className="flex flex-col md:flex-row gap-4">
        <Sidebar />
        <MainContent />
      </div>

      {/* Width constraints: full → constrained */}
      <div className="w-full md:w-2/3 lg:w-1/2 mx-auto">
        Centered content with max width
      </div>
    </div>
  );
}
```

### Container Queries (Future)

Tailwind CSS 3.4+ supports container queries:

```tsx
export function Card(): JSX.Element {
  return (
    <div className="@container">
      {/* Change layout based on container width, not viewport */}
      <div className="@md:flex @md:items-center gap-4">
        <img src="..." className="@md:w-32" />
        <div className="flex-1">
          <h3 className="text-lg @md:text-xl">Card Title</h3>
          <p className="text-sm @md:text-base">Description</p>
        </div>
      </div>
    </div>
  );
}
```

---

## Overflow Behavior

### Hidden Overflow Issues

**Problem:** Content cut off when using `overflow-hidden`

```tsx
// ❌ BAD - Content gets cut off on mobile
<div className="overflow-hidden h-screen">
  <LongContent /> {/* Bottom is cut off */}
</div>

// ✅ GOOD - Allow scrolling
<div className="overflow-y-auto h-screen">
  <LongContent /> {/* Scrollable */}
</div>
```

### Horizontal Scroll Prevention

```tsx
// Prevent horizontal scroll on mobile
export function Layout({ children }: { children: React.ReactNode }): JSX.Element {
  return (
    <div className="overflow-x-hidden min-h-screen">
      {children}
    </div>
  );
}
```

### Scrollable Containers

```tsx
export function SidebarMenu(): JSX.Element {
  return (
    <aside className="w-64 h-screen flex flex-col">
      {/* Header - Fixed */}
      <div className="p-4 border-b">
        <Logo />
      </div>

      {/* Menu - Scrollable */}
      <nav className="flex-1 overflow-y-auto p-4">
        <MenuItem />
        <MenuItem />
        {/* ... many items ... */}
      </nav>

      {/* Footer - Fixed */}
      <div className="p-4 border-t">
        <UserProfile />
      </div>
    </aside>
  );
}
```

### Custom Scrollbars

```css
/* File: src/frontend/index.css */

/* Custom scrollbar (WebKit browsers) */
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Firefox */
.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #888 #f1f1f1;
}
```

---

## Custom CSS Guidelines

### When to Use Custom CSS

**Use Tailwind for:**
- Layout (flex, grid)
- Spacing (margin, padding)
- Typography (font size, weight)
- Colors (background, text, border)
- Common patterns (hover, focus)

**Use Custom CSS for:**
- Complex animations
- Pseudo-elements (::before, ::after)
- Browser-specific styles
- Third-party library overrides
- Non-standard properties

### Custom CSS Example

```css
/* File: src/frontend/index.css */

@layer components {
  /* Animated gradient background */
  .gradient-bg {
    background: linear-gradient(135deg, #2563eb 0%, #2dd4da 100%);
    background-size: 200% 200%;
    animation: gradient-shift 3s ease infinite;
  }

  @keyframes gradient-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  /* Glass morphism effect */
  .glass {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }

  /* Loading skeleton */
  .skeleton {
    background: linear-gradient(
      90deg,
      #f0f0f0 25%,
      #e0e0e0 50%,
      #f0f0f0 75%
    );
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s ease-in-out infinite;
  }

  @keyframes skeleton-loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }
}
```

**Usage:**

```tsx
export function HeroSection(): JSX.Element {
  return (
    <section className="gradient-bg min-h-screen flex items-center justify-center text-white">
      <div className="glass p-12 rounded-2xl max-w-2xl">
        <h1 className="text-5xl font-bold mb-4">
          Welcome to LRH Portal
        </h1>
        <p className="text-xl">
          Manage partnerships and grow your business.
        </p>
      </div>
    </section>
  );
}

export function LoadingCard(): JSX.Element {
  return (
    <div className="card">
      <div className="skeleton h-8 w-3/4 mb-4 rounded"></div>
      <div className="skeleton h-4 w-full mb-2 rounded"></div>
      <div className="skeleton h-4 w-5/6 rounded"></div>
    </div>
  );
}
```

---

## Best Practices Summary

### 1. CSS Transform Scale

```typescript
// Calculate exact scale
const scale = containerWidth / actualWidth;

// Never guess, always calculate
// Use DevTools to verify computed scale
```

### 2. Responsive Hiding

```tsx
// ✅ CORRECT
<div className="grid grid-cols-3 max-md:hidden">

// ❌ NEVER
<div className="hidden md:grid">
```

### 3. Breakpoints

```tsx
// ✅ Use md: (768px) for desktop
<div className="text-base md:text-lg">

// ❌ Don't use lg: (1024px) for desktop
<div className="text-base lg:text-lg">
```

### 4. Tailwind First, Custom Second

```tsx
// ✅ GOOD - Tailwind utilities
<button className="px-6 py-3 bg-primary text-white rounded-lg hover:bg-primary/90">

// ❌ BAD - Inline styles
<button style={{ padding: '12px 24px', background: '#2563eb' }}>
```

### 5. Verify in Browser DevTools

- Check computed styles
- Verify transform scale values
- Test responsive breakpoints
- Debug overflow issues

---

## Related Documentation

- [01-development-workflow.md](./01-development-workflow.md) - Dev server for live CSS updates
- [05-frontend-patterns.md](./05-frontend-patterns.md) - React components with Tailwind
- [08-troubleshooting.md](./08-troubleshooting.md) - CSS not applying, HMR issues
