/**
 * Portal Branding Configuration
 * Provides branding colors, logos, and assets based on portal type
 */

export type PortalType = 'lo' | 're' | 'c21p' | 'mi';

export interface PortalBranding {
  type: PortalType;
  name: string;
  logo: string;
  gradientStart: string;
  gradientEnd: string;
  gradientVideo?: string;
  primaryColor: string;
  secondaryColor: string;
}

const baseAssetsUrl = (window as any).frsPortalConfig?.pluginUrl
  ? `${(window as any).frsPortalConfig.pluginUrl}/assets/branding`
  : '/wp-content/plugins/frs-lrg/assets/branding';

export const PORTAL_BRANDING: Record<PortalType, PortalBranding> = {
  lo: {
    type: 'lo',
    name: '21st Century Lending',
    logo: `${baseAssetsUrl}/21cl-logo.png`,
    gradientStart: '#2563eb', // Blue
    gradientEnd: '#2dd4da', // Cyan
    gradientVideo: (window as any).frsPortalConfig?.gradientUrl || '',
    primaryColor: '#2563eb',
    secondaryColor: '#2dd4da',
  },
  re: {
    type: 're',
    name: 'Century 21',
    logo: `${baseAssetsUrl}/c21/C21_Wordmark_Gold.png`,
    gradientStart: '#FFD700', // Bright gold
    gradientEnd: '#FFA500', // Orange-gold
    gradientVideo: `${baseAssetsUrl}/c21/uri_ifs___V_qOW8VPZVcSbYzYtPJpibHe59rkbdVzCnRp4VqLXXOIM.mp4`,
    primaryColor: '#FFD700',
    secondaryColor: '#FFA500',
  },
  c21p: {
    type: 'c21p',
    name: 'Century 21 Plus',
    logo: `${baseAssetsUrl}/c21/C21_Wordmark_Gold.png`,
    gradientStart: '#FFD700',
    gradientEnd: '#FFA500',
    gradientVideo: `${baseAssetsUrl}/c21/uri_ifs___V_zTd2zSEhnXf4guo7pMlsFK2faW0dibCLI4Bu1rSX6wE.mp4`,
    primaryColor: '#FFD700',
    secondaryColor: '#FFA500',
  },
  mi: {
    type: 'mi',
    name: 'MI Partners',
    logo: `${baseAssetsUrl}/21cl-logo.png`, // Placeholder
    gradientStart: '#6366f1', // Purple
    gradientEnd: '#8b5cf6', // Violet
    primaryColor: '#6366f1',
    secondaryColor: '#8b5cf6',
  },
};

/**
 * Detect portal type from URL path
 */
export function detectPortalType(): PortalType {
  const path = window.location.pathname;

  if (path.startsWith('/re/')) return 're';
  if (path.startsWith('/c21p/')) return 'c21p';
  if (path.startsWith('/mi/')) return 'mi';
  if (path.startsWith('/lo/')) return 'lo';

  // Default to LO portal
  return 'lo';
}

/**
 * Get branding for current portal
 */
export function getCurrentPortalBranding(): PortalBranding {
  const portalType = detectPortalType();
  return PORTAL_BRANDING[portalType];
}

/**
 * Get branding for specific portal type
 */
export function getPortalBranding(type: PortalType): PortalBranding {
  return PORTAL_BRANDING[type];
}
