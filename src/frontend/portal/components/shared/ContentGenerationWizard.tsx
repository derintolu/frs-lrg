import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Button } from '../ui/button';
import { Badge } from '../ui/badge';
import { LoadingSpinner } from '../ui/loading';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { RadioGroup, RadioGroupItem } from '../ui/radio-group';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '../ui/dialog';
import {
  User,
  Users,
  Building2,
  Home,
  Calculator,
  FileText,
  Link2,
  ArrowRight,
  ArrowLeft,
  Check,
  Sparkles,
  X
} from 'lucide-react';
import { DataService } from '../../utils/dataService';

interface ContentGenerationWizardProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (pageId: string) => void;
  userRole: 'loan_officer' | 'realtor_partner';
  currentUserId: string;
}

interface Partner {
  id: string;
  display_name: string;
  email: string;
  company?: string;
  avatar?: string;
}

interface TemplateOption {
  id: string;
  type: 'biolink' | 'prequal' | 'openhouse' | 'calculator' | 'valuation' | 'tools';
  name: string;
  description: string;
  icon: any;
  requiresCoBrand?: boolean;
  requiresPropertyData?: boolean;
}

const TEMPLATES: TemplateOption[] = [
  {
    id: 'biolink',
    type: 'biolink',
    name: 'Bio Link Page',
    description: 'Personal landing page with links to all your resources',
    icon: Link2,
    requiresCoBrand: false,
  },
  {
    id: 'prequal',
    type: 'prequal',
    name: 'Pre-Qualification Page',
    description: 'Co-branded page for pre-qualification applications',
    icon: FileText,
    requiresCoBrand: true,
  },
  {
    id: 'openhouse',
    type: 'openhouse',
    name: 'Open House Page',
    description: 'Co-branded page for specific property open house',
    icon: Home,
    requiresCoBrand: true,
    requiresPropertyData: true,
  },
  {
    id: 'calculator',
    type: 'calculator',
    name: 'Mortgage Calculator',
    description: 'Interactive mortgage payment calculator landing page',
    icon: Calculator,
    requiresCoBrand: false,
  },
  {
    id: 'valuation',
    type: 'valuation',
    name: 'Property Valuation',
    description: 'Property value estimator tool landing page',
    icon: Building2,
    requiresCoBrand: false,
  },
];

export function ContentGenerationWizard({
  isOpen,
  onClose,
  onSuccess,
  userRole,
  currentUserId,
}: ContentGenerationWizardProps) {
  const [step, setStep] = useState(1);
  const [pageType, setPageType] = useState<'single' | 'cobranded'>('single');
  const [selectedTemplate, setSelectedTemplate] = useState<TemplateOption | null>(null);
  const [selectedPartner, setSelectedPartner] = useState<Partner | null>(null);
  const [partners, setPartners] = useState<Partner[]>([]);
  const [propertyAddress, setPropertyAddress] = useState('');
  const [propertyData, setPropertyData] = useState({
    price: '',
    bedrooms: '',
    bathrooms: '',
    sqft: '',
  });
  const [isLoadingPartners, setIsLoadingPartners] = useState(false);
  const [isGenerating, setIsGenerating] = useState(false);

  // Reset wizard when opened
  useEffect(() => {
    if (isOpen) {
      setStep(1);
      setPageType('single');
      setSelectedTemplate(null);
      setSelectedPartner(null);
      setPropertyAddress('');
      setPropertyData({ price: '', bedrooms: '', bathrooms: '', sqft: '' });
    }
  }, [isOpen]);

  // Load partners when needed
  useEffect(() => {
    if (isOpen && (step === 2 || (userRole === 'realtor_partner' && step === 1))) {
      loadPartners();
    }
  }, [isOpen, step, userRole]);

  const loadPartners = async () => {
    try {
      setIsLoadingPartners(true);

      // Fetch partnerships for current user
      const partnerships = await DataService.getPartnerships(currentUserId);

      // Extract partner users from partnerships
      const partnerUsers = partnerships.map((p: any) => {
        // For loan officers, get realtor partners
        // For realtors, get loan officer partners
        const isLoanOfficer = userRole === 'loan_officer';
        const partnerId = isLoanOfficer ? p.realtor_id : p.loan_officer_id;
        const partnerName = isLoanOfficer ? p.realtor_name : p.loan_officer_name;

        return {
          id: partnerId,
          display_name: partnerName,
          email: p.partner_email || '',
          company: p.partner_company || '',
        };
      });

      setPartners(partnerUsers);
    } catch (error) {
      console.error('Failed to load partners:', error);
    } finally {
      setIsLoadingPartners(false);
    }
  };

  const getAvailableTemplates = () => {
    if (userRole === 'loan_officer' && pageType === 'single') {
      // Loan officers can create single pages - exclude co-brand required templates
      return TEMPLATES.filter(t => !t.requiresCoBrand);
    } else {
      // Co-branded pages or realtor portal - show all templates
      return TEMPLATES;
    }
  };

  const handleNext = () => {
    // Loan Officer flow
    if (userRole === 'loan_officer') {
      if (step === 1) {
        // Step 1: Choose single or co-branded
        if (pageType === 'cobranded') {
          setStep(2); // Go to partner selection
        } else {
          setStep(3); // Skip to template selection
        }
      } else if (step === 2) {
        // Step 2: Partner selected, go to template
        if (selectedPartner) {
          setStep(3);
        }
      } else if (step === 3) {
        // Step 3: Template selected, check if needs property data
        if (selectedTemplate?.requiresPropertyData) {
          setStep(4);
        } else {
          handleGenerate();
        }
      } else if (step === 4) {
        // Step 4: Property data entered, generate
        handleGenerate();
      }
    }
    // Realtor flow
    else if (userRole === 'realtor_partner') {
      if (step === 1) {
        // Step 1: Partner selected, go to template
        if (selectedPartner) {
          setStep(2);
        }
      } else if (step === 2) {
        // Step 2: Template selected, check if needs property data
        if (selectedTemplate?.requiresPropertyData) {
          setStep(3);
        } else {
          handleGenerate();
        }
      } else if (step === 3) {
        // Step 3: Property data entered, generate
        handleGenerate();
      }
    }
  };

  const handleBack = () => {
    if (step > 1) {
      // For realtors, skip back properly
      if (userRole === 'realtor_partner' && step === 3 && !selectedTemplate?.requiresPropertyData) {
        setStep(1);
      } else if (userRole === 'loan_officer' && step === 3 && pageType === 'single') {
        setStep(1);
      } else {
        setStep(step - 1);
      }
    }
  };

  const handleGenerate = async () => {
    if (!selectedTemplate) return;

    try {
      setIsGenerating(true);

      const endpoint = `/wp-json/lrh/v1/pages/generate/${selectedTemplate.type}`;
      const body: any = { user_id: currentUserId };

      // Add partner info for co-branded pages
      if (pageType === 'cobranded' || userRole === 'realtor_partner') {
        if (userRole === 'loan_officer') {
          body.loan_officer_id = currentUserId;
          body.realtor_id = selectedPartner?.id;
        } else {
          body.loan_officer_id = selectedPartner?.id;
          body.realtor_id = currentUserId;
        }
      }

      // Add property data for open house pages
      if (selectedTemplate.requiresPropertyData) {
        body.property_address = propertyAddress;
        body.property_data = propertyData;
      }

      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': (window as any).frsPortalConfig?.restNonce || '',
        },
        body: JSON.stringify(body),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to generate page');
      }

      const data = await response.json();
      const pageId = data.page?.id || data.page?.page_id;

      if (pageId) {
        onSuccess(pageId.toString());
        onClose();
      } else {
        throw new Error('No page ID returned');
      }
    } catch (error) {
      console.error('Generation failed:', error);
      alert(error instanceof Error ? error.message : 'Failed to generate page');
    } finally {
      setIsGenerating(false);
    }
  };

  const canProceed = () => {
    if (userRole === 'loan_officer') {
      if (step === 1) return true; // Can always proceed from step 1
      if (step === 2) return !!selectedPartner;
      if (step === 3) return !!selectedTemplate;
      if (step === 4) return !!propertyAddress;
    } else {
      if (step === 1) return !!selectedPartner;
      if (step === 2) return !!selectedTemplate;
      if (step === 3) return !!propertyAddress;
    }
    return false;
  };

  const renderStepContent = () => {
    // LOAN OFFICER FLOW
    if (userRole === 'loan_officer') {
      // Step 1: Choose Single or Co-branded
      if (step === 1) {
        return (
          <div className="space-y-6">
            <div className="text-center space-y-2">
              <Sparkles className="h-12 w-12 mx-auto text-[var(--brand-primary-blue)]" />
              <h3 className="text-2xl font-semibold text-[var(--brand-dark-navy)]">
                Create New Landing Page
              </h3>
              <p className="text-[var(--brand-slate)]">
                Choose whether to create a personal page or a co-branded page with a realtor partner
              </p>
            </div>

            <RadioGroup value={pageType} onValueChange={(v) => setPageType(v as any)}>
              <div className="grid grid-cols-2 gap-4">
                <label className={`cursor-pointer ${pageType === 'single' ? 'ring-2 ring-[var(--brand-primary-blue)]' : ''}`}>
                  <Card className="hover:shadow-lg transition-shadow">
                    <CardContent className="p-6 text-center space-y-3">
                      <RadioGroupItem value="single" id="single" className="sr-only" />
                      <User className="h-12 w-12 mx-auto text-[var(--brand-primary-blue)]" />
                      <div>
                        <h4 className="font-semibold text-[var(--brand-dark-navy)]">Personal Page</h4>
                        <p className="text-sm text-[var(--brand-slate)]">Just your brand</p>
                      </div>
                    </CardContent>
                  </Card>
                </label>

                <label className={`cursor-pointer ${pageType === 'cobranded' ? 'ring-2 ring-[var(--brand-primary-blue)]' : ''}`}>
                  <Card className="hover:shadow-lg transition-shadow">
                    <CardContent className="p-6 text-center space-y-3">
                      <RadioGroupItem value="cobranded" id="cobranded" className="sr-only" />
                      <Users className="h-12 w-12 mx-auto text-[var(--brand-rich-teal)]" />
                      <div>
                        <h4 className="font-semibold text-[var(--brand-dark-navy)]">Co-branded Page</h4>
                        <p className="text-sm text-[var(--brand-slate)]">With a realtor partner</p>
                      </div>
                    </CardContent>
                  </Card>
                </label>
              </div>
            </RadioGroup>
          </div>
        );
      }

      // Step 2: Select Partner (only if co-branded)
      if (step === 2 && pageType === 'cobranded') {
        return (
          <div className="space-y-6">
            <div className="text-center space-y-2">
              <Users className="h-12 w-12 mx-auto text-[var(--brand-rich-teal)]" />
              <h3 className="text-2xl font-semibold text-[var(--brand-dark-navy)]">
                Select Realtor Partner
              </h3>
              <p className="text-[var(--brand-slate)]">
                Choose which realtor to co-brand this page with
              </p>
            </div>

            {isLoadingPartners ? (
              <div className="flex justify-center py-12">
                <LoadingSpinner />
              </div>
            ) : partners.length === 0 ? (
              <Card>
                <CardContent className="text-center py-12">
                  <p className="text-[var(--brand-slate)]">
                    No realtor partnerships found. Create partnerships first.
                  </p>
                </CardContent>
              </Card>
            ) : (
              <div className="grid grid-cols-1 gap-3 max-h-[400px] overflow-y-auto">
                {partners.map((partner) => (
                  <Card
                    key={partner.id}
                    className={`cursor-pointer hover:shadow-lg transition-all ${
                      selectedPartner?.id === partner.id ? 'ring-2 ring-[var(--brand-primary-blue)]' : ''
                    }`}
                    onClick={() => setSelectedPartner(partner)}
                  >
                    <CardContent className="p-4 flex items-center space-x-4">
                      <div className="h-12 w-12 rounded-full bg-[var(--brand-pale-blue)] flex items-center justify-center text-[var(--brand-dark-navy)] font-semibold">
                        {partner.display_name.charAt(0)}
                      </div>
                      <div className="flex-1">
                        <h4 className="font-semibold text-[var(--brand-dark-navy)]">{partner.display_name}</h4>
                        <p className="text-sm text-[var(--brand-slate)]">{partner.email}</p>
                        {partner.company && (
                          <p className="text-xs text-[var(--brand-slate)]">{partner.company}</p>
                        )}
                      </div>
                      {selectedPartner?.id === partner.id && (
                        <Check className="h-5 w-5 text-[var(--brand-primary-blue)]" />
                      )}
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
        );
      }

      // Step 3: Template Selection
      if (step === 3) {
        return (
          <div className="space-y-6">
            <div className="text-center space-y-2">
              <FileText className="h-12 w-12 mx-auto text-[var(--brand-primary-blue)]" />
              <h3 className="text-2xl font-semibold text-[var(--brand-dark-navy)]">
                Choose Page Template
              </h3>
              <p className="text-[var(--brand-slate)]">
                Select the type of landing page you want to create
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {getAvailableTemplates().map((template) => {
                const Icon = template.icon;
                return (
                  <Card
                    key={template.id}
                    className={`cursor-pointer hover:shadow-lg transition-all ${
                      selectedTemplate?.id === template.id ? 'ring-2 ring-[var(--brand-primary-blue)]' : ''
                    }`}
                    onClick={() => setSelectedTemplate(template)}
                  >
                    <CardContent className="p-6 text-center space-y-3">
                      <Icon className="h-10 w-10 mx-auto text-[var(--brand-primary-blue)]" />
                      <div>
                        <h4 className="font-semibold text-[var(--brand-dark-navy)]">{template.name}</h4>
                        <p className="text-sm text-[var(--brand-slate)]">{template.description}</p>
                      </div>
                      {selectedTemplate?.id === template.id && (
                        <Check className="h-5 w-5 mx-auto text-[var(--brand-primary-blue)]" />
                      )}
                    </CardContent>
                  </Card>
                );
              })}
            </div>
          </div>
        );
      }

      // Step 4: Property Data (only for open house)
      if (step === 4 && selectedTemplate?.requiresPropertyData) {
        return (
          <div className="space-y-6">
            <div className="text-center space-y-2">
              <Home className="h-12 w-12 mx-auto text-[var(--brand-primary-blue)]" />
              <h3 className="text-2xl font-semibold text-[var(--brand-dark-navy)]">
                Property Details
              </h3>
              <p className="text-[var(--brand-slate)]">
                Enter the property information for this open house page
              </p>
            </div>

            <div className="space-y-4">
              <div>
                <Label htmlFor="address">Property Address *</Label>
                <Input
                  id="address"
                  value={propertyAddress}
                  onChange={(e) => setPropertyAddress(e.target.value)}
                  placeholder="123 Main St, City, State 12345"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="price">Price</Label>
                  <Input
                    id="price"
                    value={propertyData.price}
                    onChange={(e) => setPropertyData({ ...propertyData, price: e.target.value })}
                    placeholder="$500,000"
                  />
                </div>
                <div>
                  <Label htmlFor="sqft">Square Feet</Label>
                  <Input
                    id="sqft"
                    value={propertyData.sqft}
                    onChange={(e) => setPropertyData({ ...propertyData, sqft: e.target.value })}
                    placeholder="2,500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="bedrooms">Bedrooms</Label>
                  <Input
                    id="bedrooms"
                    value={propertyData.bedrooms}
                    onChange={(e) => setPropertyData({ ...propertyData, bedrooms: e.target.value })}
                    placeholder="3"
                  />
                </div>
                <div>
                  <Label htmlFor="bathrooms">Bathrooms</Label>
                  <Input
                    id="bathrooms"
                    value={propertyData.bathrooms}
                    onChange={(e) => setPropertyData({ ...propertyData, bathrooms: e.target.value })}
                    placeholder="2"
                  />
                </div>
              </div>
            </div>
          </div>
        );
      }
    }

    // REALTOR FLOW
    if (userRole === 'realtor_partner') {
      // Step 1: Select Loan Officer Partner
      if (step === 1) {
        return (
          <div className="space-y-6">
            <div className="text-center space-y-2">
              <Users className="h-12 w-12 mx-auto text-[var(--brand-rich-teal)]" />
              <h3 className="text-2xl font-semibold text-[var(--brand-dark-navy)]">
                Select Loan Officer Partner
              </h3>
              <p className="text-[var(--brand-slate)]">
                Choose which loan officer to co-brand this page with
              </p>
            </div>

            {isLoadingPartners ? (
              <div className="flex justify-center py-12">
                <LoadingSpinner />
              </div>
            ) : partners.length === 0 ? (
              <Card>
                <CardContent className="text-center py-12">
                  <p className="text-[var(--brand-slate)]">
                    No loan officer partnerships found. Create partnerships first.
                  </p>
                </CardContent>
              </Card>
            ) : (
              <div className="grid grid-cols-1 gap-3 max-h-[400px] overflow-y-auto">
                {partners.map((partner) => (
                  <Card
                    key={partner.id}
                    className={`cursor-pointer hover:shadow-lg transition-all ${
                      selectedPartner?.id === partner.id ? 'ring-2 ring-[var(--brand-primary-blue)]' : ''
                    }`}
                    onClick={() => setSelectedPartner(partner)}
                  >
                    <CardContent className="p-4 flex items-center space-x-4">
                      <div className="h-12 w-12 rounded-full bg-[var(--brand-pale-blue)] flex items-center justify-center text-[var(--brand-dark-navy)] font-semibold">
                        {partner.display_name.charAt(0)}
                      </div>
                      <div className="flex-1">
                        <h4 className="font-semibold text-[var(--brand-dark-navy)]">{partner.display_name}</h4>
                        <p className="text-sm text-[var(--brand-slate)]">{partner.email}</p>
                        {partner.company && (
                          <p className="text-xs text-[var(--brand-slate)]">{partner.company}</p>
                        )}
                      </div>
                      {selectedPartner?.id === partner.id && (
                        <Check className="h-5 w-5 text-[var(--brand-primary-blue)]" />
                      )}
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </div>
        );
      }

      // Step 2: Template Selection
      if (step === 2) {
        return (
          <div className="space-y-6">
            <div className="text-center space-y-2">
              <FileText className="h-12 w-12 mx-auto text-[var(--brand-primary-blue)]" />
              <h3 className="text-2xl font-semibold text-[var(--brand-dark-navy)]">
                Choose Page Template
              </h3>
              <p className="text-[var(--brand-slate)]">
                Select the type of co-branded landing page to create
              </p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {TEMPLATES.map((template) => {
                const Icon = template.icon;
                return (
                  <Card
                    key={template.id}
                    className={`cursor-pointer hover:shadow-lg transition-all ${
                      selectedTemplate?.id === template.id ? 'ring-2 ring-[var(--brand-primary-blue)]' : ''
                    }`}
                    onClick={() => setSelectedTemplate(template)}
                  >
                    <CardContent className="p-6 text-center space-y-3">
                      <Icon className="h-10 w-10 mx-auto text-[var(--brand-primary-blue)]" />
                      <div>
                        <h4 className="font-semibold text-[var(--brand-dark-navy)]">{template.name}</h4>
                        <p className="text-sm text-[var(--brand-slate)]">{template.description}</p>
                      </div>
                      {selectedTemplate?.id === template.id && (
                        <Check className="h-5 w-5 mx-auto text-[var(--brand-primary-blue)]" />
                      )}
                    </CardContent>
                  </Card>
                );
              })}
            </div>
          </div>
        );
      }

      // Step 3: Property Data (only for open house)
      if (step === 3 && selectedTemplate?.requiresPropertyData) {
        return (
          <div className="space-y-6">
            <div className="text-center space-y-2">
              <Home className="h-12 w-12 mx-auto text-[var(--brand-primary-blue)]" />
              <h3 className="text-2xl font-semibold text-[var(--brand-dark-navy)]">
                Property Details
              </h3>
              <p className="text-[var(--brand-slate)]">
                Enter the property information for this open house page
              </p>
            </div>

            <div className="space-y-4">
              <div>
                <Label htmlFor="address">Property Address *</Label>
                <Input
                  id="address"
                  value={propertyAddress}
                  onChange={(e) => setPropertyAddress(e.target.value)}
                  placeholder="123 Main St, City, State 12345"
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="price">Price</Label>
                  <Input
                    id="price"
                    value={propertyData.price}
                    onChange={(e) => setPropertyData({ ...propertyData, price: e.target.value })}
                    placeholder="$500,000"
                  />
                </div>
                <div>
                  <Label htmlFor="sqft">Square Feet</Label>
                  <Input
                    id="sqft"
                    value={propertyData.sqft}
                    onChange={(e) => setPropertyData({ ...propertyData, sqft: e.target.value })}
                    placeholder="2,500"
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="bedrooms">Bedrooms</Label>
                  <Input
                    id="bedrooms"
                    value={propertyData.bedrooms}
                    onChange={(e) => setPropertyData({ ...propertyData, bedrooms: e.target.value })}
                    placeholder="3"
                  />
                </div>
                <div>
                  <Label htmlFor="bathrooms">Bathrooms</Label>
                  <Input
                    id="bathrooms"
                    value={propertyData.bathrooms}
                    onChange={(e) => setPropertyData({ ...propertyData, bathrooms: e.target.value })}
                    placeholder="2"
                  />
                </div>
              </div>
            </div>
          </div>
        );
      }
    }

    return null;
  };

  const getTotalSteps = () => {
    if (userRole === 'loan_officer') {
      if (pageType === 'single') {
        return selectedTemplate?.requiresPropertyData ? 2 : 1;
      } else {
        return selectedTemplate?.requiresPropertyData ? 4 : 3;
      }
    } else {
      return selectedTemplate?.requiresPropertyData ? 3 : 2;
    }
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-6xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <div className="flex items-center justify-between">
            <DialogTitle className="text-2xl">Content Generation Wizard</DialogTitle>
            <Button variant="ghost" size="sm" onClick={onClose}>
              <X className="h-4 w-4" />
            </Button>
          </div>
          <DialogDescription>
            Step {step} of {getTotalSteps()}
          </DialogDescription>
        </DialogHeader>

        <div className="py-6">
          {renderStepContent()}
        </div>

        {/* Footer Actions */}
        <div className="flex justify-between items-center pt-6 border-t">
          <Button
            variant="outline"
            onClick={handleBack}
            disabled={step === 1 || isGenerating}
          >
            <ArrowLeft className="h-4 w-4 mr-2" />
            Back
          </Button>

          <div className="flex space-x-2">
            <Button variant="ghost" onClick={onClose} disabled={isGenerating}>
              Cancel
            </Button>

            {step === getTotalSteps() || (step === 3 && userRole === 'loan_officer' && !selectedTemplate?.requiresPropertyData) || (step === 2 && userRole === 'realtor_partner' && !selectedTemplate?.requiresPropertyData) ? (
              <Button
                onClick={handleGenerate}
                disabled={!canProceed() || isGenerating}
                className="brand-button brand-button-primary"
              >
                {isGenerating ? (
                  <>
                    <LoadingSpinner className="h-4 w-4 mr-2" />
                    Generating...
                  </>
                ) : (
                  <>
                    <Sparkles className="h-4 w-4 mr-2" />
                    Generate Page
                  </>
                )}
              </Button>
            ) : (
              <Button
                onClick={handleNext}
                disabled={!canProceed()}
                className="brand-button brand-button-primary"
              >
                Next
                <ArrowRight className="h-4 w-4 ml-2" />
              </Button>
            )}
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}