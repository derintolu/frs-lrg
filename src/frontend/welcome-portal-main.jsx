import { createRoot } from 'react-dom/client';
import WelcomePortal from './portal/WelcomePortal';
import { WelcomeBento } from './portal/components/loan-officer-portal/WelcomeBento';
import './index.css';

// Mock navigate function for content-only mode (no actual navigation)
const mockNavigate = () => {};

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
      // Render just the content without layout/sidebar or router
      // WelcomeBento expects router context, so we need to mock it
      root.render(<WelcomeBento userId={String(wpData.userId)} />);
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
