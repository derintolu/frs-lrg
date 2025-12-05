import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Button } from '../ui/button';
import { FileText, Book, CheckSquare, Video, Download } from 'lucide-react';

export function Resources() {
  const resourceCategories = [
    {
      title: 'Buyer Guides',
      description: 'Comprehensive guides for first-time and experienced homebuyers',
      icon: Book,
      items: ['First-Time Homebuyer Guide', 'FHA Loan Guide', 'VA Loan Guide', 'Conventional Loan Guide'],
    },
    {
      title: 'Checklists',
      description: 'Step-by-step checklists to keep your clients on track',
      icon: CheckSquare,
      items: ['Pre-Approval Checklist', 'Home Shopping Checklist', 'Closing Checklist', 'Move-In Checklist'],
    },
    {
      title: 'Educational Videos',
      description: 'Video library explaining mortgage concepts',
      icon: Video,
      items: ['Understanding Interest Rates', 'Types of Mortgages', 'The Closing Process', 'Credit Tips'],
    },
    {
      title: 'Downloadable Forms',
      description: 'Forms and documents for your mortgage process',
      icon: FileText,
      items: ['Pre-Qualification Form', 'Document Checklist', 'Rate Lock Request', 'Referral Form'],
    },
  ];

  return (
    <div className="w-full min-h-screen p-4 md:p-8 bg-gray-50/50">
      <div className="max-w-7xl mx-auto">
        <div className="mb-8">
          <h1 className="text-4xl font-bold text-gray-900 mb-2">Resources</h1>
          <p className="text-gray-600 text-lg">
            Educational materials, guides, and tools to support your clients through the mortgage process
          </p>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          {resourceCategories.map((category) => {
            const Icon = category.icon;
            return (
              <Card key={category.title} className="hover:shadow-lg transition-shadow">
                <CardHeader>
                  <div className="flex items-start gap-4">
                    <div className="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center bg-black/10">
                      <Icon className="h-6 w-6 text-black" />
                    </div>
                    <div className="flex-1">
                      <CardTitle className="text-xl mb-2">{category.title}</CardTitle>
                      <p className="text-sm text-gray-600 mb-3">{category.description}</p>
                    </div>
                  </div>
                </CardHeader>
                <CardContent>
                  <ul className="space-y-2 mb-4">
                    {category.items.map((item) => (
                      <li key={item} className="flex items-center gap-2 text-sm text-gray-700">
                        <Download className="h-4 w-4 text-black" />
                        {item}
                      </li>
                    ))}
                  </ul>
                  <Button className="w-full bg-black hover:bg-black/90 text-white" variant="default">
                    View All {category.title}
                  </Button>
                </CardContent>
              </Card>
            );
          })}
        </div>

        <Card className="border-2 border-black/20">
          <CardHeader>
            <CardTitle>Need Additional Resources?</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-gray-600 mb-4">
              Can't find what you're looking for? Contact your 21st Century Lending loan officer for additional
              resources, custom materials, or educational content specific to your needs.
            </p>
            <Button className="bg-black hover:bg-black/90 text-white">
              Request Resources
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
