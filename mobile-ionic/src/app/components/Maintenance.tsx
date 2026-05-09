import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "./ui/dialog";
import { Label } from "./ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { Textarea } from "./ui/textarea";
import { Wrench, AlertTriangle, Calendar, DollarSign, Plus, TrendingUp } from "lucide-react";
import { useState } from "react";

const maintenanceRecords = [
  {
    id: "MTN-001",
    date: "2026-02-05",
    unitNumber: "TXN-1234",
    category: "Preventive Maintenance",
    mechanic: "Mario Reyes",
    parts: ["Oil", "Oil Filter"],
    laborCost: 800,
    partsCost: 1200,
    totalCost: 2000,
    status: "Completed",
    downtime: "2 hours",
  },
  {
    id: "MTN-002",
    date: "2026-02-04",
    unitNumber: "TXN-9012",
    category: "Breakdown Repair",
    mechanic: "Tony Santos",
    parts: ["Fan Belt", "Battery"],
    laborCost: 1500,
    partsCost: 4500,
    totalCost: 6000,
    status: "Completed",
    downtime: "1 day",
  },
  {
    id: "MTN-003",
    date: "2026-02-06",
    unitNumber: "TXN-3456",
    category: "Preventive Maintenance",
    mechanic: "Mario Reyes",
    parts: ["Tires (4pcs)"],
    laborCost: 2000,
    partsCost: 12000,
    totalCost: 14000,
    status: "In Progress",
    downtime: "4 hours",
  },
  {
    id: "MTN-004",
    date: "2026-02-03",
    unitNumber: "TXN-5678",
    category: "Breakdown Repair",
    mechanic: "Tony Santos",
    parts: ["Brake Pads", "Brake Fluid"],
    laborCost: 1200,
    partsCost: 3500,
    totalCost: 4700,
    status: "Completed",
    downtime: "6 hours",
  },
];

const partsCatalog = [
  { name: "Battery", price: 3500, warranty: "1 Year", supplier: "Motolite" },
  { name: "Tires (per pc)", price: 3000, warranty: "40,000 km", supplier: "Goodyear" },
  { name: "Oil (4L)", price: 800, warranty: "3 Months", supplier: "Shell" },
  { name: "Oil Filter", price: 400, warranty: "3 Months", supplier: "Generic" },
  { name: "Brake Pads", price: 2500, warranty: "6 Months", supplier: "OEM" },
  { name: "Brake Fluid", price: 400, warranty: "1 Year", supplier: "Generic" },
  { name: "Fan Belt", price: 800, warranty: "6 Months", supplier: "OEM" },
  { name: "Shock Absorbers (per pc)", price: 2500, warranty: "1 Year", supplier: "KYB" },
];

const maintenanceSchedule = [
  {
    unitNumber: "TXN-1234",
    maintenanceType: "Change Oil",
    lastDone: "2026-02-05",
    nextDue: "2026-03-05",
    status: "Upcoming",
    daysLeft: 27,
  },
  {
    unitNumber: "TXN-5678",
    maintenanceType: "Tires Rotation",
    lastDone: "2025-08-10",
    nextDue: "2026-02-10",
    status: "Overdue",
    daysLeft: -4,
  },
  {
    unitNumber: "TXN-9012",
    maintenanceType: "General Check-up",
    lastDone: "2025-11-15",
    nextDue: "2026-02-15",
    status: "Due Soon",
    daysLeft: 9,
  },
  {
    unitNumber: "TXN-3456",
    maintenanceType: "Battery Check",
    lastDone: "2026-01-20",
    nextDue: "2026-07-20",
    status: "Upcoming",
    daysLeft: 164,
  },
];

const highRiskUnits = [
  {
    unitNumber: "TXN-9012",
    breakdownCount: 4,
    totalMaintenanceCost: 18500,
    lastBreakdown: "2026-02-04",
    avgDowntime: "18 hours",
  },
  {
    unitNumber: "TXN-7890",
    breakdownCount: 3,
    totalMaintenanceCost: 15200,
    lastBreakdown: "2026-01-28",
    avgDowntime: "12 hours",
  },
];

export function Maintenance() {
  const [recordDialogOpen, setRecordDialogOpen] = useState(false);

  const getScheduleStatusColor = (status: string) => {
    switch (status) {
      case "Overdue":
        return "bg-red-100 text-red-800";
      case "Due Soon":
        return "bg-yellow-100 text-yellow-800";
      case "Upcoming":
        return "bg-green-100 text-green-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  const stats = {
    totalMaintenance: maintenanceRecords.length,
    preventive: maintenanceRecords.filter((r) => r.category === "Preventive Maintenance").length,
    breakdown: maintenanceRecords.filter((r) => r.category === "Breakdown Repair").length,
    totalCost: maintenanceRecords.reduce((sum, r) => sum + r.totalCost, 0),
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl">Maintenance & Parts Tracking</h2>
          <p className="text-sm text-gray-500 mt-1">
            Manage preventive maintenance, breakdowns, and parts inventory
          </p>
        </div>
        <Dialog open={recordDialogOpen} onOpenChange={setRecordDialogOpen}>
          <DialogTrigger asChild>
            <Button className="bg-yellow-500 hover:bg-yellow-600 text-gray-900">
              <Plus className="mr-2 h-4 w-4" />
              Record Maintenance
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-md max-h-[90vh] overflow-y-auto">
            <DialogHeader>
              <DialogTitle>Record Maintenance Activity</DialogTitle>
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
                <Label>Maintenance Category</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Select category" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="preventive">Preventive Maintenance</SelectItem>
                    <SelectItem value="breakdown">Breakdown Repair</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Mechanic Assigned</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Select mechanic" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="mario">Mario Reyes</SelectItem>
                    <SelectItem value="tony">Tony Santos</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Parts Replaced</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Select parts" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="battery">Battery</SelectItem>
                    <SelectItem value="tires">Tires</SelectItem>
                    <SelectItem value="oil">Oil</SelectItem>
                    <SelectItem value="brakes">Brake Pads</SelectItem>
                    <SelectItem value="fan-belt">Fan Belt</SelectItem>
                    <SelectItem value="shocks">Shock Absorbers</SelectItem>
                    <SelectItem value="others">Others</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Labor Cost (₱)</Label>
                <Input type="number" placeholder="1500" />
              </div>
              <div className="space-y-2">
                <Label>Parts Cost (₱)</Label>
                <Input type="number" placeholder="3500" />
              </div>
              <div className="space-y-2">
                <Label>Notes</Label>
                <Textarea placeholder="Additional details..." />
              </div>
              <Button className="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900">
                Record Maintenance
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      {/* Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl">{stats.totalMaintenance}</div>
            <p className="text-sm text-gray-500">Total Activities</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl text-green-600">{stats.preventive}</div>
            <p className="text-sm text-gray-500">Preventive</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl text-red-600">{stats.breakdown}</div>
            <p className="text-sm text-gray-500">Breakdowns</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl">₱{stats.totalCost.toLocaleString()}</div>
            <p className="text-sm text-gray-500">Total Cost</p>
          </CardContent>
        </Card>
      </div>

      {/* High Risk Units Alert */}
      {highRiskUnits.length > 0 && (
        <Card className="border-red-200 bg-red-50">
          <CardHeader>
            <CardTitle className="text-red-800 flex items-center gap-2">
              <AlertTriangle className="h-5 w-5" />
              High Maintenance Risk Units
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {highRiskUnits.map((unit) => (
                <div key={unit.unitNumber} className="p-3 bg-white rounded-lg">
                  <div className="flex items-start justify-between mb-2">
                    <div>
                      <p className="font-medium">{unit.unitNumber}</p>
                      <p className="text-sm text-gray-600">
                        {unit.breakdownCount} breakdowns this month • Avg downtime: {unit.avgDowntime}
                      </p>
                    </div>
                    <Badge className="bg-red-100 text-red-800">High Risk</Badge>
                  </div>
                  <div className="grid grid-cols-2 gap-2 text-sm">
                    <div>
                      <p className="text-gray-500">Total Maintenance Cost</p>
                      <p className="text-red-600 font-medium">₱{unit.totalMaintenanceCost.toLocaleString()}</p>
                    </div>
                    <div>
                      <p className="text-gray-500">Last Breakdown</p>
                      <p>{unit.lastBreakdown}</p>
                    </div>
                  </div>
                  <Button variant="outline" size="sm" className="mt-3 w-full text-red-600">
                    Review for Retirement/Overhaul
                  </Button>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Maintenance Schedule */}
      <Card>
        <CardHeader>
          <CardTitle>Preventive Maintenance Scheduler</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-3 px-4 text-sm">Unit</th>
                  <th className="text-left py-3 px-4 text-sm">Maintenance Type</th>
                  <th className="text-left py-3 px-4 text-sm">Last Done</th>
                  <th className="text-left py-3 px-4 text-sm">Next Due</th>
                  <th className="text-left py-3 px-4 text-sm">Days Left</th>
                  <th className="text-left py-3 px-4 text-sm">Status</th>
                </tr>
              </thead>
              <tbody>
                {maintenanceSchedule.map((schedule, idx) => (
                  <tr key={idx} className="border-b hover:bg-gray-50">
                    <td className="py-3 px-4 text-sm">{schedule.unitNumber}</td>
                    <td className="py-3 px-4 text-sm">{schedule.maintenanceType}</td>
                    <td className="py-3 px-4 text-sm">{schedule.lastDone}</td>
                    <td className="py-3 px-4 text-sm">{schedule.nextDue}</td>
                    <td className="py-3 px-4 text-sm">
                      {schedule.daysLeft > 0 ? `${schedule.daysLeft} days` : `${Math.abs(schedule.daysLeft)} days overdue`}
                    </td>
                    <td className="py-3 px-4 text-sm">
                      <Badge className={getScheduleStatusColor(schedule.status)}>{schedule.status}</Badge>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      {/* Parts Catalog */}
      <Card>
        <CardHeader>
          <CardTitle>Parts Catalog & Pricing</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            {partsCatalog.map((part, idx) => (
              <div key={idx} className="p-4 border rounded-lg">
                <div className="flex items-start justify-between mb-2">
                  <h4 className="font-medium">{part.name}</h4>
                  <Wrench className="h-4 w-4 text-gray-400" />
                </div>
                <div className="space-y-1 text-sm">
                  <p className="text-green-600 font-medium">₱{part.price.toLocaleString()}</p>
                  <p className="text-gray-600">Warranty: {part.warranty}</p>
                  <p className="text-gray-500">Supplier: {part.supplier}</p>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Maintenance Records */}
      <Card>
        <CardHeader>
          <CardTitle>Recent Maintenance Records</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {maintenanceRecords.map((record) => (
              <div key={record.id} className="p-4 border rounded-lg">
                <div className="flex items-start justify-between mb-3">
                  <div>
                    <p className="font-medium">{record.id} - {record.unitNumber}</p>
                    <p className="text-sm text-gray-600">
                      {record.date} • {record.mechanic}
                    </p>
                  </div>
                  <Badge
                    className={
                      record.category === "Preventive Maintenance"
                        ? "bg-green-100 text-green-800"
                        : "bg-red-100 text-red-800"
                    }
                  >
                    {record.category}
                  </Badge>
                </div>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                  <div>
                    <p className="text-gray-500">Parts</p>
                    <p>{record.parts.join(", ")}</p>
                  </div>
                  <div>
                    <p className="text-gray-500">Labor Cost</p>
                    <p>₱{record.laborCost.toLocaleString()}</p>
                  </div>
                  <div>
                    <p className="text-gray-500">Total Cost</p>
                    <p className="font-medium text-orange-600">₱{record.totalCost.toLocaleString()}</p>
                  </div>
                  <div>
                    <p className="text-gray-500">Downtime</p>
                    <p>{record.downtime}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
