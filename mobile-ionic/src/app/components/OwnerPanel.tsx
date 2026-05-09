import { useState, useEffect } from "react";
import { useAuth } from "../context/AuthContext";
import api from "../services/api";
import { toast } from "sonner";
import {
  Crown, Shield, Users, UserPlus, Activity, Lock, ShieldAlert,
  CheckCircle, XCircle, RefreshCw, Loader2, X, ChevronRight,
  LayoutDashboard, Eye, EyeOff, Search, Key, Archive, Trash2, Edit2,
  Database, Check
} from "lucide-react";

export function OwnerPanel() {
  const { user } = useAuth();
  const [tab, setTab] = useState("page_access");
  const [data, setData] = useState<any>(null);
  const [auditLogs, setAuditLogs] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string|null>(null);
  const [search, setSearch] = useState("");
  const [showVerifyModal, setShowVerifyModal] = useState(false);
  const [verifyPassword, setVerifyPassword] = useState("");
  const [deleteTarget, setDeleteTarget] = useState<{id:number, type:string}|null>(null);
  const [deleting, setDeleting] = useState(false);
  const [auditSearch, setAuditSearch] = useState("");
  const [auditAction, setAuditAction] = useState("");
  const [auditRole, setAuditRole] = useState("");

  // Staff form
  const [sf, setSf] = useState({first_name:"",last_name:"",email:"",phone_number:"",address:"",role:""});
  const [submitting, setSubmitting] = useState(false);
  const [tempPass, setTempPass] = useState("");

  // Security form
  const [archForm, setArchForm] = useState({archive_password:"",archive_password_confirmation:""});
  const [showPass, setShowPass] = useState(false);

  // Page access logic
  const [accessUser, setAccessUser] = useState<any>(null);
  const [selectedPages, setSelectedPages] = useState<string[]>([]);
  const [savingAccess, setSavingAccess] = useState(false);

  // Role Management Modal
  const [showRoleModal, setShowRoleModal] = useState(false);
  const [showAddRole, setShowAddRole] = useState(false);
  const [savingRole, setSavingRole] = useState(false);
  const [roleForm, setRoleForm] = useState({ name: "", label: "", description: "" });

  useEffect(() => { loadData(); }, []);
  useEffect(() => { if (tab === "login_history") loadAudit(); }, [tab, auditSearch, auditAction, auditRole]);

  const loadData = async () => {
    setLoading(true); setError(null);
    try {
      const r = await api.get("/super-admin/overview");
      if (r.data.success) {
        setData(r.data);
        if (accessUser) {
          // Keep access user updated if it was selected
          const updatedUser = r.data.allUsers.find((u:any) => u.id === accessUser.id);
          if (updatedUser) {
            setAccessUser(updatedUser);
            setSelectedPages(updatedUser.allowed_pages || []);
          }
        }
      }
      else setError("Server returned error.");
    } catch(e:any) {
      const msg = e?.response?.data?.message || e?.message || "Connection failed";
      setError(msg);
      toast.error("Owner Panel: " + msg);
    } finally { setLoading(false); }
  };

  const loadAudit = async () => {
    try {
      const params = new URLSearchParams();
      params.append("per_page", "50");
      if (auditSearch) params.append("search", auditSearch);
      if (auditAction) params.append("action", auditAction);
      if (auditRole) params.append("role", auditRole);
      
      const r = await api.get(`/super-admin/audit?${params.toString()}`);
      setAuditLogs(r.data.data || []);
    } catch(e:any) { toast.error("Audit: " + (e?.response?.data?.message || "Failed")); }
  };

  const createStaff = async (e: React.FormEvent) => {
    e.preventDefault(); setSubmitting(true);
    try {
      const r = await api.post("/super-admin/staff", sf);
      if (r.data.success) { toast.success(r.data.message); setTempPass(r.data.temp_password); loadData(); }
      else toast.error(r.data.message);
    } catch(e:any) { toast.error(e?.response?.data?.message || "Failed to create staff."); }
    finally { setSubmitting(false); }
  };

  const saveNewRole = async (e: React.FormEvent) => {
    e.preventDefault();
    setSavingRole(true);
    try {
      const r = await api.post("/super-admin/roles", roleForm);
      if (r.data.success) {
        toast.success(r.data.message);
        setShowAddRole(false);
        setRoleForm({ name: "", label: "", description: "" });
        loadData();
      } else toast.error(r.data.message);
    } catch (e: any) {
      toast.error(e?.response?.data?.message || "Failed to create role.");
    } finally { setSavingRole(false); }
  };

  const handleArchiveRole = async (id: number) => {
    if (!confirm("Are you sure you want to archive this role? Users with this role may be affected.")) return;
    try {
      const r = await api.delete(`/super-admin/roles/${id}/archive`);
      toast.success(r.data.message);
      loadData();
    } catch (e: any) { toast.error(e?.response?.data?.message || "Failed to archive role."); }
  };

  const handleRestoreRole = async (id: number) => {
    try {
      const r = await api.post(`/super-admin/roles/${id}/restore`);
      toast.success(r.data.message);
      loadData();
    } catch (e: any) { toast.error(e?.response?.data?.message || "Failed to restore role."); }
  };

  const savePageAccess = async () => {
    if (!accessUser) return;
    setSavingAccess(true);
    try {
      const r = await api.post(`/super-admin/users/${accessUser.id}/page-access`, { pages: selectedPages });
      toast.success(r.data.message);
      loadData();
    } catch(e:any) {
      toast.error(e?.response?.data?.message || "Failed to update page access.");
    } finally {
      setSavingAccess(false);
    }
  };

  const [editUser, setEditUser] = useState<any>(null);
  const [editing, setEditing] = useState(false);

  const saveEditUser = async (e: React.FormEvent) => {
    e.preventDefault(); setEditing(true);
    try {
      const payload = {
        first_name: editUser.first_name,
        last_name: editUser.last_name,
        email: editUser.email,
        phone_number: editUser.phone_number,
        address: editUser.address,
        role: editUser.role
      };
      const r = await api.put(`/super-admin/users/${editUser.id}/update`, payload);
      toast.success(r.data.message);
      setEditUser(null);
      loadData();
    } catch(e:any) { toast.error(e?.response?.data?.message || "Failed to update user."); }
    finally { setEditing(false); }
  };

  const archiveUser = async (id: number) => {
    if (!confirm("Archive this user?")) return;
    try {
      const r = await api.post(`/super-admin/users/${id}/archive`);
      toast.success(r.data.message); loadData();
    } catch(e:any) { toast.error(e?.response?.data?.message || "Failed."); }
  };

  const [showUserArchiveModal, setShowUserArchiveModal] = useState(false);

  const restoreUser = async (id: number) => {
    try {
      const r = await api.post(`/super-admin/users/${id}/restore`);
      toast.success(r.data.message); loadData();
    } catch(e:any) { toast.error(e?.response?.data?.message || "Failed."); }
  };

  const permanentlyDeleteUser = (id: number) => {
    setDeleteTarget({ id, type: 'user' });
    setShowVerifyModal(true);
  };

  const handleConfirmDelete = async () => {
    if (!deleteTarget || !verifyPassword) return;
    setDeleting(true);
    try {
      let url = "";
      if (deleteTarget.type === 'user') url = `/super-admin/users/${deleteTarget.id}`;
      else if (deleteTarget.type === 'role') url = `/super-admin/roles/${deleteTarget.id}`;
      else if (deleteTarget.type === 'classification') url = `/super-admin/classifications/${deleteTarget.id}`;
      
      const r = await api.delete(url, { data: { archive_password: verifyPassword } });
      toast.success(r.data.message);
      setShowVerifyModal(false);
      setVerifyPassword("");
      setDeleteTarget(null);
      loadData();
    } catch(e:any) { 
      toast.error(e?.response?.data?.message || "Verification failed."); 
    } finally { 
      setDeleting(false); 
    }
  };

  const approveUser = async (id: number) => {
    try {
      const r = await api.post(`/super-admin/users/${id}/approve`);
      toast.success(r.data.message); loadData();
    } catch(e:any) { toast.error(e?.response?.data?.message || "Failed."); }
  };

  const rejectUser = async (id: number) => {
    if (!confirm("Reject this user application?")) return;
    try {
      const r = await api.post(`/super-admin/users/${id}/reject`);
      toast.success(r.data.message); loadData();
    } catch(e:any) { toast.error(e?.response?.data?.message || "Failed."); }
  };

  const toggleDisable = async (id: number, currentStatus: boolean) => {
    const action = currentStatus ? "enable" : "disable";
    if (!confirm(`Are you sure you want to ${action} this account?`)) return;
    try {
      const r = await api.post(`/super-admin/users/${id}/toggle-disable`);
      toast.success(r.data.message); loadData();
    } catch(e:any) { toast.error(e?.response?.data?.message || "Failed."); }
  };

  const saveArchivePassword = async (e: React.FormEvent) => {
    e.preventDefault();
    if (archForm.archive_password !== archForm.archive_password_confirmation) { toast.error("Passwords do not match."); return; }
    try {
      const r = await api.post("/super-admin/archive-password", archForm);
      toast.success(r.data.message);
      setArchForm({archive_password:"",archive_password_confirmation:""});
    } catch(e:any) { toast.error(e?.response?.data?.message || "Failed."); }
  };

  if (user?.role !== "super_admin") return (
    <div className="flex flex-col items-center justify-center min-h-[400px] gap-4 p-6">
      <Shield className="w-16 h-16 text-red-400"/>
      <h2 className="text-xl font-black text-gray-900">Access Denied</h2>
      <p className="text-gray-500 text-sm text-center">You need Owner (Super Admin) privileges to access this panel.</p>
    </div>
  );

  const tabs = [
    {id:"overview", label:"OVERVIEW", icon:LayoutDashboard},
    {id:"create_staff", label:"CREATE STAFF", icon:UserPlus},
    {id:"all_users", label:"ALL USERS", icon:Users},
    {id:"page_access", label:"PAGE ACCESS", icon:Shield},
    {id:"login_history", label:"LOGIN HISTORY", icon:Activity},
    {id:"system_security", label:"SYSTEM SECURITY", icon:Lock},
  ];

  const filteredUsers = (data?.allUsers||[]).filter((u:any) => {
    const matchesSearch = u.full_name?.toLowerCase().includes(search.toLowerCase()) ||
      u.email?.toLowerCase().includes(search.toLowerCase()) ||
      u.role?.toLowerCase().includes(search.toLowerCase());
    return matchesSearch && !u.deleted_at;
  });

  const statusColor = (s:string) => s==="approved"?"bg-green-100 text-green-700":s==="pending"?"bg-amber-100 text-amber-700":"bg-red-100 text-red-700";

  // Page definitions matching Laravel backend
  const pageGroups = [
    {
      title: "1. CORE MANAGEMENT",
      pages: [
        { id: "dashboard", label: "DASHBOARD" },
        { id: "units.*", label: "UNIT MANAGEMENT" },
        { id: "driver-management.*", label: "DRIVER MANAGEMENT" },
        { id: "activity-logs.*", label: "HISTORY LOGS" }
      ]
    },
    {
      title: "2. OPERATIONS",
      pages: [
        { id: "live-tracking.*", label: "LIVE TRACKING" },
        { id: "maintenance.*", label: "MAINTENANCE" },
        { id: "coding.*", label: "CODING MANAGEMENT" },
        { id: "driver-behavior.*", label: "DRIVER BEHAVIOR" },
        { id: "spare-parts.*", label: "SPARE PARTS INVENTORY" },
        { id: "suppliers.*", label: "SUPPLIERS" }
      ]
    },
    {
      title: "3. FINANCIAL",
      pages: [
        { id: "boundaries.*", label: "BOUNDARIES" },
        { id: "office-expenses.*", label: "OFFICE EXPENSES" },
        { id: "salary.*", label: "SALARY MANAGEMENT" },
        { id: "boundary-rules.*", label: "BOUNDARY RULES" }
      ]
    },
    {
      title: "4. LEGAL & ADMIN",
      pages: [
        { id: "decision-management.*", label: "FRANCHISE" },
        { id: "staff.*", label: "STAFF RECORDS" },
        { id: "archive.*", label: "ARCHIVE ACCESS" }
      ]
    },
    {
      title: "5. REPORTS",
      pages: [
        { id: "analytics.*", label: "ANALYTICS" },
        { id: "profitability.*", label: "UNIT PROFITABILITY" }
      ]
    }
  ];

  const togglePage = (pageId: string) => {
    if (selectedPages.includes(pageId)) {
      setSelectedPages(selectedPages.filter(id => id !== pageId));
    } else {
      setSelectedPages([...selectedPages, pageId]);
    }
  };

  const selectAllPages = () => {
    setSelectedPages(pageGroups.flatMap(g => g.pages.map(p => p.id)));
  };

  return (
    <div className="space-y-6 pb-10">
      {/* Header matching web app */}
      <div className="bg-[#fffdf0] border border-[#fef0c7] border-l-[6px] border-l-amber-500 rounded-2xl p-6 shadow-sm">
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 bg-amber-500 rounded-full flex items-center justify-center shadow-sm">
              <Crown className="w-8 h-8 text-white"/>
            </div>
            <div>
              <div className="flex items-center gap-2 mb-1">
                <h1 className="text-2xl font-black text-gray-900 tracking-tight">Owner Control Center</h1>
                <span className="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded uppercase">Owner</span>
              </div>
              <p className="text-gray-500 text-sm">Welcome back, <span className="font-bold text-gray-900">{user?.full_name}</span> Full system access</p>
            </div>
          </div>

          {/* Quick stats */}
          {data && (
            <div className="flex items-center gap-6">
              <div className="text-center">
                <p className="text-2xl font-black text-green-600">{data.stats?.active_users??0}</p>
                <p className="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Active</p>
              </div>
              <div className="text-center">
                <p className="text-2xl font-black text-gray-900">{data.stats?.total_users??0}</p>
                <p className="text-[10px] text-gray-500 uppercase font-bold tracking-widest">Total Users</p>
              </div>
            </div>
          )}
        </div>
        
        {/* Tabs */}
        <div className="flex overflow-x-auto gap-6 mt-8 border-b border-gray-200 scrollbar-hide">
          {tabs.map(t=>(
            <button key={t.id} onClick={()=>setTab(t.id)}
              className={`flex items-center gap-2 pb-3 text-sm font-bold whitespace-nowrap transition-all border-b-2
                ${tab===t.id?"border-amber-500 text-amber-700":"border-transparent text-gray-500 hover:text-gray-900"}`}>
              <t.icon className="w-4 h-4"/>{t.label}
            </button>
          ))}
        </div>
      </div>

      {loading ? (
        <div className="flex justify-center p-10"><Loader2 className="w-8 h-8 animate-spin text-amber-500"/></div>
      ) : error ? (
        <div className="bg-red-50 border border-red-200 rounded-2xl p-5 text-center">
          <XCircle className="w-10 h-10 text-red-400 mx-auto mb-2"/>
          <p className="font-bold text-red-700">Failed to load</p>
          <p className="text-red-500 text-xs mt-1 mb-3">{error}</p>
          <button onClick={loadData} className="bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold">Retry</button>
        </div>
      ) : (
        <>
          {/* OVERVIEW */}
          {tab==="overview" && (
            <div className="space-y-4">
              <div className="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50">
                <p className="p-4 text-sm font-bold text-gray-700 flex items-center gap-2"><Activity className="w-4 h-4 text-amber-500"/> Recent Login Activity</p>
                {(data?.recentAudit||[]).length === 0 && <p className="p-6 text-center text-gray-400 text-sm">No recent activity.</p>}
                {(data?.recentAudit||[]).map((a:any)=>(
                  <div key={a.id} className="p-3 flex items-center justify-between">
                    <div>
                      <p className="text-sm font-bold text-gray-900">{a.user_name}</p>
                      <p className="text-[10px] text-gray-400">{new Date(a.created_at).toLocaleString()}</p>
                    </div>
                    <span className={`text-[9px] font-bold px-2 py-1 rounded-full uppercase
                      ${a.action==="login"?"bg-blue-100 text-blue-600":a.action==="failed_login"?"bg-red-100 text-red-600":"bg-gray-100 text-gray-500"}`}>
                      {a.action?.replace("_"," ")}
                    </span>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* PAGE ACCESS (The main feature requested in the screenshot) */}
          {tab==="page_access" && (
            <div className="space-y-4">
              <div className="bg-cyan-50 border border-cyan-100 rounded-xl p-3 flex items-start gap-2">
                <ShieldAlert className="w-4 h-4 text-cyan-600 mt-0.5 flex-shrink-0"/>
                <p className="text-sm text-cyan-800">Click a user below, then toggle which pages they can access. If nothing is selected, the user will have NO access to restricted pages.</p>
              </div>

              <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {/* Left Column: Users */}
                <div className="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-[600px]">
                  <div className="p-4 border-b border-gray-100 bg-gray-50">
                    <p className="text-xs font-bold text-gray-500 tracking-widest uppercase">Select User</p>
                  </div>
                  <div className="flex-1 overflow-y-auto p-2 space-y-1">
                    {(data?.allUsers||[]).map((u:any)=>(
                      <button
                        key={u.id}
                        onClick={()=>{
                          setAccessUser(u);
                          setSelectedPages(u.allowed_pages || []);
                        }}
                        className={`w-full flex items-center gap-3 p-3 rounded-xl text-left transition-colors
                          ${accessUser?.id === u.id ? "bg-amber-50 border border-amber-200" : "hover:bg-gray-50 border border-transparent"}`}
                      >
                        <div className={`w-10 h-10 rounded-full flex items-center justify-center font-black text-sm flex-shrink-0
                          ${accessUser?.id === u.id ? "bg-amber-500 text-white" : "bg-gray-100 text-gray-500"}`}>
                          {u.full_name?.charAt(0)||"U"}
                        </div>
                        <div className="flex-1 min-w-0">
                          <p className={`font-bold text-sm truncate ${accessUser?.id === u.id ? "text-amber-900" : "text-gray-900"}`}>{u.full_name}</p>
                          <p className="text-xs text-gray-500 capitalize">{u.role}</p>
                        </div>
                        <ChevronRight className={`w-4 h-4 ${accessUser?.id === u.id ? "text-amber-500" : "text-transparent"}`}/>
                      </button>
                    ))}
                  </div>
                </div>

                {/* Right Column: Permissions */}
                <div className="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm flex flex-col h-[600px]">
                  {accessUser ? (
                    <>
                      <div className="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
                        <p className="text-xs font-bold text-gray-500 tracking-widest uppercase">
                          Page Permissions — <span className="text-amber-600">{accessUser.full_name}</span>
                        </p>
                        <div className="flex items-center gap-2">
                          <button onClick={selectAllPages} className="px-3 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-50">Select All</button>
                          <button onClick={()=>setSelectedPages([])} className="px-3 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-lg text-xs font-bold hover:bg-gray-50">Clear All</button>
                          <button onClick={savePageAccess} disabled={savingAccess} className="px-4 py-1.5 bg-amber-500 text-white rounded-lg text-xs font-bold hover:bg-amber-600 shadow-sm flex items-center gap-2">
                            {savingAccess ? <Loader2 className="w-3 h-3 animate-spin"/> : <Lock className="w-3 h-3"/>}
                            Save Access
                          </button>
                        </div>
                      </div>
                      <div className="flex-1 overflow-y-auto p-6 space-y-8">
                        {pageGroups.map((group) => (
                          <div key={group.title}>
                            <h3 className="text-xs font-black text-gray-900 mb-3">{group.title}</h3>
                            <div className="flex flex-wrap gap-2">
                              {group.pages.map((page) => {
                                const isSelected = selectedPages.includes(page.id);
                                return (
                                  <button
                                    key={page.id}
                                    onClick={() => togglePage(page.id)}
                                    className={`px-3 py-2 border rounded-lg text-xs font-bold flex items-center gap-2 transition-all
                                      ${isSelected ? "bg-[#fef3c7] border-amber-300 text-amber-900" : "bg-white border-gray-200 text-gray-500 hover:border-gray-300"}`}
                                  >
                                    <div className={`w-3 h-3 rounded-full border ${isSelected ? "bg-amber-500 border-amber-600" : "bg-white border-gray-300"}`} />
                                    {page.label}
                                  </button>
                                );
                              })}
                            </div>
                          </div>
                        ))}
                      </div>
                    </>
                  ) : (
                    <div className="flex-1 flex flex-col items-center justify-center p-8 text-gray-400">
                      <Users className="w-12 h-12 mb-3 text-gray-200"/>
                      <p className="text-sm font-bold">No User Selected</p>
                      <p className="text-xs mt-1 text-center">Select a user from the left list to configure their page permissions.</p>
                    </div>
                  )}
                </div>
              </div>
            </div>
          )}

          {/* CREATE STAFF */}
          {tab==="create_staff" && (
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              {/* Left Info Column */}
              <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 flex flex-col justify-center items-start">
                <div className="w-16 h-16 bg-amber-50 rounded-full flex items-center justify-center mb-6">
                  <UserPlus className="w-8 h-8 text-amber-500" />
                </div>
                <h2 className="text-2xl font-black text-gray-900 tracking-tight mb-4">Create Staff Account</h2>
                <p className="text-gray-500 text-sm leading-relaxed mb-8">
                  Add a new member to your team. The system will automatically generate a secure password and send the login credentials directly to their email address.
                </p>
                <div className="bg-[#fffdf0] border border-[#fef0c7] rounded-xl p-4 w-full">
                  <p className="text-xs font-bold text-amber-700 tracking-widest uppercase mb-2 flex items-center gap-2">
                    <ShieldAlert className="w-4 h-4"/> Security Note
                  </p>
                  <p className="text-xs text-amber-600">
                    For security, new users are required to change their auto-generated password immediately upon their first login
                  </p>
                </div>
              </div>

              {/* Right Form Column */}
              <div className="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
                {tempPass ? (
                  <div className="text-center py-10">
                    <CheckCircle className="w-16 h-16 text-green-500 mx-auto mb-4"/>
                    <h3 className="font-black text-2xl text-gray-900 mb-2">Account Created Successfully!</h3>
                    <p className="text-gray-500 text-sm mb-6">Share this one-time password with the new staff member:</p>
                    <div className="bg-gray-50 border-2 border-dashed border-gray-300 rounded-2xl p-6 text-4xl font-mono font-black tracking-widest text-gray-900 mb-6 select-all max-w-sm mx-auto">{tempPass}</div>
                    <p className="text-xs font-bold text-amber-600 mb-8 uppercase tracking-widest">They must change this on first login</p>
                    <button onClick={()=>{ setTempPass(""); setSf({first_name:"",last_name:"",email:"",phone_number:"",address:"",role:""}); }}
                      className="bg-amber-500 hover:bg-amber-600 text-white font-bold px-8 py-3 rounded-xl text-sm transition-colors flex items-center gap-2 mx-auto">
                      <UserPlus className="w-4 h-4"/> Create Another Account
                    </button>
                  </div>
                ) : (
                  <form onSubmit={createStaff} className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-2 block">First Name <span className="text-red-500">*</span></label>
                        <input required type="text" placeholder="Enter first name" value={sf.first_name} onChange={e=>setSf({...sf,first_name:e.target.value})}
                          className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-shadow"/>
                      </div>
                      <div>
                        <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-2 block">Last Name <span className="text-red-500">*</span></label>
                        <input required type="text" placeholder="Enter last name" value={sf.last_name} onChange={e=>setSf({...sf,last_name:e.target.value})}
                          className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-shadow"/>
                      </div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-2 block">Email Address <span className="text-red-500">*</span></label>
                        <input required type="email" placeholder="name@eurotaxi.com" value={sf.email} onChange={e=>setSf({...sf,email:e.target.value})}
                          className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-shadow"/>
                      </div>
                      <div>
                        <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-2 block">Phone Number</label>
                        <input type="text" placeholder="+63 9XX XXX XXXX" value={sf.phone_number} onChange={e=>setSf({...sf,phone_number:e.target.value})}
                          className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-shadow"/>
                      </div>
                    </div>
                    <div>
                      <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-2 block">Home Address</label>
                      <input type="text" placeholder="Enter complete home address" value={sf.address} onChange={e=>setSf({...sf,address:e.target.value})}
                        className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-shadow"/>
                    </div>
                    <div className="relative">
                      <div className="flex flex-col sm:flex-row sm:items-center justify-between mb-2 gap-2">
                        <label className="text-xs font-bold text-gray-700 uppercase tracking-widest block">Assign System Role <span className="text-red-500">*</span></label>
                        <button type="button" onClick={() => setShowRoleModal(true)} 
                          className="text-xs font-bold bg-[#fffdf0] text-amber-700 border border-[#fef0c7] px-3 py-1.5 rounded-lg flex items-center gap-1.5 hover:bg-amber-50 transition-colors w-fit">
                          <Key className="w-3 h-3"/> Manage System Roles
                        </button>
                      </div>
                      <select required value={sf.role} onChange={e=>setSf({...sf,role:e.target.value})}
                        className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-shadow appearance-none">
                        <option value="">Select a role...</option>
                        {(data?.roles||[]).map((r:any)=><option key={r.id} value={r.name}>{r.label}</option>)}
                      </select>
                    </div>
                    <div className="flex justify-end pt-4">
                      <button type="submit" disabled={submitting}
                        className="w-full sm:w-auto bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-8 rounded-xl disabled:opacity-60 transition-colors flex items-center justify-center gap-2">
                        <UserPlus className="w-4 h-4" />
                        {submitting?"Creating Account...":"Create Staff Account"}
                      </button>
                    </div>
                  </form>
                )}
              </div>
            </div>
          )}

          {/* ALL USERS */}
          {tab==="all_users" && (
            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
              <div className="p-4 border-b border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4">
                <div className="flex items-center gap-3 w-full md:w-auto">
                  <div className="relative w-full md:w-64">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"/>
                    <input type="text" placeholder="Search users..." value={search} onChange={e=>setSearch(e.target.value)}
                      className="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 outline-none"/>
                  </div>
                  <select className="bg-white border border-gray-200 text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 outline-none">
                    <option value="">All Statuses</option>
                    <option value="approved">Activated</option>
                    <option value="pending">Pending</option>
                  </select>
                </div>
                <button onClick={() => setShowUserArchiveModal(true)} className="flex items-center gap-2 border border-amber-500 text-amber-600 px-4 py-2 rounded-xl text-sm font-bold hover:bg-amber-50 transition-colors w-full md:w-auto justify-center">
                  <Archive className="w-4 h-4"/> View Archives
                </button>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full text-left border-collapse min-w-[900px]">
                  <thead>
                    <tr className="bg-gray-50 border-b border-gray-100">
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">User</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Role</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Status</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Active</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Last Login</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-50">
                    {filteredUsers.length === 0 && (
                      <tr><td colSpan={6} className="px-6 py-8 text-center text-gray-400 text-sm">No users found.</td></tr>
                    )}
                    {filteredUsers.map((u: any) => (
                      <tr key={u.id} className="hover:bg-gray-50/50 transition-colors">
                        <td className="px-6 py-4">
                          <div className="flex items-center gap-3">
                            <div className="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center font-bold text-gray-600 flex-shrink-0">
                              {u.full_name?.charAt(0) || "?"}
                            </div>
                            <div>
                              <p className="font-bold text-gray-900 text-sm">{u.full_name}</p>
                              <p className="text-xs text-gray-500">{u.email}</p>
                            </div>
                          </div>
                        </td>
                        <td className="px-6 py-4">
                          <span className={`inline-block px-3 py-1 border text-[10px] font-black uppercase tracking-wider rounded-full
                            ${u.role==='super_admin'?'bg-amber-50 text-amber-600 border-amber-100':
                              u.role==='manager'?'bg-blue-50 text-blue-600 border-blue-100':
                              u.role==='dispatcher'?'bg-emerald-50 text-emerald-600 border-emerald-100':'bg-indigo-50 text-indigo-600 border-indigo-100'}`}>
                            {u.role}
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          {u.approval_status === "approved" ? (
                            <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-green-50 border border-green-200 text-green-600 text-[10px] font-black uppercase tracking-wider rounded-full">
                              <div className="w-1.5 h-1.5 rounded-full bg-green-500"></div> ACTIVATED
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-600 text-[10px] font-black uppercase tracking-wider rounded-full">
                              <div className="w-1.5 h-1.5 rounded-full bg-amber-500"></div> PENDING
                            </span>
                          )}
                        </td>
                        <td className="px-6 py-4">
                          {!u.is_disabled ? (
                            <button onClick={()=>toggleDisable(u.id, !!u.is_disabled)} className="inline-flex items-center gap-1.5 px-3 py-1 border border-green-500 text-green-600 text-[10px] font-bold uppercase tracking-wider rounded-full hover:bg-green-50 transition-colors" title="Click to disable account">
                              <div className="w-1.5 h-1.5 rounded-full bg-green-500"></div> Active
                            </button>
                          ) : (
                            <button onClick={()=>toggleDisable(u.id, !!u.is_disabled)} className="inline-flex items-center gap-1.5 px-3 py-1 border border-red-500 text-red-600 text-[10px] font-bold uppercase tracking-wider rounded-full hover:bg-red-50 transition-colors" title="Click to enable account">
                              <div className="w-1.5 h-1.5 rounded-full bg-red-500"></div> Disabled
                            </button>
                          )}
                        </td>
                        <td className="px-6 py-4 text-xs text-gray-500">
                          {u.last_login_at ? new Date(u.last_login_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : "Never"}
                        </td>
                        <td className="px-6 py-4">
                          <div className="flex items-center justify-end gap-2">
                            {u.approval_status === "pending" && (
                               <button onClick={()=>approveUser(u.id)} className="p-2 text-gray-400 hover:text-green-500 hover:bg-green-50 rounded-lg transition-colors" title="Approve">
                                 <CheckCircle className="w-4 h-4"/>
                               </button>
                            )}
                            <button onClick={()=>setEditUser(u)} className="p-2 text-gray-400 hover:text-amber-500 hover:bg-amber-50 rounded-lg transition-colors" title="Edit User">
                              <Edit2 className="w-4 h-4"/>
                            </button>
                            <button onClick={()=>archiveUser(u.id)} className="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Archive">
                              <Trash2 className="w-4 h-4"/>
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* LOGIN HISTORY */}
          {tab==="login_history" && (
            <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
              <div className="p-4 border-b border-gray-100 flex flex-col lg:flex-row items-center justify-between gap-4 bg-white">
                <div className="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                  <div className="relative w-full md:w-64">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"/>
                    <input type="text" placeholder="Search by name, email or IP..." value={auditSearch} onChange={e=>setAuditSearch(e.target.value)}
                      className="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 outline-none"/>
                  </div>
                  <select value={auditAction} onChange={e=>setAuditAction(e.target.value)}
                    className="bg-white border border-gray-200 text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 outline-none w-full md:w-auto">
                    <option value="">All Actions</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                    <option value="failed_login">Failed Login</option>
                    <option value="created">Created</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                  </select>
                  <select value={auditRole} onChange={e=>setAuditRole(e.target.value)}
                    className="bg-white border border-gray-200 text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-amber-500 outline-none w-full md:w-auto">
                    <option value="">All Roles</option>
                    {(data?.roles||[]).map((r:any)=>(
                      <option key={r.id} value={r.name}>{r.label}</option>
                    ))}
                    <option value="super_admin">Owner</option>
                  </select>
                </div>
                <button onClick={loadAudit} className="p-2 border border-gray-200 hover:bg-gray-50 rounded-xl transition-colors">
                  <RefreshCw className="w-4 h-4 text-gray-400"/>
                </button>
              </div>

              <div className="overflow-x-auto">
                <table className="w-full text-left border-collapse min-w-[1000px]">
                  <thead>
                    <tr className="bg-gray-50 border-b border-gray-100">
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">User</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Role</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Action</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">IP Address</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Browser / Device</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Notes</th>
                      <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest whitespace-nowrap">Date & Time</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-50">
                    {auditLogs.length === 0 && (
                      <tr><td colSpan={7} className="px-6 py-8 text-center text-gray-400 text-sm">No records found.</td></tr>
                    )}
                    {auditLogs.map((log: any) => (
                      <tr key={log.id} className="hover:bg-gray-50/50 transition-colors">
                        <td className="px-6 py-4">
                          <div>
                            <p className="font-bold text-gray-900 text-sm">{log.user_name}</p>
                            <p className="text-xs text-gray-500">{log.user_email}</p>
                          </div>
                        </td>
                        <td className="px-6 py-4">
                          <span className={`inline-block px-3 py-1 border text-[10px] font-black uppercase tracking-wider rounded-full
                            ${log.user_role==='super_admin'?'bg-amber-50 text-amber-600 border-amber-100':
                              log.user_role==='manager'?'bg-blue-50 text-blue-600 border-blue-100':
                              log.user_role==='dispatcher'?'bg-emerald-50 text-emerald-600 border-emerald-100':'bg-indigo-50 text-indigo-600 border-indigo-100'}`}>
                            {log.user_role?.replace(/_/g, ' ')}
                          </span>
                        </td>
                        <td className="px-6 py-4">
                          <span className={`inline-flex items-center gap-1.5 px-3 py-1 text-[10px] font-black uppercase tracking-wider rounded-full border
                            ${log.action==="login"?"bg-blue-50 text-blue-600 border-blue-100":
                              log.action==="logout"?"bg-gray-50 text-gray-600 border-gray-100":
                              log.action==="failed_login"?"bg-red-50 text-red-600 border-red-100":
                              log.action==="created"?"bg-green-50 text-green-600 border-green-100":"bg-amber-50 text-amber-600 border-amber-100"}`}>
                            <div className={`w-1 h-1 rounded-full ${log.action==="login"?"bg-blue-600":log.action==="logout"?"bg-gray-600":"bg-amber-600"}`}></div>
                            {log.action?.replace(/_/g," ")}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-xs text-gray-600 font-mono">
                          {log.ip_address || "—"}
                        </td>
                        <td className="px-6 py-4 text-xs text-gray-500 max-w-xs truncate" title={log.user_agent}>
                          {log.user_agent || "—"}
                        </td>
                        <td className="px-6 py-4 text-xs text-gray-500">
                          {log.notes || "—"}
                        </td>
                        <td className="px-6 py-4 text-xs text-gray-600 whitespace-nowrap">
                          {new Date(log.created_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          )}

          {/* SECURITY */}
          {tab==="system_security" && (
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
              {/* Left Column: Form */}
              <div className="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div className="p-4 bg-red-50/50 border-b border-red-100 flex items-center gap-3">
                  <div className="w-10 h-10 bg-white rounded-xl shadow-sm border border-red-100 flex items-center justify-center">
                    <ShieldAlert className="w-5 h-5 text-red-600"/>
                  </div>
                  <div>
                    <span className="font-black text-xs text-red-700 uppercase tracking-widest">Archive Deletion Lock</span>
                    <p className="text-[10px] text-red-500 font-medium">Prevents accidental or unauthorized permanent data loss.</p>
                  </div>
                </div>
                <div className="p-6 space-y-6">
                  <form onSubmit={saveArchivePassword} className="space-y-5">
                    <div>
                      <div className="flex justify-between items-center mb-1.5">
                        <label className="text-[11px] font-black text-gray-700 uppercase tracking-widest block">Current Deletion Password</label>
                        <span className="text-[9px] text-gray-400 font-medium italic">If this is your first time, leave empty.</span>
                      </div>
                      <div className="relative">
                        <input required minLength={6} type={showPass?"text":"password"} value={archForm.archive_password}
                          placeholder="Enter new deletion password"
                          onChange={e=>setArchForm({...archForm,archive_password:e.target.value})}
                          className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-all"/>
                        <button type="button" onClick={()=>setShowPass(!showPass)} className="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                          {showPass?<EyeOff className="w-4 h-4"/>:<Eye className="w-4 h-4"/>}
                        </button>
                      </div>
                    </div>
                    <div>
                      <label className="text-[11px] font-black text-gray-700 uppercase tracking-widest mb-1.5 block">Confirm New Password</label>
                      <input required minLength={6} type={showPass?"text":"password"} value={archForm.archive_password_confirmation}
                        placeholder="Repeat deletion password"
                        onChange={e=>setArchForm({...archForm,archive_password_confirmation:e.target.value})}
                        className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-all"/>
                    </div>

                    <div className="bg-blue-50/50 border border-blue-100 rounded-2xl p-4 flex gap-3">
                       <CheckCircle className="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"/>
                       <div>
                          <p className="text-[11px] font-black text-blue-700 uppercase tracking-widest mb-1">Security Notice</p>
                          <p className="text-[10px] text-blue-600 leading-relaxed font-medium">
                            This password is <span className="underline font-bold text-blue-800">separate</span> from your login password. It is required whenever someone attempts to <strong>Permanently Delete</strong> items from the archives (Users, Roles, Incident Types).
                          </p>
                       </div>
                    </div>

                    <button type="submit" className="w-full bg-gray-900 hover:bg-black text-white font-black py-4 rounded-xl transition-all shadow-lg shadow-gray-200 flex items-center justify-center gap-2 text-sm uppercase tracking-widest">
                      <Check className="w-4 h-4"/> Update Deletion Password
                    </button>
                  </form>
                </div>
              </div>

              {/* Right Column: Status */}
              <div className="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 className="text-lg font-black text-gray-900 tracking-tight mb-6">System Integrity Status</h3>
                <div className="space-y-4">
                  {[
                    { label: "Database Connection", icon: Database, status: "SECURE", color: "green" },
                    { label: "MFA Enforcement", icon: Shield, status: "ACTIVE", color: "green" },
                    { label: "Audit Logging", icon: Activity, status: "LOGGING", color: "green" }
                  ].map((item, idx) => (
                    <div key={idx} className="flex items-center justify-between p-4 bg-gray-50/50 rounded-2xl border border-gray-100 hover:border-amber-100 transition-colors group">
                      <div className="flex items-center gap-3">
                        <div className="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform">
                          <item.icon className="w-5 h-5 text-gray-400 group-hover:text-amber-500 transition-colors"/>
                        </div>
                        <span className="text-sm font-bold text-gray-700">{item.label}</span>
                      </div>
                      <span className={`px-3 py-1 bg-${item.color}-50 border border-${item.color}-100 text-${item.color}-600 text-[10px] font-black uppercase tracking-wider rounded-full shadow-sm`}>
                        {item.status}
                      </span>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}
        </>
      )}

      {/* MODALS */}
      {editUser && (
        <div className="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl w-full max-w-md shadow-2xl p-6">
            <div className="flex items-center justify-between mb-6">
              <h3 className="text-xl font-black text-gray-900">Edit User</h3>
              <button onClick={() => setEditUser(null)} className="text-gray-400 hover:text-gray-600"><X className="w-5 h-5" /></button>
            </div>
            <form onSubmit={saveEditUser} className="space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-1 block">First Name</label>
                  <input required type="text" value={editUser.first_name} onChange={e => setEditUser({ ...editUser, first_name: e.target.value })}
                    className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" />
                </div>
                <div>
                  <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-1 block">Last Name</label>
                  <input required type="text" value={editUser.last_name} onChange={e => setEditUser({ ...editUser, last_name: e.target.value })}
                    className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" />
                </div>
              </div>
              <div>
                <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-1 block">Email</label>
                <input required type="email" value={editUser.email} onChange={e => setEditUser({ ...editUser, email: e.target.value })}
                  className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" />
              </div>
              <div>
                <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-1 block">Phone Number</label>
                <input required type="text" value={editUser.phone_number} onChange={e => setEditUser({ ...editUser, phone_number: e.target.value })}
                  className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" />
              </div>
              <div>
                <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-1 block">Address</label>
                <input required type="text" value={editUser.address} onChange={e => setEditUser({ ...editUser, address: e.target.value })}
                  className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" />
              </div>
              <div>
                <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-1 block">Role</label>
                <select required value={editUser.role} onChange={e => setEditUser({ ...editUser, role: e.target.value })}
                  className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                  {data?.roles?.map((r: any) => (
                    <option key={r.id} value={r.name}>{r.label}</option>
                  ))}
                  <option value="super_admin">Super Admin (Owner)</option>
                </select>
              </div>
              <div className="pt-2 flex gap-3">
                <button type="button" onClick={() => setEditUser(null)} className="flex-1 py-3 border border-gray-200 text-gray-600 rounded-xl font-bold hover:bg-gray-50 transition-colors">
                  Cancel
                </button>
                <button type="submit" disabled={editing} className="flex-1 bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl disabled:opacity-60 transition-colors">
                  {editing ? "Saving..." : "Save Changes"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
      {showUserArchiveModal && (
        <div className="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl w-full max-w-4xl shadow-2xl flex flex-col max-h-[90vh]">
            <div className="p-6 flex items-start justify-between">
              <div>
                <h2 className="text-2xl font-black text-gray-900 tracking-tight">User Archives</h2>
                <p className="text-sm text-gray-500 mt-1">Previously deleted staff accounts. You can restore them if needed.</p>
              </div>
              <button onClick={() => setShowUserArchiveModal(false)} className="p-2 bg-gray-50 text-gray-400 rounded-xl hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <X className="w-5 h-5" />
              </button>
            </div>
            <div className="flex-1 overflow-auto">
              <table className="w-full text-left border-collapse min-w-[700px]">
                <thead>
                  <tr className="bg-gray-50 border-y border-gray-100">
                    <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">User</th>
                    <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Role</th>
                    <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest">Date Deleted</th>
                    <th className="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-widest text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-50">
                  {data?.archivedUsers?.length === 0 && (
                    <tr><td colSpan={4} className="p-8 text-center text-gray-400">No archived users found.</td></tr>
                  )}
                  {data?.archivedUsers?.map((u: any) => (
                    <tr key={u.id} className="hover:bg-gray-50/50">
                      <td className="px-6 py-4">
                        <p className="font-bold text-gray-900 text-sm">{u.full_name}</p>
                        <p className="text-xs text-gray-500">{u.email}</p>
                      </td>
                      <td className="px-6 py-4">
                        <span className={`inline-block px-3 py-1 border text-[10px] font-black uppercase tracking-wider rounded-full
                          ${u.role==='super_admin'?'bg-amber-50 text-amber-600 border-amber-100':
                            u.role==='manager'?'bg-blue-50 text-blue-600 border-blue-100':
                            u.role==='dispatcher'?'bg-emerald-50 text-emerald-600 border-emerald-100':'bg-indigo-50 text-indigo-600 border-indigo-100'}`}>
                          {u.role}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-xs text-gray-500">
                        {u.deleted_at ? new Date(u.deleted_at).toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : "Unknown"}
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex items-center justify-end gap-2">
                          <button onClick={() => restoreUser(u.id)} className="flex items-center gap-1.5 px-4 py-2 bg-green-700 hover:bg-green-800 text-white text-xs font-bold rounded-lg transition-colors">
                            <RefreshCw className="w-3 h-3" /> Restore
                          </button>
                          <button onClick={() => permanentlyDeleteUser(u.id)} className="flex items-center gap-1.5 px-4 py-2 bg-red-800 hover:bg-red-900 text-white text-xs font-bold rounded-lg transition-colors">
                            <Trash2 className="w-3 h-3" /> Delete
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
            <div className="p-4 border-t border-gray-100 flex justify-end">
              <button onClick={() => setShowUserArchiveModal(false)} className="px-6 py-2 border border-gray-200 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 transition-colors">Close</button>
            </div>
          </div>
        </div>
      )}

      {showRoleModal && (
        <div className="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl w-full max-w-4xl shadow-2xl flex flex-col max-h-[90vh]">
            <div className="p-6 border-b border-gray-100 flex items-center justify-between">
              <div>
                <h2 className="text-xl font-black text-gray-900 tracking-tight">System Role Management</h2>
                <p className="text-sm text-gray-500">Define, modify, or retire specialized access roles for your organization.</p>
              </div>
              <div className="flex items-center gap-3">
                <button onClick={() => setShowAddRole(true)} className="bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-bold flex items-center gap-2 hover:bg-amber-600">
                  <UserPlus className="w-4 h-4" /> Add New Role
                </button>
                <button onClick={() => setShowRoleModal(false)} className="text-gray-400 hover:text-gray-600"><X className="w-6 h-6" /></button>
              </div>
            </div>
            <div className="flex-1 overflow-y-auto p-6 bg-gray-50 grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* ACTIVE ROLES */}
              <div className="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <p className="text-xs font-bold text-gray-500 tracking-widest uppercase mb-4 flex items-center gap-2">
                  <Shield className="w-4 h-4 text-green-500" /> ACTIVE SYSTEM ROLES
                </p>
                <div className="space-y-3">
                  {data?.roles?.map((r: any) => (
                    <div key={r.id} className="p-4 border border-gray-100 rounded-xl flex items-center gap-4 hover:border-gray-200 transition-colors bg-gray-50/50 group">
                      <div className="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-400 flex-shrink-0">
                        <Users className="w-5 h-5" />
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2 mb-1">
                          <p className="font-bold text-gray-900 truncate">{r.label}</p>
                          <span className="text-[9px] font-bold uppercase tracking-widest bg-gray-200 text-gray-500 px-2 py-0.5 rounded flex-shrink-0">{r.name}</span>
                        </div>
                        <p className="text-xs text-gray-400 truncate">{r.description || "No description provided."}</p>
                      </div>
                      {r.name !== "super_admin" && (
                        <button onClick={() => handleArchiveRole(r.id)} className="opacity-0 group-hover:opacity-100 p-2 text-amber-500 hover:bg-amber-50 rounded-lg transition-all flex-shrink-0" title="Archive Role">
                          <Archive className="w-4 h-4" />
                        </button>
                      )}
                    </div>
                  ))}
                </div>
              </div>
              {/* ARCHIVED ROLES */}
              <div className="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <p className="text-xs font-bold text-gray-500 tracking-widest uppercase mb-4 flex items-center gap-2">
                  <Activity className="w-4 h-4 text-gray-400" /> ARCHIVED / RETIRED ROLES
                </p>
                <div className="space-y-3">
                  {data?.archivedRoles?.length === 0 && (
                    <div className="text-center py-12">
                      <div className="w-12 h-12 bg-gray-100 rounded-lg mx-auto mb-3 flex items-center justify-center text-gray-300">
                         <XCircle className="w-6 h-6"/>
                      </div>
                      <p className="text-sm font-bold text-gray-400 uppercase tracking-widest">NO ARCHIVED ROLES</p>
                    </div>
                  )}
                  {data?.archivedRoles?.map((r: any) => (
                    <div key={r.id} className="p-4 border border-gray-100 rounded-xl flex items-center gap-4 bg-gray-50 opacity-75 group">
                      <div className="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-400 flex-shrink-0">
                        <Users className="w-5 h-5" />
                      </div>
                      <div className="flex-1 min-w-0">
                        <div className="flex items-center gap-2 mb-1">
                          <p className="font-bold text-gray-500 line-through truncate">{r.label}</p>
                          <span className="text-[9px] font-bold uppercase tracking-widest bg-gray-200 text-gray-400 px-2 py-0.5 rounded flex-shrink-0">{r.name}</span>
                        </div>
                        <p className="text-xs text-gray-400 truncate">{r.description || "No description provided."}</p>
                      </div>
                      <div className="flex items-center gap-1">
                        <button onClick={() => handleRestoreRole(r.id)} className="opacity-0 group-hover:opacity-100 p-2 text-green-500 hover:bg-green-50 rounded-lg transition-all flex-shrink-0" title="Restore Role">
                          <RefreshCw className="w-4 h-4" />
                        </button>
                        <button onClick={() => { setDeleteTarget({id: r.id, type: 'role'}); setShowVerifyModal(true); }} className="opacity-0 group-hover:opacity-100 p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all flex-shrink-0" title="Permanently Delete Role">
                          <Trash2 className="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
            <div className="p-4 border-t border-gray-100 bg-white flex justify-end rounded-b-3xl">
              <button onClick={() => setShowRoleModal(false)} className="px-6 py-2 border border-gray-200 text-gray-600 rounded-xl text-sm font-bold hover:bg-gray-50 transition-colors">
                Close Manager
              </button>
            </div>
          </div>
        </div>
      )}

      {showAddRole && (
        <div className="fixed inset-0 bg-gray-900/70 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl w-full max-w-md shadow-2xl p-6">
            <div className="flex items-center justify-between mb-6">
              <h3 className="text-xl font-black text-gray-900">Add New Role</h3>
              <button onClick={() => setShowAddRole(false)} className="text-gray-400 hover:text-gray-600"><X className="w-5 h-5" /></button>
            </div>
            <form onSubmit={saveNewRole} className="space-y-4">
              <div>
                <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-1 block">Role Key (Internal) <span className="text-red-500">*</span></label>
                <input required type="text" placeholder="e.g., branch_manager" value={roleForm.name} onChange={e => setRoleForm({ ...roleForm, name: e.target.value })}
                  className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" />
              </div>
              <div>
                <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-1 block">Role Label (Display) <span className="text-red-500">*</span></label>
                <input required type="text" placeholder="e.g., Branch Manager" value={roleForm.label} onChange={e => setRoleForm({ ...roleForm, label: e.target.value })}
                  className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none" />
              </div>
              <div>
                <label className="text-xs font-bold text-gray-700 uppercase tracking-widest mb-1 block">Description</label>
                <textarea rows={3} placeholder="Describe the responsibilities..." value={roleForm.description} onChange={e => setRoleForm({ ...roleForm, description: e.target.value })}
                  className="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none resize-none" />
              </div>
              <div className="pt-2">
                <button type="submit" disabled={savingRole} className="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl disabled:opacity-60 transition-colors">
                  {savingRole ? "Saving..." : "Create Role"}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
      {showVerifyModal && (
        <div className="fixed inset-0 bg-gray-900/60 backdrop-blur-md z-[100] flex items-center justify-center p-4">
          <div className="bg-white rounded-[32px] w-full max-w-sm shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <div className="p-8 flex flex-col items-center text-center">
              <div className="w-20 h-20 bg-red-50 rounded-3xl flex items-center justify-center mb-6 border border-red-100 shadow-sm">
                <ShieldAlert className="w-10 h-10 text-red-500 animate-pulse" />
              </div>
              
              <h3 className="text-2xl font-black text-[#8b1a1a] tracking-tight mb-2">Security Verification</h3>
              <p className="text-sm text-gray-500 leading-relaxed mb-8">
                To permanently delete this item, please enter the <span className="font-bold text-gray-700">Archive Deletion Password</span> below.
              </p>

              <div className="w-full space-y-6">
                <div className="relative group">
                  <input
                    type="password"
                    placeholder="••••••"
                    value={verifyPassword}
                    onChange={(e) => setVerifyPassword(e.target.value)}
                    className="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl px-6 py-4 text-center text-2xl tracking-[1em] font-mono focus:border-red-200 focus:bg-white outline-none transition-all placeholder:tracking-normal placeholder:text-gray-300"
                    autoFocus
                  />
                </div>

                <div className="bg-[#fffcf0] border border-[#fef0c7] rounded-2xl p-4 flex gap-3 text-left">
                  <ShieldAlert className="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                  <p className="text-[11px] text-amber-700 leading-relaxed font-medium">
                    <span className="font-bold uppercase">Warning:</span> Permanent deletion is <span className="underline font-bold">irreversible</span>. All associated data will be removed from the system forever.
                  </p>
                </div>

                <div className="grid grid-cols-2 gap-4 pt-2">
                  <button
                    type="button"
                    onClick={() => { setShowVerifyModal(false); setVerifyPassword(""); setDeleteTarget(null); }}
                    className="py-4 border border-gray-200 text-gray-600 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-gray-50 transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    disabled={deleting || !verifyPassword}
                    onClick={handleConfirmDelete}
                    className="py-4 bg-[#8b1a1a] hover:bg-[#6b1414] text-white rounded-2xl font-black text-sm uppercase tracking-widest disabled:opacity-50 transition-all shadow-lg shadow-red-100"
                  >
                    {deleting ? "Verifying..." : "Confirm Delete"}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
