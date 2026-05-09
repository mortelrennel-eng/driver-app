import { useState, useEffect, useCallback } from "react";
import { useNavigate } from "react-router-dom";
import { Search, RefreshCw, ChevronRight, Loader2, Car, X, SlidersHorizontal, Grid3X3, List, Printer, Flag, Plus, AlertTriangle, Wrench, MoreVertical, Eye, Edit2, Trash2, Info, CreditCard, Save, Calendar, Users, Clock, AlertCircle, UserMinus, ChevronDown } from "lucide-react";
import api from "../services/api";
import { toast } from "sonner";

const fmtRate = (n: any) => "₱" + Number(n || 0).toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function StatusDot({ status }: { status: string }) {
  const s = status?.toLowerCase();
  const map: any = {
    active:      { dot: "bg-green-500 shadow-[0_0_6px_rgba(34,197,94,0.7)] animate-pulse", text: "text-green-600", label: "Active" },
    maintenance: { dot: "bg-red-500 shadow-[0_0_5px_rgba(239,68,68,0.6)]",                 text: "text-red-600",   label: "Maintenance" },
    coding:      { dot: "bg-yellow-500 animate-[blink_1.1s_step-start_infinite]",           text: "text-yellow-600",label: "Coding" },
    at_risk:     { dot: "bg-orange-500 shadow-[0_0_6px_rgba(249,115,22,0.7)] animate-pulse",text: "text-orange-600",label: "At Risk" },
  };
  const cfg = map[s] || { dot: "bg-gray-400", text: "text-gray-500", label: status || "Unknown" };
  return (
    <span className="inline-flex items-center gap-1.5">
      <span className={`w-2 h-2 rounded-full flex-shrink-0 ${cfg.dot}`} />
      <span className={`text-[10px] font-black uppercase tracking-widest ${cfg.text}`}>{cfg.label}</span>
    </span>
  );
}

function HealthBar({ unit }: { unit: any }) {
  const hasGps = unit.gps_device_count > 0 || !!unit.imei;
  if (!hasGps) return null;
  const SERVICE_KM = 5000;
  const kmSince = Math.max(0, (unit.current_gps_odo || 0) - (unit.last_service_odo_gps || 0));
  const pct = Math.min(100, Math.round((kmSince / SERVICE_KM) * 100));
  const isOverdue = kmSince >= SERVICE_KM;
  
  if (!isOverdue) return (
    <div className="mt-2">
      <div className="h-1 bg-gray-100 rounded-full overflow-hidden">
        <div className="h-full bg-green-500 rounded-full" style={{ width: `${pct}%` }} />
      </div>
    </div>
  );

  return (
    <div className="mt-4 pt-4 border-t border-red-50">
      <div className="flex justify-between items-center mb-1.5">
        <span className="flex items-center gap-1.5 text-[10px] font-black text-red-600 uppercase tracking-widest">
          <AlertTriangle className="w-3 h-3 animate-pulse" /> SERVICE OVERDUE
        </span>
        <span className="text-[10px] text-gray-400 font-bold tabular-nums">
          {Number(unit.current_gps_odo || 0).toLocaleString()} / {Number(SERVICE_KM).toLocaleString()} KM
        </span>
      </div>
      <div className="relative h-2 bg-red-100 rounded-full overflow-hidden mb-2">
        <div className="absolute inset-y-0 left-0 bg-red-600 rounded-full animate-pulse" style={{ width: "100%" }}>
          <div className="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-[shimmer_2s_infinite]" />
        </div>
      </div>
      <p className="text-[10px] text-red-500 font-bold italic">
        Unit has exceeded the {Number(SERVICE_KM).toLocaleString()}km service interval by {Number(kmSince).toLocaleString()}km.
      </p>
    </div>
  );
}

function FlaggedModal({ units, onClose }: { units: any[], onClose: () => void }) {
  const flagged = units.filter(u => u.status === 'at_risk' || u.status === 'missing' || u.is_pinned_missing);
  return (
    <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-end">
      <div className="bg-white w-full rounded-t-3xl max-h-[80vh] flex flex-col shadow-2xl">
        <div className="bg-red-600 p-4 rounded-t-3xl flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Flag className="w-5 h-5 text-white" />
            <p className="font-black text-white text-base">Flagged Units</p>
            <span className="bg-white text-red-600 text-xs font-black px-2 py-0.5 rounded-full">{flagged.length}</span>
          </div>
          <button onClick={onClose}><X className="w-5 h-5 text-white" /></button>
        </div>
        <div className="flex-1 overflow-y-auto p-4 space-y-3">
          {flagged.length === 0 ? (
            <div className="text-center py-10">
              <Flag className="w-12 h-12 text-gray-200 mx-auto mb-2" />
              <p className="text-gray-500 font-bold text-sm">No flagged units</p>
            </div>
          ) : flagged.map(u => (
            <div key={u.id} className="bg-red-50 border border-red-200 rounded-2xl p-4">
              <div className="flex justify-between items-start mb-2">
                <p className="font-black text-gray-900 text-base">{u.plate_number}</p>
                <StatusDot status={u.status} />
              </div>
              <p className="text-xs text-gray-500">{u.make} {u.model} • {u.year}</p>
              <p className="text-xs text-gray-400 mt-1">D1: {u.primary_driver || "No Driver"}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

const getCodingInfo = (plate: string) => {
  if (!plate) return { day: "N/A", next: "N/A", remaining: "N/A" };
  const lastDigit = plate.trim().slice(-1);
  const dayMap: any = { '1': 'Monday', '2': 'Monday', '3': 'Tuesday', '4': 'Tuesday', '5': 'Wednesday', '6': 'Wednesday', '7': 'Thursday', '8': 'Thursday', '9': 'Friday', '0': 'Friday' };
  const codingDay = dayMap[lastDigit] || "N/A";
  
  if (codingDay === "N/A") return { day: "N/A", next: "N/A", remaining: "N/A" };
  
  const dayIndex: any = { 'Monday': 1, 'Tuesday': 2, 'Wednesday': 3, 'Thursday': 4, 'Friday': 5 };
  const today = new Date();
  const currentDay = today.getDay(); // 0 is Sunday, 1 is Monday...
  const target = dayIndex[codingDay];
  
  let diff = target - currentDay;
  if (diff < 0) diff += 7;
  if (diff === 0) diff = 7; // Next week if today is the day

  const nextDate = new Date();
  nextDate.setDate(today.getDate() + diff);
  
  return {
    day: codingDay,
    next: nextDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
    remaining: `${diff} ${diff === 1 ? 'day' : 'days'}`
  };
};

function SearchableSelect({ label, placeholder, value, options, onChange, helperText }: { 
  label: string; 
  placeholder: string; 
  value: string; 
  options: any[]; 
  onChange: (id: string) => void;
  helperText?: string;
}) {
  const [isOpen, setIsOpen] = useState(false);
  const [search, setSearch] = useState("");
  
  const selectedOption = options.find(o => o.id == value);
  const filtered = options.filter(o => 
    o.name.toLowerCase().includes(search.toLowerCase()) || 
    (o.license && o.license.toLowerCase().includes(search.toLowerCase()))
  );

  return (
    <div className="relative">
      <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">{label}</label>
      <div className="relative">
        <input 
          type="text"
          className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl px-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all pr-10"
          placeholder={placeholder}
          value={isOpen ? search : (selectedOption ? selectedOption.name : "")}
          onChange={e => { setSearch(e.target.value); setIsOpen(true); }}
          onFocus={() => { setIsOpen(true); setSearch(""); }}
          onBlur={() => setTimeout(() => setIsOpen(false), 200)}
        />
        <div className="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-1">
          {value && !isOpen && (
            <button type="button" onClick={(e) => { e.stopPropagation(); onChange(""); }} className="p-1 hover:bg-gray-100 rounded-full">
              <X className="w-3 h-3 text-gray-400" />
            </button>
          )}
          <ChevronDown className={`w-4 h-4 text-gray-400 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
        </div>
      </div>
      
      {isOpen && (
        <div className="absolute z-[60] left-0 right-0 mt-2 bg-white border border-gray-100 rounded-2xl shadow-xl max-h-60 overflow-y-auto overflow-x-hidden animate-in fade-in slide-in-from-top-2 duration-200">
          <div className="p-2 space-y-1">
            {filtered.length === 0 ? (
              <p className="p-4 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">No drivers found</p>
            ) : (
              filtered.map(opt => (
                <button
                  key={opt.id}
                  type="button"
                  onClick={() => { onChange(opt.id); setIsOpen(false); setSearch(""); }}
                  className={`w-full text-left p-3 rounded-xl transition-all flex flex-col gap-0.5 ${value == opt.id ? 'bg-blue-50' : 'hover:bg-gray-50'}`}
                >
                  <span className={`text-sm font-black ${value == opt.id ? 'text-blue-600' : 'text-gray-900'}`}>{opt.name}</span>
                  {opt.license && <span className="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{opt.license}</span>}
                </button>
              ))
            )}
          </div>
        </div>
      )}
      {helperText && <p className="text-[10px] text-gray-400 font-bold mt-1.5 ml-1 italic">{helperText}</p>}
    </div>
  );
}

function EditUnitModal({ unit, onClose, onUpdated }: { unit: any; onClose: () => void; onUpdated: () => void }) {
  const [form, setForm] = useState({ 
    plate_number: unit.plate_number, 
    make: unit.make, 
    model: unit.model, 
    year: unit.year, 
    motor_no: unit.motor_no, 
    chassis_no: unit.chassis_no, 
    status: unit.status, 
    unit_type: unit.unit_type || "standard",
    boundary_rate: unit.boundary_rate || "",
    purchase_date: unit.purchase_date || "",
    purchase_cost: unit.purchase_cost || "",
    driver_id: unit.driver_id || "",
    secondary_driver_id: unit.secondary_driver_id || "",
  });
  const [drivers, setDrivers] = useState<any[]>([]);
  const [loadingDrivers, setLoadingDrivers] = useState(false);
  const [saving, setSaving] = useState(false);
  const set = (k: string, v: any) => setForm(p => ({ ...p, [k]: v }));

  useEffect(() => {
    const fetchDrivers = async () => {
      setLoadingDrivers(true);
      try {
        const res = await api.get("/drivers");
        if (res.data.success) setDrivers(res.data.data);
      } catch (err) { console.error("Error fetching drivers", err); }
      finally { setLoadingDrivers(false); }
    };
    fetchDrivers();
  }, []);

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      await api.put(`/units/${unit.id}`, form);
      toast.success("Unit updated successfully!");
      onUpdated();
      onClose();
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to update unit.");
    } finally { setSaving(false); }
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-end sm:items-center sm:justify-center p-0 sm:p-4">
      <div className="bg-white w-full sm:max-w-xl rounded-t-3xl sm:rounded-3xl max-h-[95vh] flex flex-col shadow-2xl overflow-hidden">
        {/* Header - matching web premium dark look */}
        <div className="bg-slate-900 p-5 flex items-center justify-between border-b border-white/10">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
              <Edit2 className="w-5 h-5 text-white" />
            </div>
            <div>
              <p className="font-black text-white text-base leading-none">Edit Unit</p>
              <p className="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-1">Update vehicle information and settings</p>
            </div>
          </div>
          <button onClick={onClose} className="p-2 hover:bg-white/10 rounded-full transition-colors"><X className="w-5 h-5 text-white" /></button>
        </div>

        <form onSubmit={submit} className="flex-1 overflow-y-auto p-6 space-y-6">
          {/* Section: Basic Information */}
          <div className="flex items-center gap-2 mb-2 px-1">
            <div className="w-7 h-7 bg-blue-50 rounded-lg flex items-center justify-center">
              <Info className="w-4 h-4 text-blue-500" />
            </div>
            <p className="text-sm font-black text-gray-800 tracking-tight">Basic Information</p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
            {/* Plate Number */}
            <div className="col-span-full">
              <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Plate Number <span className="text-red-500">*</span></label>
              <div className="relative">
                <CreditCard className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <input required className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl pl-11 pr-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all uppercase"
                  placeholder="ABC 1234" value={form.plate_number} onChange={e => set("plate_number", e.target.value.toUpperCase())} />
              </div>
            </div>

            {/* Make */}
            <div>
              <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Make <span className="text-red-500">*</span></label>
              <input required className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl px-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all uppercase"
                placeholder="TOYOTA" value={form.make} onChange={e => set("make", e.target.value.toUpperCase())} />
            </div>

            {/* Model */}
            <div>
              <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Model <span className="text-red-500">*</span></label>
              <input required className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl px-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all uppercase"
                placeholder="VIOS" value={form.model} onChange={e => set("model", e.target.value.toUpperCase())} />
            </div>

            {/* Year */}
            <div>
              <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Year <span className="text-red-500">*</span></label>
              <input type="number" required className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl px-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all"
                value={form.year} onChange={e => set("year", e.target.value)} />
            </div>

            {/* Motor No */}
            <div className="col-span-full">
              <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Motor No <span className="text-red-500">*</span></label>
              <input required className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl px-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all uppercase"
                placeholder="Engine serial number" value={form.motor_no} onChange={e => set("motor_no", e.target.value.toUpperCase())} />
            </div>

            {/* Chassis No */}
            <div className="col-span-full">
              <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Chassis No <span className="text-red-500">*</span></label>
              <input required className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl px-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all uppercase"
                placeholder="Vehicle identification number" value={form.chassis_no} onChange={e => set("chassis_no", e.target.value.toUpperCase())} />
            </div>

            {/* Status */}
            <div>
              <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Status</label>
              <select className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl px-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all"
                value={form.status} onChange={e => set("status", e.target.value)}>
                <option value="active">Active</option>
                <option value="at_risk">At Risk / Missing</option>
                <option value="maintenance">Maintenance</option>
                <option value="retired">Retired</option>
                <option value="vacant">Vacant</option>
              </select>
            </div>

            {/* Unit Type */}
            <div>
              <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Unit Type</label>
              <select className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl px-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all"
                value={form.unit_type} onChange={e => set("unit_type", e.target.value)}>
                <option value="new">New</option>
                <option value="standard">Standard</option>
                <option value="old">Old</option>
                <option value="rented">Rented</option>
              </select>
            </div>
          </div>

          {/* Section: Financial Information */}
          <div className="pt-4 border-t border-gray-100">
            <div className="flex items-center gap-2 mb-6 px-1">
              <div className="w-7 h-7 bg-purple-50 rounded-lg flex items-center justify-center">
                <span className="text-purple-500 font-black text-xs leading-none">₱</span>
              </div>
              <p className="text-sm font-black text-gray-800 tracking-tight">Financial Information</p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
              {/* Boundary Rate */}
              <div className="col-span-full">
                <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-0.5 ml-1">Boundary Rate <span className="text-red-500">*</span></label>
                <p className="text-[10px] text-gray-400 font-bold mb-1.5 ml-1">Daily boundary collection target</p>
                <div className="relative">
                  <span className="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-gray-400">₱</span>
                  <input type="number" required className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl pl-11 pr-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all"
                    placeholder="0.00" value={form.boundary_rate} onChange={e => set("boundary_rate", e.target.value)} />
                </div>
              </div>

              {/* Purchase Cost */}
              <div>
                <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-0.5 ml-1">Purchase Cost</label>
                <div className="relative">
                  <span className="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-black text-gray-400">₱</span>
                  <input type="number" className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl pl-11 pr-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all"
                    placeholder="0.00" value={form.purchase_cost} onChange={e => set("purchase_cost", e.target.value)} />
                </div>
              </div>

              {/* Purchase Date */}
              <div>
                <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-0.5 ml-1">Purchase Date</label>
                <div className="relative">
                  <Calendar className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                  <input type="date" className="w-full border-2 border-gray-100 bg-gray-50/30 rounded-2xl pl-11 pr-4 py-3.5 text-sm font-black text-gray-900 focus:outline-none focus:border-blue-400 focus:bg-white transition-all"
                    value={form.purchase_date} onChange={e => set("purchase_date", e.target.value)} />
                </div>
              </div>
            </div>
          </div>

          {/* Section: Driver Assignment */}
          <div className="pt-4 border-t border-gray-100">
            <div className="flex items-center gap-2 mb-6 px-1">
              <div className="w-7 h-7 bg-blue-50 rounded-lg flex items-center justify-center">
                <Users className="w-4 h-4 text-blue-500" />
              </div>
              <p className="text-sm font-black text-gray-800 tracking-tight">Driver Assignment</p>
            </div>
            
            <div className="space-y-6">
              {/* Primary Driver */}
              <SearchableSelect 
                label="Primary Driver"
                placeholder="Start typing to search drivers..."
                value={form.driver_id}
                options={drivers.filter(d => d.is_available || d.id == unit.driver_id || d.id == unit.secondary_driver_id)}
                onChange={val => {
                  set("driver_id", val);
                  if (val && val === form.secondary_driver_id) set("secondary_driver_id", "");
                }}
                helperText="Main driver assigned to this unit"
              />

              {/* Secondary Driver */}
              <SearchableSelect 
                label="Secondary Driver (Optional)"
                placeholder="Backup or relief driver (optional)"
                value={form.secondary_driver_id}
                options={drivers.filter(d => d.is_available || d.id == unit.driver_id || d.id == unit.secondary_driver_id)}
                onChange={val => {
                  set("secondary_driver_id", val);
                  if (val && val === form.driver_id) set("driver_id", "");
                }}
                helperText="Backup or relief driver (optional)"
              />

              {/* Remove All Drivers Button - matching web premium look */}
              <div className="pt-2">
                <button 
                  type="button"
                  onClick={() => { setForm(p => ({ ...p, driver_id: "", secondary_driver_id: "" })); }}
                  className="w-full flex items-center justify-center gap-2 py-3 bg-red-50 border border-red-100 rounded-2xl text-red-600 hover:bg-red-100 transition-all active:scale-[0.98]"
                >
                  <UserMinus className="w-4 h-4" />
                  <span className="text-xs font-black uppercase tracking-widest">Remove All Drivers</span>
                </button>
                <p className="text-[9px] text-center text-gray-400 font-bold mt-2 uppercase tracking-tighter">Clear both driver assignments for this unit</p>
              </div>
            </div>
          </div>

          {/* Section: Coding Information (Synchronized with Web) */}
          <div className="pt-4 border-t border-gray-100">
            {/* Coding Schedule Summary (Light Blue Box from Web) */}
            <div className="bg-blue-50/50 rounded-2xl p-4 border border-blue-100 mb-6">
              <div className="grid grid-cols-5 gap-1">
                {[
                  { d: 'Mon', n: '1, 2' },
                  { d: 'Tue', n: '3, 4' },
                  { d: 'Wed', n: '5, 6' },
                  { d: 'Thu', n: '7, 8' },
                  { d: 'Fri', n: '9, 0' }
                ].map(s => (
                  <div key={s.d} className="text-center">
                    <p className="text-[9px] font-black text-gray-400 uppercase mb-1">{s.d}</p>
                    <span className="px-2 py-0.5 bg-blue-100 text-blue-600 text-[10px] font-black rounded-lg">{s.n}</span>
                  </div>
                ))}
              </div>
            </div>

            <div className="flex items-center gap-2 mb-6 px-1">
              <div className="w-7 h-7 bg-orange-50 rounded-lg flex items-center justify-center">
                <Clock className="w-4 h-4 text-orange-500" />
              </div>
              <p className="text-sm font-black text-gray-800 tracking-tight">Coding Information</p>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
              {(() => {
                const info = getCodingInfo(form.plate_number);
                return (
                  <>
                    <div>
                      <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Coding Day</label>
                      <div className="px-4 py-3.5 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-black text-gray-900 uppercase">
                        {info.day}
                      </div>
                    </div>
                    <div>
                      <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Next Coding Date</label>
                      <div className="px-4 py-3.5 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-black text-gray-900">
                        {info.next}
                      </div>
                    </div>
                    <div>
                      <label className="block text-[11px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Days Until Next Coding</label>
                      <div className="px-4 py-3.5 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-black text-blue-600">
                        {info.remaining}
                      </div>
                    </div>
                  </>
                );
              })()}
            </div>
          </div>

          <div className="flex gap-4 pt-6 pb-2">
            <button type="button" onClick={onClose} className="flex-1 py-4 rounded-2xl border-2 border-gray-100 text-sm font-black text-gray-500 hover:bg-gray-50 active:bg-gray-100 transition-all uppercase tracking-widest">Cancel</button>
            <button type="submit" disabled={saving} className="flex-[1.5] py-4 rounded-2xl bg-blue-600 text-white text-sm font-black shadow-lg shadow-blue-200 active:scale-95 transition-all flex items-center justify-center gap-2 uppercase tracking-widest disabled:opacity-50">
              {saving ? <Loader2 className="w-4 h-4 animate-spin" /> : <Save className="w-4 h-4" />}
              {saving ? "Updating..." : "Update Unit"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function AddUnitModal({ onClose, onAdded }: { onClose: () => void; onAdded: () => void }) {
  const [form, setForm] = useState({ plate_number:"", make:"", model:"", year: new Date().getFullYear(), boundary_rate:"1100", purchase_cost:"0", motor_no:"", chassis_no:"" });
  const [saving, setSaving] = useState(false);
  const set = (k: string, v: any) => setForm(p => ({ ...p, [k]: v }));

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    try {
      await api.post("/units", form);
      toast.success("Unit added successfully!");
      onAdded();
      onClose();
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to add unit.");
    } finally { setSaving(false); }
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-end">
      <div className="bg-white w-full rounded-t-3xl max-h-[90vh] flex flex-col shadow-2xl">
        <div className="bg-slate-800 p-4 rounded-t-3xl flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Car className="w-5 h-5 text-white" />
            <p className="font-black text-white text-base">Add New Unit</p>
          </div>
          <button onClick={onClose}><X className="w-5 h-5 text-white" /></button>
        </div>
        <form onSubmit={submit} className="flex-1 overflow-y-auto p-5 space-y-4">
          {[
            { label:"Plate Number *", key:"plate_number", placeholder:"e.g. ABC 1234", upper:true },
            { label:"Make *",         key:"make",         placeholder:"e.g. TOYOTA",   upper:true },
            { label:"Model *",        key:"model",        placeholder:"e.g. VIOS",     upper:true },
            { label:"Motor No *",     key:"motor_no",     placeholder:"e.g. 2NZ7847183", upper:true },
            { label:"Chassis No *",   key:"chassis_no",   placeholder:"e.g. NCP151...", upper:true },
          ].map(f => (
            <div key={f.key}>
              <label className="block text-xs font-bold text-gray-700 mb-1">{f.label}</label>
              <input required className="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-yellow-400"
                placeholder={f.placeholder} value={(form as any)[f.key]}
                onChange={e => set(f.key, f.upper ? e.target.value.toUpperCase() : e.target.value)} />
            </div>
          ))}
          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">Year *</label>
              <input type="number" required className="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-yellow-400"
                value={form.year} onChange={e => set("year", e.target.value)} />
            </div>
            <div>
              <label className="block text-xs font-bold text-gray-700 mb-1">Boundary Rate *</label>
              <input type="number" required className="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-yellow-400"
                value={form.boundary_rate} onChange={e => set("boundary_rate", e.target.value)} />
            </div>
          </div>
          <div>
            <label className="block text-xs font-bold text-gray-700 mb-1">Purchase Cost</label>
            <input type="number" className="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:border-yellow-400"
              value={form.purchase_cost} onChange={e => set("purchase_cost", e.target.value)} />
          </div>
          <div className="flex gap-3 pt-2 pb-6">
            <button type="button" onClick={onClose} className="flex-1 py-3 rounded-xl border-2 border-gray-200 text-sm font-black text-gray-700">Cancel</button>
            <button type="submit" disabled={saving} className="flex-1 py-3 rounded-xl bg-green-600 text-white text-sm font-black disabled:opacity-50">
              {saving ? "Saving..." : "+ Add Unit"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export function UnitManagement() {
  const [units, setUnits] = useState<any[]>([]);
  const [stats, setStats] = useState({ total:0, on_road:0, garage:0, workshop:0, coding:0 });
  const [isLoading, setIsLoading] = useState(true);
  const [apiError, setApiError] = useState<string|null>(null);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("");
  const [sort, setSort] = useState("alphabetical");
  const [showFilters, setShowFilters] = useState(false);
  const [viewMode, setViewMode] = useState<"table"|"cards">("table");
  const [showFlagged, setShowFlagged] = useState(false);
  const [showAddUnit, setShowAddUnit] = useState(false);
  const [editingUnit, setEditingUnit] = useState<any|null>(null);
  const [activeMenu, setActiveMenu] = useState<number|null>(null);
  const navigate = useNavigate();

  const archiveUnit = async (unit: any) => {
    if (!window.confirm(`Are you sure you want to archive Unit ${unit.plate_number}? This will move it to the archive system.`)) return;
    try {
      await api.delete(`/units/${unit.id}`);
      toast.success(`Unit ${unit.plate_number} archived successfully.`);
      fetchUnits();
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to archive unit.");
    } finally {
      setActiveMenu(null);
    }
  };

  const fetchUnits = useCallback(async () => {
    setIsLoading(true);
    setApiError(null);
    try {
      const params: any = {};
      if (search) params.search = search;
      if (statusFilter) params.status = statusFilter;
      if (sort) params.sort = sort;
      const res = await api.get("/units", { params });
      if (res.data.success) {
        setUnits(res.data.data);
        if (res.data.stats) setStats(res.data.stats);
      }
    } catch (e: any) {
      const msg = e.response?.data?.message || e.message || "Failed to load units.";
      setApiError(msg);
      toast.error("API Error: " + msg);
    } finally { setIsLoading(false); }
  }, [search, statusFilter, sort]);

  useEffect(() => {
    const t = setTimeout(() => fetchUnits(), 350);
    return () => clearTimeout(t);
  }, [fetchUnits]);

  const printToPDF = () => {
    window.open("https://eurotaxisystem.site/units/print", "_blank");
  };

  return (
    <div className="flex flex-col min-h-full bg-gray-50">
      {/* ── Header ── */}
      <div className="bg-white px-4 pt-4 pb-3 border-b border-gray-200">
        <div className="flex items-center justify-between mb-3">
          <div>
            <h1 className="text-xl font-black text-gray-900 leading-tight">Unit Management</h1>
            <p className="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Manage your fleet of taxi units</p>
          </div>
          <button onClick={fetchUnits} className="p-2 bg-gray-100 rounded-xl active:bg-gray-200">
            <RefreshCw className="w-4 h-4 text-gray-500" />
          </button>
        </div>

        {/* Search */}
        <div className="relative mb-2">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input type="text" placeholder="Search plate numbers, model, driver..."
            value={search} onChange={e => setSearch(e.target.value)}
            className="w-full pl-10 pr-10 py-3 bg-gray-900 text-white placeholder-gray-400 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" />
          {search && <button onClick={() => setSearch("")} className="absolute right-3 top-1/2 -translate-y-1/2"><X className="w-4 h-4 text-gray-400" /></button>}
        </div>

        {/* Filter toggle */}
        <button onClick={() => setShowFilters(!showFilters)} className="flex items-center gap-1.5 text-[10px] font-black text-gray-500 uppercase tracking-widest px-2 py-1">
          <SlidersHorizontal className="w-3 h-3" />{showFilters ? "Hide Filters" : "Show Filters"}
        </button>
        {showFilters && (
          <div className="flex gap-2 mt-2">
            <select value={statusFilter} onChange={e => setStatusFilter(e.target.value)}
              className="flex-1 bg-gray-100 rounded-xl px-3 py-2 text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400">
              <option value="">All Status</option>
              <option value="active">Active Units</option>
              <option value="maintenance">In Maintenance</option>
              <option value="coding">In Coding</option>
              <option value="at_risk">At Risk</option>
              <option value="retired">Retired</option>
            </select>
            <select value={sort} onChange={e => setSort(e.target.value)}
              className="flex-1 bg-gray-100 rounded-xl px-3 py-2 text-xs font-bold text-gray-700 focus:outline-none focus:ring-2 focus:ring-yellow-400">
              <option value="alphabetical">A-Z (Plate #)</option>
              <option value="newest">Newest Added</option>
              <option value="oldest">Oldest Added</option>
            </select>
          </div>
        )}

        {/* ── Toolbar Buttons (matching web) ── */}
        <div className="flex items-center gap-1.5 mt-3 overflow-x-auto scrollbar-hide pb-1">
          {/* Table / Cards toggle */}
          <div className="flex items-center bg-gray-100 rounded-xl p-1 border border-gray-200 flex-shrink-0">
            <button onClick={() => setViewMode("table")}
              className={`flex items-center gap-1 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all ${viewMode==="table" ? "bg-white shadow text-yellow-700" : "text-gray-500"}`}>
              <List className="w-3 h-3" />Table
            </button>
            <button onClick={() => setViewMode("cards")}
              className={`flex items-center gap-1 px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all ${viewMode==="cards" ? "bg-white shadow text-yellow-700" : "text-gray-500"}`}>
              <Grid3X3 className="w-3 h-3" />Cards
            </button>
          </div>
          {/* Flagged Units */}
          <button onClick={() => setShowFlagged(true)}
            className="flex items-center gap-1 px-3 py-2 bg-red-600 text-white rounded-xl text-[10px] font-black flex-shrink-0 active:bg-red-700">
            <Flag className="w-3 h-3" />Flagged
          </button>
          {/* Print to PDF */}
          <button onClick={printToPDF}
            className="flex items-center gap-1 px-3 py-2 bg-blue-600 text-white rounded-xl text-[10px] font-black flex-shrink-0 active:bg-blue-700">
            <Printer className="w-3 h-3" />Print PDF
          </button>
          {/* Add Unit */}
          <button onClick={() => setShowAddUnit(true)}
            className="flex items-center gap-1 px-3 py-2 bg-green-600 text-white rounded-xl text-[10px] font-black flex-shrink-0 active:bg-green-700">
            <Plus className="w-3 h-3" />Add Unit
          </button>
        </div>
      </div>

      {/* ── Quick Stats Bar ── */}
      <div className="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 px-4 py-3 flex items-center gap-2 overflow-x-auto scrollbar-hide">
        {[
          { dot:"bg-white animate-pulse",                                                     text:"text-gray-300",  val:stats.total,    label:"Total"  },
          { dot:"bg-green-400 animate-pulse shadow-[0_0_6px_rgba(74,222,128,0.8)]",          text:"text-green-300", val:stats.on_road,  label:"Active" },
          { dot:"bg-orange-400 animate-pulse shadow-[0_0_6px_rgba(251,146,60,0.8)]",         text:"text-orange-300",val:stats.garage,   label:"At Risk"},
          { dot:"bg-yellow-400 animate-pulse shadow-[0_0_6px_rgba(250,204,21,0.8)]",         text:"text-yellow-300",val:stats.workshop, label:"Maint." },
          { dot:"bg-red-400 animate-pulse shadow-[0_0_6px_rgba(248,113,113,0.8)]",           text:"text-red-300",   val:stats.coding,   label:"Coding" },
        ].map((s, i) => (
          <div key={i} className="flex items-center gap-1.5 flex-shrink-0">
            {i > 0 && <span className="text-gray-600">·</span>}
            <div className={`flex items-center gap-1.5 px-3 py-1 rounded-full ${i===0?"bg-white/10":i===1?"bg-green-500/20":i===2?"bg-orange-500/20":i===3?"bg-yellow-500/20":"bg-red-500/20"}`}>
              <span className={`w-2 h-2 rounded-full ${s.dot}`} />
              <span className={`text-[10px] font-bold ${s.text}`}>{s.label}</span>
              <span className={`text-sm font-black tabular-nums ${s.text.replace("300","400")}`}>{s.val}</span>
            </div>
          </div>
        ))}
        <div className="ml-auto flex-shrink-0"><span className="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_6px_rgba(74,222,128,0.8)] animate-pulse block" /></div>
      </div>

      {/* ── Unit List ── */}
      {isLoading ? (
        <div className="flex flex-col items-center justify-center flex-1 py-20 gap-3">
          <Loader2 className="h-8 w-8 animate-spin text-yellow-500" />
          <p className="text-sm text-gray-500 font-medium">Loading units...</p>
        </div>
      ) : apiError ? (
        <div className="flex flex-col items-center justify-center flex-1 py-20 gap-3 text-center px-8">
          <div className="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mb-2">
            <span className="text-2xl">⚠️</span>
          </div>
          <p className="text-base font-black text-gray-800">Failed to Load Units</p>
          <p className="text-xs text-red-600 font-medium bg-red-50 border border-red-200 rounded-xl px-4 py-3 max-w-xs break-words">{apiError}</p>
          <button onClick={fetchUnits} className="mt-2 px-6 py-2.5 bg-yellow-400 text-gray-900 font-black text-sm rounded-xl active:bg-yellow-500">
            Try Again
          </button>
        </div>
      ) : units.length === 0 ? (
        <div className="flex flex-col items-center justify-center flex-1 py-20 gap-3 text-center px-8">
          <Car className="w-16 h-16 text-gray-200" />
          <p className="text-base font-black text-gray-800">No units found</p>
          <p className="text-sm text-gray-400 italic">Try adjusting your search criteria.</p>
        </div>
      ) : viewMode === "table" ? (
        /* ── TABLE VIEW ── */
        <div className="flex-1 divide-y divide-gray-100 bg-white">
          {/* Column Headers (matching web exactly) */}
          <div className="grid grid-cols-[1.5fr_1.5fr_1.5fr_1fr_1.2fr_0.5fr] px-4 py-3 bg-gray-50 border-b border-gray-200">
            <p className="text-[9px] font-black text-gray-400 uppercase tracking-widest">Plate Number Info</p>
            <p className="text-[9px] font-black text-gray-400 uppercase tracking-widest">Vehicle Details</p>
            <p className="text-[9px] font-black text-gray-400 uppercase tracking-widest">Assigned Drivers</p>
            <p className="text-[9px] font-black text-gray-400 uppercase tracking-widest text-center">Status</p>
            <p className="text-[9px] font-black text-gray-400 uppercase tracking-widest text-right">Boundary Rate</p>
            <p className="text-[9px] font-black text-gray-400 uppercase tracking-widest text-right">Actions</p>
          </div>
          {units.map(u => (
            <div key={u.id} className="relative group">
              <div className="px-4 py-4 hover:bg-yellow-50/30 transition-all border-l-4 border-transparent hover:border-yellow-400">
                <div className="grid grid-cols-[1.5fr_1.5fr_1.5fr_1fr_1.2fr_0.5fr] gap-2 items-center">
                  {/* Plate Info */}
                  <div onClick={() => navigate(`/units/${u.id}`)} className="cursor-pointer">
                    <p className="text-sm font-black text-gray-900 tracking-tight leading-none mb-1.5">{u.plate_number}</p>
                    <p className="text-[8px] font-bold text-gray-400 uppercase leading-tight">M: {u.motor_no}</p>
                    <p className="text-[8px] font-bold text-gray-400 uppercase leading-tight">C: {u.chassis_no}</p>
                  </div>
                  {/* Vehicle Details */}
                  <div onClick={() => navigate(`/units/${u.id}`)} className="cursor-pointer">
                    <p className="text-xs font-black text-gray-900 leading-none mb-1">{u.make} {u.model}</p>
                    <div className="flex items-center gap-1.5">
                      <span className="text-[9px] text-gray-400 font-bold">{u.year}</span>
                      <span className="px-1.5 py-0.5 bg-blue-50 text-blue-600 text-[8px] font-black rounded uppercase tracking-tighter">NEW</span>
                    </div>
                  </div>
                  {/* Assigned Drivers */}
                  <div onClick={() => navigate(`/units/${u.id}`)} className="cursor-pointer">
                    <p className="text-[9px] text-gray-500 mb-1 leading-none"><span className="font-black text-gray-400 uppercase text-[8px]">D1:</span> {u.primary_driver || <span className="italic text-gray-300">No D1</span>}</p>
                    <p className="text-[9px] text-gray-900 font-bold leading-none"><span className="font-black text-gray-400 uppercase text-[8px]">D2:</span> {u.secondary_driver || <span className="italic text-gray-300">No D2</span>}</p>
                  </div>
                  {/* Status */}
                  <div className="text-center" onClick={() => navigate(`/units/${u.id}`)}>
                    <StatusDot status={u.status} />
                  </div>
                  {/* Boundary Rate */}
                  <div className="text-right" onClick={() => navigate(`/units/${u.id}`)}>
                    <p className="text-sm font-black text-gray-900 leading-none mb-1.5">{fmtRate(u.boundary_rate)}</p>
                    <span className="px-2 py-0.5 bg-blue-600 text-white text-[8px] font-black uppercase rounded tracking-widest shadow-sm">SUNDAY DISCOUNT</span>
                  </div>
                  {/* Actions */}
                  <div className="text-right relative">
                    <button 
                      onClick={(e) => { e.stopPropagation(); setActiveMenu(activeMenu === u.id ? null : u.id); }}
                      className="p-1.5 hover:bg-gray-100 rounded-lg text-gray-400 hover:text-gray-900 transition-colors"
                    >
                      <MoreVertical className="w-4 h-4" />
                    </button>
                    {activeMenu === u.id && (
                      <div className="absolute right-0 top-full mt-1 w-44 bg-white border border-gray-100 rounded-2xl shadow-2xl z-[100] py-2 animate-in fade-in zoom-in duration-200">
                        <button 
                          onClick={(e) => { e.stopPropagation(); setEditingUnit(u); setActiveMenu(null); }}
                          className="w-full px-4 py-2 text-left flex items-center gap-2 hover:bg-gray-50 text-xs font-black text-gray-700 uppercase tracking-widest transition-colors"
                        >
                          <Edit2 className="w-3 h-3 text-yellow-500" /> Edit Unit
                        </button>
                        <button 
                          onClick={(e) => { e.stopPropagation(); archiveUnit(u); }}
                          className="w-full px-4 py-2 text-left flex items-center gap-2 hover:bg-red-50 text-xs font-black text-red-600 uppercase tracking-widest transition-colors"
                        >
                          <Trash2 className="w-3 h-3" /> Archive Unit
                        </button>
                      </div>
                    )}
                  </div>
                </div>
                <HealthBar unit={u} />
              </div>
            </div>
          ))}
        </div>
      ) : (
        /* ── CARDS VIEW — matches eurotaxisystem.site/units cards layout ── */
        <div className="flex-1 p-3 grid grid-cols-2 gap-3 bg-gray-100">
          {units.map(u => {
            const hasGps = u.gps_device_count > 0 || !!u.imei;
            const SERVICE_KM = 5000;
            const kmSince = Math.max(0, (u.current_gps_odo||0) - (u.last_service_odo_gps||0));
            const pct = Math.min(100, Math.round((kmSince / SERVICE_KM) * 100));
            const isOverdue = kmSince >= SERVICE_KM;
            let barColor = "bg-green-500", barText = "text-green-600", barLabel = "Good";
            if (isOverdue)    { barColor = "bg-red-600";    barText = "text-red-600";    barLabel = "SERVICE OVERDUE"; }
            else if (pct>=85) { barColor = "bg-orange-500"; barText = "text-orange-600"; barLabel = "Service Due Soon"; }
            else if (pct>=60) { barColor = "bg-yellow-400"; barText = "text-yellow-600"; barLabel = "Maintenance Progress"; }

            const s = u.status?.toLowerCase();
            const statusCfg: any = {
              active:      { dot:"bg-green-500 animate-pulse shadow-[0_0_5px_rgba(34,197,94,0.7)]",  label:"Active",      txt:"text-green-600" },
              maintenance: { dot:"bg-red-500 shadow-[0_0_4px_rgba(239,68,68,0.6)]",                  label:"Maintenance", txt:"text-red-600"   },
              coding:      { dot:"bg-yellow-400 animate-[blink_1.1s_step-start_infinite]",            label:"Coding",      txt:"text-yellow-600"},
              at_risk:     { dot:"bg-orange-500 animate-pulse shadow-[0_0_5px_rgba(249,115,22,0.7)]",label:"At Risk",     txt:"text-orange-600"},
            };
            const sc = statusCfg[s] || { dot:"bg-gray-400", label: u.status || "Unknown", txt:"text-gray-500" };

            return (
              <div key={u.id} onClick={() => navigate(`/units/${u.id}`)}
                className="bg-white rounded-2xl shadow overflow-hidden cursor-pointer active:opacity-90 transition-all flex flex-col">

                {/* ── Card Header: Plate + Status (matching web dark header) ── */}
                <div className="bg-gray-900 px-3 py-2 flex items-center justify-between">
                  <span className="text-white font-black text-xs tracking-wider">{u.plate_number}</span>
                  <span className={`flex items-center gap-1 text-[9px] font-black uppercase ${sc.txt}`}>
                    <span className={`w-1.5 h-1.5 rounded-full ${sc.dot}`} />
                    {sc.label}
                  </span>
                </div>

                {/* ── Car Icon + Vehicle Details ── */}
                <div className="flex items-center gap-2 px-3 py-3 border-b border-gray-100">
                  <div className="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <Car className="w-7 h-7 text-red-400" />
                  </div>
                  <div className="min-w-0">
                    <p className="text-sm font-black text-gray-900 leading-tight">{u.make}</p>
                    <p className="text-sm font-black text-gray-900 leading-tight">{u.model}</p>
                    <div className="flex items-center gap-1 mt-0.5">
                      <span className="text-[9px] text-gray-400 font-bold">{u.year}</span>
                      <span className="text-gray-300">•</span>
                      <span className="px-1.5 py-0.5 bg-gray-900 text-white text-[8px] font-black rounded uppercase">
                        {u.unit_type === 'new' || (u.boundary_rate > 1000) ? 'NEW' : 'OLD'}
                      </span>
                    </div>
                  </div>
                </div>

                {/* ── Boundary Rate ── */}
                <div className="px-3 py-2 border-b border-gray-100 flex items-center gap-1.5">
                  <span className="text-green-600">💳</span>
                  <span className="text-sm font-black text-gray-900">{fmtRate(u.boundary_rate)}</span>
                </div>

                {/* ── Primary Driver ── */}
                <div className="px-3 py-2 border-b border-gray-100">
                  <p className="text-[8px] font-black text-gray-400 uppercase tracking-wider mb-1">Primary Driver</p>
                  <div className="flex items-center gap-1.5">
                    <div className="w-5 h-5 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                      <span className="text-[9px] text-gray-400">👤</span>
                    </div>
                    <p className={`text-xs font-bold truncate ${u.primary_driver ? "text-gray-900" : "text-gray-300 italic"}`}>
                      {u.primary_driver || "Unassigned"}
                    </p>
                  </div>
                </div>
 
                {/* ── Maintenance Health Bar (GPS units only) ── */}
                {hasGps && (
                  <div className="px-3 py-2 border-b border-gray-100">
                    <div className="flex justify-between items-center mb-1">
                      <span className={`flex items-center gap-1 text-[8px] font-black uppercase tracking-wide ${barText}`}>
                        {isOverdue && <span>⚠</span>}
                        {barLabel}
                      </span>
                      <span className="text-[8px] text-gray-400 font-bold tabular-nums">
                        {Number(kmSince).toLocaleString()} / {Number(SERVICE_KM).toLocaleString()} KM
                      </span>
                    </div>
                    <div className="relative h-1.5 bg-gray-100 rounded-full overflow-hidden">
                      <div className={`absolute inset-y-0 left-0 ${barColor} ${isOverdue?"animate-pulse":""} rounded-full`}
                        style={{ width:`${pct}%` }} />
                    </div>
                    {isOverdue && (
                      <p className="text-[8px] text-red-500 mt-1 italic leading-tight">
                        Exceeded by {Number(kmSince - SERVICE_KM).toLocaleString()}km.
                      </p>
                    )}
                  </div>
                )}

                {/* ── Serial Info Footer + Actions ── */}
                <div className="px-3 py-2 bg-gray-50 mt-auto border-t border-gray-100 flex items-center justify-between">
                  <div className="min-w-0">
                    <p className="text-[8px] font-black text-gray-400 uppercase tracking-wider mb-0.5">Serial Info</p>
                    <p className="text-[8px] text-gray-500 truncate">M: {u.motor_no || "N/A"}</p>
                    <p className="text-[8px] text-gray-500 truncate">C: {u.chassis_no || "N/A"}</p>
                  </div>
                  
                  <div className="relative">
                    <button 
                      onClick={(e) => { e.stopPropagation(); setActiveMenu(activeMenu === u.id ? null : u.id); }}
                      className="p-1.5 hover:bg-gray-200 rounded-lg text-gray-400 hover:text-gray-900 transition-colors"
                    >
                      <MoreVertical className="w-4 h-4" />
                    </button>
                    {activeMenu === u.id && (
                      <div className="absolute right-0 bottom-full mb-1 w-44 bg-white border border-gray-100 rounded-2xl shadow-2xl z-[100] py-2 animate-in fade-in slide-in-from-bottom-2 duration-200">
                        <button 
                          onClick={(e) => { e.stopPropagation(); setEditingUnit(u); setActiveMenu(null); }}
                          className="w-full px-4 py-2 text-left flex items-center gap-2 hover:bg-gray-50 text-xs font-black text-gray-700 uppercase tracking-widest transition-colors"
                        >
                          <Edit2 className="w-3 h-3 text-yellow-500" /> Edit Unit
                        </button>
                        <button 
                          onClick={(e) => { e.stopPropagation(); archiveUnit(u); }}
                          className="w-full px-4 py-2 text-left flex items-center gap-2 hover:bg-red-50 text-xs font-black text-red-600 uppercase tracking-widest transition-colors"
                        >
                          <Trash2 className="w-3 h-3 text-red-500" /> Archive Unit
                        </button>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* ── Modals ── */}
      {showFlagged && <FlaggedModal units={units} onClose={() => setShowFlagged(false)} />}
      {showAddUnit && <AddUnitModal onClose={() => setShowAddUnit(false)} onAdded={fetchUnits} />}
      {editingUnit && <EditUnitModal unit={editingUnit} onClose={() => setEditingUnit(null)} onUpdated={fetchUnits} />}
    </div>
  );
}
