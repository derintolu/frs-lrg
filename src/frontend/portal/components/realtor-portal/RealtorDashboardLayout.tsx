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
    <div className="relative w-full overflow-hidden" style={{ backgroundColor: '#000000' }}>
      {/* Gold Yard Sign Accent - Top Left Corner */}
      <div
        className="absolute top-0 left-0 h-full w-4"
        style={{
          backgroundImage: 'url(https://hub21.local/wp-content/uploads/2025/12/C21-Brand-Kit-Yard-Sign-2.png)',
          backgroundSize: 'contain',
          backgroundPosition: 'top left',
          backgroundRepeat: 'no-repeat',
          opacity: 0.8,
          zIndex: 1
        }}
      />

      {/* Partner Company Logo */}
      <div className="relative w-full px-6 py-8 flex flex-col items-center justify-center gap-3" style={{ zIndex: 10 }}>
        {/* Partner Company Logo (Century 21, etc.) */}
        {companyLogo && (
          <div className="flex-shrink-0">
            <img
              src={companyLogo}
              alt={companyDisplayName}
              className="h-20 w-auto max-w-[240px] object-contain"
            />
          </div>
        )}

        {/* Company Name */}
        <div className="text-center">
          <h3 className="font-bold text-base" style={{ color: '#D4AF37' }}>
            {companyDisplayName}
          </h3>
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
        backgroundColor="#000000"
        textColor="#FFFFFF"
        activeItemColor="#D4AF37"
        activeItemBackground="rgba(212, 175, 55, 0.1)"
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
