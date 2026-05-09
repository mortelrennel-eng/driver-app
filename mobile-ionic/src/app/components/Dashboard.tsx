import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { Car, Users, TrendingUp, Wrench, DollarSign, Calendar, Activity, BarChart3, X, ChevronRight, Loader2, RefreshCw, Crown, PieChart as PieChartIcon, LineChart as LineChartIcon, Code, Search, XCircle, CheckCircle, AlertCircle } from "lucide-react";
import { LineChart, Line, BarChart, Bar, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, AreaChart, Area, ReferenceLine, LabelList } from "recharts";
import api from "../services/api";
import { toast } from "sonner";
import dayjs from "dayjs";

const COLORS = ["#3b82f6","#10b981","#f59e0b","#ef4444","#8b5cf6","#06b6d4"];
const fmt = (n: any) => "₱" + Number(n||0).toLocaleString("en-PH",{minimumFractionDigits:2,maximumFractionDigits:2});

function Modal({title,color,onClose,children}:{title:string;color:string;onClose:()=>void;children:any}) {
  return (
    <div className="fixed inset-0 bg-black/60 z-50 flex items-end justify-center" onClick={onClose}>
      <div className="bg-white w-full max-h-[85vh] rounded-t-3xl overflow-hidden flex flex-col" onClick={e=>e.stopPropagation()}>
        <div className={`p-4 ${color} flex items-center justify-between flex-shrink-0`}>
          <span className="text-white font-bold text-base">{title}</span>
          <button onClick={onClose}><X className="w-5 h-5 text-white"/></button>
        </div>
        <div className="overflow-y-auto flex-1 p-4">{children}</div>
      </div>
    </div>
  );
}

export function Dashboard() {
  const navigate = useNavigate();
  const [data, setData] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [activeModal, setActiveModal] = useState<string|null>(null);
  const [days, setDays] = useState(7);
  const [error, setError] = useState<string|null>(null);

  useEffect(()=>{ 
    load(); 
    // Self-healing check: Ensure push notifications register on dashboard load
    import("../services/pushNotifications").then(({ initPushNotifications }) => {
      initPushNotifications().catch(err => {
        console.error("Dashboard push auto-init error:", err);
      });
    });
  },[]);
  useEffect(()=>{ if(data) loadTrend(); },[days]);

  const load = async () => {
    setLoading(true); setError(null);
    try {
      const r = await api.get("/dashboard?days="+days);
      if(r.data.success) setData(r.data);
      else setError("Server returned error");
    } catch(e:any) {
      setError(e?.response?.data?.message || e?.message || "Network error");
    } finally { setLoading(false); }
  };

  const loadTrend = async () => {
    try {
      const r = await api.get("/dashboard?days="+days);
      if(r.data.success) setData((p:any)=>({...p, chartData: {...p.chartData, revenueTrend: r.data.chartData?.revenueTrend||[]}}));
    } catch{}
  };

  if(loading) return (
    <div className="flex flex-col items-center justify-center min-h-[60vh] gap-3">
      <Loader2 className="w-8 h-8 animate-spin text-blue-600"/>
      <p className="text-gray-500 text-sm italic font-medium tracking-tight">Accessing fleet data...</p>
    </div>
  );

  if(error) return (
    <div className="flex flex-col items-center justify-center min-h-[60vh] gap-4 p-6">
      <div className="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
        <X className="w-8 h-8 text-red-500"/>
      </div>
      <p className="text-gray-700 font-bold text-center">Failed to load dashboard</p>
      <p className="text-red-500 text-sm text-center bg-red-50 p-3 rounded-xl border border-red-100">{error}</p>
      <button onClick={load} className="flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-xl font-bold">
        <RefreshCw className="w-4 h-4"/> Retry
      </button>
    </div>
  );

  const stats = data?.stats;
  const charts = data?.chartData;
  const insights = data?.insights;
  const modal = data?.modalData;

  const cards = [
    {id:"units",    label:"Total Units",       val:stats?.active_units??0,             sub:`${stats?.roi_achieved??0} ROI Achieved`, icon:Car,      c:"text-blue-500", bg:"bg-blue-50", bd:"border-blue-100", wave:"rgba(59, 130, 246, 0.05)"},
    {id:"boundary", label:"Boundary Revenue",  val:fmt(stats?.today_boundary),          sub:`${fmt(stats?.month_boundary)} this month`, icon:DollarSign, c:"text-emerald-500", bg:"bg-emerald-50", bd:"border-emerald-100", wave:"rgba(16, 185, 129, 0.05)"},
    {id:"income",   label:"Net Income (Kita)",  val:fmt(stats?.net_income),              sub:`${fmt(stats?.net_income_month)} this month`, icon:TrendingUp, c:"text-green-600", bg:"bg-green-50", bd:"border-green-200", wave:"rgba(34, 197, 94, 0.05)"},
    {id:"maintenance",label:"Units Under Mntnc",val:stats?.maintenance_units??0,         sub:"Ongoing maintenance",   icon:Wrench,    c:"text-orange-500", bg:"bg-orange-50", bd:"border-orange-100", wave:"rgba(249, 115, 22, 0.05)"},
    {id:"drivers",  label:"Active Drivers",    val:stats?.active_drivers??0,            sub:"Registered drivers",    icon:Users,     c:"text-indigo-500", bg:"bg-indigo-50", bd:"border-indigo-100", wave:"rgba(99, 102, 241, 0.05)"},
    {id:"expenses", label:"Total Expenses",    val:fmt(stats?.today_expenses),          sub:"Today total",           icon:Activity,  c:"text-rose-500", bg:"bg-rose-50", bd:"border-rose-100", wave:"rgba(244, 63, 94, 0.05)"},
    {id:"coding",   label:"Coding Units Today", val:stats?.coding_units??0,             sub:new Date().toLocaleDateString("en-PH",{weekday:"long"}), icon:Calendar, c:"text-violet-500", bg:"bg-violet-50", bd:"border-violet-100", wave:"rgba(139, 92, 246, 0.05)"},
  ];

  return (
    <div className="space-y-4 pb-20 p-2">
      {/* Header matching web dashboard precisely but more compact */}
      <div className="flex flex-col gap-0.5">
        <div className="flex items-center justify-between">
          <h1 className="text-xl font-black text-gray-900 tracking-tight">Euro Taxi System</h1>
          <button onClick={load} className="p-2 bg-gray-100 rounded-xl active:bg-gray-200 transition-colors">
            <RefreshCw className="w-4 h-4 text-gray-500"/>
          </button>
        </div>
        <div className="flex items-center gap-2">
           <div className="w-1 h-1 bg-green-500 rounded-full animate-pulse"></div>
           <p className="text-[8px] font-bold text-gray-400 uppercase tracking-widest">Live • {new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })}</p>
        </div>
      </div>

      {/* 1. STATS CARDS (Matching Web Design + User Arrangement) */}
      <div className="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        {[
          { 
            id: 'units', 
            label: "Total Units", 
            val: stats?.active_units, 
            sub: `${stats?.roi_achieved} ROI Achieved`, 
            icon: Car, 
            bg: "bg-blue-50/50", 
            bd: "border-blue-100", 
            c: "text-blue-600",
            iconBg: "bg-blue-100/50"
          },
          { 
            id: 'boundary', 
            label: "Boundary Revenue", 
            val: fmt(stats?.today_boundary), 
            sub: "TODAY", 
            sub2: `${fmt(stats?.month_boundary)} THIS MONTH`,
            icon: DollarSign, 
            bg: "bg-emerald-50/50", 
            bd: "border-emerald-100", 
            c: "text-emerald-600",
            iconBg: "bg-emerald-100/50"
          },
          { 
            id: 'income', 
            label: "Net Income (Kita)", 
            val: fmt(stats?.net_income), 
            sub: "TODAY", 
            sub2: `${fmt(stats?.net_income_month)} THIS MONTH`,
            icon: TrendingUp, 
            bg: "bg-green-50/50", 
            bd: "border-green-100", 
            c: "text-green-600",
            iconBg: "bg-green-100/50"
          },
          { 
            id: 'maintenance', 
            label: "Units Under Mntnc", 
            val: stats?.maintenance_units, 
            sub: "Ongoing Maintenance", 
            icon: Wrench, 
            bg: "bg-orange-50/50", 
            bd: "border-orange-100", 
            c: "text-orange-600",
            iconBg: "bg-orange-100/50"
          },
        ].map((s) => (
          <div key={s.id} onClick={()=>setActiveModal(s.id)}
            className={`group relative overflow-hidden rounded-[1.5rem] ${s.bg} border ${s.bd} p-3 active:scale-[0.98] transition-all cursor-pointer shadow-sm`}>
            {/* Background Illustration */}
            <div className="absolute bottom-0 right-0 left-0 h-12 opacity-[0.05] pointer-events-none">
               <svg viewBox="0 0 100 20" className="w-full h-full preserve-3d">
                  <path d="M0 10 Q 25 20 50 10 T 100 10 V 20 H 0 Z" fill="currentColor" className={s.c}/>
               </svg>
            </div>
            
            <div className="flex justify-between items-start relative z-10 mb-2">
               <div className={`p-2 ${s.iconBg} rounded-xl shadow-sm`}>
                  <s.icon className={`w-4 h-4 ${s.c}`}/>
               </div>
               <ChevronRight className="w-3 h-3 text-gray-300"/>
            </div>
            
            <div className="relative z-10">
               <p className="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1 truncate">{s.label}</p>
               <p className="text-lg font-black text-gray-900 tracking-tighter leading-none">{s.val}</p>
               <p className="text-[7px] font-bold text-gray-500 mt-1 uppercase tracking-tight truncate">{s.sub}</p>
            </div>
            
            {s.sub2 && (
               <div className="mt-2 pt-2 border-t border-black/5 relative z-10">
                  <p className="text-[9px] font-black text-gray-900 tracking-tight truncate">{s.sub2.split(' ')[0]}</p>
                  <p className="text-[7px] font-black text-gray-400 uppercase tracking-tighter truncate">{s.sub2.split(' ').slice(1).join(' ')}</p>
               </div>
            )}
          </div>
        ))}
      </div>

      <div className="grid grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
        {[
          { 
            id: 'drivers', 
            label: "Active Drivers", 
            val: stats?.active_drivers, 
            sub: "Registered", 
            icon: Users, 
            bg: "bg-indigo-50/50", 
            bd: "border-indigo-100", 
            c: "text-indigo-600",
            iconBg: "bg-indigo-100/50",
            span: "col-span-1"
          },
          { 
            id: 'expenses', 
            label: "Total Expenses Today", 
            val: fmt(stats?.today_expenses), 
            sub: "Today", 
            icon: Activity, 
            bg: "bg-rose-50/50", 
            bd: "border-rose-100", 
            c: "text-rose-600",
            iconBg: "bg-rose-100/50",
            span: "col-span-1"
          },
          { 
            id: 'coding', 
            label: "Coding Units Today", 
            val: stats?.coding_units, 
            sub: dayjs().format('dddd'), 
            icon: Calendar, 
            bg: "bg-purple-50/50", 
            bd: "border-purple-100", 
            c: "text-purple-600",
            iconBg: "bg-purple-100/50",
            span: "col-span-2 lg:col-span-1"
          },
        ].map((s) => (
          <div key={s.id} onClick={()=>setActiveModal(s.id)}
            className={`${s.span} group relative overflow-hidden rounded-[1.25rem] ${s.bg} border ${s.bd} p-3 active:scale-[0.98] transition-all cursor-pointer shadow-sm`}>
            <div className="flex items-center gap-2 mb-2 relative z-10">
               <div className={`p-1.5 ${s.iconBg} rounded-lg shadow-sm`}>
                  <s.icon className={`w-3 h-3 ${s.c}`}/>
               </div>
               <p className="text-[7px] font-black text-gray-500 uppercase tracking-tighter truncate">{s.label.split(' ')[0]}</p>
            </div>
            <div className="relative z-10">
               <p className="text-xs font-black text-gray-900 tracking-tighter leading-none">{s.val}</p>
               <p className="text-[6px] font-bold text-gray-400 mt-1 uppercase tracking-tight truncate">{s.sub}</p>
            </div>
          </div>
        ))}
      </div>
      {/* 2. UNIT PERFORMANCE (Matching Web Sidebar Layout) */}
      <div className="bg-white rounded-[2.5rem] border border-gray-100 shadow-2xl overflow-hidden mb-4">
        <div className="p-6 border-b border-gray-50 flex items-center justify-between">
          <div className="flex items-center gap-3">
             <div className="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center">
                <BarChart3 className="w-6 h-6 text-blue-600"/>
             </div>
             <h3 className="font-black text-gray-900 uppercase tracking-tight">Unit Performance</h3>
          </div>
          <span className="text-[10px] font-black text-blue-600 bg-blue-50 px-3 py-1.5 rounded-full uppercase tracking-widest border border-blue-100">Top 10 Performers</span>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-4">
          {/* Main Chart Area */}
          <div className="md:col-span-3 p-4">
            <div className="h-[450px]">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={charts?.unitPerformance||[]} layout="vertical" margin={{ left: -10, right: 35, top: 10, bottom: 0 }}>
                  <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke="#f1f5f9" />
                  <XAxis type="number" hide />
                  <YAxis type="category" dataKey="plate" tick={{fontSize: 9, fontWeight: 900, fill: '#1e293b'}} axisLine={false} tickLine={false} width={80} />
                  <Tooltip 
                    cursor={{fill: '#f8fafc'}}
                    contentStyle={{borderRadius: 16, border: 'none', boxShadow: '0 12px 32px rgba(0,0,0,0.1)'}}
                    formatter={(v:any)=>fmt(v)}
                  />
                  {/* Target Bar (Hollow Amber) */}
                  <Bar dataKey="target" name="Monthly Target" fill="transparent" stroke="#fcd34d" strokeWidth={1.5} radius={[0, 4, 4, 0]} barSize={16}>
                    <LabelList dataKey="target" position="insideRight" style={{fontSize: 8, fontWeight: 900, fill: '#b45309'}} offset={8} formatter={(v:any)=>Math.round(v)} />
                  </Bar>
                  {/* Actual Bar (Solid Blue) */}
                  <Bar dataKey="actual" name="Actual Collection" fill="#3b82f6" radius={[0, 4, 4, 0]} barSize={8}>
                    <LabelList dataKey="actual" position="right" style={{fontSize: 8, fontWeight: 900, fill: '#3b82f6'}} offset={8} formatter={(v:any)=>v > 0 ? v.toFixed(2) : ''} />
                  </Bar>
                </BarChart>
              </ResponsiveContainer>
            </div>
          </div>

          {/* Sidebar Insights (Matching Web) */}
          <div className="bg-gray-50/50 p-6 border-l border-gray-100 flex flex-col gap-8">
            <div>
              <p className="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Fleet Health</p>
              <div className="flex items-end gap-2">
                <p className="text-4xl font-black text-gray-900 leading-none">{insights?.fleetHealth??0}%</p>
                <div className="flex items-center text-green-600 font-bold text-[10px] mb-1">
                   <TrendingUp className="w-3 h-3 mr-0.5"/> +2.4%
                </div>
              </div>
              <p className="text-[11px] text-gray-500 mt-2 font-medium leading-relaxed">
                Most units are meeting over 80% of their monthly boundary targets.
              </p>
            </div>

            <div className="pt-6 border-t border-gray-200">
              <p className="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-2">Top Performer</p>
              <p className="text-xl font-black text-gray-900">{insights?.topPerformerUnit}</p>
              <p className="text-[11px] text-gray-500 mt-2 font-medium leading-relaxed">
                Consistency in daily collections makes this your most reliable asset.
              </p>
            </div>

            <div className="pt-6 border-t border-gray-200">
              <p className="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-4">Legend</p>
              <div className="space-y-3">
                <div className="flex items-center gap-3">
                   <div className="w-4 h-4 rounded bg-[#3b82f6] shadow-sm"></div>
                   <span className="text-[10px] font-black text-gray-600 uppercase tracking-widest">Actual Collection</span>
                </div>
                <div className="flex items-center gap-3">
                   <div className="w-4 h-4 rounded border-2 border-[#fcd34d] bg-amber-50"></div>
                   <span className="text-[10px] font-black text-gray-600 uppercase tracking-widest">Monthly Target</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* 4. REVENUE TREND */}
      <div className="bg-white rounded-[2rem] border border-gray-100 shadow-xl overflow-hidden mb-4">
        <div className="p-4 border-b border-gray-50 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center">
              <TrendingUp className="w-4 h-4 text-blue-600"/>
            </div>
            <h3 className="font-black text-gray-900 uppercase tracking-tight text-sm">Revenue Trend</h3>
          </div>
          <div className="flex gap-1">
            {[7, 30, 90, 365].map(d => (
              <button key={d} onClick={() => setDays(d)} className={`px-2 py-1 text-[8px] font-black rounded-lg transition-all ${days === d ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-50 text-gray-400'}`}>
                {d === 365 ? '1 YEAR' : d === 90 ? '3 MOS' : `${d} DAYS`}
              </button>
            ))}
          </div>
        </div>
        <div className="p-4 h-52">
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={charts?.revenueTrend||[]} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
              <defs>
                <linearGradient id="colorRev" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#3b82f6" stopOpacity={0.2}/>
                  <stop offset="95%" stopColor="#3b82f6" stopOpacity={0}/>
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9"/>
              <XAxis dataKey="date" tick={{fontSize:8, fontWeight:700, fill:'#94a3b8'}} axisLine={false} tickLine={false}/>
              <YAxis tick={{fontSize:8, fontWeight:700, fill:'#94a3b8'}} axisLine={false} tickLine={false} tickFormatter={(v)=>v >= 1000 ? `${v/1000}k` : v}/>
              <Tooltip 
                contentStyle={{borderRadius: 16, border: 'none', boxShadow: '0 12px 32px rgba(0,0,0,0.1)'}}
                formatter={(v:any)=>fmt(v)}
              />
              <Area type="monotone" dataKey="revenue" stroke="#3b82f6" strokeWidth={3} fillOpacity={1} fill="url(#colorRev)" />
            </AreaChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* 5. EXPENSE BREAKDOWN & WEEKLY OVERVIEW */}
      <div className="grid grid-cols-1 gap-4 mb-4">
          <div className="bg-white rounded-[2.5rem] border border-gray-100 shadow-lg p-6">
            <div className="flex items-center gap-3 mb-6">
               <div className="w-10 h-10 bg-rose-50 rounded-2xl flex items-center justify-center">
                  <PieChartIcon className="w-5 h-5 text-rose-500"/>
               </div>
               <h3 className="font-black text-gray-900 uppercase tracking-tight">Expense Distribution</h3>
            </div>
            <div className="h-48">
               <ResponsiveContainer width="100%" height="100%">
                  <PieChart>
                    <Pie data={charts?.expenseBreakdown||[]} cx="35%" cy="50%" innerRadius={0} outerRadius={60} dataKey="value" stroke="#fff" strokeWidth={2}>
                      {(charts?.expenseBreakdown||[]).map((_:any,i:number)=><Cell key={i} fill={['#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#ec4899'][i % 5]}/>)}
                    </Pie>
                    <Tooltip contentStyle={{borderRadius: 12, border: 'none', boxShadow: '0 8px 24px rgba(0,0,0,0.1)'}}/>
                    <Legend layout="vertical" align="right" verticalAlign="middle" iconType="circle" wrapperStyle={{fontSize: 9, fontWeight: 700}} />
                  </PieChart>
               </ResponsiveContainer>
            </div>
          </div>

          <div className="bg-white rounded-[2rem] border border-gray-100 shadow-xl p-4">
            <div className="flex items-center justify-between mb-6">
              <div className="flex items-center gap-2">
                <div className="w-8 h-8 bg-indigo-50 rounded-xl flex items-center justify-center">
                  <LineChartIcon className="w-4 h-4 text-indigo-600"/>
                </div>
                <h3 className="font-black text-gray-900 uppercase tracking-tight text-sm">Weekly Overview</h3>
              </div>
            </div>
            <div className="p-4 h-64">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={charts?.weeklyData||[]} margin={{ top: 25, right: 30, left: -10, bottom: 0 }}>
                  <defs>
                    <linearGradient id="colorBoundary" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#eab308" stopOpacity={0.3}/>
                      <stop offset="95%" stopColor="#eab308" stopOpacity={0}/>
                    </linearGradient>
                    <linearGradient id="colorExpenses" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#ef4444" stopOpacity={0.3}/>
                      <stop offset="95%" stopColor="#ef4444" stopOpacity={0}/>
                    </linearGradient>
                    <linearGradient id="colorNet" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#22c55e" stopOpacity={0.3}/>
                      <stop offset="95%" stopColor="#22c55e" stopOpacity={0}/>
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9"/>
                  <XAxis dataKey="day" tick={{fontSize:9, fontWeight:700, fill:'#94a3b8'}} axisLine={false} tickLine={false}/>
                  <YAxis tick={{fontSize:9, fontWeight:700, fill:'#94a3b8'}} axisLine={false} tickLine={false} tickFormatter={(v)=>v !== 0 ? (Math.abs(v) >= 1000000 ? `${v/1000000}M` : `${v/1000}k`) : '0'}/>
                  <Tooltip 
                    contentStyle={{borderRadius: 16, border: 'none', boxShadow: '0 12px 32px rgba(0,0,0,0.1)', padding: '10px'}}
                  />
                  <Legend verticalAlign="top" align="center" iconType="circle" wrapperStyle={{fontSize: 10, fontWeight: 700, paddingBottom: 20}} />
                  
                  <Area type="monotone" dataKey="boundary" name="Boundary" stroke="#eab308" strokeWidth={3} fillOpacity={1} fill="url(#colorBoundary)" dot={{ r: 4, fill: '#eab308' }} activeDot={{ r: 6 }}>
                     <LabelList dataKey="boundary" position="top" offset={10} style={{fontSize: 7, fontWeight: 900, fill: '#854d0e'}} formatter={(v:any)=>v > 0 ? Math.round(v) : ''} />
                  </Area>
                  
                  <Area type="monotone" dataKey="expenses" name="Expenses" stroke="#ef4444" strokeWidth={3} fillOpacity={1} fill="url(#colorExpenses)" dot={{ r: 4, fill: '#ef4444' }} activeDot={{ r: 6 }}>
                     <LabelList dataKey="expenses" position="top" offset={10} style={{fontSize: 7, fontWeight: 900, fill: '#991b1b'}} formatter={(v:any)=>v > 0 ? (v >= 1000000 ? (v/1000000).toFixed(2)+'M' : Math.round(v)) : ''} />
                  </Area>
                  
                  <Area type="monotone" dataKey="net" name="Net Income" stroke="#22c55e" strokeWidth={3} fillOpacity={1} fill="url(#colorNet)" dot={{ r: 4, fill: '#22c55e' }} activeDot={{ r: 6 }}>
                     <LabelList dataKey="net" position="bottom" offset={10} style={{fontSize: 7, fontWeight: 900, fill: '#166534'}} formatter={(v:any)=>v !== 0 ? (Math.abs(v) >= 1000000 ? (v/1000000).toFixed(2)+'M' : Math.round(v)) : ''} />
                  </Area>
                  
                  <ReferenceLine y={0} stroke="#cbd5e1" strokeWidth={2} />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </div>
      </div>

      {/* 6. UNIT STATUS & TOP DRIVERS */}
      <div className="grid grid-cols-1 gap-4 mb-8">
          <div className="bg-white rounded-[2rem] border border-gray-100 shadow-lg p-6">
            <div className="flex items-center gap-3 mb-6">
               <div className="w-10 h-10 bg-emerald-50 rounded-2xl flex items-center justify-center">
                  <Activity className="w-5 h-5 text-emerald-500"/>
               </div>
               <h3 className="font-black text-gray-900 uppercase tracking-tight text-sm">Unit Status Distribution</h3>
            </div>
            <div className="h-56 relative">
               <ResponsiveContainer width="100%" height="100%">
                  <PieChart>
                    <Pie 
                      data={charts?.unitStatusDist||[]} 
                      cx="50%" cy="50%" 
                      innerRadius={55} 
                      outerRadius={75} 
                      paddingAngle={4} 
                      dataKey="value"
                      stroke="none"
                    >
                      {(charts?.unitStatusDist||[]).map((_:any,i:number)=><Cell key={i} fill={['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#64748b'][i % 5]}/>)}
                    </Pie>
                    <Tooltip contentStyle={{borderRadius: 12, border: 'none', boxShadow: '0 8px 24px rgba(0,0,0,0.1)'}}/>
                    <Legend layout="vertical" align="right" verticalAlign="middle" iconType="circle" wrapperStyle={{fontSize: 9, fontWeight: 700}} />
                  </PieChart>
               </ResponsiveContainer>
                <div className="absolute top-1/2 left-[35%] -translate-y-1/2 -translate-x-1/2 text-center pointer-events-none">
                  <p className="text-xl font-black text-gray-900 leading-none">{stats?.active_units}</p>
                  <p className="text-[8px] font-black text-gray-400 uppercase tracking-widest">Total Units</p>
                </div>
            </div>
          </div>

          <div className="bg-white rounded-[2rem] border border-gray-100 shadow-xl overflow-hidden">
             <div className="p-4 border-b border-gray-50 flex items-center justify-between">
                <div className="flex items-center gap-2">
                   <div className="w-8 h-8 bg-amber-50 rounded-xl flex items-center justify-center">
                      <Users className="w-4 h-4 text-amber-600"/>
                   </div>
                   <h3 className="font-black text-gray-900 uppercase tracking-tight text-sm">Top Performing Drivers</h3>
                </div>
             </div>
              <div className="p-4 h-[350px]">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart data={charts?.topDrivers||[]} layout="vertical" margin={{ left: -10, right: 35, top: 0, bottom: 0 }}>
                    <CartesianGrid strokeDasharray="3 3" horizontal={false} stroke="#f1f5f9" />
                    <XAxis type="number" hide />
                    <YAxis type="category" dataKey="name" tick={{fontSize: 9, fontWeight: 700, fill: '#64748b'}} axisLine={false} tickLine={false} width={100} />
                    <Tooltip cursor={{fill: '#f8fafc'}} />
                    <Bar dataKey="score" radius={[0, 4, 4, 0]} barSize={16}>
                      {(charts?.topDrivers||[]).map((_: any, index: number) => (
                        <Cell key={`cell-${index}`} fill={['#3b82f6', '#8b5cf6', '#0d9488', '#64748b', '#ec4899'][index % 5]} />
                      ))}
                      <LabelList dataKey="score" position="right" style={{fontSize: 10, fontWeight: 900, fill: '#1e293b'}} offset={8} />
                    </Bar>
                  </BarChart>
                </ResponsiveContainer>
              </div>
          </div>

      </div>

      {/* MODALS - Minimal Update needed to match new theme */}
      {activeModal==="units" && <Modal title="Units Overview" color="bg-indigo-600" onClose={()=>setActiveModal(null)}><FleetModal stats={stats} modal={modal} navigate={navigate} /></Modal>}
      {activeModal==="boundary" && <Modal title="Daily Boundary Collections" color="bg-emerald-600" onClose={()=>setActiveModal(null)}><BoundaryModal stats={stats} modal={modal} navigate={navigate} /></Modal>}
      {activeModal==="income" && <Modal title="Net Income Details" color="bg-emerald-600" onClose={()=>setActiveModal(null)}><IncomeModal stats={stats} modal={modal} /></Modal>}
      {activeModal==="maintenance" && <Modal title="Units Under Maintenance" color="bg-orange-500" onClose={()=>setActiveModal(null)}><MaintenanceModal modal={modal} /></Modal>}
      {activeModal==="drivers" && <Modal title="Active Drivers" color="bg-indigo-600" onClose={()=>setActiveModal(null)}><DriversModal modal={modal} /></Modal>}
      {activeModal==="expenses" && <Modal title="Total Expenses Today" color="bg-rose-800" onClose={()=>setActiveModal(null)}><ExpensesModal modal={modal} /></Modal>}
      {activeModal==="coding" && <Modal title="Coding Units" color="bg-gradient-to-r from-fuchsia-600 to-pink-600" onClose={()=>setActiveModal(null)}><CodingModal stats={stats} modal={modal} /></Modal>}
    </div>
  );
}

// Sub-components for Modals
function FleetModal({stats, modal, navigate}: any) {
  const [filter, setFilter] = useState('all');
  const [search, setSearch] = useState('');
  
  const units = modal?.unitsList || [];
  const filteredUnits = units.filter((u: any) => {
    const matchesFilter = filter === 'all' || u.status === filter;
    const matchesSearch = u.plate.toLowerCase().includes(search.toLowerCase());
    return matchesFilter && matchesSearch;
  });

  const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
      case 'active': return { bg: 'bg-green-50', text: 'text-green-600', bd: 'border-green-100', label: 'ACTIVE' };
      case 'maintenance': return { bg: 'bg-red-50', text: 'text-red-600', bd: 'border-red-100', label: 'MAINTENANCE' };
      case 'coding': return { bg: 'bg-amber-50', text: 'text-amber-600', bd: 'border-amber-100', label: 'CODING' };
      default: return { bg: 'bg-gray-50', text: 'text-gray-400', bd: 'border-gray-200', label: 'VACANT' };
    }
  };

  return (
    <div className="space-y-6">
      {/* Search & Filter Bar */}
      <div className="flex flex-col gap-4">
        <div className="relative">
          <input 
            type="text" 
            placeholder="Search plate number..." 
            className="w-full bg-gray-100 border-none rounded-2xl py-4 pl-12 pr-4 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-blue-500 transition-all"
            value={search}
            onChange={(e)=>setSearch(e.target.value)}
          />
          <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
            <Users className="w-5 h-5"/>
          </div>
        </div>
        
        <div className="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
          {['all', 'active', 'maintenance', 'coding', 'vacant'].map((f) => (
            <button 
              key={f}
              onClick={() => setFilter(f)}
              className={`px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all ${filter === f ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'}`}
            >
              {f}
            </button>
          ))}
        </div>
      </div>

      {/* Summary Summary Cards */}
      <div className="grid grid-cols-2 gap-3">
        {[
          { l: 'Total Units', v: units.length, c: 'text-blue-600', bg: 'bg-blue-50/50' },
          { l: 'Vacant (No Driver)', v: units.filter((u:any)=>u.status==='vacant').length, c: 'text-emerald-600', bg: 'bg-emerald-50/50' },
          { l: 'Active Units', v: units.filter((u:any)=>u.status==='active').length, c: 'text-amber-600', bg: 'bg-amber-50/50' },
          { l: 'Avg ROI', v: units.length > 0 ? (units.reduce((a:any,b:any)=>a+b.roi,0)/units.length).toFixed(1)+'%' : '0%', c: 'text-purple-600', bg: 'bg-purple-50/50' }
        ].map((s) => (
          <div key={s.l} className={`${s.bg} rounded-2xl p-4 border border-gray-50 flex flex-col items-center text-center`}>
            <p className={`text-2xl font-black ${s.c} leading-none mb-1`}>{s.v}</p>
            <p className="text-[8px] font-black text-gray-400 uppercase tracking-widest">{s.l}</p>
          </div>
        ))}
      </div>

      {/* Units Grid */}
      <div className="grid grid-cols-2 gap-3">
        {filteredUnits.map((u: any) => {
          const s = getStatusColor(u.status);
          return (
            <div key={u.id} className={`${s.bg} border ${s.bd} rounded-2xl p-4 transition-all active:scale-[0.98]`}>
              <div className="flex justify-between items-start mb-4">
                <p className="text-[11px] font-black text-gray-900 tracking-tight">{u.plate}</p>
                <span className={`text-[7px] font-black px-1.5 py-0.5 rounded-md ${s.text} bg-white border ${s.bd}`}>{s.label}</span>
              </div>
              
              <div className="space-y-3">
                 <div className="flex justify-between items-end">
                    <div>
                       <p className="text-[7px] font-black text-gray-400 uppercase tracking-tighter">Total Coll.</p>
                       <p className="text-[10px] font-black text-green-600 leading-none">{fmt(u.total_collection).split('.')[0]}</p>
                    </div>
                    <div className="text-right">
                       <p className="text-[7px] font-black text-gray-400 uppercase tracking-tighter">ROI</p>
                       <p className="text-[10px] font-black text-gray-900 leading-none">{u.roi}%</p>
                    </div>
                 </div>
                 <div className="pt-2 border-t border-black/5 flex justify-between items-center">
                    <span className="text-[6px] font-bold text-gray-400">ID: {u.plate}</span>
                    <span className="text-[6px] font-bold text-gray-400">NO DAILY</span>
                 </div>
              </div>
            </div>
          );
        })}
      </div>

      <button onClick={()=>navigate("/units")} className="w-full bg-blue-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-blue-100 active:scale-95 transition-all">View Full Unit List</button>
    </div>
  );
}

function BoundaryModal({stats, modal, navigate}: any) {
  const [search, setSearch] = useState('');
  const [date, setDate] = useState(dayjs().format('MM/DD/YYYY'));
  
  const boundaries = modal?.boundaryList || [];
  const filteredBoundaries = boundaries.filter((b: any) => {
    const matchesSearch = b.plate_number.toLowerCase().includes(search.toLowerCase()) || 
                         (b.driver_name || '').toLowerCase().includes(search.toLowerCase());
    return matchesSearch;
  });

  return (
    <div className="space-y-6">
      {/* Search & Date Filter Bar */}
      <div className="flex gap-2">
        <div className="relative flex-1">
          <input 
            type="text" 
            placeholder="Search by unit number, driver, or amount..." 
            className="w-full bg-gray-100 border-none rounded-2xl py-4 pl-12 pr-4 text-[11px] font-bold text-gray-900 focus:ring-2 focus:ring-emerald-500 transition-all"
            value={search}
            onChange={(e)=>setSearch(e.target.value)}
          />
          <div className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
            <DollarSign className="w-5 h-5"/>
          </div>
        </div>
        <div className="bg-gray-100 px-4 rounded-2xl flex items-center gap-2 border-none">
           <span className="text-[10px] font-black text-gray-600">{date}</span>
           <Calendar className="w-4 h-4 text-gray-400"/>
        </div>
      </div>

      {/* Summary Summary Cards */}
      <div className="grid grid-cols-2 gap-3">
        {[
          { l: 'Total Today', v: fmt(stats?.today_boundary), c: 'text-emerald-600', bg: 'bg-emerald-50/50', icon: Calendar },
          { l: 'Yesterday Total', v: fmt(stats?.yesterday_boundary), c: 'text-blue-600', bg: 'bg-blue-50/50', icon: RefreshCw },
          { l: 'Monthly Total', v: fmt(stats?.month_boundary), c: 'text-purple-600', bg: 'bg-purple-50/50', icon: BarChart3 },
          { l: 'Yearly Total Amount', v: fmt(stats?.year_boundary), c: 'text-amber-600', bg: 'bg-amber-50/50', icon: TrendingUp }
        ].map((s) => (
          <div key={s.l} className={`${s.bg} rounded-2xl p-4 border border-gray-50 flex items-center gap-3`}>
            <div className={`p-2 bg-white rounded-xl shadow-sm ${s.c}`}><s.icon className="w-4 h-4"/></div>
            <div>
               <p className={`text-[11px] font-black ${s.c} leading-none mb-1`}>{s.v.split('.')[0]}</p>
               <p className="text-[7px] font-black text-gray-400 uppercase tracking-widest leading-none">{s.l}</p>
            </div>
          </div>
        ))}
      </div>

      {/* Content Area */}
      {filteredBoundaries.length === 0 ? (
        <div className="py-20 flex flex-col items-center text-center">
           <div className="w-16 h-16 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-4 border border-gray-100">
              <Calendar className="w-8 h-8 text-gray-200"/>
           </div>
           <p className="text-sm font-black text-gray-800">No boundary collections found</p>
           <p className="text-[11px] text-gray-400 font-medium mt-1">Try adjusting your search or date filter</p>
        </div>
      ) : (
        <div className="space-y-3">
           {filteredBoundaries.map((b: any) => (
             <div key={b.id} className="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center justify-between active:scale-[0.98] transition-all">
                <div className="flex items-center gap-4">
                   <div className="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center font-black text-emerald-600 text-xs">TX</div>
                   <div>
                      <p className="font-black text-gray-900 text-sm leading-none mb-1">{b.plate_number}</p>
                      <p className="text-[10px] text-gray-400 font-medium">{b.driver_name || 'No driver'}</p>
                   </div>
                </div>
                <div className="text-right">
                   <p className="text-sm font-black text-emerald-600">{fmt(b.actual_boundary)}</p>
                   <span className="text-[8px] font-black text-gray-400 uppercase tracking-widest">{b.status}</span>
                </div>
             </div>
           ))}
        </div>
      )}

      <button onClick={()=>navigate("/boundaries")} className="w-full bg-emerald-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-emerald-100 flex items-center justify-center gap-2 active:scale-95 transition-all">
        Full Boundary Logs <ChevronRight className="w-5 h-5"/>
      </button>
    </div>
  );
}

function IncomeModal({stats, modal}: any) {
  const [tab, setTab] = useState('today');

  const data = modal?.financialBreakdown?.[tab] || {
    total_revenue: 0,
    total_expenses: 0,
    boundaries: [],
    maintenance: [],
    general: [],
    salaries: []
  };

  const printReport = () => {
    const periodLabel = tab.toUpperCase();
    const timestamp = dayjs().format('MM/DD/YYYY, h:mm:ss A');
    
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const reportHtml = `
      <html>
        <head>
          <title>Financial Report - ${periodLabel}</title>
          <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 40px; color: #1e293b; line-height: 1.5; }
            .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; }
            .header h1 { margin: 0; font-size: 28px; font-weight: 900; text-transform: uppercase; letter-spacing: 4px; color: #0f172a; }
            .header p { margin: 5px 0; color: #64748b; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; }
            .section { margin-bottom: 30px; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
            .section-header { background: #0f172a; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; }
            .section-header.expense { background: #991b1b; }
            .section-header h2 { margin: 0; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; font-weight: 800; }
            .section-header .amount { font-size: 18px; font-weight: 900; color: #4ade80; }
            .section-header.expense .amount { color: #fca5a5; }
            .content { padding: 20px; }
            table { width: 100% !important; border-collapse: collapse; margin-top: 10px; }
            th { text-align: left; padding: 10px; border-bottom: 2px solid #f1f5f9; color: #64748b; text-transform: uppercase; font-size: 10px; font-weight: 800; }
            td { padding: 10px; border-bottom: 1px solid #f8fafc; font-size: 12px; }
            .text-right { text-align: right; }
            .font-bold { font-weight: 700; }
            .font-black { font-weight: 900; }
            .footer { text-align: center; margin-top: 50px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; font-weight: 600; }
            .sub-section-title { font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin: 20px 0 10px 0; display: flex; justify-content: space-between; }
            .total-row { background: #f8fafc; font-weight: 900; }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>Financial Report</h1>
            <p>Euro Taxi Management System &mdash; ${periodLabel}</p>
          </div>

          <div class="section">
            <div class="section-header">
              <h2>Revenue: Total Boundary Collected</h2>
              <div class="amount">${fmt(data.total_revenue)}</div>
            </div>
            <div class="content">
              <table>
                <thead>
                  <tr>
                    <th>Unit / Driver Detail</th>
                    <th class="text-right">Amount Collected</th>
                  </tr>
                </thead>
                <tbody>
                  ${data.boundaries.length === 0 ? '<tr><td colspan="2" style="text-align:center; padding: 20px; color: #cbd5e1; font-style: italic;">No records found</td></tr>' : 
                    data.boundaries.map((b: any) => `
                    <tr>
                      <td><span class="font-black">${b.plate_number}</span><br/><span style="font-size: 10px; color: #64748b; text-transform: uppercase;">${b.driver_name || 'No Driver'}</span></td>
                      <td class="text-right font-black" style="color: #059669;">${fmt(b.actual_boundary)}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>
          </div>

          <div class="section">
            <div class="section-header expense">
              <h2>Operating Expenses Breakdown</h2>
              <div class="amount">${fmt(data.total_expenses)}</div>
            </div>
            <div class="content">
              <div class="sub-section-title">
                <span>Maintenance & Repairs Itemized</span>
                <span style="color: #b91c1c;">Total: ${fmt(data.maintenance.reduce((a:any,b:any)=>a+b.cost,0))}</span>
              </div>
              <table>
                <thead>
                  <tr>
                    <th>Unit / Type</th>
                    <th class="text-right">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  ${data.maintenance.length === 0 ? '<tr><td colspan="2" style="text-align:center; padding: 10px; color: #cbd5e1;">No maintenance records</td></tr>' : 
                    data.maintenance.map((m: any) => `
                    <tr>
                      <td class="font-bold">${m.plate_number} - ${m.type}</td>
                      <td class="text-right font-bold" style="color: #b91c1c;">${fmt(m.cost)}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>

              <div class="sub-section-title" style="margin-top: 30px;">
                <span>General Office Expenses Itemized</span>
                <span style="color: #b91c1c;">Total: ${fmt(data.general.reduce((a:any,b:any)=>a+b.amount,0))}</span>
              </div>
              <table>
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                  </tr>
                </thead>
                <tbody>
                  ${data.general.length === 0 ? '<tr><td colspan="3" style="text-align:center; padding: 10px; color: #cbd5e1;">No expense records</td></tr>' : 
                    data.general.map((e: any) => `
                    <tr>
                      <td style="color: #64748b;">${dayjs(e.date).format('MM/DD/YYYY')}</td>
                      <td class="font-bold">${e.description}</td>
                      <td class="text-right font-bold" style="color: #b91c1c;">${fmt(e.amount)}</td>
                    </tr>
                  `).join('')}
                </tbody>
              </table>
            </div>
          </div>

          <div class="footer">
            <p>Authenticated Financial Statement &bull; Generated on ${timestamp}</p>
            <p style="margin-top: 5px; font-size: 8px; color: #cbd5e1;">&copy; ${new Date().getFullYear()} Euro Taxi. All rights reserved.</p>
          </div>
        </body>
      </html>
    `;

    printWindow.document.write(reportHtml);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
      printWindow.print();
    }, 500);
  };

  return (
    <div className="space-y-6">
       {/* Tabs */}
       <div className="bg-emerald-800/10 p-1 rounded-2xl flex gap-1">
          {['Today', 'Weekly', 'Monthly', 'Yearly'].map(t => (
             <button key={t} onClick={()=>setTab(t.toLowerCase())}
                className={`flex-1 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all ${tab === t.toLowerCase() ? 'bg-white text-emerald-700 shadow-sm' : 'text-gray-400 hover:bg-white/50'}`}>
                {t}
             </button>
          ))}
       </div>

       {/* Revenue Section */}
       <div className="rounded-[1.5rem] overflow-hidden border border-slate-200 shadow-sm">
          <div className="bg-slate-900 p-4 flex justify-between items-center">
             <p className="text-[9px] font-black text-slate-400 uppercase tracking-widest">Revenue: Total Boundary Collected</p>
             <p className="text-sm font-black text-emerald-400">{fmt(data.total_revenue)}</p>
          </div>
          <div className="bg-white p-4">
             <div className="flex justify-between pb-2 mb-2 border-b border-gray-50 text-[8px] font-black text-gray-400 uppercase tracking-widest">
                <span>Unit / Driver Detail</span>
                <span className="text-right">Amount Collected</span>
             </div>
             <div className="max-h-40 overflow-y-auto space-y-2">
                {data.boundaries.length === 0 ? (
                   <p className="py-4 text-center text-[10px] font-bold text-gray-300 uppercase italic">No records found</p>
                ) : data.boundaries.map((b: any) => (
                   <div key={b.id} className="flex justify-between items-center text-[10px]">
                      <div className="flex flex-col">
                         <span className="font-black text-gray-900">{b.plate_number}</span>
                         <span className="text-[8px] text-gray-400 uppercase font-bold">{b.driver_name || 'No Driver'}</span>
                      </div>
                      <span className="font-black text-emerald-600">{fmt(b.actual_boundary)}</span>
                   </div>
                ))}
             </div>
          </div>
       </div>

       {/* Expenses Section */}
       <div className="rounded-[1.5rem] overflow-hidden border border-rose-200 shadow-sm">
          <div className="bg-rose-800 p-4 flex justify-between items-center">
             <p className="text-[9px] font-black text-rose-100 uppercase tracking-widest">Operating Expenses Breakdown</p>
             <p className="text-sm font-black text-white">{fmt(data.total_expenses)}</p>
          </div>
          <div className="bg-white p-4 space-y-6">
             {/* Maintenance */}
             <div>
                <div className="flex justify-between items-center mb-3">
                   <p className="text-[8px] font-black text-gray-400 uppercase tracking-widest">Maintenance & Repairs Itemized</p>
                   <p className="text-[8px] font-black text-rose-600 uppercase">Total: {fmt(data.maintenance.reduce((a:any,b:any)=>a+b.cost,0))}</p>
                </div>
                <div className="space-y-1">
                   {data.maintenance.length === 0 ? (
                      <p className="py-2 text-center text-[9px] font-bold text-gray-200 uppercase italic">No records found</p>
                   ) : data.maintenance.map((m: any) => (
                      <div key={m.id} className="flex justify-between text-[9px] font-bold">
                         <span className="text-gray-600">{m.plate_number} - {m.type}</span>
                         <span className="text-rose-600">{fmt(m.cost)}</span>
                      </div>
                   ))}
                </div>
             </div>

             {/* General Expenses */}
             <div className="pt-4 border-t border-gray-100">
                <div className="flex justify-between items-center mb-3">
                   <p className="text-[8px] font-black text-gray-400 uppercase tracking-widest">General Office Expenses Itemized</p>
                   <p className="text-[8px] font-black text-rose-600 uppercase">Total: {fmt(data.general.reduce((a:any,b:any)=>a+b.amount,0))}</p>
                </div>
                <div className="space-y-3">
                   <div className="flex justify-between text-[7px] font-black text-gray-300 uppercase">
                      <span className="w-16">Date</span>
                      <span className="flex-1 px-4">Description</span>
                      <span className="w-16 text-right">Amount</span>
                   </div>
                   <div className="max-h-48 overflow-y-auto space-y-2">
                      {data.general.length === 0 ? (
                         <p className="py-2 text-center text-[9px] font-bold text-gray-200 uppercase italic">No records found</p>
                      ) : data.general.map((e: any) => (
                         <div key={e.id} className="flex justify-between text-[9px] font-bold gap-4 items-start">
                            <span className="text-gray-400 w-16 whitespace-nowrap">{dayjs(e.date).format('M/D/YYYY')}</span>
                            <span className="flex-1 text-gray-900 leading-tight">{e.description}</span>
                            <span className="text-rose-600 w-16 text-right">{fmt(e.amount)}</span>
                         </div>
                      ))}
                   </div>
                </div>
             </div>
          </div>
       </div>

       <button 
        onClick={printReport}
        className="w-full bg-emerald-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-emerald-100 flex items-center justify-center gap-2 active:scale-95 transition-all">
          <TrendingUp className="w-4 h-4"/>
          PRINT REPORT
       </button>
    </div>
  );
}

function MaintenanceModal({modal}: any) {
  const [filter, setFilter] = useState('all');
  const [search, setSearch] = useState('');
  
  const list = modal?.maintenanceList || [];
  
  // Logic: "All" and the first 4 summary cards usually refer to ACTIVE/PENDING maintenance.
  // "Complete" is treated as a separate category.
  const filteredList = list.filter((m: any) => {
    const isComplete = m.status?.toLowerCase() === 'completed';
    const matchesSearch = m.plate_number.toLowerCase().includes(search.toLowerCase()) || 
                         m.type?.toLowerCase().includes(search.toLowerCase()) ||
                         m.description?.toLowerCase().includes(search.toLowerCase());
    
    if (!matchesSearch) return false;

    if (filter === 'all') return !isComplete;
    if (filter === 'complete') return isComplete;
    return m.type?.toLowerCase() === filter && !isComplete;
  });

  const getStatusColor = (item: any) => {
    const isComplete = item.status?.toLowerCase() === 'completed';
    if (isComplete) return { text: 'text-emerald-600', bg: 'bg-emerald-50', bd: 'border-emerald-100', label: 'COMPLETE' };
    
    switch (item.type?.toLowerCase()) {
      case 'preventive': return { text: 'text-blue-600', bg: 'bg-blue-50', bd: 'border-blue-100', label: 'PREVENTIVE' };
      case 'corrective': return { text: 'text-amber-600', bg: 'bg-amber-50', bd: 'border-amber-100', label: 'CORRECTIVE' };
      case 'emergency': return { text: 'text-red-600', bg: 'bg-red-50', bd: 'border-red-100', label: 'EMERGENCY' };
      default: return { text: 'text-gray-600', bg: 'bg-gray-50', bd: 'border-gray-100', label: 'UNSPECIFIED' };
    }
  };

  const activeMaintenance = list.filter((m:any) => m.status?.toLowerCase() !== 'completed');

  return (
    <div className="space-y-6">
      {/* Search Bar */}
      <div className="relative">
        <input 
          type="text" 
          placeholder="Search by unit number, plate, or maintenance type..." 
          className="w-full bg-orange-50/50 border border-orange-100 rounded-2xl py-4 pl-12 pr-4 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-orange-500 transition-all placeholder:text-orange-300"
          value={search}
          onChange={(e)=>setSearch(e.target.value)}
        />
        <div className="absolute left-4 top-1/2 -translate-y-1/2 text-orange-400">
          <Wrench className="w-5 h-5"/>
        </div>
      </div>

      {/* Filter Tabs */}
      <div className="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
        {['all', 'preventive', 'corrective', 'emergency', 'complete'].map((f) => (
          <button 
            key={f}
            onClick={() => setFilter(f)}
            className={`px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all whitespace-nowrap ${filter === f ? 'bg-orange-600 text-white shadow-lg shadow-orange-100' : 'bg-gray-100 text-gray-400 hover:bg-gray-200'}`}
          >
            {f}
          </button>
        ))}
      </div>

      {/* Summary Cards - Matching Web Logic (Active vs Complete) */}
      <div className="grid grid-cols-3 gap-2">
        {[
          { l: 'MAINTENANCE', v: activeMaintenance.length, c: 'text-orange-600' },
          { l: 'PREVENTIVE', v: activeMaintenance.filter((m:any)=>m.type?.toLowerCase()==='preventive').length, c: 'text-blue-600' },
          { l: 'CORRECTIVE', v: activeMaintenance.filter((m:any)=>m.type?.toLowerCase()==='corrective').length, c: 'text-amber-600' },
          { l: 'EMERGENCY', v: activeMaintenance.filter((m:any)=>m.type?.toLowerCase()==='emergency').length, c: 'text-red-600' },
          { l: 'COMPLETE', v: list.filter((m:any)=>m.status?.toLowerCase()==='completed').length, c: 'text-emerald-600' }
        ].map((s) => (
          <div key={s.l} className="bg-gray-50 rounded-xl p-3 border border-gray-100 flex flex-col items-center text-center">
            <p className={`text-lg font-black ${s.c} leading-none mb-1`}>{s.v}</p>
            <p className="text-[7px] font-black text-gray-400 uppercase tracking-tighter">{s.l}</p>
          </div>
        ))}
      </div>

      {/* Maintenance List */}
      <div className="space-y-4">
        {filteredList.length === 0 ? (
          <div className="text-center py-12">
            <Wrench className="w-12 h-12 text-gray-200 mx-auto mb-3" />
            <p className="text-gray-400 font-black uppercase text-[10px] tracking-widest">No records found</p>
          </div>
        ) : filteredList.map((m: any) => {
          const s = getStatusColor(m);
          return (
            <div key={m.id} className="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col">
              <div className="p-5 flex justify-between items-start">
                <div className="flex items-center gap-3">
                  <div className={`w-10 h-10 ${s.bg} rounded-2xl flex items-center justify-center`}>
                    <Wrench className={`w-5 h-5 ${s.text}`}/>
                  </div>
                  <div>
                    <h4 className="font-black text-gray-900 text-sm">{m.plate_number}</h4>
                    <p className="text-[10px] font-bold text-gray-400 uppercase tracking-tight">Started: {m.date_started}</p>
                  </div>
                </div>
                <div className="text-right">
                  <span className={`text-[9px] font-black px-3 py-1.5 rounded-xl uppercase tracking-widest ${s.bg} ${s.text} border ${s.bd}`}>
                    {s.label}
                  </span>
                  <p className="text-[10px] font-bold text-gray-400 mt-2">{m.date_started}</p>
                </div>
              </div>
              
              <div className="px-5 pb-5 space-y-3">
                <div className="flex items-center gap-2">
                  <span className="text-[10px] font-black text-gray-400 uppercase">Status:</span>
                  <span className="text-[10px] font-black text-orange-600 uppercase">{m.status || 'pending'}</span>
                </div>
                
                <div className="bg-gray-50 rounded-2xl p-4">
                  <p className="text-xs text-gray-600 leading-relaxed font-medium">
                    {m.description || "No description available for this maintenance task."}
                  </p>
                </div>

                <div className="flex items-center justify-between pt-2 border-t border-gray-50">
                   <div className="flex items-center gap-1.5">
                      <div className={`w-1.5 h-1.5 ${m.status?.toLowerCase() === 'completed' ? 'bg-emerald-500' : 'bg-orange-500'} rounded-full`}></div>
                      <span className="text-[9px] font-black text-gray-400 uppercase tracking-widest">{m.status || 'pending'}</span>
                   </div>
                   <p className="text-sm font-black text-gray-900">{fmt(m.cost)}</p>
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
}

function DriversModal({modal}: any) {
  const [search, setSearch] = useState('');
  const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');
  
  const drivers = modal?.driversList || [];
  
  const filteredDrivers = drivers
    .filter((d: any) => {
      const fullName = `${d.first_name} ${d.last_name}`.toLowerCase();
      return fullName.includes(search.toLowerCase()) || 
             d.license_number?.toLowerCase().includes(search.toLowerCase()) ||
             d.contact_number?.toLowerCase().includes(search.toLowerCase());
    })
    .sort((a: any, b: any) => {
      const nameA = `${a.first_name} ${a.last_name}`.toLowerCase();
      const nameB = `${b.first_name} ${b.last_name}`.toLowerCase();
      return sortOrder === 'asc' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
    });

  const totalDrivers = drivers.length;
  const vacantDrivers = drivers.filter((d:any) => !d.plate_number).length;
  const activeDrivers = drivers.filter((d:any) => d.plate_number).length;

  return (
    <div className="space-y-6">
      {/* Search & Sort Bar */}
      <div className="flex gap-2">
        <div className="relative flex-1">
          <input 
            type="text" 
            placeholder="Search by name, license, or contact..." 
            className="w-full bg-indigo-50/50 border border-indigo-100 rounded-2xl py-4 pl-12 pr-4 text-sm font-bold text-gray-900 focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-indigo-300"
            value={search}
            onChange={(e)=>setSearch(e.target.value)}
          />
          <div className="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-400">
            <Users className="w-5 h-5"/>
          </div>
        </div>
        <button 
          onClick={() => setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc')}
          className="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl text-indigo-600 active:scale-95 transition-all flex items-center gap-2"
        >
          <span className="text-[10px] font-black uppercase">A-Z</span>
          <div className={`transition-transform ${sortOrder === 'desc' ? 'rotate-180' : ''}`}>
             <TrendingUp className="w-4 h-4"/>
          </div>
        </button>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-2 gap-3">
        {[
          { l: 'TOTAL DRIVERS', v: totalDrivers, c: 'text-blue-600', bg: 'bg-blue-50/50' },
          { l: 'TOTAL VACANT DRIVERS', v: vacantDrivers, c: 'text-emerald-600', bg: 'bg-emerald-50/50' },
          { l: 'TOTAL ACTIVE DRIVERS', v: activeDrivers, c: 'text-orange-600', bg: 'bg-orange-50/50' },
          { l: 'TOP PERFORMERS', v: 1, c: 'text-purple-600', bg: 'bg-purple-50/50' }
        ].map((s) => (
          <div key={s.l} className={`${s.bg} rounded-2xl p-4 border border-gray-50 flex flex-col items-center text-center`}>
            <p className={`text-2xl font-black ${s.c} leading-none mb-1`}>{s.v}</p>
            <p className="text-[8px] font-black text-gray-400 uppercase tracking-widest">{s.l}</p>
          </div>
        ))}
      </div>

      {/* Drivers List */}
      <div className="space-y-4">
        {filteredDrivers.length === 0 ? (
          <div className="text-center py-12">
            <Users className="w-12 h-12 text-gray-200 mx-auto mb-3" />
            <p className="text-gray-400 font-black uppercase text-[10px] tracking-widest">No matching drivers</p>
          </div>
        ) : filteredDrivers.map((d: any) => (
          <div key={d.id} className="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden flex flex-col">
            <div className="p-5 flex justify-between items-start">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 bg-indigo-50 rounded-2xl flex items-center justify-center">
                  <Users className="w-5 h-5 text-indigo-500"/>
                </div>
                <div>
                  <h4 className="font-black text-gray-900 text-sm">{d.first_name} {d.last_name}</h4>
                  <p className="text-[10px] font-bold text-gray-400 uppercase tracking-tight">{d.license_number || 'TBD-UNKNOWN'}</p>
                </div>
              </div>
              <div className="text-right">
                <span className={`text-[9px] font-black px-3 py-1.5 rounded-xl uppercase tracking-widest ${d.plate_number ? 'bg-green-50 text-green-600 border-green-100' : 'bg-gray-50 text-gray-400 border-gray-100'} border`}>
                  {d.plate_number ? 'Assigned' : 'Vacant'}
                </span>
                {d.plate_number && <p className="text-[10px] font-black text-gray-400 mt-2 uppercase">{d.plate_number}</p>}
              </div>
            </div>
            
            <div className="px-5 pb-5 space-y-4">
              <div className="grid grid-cols-2 gap-4">
                 <div>
                   <p className="text-[8px] font-black text-gray-400 uppercase mb-1">Contact:</p>
                   <p className="text-[11px] font-bold text-gray-700">{d.phone || 'N/A'}</p>
                 </div>
                 <div>
                   <p className="text-[8px] font-black text-gray-400 uppercase mb-1">Address:</p>
                   <p className="text-[11px] font-bold text-gray-700">{d.address || 'No address available'}</p>
                 </div>
               </div>
               
               <div className="flex items-center justify-between pt-4 border-t border-gray-50">
                 <div className="flex items-center gap-2">
                    <div className={`w-2 h-2 rounded-full ${d.performance_rating === 'excellent' ? 'bg-green-500' : d.performance_rating === 'good' ? 'bg-yellow-500' : d.performance_rating === 'average' ? 'bg-orange-500' : 'bg-gray-400'} animate-pulse`}></div>
                    <span className="text-[10px] font-black text-gray-400 uppercase">{d.performance_rating || 'needs_improvement'}</span>
                 </div>
                 <div className="text-right">
                    <p className="text-sm font-black text-blue-600">{fmt(d.total_collected)}</p>
                    <p className="text-[8px] font-black text-gray-400 uppercase">Total Collected</p>
                 </div>
               </div>

              <div className="flex items-center justify-between pt-2">
                 <div className="flex items-center gap-1.5">
                    <Calendar className="w-3 h-3 text-gray-300"/>
                    <span className="text-[9px] font-bold text-gray-400">No hire date</span>
                 </div>
                 <div className="flex items-center gap-1.5">
                    <div className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span className="text-[9px] font-black text-green-600 uppercase">Active</span>
                 </div>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

function ExpensesModal({modal}: any) {
  const [tab, setTab] = useState('today');
  
  // Map display tab to API key - matches web dashboard exactly
  // Web uses: 'today', 'week' (Sunday-start), 'month', 'year'
  const tabToKey: Record<string,string> = {
    'today': 'today',
    'weekly': 'week',
    'monthly': 'month',
    'yearly': 'year',
  };

  const data = modal?.financialBreakdown?.[tabToKey[tab] || tab] || {
    total_expenses: 0,
    maintenance: [],
    general: [],
    salaries: []
  };

  const totalMaintenance = data.maintenance.reduce((a:any, b:any) => a + Number(b.cost || 0), 0);
  const totalGeneral = data.general.reduce((a:any, b:any) => a + Number(b.amount || 0), 0);
  const totalSalaries = data.salaries.reduce((a:any, b:any) => a + Number(b.total_salary || 0), 0);

  const tabs = ['today', 'weekly', 'monthly', 'yearly'];
  const tabLabels: Record<string,string> = { today: 'Today', weekly: 'Weekly', monthly: 'Monthly', yearly: 'Yearly' };

  return (
    <div className="space-y-6">
       {/* Tabs */}
       <div className="bg-rose-900/10 p-1 rounded-2xl flex gap-1">
          {tabs.map(t => (
             <button key={t} onClick={()=>setTab(t)}
                className={`flex-1 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all ${tab === t ? 'bg-white text-rose-700 shadow-sm' : 'text-gray-400 hover:bg-white/50'}`}>
                {tabLabels[t]}
             </button>
          ))}
       </div>

       {/* Detailed Breakdown Banner — matches web's red-900 header */}
       <div className="bg-rose-900 rounded-t-2xl rounded-b-none flex justify-between items-center px-6 py-4">
          <span className="text-[11px] font-black text-white uppercase tracking-[0.1em]">Detailed Expenses Breakdown</span>
          <span className="text-xl font-black text-rose-300">{fmt(Number(totalMaintenance + totalGeneral))}</span>
       </div>
       <div className="bg-white rounded-b-3xl border border-gray-100 overflow-hidden shadow-sm mb-1">

          {/* Maintenance Section — Date | Description | Amount (matches web) */}
          <div>
             <div className="bg-gray-50 px-4 py-2 border-b border-gray-100 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                <span>Maintenance &amp; Repairs Itemized</span>
                <span className="text-orange-600 font-black">Total: {fmt(totalMaintenance)}</span>
             </div>

             {data.maintenance.length === 0 ? (
                <div className="px-4 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-50">
                   No records found
                </div>
             ) : (
                <div>
                   {/* Table header */}
                   <div className="flex bg-gray-50/50 border-b border-gray-100 text-[8px] uppercase tracking-widest text-gray-400 font-bold">
                      <span className="px-4 py-2 w-1/4">Date</span>
                      <span className="px-4 py-2 flex-1">Description</span>
                      <span className="px-4 py-2 w-1/4 text-right">Amount</span>
                   </div>
                   <div className="divide-y divide-gray-50">
                      {data.maintenance.map((m: any) => (
                         <div key={m.id} className="flex items-start hover:bg-gray-50/50 transition-colors">
                            <span className="px-4 py-2 text-[9px] text-gray-400 font-bold uppercase whitespace-nowrap w-1/4">
                               {m.date ? dayjs(m.date).format('YYYY-MM-DD') : '—'}
                            </span>
                            <span className="px-4 py-2 text-[10px] font-black text-gray-800 tracking-tight flex-1">
                               Unit {m.plate_number} - {(m.type || 'maintenance').toLowerCase()}
                            </span>
                            <span className="px-4 py-2 text-xs font-black text-rose-500 text-right whitespace-nowrap w-1/4">
                               {fmt(Number(m.cost || 0))}
                            </span>
                         </div>
                      ))}
                   </div>
                </div>
             )}
          </div>

          {/* General Office Expenses — Date | Description | Amount (matches web) */}
          <div>
             <div className="bg-gray-50 px-4 py-2 border-b border-gray-100 flex justify-between text-[9px] font-black text-gray-400 uppercase tracking-widest">
                <span>General Office Expenses Itemized</span>
                <span className="text-rose-500 font-black">Total: {fmt(totalGeneral)}</span>
             </div>

             {data.general.length === 0 ? (
                <div className="px-4 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                   No records found
                </div>
             ) : (
                <div>
                   <div className="flex bg-gray-50/50 border-b border-gray-100 text-[8px] uppercase tracking-widest text-gray-400 font-bold">
                      <span className="px-4 py-2 w-1/4">Date</span>
                      <span className="px-4 py-2 flex-1">Description</span>
                      <span className="px-4 py-2 w-1/4 text-right">Amount</span>
                   </div>
                   <div className="max-h-72 overflow-y-auto divide-y divide-gray-50">
                      {data.general.map((e: any) => (
                         <div key={e.id} className="flex items-start hover:bg-gray-50/50 transition-colors">
                            <span className="px-4 py-2 text-[9px] text-gray-400 font-bold uppercase whitespace-nowrap w-1/4">
                               {e.date ? dayjs(e.date).format('YYYY-MM-DD') : '—'}
                            </span>
                            <span className="px-4 py-2 text-[10px] font-black text-gray-800 tracking-tight flex-1">
                               {e.description || e.category || 'Office Expense'}
                            </span>
                            <span className="px-4 py-2 text-xs font-black text-rose-500 text-right whitespace-nowrap w-1/4">
                               {fmt(Number(e.amount || 0))}
                            </span>
                         </div>
                      ))}
                   </div>
                </div>
             )}
          </div>
       </div>

       {/* Print Button — matches web's printExpensesNewTab() */}
       <button
          onClick={() => {
            const periodLabel = tabLabels[tab] || 'TODAY';
            const timestamp = dayjs().format('MM/DD/YYYY, h:mm:ss A');
            const fmt2 = (n: number) => '₱' + Number(n||0).toLocaleString('en-PH',{minimumFractionDigits:2,maximumFractionDigits:2});

            const renderRows = (items: any[], type: 'maintenance'|'general') =>
              items.length === 0
                ? `<div class="no-records">No records found</div>`
                : `<table>
                    <thead><tr>
                      <th>Date</th><th>Description</th><th class="text-right">Amount</th>
                    </tr></thead>
                    <tbody>
                      ${items.map((item: any) => `<tr>
                        <td class="date">${item.date ? dayjs(item.date).format('YYYY-MM-DD') : '—'}</td>
                        <td>${type === 'maintenance' ? `Unit ${item.plate_number} - ${(item.type||'maintenance').toLowerCase()}` : (item.description || item.category || 'Office Expense')}</td>
                        <td class="amount">${fmt2(type === 'maintenance' ? Number(item.cost||0) : Number(item.amount||0))}</td>
                      </tr>`).join('')}
                    </tbody>
                  </table>`;

            const win = window.open('', '_blank');
            if (!win) return;
            win.document.write(`<!DOCTYPE html><html lang="en"><head>
              <meta charset="UTF-8">
              <title>Expense Statement — ${periodLabel}</title>
              <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                body { background:#fff; font-family:'Segoe UI',system-ui,sans-serif; padding:40px; color:#111; }
                h1 { text-align:center; font-size:24px; font-weight:900; text-transform:uppercase; letter-spacing:.15em; margin-bottom:4px; }
                .subtitle { text-align:center; font-size:11px; color:#64748b; font-weight:700; letter-spacing:.15em; text-transform:uppercase; margin-bottom:32px; }
                .section-header { display:flex; justify-content:space-between; align-items:center; background:#7f1d1d; color:white; padding:10px 20px; border-radius:6px 6px 0 0; }
                .section-header span { font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.08em; }
                .sub-header { display:flex; justify-content:space-between; background:#f8f8f8; padding:6px 20px; border-left:1px solid #eee; border-right:1px solid #eee; font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:.12em; color:#94a3b8; }
                .sub-total { color:#dc2626; }
                table { width:100%; border-collapse:collapse; border:1px solid #f0f0f0; border-top:none; margin-bottom:24px; }
                thead tr { background:#f8fafc; border-bottom:1px solid #e2e8f0; }
                thead th { padding:8px 20px; font-size:8px; text-transform:uppercase; letter-spacing:.12em; color:#94a3b8; font-weight:700; text-align:left; }
                th.text-right, td.amount { text-align:right; }
                tbody tr { border-bottom:1px solid #f8f8f8; }
                td { padding:8px 20px; font-size:11px; color:#1e293b; }
                td.date { color:#94a3b8; font-weight:700; font-size:9px; text-transform:uppercase; }
                td.amount { font-weight:900; color:#dc2626; white-space:nowrap; }
                .no-records { padding:16px 20px; text-align:center; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.1em; border:1px solid #f0f0f0; border-top:none; margin-bottom:24px; }
                .footer { text-align:center; margin-top:40px; padding-top:16px; border-top:1px solid #e2e8f0; font-size:9px; color:#94a3b8; }
              </style>
            </head><body>
              <h1>Expense Statement</h1>
              <p class="subtitle">Euro Taxi Management System &mdash; ${periodLabel}</p>

              <div class="section-header">
                <span>Maintenance &amp; Repairs Itemized</span>
                <span class="sub-total">Total: ${fmt2(totalMaintenance)}</span>
              </div>
              ${renderRows(data.maintenance, 'maintenance')}

              <div class="section-header">
                <span>General Office Expenses Itemized</span>
                <span class="sub-total">Total: ${fmt2(totalGeneral)}</span>
              </div>
              ${renderRows(data.general, 'general')}

              <div class="footer">
                <p>Authenticated Expense Summary &mdash; Generated: ${timestamp}</p>
              </div>
            </body></html>`);
            win.document.close();
            win.focus();
            setTimeout(() => { win.print(); }, 300);
          }}
          className="w-full bg-rose-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-rose-100 flex items-center justify-center gap-2 active:scale-95 transition-all"
       >
          <Activity className="w-4 h-4"/>
          PRINT EXPENSES REPORT
       </button>
    </div>
  );
}

function CodingModal({stats, modal}: any) {
  const [search, setSearch] = useState('');
  const [period, setPeriod] = useState('all'); // all, today, tomorrow, past
  
  const units = modal?.codingList || [];
  
  const today = dayjs().startOf('day');
  const tomorrow = today.add(1, 'day');
  const todayDayName = today.format('dddd');
  const tomorrowDayName = tomorrow.format('dddd');
  const todayStr = today.format('YYYY-MM-DD');
  const tomorrowStr = tomorrow.format('YYYY-MM-DD');

  let countToday = 0;
  let countTomorrow = 0;
  let countPast = 0;
  
  units.forEach((unit: any) => {
    const unitDate = unit.start_date;
    const codingDay = unit.coding_day;
    const isCompleted = unit.coding_status === 'completed';
    
    if (isCompleted || (unitDate && unitDate < todayStr)) {
        countPast++;
    } else if (unitDate === todayStr || (!unitDate && codingDay === todayDayName)) {
        countToday++;
    } else if (unitDate === tomorrowStr || (!unitDate && codingDay === tomorrowDayName)) {
        countTomorrow++;
    }
  });

  const filteredUnits = units.filter((u: any) => {
    const matchesSearch = u.plate_number?.toLowerCase().includes(search.toLowerCase()) || 
                          u.description?.toLowerCase().includes(search.toLowerCase()) ||
                          u.status?.toLowerCase().includes(search.toLowerCase());
                          
    const unitDate = u.start_date;
    const codingDay = u.coding_day;
    const isCompleted = u.coding_status === 'completed';
    
    let isPast = isCompleted || (unitDate && unitDate < todayStr);
    let isToday = !isPast && (unitDate === todayStr || (!unitDate && codingDay === todayDayName));
    let isTomorrow = !isPast && !isToday && (unitDate === tomorrowStr || (!unitDate && codingDay === tomorrowDayName));

    let matchesPeriod = true;
    if (period === 'today') matchesPeriod = isToday;
    else if (period === 'tomorrow') matchesPeriod = isTomorrow;
    else if (period === 'past') matchesPeriod = isPast;
    
    return matchesSearch && matchesPeriod;
  });

  return (
    <div className="space-y-4">
      {/* Search & Tabs Header inside Modal */}
      <div className="bg-gradient-to-r from-fuchsia-600 to-pink-600 -m-4 mb-4 p-4 shadow-md">
        <div className="flex flex-col gap-3">
          <div className="flex items-center gap-3 mb-2">
            <div className="p-2 bg-white/20 backdrop-blur-sm rounded-lg border border-white/30">
              <Code className="w-5 h-5 text-white" />
            </div>
            <div>
              <p className="text-sm font-bold text-white leading-tight">Complete coding unit management details</p>
            </div>
          </div>
          <div className="flex flex-col gap-2">
            <div className="relative">
              <input 
                type="text" 
                placeholder="Search by unit number, plate, or coding status..." 
                className="w-full bg-white/20 backdrop-blur-sm border border-white/30 rounded-xl py-3 pl-10 pr-10 text-xs font-medium text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all"
                value={search}
                onChange={(e)=>setSearch(e.target.value)}
              />
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white/60"/>
              {search && (
                <button onClick={()=>setSearch('')} className="absolute right-3 top-1/2 -translate-y-1/2">
                  <XCircle className="w-4 h-4 text-white/60 hover:text-white" />
                </button>
              )}
            </div>
            
            <div className="flex bg-white/10 backdrop-blur-sm border border-white/30 rounded-xl p-1 overflow-x-auto scrollbar-hide">
              {['all', 'today', 'tomorrow', 'past'].map(p => (
                <button 
                  key={p}
                  onClick={() => setPeriod(p)}
                  className={`flex-1 min-w-[70px] py-2 text-[10px] font-bold rounded-lg transition-all capitalize ${period === p ? 'bg-white text-fuchsia-700 shadow-sm' : 'text-white/70 hover:bg-white/10 hover:text-white'}`}
                >
                  {p}
                </button>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Summary Stats */}
      <div className="grid grid-cols-2 gap-2">
        <div className="bg-white rounded-xl p-3 border border-fuchsia-100 shadow-sm flex items-center gap-2">
           <div className="p-1.5 bg-fuchsia-50 rounded-lg">
             <Code className="w-4 h-4 text-fuchsia-600" />
           </div>
           <div>
             <p className="text-base font-black text-fuchsia-600 leading-none mb-0.5">{units.length}</p>
             <p className="text-[8px] font-bold text-gray-500 uppercase tracking-widest leading-none">Coding</p>
           </div>
        </div>
        <div className="bg-white rounded-xl p-3 border border-blue-100 shadow-sm flex items-center gap-2">
           <div className="p-1.5 bg-blue-50 rounded-lg">
             <Calendar className="w-4 h-4 text-blue-600" />
           </div>
           <div>
             <p className="text-base font-black text-blue-600 leading-none mb-0.5">{countToday}</p>
             <p className="text-[8px] font-bold text-gray-500 uppercase tracking-widest leading-none">Today's Coding</p>
           </div>
        </div>
        <div className="bg-white rounded-xl p-3 border border-green-100 shadow-sm flex items-center gap-2">
           <div className="p-1.5 bg-green-50 rounded-lg">
             <CheckCircle className="w-4 h-4 text-green-600" />
           </div>
           <div>
             <p className="text-base font-black text-green-600 leading-none mb-0.5">{countTomorrow}</p>
             <p className="text-[8px] font-bold text-gray-500 uppercase tracking-widest leading-none">Tomorrow's Coding</p>
           </div>
        </div>
        <div className="bg-white rounded-xl p-3 border border-orange-100 shadow-sm flex items-center gap-2">
           <div className="p-1.5 bg-orange-50 rounded-lg">
             <AlertCircle className="w-4 h-4 text-orange-600" />
           </div>
           <div>
             <p className="text-base font-black text-orange-600 leading-none mb-0.5">{countPast}</p>
             <p className="text-[8px] font-bold text-gray-500 uppercase tracking-widest leading-none">Past Coding</p>
           </div>
        </div>
      </div>

      {/* Coding Units List */}
      <div className="grid grid-cols-1 gap-3 pb-8">
        {filteredUnits.length === 0 ? (
          <div className="text-center py-10 bg-gray-50/50 rounded-2xl border border-dashed border-gray-200">
            <div className="inline-flex p-3 bg-gray-100 rounded-full mb-3">
              <Code className="w-6 h-6 text-gray-400" />
            </div>
            <p className="text-sm font-bold text-gray-600 mb-1">No coding units found</p>
            <p className="text-[10px] text-gray-400">Try adjusting your search or date filter</p>
          </div>
        ) : (
          filteredUnits.map((u: any, i: number) => (
            <div key={i} className="bg-white rounded-2xl border-l-4 border-fuchsia-500 border-y border-r border-gray-100 shadow-sm overflow-hidden">
              <div className="p-4">
                <div className="flex justify-between items-start mb-3">
                  <div className="flex items-center gap-2">
                    <div className="p-1.5 bg-fuchsia-50 rounded-lg">
                      <Code className="w-4 h-4 text-fuchsia-600" />
                    </div>
                    <p className="text-base font-black text-gray-900 leading-none">{u.plate_number || 'N/A'}</p>
                  </div>
                  <div className="text-right">
                    <p className="text-sm font-bold text-fuchsia-600 leading-none mb-1">{u.coding_type || 'Coding'}</p>
                    <p className="text-[9px] font-bold text-gray-400">
                      {u.start_date ? u.start_date : (u.coding_day !== 'Unknown' ? 'Every ' + u.coding_day : 'No date')}
                    </p>
                  </div>
                </div>

                <div className="bg-gray-50 rounded-xl p-3 mb-3">
                  <div className="flex justify-between items-center mb-1">
                    <p className="text-[10px] font-bold text-gray-900">Status: {u.status || 'Unknown'}</p>
                    <p className="text-[9px] text-gray-500">{u.estimated_completion || 'Not specified'}</p>
                  </div>
                  <p className="text-[10px] text-gray-600"><span className="font-bold">Description:</span> {u.description || 'No description available'}</p>
                </div>

                <div className="flex justify-between items-center border-t border-gray-100 pt-3">
                  <div className="flex items-center gap-1">
                    <Calendar className="w-3 h-3 text-gray-400" />
                    <span className="text-[9px] font-bold text-gray-500">
                      {u.start_date ? u.start_date : (u.coding_day !== 'Unknown' ? 'Every ' + u.coding_day : 'No start date')}
                    </span>
                  </div>
                  <div className="flex items-center gap-1">
                    <CheckCircle className="w-3 h-3 text-gray-400" />
                    <span className="text-[9px] font-bold text-gray-500">{u.status || 'Unknown'}</span>
                  </div>
                </div>
              </div>
            </div>
          ))
        )}
      </div>
    </div>
  );
}

