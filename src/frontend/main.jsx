import { createRoot } from "react-dom/client";
import "./index.css";
import LoanOfficerPortal from "./portal/LoanOfficerPortal";
import { PortalSidebarLayout } from "./portal/components/PortalSidebarLayout";
import { ProfileEditProvider } from "./portal/contexts/ProfileEditContext";
import { MyProfile } from './portal/components/loan-officer-portal/MyProfile';
import { MarketingOverview } from './portal/components/loan-officer-portal/MarketingOverview';
import { LeadTracking } from './portal/components/loan-officer-portal/LeadTracking';
import { FluentBookingCalendar } from './portal/components/loan-officer-portal/FluentBookingCalendar';
import { LandingPagesMarketing } from './portal/components/loan-officer-portal/LandingPagesMarketing';
import { EmailCampaignsMarketing } from './portal/components/loan-officer-portal/EmailCampaignsMarketing';
import { LocalSEOMarketing } from './portal/components/loan-officer-portal/LocalSEOMarketing';
import { BrandShowcase } from './portal/components/loan-officer-portal/BrandShowcase';
import { MarketingOrders } from './portal/components/loan-officer-portal/MarketingOrders';
import { MortgageCalculator } from './portal/components/loan-officer-portal/MortgageCalculator';
import { PropertyValuation } from './portal/components/loan-officer-portal/PropertyValuation';
import { Settings } from './portal/components/loan-officer-portal/Settings';
import { MarketingSubnav } from './portal/components/loan-officer-portal/MarketingSubnav';
import { DataService } from './portal/utils/dataService';
import { BrowserRouter } from 'react-router-dom';

// Realtor Portal Components
import { RealtorOverview } from './portal/components/realtor-portal/RealtorOverview';
import { MarketingTools } from './portal/components/realtor-portal/MarketingTools';
import { CalculatorTools } from './portal/components/realtor-portal/CalculatorTools';
import { CompanyOverview } from './portal/components/realtor-portal/CompanyOverview';
import { Resources } from './portal/components/realtor-portal/Resources';
import { Profile } from './portal/components/realtor-portal/Profile';

/**
 * Consolidated Frontend Entry Point
 *
 * This single entry point handles mounting different React apps based on
 * which root element is present on the page. This matches the WordPress
 * Plugin Boilerplate pattern where one entry point serves all frontend apps.
 *
 * CRITICAL: Configuration is passed via wp_localize_script which creates
 * window.frsPortalConfig. This is MORE RELIABLE than wp_add_inline_script
 * for ES6 modules because wp_localize_script ensures proper execution order.
 */

// Get configuration from wp_localize_script (created by Frontend.php)
// Uses frsPortalConfig (legacy name) for compatibility
const config = window.frsPortalConfig || {
  userId: 0,
  userName: '',
  userEmail: '',
  userAvatar: '',
  userRole: 'loan_officer',
  restNonce: '',
  apiUrl: '/wp-json/lrh/v1/',
  gradientUrl: '',
  currentUser: {
    id: 0,
    name: '',
    email: '',
    avatar: '',
    roles: []
  }
};

// Debug logging
console.log('[LRH] Frontend entry point loaded');
console.log('[LRH] Config available:', !!window.frsPortalConfig);
console.log('[LRH] Config data:', config);

// Check for Loan Officer Portal root (new or legacy for backward compatibility)
const portalRoot =
  document.getElementById("lrh-portal-root") ||
  document.getElementById("frs-partnership-portal-root");

if (portalRoot) {
  console.log('[LRH] Mounting Loan Officer Portal to:', portalRoot.id);

  try {
    createRoot(portalRoot).render(
      <LoanOfficerPortal {...config} />
    );
    console.log('[LRH] Loan Officer Portal mounted successfully');
  } catch (error) {
    console.error('[LRH] Failed to mount Loan Officer Portal:', error);
  }
}

// Check for Portal Sidebar root
const sidebarRoot = document.getElementById("lrh-portal-sidebar-root");

if (sidebarRoot) {
  console.log('[LRH] Mounting Portal Sidebar (Navy Layout)');
  console.log('[LRH] Config data:', config);

  // Build currentUser object for PortalSidebarLayout
  const currentUser = {
    id: String(config.userId || ''),
    name: config.userName || '',
    email: config.userEmail || '',
    avatar: config.userAvatar || '',
    profile_slug: config.profileSlug || '',
    job_title: config.userJobTitle || '',
  };

  try {
    createRoot(sidebarRoot).render(
      <ProfileEditProvider>
        <PortalSidebarLayout
          currentUser={currentUser}
          isOwnProfile={true}
          sidebarOnly={true}
        />
      </ProfileEditProvider>
    );
    console.log('[LRH] Portal Sidebar mounted successfully');
  } catch (error) {
    console.error('[LRH] Failed to mount Portal Sidebar:', error);
  }
}

// Mount content-only pages (uses [lrh_content_*] shortcodes)
// Function to mount components
const mountComponents = async () => {
  const contentRoots = document.querySelectorAll('[data-lrh-content]');

  if (contentRoots.length === 0) return;

  console.log('[LRH] Found content-only roots:', contentRoots.length);

  // Load current user data
  let currentUser;
  try {
    currentUser = await DataService.getCurrentUser();
  } catch (err) {
    console.error('[LRH] Failed to load user for content pages:', err);
    return;
  }

  const userId = currentUser.id;

  contentRoots.forEach((root) => {
    const contentType = root.getAttribute('data-lrh-content');
    let component = null;

    switch (contentType) {
      case 'profile':
        component = <MyProfile userId={userId} autoEdit={false} />;
        break;
      case 'marketing':
        component = <MarketingOverview userId={userId} />;
        break;
      case 'calendar':
        component = <FluentBookingCalendar userId={userId} />;
        break;
      case 'landing-pages':
        component = <LandingPagesMarketing userId={userId} currentUser={currentUser} />;
        break;
      case 'email-campaigns':
        component = <EmailCampaignsMarketing userId={userId} currentUser={currentUser} />;
        break;
      case 'local-seo':
        component = <LocalSEOMarketing userId={userId} currentUser={currentUser} />;
        break;
      case 'brand-guide':
        component = <BrandShowcase />;
        break;
      case 'orders':
        component = <MarketingOrders userId={userId} currentUser={currentUser} />;
        break;
      case 'lead-tracking':
        component = <LeadTracking userId={userId} />;
        break;
      case 'tools':
        component = <MortgageCalculator />;
        break;
      case 'settings':
        component = <Settings userId={userId} />;
        break;
      default:
        console.warn(`[LRH] Unknown content type: ${contentType}`);
        return;
    }

    if (component) {
      console.log(`[LRH] Mounting content-only page: ${contentType}`);
      createRoot(root).render(
        <BrowserRouter>
          {component}
        </BrowserRouter>
      );
    }
  });

  // Mount subnav panels
  const subnavRoots = document.querySelectorAll('[data-lrh-subnav]');
  subnavRoots.forEach((root) => {
    const subnavType = root.getAttribute('data-lrh-subnav');
    let component = null;

    switch (subnavType) {
      case 'marketing':
        component = <MarketingSubnav />;
        break;
      default:
        console.warn(`[LRH] Unknown subnav type: ${subnavType}`);
        return;
    }

    if (component) {
      console.log(`[LRH] Mounting subnav: ${subnavType}`);
      createRoot(root).render(component);
    }
  });

  // Mount generic components (uses [lrh_component] shortcode)
  // Component Registry - maps component names to actual imports
  const componentRegistry = {
    // Loan Officer Portal Components
    'MyProfile': MyProfile,
    'MarketingOverview': MarketingOverview,
    'LeadTracking': LeadTracking,
    'FluentBookingCalendar': FluentBookingCalendar,
    'LandingPagesMarketing': LandingPagesMarketing,
    'EmailCampaignsMarketing': EmailCampaignsMarketing,
    'LocalSEOMarketing': LocalSEOMarketing,
    'BrandShowcase': BrandShowcase,
    'MarketingOrders': MarketingOrders,
    'MortgageCalculator': MortgageCalculator,
    'PropertyValuation': PropertyValuation,
    'Settings': Settings,
    'MarketingSubnav': MarketingSubnav,

    // Realtor Partner Components
    'RealtorOverview': RealtorOverview,
    'MarketingTools': MarketingTools,
    'CalculatorTools': CalculatorTools,
    'CompanyOverview': CompanyOverview,
    'Resources': Resources,
    'Profile': Profile,
  };

  const componentRoots = document.querySelectorAll('[data-lrh-component]');

  if (componentRoots.length > 0) {
    console.log(`[LRH] Found ${componentRoots.length} generic component(s)`);

    componentRoots.forEach((root) => {
      const componentName = root.getAttribute('data-lrh-component');
      const propsJson = root.getAttribute('data-lrh-props');

      // Parse props
      let props = {};
      try {
        props = JSON.parse(propsJson || '{}');
      } catch (err) {
        console.error(`[LRH] Failed to parse props for ${componentName}:`, err);
        return;
      }

      // Get component from registry
      const Component = componentRegistry[componentName];

      if (!Component) {
        console.error(`[LRH] Component not found in registry: ${componentName}`);
        root.innerHTML = `<div style="padding: 1rem; background: #fee; border: 1px solid #fcc; border-radius: 4px;">Component "${componentName}" not found in registry.</div>`;
        return;
      }

      // Auto-inject common props if not provided
      if (!props.userId && currentUser?.id) {
        props.userId = currentUser.id;
      }
      if (!props.currentUser && currentUser) {
        props.currentUser = currentUser;
      }

      console.log(`[LRH] Mounting component: ${componentName}`, props);

      try {
        createRoot(root).render(
          <BrowserRouter>
            <Component {...props} />
          </BrowserRouter>
        );
        console.log(`[LRH] Successfully mounted: ${componentName}`);
      } catch (error) {
        console.error(`[LRH] Failed to mount ${componentName}:`, error);
        root.innerHTML = `<div style="padding: 1rem; background: #fee; border: 1px solid #fcc; border-radius: 4px;">Error rendering component "${componentName}": ${error.message}</div>`;
      }
    });
  }
};

// Run mounting immediately if DOM is ready, otherwise wait for DOMContentLoaded
console.log('[LRH] Document readyState:', document.readyState);
if (document.readyState === 'loading') {
  console.log('[LRH] Waiting for DOMContentLoaded...');
  document.addEventListener('DOMContentLoaded', mountComponents);
} else {
  console.log('[LRH] DOM already loaded, mounting immediately...');
  mountComponents();
}

// Log if no root elements found
if (!portalRoot && !sidebarRoot) {
  console.log('[LRH] No portal root elements found on this page');
}
