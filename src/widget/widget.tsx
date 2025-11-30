import React from 'react';
import { createRoot } from 'react-dom/client';
import { MortgageCalculatorWidget } from './MortgageCalculatorWidget';
import '../frontend/portal/index.css';

console.log('Widget script loaded');

// Auto-initialize on load
if (typeof document !== 'undefined') {
  console.log('Document available, setting up DOMContentLoaded listener');

  window.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded fired');
    const container = document.getElementById('mortgage-calculator');
    console.log('Container found:', container);

    if (container) {
      console.log('Creating React root and rendering...');
      console.log('Container dataset:', container.dataset);

      // Read configuration from data attributes
      const config = {
        loanOfficerId: container.dataset.loanOfficerId ? parseInt(container.dataset.loanOfficerId) : undefined,
        webhookUrl: container.dataset.webhookUrl,
        showLeadForm: container.dataset.showLeadForm !== 'false',
        brandColor: container.dataset.brandColor,
        logoUrl: container.dataset.logoUrl,
        loanOfficerName: container.dataset.loanOfficerName,
        loanOfficerEmail: container.dataset.loanOfficerEmail,
        loanOfficerPhone: container.dataset.loanOfficerPhone,
        emailEnabled: container.dataset.emailEnabled !== 'false',
        emailApiUrl: container.dataset.emailApiUrl,
        disclaimer: container.dataset.disclaimer,
        gradientStart: container.dataset.gradientStart,
        gradientEnd: container.dataset.gradientEnd,
        borderColor: container.dataset.borderColor
      };

      console.log('Widget config:', config);

      try {
        const root = createRoot(container);
        root.render(
          <React.StrictMode>
            <MortgageCalculatorWidget config={config} />
          </React.StrictMode>
        );
        console.log('React render complete');
      } catch (error) {
        console.error('Error rendering widget:', error);
      }
    } else {
      console.error('Container #mortgage-calculator not found');
    }
  });
}
