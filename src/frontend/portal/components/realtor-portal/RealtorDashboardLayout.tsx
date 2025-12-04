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
  ExternalLink
} from 'lucide-react';
import type { User as UserType } from '../../utils/dataService';
import { CollapsibleSidebar, MenuItem } from '../ui/CollapsibleSidebar';
import { ProfileCompletionCard } from '../loan-officer-portal/ProfileCompletionCard';
import { Button } from '../ui/button';

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
        background: `linear-gradient(135deg, #252526 0%, #252526 100%)`,
        minHeight: '200px',
      }}
    >
      {/* Animated Video Background with Gold Shimmer */}
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
              filter: 'sepia(100%) saturate(150%) hue-rotate(10deg) brightness(0.9)',
            }}
          >
            <source src={gradientUrl} type="video/mp4" />
          </video>
          {/* Gold shimmer overlay */}
          <div
            className="absolute inset-0 bg-[#beaf87]/40"
            style={{
              zIndex: 1,
              mixBlendMode: 'overlay',
            }}
          />
          {/* Additional gold shimmer layer for depth */}
          <div
            className="absolute inset-0"
            style={{
              zIndex: 2,
              background: 'linear-gradient(135deg, rgba(190, 175, 135, 0.3) 0%, rgba(255, 215, 0, 0.2) 50%, rgba(190, 175, 135, 0.3) 100%)',
              mixBlendMode: 'screen',
            }}
          />
        </>
      )}

      {/* User Avatar */}
      <div className="relative mb-3 z-10 flex items-center justify-center">
        <img
          src={userAvatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=beaf87&color=fff`}
          alt={userName}
          className="w-[104px] h-[104px] rounded-full border-4 border-white shadow-lg"
          onError={(e) => {
            e.currentTarget.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=beaf87&color=fff`;
          }}
        />
      </div>

      {/* User Info */}
      <h3 className="font-semibold text-white text-2xl mb-1 z-10 relative">{userName}</h3>
      <p className="text-white/80 text-base mb-3 z-10 relative">{currentUser.email || 'User'}</p>

      {/* Company Logo */}
      {companyLogo && (
        <div className="relative z-10 mb-3">
          <img
            src={companyLogo}
            alt={companyDisplayName}
            className="h-12 w-auto max-w-[180px] object-contain"
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
      {/* Profile Link Widget */}
      <div className="px-4">
        <div className="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
          <p className="text-white/80 text-xs mb-2">Your Profile Link</p>
          <div className="flex gap-2">
            <Button
              onClick={handleCopyProfileLink}
              variant="ghost"
              size="sm"
              className="flex-1 bg-white/10 hover:bg-white/20 text-white border border-white/30"
            >
              <Copy className="h-4 w-4 mr-2" />
              Copy
            </Button>
            <Button
              onClick={() => window.open(currentUser.profile_url || window.location.origin, '_blank')}
              variant="ghost"
              size="sm"
              className="flex-1 bg-white/10 hover:bg-white/20 text-white border border-white/30"
            >
              <ExternalLink className="h-4 w-4 mr-2" />
              Open
            </Button>
          </div>
        </div>
      </div>

      {/* Profile Completion Card */}
      <div className="px-4">
        <div className="bg-white rounded-lg shadow-lg">
          <ProfileCompletionCard
            userData={currentUser}
            gradientStart="#beaf87"
            gradientEnd="#d4af37"
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
        backgroundColor="#252526"
        textColor="#FFFFFF"
        activeItemColor="#beaf87"
        activeItemBackground="rgba(190, 175, 135, 0.15)"
        position="left"
        topOffset={headerHeight}
        defaultCollapsed={sidebarCollapsed}
        onCollapsedChange={setSidebarCollapsed}
      />

      {/* Main Content */}
      <main className="max-md:p-0 max-md:m-0 md:pt-8 md:pb-6 md:pl-0 md:pr-0">
        <Outlet />
      </main>
    </div>
  );
}
