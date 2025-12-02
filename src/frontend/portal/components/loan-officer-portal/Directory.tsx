import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Megaphone, TrendingUp, Wrench, Users } from 'lucide-react';
import { Link } from 'react-router-dom';

interface DirectoryProps {
  userId: string;
}

export function Directory({ userId }: DirectoryProps) {
  const sections = [
    {
      title: 'Marketing',
      description: 'Manage your marketing campaigns, landing pages, and social media',
      icon: Megaphone,
      href: '/marketing',
      color: 'from-blue-500 to-cyan-500'
    },
    {
      title: 'Lead Tracking',
      description: 'Track and manage your leads',
      icon: TrendingUp,
      href: '/leads',
      color: 'from-green-500 to-emerald-500'
    },
    {
      title: 'Tools',
      description: 'Mortgage calculator, property valuation, and more',
      icon: Wrench,
      href: '/tools',
      color: 'from-purple-500 to-pink-500'
    },
    {
      title: 'My Profile',
      description: 'Update your profile and settings',
      icon: Users,
      href: '/profile',
      color: 'from-orange-500 to-red-500'
    },
  ];

  return (
    <div className="max-w-7xl mx-auto p-6">
      <div className="mb-8">
        <h1 className="text-3xl font-bold mb-2">Portal Directory</h1>
        <p className="text-muted-foreground">Quick access to all portal features</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {sections.map((section) => {
          const Icon = section.icon;
          return (
            <Link key={section.href} to={section.href} className="no-underline">
              <Card className="h-full hover:shadow-lg transition-shadow cursor-pointer">
                <CardHeader>
                  <div className="flex items-center gap-4">
                    <div className={`p-3 rounded-lg bg-gradient-to-br ${section.color}`}>
                      <Icon className="w-6 h-6 text-white" />
                    </div>
                    <div>
                      <CardTitle>{section.title}</CardTitle>
                      <CardDescription>{section.description}</CardDescription>
                    </div>
                  </div>
                </CardHeader>
              </Card>
            </Link>
          );
        })}
      </div>
    </div>
  );
}
