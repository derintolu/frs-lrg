import { useState, useEffect } from 'react';
import { Card, CardContent } from '../frontend/portal/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../frontend/portal/components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../frontend/portal/components/ui/select';
import { Calculator, User, Mail, Phone, Send } from 'lucide-react';
import { PageHeader } from '../frontend/portal/components/loan-officer-portal/PageHeader';
import { Button } from '../frontend/portal/components/ui/button';
import { Input } from '../frontend/portal/components/ui/input';
import { Label } from '../frontend/portal/components/ui/label';
import {
  ConventionalCalculator,
  AffordabilityCalculator,
  BuydownCalculator,
  DSCRCalculator,
  RefinanceCalculator,
  NetProceedsCalculator,
  RentVsBuyCalculator
} from '../frontend/portal/components/calculators';

export interface WidgetConfig {
  loanOfficerId?: number;
  webhookUrl?: string;
  emailEnabled?: boolean;
  showLeadForm?: boolean;
  brandColor?: string;
  logoUrl?: string;
}

interface LeadData {
  name: string;
  email: string;
  phone: string;
}

interface LoanOfficerData {
  name: string;
  email: string;
  phone: string;
  nmls: string;
  jobTitle: string;
  avatar: string;
}

// Loan Officer Profile Component
function LoanOfficerProfile({ loanOfficer }: { loanOfficer: LoanOfficerData | null }) {
  if (!loanOfficer) return null;

  return (
    <Card className="mb-6">
      <CardContent className="flex items-center gap-4 p-6">
        <div
          className="relative p-1 rounded-full"
          style={{
            background: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)'
          }}
        >
          {loanOfficer.avatar ? (
            <img
              src={loanOfficer.avatar}
              alt={loanOfficer.name}
              className="w-24 h-24 rounded-full object-cover"
            />
          ) : (
            <div className="w-24 h-24 rounded-full bg-white flex items-center justify-center">
              <User className="w-12 h-12 text-gray-400" />
            </div>
          )}
        </div>
        <div className="flex-1">
          <h3
            className="text-2xl font-bold"
            style={{
              backgroundImage: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
              WebkitBackgroundClip: 'text',
              WebkitTextFillColor: 'transparent',
              backgroundClip: 'text'
            }}
          >
            {loanOfficer.name}
          </h3>
          <div className="flex flex-wrap items-baseline gap-x-3 gap-y-1 mt-1">
            {loanOfficer.jobTitle && (
              <span className="text-base font-semibold text-muted-foreground">{loanOfficer.jobTitle}</span>
            )}
            {loanOfficer.nmls && (
              <span
                className="text-base font-semibold"
                style={{
                  backgroundImage: 'linear-gradient(135deg, #2563eb 0%, #2dd4da 100%)',
                  WebkitBackgroundClip: 'text',
                  WebkitTextFillColor: 'transparent',
                  backgroundClip: 'text'
                }}
              >
                NMLS# {loanOfficer.nmls}
              </span>
            )}
          </div>
          <div className="flex flex-wrap gap-x-4 gap-y-1 mt-2">
            {loanOfficer.phone && (
              <div className="flex items-center gap-1 text-sm text-muted-foreground">
                <Phone className="w-3 h-3" />
                <a href={`tel:${loanOfficer.phone}`} className="hover:underline">
                  {loanOfficer.phone}
                </a>
              </div>
            )}
            {loanOfficer.email && (
              <div className="flex items-center gap-1 text-sm text-muted-foreground">
                <Mail className="w-3 h-3" />
                <a href={`mailto:${loanOfficer.email}`} className="hover:underline">
                  {loanOfficer.email}
                </a>
              </div>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

// Lead Capture Form Component
function LeadCaptureForm({
  loanOfficer,
  webhookUrl,
  brandColor = '#3b82f6'
}: {
  loanOfficer: LoanOfficerData | null;
  webhookUrl?: string;
  brandColor?: string;
}) {
  const [leadData, setLeadData] = useState<LeadData>({
    name: '',
    email: '',
    phone: ''
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitSuccess, setSubmitSuccess] = useState(false);
  const [submitError, setSubmitError] = useState<string | null>(null);

  const handleSubmit = async () => {
    if (!leadData.name || !leadData.email) {
      setSubmitError('Please enter your name and email');
      return;
    }

    setIsSubmitting(true);
    setSubmitError(null);

    const payload = {
      lead: leadData,
      loanOfficer: loanOfficer ? {
        name: loanOfficer.name,
        email: loanOfficer.email,
        phone: loanOfficer.phone,
        nmls: loanOfficer.nmls
      } : null,
      timestamp: new Date().toISOString(),
      source: 'mortgage-calculator-widget',
      url: window.location.href
    };

    try {
      if (webhookUrl) {
        await fetch(webhookUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
      }

      // Also submit to FluentForm if available
      const formElement = document.querySelector('[data-form_id="6"]');
      if (formElement) {
        // Fill FluentForm fields programmatically
        const nameInput = formElement.querySelector('[name="full_name"]') as HTMLInputElement;
        const emailInput = formElement.querySelector('[name="email"]') as HTMLInputElement;
        const phoneInput = formElement.querySelector('[name="phone_number"]') as HTMLInputElement;

        if (nameInput) nameInput.value = leadData.name;
        if (emailInput) emailInput.value = leadData.email;
        if (phoneInput) phoneInput.value = leadData.phone;
      }

      setSubmitSuccess(true);
      setSubmitError(null);
    } catch (error) {
      console.error('Error submitting lead:', error);
      setSubmitError('Failed to submit. Please try again or contact us directly.');
    } finally {
      setIsSubmitting(false);
    }
  };

  if (submitSuccess) {
    return (
      <Card className="bg-green-50 border-green-200">
        <CardContent className="pt-6">
          <div className="text-center">
            <div className="text-green-600 text-lg font-semibold mb-2">
              Thanks for your interest!
            </div>
            <p className="text-sm text-gray-600">
              {loanOfficer ? `${loanOfficer.name} will be in touch soon!` : 'We will be in touch soon!'}
            </p>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardContent className="pt-6 space-y-4">
        <h3 className="font-semibold text-lg">Get Started</h3>
        <p className="text-sm text-muted-foreground">
          Ready to explore your mortgage options? Let&apos;s connect!
        </p>

        <div className="space-y-2">
          <Label htmlFor="name">Name</Label>
          <div className="relative">
            <User className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
            <Input
              id="name"
              className="pl-9"
              placeholder="Your name"
              value={leadData.name}
              onChange={(e) => setLeadData({...leadData, name: e.target.value})}
            />
          </div>
        </div>

        <div className="space-y-2">
          <Label htmlFor="email">Email</Label>
          <div className="relative">
            <Mail className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
            <Input
              id="email"
              type="email"
              className="pl-9"
              placeholder="your@email.com"
              value={leadData.email}
              onChange={(e) => setLeadData({...leadData, email: e.target.value})}
            />
          </div>
        </div>

        <div className="space-y-2">
          <Label htmlFor="phone">Phone</Label>
          <div className="relative">
            <Phone className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
            <Input
              id="phone"
              type="tel"
              className="pl-9"
              placeholder="(555) 123-4567"
              value={leadData.phone}
              onChange={(e) => setLeadData({...leadData, phone: e.target.value})}
            />
          </div>
        </div>

        {submitError && (
          <div className="text-sm text-red-600 bg-red-50 p-3 rounded">
            {submitError}
          </div>
        )}

        <Button
          onClick={handleSubmit}
          disabled={isSubmitting}
          className="w-full"
          style={{ backgroundColor: brandColor }}
        >
          {isSubmitting ? (
            'Sending...'
          ) : (
            <>
              <Send className="h-4 w-4 mr-2" />
              Contact {loanOfficer ? loanOfficer.name : 'Us'}
            </>
          )}
        </Button>
      </CardContent>
    </Card>
  );
}

export function MortgageCalculatorFullWidget({ config = {} }: { config?: WidgetConfig }) {
  const {
    loanOfficerId,
    webhookUrl,
    showLeadForm = true,
    brandColor = '#3b82f6',
    logoUrl
  } = config;

  const [activeTab, setActiveTab] = useState('conventional');
  const [loanOfficer, setLoanOfficer] = useState<LoanOfficerData | null>(null);

  // Fetch loan officer data
  useEffect(() => {
    const fetchLoanOfficer = async () => {
      // Try to get from config first, then from frsPortalConfig, then from URL param
      let officerId = loanOfficerId;

      if (!officerId) {
        // Check if there's a user logged in
        const userId = (window as any).frsPortalConfig?.userId;
        if (userId) {
          officerId = userId;
        }
      }

      if (!officerId) {
        // Check URL parameter ?loan_officer_id=123
        const urlParams = new URLSearchParams(window.location.search);
        const urlOfficerId = urlParams.get('loan_officer_id');
        if (urlOfficerId) {
          officerId = parseInt(urlOfficerId);
        }
      }

      if (officerId) {
        try {
          const response = await fetch(`/wp-json/frs-users/v1/profiles/user/${officerId}`);
          const result = await response.json();

          if (result.success && result.data) {
            const data = result.data;
            setLoanOfficer({
              name: data.first_name && data.last_name
                ? `${data.first_name} ${data.last_name}`
                : data.display_name || '',
              email: data.email || '',
              phone: data.mobile_number || data.phone_number || '',
              nmls: data.nmls || data.nmls_number || '',
              jobTitle: data.job_title || '',
              avatar: data.profile_photo || ''
            });
          }
        } catch (error) {
          console.error('Failed to fetch loan officer:', error);
        }
      }
    };

    fetchLoanOfficer();
  }, [loanOfficerId]);

  return (
    <div className="w-full max-w-7xl mx-auto p-6">
      {logoUrl && (
        <div className="mb-6 text-center">
          <img src={logoUrl} alt="Logo" className="h-16 mx-auto" />
        </div>
      )}

      {/* Loan Officer Profile */}
      <LoanOfficerProfile loanOfficer={loanOfficer} />

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div className="lg:col-span-2">
          {/* Page Header */}
          <PageHeader
            icon={Calculator}
            title="Mortgage Calculator"
            iconBgColor="linear-gradient(135deg, #3b82f6 0%, #2DD4DA 100%)"
          />
          <p className="text-muted-foreground mt-2 mb-6">
            Calculate payments for different mortgage types
          </p>

          <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
            {/* Mobile: Dropdown selector */}
            <div className="md:hidden mb-6">
              <Select value={activeTab} onValueChange={setActiveTab}>
                <SelectTrigger className="w-full">
                  <SelectValue placeholder="Select calculator type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="conventional">Payment Calculator</SelectItem>
                  <SelectItem value="affordability">Affordability Calculator</SelectItem>
                  <SelectItem value="buydown">Buydown Calculator</SelectItem>
                  <SelectItem value="dscr">DSCR Calculator</SelectItem>
                  <SelectItem value="refinance">Refinance Calculator</SelectItem>
                  <SelectItem value="netproceeds">Net Proceeds Calculator</SelectItem>
                  <SelectItem value="rentvsbuy">Rent vs Buy Calculator</SelectItem>
                </SelectContent>
              </Select>
            </div>

            {/* Desktop: Tabs */}
            <TabsList className="grid w-full grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 mb-6 gap-1">
              <TabsTrigger value="conventional">Payment</TabsTrigger>
              <TabsTrigger value="affordability">Affordability</TabsTrigger>
              <TabsTrigger value="buydown">Buydown</TabsTrigger>
              <TabsTrigger value="dscr">DSCR</TabsTrigger>
              <TabsTrigger value="refinance">Refinance</TabsTrigger>
              <TabsTrigger value="netproceeds">Net Proceeds</TabsTrigger>
              <TabsTrigger value="rentvsbuy">Rent vs Buy</TabsTrigger>
            </TabsList>

            <TabsContent value="conventional">
              <ConventionalCalculator />
            </TabsContent>

            <TabsContent value="affordability">
              <AffordabilityCalculator />
            </TabsContent>

            <TabsContent value="buydown">
              <BuydownCalculator />
            </TabsContent>

            <TabsContent value="dscr">
              <DSCRCalculator />
            </TabsContent>

            <TabsContent value="refinance">
              <RefinanceCalculator />
            </TabsContent>

            <TabsContent value="netproceeds">
              <NetProceedsCalculator />
            </TabsContent>

            <TabsContent value="rentvsbuy">
              <RentVsBuyCalculator />
            </TabsContent>
          </Tabs>
        </div>

        {/* Sidebar with Lead Form */}
        <div className="lg:col-span-1">
          {showLeadForm && (
            <LeadCaptureForm
              loanOfficer={loanOfficer}
              webhookUrl={webhookUrl}
              brandColor={brandColor}
            />
          )}
        </div>
      </div>
    </div>
  );
}
