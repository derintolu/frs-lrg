import { Navigate } from 'react-router-dom';
import { RealtorDashboardLayout } from './RealtorDashboardLayout';
import { CompanyOverview } from './CompanyOverview';
import { MarketingTools } from './MarketingTools';
import { CalculatorTools } from './CalculatorTools';
import { Resources } from './Resources';
import type { User } from '../../utils/dataService';

interface RoutesConfig {
  currentUser: User;
  userId: string;
  companyId?: string;
  companyName?: string;
  loanOfficerIds?: number[];
  branding?: {
    primaryColor: string;
    secondaryColor: string;
    customLogo: string;
    companyName: string;
    headerBackground: string;
  };
}

export const getRealtorRoutes = ({ currentUser, userId, companyId, companyName, loanOfficerIds, branding }: RoutesConfig) => [
  {
    path: '/',
    element: <RealtorDashboardLayout currentUser={currentUser} branding={branding} />,
    children: [
      {
        path: '/',
        element: <CompanyOverview userId={userId} companyId={companyId || ''} companyName={companyName || branding?.companyName || 'Company'} loanOfficerIds={loanOfficerIds || []} />,
      },
      {
        path: '/marketing/*',
        element: <MarketingTools companyName={companyName || branding?.companyName || 'Company'} />,
      },
      {
        path: '/tools/*',
        element: <CalculatorTools />,
      },
      {
        path: '/resources',
        element: <Resources />,
      },
    ],
  },
];
