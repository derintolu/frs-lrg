import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import WelcomePortal from './portal/WelcomePortal';
import { WelcomeBento } from './portal/components/loan-officer-portal/WelcomeBento';
import './index.css';

// Get the config from WordPress
const wpData = window.frsPortalConfig;

if (!wpData) {
  console.error('Welcome Portal configuration not found. Make sure frsPortalConfig is defined.');
} else {
  const container = document.getElementById('lrh-welcome-portal-root');
  if (container) {
    const contentOnly = container.getAttribute('data-content-only') === 'true';
    const root = createRoot(container);

    if (contentOnly) {
      // Render just the content without layout/sidebar
      // Wrap in BrowserRouter since WelcomeBento uses useNavigate()
      root.render(
        <BrowserRouter>
          <WelcomeBento userId={String(wpData.userId)} />
        </BrowserRouter>
      );
    } else {
      // Render full portal with sidebar
      root.render(
        <WelcomePortal
          userId={wpData.userId}
          userName={wpData.userName}
          userEmail={wpData.userEmail}
          userAvatar={wpData.userAvatar}
          restNonce={wpData.restNonce}
        />
      );
    }
  } else {
    console.error('Welcome Portal root element not found');
  }
}
