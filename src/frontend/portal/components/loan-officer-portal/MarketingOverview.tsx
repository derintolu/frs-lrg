import { Card } from '../ui/card';
import { ShoppingBag, Calendar, FileText, Mail, MapPin, BookOpen, ArrowRight } from 'lucide-react';
import { useNavigate } from 'react-router-dom';

interface MarketingOverviewProps {
  userId: string;
}

export function MarketingOverview({ userId }: MarketingOverviewProps) {
  const navigate = useNavigate();

  return (
    <div className="w-full min-h-screen p-4 md:p-8">
      {/* Header Section */}
      <div className="brand-page-header max-w-7xl mx-auto">
        <h1 className="brand-page-title">Marketing Hub</h1>
        <p className="brand-page-subtitle">Choose a tool to get started</p>
      </div>

      {/* Bento Grid */}
      <div className="max-w-7xl mx-auto">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 auto-rows-[200px]">

          {/* Calendar - Large Featured */}
          <Card
            className="lg:col-span-2 lg:row-span-2 brand-feature-card"
            onClick={() => navigate('/marketing/calendar')}
          >
            <div className="h-full p-8 flex flex-col justify-between">
              <div>
                <div className="brand-card-icon w-12 h-12 mb-6">
                  <Calendar className="h-6 w-6" />
                </div>
                <h2 className="text-2xl font-bold text-[var(--brand-dark-navy)] mb-3">Calendar</h2>
                <p className="brand-card-description text-base">
                  Manage your booking calendar and appointments
                </p>
              </div>
              <span className="brand-card-link">
                Explore <ArrowRight className="h-4 w-4" />
              </span>
            </div>
          </Card>

          {/* Social & Print */}
          <Card
            className="brand-feature-card"
            onClick={() => navigate('/marketing/orders')}
          >
            <div className="h-full p-6 flex flex-col justify-between">
              <div className="brand-card-icon">
                <ShoppingBag className="h-5 w-5" />
              </div>
              <div>
                <h3 className="brand-card-title mb-1">Social & Print</h3>
                <p className="brand-card-description text-sm">Order marketing materials</p>
              </div>
            </div>
          </Card>

          {/* Email Campaigns */}
          <Card
            className="brand-feature-card"
            onClick={() => navigate('/marketing/email-campaigns')}
          >
            <div className="h-full p-6 flex flex-col justify-between">
              <div className="brand-card-icon">
                <Mail className="h-5 w-5" />
              </div>
              <div>
                <h3 className="brand-card-title mb-1">Email Campaigns</h3>
                <p className="brand-card-description text-sm">Design and send campaigns</p>
              </div>
            </div>
          </Card>

          {/* Landing Pages */}
          <Card
            className="lg:col-span-2 brand-feature-card"
            onClick={() => navigate('/marketing/landing-pages')}
          >
            <div className="h-full p-6 flex items-center justify-between">
              <div className="flex items-center gap-4">
                <div className="brand-card-icon w-12 h-12">
                  <FileText className="h-6 w-6" />
                </div>
                <div>
                  <h3 className="brand-card-title text-xl mb-1">Landing Pages</h3>
                  <p className="brand-card-description">Create custom pages for your campaigns</p>
                </div>
              </div>
              <ArrowRight className="h-5 w-5 text-[var(--brand-electric-blue)] group-hover:translate-x-1 transition-all" />
            </div>
          </Card>

          {/* Local SEO */}
          <Card
            className="brand-feature-card"
            onClick={() => navigate('/marketing/local-seo')}
          >
            <div className="h-full p-6 flex flex-col justify-between">
              <div className="brand-card-icon">
                <MapPin className="h-5 w-5" />
              </div>
              <div>
                <h3 className="brand-card-title mb-1">Local SEO</h3>
                <p className="brand-card-description text-sm">Optimize local presence</p>
              </div>
            </div>
          </Card>

          {/* Brand Guide */}
          <Card
            className="brand-feature-card"
            onClick={() => navigate('/marketing/brand-guide')}
          >
            <div className="h-full p-6 flex flex-col justify-between">
              <div className="brand-card-icon">
                <BookOpen className="h-5 w-5" />
              </div>
              <div>
                <h3 className="brand-card-title mb-1">Brand Guide</h3>
                <p className="brand-card-description text-sm">Access brand assets</p>
              </div>
            </div>
          </Card>

        </div>
      </div>
    </div>
  );
}
