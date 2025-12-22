import React from 'react';
import { createRoot } from 'react-dom/client';
import { MortgageCalculatorWidget } from './MortgageCalculatorWidget';
import { PropertyValuation } from '../frontend/portal/components/loan-officer-portal/PropertyValuation';
import { ToolsLandingPage } from './ToolsLandingPage';
import { WelcomeDashboardWidget } from './WelcomeDashboardWidget';
import { LOContactWidget } from './LOContactWidget';
import { TeamWidget } from './TeamWidget';
import { LeadSubmissionsWidget } from './LeadSubmissionsWidget';
import { CommunityPostsWidget } from './CommunityPostsWidget';
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

    // Mount Welcome Dashboard Widget
    const welcomeContainer = document.getElementById('frs-welcome-dashboard');
    if (welcomeContainer) {
      console.log('Mounting Welcome Dashboard Widget');

      const props = {
        userName: welcomeContainer.dataset.userName || 'Friend',
        showMarketRates: welcomeContainer.dataset.showMarketRates !== 'false',
      };

      try {
        const root = createRoot(welcomeContainer);
        root.render(
          <React.StrictMode>
            <WelcomeDashboardWidget {...props} />
          </React.StrictMode>
        );
        console.log('Welcome Dashboard mounted');
      } catch (error) {
        console.error('Error mounting Welcome Dashboard:', error);
      }
    }

    // Mount LO Contact Widget(s) - supports multiple instances
    const loContactContainers = document.querySelectorAll('.frs-lo-contact');
    loContactContainers.forEach((container, index) => {
      console.log(`Mounting LO Contact Widget #${index + 1}`);

      const props = {
        name: container.getAttribute('data-name') || 'Loan Officer',
        title: container.getAttribute('data-title') || 'Loan Officer',
        phone: container.getAttribute('data-phone') || '',
        email: container.getAttribute('data-email') || '',
        avatar: container.getAttribute('data-avatar') || '',
        nmls: container.getAttribute('data-nmls') || '',
        variant: (container.getAttribute('data-variant') as 'card' | 'inline' | 'minimal') || 'card',
      };

      try {
        const root = createRoot(container as HTMLElement);
        root.render(
          <React.StrictMode>
            <LOContactWidget {...props} />
          </React.StrictMode>
        );
        console.log(`LO Contact Widget #${index + 1} mounted`);
      } catch (error) {
        console.error(`Error mounting LO Contact Widget #${index + 1}:`, error);
      }
    });

    // Mount Team Widget
    const teamContainer = document.getElementById('frs-team-widget');
    if (teamContainer) {
      console.log('Mounting Team Widget');

      // Parse members from JSON data attribute
      let members = [];
      try {
        const membersData = teamContainer.dataset.members;
        if (membersData) {
          members = JSON.parse(membersData);
        }
      } catch (e) {
        console.error('Error parsing team members data:', e);
      }

      const props = {
        title: teamContainer.dataset.title || 'Your 21st Century Lending Team!',
        showTitle: teamContainer.dataset.showTitle !== 'false',
        members: members,
        layout: (teamContainer.dataset.layout as 'row' | 'column' | 'grid') || 'row',
        size: (teamContainer.dataset.size as 'default' | 'large') || 'default',
      };

      try {
        const root = createRoot(teamContainer);
        root.render(
          <React.StrictMode>
            <TeamWidget {...props} />
          </React.StrictMode>
        );
        console.log('Team Widget mounted');
      } catch (error) {
        console.error('Error mounting Team Widget:', error);
      }
    }

    // Mount Lead Submissions Widget
    const leadSubmissionsContainer = document.getElementById('frs-lead-submissions');
    if (leadSubmissionsContainer) {
      console.log('Mounting Lead Submissions Widget');

      const props = {
        userId: parseInt(leadSubmissionsContainer.dataset.userId || '0'),
        userRole: (leadSubmissionsContainer.dataset.userRole as 'loan_officer' | 'realtor') || 'loan_officer',
        limit: parseInt(leadSubmissionsContainer.dataset.limit || '5'),
        showHeader: leadSubmissionsContainer.dataset.showHeader !== 'false',
        title: leadSubmissionsContainer.dataset.title || 'Recent Leads',
        restUrl: leadSubmissionsContainer.dataset.restUrl || '/wp-json/lrh/v1',
        nonce: leadSubmissionsContainer.dataset.nonce || '',
      };

      try {
        const root = createRoot(leadSubmissionsContainer);
        root.render(
          <React.StrictMode>
            <LeadSubmissionsWidget {...props} />
          </React.StrictMode>
        );
        console.log('Lead Submissions Widget mounted');
      } catch (error) {
        console.error('Error mounting Lead Submissions Widget:', error);
      }
    }

    // Mount Community Posts Widget
    const communityPostsContainer = document.getElementById('frs-community-posts');
    if (communityPostsContainer) {
      console.log('Mounting Community Posts Widget');

      const props = {
        limit: parseInt(communityPostsContainer.dataset.limit || '5'),
        showHeader: communityPostsContainer.dataset.showHeader !== 'false',
        title: communityPostsContainer.dataset.title || 'The Pulse',
        restUrl: communityPostsContainer.dataset.restUrl || '/wp-json/wp/v2',
        layout: (communityPostsContainer.dataset.layout as 'list' | 'grid' | 'compact') || 'list',
      };

      try {
        const root = createRoot(communityPostsContainer);
        root.render(
          <React.StrictMode>
            <CommunityPostsWidget {...props} />
          </React.StrictMode>
        );
        console.log('Community Posts Widget mounted');
      } catch (error) {
        console.error('Error mounting Community Posts Widget:', error);
      }
    }

    if (!toolsLandingContainer && !mortgageContainer && !propertyContainer && !welcomeContainer && loContactContainers.length === 0 && !teamContainer && !leadSubmissionsContainer && !communityPostsContainer) {
      console.log('No widget containers found on this page');
    }
  });
}
