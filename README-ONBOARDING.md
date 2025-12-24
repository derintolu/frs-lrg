# FRS Onboarding System

Unified onboarding and tour system built in **frs-lrg** and shared across all FRS plugins.

## Features

- **Configurable Tours** - JSON-based tour step definitions
- **Element Highlighting** - Smart positioning around UI elements
- **State Management** - localStorage + user meta persistence
- **Auto-Trigger** - First-login detection
- **Manual Triggers** - "Take Tour" buttons
- **Multiple Variants** - Element-focused or centered modals
- **Pre-Built Components** - Welcome, Profile, Calendar, Biolink, Partnership tours

## Usage in Other Plugins

### Import Components

```tsx
// In frs-wp-users or frs-partnership-portal
import {
  OnboardingTour,
  TourTrigger,
  WelcomeOnboarding,
  useTourState,
  profileTourConfig
} from 'frs-lrg/src/frontend/portal/components/onboarding';
```

### Basic Tour

```tsx
import { useState } from 'react';
import { OnboardingTour, TourTrigger, profileTourConfig } from 'frs-lrg/src/frontend/portal/components/onboarding';

export function MyComponent() {
  const [isTourOpen, setIsTourOpen] = useState(false);

  return (
    <>
      <TourTrigger onStartTour={() => setIsTourOpen(true)} />

      <OnboardingTour
        config={profileTourConfig}
        isOpen={isTourOpen}
        onClose={() => setIsTourOpen(false)}
        onComplete={() => {
          console.log('Tour completed!');
          setIsTourOpen(false);
        }}
      />
    </>
  );
}
```

### Auto-Show on First Visit

```tsx
import { useFirstTimeTour } from 'frs-lrg/src/frontend/portal/components/onboarding';

export function MyComponent({ userId }) {
  const [isTourOpen, setIsTourOpen] = useState(false);
  const tourState = useFirstTimeTour('my-tour-id', userId, 2000); // 2 second delay

  // Auto-show tour on first visit
  if (tourState.shouldAutoShow && !isTourOpen) {
    setIsTourOpen(true);
    tourState.dismissAutoShow();
  }

  // ... rest of component
}
```

### Custom Tour Configuration

```tsx
import { OnboardingTour, type TourConfig } from 'frs-lrg/src/frontend/portal/components/onboarding';
import { User, Mail } from 'lucide-react';

const myCustomTour: TourConfig = {
  id: 'my-custom-tour',
  variant: 'default', // or 'centered'
  steps: [
    {
      id: 'step-1',
      title: 'Welcome',
      description: 'This highlights a specific element',
      target: '[data-tour="my-element"]', // CSS selector
      position: 'right',
    },
    {
      id: 'step-2',
      title: 'Centered Modal',
      description: 'This shows a centered modal',
      target: 'body',
      position: 'center',
      icon: User,
      action: {
        label: 'Open Settings',
        onClick: () => window.location.hash = '#/settings'
      }
    }
  ]
};
```

## Pre-Built Onboarding Components

### WelcomeOnboarding

Full onboarding checklist with auto-tour on first visit.

```tsx
import { WelcomeOnboarding } from 'frs-lrg/src/frontend/portal/components/onboarding';

<WelcomeOnboarding
  userId={currentUser.id}
  userRole="loan-officer"
  profileComplete={true}
  biolinkCreated={false}
  hasPartnerships={false}
  calendarSetup={false}
/>
```

### ProfileOnboarding

Inline help banner for profile management.

```tsx
import { ProfileOnboarding } from 'frs-lrg/src/frontend/portal/components/onboarding';

<ProfileOnboarding userId={currentUser.id} />
```

### CalendarOnboarding

Calendar setup checklist with auto-tour.

```tsx
import { CalendarOnboarding } from 'frs-lrg/src/frontend/portal/components/onboarding';

<CalendarOnboarding
  userId={currentUser.id}
  hasIntegration={false}
  hasVideoConferencing={false}
  hasAvailability={true}
  hasEventTypes={false}
/>
```

## Tour Configurations

Pre-built tour configs available:

- `profileTourConfig` - Profile management tour
- `calendarTourConfig` - Calendar setup tour (centered variant)
- `biolinkTourConfig` - Biolink creation tour
- `partnershipTourConfig` - Partnership management tour
- `welcomeTourConfig` - First-time welcome tour

## State Management

Tours automatically persist state to localStorage and optionally to WordPress user meta.

```tsx
import { useTourState } from 'frs-lrg/src/frontend/portal/components/onboarding';

const tourState = useTourState('tour-id', userId);

// Check state
console.log(tourState.completed); // boolean
console.log(tourState.skipped); // boolean
console.log(tourState.shouldShow); // !completed && !skipped

// Update state
tourState.markCompleted();
tourState.markSkipped();
tourState.reset();
```

## Adding data-tour Attributes

For element highlighting to work, add `data-tour` attributes to your UI elements:

```tsx
<div data-tour="profile-summary">
  <h2>Your Profile</h2>
  <p>Contact details and bio</p>
</div>

<div data-tour="announcements">
  <h3>Company Updates</h3>
  {/* ... */}
</div>

<button data-tour="biolink-tab">
  Biolink
</button>
```

## File Structure

```
frs-lrg/src/frontend/portal/
├── components/
│   ├── OnboardingTour.tsx          # Core tour component
│   ├── WelcomeOnboarding.tsx       # Welcome checklist + tour
│   ├── ProfileOnboarding.tsx       # Profile help banner
│   ├── CalendarOnboarding.tsx      # Calendar setup checklist
│   ├── tour-configs.ts             # Pre-built tour definitions
│   └── onboarding/
│       └── index.ts                # Central export
└── hooks/
    └── useTourState.ts             # State management hooks
```

## REST API Endpoint

Tour state is persisted to WordPress user meta via:

```
POST /wp-json/frs-lrg/v1/tour-state
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

## Migration from Old Tours

Replace old ProfileTour and CalendarTour imports:

```tsx
// OLD - in frs-partnership-portal and frs-lrg
import { ProfileTour, TourTrigger } from './ProfileTour';
import { CalendarTour } from './loan-officer-portal/CalendarTour';

// NEW - import from frs-lrg onboarding system
import {
  OnboardingTour,
  TourTrigger,
  profileTourConfig,
  calendarTourConfig
} from 'frs-lrg/src/frontend/portal/components/onboarding';

// Usage
<OnboardingTour
  config={profileTourConfig}
  isOpen={isTourOpen}
  onClose={() => setIsTourOpen(false)}
/>
```

## License

Part of the FRS plugin suite.
