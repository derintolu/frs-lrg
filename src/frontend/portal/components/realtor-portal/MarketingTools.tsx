import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Button } from '../ui/button';
import { FileText, Image, Share2, Download } from 'lucide-react';

interface MarketingToolsProps {
  companyName: string;
}

export function MarketingTools({ companyName }: MarketingToolsProps) {
  const marketingAssets = [
    {
      title: 'Co-Branded Flyers',
      description: 'Download ready-to-print flyers featuring both your company and 21st Century Lending',
      icon: FileText,
      action: 'Download Templates',
    },
    {
      title: 'Social Media Graphics',
      description: 'Professional graphics optimized for Facebook, Instagram, LinkedIn, and Twitter',
      icon: Image,
      action: 'View Gallery',
    },
    {
      title: 'Email Templates',
      description: 'Pre-written email templates to introduce your mortgage partnership',
      icon: Share2,
      action: 'Browse Templates',
    },
    {
      title: 'Digital Assets',
      description: 'Logos, brand guidelines, and digital materials for your website',
      icon: Download,
      action: 'Access Files',
    },
  ];

  return (
    <div className="w-full min-h-screen p-4 md:p-8 bg-gray-50/50">
      <div className="max-w-7xl mx-auto">
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-2">Marketing Tools</h1>
          <p className="text-gray-600 text-lg">
            Co-branded marketing materials to promote your partnership with 21st Century Lending
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {marketingAssets.map((asset) => {
            const Icon = asset.icon;
            return (
              <Card key={asset.title} className="hover:shadow-lg transition-shadow">
                <CardHeader>
                  <div className="flex items-start gap-4">
                    <div
                      className="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center"
                      style={{ backgroundColor: 'rgba(212, 175, 55, 0.1)' }}
                    >
                      <Icon className="h-6 w-6" style={{ color: '#D4AF37' }} />
                    </div>
                    <div className="flex-1">
                      <CardTitle className="text-xl mb-2">{asset.title}</CardTitle>
                      <p className="text-sm text-gray-600">{asset.description}</p>
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <Button className="w-full" variant="outline">
                    {asset.action}
                  </Button>
                </CardContent>
              </Card>
            );
          })}
        </div>

        <Card className="mt-8">
          <CardHeader>
            <CardTitle>Need Custom Marketing Materials?</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600 mb-4">
              Our marketing team can create custom co-branded materials tailored specifically for {companyName}.
              Contact your loan officer to discuss custom marketing solutions.
            </p>
            <Button style={{ backgroundColor: '#D4AF37', color: '#000000' }}>
              Request Custom Materials
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
