import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "./ui/dialog";
import { Label } from "./ui/label";
import { Input } from "./ui/input";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "./ui/tabs";
import { Users, Zap, FileText, Fuel, Plus, DollarSign, Calendar, AlertCircle } from "lucide-react";
import { useState } from "react";

const staffData = [
  { id: 1, name: "Maria Reyes", role: "Admin", salaryType: "Monthly", salary: 18000, status: "Paid" },
  { id: 2, name: "Jose Santos", role: "Dispatcher", salaryType: "Monthly", salary: 15000, status: "Paid" },
  { id: 3, name: "Ana Cruz", role: "Accountant", salaryType: "Monthly", salary: 20000, status: "Paid" },
  { id: 4, name: "Carlo Tan", role: "Mechanic", salaryType: "Daily", salary: 600, status: "Paid" },
];

const utilitiesData = [
  { id: 1, type: "Internet/WiFi", amount: 2500, billingPeriod: "Feb 2026", status: "Paid", dueDate: "2026-02-10" },
  { id: 2, type: "Electricity", amount: 8500, billingPeriod: "Feb 2026", status: "Paid", dueDate: "2026-02-15" },
  { id: 3, type: "Water", amount: 1200, billingPeriod: "Feb 2026", status: "Unpaid", dueDate: "2026-02-20" },
  { id: 4, type: "Office Rent", amount: 15000, billingPeriod: "Feb 2026", status: "Paid", dueDate: "2026-02-01" },
];

const registrationData = [
  {
    id: 1,
    unitNumber: "TXN-1234",
    ltoRegistration: 3500,
    mvuc: 1200,
    insurance: 8500,
    franchiseFee: 2500,
    totalCost: 15700,
    expiryDate: "2026-08-15",
    status: "Active",
    daysToExpiry: 190,
  },
  {
    id: 2,
    unitNumber: "TXN-5678",
    ltoRegistration: 3500,
    mvuc: 1200,
    insurance: 9200,
    franchiseFee: 2500,
    totalCost: 16400,
    expiryDate: "2026-03-10",
    status: "Expiring Soon",
    daysToExpiry: 32,
  },
  {
    id: 3,
    unitNumber: "TXN-9012",
    ltoRegistration: 3500,
    mvuc: 1200,
    insurance: 8800,
    franchiseFee: 2500,
    totalCost: 16000,
    expiryDate: "2026-10-20",
    status: "Active",
    daysToExpiry: 256,
  },
];

const fuelComplianceData = [
  {
    id: 1,
    unitNumber: "TXN-1234",
    driver: "Juan Dela Cruz",
    initialFullTank: { date: "2026-01-01", liters: 40, cost: 2800 },
    lastReturn: { date: "2026-02-05", fuelStatus: "Full Tank", deficiency: 0 },
    status: "Compliant",
  },
  {
    id: 2,
    unitNumber: "TXN-5678",
    driver: "Pedro Reyes",
    initialFullTank: { date: "2026-01-01", liters: 40, cost: 2800 },
    lastReturn: { date: "2026-02-06", fuelStatus: "Not Full", deficiency: 10 },
    status: "Deficient",
    penalty: 700,
  },
  {
    id: 3,
    unitNumber: "TXN-9012",
    driver: "Ana Garcia",
    initialFullTank: { date: "2026-01-01", liters: 40, cost: 2800 },
    lastReturn: { date: "2026-02-04", fuelStatus: "Full Tank", deficiency: 0 },
    status: "Compliant",
  },
];

export function OfficeExpenses() {
  const [activeTab, setActiveTab] = useState("staff");
  const [dialogOpen, setDialogOpen] = useState(false);

  const totalStaffSalary = staffData.reduce((sum, staff) => {
    return sum + (staff.salaryType === "Monthly" ? staff.salary : staff.salary * 30);
  }, 0);

  const totalUtilities = utilitiesData.reduce((sum, util) => sum + util.amount, 0);
  const totalRegistration = registrationData.reduce((sum, reg) => sum + reg.totalCost, 0);
  const totalFuelPenalties = fuelComplianceData
    .filter((f) => f.status === "Deficient")
    .reduce((sum, f) => sum + (f.penalty || 0), 0);

  const totalMonthlyExpenses = totalStaffSalary + totalUtilities + totalRegistration + totalFuelPenalties;

  const getStatusColor = (status: string) => {
    switch (status) {
      case "Paid":
      case "Compliant":
      case "Active":
        return "bg-green-100 text-green-800";
      case "Unpaid":
      case "Deficient":
        return "bg-red-100 text-red-800";
      case "Expiring Soon":
        return "bg-yellow-100 text-yellow-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  const expiringRegistrations = registrationData.filter((r) => r.daysToExpiry <= 60);

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl">Office Expenses & Operations</h2>
          <p className="text-sm text-gray-500 mt-1">
            Track staff salaries, utilities, vehicle registration, and fuel compliance
          </p>
        </div>
        <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
          <DialogTrigger asChild>
            <Button className="bg-yellow-500 hover:bg-yellow-600 text-gray-900">
              <Plus className="mr-2 h-4 w-4" />
              Add Expense Record
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle>Add Expense Record</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>Expense Type</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Select type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="staff">Staff Salary</SelectItem>
                    <SelectItem value="utilities">Utilities</SelectItem>
                    <SelectItem value="registration">Vehicle Registration</SelectItem>
                    <SelectItem value="fuel">Fuel Penalty</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Amount (₱)</Label>
                <Input type="number" placeholder="5000" />
              </div>
              <div className="space-y-2">
                <Label>Description</Label>
                <Input placeholder="Enter details..." />
              </div>
              <Button className="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900">
                Add Record
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      {/* Summary Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-5 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl">₱{totalMonthlyExpenses.toLocaleString()}</div>
            <p className="text-sm text-gray-500">Total Monthly</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl">₱{totalStaffSalary.toLocaleString()}</div>
            <p className="text-sm text-gray-500">Staff Salaries</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl">₱{totalUtilities.toLocaleString()}</div>
            <p className="text-sm text-gray-500">Utilities</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl">₱{totalRegistration.toLocaleString()}</div>
            <p className="text-sm text-gray-500">Registrations</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl text-red-600">₱{totalFuelPenalties.toLocaleString()}</div>
            <p className="text-sm text-gray-500">Fuel Penalties</p>
          </CardContent>
        </Card>
      </div>

      {/* Registration Expiry Alerts */}
      {expiringRegistrations.length > 0 && (
        <Card className="border-orange-200 bg-orange-50">
          <CardHeader>
            <CardTitle className="text-orange-800 flex items-center gap-2">
              <AlertCircle className="h-5 w-5" />
              Registration Expiring Soon
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-2">
              {expiringRegistrations.map((reg) => (
                <div key={reg.id} className="p-3 bg-white rounded-lg flex items-center justify-between">
                  <div>
                    <p className="font-medium">{reg.unitNumber}</p>
                    <p className="text-sm text-gray-600">
                      Expires: {reg.expiryDate} ({reg.daysToExpiry} days left)
                    </p>
                  </div>
                  <Button size="sm" variant="outline">
                    Renew Now
                  </Button>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Tabs for Different Expense Categories */}
      <Tabs value={activeTab} onValueChange={setActiveTab}>
        <TabsList className="grid w-full grid-cols-4">
          <TabsTrigger value="staff">Staff Salaries</TabsTrigger>
          <TabsTrigger value="utilities">Utilities</TabsTrigger>
          <TabsTrigger value="registration">Vehicle Registration</TabsTrigger>
          <TabsTrigger value="fuel">Fuel Compliance</TabsTrigger>
        </TabsList>

        {/* Staff Salaries Tab */}
        <TabsContent value="staff" className="mt-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Users className="h-5 w-5" />
                Office Staff Salary Management
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b">
                      <th className="text-left py-3 px-4 text-sm">Name</th>
                      <th className="text-left py-3 px-4 text-sm">Role</th>
                      <th className="text-left py-3 px-4 text-sm">Salary Type</th>
                      <th className="text-left py-3 px-4 text-sm">Amount</th>
                      <th className="text-left py-3 px-4 text-sm">Monthly Cost</th>
                      <th className="text-left py-3 px-4 text-sm">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    {staffData.map((staff) => (
                      <tr key={staff.id} className="border-b hover:bg-gray-50">
                        <td className="py-3 px-4 text-sm">{staff.name}</td>
                        <td className="py-3 px-4 text-sm">{staff.role}</td>
                        <td className="py-3 px-4 text-sm">{staff.salaryType}</td>
                        <td className="py-3 px-4 text-sm">
                          ₱{staff.salary.toLocaleString()}
                          {staff.salaryType === "Daily" && <span className="text-gray-500">/day</span>}
                        </td>
                        <td className="py-3 px-4 text-sm">
                          ₱
                          {staff.salaryType === "Monthly"
                            ? staff.salary.toLocaleString()
                            : (staff.salary * 30).toLocaleString()}
                        </td>
                        <td className="py-3 px-4 text-sm">
                          <Badge className={getStatusColor(staff.status)}>{staff.status}</Badge>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                  <tfoot>
                    <tr className="border-t-2">
                      <td colSpan={4} className="py-3 px-4 text-sm font-medium text-right">
                        Total Monthly Payroll:
                      </td>
                      <td className="py-3 px-4 text-sm font-medium">
                        ₱{totalStaffSalary.toLocaleString()}
                      </td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Utilities Tab */}
        <TabsContent value="utilities" className="mt-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Zap className="h-5 w-5" />
                Utilities & Fixed Bills Tracking
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {utilitiesData.map((util) => (
                  <div key={util.id} className="p-4 border rounded-lg flex items-center justify-between">
                    <div className="flex-1">
                      <p className="font-medium">{util.type}</p>
                      <p className="text-sm text-gray-600">
                        {util.billingPeriod} • Due: {util.dueDate}
                      </p>
                    </div>
                    <div className="flex items-center gap-4">
                      <div className="text-right">
                        <p className="font-medium">₱{util.amount.toLocaleString()}</p>
                      </div>
                      <Badge className={getStatusColor(util.status)}>{util.status}</Badge>
                    </div>
                  </div>
                ))}
                <div className="pt-4 border-t flex justify-between items-center">
                  <p className="font-medium">Total Monthly Utilities:</p>
                  <p className="text-2xl font-medium">₱{totalUtilities.toLocaleString()}</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Vehicle Registration Tab */}
        <TabsContent value="registration" className="mt-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <FileText className="h-5 w-5" />
                Vehicle Registration & Compliance Expenses
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                {registrationData.map((reg) => (
                  <div key={reg.id} className="p-4 border rounded-lg">
                    <div className="flex items-start justify-between mb-3">
                      <div>
                        <p className="font-medium">{reg.unitNumber}</p>
                        <p className="text-sm text-gray-600">
                          Expiry: {reg.expiryDate} ({reg.daysToExpiry} days)
                        </p>
                      </div>
                      <Badge className={getStatusColor(reg.status)}>{reg.status}</Badge>
                    </div>
                    <div className="grid grid-cols-4 gap-3 text-sm">
                      <div>
                        <p className="text-gray-500">LTO Registration</p>
                        <p>₱{reg.ltoRegistration.toLocaleString()}</p>
                      </div>
                      <div>
                        <p className="text-gray-500">MVUC</p>
                        <p>₱{reg.mvuc.toLocaleString()}</p>
                      </div>
                      <div>
                        <p className="text-gray-500">Insurance</p>
                        <p>₱{reg.insurance.toLocaleString()}</p>
                      </div>
                      <div>
                        <p className="text-gray-500">Franchise Fee</p>
                        <p>₱{reg.franchiseFee.toLocaleString()}</p>
                      </div>
                    </div>
                    <div className="mt-3 pt-3 border-t flex justify-between items-center">
                      <p className="text-sm text-gray-600">Total Registration Cost</p>
                      <p className="font-medium text-green-600">₱{reg.totalCost.toLocaleString()}</p>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        {/* Fuel Compliance Tab */}
        <TabsContent value="fuel" className="mt-6">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <Fuel className="h-5 w-5" />
                Fuel Policy & Compliance Tracking
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="mb-4 p-4 bg-blue-50 rounded-lg">
                <p className="text-sm text-blue-800">
                  <strong>Fuel Policy:</strong> Company provides one-time full tank. Daily fuel is driver responsibility.
                  Unit must be returned with full tank. Deficiency results in penalty.
                </p>
              </div>
              <div className="space-y-4">
                {fuelComplianceData.map((fuel) => (
                  <div
                    key={fuel.id}
                    className={`p-4 border rounded-lg ${
                      fuel.status === "Deficient" ? "border-red-200 bg-red-50" : ""
                    }`}
                  >
                    <div className="flex items-start justify-between mb-3">
                      <div>
                        <p className="font-medium">{fuel.unitNumber}</p>
                        <p className="text-sm text-gray-600">Driver: {fuel.driver}</p>
                      </div>
                      <Badge className={getStatusColor(fuel.status)}>{fuel.status}</Badge>
                    </div>
                    <div className="grid grid-cols-2 gap-4 text-sm">
                      <div>
                        <p className="text-gray-500 mb-2">Initial Company Full Tank</p>
                        <p>Date: {fuel.initialFullTank.date}</p>
                        <p>Liters: {fuel.initialFullTank.liters}L</p>
                        <p>Cost: ₱{fuel.initialFullTank.cost.toLocaleString()}</p>
                      </div>
                      <div>
                        <p className="text-gray-500 mb-2">Last Unit Return</p>
                        <p>Date: {fuel.lastReturn.date}</p>
                        <p>Status: {fuel.lastReturn.fuelStatus}</p>
                        {fuel.status === "Deficient" && (
                          <>
                            <p className="text-red-600">Deficiency: {fuel.lastReturn.deficiency}L</p>
                            <p className="text-red-600 font-medium">Penalty: ₱{fuel.penalty?.toLocaleString()}</p>
                          </>
                        )}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
