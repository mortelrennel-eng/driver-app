import { useState, useEffect, useRef } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "./ui/tabs";
import {
  ArrowLeft,
  Video,
  Play,
  Pause,
  Download,
  Camera,
  AlertTriangle,
  Calendar,
  Clock,
  MapPin,
  Maximize2,
  Volume2,
  VolumeX
} from "lucide-react";
import { mockGpsData, type GpsLocation } from "../utils/mockGpsData";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "./ui/select";
import { Slider } from "./ui/slider";

interface DashcamRecording {
  id: string;
  timestamp: Date;
  duration: number; // seconds
  fileSize: string;
  thumbnail: string;
  type: "normal" | "incident" | "flagged";
  location: string;
}

// Mock dashcam recordings
const generateMockRecordings = (unitId: string): DashcamRecording[] => {
  const recordings: DashcamRecording[] = [];
  const now = new Date();

  for (let i = 0; i < 20; i++) {
    const timestamp = new Date(now.getTime() - i * 3600000); // Every hour
    recordings.push({
      id: `${unitId}-rec-${i}`,
      timestamp,
      duration: 300 + Math.random() * 600, // 5-15 minutes
      fileSize: `${(Math.random() * 500 + 100).toFixed(0)} MB`,
      thumbnail: `https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=400&h=225&fit=crop`,
      type: i % 8 === 0 ? "incident" : i % 5 === 0 ? "flagged" : "normal",
      location: "Makati City, Metro Manila"
    });
  }

  return recordings;
};

export function DashcamViewer() {
  const { unitId } = useParams<{ unitId: string }>();
  const navigate = useNavigate();
  const videoRef = useRef<HTMLVideoElement>(null);
  
  const [unit, setUnit] = useState<GpsLocation | null>(null);
  const [isLive, setIsLive] = useState(true);
  const [isPlaying, setIsPlaying] = useState(true);
  const [isMuted, setIsMuted] = useState(false);
  const [recordings, setRecordings] = useState<DashcamRecording[]>([]);
  const [selectedRecording, setSelectedRecording] = useState<DashcamRecording | null>(null);
  const [filterType, setFilterType] = useState<string>("all");
  const [currentTime, setCurrentTime] = useState(0);
  const [duration, setDuration] = useState(0);

  useEffect(() => {
    if (!unitId) return;

    const foundUnit = mockGpsData.find(u => u.unitId === unitId);
    if (foundUnit) {
      setUnit(foundUnit);
      setRecordings(generateMockRecordings(unitId));
    }
  }, [unitId]);

  const handlePlayPause = () => {
    if (videoRef.current) {
      if (isPlaying) {
        videoRef.current.pause();
      } else {
        videoRef.current.play();
      }
      setIsPlaying(!isPlaying);
    }
  };

  const handleMuteToggle = () => {
    if (videoRef.current) {
      videoRef.current.muted = !isMuted;
      setIsMuted(!isMuted);
    }
  };

  const handleFullscreen = () => {
    if (videoRef.current) {
      videoRef.current.requestFullscreen();
    }
  };

  const formatTime = (seconds: number): string => {
    const mins = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60);
    return `${mins}:${secs.toString().padStart(2, '0')}`;
  };

  const filteredRecordings = recordings.filter(rec => {
    if (filterType === "all") return true;
    return rec.type === filterType;
  });

  const recordingStats = {
    total: recordings.length,
    incidents: recordings.filter(r => r.type === "incident").length,
    flagged: recordings.filter(r => r.type === "flagged").length,
    normal: recordings.filter(r => r.type === "normal").length
  };

  if (!unit) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="text-center">
          <Video className="h-12 w-12 mx-auto mb-4 text-gray-400" />
          <p className="text-gray-600">Unit not found</p>
          <Button onClick={() => navigate("/live-tracking")} className="mt-4">
            Back to Fleet Tracking
          </Button>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <Button variant="ghost" onClick={() => navigate(`/live-tracking/${unitId}`)}>
            <ArrowLeft className="h-5 w-5" />
          </Button>
          <div>
            <div className="flex items-center space-x-3">
              <h2 className="text-3xl">{unit.plateNumber} - Dashcam</h2>
              {isLive && (
                <Badge className="bg-red-600">
                  <span className="animate-pulse mr-2">●</span> LIVE
                </Badge>
              )}
            </div>
            <p className="text-gray-600">Real-time and recorded dashcam footage</p>
          </div>
        </div>
        <div className="flex gap-2">
          <Button
            variant={isLive ? "default" : "outline"}
            onClick={() => setIsLive(true)}
          >
            <Video className="h-4 w-4 mr-2" />
            Live Feed
          </Button>
          <Button
            variant={!isLive ? "default" : "outline"}
            onClick={() => setIsLive(false)}
          >
            <Camera className="h-4 w-4 mr-2" />
            Recordings
          </Button>
        </div>
      </div>

      {isLive ? (
        /* Live Feed View */
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <Card className="lg:col-span-2">
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle>Live Dashcam Feed</CardTitle>
                <Badge variant="destructive" className="animate-pulse">
                  ● RECORDING
                </Badge>
              </div>
            </CardHeader>
            <CardContent className="p-0">
              <div className="relative bg-black aspect-video">
                {/* Mock video player - in production, this would be a real video stream */}
                <div className="absolute inset-0 flex items-center justify-center">
                  <div className="text-center text-white">
                    <Video className="h-16 w-16 mx-auto mb-4 opacity-50" />
                    <p className="text-lg mb-2">Live Dashcam Feed</p>
                    <p className="text-sm text-gray-400">
                      Mock stream for {unit.plateNumber}
                    </p>
                    <p className="text-xs text-gray-500 mt-2">
                      In production, this will show real-time video from the dashcam
                    </p>
                  </div>
                </div>

                {/* Live info overlay */}
                <div className="absolute top-4 left-4 space-y-2">
                  <Badge className="bg-red-600 bg-opacity-90">
                    <span className="animate-pulse mr-2">●</span> LIVE
                  </Badge>
                  <div className="bg-black bg-opacity-70 text-white px-3 py-2 rounded text-sm">
                    <div className="flex items-center space-x-2">
                      <Clock className="h-4 w-4" />
                      <span>{new Date().toLocaleTimeString()}</span>
                    </div>
                  </div>
                </div>

                <div className="absolute top-4 right-4">
                  <div className="bg-black bg-opacity-70 text-white px-3 py-2 rounded text-sm">
                    <div className="flex items-center space-x-2">
                      <MapPin className="h-4 w-4" />
                      <span>{unit.address}</span>
                    </div>
                  </div>
                </div>

                {/* Speed overlay */}
                <div className="absolute bottom-4 left-4">
                  <div className="bg-black bg-opacity-70 text-white px-4 py-3 rounded-lg">
                    <div className="text-3xl font-bold">{unit.speed}</div>
                    <div className="text-xs text-gray-400">km/h</div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Live Feed Info</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <p className="text-sm text-gray-600 mb-1">Unit</p>
                <p className="font-semibold">{unit.plateNumber}</p>
              </div>
              {unit.driver && (
                <div>
                  <p className="text-sm text-gray-600 mb-1">Current Driver</p>
                  <p className="font-semibold">{unit.driver}</p>
                </div>
              )}
              <div>
                <p className="text-sm text-gray-600 mb-1">Status</p>
                <Badge variant={unit.status === "active" ? "default" : "secondary"}>
                  {unit.status}
                </Badge>
              </div>
              <div>
                <p className="text-sm text-gray-600 mb-1">Current Location</p>
                <p className="text-sm">{unit.address}</p>
              </div>
              <div>
                <p className="text-sm text-gray-600 mb-1">Stream Quality</p>
                <Badge variant="outline">720p HD</Badge>
              </div>
              <div>
                <p className="text-sm text-gray-600 mb-1">Recording Status</p>
                <div className="flex items-center space-x-2">
                  <div className="h-2 w-2 bg-red-600 rounded-full animate-pulse" />
                  <span className="text-sm">Recording</span>
                </div>
              </div>
              <div className="pt-4 border-t space-y-2">
                <Button className="w-full" variant="outline">
                  <Camera className="h-4 w-4 mr-2" />
                  Capture Screenshot
                </Button>
                <Button className="w-full" variant="outline">
                  <Download className="h-4 w-4 mr-2" />
                  Download Live Recording
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      ) : (
        /* Recordings View */
        <div className="space-y-6">
          {/* Stats */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Card>
              <CardHeader className="pb-2">
                <CardDescription>Total Recordings</CardDescription>
                <CardTitle className="text-2xl">{recordingStats.total}</CardTitle>
              </CardHeader>
            </Card>
            <Card>
              <CardHeader className="pb-2">
                <CardDescription>Incident Reports</CardDescription>
                <CardTitle className="text-2xl text-red-600">{recordingStats.incidents}</CardTitle>
              </CardHeader>
            </Card>
            <Card>
              <CardHeader className="pb-2">
                <CardDescription>Flagged Videos</CardDescription>
                <CardTitle className="text-2xl text-yellow-600">{recordingStats.flagged}</CardTitle>
              </CardHeader>
            </Card>
            <Card>
              <CardHeader className="pb-2">
                <CardDescription>Normal Recordings</CardDescription>
                <CardTitle className="text-2xl text-green-600">{recordingStats.normal}</CardTitle>
              </CardHeader>
            </Card>
          </div>

          {/* Filter */}
          <div className="flex justify-between items-center">
            <h3 className="text-xl">Recorded Videos</h3>
            <Select value={filterType} onValueChange={setFilterType}>
              <SelectTrigger className="w-48">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Recordings</SelectItem>
                <SelectItem value="incident">Incidents Only</SelectItem>
                <SelectItem value="flagged">Flagged Videos</SelectItem>
                <SelectItem value="normal">Normal Recordings</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {/* Recordings Grid */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            {filteredRecordings.map((recording) => (
              <Card
                key={recording.id}
                className="cursor-pointer hover:shadow-lg transition-shadow"
                onClick={() => setSelectedRecording(recording)}
              >
                <CardContent className="p-0">
                  <div className="relative">
                    <img
                      src={recording.thumbnail}
                      alt="Recording thumbnail"
                      className="w-full h-40 object-cover rounded-t-lg"
                    />
                    <div className="absolute top-2 right-2">
                      {recording.type === "incident" && (
                        <Badge variant="destructive">
                          <AlertTriangle className="h-3 w-3 mr-1" />
                          Incident
                        </Badge>
                      )}
                      {recording.type === "flagged" && (
                        <Badge className="bg-yellow-600">Flagged</Badge>
                      )}
                    </div>
                    <div className="absolute bottom-2 right-2 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                      {formatTime(recording.duration)}
                    </div>
                  </div>
                  <div className="p-3 space-y-2">
                    <div className="flex items-center text-sm text-gray-600">
                      <Calendar className="h-3 w-3 mr-1" />
                      {recording.timestamp.toLocaleDateString()}
                    </div>
                    <div className="flex items-center text-sm text-gray-600">
                      <Clock className="h-3 w-3 mr-1" />
                      {recording.timestamp.toLocaleTimeString()}
                    </div>
                    <div className="flex items-center text-sm text-gray-600">
                      <MapPin className="h-3 w-3 mr-1" />
                      {recording.location}
                    </div>
                    <div className="text-xs text-gray-500">
                      Size: {recording.fileSize}
                    </div>
                    <div className="flex gap-2 mt-2">
                      <Button size="sm" className="flex-1">
                        <Play className="h-3 w-3 mr-1" />
                        Play
                      </Button>
                      <Button size="sm" variant="outline">
                        <Download className="h-3 w-3" />
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>

          {filteredRecordings.length === 0 && (
            <Card>
              <CardContent className="py-12 text-center">
                <Video className="h-12 w-12 mx-auto mb-4 text-gray-400" />
                <p className="text-gray-600">No recordings found</p>
              </CardContent>
            </Card>
          )}
        </div>
      )}
    </div>
  );
}
