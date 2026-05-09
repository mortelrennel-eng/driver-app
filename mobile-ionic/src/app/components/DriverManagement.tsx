import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Input } from "./ui/input";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "./ui/dialog";
import { Label } from "./ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { Search, UserPlus, Phone, Mail, Car, Calendar, Users } from "lucide-react";
import { useState } from "react";

const driversData = [
  {
    id: "DRV-001",
    name: "Juan Dela Cruz",
    phone: "+63 917 123 4567",
    email: "juan.delacruz@email.com",
    license: "N04-12-345678",
    employmentStatus: "Active",
    assignedUnit: "TXN-1234",
    assignmentMode: "Single Driver",
    role: "Primary Driver",
    behaviorScore: 4.8,
    totalTrips: 1247,
    totalBoundary: 480000,
  },
  {
    id: "DRV-002",
    name: "Pedro Reyes",
    phone: "+63 917 234 5678",
    email: "pedro.reyes@email.com",
    license: "N04-12-456789",
    employmentStatus: "Active",
    assignedUnit: "TXN-5678",
    assignmentMode: "Two Drivers (Day A)",
    role: "Day A Driver",
    behaviorScore: 4.9,
    totalTrips: 982,
    totalBoundary: 420000,
  },
  {
    id: "DRV-003",
    name: "Maria Santos",
    phone: "+63 917 345 6789",
    email: "maria.santos@email.com",
    license: "N04-12-567890",
    employmentStatus: "Active",
    assignedUnit: "TXN-5678",
    assignmentMode: "Two Drivers (Day B)",
    role: "Day B Driver",
    behaviorScore: 4.7,
    totalTrips: 856,
    totalBoundary: 380000,
  },
  {
    id: "DRV-004",
    name: "Ana Garcia",
    phone: "+63 917 456 7890",
    email: "ana.garcia@email.com",
    license: "N04-12-678901",
    employmentStatus: "Active",
    assignedUnit: "TXN-9012",
    assignmentMode: "Single Driver",
    role: "Primary Driver",
    behaviorScore: 4.6,
    totalTrips: 734,
    totalBoundary: 350000,
  },
  {
    id: "DRV-005",
    name: "Carlos Martinez",
    phone: "+63 917 567 8901",
    email: "carlos.martinez@email.com",
    license: "N04-12-789012",
    employmentStatus: "Available",
    assignedUnit: null,
    assignmentMode: "Unassigned",
    role: "-",
    behaviorScore: 4.5,
    totalTrips: 623,
    totalBoundary: 280000,
  },
];

const vacantUnits = [
  { unitNumber: "TXN-7890", status: "Vacant - No Driver" },
  { unitNumber: "TXN-2468", status: "Vacant - Driver Resigned" },
];

const rotationSchedule = [
  {
    unitNumber: "TXN-5678",
    dayADriver: "Pedro Reyes",
    dayBDriver: "Maria Santos",
    todayActive: "Pedro Reyes (Day A)",
    nextRotation: "Tomorrow",
  },
  {
    unitNumber: "TXN-1357",
    dayADriver: "Roberto Tan",
    dayBDriver: "Lisa Garcia",
    todayActive: "Lisa Garcia (Day B)",
    nextRotation: "Tomorrow",
  },
];

export function DriverManagement() {
  const [searchQuery, setSearchQuery] = useState("");
  const [assignDialogOpen, setAssignDialogOpen] = useState(false);

  const filteredDrivers = driversData.filter(
    (driver) =>
      driver.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      driver.id.toLowerCase().includes(searchQuery.toLowerCase()) ||
      (driver.assignedUnit && driver.assignedUnit.toLowerCase().includes(searchQuery.toLowerCase()))
  );

  const getStatusColor = (status: string) => {
    switch (status) {
      case "Active":
        return "bg-green-100 text-green-800";
      case "Available":
        return "bg-blue-100 text-blue-800";
      case "Suspended":
        return "bg-red-100 text-red-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  const stats = {
    total: driversData.length,
    active: driversData.filter((d) => d.employmentStatus === "Active").length,
    available: driversData.filter((d) => d.employmentStatus === "Available").length,
    singleDriver: driversData.filter((d) => d.assignmentMode === "Single Driver").length,
    twoDrivers: driversData.filter((d) => d.assignmentMode.includes("Two Drivers")).length / 2,
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl">Driver Management & Dispatch</h2>
          <p className="text-sm text-gray-500 mt-1">
            Manage driver assignments with 1 or 2 drivers per unit configuration
          </p>
        </div>
        <Dialog open={assignDialogOpen} onOpenChange={setAssignDialogOpen}>
          <DialogTrigger asChild>
            <Button className="bg-yellow-500 hover:bg-yellow-600 text-gray-900">
              <UserPlus className="mr-2 h-4 w-4" />
              Assign Driver to Unit
            </Button>
          </DialogTrigger>
          <DialogContent className="max-w-md">
            <DialogHeader>
              <DialogTitle>Assign Driver to Unit</DialogTitle>
            </DialogHeader>
            <div className="space-y-4">
              <div className="space-y-2">
                <Label>Select Driver</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Choose driver" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="DRV-005">Carlos Martinez</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Select Unit</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Choose unit" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="TXN-7890">TXN-7890 (Vacant)</SelectItem>
                    <SelectItem value="TXN-2468">TXN-2468 (Vacant)</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label>Assignment Mode</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Choose mode" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="single">Single Driver (1 Driver per Unit)</SelectItem>
                    <SelectItem value="day-a">Two Drivers - Day A Driver</SelectItem>
                    <SelectItem value="day-b">Two Drivers - Day B Driver</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="p-3 bg-blue-50 rounded-lg text-sm">
                <p className="text-blue-800">
                  <strong>Single Driver:</strong> Driver has full 24-hour responsibility and mandatory boundary payment.
                </p>
                <p className="text-blue-800 mt-2">
                  <strong>Two Drivers:</strong> Drivers alternate daily. Boundary responsibility is based on the scheduled driver of the day.
                </p>
              </div>
              <Button className="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900">
                Assign Driver
              </Button>
            </div>
          </DialogContent>
        </Dialog>
      </div>

      {/* Search */}
      <Card>
        <CardContent className="pt-6">
          <div className="relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
              type="text"
              placeholder="Search by name, ID, or unit..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10"
            />
          </div>
        </CardContent>
      </Card>

      {/* Stats */}
      <div className="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl">{stats.total}</div>
            <p className="text-sm text-gray-500">Total Drivers</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl text-green-600">{stats.active}</div>
            <p className="text-sm text-gray-500">Active</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl text-blue-600">{stats.available}</div>
            <p className="text-sm text-gray-500">Available</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl">{stats.singleDriver}</div>
            <p className="text-sm text-gray-500">Single Driver Units</p>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="text-2xl">{stats.twoDrivers}</div>
            <p className="text-sm text-gray-500">Two Driver Units</p>
          </CardContent>
        </Card>
      </div>

      {/* Vacant Units Alert */}
      {vacantUnits.length > 0 && (
        <Card className="border-orange-200 bg-orange-50">
          <CardHeader>
            <CardTitle className="text-orange-800 flex items-center gap-2">
              <Car className="h-5 w-5" />
              Vacant Units Needing Driver Assignment
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              {vacantUnits.map((unit, idx) => (
                <div key={idx} className="p-3 bg-white rounded-lg flex items-center justify-between">
                  <div>
                    <p className="font-medium">{unit.unitNumber}</p>
                    <p className="text-sm text-gray-600">{unit.status}</p>
                  </div>
                  <Button size="sm" variant="outline">
                    Assign Driver
                  </Button>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Two Driver Rotation Schedule */}
      {rotationSchedule.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle>Two-Driver Rotation Schedule</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-3">
              {rotationSchedule.map((schedule, idx) => (
                <div key={idx} className="p-4 border rounded-lg">
                  <div className="flex items-start justify-between mb-3">
                    <div>
                      <p className="font-medium">{schedule.unitNumber}</p>
                      <p className="text-sm text-green-600">Active Today: {schedule.todayActive}</p>
                    </div>
                    <Badge className="bg-blue-100 text-blue-800">2-Driver Setup</Badge>
                  </div>
                  <div className="grid grid-cols-2 gap-3 text-sm">
                    <div>
                      <p className="text-gray-500">Day A Driver</p>
                      <p>{schedule.dayADriver}</p>
                    </div>
                    <div>
                      <p className="text-gray-500">Day B Driver</p>
                      <p>{schedule.dayBDriver}</p>
                    </div>
                  </div>
                  <div className="mt-2 text-sm text-gray-600">
                    <Calendar className="inline h-4 w-4 mr-1" />
                    Next rotation: {schedule.nextRotation}
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Drivers List */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
        {filteredDrivers.map((driver) => (
          <Card key={driver.id}>
            <CardHeader>
              <div className="flex items-start justify-between">
                <div className="flex items-start gap-3">
                  <Users className="h-5 w-5 text-gray-600 mt-1" />
                  <div>
                    <CardTitle className="text-lg">{driver.name}</CardTitle>
                    <p className="text-sm text-gray-500 mt-1">{driver.id}</p>
                  </div>
                </div>
                <Badge className={getStatusColor(driver.employmentStatus)}>
                  {driver.employmentStatus}
                </Badge>
              </div>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                {/* Contact Info */}
                <div className="space-y-2 text-sm">
                  <div className="flex items-center">
                    <Phone className="h-4 w-4 mr-2 text-gray-500" />
                    {driver.phone}
                  </div>
                  <div className="flex items-center">
                    <Mail className="h-4 w-4 mr-2 text-gray-500" />
                    {driver.email}
                  </div>
                  <div className="flex items-center">
                    <Car className="h-4 w-4 mr-2 text-gray-500" />
                    License: {driver.license}
                  </div>
                </div>

                {/* Assignment Info */}
                <div className="pt-3 border-t">
                  <div className="grid grid-cols-2 gap-3 text-sm">
                    <div>
                      <p className="text-gray-500">Assigned Unit</p>
                      <p className="font-medium">
                        {driver.assignedUnit ? driver.assignedUnit : "Unassigned"}
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-500">Assignment Mode</p>
                      <p className="text-xs">
                        {driver.assignmentMode}
                        {driver.role !== "-" && (
                          <Badge variant="outline" className="ml-1 text-xs">
                            {driver.role}
                          </Badge>
                        )}
                      </p>
                    </div>
                  </div>
                </div>

                {/* Performance */}
                <div className="pt-3 border-t">
                  <div className="grid grid-cols-3 gap-3 text-sm">
                    <div>
                      <p className="text-gray-500">Score</p>
                      <p>⭐ {driver.behaviorScore}</p>
                    </div>
                    <div>
                      <p className="text-gray-500">Trips</p>
                      <p>{driver.totalTrips}</p>
                    </div>
                    <div>
                      <p className="text-gray-500">Boundary</p>
                      <p>₱{(driver.totalBoundary / 1000).toFixed(0)}k</p>
                    </div>
                  </div>
                </div>

                {/* Actions */}
                <div className="pt-3 flex gap-2">
                  <Button variant="outline" size="sm" className="flex-1">
                    View Details
                  </Button>
                  <Button variant="outline" size="sm" className="flex-1">
                    Reassign
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
