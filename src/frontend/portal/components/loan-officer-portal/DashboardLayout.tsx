import { useState, useEffect } from 'react';
import { Outlet, useNavigate, useLocation } from 'react-router-dom';
import { Card } from '../ui/card';
import { Separator } from '../ui/separator';
import {
  Home,
  Users,
  TrendingUp,
  Megaphone,
  ShoppingBag,
  UserPlus,
  Wrench
} from 'lucide-react';
import type { User as UserType } from '../../utils/dataService';
import { CollapsibleSidebar, MenuItem } from '../ui/CollapsibleSidebar';
import { ProfileCompletionNotification } from './ProfileCompletionNotification';

interface DashboardLayoutProps {
  currentUser: UserType;
}

export function DashboardLayout({ currentUser }: DashboardLayoutProps) {
  const navigate = useNavigate();
  const location = useLocation();
  const [headerHeight, setHeaderHeight] = useState<string>('0px');
  // Start collapsed on mobile (< 768px), open on desktop
  const [sidebarCollapsed, setSidebarCollapsed] = useState(() => {
    return typeof window !== 'undefined' && window.innerWidth < 768;
  });

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
        console.log('Admin bar height:', adminBar.getBoundingClientRect().height);
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
          console.log('Found header with selector:', selector);
          break;
        }
      }

      if (blocksyHeader) {
        const height = blocksyHeader.getBoundingClientRect().height;
        totalOffset += height;
        console.log('Blocksy header height:', height);
      }

      console.log('Total offset (admin bar + header):', totalOffset);
      setHeaderHeight(`${totalOffset}px`);
    };

    // Calculate immediately
    calculateHeaderHeight();

    // Recalculate on window resize
    window.addEventListener('resize', calculateHeaderHeight);

    // Cleanup
    return () => window.removeEventListener('resize', calculateHeaderHeight);
  }, []);

  // Convert navigation items to CollapsibleSidebar MenuItem format
  const menuItems: MenuItem[] = [
    { id: '/', label: 'Welcome', icon: Home },
    {
      id: 'marketing',
      label: 'Marketing',
      icon: Megaphone,
      children: [
        { id: 'marketing/orders', label: 'Social & Print' },
        { id: 'marketing/biolink', label: 'Biolink' },
        { id: 'marketing/calendar', label: 'Calendar' },
        { id: 'marketing/landing-pages', label: 'Landing Pages' },
        { id: 'marketing/email-campaigns', label: 'Email Campaigns' },
        { id: 'marketing/local-seo', label: 'Local SEO' },
        { id: 'marketing/brand-guide', label: 'Brand Guide' },
      ]
    },
    { id: 'leads', label: 'Lead Tracking', icon: TrendingUp },
    {
      id: 'partnerships',
      label: 'Partnerships',
      icon: UserPlus,
      children: [
        { id: 'partnerships/overview', label: 'Overview' },
        { id: 'partnerships/invites', label: 'Invites' },
        { id: 'partnerships/cobranded-marketing', label: 'Co-branded Marketing' },
      ]
    },
    {
      id: 'tools',
      label: 'Tools',
      icon: Wrench,
      children: [
        { id: 'tools/mortgage-calculator', label: 'Mortgage Calculator' },
        { id: 'tools/property-valuation', label: 'Property Valuation' },
      ]
    },
  ];

  // Header content - User Profile Section with Gradient Background and GIF
  const sidebarHeader = (
    <div
      className="relative p-6 flex flex-col items-center text-center w-full overflow-hidden"
      style={{
        background: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
        minHeight: '200px',
      }}
    >
      {/* Animated Video Background */}
      {gradientUrl && (
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

      {/* User Avatar */}
      <div className="relative mb-3 z-10">
        <img
          src={currentUser.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(currentUser.name || 'User')}&background=2DD4DA&color=fff`}
          alt={currentUser.name || 'User'}
          className="size-20 rounded-full border-4 border-white shadow-lg"
          onError={(e) => {
            e.currentTarget.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(currentUser.name || 'User')}&background=2DD4DA&color=fff`;
          }}
        />
      </div>

      {/* User Info */}
      <h3 className="font-semibold text-white text-lg mb-1 z-10 relative">{currentUser.name}</h3>
      <p className="text-white/80 text-xs mb-3 z-10 relative">{currentUser.email || 'Loan Officer'}</p>

      {/* Action Buttons */}
      <div className="flex gap-2 z-10 relative">
        <button
          className="px-3 py-1 text-xs bg-white/10 hover:bg-white/20 text-white rounded border border-white/30 transition-all backdrop-blur-md shadow-lg"
          onClick={() => navigate('/profile')}
        >
          View Profile
        </button>
        <button
          className="px-3 py-1 text-xs bg-white/10 hover:bg-white/20 text-white rounded border border-white/30 transition-all backdrop-blur-md shadow-lg"
          onClick={() => navigate('/profile/edit')}
        >
          Edit Profile
        </button>
      </div>
    </div>
  );

  // Map currentUser to the format expected by ProfileCompletionNotification
  const [profileMetadata, setProfileMetadata] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    job_title: '',
    company: '',
    nmls_id: '',
    bio: '',
    linkedin_url: '',
    facebook_url: '',
    instagram_url: '',
  });

  // Load complete profile data on mount
  useEffect(() => {
    const loadProfileData = async () => {
      try {
        const response = await fetch('/wp-json/frs/v1/users/me/person-profile', {
          headers: {
            'X-WP-Nonce': (window as any).frsPortalConfig?.restNonce || ''
          }
        });

        if (response.ok) {
          const personData = await response.json();
          const nameParts = (currentUser.name || '').split(' ');
          setProfileMetadata({
            first_name: nameParts[0] || '',
            last_name: nameParts.slice(1).join(' ') || '',
            email: personData.primary_business_email || currentUser.email || '',
            phone: personData.phone_number || '',
            job_title: personData.job_title || '',
            company: personData.company || '21st Century Lending',
            nmls_id: personData.nmls_id || '',
            bio: personData.biography || '',
            linkedin_url: personData.linkedin_url || '',
            facebook_url: personData.facebook_url || '',
            instagram_url: personData.instagram_url || '',
          });
        }
      } catch (err) {
        console.error('Failed to load profile metadata:', err);
      }
    };

    loadProfileData();
  }, [currentUser]);

  // Sidebar footer - Profile Completion Notification
  const sidebarFooter = (
    <div className="px-3 pb-3">
      <ProfileCompletionNotification
        userData={profileMetadata}
        onNavigate={(path) => navigate(`/${path}`)}
      />
    </div>
  );

  const handleItemClick = (item: MenuItem) => {
    navigate(`/${item.id}`);
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
        width="16rem"
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
      <main className="max-md:p-0 max-md:pt-4 md:p-6 md:pt-8">
        <Outlet />
      </main>
    </div>
  );
}
