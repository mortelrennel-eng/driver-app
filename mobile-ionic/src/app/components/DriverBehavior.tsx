import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import { Badge } from "./ui/badge";
import { Button } from "./ui/button";
import { Progress } from "./ui/progress";
import { Award, TrendingUp, TrendingDown, AlertTriangle, CheckCircle, Trophy } from "lucide-react";

const driverBehaviorData = [
  {
    id: "DRV-001",
    name: "Juan Dela Cruz",
    overallScore: 4.8,
    metrics: {
      boundaryTimeliness: 95,
      shortageFrequency: 2,
      accidentHistory: 0,
      complaints: 1,
      attendanceConsistency: 98,
    },
    incentiveEligible: true,
    recentIncidents: [
      { date: "2026-01-15", type: "Complaint", details: "Late pickup reported by passenger" },
    ],
  },
  {
    id: "DRV-002",
    name: "Pedro Reyes",
    overallScore: 4.9,
    metrics: {
      boundaryTimeliness: 100,
      shortageFrequency: 0,
      accidentHistory: 0,
      complaints: 0,
      attendanceConsistency: 100,
    },
    incentiveEligible: true,
    recentIncidents: [],
  },
  {
    id: "DRV-003",
    name: "Maria Santos",
    overallScore: 4.7,
    metrics: {
      boundaryTimeliness: 92,
      shortageFrequency: 3,
      accidentHistory: 0,
      complaints: 2,
      attendanceConsistency: 95,
    },
    incentiveEligible: true,
    recentIncidents: [
      { date: "2026-02-01", type: "Shortage", details: "₱200 boundary shortage" },
      { date: "2026-01-28", type: "Complaint", details: "Rude behavior reported" },
    ],
  },
  {
    id: "DRV-004",
    name: "Ana Garcia",
    overallScore: 4.6,
    metrics: {
      boundaryTimeliness: 88,
      shortageFrequency: 5,
      accidentHistory: 1,
      complaints: 3,
      attendanceConsistency: 90,
    },
    incentiveEligible: false,
    recentIncidents: [
      { date: "2026-02-03", type: "Accident", details: "Minor fender bender - side mirror damage" },
      { date: "2026-01-20", type: "Shortage", details: "₱500 boundary shortage" },
      { date: "2026-01-10", type: "Complaint", details: "Overcharging passenger" },
    ],
  },
  {
    id: "DRV-005",
    name: "Carlos Martinez",
    overallScore: 4.5,
    metrics: {
      boundaryTimeliness: 85,
      shortageFrequency: 6,
      accidentHistory: 1,
      complaints: 4,
      attendanceConsistency: 87,
    },
    incentiveEligible: false,
    recentIncidents: [
      { date: "2026-02-05", type: "Shortage", details: "₱300 boundary shortage" },
      { date: "2026-01-30", type: "Late Payment", details: "Boundary paid 2 days late" },
      { date: "2026-01-15", type: "Accident", details: "Rear-end collision - minor damage" },
    ],
  },
];

const topPerformers = driverBehaviorData
  .filter((d) => d.incentiveEligible)
  .sort((a, b) => b.overallScore - a.overallScore)
  .slice(0, 3);

const atRiskDrivers = driverBehaviorData
  .filter((d) => d.overallScore < 4.7 || d.metrics.shortageFrequency > 4)
  .sort((a, b) => a.overallScore - b.overallScore);

export function DriverBehavior() {
  const getScoreColor = (score: number) => {
    if (score >= 4.8) return "text-green-600";
    if (score >= 4.5) return "text-blue-600";
    if (score >= 4.0) return "text-yellow-600";
    return "text-red-600";
  };

  const getScoreBadgeColor = (score: number) => {
    if (score >= 4.8) return "bg-green-100 text-green-800";
    if (score >= 4.5) return "bg-blue-100 text-blue-800";
    if (score >= 4.0) return "bg-yellow-100 text-yellow-800";
    return "bg-red-100 text-red-800";
  };

  const getIncidentColor = (type: string) => {
    switch (type) {
      case "Accident":
        return "bg-red-100 text-red-800";
      case "Shortage":
      case "Late Payment":
        return "bg-yellow-100 text-yellow-800";
      case "Complaint":
        return "bg-orange-100 text-orange-800";
      default:
        return "bg-gray-100 text-gray-800";
    }
  };

  const avgScore = (
    driverBehaviorData.reduce((sum, d) => sum + d.overallScore, 0) / driverBehaviorData.length
  ).toFixed(2);

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl">Driver Behavior & Performance Tracking</h2>
        <p className="text-sm text-gray-500 mt-1">
          Monitor driver behavior metrics and manage incentive eligibility
        </p>
      </div>

      {/* Summary Stats */}
      <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <div className="text-2xl">{avgScore}</div>
                <p className="text-sm text-gray-500">Average Score</p>
              </div>
              <Award className="h-8 w-8 text-yellow-600" />
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <div className="text-2xl text-green-600">{topPerformers.length}</div>
                <p className="text-sm text-gray-500">Top Performers</p>
              </div>
              <Trophy className="h-8 w-8 text-green-600" />
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <div className="text-2xl text-red-600">{atRiskDrivers.length}</div>
                <p className="text-sm text-gray-500">At Risk</p>
              </div>
              <AlertTriangle className="h-8 w-8 text-red-600" />
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <div className="text-2xl text-blue-600">
                  {driverBehaviorData.filter((d) => d.incentiveEligible).length}
                </div>
                <p className="text-sm text-gray-500">Incentive Eligible</p>
              </div>
              <CheckCircle className="h-8 w-8 text-blue-600" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Top Performers */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Trophy className="h-5 w-5 text-yellow-600" />
            Top Performing Drivers
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {topPerformers.map((driver, index) => (
              <div key={driver.id} className="flex items-center gap-4 p-4 bg-green-50 rounded-lg">
                <div className="flex-shrink-0 w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center text-xl font-bold text-gray-900">
                  {index + 1}
                </div>
                <div className="flex-1">
                  <div className="flex items-center gap-2">
                    <p className="font-medium">{driver.name}</p>
                    <Badge className="bg-green-100 text-green-800">Incentive Eligible</Badge>
                  </div>
                  <div className="grid grid-cols-5 gap-4 mt-2 text-sm">
                    <div>
                      <p className="text-gray-500">Score</p>
                      <p className="font-medium text-green-600">⭐ {driver.overallScore}</p>
                    </div>
                    <div>
                      <p className="text-gray-500">Timeliness</p>
                      <p>{driver.metrics.boundaryTimeliness}%</p>
                    </div>
                    <div>
                      <p className="text-gray-500">Shortages</p>
                      <p>{driver.metrics.shortageFrequency}</p>
                    </div>
                    <div>
                      <p className="text-gray-500">Accidents</p>
                      <p>{driver.metrics.accidentHistory}</p>
                    </div>
                    <div>
                      <p className="text-gray-500">Attendance</p>
                      <p>{driver.metrics.attendanceConsistency}%</p>
                    </div>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* At Risk Drivers */}
      {atRiskDrivers.length > 0 && (
        <Card className="border-red-200 bg-red-50">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-red-800">
              <AlertTriangle className="h-5 w-5" />
              Drivers At Risk - Requires Attention
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {atRiskDrivers.map((driver) => (
                <div key={driver.id} className="p-4 bg-white rounded-lg">
                  <div className="flex items-start justify-between mb-3">
                    <div>
                      <p className="font-medium">{driver.name}</p>
                      <p className="text-sm text-gray-600">{driver.id}</p>
                    </div>
                    <Badge className={getScoreBadgeColor(driver.overallScore)}>
                      Score: {driver.overallScore}
                    </Badge>
                  </div>
                  <div className="grid grid-cols-5 gap-3 mb-3 text-sm">
                    <div>
                      <p className="text-gray-500">Timeliness</p>
                      <p className={driver.metrics.boundaryTimeliness < 90 ? "text-red-600" : ""}>
                        {driver.metrics.boundaryTimeliness}%
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-500">Shortages</p>
                      <p className={driver.metrics.shortageFrequency > 3 ? "text-red-600" : ""}>
                        {driver.metrics.shortageFrequency}
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-500">Accidents</p>
                      <p className={driver.metrics.accidentHistory > 0 ? "text-red-600" : ""}>
                        {driver.metrics.accidentHistory}
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-500">Complaints</p>
                      <p className={driver.metrics.complaints > 2 ? "text-red-600" : ""}>
                        {driver.metrics.complaints}
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-500">Attendance</p>
                      <p className={driver.metrics.attendanceConsistency < 90 ? "text-red-600" : ""}>
                        {driver.metrics.attendanceConsistency}%
                      </p>
                    </div>
                  </div>
                  {driver.recentIncidents.length > 0 && (
                    <div className="border-t pt-3">
                      <p className="text-sm text-gray-600 mb-2">Recent Incidents:</p>
                      <div className="space-y-2">
                        {driver.recentIncidents.slice(0, 2).map((incident, idx) => (
                          <div key={idx} className="flex items-start gap-2 text-sm">
                            <Badge className={getIncidentColor(incident.type)}>{incident.type}</Badge>
                            <div>
                              <p className="text-gray-600">{incident.details}</p>
                              <p className="text-xs text-gray-400">{incident.date}</p>
                            </div>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}
                  <Button variant="outline" size="sm" className="mt-3 w-full text-red-600">
                    Review & Take Action
                  </Button>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      )}

      {/* All Drivers Detailed View */}
      <Card>
        <CardHeader>
          <CardTitle>All Drivers - Detailed Behavior Metrics</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            {driverBehaviorData.map((driver) => (
              <div key={driver.id} className="p-4 border rounded-lg">
                <div className="flex items-start justify-between mb-4">
                  <div>
                    <div className="flex items-center gap-3">
                      <p className="font-medium text-lg">{driver.name}</p>
                      <span className={`text-2xl ${getScoreColor(driver.overallScore)}`}>
                        ⭐ {driver.overallScore}
                      </span>
                    </div>
                    <p className="text-sm text-gray-600">{driver.id}</p>
                  </div>
                  {driver.incentiveEligible ? (
                    <Badge className="bg-green-100 text-green-800 flex items-center gap-1">
                      <CheckCircle className="h-3 w-3" />
                      Incentive Eligible
                    </Badge>
                  ) : (
                    <Badge className="bg-gray-100 text-gray-800">Not Eligible</Badge>
                  )}
                </div>

                {/* Metrics with Progress Bars */}
                <div className="space-y-3">
                  <div>
                    <div className="flex justify-between text-sm mb-1">
                      <span className="text-gray-600">Boundary Payment Timeliness</span>
                      <span className="font-medium">{driver.metrics.boundaryTimeliness}%</span>
                    </div>
                    <Progress value={driver.metrics.boundaryTimeliness} className="h-2" />
                  </div>
                  <div>
                    <div className="flex justify-between text-sm mb-1">
                      <span className="text-gray-600">Attendance Consistency</span>
                      <span className="font-medium">{driver.metrics.attendanceConsistency}%</span>
                    </div>
                    <Progress value={driver.metrics.attendanceConsistency} className="h-2" />
                  </div>
                  <div className="grid grid-cols-3 gap-4 text-sm pt-2">
                    <div>
                      <p className="text-gray-500">Shortage Frequency</p>
                      <p
                        className={`font-medium ${
                          driver.metrics.shortageFrequency > 3 ? "text-red-600" : "text-green-600"
                        }`}
                      >
                        {driver.metrics.shortageFrequency} times
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-500">Accident History</p>
                      <p
                        className={`font-medium ${
                          driver.metrics.accidentHistory > 0 ? "text-red-600" : "text-green-600"
                        }`}
                      >
                        {driver.metrics.accidentHistory} incidents
                      </p>
                    </div>
                    <div>
                      <p className="text-gray-500">Complaints</p>
                      <p
                        className={`font-medium ${
                          driver.metrics.complaints > 2 ? "text-red-600" : "text-green-600"
                        }`}
                      >
                        {driver.metrics.complaints} complaints
                      </p>
                    </div>
                  </div>
                </div>

                {/* Recent Incidents */}
                {driver.recentIncidents.length > 0 && (
                  <div className="mt-4 pt-4 border-t">
                    <p className="text-sm text-gray-600 mb-2">Recent Incidents:</p>
                    <div className="space-y-2">
                      {driver.recentIncidents.map((incident, idx) => (
                        <div key={idx} className="flex items-start gap-2 text-sm p-2 bg-gray-50 rounded">
                          <Badge className={getIncidentColor(incident.type)} variant="outline">
                            {incident.type}
                          </Badge>
                          <div className="flex-1">
                            <p className="text-gray-700">{incident.details}</p>
                            <p className="text-xs text-gray-400 mt-1">{incident.date}</p>
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                )}
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
