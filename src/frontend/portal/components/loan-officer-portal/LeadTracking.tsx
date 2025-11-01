import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Badge } from '../ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table';
import { Switch } from '../ui/switch';
import { Label } from '../ui/label';
import { Separator } from '../ui/separator';
import {
  Search,
  Filter,
  Download,
  Plus,
  Phone,
  Mail,
  Calendar,
  DollarSign,
  TrendingUp,
  User,
  MapPin,
  Clock,
  Settings,
  CheckCircle,
  Users,
  Zap,
  XCircle
} from 'lucide-react';
// import { useLeads } from '../../hooks/useLeads'; // To be implemented
import { DataService } from '../../utils/dataService';
import { PageHeader } from './PageHeader';

interface LeadTrackingProps {
  userId: string;
}

export function LeadTracking({ userId }: LeadTrackingProps) {
  const [activeTab, setActiveTab] = useState('leads');
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [sourceFilter, setSourceFilter] = useState('all');
  const [emailEnabled, setEmailEnabled] = useState(true);
  const [emailUsername] = useState('holley@21stcenturylending.com');
  const [autoForward, setAutoForward] = useState(true);

  // Real leads data from API
  const [leads, setLeads] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);

  // Load leads data on component mount
  useEffect(() => {
    const loadLeads = async () => {
      if (!userId) return;

      try {
        setLoading(true);
        const leadsData = await DataService.getLeadsForLO(userId);
        setLeads(leadsData || []);
      } catch (err) {
        console.error('Failed to load leads:', err);
        setLeads([]);
      } finally {
        setLoading(false);
      }
    };

    loadLeads();
  }, [userId]);

  const refetch = async () => {
    if (!userId) return;
    try {
      const leadsData = await DataService.getLeadsForLO(userId);
      setLeads(leadsData || []);
    } catch (err) {
      console.error('Failed to refresh leads:', err);
    }
  };

  // Status badge function matching the live table format
  const getLeadStatusBadge = (status: 'new' | 'contacted' | 'qualified' | 'closed' | 'lost' | string) => {
    const statusConfig = {
      new: { label: 'New', color: 'bg-blue-600' },
      contacted: { label: 'Contacted', color: 'bg-yellow-600' },
      qualified: { label: 'Qualified', color: 'bg-purple-600' },
      closed: { label: 'Closed', color: 'bg-green-600' },
      lost: { label: 'Lost', color: 'bg-red-600' }
    };

    const config = statusConfig[status as keyof typeof statusConfig] || statusConfig.new;
    const icons = {
      new: Plus,
      contacted: Phone,
      qualified: CheckCircle,
      closed: CheckCircle,
      lost: XCircle
    };
    const IconComponent = icons[status as keyof typeof icons] || Plus;

    return (
      <Badge className={`${config.color} text-white flex items-center space-x-1`}>
        <IconComponent className="h-3 w-3" />
        <span>{config.label}</span>
      </Badge>
    );
  };

  const statusCounts = {
    all: leads.length,
    hot: leads.filter(l => l.status === 'hot').length,
    warm: leads.filter(l => l.status === 'warm').length,
    cold: leads.filter(l => l.status === 'cold').length,
    contacted: leads.filter(l => l.status === 'contacted').length,
    qualified: leads.filter(l => l.status === 'qualified').length,
    closed: leads.filter(l => l.status === 'closed').length,
  };

  // Export functionality
  const handleExport = async () => {
    try {
      const blob = await LoanOfficerDataService.exportLeads();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `leads-${new Date().toISOString()}.csv`;
      a.click();
      window.URL.revokeObjectURL(url);
    } catch (error) {
      console.error('Export failed:', error);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <PageHeader
          icon={Users}
          title="Lead Tracking"
          iconBgColor="linear-gradient(135deg, #3b82f6 0%, #2DD4DA 100%)"
        />
        <div className="flex space-x-2">
          <Button variant="outline" onClick={handleExport} disabled={loading}>
            <Download className="size-4 mr-2" />
            Export
          </Button>
          <Button
            style={{ backgroundColor: 'var(--brand-electric-blue)' }}
            className="text-white border-0 hover:opacity-90 transition-opacity"
          >
            <Plus className="size-4 mr-2" />
            Add Lead
          </Button>
        </div>
      </div>

      <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
        <TabsList className="grid w-full grid-cols-2">
          <TabsTrigger value="leads">Leads</TabsTrigger>
          <TabsTrigger value="integrations">Integrations</TabsTrigger>
        </TabsList>

        <TabsContent value="leads" className="space-y-6">

      {/* Filters */}
      <Card>
        <CardContent className="pt-6">
          <div className="flex flex-wrap gap-4 max-w-full">
            <div className="flex-1 min-w-[200px] max-w-[400px]">
              <div className="relative">
                <Search className="absolute left-2 top-2.5 size-4 text-muted-foreground" />
                <Input
                  placeholder="Search leads..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-8"
                />
              </div>
            </div>

            <Select value={statusFilter} onValueChange={setStatusFilter}>
              <SelectTrigger className="w-[120px]">
                <SelectValue placeholder="Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="hot">Hot</SelectItem>
                <SelectItem value="warm">Warm</SelectItem>
                <SelectItem value="cold">Cold</SelectItem>
                <SelectItem value="contacted">Contacted</SelectItem>
                <SelectItem value="qualified">Qualified</SelectItem>
                <SelectItem value="closed">Closed</SelectItem>
              </SelectContent>
            </Select>

            <Select value={sourceFilter} onValueChange={setSourceFilter}>
              <SelectTrigger className="w-[140px]">
                <SelectValue placeholder="Source" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Sources</SelectItem>
                <SelectItem value="Biolink">Biolink</SelectItem>
                <SelectItem value="First Time Buyer Landing Page">First Time Buyer</SelectItem>
                <SelectItem value="Investment Property Landing Page">Investment Property</SelectItem>
                <SelectItem value="VA Loan Landing Page">VA Loan</SelectItem>
                <SelectItem value="Refinance Landing Page">Refinance</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </CardContent>
      </Card>



      {/* Leads Table */}
      <Card>
        <CardHeader>
          <CardTitle>Leads ({leads.length})</CardTitle>
          <CardDescription>Detailed view of all your leads and their status</CardDescription>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="flex items-center justify-center py-12">
              <div className="text-center space-y-2">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                <p className="text-sm text-muted-foreground">Loading leads...</p>
              </div>
            </div>
          ) : leads.length === 0 ? (
            <div className="flex items-center justify-center py-12">
              <div className="text-center space-y-2">
                <User className="size-12 text-muted-foreground mx-auto" />
                <p className="text-sm text-muted-foreground">No leads found</p>
              </div>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Lead Name</TableHead>
                  <TableHead>Contact</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Loan Amount</TableHead>
                  <TableHead>Source</TableHead>
                  <TableHead>Created</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {leads.map((lead) => (
                  <TableRow key={lead.id}>
                    <TableCell>
                      <div>
                        <div className="font-medium text-[var(--brand-dark-navy)]">
                          {lead.firstName && lead.lastName
                            ? `${lead.firstName} ${lead.lastName}`
                            : lead.name || 'Unknown Lead'}
                        </div>
                        <div className="text-sm text-[var(--brand-slate)]">{lead.propertyType || lead.notes?.substring(0, 30)}</div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div>
                        <div className="text-sm text-[var(--brand-slate)]">{lead.email || 'No email'}</div>
                        <div className="text-sm text-[var(--brand-slate)]">{lead.phone || 'No phone'}</div>
                      </div>
                    </TableCell>
                    <TableCell>{getLeadStatusBadge(lead.status)}</TableCell>
                    <TableCell className="text-[var(--brand-slate)]">
                      {lead.loanAmount
                        ? `$${lead.loanAmount.toLocaleString()}`
                        : lead.loan_amount
                        ? `$${lead.loan_amount.toLocaleString()}`
                        : 'N/A'}
                    </TableCell>
                    <TableCell className="text-[var(--brand-slate)]">{lead.source || 'Unknown'}</TableCell>
                    <TableCell className="text-[var(--brand-slate)]">
                      {lead.createdAt
                        ? new Date(lead.createdAt).toLocaleDateString()
                        : lead.created_date
                        ? new Date(lead.created_date).toLocaleDateString()
                        : 'N/A'}
                    </TableCell>
                    <TableCell>
                      <div className="flex space-x-2">
                        <Button variant="ghost" size="icon" title="Send email" onClick={() => lead.email && window.open(`mailto:${lead.email}`, '_blank')}>
                          <Mail className="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" title="Call lead" onClick={() => lead.phone && window.open(`tel:${lead.phone}`, '_blank')}>
                          <Phone className="h-4 w-4" />
                        </Button>
                        <Button variant="ghost" size="icon" title="View details">
                          <User className="h-4 w-4" />
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          )}
        </CardContent>
      </Card>
        </TabsContent>

        <TabsContent value="integrations" className="space-y-6">
          <div className="grid gap-6 md:grid-cols-2">
            {/* Email Notifications */}
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-3">
                    <div
                      className="size-10 rounded-lg flex items-center justify-center"
                      style={{ backgroundColor: 'var(--brand-electric-blue)' }}
                    >
                      <Mail className="size-5 text-white" />
                    </div>
                    <div>
                      <CardTitle style={{ color: 'var(--brand-navy)' }}>Email Notifications</CardTitle>
                      <CardDescription>Manage lead notification settings</CardDescription>
                    </div>
                  </div>
                  <Badge
                    style={{ backgroundColor: 'var(--brand-cyan)', color: 'white', border: 'none' }}
                  >
                    <CheckCircle className="size-3 mr-1" />
                    Active
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-4">
                  <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                    <div>
                      <Label htmlFor="primary-email" className="font-medium">Primary Email</Label>
                      <p className="text-sm text-muted-foreground">{emailUsername}</p>
                    </div>
                    <Badge variant="outline">Primary</Badge>
                  </div>

                  <Separator />

                  <div className="space-y-3">
                    <div className="flex items-center justify-between">
                      <div>
                        <Label htmlFor="new-lead-notify">New Lead Notifications</Label>
                        <p className="text-xs text-muted-foreground">Get notified when new leads are submitted</p>
                      </div>
                      <Switch
                        id="new-lead-notify"
                        checked={emailEnabled}
                        onCheckedChange={setEmailEnabled}
                      />
                    </div>

                    <div className="flex items-center justify-between">
                      <div>
                        <Label htmlFor="auto-forward">Auto-forward Leads</Label>
                        <p className="text-xs text-muted-foreground">Automatically forward new leads to your email</p>
                      </div>
                      <Switch
                        id="auto-forward"
                        checked={autoForward}
                        onCheckedChange={setAutoForward}
                        disabled={!emailEnabled}
                      />
                    </div>
                  </div>
                </div>

                <div className="flex space-x-2 pt-4">
                  <Button
                    className="flex-1 text-white border-0 hover:opacity-90 transition-opacity"
                    style={{ backgroundColor: 'var(--brand-electric-blue)' }}
                  >
                    Save Settings
                  </Button>
                </div>
              </CardContent>
            </Card>

            {/* Follow Up Boss */}
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-3">
                    <div
                      className="size-10 rounded-lg flex items-center justify-center"
                      style={{ backgroundColor: 'var(--brand-cyan)' }}
                    >
                      <Users className="size-5 text-white" />
                    </div>
                    <div>
                      <CardTitle style={{ color: 'var(--brand-navy)' }}>Follow Up Boss</CardTitle>
                      <CardDescription>Complete CRM solution for real estate professionals</CardDescription>
                    </div>
                  </div>
                  <Badge
                    variant="secondary"
                    style={{ backgroundColor: 'var(--brand-cyan)', color: 'white', border: 'none' }}
                  >
                    <Clock className="size-3 mr-1" />
                    Coming Soon
                  </Badge>
                </div>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="text-center py-8">
                  <div className="space-y-4">
                    <div className="space-y-2">
                      <h4 className="font-medium" style={{ color: 'var(--brand-navy)' }}>Features Coming Soon</h4>
                      <p className="text-sm text-muted-foreground">
                        Complete CRM integration with automated lead management and follow-ups
                      </p>
                    </div>

                    <div className="flex flex-wrap gap-2 justify-center">
                      {['Lead Management', 'Automated Follow-ups', 'Team Collaboration', 'Mobile App', 'Reporting'].map((feature, index) => (
                        <Badge
                          key={index}
                          variant="outline"
                          style={{
                            borderColor: 'var(--brand-primary-blue)',
                            color: 'var(--brand-primary-blue)'
                          }}
                        >
                          {feature}
                        </Badge>
                      ))}
                    </div>

                    <Button
                      disabled
                      className="mt-4 text-white border-0 hover:opacity-90 transition-opacity"
                      style={{ backgroundColor: 'var(--brand-cyan)' }}
                    >
                      <Zap className="size-4 mr-2" />
                      Notify Me When Available
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          {/* Integration Stats */}
          <Card>
            <CardHeader>
              <CardTitle style={{ color: 'var(--brand-navy)' }}>Integration Status</CardTitle>
              <CardDescription>Overview of your connected integrations</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="grid gap-4 md:grid-cols-3">
                <div className="text-center p-4 bg-muted/50 rounded-lg">
                  <div className="text-2xl font-bold" style={{ color: 'var(--brand-primary-blue)' }}>1</div>
                  <div className="text-sm text-muted-foreground">Connected</div>
                </div>
                <div className="text-center p-4 bg-muted/50 rounded-lg">
                  <div className="text-2xl font-bold" style={{ color: 'var(--brand-rich-teal)' }}>1</div>
                  <div className="text-sm text-muted-foreground">Coming Soon</div>
                </div>
                <div className="text-center p-4 bg-muted/50 rounded-lg">
                  <div className="text-2xl font-bold" style={{ color: 'var(--brand-navy)' }}>98%</div>
                  <div className="text-sm text-muted-foreground">Uptime</div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
