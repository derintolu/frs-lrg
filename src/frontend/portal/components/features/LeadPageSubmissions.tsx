import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Badge } from '../ui/badge';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table';
import {
  Search,
  Download,
  Phone,
  Mail,
  User,
  Home,
  Star,
  Calendar,
  FileText,
  Filter,
  Plus,
  CheckCircle,
  XCircle,
  Clock,
  Loader2,
  ExternalLink
} from 'lucide-react';
import { DataService, LeadPageSubmission } from '../../utils/dataService';
import { PageHeader } from '../loan-officer-portal/PageHeader';

interface LeadPageSubmissionsProps {
  userId: string;
  userRole?: 'loan_officer' | 'realtor';
}

export function LeadPageSubmissions({ userId, userRole = 'loan_officer' }: LeadPageSubmissionsProps) {
  const [submissions, setSubmissions] = useState<LeadPageSubmission[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchTerm, setSearchTerm] = useState('');
  const [statusFilter, setStatusFilter] = useState('all');
  const [typeFilter, setTypeFilter] = useState('all');

  useEffect(() => {
    const loadData = async () => {
      if (!userId) return;

      try {
        setLoading(true);
        const data = userRole === 'loan_officer'
          ? await DataService.getLeadPageSubmissionsForLO(userId)
          : await DataService.getLeadPageSubmissionsForRealtor(userId);
        setSubmissions(data);
      } catch (error) {
        console.error('Failed to load submissions:', error);
      } finally {
        setLoading(false);
      }
    };

    loadData();
  }, [userId, userRole]);

  const getPageTypeIcon = (type: string) => {
    switch (type) {
      case 'open_house':
        return <Home className="h-4 w-4 text-blue-600" />;
      case 'customer_spotlight':
        return <Star className="h-4 w-4 text-purple-600" />;
      case 'event':
        return <Calendar className="h-4 w-4 text-green-600" />;
      default:
        return <FileText className="h-4 w-4 text-gray-600" />;
    }
  };

  const getStatusBadge = (status: string) => {
    const config: Record<string, { label: string; icon: any; className: string }> = {
      new: { label: 'New', icon: Plus, className: 'bg-blue-100 text-blue-800 border-blue-200 hover:bg-blue-200' },
      contacted: { label: 'Contacted', icon: Phone, className: 'bg-amber-100 text-amber-800 border-amber-200 hover:bg-amber-200' },
      qualified: { label: 'Qualified', icon: CheckCircle, className: 'bg-purple-100 text-purple-800 border-purple-200 hover:bg-purple-200' },
      closed: { label: 'Closed', icon: CheckCircle, className: 'bg-green-100 text-green-800 border-green-200 hover:bg-green-200' },
      lost: { label: 'Lost', icon: XCircle, className: 'bg-red-100 text-red-800 border-red-200 hover:bg-red-200' },
    };
    const { label, icon: Icon, className } = config[status] || config.new;
    return (
      <Badge className={`${className} border font-medium shadow-sm`}>
        <Icon className="h-3 w-3 mr-1" />
        {label}
      </Badge>
    );
  };

  // Filter submissions
  const filteredSubmissions = submissions.filter((sub) => {
    const matchesSearch =
      !searchTerm ||
      `${sub.firstName} ${sub.lastName}`.toLowerCase().includes(searchTerm.toLowerCase()) ||
      sub.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
      sub.leadPageTitle.toLowerCase().includes(searchTerm.toLowerCase());

    const matchesStatus = statusFilter === 'all' || sub.status === statusFilter;
    const matchesType = typeFilter === 'all' || sub.pageType === typeFilter;

    return matchesSearch && matchesStatus && matchesType;
  });

  const handleExport = () => {
    // Simple CSV export
    const headers = ['Name', 'Email', 'Phone', 'Page', 'Type', 'Status', 'Date'];
    const rows = filteredSubmissions.map((sub) => [
      `${sub.firstName} ${sub.lastName}`,
      sub.email,
      sub.phone,
      sub.leadPageTitle,
      sub.pageType,
      sub.status,
      new Date(sub.createdAt).toLocaleDateString(),
    ]);

    const csv = [headers.join(','), ...rows.map((row) => row.join(','))].join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `lead-page-submissions-${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <PageHeader
          icon={FileText}
          title="Lead Page Submissions"
          iconBgColor="linear-gradient(135deg, var(--brand-electric-blue) 0%, var(--brand-cyan) 100%)"
        />
        <div className="flex space-x-2">
          <Button variant="outline" onClick={handleExport} disabled={loading || submissions.length === 0}>
            <Download className="size-4 mr-2" />
            Export
          </Button>
          <Button
            onClick={() => window.location.href = '/wp-admin/post-new.php?post_type=frs_lead_page'}
            style={{ backgroundColor: 'var(--brand-electric-blue)' }}
            className="text-white border-0 hover:opacity-90 transition-opacity"
          >
            <Plus className="size-4 mr-2" />
            Create Page
          </Button>
        </div>
      </div>

      {/* Stats Summary */}
      <div className="grid gap-4 md:grid-cols-4">
        <Card className="border-gray-200 shadow-sm hover:shadow-md transition-shadow">
          <CardContent className="p-5">
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <p className="text-xs font-semibold text-gray-600 uppercase tracking-wide">Total Submissions</p>
                <p className="text-3xl font-bold text-gray-900">
                  {submissions.length}
                </p>
              </div>
              <div className="p-3 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl shadow-sm">
                <User className="h-6 w-6 text-gray-700" />
              </div>
            </div>
          </CardContent>
        </Card>
        <Card className="border-blue-200 shadow-sm hover:shadow-md transition-shadow bg-gradient-to-br from-blue-50/30 to-white">
          <CardContent className="p-5">
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <p className="text-xs font-semibold text-blue-700 uppercase tracking-wide">New Leads</p>
                <p className="text-3xl font-bold text-blue-900">
                  {submissions.filter((s) => s.status === 'new').length}
                </p>
              </div>
              <div className="p-3 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl shadow-sm">
                <Plus className="h-6 w-6 text-blue-700" />
              </div>
            </div>
          </CardContent>
        </Card>
        <Card className="border-amber-200 shadow-sm hover:shadow-md transition-shadow bg-gradient-to-br from-amber-50/30 to-white">
          <CardContent className="p-5">
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <p className="text-xs font-semibold text-amber-700 uppercase tracking-wide">Contacted</p>
                <p className="text-3xl font-bold text-amber-900">
                  {submissions.filter((s) => s.status === 'contacted').length}
                </p>
              </div>
              <div className="p-3 bg-gradient-to-br from-amber-100 to-amber-200 rounded-xl shadow-sm">
                <Phone className="h-6 w-6 text-amber-700" />
              </div>
            </div>
          </CardContent>
        </Card>
        <Card className="border-green-200 shadow-sm hover:shadow-md transition-shadow bg-gradient-to-br from-green-50/30 to-white">
          <CardContent className="p-5">
            <div className="flex items-center justify-between">
              <div className="space-y-1">
                <p className="text-xs font-semibold text-green-700 uppercase tracking-wide">Closed Won</p>
                <p className="text-3xl font-bold text-green-900">
                  {submissions.filter((s) => s.status === 'closed').length}
                </p>
              </div>
              <div className="p-3 bg-gradient-to-br from-green-100 to-green-200 rounded-xl shadow-sm">
                <CheckCircle className="h-6 w-6 text-green-700" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Filters */}
      <Card className="border-gray-200 shadow-sm">
        <CardContent className="pt-6">
          <div className="flex flex-wrap gap-4 items-center">
            <div className="flex-1 min-w-[250px] max-w-[400px]">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <Input
                  placeholder="Search by name, email, or page..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-10 h-10 border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                />
              </div>
            </div>

            <div className="flex items-center gap-2">
              <Filter className="h-4 w-4 text-gray-500" />
              <Select value={statusFilter} onValueChange={setStatusFilter}>
                <SelectTrigger className="w-[150px] h-10 border-gray-300">
                  <SelectValue placeholder="Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="new">New</SelectItem>
                  <SelectItem value="contacted">Contacted</SelectItem>
                  <SelectItem value="qualified">Qualified</SelectItem>
                  <SelectItem value="closed">Closed</SelectItem>
                  <SelectItem value="lost">Lost</SelectItem>
                </SelectContent>
              </Select>

              <Select value={typeFilter} onValueChange={setTypeFilter}>
                <SelectTrigger className="w-[180px] h-10 border-gray-300">
                  <SelectValue placeholder="Page Type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Types</SelectItem>
                  <SelectItem value="open_house">Open House</SelectItem>
                  <SelectItem value="customer_spotlight">Customer Spotlight</SelectItem>
                  <SelectItem value="event">Event</SelectItem>
                  <SelectItem value="general">General</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Submissions Table */}
      <Card className="border-gray-200 shadow-sm">
        <CardHeader className="border-b border-gray-100 pb-4">
          <div className="flex items-center justify-between">
            <div>
              <CardTitle className="text-xl font-bold text-gray-900">
                Submissions ({filteredSubmissions.length})
              </CardTitle>
              <CardDescription className="mt-1">
                Leads captured from your Generation Station landing pages
              </CardDescription>
            </div>
            {filteredSubmissions.length > 0 && (
              <Badge variant="secondary" className="text-sm px-3 py-1">
                {filteredSubmissions.length} {filteredSubmissions.length === 1 ? 'Result' : 'Results'}
              </Badge>
            )}
          </div>
        </CardHeader>
        <CardContent className="p-0">
          {loading ? (
            <div className="flex items-center justify-center py-16">
              <div className="text-center space-y-3">
                <Loader2 className="h-10 w-10 animate-spin mx-auto text-blue-600" />
                <p className="text-sm font-medium text-gray-600">Loading submissions...</p>
              </div>
            </div>
          ) : filteredSubmissions.length === 0 ? (
            <div className="flex items-center justify-center py-16 px-4">
              <div className="text-center space-y-3 max-w-md">
                <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100">
                  <User className="h-8 w-8 text-gray-400" />
                </div>
                <div>
                  <p className="font-semibold text-gray-900 text-lg mb-1">No submissions found</p>
                  <p className="text-sm text-gray-500">
                    {submissions.length === 0
                      ? 'Create a lead page to start capturing leads from your audience'
                      : 'Try adjusting your search or filter criteria'}
                  </p>
                </div>
                {submissions.length === 0 && (
                  <Button
                    className="mt-2 text-white"
                    style={{ backgroundColor: 'var(--brand-electric-blue)' }}
                    onClick={() => window.location.href = '/wp-admin/post-new.php?post_type=frs_lead_page'}
                  >
                    <Plus className="h-4 w-4 mr-2" />
                    Create Lead Page
                  </Button>
                )}
              </div>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow className="bg-gray-50 hover:bg-gray-50">
                    <TableHead className="font-semibold text-gray-700">Lead</TableHead>
                    <TableHead className="font-semibold text-gray-700">Contact</TableHead>
                    <TableHead className="font-semibold text-gray-700">Source Page</TableHead>
                    <TableHead className="font-semibold text-gray-700">Status</TableHead>
                    <TableHead className="font-semibold text-gray-700">Date</TableHead>
                    <TableHead className="font-semibold text-gray-700 text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredSubmissions.map((sub) => (
                    <TableRow key={sub.id} className="hover:bg-gray-50/50 transition-colors border-b border-gray-100">
                      <TableCell className="py-4">
                        <div className="space-y-1">
                          <div className="font-semibold text-gray-900">
                            {sub.firstName} {sub.lastName}
                          </div>
                          {sub.message && (
                            <div className="text-xs text-gray-500 truncate max-w-[200px] bg-gray-50 px-2 py-1 rounded">
                              {sub.message}
                            </div>
                          )}
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="space-y-1">
                          <div className="text-sm text-gray-900 flex items-center gap-1.5">
                            <Mail className="h-3.5 w-3.5 text-gray-400" />
                            {sub.email || <span className="text-gray-400">No email</span>}
                          </div>
                          <div className="text-sm text-gray-600 flex items-center gap-1.5">
                            <Phone className="h-3.5 w-3.5 text-gray-400" />
                            {sub.phone || <span className="text-gray-400">No phone</span>}
                          </div>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex items-center gap-2 px-2.5 py-1.5 bg-gray-50 rounded-lg w-fit">
                          {getPageTypeIcon(sub.pageType)}
                          <span className="text-sm font-medium text-gray-700 truncate max-w-[150px]">
                            {sub.leadPageTitle}
                          </span>
                        </div>
                      </TableCell>
                      <TableCell className="py-4">{getStatusBadge(sub.status)}</TableCell>
                      <TableCell className="py-4">
                        <div className="flex items-center gap-1.5 text-sm text-gray-600">
                          <Clock className="h-3.5 w-3.5 text-gray-400" />
                          {new Date(sub.createdAt).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                          })}
                        </div>
                      </TableCell>
                      <TableCell className="py-4">
                        <div className="flex justify-end gap-1">
                          <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 hover:bg-blue-50 hover:text-blue-600"
                            title="Send email"
                            onClick={() => sub.email && window.open(`mailto:${sub.email}`, '_blank')}
                            disabled={!sub.email}
                          >
                            <Mail className="h-4 w-4" />
                          </Button>
                          <Button
                            variant="ghost"
                            size="icon"
                            className="h-8 w-8 hover:bg-green-50 hover:text-green-600"
                            title="Call"
                            onClick={() => sub.phone && window.open(`tel:${sub.phone}`, '_blank')}
                            disabled={!sub.phone}
                          >
                            <Phone className="h-4 w-4" />
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
