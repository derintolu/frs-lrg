import { createRoot } from 'react-dom/client';
import WelcomePortal from './portal/WelcomePortal';
import './index.css';

// Get the config from WordPress
const wpData = window.frsPortalConfig;

if (!wpData) {
  console.error('Welcome Portal configuration not found. Make sure frsPortalConfig is defined.');
} else {
  const container = document.getElementById('lrh-welcome-portal-root');
  if (container) {
    const root = createRoot(container);
    root.render(
      <WelcomePortal
        userId={wpData.userId}
        userName={wpData.userName}
        userEmail={wpData.userEmail}
        userAvatar={wpData.userAvatar}
        restNonce={wpData.restNonce}
      />
    );
  } else {
    console.error('Welcome Portal root element not found');
  }
}
