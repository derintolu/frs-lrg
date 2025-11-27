import { createRoot } from 'react-dom/client';
import { createHashRouter, RouterProvider } from 'react-router-dom';
import './index.css';
import { PartnershipsSection } from './portal/components/loan-officer-portal/PartnershipsSection';
import { HybridGroupManagement } from './portal/components/loan-officer-portal/HybridGroupManagement';

/**
 * Partnerships Section Entry Point
 *
 * Standalone section for partner company management.
 * Accessible via [lrh_partnerships_section] shortcode.
 */

// Get configuration from wp_localize_script
const config = (window as any).frsPortalConfig || {
  userId: '0',
  userName: '',
  userEmail: '',
  userAvatar: '',
  userRole: 'loan_officer',
  restNonce: '',
  apiUrl: '/wp-json/lrh/v1/',
  currentUser: {
    id: 0,
    name: '',
    email: '',
    avatar: '',
    roles: []
  }
};

console.log('[LRH] Partnerships Section entry point loaded');
console.log('[LRH] Config:', config);

const root = document.getElementById('lrh-partnerships-section-root');

if (root) {
  console.log('[LRH] Mounting Partnerships Section');

  // Create router
  const router = createHashRouter([
    {
      path: '/',
      element: <PartnershipsSection userId={config.userId} />,
    },
    {
      path: '/:slug',
      element: <HybridGroupManagement />,
    },
  ]);

  try {
    createRoot(root).render(<RouterProvider router={router} />);
    console.log('[LRH] Partnerships Section mounted successfully');
  } catch (error) {
    console.error('[LRH] Failed to mount Partnerships Section:', error);
  }
} else {
  console.log('[LRH] No partnerships section root element found');
}
