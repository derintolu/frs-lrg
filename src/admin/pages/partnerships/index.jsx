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
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Badge } from "@/components/ui/badge";

export default function PartnershipsPage() {
  const [realtorPartners, setRealtorPartners] = useState([]);
  const [loanOfficers, setLoanOfficers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [isInviteDialogOpen, setIsInviteDialogOpen] = useState(false);
  const [isAssignDialogOpen, setIsAssignDialogOpen] = useState(false);
  const [selectedRealtor, setSelectedRealtor] = useState(null);

  const [inviteFormData, setInviteFormData] = useState({
    loan_officer_id: "",
    agent_name: "",
    agent_email: "",
    custom_message: "",
  });

  const [assignFormData, setAssignFormData] = useState({
    loan_officer_id: "",
  });

  const [stats, setStats] = useState({
    total: 0,
    active: 0,
    pending: 0,
  });

  useEffect(() => {
    fetchRealtorPartners();
    fetchLoanOfficers();
  }, []);

  const fetchRealtorPartners = async () => {
    try {
      const response = await fetch(
        `${window.lrhAdmin.apiUrl}realtor-partners`,
        {
          headers: {
            "X-WP-Nonce": window.lrhAdmin.nonce,
          },
        }
      );
      const data = await response.json();
      if (data.success) {
        setRealtorPartners(data.data);
        calculateStats(data.data);
      }
    } catch (error) {
      console.error("Error fetching realtor partners:", error);
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
    let totalActive = 0;
    let totalPending = 0;
    let totalPartnerships = 0;

    data.forEach((realtorData) => {
      realtorData.partnerships.forEach((partnership) => {
        totalPartnerships++;
        if (partnership.status === "active") totalActive++;
        if (partnership.status === "pending") totalPending++;
      });
    });

    setStats({
      total: totalPartnerships,
      active: totalActive,
      pending: totalPending,
    });
  };

  const handleInviteSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(
        `${window.lrhAdmin.apiUrl}partnerships/invite`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": window.lrhAdmin.nonce,
          },
          body: JSON.stringify(inviteFormData),
        }
      );
      const data = await response.json();
      if (data.success) {
        setIsInviteDialogOpen(false);
        setInviteFormData({
          loan_officer_id: "",
          agent_name: "",
          agent_email: "",
          custom_message: "",
        });
        fetchRealtorPartners();
      }
    } catch (error) {
      console.error("Error inviting partner:", error);
    }
  };

  const handleAssignSubmit = async (e) => {
    e.preventDefault();
    try {
      const response = await fetch(
        `${window.lrhAdmin.apiUrl}partnerships/assign`,
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-WP-Nonce": window.lrhAdmin.nonce,
          },
          body: JSON.stringify({
            realtor_id: selectedRealtor.id,
            loan_officer_id: assignFormData.loan_officer_id,
          }),
        }
      );
      const data = await response.json();
      if (data.success) {
        setIsAssignDialogOpen(false);
        setAssignFormData({ loan_officer_id: "" });
        setSelectedRealtor(null);
        fetchRealtorPartners();
      }
    } catch (error) {
      console.error("Error assigning loan officer:", error);
    }
  };

  const openAssignDialog = (realtorData) => {
    setSelectedRealtor(realtorData.realtor);
    setIsAssignDialogOpen(true);
  };

  const getStatusBadge = (status) => {
    const variants = {
      active: "default",
      pending: "secondary",
      inactive: "outline",
    };
    const colors = {
      active: "bg-green-100 text-green-800",
      pending: "bg-yellow-100 text-yellow-800",
      inactive: "bg-gray-100 text-gray-800",
    };
    return (
      <Badge variant={variants[status] || "outline"} className={colors[status] || ""}>
        {status}
      </Badge>
    );
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
        <h2 className="text-3xl font-bold tracking-tight">Partnership Management</h2>
        <div className="flex items-center space-x-2">
          <Dialog open={isInviteDialogOpen} onOpenChange={setIsInviteDialogOpen}>
            <DialogTrigger asChild>
              <Button>Invite New Partner</Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Invite New Partner</DialogTitle>
                <DialogDescription>
                  Send an invitation to a new realtor partner.
                </DialogDescription>
              </DialogHeader>
              <form onSubmit={handleInviteSubmit}>
                <div className="grid gap-4 py-4">
                  <div className="grid gap-2">
                    <Label htmlFor="loan_officer_id">Loan Officer</Label>
                    <Select
                      value={inviteFormData.loan_officer_id}
                      onValueChange={(value) =>
                        setInviteFormData({ ...inviteFormData, loan_officer_id: value })
                      }
                      required
                    >
                      <SelectTrigger>
                        <SelectValue placeholder="Select Loan Officer..." />
                      </SelectTrigger>
                      <SelectContent>
                        {loanOfficers.map((lo) => (
                          <SelectItem key={lo.ID} value={lo.ID.toString()}>
                            {lo.display_name} ({lo.user_email})
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                  </div>
                  <div className="grid gap-2">
                    <Label htmlFor="agent_name">Agent Name</Label>
                    <Input
                      id="agent_name"
                      value={inviteFormData.agent_name}
                      onChange={(e) =>
                        setInviteFormData({ ...inviteFormData, agent_name: e.target.value })
                      }
                    />
                  </div>
                  <div className="grid gap-2">
                    <Label htmlFor="agent_email">Agent Email *</Label>
                    <Input
                      id="agent_email"
                      type="email"
                      value={inviteFormData.agent_email}
                      onChange={(e) =>
                        setInviteFormData({ ...inviteFormData, agent_email: e.target.value })
                      }
                      required
                    />
                  </div>
                  <div className="grid gap-2">
                    <Label htmlFor="custom_message">Custom Message</Label>
                    <Textarea
                      id="custom_message"
                      value={inviteFormData.custom_message}
                      onChange={(e) =>
                        setInviteFormData({ ...inviteFormData, custom_message: e.target.value })
                      }
                      placeholder="Optional personal message to include in the invitation..."
                    />
                  </div>
                </div>
                <DialogFooter>
                  <Button type="submit">Send Invitation</Button>
                </DialogFooter>
              </form>
            </DialogContent>
          </Dialog>
          <Button
            variant="outline"
            onClick={() => (window.location.hash = "/bulk-invites")}
          >
            Bulk Invites
          </Button>
        </div>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Partnerships</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats.total}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Active</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats.active}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Pending</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{stats.pending}</div>
          </CardContent>
        </Card>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Realtor Partners</CardTitle>
          <CardDescription>
            View and manage realtor partners and their loan officer connections
          </CardDescription>
        </CardHeader>
        <CardContent>
          {loading ? (
            <p>Loading partnerships...</p>
          ) : realtorPartners.length === 0 ? (
            <div className="text-center py-10">
              <p className="text-muted-foreground mb-4">No realtor partners found.</p>
              <Button onClick={() => setIsInviteDialogOpen(true)}>
                Add First Realtor Partner
              </Button>
            </div>
          ) : (
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Realtor Partner</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead>Connected Loan Officers</TableHead>
                  <TableHead>Total Partnerships</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {realtorPartners.map((realtorData) => (
                  <TableRow key={realtorData.realtor.id}>
                    <TableCell>
                      <div>
                        <strong>{realtorData.realtor.display_name}</strong>
                        <br />
                        <small className="text-muted-foreground">
                          ID: {realtorData.realtor.id}
                        </small>
                      </div>
                    </TableCell>
                    <TableCell>{realtorData.realtor.email}</TableCell>
                    <TableCell>
                      {realtorData.partnerships.length === 0 ? (
                        <span className="text-muted-foreground text-sm">No partnerships</span>
                      ) : (
                        <div className="space-y-2">
                          {realtorData.partnerships.map((partnership) => (
                            <div
                              key={partnership.id}
                              className="p-2 bg-muted/50 rounded border-l-2 border-blue-500"
                            >
                              <div className="flex items-center gap-2">
                                <strong className="text-sm">{partnership.lo_name}</strong>
                                {getStatusBadge(partnership.status)}
                              </div>
                              <small className="text-muted-foreground block">
                                {partnership.lo_email}
                              </small>
                              <small className="text-muted-foreground block">
                                Since: {formatDate(partnership.created_date)}
                              </small>
                            </div>
                          ))}
                        </div>
                      )}
                    </TableCell>
                    <TableCell>
                      <span className="text-lg font-bold text-blue-600">
                        {realtorData.partnerships.length}
                      </span>
                    </TableCell>
                    <TableCell>
                      <div className="flex gap-2">
                        <Button
                          variant="outline"
                          size="sm"
                          onClick={() => openAssignDialog(realtorData)}
                        >
                          Assign LO
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

      {/* Assign Loan Officer Dialog */}
      <Dialog open={isAssignDialogOpen} onOpenChange={setIsAssignDialogOpen}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Assign Loan Officer</DialogTitle>
            <DialogDescription>
              Create a partnership between a loan officer and this realtor.
            </DialogDescription>
          </DialogHeader>
          {selectedRealtor && (
            <div className="p-4 bg-muted rounded-md mb-4">
              <h4 className="font-semibold mb-2">Creating partnership for:</h4>
              <p>
                <strong>{selectedRealtor.display_name}</strong>
                <br />
                <small className="text-muted-foreground">{selectedRealtor.email}</small>
              </p>
            </div>
          )}
          <form onSubmit={handleAssignSubmit}>
            <div className="grid gap-4 py-4">
              <div className="grid gap-2">
                <Label htmlFor="assign_loan_officer_id">Loan Officer</Label>
                <Select
                  value={assignFormData.loan_officer_id}
                  onValueChange={(value) =>
                    setAssignFormData({ ...assignFormData, loan_officer_id: value })
                  }
                  required
                >
                  <SelectTrigger>
                    <SelectValue placeholder="Select Loan Officer..." />
                  </SelectTrigger>
                  <SelectContent>
                    {loanOfficers.map((lo) => (
                      <SelectItem key={lo.ID} value={lo.ID.toString()}>
                        {lo.display_name} ({lo.user_email})
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <p className="text-sm text-muted-foreground">
                  Select the loan officer to partner with this realtor.
                </p>
              </div>
            </div>
            <DialogFooter>
              <Button type="button" variant="outline" onClick={() => setIsAssignDialogOpen(false)}>
                Cancel
              </Button>
              <Button type="submit">Create Partnership</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  );
}
