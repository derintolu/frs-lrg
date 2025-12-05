import { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Button } from '../ui/button';
import { FileText, Image, Share2, Download, Plus, Globe } from 'lucide-react';
import { ContentGenerationWizard } from '../shared/ContentGenerationWizard';

interface MarketingToolsProps {
  companyName: string;
  userId?: string;
}

export function MarketingTools({ companyName, userId }: MarketingToolsProps) {
  const [wizardOpen, setWizardOpen] = useState(false);

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
          <div className="flex justify-between items-start">
            <div>
              <h1 className="text-4xl font-bold text-gray-900 mb-2">Marketing Tools</h1>
              <p className="text-gray-600 text-lg">
                Co-branded marketing materials to promote your partnership with 21st Century Lending
              </p>
            </div>
            {userId && (
              <Button
                onClick={() => setWizardOpen(true)}
                className="bg-black hover:bg-black/90 text-white"
              >
                <Plus className="h-4 w-4 mr-2" />
                Create Landing Page
              </Button>
            )}
          </div>
        </div>

        {/* Content Generation Wizard */}
        {userId && (
          <ContentGenerationWizard
            isOpen={wizardOpen}
            onClose={() => setWizardOpen(false)}
            onSuccess={(pageId) => {
              setWizardOpen(false);
              // Optionally navigate to the page or show success message
              alert(`Landing page created successfully! Page ID: ${pageId}`);
            }}
            userRole="realtor_partner"
            currentUserId={userId}
          />
        )}

        {/* Co-branded Landing Pages Section */}
        {userId && (
          <Card className="mb-8 border-2 border-black/10">
            <CardHeader>
              <div className="flex items-start gap-4">
                <div className="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center bg-black/10">
                  <Globe className="h-6 w-6 text-black" />
                </div>
                <div className="flex-1">
                  <CardTitle className="text-xl mb-2">Co-Branded Landing Pages</CardTitle>
                  <p className="text-sm text-gray-600">
                    Create professional landing pages co-branded with your loan officer partners for pre-qualification, open houses, and more.
                  </p>
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <Button
                onClick={() => setWizardOpen(true)}
                className="w-full bg-black hover:bg-black/90 text-white"
              >
                <Plus className="h-4 w-4 mr-2" />
                Create Co-Branded Page
              </Button>
            </CardContent>
          </Card>
        )}

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {marketingAssets.map((asset) => {
            const Icon = asset.icon;
            return (
              <Card key={asset.title} className="hover:shadow-lg transition-shadow">
                <CardHeader>
                  <div className="flex items-start gap-4">
                    <div className="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center bg-black/10">
                      <Icon className="h-6 w-6 text-black" />
                    </div>
                    <div className="flex-1">
                      <CardTitle className="text-xl mb-2">{asset.title}</CardTitle>
                      <p className="text-sm text-gray-600">{asset.description}</p>
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <Button className="w-full bg-black hover:bg-black/90 text-white" variant="default">
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
            <Button className="bg-black hover:bg-black/90 text-white">
              Request Custom Materials
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
