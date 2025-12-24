# Onboarding & Welcome Tour Implementation Guide

## Overview

We've built a complete onboarding and tour system in **frs-lrg** that can be imported and used across all FRS plugins.

## What Was Built

### 1. Core Tour System

**File:** `src/frontend/portal/components/OnboardingTour.tsx`

A unified tour component that combines features from ProfileTour and CalendarTour:
- Element highlighting with smart positioning
- Centered modal variant for general steps
- Action buttons for guided tasks
- Fully configurable via props
- Two variants: `default` (element-focused) and `centered` (modal)

```tsx
<OnboardingTour
  config={welcomeTourConfig}
  isOpen={isTourOpen}
  onClose={() => setIsTourOpen(false)}
  onComplete={() => {
    // Mark tour as completed
    tourState.markCompleted();
  }}
/>
```

### 2. Tour Configurations

**File:** `src/frontend/portal/components/tour-configs.ts`

Pre-built tour configs ready to use:

1. **profileTourConfig** - 4-step profile management tour
2. **calendarTourConfig** - 6-step calendar setup tour with action buttons
3. **biolinkTourConfig** - 4-step biolink creation tour
4. **partnershipTourConfig** - 4-step partnership management tour
5. **welcomeTourConfig** - 6-step first-time welcome tour

Each config includes:
- Unique ID for state tracking
- Step-by-step instructions
- Target element selectors (data-tour attributes)
- Optional icons (Lucide icons)
- Optional action buttons

### 3. State Management

**File:** `src/frontend/portal/hooks/useTourState.ts`

Three hooks for managing tour state:

#### `useTourState(tourId, userId)`
```tsx
const tourState = useTourState('profile-welcome', userId);

// Check state
tourState.completed    // boolean
tourState.skipped      // boolean
tourState.shouldShow   // !completed && !skipped

// Update state
tourState.markCompleted();
tourState.markSkipped();
tourState.reset();
```

#### `useFirstTimeTour(tourId, userId, delay)`
Auto-shows tour on first visit with delay:
```tsx
const tourState = useFirstTimeTour('first-time-welcome', userId, 2000);

if (tourState.shouldAutoShow && !isTourOpen) {
  setIsTourOpen(true);
  tourState.dismissAutoShow();
}
```

#### `useActiveTour()`
Tracks which tour is currently active (prevents multiple tours running):
```tsx
const { activeTourId, setActiveTour, clearActiveTour } = useActiveTour();
```

**State Persistence:**
- localStorage (immediate, client-side)
- WordPress user meta (via REST API `/wp-json/frs-lrg/v1/tour-state`)

### 4. Pre-Built Onboarding Components

#### WelcomeOnboarding
**File:** `src/frontend/portal/components/WelcomeOnboarding.tsx`

Full onboarding checklist with:
- Progress bar
- 4 tasks (profile, biolink, partnerships, calendar)
- Auto-triggers welcome tour on first visit
- Completion tracking

```tsx
<WelcomeOnboarding
  userId={currentUser.id}
  userRole="loan-officer"
  profileComplete={true}
  biolinkCreated={false}
  hasPartnerships={false}
  calendarSetup={false}
/>
```

#### ProfileOnboarding
**File:** `src/frontend/portal/components/ProfileOnboarding.tsx`

Inline help banner for profile management:
```tsx
<ProfileOnboarding userId={currentUser.id} />
```

#### CalendarOnboarding
**File:** `src/frontend/portal/components/CalendarOnboarding.tsx`

Calendar setup checklist:
```tsx
<CalendarOnboarding
  userId={currentUser.id}
  hasIntegration={false}
  hasVideoConferencing={false}
  hasAvailability={true}
  hasEventTypes={false}
/>
```

### 5. Central Export

**File:** `src/frontend/portal/components/onboarding/index.ts`

Single import point for all onboarding features:

```tsx
// In frs-wp-users, frs-partnership-portal, or other plugins:
import {
  OnboardingTour,
  TourTrigger,
  WelcomeOnboarding,
  ProfileOnboarding,
  CalendarOnboarding,
  useTourState,
  useFirstTimeTour,
  profileTourConfig,
  calendarTourConfig,
  welcomeTourConfig,
} from 'frs-lrg/src/frontend/portal/components/onboarding';
```

## Implementation Steps

### Step 1: Add data-tour Attributes

Add `data-tour` attributes to UI elements you want to highlight:

```tsx
<div data-tour="profile-summary">
  <h2>Your Profile</h2>
  <p>Contact details and bio</p>
</div>

<div data-tour="announcements">
  <h3>Company Updates</h3>
</div>

<button data-tour="biolink-tab">Biolink</button>
```

### Step 2: Import & Use Tours

```tsx
import { useState } from 'react';
import {
  OnboardingTour,
  TourTrigger,
  profileTourConfig,
  useTourState
} from 'frs-lrg/src/frontend/portal/components/onboarding';

export function MyComponent({ userId }) {
  const [isTourOpen, setIsTourOpen] = useState(false);
  const tourState = useTourState('profile-welcome', userId);

  return (
    <>
      {/* Tour trigger button */}
      <TourTrigger onStartTour={() => setIsTourOpen(true)} />

      {/* Tour overlay */}
      <OnboardingTour
        config={profileTourConfig}
        isOpen={isTourOpen}
        onClose={() => setIsTourOpen(false)}
        onComplete={() => tourState.markCompleted()}
      />
    </>
  );
}
```

### Step 3: Auto-Show on First Visit

```tsx
import { useFirstTimeTour } from 'frs-lrg/src/frontend/portal/components/onboarding';

export function WelcomePage({ userId }) {
  const [isTourOpen, setIsTourOpen] = useState(false);
  const tourState = useFirstTimeTour('first-time-welcome', userId, 2000);

  // Auto-show tour
  if (tourState.shouldAutoShow && !isTourOpen) {
    setIsTourOpen(true);
    tourState.dismissAutoShow();
  }

  // ... rest of component
}
```

### Step 4: Create Custom Tours

```tsx
import { TourConfig } from 'frs-lrg/src/frontend/portal/components/onboarding';
import { User, Settings } from 'lucide-react';

const myCustomTour: TourConfig = {
  id: 'my-custom-tour',
  variant: 'default', // or 'centered'
  steps: [
    {
      id: 'step-1',
      title: 'Welcome',
      description: 'This highlights a specific element',
      target: '[data-tour="my-element"]',
      position: 'right',
    },
    {
      id: 'step-2',
      title: 'Settings',
      description: 'Configure your preferences',
      target: 'body',
      position: 'center',
      icon: Settings,
      action: {
        label: 'Open Settings',
        onClick: () => navigate('/settings')
      }
    }
  ]
};
```

## REST API

### Save Tour State

**Endpoint:** `POST /wp-json/frs-lrg/v1/tour-state`

**Request:**
```json
{
  "user_id": 123,
  "tour_id": "profile-welcome",
  "state": {
    "tourId": "profile-welcome",
    "completed": true,
    "completedAt": "2025-01-15T10:30:00.000Z",
    "skipped": false
  }
}
```

**Note:** This endpoint needs to be implemented in frs-lrg PHP backend.

## Tour Variants

### Default Variant (Element-Focused)

Best for: Highlighting specific UI elements

```tsx
{
  variant: 'default',
  steps: [
    {
      target: '[data-tour="profile-summary"]',
      position: 'right', // tooltip appears to the right
    }
  ]
}
```

Features:
- Four-sided dark overlay around highlighted element
- Tooltip positions smartly (right, left, above, below)
- Auto-scrolls element into view

### Centered Variant (Modal)

Best for: General information, setup wizards

```tsx
{
  variant: 'centered',
  steps: [
    {
      target: 'body',
      position: 'center',
      icon: Calendar, // Lucide icon
    }
  ]
}
```

Features:
- Full dark overlay
- Centered modal
- Large icons
- Action buttons

## Migration from Old Tours

Replace old ProfileTour and CalendarTour:

```tsx
// OLD
import { ProfileTour } from './ProfileTour';
import { CalendarTour } from './loan-officer-portal/CalendarTour';

// NEW
import {
  OnboardingTour,
  profileTourConfig,
  calendarTourConfig
} from 'frs-lrg/src/frontend/portal/components/onboarding';

// Usage
<OnboardingTour config={profileTourConfig} isOpen={isOpen} onClose={onClose} />
<OnboardingTour config={calendarTourConfig} isOpen={isOpen} onClose={onClose} />
```

## File Structure

```
frs-lrg/src/frontend/portal/
├── components/
│   ├── OnboardingTour.tsx          # Core tour component
│   ├── WelcomeOnboarding.tsx       # Welcome checklist
│   ├── ProfileOnboarding.tsx       # Profile help banner
│   ├── CalendarOnboarding.tsx      # Calendar setup
│   ├── tour-configs.ts             # Pre-built tours
│   └── onboarding/
│       └── index.ts                # Central export
└── hooks/
    └── useTourState.ts             # State management
```

## Next Steps

1. **Build frs-lrg**: `npm run build` (currently in progress)
2. **Add REST API endpoint** in frs-lrg PHP for tour state persistence
3. **Replace old tours** in frs-partnership-portal and frs-wp-users
4. **Add data-tour attributes** to existing components
5. **Test tours** in each plugin context

## Tips

- Keep tour steps concise (1-2 sentences max)
- Use action buttons to guide users to external pages
- Test tours on mobile (responsive positioning)
- Don't auto-show tours on every page load (use first-time logic)
- Provide manual "Take Tour" buttons for users who skip

## Support

For questions or issues:
- Check README-ONBOARDING.md for detailed examples
- Review existing tour configs in tour-configs.ts
- Check component source code for props/types
