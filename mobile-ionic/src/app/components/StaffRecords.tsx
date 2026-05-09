import { useState, useEffect, useCallback } from "react";
import { Search, RefreshCw, Users, Plus, ShieldCheck, ShieldAlert, Phone, MapPin, Contact2, Edit2, Archive, Loader2, Save, X } from "lucide-react";
import api from "../services/api";
import { toast } from "sonner";

interface AdminStaff {
  id: number;
  full_name: string;
  role: string;
  email: string;
  is_active: number;
}

interface GeneralStaff {
  id: number;
  name: string;
  role: string;
  phone: string | null;
  contact_person: string | null;
  emergency_phone: string | null;
  address: string | null;
  status: string;
}

export function StaffRecords() {
  const [adminStaff, setAdminStaff] = useState<AdminStaff[]>([]);
  const [generalStaff, setGeneralStaff] = useState<GeneralStaff[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState("");
  const [activeTab, setActiveTab] = useState("general"); // general, admin

  // Form Modal States
  const [showModal, setShowModal] = useState(false);
  const [editingStaff, setEditingStaff] = useState<GeneralStaff | null>(null);
  const [formLoading, setFormLoading] = useState(false);

  // Form Fields (For General Staff)
  const [name, setName] = useState("");
  const [role, setRole] = useState("");
  const [phone, setPhone] = useState("");
  const [contactPerson, setContactPerson] = useState("");
  const [emergencyPhone, setEmergencyPhone] = useState("");
  const [address, setAddress] = useState("");
  const [status, setStatus] = useState("active");

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const res = await api.get(`/staff?search=${searchQuery}`);
      if (res.data.success) {
        setAdminStaff(res.data.adminStaff);
        setGeneralStaff(res.data.generalStaff);
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to load staff records");
    } finally {
      setLoading(false);
    }
  }, [searchQuery]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const handleOpenCreateModal = () => {
    setEditingStaff(null);
    setName("");
    setRole("");
    setPhone("");
    setContactPerson("");
    setEmergencyPhone("");
    setAddress("");
    setStatus("active");
    setShowModal(true);
  };

  const handleOpenEditModal = (s: GeneralStaff) => {
    setEditingStaff(s);
    setName(s.name);
    setRole(s.role);
    setPhone(s.phone || "");
    setContactPerson(s.contact_person || "");
    setEmergencyPhone(s.emergency_phone || "");
    setAddress(s.address || "");
    setStatus(s.status);
    setShowModal(true);
  };

  const handleFormSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormLoading(true);
    try {
      const payload = {
        name,
        role,
        phone: phone || null,
        contact_person: contactPerson || null,
        emergency_phone: emergencyPhone || null,
        address: address || null,
        status,
      };

      let res;
      if (editingStaff) {
        res = await api.put(`/staff/${editingStaff.id}`, payload);
      } else {
        res = await api.post("/staff", payload);
      }

      if (res.data.success) {
        toast.success(res.data.message || "Record saved successfully");
        setShowModal(false);
        fetchData();
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Validation Error: Letters & spaces only for names.");
    } finally {
      setFormLoading(false);
    }
  };

  const handleArchive = async (id: number, staffName: string) => {
    if (!window.confirm(`Are you sure you want to archive the staff record of ${staffName}?`)) return;
    try {
      const res = await api.delete(`/staff/${id}`);
      if (res.data.success) {
        toast.success(`${staffName} moved to archive`);
        fetchData();
      }
    } catch (err: any) {
      toast.error("Failed to archive staff record");
    }
  };

  return (
    <div className="flex flex-col min-h-full bg-gray-50 pb-20">
      {/* Header */}
      <div className="bg-white px-4 pt-6 pb-4 border-b border-gray-200">
        <div className="flex items-center justify-between mb-4">
          <div>
            <h1 className="text-xl font-black text-gray-900 leading-tight">Staff Records</h1>
            <p className="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Manage fleet operators and admin staff</p>
          </div>
          <div className="flex gap-2">
            <button onClick={handleOpenCreateModal} className="flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white rounded-xl active:scale-95 transition-all shadow-md shadow-blue-200">
              <Plus className="w-4 h-4" />
              <span className="text-[10px] font-black uppercase tracking-wider">Add Staff</span>
            </button>
            <button onClick={fetchData} className="p-2.5 bg-gray-100 rounded-xl active:bg-gray-200 transition-colors">
              <RefreshCw className={`w-4 h-4 text-gray-500 ${loading ? 'animate-spin' : ''}`} />
            </button>
          </div>
        </div>

        {/* Dashboard Stat Cards */}
        <div className="grid grid-cols-2 gap-3 mb-4">
          <div className="bg-blue-50 border border-blue-100 p-3 rounded-2xl">
            <p className="text-[9px] font-black uppercase text-blue-500 tracking-wider">Account Holders</p>
            <p className="text-sm font-black text-blue-700 leading-none mt-1">{adminStaff.length} Admins</p>
          </div>
          <div className="bg-green-50 border border-green-100 p-3 rounded-2xl">
            <p className="text-[9px] font-black uppercase text-green-500 tracking-wider">Auxiliary Staff</p>
            <p className="text-sm font-black text-green-700 leading-none mt-1">{generalStaff.length} Members</p>
          </div>
        </div>

        {/* Search */}
        <div className="relative mb-4">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input 
            type="text" 
            placeholder="Search by staff name, role..."
            className="w-full pl-10 pr-4 py-3 bg-gray-100 border-none rounded-2xl text-sm font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>

        {/* Tabs */}
        <div className="flex bg-gray-100 p-1 rounded-2xl">
          <button 
            onClick={() => { setActiveTab("general"); setSearchQuery(""); }}
            className={`flex-1 py-3 text-center rounded-xl text-[10px] font-black uppercase tracking-wider transition-all ${activeTab === "general" ? "bg-white text-blue-600 shadow-sm" : "text-gray-500"}`}
          >
            Auxiliary Staff ({generalStaff.length})
          </button>
          <button 
            onClick={() => { setActiveTab("admin"); setSearchQuery(""); }}
            className={`flex-1 py-3 text-center rounded-xl text-[10px] font-black uppercase tracking-wider transition-all ${activeTab === "admin" ? "bg-white text-blue-600 shadow-sm" : "text-gray-500"}`}
          >
            Web Admins ({adminStaff.length})
          </button>
        </div>
      </div>

      {/* Staff List */}
      <div className="flex-1 p-4">
        {loading ? (
          <div className="flex flex-col items-center justify-center py-20 gap-4">
            <Loader2 className="w-8 h-8 text-blue-500 animate-spin" />
            <p className="text-xs font-black text-gray-400 uppercase tracking-widest">Loading records...</p>
          </div>
        ) : activeTab === "general" ? (
          generalStaff.length === 0 ? (
            <div className="bg-white rounded-3xl p-10 text-center border border-dashed border-gray-200">
              <Users className="w-12 h-12 text-gray-200 mx-auto mb-3" />
              <p className="text-sm font-black text-gray-400 uppercase tracking-widest">No general staff found</p>
            </div>
          ) : (
            <div className="space-y-3">
              {generalStaff.map((staff) => (
                <div key={staff.id} className="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
                  <div className="flex items-start justify-between mb-3">
                    <div>
                      <h3 className="text-sm font-black text-gray-900 leading-tight">{staff.name}</h3>
                      <p className="text-[10px] font-bold text-gray-400 uppercase mt-0.5">{staff.role}</p>
                    </div>
                    <span className={`px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider ${
                      staff.status === 'active' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'
                    }`}>{staff.status}</span>
                  </div>

                  <div className="space-y-2 bg-gray-50 p-4 rounded-2xl mb-4">
                    {staff.phone && (
                      <div className="flex items-center gap-2 text-gray-600">
                        <Phone className="w-3.5 h-3.5 text-blue-500" />
                        <span className="text-[11px] font-bold">{staff.phone}</span>
                      </div>
                    )}
                    {staff.address && (
                      <div className="flex items-center gap-2 text-gray-600">
                        <MapPin className="w-3.5 h-3.5 text-blue-500" />
                        <span className="text-[11px] font-bold">{staff.address}</span>
                      </div>
                    )}
                    {staff.contact_person && (
                      <div className="flex items-center gap-2 text-gray-600">
                        <Contact2 className="w-3.5 h-3.5 text-blue-500" />
                        <span className="text-[11px] font-bold">ICE: {staff.contact_person} ({staff.emergency_phone || 'N/A'})</span>
                      </div>
                    )}
                  </div>

                  <div className="flex gap-2">
                    <button 
                      onClick={() => handleOpenEditModal(staff)}
                      className="flex-1 flex items-center justify-center gap-1.5 py-3 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-2xl text-[9px] font-black uppercase tracking-wider active:scale-95 transition-all"
                    >
                      <Edit2 className="w-3.5 h-3.5" />
                      Modify Record
                    </button>
                    <button 
                      onClick={() => handleArchive(staff.id, staff.name)}
                      className="p-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-2xl active:scale-95 transition-all"
                    >
                      <Archive className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )
        ) : (
          adminStaff.length === 0 ? (
            <div className="bg-white rounded-3xl p-10 text-center border border-dashed border-gray-200">
              <Users className="w-12 h-12 text-gray-200 mx-auto mb-3" />
              <p className="text-sm font-black text-gray-400 uppercase tracking-widest">No admin accounts found</p>
            </div>
          ) : (
            <div className="space-y-3">
              {adminStaff.map((admin) => (
                <div key={admin.id} className="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
                  <div>
                    <h3 className="text-sm font-black text-gray-900 leading-tight">{admin.full_name}</h3>
                    <p className="text-[10px] font-bold text-gray-400 uppercase mt-1">{admin.role} • {admin.email}</p>
                  </div>
                  <span className={`p-2 rounded-xl ${admin.is_active === 1 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'}`}>
                    {admin.is_active === 1 ? <ShieldCheck className="w-4 h-4" /> : <ShieldAlert className="w-4 h-4" />}
                  </span>
                </div>
              ))}
            </div>
          )
        )}
      </div>

      {/* Auxiliary Staff Create / Edit Modal */}
      {showModal && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
          <div className="bg-white w-full max-w-sm rounded-[2rem] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200 flex flex-col max-h-[85vh]">
            <div className="bg-blue-600 p-5 flex flex-col items-center gap-2 text-white shrink-0">
              <h3 className="font-black text-md text-center leading-tight">
                {editingStaff ? "Update Staff Record" : "Add Auxiliary Staff"}
              </h3>
              <p className="text-blue-100 text-[9px] font-bold uppercase tracking-widest text-center">Standard Employment registry</p>
            </div>

            <form onSubmit={handleFormSubmit} className="p-5 space-y-4 overflow-y-auto flex-1">
              <div>
                <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Staff Member Name</label>
                <input 
                  type="text"
                  required
                  placeholder="Letters only..."
                  className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Assigned Role</label>
                  <input 
                    type="text"
                    required
                    placeholder="e.g. Inspector/Cashier"
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={role}
                    onChange={(e) => setRole(e.target.value)}
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Phone Number</label>
                  <input 
                    type="text"
                    placeholder="Digits only..."
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                  />
                </div>
              </div>

              <div>
                <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Home Address</label>
                <input 
                  type="text"
                  placeholder="Complete street address..."
                  className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                  value={address}
                  onChange={(e) => setAddress(e.target.value)}
                />
              </div>

              <div className="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">ICE Contact Person</label>
                  <input 
                    type="text"
                    placeholder="Emergency name..."
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={contactPerson}
                    onChange={(e) => setContactPerson(e.target.value)}
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">ICE Phone No</label>
                  <input 
                    type="text"
                    placeholder="Emergency digits..."
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={emergencyPhone}
                    onChange={(e) => setEmergencyPhone(e.target.value)}
                  />
                </div>
              </div>

              <div>
                <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Employment Status</label>
                <select
                  value={status}
                  onChange={(e) => setStatus(e.target.value)}
                  className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <div className="flex gap-3 pt-3">
                <button 
                  type="button"
                  onClick={() => setShowModal(false)}
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
                  Register
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
