import { useState, useEffect } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import {
  Home,
  Users,
  Briefcase,
  FileText,
  Calculator,
  TrendingUp,
  Wrench
} from 'lucide-react';
import type { User as UserType } from '../../utils/dataService';
import { CollapsibleSidebar, MenuItem } from '../ui/CollapsibleSidebar';

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
    {
      id: '/marketing',
      label: 'Marketing',
      icon: Briefcase,
      children: [
        { id: '/marketing/landing-pages', label: 'Landing Pages' },
        { id: '/marketing/cobranded', label: 'Co-branded Materials' },
        { id: '/marketing/social-media', label: 'Social Media Assets' },
      ]
    },
    { id: '/loan-officers', label: 'My Loan Officers', icon: Users },
    { id: '/leads', label: 'Lead Tracking', icon: TrendingUp },
    {
      id: '/tools',
      label: 'Tools',
      icon: Wrench,
      children: [
        { id: '/tools/mortgage-calculator', label: 'Mortgage Calculator' },
        { id: '/tools/property-valuation', label: 'Property Valuation' },
      ]
    },
    { id: '/resources', label: 'Resources', icon: FileText },
  ];

  // Use company branding or default colors
  const primaryColor = branding?.primaryColor || '#2563eb';
  const secondaryColor = branding?.secondaryColor || '#2dd4da';
  const companyLogo = branding?.customLogo || '';
  const companyDisplayName = branding?.companyName || 'Real Estate Agent';
  const customHeaderBg = branding?.headerBackground || '';

  const sidebarHeader = (
    <div className="relative w-full overflow-hidden">
      {/* Gradient Banner */}
      <div
        className="relative w-full overflow-visible"
        style={{
          background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`,
          height: '100px'
        }}
      >
        {/* Custom Background Image/Video */}
        {customHeaderBg ? (
          <>
            {customHeaderBg.endsWith('.mp4') || customHeaderBg.endsWith('.webm') ? (
              <video
                autoPlay
                muted
                loop
                playsInline
                className="absolute inset-0 w-full h-full object-cover"
                style={{ zIndex: 0 }}
              >
                <source src={customHeaderBg} type="video/mp4" />
              </video>
            ) : (
              <div
                className="absolute inset-0 w-full h-full bg-cover bg-center"
                style={{
                  backgroundImage: `url(${customHeaderBg})`,
                  zIndex: 0
                }}
              />
            )}
            {/* Dark overlay for text/logo readability */}
            <div
              className="absolute inset-0 bg-black/30"
              style={{ zIndex: 1 }}
            />
          </>
        ) : gradientUrl && (
          <>
            <video
              autoPlay
              muted
              loop
              playsInline
              className="absolute inset-0 w-full h-full object-cover"
              style={{ zIndex: 0 }}
            >
              <source src={gradientUrl} type="video/mp4" />
            </video>
            {/* Dark overlay for text readability */}
            <div
              className="absolute inset-0 bg-black/20"
              style={{ zIndex: 1 }}
            />
          </>
        )}

        {/* Company Logo and Name - Centered Layout */}
        <div
          className="relative w-full px-4 py-4 flex flex-col items-center justify-center gap-2"
          style={{ zIndex: 10 }}
        >
          {/* Company Logo */}
          {companyLogo ? (
            <div className="flex-shrink-0">
              <img
                src={companyLogo}
                alt={companyDisplayName}
                className="h-12 w-auto max-w-[200px] object-contain drop-shadow-lg"
                style={{ filter: 'brightness(0) invert(1)' }}
              />
            </div>
          ) : (
            <div className="flex-shrink-0">
              <div
                className="size-14 rounded-full overflow-hidden shadow-lg bg-white/20 flex items-center justify-center"
                style={{
                  border: '2px solid rgba(255, 255, 255, 0.3)',
                }}
              >
                <span className="text-white text-xl font-bold">
                  {companyDisplayName.charAt(0).toUpperCase()}
                </span>
              </div>
            </div>
          )}

          {/* Company Name */}
          <div className="text-center">
            <h3 className="font-bold text-white text-base drop-shadow-md">
              {companyDisplayName}
            </h3>
          </div>
        </div>
      </div>
    </div>
  );

  const sidebarFooter = null;

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
        backgroundColor="hsl(var(--sidebar-background))"
        textColor="hsl(var(--sidebar-foreground))"
        activeItemColor="hsl(var(--sidebar-foreground))"
        activeItemBackground="linear-gradient(to right, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.1))"
        position="left"
        topOffset={headerHeight}
        defaultCollapsed={sidebarCollapsed}
        onCollapsedChange={setSidebarCollapsed}
      />

      {/* Main Content */}
      <main className="max-md:p-0 max-md:m-0 md:pt-8 md:pb-6 md:pl-0 md:pr-0 md:ml-[320px] md:mr-0">
        <Outlet />
      </main>
    </div>
  );
}
