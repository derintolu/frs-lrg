import React from 'react';
import { createRoot } from 'react-dom/client';
import { MortgageCalculatorWidget } from './MortgageCalculatorWidget';
import { PropertyValuation } from '../frontend/portal/components/loan-officer-portal/PropertyValuation';
import { ToolsLandingPage } from './ToolsLandingPage';
import '../frontend/portal/index.css';

console.log('Widget script loaded');

// Auto-initialize on load
if (typeof document !== 'undefined') {
  console.log('Document available, setting up DOMContentLoaded listener');

  window.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded fired');

    // Mount Tools Landing Page
    const toolsLandingContainer = document.getElementById('frs-tools-landing-root');
    if (toolsLandingContainer) {
      console.log('Mounting Tools Landing Page');

      const props = {
        loanOfficerId: toolsLandingContainer.dataset.loanOfficerId ? parseInt(toolsLandingContainer.dataset.loanOfficerId) : undefined,
        loanOfficerName: toolsLandingContainer.dataset.loanOfficerName,
        loanOfficerEmail: toolsLandingContainer.dataset.loanOfficerEmail,
        loanOfficerPhone: toolsLandingContainer.dataset.loanOfficerPhone,
        loanOfficerNmls: toolsLandingContainer.dataset.loanOfficerNmls,
        loanOfficerTitle: toolsLandingContainer.dataset.loanOfficerTitle,
        loanOfficerAvatar: toolsLandingContainer.dataset.loanOfficerAvatar,
        webhookUrl: toolsLandingContainer.dataset.webhookUrl,
        showLeadForm: toolsLandingContainer.dataset.showLeadForm !== 'false'
      };

      try {
        const root = createRoot(toolsLandingContainer);
        root.render(
          <React.StrictMode>
            <ToolsLandingPage {...props} />
          </React.StrictMode>
        );
        console.log('Tools Landing Page mounted');
      } catch (error) {
        console.error('Error mounting Tools Landing Page:', error);
      }
    }

    // Mount Mortgage Calculator Widget
    const mortgageContainer = document.getElementById('mortgage-calculator');
    if (mortgageContainer) {
      console.log('Mounting Mortgage Calculator Widget');

      // Read configuration from data attributes
      const config = {
        loanOfficerId: mortgageContainer.dataset.loanOfficerId ? parseInt(mortgageContainer.dataset.loanOfficerId) : undefined,
        webhookUrl: mortgageContainer.dataset.webhookUrl,
        showLeadForm: mortgageContainer.dataset.showLeadForm !== 'false',
        brandColor: mortgageContainer.dataset.brandColor,
        logoUrl: mortgageContainer.dataset.logoUrl,
        loanOfficerName: mortgageContainer.dataset.loanOfficerName,
        loanOfficerEmail: mortgageContainer.dataset.loanOfficerEmail,
        loanOfficerPhone: mortgageContainer.dataset.loanOfficerPhone,
        emailEnabled: mortgageContainer.dataset.emailEnabled !== 'false',
        emailApiUrl: mortgageContainer.dataset.emailApiUrl,
        disclaimer: mortgageContainer.dataset.disclaimer,
        gradientStart: mortgageContainer.dataset.gradientStart,
        gradientEnd: mortgageContainer.dataset.gradientEnd,
        borderColor: mortgageContainer.dataset.borderColor
      };

      try {
        const root = createRoot(mortgageContainer);
        root.render(
          <React.StrictMode>
            <MortgageCalculatorWidget config={config} />
          </React.StrictMode>
        );
        console.log('Mortgage Calculator mounted');
      } catch (error) {
        console.error('Error mounting Mortgage Calculator:', error);
      }
    }

    // Mount Property Valuation Widget
    const propertyContainer = document.getElementById('property-valuation');
    if (propertyContainer) {
      console.log('Mounting Property Valuation Widget');

      try {
        const root = createRoot(propertyContainer);
        root.render(
          <React.StrictMode>
            <PropertyValuation />
          </React.StrictMode>
        );
        console.log('Property Valuation mounted');
      } catch (error) {
        console.error('Error mounting Property Valuation:', error);
      }
    }

    if (!toolsLandingContainer && !mortgageContainer && !propertyContainer) {
      console.log('No widget containers found on this page');
    }
  });
}
