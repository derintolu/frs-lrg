import { useState, useEffect } from "react";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { CheckCircle2, Circle } from "lucide-react";

export default function DashboardPage() {
  const [stats, setStats] = useState({
    activePartnerships: 0,
    pendingInvitations: 0,
    totalLeads: 0,
    recentLeads: 0,
  });
  const [recentActivity, setRecentActivity] = useState({
    partnerships: [],
    leads: [],
  });
  const [userCounts, setUserCounts] = useState({
    loanOfficers: 0,
    realtors: 0,
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    try {
      const response = await fetch(`${window.lrhAdmin.apiUrl}dashboard/stats`, {
        headers: {
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
      });
      const data = await response.json();
      if (data.success) {
        setStats(data.data.stats);
        setRecentActivity(data.data.recentActivity);
        setUserCounts(data.data.userCounts);
      }
    } catch (error) {
      console.error("Error fetching dashboard data:", error);
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);

    if (diff < 60) return "just now";
    if (diff < 3600) return `${Math.floor(diff / 60)} min ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} hours ago`;
    if (diff < 604800) return `${Math.floor(diff / 86400)} days ago`;
    return date.toLocaleDateString();
  };

  return (
    <div className="flex-1 space-y-4 p-8 pt-6">
      <div className="flex items-center justify-between">
        <h2 className="text-3xl font-bold tracking-tight">Partnership Portal Dashboard</h2>
      </div>

      {/* Quick Stats */}
      <div className="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
              Active Partnerships
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold text-blue-600">
              {loading ? "..." : stats.activePartnerships}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
              Pending Invitations
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold text-blue-600">
              {loading ? "..." : stats.pendingInvitations}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
              Total Leads
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold text-blue-600">
              {loading ? "..." : stats.totalLeads}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardTitle className="text-sm font-medium text-muted-foreground">
              Leads This Week
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-3xl font-bold text-blue-600">
              {loading ? "..." : stats.recentLeads}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Quick Actions */}
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex gap-2 flex-wrap">
            <a href="#/partnerships" className="inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90">
              Manage Partnerships
            </a>
            <a href="#/integrations" className="inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground">
              Setup Integrations
            </a>
            <a href="#/bulk-invites" className="inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground">
              Bulk Invites
            </a>
            <a href="#/settings" className="inline-flex items-center justify-center rounded-md text-sm font-medium h-10 px-4 py-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground">
              Plugin Settings
            </a>
          </div>
        </CardContent>
      </Card>

      {/* User Overview */}
      <Card>
        <CardHeader>
          <CardTitle>User Overview</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium">Loan Officers</span>
              <div className="flex items-center gap-2">
                <span className="text-lg font-bold">{loading ? "..." : userCounts.loanOfficers}</span>
                <a href="/wp-admin/users.php?role=loan_officer" className="text-sm text-blue-600 hover:underline">
                  View Users
                </a>
              </div>
            </div>
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium">Realtor Partners</span>
              <div className="flex items-center gap-2">
                <span className="text-lg font-bold">{loading ? "..." : userCounts.realtors}</span>
                <a href="/wp-admin/users.php?role=realtor_partner" className="text-sm text-blue-600 hover:underline">
                  View Users
                </a>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Recent Activity */}
      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Recent Partnerships</CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <p className="text-sm text-muted-foreground">Loading...</p>
            ) : recentActivity.partnerships.length === 0 ? (
              <p className="text-sm text-muted-foreground">No partnerships yet.</p>
            ) : (
              <div className="space-y-3">
                {recentActivity.partnerships.map((partnership) => (
                  <div key={partnership.id} className="flex flex-col gap-1 p-2 bg-muted/50 rounded border-l-2 border-blue-500">
                    <div className="flex items-center justify-between">
                      <strong className="text-sm">{partnership.partner_name || partnership.partner_email}</strong>
                      <span className={`text-xs px-2 py-1 rounded ${
                        partnership.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                      }`}>
                        {partnership.status}
                      </span>
                    </div>
                    <p className="text-xs text-muted-foreground">
                      invited by {partnership.lo_name} • {formatDate(partnership.created_date)}
                    </p>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Recent Leads</CardTitle>
          </CardHeader>
          <CardContent>
            {loading ? (
              <p className="text-sm text-muted-foreground">Loading...</p>
            ) : recentActivity.leads.length === 0 ? (
              <p className="text-sm text-muted-foreground">No leads yet.</p>
            ) : (
              <div className="space-y-3">
                {recentActivity.leads.map((lead) => (
                  <div key={lead.id} className="flex flex-col gap-1 p-2 bg-muted/50 rounded border-l-2 border-blue-500">
                    <strong className="text-sm">{lead.first_name} {lead.last_name}</strong>
                    <p className="text-xs text-muted-foreground">
                      {lead.lo_name && `to ${lead.lo_name}`}
                      {lead.agent_name && ` from ${lead.agent_name}`}
                      {!lead.lo_name && !lead.agent_name && 'Direct submission'}
                      {' • '}
                      {formatDate(lead.created_date)}
                    </p>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Setup Checklist */}
      <Card>
        <CardHeader>
          <CardTitle>Setup Checklist</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-2">
            <div className="flex items-center gap-2">
              <CheckCircle2 className="h-5 w-5 text-green-600" />
              <span className="text-sm">Plugin installed and activated</span>
            </div>
            <div className="flex items-center gap-2">
              <Circle className="h-5 w-5 text-yellow-600" />
              <span className="text-sm">
                Configure integrations
                <a href="#/integrations" className="ml-2 text-blue-600 hover:underline">Configure</a>
              </span>
            </div>
            <div className="flex items-center gap-2">
              <Circle className="h-5 w-5 text-yellow-600" />
              <span className="text-sm">
                Create landing pages for loan officers
                <a href="/wp-admin/edit.php?post_type=frs_biolink" className="ml-2 text-blue-600 hover:underline">Create</a>
              </span>
            </div>
            <div className="flex items-center gap-2">
              <Circle className="h-5 w-5 text-yellow-600" />
              <span className="text-sm">
                Send partnership invitations
                <a href="#/partnerships" className="ml-2 text-blue-600 hover:underline">Manage</a>
              </span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
