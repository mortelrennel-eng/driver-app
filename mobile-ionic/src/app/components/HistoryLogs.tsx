import { useState, useEffect, useCallback } from "react";
import { Search, RefreshCw, History, Key, UserCheck, ShieldAlert, Loader2, Calendar } from "lucide-react";
import api from "../services/api";
import { toast } from "sonner";

interface AuditLog {
  id: number;
  user_name: string;
  user_email: string;
  user_role: string;
  ip_address: string;
  action: string;
  description: string;
  created_at: string;
}

export function HistoryLogs() {
  const [logs, setLogs] = useState<AuditLog[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState("");
  const [actionFilter, setActionFilter] = useState("");
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const url = `/super-admin/audit?search=${searchQuery}&action=${actionFilter}&page=${page}&per_page=20`;
      const res = await api.get(url);
      
      // Paginated return structure
      if (res.data.data) {
        setLogs(res.data.data);
        setTotalPages(res.data.last_page || 1);
      } else {
        setLogs(res.data);
      }
    } catch (err: any) {
      toast.error("Failed to load administrative audit logs.");
    } finally {
      setLoading(false);
    }
  }, [searchQuery, actionFilter, page]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const getActionBadge = (action: string) => {
    const act = action.toLowerCase();
    if (act.includes('login') && !act.includes('failed')) {
      return <span className="px-2 py-0.5 bg-green-50 text-green-600 rounded-md text-[9px] font-black uppercase tracking-wider flex items-center gap-1"><UserCheck className="w-2.5 h-2.5" /> Approved Login</span>;
    }
    if (act.includes('failed')) {
      return <span className="px-2 py-0.5 bg-red-50 text-red-600 rounded-md text-[9px] font-black uppercase tracking-wider flex items-center gap-1"><ShieldAlert className="w-2.5 h-2.5" /> Intrusion Risk</span>;
    }
    return <span className="px-2 py-0.5 bg-blue-50 text-blue-600 rounded-md text-[9px] font-black uppercase tracking-wider flex items-center gap-1"><History className="w-2.5 h-2.5" /> Action Logged</span>;
  };

  return (
    <div className="flex flex-col min-h-full bg-gray-50 pb-20">
      {/* Header */}
      <div className="bg-white px-4 pt-6 pb-4 border-b border-gray-200">
        <div className="flex items-center justify-between mb-4">
          <div>
            <h1 className="text-xl font-black text-gray-900 leading-tight">Administrative Logs</h1>
            <p className="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Real-time terminal audit trail</p>
          </div>
          <button onClick={fetchData} className="p-2.5 bg-gray-100 rounded-xl active:bg-gray-200 transition-colors">
            <RefreshCw className={`w-4 h-4 text-gray-500 ${loading ? 'animate-spin' : ''}`} />
          </button>
        </div>

        {/* Action Filter */}
        <div className="grid grid-cols-2 gap-2 mb-4">
          <select 
            value={actionFilter} 
            onChange={(e) => { setActionFilter(e.target.value); setPage(1); }}
            className="px-3.5 py-2.5 bg-gray-100 border-none rounded-xl text-xs font-black text-gray-700 focus:outline-none"
          >
            <option value="">All Actions</option>
            <option value="login">Successful Logins</option>
            <option value="failed_login">Failed Intrusion Attempts</option>
            <option value="logout">Logouts</option>
            <option value="approved">Account Approvals</option>
            <option value="rejected">Account Rejections</option>
          </select>

          <div className="relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input 
              type="text" 
              placeholder="Search user, IP..."
              className="w-full pl-9 pr-3 py-2.5 bg-gray-100 border-none rounded-xl text-xs font-bold text-gray-900 focus:outline-none"
              value={searchQuery}
              onChange={(e) => { setSearchQuery(e.target.value); setPage(1); }}
            />
          </div>
        </div>
      </div>

      {/* Log Feed */}
      <div className="flex-1 p-4">
        {loading ? (
          <div className="flex flex-col items-center justify-center py-20 gap-4">
            <Loader2 className="w-8 h-8 text-blue-500 animate-spin" />
            <p className="text-xs font-black text-gray-400 uppercase tracking-widest">Reading audit records...</p>
          </div>
        ) : logs.length === 0 ? (
          <div className="bg-white rounded-3xl p-10 text-center border border-dashed border-gray-200">
            <History className="w-12 h-12 text-gray-200 mx-auto mb-3" />
            <p className="text-sm font-black text-gray-400 uppercase tracking-widest">No audit trails recorded</p>
          </div>
        ) : (
          <div className="space-y-3.5">
            {logs.map((log) => (
              <div key={log.id} className="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-start gap-4">
                <div className="w-10 h-10 rounded-2xl bg-gray-50 border border-gray-100 shrink-0 flex items-center justify-center">
                  <Key className={`w-5 h-5 ${log.action === 'failed_login' ? 'text-red-500' : 'text-blue-500'}`} />
                </div>
                
                <div className="flex-1 min-w-0">
                  <div className="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                    <h3 className="text-xs font-black text-gray-900 truncate max-w-[150px]">{log.user_name || "Guest Intruder"}</h3>
                    {getActionBadge(log.action)}
                  </div>

                  <p className="text-[11px] font-bold text-gray-400 uppercase tracking-tight">{log.user_role || "External Visitor"} • IP: {log.ip_address}</p>
                  <p className="text-xs text-gray-600 font-medium mt-2 leading-relaxed bg-gray-50/50 p-3 rounded-2xl border border-gray-100/50">{log.description}</p>
                  
                  <div className="flex items-center gap-1 mt-2.5 text-gray-400">
                    <Calendar className="w-3 h-3" />
                    <span className="text-[9px] font-bold uppercase tracking-tight">{new Date(log.created_at).toLocaleString()}</span>
                  </div>
                </div>
              </div>
            ))}

            {/* Pagination Controls */}
            {totalPages > 1 && (
              <div className="flex items-center justify-between pt-4 pb-8">
                <button 
                  disabled={page === 1}
                  onClick={() => setPage(p => p - 1)}
                  className="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[10px] font-black uppercase text-gray-600 disabled:opacity-50"
                >
                  Previous
                </button>
                <span className="text-[10px] font-black text-gray-400 uppercase tracking-wider">Page {page} of {totalPages}</span>
                <button 
                  disabled={page === totalPages}
                  onClick={() => setPage(p => p + 1)}
                  className="px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-[10px] font-black uppercase text-gray-600 disabled:opacity-50"
                >
                  Next
                </button>
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
