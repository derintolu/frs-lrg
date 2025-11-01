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
      gradient: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
      onClick: () => onNavigate?.('tools-mortgage-calculator'),
    },
    {
      id: 'property-valuation',
      name: 'Property Valuation',
      icon: Home,
      gradient: 'linear-gradient(135deg, #10b981 0%, #06b6d4 100%)',
      onClick: () => onNavigate?.('tools-property-valuation'),
    },
  ];

  const apps: AppTile[] = [
    {
      id: 'outlook',
      name: 'Outlook',
      image: '/wp-content/plugins/frs-partnership-portal/assets/images/outlook-logo.svg',
      gradient: 'linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%)',
      onClick: () => window.open('https://outlook.office.com/', '_blank'),
    },
    {
      id: 'arive',
      name: 'Arive',
      image: '/wp-content/plugins/frs-partnership-portal/assets/images/Arive-Highlight-Logo - 01.webp',
      gradient: 'linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%)',
      onClick: () => window.open('https://app.arive.com/login', '_blank'),
    },
    {
      id: 'fub',
      name: 'Follow Up Boss',
      image: '/wp-content/plugins/frs-partnership-portal/assets/images/FUB LOG.webp',
      gradient: 'linear-gradient(135deg, #ffffff 0%, #f5f5f5 100%)',
      onClick: () => window.open('https://app.followupboss.com/login', '_blank'),
    },
  ];

  return (
    <Card
      className="shadow-xl border-0 rounded w-full h-full overflow-hidden flex flex-col"
      style={{
        background: 'linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #dbeafe 100%)'
      }}
    >
      <CardHeader className="pt-3 px-4 pb-0 flex-shrink-0">
        <CardTitle className="flex items-center gap-2 text-gray-900 text-base">
          <Grid2x2 className="h-4 w-4" />
          Toolbox
        </CardTitle>
      </CardHeader>
      <CardContent className="px-4 pb-3 pt-2 flex-1 overflow-y-auto">
        {/* Tools Section */}
        <div className="mb-2">
          <h3 className="text-xs font-semibold text-gray-600 mb-1">Tools</h3>
          <div className="flex flex-row justify-start items-start gap-1.5 flex-wrap">
            {tools.map((tool) => (
              <div key={tool.id} className="flex flex-col items-center gap-1">
                <button
                  type="button"
                  onClick={tool.onClick}
                  className="group relative flex items-center justify-center aspect-square w-[55px] h-[55px] md:w-[60px] md:h-[60px] rounded-lg transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 active:scale-95 shadow-md"
                  style={{ background: tool.gradient }}
                >
                  {tool.image ? (
                    <img
                      src={tool.image}
                      alt={tool.name}
                      className="w-3/4 h-3/4 object-contain"
                    />
                  ) : tool.icon ? (
                    <tool.icon className="w-3/4 h-3/4 text-white" strokeWidth={1.5} />
                  ) : null}
                </button>
                <span className="text-gray-900 text-[0.65rem] font-medium text-center leading-tight max-w-[60px]">{tool.name}</span>
              </div>
            ))}
          </div>
        </div>

        <Separator className="my-2" />

        {/* Apps Section */}
        <div>
          <h3 className="text-xs font-semibold text-gray-600 mb-1">Apps</h3>
          <div className="flex flex-row justify-start items-start gap-1.5 flex-wrap">
            {apps.map((app) => (
              <div key={app.id} className="flex flex-col items-center gap-1">
                <button
                  type="button"
                  onClick={app.onClick}
                  className="group relative flex items-center justify-center aspect-square w-[55px] h-[55px] md:w-[60px] md:h-[60px] rounded-lg transition-all duration-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-white active:scale-95 bg-white shadow-md"
                >
                  {app.image ? (
                    <img
                      src={app.image}
                      alt={app.name}
                      className="w-3/4 h-3/4 object-contain"
                    />
                  ) : app.icon ? (
                    <app.icon className="w-3/4 h-3/4 text-gray-600" strokeWidth={1.5} />
                  ) : null}
                </button>
                <span className="text-gray-900 text-[0.65rem] font-medium text-center leading-tight max-w-[60px]">{app.name}</span>
              </div>
            ))}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}
