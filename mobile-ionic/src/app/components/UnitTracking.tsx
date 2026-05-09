import { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { MapContainer, TileLayer, Marker, Polyline, Popup } from "react-leaflet";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "./ui/tabs";
import {
  ArrowLeft,
  Car,
  Navigation,
  Clock,
  MapPin,
  Video,
  Activity,
  Route,
  Gauge,
  Calendar
} from "lucide-react";
import { mockGpsData, generateMockRoute, getStatusColor, getStatusLabel, type GpsLocation, type RoutePoint } from "../utils/mockGpsData";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";

const createUnitIcon = (color: string) => {
  return L.divIcon({
    className: 'custom-div-icon',
    html: `
      <div style="
        background-color: ${color};
        width: 40px;
        height: 40px;
        border-radius: 50% 50% 50% 0;
        position: relative;
        transform: rotate(-45deg);
        border: 4px solid white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.4);
      ">
        <div style="
          position: absolute;
          top: 50%;
          left: 50%;
          transform: translate(-50%, -50%) rotate(45deg);
          font-size: 20px;
        ">🚕</div>
      </div>
    `,
    iconSize: [40, 40],
    iconAnchor: [20, 40],
    popupAnchor: [0, -40]
  });
};

export function UnitTracking() {
  const { unitId } = useParams<{ unitId: string }>();
  const navigate = useNavigate();
  const [unit, setUnit] = useState<GpsLocation | null>(null);
  const [routeHistory, setRouteHistory] = useState<RoutePoint[]>([]);
  const [timeRange, setTimeRange] = useState<string>("2");

  useEffect(() => {
    if (!unitId) return;

    const foundUnit = mockGpsData.find(u => u.unitId === unitId);
    if (foundUnit) {
      setUnit(foundUnit);
      setRouteHistory(generateMockRoute(unitId, parseInt(timeRange)));
    }
  }, [unitId, timeRange]);

  if (!unit) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="text-center">
          <Car className="h-12 w-12 mx-auto mb-4 text-gray-400" />
          <p className="text-gray-600">Unit not found</p>
          <Button onClick={() => navigate("/live-tracking")} className="mt-4">
            Back to Fleet Tracking
          </Button>
        </div>
      </div>
    );
  }

  const routeCoordinates: [number, number][] = routeHistory.map(point => [
    point.latitude,
    point.longitude
  ]);

  const totalDistance = routeHistory.length * 0.5; // Mock calculation
  const maxSpeed = Math.max(...routeHistory.map(p => p.speed));
  const avgSpeed = routeHistory.reduce((sum, p) => sum + p.speed, 0) / routeHistory.length || 0;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <Button variant="ghost" onClick={() => navigate("/live-tracking")}>
            <ArrowLeft className="h-5 w-5" />
          </Button>
          <div>
            <div className="flex items-center space-x-3">
              <h2 className="text-3xl">{unit.plateNumber}</h2>
              <Badge variant={
                unit.status === "active" ? "default" :
                unit.status === "idle" ? "secondary" : "destructive"
              }>
                {getStatusLabel(unit.status)}
              </Badge>
            </div>
            <p className="text-gray-600">Individual Unit Tracking & Route History</p>
          </div>
        </div>
        <div className="flex gap-2">
          <Button
            variant="outline"
            onClick={() => navigate(`/live-tracking/${unitId}/dashcam`)}
          >
            <Video className="h-4 w-4 mr-2" />
            View Dashcam
          </Button>
          <Button
            variant="outline"
            onClick={() => navigate("/units")}
          >
            <Car className="h-4 w-4 mr-2" />
            Unit Details
          </Button>
        </div>
      </div>

      {/* Quick Stats */}
      <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Card>
          <CardHeader className="pb-2">
            <CardDescription className="flex items-center">
              <Navigation className="h-4 w-4 mr-2" />
              Current Speed
            </CardDescription>
            <CardTitle className="text-2xl">{unit.speed} km/h</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription className="flex items-center">
              <Gauge className="h-4 w-4 mr-2" />
              Average Speed
            </CardDescription>
            <CardTitle className="text-2xl">{avgSpeed.toFixed(0)} km/h</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription className="flex items-center">
              <Route className="h-4 w-4 mr-2" />
              Distance Covered
            </CardDescription>
            <CardTitle className="text-2xl">{totalDistance.toFixed(1)} km</CardTitle>
          </CardHeader>
        </Card>
        <Card>
          <CardHeader className="pb-2">
            <CardDescription className="flex items-center">
              <Clock className="h-4 w-4 mr-2" />
              Last Update
            </CardDescription>
            <CardTitle className="text-lg">{unit.lastUpdate.toLocaleTimeString()}</CardTitle>
          </CardHeader>
        </Card>
      </div>

      <Tabs defaultValue="live" className="space-y-4">
        <TabsList>
          <TabsTrigger value="live">Live Location</TabsTrigger>
          <TabsTrigger value="route">Route History</TabsTrigger>
          <TabsTrigger value="details">Unit Details</TabsTrigger>
        </TabsList>

        {/* Live Location Tab */}
        <TabsContent value="live" className="space-y-4">
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <Card className="lg:col-span-2">
              <CardHeader>
                <CardTitle>Current Location</CardTitle>
                <CardDescription>Real-time GPS position</CardDescription>
              </CardHeader>
              <CardContent className="p-0">
                <div className="h-[500px] w-full">
                  <MapContainer
                    center={[unit.latitude, unit.longitude]}
                    zoom={15}
                    style={{ height: "100%", width: "100%" }}
                    className="rounded-b-lg"
                  >
                    <TileLayer
                      attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                      url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                    />
                    <Marker
                      position={[unit.latitude, unit.longitude]}
                      icon={createUnitIcon(getStatusColor(unit.status))}
                    >
                      <Popup>
                        <div className="space-y-2">
                          <h3 className="font-bold">{unit.plateNumber}</h3>
                          <p className="text-sm">Speed: {unit.speed} km/h</p>
                          <p className="text-sm">Heading: {unit.heading}°</p>
                        </div>
                      </Popup>
                    </Marker>
                  </MapContainer>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Location Info</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {unit.driver && (
                  <div>
                    <p className="text-sm text-gray-600 mb-1">Current Driver</p>
                    <p className="font-semibold">{unit.driver}</p>
                  </div>
                )}
                <div>
                  <p className="text-sm text-gray-600 mb-1">Address</p>
                  <p className="font-semibold">{unit.address}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Coordinates</p>
                  <p className="font-mono text-sm">
                    {unit.latitude.toFixed(6)}, {unit.longitude.toFixed(6)}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Heading</p>
                  <p className="font-semibold">{unit.heading}° {getCompassDirection(unit.heading)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Status</p>
                  <Badge variant={
                    unit.status === "active" ? "default" :
                    unit.status === "idle" ? "secondary" : "destructive"
                  }>
                    {getStatusLabel(unit.status)}
                  </Badge>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Last Updated</p>
                  <p className="text-sm">{unit.lastUpdate.toLocaleString()}</p>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        {/* Route History Tab */}
        <TabsContent value="route" className="space-y-4">
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <div>
                  <CardTitle>Route History</CardTitle>
                  <CardDescription>GPS tracking history and traveled route</CardDescription>
                </div>
                <Select value={timeRange} onValueChange={setTimeRange}>
                  <SelectTrigger className="w-48">
                    <Calendar className="h-4 w-4 mr-2" />
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="1">Last 1 Hour</SelectItem>
                    <SelectItem value="2">Last 2 Hours</SelectItem>
                    <SelectItem value="4">Last 4 Hours</SelectItem>
                    <SelectItem value="8">Last 8 Hours</SelectItem>
                    <SelectItem value="24">Last 24 Hours</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </CardHeader>
            <CardContent className="p-0">
              <div className="h-[500px] w-full">
                <MapContainer
                  center={[unit.latitude, unit.longitude]}
                  zoom={13}
                  style={{ height: "100%", width: "100%" }}
                  className="rounded-b-lg"
                >
                  <TileLayer
                    attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                  />
                  {routeCoordinates.length > 0 && (
                    <Polyline
                      positions={routeCoordinates}
                      pathOptions={{
                        color: '#3b82f6',
                        weight: 4,
                        opacity: 0.7
                      }}
                    />
                  )}
                  <Marker
                    position={[unit.latitude, unit.longitude]}
                    icon={createUnitIcon(getStatusColor(unit.status))}
                  >
                    <Popup>Current Position</Popup>
                  </Marker>
                </MapContainer>
              </div>
            </CardContent>
          </Card>

          {/* Route Statistics */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card>
              <CardHeader>
                <CardDescription>Total Distance</CardDescription>
                <CardTitle className="text-2xl">{totalDistance.toFixed(2)} km</CardTitle>
              </CardHeader>
            </Card>
            <Card>
              <CardHeader>
                <CardDescription>Max Speed</CardDescription>
                <CardTitle className="text-2xl">{maxSpeed.toFixed(0)} km/h</CardTitle>
              </CardHeader>
            </Card>
            <Card>
              <CardHeader>
                <CardDescription>Data Points</CardDescription>
                <CardTitle className="text-2xl">{routeHistory.length}</CardTitle>
              </CardHeader>
            </Card>
          </div>
        </TabsContent>

        {/* Unit Details Tab */}
        <TabsContent value="details" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Unit Information</CardTitle>
              <CardDescription>Complete details about this taxi unit</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <h4 className="font-semibold mb-3">Vehicle Details</h4>
                  <dl className="space-y-2">
                    <div>
                      <dt className="text-sm text-gray-600">Plate Number</dt>
                      <dd className="font-semibold">{unit.plateNumber}</dd>
                    </div>
                    <div>
                      <dt className="text-sm text-gray-600">Unit ID</dt>
                      <dd className="font-semibold">#{unit.unitId}</dd>
                    </div>
                    <div>
                      <dt className="text-sm text-gray-600">Status</dt>
                      <dd>
                        <Badge variant={
                          unit.status === "active" ? "default" :
                          unit.status === "idle" ? "secondary" : "destructive"
                        }>
                          {getStatusLabel(unit.status)}
                        </Badge>
                      </dd>
                    </div>
                  </dl>
                </div>

                <div>
                  <h4 className="font-semibold mb-3">Current Driver</h4>
                  <dl className="space-y-2">
                    <div>
                      <dt className="text-sm text-gray-600">Name</dt>
                      <dd className="font-semibold">{unit.driver || "Not assigned"}</dd>
                    </div>
                  </dl>
                </div>

                <div>
                  <h4 className="font-semibold mb-3">GPS Information</h4>
                  <dl className="space-y-2">
                    <div>
                      <dt className="text-sm text-gray-600">Latitude</dt>
                      <dd className="font-mono text-sm">{unit.latitude.toFixed(6)}</dd>
                    </div>
                    <div>
                      <dt className="text-sm text-gray-600">Longitude</dt>
                      <dd className="font-mono text-sm">{unit.longitude.toFixed(6)}</dd>
                    </div>
                    <div>
                      <dt className="text-sm text-gray-600">Last Update</dt>
                      <dd className="text-sm">{unit.lastUpdate.toLocaleString()}</dd>
                    </div>
                  </dl>
                </div>

                <div>
                  <h4 className="font-semibold mb-3">Motion Data</h4>
                  <dl className="space-y-2">
                    <div>
                      <dt className="text-sm text-gray-600">Current Speed</dt>
                      <dd className="font-semibold">{unit.speed} km/h</dd>
                    </div>
                    <div>
                      <dt className="text-sm text-gray-600">Heading</dt>
                      <dd className="font-semibold">{unit.heading}° {getCompassDirection(unit.heading)}</dd>
                    </div>
                  </dl>
                </div>
              </div>

              <div className="pt-4 border-t">
                <Button onClick={() => navigate("/units")} className="mr-2">
                  View Full Unit Details
                </Button>
                <Button variant="outline" onClick={() => navigate(`/live-tracking/${unitId}/dashcam`)}>
                  <Video className="h-4 w-4 mr-2" />
                  View Dashcam
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}

function getCompassDirection(heading: number): string {
  const directions = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
  const index = Math.round(heading / 45) % 8;
  return directions[index];
}
