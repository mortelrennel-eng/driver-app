import { useState, useEffect, useCallback } from "react";
import { Search, RefreshCw, Briefcase, Plus, Calendar, ShieldCheck, ShieldX, User, Edit2, Archive, Loader2, Save, X, Eye } from "lucide-react";
import api from "../services/api";
import { toast } from "sonner";

interface FranchiseUnit {
  id?: number;
  make: string;
  motor_no: string;
  chasis_no: string;
  plate_no: string;
  year_model: string;
}

interface FranchiseCase {
  id: number;
  applicant_name: string;
  case_no: string;
  type_of_application: string;
  denomination: string;
  date_filed: string;
  expiry_date: string;
  status: string;
  unit_count: number;
  units?: FranchiseUnit[];
}

export function Franchise() {
  const [cases, setCases] = useState<FranchiseCase[]>([]);
  const [stats, setStats] = useState({
    total_cases: 0,
    expiring_soon: 0,
    expired: 0,
    pending: 0,
    approved: 0,
    rejected: 0
  });
  const [pagination, setPagination] = useState({ current_page: 1, total_pages: 1 });
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState("");
  const [page, setPage] = useState(1);
  const [activeTab, setActiveTab] = useState("all"); // all, pending, approved, rejected

  // Form states
  const [showFormModal, setShowFormModal] = useState(false);
  const [editingCase, setEditingCase] = useState<FranchiseCase | null>(null);
  const [formLoading, setFormLoading] = useState(false);

  const [applicantName, setApplicantName] = useState("");
  const [caseNo, setCaseNo] = useState("");
  const [typeOfApplication, setTypeOfApplication] = useState("");
  const [denomination, setDenomination] = useState("");
  const [dateFiled, setDateFiled] = useState("");
  const [expiryDate, setExpiryDate] = useState("");
  
  // Dynamic Franchise units in form
  const [formUnits, setFormUnits] = useState<FranchiseUnit[]>([
    { make: "", motor_no: "", chasis_no: "", plate_no: "", year_model: "" }
  ]);

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const res = await api.get(`/franchise?search=${searchQuery}&page=${page}`);
      if (res.data.success) {
        setCases(res.data.data);
        setStats(res.data.stats);
        setPagination(res.data.pagination);
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to load franchise data");
    } finally {
      setLoading(false);
    }
  }, [searchQuery, page]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const handleOpenCreateModal = () => {
    setEditingCase(null);
    setApplicantName("");
    setCaseNo("");
    setTypeOfApplication("");
    setDenomination("");
    setDateFiled(new Date().toISOString().split('T')[0]);
    setExpiryDate("");
    setFormUnits([{ make: "", motor_no: "", chasis_no: "", plate_no: "", year_model: "" }]);
    setShowFormModal(true);
  };

  const handleOpenEditModal = (c: FranchiseCase) => {
    setEditingCase(c);
    setApplicantName(c.applicant_name);
    setCaseNo(c.case_no);
    setTypeOfApplication(c.type_of_application);
    setDenomination(c.denomination);
    setDateFiled(c.date_filed);
    setExpiryDate(c.expiry_date);
    setFormUnits(c.units && c.units.length > 0 ? [...c.units] : [{ make: "", motor_no: "", chasis_no: "", plate_no: "", year_model: "" }]);
    setShowFormModal(true);
  };

  const handleUnitFormChange = (index: number, field: keyof FranchiseUnit, value: string) => {
    const updated = [...formUnits];
    updated[index] = { ...updated[index], [field]: value };
    setFormUnits(updated);
  };

  const handleAddUnitToForm = () => {
    setFormUnits([...formUnits, { make: "", motor_no: "", chasis_no: "", plate_no: "", year_model: "" }]);
  };

  const handleRemoveUnitFromForm = (index: number) => {
    if (formUnits.length === 1) return;
    setFormUnits(formUnits.filter((_, i) => i !== index));
  };

  const handleSubmitForm = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormLoading(true);
    try {
      const payload = {
        applicant_name: applicantName,
        case_no: caseNo,
        type_of_application: typeOfApplication,
        denomination: denomination,
        date_filed: dateFiled,
        expiry_date: expiryDate,
        units: formUnits.filter(u => u.plate_no || u.motor_no)
      };

      let res;
      if (editingCase) {
        res = await api.put(`/franchise/${editingCase.id}`, payload);
      } else {
        res = await api.post("/franchise", payload);
      }

      if (res.data.success) {
        toast.success(res.data.message || "Saved successfully");
        setShowFormModal(false);
        fetchData();
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to save franchise case. Please resolve inputs.");
    } finally {
      setFormLoading(false);
    }
  };

  const handleApprove = async (id: number) => {
    if (!window.confirm("Approve this case?")) return;
    try {
      const res = await api.post(`/franchise/${id}/approve`);
      if (res.data.success) {
        toast.success("Franchise approved successfully");
        fetchData();
      }
    } catch (err: any) {
      toast.error("Error approving case");
    }
  };

  const handleReject = async (id: number) => {
    if (!window.confirm("Reject this case?")) return;
    try {
      const res = await api.post(`/franchise/${id}/reject`);
      if (res.data.success) {
        toast.warning("Franchise case rejected");
        fetchData();
      }
    } catch (err: any) {
      toast.error("Error rejecting case");
    }
  };

  const handleArchive = async (id: number) => {
    if (!window.confirm("Move this franchise case to Archive?")) return;
    try {
      const res = await api.delete(`/franchise/${id}`);
      if (res.data.success) {
        toast.success("Franchise case moved to Archive");
        fetchData();
      }
    } catch (err: any) {
      toast.error("Failed to archive");
    }
  };

  const filteredCases = cases.filter(c => {
    if (activeTab === "all") return true;
    return c.status === activeTab;
  });

  return (
    <div className="flex flex-col min-h-full bg-gray-50 pb-20">
      {/* Header */}
      <div className="bg-white px-4 pt-6 pb-4 border-b border-gray-200">
        <div className="flex items-center justify-between mb-4">
          <div>
            <h1 className="text-xl font-black text-gray-900 leading-tight">Franchise Cases</h1>
            <p className="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Decision support and LTFRB logs</p>
          </div>
          <div className="flex gap-2">
            <button onClick={handleOpenCreateModal} className="flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white rounded-xl active:scale-95 transition-all shadow-md shadow-blue-200">
              <Plus className="w-4 h-4" />
              <span className="text-[10px] font-black uppercase tracking-wider">New Case</span>
            </button>
            <button onClick={fetchData} className="p-2.5 bg-gray-100 rounded-xl active:bg-gray-200 transition-colors">
              <RefreshCw className={`w-4 h-4 text-gray-500 ${loading ? 'animate-spin' : ''}`} />
            </button>
          </div>
        </div>

        {/* Dynamic Horizontal Overview */}
        <div className="grid grid-cols-4 gap-2 mb-4">
          <div className="bg-blue-50 border border-blue-100 p-2.5 rounded-2xl">
            <p className="text-[8px] font-black uppercase text-blue-500 tracking-wider">Total Cases</p>
            <p className="text-sm font-black text-blue-700 leading-none mt-1">{stats.total_cases}</p>
          </div>
          <div className="bg-amber-50 border border-amber-100 p-2.5 rounded-2xl">
            <p className="text-[8px] font-black uppercase text-amber-500 tracking-wider">Expiring</p>
            <p className="text-sm font-black text-amber-700 leading-none mt-1">{stats.expiring_soon}</p>
          </div>
          <div className="bg-red-50 border border-red-100 p-2.5 rounded-2xl">
            <p className="text-[8px] font-black uppercase text-red-500 tracking-wider">Expired</p>
            <p className="text-sm font-black text-red-700 leading-none mt-1">{stats.expired}</p>
          </div>
          <div className="bg-gray-50 border border-gray-100 p-2.5 rounded-2xl">
            <p className="text-[8px] font-black uppercase text-gray-500 tracking-wider">Pending</p>
            <p className="text-sm font-black text-gray-700 leading-none mt-1">{stats.pending}</p>
          </div>
        </div>

        {/* Search */}
        <div className="relative mb-4">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input 
            type="text" 
            placeholder="Search by case, applicant, type..."
            className="w-full pl-10 pr-4 py-3 bg-gray-100 border-none rounded-2xl text-sm font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>

        {/* Tabs */}
        <div className="flex bg-gray-100 p-1 rounded-2xl">
          {["all", "pending", "approved", "rejected"].map((tab) => (
            <button
              key={tab}
              onClick={() => { setActiveTab(tab); setPage(1); }}
              className={`flex-1 py-3 text-center rounded-xl text-[10px] font-black uppercase tracking-wider transition-all ${activeTab === tab ? "bg-white text-blue-600 shadow-sm" : "text-gray-500"}`}
            >
              {tab}
            </button>
          ))}
        </div>
      </div>

      {/* Case List */}
      <div className="flex-1 p-4">
        {loading ? (
          <div className="flex flex-col items-center justify-center py-20 gap-4">
            <Loader2 className="w-8 h-8 text-blue-500 animate-spin" />
            <p className="text-xs font-black text-gray-400 uppercase tracking-widest">Loading cases...</p>
          </div>
        ) : filteredCases.length === 0 ? (
          <div className="bg-white rounded-3xl p-10 text-center border border-dashed border-gray-200">
            <Briefcase className="w-12 h-12 text-gray-200 mx-auto mb-3" />
            <p className="text-sm font-black text-gray-400 uppercase tracking-widest">No cases found</p>
          </div>
        ) : (
          <div className="space-y-3">
            {filteredCases.map((c) => (
              <div key={c.id} className="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
                <div className="flex items-center justify-between mb-3">
                  <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest">CASE NO. {c.case_no}</span>
                  <span className={`px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider ${
                    c.status === 'approved' ? 'bg-green-50 text-green-600' :
                    c.status === 'rejected' ? 'bg-red-50 text-red-600' :
                    'bg-amber-50 text-amber-600'
                  }`}>{c.status}</span>
                </div>

                <h3 className="text-sm font-black text-gray-900 leading-tight mb-1">{c.applicant_name}</h3>
                <p className="text-[10px] font-bold text-gray-400 uppercase mb-3">{c.type_of_application} • {c.denomination}</p>

                <div className="grid grid-cols-2 gap-3 mb-4 bg-gray-50 p-3 rounded-2xl">
                  <div>
                    <span className="text-[8px] font-black text-gray-400 uppercase tracking-wider">Date Filed</span>
                    <p className="text-[11px] font-black text-gray-700">{c.date_filed}</p>
                  </div>
                  <div>
                    <span className="text-[8px] font-black text-gray-400 uppercase tracking-wider">Expiry Date</span>
                    <p className="text-[11px] font-black text-red-500">{c.expiry_date}</p>
                  </div>
                </div>

                {/* Sub units included */}
                {c.unit_count > 0 && (
                  <div className="mb-4">
                    <span className="text-[8px] font-black text-gray-400 uppercase tracking-wider">Registered Units ({c.unit_count})</span>
                    <div className="flex flex-wrap gap-1.5 mt-1.5">
                      {c.units?.map((u, i) => (
                        <span key={i} className="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-[9px] font-bold">
                          {u.plate_no} ({u.make})
                        </span>
                      ))}
                    </div>
                  </div>
                )}

                <div className="flex gap-2">
                  {c.status === 'pending' && (
                    <>
                      <button 
                        onClick={() => handleApprove(c.id)}
                        className="flex-1 flex items-center justify-center gap-1.5 py-3.5 bg-green-50 text-green-600 rounded-2xl text-[9px] font-black uppercase tracking-wider active:scale-95 transition-all"
                      >
                        <ShieldCheck className="w-3.5 h-3.5" />
                        Approve
                      </button>
                      <button 
                        onClick={() => handleReject(c.id)}
                        className="flex-1 flex items-center justify-center gap-1.5 py-3.5 bg-red-50 text-red-600 rounded-2xl text-[9px] font-black uppercase tracking-wider active:scale-95 transition-all"
                      >
                        <ShieldX className="w-3.5 h-3.5" />
                        Reject
                      </button>
                    </>
                  )}
                  <button 
                    onClick={() => handleOpenEditModal(c)}
                    className="p-3.5 bg-gray-50 text-gray-500 rounded-2xl hover:bg-gray-100 active:scale-95 transition-all"
                  >
                    <Edit2 className="w-3.5 h-3.5" />
                  </button>
                  <button 
                    onClick={() => handleArchive(c.id)}
                    className="p-3.5 bg-gray-50 text-gray-400 hover:text-red-500 rounded-2xl hover:bg-gray-100 active:scale-95 transition-all"
                  >
                    <Archive className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Dynamic Creation / Edit Modal Form */}
      {showFormModal && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
          <div className="bg-white w-full max-w-sm rounded-[2rem] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200 flex flex-col max-h-[85vh]">
            <div className="bg-blue-600 p-5 flex flex-col items-center gap-2 text-white shrink-0">
              <h3 className="font-black text-md text-center leading-tight">
                {editingCase ? "Modify Franchise Case" : "Register Franchise Case"}
              </h3>
              <p className="text-blue-100 text-[9px] font-bold uppercase tracking-widest text-center">LTFRB Legal Filing Dashboard</p>
            </div>

            <form onSubmit={handleSubmitForm} className="p-5 space-y-4 overflow-y-auto flex-1">
              <div>
                <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Applicant Name</label>
                <input 
                  type="text"
                  required
                  placeholder="Enter full legal name..."
                  className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                  value={applicantName}
                  onChange={(e) => setApplicantName(e.target.value)}
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Case Number</label>
                  <input 
                    type="text"
                    required
                    placeholder="Numbers only..."
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={caseNo}
                    onChange={(e) => setCaseNo(e.target.value)}
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Application Type</label>
                  <input 
                    type="text"
                    required
                    placeholder="e.g. Sale & Transfer"
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={typeOfApplication}
                    onChange={(e) => setTypeOfApplication(e.target.value)}
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Date Filed</label>
                  <input 
                    type="date"
                    required
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={dateFiled}
                    onChange={(e) => setDateFiled(e.target.value)}
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Expiry Date</label>
                  <input 
                    type="date"
                    required
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={expiryDate}
                    onChange={(e) => setExpiryDate(e.target.value)}
                  />
                </div>
              </div>

              <div>
                <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Denomination</label>
                <input 
                  type="text"
                  required
                  placeholder="e.g. TAXI CAB"
                  className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                  value={denomination}
                  onChange={(e) => setDenomination(e.target.value)}
                />
              </div>

              {/* Dynamic Units segment inside modal */}
              <div className="pt-2 border-t border-gray-100">
                <div className="flex items-center justify-between mb-3">
                  <h4 className="text-[10px] font-black text-gray-400 uppercase tracking-widest">Case Vehicles</h4>
                  <button type="button" onClick={handleAddUnitToForm} className="text-[9px] font-black uppercase text-blue-600 hover:underline">
                    + Add Unit
                  </button>
                </div>

                <div className="space-y-3.5">
                  {formUnits.map((u, i) => (
                    <div key={i} className="p-3 bg-gray-50 border border-gray-100 rounded-2xl space-y-2 relative">
                      {formUnits.length > 1 && (
                        <button type="button" onClick={() => handleRemoveUnitFromForm(i)} className="absolute right-2 top-2 p-1 text-gray-400 hover:text-red-500">
                          <X className="w-3.5 h-3.5" />
                        </button>
                      )}
                      <div className="grid grid-cols-2 gap-2">
                        <input 
                          type="text" 
                          placeholder="Make (e.g. TOYOTA)" 
                          className="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-[11px] font-bold" 
                          value={u.make}
                          onChange={(e) => handleUnitFormChange(i, "make", e.target.value)}
                        />
                        <input 
                          type="text" 
                          placeholder="Plate (e.g. XYZ123)" 
                          className="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-[11px] font-bold uppercase" 
                          value={u.plate_no}
                          onChange={(e) => handleUnitFormChange(i, "plate_no", e.target.value)}
                        />
                      </div>
                      <div className="grid grid-cols-2 gap-2">
                        <input 
                          type="text" 
                          placeholder="Engine/Motor No" 
                          className="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-[10px] font-bold uppercase" 
                          value={u.motor_no}
                          onChange={(e) => handleUnitFormChange(i, "motor_no", e.target.value)}
                        />
                        <input 
                          type="text" 
                          placeholder="Year (e.g. 2024)" 
                          className="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl text-[10px] font-bold" 
                          value={u.year_model}
                          onChange={(e) => handleUnitFormChange(i, "year_model", e.target.value)}
                        />
                      </div>
                    </div>
                  ))}
                </div>
              </div>

              <div className="flex gap-3 pt-3 shrink-0">
                <button 
                  type="button"
                  onClick={() => setShowFormModal(false)}
                  className="flex-1 py-4 border-2 border-gray-100 rounded-2xl text-[10px] font-black text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button 
                  type="submit"
                  disabled={formLoading}
                  className="flex-1 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-200 active:scale-95 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                >
                  {formLoading ? <Loader2 className="w-3.5 h-3.5 animate-spin" /> : <Save className="w-3.5 h-3.5" />}
                  Save Case
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
