import { createRoot } from "react-dom/client";
import LoanOfficerPortal from "./LoanOfficerPortal.tsx";
import "./index.css";

// WordPress integration - look for the portal root element
// Support both new and legacy root element IDs for backward compatibility
const partnershipPortalRoot =
  document.getElementById("lrh-portal-root") ||
  document.getElementById("frs-partnership-portal-root");

// Mount Loan Officer Portal (uses [lrh_portal] or [frs_partnership_portal] shortcode)
if (partnershipPortalRoot) {
  const config = (window as any).frsPortalConfig || {
    userId: 0,
    userName: '',
    userEmail: '',
    userAvatar: '',
    userRole: 'loan_officer',
    restNonce: '',
    apiUrl: '/wp-json/lrh/v1/'
  };

  console.log('Loan Officer Portal mounting with config:', config);
  console.log('Mounting to element:', partnershipPortalRoot.id);

  createRoot(partnershipPortalRoot).render(
    <LoanOfficerPortal {...config} />
  );

  console.log('Loan Officer Portal mounted successfully');
}
