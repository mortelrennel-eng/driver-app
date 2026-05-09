import { Card, CardContent, CardHeader, CardTitle } from "./ui/card";
import {
  BarChart,
  Bar,
  LineChart,
  Line,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from "recharts";
import { TrendingUp, TrendingDown, DollarSign, Users, Car, AlertTriangle } from "lucide-react";
import { Badge } from "./ui/badge";

const monthlyRevenueData = [
  { month: "Jan", boundary: 385000, expenses: 185000, net: 200000 },
  { month: "Feb", boundary: 420000, expenses: 195000, net: 225000 },
  { month: "Mar", boundary: 445000, expenses: 210000, net: 235000 },
  { month: "Apr", boundary: 410000, expenses: 188000, net: 222000 },
  { month: "May", boundary: 475000, expenses: 220000, net: 255000 },
  { month: "Jun", boundary: 510000, expenses: 215000, net: 295000 },
];

const unitIdleAnalysis = [
  { unit: "TXN-9012", idleDays: 8, reason: "Frequent Breakdowns", impact: "₱9,600 lost" },
  { unit: "TXN-7890", idleDays: 5, reason: "No Driver Assigned", impact: "₱6,000 lost" },
  { unit: "TXN-3456", idleDays: 4, reason: "Preventive Maintenance", impact: "₱4,800 lost" },
  { unit: "TXN-2468", idleDays: 3, reason: "Coding Day Extended", impact: "₱3,600 lost" },
];

const maintenanceCostTrend = [
  { unit: "TXN-9012", cost: 18500, frequency: 4, category: "High Risk" },
  { unit: "TXN-7890", cost: 15200, frequency: 3, category: "High Risk" },
  { unit: "TXN-5678", cost: 9200, frequency: 2, category: "Normal" },
  { unit: "TXN-1234", cost: 4500, frequency: 1, category: "Low" },
  { unit: "TXN-3456", cost: 8800, frequency: 2, category: "Normal" },
];

const driverUnitPreference = [
  { driver: "Juan Dela Cruz", preferredUnit: "TXN-1234", refusalCount: 0, reason: "Good condition" },
  { driver: "Pedro Reyes", preferredUnit: "TXN-5678", refusalCount: 1, reason: "Comfortable" },
  { driver: "Ana Garcia", preferredUnit: "TXN-9012", refusalCount: 3, reason: "Frequent issues" },
  { driver: "Carlos Martinez", preferredUnit: "Any", refusalCount: 2, reason: "Old units avoided" },
];

const seasonalRevenueData = [
  { season: "Jan-Mar", revenue: 1250000, trips: 12500, avgPerTrip: 100 },
  { season: "Apr-Jun", revenue: 1395000, trips: 13950, avgPerTrip: 100 },
  { season: "Jul-Sep", revenue: 1180000, trips: 11800, avgPerTrip: 100 },
  { season: "Oct-Dec", revenue: 1420000, trips: 14200, avgPerTrip: 100 },
];

const expenseBreakdown = [
  { name: "Maintenance", value: 85000, color: "#ef4444" },
  { name: "Staff Salaries", value: 53000, color: "#3b82f6" },
  { name: "Utilities", value: 27200, color: "#eab308" },
  { name: "Registration", value: 48100, color: "#8b5cf6" },
  { name: "Fuel Penalties", value: 1700, color: "#ec4899" },
];

export function Analytics() {
  const totalRevenue = monthlyRevenueData.reduce((sum, m) => sum + m.boundary, 0);
  const totalExpenses = monthlyRevenueData.reduce((sum, m) => sum + m.expenses, 0);
  const netIncome = totalRevenue - totalExpenses;
  const profitMargin = ((netIncome / totalRevenue) * 100).toFixed(1);

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl">Descriptive Analytics & Insights</h2>
        <p className="text-sm text-gray-500 mt-1">
          Answer WHY problems: idle units, maintenance costs, driver preferences, and seasonal trends
        </p>
      </div>

      {/* Key Financial Metrics */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-500">Total Revenue (6mo)</p>
                <p className="text-2xl">₱{(totalRevenue / 1000).toFixed(0)}k</p>
                <div className="flex items-center text-green-600 text-sm mt-1">
                  <TrendingUp className="h-4 w-4 mr-1" />
                  +12.5%
                </div>
              </div>
              <DollarSign className="h-8 w-8 text-green-600" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-500">Total Expenses</p>
                <p className="text-2xl">₱{(totalExpenses / 1000).toFixed(0)}k</p>
                <div className="flex items-center text-red-600 text-sm mt-1">
                  <TrendingUp className="h-4 w-4 mr-1" />
                  +5.8%
                </div>
              </div>
              <DollarSign className="h-8 w-8 text-red-600" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-500">Net Income</p>
                <p className="text-2xl text-green-600">₱{(netIncome / 1000).toFixed(0)}k</p>
                <div className="flex items-center text-green-600 text-sm mt-1">
                  <TrendingUp className="h-4 w-4 mr-1" />
                  +18.2%
                </div>
              </div>
              <TrendingUp className="h-8 w-8 text-green-600" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-gray-500">Profit Margin</p>
                <p className="text-2xl">{profitMargin}%</p>
                <p className="text-xs text-gray-500 mt-1">Healthy margin</p>
              </div>
              <Car className="h-8 w-8 text-blue-600" />
            </div>
          </CardContent>
        </Card>
      </div>

      {/* WHY Analysis: Unit Idle Reasons */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <AlertTriangle className="h-5 w-5 text-orange-600" />
            WHY are units idle? - Root Cause Analysis
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {unitIdleAnalysis.map((item, idx) => (
              <div key={idx} className="p-4 border rounded-lg flex items-center justify-between">
                <div className="flex-1">
                  <div className="flex items-center gap-3 mb-1">
                    <p className="font-medium">{item.unit}</p>
                    <Badge variant="outline">{item.idleDays} days idle</Badge>
                  </div>
                  <p className="text-sm text-gray-600">Reason: {item.reason}</p>
                </div>
                <div className="text-right">
                  <p className="text-red-600 font-medium">{item.impact}</p>
                  <p className="text-xs text-gray-500">Revenue lost</p>
                </div>
              </div>
            ))}
          </div>
          <div className="mt-4 p-3 bg-orange-50 rounded-lg">
            <p className="text-sm text-orange-800">
              <strong>Insight:</strong> 20 days total idle time = ₱24,000 potential revenue loss. Main causes:
              Breakdowns (40%) and driver vacancy (25%).
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Charts */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Revenue vs Expenses Trend */}
        <Card>
          <CardHeader>
            <CardTitle>Revenue vs Expenses Trend</CardTitle>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={monthlyRevenueData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Line type="monotone" dataKey="boundary" stroke="#eab308" strokeWidth={2} name="Boundary" />
                <Line type="monotone" dataKey="expenses" stroke="#ef4444" strokeWidth={2} name="Expenses" />
                <Line type="monotone" dataKey="net" stroke="#22c55e" strokeWidth={2} name="Net Income" />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        {/* Expense Breakdown */}
        <Card>
          <CardHeader>
            <CardTitle>Expense Distribution</CardTitle>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie
                  data={expenseBreakdown}
                  cx="50%"
                  cy="50%"
                  labelLine={false}
                  label={({ name, value }) => `${name}: ₱${(value / 1000).toFixed(0)}k`}
                  outerRadius={100}
                  fill="#8884d8"
                  dataKey="value"
                >
                  {expenseBreakdown.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip />
              </PieChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        {/* Seasonal Revenue Comparison */}
        <Card>
          <CardHeader>
            <CardTitle>Seasonal Revenue Comparison</CardTitle>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={seasonalRevenueData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="season" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Bar dataKey="revenue" fill="#eab308" name="Revenue (₱)" />
              </BarChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        {/* Maintenance Cost by Unit */}
        <Card>
          <CardHeader>
            <CardTitle>WHY high maintenance cost? - Per Unit Analysis</CardTitle>
          </CardHeader>
          <CardContent>
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={maintenanceCostTrend}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="unit" />
                <YAxis />
                <Tooltip />
                <Bar dataKey="cost" fill="#ef4444" name="Maintenance Cost (₱)" />
              </BarChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </div>

      {/* Maintenance Cost Detailed Analysis */}
      <Card>
        <CardHeader>
          <CardTitle>Which units have high maintenance costs?</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-3 px-4 text-sm">Unit</th>
                  <th className="text-left py-3 px-4 text-sm">Total Cost</th>
                  <th className="text-left py-3 px-4 text-sm">Frequency</th>
                  <th className="text-left py-3 px-4 text-sm">Cost per Incident</th>
                  <th className="text-left py-3 px-4 text-sm">Category</th>
                  <th className="text-left py-3 px-4 text-sm">Action</th>
                </tr>
              </thead>
              <tbody>
                {maintenanceCostTrend.map((unit) => (
                  <tr key={unit.unit} className="border-b hover:bg-gray-50">
                    <td className="py-3 px-4 text-sm font-medium">{unit.unit}</td>
                    <td className="py-3 px-4 text-sm">₱{unit.cost.toLocaleString()}</td>
                    <td className="py-3 px-4 text-sm">{unit.frequency} times</td>
                    <td className="py-3 px-4 text-sm">
                      ₱{(unit.cost / unit.frequency).toLocaleString()}
                    </td>
                    <td className="py-3 px-4 text-sm">
                      <Badge
                        className={
                          unit.category === "High Risk"
                            ? "bg-red-100 text-red-800"
                            : unit.category === "Normal"
                            ? "bg-yellow-100 text-yellow-800"
                            : "bg-green-100 text-green-800"
                        }
                      >
                        {unit.category}
                      </Badge>
                    </td>
                    <td className="py-3 px-4 text-sm">
                      {unit.category === "High Risk" ? (
                        <span className="text-red-600">Consider retirement</span>
                      ) : (
                        <span className="text-green-600">Monitor</span>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>

      {/* Driver-Unit Preference Analysis */}
      <Card>
        <CardHeader>
          <CardTitle>WHY do drivers refuse certain units? - Preference Matrix</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            {driverUnitPreference.map((pref, idx) => (
              <div key={idx} className="p-4 border rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <p className="font-medium">{pref.driver}</p>
                  {pref.refusalCount > 2 ? (
                    <Badge className="bg-red-100 text-red-800">{pref.refusalCount} refusals</Badge>
                  ) : pref.refusalCount > 0 ? (
                    <Badge className="bg-yellow-100 text-yellow-800">{pref.refusalCount} refusals</Badge>
                  ) : (
                    <Badge className="bg-green-100 text-green-800">No refusals</Badge>
                  )}
                </div>
                <div className="grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <p className="text-gray-500">Preferred Unit</p>
                    <p>{pref.preferredUnit}</p>
                  </div>
                  <div>
                    <p className="text-gray-500">Reason</p>
                    <p>{pref.reason}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
          <div className="mt-4 p-3 bg-blue-50 rounded-lg">
            <p className="text-sm text-blue-800">
              <strong>Insight:</strong> TXN-9012 has highest refusal rate (3 times). Driver feedback: "Frequent
              breakdowns" - Correlates with maintenance data. Consider unit replacement or major overhaul.
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Seasonal Analysis Insight */}
      <Card>
        <CardHeader>
          <CardTitle>WHEN is revenue highest? - Seasonal Trends</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-3 px-4 text-sm">Season</th>
                  <th className="text-left py-3 px-4 text-sm">Total Revenue</th>
                  <th className="text-left py-3 px-4 text-sm">Total Trips</th>
                  <th className="text-left py-3 px-4 text-sm">Avg per Trip</th>
                  <th className="text-left py-3 px-4 text-sm">Performance</th>
                </tr>
              </thead>
              <tbody>
                {seasonalRevenueData.map((season, idx) => (
                  <tr key={idx} className="border-b hover:bg-gray-50">
                    <td className="py-3 px-4 text-sm font-medium">{season.season}</td>
                    <td className="py-3 px-4 text-sm">₱{season.revenue.toLocaleString()}</td>
                    <td className="py-3 px-4 text-sm">{season.trips.toLocaleString()}</td>
                    <td className="py-3 px-4 text-sm">₱{season.avgPerTrip}</td>
                    <td className="py-3 px-4 text-sm">
                      {season.revenue > 1300000 ? (
                        <Badge className="bg-green-100 text-green-800">Peak Season</Badge>
                      ) : season.revenue < 1200000 ? (
                        <Badge className="bg-red-100 text-red-800">Low Season</Badge>
                      ) : (
                        <Badge className="bg-blue-100 text-blue-800">Normal</Badge>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <div className="mt-4 p-3 bg-green-50 rounded-lg">
            <p className="text-sm text-green-800">
              <strong>Business Decision:</strong> Oct-Dec is peak season (+17% vs average). Consider: (1) Reduce
              maintenance during this period, (2) Offer driver incentives, (3) Delay new unit acquisition to Q4 for
              maximum ROI impact.
            </p>
          </div>
        </CardContent>
      </Card>

      {/* Strategic Recommendations */}
      <Card>
        <CardHeader>
          <CardTitle>Strategic Decision Support</CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-3">
            <div className="p-4 bg-blue-50 rounded-lg">
              <p className="font-medium text-blue-900 mb-2">✅ When to buy new units?</p>
              <p className="text-sm text-blue-800">
                Current net income: ₱295k/month. With 2 units at ROI (eligible for boundary reduction), consider
                acquiring 1-2 new units in Q3 to capitalize on Q4 peak season.
              </p>
            </div>
            <div className="p-4 bg-green-50 rounded-lg">
              <p className="font-medium text-green-900 mb-2">✅ When to lower boundary?</p>
              <p className="text-sm text-green-800">
                Units TXN-5678 and TXN-3456 achieved ROI. Recommend boundary reduction from ₱1,000 → ₱800 to
                incentivize drivers and improve retention.
              </p>
            </div>
            <div className="p-4 bg-red-50 rounded-lg">
              <p className="font-medium text-red-900 mb-2">❌ Which units to retire?</p>
              <p className="text-sm text-red-800">
                TXN-9012 and TXN-7890 are high-risk units (maintenance cost {'>'} ₱15k with 3-4 breakdowns). Total
                loss: ₱33,700 + 13 days idle. Recommend replacement within 3 months.
              </p>
            </div>
            <div className="p-4 bg-yellow-50 rounded-lg">
              <p className="font-medium text-yellow-900 mb-2">⚠️ Which drivers to retain/remove?</p>
              <p className="text-sm text-yellow-800">
                Top performers (Pedro Reyes, Juan Dela Cruz) - offer unit upgrade incentive. At-risk driver (Carlos
                Martinez) - 6 shortages, low score. Issue final warning or consider replacement.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
