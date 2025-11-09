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
      gradient: 'radial-gradient(circle 200px at 30% 40%, rgba(37, 99, 235, 0.6) 0%, rgba(37, 99, 235, 0.3) 50%, transparent 100%)',
      onClick: () => onNavigate?.('tools-mortgage-calculator'),
    },
    {
      id: 'property-valuation',
      name: 'Property Valuation',
      icon: Home,
      gradient: 'radial-gradient(circle 200px at 30% 40%, rgba(16, 185, 129, 0.6) 0%, rgba(16, 185, 129, 0.3) 50%, transparent 100%)',
      onClick: () => onNavigate?.('tools-property-valuation'),
    },
  ];

  const apps: AppTile[] = [
    {
      id: 'outlook',
      name: 'Outlook',
      icon: () => (
        <svg viewBox="0 0 48 48" className="w-12 h-12">
          <rect x="4" y="8" width="40" height="32" rx="2" fill="#0078d4"/>
          <path d="M4 10 L24 24 L44 10" stroke="white" strokeWidth="2" fill="none"/>
          <ellipse cx="24" cy="24" rx="10" ry="8" fill="white"/>
          <ellipse cx="24" cy="24" rx="6" ry="5" fill="#0078d4"/>
        </svg>
      ),
      gradient: 'radial-gradient(circle 200px at 30% 40%, rgba(0, 120, 212, 0.65) 0%, rgba(0, 120, 212, 0.35) 50%, transparent 100%)',
      onClick: () => window.open('https://outlook.office.com/', '_blank'),
    },
    {
      id: 'arive',
      name: 'Arive',
      image: '/wp-content/plugins/frs-partnership-portal/assets/images/Arive-Highlight-Logo - 01.webp',
      gradient: 'radial-gradient(circle 200px at 30% 40%, rgba(30, 144, 255, 0.6) 0%, rgba(30, 144, 255, 0.3) 50%, transparent 100%)',
      onClick: () => window.open('https://app.arive.com/login', '_blank'),
    },
    {
      id: 'fub',
      name: 'Follow Up Boss',
      image: '/wp-content/plugins/frs-partnership-portal/assets/images/FUB LOG.webp',
      gradient: 'radial-gradient(circle 200px at 30% 40%, rgba(220, 38, 38, 0.65) 0%, rgba(220, 38, 38, 0.35) 50%, transparent 100%)',
      onClick: () => window.open('https://app.followupboss.com/login', '_blank'),
    },
  ];

  return (
    <div className="grid grid-cols-2 md:grid-cols-3 gap-3 h-full">
      {tools.map((tool) => (
        <button
          key={tool.id}
          type="button"
          onClick={tool.onClick}
          className="relative shadow-lg rounded overflow-hidden p-4 flex flex-col items-center justify-center gap-2 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] border border-white/20"
          style={{
            background: `linear-gradient(135deg, rgba(255, 255, 255, 0.75) 0%, rgba(248, 250, 252, 0.7) 100%), ${tool.gradient}`,
          }}
        >
          <div className="absolute inset-0 bg-gradient-to-br from-white/20 via-transparent to-white/10 pointer-events-none"></div>
          {tool.image ? (
            <img
              src={tool.image}
              alt={tool.name}
              className="w-12 h-12 object-contain relative z-10"
            />
          ) : tool.icon ? (
            <tool.icon className="w-12 h-12 text-gray-700 relative z-10" strokeWidth={1.5} />
          ) : null}
          <span className="text-gray-800 text-xs font-semibold text-center relative z-10">{tool.name}</span>
        </button>
      ))}
      {apps.map((app) => (
        <button
          key={app.id}
          type="button"
          onClick={app.onClick}
          className="relative shadow-lg rounded overflow-hidden p-4 flex flex-col items-center justify-center gap-2 transition-all duration-300 hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] border border-white/20"
          style={{
            background: `linear-gradient(135deg, rgba(255, 255, 255, 0.75) 0%, rgba(248, 250, 252, 0.7) 100%), ${app.gradient}`,
          }}
        >
          <div className="absolute inset-0 bg-gradient-to-br from-white/20 via-transparent to-white/10 pointer-events-none"></div>
          {app.image ? (
            <img
              src={app.image}
              alt={app.name}
              className="w-12 h-12 object-contain relative z-10"
            />
          ) : app.icon ? (
            typeof app.icon === 'function' ? (
              <div className="relative z-10">{app.icon()}</div>
            ) : (
              <app.icon className="w-12 h-12 text-gray-700 relative z-10" strokeWidth={1.5} />
            )
          ) : null}
          <span className="text-gray-800 text-xs font-semibold text-center relative z-10">{app.name}</span>
        </button>
      ))}
    </div>
  );
}
