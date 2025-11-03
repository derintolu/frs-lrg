import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Button } from '../ui/button';
import { LoadingSpinner } from '../ui/loading';
import {
  Globe,
  TrendingUp,
  QrCode,
  Download,
  ExternalLink,
  Edit,
  BarChart3,
  Users,
  Clock,
} from 'lucide-react';
import { DataService } from '../../utils/dataService';
import type { Lead } from '../../utils/dataService';

interface BiolinkMarketingProps {
  userId: string;
  currentUser: any;
}

export function BiolinkMarketing({ userId, currentUser }: BiolinkMarketingProps) {
  const [previewKey, setPreviewKey] = useState(0);

  // Biolink analytics data
  const [biolinkStats, setBiolinkStats] = useState({
    totalLeads: 0,
    conversionRate: 0,
    leadsTrend: '+0%',
    conversionTrend: '+0%'
  });
  const [isLoadingBiolinkStats, setIsLoadingBiolinkStats] = useState(true);

  // Get biolink URL for preview - sanitize first name to match WordPress sanitize_title()
  const sanitizeSlug = (text: string) => {
    return text
      .toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
      .replace(/\s+/g, '-') // Replace spaces with hyphens
      .replace(/-+/g, '-') // Replace multiple hyphens with single
      .replace(/^-+|-+$/g, ''); // Trim hyphens from start/end
  };

  const firstName = currentUser.name.split(' ')[0];
  const userSlug = sanitizeSlug(firstName);
  const biolinkUrl = `${window.location.origin}/${userSlug}`;

  // Recent leads from biolink
  const [recentLeads, setRecentLeads] = useState<Lead[]>([]);
  const [monthlyData, setMonthlyData] = useState<number[]>([]);

  // Load biolink analytics data
  useEffect(() => {
    const loadBiolinkStats = async () => {
      try {
        setIsLoadingBiolinkStats(true);
        const leadsData = await DataService.getLeadsForLO(currentUser.id);

        // Filter leads that came from biolink sources only
        const biolinkLeads = (leadsData || []).filter(lead => {
          const source = lead.source?.toLowerCase() || '';
          return source.includes('biolink') || source.includes('fluentform') || source === 'biolink_form';
        });

        const totalLeads = biolinkLeads.length;
        const conversionRate = totalLeads > 0 ? Math.round((totalLeads / 100) * 100) / 100 : 0;

        setBiolinkStats({
          totalLeads,
          conversionRate,
          leadsTrend: '+12%',
          conversionTrend: '+8%'
        });

        // Get recent 4 leads
        const recent = biolinkLeads
          .sort((a, b) => new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime())
          .slice(0, 4);
        setRecentLeads(recent);

        // Calculate monthly trend (last 7 days)
        const last7Days = Array(7).fill(0);
        const now = new Date();
        biolinkLeads.forEach(lead => {
          const leadDate = new Date(lead.createdAt);
          const daysDiff = Math.floor((now.getTime() - leadDate.getTime()) / (1000 * 60 * 60 * 24));
          if (daysDiff < 7) {
            last7Days[6 - daysDiff]++;
          }
        });
        setMonthlyData(last7Days);
      } catch (err) {
        console.error('Failed to load biolink stats:', err);
      } finally {
        setIsLoadingBiolinkStats(false);
      }
    };

    loadBiolinkStats();
  }, [currentUser.id]);

  const biolinkStatsDisplay = [
    {
      title: 'Total Leads',
      value: biolinkStats.totalLeads,
      change: biolinkStats.leadsTrend,
      icon: TrendingUp,
      color: 'text-green-600'
    },
    {
      title: 'Conversion',
      value: `${biolinkStats.conversionRate}%`,
      change: biolinkStats.conversionTrend,
      icon: TrendingUp,
      color: 'text-blue-600'
    }
  ];

  // QR Code URL (using free API) - higher resolution for clarity
  const qrCodeUrl = `https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=${encodeURIComponent(biolinkUrl)}`;

  // Helper to get time ago
  const getTimeAgo = (date: string) => {
    const now = new Date();
    const then = new Date(date);
    const seconds = Math.floor((now.getTime() - then.getTime()) / 1000);
    if (seconds < 60) return 'Just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    return `${days}d ago`;
  };

  return (
    <div className="p-4">
      {/* Single Grid: Left Column (cards) + Right Column (phone) */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-3">

        {/* Left Column - All Cards */}
        <div className="col-span-1 space-y-3 flex flex-col h-full">

          {/* Share URL Card */}
          <Card className="shadow-md" style={{ background: 'white', border: '1px solid var(--brand-powder-blue)' }}>
            <CardContent className="p-3">
              <div className="flex items-center gap-2 mb-2">
                <Globe className="h-3.5 w-3.5" style={{ color: 'var(--brand-steel-blue)' }} />
                <span className="text-xs font-semibold" style={{ color: 'var(--brand-dark-navy)' }}>Share Your Biolink</span>
              </div>
              <a
                href={biolinkUrl}
                target="_blank"
                rel="noopener noreferrer"
                className="text-xs hover:underline truncate block mb-2"
                style={{ color: 'var(--brand-electric-blue)' }}
              >
                {biolinkUrl}
              </a>
              <div className="flex gap-2">
                <Button
                  size="sm"
                  variant="outline"
                  className="text-xs h-7 flex-1 font-semibold"
                  onClick={() => {
                    navigator.clipboard.writeText(biolinkUrl);
                  }}
                >
                  Copy Link
                </Button>
                <Button
                  size="sm"
                  className="text-xs h-7 flex-1 font-semibold"
                  style={{ background: 'var(--gradient-hero)', color: 'white' }}
                  onClick={() => window.open(biolinkUrl, '_blank')}
                >
                  <ExternalLink className="h-3 w-3 mr-1" />
                  View Live
                </Button>
              </div>
            </CardContent>
          </Card>

          {/* QR Code Card */}
          <Card className="overflow-hidden shadow-md" style={{ background: 'var(--brand-pale-blue)', border: '1px solid var(--brand-powder-blue)' }}>
            <CardContent className="p-4 flex flex-col items-center gap-3">
              <div className="flex items-center gap-2">
                <QrCode className="h-4 w-4" style={{ color: 'var(--brand-steel-blue)' }} />
                <span className="text-sm font-semibold" style={{ color: 'var(--brand-dark-navy)' }}>QR Code</span>
              </div>
              <img src={qrCodeUrl} alt="QR Code" className="w-48 h-48 rounded-lg shadow-md" />
              <Button
                size="sm"
                variant="outline"
                className="text-xs h-7 px-3 w-full font-semibold"
                onClick={() => {
                  const link = document.createElement('a');
                  link.href = qrCodeUrl;
                  link.download = 'biolink-qr-code.png';
                  link.click();
                }}
              >
                <Download className="h-3 w-3 mr-1" />
                Download QR
              </Button>
            </CardContent>
          </Card>

          {/* View Leads Button - Mobile Only */}
          <Button
            className="md:hidden w-full h-12 font-semibold"
            style={{ background: 'var(--gradient-hero)', color: 'white' }}
            onClick={() => window.location.hash = '#leads'}
          >
            <Users className="h-4 w-4 mr-2" />
            View Leads
          </Button>

          {/* Total Leads Card - Desktop Only */}
          <Card className="max-md:hidden flex overflow-hidden shadow-md flex-1" style={{ background: 'var(--gradient-brand-blue)' }}>
            <CardContent className="p-3">
              <div className="flex items-center justify-between mb-1">
                <TrendingUp className="h-4 w-4 text-white" />
                <span className="text-xs font-semibold text-white bg-black/20 px-2 py-0.5 rounded">{biolinkStats.leadsTrend}</span>
              </div>
              <p className="text-xs font-medium text-white mb-1">Total Leads</p>
              <p className="text-3xl font-bold text-white">{biolinkStats.totalLeads}</p>
            </CardContent>
          </Card>

          {/* Conversion Card - Desktop Only */}
          <Card className="max-md:hidden flex overflow-hidden shadow-md flex-1" style={{ background: 'var(--gradient-brand-teal)' }}>
            <CardContent className="p-3">
              <div className="flex items-center justify-between mb-1">
                <BarChart3 className="h-4 w-4 text-white" />
                <span className="text-xs font-semibold text-white bg-black/20 px-2 py-0.5 rounded">{biolinkStats.conversionTrend}</span>
              </div>
              <p className="text-xs font-medium text-white mb-1">Conversion</p>
              <p className="text-3xl font-bold text-white">{biolinkStats.conversionRate}%</p>
            </CardContent>
          </Card>

        </div>

        {/* Right Column - iPhone Preview - Desktop Only */}
        <div className="max-md:hidden block col-span-1 md:col-span-2 h-full">
          <Card className="shadow-lg h-full" style={{ background: 'white', border: '1px solid var(--brand-powder-blue)' }}>
            <CardContent className="p-4 flex items-start gap-4 h-full">
              <div className="flex items-center gap-2" style={{ writingMode: 'vertical-rl', transform: 'rotate(180deg)' }}>
                <span className="font-semibold text-sm whitespace-nowrap" style={{ color: 'var(--brand-dark-navy)' }}>Biolink Preview</span>
                <Globe className="h-4 w-4" style={{ color: 'var(--brand-steel-blue)' }} />
              </div>
              <div className="flex items-center justify-center flex-1">
                {/* Gradient Border Container */}
                <div
                  className="rounded-[60px] p-[2px] relative"
                  style={{
                    background: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
                    width: '320px',
                    height: '640px',
                    boxShadow: '0 25px 50px -12px rgba(0, 0, 0, 0.25)'
                  }}
                >
                  {/* iPhone Mockup */}
                  <div className="bg-black rounded-[59px] p-2.5 relative w-full h-full">
                    <div className="w-full h-full bg-white rounded-[50px] overflow-hidden relative">
                      {/* Dynamic Island */}
                      <div
                        className="absolute top-[15px] left-1/2 -translate-x-1/2 bg-black rounded-[18.5px] z-10"
                        style={{ width: '126px', height: '37px' }}
                      />
                      {/* Content */}
                      <div className="w-full h-full relative" style={{ aspectRatio: '9/16' }}>
                        <iframe
                          key={previewKey}
                          src={`${biolinkUrl}?preview=1&t=${Date.now()}`}
                          className="border-0 absolute inset-0"
                          style={{
                            width: '125%',
                            height: '125%',
                            transform: 'scale(0.8)',
                            transformOrigin: 'top center'
                          }}
                          title="Biolink Preview"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

      </div>
    </div>
  );
}
