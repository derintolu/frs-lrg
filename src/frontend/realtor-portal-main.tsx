import { createRoot } from 'react-dom/client';
import RealtorPortal from './portal/RealtorPortal';
import './index.css';

/**
 * Realtor Portal Entry Point
 *
 * Standalone portal for real estate agents to access marketing materials,
 * view their loan officer partnerships, and manage leads.
 * Accessible via [lrh_realtor_portal] shortcode.
 */

// Get the config from WordPress
const wpData = window.frsPortalConfig;

if (!wpData) {
  console.error('Realtor Portal configuration not found. Make sure frsPortalConfig is defined.');
} else {
  const container = document.getElementById('lrh-realtor-portal-root');
  if (container) {
    // Get branding data from data attributes
    const brandingData = container.getAttribute('data-branding');
    let branding = undefined;
    if (brandingData) {
      try {
        branding = JSON.parse(brandingData);
      } catch (e) {
        console.error('Failed to parse branding data:', e);
      }
    }

    const root = createRoot(container);
    root.render(
      <RealtorPortal
        userId={wpData.userId}
        userName={wpData.userName}
        userEmail={wpData.userEmail}
        userAvatar={wpData.userAvatar}
        restNonce={wpData.restNonce}
        companySlug={container.getAttribute('data-company-slug') || ''}
        companyId={container.getAttribute('data-company-id') || ''}
        companyName={container.getAttribute('data-company-name') || ''}
        branding={branding}
      />
    );
  } else {
    console.error('Realtor Portal root element not found');
  }
}
