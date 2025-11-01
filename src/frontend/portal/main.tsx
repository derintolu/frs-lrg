import { createRoot } from "react-dom/client";
import LoanOfficerPortal from "./LoanOfficerPortal.tsx";
import "./index.css";

// WordPress integration - look for the portal root element
const partnershipPortalRoot = document.getElementById("lrh-portal-root");

// Mount Loan Officer Portal (uses [lrh_portal] shortcode)
if (partnershipPortalRoot) {
  const config = (window as any).lrhPortalConfig || {
    userId: 0,
    userName: '',
    userEmail: '',
    userAvatar: '',
    userRole: 'loan_officer',
    restNonce: '',
    apiUrl: '/wp-json/lrh/v1/'
  };

  console.log('Loan Officer Portal mounting with config:', config);

  createRoot(partnershipPortalRoot).render(
    <LoanOfficerPortal {...config} />
  );

  console.log('Loan Officer Portal mounted successfully');
}
