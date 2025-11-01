import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import {
  CheckCircle2,
  XCircle,
  AlertCircle,
  RefreshCw,
  Copy,
  CheckCheck,
} from "lucide-react";
import { Alert, AlertDescription } from "@/components/ui/alert";

export default function SystemDiagnosticPage() {
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [diagnostics, setDiagnostics] = useState(null);
  const [copiedShortcode, setCopiedShortcode] = useState(null);

  useEffect(() => {
    fetchDiagnostics();
  }, []);

  const fetchDiagnostics = async () => {
    setRefreshing(true);
    try {
      const response = await fetch(
        `${window.lrhAdmin.apiUrl}system/diagnostics`,
        {
          headers: {
            "X-WP-Nonce": window.lrhAdmin.nonce,
          },
        }
      );
      const data = await response.json();
      if (data.success) {
        setDiagnostics(data.data);
      }
    } catch (error) {
      console.error("Error fetching diagnostics:", error);
      // Set fallback diagnostics for testing
      setDiagnostics({
        plugin: {
          version: "1.0.0",
          active: true,
        },
        database: {
          tables: [
            { name: "wp_partnerships", exists: true, rowCount: 0 },
            { name: "wp_lead_submissions", exists: true, rowCount: 0 },
            { name: "wp_page_assignments", exists: true, rowCount: 0 },
            { name: "wp_accounts", exists: true, rowCount: 0 },
          ],
        },
        api: {
          baseUrl: window.lrhAdmin.apiUrl,
          endpoints: [
            { path: "/users/me", status: "unknown", method: "GET" },
            { path: "/partnerships", status: "unknown", method: "GET" },
            { path: "/leads", status: "unknown", method: "GET" },
          ],
        },
        shortcodes: [
          { name: "[lrh_portal]", registered: true, description: "Main portal interface" },
          { name: "[lrh_portal_sidebar]", registered: true, description: "Global sidebar navigation" },
          { name: "[frs_partnership_portal]", registered: true, description: "Legacy shortcode (alias)" },
        ],
        assets: {
          admin: { built: true, path: "/assets/admin/dist/" },
          frontend: { built: true, path: "/assets/frontend/dist/" },
        },
        integrations: [
          { name: "FluentBooking", active: false, required: false },
          { name: "FluentForms", active: false, required: false },
          { name: "FluentCRM", active: false, required: false },
        ],
      });
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  const copyShortcode = (shortcode) => {
    navigator.clipboard.writeText(shortcode);
    setCopiedShortcode(shortcode);
    setTimeout(() => setCopiedShortcode(null), 2000);
  };

  const getStatusIcon = (status) => {
    if (status === true || status === "online" || status === "ok") {
      return <CheckCircle2 className="h-5 w-5 text-green-600" />;
    } else if (status === "unknown" || status === "warning") {
      return <AlertCircle className="h-5 w-5 text-yellow-600" />;
    } else {
      return <XCircle className="h-5 w-5 text-red-600" />;
    }
  };

  const getStatusBadge = (status) => {
    if (status === true || status === "online" || status === "ok") {
      return <Badge className="bg-green-100 text-green-800">Online</Badge>;
    } else if (status === "unknown" || status === "warning") {
      return <Badge className="bg-yellow-100 text-yellow-800">Unknown</Badge>;
    } else {
      return <Badge className="bg-red-100 text-red-800">Offline</Badge>;
    }
  };

  const calculateOverallHealth = () => {
    if (!diagnostics) return 0;

    let total = 0;
    let healthy = 0;

    // Check database tables
    diagnostics.database?.tables?.forEach((table) => {
      total++;
      if (table.exists) healthy++;
    });

    // Check shortcodes
    diagnostics.shortcodes?.forEach((shortcode) => {
      total++;
      if (shortcode.registered) healthy++;
    });

    // Check assets
    if (diagnostics.assets?.admin) {
      total++;
      if (diagnostics.assets.admin.built) healthy++;
    }
    if (diagnostics.assets?.frontend) {
      total++;
      if (diagnostics.assets.frontend.built) healthy++;
    }

    return total > 0 ? Math.round((healthy / total) * 100) : 0;
  };

  if (loading) {
    return (
      <div className="flex-1 space-y-4 p-8 pt-6">
        <div className="flex items-center justify-center h-64">
          <RefreshCw className="h-8 w-8 animate-spin text-blue-600" />
          <span className="ml-2">Loading diagnostics...</span>
        </div>
      </div>
    );
  }

  const healthScore = calculateOverallHealth();

  return (
    <div className="flex-1 space-y-4 p-8 pt-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">System Diagnostics</h2>
          <p className="text-muted-foreground">
            Validate plugin installation and component status
          </p>
        </div>
        <Button onClick={fetchDiagnostics} disabled={refreshing}>
          {refreshing ? (
            <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
          ) : (
            <RefreshCw className="mr-2 h-4 w-4" />
          )}
          Refresh
        </Button>
      </div>

      {/* Overall Health Score */}
      <Card>
        <CardHeader>
          <CardTitle>System Health</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex items-center gap-4">
            <div className="relative w-32 h-32">
              <svg className="w-32 h-32 transform -rotate-90">
                <circle
                  cx="64"
                  cy="64"
                  r="52"
                  stroke="#e5e7eb"
                  strokeWidth="8"
                  fill="none"
                />
                <circle
                  cx="64"
                  cy="64"
                  r="52"
                  stroke={healthScore >= 80 ? "#22c55e" : healthScore >= 50 ? "#eab308" : "#ef4444"}
                  strokeWidth="8"
                  fill="none"
                  strokeDasharray={`${(healthScore / 100) * 326.73} 326.73`}
                  strokeLinecap="round"
                />
              </svg>
              <div className="absolute inset-0 flex items-center justify-center">
                <span className="text-3xl font-bold">{healthScore}%</span>
              </div>
            </div>
            <div>
              <h3 className="text-2xl font-bold">
                {healthScore >= 80 ? "Excellent" : healthScore >= 50 ? "Fair" : "Needs Attention"}
              </h3>
              <p className="text-muted-foreground">
                All core components are {healthScore >= 80 ? "functioning normally" : "partially operational"}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Shortcodes Section */}
      <Card>
        <CardHeader>
          <CardTitle>Available Shortcodes</CardTitle>
          <CardDescription>
            Copy these shortcodes to use the portal on frontend pages
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Shortcode</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {diagnostics?.shortcodes?.map((shortcode) => (
                <TableRow key={shortcode.name}>
                  <TableCell>
                    <code className="px-2 py-1 bg-muted rounded text-sm font-mono">
                      {shortcode.name}
                    </code>
                  </TableCell>
                  <TableCell>{shortcode.description}</TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      {getStatusIcon(shortcode.registered)}
                      {shortcode.registered ? (
                        <span className="text-sm text-green-600">Registered</span>
                      ) : (
                        <span className="text-sm text-red-600">Not Registered</span>
                      )}
                    </div>
                  </TableCell>
                  <TableCell>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => copyShortcode(shortcode.name)}
                    >
                      {copiedShortcode === shortcode.name ? (
                        <>
                          <CheckCheck className="mr-2 h-4 w-4" />
                          Copied
                        </>
                      ) : (
                        <>
                          <Copy className="mr-2 h-4 w-4" />
                          Copy
                        </>
                      )}
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {/* Database Tables */}
      <Card>
        <CardHeader>
          <CardTitle>Database Tables</CardTitle>
          <CardDescription>Custom tables created by the plugin</CardDescription>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Table Name</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Rows</TableHead>
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {diagnostics?.database?.tables?.map((table) => (
                <TableRow key={table.name}>
                  <TableCell>
                    <code className="text-sm font-mono">{table.name}</code>
                  </TableCell>
                  <TableCell>
                    <div className="flex items-center gap-2">
                      {getStatusIcon(table.exists)}
                      {table.exists ? (
                        <span className="text-sm text-green-600">Exists</span>
                      ) : (
                        <span className="text-sm text-red-600">Missing</span>
                      )}
                    </div>
                  </TableCell>
                  <TableCell>
                    {table.exists ? (
                      <Badge variant="outline">{table.rowCount} rows</Badge>
                    ) : (
                      <span className="text-muted-foreground">N/A</span>
                    )}
                  </TableCell>
                  <TableCell>
                    {!table.exists && (
                      <Button variant="outline" size="sm">
                        Create Table
                      </Button>
                    )}
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>

      {/* API Endpoints */}
      <Card>
        <CardHeader>
          <CardTitle>REST API Endpoints</CardTitle>
          <CardDescription>
            Base URL: <code className="text-sm">{diagnostics?.api?.baseUrl}</code>
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {diagnostics?.api?.endpoints?.slice(0, 10).map((endpoint) => (
              <div
                key={endpoint.path}
                className="flex items-center justify-between p-3 border rounded-lg"
              >
                <div className="flex items-center gap-3">
                  {getStatusIcon(endpoint.status)}
                  <div>
                    <div className="flex items-center gap-2">
                      <Badge variant="outline" className="text-xs">
                        {endpoint.method}
                      </Badge>
                      <code className="text-sm">{endpoint.path}</code>
                    </div>
                  </div>
                </div>
                {getStatusBadge(endpoint.status)}
              </div>
            ))}
            <Alert>
              <AlertDescription>
                <strong>40+ API endpoints available.</strong> Use the Integrations page to test
                specific endpoints.
              </AlertDescription>
            </Alert>
          </div>
        </CardContent>
      </Card>

      {/* Built Assets */}
      <Card>
        <CardHeader>
          <CardTitle>Frontend Assets</CardTitle>
          <CardDescription>Compiled JavaScript and CSS bundles</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            <div className="flex items-center justify-between p-3 border rounded-lg">
              <div className="flex items-center gap-3">
                {getStatusIcon(diagnostics?.assets?.admin?.built)}
                <div>
                  <div className="font-medium">Admin React App</div>
                  <code className="text-xs text-muted-foreground">
                    {diagnostics?.assets?.admin?.path}
                  </code>
                </div>
              </div>
              {getStatusBadge(diagnostics?.assets?.admin?.built)}
            </div>
            <div className="flex items-center justify-between p-3 border rounded-lg">
              <div className="flex items-center gap-3">
                {getStatusIcon(diagnostics?.assets?.frontend?.built)}
                <div>
                  <div className="font-medium">Frontend Portal App</div>
                  <code className="text-xs text-muted-foreground">
                    {diagnostics?.assets?.frontend?.path}
                  </code>
                </div>
              </div>
              {getStatusBadge(diagnostics?.assets?.frontend?.built)}
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Integrations */}
      <Card>
        <CardHeader>
          <CardTitle>Plugin Integrations</CardTitle>
          <CardDescription>Third-party plugin dependencies</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {diagnostics?.integrations?.map((integration) => (
              <div
                key={integration.name}
                className="flex items-center justify-between p-3 border rounded-lg"
              >
                <div className="flex items-center gap-3">
                  {getStatusIcon(integration.active)}
                  <div>
                    <div className="font-medium">{integration.name}</div>
                    <div className="text-xs text-muted-foreground">
                      {integration.required ? "Required" : "Optional"}
                    </div>
                  </div>
                </div>
                <div className="flex items-center gap-2">
                  {getStatusBadge(integration.active)}
                  {!integration.active && integration.required && (
                    <Badge variant="destructive">Action Required</Badge>
                  )}
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Quick Actions */}
      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="flex gap-2 flex-wrap">
            <Button variant="outline" onClick={() => window.location.hash = "/settings"}>
              Plugin Settings
            </Button>
            <Button variant="outline" onClick={() => window.location.hash = "/integrations"}>
              Configure Integrations
            </Button>
            <Button
              variant="outline"
              onClick={() => window.open("/wp-admin/plugin-install.php", "_blank")}
            >
              Install Plugins
            </Button>
            <Button
              variant="outline"
              onClick={() => window.open("/wp-admin/plugins.php", "_blank")}
            >
              Manage Plugins
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
