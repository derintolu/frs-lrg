import { createRoot } from 'react-dom/client';
import PartnershipsPortal from './portal/PartnershipsPortal';
import './index.css';

/**
 * Partnerships Section Entry Point
 *
 * Standalone section for partner company management.
 * Accessible via [lrh_partnerships_section] shortcode.
 */

// Get the config from WordPress
const wpData = window.frsPortalConfig;

if (!wpData) {
  console.error('Partnerships Section configuration not found. Make sure frsPortalConfig is defined.');
} else {
  const container = document.getElementById('lrh-partnerships-section-root');
  if (container) {
    const root = createRoot(container);
    root.render(
      <PartnershipsPortal
        userId={wpData.userId}
        userName={wpData.userName}
        userEmail={wpData.userEmail}
        userAvatar={wpData.userAvatar}
        restNonce={wpData.restNonce}
      />
    );
  } else {
    console.error('Partnerships Section root element not found');
  }
}
