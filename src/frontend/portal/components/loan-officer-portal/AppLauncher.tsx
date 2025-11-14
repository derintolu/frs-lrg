import { Grid2x2, Calculator, Home } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Separator } from '../ui/separator';

interface AppTile {
  id: string;
  name: string;
  icon?: React.ComponentType<any>;
  image?: string;
  gradient: string;
  onClick: () => void;
}

interface AppLauncherProps {
  onNavigate?: ((view: string) => void) | undefined;
}

export function AppLauncher({ onNavigate }: AppLauncherProps) {
  const tools: AppTile[] = [
    {
      id: 'mortgage-calculator',
      name: 'Mortgage Calculator',
      icon: Calculator,
      gradient: 'var(--gradient-hero)',
      onClick: () => onNavigate?.('tools/mortgage-calculator'),
    },
    {
      id: 'property-valuation',
      name: 'Property Valuation',
      icon: Home,
      gradient: 'var(--gradient-brand-navy)',
      onClick: () => onNavigate?.('tools/property-valuation'),
    },
  ];

  const apps: AppTile[] = [
    {
      id: 'outlook',
      name: 'Outlook',
      image: '/wp-content/plugins/frs-lrg/icons8-outlook.svg',
      gradient: 'var(--brand-dark-navy)',
      onClick: () => window.open('https://outlook.office.com/', '_blank'),
    },
    {
      id: 'arive',
      name: 'Arive',
      image: '/wp-content/plugins/frs-partnership-portal/assets/images/Arive-Highlight-Logo - 01.webp',
      gradient: 'var(--brand-dark-navy)',
      onClick: () => window.open('https://app.arive.com/login', '_blank'),
    },
    {
      id: 'fub',
      name: 'Follow Up Boss',
      image: '/wp-content/plugins/frs-partnership-portal/assets/images/FUB LOG.webp',
      gradient: 'var(--brand-dark-navy)',
      onClick: () => window.open('https://app.followupboss.com/login', '_blank'),
    },
  ];

  const allApps = [...tools, ...apps];

  return (
    <div style={{
      display: 'grid',
      gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))',
      gap: '8px'
    }}>
      {allApps.map((app) => (
        <button
          key={app.id}
          type="button"
          onClick={app.onClick}
          className="relative shadow-lg rounded-lg overflow-hidden p-4 flex flex-col items-center justify-center gap-2 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] bg-white border border-gray-100"
        >
          {app.image ? (
            <img
              src={app.image}
              alt={app.name}
              className={`object-contain relative z-10 ${app.id === 'arive' ? 'w-14 h-14' : 'w-12 h-12'}`}
            />
          ) : app.icon ? (
            <app.icon className="w-12 h-12 text-blue-600 relative z-10" strokeWidth={1.5} />
          ) : null}
          <span className="text-gray-900 text-sm font-semibold text-center relative z-10">{app.name}</span>
        </button>
      ))}
    </div>
  );
}
