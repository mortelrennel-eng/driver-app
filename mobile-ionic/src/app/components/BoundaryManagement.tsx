import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "./ui/dialog";
import { Label } from "./ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "./ui/tabs";
import { Calendar, DollarSign, AlertCircle, CheckCircle, Plus, Search } from "lucide-react";
import { useEffect, useState } from "react";
import api from "../services/api";

export function BoundaryManagement() {
  const [searchQuery, setSearchQuery] = useState("");
  const [recordDialogOpen, setRecordDialogOpen] = useState(false);
  const [activeTab, setActiveTab] = useState("today");
  const [records, setRecords] = useState<any[]>([]);
  const [stats, setStats] = useState<any>({
    totalExpected: 0,
    totalCollected: 0,
    paid: 0,
    shortage: 0,
    unpaid: 0
  });
  const [loading, setLoading] = useState(true);
  const [boundaryRules] = useState({
    newUnit: { regular: "1,200", half: "600", coding: "1,000" },
    oldUnit: { regular: "1,000", half: "500", coding: "800" },
  });
  const [overduePayments] = useState<any[]>([]);

  useEffect(() => {
    fetchBoundaries();
  }, [activeTab]);

  const fetchBoundaries = async () => {
    try {
      setLoading(true);
      const response = await api.get('/boundaries');
      if (response.data.success) {
        setRecords(response.data.records);
        setStats(response.data.stats);
      }
    } catch (error) {
      console.error("Failed to fetch boundaries:", error);
    } finally {
      setLoading(false);
    }
  };

  const filteredRecords = records.filter(
    (record) =>
      record.unitNumber?.toLowerCase().includes(searchQuery.toLowerCase()) ||
      record.driver?.toLowerCase().includes(searchQuery.toLowerCase()) ||
      record.id?.toString().includes(searchQuery.toLowerCase())
  );

  const getStatusColor = (status: string) => {
    switch (status) {
      case "Paid":
        return "bg-green-100 text-green-800";
      case "Shortage":
        return "bg-yellow-100 text-yellow-800";
      case "Unpaid":
        return "bg-red-100 text-red-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  const todayStats = stats;

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl">Boundary Management</h2>
          <p className="text-sm text-gray-500 mt-1">
            Track daily boundary collections and manage payment rules
          </p>
        </div>
        <Dialog open={recordDialogOpen} onOpenChange={setRecordDialogOpen}>
          <DialogTrigger asChild>
            <Button className="bg-yellow-500 hover:bg-yellow-600 text-gray-900">
              <Plus className="mr-2 h-4 w-4" />
              Record Boundary Payment
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle>Record Boundary Payment</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>Unit Number</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Select unit" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="TXN-1234">TXN-1234</SelectItem>
                    <SelectItem value="TXN-5678">TXN-5678</SelectItem>
                    <SelectItem value="TXN-9012">TXN-9012</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Driver Name</Label>
                <Input placeholder="Auto-filled based on unit" disabled />
              </div>
              <div className="space-y-2">
                <Label>Boundary Type</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Select type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="regular">Regular (24hrs)</SelectItem>
                    <SelectItem value="half">Half Boundary (12hrs)</SelectItem>
                    <SelectItem value="coding">Coding (24hrs)</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Expected Amount (₱)</Label>
                <Input value="1,200" disabled />
              </div>
              <div className="space-y-2">
                <Label>Amount Paid (₱)</Label>
                <Input type="number" placeholder="1200" />
              </div>
              <Button className="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900">
                Record Payment
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      {/* Boundary Rules Reference */}
      <Card>
        <CardHeader>
          <CardTitle>Boundary Rules Engine</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h4 className="font-medium mb-3">New Units</h4>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between p-2 bg-gray-50 rounded">
                  <span>Regular (24 hrs)</span>
                  <span className="font-medium">₱{boundaryRules.newUnit.regular}</span>
                </div>
                <div className="flex justify-between p-2 bg-gray-50 rounded">
                  <span>Half Boundary (12 hrs)</span>
                  <span className="font-medium">₱{boundaryRules.newUnit.half}</span>
                </div>
                <div className="flex justify-between p-2 bg-gray-50 rounded">
                  <span>Coding (24 hrs)</span>
                  <span className="font-medium">₱{boundaryRules.newUnit.coding}</span>
                </div>
              </div>
            </div>
            <div>
              <h4 className="font-medium mb-3">Old Units</h4>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between p-2 bg-gray-50 rounded">
                  <span>Regular (24 hrs)</span>
                  <span className="font-medium">₱{boundaryRules.oldUnit.regular}</span>
                </div>
                <div className="flex justify-between p-2 bg-gray-50 rounded">
                  <span>Half Boundary (12 hrs)</span>
                  <span className="font-medium">₱{boundaryRules.oldUnit.half}</span>
                </div>
                <div className="flex justify-between p-2 bg-gray-50 rounded">
                  <span>Coding (24 hrs)</span>
                  <span className="font-medium">₱{boundaryRules.oldUnit.coding}</span>
                </div>
              </div>
            </div>
          </div>
          <div className="mt-4 p-3 bg-blue-50 rounded-lg">
            <p className="text-sm text-blue-800">
              <strong>Policy:</strong> Assigned driver is mandatory to pay boundary kahit hindi bumiyahe
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Today's Stats */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl">₱{todayStats.totalExpected.toLocaleString()}</div>
            <p className="text-sm text-gray-500">Expected Today</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl text-green-600">₱{todayStats.totalCollected.toLocaleString()}</div>
            <p className="text-sm text-gray-500">Collected Today</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl text-green-600">{todayStats.paid}</div>
            <p className="text-sm text-gray-500">Paid</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl text-yellow-600">{todayStats.shortage}</div>
            <p className="text-sm text-gray-500">Shortage</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl text-red-600">{todayStats.unpaid}</div>
            <p className="text-sm text-gray-500">Unpaid</p>
          </CardContent>
        </Card>
      </div>

      {/* Overdue Payments Alert */}
      {overduePayments.length > 0 && (
        <Card className="border-red-200 bg-red-50">
          <CardHeader>
            <CardTitle className="text-red-800 flex items-center gap-2">
              <AlertCircle className="h-5 w-5" />
              Overdue Payments
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-2">
              {overduePayments.map((payment, idx) => (
                <div key={idx} className="flex items-center justify-between p-3 bg-white rounded-lg">
                  <div>
                    <p className="font-medium">{payment.driver}</p>
                    <p className="text-sm text-gray-600">
                      {payment.unitNumber} • {payment.daysOverdue} days overdue
                    </p>
                  </div>
                  <div className="text-right">
                    <p className="font-medium text-red-600">₱{payment.totalDue.toLocaleString()}</p>
                    <p className="text-xs text-gray-500">Last: {payment.lastPayment}</p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Search */}
      <Card>
        <CardContent className="pt-6">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
              type="text"
              placeholder="Search by unit number, driver, or record ID..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10"
            />
          </div>
        </CardContent>
      </Card>

      {/* Boundary Records */}
      <Card>
        <CardHeader>
          <CardTitle>Boundary Records</CardTitle>
        </CardHeader>
        <CardContent>
          <Tabs value={activeTab} onValueChange={setActiveTab}>
            <TabsList>
              <TabsTrigger value="today">Today</TabsTrigger>
              <TabsTrigger value="week">This Week</TabsTrigger>
              <TabsTrigger value="month">This Month</TabsTrigger>
            </TabsList>

            <TabsContent value={activeTab} className="mt-4">
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b">
                      <th className="text-left py-3 px-4 text-sm">Record ID</th>
                      <th className="text-left py-3 px-4 text-sm">Date</th>
                      <th className="text-left py-3 px-4 text-sm">Unit</th>
                      <th className="text-left py-3 px-4 text-sm">Driver</th>
                      <th className="text-left py-3 px-4 text-sm">Type</th>
                      <th className="text-left py-3 px-4 text-sm">Expected</th>
                      <th className="text-left py-3 px-4 text-sm">Paid</th>
                      <th className="text-left py-3 px-4 text-sm">Status</th>
                      <th className="text-left py-3 px-4 text-sm">Time</th>
                    </tr>
                  </thead>
                  <tbody>
                    {filteredRecords.map((record) => (
                      <tr key={record.id} className="border-b hover:bg-gray-50">
                        <td className="py-3 px-4 text-sm">{record.id}</td>
                        <td className="py-3 px-4 text-sm">{record.date}</td>
                        <td className="py-3 px-4 text-sm">
                          <div>
                            <p>{record.unitNumber}</p>
                            <p className="text-xs text-gray-500">{record.unitType}</p>
                          </div>
                        </td>
                        <td className="py-3 px-4 text-sm">{record.driver}</td>
                        <td className="py-3 px-4 text-sm">{record.boundaryType}</td>
                        <td className="py-3 px-4 text-sm">₱{record.expectedAmount}</td>
                        <td className="py-3 px-4 text-sm">
                          <span className={record.paidAmount < record.expectedAmount ? "text-yellow-600" : ""}>
                            ₱{record.paidAmount}
                          </span>
                          {record.status === "Shortage" && (
                            <p className="text-xs text-red-600">Short: ₱{record.shortage}</p>
                          )}
                        </td>
                        <td className="py-3 px-4 text-sm">
                          <Badge className={getStatusColor(record.status)}>{record.status}</Badge>
                        </td>
                        <td className="py-3 px-4 text-sm">{record.paymentTime}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </TabsContent>
          </Tabs>
        </CardContent>
      </Card>
    </div>
  );
}
