import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Button } from '../ui/button';
import { Avatar, AvatarImage, AvatarFallback } from '../ui/avatar';
import { Badge } from '../ui/badge';
import {
  Users,
  TrendingUp,
  FileText,
  ExternalLink,
  Mail,
  Phone,
} from 'lucide-react';

interface LoanOfficer {
  id: number;
  name: string;
  email: string;
  phone: string;
  avatar_url: string;
  nmls_id: string;
  title?: string;
}

interface CompanyOverviewProps {
  userId: string;
  companyId: string;
  companyName: string;
  loanOfficerIds: number[];
}

export function CompanyOverview({ userId, companyId, companyName, loanOfficerIds }: CompanyOverviewProps) {
  const [loanOfficers, setLoanOfficers] = useState<LoanOfficer[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [stats, setStats] = useState({
    loanOfficers: 0,
    leadsThisMonth: 0,
    landingPages: 0,
  });

  useEffect(() => {
    loadLoanOfficers();
  }, [loanOfficerIds]);

  const loadLoanOfficers = async () => {
    if (!loanOfficerIds || loanOfficerIds.length === 0) {
      setIsLoading(false);
      return;
    }

    try {
      setIsLoading(true);

      // Fetch all loan officers and filter by IDs
      const response = await fetch(`/wp-json/lrh/v1/partnerships/loan-officers`, {
        credentials: 'include',
        headers: {
          'X-WP-Nonce': (window as any).wpApiSettings?.nonce || (window as any).frsPortalConfig?.restNonce || '',
        },
      });

      if (response.ok) {
        const result = await response.json();
        const allOfficers = result.data || [];

        // Filter to only assigned officers
        const assignedOfficers = allOfficers
          .filter((officer: any) => loanOfficerIds.includes(Number(officer.ID)))
          .map((officer: any) => ({
            id: officer.ID,
            name: officer.data?.display_name || officer.data?.user_login || 'Unknown',
            email: officer.data?.user_email || '',
            phone: officer.phone || '',
            avatar_url: officer.avatar_url || '',
            nmls_id: officer.nmls_id || '',
            title: officer.title || '',
          }));

        setLoanOfficers(assignedOfficers);
        setStats({
          loanOfficers: assignedOfficers.length,
          leadsThisMonth: 0, // TODO: Load realtor's personal lead count
          landingPages: 0, // TODO: Load realtor's personal landing page count
        });
      }
    } catch (err) {
      console.error('Failed to load loan officers:', err);
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) {
    return (
      <div className="w-full min-h-screen p-4 md:p-8 bg-gray-50/50">
        <div className="max-w-7xl mx-auto">
          <p className="text-gray-600">Loading your dashboard...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="w-full min-h-screen p-4 md:p-8 bg-gray-50/50">
      <div className="max-w-7xl mx-auto">
        {/* Header */}
        <h1 className="text-4xl font-bold text-gray-900 mb-6">Welcome, {companyName}!</h1>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Your Loan Officers</p>
                  <p className="text-3xl font-bold text-gray-900">{stats.loanOfficers}</p>
                </div>
                <Users className="h-10 w-10 text-black" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Your Leads This Month</p>
                  <p className="text-3xl font-bold text-gray-900">{stats.leadsThisMonth}</p>
                </div>
                <TrendingUp className="h-10 w-10 text-black" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Your Landing Pages</p>
                  <p className="text-3xl font-bold text-gray-900">{stats.landingPages}</p>
                </div>
                <FileText className="h-10 w-10 text-black" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Loan Officers Section */}
        <Card className="mb-8">
          <CardHeader>
            <CardTitle>Your 21st Century Lending Team</CardTitle>
          </CardHeader>
          <CardContent>
            {loanOfficers.length === 0 ? (
              <div className="text-center py-12">
                <Users className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                <h3 className="text-lg font-semibold text-gray-900 mb-2">
                  No loan officers assigned yet
                </h3>
                <p className="text-gray-600 mb-4">
                  Your loan officer team will appear here once the partnership is set up
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {loanOfficers.map((lo) => {
                  const loName = lo.name || lo.email || 'Unknown';

                  return (
                    <div
                      key={lo.id}
                      className="flex flex-col items-center gap-4 p-6 border rounded-lg hover:shadow-md transition-shadow bg-white"
                    >
                      <Avatar className="h-20 w-20">
                        <AvatarImage src={lo.avatar_url} alt={loName} />
                        <AvatarFallback className="text-xl">{loName.charAt(0).toUpperCase()}</AvatarFallback>
                      </Avatar>
                      <div className="text-center flex-1">
                        <h3 className="font-semibold text-gray-900 mb-1">{loName}</h3>
                        {lo.title && (
                          <p className="text-sm text-gray-600 mb-2">{lo.title}</p>
                        )}
                        {lo.nmls_id && (
                          <Badge variant="outline" className="text-xs mb-3">
                            NMLS: {lo.nmls_id}
                          </Badge>
                        )}
                        <div className="flex gap-2 justify-center">
                          {lo.email && (
                            <Button variant="ghost" size="sm" asChild>
                              <a href={`mailto:${lo.email}`} title={`Email ${loName}`}>
                                <Mail className="h-4 w-4" />
                              </a>
                            </Button>
                          )}
                          {lo.phone && (
                            <Button variant="ghost" size="sm" asChild>
                              <a href={`tel:${lo.phone}`} title={`Call ${loName}`}>
                                <Phone className="h-4 w-4" />
                              </a>
                            </Button>
                          )}
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </CardContent>
        </Card>

        {/* Quick Actions */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Card className="hover:shadow-lg transition-shadow cursor-pointer">
            <CardContent className="pt-6">
              <div className="text-center">
                <div className="inline-flex items-center justify-center w-12 h-12 bg-black/10 rounded-full mb-4">
                  <FileText className="h-6 w-6 text-black" />
                </div>
                <h3 className="font-semibold text-gray-900 mb-2">Marketing Materials</h3>
                <p className="text-sm text-gray-600 mb-4">
                  Access co-branded flyers, social media posts, and more
                </p>
                <Button variant="default" className="bg-black hover:bg-black/90 text-white" size="sm">
                  View Materials
                  <ExternalLink className="h-4 w-4 ml-2" />
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card className="hover:shadow-lg transition-shadow cursor-pointer">
            <CardContent className="pt-6">
              <div className="text-center">
                <div className="inline-flex items-center justify-center w-12 h-12 bg-black/10 rounded-full mb-4">
                  <TrendingUp className="h-6 w-6 text-black" />
                </div>
                <h3 className="font-semibold text-gray-900 mb-2">Lead Tracking</h3>
                <p className="text-sm text-gray-600 mb-4">
                  Track and manage your mortgage leads
                </p>
                <Button variant="default" className="bg-black hover:bg-black/90 text-white" size="sm">
                  View Leads
                  <ExternalLink className="h-4 w-4 ml-2" />
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card className="hover:shadow-lg transition-shadow cursor-pointer">
            <CardContent className="pt-6">
              <div className="text-center">
                <div className="inline-flex items-center justify-center w-12 h-12 bg-black/10 rounded-full mb-4">
                  <FileText className="h-6 w-6 text-black" />
                </div>
                <h3 className="font-semibold text-gray-900 mb-2">Resources</h3>
                <p className="text-sm text-gray-600 mb-4">
                  Access mortgage guides, checklists, and tools
                </p>
                <Button variant="default" className="bg-black hover:bg-black/90 text-white" size="sm">
                  View Resources
                  <ExternalLink className="h-4 w-4 ml-2" />
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
