# Component Shortcode System

A flexible system for embedding any React component from the frs-lrg plugin into SureDash pages or any WordPress content.

## Usage

### Basic Component

```
[lrh_component name="MyProfile"]
```

### Component with Props

```
[lrh_component name="LeadTracking" props='{"showFilters":true,"limit":10}']
```

### Component with Styling

```
[lrh_component name="MarketingOverview" wrapper_class="p-4 bg-white rounded-lg shadow"]
```

### Component with Custom Styles

```
[lrh_component name="BrandShowcase" wrapper_style="max-width: 1200px; margin: 0 auto;"]
```

## Available Components

The following components are currently registered and ready to use:

### Loan Officer Portal Components
- `MyProfile` - User profile editor
- `MarketingOverview` - Marketing dashboard
- `LeadTracking` - Lead tracking table
- `FluentBookingCalendar` - Booking calendar integration
- `LandingPagesMarketing` - Co-branded landing page generator
- `EmailCampaignsMarketing` - Email campaign management
- `LocalSEOMarketing` - Local SEO tools
- `BrandShowcase` - Brand assets showcase
- `MarketingOrders` - Marketing material orders
- `MortgageCalculator` - Mortgage payment calculator
- `PropertyValuation` - Property valuation tool
- `Settings` - User settings panel
- `MarketingSubnav` - Marketing navigation panel

## Auto-Injected Props

The system automatically injects these props if not provided:
- `userId` - Current user's ID
- `currentUser` - Full current user object

## Adding New Components to Registry

To make a new component available via shortcode:

1. Import the component in `src/frontend/main.jsx`:
   ```javascript
   import { YourComponent } from './portal/components/YourComponent';
   ```

2. Add it to the `componentRegistry` object:
   ```javascript
   const componentRegistry = {
     // ... existing components
     'YourComponent': YourComponent,
   };
   ```

3. Use it in any WordPress content:
   ```
   [lrh_component name="YourComponent"]
   ```

## Example: Adding Realtor Portal Components

To add realtor portal components:

```javascript
// In src/frontend/main.jsx

// Add imports
import { RealtorOverview } from './portal/components/realtor-portal/RealtorOverview';
import { RealtorDashboard } from './portal/components/realtor-portal/RealtorDashboard';
import { MarketingTools } from './portal/components/realtor-portal/MarketingTools';

// Add to componentRegistry
const componentRegistry = {
  // Loan Officer Portal Components
  'MyProfile': MyProfile,
  // ... other LO components

  // Realtor Portal Components
  'RealtorOverview': RealtorOverview,
  'RealtorDashboard': RealtorDashboard,
  'MarketingTools': MarketingTools,
};
```

Then use in SureDash:
```
[lrh_component name="RealtorOverview"]
[lrh_component name="MarketingTools" props='{"category":"digital"}']
```

## Props Format

Props must be valid JSON. Common patterns:

### String props
```
props='{"title":"My Title","description":"Some text"}'
```

### Boolean props
```
props='{"showHeader":true,"isEditable":false}'
```

### Number props
```
props='{"limit":10,"offset":0}'
```

### Array props
```
props='{"items":["item1","item2","item3"]}'
```

### Object props
```
props='{"config":{"theme":"dark","size":"large"}}'
```

## Error Handling

If a component name is not found in the registry, an error message will be displayed:
```
Component "ComponentName" not found in registry.
```

If there's an error rendering the component, a detailed error message will be shown in the console and on the page.

## Tips

1. **Check the browser console** for detailed mounting logs and any errors
2. **Use wrapper_class** for Tailwind CSS utility classes
3. **Common props are auto-injected** - you don't need to manually pass userId or currentUser
4. **JSON must be valid** - use single quotes around the JSON string, double quotes inside
5. **Test in dev mode** - Run `npm run dev` to see components update in real-time

## Development Workflow

1. Create your React component in `src/frontend/portal/components/`
2. Import it in `main.jsx`
3. Add it to the `componentRegistry`
4. Use `npm run dev` to test with hot reload
5. When ready, run `npm run build` for production
6. Paste the shortcode into any SureDash page or WordPress content

## Debugging

Check the browser console for these log messages:
- `[LRH] Frontend entry point loaded` - Entry point initialized
- `[LRH] Found X generic component(s)` - Components detected
- `[LRH] Mounting component: ComponentName` - Component mounting
- `[LRH] Successfully mounted: ComponentName` - Component rendered

If you see errors, they'll show the component name and specific error message.
