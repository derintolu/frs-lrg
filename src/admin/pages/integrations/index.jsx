import { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Badge } from "@/components/ui/badge";
import { Alert, AlertDescription } from "@/components/ui/alert";

export default function IntegrationsPage() {
  const [settings, setSettings] = useState({
    rentcast_api_key: "",
    form_field_mappings: {},
    canva_embeds: [],
    fluent_booking_enabled: false,
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState(null);

  useEffect(() => {
    fetchSettings();
  }, []);

  const fetchSettings = async () => {
    try {
      const response = await fetch(`${window.lrhAdmin.apiUrl}settings`, {
        headers: {
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
      });
      const data = await response.json();
      if (data.success) {
        setSettings({
          rentcast_api_key: data.data.rentcast_api_key || "",
          form_field_mappings: data.data.form_field_mappings || {},
          canva_embeds: data.data.canva_embeds || [],
          fluent_booking_enabled: data.data.fluent_booking_enabled || false,
        });
      }
    } catch (error) {
      console.error("Error fetching settings:", error);
    } finally {
      setLoading(false);
    }
  };

  const saveSettings = async (updatedSettings) => {
    setSaving(true);
    setMessage(null);
    try {
      const response = await fetch(`${window.lrhAdmin.apiUrl}settings`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
        body: JSON.stringify(updatedSettings),
      });
      const data = await response.json();
      if (data.success) {
        setMessage({ type: "success", text: "Settings saved successfully" });
      } else {
        setMessage({ type: "error", text: "Failed to save settings" });
      }
    } catch (error) {
      setMessage({ type: "error", text: error.message });
    } finally {
      setSaving(false);
    }
  };

  const handleRentcastSave = () => {
    saveSettings({ rentcast_api_key: settings.rentcast_api_key });
  };

  if (loading) {
    return (
      <div className="flex-1 space-y-4 p-8 pt-6">
        <p>Loading integrations...</p>
      </div>
    );
  }

  return (
    <div className="flex-1 space-y-4 p-8 pt-6">
      <div className="flex items-center justify-between space-y-2">
        <h2 className="text-3xl font-bold tracking-tight">Integrations</h2>
      </div>

      {message && (
        <Alert variant={message.type === "error" ? "destructive" : "default"}>
          <AlertDescription>{message.text}</AlertDescription>
        </Alert>
      )}

      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Rentcast API</CardTitle>
            <CardDescription>
              Property valuation API configuration
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="rentcast-api-key">API Key</Label>
              <Input
                id="rentcast-api-key"
                type="password"
                value={settings.rentcast_api_key}
                onChange={(e) =>
                  setSettings({ ...settings, rentcast_api_key: e.target.value })
                }
                placeholder="Enter your Rentcast API key"
              />
            </div>
            <Button onClick={handleRentcastSave} disabled={saving}>
              {saving ? "Saving..." : "Save API Key"}
            </Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <div className="flex items-center justify-between">
              <div>
                <CardTitle>FluentBooking Calendar</CardTitle>
                <CardDescription>
                  Calendar integration for loan officers
                </CardDescription>
              </div>
              <Badge variant={settings.fluent_booking_enabled ? "default" : "secondary"}>
                {settings.fluent_booking_enabled ? "Active" : "Inactive"}
              </Badge>
            </div>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground mb-4">
              FluentBooking provides calendar booking functionality for loan
              officers to schedule appointments with partners and clients.
            </p>
            <Button
              variant={settings.fluent_booking_enabled ? "outline" : "default"}
              onClick={() => {
                const newValue = !settings.fluent_booking_enabled;
                setSettings({ ...settings, fluent_booking_enabled: newValue });
                saveSettings({ fluent_booking_enabled: newValue });
              }}
            >
              {settings.fluent_booking_enabled ? "Disable" : "Enable"}
            </Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Canva Marketing Materials</CardTitle>
            <CardDescription>
              Configure up to 6 Canva embeds for social media and print
              materials
            </CardDescription>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground mb-4">
              Canva embeds allow loan officers to create branded marketing
              materials directly from their portal.
            </p>
            <p className="text-sm">
              Configured Embeds: {settings.canva_embeds.length}/6
            </p>
            <Button variant="outline" className="mt-4">
              Manage Embeds
            </Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Form Integration</CardTitle>
            <CardDescription>
              Configure form field mappings and lead attribution
            </CardDescription>
          </CardHeader>
          <CardContent>
            <p className="text-sm text-muted-foreground mb-4">
              Map form fields from various form plugins to lead submission
              fields and configure lead attribution rules.
            </p>
            <div className="space-y-2">
              <p className="text-sm">
                Field Mappings: {Object.keys(settings.form_field_mappings).length}
              </p>
            </div>
            <Button variant="outline" className="mt-4">
              Configure Mappings
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
