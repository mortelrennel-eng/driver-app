import { useState, useEffect, useCallback } from "react";
import { Search, RefreshCw, RotateCcw, Trash2, ShieldAlert, X, Loader2, Database, Users, CreditCard, Wrench, Calendar, UserCheck, AlertTriangle, Settings, Truck } from "lucide-react";
import api from "../services/api";
import { toast } from "sonner";

interface ArchivedItem {
  id: number;
  name: string;
  archived_at: string;
}

export function Archive() {
  const [data, setData] = useState<Record<string, ArchivedItem[]>>({});
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState("units");
  const [searchQuery, setSearchQuery] = useState("");
  const [showDeleteModal, setShowDeleteModal] = useState<{ type: string; id: number; name: string } | null>(null);
  const [deletePassword, setDeletePassword] = useState("");
  const [deleting, setDeleting] = useState(false);

  const tabs = [
    { id: "units", label: "Units", icon: <Truck className="w-4 h-4" /> },
    { id: "drivers", label: "Drivers", icon: <Users className="w-4 h-4" /> },
    { id: "expenses", label: "Expenses", icon: <CreditCard className="w-4 h-4" /> },
    { id: "maintenance", label: "Maintenance", icon: <Wrench className="w-4 h-4" /> },
    { id: "boundaries", label: "Boundaries", icon: <Calendar className="w-4 h-4" /> },
    { id: "staff", label: "Staff", icon: <UserCheck className="w-4 h-4" /> },
    { id: "incidents", label: "Incidents", icon: <AlertTriangle className="w-4 h-4" /> },
    { id: "pricing_rules", label: "Pricing Rules", icon: <Settings className="w-4 h-4" /> },
    { id: "suppliers", label: "Suppliers", icon: <Database className="w-4 h-4" /> },
  ];

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const res = await api.get("/archive");
      if (res.data.success) {
        setData(res.data.data);
      }
    } catch (err: any) {
      const msg = err.response?.data?.message || err.message || "Failed to load archive data";
      toast.error(msg);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const handleRestore = async (type: string, id: number, name: string) => {
    if (!window.confirm(`Are you sure you want to restore ${name}?`)) return;
    try {
      const res = await api.post(`/archive/restore/${type}/${id}`);
      if (res.data.success) {
        toast.success(`${name} restored successfully`);
        fetchData();
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to restore item");
    }
  };

  const handlePermanentDelete = async () => {
    if (!showDeleteModal) return;
    setDeleting(true);
    try {
      const res = await api.post(`/archive/delete/${showDeleteModal.type}/${showDeleteModal.id}`, { password: deletePassword });
      if (res.data.success) {
        toast.success(`${showDeleteModal.name} permanently deleted`);
        setShowDeleteModal(null);
        setDeletePassword("");
        fetchData();
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to delete item. Please check your password.");
    } finally {
      setDeleting(false);
    }
  };

  const currentItems = data[activeTab] || [];
  const filteredItems = currentItems.filter(item => 
    item.name.toLowerCase().includes(searchQuery.toLowerCase())
  );

  return (
    <div className="flex flex-col min-h-full bg-gray-50 pb-20">
      {/* Header */}
      <div className="bg-white px-4 pt-6 pb-4 border-b border-gray-200">
        <div className="flex items-center justify-between mb-4">
          <div>
            <h1 className="text-xl font-black text-gray-900 leading-tight">Archive Management</h1>
            <p className="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Restore or permanently wipe records</p>
          </div>
          <button onClick={fetchData} className="p-2 bg-gray-100 rounded-xl active:bg-gray-200 transition-colors">
            <RefreshCw className={`w-4 h-4 text-gray-500 ${loading ? 'animate-spin' : ''}`} />
          </button>
        </div>

        {/* Search */}
        <div className="relative mb-4">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input 
            type="text" 
            placeholder={`Search archived ${activeTab}...`}
            className="w-full pl-10 pr-4 py-3 bg-gray-100 border-none rounded-2xl text-sm font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>

        {/* Tabs - Horizontal Scrollable */}
        <div className="flex overflow-x-auto no-scrollbar gap-2 pb-1">
          {tabs.map((tab) => {
            const count = data[tab.id]?.length || 0;
            const isActive = activeTab === tab.id;
            return (
              <button
                key={tab.id}
                onClick={() => { setActiveTab(tab.id); setSearchQuery(""); }}
                className={`flex items-center gap-2 px-4 py-2.5 rounded-xl whitespace-nowrap transition-all ${
                  isActive 
                  ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' 
                  : 'bg-white border border-gray-100 text-gray-500 hover:bg-gray-50'
                }`}
              >
                {tab.icon}
                <span className="text-[11px] font-black uppercase tracking-wider">{tab.label}</span>
                <span className={`text-[10px] font-black px-1.5 py-0.5 rounded-md ${isActive ? 'bg-blue-500/50 text-white' : 'bg-gray-100 text-gray-400'}`}>
                  {count}
                </span>
              </button>
            );
          })}
        </div>
      </div>

      {/* Content List */}
      <div className="flex-1 p-4">
        {loading ? (
          <div className="flex flex-col items-center justify-center py-20 gap-4">
            <Loader2 className="w-8 h-8 text-blue-500 animate-spin" />
            <p className="text-xs font-black text-gray-400 uppercase tracking-widest">Loading archive data...</p>
          </div>
        ) : filteredItems.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-20 bg-white rounded-3xl border-2 border-dashed border-gray-200">
            <Database className="w-12 h-12 text-gray-200 mb-4" />
            <p className="text-sm font-black text-gray-400 uppercase tracking-widest">No archived items found</p>
          </div>
        ) : (
          <div className="space-y-3">
            {filteredItems.map((item) => (
              <div key={item.id} className="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm animate-in fade-in slide-in-from-bottom-2 duration-300">
                <div className="flex items-start justify-between mb-4">
                  <div className="flex-1">
                    <p className="text-sm font-black text-gray-900 leading-tight mb-1">{item.name}</p>
                    <div className="flex items-center gap-1.5 text-gray-400">
                      <Calendar className="w-3 h-3" />
                      <span className="text-[10px] font-bold uppercase tracking-tight">Archived: {item.archived_at}</span>
                    </div>
                  </div>
                </div>

                <div className="flex gap-2">
                  <button 
                    onClick={() => handleRestore(activeTab, item.id, item.name)}
                    className="flex-1 flex items-center justify-center gap-2 py-3 bg-blue-50 text-blue-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-100 transition-colors active:scale-95"
                  >
                    <RotateCcw className="w-3 h-3" />
                    Restore
                  </button>
                  <button 
                    onClick={() => setShowDeleteModal({ type: activeTab, id: item.id, name: item.name })}
                    className="flex-1 flex items-center justify-center gap-2 py-3 bg-red-50 text-red-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-red-100 transition-colors active:scale-95"
                  >
                    <Trash2 className="w-3 h-3" />
                    Delete Permanently
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Permanent Delete Modal */}
      {showDeleteModal && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
          <div className="bg-white w-full max-w-sm rounded-[2rem] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
            <div className="bg-red-600 p-6 flex flex-col items-center gap-3">
              <div className="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-md">
                <ShieldAlert className="w-6 h-6 text-white" />
              </div>
              <h3 className="text-white font-black text-lg text-center leading-tight">Critical Security Check</h3>
              <p className="text-red-100 text-[10px] font-bold text-center uppercase tracking-widest">This action is irreversible and will permanently wipe the record.</p>
            </div>
            
            <div className="p-6 space-y-4">
              <div>
                <p className="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Target Item</p>
                <div className="bg-gray-50 border border-gray-100 p-3 rounded-2xl">
                  <p className="text-xs font-black text-gray-900">{showDeleteModal.name}</p>
                </div>
              </div>

              <div>
                <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Archive Password</label>
                <input 
                  type="password"
                  autoFocus
                  placeholder="Enter archive password..."
                  className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-sm font-black focus:outline-none focus:border-red-500 transition-all"
                  value={deletePassword}
                  onChange={(e) => setDeletePassword(e.target.value)}
                />
              </div>

              <div className="flex gap-3 pt-2">
                <button 
                  onClick={() => { setShowDeleteModal(null); setDeletePassword(""); }}
                  className="flex-1 py-4 border-2 border-gray-100 rounded-2xl text-[11px] font-black text-gray-500 uppercase tracking-widest hover:bg-gray-50 transition-colors"
                >
                  Cancel
                </button>
                <button 
                  disabled={!deletePassword || deleting}
                  onClick={handlePermanentDelete}
                  className="flex-1 py-4 bg-red-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest shadow-lg shadow-red-200 active:scale-95 transition-all disabled:opacity-50 flex items-center justify-center gap-2"
                >
                  {deleting ? <Loader2 className="w-3 h-3 animate-spin" /> : <Trash2 className="w-3 h-3" />}
                  Wipe Data
                </button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
