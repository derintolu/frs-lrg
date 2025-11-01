import { useState, useEffect } from "react";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Switch } from "@/components/ui/switch";
import { Button } from "@/components/ui/button";
import { useToast } from "@/components/ui/use-toast";
import { Loader2 } from "lucide-react";
import SettingsLayout from "@/admin/pages/settings/layout";

export default function Settings() {
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [systemInfo, setSystemInfo] = useState(null);

  const [settings, setSettings] = useState({
    notify_loan_officer: true,
    notify_agent: true,
    notify_admin: false,
    admin_notification_email: "",
    invitation_expiry: 30,
    max_partnerships: 0,
    debug_mode: false,
    cleanup_on_deactivate: false,
  });

  useEffect(() => {
    fetchSettings();
    fetchSystemInfo();
  }, []);

  const fetchSettings = async () => {
    setLoading(true);
    try {
      const response = await fetch(`${window.lrhAdmin.apiUrl}/settings`, {
        headers: {
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
      });
      const data = await response.json();
      if (data.success) {
        setSettings(data.data);
      }
    } catch (error) {
      toast({
        title: "Error",
        description: "Failed to load settings",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const fetchSystemInfo = async () => {
    try {
      const response = await fetch(`${window.lrhAdmin.apiUrl}/settings/system-info`, {
        headers: {
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
      });
      const data = await response.json();
      if (data.success) {
        setSystemInfo(data.data);
      }
    } catch (error) {
      console.error("Failed to load system info:", error);
    }
  };

  const handleSave = async () => {
    setSaving(true);
    try {
      const response = await fetch(`${window.lrhAdmin.apiUrl}/settings`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
        body: JSON.stringify(settings),
      });
      const data = await response.json();
      if (data.success) {
        toast({
          title: "Success",
          description: "Settings saved successfully",
        });
      } else {
        throw new Error(data.message || "Failed to save settings");
      }
    } catch (error) {
      toast({
        title: "Error",
        description: error.message || "Failed to save settings",
        variant: "destructive",
      });
    } finally {
      setSaving(false);
    }
  };

  const handleCheckboxChange = (field, checked) => {
    setSettings((prev) => ({ ...prev, [field]: checked }));
  };

  const handleInputChange = (field, value) => {
    setSettings((prev) => ({ ...prev, [field]: value }));
  };

  if (loading) {
    return (
      <SettingsLayout>
        <div className="flex items-center justify-center h-64">
          <Loader2 className="h-8 w-8 animate-spin" />
        </div>
      </SettingsLayout>
    );
  }

  return (
    <SettingsLayout>
      <div className="space-y-6">
        <div>
          <h3 className="text-lg font-medium">Plugin Settings</h3>
          <p className="text-sm text-muted-foreground">
            Configure notifications and advanced settings for the Lending Resource Hub.
          </p>
        </div>

        <Tabs defaultValue="notifications" className="w-full">
          <TabsList>
            <TabsTrigger value="notifications">Notifications</TabsTrigger>
            <TabsTrigger value="advanced">Advanced</TabsTrigger>
          </TabsList>

          <TabsContent value="notifications">
            <Card>
              <CardHeader>
                <CardTitle>Notification Settings</CardTitle>
                <CardDescription>
                  Configure email notifications for leads and partnerships
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="space-y-4">
                  <h4 className="text-sm font-medium">Lead Submission Notifications</h4>

                  <div className="flex items-center justify-between">
                    <Label htmlFor="notify_loan_officer" className="cursor-pointer">
                      Notify Loan Officer when a new lead is submitted
                    </Label>
                    <Switch
                      id="notify_loan_officer"
                      checked={settings.notify_loan_officer}
                      onCheckedChange={(checked) =>
                        handleCheckboxChange("notify_loan_officer", checked)
                      }
                    />
                  </div>

                  <div className="flex items-center justify-between">
                    <Label htmlFor="notify_agent" className="cursor-pointer">
                      Notify Realtor Partner when a new lead is submitted
                    </Label>
                    <Switch
                      id="notify_agent"
                      checked={settings.notify_agent}
                      onCheckedChange={(checked) =>
                        handleCheckboxChange("notify_agent", checked)
                      }
                    />
                  </div>
                </div>

                <div className="space-y-4">
                  <h4 className="text-sm font-medium">Partnership Notifications</h4>

                  <div className="flex items-center justify-between">
                    <Label htmlFor="notify_admin" className="cursor-pointer">
                      Notify admin when partnerships are created or updated
                    </Label>
                    <Switch
                      id="notify_admin"
                      checked={settings.notify_admin}
                      onCheckedChange={(checked) =>
                        handleCheckboxChange("notify_admin", checked)
                      }
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="admin_notification_email">Admin Notification Email</Label>
                    <Input
                      id="admin_notification_email"
                      type="email"
                      placeholder="admin@example.com"
                      value={settings.admin_notification_email}
                      onChange={(e) =>
                        handleInputChange("admin_notification_email", e.target.value)
                      }
                    />
                    <p className="text-sm text-muted-foreground">
                      Leave blank to use the WordPress admin email
                    </p>
                  </div>
                </div>

                <Button onClick={handleSave} disabled={saving}>
                  {saving && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                  Save Notification Settings
                </Button>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="advanced">
            <Card>
              <CardHeader>
                <CardTitle>Advanced Settings</CardTitle>
                <CardDescription>
                  Configure partnership and system settings
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-6">
                <div className="space-y-2">
                  <Label htmlFor="invitation_expiry">Invitation Expiry (days)</Label>
                  <Input
                    id="invitation_expiry"
                    type="number"
                    min="1"
                    max="365"
                    value={settings.invitation_expiry}
                    onChange={(e) =>
                      handleInputChange("invitation_expiry", parseInt(e.target.value))
                    }
                  />
                  <p className="text-sm text-muted-foreground">
                    Number of days before partnership invitations expire
                  </p>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="max_partnerships">Maximum Partnerships per Realtor</Label>
                  <Input
                    id="max_partnerships"
                    type="number"
                    min="0"
                    value={settings.max_partnerships}
                    onChange={(e) =>
                      handleInputChange("max_partnerships", parseInt(e.target.value))
                    }
                  />
                  <p className="text-sm text-muted-foreground">
                    Set to 0 for unlimited partnerships
                  </p>
                </div>

                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <Label htmlFor="debug_mode" className="cursor-pointer">
                      Enable Debug Mode
                    </Label>
                    <Switch
                      id="debug_mode"
                      checked={settings.debug_mode}
                      onCheckedChange={(checked) => handleCheckboxChange("debug_mode", checked)}
                    />
                  </div>
                  <p className="text-sm text-muted-foreground">
                    Enable detailed logging for troubleshooting
                  </p>
                </div>

                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <Label htmlFor="cleanup_on_deactivate" className="cursor-pointer">
                      Clean up data on plugin deactivation
                    </Label>
                    <Switch
                      id="cleanup_on_deactivate"
                      checked={settings.cleanup_on_deactivate}
                      onCheckedChange={(checked) =>
                        handleCheckboxChange("cleanup_on_deactivate", checked)
                      }
                    />
                  </div>
                  <p className="text-sm text-muted-foreground">
                    Warning: This will delete all plugin data when deactivated
                  </p>
                </div>

                <Button onClick={handleSave} disabled={saving}>
                  {saving && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                  Save Advanced Settings
                </Button>

                {systemInfo && (
                  <div className="mt-8 space-y-4">
                    <h4 className="text-sm font-medium">System Information</h4>
                    <div className="rounded-md border">
                      <table className="w-full text-sm">
                        <tbody>
                          <tr className="border-b">
                            <td className="p-3 font-medium bg-muted/50">Plugin Version</td>
                            <td className="p-3">{systemInfo.plugin_version}</td>
                          </tr>
                          <tr className="border-b">
                            <td className="p-3 font-medium bg-muted/50">WordPress Version</td>
                            <td className="p-3">{systemInfo.wp_version}</td>
                          </tr>
                          <tr className="border-b">
                            <td className="p-3 font-medium bg-muted/50">PHP Version</td>
                            <td className="p-3">{systemInfo.php_version}</td>
                          </tr>
                          <tr className="border-b">
                            <td className="p-3 font-medium bg-muted/50">Database Version</td>
                            <td className="p-3">{systemInfo.db_version}</td>
                          </tr>
                          <tr className="border-b">
                            <td className="p-3 font-medium bg-muted/50">Total Partnerships</td>
                            <td className="p-3">{systemInfo.total_partnerships}</td>
                          </tr>
                          <tr className="border-b">
                            <td className="p-3 font-medium bg-muted/50">Total Leads</td>
                            <td className="p-3">{systemInfo.total_leads}</td>
                          </tr>
                          <tr>
                            <td className="p-3 font-medium bg-muted/50">Total Loan Officers</td>
                            <td className="p-3">{systemInfo.total_loan_officers}</td>
                          </tr>
                          <tr>
                            <td className="p-3 font-medium bg-muted/50">Total Realtors</td>
                            <td className="p-3">{systemInfo.total_realtors}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                )}
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </SettingsLayout>
  );
}
