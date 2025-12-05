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
import type { MenuItem } from '../ui/CollapsibleSidebar';
import { ProfileCompletionCard } from './ProfileCompletionCard';
import { getCurrentPortalBranding } from '../../utils/portalBranding';
import { DataService } from '../../utils/dataService';

// Get components from child theme global
const MarketingSidebarOverlay = (window as any).FRSComponents?.MarketingSidebarOverlay;
const CollapsibleSidebar = (window as any).FRSComponents?.CollapsibleSidebar;
import { calculateProfileCompletion } from '../../utils/profileCompletion';

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

  // WordPress menu integration - fetch menu from WP if available
  const [wpMenuItems, setWpMenuItems] = useState<MenuItem[] | null>(null);
  const [wpMenuLoading, setWpMenuLoading] = useState(true);

  // Fetch WordPress menu on mount
  useEffect(() => {
    const fetchMenu = async () => {
      try {
        const response = await DataService.get('/menu/lrh_lo_portal_menu');
        if (response && Array.isArray(response)) {
          setWpMenuItems(response);
        }
      } catch (error) {
        console.log('[LRH] WordPress menu not configured, using default menu');
      } finally {
        setWpMenuLoading(false);
      }
    };

    fetchMenu();
  }, []);

  // Get dynamic branding based on portal type
  const branding = getCurrentPortalBranding();
  const gradientUrl = branding.gradientVideo || (window as any).frsPortalConfig?.gradientUrl || '';

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

  // Determine if we're on a profile or marketing page
  const isProfilePage = location.pathname.startsWith('/profile');
  const isMarketingPage = location.pathname.startsWith('/marketing');
  const [showMarketingOverlay, setShowMarketingOverlay] = useState(false);

  // Auto-show marketing overlay when on marketing pages
  useEffect(() => {
    if (isMarketingPage) {
      setShowMarketingOverlay(true);
    } else {
      setShowMarketingOverlay(false);
    }
  }, [isMarketingPage]);

  // Map currentUser to the format expected by ProfileCompletionCard
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

  // Profile completion card state
  const [isCardDismissed, setIsCardDismissed] = useState(false);
  const [hasReached100Percent, setHasReached100Percent] = useState(false);

  // Load complete profile data on mount
  useEffect(() => {
    const loadProfileData = async () => {
      try {
        // Fetch from frs-users plugin profile endpoint
        const response = await fetch('/wp-json/frs-users/v1/profiles/user/me', {
          credentials: 'include',
          headers: {
            'X-WP-Nonce': (window as any).wpApiSettings?.nonce || (window as any).frsPortalConfig?.restNonce || ''
          }
        });

        if (response.ok) {
          const result = await response.json();
          const profileData = result.data || result;

          setProfileMetadata({
            first_name: profileData.first_name || '',
            last_name: profileData.last_name || '',
            email: profileData.email || currentUser.email || '',
            phone: profileData.phone_number || profileData.mobile_number || '',
            job_title: profileData.job_title || '',
            company: profileData.office || '21st Century Lending',
            nmls_id: profileData.nmls_number || profileData.nmls || '',
            bio: profileData.biography || '',
            linkedin_url: profileData.linkedin_url || '',
            facebook_url: profileData.facebook_url || '',
            instagram_url: profileData.instagram_url || '',
          });
        } else {
          // Fallback to basic user data if profile not found
          const nameParts = (currentUser.name || '').split(' ');
          setProfileMetadata({
            first_name: nameParts[0] || '',
            last_name: nameParts.slice(1).join(' ') || '',
            email: currentUser.email || '',
            phone: '',
            job_title: '',
            company: '21st Century Lending',
            nmls_id: '',
            bio: '',
            linkedin_url: '',
            facebook_url: '',
            instagram_url: '',
          });
        }
      } catch (err) {
        console.error('Failed to load profile metadata:', err);
        // Fallback to basic user data
        const nameParts = (currentUser.name || '').split(' ');
        setProfileMetadata({
          first_name: nameParts[0] || '',
          last_name: nameParts.slice(1).join(' ') || '',
          email: currentUser.email || '',
          phone: '',
          job_title: '',
          company: '21st Century Lending',
          nmls_id: '',
          bio: '',
          linkedin_url: '',
          facebook_url: '',
          instagram_url: '',
        });
      }
    };

    loadProfileData();
  }, [currentUser]);

  // Check if user has reached 100% profile completion (persisted in user meta)
  useEffect(() => {
    const checkCompletionStatus = async () => {
      try {
        const response = await fetch(`/wp-json/wp/v2/users/me`, {
          credentials: 'include',
          headers: {
            'X-WP-Nonce': (window as any).wpApiSettings?.nonce || (window as any).frsPortalConfig?.restNonce || ''
          }
        });

        if (response.ok) {
          const userData = await response.json();
          const reached100 = userData.meta?.profile_completion_reached_100 === '1';
          setHasReached100Percent(reached100);
        }
      } catch (err) {
        console.error('Failed to check completion status:', err);
      }
    };

    checkCompletionStatus();
  }, []);

  // Update user meta when profile reaches 100% completion
  useEffect(() => {
    const updateCompletionStatus = async () => {
      // Calculate current completion
      const completion = calculateProfileCompletion(profileMetadata);

      // If reached 100% and not yet marked in user meta
      if (completion.percentage >= 100 && !hasReached100Percent) {
        try {
          const response = await fetch(`/wp-json/wp/v2/users/me`, {
            method: 'POST',
            credentials: 'include',
            headers: {
              'Content-Type': 'application/json',
              'X-WP-Nonce': (window as any).wpApiSettings?.nonce || (window as any).frsPortalConfig?.restNonce || ''
            },
            body: JSON.stringify({
              meta: {
                profile_completion_reached_100: '1'
              }
            })
          });

          if (response.ok) {
            setHasReached100Percent(true);
            console.log('Profile completion 100% status saved');
          }
        } catch (err) {
          console.error('Failed to update completion status:', err);
        }
      }
    };

    // Only run if we have profile metadata
    if (profileMetadata.email) {
      updateCompletionStatus();
    }
  }, [profileMetadata, hasReached100Percent]);

  // Handle dismiss button click
  const handleDismissProfileCard = () => {
    setIsCardDismissed(true);
  };

  // Convert navigation items to CollapsibleSidebar MenuItem format
  // Show profile-specific menu when on profile page
  const shouldShowProfileCard = isProfilePage && !hasReached100Percent && !isCardDismissed;

  // Default menu items (fallback if WordPress menu not configured)
  const defaultMenuItems: MenuItem[] = isProfilePage ? [
    { id: '/profile', label: 'Profile', icon: Users, url: '/profile' },
    { id: '/profile/settings', label: 'Settings', icon: Wrench, url: '/profile/settings' },
    // Only show ProfileCompletionCard if not at 100% and not dismissed
    ...(shouldShowProfileCard ? [{
      id: 'profile-completion-widget',
      label: '',
      customWidget: (
        <ProfileCompletionCard
          userData={profileMetadata}
          onDismiss={handleDismissProfileCard}
        />
      )
    }] : []),
  ] : [
    {
      id: '/marketing',
      label: 'Marketing',
      icon: Megaphone,
      url: '/marketing',
      children: [
        { id: '/marketing/calendar', label: 'Calendar', url: '/marketing/calendar' },
        { id: '/marketing/orders', label: 'Social & Print', url: '/marketing/orders' },
        { id: '/marketing/landing-pages', label: 'Landing Pages', url: '/marketing/landing-pages' },
        { id: '/marketing/email-campaigns', label: 'Email Campaigns', url: '/marketing/email-campaigns' },
        { id: '/marketing/local-seo', label: 'Local SEO', url: '/marketing/local-seo' },
        { id: '/marketing/brand-guide', label: 'Brand Guide', url: '/marketing/brand-guide' },
      ]
    },
    { id: '/leads', label: 'Lead Tracking', icon: TrendingUp, url: '/leads' },
    { id: '/directory', label: 'Directory', icon: Users, url: '/directory' },
    {
      id: '/tools',
      label: 'Tools',
      icon: Wrench,
      url: '/tools',
      children: [
        { id: '/tools/for-your-website', label: 'For Your Website', url: '/tools/for-your-website' },
        { id: '/tools/mortgage-calculator', label: 'Mortgage Calculator', url: '/tools/mortgage-calculator' },
        { id: '/tools/property-valuation', label: 'Property Valuation', url: '/tools/property-valuation' },
      ]
    },
  ];

  // Use WordPress menu if available, otherwise use default
  const menuItems = wpMenuItems || defaultMenuItems;

  // Check if on profile page
  const isOnProfile = location.pathname === '/profile' || location.pathname.startsWith('/profile/');

  // Header content - Bento-style with overlapping avatar
  const sidebarHeader = (
    <div className="relative w-full overflow-hidden">
      {/* Gradient Banner */}
      <div
        className="relative w-full overflow-visible"
        style={{
          background: `linear-gradient(135deg, ${branding.gradientStart} 0%, ${branding.gradientEnd} 100%)`,
          height: '140px'
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

        {/* Logo - Show for RE portal */}
        {branding.type === 're' && branding.logo && (
          <div
            className="relative w-full px-4 pt-3 pb-2 flex justify-center"
            style={{ zIndex: 10 }}
          >
            <img
              src={branding.logo}
              alt={branding.name}
              className="h-8 object-contain drop-shadow-lg"
            />
          </div>
        )}

        {/* Avatar and Name - Horizontal Layout */}
        <div
          className="relative w-full px-4 py-4 flex items-center gap-3"
          style={{ zIndex: 10 }}
        >
          {/* Avatar with gradient border */}
          <div className="flex-shrink-0">
            <div
              className="size-14 rounded-full overflow-hidden shadow-lg"
              style={{
                border: '2px solid transparent',
                backgroundImage: `linear-gradient(white, white), linear-gradient(135deg, ${branding.gradientStart} 0%, ${branding.gradientEnd} 100%)`,
                backgroundOrigin: 'padding-box, border-box',
                backgroundClip: 'padding-box, border-box',
              }}
            >
              <img
                src={currentUser.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(currentUser.name || 'User')}&background=2DD4DA&color=fff`}
                alt={currentUser.name || 'User'}
                className="w-full h-full object-cover"
                onError={(e) => {
                  e.currentTarget.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(currentUser.name || 'User')}&background=2DD4DA&color=fff`;
                }}
              />
            </div>
          </div>

          {/* Name and Title */}
          <div className="flex-1 min-w-0">
            <h3 className="font-bold text-white text-base mb-0.5 drop-shadow-md truncate">{currentUser.name}</h3>
            <p className="font-normal text-white text-sm drop-shadow-md truncate">{profileMetadata.job_title || 'Loan Officer'}</p>
          </div>
        </div>
      </div>
    </div>
  );

  // Sidebar footer
  const sidebarFooter = null;

  const handleItemClick = (item: MenuItem) => {
    navigate(item.id);
  };

  // Check if CollapsibleSidebar is available
  if (!CollapsibleSidebar) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-red-600">Component Loading Error</h1>
          <p className="text-gray-600 mt-2">CollapsibleSidebar component not found. Please ensure the child theme is active and built.</p>
        </div>
      </div>
    );
  }

  return (
    <div
      className="min-h-screen"
      style={{
        background: branding.type === 're' || branding.type === 'c21p' ? '#000000' : 'var(--brand-page-background)',
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

      {/* Marketing Overlay - rendered from child theme component */}
      {showMarketingOverlay && MarketingSidebarOverlay && (
        <MarketingSidebarOverlay
          activeItemId={location.pathname}
          onItemClick={(path) => {
            navigate(path);
          }}
          onBackClick={() => setShowMarketingOverlay(false)}
          isCollapsed={sidebarCollapsed}
          backgroundColor="hsl(var(--sidebar-background))"
          textColor="hsl(var(--sidebar-foreground))"
          activeItemColor="hsl(var(--sidebar-foreground))"
          activeItemBackground="linear-gradient(to right, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.1))"
        />
      )}

      {/* Main Content */}
      <main className="max-md:p-0 max-md:m-0 md:pt-8 md:pb-6 md:pl-0 md:pr-0 md:ml-[320px] md:mr-0">
        <Outlet />
      </main>
    </div>
  );
}
