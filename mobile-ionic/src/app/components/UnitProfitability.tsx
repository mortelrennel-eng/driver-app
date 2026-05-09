import { useState, useEffect, useCallback } from "react";
import { Search, RefreshCw, BarChart3, BrainCircuit, Calendar, ChevronRight, AlertCircle, Wrench, CreditCard, DollarSign, Loader2, X } from "lucide-react";
import api from "../services/api";
import { toast } from "sonner";

interface ProfitabilityUnit {
  id: number;
  plate_number: string;
  make: string;
  model: string;
  year: number;
  purchase_cost: number;
  boundary_rate: number;
  total_boundary: number;
  total_target_boundary: number;
  boundary_days: number;
  total_maintenance: number;
  maintenance_days: number;
  total_expenses: number;
  expense_days: number;
  net_income: number;
  profit_margin: number;
  roi_percentage: number;
  payback_period: number;
  roi_achieved: number;
}

interface DetailRecord {
  id: number;
  amount?: number;
  cost?: number;
  actual_boundary?: number;
  date?: string;
  date_started?: string;
  description?: string;
  notes?: string;
}

export function UnitProfitability() {
  const [profitability, setProfitability] = useState<ProfitabilityUnit[]>([]);
  const [overview, setOverview] = useState({
    total_boundary: 0,
    total_maintenance: 0,
    total_expenses: 0,
    net_income: 0,
    total_units: 0,
    avg_margin: 0
  });
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState("");
  const [dateFrom, setDateFrom] = useState(new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0]);
  const [dateTo, setDateTo] = useState(new Date().toISOString().split('T')[0]);

  // AI DSS report states
  const [aiAnalysis, setAiAnalysis] = useState<string | null>(null);
  const [aiLoading, setAiLoading] = useState(false);
  const [showAiModal, setShowAiModal] = useState(false);

  // Single Vehicle Breakdown States
  const [selectedUnit, setSelectedUnit] = useState<ProfitabilityUnit | null>(null);
  const [details, setDetails] = useState<{ boundaries: DetailRecord[]; maintenances: DetailRecord[]; expenses: DetailRecord[] } | null>(null);
  const [detailsLoading, setDetailsLoading] = useState(false);

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const res = await api.get(`/unit-profitability?date_from=${dateFrom}&date_to=${dateTo}`);
      if (res.data.success) {
        setProfitability(res.data.profitability);
        setOverview(res.data.overview);
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to load profitability metrics");
    } finally {
      setLoading(false);
    }
  }, [dateFrom, dateTo]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const handleGenerateAiDss = async () => {
    setAiLoading(true);
    setShowAiModal(true);
    try {
      const res = await api.get(`/unit-profitability/ai-dss?date_from=${dateFrom}&date_to=${dateTo}`);
      if (res.data.success) {
        setAiAnalysis(res.data.analysis);
      } else {
        toast.error(res.data.message || "AI generator failed");
      }
    } catch (err: any) {
      toast.error("AI service currently busy or offline. Please retry.");
    } finally {
      setAiLoading(false);
    }
  };

  const handleFetchDetails = async (unit: ProfitabilityUnit) => {
    setSelectedUnit(unit);
    setDetailsLoading(true);
    try {
      const res = await api.get(`/unit-profitability/details?unit_id=${unit.id}&date_from=${dateFrom}&date_to=${dateTo}`);
      if (res.data.success) {
        setDetails({
          boundaries: res.data.boundaries,
          maintenances: res.data.maintenances,
          expenses: res.data.expenses
        });
      }
    } catch (err: any) {
      toast.error("Failed to load unit details breakdown");
    } finally {
      setDetailsLoading(false);
    }
  };

  const filteredProfitability = profitability.filter(unit => 
    unit.plate_number.toLowerCase().includes(searchQuery.toLowerCase()) ||
    unit.make.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="flex flex-col min-h-full bg-gray-50 pb-20">
      {/* Header */}
      <div className="bg-white px-4 pt-6 pb-4 border-b border-gray-200">
        <div className="flex items-center justify-between mb-4">
          <div>
            <h1 className="text-xl font-black text-gray-900 leading-tight">Unit Profitability</h1>
            <p className="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Calculated ROI and Margin analysis</p>
          </div>
          <div className="flex gap-2">
            <button onClick={handleGenerateAiDss} className="flex items-center gap-1.5 px-4 py-2.5 bg-violet-600 text-white rounded-xl active:scale-95 transition-all shadow-md shadow-violet-200">
              <BrainCircuit className="w-4 h-4" />
              <span className="text-[10px] font-black uppercase tracking-wider">AI DSS</span>
            </button>
            <button onClick={fetchData} className="p-2.5 bg-gray-100 rounded-xl active:bg-gray-200 transition-colors">
              <RefreshCw className={`w-4 h-4 text-gray-500 ${loading ? 'animate-spin' : ''}`} />
            </button>
          </div>
        </div>

        {/* Dynamic Financial Overview Panel */}
        <div className="grid grid-cols-4 gap-2 mb-4 text-center">
          <div className="bg-green-50 border border-green-100 p-2 rounded-2xl">
            <p className="text-[8px] font-black uppercase text-green-500 tracking-wider">Revenue</p>
            <p className="text-[11px] font-black text-green-700 leading-none mt-1">₱{overview.total_boundary.toLocaleString()}</p>
          </div>
          <div className="bg-red-50 border border-red-100 p-2 rounded-2xl">
            <p className="text-[8px] font-black uppercase text-red-500 tracking-wider">Maintenance</p>
            <p className="text-[11px] font-black text-red-700 leading-none mt-1">₱{overview.total_maintenance.toLocaleString()}</p>
          </div>
          <div className="bg-amber-50 border border-amber-100 p-2 rounded-2xl">
            <p className="text-[8px] font-black uppercase text-amber-500 tracking-wider">Expenses</p>
            <p className="text-[11px] font-black text-amber-700 leading-none mt-1">₱{overview.total_expenses.toLocaleString()}</p>
          </div>
          <div className="bg-blue-50 border border-blue-100 p-2 rounded-2xl">
            <p className="text-[8px] font-black uppercase text-blue-500 tracking-wider">Net Income</p>
            <p className="text-[11px] font-black text-blue-700 leading-none mt-1">₱{overview.net_income.toLocaleString()}</p>
          </div>
        </div>

        {/* Date Filters */}
        <div className="grid grid-cols-2 gap-2 mb-4">
          <div className="flex flex-col">
            <label className="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Range From</label>
            <input 
              type="date"
              className="px-3 py-2 bg-gray-100 border-none rounded-xl text-xs font-black text-gray-700 focus:outline-none"
              value={dateFrom}
              onChange={(e) => setDateFrom(e.target.value)}
            />
          </div>
          <div className="flex flex-col">
            <label className="text-[8px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Range To</label>
            <input 
              type="date"
              className="px-3 py-2 bg-gray-100 border-none rounded-xl text-xs font-black text-gray-700 focus:outline-none"
              value={dateTo}
              onChange={(e) => setDateTo(e.target.value)}
            />
          </div>
        </div>

        {/* Search */}
        <div className="relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input 
            type="text" 
            placeholder="Search by plate number, manufacturer..."
            className="w-full pl-10 pr-4 py-3 bg-gray-100 border-none rounded-2xl text-sm font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>
      </div>

      {/* Plate Profitability Lists */}
      <div className="flex-1 p-4">
        {loading ? (
          <div className="flex flex-col items-center justify-center py-20 gap-4">
            <Loader2 className="w-8 h-8 text-blue-500 animate-spin" />
            <p className="text-xs font-black text-gray-400 uppercase tracking-widest">Loading profitability...</p>
          </div>
        ) : filteredProfitability.length === 0 ? (
          <div className="bg-white rounded-3xl p-10 text-center border border-dashed border-gray-200">
            <BarChart3 className="w-12 h-12 text-gray-200 mx-auto mb-3" />
            <p className="text-sm font-black text-gray-400 uppercase tracking-widest">No unit metrics recorded</p>
          </div>
        ) : (
          <div className="space-y-3">
            {filteredProfitability.map((unit) => (
              <div 
                key={unit.id} 
                onClick={() => handleFetchDetails(unit)}
                className="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm active:scale-[0.98] transition-all cursor-pointer flex items-center justify-between"
              >
                <div className="flex-1">
                  <div className="flex items-center gap-2 mb-2">
                    <span className="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-lg text-[9px] font-black uppercase tracking-wider">{unit.make} {unit.model}</span>
                    <span className={`px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider ${unit.net_income >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}`}>
                      {unit.profit_margin.toFixed(0)}% Margin
                    </span>
                  </div>

                  <h3 className="text-sm font-black text-gray-900 leading-none mb-3">{unit.plate_number}</h3>

                  <div className="grid grid-cols-3 gap-2 text-center bg-gray-50 p-2.5 rounded-xl">
                    <div>
                      <span className="text-[7px] font-black text-gray-400 uppercase tracking-wider">Boundary</span>
                      <p className="text-[10px] font-black text-gray-700">₱{(unit.total_boundary || 0).toLocaleString()}</p>
                    </div>
                    <div>
                      <span className="text-[7px] font-black text-gray-400 uppercase tracking-wider">Maintenance</span>
                      <p className="text-[10px] font-black text-red-500">₱{(unit.total_maintenance || 0).toLocaleString()}</p>
                    </div>
                    <div>
                      <span className="text-[7px] font-black text-gray-400 uppercase tracking-wider">Net Income</span>
                      <p className="text-[10px] font-black text-blue-600">₱{(unit.net_income || 0).toLocaleString()}</p>
                    </div>
                  </div>
                </div>
                <ChevronRight className="w-5 h-5 text-gray-300 ml-3" />
              </div>
            ))}
          </div>
        )}
      </div>

      {/* AI DSS Modal Drawer */}
      {showAiModal && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
          <div className="bg-white w-full max-w-sm rounded-[2rem] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200 flex flex-col max-h-[85vh]">
            <div className="bg-violet-600 p-5 flex flex-col items-center gap-2 text-white shrink-0">
              <div className="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center">
                <BrainCircuit className="w-5 h-5" />
              </div>
              <h3 className="font-black text-md text-center leading-tight">Tactical AI DSS Report</h3>
              <p className="text-violet-100 text-[9px] font-bold uppercase tracking-widest text-center">Powered by Google Gemini Fleet Analytics</p>
            </div>

            <div className="p-5 overflow-y-auto flex-1 prose prose-sm text-xs text-gray-600 font-medium">
              {aiLoading ? (
                <div className="flex flex-col items-center justify-center py-20 gap-3">
                  <Loader2 className="w-8 h-8 text-violet-500 animate-spin" />
                  <p className="text-xs font-black text-gray-400 uppercase tracking-widest text-center">Analyzing fleet spreadsheets...</p>
                </div>
              ) : aiAnalysis ? (
                <div className="space-y-3 whitespace-pre-line leading-relaxed" dangerouslySetInnerHTML={{ __html: aiAnalysis }} />
              ) : (
                <div className="text-center py-10">
                  <AlertCircle className="w-8 h-8 text-red-500 mx-auto mb-2" />
                  <p className="text-xs font-black text-gray-400 uppercase tracking-widest">Report Unavailable</p>
                </div>
              )}
            </div>

            <div className="p-5 border-t border-gray-100 shrink-0">
              <button 
                onClick={() => { setShowAiModal(false); setAiAnalysis(null); }}
                className="w-full py-4 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-colors active:scale-95"
              >
                Close Report
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Vehicle Breakdowns Drawer */}
      {selectedUnit && (
        <div className="fixed inset-0 z-[100] flex items-end justify-center bg-black/60 backdrop-blur-sm">
          <div className="bg-white w-full max-w-md rounded-t-[2.5rem] overflow-hidden shadow-2xl animate-in slide-in-from-bottom duration-300 flex flex-col max-h-[85vh]">
            <div className="p-6 border-b border-gray-100 flex items-center justify-between shrink-0">
              <div>
                <span className="text-[8px] font-black text-gray-400 uppercase tracking-widest">VEHICLE OPERATIONAL LEDGER</span>
                <h3 className="text-md font-black text-gray-900 mt-1">{selectedUnit.plate_number}</h3>
              </div>
              <button 
                onClick={() => { setSelectedUnit(null); setDetails(null); }}
                className="p-2 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl transition-all"
              >
                <X className="w-4 h-4" />
              </button>
            </div>

            <div className="p-6 overflow-y-auto flex-1 space-y-5">
              {detailsLoading ? (
                <div className="flex flex-col items-center justify-center py-16 gap-3">
                  <Loader2 className="w-6 h-6 text-blue-500 animate-spin" />
                  <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest">Consolidating lists...</p>
                </div>
              ) : details ? (
                <>
                  {/* Revenue boundaries breakdown */}
                  <div>
                    <h4 className="text-[10px] font-black text-green-600 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                      <DollarSign className="w-3.5 h-3.5" />
                      Rentals / Boundaries ({details.boundaries.length})
                    </h4>
                    {details.boundaries.length === 0 ? (
                      <p className="text-[10px] font-bold text-gray-400 uppercase ml-5">No boundary deposits recorded</p>
                    ) : (
                      <div className="space-y-2 ml-5">
                        {details.boundaries.map((b) => (
                          <div key={b.id} className="flex justify-between items-center text-xs py-1 border-b border-gray-100">
                            <span className="font-bold text-gray-500">{b.date}</span>
                            <span className="font-black text-green-600">₱{(b.actual_boundary || 0).toLocaleString()}</span>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>

                  {/* Maintenance items breakdown */}
                  <div className="pt-4 border-t border-gray-100">
                    <h4 className="text-[10px] font-black text-red-600 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                      <Wrench className="w-3.5 h-3.5" />
                      Maintenance logs ({details.maintenances.length})
                    </h4>
                    {details.maintenances.length === 0 ? (
                      <p className="text-[10px] font-bold text-gray-400 uppercase ml-5">No maintenance logged</p>
                    ) : (
                      <div className="space-y-2.5 ml-5">
                        {details.maintenances.map((m) => (
                          <div key={m.id} className="flex justify-between items-start text-xs py-1 border-b border-gray-100">
                            <div>
                              <p className="font-black text-gray-700">{m.description || "Routine Maintenance"}</p>
                              <span className="text-[9px] font-bold text-gray-400 uppercase">{m.date_started}</span>
                            </div>
                            <span className="font-black text-red-600">₱{(m.cost || 0).toLocaleString()}</span>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>

                  {/* General expenses breakdown */}
                  <div className="pt-4 border-t border-gray-100">
                    <h4 className="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                      <CreditCard className="w-3.5 h-3.5" />
                      Other Expenses ({details.expenses.length})
                    </h4>
                    {details.expenses.length === 0 ? (
                      <p className="text-[10px] font-bold text-gray-400 uppercase ml-5">No general expenses recorded</p>
                    ) : (
                      <div className="space-y-2.5 ml-5">
                        {details.expenses.map((e) => (
                          <div key={e.id} className="flex justify-between items-start text-xs py-1 border-b border-gray-100">
                            <div>
                              <p className="font-black text-gray-700">{e.description}</p>
                              <span className="text-[9px] font-bold text-gray-400 uppercase">{e.date}</span>
                            </div>
                            <span className="font-black text-amber-600">₱{(e.amount || 0).toLocaleString()}</span>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                </>
              ) : null}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
