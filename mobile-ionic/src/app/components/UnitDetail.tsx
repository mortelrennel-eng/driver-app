import { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { ArrowLeft, Car, Users, Calendar, TrendingUp, Wrench, MapPin, Video, Loader2, RefreshCw, RotateCcw } from "lucide-react";
import api from "../services/api";
import { toast } from "sonner";

const fmt = (n: any) => "₱" + Number(n || 0).toLocaleString("en-PH", { minimumFractionDigits: 2 });
const fmtDate = (d: any) => d ? new Date(d).toLocaleDateString("en-PH", { month: "short", day: "2-digit", year: "numeric" }) : "N/A";

const TABS = ["Overview","Drivers","Coding","Boundary","Maintenance","ROI","Location","Dashcam"];
const CODING_SCHEDULE: Record<string,string> = { Monday:"1, 2", Tuesday:"3, 4", Wednesday:"5, 6", Thursday:"7, 8", Friday:"9, 0" };

function StatusPill({ status }: { status: string }) {
  const s = status?.toLowerCase();
  const cfg: any = {
    active: "bg-green-100 text-green-700 border-green-200",
    maintenance: "bg-red-100 text-red-700 border-red-200",
    coding: "bg-yellow-100 text-yellow-700 border-yellow-200",
    at_risk: "bg-orange-100 text-orange-700 border-orange-200",
  };
  return <span className={`px-2.5 py-1 text-[10px] font-black uppercase rounded-full border ${cfg[s] || "bg-gray-100 text-gray-600 border-gray-200"}`}>{status}</span>;
}

function InfoRow({ label, value }: { label: string; value: any }) {
  return (
    <div className="flex justify-between items-center py-2.5 border-b border-gray-50 last:border-0">
      <span className="text-xs font-bold text-gray-400 uppercase tracking-tight">{label}</span>
      <span className="font-black text-gray-800 text-right text-sm">{value ?? "N/A"}</span>
    </div>
  );
}

function HealthBar({ unit }: { unit: any }) {
  if (!unit?.gps_device_count && !unit?.imei) return null;
  const km = Math.max(0, (unit.current_gps_odo || 0) - (unit.last_service_odo_gps || 0));
  const pct = Math.min(100, Math.round((km / 5000) * 100));
  const over = km >= 5000;
  const bar = over ? "bg-red-600" : pct >= 85 ? "bg-orange-500" : pct >= 60 ? "bg-yellow-400" : "bg-green-500";
  const txt = over ? "text-red-600" : pct >= 85 ? "text-orange-600" : pct >= 60 ? "text-yellow-600" : "text-green-600";
  const lbl = over ? "⚠ SERVICE OVERDUE" : pct >= 85 ? "Service Due Soon" : pct >= 60 ? "Maintenance Progress" : "Optimal Health";
  return (
    <div className="bg-white border border-gray-100 rounded-2xl p-4 mt-4">
      <div className="flex justify-between mb-2">
        <span className={`text-[10px] font-black uppercase tracking-wider ${txt}`}>{lbl}</span>
        <span className="text-[10px] text-gray-400 font-bold">{Number(km).toLocaleString()} / 5,000 KM</span>
      </div>
      <div className="h-2.5 bg-gray-100 rounded-full overflow-hidden">
        <div className={`h-full ${bar} rounded-full transition-all`} style={{ width: `${pct}%` }} />
      </div>
      {over && <p className="text-[10px] text-red-500 mt-1 italic">Exceeded by {Number(km - 5000).toLocaleString()}km.</p>}
    </div>
  );
}

export function UnitDetail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [unit, setUnit] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState("Overview");

  useEffect(() => {
    (async () => {
      setLoading(true);
      try {
        const res = await api.get(`/units/${id}`);
        setUnit(res.data.data ?? res.data);
      } catch (e: any) {
        toast.error(e.response?.data?.message || "Failed to load unit details.");
      } finally { setLoading(false); }
    })();
  }, [id]);

  if (loading) return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-gray-50 gap-3">
      <Loader2 className="w-8 h-8 animate-spin text-blue-600" />
      <p className="text-sm text-gray-500 font-medium">Loading unit details...</p>
    </div>
  );
  if (!unit) return (
    <div className="flex flex-col items-center justify-center min-h-screen bg-gray-50 gap-3 px-6 text-center">
      <Car className="w-16 h-16 text-gray-200" />
      <p className="text-base font-black text-gray-800">Unit not found</p>
      <button onClick={() => navigate("/units")} className="px-6 py-2.5 bg-blue-600 text-white font-black text-sm rounded-xl">Go Back</button>
    </div>
  );

  const driverCount = (unit.primary_driver ? 1 : 0) + (unit.secondary_driver ? 1 : 0);
  const driversFull = driverCount >= 2;
  const lastDigit = (unit.plate_number || "").slice(-1);
  const codingDay = unit.coding_day || "N/A";
  const today = new Date().toLocaleString("en-US", { weekday: "long" });

  return (
    <div className="flex flex-col min-h-full bg-gray-50">
      {/* Back Button */}
      <div className="bg-white px-4 pt-4 pb-2 flex items-center gap-3 border-b border-gray-100">
        <button onClick={() => navigate("/units")} className="p-2 bg-gray-100 rounded-xl active:bg-gray-200">
          <ArrowLeft className="w-4 h-4 text-gray-600" />
        </button>
        <div>
          <p className="text-xs text-gray-400 font-bold uppercase tracking-widest">Unit Details</p>
          <p className="text-base font-black text-gray-900 leading-tight">Complete unit information</p>
        </div>
      </div>

      {/* Hero Header — matches web dark card */}
      <div className="bg-gradient-to-r from-slate-800 to-blue-900 mx-4 mt-4 rounded-2xl p-4 shadow-lg">
        <div className="flex justify-between items-start mb-3">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
              <Car className="w-6 h-6 text-white" />
            </div>
            <div>
              <p className="text-xl font-black text-white tracking-wider">{unit.plate_number}</p>
              <p className="text-blue-200 text-xs font-bold">{unit.make} {unit.model} ({unit.year})</p>
            </div>
          </div>
          <div className="text-right">
            <p className="text-xl font-black text-white">{fmt(unit.boundary_rate)}</p>
            <p className="text-blue-200 text-[10px] font-bold uppercase tracking-wider">Daily Boundary Rate</p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <span className="px-2.5 py-1 bg-white/20 text-white text-[10px] font-black rounded-full uppercase">{unit.status}</span>
          <span className="px-2.5 py-1 bg-white/20 text-white text-[10px] font-black rounded-full uppercase">{unit.unit_type || "Standard"}</span>
        </div>
      </div>

      {/* Tab Bar — scrollable */}
      <div className="overflow-x-auto scrollbar-hide bg-white mt-4 border-b border-gray-200">
        <div className="flex min-w-max">
          {TABS.map(t => (
            <button key={t} onClick={() => setActiveTab(t)}
              className={`px-4 py-3 text-[10px] font-black uppercase tracking-wider border-b-2 transition-all whitespace-nowrap ${
                activeTab === t ? "border-blue-600 text-blue-600" : "border-transparent text-gray-400"
              }`}>{t}</button>
          ))}
        </div>
      </div>

      {/* Tab Content */}
      <div className="flex-1 overflow-y-auto px-4 py-4">
        {/* ── OVERVIEW ── */}
        {activeTab === "Overview" && (
          <div className="space-y-6">
            {/* Quick Stats */}
            <div className="grid grid-cols-2 gap-4">
              {[
                { icon: <Users className="w-5 h-5 text-blue-600" />, bg: "bg-blue-50/50", label: "Drivers", val: `${driverCount}/2` },
                { icon: <Calendar className="w-5 h-5 text-green-600" />, bg: "bg-green-50/50", label: "Next Coding", val: `${unit.days_until_coding ?? "?"}d` },
                { icon: <TrendingUp className="w-5 h-5 text-purple-600" />, bg: "bg-purple-50/50", label: "ROI", val: `${Number(unit.roi_percentage || 0).toFixed(1)}%` },
                { icon: <Wrench className="w-5 h-5 text-orange-600" />, bg: "bg-orange-50/50", label: "Maint Jobs", val: unit.maintenance_count ?? 0 },
              ].map((s, i) => (
                <div key={i} className="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm flex items-center gap-4">
                  <div className={`p-3 ${s.bg} rounded-2xl`}>{s.icon}</div>
                  <div>
                    <p className="text-[10px] text-gray-400 uppercase font-black tracking-widest mb-0.5">{s.label}</p>
                    <p className="text-xl font-black text-gray-900">{s.val}</p>
                  </div>
                </div>
              ))}
            </div>

            <div className="grid grid-cols-1 gap-6">
              {/* Basic Information */}
              <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
                <p className="text-[10px] font-black text-gray-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                  <div className="p-1 bg-blue-50 rounded-md">
                    <Car className="w-3 h-3 text-blue-600" />
                  </div>
                  Basic Information
                </p>
                <div className="space-y-4">
                  <InfoRow label="Plate Number" value={unit.plate_number} />
                  <InfoRow label="Vehicle" value={`${unit.make} ${unit.model}`} />
                  <InfoRow label="Year" value={unit.year} />
                  <InfoRow label="Created By" value={unit.created_by_name || "System"} />
                  <InfoRow label="Last Update" value={new Date(unit.updated_at).toLocaleString("en-US", { month: "short", day: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit", hour12: true })} />
                  
                  <div className="pt-6 mt-2 border-t border-gray-50 flex justify-between items-center">
                    <span className="text-xs font-black text-gray-900 uppercase tracking-widest">Active Rate</span>
                    <span className="text-2xl font-black text-blue-600 tracking-tight">{fmt(unit.boundary_rate)}</span>
                  </div>
                </div>
              </div>

              {/* Driver Assignment */}
              <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
                <div className="flex justify-between items-center mb-6">
                  <p className="text-[10px] font-black text-gray-900 uppercase tracking-widest flex items-center gap-2">
                    <div className="p-1 bg-blue-50 rounded-md">
                      <Users className="w-3 h-3 text-blue-600" />
                    </div>
                    Driver Assignment
                  </p>
                  <span className={`px-3 py-1 text-[10px] font-black rounded-full border uppercase ${driversFull ? "bg-red-50 text-red-600 border-red-100" : "bg-green-50 text-green-600 border-green-100"}`}>
                    {driversFull ? "Full" : "Available"}
                  </span>
                </div>

                <div className="space-y-3">
                  <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest">Status</p>
                  {[unit.primary_driver, unit.secondary_driver].filter(Boolean).map((d: any, i: number) => (
                    <div key={i} className="bg-gray-50/50 p-4 rounded-2xl border border-gray-100">
                      <div className="flex justify-between items-start">
                        <div>
                          <p className="text-sm font-black text-gray-900 mb-1">{d.full_name}</p>
                          <p className="text-[11px] text-gray-500 font-medium">License: {d.license_number}</p>
                          <p className="text-[11px] text-gray-500 font-medium">Contact: {d.contact_number}</p>
                        </div>
                      </div>
                    </div>
                  ))}
                  {driverCount === 0 && (
                    <div className="py-8 text-center bg-gray-50/50 rounded-2xl border border-dashed border-gray-200">
                      <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest">No Drivers Assigned</p>
                    </div>
                  )}
                </div>
              </div>
            </div>

            <HealthBar unit={unit} />
          </div>
        )}

        {/* ── DRIVERS ── */}
        {activeTab === "Drivers" && (
          <div className="space-y-6">
            <div className="flex items-center gap-2 mb-2">
              <div className="p-2 bg-blue-50 rounded-lg">
                <Users className="w-5 h-5 text-blue-600" />
              </div>
              <h3 className="text-sm font-black text-gray-900 uppercase tracking-widest">Assigned Drivers Details</h3>
            </div>

            {[
              { label: "Primary Driver", data: unit.primary_driver },
              { label: "Secondary Driver", data: unit.secondary_driver },
            ].map((d, i) => (
              <div key={i} className="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm overflow-hidden relative">
                <div className="flex justify-between items-start mb-4">
                  <div>
                    <p className="text-[10px] font-black text-blue-600 uppercase tracking-widest mb-1">{d.label}</p>
                    <h4 className={`text-lg font-black ${d.data?.full_name ? "text-gray-900" : "text-gray-300 italic"}`}>
                      {d.data?.full_name || "Unassigned"}
                    </h4>
                    {d.data && (
                      <p className="text-[11px] text-gray-500 font-medium mt-1">
                        License: {d.data.license_number} <span className="mx-1 text-gray-300">|</span> Contact: {d.data.contact_number}
                      </p>
                    )}
                  </div>
                  {d.data && (
                    <span className="px-3 py-1 bg-green-50 text-green-600 text-[10px] font-black rounded-full border border-green-100 uppercase">
                      Active
                    </span>
                  )}
                </div>

                {d.data ? (
                  <div className="grid grid-cols-2 gap-y-4 gap-x-4 mt-6 pt-6 border-t border-gray-50">
                    <div>
                      <p className="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">License Number</p>
                      <p className="text-sm font-black text-gray-900">{d.data.license_number}</p>
                    </div>
                    <div>
                      <p className="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Contact</p>
                      <p className="text-sm font-black text-gray-900">{d.data.contact_number}</p>
                    </div>
                    <div>
                      <p className="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Daily Target</p>
                      <p className="text-sm font-black text-gray-900">{fmt(d.data.daily_boundary_target)}</p>
                    </div>
                    <div>
                      <p className="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Hire Date</p>
                      <p className="text-sm font-black text-gray-900">{fmtDate(d.data.hire_date)}</p>
                    </div>
                    <div className="col-span-2">
                      <p className="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">License Expiry</p>
                      <p className={`text-sm font-black ${new Date(d.data.license_expiry) < new Date() ? "text-red-600" : "text-gray-900"}`}>
                        {fmtDate(d.data.license_expiry)}
                      </p>
                    </div>
                  </div>
                ) : (
                  <div className="py-8 text-center bg-gray-50/50 rounded-2xl border border-dashed border-gray-200 mt-4">
                    <Users className="w-10 h-10 text-gray-200 mx-auto mb-2" />
                    <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver Slot Available</p>
                  </div>
                )}
              </div>
            ))}
          </div>
        )}

        {/* ── CODING ── */}
        {activeTab === "Coding" && (
          <div className="space-y-6">
            <div className="flex items-center gap-2 mb-2">
              <div className="p-2 bg-blue-50 rounded-lg">
                <Calendar className="w-5 h-5 text-blue-600" />
              </div>
              <h3 className="text-sm font-black text-gray-900 uppercase tracking-widest">MMDA Coding Schedule</h3>
            </div>

            <div className="grid grid-cols-1 gap-6">
              {/* Current Unit Status */}
              <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
                <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 pb-2 border-b border-gray-50">Current Unit Status</p>
                <div className="space-y-5">
                  <div className="flex justify-between items-center">
                    <span className="text-[11px] font-black text-gray-400 uppercase tracking-tight">Coding Day</span>
                    <span className="px-3 py-1 bg-blue-600 text-white rounded-full text-[10px] font-black uppercase tracking-wider">{codingDay}</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-[11px] font-black text-gray-400 uppercase tracking-tight">Plate Ending</span>
                    <span className="text-lg font-black text-gray-900">{lastDigit}</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-[11px] font-black text-gray-400 uppercase tracking-tight">Next Schedule</span>
                    <span className="text-sm font-black text-gray-900">{unit.next_coding_date}</span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-[11px] font-black text-gray-400 uppercase tracking-tight">Remaining</span>
                    <span className={`text-lg font-black ${unit.days_until_coding === 0 ? "text-red-600" : "text-green-600"}`}>
                      {unit.days_until_coding === 0 ? "Today" : `${unit.days_until_coding} Days`}
                    </span>
                  </div>
                </div>
              </div>

              {/* Standard MMDA Reference */}
              <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
                <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-6 pb-2 border-b border-gray-50">Standard MMDA Reference</p>
                <div className="space-y-1">
                  {Object.entries(CODING_SCHEDULE).map(([day, digits]) => (
                    <div key={day} className={`flex justify-between items-center p-3 rounded-2xl ${today === day ? "bg-blue-50/50" : ""}`}>
                      <span className={`text-[11px] font-black uppercase tracking-tight ${today === day ? "text-blue-600" : "text-gray-500"}`}>{day}</span>
                      <span className={`text-base font-black ${today === day ? "text-blue-600" : "text-gray-900"}`}>{digits}</span>
                    </div>
                  ))}
                </div>
                <div className="mt-6 pt-4 border-t border-gray-50">
                  <div className="flex items-center gap-2 text-[10px] text-gray-400 font-bold uppercase tracking-widest italic">
                    <div className="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse" />
                    Coding Time: 7:00 AM – 10:00 AM
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* ── BOUNDARY ── */}
        {activeTab === "Boundary" && (
          <div className="bg-white border border-gray-100 rounded-3xl shadow-sm overflow-hidden">
            <div className="px-6 py-5 border-b border-gray-50 flex items-center gap-3">
              <div className="p-2 bg-blue-50 rounded-lg">
                <TrendingUp className="w-5 h-5 text-blue-600" />
              </div>
              <p className="text-sm font-black text-gray-900 uppercase tracking-widest">Boundary Collection History</p>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left border-collapse">
                <thead>
                  <tr className="bg-gray-50/50">
                    <th className="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Date</th>
                    <th className="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Driver</th>
                    <th className="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Remarks</th>
                    <th className="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Amount</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-50">
                  {unit.boundary_history?.length > 0 ? (
                    unit.boundary_history.map((b: any, i: number) => (
                      <tr key={i} className="hover:bg-gray-50/30 transition-colors">
                        <td className="px-6 py-4">
                          <p className="text-xs font-black text-gray-600 font-mono">
                            {new Date(b.date).toISOString().split('T')[0]}
                          </p>
                        </td>
                        <td className="px-6 py-4">
                          <p className="text-xs font-bold text-gray-700">{b.full_name || "N/A"}</p>
                        </td>
                        <td className="px-6 py-4">
                          <p className="text-[10px] text-gray-400 font-medium italic">{b.remarks || "---"}</p>
                        </td>
                        <td className="px-6 py-4 text-right">
                          <p className="text-sm font-black text-green-600">{fmt(b.actual_boundary)}</p>
                          <p className="text-[9px] text-gray-300 uppercase font-black">{b.status}</p>
                        </td>
                      </tr>
                    ))
                  ) : (
                    <tr>
                      <td colSpan={4} className="py-20 text-center">
                        <div className="flex flex-col items-center">
                          <TrendingUp className="w-12 h-12 text-gray-100 mb-3" />
                          <p className="text-xs font-bold text-gray-300 uppercase tracking-widest">No collection history found</p>
                        </div>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* ── MAINTENANCE ── */}
        {activeTab === "Maintenance" && (
          <div className="space-y-6">
            <div className="flex justify-between items-center mb-2">
              <div className="flex items-center gap-2">
                <div className="p-2 bg-blue-50 rounded-lg">
                  <Wrench className="w-5 h-5 text-blue-600" />
                </div>
                <h3 className="text-sm font-black text-gray-900 uppercase tracking-widest">Vehicle Maintenance Records</h3>
              </div>
              <span className="px-3 py-1 bg-orange-100 text-orange-700 text-[10px] font-black rounded-full border border-orange-200 uppercase tracking-wider">
                Total: {fmt(unit.maintenance_total_cost)}
              </span>
            </div>

            {unit.maintenance_records?.length > 0 ? unit.maintenance_records.map((m: any, i: number) => (
              <div key={i} className="bg-white border border-gray-100 rounded-3xl shadow-sm overflow-hidden mb-6">
                {/* Dark Header */}
                <div className="bg-slate-800 p-4 flex justify-between items-center text-white">
                  <div>
                    <p className="text-xs font-black uppercase tracking-widest">{m.maintenance_type || "Maintenance"}</p>
                    <p className="text-[10px] text-slate-400 font-bold mt-0.5">{new Date(m.date_started).toISOString().split('T')[0]}</p>
                  </div>
                  <div className="text-right">
                    <p className="text-sm font-black tracking-tight">{fmt(m.cost)}</p>
                    <p className="text-[9px] text-slate-400 font-black uppercase">Total Cost</p>
                  </div>
                </div>

                {/* Content Area */}
                <div className="p-5 space-y-4">
                  <div className="grid grid-cols-2 gap-4">
                    <div>
                      <p className="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Mechanic</p>
                      <p className="text-xs font-black text-gray-700">{m.mechanic_name || "N/A"}</p>
                    </div>
                    <div>
                      <p className="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Status</p>
                      <span className={`px-2 py-0.5 text-[9px] font-black rounded-full border uppercase inline-flex items-center gap-1 ${
                        m.status === "completed" ? "bg-green-50 text-green-600 border-green-100" : 
                        m.status === "pending" ? "bg-yellow-50 text-yellow-600 border-yellow-100" : 
                        "bg-gray-50 text-gray-600 border-gray-100"
                      }`}>
                        {m.status === "pending" && <div className="w-1 h-1 rounded-full bg-yellow-600 animate-pulse" />}
                        {m.status}
                      </span>
                    </div>
                  </div>

                  <div>
                    <p className="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Driver</p>
                    <p className="text-xs font-bold text-gray-700">{m.driver_name || "N/A"}</p>
                  </div>

                  <div className="pt-4 border-t border-gray-50">
                    <p className="text-[9px] text-gray-400 uppercase font-black tracking-widest mb-1">Description</p>
                    <p className="text-xs text-gray-600 leading-relaxed">{m.description || "No description provided."}</p>
                  </div>

                  {/* Parts Replaced Section */}
                  {m.parts?.length > 0 && (
                    <div className="mt-6 rounded-2xl overflow-hidden border border-blue-50">
                      <div className="bg-blue-50 px-4 py-2 flex items-center gap-2">
                        <Wrench className="w-3 h-3 text-blue-600" />
                        <span className="text-[9px] font-black text-blue-700 uppercase tracking-widest">Parts Replaced</span>
                      </div>
                      <div className="bg-white divide-y divide-blue-50/50">
                        {m.parts.map((p: any, pi: number) => (
                          <div key={pi} className="px-4 py-2.5 flex justify-between items-center hover:bg-blue-50/20 transition-colors">
                            <div>
                              <p className="text-[11px] font-black text-gray-700">{p.part_name}</p>
                              {p.quantity > 1 && <p className="text-[9px] text-gray-400 font-bold uppercase">Qty: {p.quantity}</p>}
                            </div>
                            <p className="text-xs font-black text-blue-600">{fmt(p.total)}</p>
                          </div>
                        ))}
                        <div className="bg-blue-50/30 px-4 py-2.5 flex justify-between items-center">
                          <span className="text-[9px] font-black text-blue-700 uppercase tracking-widest">Parts Subtotal</span>
                          <span className="text-xs font-black text-blue-700">{fmt(m.parts_subtotal)}</span>
                        </div>
                      </div>
                    </div>
                  )}

                  {/* Other Costs Section */}
                  {m.others?.length > 0 && (
                    <div className="mt-4 rounded-2xl overflow-hidden border border-orange-50">
                      <div className="bg-orange-50 px-4 py-2 flex items-center gap-2">
                        <TrendingUp className="w-3 h-3 text-orange-600" />
                        <span className="text-[9px] font-black text-orange-700 uppercase tracking-widest">Other Costs & Services</span>
                      </div>
                      <div className="bg-white divide-y divide-orange-50/50">
                        {m.others.map((o: any, oi: number) => (
                          <div key={oi} className="px-4 py-2.5 flex justify-between items-center hover:bg-orange-50/20 transition-colors">
                            <p className="text-[11px] font-black text-gray-700">{o.part_name}</p>
                            <p className="text-xs font-black text-orange-600">{fmt(o.total)}</p>
                          </div>
                        ))}
                        <div className="bg-orange-50/30 px-4 py-2.5 flex justify-between items-center">
                          <span className="text-[9px] font-black text-orange-700 uppercase tracking-widest">Services Subtotal</span>
                          <span className="text-xs font-black text-orange-700">{fmt(m.others_subtotal)}</span>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              </div>
            )) : (
              <div className="bg-white border border-gray-100 rounded-3xl py-20 text-center shadow-sm">
                <div className="flex flex-col items-center">
                  <Wrench className="w-16 h-16 text-gray-100 mb-4" />
                  <p className="text-xs font-bold text-gray-300 uppercase tracking-widest">No Maintenance Records Found</p>
                </div>
              </div>
            )}
          </div>
        )}

        {/* ── ROI ── */}
        {activeTab === "ROI" && (
          <div className="space-y-6">
            {/* ROI Performance Analysis Header Card */}
            <div className="bg-indigo-600 rounded-[2.5rem] p-8 shadow-xl shadow-indigo-100 relative overflow-hidden">
              <div className="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-20 -mt-20 blur-3xl" />
              <div className="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-10 -mb-10 blur-2xl" />
              
              <div className="relative flex items-center gap-3 mb-8">
                <div className="p-2 bg-white/10 rounded-xl backdrop-blur-md">
                  <TrendingUp className="w-5 h-5 text-white" />
                </div>
                <h3 className="text-sm font-black text-white uppercase tracking-widest">ROI Performance Analysis</h3>
              </div>

              <div className="relative grid grid-cols-1 gap-4">
                {[
                  { label: "Total Investment", val: unit.roi?.total_investment, bg: "bg-white/10" },
                  { label: "Total Net Revenue", val: unit.roi?.total_revenue, bg: "bg-white/10", color: "text-green-300" },
                  { label: "Total Expenses", val: unit.roi?.total_expenses, bg: "bg-white/10", color: "text-orange-300" },
                ].map((m, i) => (
                  <div key={i} className={`${m.bg} backdrop-blur-md border border-white/10 rounded-3xl p-6`}>
                    <p className="text-[10px] font-black text-white/60 uppercase tracking-widest mb-2">{m.label}</p>
                    <p className={`text-2xl font-black ${m.color || "text-white"} tracking-tight`}>{fmt(m.val)}</p>
                  </div>
                ))}
              </div>
            </div>

            <div className="grid grid-cols-1 gap-6">
              {/* Key Metrics */}
              <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
                <p className="text-[10px] font-black text-gray-900 uppercase tracking-widest mb-6 border-b border-gray-50 pb-2">Key Metrics</p>
                <div className="space-y-5">
                  <div className="flex justify-between items-center">
                    <span className="text-[11px] font-black text-gray-400 uppercase tracking-tight">ROI Percentage</span>
                    <span className={`text-xl font-black ${unit.roi_percentage >= 0 ? "text-green-600" : "text-red-600"}`}>
                      {unit.roi_percentage}%
                    </span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-[11px] font-black text-gray-400 uppercase tracking-tight">Payback Period</span>
                    <span className="text-xl font-black text-blue-600">{unit.roi?.payback_period} <span className="text-[10px] uppercase">Mths</span></span>
                  </div>
                  <div className="flex justify-between items-center">
                    <span className="text-[11px] font-black text-gray-400 uppercase tracking-tight">Avg Monthly Revenue</span>
                    <span className="text-xl font-black text-green-600">{fmt(unit.roi?.monthly_avg)}</span>
                  </div>
                </div>
              </div>

              {/* Goal Progress */}
              <div className="bg-white border border-gray-100 rounded-3xl p-6 shadow-sm">
                <p className="text-[10px] font-black text-gray-900 uppercase tracking-widest mb-6 border-b border-gray-50 pb-2">Goal Progress</p>
                <div className="space-y-8">
                  {/* Investment Achievement */}
                  <div>
                    <div className="flex justify-between items-center mb-3">
                      <span className="text-[11px] font-black text-gray-400 uppercase tracking-tight">Investment Achievement</span>
                      <span className={`text-sm font-black ${unit.roi_percentage >= 0 ? "text-green-600" : "text-red-600"}`}>
                        {unit.roi_percentage}%
                      </span>
                    </div>
                    <div className="h-2 bg-gray-100 rounded-full overflow-hidden">
                      <div 
                        className="h-full bg-indigo-600 rounded-full transition-all duration-1000" 
                        style={{ width: `${Math.min(Math.max(unit.roi_percentage || 0, 0), 100)}%` }}
                      />
                    </div>
                  </div>

                  {/* Monthly Target Efficiency */}
                  <div>
                    <div className="flex justify-between items-center mb-3">
                      <span className="text-[11px] font-black text-gray-400 uppercase tracking-tight">Monthly Target Efficiency</span>
                      <span className="text-xs font-black text-green-600">{fmt(unit.roi?.monthly_target)} <span className="text-gray-400 font-bold">Target</span></span>
                    </div>
                    <div className="h-2 bg-gray-100 rounded-full overflow-hidden">
                      <div 
                        className="h-full bg-green-500 rounded-full transition-all duration-1000" 
                        style={{ width: `${Math.min(Math.max(((unit.roi?.monthly_avg || 0) / (unit.roi?.monthly_target || 1)) * 100, 0), 100)}%` }}
                      />
                    </div>
                  </div>

                  <div className="flex justify-between items-center pt-4 border-t border-gray-50">
                    <span className="text-[11px] font-black text-gray-400 uppercase tracking-tight">Status</span>
                    <span className={`px-3 py-1 text-[10px] font-black rounded-full border uppercase ${unit.roi?.roi_status === 'Achieved' ? "bg-green-50 text-green-600 border-green-100" : "bg-blue-50 text-blue-600 border-blue-100"}`}>
                      {unit.roi?.roi_status}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* ── LOCATION ── */}
        {activeTab === "Location" && (
          <div className="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
            <div className="flex justify-between items-center mb-4">
              <p className="text-sm font-black text-gray-900 uppercase tracking-widest">Real-Time Location</p>
            </div>
            <div className="grid grid-cols-2 gap-3 mb-4">
              {[
                { label: "GPS Status", val: unit.imei ? "Active" : "No GPS" },
                { label: "Speed", val: `${unit.gps_speed || 0} KM/H` },
                { label: "Engine", val: unit.gps_ignition === 1 ? "ON" : "OFF" },
                { label: "Last Sync", val: unit.last_location_update ? fmtDate(unit.last_location_update) : "N/A" },
              ].map((g, i) => (
                <div key={i} className="bg-gray-50 p-3 rounded-xl border border-gray-100">
                  <p className="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">{g.label}</p>
                  <p className="text-sm font-black text-gray-800">{g.val}</p>
                </div>
              ))}
            </div>
            <div className="bg-gray-50 rounded-2xl h-40 flex items-center justify-center border border-gray-100">
              <div className="text-center">
                <MapPin className="w-8 h-8 text-gray-300 mx-auto mb-2" />
                <p className="text-xs font-bold text-gray-400">{unit.current_location || "Location unavailable"}</p>
                {unit.latitude && unit.longitude && (
                  <p className="text-[10px] text-gray-300 font-mono mt-1">{unit.latitude}, {unit.longitude}</p>
                )}
              </div>
            </div>
          </div>
        )}

        {/* ── DASHCAM ── */}
        {activeTab === "Dashcam" && (
          <div className="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
            <p className="text-sm font-black text-gray-900 uppercase tracking-widest border-b border-gray-100 pb-3 mb-4">Dashcam Information</p>
            <InfoRow label="Dashcam" value={<span className={`px-2 py-0.5 text-[10px] font-black rounded-full ${unit.dashcam_enabled ? "bg-green-100 text-green-700" : "bg-red-100 text-red-700"}`}>{unit.dashcam_enabled ? "Enabled" : "Disabled"}</span>} />
            <div className="mt-4 bg-gray-50 rounded-2xl h-32 flex items-center justify-center border border-gray-100">
              <div className="text-center">
                <Video className="w-8 h-8 text-gray-300 mx-auto mb-2" />
                <p className="text-xs font-bold text-gray-400">Video integration coming soon</p>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
