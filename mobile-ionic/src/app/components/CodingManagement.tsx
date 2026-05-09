import { useState, useEffect, useCallback } from "react";
import { Search, RefreshCw, Calendar, Truck, Clock, ShieldAlert, Edit2, Loader2, Save, X, Eye } from "lucide-react";
import api from "../services/api";
import { toast } from "sonner";

interface CodingRule {
  id: number;
  coding_day: string;
  restricted_plate_numbers: string;
  coding_type: string;
  allowed_areas: string;
  time_start: string | null;
  time_end: string | null;
  notes: string | null;
  status: string;
}

interface FleetUnit {
  id: number;
  plate_number: string;
  make: string;
  model: string;
  coding_day: string;
  status: string;
  driver1_name: string;
}

export function CodingManagement() {
  const [rules, setRules] = useState<CodingRule[]>([]);
  const [fleet, setFleet] = useState<FleetUnit[]>([]);
  const [stats, setStats] = useState({ today_coding: 0, on_road: 0, active_coding_fleet: 0 });
  const [todayName, setTodayName] = useState("");
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState("");
  const [activeTab, setActiveTab] = useState<string>("today"); // today, calendar, rules
  const [selectedDay, setSelectedDay] = useState<string>("Monday");
  const [editUnit, setEditUnit] = useState<FleetUnit | null>(null);
  const [newCodingDay, setNewCodingDay] = useState("");
  const [updating, setUpdating] = useState(false);

  const daysOfWeek = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const res = await api.get("/coding?search=" + searchQuery);
      if (res.data.success) {
        setRules(res.data.rules);
        setFleet(res.data.fleet);
        setStats(res.data.stats);
        setTodayName(res.data.today_name);
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to load coding data");
    } finally {
      setLoading(false);
    }
  }, [searchQuery]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const handleUpdateCodingDay = async () => {
    if (!editUnit || !newCodingDay) return;
    setUpdating(true);
    try {
      const res = await api.post("/coding/update-day", {
        unit_id: editUnit.id,
        coding_day: newCodingDay
      });
      if (res.data.success) {
        toast.success(`Coding day for ${editUnit.plate_number} updated to ${newCodingDay}`);
        setEditUnit(null);
        fetchData();
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to update coding day");
    } finally {
      setUpdating(false);
    }
  };

  // Filter fleet based on today's coding day and search query
  const codingTodayUnits = fleet.filter(unit => {
    const matchesDay = unit.coding_day === todayName;
    const matchesSearch = searchQuery ? unit.plate_number.toLowerCase().includes(searchQuery.toLowerCase()) : true;
    return matchesDay && matchesSearch;
  });

  // Filter fleet for the calendar view based on selected horizontal tab day
  const calendarUnits = fleet.filter(unit => unit.coding_day === selectedDay);

  return (
    <div className="flex flex-col min-h-full bg-gray-50 pb-20">
      {/* Header */}
      <div className="bg-white px-4 pt-6 pb-4 border-b border-gray-200">
        <div className="flex items-center justify-between mb-4">
          <div>
            <h1 className="text-xl font-black text-gray-900 leading-tight">Coding Management</h1>
            <p className="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Plate number scheduling & restrictions</p>
          </div>
          <button onClick={fetchData} className="p-2 bg-gray-100 rounded-xl active:bg-gray-200 transition-colors">
            <RefreshCw className={`w-4 h-4 text-gray-500 ${loading ? 'animate-spin' : ''}`} />
          </button>
        </div>

        {/* Dashboard Stat Cards */}
        <div className="grid grid-cols-3 gap-2.5 mb-5">
          <div className="bg-amber-50 rounded-2xl p-3 border border-amber-100">
            <p className="text-[9px] font-black uppercase text-amber-500 tracking-wider">Coding Today</p>
            <p className="text-lg font-black text-amber-700 leading-none mt-1">{stats.today_coding}</p>
          </div>
          <div className="bg-green-50 rounded-2xl p-3 border border-green-100">
            <p className="text-[9px] font-black uppercase text-green-500 tracking-wider">Active Road</p>
            <p className="text-lg font-black text-green-700 leading-none mt-1">{stats.on_road}</p>
          </div>
          <div className="bg-red-50 rounded-2xl p-3 border border-red-100">
            <p className="text-[9px] font-black uppercase text-red-500 tracking-wider">Active coding</p>
            <p className="text-lg font-black text-red-700 leading-none mt-1">{stats.active_coding_fleet}</p>
          </div>
        </div>

        {/* Search */}
        <div className="relative mb-4">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input 
            type="text" 
            placeholder="Search plate or unit..."
            className="w-full pl-10 pr-4 py-3 bg-gray-100 border-none rounded-2xl text-sm font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>

        {/* Navigation Tabs */}
        <div className="flex bg-gray-100 p-1 rounded-2xl">
          <button 
            onClick={() => { setActiveTab("today"); setSearchQuery(""); }}
            className={`flex-1 py-3 text-center rounded-xl text-[10px] font-black uppercase tracking-wider transition-all ${activeTab === "today" ? "bg-white text-blue-600 shadow-sm" : "text-gray-500"}`}
          >
            Today's Focus
          </button>
          <button 
            onClick={() => { setActiveTab("calendar"); setSearchQuery(""); }}
            className={`flex-1 py-3 text-center rounded-xl text-[10px] font-black uppercase tracking-wider transition-all ${activeTab === "calendar" ? "bg-white text-blue-600 shadow-sm" : "text-gray-500"}`}
          >
            Weekly Calendar
          </button>
          <button 
            onClick={() => { setActiveTab("rules"); setSearchQuery(""); }}
            className={`flex-1 py-3 text-center rounded-xl text-[10px] font-black uppercase tracking-wider transition-all ${activeTab === "rules" ? "bg-white text-blue-600 shadow-sm" : "text-gray-500"}`}
          >
            Active Rules
          </button>
        </div>
      </div>

      {/* Content Area */}
      <div className="flex-1 p-4">
        {loading ? (
          <div className="flex flex-col items-center justify-center py-20 gap-4">
            <Loader2 className="w-8 h-8 text-blue-500 animate-spin" />
            <p className="text-xs font-black text-gray-400 uppercase tracking-widest">Loading details...</p>
          </div>
        ) : activeTab === "today" ? (
          <div className="space-y-3">
            <div className="bg-amber-500 text-white rounded-3xl p-5 shadow-lg shadow-amber-200">
              <div className="flex items-center gap-2 mb-2">
                <Calendar className="w-5 h-5" />
                <h2 className="font-black text-sm uppercase tracking-wider">Today is {todayName}</h2>
              </div>
              <p className="text-xs font-medium text-amber-50">Units scheduled below are strictly restricted from city roads today based on Unified Plate rules.</p>
            </div>

            {codingTodayUnits.length === 0 ? (
              <div className="bg-white rounded-3xl p-8 border-2 border-dashed border-gray-200 text-center">
                <Truck className="w-10 h-10 text-gray-300 mx-auto mb-3" />
                <p className="text-xs font-black text-gray-400 uppercase tracking-widest">No units are coding today</p>
              </div>
            ) : (
              <div className="space-y-3">
                {codingTodayUnits.map((unit) => (
                  <div key={unit.id} className="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                    <div>
                      <span className="px-2.5 py-1 bg-amber-50 text-amber-600 rounded-lg text-[9px] font-black uppercase tracking-wider mb-2 inline-block">CODING TODAY</span>
                      <h3 className="text-sm font-black text-gray-900 leading-none">{unit.plate_number}</h3>
                      <p className="text-[10px] text-gray-400 font-bold uppercase mt-1">{unit.make} {unit.model} | Driver: {unit.driver1_name || 'Unassigned'}</p>
                    </div>
                    <button 
                      onClick={() => { setEditUnit(unit); setNewCodingDay(unit.coding_day); }}
                      className="p-3 bg-gray-50 text-gray-500 rounded-2xl hover:bg-gray-100 transition-all active:scale-95"
                    >
                      <Edit2 className="w-4 h-4" />
                    </button>
                  </div>
                ))}
              </div>
            )}
          </div>
        ) : activeTab === "calendar" ? (
          <div className="space-y-4">
            {/* Horizontal week picker */}
            <div className="flex gap-1.5 overflow-x-auto no-scrollbar">
              {daysOfWeek.map((day) => (
                <button
                  key={day}
                  onClick={() => setSelectedDay(day)}
                  className={`px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider whitespace-nowrap transition-all ${selectedDay === day ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-500 border border-gray-100'}`}
                >
                  {day}
                </button>
              ))}
            </div>

            <div className="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
              <h2 className="font-black text-sm text-gray-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                <Truck className="w-4 h-4 text-blue-500" />
                {selectedDay} Fleet ({calendarUnits.length} Units)
              </h2>

              {calendarUnits.length === 0 ? (
                <p className="text-xs font-bold text-gray-400 text-center py-10 uppercase tracking-widest">No units assigned on this day</p>
              ) : (
                <div className="divide-y divide-gray-100">
                  {calendarUnits.map((unit) => (
                    <div key={unit.id} className="py-3 flex items-center justify-between first:pt-0 last:pb-0">
                      <div>
                        <p className="text-xs font-black text-gray-900">{unit.plate_number}</p>
                        <p className="text-[9px] text-gray-400 font-bold uppercase mt-0.5">{unit.make} {unit.model} | {unit.status}</p>
                      </div>
                      <button 
                        onClick={() => { setEditUnit(unit); setNewCodingDay(unit.coding_day); }}
                        className="p-2 bg-gray-50 text-gray-400 rounded-xl hover:bg-gray-100 transition-all active:scale-95"
                      >
                        <Edit2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>
        ) : (
          <div className="space-y-3">
            {rules.length === 0 ? (
              <div className="bg-white rounded-3xl p-8 border-2 border-dashed border-gray-200 text-center">
                <ShieldAlert className="w-10 h-10 text-gray-300 mx-auto mb-3" />
                <p className="text-xs font-black text-gray-400 uppercase tracking-widest">No rules configured in system</p>
              </div>
            ) : (
              <div className="space-y-3">
                {rules.map((rule) => (
                  <div key={rule.id} className="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
                    <div className="flex items-center justify-between mb-3">
                      <span className="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase tracking-wider">{rule.coding_day}</span>
                      <span className="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-[9px] font-black uppercase tracking-wider">{rule.status}</span>
                    </div>
                    <p className="text-sm font-black text-gray-900 leading-tight mb-2">Plates Restricted: {rule.restricted_plate_numbers}</p>
                    {rule.notes && <p className="text-[10px] text-gray-400 font-medium mb-3 bg-gray-50 p-2.5 rounded-xl">{rule.notes}</p>}
                    <div className="flex items-center gap-1.5 text-gray-500">
                      <Clock className="w-3.5 h-3.5 text-blue-500" />
                      <span className="text-[10px] font-bold uppercase tracking-tight">Allowed: {rule.allowed_areas || "No exceptions"}</span>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}
      </div>

      {/* Re-assign Coding Day Modal */}
      {editUnit && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div className="bg-white w-full max-w-sm rounded-[2rem] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
            <div className="bg-blue-600 p-6 flex flex-col items-center gap-3 text-white">
              <div className="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md">
                <Calendar className="w-6 h-6" />
              </div>
              <h3 className="font-black text-lg text-center leading-tight">Re-assign Coding Day</h3>
              <p className="text-blue-100 text-[10px] font-bold text-center uppercase tracking-widest">Update coding restriction for {editUnit.plate_number}</p>
            </div>

            <div className="p-6 space-y-4">
              <div>
                <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Assigned Day</label>
                <select
                  value={newCodingDay}
                  onChange={(e) => setNewCodingDay(e.target.value)}
                  className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-black focus:outline-none focus:border-blue-500 transition-all"
                >
                  {daysOfWeek.map((day) => (
                    <option key={day} value={day}>{day}</option>
                  ))}
                </select>
              </div>

              <div className="flex gap-3 pt-2">
                <button 
                  onClick={() => setEditUnit(null)}
                  className="flex-1 py-4 border-2 border-gray-100 rounded-2xl text-[11px] font-black text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button 
                  disabled={updating}
                  onClick={handleUpdateCodingDay}
                  className="flex-1 py-4 bg-blue-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-lg shadow-blue-200 active:scale-95 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                >
                  {updating ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <Save className="w-3.5 h-3.5" />}
                  Save Changes
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
