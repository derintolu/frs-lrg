import { Card } from '../ui/card';
import { ShoppingBag, Calendar, FileText, Mail, MapPin, BookOpen, ArrowRight } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

interface MarketingOverviewProps {
  userId: string;
}

export function MarketingOverview({ userId }: MarketingOverviewProps) {
  const navigate = useNavigate();

  return (
    <div className="w-full min-h-screen p-4 md:p-8 bg-gray-50/50">
      {/* Header Section */}
      <div className="mb-10 max-w-7xl mx-auto">
        <h1 className="text-4xl font-bold text-gray-900 mb-2">Marketing Hub</h1>
        <p className="text-gray-600 text-lg">Choose a tool to get started</p>
      </div>

      {/* Bento Grid */}
      <div className="max-w-7xl mx-auto">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 auto-rows-[200px]">

          {/* Calendar - Large Featured */}
          <Card
            className="lg:col-span-2 lg:row-span-2 relative overflow-hidden cursor-pointer group hover:shadow-xl transition-all duration-300 bg-white border border-gray-200"
            onClick={() => navigate('/marketing/calendar')}
          >
            <div className="h-full p-8 flex flex-col justify-between">
              <div>
                <div className="p-3 rounded-2xl w-fit mb-6 transition-colors" style={{ backgroundColor: 'rgba(37, 99, 235, 0.1)' }}>
                  <Calendar className="h-8 w-8" style={{ color: 'var(--brand-electric-blue)' }} />
                </div>
                <h2 className="text-3xl font-bold text-gray-900 mb-3">Calendar</h2>
                <p className="text-gray-600 text-lg leading-relaxed">
                  Manage your booking calendar and appointments
                </p>
              </div>
              <div className="flex items-center gap-2 font-medium group-hover:gap-3 transition-all" style={{ color: 'var(--brand-electric-blue)' }}>
                <span>Explore</span>
                <ArrowRight className="h-5 w-5" />
              </div>
            </div>
          </Card>

          {/* Social & Print */}
          <Card
            className="relative overflow-hidden cursor-pointer group hover:shadow-xl transition-all duration-300 bg-white border border-gray-200"
            onClick={() => navigate('/marketing/orders')}
          >
            <div className="h-full p-6 flex flex-col justify-between">
              <div className="p-2.5 rounded-xl w-fit transition-colors" style={{ backgroundColor: 'rgba(45, 212, 218, 0.1)' }}>
                <ShoppingBag className="h-6 w-6" style={{ color: 'var(--brand-cyan)' }} />
              </div>
              <div>
                <h3 className="text-xl font-bold text-gray-900 mb-2">Social & Print</h3>
                <p className="text-gray-600 text-sm">Order marketing materials</p>
              </div>
            </div>
          </Card>

          {/* Email Campaigns */}
          <Card
            className="relative overflow-hidden cursor-pointer group hover:shadow-xl transition-all duration-300 bg-white border border-gray-200"
            onClick={() => navigate('/marketing/email-campaigns')}
          >
            <div className="h-full p-6 flex flex-col justify-between">
              <div className="p-2.5 rounded-xl w-fit transition-colors" style={{ backgroundColor: 'rgba(125, 179, 232, 0.1)' }}>
                <Mail className="h-6 w-6" style={{ color: 'var(--brand-light-blue)' }} />
              </div>
              <div>
                <h3 className="text-xl font-bold text-gray-900 mb-2">Email Campaigns</h3>
                <p className="text-gray-600 text-sm">Design and send campaigns</p>
              </div>
            </div>
          </Card>

          {/* Landing Pages */}
          <Card
            className="lg:col-span-2 relative overflow-hidden cursor-pointer group hover:shadow-xl transition-all duration-300 bg-white border border-gray-200"
            onClick={() => navigate('/marketing/landing-pages')}
          >
            <div className="h-full p-6 flex items-center justify-between">
              <div className="flex items-center gap-4">
                <div className="p-3 rounded-2xl transition-colors" style={{ backgroundColor: 'rgba(64, 92, 122, 0.1)' }}>
                  <FileText className="h-7 w-7" style={{ color: 'var(--brand-steel-blue)' }} />
                </div>
                <div>
                  <h3 className="text-2xl font-bold text-gray-900 mb-1">Landing Pages</h3>
                  <p className="text-gray-600">Create custom pages for your campaigns</p>
                </div>
              </div>
              <ArrowRight className="h-6 w-6 text-gray-400 group-hover:translate-x-1 transition-all" style={{ color: 'var(--brand-steel-blue)' }} />
            </div>
          </Card>

          {/* Local SEO */}
          <Card
            className="relative overflow-hidden cursor-pointer group hover:shadow-xl transition-all duration-300 bg-white border border-gray-200"
            onClick={() => navigate('/marketing/local-seo')}
          >
            <div className="h-full p-6 flex flex-col justify-between">
              <div className="p-2.5 rounded-xl w-fit transition-colors" style={{ backgroundColor: 'rgba(45, 212, 218, 0.1)' }}>
                <MapPin className="h-6 w-6" style={{ color: 'var(--brand-cyan)' }} />
              </div>
              <div>
                <h3 className="text-xl font-bold text-gray-900 mb-2">Local SEO</h3>
                <p className="text-gray-600 text-sm">Optimize local presence</p>
              </div>
            </div>
          </Card>

          {/* Brand Guide */}
          <Card
            className="relative overflow-hidden cursor-pointer group hover:shadow-xl transition-all duration-300 bg-white border border-gray-200"
            onClick={() => navigate('/marketing/brand-guide')}
          >
            <div className="h-full p-6 flex flex-col justify-between">
              <div className="p-2.5 rounded-xl w-fit transition-colors" style={{ backgroundColor: 'rgba(68, 75, 87, 0.1)' }}>
                <BookOpen className="h-6 w-6" style={{ color: 'var(--brand-slate)' }} />
              </div>
              <div>
                <h3 className="text-xl font-bold text-gray-900 mb-2">Brand Guide</h3>
                <p className="text-gray-600 text-sm">Access brand assets</p>
              </div>
            </div>
          </Card>

        </div>
      </div>
    </div>
  );
}
