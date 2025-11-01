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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";

export default function LeadsPage() {
  const [leads, setLeads] = useState([]);
  const [filteredLeads, setFilteredLeads] = useState([]);
  const [loanOfficers, setLoanOfficers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [selectedLead, setSelectedLead] = useState(null);
  const [isViewModalOpen, setIsViewModalOpen] = useState(false);
  const [isNoteModalOpen, setIsNoteModalOpen] = useState(false);
  const [noteContent, setNoteContent] = useState("");

  const [filters, setFilters] = useState({
    status: "all",
    source: "all",
    loanOfficer: "all",
    dateFrom: "",
    dateTo: "",
    search: "",
  });

  const [stats, setStats] = useState({
    total: 0,
    newLeads: 0,
    contacted: 0,
    converted: 0,
  });

  useEffect(() => {
    fetchLeads();
    fetchLoanOfficers();
  }, []);

  useEffect(() => {
    applyFilters();
  }, [leads, filters]);

  const fetchLeads = async () => {
    try {
      const response = await fetch(`${window.lrhAdmin.apiUrl}leads`, {
        headers: {
          "X-WP-Nonce": window.lrhAdmin.nonce,
        },
      });
      const data = await response.json();
      if (data.success) {
        setLeads(data.data);
        calculateStats(data.data);
      }
    } catch (error) {
      console.error("Error fetching leads:", error);
    } finally {
      setLoading(false);
    }
  };

  const fetchLoanOfficers = async () => {
    try {
      const response = await fetch(
        `${window.lrhAdmin.apiUrl}partnerships/loan-officers`,
        {
          headers: {
            "X-WP-Nonce": window.lrhAdmin.nonce,
          },
        }
      );
      const data = await response.json();
      if (data.success) {
        setLoanOfficers(data.data);
      }
    } catch (error) {
      console.error("Error fetching loan officers:", error);
    }
  };

  const calculateStats = (data) => {
    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);

    const newLeads = data.filter((lead) => {
      const createdDate = new Date(lead.created_date);
      return createdDate >= sevenDaysAgo;
    });

    setStats({
      total: data.length,
      newLeads: newLeads.length,
      contacted: data.filter((l) => l.status === "contacted").length,
      converted: data.filter((l) => l.status === "converted").length,
    });
  };

  const applyFilters = () => {
    let filtered = [...leads];

    // Status filter
    if (filters.status && filters.status !== "all") {
      filtered = filtered.filter((lead) => lead.status === filters.status);
    }

    // Loan Officer filter
    if (filters.loanOfficer && filters.loanOfficer !== "all") {
      filtered = filtered.filter(
        (lead) => lead.loan_officer_id == filters.loanOfficer
      );
    }

    // Date filters
    if (filters.dateFrom) {
      const fromDate = new Date(filters.dateFrom);
      filtered = filtered.filter(
        (lead) => new Date(lead.created_date) >= fromDate
      );
    }

    if (filters.dateTo) {
      const toDate = new Date(filters.dateTo);
      toDate.setHours(23, 59, 59, 999);
      filtered = filtered.filter(
        (lead) => new Date(lead.created_date) <= toDate
      );
    }

    // Search filter
    if (filters.search) {
      const searchLower = filters.search.toLowerCase();
      filtered = filtered.filter(
        (lead) =>
          `${lead.first_name} ${lead.last_name}`.toLowerCase().includes(searchLower) ||
          lead.email?.toLowerCase().includes(searchLower) ||
          lead.phone?.toLowerCase().includes(searchLower)
      );
    }

    setFilteredLeads(filtered);
  };

  const clearFilters = () => {
    setFilters({
      status: "all",
      source: "all",
      loanOfficer: "all",
      dateFrom: "",
      dateTo: "",
      search: "",
    });
  };

  const updateLeadStatus = async (leadId, newStatus) => {
    try {
      const response = await fetch(
        `${window.lrhAdmin.apiUrl}leads/${leadId}/status`,
        {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": window.lrhAdmin.nonce,
          },
          body: JSON.stringify({ status: newStatus }),
        }
      );
      const data = await response.json();
      if (data.success) {
        // Update local state
        setLeads((prevLeads) =>
          prevLeads.map((lead) =>
            lead.id === leadId ? { ...lead, status: newStatus } : lead
          )
        );
        calculateStats(
          leads.map((lead) =>
            lead.id === leadId ? { ...lead, status: newStatus } : lead
          )
        );
      }
    } catch (error) {
      console.error("Error updating lead status:", error);
    }
  };

  const deleteLead = async (leadId) => {
    if (!confirm("Are you sure you want to delete this lead?")) {
      return;
    }

    try {
      const response = await fetch(
        `${window.lrhAdmin.apiUrl}leads/${leadId}`,
        {
          method: "DELETE",
          headers: {
            "X-WP-Nonce": window.lrhAdmin.nonce,
          },
        }
      );
      const data = await response.json();
      if (data.success) {
        setLeads((prevLeads) => prevLeads.filter((lead) => lead.id !== leadId));
      }
    } catch (error) {
      console.error("Error deleting lead:", error);
    }
  };

  const handleViewLead = (lead) => {
    setSelectedLead(lead);
    setIsViewModalOpen(true);
  };

  const handleAddNote = (lead) => {
    setSelectedLead(lead);
    setNoteContent("");
    setIsNoteModalOpen(true);
  };

  const submitNote = async () => {
    if (!noteContent.trim()) return;

    try {
      const response = await fetch(
        `${window.lrhAdmin.apiUrl}leads/${selectedLead.id}/notes`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": window.lrhAdmin.nonce,
          },
          body: JSON.stringify({ note: noteContent }),
        }
      );
      const data = await response.json();
      if (data.success) {
        setIsNoteModalOpen(false);
        setNoteContent("");
        // Optionally refresh leads to get updated notes
      }
    } catch (error) {
      console.error("Error adding note:", error);
    }
  };

  const exportLeads = () => {
    const csvContent = [
      [
        "Name",
        "Email",
        "Phone",
        "Loan Amount",
        "Property Value",
        "Property Address",
        "Source",
        "Status",
        "Created Date",
      ].join(","),
      ...filteredLeads.map((lead) =>
        [
          `"${lead.first_name} ${lead.last_name}"`,
          lead.email || "",
          lead.phone || "",
          lead.loan_amount || "",
          lead.property_value || "",
          `"${lead.property_address || ""}"`,
          lead.lo_name || lead.agent_name || "Direct",
          lead.status || "",
          lead.created_date || "",
        ].join(",")
      ),
    ].join("\n");

    const blob = new Blob([csvContent], { type: "text/csv" });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `leads-${new Date().toISOString().split("T")[0]}.csv`;
    a.click();
  };

  const formatCurrency = (amount) => {
    if (!amount) return "N/A";
    return new Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "USD",
      maximumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    return new Date(dateString).toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
    });
  };

  return (
    <div className="flex-1 space-y-4 p-8 pt-6">
      <div className="flex items-center justify-between space-y-2">
        <h2 className="text-3xl font-bold tracking-tight">Lead Management</h2>
        <div className="flex items-center space-x-2">
          <Button onClick={exportLeads} disabled={filteredLeads.length === 0}>
            Export Leads
          </Button>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Leads</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-blue-600">{stats.total}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">New (7 days)</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-blue-600">{stats.newLeads}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Contacted</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-blue-600">{stats.contacted}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Converted</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-blue-600">{stats.converted}</div>
          </CardContent>
        </Card>
      </div>

      {/* Filters */}
      <Card>
        <CardHeader>
          <CardTitle>Filters</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-6">
            <div className="space-y-2">
              <Label>Status</Label>
              <Select
                value={filters.status}
                onValueChange={(value) =>
                  setFilters({ ...filters, status: value })
                }
              >
                <SelectTrigger>
                  <SelectValue placeholder="All Statuses" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Statuses</SelectItem>
                  <SelectItem value="new">New</SelectItem>
                  <SelectItem value="contacted">Contacted</SelectItem>
                  <SelectItem value="qualified">Qualified</SelectItem>
                  <SelectItem value="converted">Converted</SelectItem>
                  <SelectItem value="closed">Closed</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label>Loan Officer</Label>
              <Select
                value={filters.loanOfficer}
                onValueChange={(value) =>
                  setFilters({ ...filters, loanOfficer: value })
                }
              >
                <SelectTrigger>
                  <SelectValue placeholder="All Loan Officers" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Loan Officers</SelectItem>
                  {loanOfficers.map((lo) => (
                    <SelectItem key={lo.ID} value={lo.ID.toString()}>
                      {lo.display_name}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            <div className="space-y-2">
              <Label>From Date</Label>
              <Input
                type="date"
                value={filters.dateFrom}
                onChange={(e) =>
                  setFilters({ ...filters, dateFrom: e.target.value })
                }
              />
            </div>

            <div className="space-y-2">
              <Label>To Date</Label>
              <Input
                type="date"
                value={filters.dateTo}
                onChange={(e) =>
                  setFilters({ ...filters, dateTo: e.target.value })
                }
              />
            </div>

            <div className="space-y-2">
              <Label>Search</Label>
              <Input
                type="search"
                placeholder="Search leads..."
                value={filters.search}
                onChange={(e) =>
                  setFilters({ ...filters, search: e.target.value })
                }
              />
            </div>

            <div className="space-y-2">
              <Label>&nbsp;</Label>
              <Button variant="outline" onClick={clearFilters} className="w-full">
                Clear Filters
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Leads Table */}
      <Card>
        <CardHeader>
          <CardTitle>All Leads</CardTitle>
          <CardDescription>
            {filteredLeads.length} lead{filteredLeads.length !== 1 ? "s" : ""} found
          </CardDescription>
        </CardHeader>
        <CardContent>
          {loading ? (
            <p className="text-muted-foreground">Loading leads...</p>
          ) : filteredLeads.length === 0 ? (
            <div className="text-center py-10">
              <p className="text-muted-foreground mb-2">No leads found.</p>
              <small className="text-muted-foreground">
                Leads will appear here when forms are submitted from your landing pages.
              </small>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Name</TableHead>
                  <TableHead>Contact</TableHead>
                  <TableHead>Loan Details</TableHead>
                  <TableHead>Source</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Date</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {filteredLeads.map((lead) => (
                  <TableRow key={lead.id}>
                    <TableCell>
                      <strong>
                        {lead.first_name} {lead.last_name}
                      </strong>
                    </TableCell>
                    <TableCell>
                      <div>{lead.email}</div>
                      {lead.phone && (
                        <small className="text-muted-foreground">{lead.phone}</small>
                      )}
                    </TableCell>
                    <TableCell>
                      {lead.loan_amount && (
                        <div>
                          <strong>{formatCurrency(lead.loan_amount)}</strong>
                        </div>
                      )}
                      {lead.property_value && (
                        <small className="text-muted-foreground block">
                          Property: {formatCurrency(lead.property_value)}
                        </small>
                      )}
                      {lead.property_address && (
                        <small className="text-muted-foreground block">
                          {lead.property_address}
                        </small>
                      )}
                    </TableCell>
                    <TableCell>
                      <div className="text-sm">
                        {lead.lo_name && (
                          <div>
                            <strong>LO:</strong> {lead.lo_name}
                          </div>
                        )}
                        {lead.agent_name && (
                          <div>
                            <strong>Agent:</strong> {lead.agent_name}
                          </div>
                        )}
                        {!lead.lo_name && !lead.agent_name && (
                          <span className="text-muted-foreground">Direct</span>
                        )}
                      </div>
                    </TableCell>
                    <TableCell>
                      <Select
                        value={lead.status || "new"}
                        onValueChange={(value) =>
                          updateLeadStatus(lead.id, value)
                        }
                      >
                        <SelectTrigger className="w-[130px]">
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="new">New</SelectItem>
                          <SelectItem value="contacted">Contacted</SelectItem>
                          <SelectItem value="qualified">Qualified</SelectItem>
                          <SelectItem value="converted">Converted</SelectItem>
                          <SelectItem value="closed">Closed</SelectItem>
                        </SelectContent>
                      </Select>
                    </TableCell>
                    <TableCell>
                      <span title={lead.created_date}>
                        {formatDate(lead.created_date)}
                      </span>
                    </TableCell>
                    <TableCell>
                      <div className="flex gap-2">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleViewLead(lead)}
                        >
                          View
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => handleAddNote(lead)}
                        >
                          Note
                        </Button>
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => deleteLead(lead.id)}
                        >
                          Delete
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

      {/* View Lead Modal */}
      <Dialog open={isViewModalOpen} onOpenChange={setIsViewModalOpen}>
        <DialogContent className="max-w-2xl">
          <DialogHeader>
            <DialogTitle>Lead Details</DialogTitle>
          </DialogHeader>
          {selectedLead && (
            <div className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label>Name</Label>
                  <p className="text-sm">
                    {selectedLead.first_name} {selectedLead.last_name}
                  </p>
                </div>
                <div>
                  <Label>Email</Label>
                  <p className="text-sm">{selectedLead.email}</p>
                </div>
                <div>
                  <Label>Phone</Label>
                  <p className="text-sm">{selectedLead.phone || "N/A"}</p>
                </div>
                <div>
                  <Label>Status</Label>
                  <p className="text-sm">{selectedLead.status}</p>
                </div>
                <div>
                  <Label>Loan Amount</Label>
                  <p className="text-sm">
                    {formatCurrency(selectedLead.loan_amount)}
                  </p>
                </div>
                <div>
                  <Label>Property Value</Label>
                  <p className="text-sm">
                    {formatCurrency(selectedLead.property_value)}
                  </p>
                </div>
                <div className="col-span-2">
                  <Label>Property Address</Label>
                  <p className="text-sm">{selectedLead.property_address || "N/A"}</p>
                </div>
                <div className="col-span-2">
                  <Label>Message</Label>
                  <p className="text-sm">{selectedLead.message || "N/A"}</p>
                </div>
                <div>
                  <Label>Loan Officer</Label>
                  <p className="text-sm">{selectedLead.lo_name || "N/A"}</p>
                </div>
                <div>
                  <Label>Agent</Label>
                  <p className="text-sm">{selectedLead.agent_name || "N/A"}</p>
                </div>
                <div>
                  <Label>Created Date</Label>
                  <p className="text-sm">{formatDate(selectedLead.created_date)}</p>
                </div>
              </div>
            </div>
          )}
          <DialogFooter>
            <Button variant="outline" onClick={() => setIsViewModalOpen(false)}>
              Close
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      {/* Add Note Modal */}
      <Dialog open={isNoteModalOpen} onOpenChange={setIsNoteModalOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Add Note</DialogTitle>
            <DialogDescription>
              Add a note about this lead for future reference
            </DialogDescription>
          </DialogHeader>
          <div className="space-y-4">
            <div>
              <Label htmlFor="note">Note</Label>
              <Textarea
                id="note"
                value={noteContent}
                onChange={(e) => setNoteContent(e.target.value)}
                placeholder="Enter your note about this lead..."
                rows={5}
              />
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" onClick={() => setIsNoteModalOpen(false)}>
              Cancel
            </Button>
            <Button onClick={submitNote}>Add Note</Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  );
}
