import { useState, useEffect } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import {
  Home,
  Users,
  Briefcase,
  FileText,
  Calculator,
  TrendingUp,
  Wrench,
  Copy,
  ExternalLink,
  Sparkles,
  GraduationCap
} from 'lucide-react';
import type { User as UserType } from '../../utils/dataService';
import { CollapsibleSidebar, MenuItem } from '../ui/CollapsibleSidebar';
import { ProfileCompletionCard } from '../loan-officer-portal/ProfileCompletionCard';
import { Button } from '../ui/button';
import { Century21WelcomeOnboarding } from './Century21WelcomeOnboarding';
import { ContentGenerationWizard } from '../shared/ContentGenerationWizard';

interface RealtorDashboardLayoutProps {
  currentUser: UserType;
  branding?: {
    primaryColor: string;
    secondaryColor: string;
    customLogo: string;
    companyName: string;
    headerBackground: string;
  };
}

export function RealtorDashboardLayout({ currentUser, branding }: RealtorDashboardLayoutProps) {
  const navigate = useNavigate();
  const location = useLocation();
  const [headerHeight, setHeaderHeight] = useState<string>('0px');
  const [sidebarCollapsed, setSidebarCollapsed] = useState(() => {
    return typeof window !== 'undefined' && window.innerWidth < 768;
  });

  // Onboarding state
  const [onboardingOpen, setOnboardingOpen] = useState(false);
  const [wizardOpen, setWizardOpen] = useState(false);

  // Check for first login on mount
  useEffect(() => {
    const hasSeenOnboarding = localStorage.getItem(`c21_onboarding_seen_${currentUser.id}`);
    if (!hasSeenOnboarding) {
      // Show onboarding on first login
      setOnboardingOpen(true);
      localStorage.setItem(`c21_onboarding_seen_${currentUser.id}`, 'true');
    }
  }, [currentUser.id]);

  // Safety check
  if (!currentUser) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <p className="text-gray-600">Loading...</p>
      </div>
    );
  }

  // Ensure name is always a string
  const userName = currentUser.name || currentUser.email || 'User';
  const userAvatar = currentUser.avatar || currentUser.headshot_url || '';

  // Get gradient URL from WordPress data
  const gradientUrl = (window as any).frsPortalConfig?.gradientUrl || (window as any).frsSidebarData?.gradientUrl || '';

  // Calculate total offset (header + admin bar)
  useEffect(() => {
    const calculateHeaderHeight = () => {
      let totalOffset = 0;

      // Check for WordPress admin bar
      const adminBar = document.getElementById('wpadminbar');
      if (adminBar) {
        totalOffset += adminBar.getBoundingClientRect().height;
      }

      // Try multiple Blocksy header selectors
      const selectors = [
        'header[data-id]',
        '.ct-header',
        'header.site-header',
        '#header',
        'header[id^="ct-"]',
        'header'
      ];

      let blocksyHeader = null;
      for (const selector of selectors) {
        blocksyHeader = document.querySelector(selector);
        if (blocksyHeader) {
          break;
        }
      }

      if (blocksyHeader) {
        const height = blocksyHeader.getBoundingClientRect().height;
        totalOffset += height;
      }

      setHeaderHeight(`${totalOffset}px`);
    };

    calculateHeaderHeight();
    window.addEventListener('resize', calculateHeaderHeight);

    return () => window.removeEventListener('resize', calculateHeaderHeight);
  }, []);

  const menuItems: MenuItem[] = [
    { id: '/', label: 'Overview', icon: Home },
    { id: '/marketing', label: 'Marketing Tools', icon: Briefcase },
    { id: '/tools', label: 'Calculator & Tools', icon: Calculator },
    { id: '/resources', label: 'Resources', icon: FileText },
    { id: '/profile', label: 'Profile', icon: Users },
  ];

  // Use company branding or default colors
  const primaryColor = branding?.primaryColor || '#2563eb';
  const secondaryColor = branding?.secondaryColor || '#2dd4da';
  const companyLogo = branding?.customLogo || '';
  const companyDisplayName = branding?.companyName || 'Real Estate Agent';
  const customHeaderBg = branding?.headerBackground || '';

  const sidebarHeader = (
    <div
      className="relative p-6 flex flex-col items-center justify-center text-center w-full overflow-hidden"
      style={{
        background: 'linear-gradient(135deg, #000000 0%, #1a1a1a 100%)',
        minHeight: '200px',
      }}
    >
      {/* Animated Video Background with Black overlay */}
      {gradientUrl && (
        <>
          <video
            autoPlay
            muted
            loop
            playsInline
            className="absolute inset-0 w-full h-full object-cover"
            style={{
              zIndex: 0,
              filter: 'saturate(0) brightness(0.4)',
            }}
          >
            <source src={gradientUrl} type="video/mp4" />
          </video>
          {/* Black overlay gradient */}
          <div
            className="absolute inset-0"
            style={{
              zIndex: 1,
              background: 'linear-gradient(135deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.6) 100%)',
            }}
          />
        </>
      )}

      {/* Company Logo */}
      {companyLogo && (
        <div className="relative z-10">
          <img
            src={companyLogo}
            alt={companyDisplayName}
            className="h-24 w-auto max-w-[280px] object-contain"
            style={{
              filter: 'brightness(0) saturate(100%) invert(69%) sepia(15%) saturate(815%) hue-rotate(8deg) brightness(93%) contrast(87%)',
            }}
          />
        </div>
      )}
    </div>
  );

  // Profile link copy handler
  const handleCopyProfileLink = () => {
    const profileUrl = currentUser.profile_url || window.location.origin;
    navigator.clipboard.writeText(profileUrl).then(() => {
      // Could add a toast notification here
      alert('Profile link copied to clipboard!');
    });
  };

  const sidebarFooter = (
    <div className="w-full space-y-4 pb-4">
      {/* Get Started Button */}
      <div className="px-4">
        <Button
          onClick={() => setOnboardingOpen(true)}
          className="w-full bg-[#beaf87] hover:bg-[#a89b75] text-white shadow-lg py-4 h-auto"
          size="lg"
        >
          <GraduationCap className="h-4 w-4 mr-2" />
          Get Started Guide
        </Button>
      </div>

      {/* Profile Completion Card */}
      <div className="px-4">
        <div className="bg-white rounded-lg shadow-lg">
          <ProfileCompletionCard
            userData={currentUser}
            gradientStart="#000000"
            gradientEnd="#333333"
          />
        </div>
      </div>
    </div>
  );

  const handleItemClick = (item: MenuItem) => {
    navigate(item.id);
  };

  return (
    <div
      className="min-h-screen"
      style={{
        background: 'var(--brand-page-background)',
        position: 'relative',
        zIndex: 1,
        width: '100%',
        marginTop: 0
      }}
    >
      <CollapsibleSidebar
        menuItems={menuItems}
        activeItemId={location.pathname}
        onItemClick={handleItemClick}
        header={sidebarHeader}
        footer={sidebarFooter}
        width="320px"
        collapsedWidth="4rem"
        backgroundColor="#ffffff"
        textColor="#000000"
        activeItemColor="#000000"
        activeItemBackground="rgba(0, 0, 0, 0.05)"
        position="left"
        topOffset={headerHeight}
        defaultCollapsed={sidebarCollapsed}
        onCollapsedChange={setSidebarCollapsed}
      />

      {/* Main Content */}
      <main className="max-md:p-0 max-md:m-0 md:pt-8 md:pb-6 md:pr-0" style={{ marginLeft: '100px' }}>
        <Outlet />
      </main>

      {/* Century 21 Welcome Onboarding */}
      <Century21WelcomeOnboarding
        isOpen={onboardingOpen}
        onClose={() => setOnboardingOpen(false)}
        currentUser={currentUser}
        companyName={companyDisplayName}
        onCreateLandingPage={() => {
          setOnboardingOpen(false);
          setWizardOpen(true);
        }}
        onCompleteProfile={() => {
          setOnboardingOpen(false);
          navigate('/profile');
        }}
        onNavigate={(path) => navigate(path)}
      />

      {/* Content Generation Wizard */}
      <ContentGenerationWizard
        isOpen={wizardOpen}
        onClose={() => setWizardOpen(false)}
        onSuccess={(pageId) => {
          setWizardOpen(false);
          alert(`Landing page created successfully! Page ID: ${pageId}`);
        }}
        userRole="realtor_partner"
        currentUserId={currentUser.id}
      />
    </div>
  );
}
