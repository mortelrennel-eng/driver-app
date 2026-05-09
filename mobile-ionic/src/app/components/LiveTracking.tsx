import { useState, useEffect } from "react";
import { MapContainer, TileLayer, Marker, Popup, useMap } from "react-leaflet";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Input } from "./ui/input";
import { Button } from "./ui/button";
import { 
  Car, 
  Search, 
  Filter, 
  MapPin, 
  Navigation, 
  Clock,
  Video,
  RefreshCw
} from "lucide-react";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { useNavigate } from "react-router-dom";
import api from "../services/api";
import { toast } from "sonner";

// Define the interface based on your API response
interface UnitLocation {
  unit_id: number;
  driver_id: number | null;
  plate_number: string;
  driver_name: string;
  secondary_driver: string | null;
  gps_status: string;
  speed: number;
  ignition_status: boolean;
  last_update: string | null;
  offline_display: string;
  latitude: number | null;
  longitude: number | null;
  angle: number;
  odo: number;
  u_status: string;
  daily_dist: number;
}


// Fix for default marker icon in Leaflet
const createCustomIcon = (color: string, plateNumber: string) => {
  return L.divIcon({
    className: 'custom-div-icon',
    html: `
      <div style="position: relative;">
        <div style="
          background-color: ${color};
          width: 32px;
          height: 32px;
          border-radius: 50% 50% 50% 0;
          position: relative;
          transform: rotate(-45deg);
          border: 3px solid white;
          box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        ">
          <div style="
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(45deg);
            color: white;
            font-weight: bold;
            font-size: 14px;
          ">🚕</div>
        </div>
        <div style="
          position: absolute;
          top: 35px;
          left: 50%;
          transform: translateX(-50%);
          background: white;
          padding: 2px 6px;
          border-radius: 4px;
          white-space: nowrap;
          font-size: 10px;
          font-weight: bold;
          box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        ">${plateNumber}</div>
      </div>
    `,
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
  });
};

function MapUpdater({ center }: { center: [number, number] }) {
  const map = useMap();
  useEffect(() => {
    map.setView(center, map.getZoom());
  }, [center, map]);
  return null;
}

export function LiveTracking() {
  const navigate = useNavigate();
  const [locations, setLocations] = useState<UnitLocation[]>([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [statusFilter, setStatusFilter] = useState<string>("all");
  const [selectedUnit, setSelectedUnit] = useState<UnitLocation | null>(null);
  const [mapCenter, setMapCenter] = useState<[number, number]>([14.5547, 121.0244]);
  const [isAutoRefresh, setIsAutoRefresh] = useState(true);
  const [apiStats, setApiStats] = useState<any>(null);

  const fetchLiveTracking = async () => {
    try {
      const response = await api.get("/live-tracking/units");
      if (response.data.success) {
        setLocations(response.data.units);
        setApiStats(response.data.stats);
        
        // Update map center if we have units and no unit is manually selected
        if (!selectedUnit && response.data.units.length > 0) {
          const firstValid = response.data.units.find((u: UnitLocation) => u.latitude && u.longitude);
          if (firstValid) {
            setMapCenter([firstValid.latitude, firstValid.longitude]);
          }
        }
      }
    } catch (error) {
      console.error("Live Tracking API Error:", error);
      toast.error("Failed to fetch live GPS data.");
    }
  };

  // Initial fetch and auto-refresh
  useEffect(() => {
    fetchLiveTracking();
    if (!isAutoRefresh) return;

    const interval = setInterval(() => {
      fetchLiveTracking();
    }, 30000); // 30 seconds real-time update to save DB connections

    return () => clearInterval(interval);
  }, [isAutoRefresh]);


  // Filter units
  const filteredLocations = locations.filter(loc => {
    const matchesSearch = 
      loc.plate_number.toLowerCase().includes(searchTerm.toLowerCase()) ||
      (loc.driver_name && loc.driver_name.toLowerCase().includes(searchTerm.toLowerCase()));
    
    const matchesStatus = statusFilter === "all" || loc.gps_status === statusFilter;

    // Only show units with valid GPS coordinates on the map
    return matchesSearch && matchesStatus && loc.latitude !== null && loc.longitude !== null;
  });

  // Statistics fallback
  const stats = apiStats || {
    total: locations.length,
    active: locations.filter(l => l.gps_status === "moving" || l.gps_status === "idle").length,
    idle: locations.filter(l => l.gps_status === "idle").length,
    offline: locations.filter(l => l.gps_status === "offline").length,
    avgSpeed: Math.round(
      locations.filter(l => l.speed > 0).reduce((sum, l) => sum + l.speed, 0) / 
      (locations.filter(l => l.speed > 0).length || 1)
    )
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case "moving": return "#22c55e"; // green
      case "idle": return "#eab308"; // yellow
      case "stopped": return "#f97316"; // orange
      case "offline": return "#ef4444"; // red
      default: return "#6b7280"; // gray
    }
  };

  const handleUnitClick = (location: UnitLocation) => {
    setSelectedUnit(location);
    if (location.latitude && location.longitude) {
      setMapCenter([location.latitude, location.longitude]);
    }
  };

  const viewUnitDetails = (unitId: number) => {
    navigate(`/live-tracking/${unitId}`);
  };


  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl">Live Fleet Tracking</h2>
          <p className="text-gray-600">Real-time GPS monitoring of all taxi units</p>
        </div>
        <Button
          variant={isAutoRefresh ? "default" : "outline"}
          onClick={() => setIsAutoRefresh(!isAutoRefresh)}
          className={isAutoRefresh ? "bg-green-600 hover:bg-green-700" : ""}
        >
          <RefreshCw className={`h-4 w-4 mr-2 ${isAutoRefresh ? "animate-spin" : ""}`} />
          {isAutoRefresh ? "Auto-Refresh ON" : "Auto-Refresh OFF"}
        </Button>
      </div>

      {/* Statistics Cards */}
      <div className="grid grid-cols-1 md:grid-cols-5 gap-4">
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Total Units</CardDescription>
            <CardTitle className="text-3xl">{stats.total}</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Active</CardDescription>
            <CardTitle className="text-3xl text-green-600">{stats.active}</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Idle</CardDescription>
            <CardTitle className="text-3xl text-yellow-600">{stats.idle}</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Offline</CardDescription>
            <CardTitle className="text-3xl text-red-600">{stats.offline}</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription>Avg Speed</CardDescription>
            <CardTitle className="text-3xl">{stats.avgSpeed} <span className="text-base">km/h</span></CardTitle>
          </CardHeader>
        </Card>
      </div>

      {/* Filters and Search */}
      <div className="flex flex-col sm:flex-row gap-4">
        <div className="flex-1 relative">
          <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <Input
            placeholder="Search by plate number, driver, or location..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="pl-10"
          />
        </div>
        <Select value={statusFilter} onValueChange={setStatusFilter}>
          <SelectTrigger className="w-full sm:w-48">
            <Filter className="h-4 w-4 mr-2" />
            <SelectValue placeholder="Filter by status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Status</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="idle">Idle</SelectItem>
            <SelectItem value="offline">Offline</SelectItem>
          </SelectContent>
        </Select>
      </div>

      {/* Map and Unit List */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Map */}
        <Card className="lg:col-span-2">
          <CardHeader>
            <CardTitle>Live Map View</CardTitle>
            <CardDescription>
              {filteredLocations.length} unit(s) displayed on map
            </CardDescription>
          </CardHeader>
          <CardContent className="p-0">
            <div className="h-[600px] w-full relative">
              <MapContainer
                center={mapCenter}
                zoom={12}
                style={{ height: "100%", width: "100%" }}
                className="rounded-b-lg"
              >
                <TileLayer
                  attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                  url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                />
                <MapUpdater center={mapCenter} />
                {filteredLocations.map((location) => (
                  <Marker
                    key={location.unit_id}
                    position={[location.latitude as number, location.longitude as number]}
                    icon={createCustomIcon(getStatusColor(location.gps_status), location.plate_number)}
                    eventHandlers={{
                      click: () => handleUnitClick(location)
                    }}
                  >
                    <Popup>
                      <div className="space-y-2 min-w-[200px]">
                        <div className="flex items-center justify-between">
                          <h3 className="font-bold text-lg">{location.plate_number}</h3>
                          <Badge variant={
                            location.gps_status === "moving" ? "default" :
                            location.gps_status === "idle" ? "secondary" : "destructive"
                          }>
                            {location.gps_status.toUpperCase()}
                          </Badge>
                        </div>
                        {location.driver_name && location.driver_name !== 'None' && (
                          <p className="text-sm"><strong>Driver:</strong> {location.driver_name}</p>
                        )}
                        <p className="text-sm"><strong>Speed:</strong> {location.speed} km/h</p>
                        <p className="text-xs text-gray-500">
                          Last update: {location.last_update ? new Date(location.last_update + ' UTC').toLocaleTimeString() : 'Unknown'}
                        </p>
                        {location.gps_status === 'offline' && location.offline_display && (
                          <p className="text-xs text-red-500">Offline for: {location.offline_display}</p>
                        )}
                        <Button 
                          size="sm" 
                          className="w-full mt-2"
                          onClick={() => viewUnitDetails(location.unit_id)}
                        >
                          View Details
                        </Button>
                      </div>
                    </Popup>
                  </Marker>
                ))}
              </MapContainer>
            </div>
          </CardContent>
        </Card>

        {/* Unit List */}
        <Card>
          <CardHeader>
            <CardTitle>Unit List</CardTitle>
            <CardDescription>Click to center on map</CardDescription>
          </CardHeader>
          <CardContent className="p-0">
            <div className="max-h-[600px] overflow-y-auto">
              {filteredLocations.map((location) => (
                <div
                  key={location.unit_id}
                  className={`p-4 border-b hover:bg-gray-50 cursor-pointer transition-colors ${
                    selectedUnit?.unit_id === location.unit_id ? "bg-yellow-50" : ""
                  }`}
                  onClick={() => handleUnitClick(location)}
                >
                  <div className="flex items-start justify-between mb-2">
                    <div className="flex items-center space-x-2">
                      <Car className="h-5 w-5 text-gray-600" />
                      <span className="font-semibold">{location.plate_number}</span>
                    </div>
                    <Badge variant={
                      location.gps_status === "moving" ? "default" :
                      location.gps_status === "idle" ? "secondary" : "destructive"
                    }>
                      {location.gps_status.toUpperCase()}
                    </Badge>
                  </div>
                  {location.driver_name && location.driver_name !== 'None' && (
                    <p className="text-sm text-gray-600 mb-1">{location.driver_name}</p>
                  )}
                  <div className="flex items-center space-x-4 text-xs text-gray-500 mb-2">
                    <span className="flex items-center">
                      <Navigation className="h-3 w-3 mr-1" />
                      {location.speed} km/h
                    </span>
                    <span className="flex items-center">
                      <Clock className="h-3 w-3 mr-1" />
                      {location.last_update ? new Date(location.last_update + ' UTC').toLocaleTimeString() : 'N/A'}
                    </span>
                  </div>
                  <div className="flex gap-2">
                    <Button
                      size="sm"
                      variant="outline"
                      className="flex-1"
                      onClick={(e) => {
                        e.stopPropagation();
                        viewUnitDetails(location.unit_id);
                      }}
                    >
                      <MapPin className="h-3 w-3 mr-1" />
                      Track
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      className="flex-1"
                      onClick={(e) => {
                        e.stopPropagation();
                        navigate(`/live-tracking/${location.unit_id}/dashcam`);
                      }}
                    >
                      <Video className="h-3 w-3 mr-1" />
                      Dashcam
                    </Button>
                  </div>
                </div>
              ))}
              {filteredLocations.length === 0 && (
                <div className="p-8 text-center text-gray-500">
                  <Car className="h-12 w-12 mx-auto mb-2 opacity-50" />
                  <p>No units found</p>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
