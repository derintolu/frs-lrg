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
    const root = createRoot(container);
    root.render(
      <RealtorPortal
        userId={wpData.userId}
        userName={wpData.userName}
        userEmail={wpData.userEmail}
        userAvatar={wpData.userAvatar}
        restNonce={wpData.restNonce}
      />
    );
  } else {
    console.error('Realtor Portal root element not found');
  }
}
