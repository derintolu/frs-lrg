# Boilerplate Features

Guide to the advanced features included in the WordPress Plugin Boilerplate used by the Lending Resource Hub plugin.

---

## Table of Contents

- [Storybook](#storybook)
- [Documentation Site](#documentation-site)
- [Utility Scripts](#utility-scripts)
- [Shadcn UI](#shadcn-ui)
- [GitHub Actions CI/CD](#github-actions-cicd)
- [Grunt Tasks](#grunt-tasks)
- [Plugin Renaming System](#plugin-renaming-system)

---

## Storybook

### What is Storybook?

Storybook is a development environment for UI components. Build components in isolation and document them.

### Starting Storybook

```bash
# Start Storybook dev server
npm run storybook

# Opens browser at http://localhost:6006
```

### Creating Stories

**File:** `src/frontend/components/Button.stories.tsx`

```tsx
import type { Meta, StoryObj } from '@storybook/react';
import { Button } from './Button';

const meta: Meta<typeof Button> = {
  title: 'Components/Button',
  component: Button,
  tags: ['autodocs'],
  argTypes: {
    variant: {
      control: 'select',
      options: ['primary', 'secondary', 'outline'],
    },
  },
};

export default meta;
type Story = StoryObj<typeof Button>;

export const Primary: Story = {
  args: {
    variant: 'primary',
    children: 'Primary Button',
  },
};

export const Secondary: Story = {
  args: {
    variant: 'secondary',
    children: 'Secondary Button',
  },
};

export const Outline: Story = {
  args: {
    variant: 'outline',
    children: 'Outline Button',
  },
};
```

### Building Storybook for Production

```bash
# Build static Storybook site
npm run build-storybook

# Output: storybook-static/
```

### Storybook Configuration

**File:** `.storybook/main.js`

```javascript
module.exports = {
  stories: ['../src/**/*.stories.@(js|jsx|ts|tsx)'],
  addons: [
    '@storybook/addon-links',
    '@storybook/addon-essentials',
    '@storybook/addon-interactions',
  ],
  framework: {
    name: '@storybook/react-vite',
    options: {},
  },
};
```

---

## Documentation Site

### What is the Documentation Site?

Built with **Fumadocs** (Next.js-based documentation framework) and **Tailwind CSS**.

### Starting Documentation Site

```bash
# Navigate to documentation directory
cd documentation

# Install dependencies
npm install

# Start dev server
npm run dev

# Opens at http://localhost:3000
```

### Documentation Structure

```
documentation/
├── content/
│   └── docs/
│       ├── index.mdx         # Homepage
│       ├── getting-started/
│       │   ├── installation.mdx
│       │   └── quick-start.mdx
│       └── guides/
│           ├── backend.mdx
│           └── frontend.mdx
├── app/
│   ├── layout.tsx            # Root layout
│   ├── page.tsx              # Homepage
│   └── docs/
│       └── [[...slug]]/
│           └── page.tsx      # Dynamic docs pages
├── next.config.js            # Next.js config
└── package.json
```

### Adding Documentation Pages

**File:** `documentation/content/docs/guides/new-guide.mdx`

```mdx
---
title: New Guide
description: Learn how to do something awesome
---

# New Guide

This is a new documentation page.

## Section 1

Content here...

## Code Examples

```typescript
const example = 'Hello World';
console.log(example);
```

## Next Steps

- [Read another guide](/docs/guides/other-guide)
- [Go back home](/docs)
```

### Building Documentation Site

```bash
cd documentation
npm run build

# Output: .next/
# Deploy to Vercel, Netlify, etc.
```

---

## Utility Scripts

### Migration Script

**File:** `scripts/migrate.js`

```javascript
/**
 * Run database migrations
 */
const { execSync } = require('child_process');

console.log('Running migrations...');

try {
  execSync('wp plugin deactivate frs-lrg && wp plugin activate frs-lrg', {
    stdio: 'inherit',
  });
  console.log('✓ Migrations completed');
} catch (error) {
  console.error('✗ Migration failed:', error.message);
  process.exit(1);
}
```

**Usage:**

```bash
node scripts/migrate.js
```

### Debug Script

**File:** `scripts/debug.js`

```javascript
/**
 * Debug plugin state
 */
const { execSync } = require('child_process');

console.log('Plugin Status:');
execSync('wp plugin list | grep frs-lrg', { stdio: 'inherit' });

console.log('\nDatabase Tables:');
execSync('wp db query "SHOW TABLES LIKE \'wp_%\'"', { stdio: 'inherit' });

console.log('\nREST API Routes:');
execSync('wp rest route list | grep lrh', { stdio: 'inherit' });
```

**Usage:**

```bash
node scripts/debug.js
```

### Verify Script

**File:** `scripts/verify.js`

```javascript
/**
 * Verify build output
 */
const fs = require('fs');
const path = require('path');

const requiredFiles = [
  'assets/frontend/dist/main.js',
  'assets/frontend/dist/main.css',
  'assets/admin/dist/main.js',
  'assets/admin/dist/main.css',
];

console.log('Verifying build output...\n');

let allExist = true;
requiredFiles.forEach((file) => {
  const exists = fs.existsSync(path.join(__dirname, '..', file));
  console.log(`${exists ? '✓' : '✗'} ${file}`);
  if (!exists) allExist = false;
});

if (!allExist) {
  console.error('\n✗ Some files are missing. Run: npm run build');
  process.exit(1);
}

console.log('\n✓ All required files exist');
```

**Usage:**

```bash
node scripts/verify.js
```

---

## Shadcn UI

### What is Shadcn UI?

Collection of re-usable React components built with Radix UI and Tailwind CSS. Copy/paste components into your project.

### Installing Components

```bash
# Install a component
npx shadcn-ui@latest add button

# Install multiple components
npx shadcn-ui@latest add button dialog input label
```

### Components Location

Components are added to: `src/frontend/components/ui/`

**Example:** `src/frontend/components/ui/button.tsx`

```tsx
import * as React from 'react';
import { Slot } from '@radix-ui/react-slot';
import { cva, type VariantProps } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const buttonVariants = cva(
  'inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors',
  {
    variants: {
      variant: {
        default: 'bg-primary text-primary-foreground hover:bg-primary/90',
        secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
        outline: 'border border-input bg-background hover:bg-accent',
      },
      size: {
        default: 'h-10 px-4 py-2',
        sm: 'h-9 rounded-md px-3',
        lg: 'h-11 rounded-md px-8',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  }
);

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, asChild = false, ...props }, ref) => {
    const Comp = asChild ? Slot : 'button';
    return (
      <Comp
        className={cn(buttonVariants({ variant, size, className }))}
        ref={ref}
        {...props}
      />
    );
  }
);

Button.displayName = 'Button';

export { Button, buttonVariants };
```

### Using Shadcn Components

```tsx
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export function MyComponent(): JSX.Element {
  return (
    <div>
      <Button variant="primary">Click Me</Button>

      <Dialog>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Edit Profile</DialogTitle>
          </DialogHeader>

          <div className="space-y-4">
            <div>
              <Label htmlFor="name">Name</Label>
              <Input id="name" placeholder="Enter your name" />
            </div>

            <Button type="submit">Save</Button>
          </div>
        </DialogContent>
      </Dialog>
    </div>
  );
}
```

### Available Shadcn Components

- **Form Elements:** Input, Textarea, Select, Checkbox, Radio, Switch
- **Buttons:** Button, Toggle
- **Dialogs:** Dialog, Sheet, AlertDialog
- **Navigation:** Tabs, Accordion, Dropdown Menu
- **Feedback:** Alert, Toast, Progress
- **Data Display:** Table, Card, Badge, Avatar
- **Layout:** Separator, Scroll Area

---

## GitHub Actions CI/CD

### Workflow Files

**File:** `.github/workflows/ci.yml`

```yaml
name: CI

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '20'
          cache: 'npm'

      - name: Install dependencies
        run: npm install

      - name: Run linter
        run: npm run lint

      - name: Run type check
        run: npm run type-check

      - name: Run tests
        run: npm run test

      - name: Build
        run: npm run build

      - name: Verify build output
        run: node scripts/verify.js
```

### Deploy Workflow

**File:** `.github/workflows/deploy.yml`

```yaml
name: Deploy

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '20'

      - name: Install dependencies
        run: npm install

      - name: Build
        run: npm run build

      - name: Deploy to server
        env:
          SSH_PRIVATE_KEY: ${{ secrets.SSH_PRIVATE_KEY }}
          SERVER_HOST: ${{ secrets.SERVER_HOST }}
        run: |
          # Add deployment script here
          echo "Deploying to server..."
```

---

## Grunt Tasks

### Gruntfile Configuration

**File:** `Gruntfile.js`

```javascript
module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    // Compile SCSS to CSS
    sass: {
      dist: {
        files: {
          'assets/css/main.css': 'assets/scss/main.scss'
        }
      }
    },

    // Minify CSS
    cssmin: {
      target: {
        files: {
          'assets/css/main.min.css': 'assets/css/main.css'
        }
      }
    },

    // Watch for changes
    watch: {
      styles: {
        files: ['assets/scss/**/*.scss'],
        tasks: ['sass', 'cssmin']
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.registerTask('default', ['sass', 'cssmin']);
};
```

### Running Grunt Tasks

```bash
# Run default task
grunt

# Run specific task
grunt sass

# Watch for changes
grunt watch
```

---

## Plugin Renaming System

### Renaming the Plugin

The boilerplate includes a script to rename the plugin from the template.

**File:** `scripts/rename-plugin.js`

```javascript
/**
 * Rename plugin from template
 *
 * Usage: node scripts/rename-plugin.js "New Plugin Name"
 */
const fs = require('fs');
const path = require('path');

const oldName = 'WordPress Plugin Boilerplate';
const oldSlug = 'wordpress-plugin-boilerplate';
const oldPrefix = 'wpb';
const oldNamespace = 'WordPressPluginBoilerplate';

const newName = process.argv[2];
const newSlug = newName.toLowerCase().replace(/\s+/g, '-');
const newPrefix = newSlug.split('-').map(w => w[0]).join('').toLowerCase();
const newNamespace = newName.replace(/\s+/g, '');

// Files to update
const filesToUpdate = [
  'lending-resource-hub.php',
  'includes/**/*.php',
  'README.md',
  'package.json',
  'composer.json',
];

console.log(`Renaming plugin to: ${newName}`);
console.log(`Slug: ${newSlug}`);
console.log(`Prefix: ${newPrefix}`);
console.log(`Namespace: ${newNamespace}`);

// Perform replacements...
```

**Usage:**

```bash
node scripts/rename-plugin.js "My Awesome Plugin"
```

---

## Related Documentation

- [01-development-workflow.md](./01-development-workflow.md) - Build and dev workflow
- [02-architecture.md](./02-architecture.md) - System architecture
- [05-frontend-patterns.md](./05-frontend-patterns.md) - React components
