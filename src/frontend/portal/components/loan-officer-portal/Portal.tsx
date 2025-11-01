import { useState, useEffect } from 'react';
import { DashboardLayout } from './DashboardLayout';
import { WelcomeBento } from './WelcomeBento';
import { MyProfile } from './MyProfile';
import { BiolinkMarketing } from './BiolinkMarketing';
import { LandingPagesMarketing } from './LandingPagesMarketing';
import { EmailCampaignsMarketing } from './EmailCampaignsMarketing';
import { LocalSEOMarketing } from './LocalSEOMarketing';
import { LeadTracking } from './LeadTracking';
import { MarketingOrders } from './MarketingOrders';
import { PartnershipsOverview } from './PartnershipsOverview';
import { InvitePartner } from './InvitePartner';
import { CobrandedMarketing } from './CobrandedMarketing';
import { BrandShowcase } from './BrandShowcase';
import { MortgageCalculator } from './MortgageCalculator';
import { PropertyValuation } from './PropertyValuation';
import { FluentBookingCalendar } from './FluentBookingCalendar';
import type { User } from '../../utils/dataService';

interface PortalProps {
  userId: string;
  currentUser: User;
}

export function Portal({ userId, currentUser }: PortalProps) {
  // Get initial view from URL hash or default to 'welcome'
  const getInitialView = () => {
    const hash = window.location.hash.slice(1); // Remove the '#'
    return hash || 'welcome';
  };

  const [activeView, setActiveView] = useState(getInitialView);

  // Determine user role - realtor or loan-officer
  const userRole = currentUser.role === 'realtor' ? 'realtor' : 'loan-officer';

  // Update URL hash when view changes
  const handleViewChange = (view: string) => {
    setActiveView(view);
    window.location.hash = view;
  };

  // Listen for hash changes (browser back/forward)
  useEffect(() => {
    const handleHashChange = () => {
      const hash = window.location.hash.slice(1);
      if (hash) {
        setActiveView(hash);
      }
    };

    window.addEventListener('hashchange', handleHashChange);
    return () => window.removeEventListener('hashchange', handleHashChange);
  }, []);

  const renderContent = () => {
    switch (activeView) {
      case 'welcome':
        return <WelcomeBento userId={userId} onNavigate={handleViewChange} />;
      case 'profile':
        return <MyProfile userId={userId} autoEdit={false} />;
      case 'profile-edit':
        return <MyProfile userId={userId} autoEdit={true} />;
      case 'leads':
        return <LeadTracking userId={userId} />;
      case 'marketing':
      case 'marketing-biolink':
        return <BiolinkMarketing userId={userId} currentUser={currentUser} />;
      case 'marketing-calendar':
        return <FluentBookingCalendar userId={userId} />;
      case 'marketing-landing-pages':
        return <LandingPagesMarketing userId={userId} currentUser={currentUser} />;
      case 'marketing-email-campaigns':
        return <EmailCampaignsMarketing userId={userId} currentUser={currentUser} />;
      case 'marketing-local-seo':
        return <LocalSEOMarketing userId={userId} currentUser={currentUser} />;
      case 'marketing-brand-guide':
        return <BrandShowcase />;
      case 'digital-marketing':
      case 'marketing-orders':
        return <MarketingOrders userId={userId} currentUser={currentUser} />;
      case 'partnerships':
      case 'partnerships-overview':
        return <PartnershipsOverview userId={userId} currentUser={currentUser} />;
      case 'partnerships-invites':
        return <InvitePartner userId={userId} />;
      case 'cobranded-marketing':
        return <CobrandedMarketing userRole={userRole} userId={userId} />;
      case 'tools':
      case 'tools-mortgage-calculator':
        return <MortgageCalculator />;
      case 'tools-property-valuation':
        return <PropertyValuation />;
      default:
        return <WelcomeBento userId={userId} onNavigate={handleViewChange} />;
    }
  };

  return (
    <DashboardLayout activeView={activeView} onViewChange={handleViewChange} currentUser={currentUser}>
      {renderContent()}
    </DashboardLayout>
  );
}
