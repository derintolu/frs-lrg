import { createHashRouter } from 'react-router-dom';
import { DashboardLayout } from './components/loan-officer-portal/DashboardLayout';
import { WelcomeBento } from './components/loan-officer-portal/WelcomeBento';
import { MyProfile } from './components/loan-officer-portal/MyProfile';
import { BiolinkMarketing } from './components/loan-officer-portal/BiolinkMarketing';
import { LandingPagesMarketing } from './components/loan-officer-portal/LandingPagesMarketing';
import { EmailCampaignsMarketing } from './components/loan-officer-portal/EmailCampaignsMarketing';
import { LocalSEOMarketing } from './components/loan-officer-portal/LocalSEOMarketing';
import { LeadTracking } from './components/loan-officer-portal/LeadTracking';
import { MarketingOrders } from './components/loan-officer-portal/MarketingOrders';
import { PartnershipsOverview } from './components/loan-officer-portal/PartnershipsOverview';
import { InvitePartner } from './components/loan-officer-portal/InvitePartner';
import { CobrandedMarketing } from './components/loan-officer-portal/CobrandedMarketing';
import { BrandShowcase } from './components/loan-officer-portal/BrandShowcase';
import { MortgageCalculator } from './components/loan-officer-portal/MortgageCalculator';
import { PropertyValuation } from './components/loan-officer-portal/PropertyValuation';
import { FluentBookingCalendar } from './components/loan-officer-portal/FluentBookingCalendar';
import { Settings } from './components/loan-officer-portal/Settings';
import type { User } from './utils/dataService';

interface RouteConfig {
  currentUser: User;
  userId: string;
  userRole: 'loan-officer' | 'realtor';
}

export const createRouter = (config: RouteConfig) => {
  const { currentUser, userId, userRole } = config;

  // Check if we're in WordPress Customizer - skip URL manipulation if so
  const wpData = (window as any).frsPortalConfig;
  const isCustomizer = wpData?.isCustomizer || false;

  // Ensure the current URL has a trailing slash before the hash
  // Skip this in the WordPress Customizer to prevent breaking the customizer
  if (!isCustomizer && window.location.pathname && !window.location.pathname.endsWith('/')) {
    const newUrl = window.location.pathname + '/' + window.location.hash;
    window.history.replaceState(null, '', newUrl);
  }

  return createHashRouter([
    {
      path: '/',
      element: <DashboardLayout currentUser={currentUser} />,
      errorElement: <DashboardLayout currentUser={currentUser}><WelcomeBento userId={userId} /></DashboardLayout>,
      children: [
        {
          path: '/',
          element: <WelcomeBento userId={userId} />,
        },
        {
          path: 'profile',
          element: <MyProfile userId={userId} autoEdit={false} />,
        },
        {
          path: 'profile/edit',
          element: <MyProfile userId={userId} autoEdit={true} />,
        },
        {
          path: 'profile/settings',
          element: <Settings userId={userId} />,
        },
        {
          path: 'leads',
          element: <LeadTracking userId={userId} />,
        },
        {
          path: 'marketing',
          children: [
            {
              path: '',
              element: <BiolinkMarketing userId={userId} currentUser={currentUser} />,
            },
            {
              path: 'biolink',
              element: <BiolinkMarketing userId={userId} currentUser={currentUser} />,
            },
            {
              path: 'calendar',
              element: <FluentBookingCalendar userId={userId} />,
            },
            {
              path: 'landing-pages',
              element: <LandingPagesMarketing userId={userId} currentUser={currentUser} />,
            },
            {
              path: 'email-campaigns',
              element: <EmailCampaignsMarketing userId={userId} currentUser={currentUser} />,
            },
            {
              path: 'local-seo',
              element: <LocalSEOMarketing userId={userId} currentUser={currentUser} />,
            },
            {
              path: 'brand-guide',
              element: <BrandShowcase />,
            },
            {
              path: 'orders',
              element: <MarketingOrders userId={userId} currentUser={currentUser} />,
            },
          ],
        },
        {
          path: 'partnerships',
          children: [
            {
              path: '',
              element: <PartnershipsOverview userId={userId} currentUser={currentUser} />,
            },
            {
              path: 'overview',
              element: <PartnershipsOverview userId={userId} currentUser={currentUser} />,
            },
            {
              path: 'invites',
              element: <InvitePartner userId={userId} />,
            },
            {
              path: 'cobranded-marketing',
              element: <CobrandedMarketing userRole={userRole} userId={userId} />,
            },
          ],
        },
        {
          path: 'tools',
          children: [
            {
              path: '',
              element: <MortgageCalculator />,
            },
            {
              path: 'mortgage-calculator',
              element: <MortgageCalculator />,
            },
            {
              path: 'property-valuation',
              element: <PropertyValuation />,
            },
          ],
        },
        // Sidebar control hash routes - these prevent 404 errors when opening mobile menu
        {
          path: 'open-menu',
          element: <WelcomeBento userId={userId} />,
        },
        {
          path: 'close-menu',
          element: <WelcomeBento userId={userId} />,
        },
        {
          path: 'open-sidebar',
          element: <WelcomeBento userId={userId} />,
        },
        {
          path: 'close-sidebar',
          element: <WelcomeBento userId={userId} />,
        },
        // Catch-all route for any unmatched paths
        {
          path: '*',
          element: <WelcomeBento userId={userId} />,
        },
      ],
    },
  ]);
};
