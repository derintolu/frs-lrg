import { useState } from "react";
import { Button } from "@/components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { Alert, AlertDescription } from "@/components/ui/alert";

export default function BulkInvitesPage() {
  const [emails, setEmails] = useState("");
  const [message, setMessage] = useState("");
  const [csvFile, setCsvFile] = useState(null);
  const [processing, setProcessing] = useState(false);
  const [results, setResults] = useState(null);

  const handleTextSubmit = async (e) => {
    e.preventDefault();
    setProcessing(true);
    setResults(null);

    const emailList = emails
      .split(/[\n,;]/)
      .map((e) => e.trim())
      .filter((e) => e && validateEmail(e));

    await processInvites(emailList);
  };

  const handleCsvUpload = async (e) => {
    e.preventDefault();
    if (!csvFile) return;

    setProcessing(true);
    setResults(null);

    const reader = new FileReader();
    reader.onload = async (event) => {
      const text = event.target.result;
      const rows = text.split("\n");
      const emailList = rows
        .slice(1) // Skip header
        .map((row) => {
          const cols = row.split(",");
          return cols[0]?.trim(); // Assuming email is first column
        })
        .filter((e) => e && validateEmail(e));

      await processInvites(emailList);
    };
    reader.readAsText(csvFile);
  };

  const processInvites = async (emailList) => {
    const successList = [];
    const failedList = [];

    for (const email of emailList) {
      try {
        const response = await fetch(
          `${window.lrhAdmin.apiUrl}partnerships`,
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
              "X-WP-Nonce": window.lrhAdmin.nonce,
            },
            body: JSON.stringify({
              loan_officer_id: window.lrhAdmin.userId,
              email: email,
              name: email.split("@")[0],
              message: message,
            }),
          }
        );

        const data = await response.json();
        if (data.success) {
          successList.push(email);
        } else {
          failedList.push({ email, error: data.message || "Unknown error" });
        }
      } catch (error) {
        failedList.push({ email, error: error.message });
      }
    }

    setResults({
      success: successList,
      failed: failedList,
      total: emailList.length,
    });
    setProcessing(false);
    setEmails("");
    setCsvFile(null);
  };

  const validateEmail = (email) => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  };

  const downloadTemplate = () => {
    const csvContent = "Email,Name\nexample@email.com,John Doe";
    const blob = new Blob([csvContent], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "partnership-invites-template.csv";
    a.click();
  };

  return (
    <div className="flex-1 space-y-4 p-8 pt-6">
      <div className="flex items-center justify-between space-y-2">
        <h2 className="text-3xl font-bold tracking-tight">Bulk Invites</h2>
      </div>

      {results && (
        <Alert>
          <AlertDescription>
            <div className="space-y-2">
              <p className="font-semibold">
                Processed {results.total} invitations
              </p>
              <div className="flex gap-4">
                <Badge variant="default">
                  {results.success.length} Successful
                </Badge>
                {results.failed.length > 0 && (
                  <Badge variant="destructive">
                    {results.failed.length} Failed
                  </Badge>
                )}
              </div>
              {results.failed.length > 0 && (
                <div className="mt-2">
                  <p className="text-sm font-medium">Failed invitations:</p>
                  <ul className="text-sm list-disc list-inside">
                    {results.failed.map((f, i) => (
                      <li key={i}>
                        {f.email}: {f.error}
                      </li>
                    ))}
                  </ul>
                </div>
              )}
            </div>
          </AlertDescription>
        </Alert>
      )}

      <div className="grid gap-4 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Text Input</CardTitle>
            <CardDescription>
              Enter email addresses separated by commas, semicolons, or new
              lines
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleTextSubmit} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="emails">Email Addresses</Label>
                <Textarea
                  id="emails"
                  placeholder="email1@example.com, email2@example.com&#10;email3@example.com"
                  value={emails}
                  onChange={(e) => setEmails(e.target.value)}
                  rows={8}
                  disabled={processing}
                />
              </div>
              <div className="space-y-2">
                <Label htmlFor="message">Personal Message (Optional)</Label>
                <Textarea
                  id="message"
                  placeholder="Add a personal message to all invitations..."
                  value={message}
                  onChange={(e) => setMessage(e.target.value)}
                  rows={3}
                  disabled={processing}
                />
              </div>
              <Button type="submit" disabled={processing || !emails.trim()}>
                {processing ? "Sending..." : "Send Invitations"}
              </Button>
            </form>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>CSV Upload</CardTitle>
            <CardDescription>
              Upload a CSV file with email addresses and names
            </CardDescription>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleCsvUpload} className="space-y-4">
              <div className="space-y-2">
                <Label htmlFor="csvFile">CSV File</Label>
                <Input
                  id="csvFile"
                  type="file"
                  accept=".csv"
                  onChange={(e) => setCsvFile(e.target.files?.[0] || null)}
                  disabled={processing}
                />
                <p className="text-sm text-muted-foreground">
                  CSV should have columns: Email, Name
                </p>
              </div>
              <div className="space-y-2">
                <Label htmlFor="csv-message">Personal Message (Optional)</Label>
                <Textarea
                  id="csv-message"
                  placeholder="Add a personal message to all invitations..."
                  value={message}
                  onChange={(e) => setMessage(e.target.value)}
                  rows={3}
                  disabled={processing}
                />
              </div>
              <div className="flex gap-2">
                <Button type="submit" disabled={processing || !csvFile}>
                  {processing ? "Sending..." : "Upload and Send"}
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  onClick={downloadTemplate}
                >
                  Download Template
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
