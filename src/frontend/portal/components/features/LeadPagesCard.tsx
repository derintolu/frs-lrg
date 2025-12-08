import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Badge } from '../ui/badge';
import { Button } from '../ui/button';
import {
  Home,
  Star,
  Calendar,
  FileText,
  Eye,
  Users,
  TrendingUp,
  ExternalLink,
  Plus,
  Loader2
} from 'lucide-react';
import { DataService, LeadPage, LeadPageStats } from '../../utils/dataService';

interface LeadPagesCardProps {
  userId: string;
  userRole?: 'loan_officer' | 'realtor';
  compact?: boolean;
}

export function LeadPagesCard({ userId, userRole = 'loan_officer', compact = false }: LeadPagesCardProps) {
  const [pages, setPages] = useState<LeadPage[]>([]);
  const [stats, setStats] = useState<LeadPageStats | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadData = async () => {
      if (!userId) return;

      try {
        setLoading(true);
        const [pagesData, statsData] = await Promise.all([
          userRole === 'loan_officer'
            ? DataService.getLeadPagesForLO(userId)
            : DataService.getLeadPagesForRealtor(userId),
          DataService.getLeadPageStats(userId, userRole),
        ]);
        setPages(pagesData);
        setStats(statsData);
      } catch (error) {
        console.error('Failed to load lead pages:', error);
      } finally {
        setLoading(false);
      }
    };

    loadData();
  }, [userId, userRole]);

  const getPageTypeIcon = (type: string) => {
    switch (type) {
      case 'open_house':
        return <Home className="h-4 w-4" />;
      case 'customer_spotlight':
        return <Star className="h-4 w-4" />;
      case 'event':
        return <Calendar className="h-4 w-4" />;
      default:
        return <FileText className="h-4 w-4" />;
    }
  };

  const getPageTypeBadge = (type: string) => {
    const config: Record<string, { label: string; className: string }> = {
      open_house: { label: 'Open House', className: 'bg-blue-100 text-blue-800 border-blue-200' },
      customer_spotlight: { label: 'Spotlight', className: 'bg-purple-100 text-purple-800 border-purple-200' },
      event: { label: 'Event', className: 'bg-green-100 text-green-800 border-green-200' },
      general: { label: 'General', className: 'bg-gray-100 text-gray-800 border-gray-200' },
    };
    const { label, className } = config[type] || config.general;
    return <Badge className={`${className} border font-medium text-xs`}>{label}</Badge>;
  };

  if (loading) {
    return (
      <Card className="h-full">
        <CardContent className="flex items-center justify-center h-full min-h-[200px]">
          <Loader2 className="h-8 w-8 animate-spin text-muted-foreground" />
        </CardContent>
      </Card>
    );
  }

  // Compact view for dashboard cards
  if (compact) {
    return (
      <Card className="overflow-hidden h-full border-gray-200 shadow-sm hover:shadow-md transition-shadow">
        <CardContent className="p-5 flex flex-col gap-4 h-full">
          <div className="flex items-start justify-between">
            <div className="space-y-1">
              <h3 className="font-['Mona_Sans'] font-bold text-[20px] leading-[26px] text-gray-950">
                Generation Station
              </h3>
              <p className="font-['Mona_Sans'] font-medium text-[13px] leading-[18px] text-gray-500">
                Create co-branded landing pages for open houses, customer spotlights, and events.
              </p>
            </div>
          </div>

          {/* Stats Row */}
          <div className="grid grid-cols-3 gap-3">
            <div className="text-center p-3 bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-lg border border-blue-200/50">
              <div className="flex items-center justify-center mb-1">
                <Eye className="h-4 w-4 text-blue-600 mr-1" />
              </div>
              <div className="text-xl font-bold text-blue-700">
                {stats?.totalViews || 0}
              </div>
              <div className="text-xs text-blue-600 font-medium mt-0.5">Views</div>
            </div>
            <div className="text-center p-3 bg-gradient-to-br from-teal-50 to-teal-100/50 rounded-lg border border-teal-200/50">
              <div className="flex items-center justify-center mb-1">
                <Users className="h-4 w-4 text-teal-600 mr-1" />
              </div>
              <div className="text-xl font-bold text-teal-700">
                {stats?.totalSubmissions || 0}
              </div>
              <div className="text-xs text-teal-600 font-medium mt-0.5">Leads</div>
            </div>
            <div className="text-center p-3 bg-gradient-to-br from-cyan-50 to-cyan-100/50 rounded-lg border border-cyan-200/50">
              <div className="flex items-center justify-center mb-1">
                <TrendingUp className="h-4 w-4 text-cyan-600 mr-1" />
              </div>
              <div className="text-xl font-bold text-cyan-700">
                {stats?.conversionRate || 0}%
              </div>
              <div className="text-xs text-cyan-600 font-medium mt-0.5">Conv.</div>
            </div>
          </div>

          {/* Recent Pages Preview */}
          {pages.length > 0 ? (
            <div className="flex-1 space-y-2 min-h-[100px]">
              <div className="flex items-center justify-between mb-2">
                <span className="text-xs font-semibold text-gray-600 uppercase tracking-wide">Recent Pages</span>
                <Badge variant="secondary" className="text-xs">
                  {stats?.totalPages || 0} Total
                </Badge>
              </div>
              {pages.slice(0, 2).map((page) => (
                <div
                  key={page.id}
                  className="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg hover:border-gray-300 hover:shadow-sm transition-all"
                >
                  <div className="flex items-center gap-2 flex-1 min-w-0">
                    <div className="flex-shrink-0">
                      {getPageTypeIcon(page.pageType)}
                    </div>
                    <span className="text-sm font-medium truncate text-gray-900">
                      {page.title}
                    </span>
                  </div>
                  <div className="flex items-center gap-3 flex-shrink-0 ml-2">
                    <div className="flex items-center gap-1 text-xs text-gray-500">
                      <Eye className="h-3.5 w-3.5" />
                      <span className="font-medium">{page.views}</span>
                    </div>
                    <div className="flex items-center gap-1 text-xs text-gray-500">
                      <Users className="h-3.5 w-3.5" />
                      <span className="font-medium">{page.submissions}</span>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="flex-1 flex items-center justify-center text-center p-6 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
              <div className="space-y-2">
                <div className="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200">
                  <FileText className="h-6 w-6 text-gray-400" />
                </div>
                <p className="text-sm font-medium text-gray-600">No pages yet</p>
                <p className="text-xs text-gray-500">Create your first landing page</p>
              </div>
            </div>
          )}

          <Button
            className="w-full mt-auto text-white hover:opacity-90 transition-opacity shadow-sm"
            style={{ backgroundColor: 'var(--brand-electric-blue)' }}
            onClick={() => window.location.href = '/wp-admin/post-new.php?post_type=frs_lead_page'}
          >
            <Plus className="h-4 w-4 mr-2" />
            Create New Page
          </Button>
        </CardContent>
      </Card>
    );
  }

  // Full view
  return (
    <Card className="h-full border-gray-200 shadow-sm">
      <CardHeader className="pb-4 border-b border-gray-100">
        <div className="flex items-center justify-between">
          <CardTitle className="flex items-center gap-3">
            <div
              className="p-2.5 rounded-xl shadow-sm"
              style={{ background: 'linear-gradient(135deg, var(--brand-electric-blue) 0%, var(--brand-cyan) 100%)' }}
            >
              <FileText className="h-5 w-5 text-white" />
            </div>
            <div>
              <div className="text-lg font-bold">Generation Station</div>
              <div className="text-sm font-normal text-muted-foreground">Landing page management</div>
            </div>
          </CardTitle>
          <Button
            size="sm"
            className="text-white hover:opacity-90 transition-opacity shadow-sm"
            style={{ backgroundColor: 'var(--brand-electric-blue)' }}
            onClick={() => window.location.href = '/wp-admin/post-new.php?post_type=frs_lead_page'}
          >
            <Plus className="h-4 w-4 mr-1" />
            New Page
          </Button>
        </div>
      </CardHeader>
      <CardContent className="space-y-5 pt-5">
        {/* Stats Grid */}
        <div className="grid grid-cols-4 gap-4">
          <div className="text-center p-4 bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-xl border border-gray-200">
            <div className="flex items-center justify-center mb-2">
              <FileText className="h-5 w-5 text-gray-600" />
            </div>
            <div className="text-2xl font-bold text-gray-900">
              {stats?.totalPages || 0}
            </div>
            <div className="text-xs text-gray-600 font-medium mt-1">Total Pages</div>
          </div>
          <div className="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100/50 rounded-xl border border-blue-200">
            <div className="flex items-center justify-center mb-2">
              <Eye className="h-5 w-5 text-blue-600" />
            </div>
            <div className="text-2xl font-bold text-blue-700">
              {stats?.totalViews || 0}
            </div>
            <div className="text-xs text-blue-600 font-medium mt-1">Views</div>
          </div>
          <div className="text-center p-4 bg-gradient-to-br from-teal-50 to-teal-100/50 rounded-xl border border-teal-200">
            <div className="flex items-center justify-center mb-2">
              <Users className="h-5 w-5 text-teal-600" />
            </div>
            <div className="text-2xl font-bold text-teal-700">
              {stats?.totalSubmissions || 0}
            </div>
            <div className="text-xs text-teal-600 font-medium mt-1">Leads</div>
          </div>
          <div className="text-center p-4 bg-gradient-to-br from-cyan-50 to-cyan-100/50 rounded-xl border border-cyan-200">
            <div className="flex items-center justify-center mb-2">
              <TrendingUp className="h-5 w-5 text-cyan-600" />
            </div>
            <div className="text-2xl font-bold text-cyan-700">
              {stats?.conversionRate || 0}%
            </div>
            <div className="text-xs text-cyan-600 font-medium mt-1">Conversion</div>
          </div>
        </div>

        {/* Pages List */}
        <div className="space-y-3">
          <div className="flex items-center justify-between">
            <h4 className="font-semibold text-sm text-gray-700 uppercase tracking-wide">
              Your Lead Pages
            </h4>
            {pages.length > 0 && (
              <Badge variant="secondary" className="text-xs">
                {pages.length} {pages.length === 1 ? 'Page' : 'Pages'}
              </Badge>
            )}
          </div>

          {pages.length === 0 ? (
            <div className="text-center py-10 bg-gray-50 border-2 border-dashed border-gray-300 rounded-xl">
              <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-200 mb-3">
                <FileText className="h-8 w-8 text-gray-400" />
              </div>
              <p className="text-gray-700 font-medium mb-1">No lead pages yet</p>
              <p className="text-sm text-gray-500 mb-4">
                Create your first landing page to start capturing leads
              </p>
              <Button
                size="sm"
                className="text-white"
                style={{ backgroundColor: 'var(--brand-electric-blue)' }}
                onClick={() => window.location.href = '/wp-admin/post-new.php?post_type=frs_lead_page'}
              >
                <Plus className="h-4 w-4 mr-1" />
                Create Your First Page
              </Button>
            </div>
          ) : (
            <div className="space-y-2 max-h-[350px] overflow-y-auto pr-1">
              {pages.map((page) => (
                <div
                  key={page.id}
                  className="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-xl hover:border-gray-300 hover:shadow-md transition-all group"
                >
                  <div className="flex items-center gap-4 flex-1 min-w-0">
                    <div className="p-2.5 bg-gray-100 rounded-lg group-hover:bg-gray-200 transition-colors">
                      {getPageTypeIcon(page.pageType)}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="font-semibold flex items-center gap-2 mb-1">
                        <span className="truncate text-gray-900">{page.title}</span>
                        {getPageTypeBadge(page.pageType)}
                      </div>
                      <div className="text-sm text-gray-500 truncate">
                        {page.headline || 'No headline set'}
                      </div>
                    </div>
                  </div>

                  <div className="flex items-center gap-4 ml-4">
                    <div className="flex items-center gap-4 text-sm">
                      <div className="flex items-center gap-1.5 px-2.5 py-1.5 bg-blue-50 rounded-lg">
                        <Eye className="h-4 w-4 text-blue-600" />
                        <span className="font-semibold text-blue-700">{page.views}</span>
                      </div>
                      <div className="flex items-center gap-1.5 px-2.5 py-1.5 bg-teal-50 rounded-lg">
                        <Users className="h-4 w-4 text-teal-600" />
                        <span className="font-semibold text-teal-700">{page.submissions}</span>
                      </div>
                    </div>

                    <Button
                      variant="ghost"
                      size="icon"
                      className="hover:bg-gray-100"
                      onClick={() => window.open(page.url, '_blank')}
                      title="View page"
                    >
                      <ExternalLink className="h-4 w-4 text-gray-600" />
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
