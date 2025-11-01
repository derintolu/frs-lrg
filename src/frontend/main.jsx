import { createRoot } from "react-dom/client";
import "./index.css";
import LoanOfficerPortal from "./portal/LoanOfficerPortal";
import { PortalSidebarApp } from "./portal/components/PortalSidebarApp";

/**
 * Consolidated Frontend Entry Point
 *
 * This single entry point handles mounting different React apps based on
 * which root element is present on the page. This matches the WordPress
 * Plugin Boilerplate pattern where one entry point serves all frontend apps.
 *
 * CRITICAL: Configuration is passed via wp_localize_script which creates
 * window.frsPortalConfig. This is MORE RELIABLE than wp_add_inline_script
 * for ES6 modules because wp_localize_script ensures proper execution order.
 */

// Get configuration from wp_localize_script (created by Frontend.php)
// Uses frsPortalConfig (legacy name) for compatibility
const config = window.frsPortalConfig || {
  userId: 0,
  userName: '',
  userEmail: '',
  userAvatar: '',
  userRole: 'loan_officer',
  restNonce: '',
  apiUrl: '/wp-json/lrh/v1/',
  gradientUrl: '',
  currentUser: {
    id: 0,
    name: '',
    email: '',
    avatar: '',
    roles: []
  }
};

// Debug logging
console.log('[LRH] Frontend entry point loaded');
console.log('[LRH] Config available:', !!window.frsPortalConfig);
console.log('[LRH] Config data:', config);

// Check for Loan Officer Portal root (new or legacy for backward compatibility)
const portalRoot =
  document.getElementById("lrh-portal-root") ||
  document.getElementById("frs-partnership-portal-root");

if (portalRoot) {
  console.log('[LRH] Mounting Loan Officer Portal to:', portalRoot.id);

  try {
    createRoot(portalRoot).render(
      <LoanOfficerPortal {...config} />
    );
    console.log('[LRH] Loan Officer Portal mounted successfully');
  } catch (error) {
    console.error('[LRH] Failed to mount Loan Officer Portal:', error);
  }
}

// Check for Portal Sidebar root
const sidebarRoot = document.getElementById("lrh-portal-sidebar-root");

if (sidebarRoot) {
  console.log('[LRH] Mounting Portal Sidebar');
  console.log('[LRH] Config data:', config);
  console.log('[LRH] gradientUrl:', config.gradientUrl);

  try {
    createRoot(sidebarRoot).render(
      <PortalSidebarApp
        userId={config.userId}
        userName={config.userName}
        userEmail={config.userEmail}
        userAvatar={config.userAvatar}
        userRole={config.userRole}
        siteUrl={config.siteUrl || window.location.origin}
        portalUrl={config.portalUrl || window.location.origin + '/portal'}
        restNonce={config.restNonce}
        gradientUrl={config.gradientUrl}
        menuItems={config.menuItems || []}
      />
    );
    console.log('[LRH] Portal Sidebar mounted successfully');
  } catch (error) {
    console.error('[LRH] Failed to mount Portal Sidebar:', error);
  }
}

// Log if no root elements found
if (!portalRoot && !sidebarRoot) {
  console.log('[LRH] No portal root elements found on this page');
}
