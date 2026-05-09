// Mock GPS data for taxi units
// In production, this will be replaced with real-time GPS data from backend

export interface GpsLocation {
  unitId: string;
  plateNumber: string;
  latitude: number;
  longitude: number;
  speed: number; // km/h
  heading: number; // degrees (0-360)
  status: "active" | "idle" | "offline";
  lastUpdate: Date;
  driver?: string;
  address?: string;
}

export interface RoutePoint {
  latitude: number;
  longitude: number;
  timestamp: Date;
  speed: number;
}

// Manila area coordinates (centered around Makati CBD)
const manilaCenter = { lat: 14.5547, lng: 121.0244 };

// Generate random location within Manila area
function getRandomLocation(baseLatOffset = 0, baseLngOffset = 0) {
  return {
    latitude: manilaCenter.lat + (Math.random() - 0.5) * 0.1 + baseLatOffset,
    longitude: manilaCenter.lng + (Math.random() - 0.5) * 0.1 + baseLngOffset,
  };
}

// Mock GPS data for all units
export const mockGpsData: GpsLocation[] = [
  {
    unitId: "1",
    plateNumber: "ABC 1234",
    latitude: 14.5547,
    longitude: 121.0244,
    speed: 35,
    heading: 90,
    status: "active",
    lastUpdate: new Date(),
    driver: "Juan Dela Cruz",
    address: "Ayala Avenue, Makati City"
  },
  {
    unitId: "2",
    plateNumber: "XYZ 5678",
    latitude: 14.5995,
    longitude: 120.9842,
    speed: 0,
    heading: 180,
    status: "idle",
    lastUpdate: new Date(Date.now() - 120000),
    driver: "Pedro Santos",
    address: "EDSA, Quezon City"
  },
  {
    unitId: "3",
    plateNumber: "DEF 9012",
    latitude: 14.5764,
    longitude: 121.0851,
    speed: 45,
    heading: 270,
    status: "active",
    lastUpdate: new Date(),
    driver: "Maria Garcia",
    address: "C5 Road, Taguig City"
  },
  {
    unitId: "4",
    plateNumber: "GHI 3456",
    latitude: 14.5378,
    longitude: 121.0199,
    speed: 25,
    heading: 45,
    status: "active",
    lastUpdate: new Date(),
    driver: "Jose Reyes",
    address: "Roxas Boulevard, Manila"
  },
  {
    unitId: "5",
    plateNumber: "JKL 7890",
    latitude: 14.6091,
    longitude: 121.0223,
    speed: 0,
    heading: 0,
    status: "offline",
    lastUpdate: new Date(Date.now() - 3600000),
    driver: "Ana Lopez",
    address: "Commonwealth Avenue, Quezon City"
  },
  {
    unitId: "6",
    plateNumber: "MNO 2345",
    latitude: 14.5513,
    longitude: 121.0501,
    speed: 30,
    heading: 135,
    status: "active",
    lastUpdate: new Date(),
    driver: "Carlos Rivera",
    address: "Bonifacio Global City, Taguig"
  },
  {
    unitId: "7",
    plateNumber: "PQR 6789",
    latitude: 14.5896,
    longitude: 120.9777,
    speed: 0,
    heading: 90,
    status: "idle",
    lastUpdate: new Date(Date.now() - 300000),
    driver: "Rosa Martinez",
    address: "Quezon Avenue, Quezon City"
  },
  {
    unitId: "8",
    plateNumber: "STU 0123",
    latitude: 14.5243,
    longitude: 121.0315,
    speed: 40,
    heading: 315,
    status: "active",
    lastUpdate: new Date(),
    driver: "Luis Fernandez",
    address: "Pasay Road, Makati City"
  }
];

// Generate mock route history for a unit
export function generateMockRoute(unitId: string, hours = 2): RoutePoint[] {
  const unit = mockGpsData.find(u => u.unitId === unitId);
  if (!unit) return [];

  const points: RoutePoint[] = [];
  const pointsCount = hours * 12; // Every 5 minutes
  const now = new Date();

  for (let i = pointsCount; i >= 0; i--) {
    const timestamp = new Date(now.getTime() - i * 5 * 60000);
    const offset = i / pointsCount;
    
    points.push({
      latitude: unit.latitude + (Math.random() - 0.5) * 0.02,
      longitude: unit.longitude + (Math.random() - 0.5) * 0.02,
      timestamp,
      speed: Math.random() * 60
    });
  }

  return points;
}

// Simulate real-time GPS updates
export function simulateGpsUpdate(location: GpsLocation): GpsLocation {
  const speedChange = (Math.random() - 0.5) * 10;
  const headingChange = (Math.random() - 0.5) * 30;
  
  // Move location based on heading and speed
  const distance = location.speed * 0.0001; // Approximate conversion
  const radians = (location.heading * Math.PI) / 180;
  
  return {
    ...location,
    latitude: location.latitude + Math.cos(radians) * distance,
    longitude: location.longitude + Math.sin(radians) * distance,
    speed: Math.max(0, Math.min(80, location.speed + speedChange)),
    heading: (location.heading + headingChange + 360) % 360,
    lastUpdate: new Date()
  };
}

// Get status color for map markers
export function getStatusColor(status: GpsLocation["status"]): string {
  switch (status) {
    case "active":
      return "#22c55e"; // green
    case "idle":
      return "#eab308"; // yellow
    case "offline":
      return "#ef4444"; // red
    default:
      return "#6b7280"; // gray
  }
}

// Get status label
export function getStatusLabel(status: GpsLocation["status"]): string {
  switch (status) {
    case "active":
      return "Active";
    case "idle":
      return "Idle";
    case "offline":
      return "Offline";
    default:
      return "Unknown";
  }
}
