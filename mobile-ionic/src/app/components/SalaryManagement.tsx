import { useState, useEffect, useCallback } from "react";
import { Search, RefreshCw, CreditCard, Plus, Calendar, DollarSign, Edit2, Trash2, Loader2, Save, X, Eye } from "lucide-react";
import api from "../services/api";
import { toast } from "sonner";

interface SalaryRecord {
  id: number;
  employee_id: number;
  source: string;
  employee_name: string;
  email: string | null;
  position: string;
  basic_salary: number;
  overtime_pay: number | null;
  holiday_pay: number | null;
  night_differential: number | null;
  allowance: number | null;
  total_pay: number;
  month: number;
  year: number;
  pay_date: string;
}

interface DropdownEmployee {
  id: number;
  name: string;
  role: string;
  source: string; // user, staff
}

export function SalaryManagement() {
  const [salaries, setSalaries] = useState<SalaryRecord[]>([]);
  const [employees, setEmployees] = useState<DropdownEmployee[]>([]);
  const [summary, setSummary] = useState({
    total_employees: 0,
    total_salaries: 0,
    net_profit: 0,
    avg_salary: 0
  });
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedMonth, setSelectedMonth] = useState<number>(new Date().getMonth() + 1);
  const [selectedYear, setSelectedYear] = useState<number>(new Date().getFullYear());

  // Form Modal States
  const [showModal, setShowModal] = useState(false);
  const [editingRecord, setEditingRecord] = useState<SalaryRecord | null>(null);
  const [formLoading, setFormLoading] = useState(false);

  // Form Inputs
  const [employeeRaw, setEmployeeRaw] = useState("");
  const [employeeType, setEmployeeType] = useState("");
  const [basicSalary, setBasicSalary] = useState<number>(0);
  const [overtimePay, setOvertimePay] = useState<number>(0);
  const [holidayPay, setHolidayPay] = useState<number>(0);
  const [nightDifferential, setNightDifferential] = useState<number>(0);
  const [allowance, setAllowance] = useState<number>(0);
  const [payDate, setPayDate] = useState("");

  const months = [
    { value: 1, label: "January" },
    { value: 2, label: "February" },
    { value: 3, label: "March" },
    { value: 4, label: "April" },
    { value: 5, label: "May" },
    { value: 6, label: "June" },
    { value: 7, label: "July" },
    { value: 8, label: "August" },
    { value: 9, label: "September" },
    { value: 10, label: "October" },
    { value: 11, label: "November" },
    { value: 12, label: "December" },
  ];

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const res = await api.get(`/salaries?month=${selectedMonth}&year=${selectedYear}&search=${searchQuery}`);
      if (res.data.success) {
        setSalaries(res.data.salaries);
        setSummary(res.data.summary);
        setEmployees(res.data.employees);
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Failed to load salary data");
    } finally {
      setLoading(false);
    }
  }, [selectedMonth, selectedYear, searchQuery]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const handleOpenCreateModal = () => {
    setEditingRecord(null);
    setEmployeeRaw("");
    setEmployeeType("");
    setBasicSalary(0);
    setOvertimePay(0);
    setHolidayPay(0);
    setNightDifferential(0);
    setAllowance(0);
    setPayDate(new Date().toISOString().split('T')[0]);
    setShowModal(true);
  };

  const handleOpenEditModal = (r: SalaryRecord) => {
    setEditingRecord(r);
    setEmployeeRaw(`${r.source}_${r.employee_id}`);
    setEmployeeType(r.position);
    setBasicSalary(r.basic_salary);
    setOvertimePay(r.overtime_pay || 0);
    setHolidayPay(r.holiday_pay || 0);
    setNightDifferential(r.night_differential || 0);
    setAllowance(r.allowance || 0);
    setPayDate(r.pay_date);
    setShowModal(true);
  };

  const handleEmployeeChange = (val: string) => {
    setEmployeeRaw(val);
    const selected = employees.find(e => `${e.source}_${e.id}` === val);
    if (selected) {
      setEmployeeType(selected.role);
    }
  };

  const handleFormSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFormLoading(true);
    try {
      const payload = {
        employee_raw: employeeRaw,
        employee_type: employeeType,
        basic_salary: basicSalary,
        overtime_pay: overtimePay || null,
        holiday_pay: holidayPay || null,
        night_differential: nightDifferential || null,
        allowance: allowance || null,
        month: selectedMonth,
        year: selectedYear,
        pay_date: payDate,
      };

      let res;
      if (editingRecord) {
        res = await api.put(`/salaries/${editingRecord.id}`, payload);
      } else {
        res = await api.post("/salaries", payload);
      }

      if (res.data.success) {
        toast.success(res.data.message || "Salary processed successfully");
        setShowModal(false);
        fetchData();
      }
    } catch (err: any) {
      toast.error(err.response?.data?.message || "Error processing payroll. Confirm inputs.");
    } finally {
      setFormLoading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!window.confirm("Delete this salary payment history?")) return;
    try {
      const res = await api.delete(`/salaries/${id}`);
      if (res.data.success) {
        toast.success("Record deleted");
        fetchData();
      }
    } catch (err: any) {
      toast.error("Failed to delete salary record");
    }
  };

  const computedTotal = Number(basicSalary) + Number(overtimePay) + Number(holidayPay) + Number(nightDifferential) + Number(allowance);

  return (
    <div className="flex flex-col min-h-full bg-gray-50 pb-20">
      {/* Header */}
      <div className="bg-white px-4 pt-6 pb-4 border-b border-gray-200">
        <div className="flex items-center justify-between mb-4">
          <div>
            <h1 className="text-xl font-black text-gray-900 leading-tight">Payroll Management</h1>
            <p className="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Process staff salaries & allowances</p>
          </div>
          <div className="flex gap-2">
            <button onClick={handleOpenCreateModal} className="flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white rounded-xl active:scale-95 transition-all shadow-md shadow-blue-200">
              <Plus className="w-4 h-4" />
              <span className="text-[10px] font-black uppercase tracking-wider">Disburse</span>
            </button>
            <button onClick={fetchData} className="p-2.5 bg-gray-100 rounded-xl active:bg-gray-200 transition-colors">
              <RefreshCw className={`w-4 h-4 text-gray-500 ${loading ? 'animate-spin' : ''}`} />
            </button>
          </div>
        </div>

        {/* Dynamic Horizontal Overview */}
        <div className="grid grid-cols-4 gap-2 mb-4">
          <div className="bg-blue-50 border border-blue-100 p-2.5 rounded-2xl">
            <p className="text-[8px] font-black uppercase text-blue-500 tracking-wider">Paid Count</p>
            <p className="text-sm font-black text-blue-700 leading-none mt-1">{salaries.length}</p>
          </div>
          <div className="bg-green-50 border border-green-100 p-2.5 rounded-2xl col-span-2">
            <p className="text-[8px] font-black uppercase text-green-500 tracking-wider">Total Disbursed</p>
            <p className="text-sm font-black text-green-700 leading-none mt-1">₱{summary.total_salaries.toLocaleString()}</p>
          </div>
          <div className="bg-gray-50 border border-gray-100 p-2.5 rounded-2xl">
            <p className="text-[8px] font-black uppercase text-gray-500 tracking-wider">Avg Pay</p>
            <p className="text-sm font-black text-gray-700 leading-none mt-1">₱{Math.round(summary.avg_salary).toLocaleString()}</p>
          </div>
        </div>

        {/* Month/Year Filters */}
        <div className="grid grid-cols-2 gap-2 mb-4">
          <select 
            value={selectedMonth} 
            onChange={(e) => setSelectedMonth(Number(e.target.value))}
            className="px-3.5 py-2.5 bg-gray-100 border-none rounded-xl text-xs font-black text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          >
            {months.map((m) => (
              <option key={m.value} value={m.value}>{m.label}</option>
            ))}
          </select>

          <select 
            value={selectedYear} 
            onChange={(e) => setSelectedYear(Number(e.target.value))}
            className="px-3.5 py-2.5 bg-gray-100 border-none rounded-xl text-xs font-black text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
          >
            {[2024, 2025, 2026, 2027, 2028].map((y) => (
              <option key={y} value={y}>{y}</option>
            ))}
          </select>
        </div>

        {/* Search */}
        <div className="relative">
          <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
          <input 
            type="text" 
            placeholder="Search by staff name, position..."
            className="w-full pl-10 pr-4 py-3 bg-gray-100 border-none rounded-2xl text-sm font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
          />
        </div>
      </div>

      {/* Salary Records List */}
      <div className="flex-1 p-4">
        {loading ? (
          <div className="flex flex-col items-center justify-center py-20 gap-4">
            <Loader2 className="w-8 h-8 text-blue-500 animate-spin" />
            <p className="text-xs font-black text-gray-400 uppercase tracking-widest">Loading payroll records...</p>
          </div>
        ) : salaries.length === 0 ? (
          <div className="bg-white rounded-3xl p-10 text-center border border-dashed border-gray-200">
            <CreditCard className="w-12 h-12 text-gray-200 mx-auto mb-3" />
            <p className="text-sm font-black text-gray-400 uppercase tracking-widest">No disbursements found</p>
          </div>
        ) : (
          <div className="space-y-3">
            {salaries.map((record) => (
              <div key={record.id} className="bg-white rounded-3xl p-5 border border-gray-100 shadow-sm">
                <div className="flex items-start justify-between mb-3">
                  <div>
                    <h3 className="text-sm font-black text-gray-900 leading-tight">{record.employee_name}</h3>
                    <p className="text-[10px] font-bold text-gray-400 uppercase mt-0.5">{record.position} • {record.source.toUpperCase()}</p>
                  </div>
                  <span className="px-2.5 py-1 bg-green-50 text-green-600 rounded-lg text-[10px] font-black">
                    ₱{record.total_pay.toLocaleString()}
                  </span>
                </div>

                <div className="grid grid-cols-3 gap-2.5 mb-4 bg-gray-50 p-3 rounded-2xl text-center">
                  <div>
                    <span className="text-[8px] font-black text-gray-400 uppercase tracking-wider">Basic Pay</span>
                    <p className="text-[10px] font-black text-gray-700">₱{(record.basic_salary || 0).toLocaleString()}</p>
                  </div>
                  <div>
                    <span className="text-[8px] font-black text-gray-400 uppercase tracking-wider">Allowance</span>
                    <p className="text-[10px] font-black text-gray-700">₱{(record.allowance || 0).toLocaleString()}</p>
                  </div>
                  <div>
                    <span className="text-[8px] font-black text-gray-400 uppercase tracking-wider">Disbursed On</span>
                    <p className="text-[10px] font-black text-blue-600">{record.pay_date}</p>
                  </div>
                </div>

                <div className="flex gap-2">
                  <button 
                    onClick={() => handleOpenEditModal(record)}
                    className="flex-1 flex items-center justify-center gap-1.5 py-3 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-2xl text-[9px] font-black uppercase tracking-wider active:scale-95 transition-all"
                  >
                    <Edit2 className="w-3.5 h-3.5" />
                    Modify
                  </button>
                  <button 
                    onClick={() => handleDelete(record.id)}
                    className="flex-1 flex items-center justify-center gap-1.5 py-3 bg-red-50 hover:bg-red-100 text-red-600 rounded-2xl text-[9px] font-black uppercase tracking-wider active:scale-95 transition-all"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                    Remove
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>

      {/* Disburse Salary Modal */}
      {showModal && (
        <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
          <div className="bg-white w-full max-w-sm rounded-[2rem] overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200 flex flex-col max-h-[85vh]">
            <div className="bg-blue-600 p-5 flex flex-col items-center gap-2 text-white shrink-0">
              <h3 className="font-black text-md text-center leading-tight">
                {editingRecord ? "Adjust Payroll Record" : "Disburse Employee Salary"}
              </h3>
              <p className="text-blue-100 text-[9px] font-bold uppercase tracking-widest text-center">Standard Corporate Payroll Ledger</p>
            </div>

            <form onSubmit={handleFormSubmit} className="p-5 space-y-4 overflow-y-auto flex-1">
              {!editingRecord && (
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Select Employee</label>
                  <select
                    required
                    value={employeeRaw}
                    onChange={(e) => handleEmployeeChange(e.target.value)}
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                  >
                    <option value="">-- Choose Account or general Staff --</option>
                    {employees.map((emp) => (
                      <option key={`${emp.source}_${emp.id}`} value={`${emp.source}_${emp.id}`}>
                        {emp.name} ({emp.role} - {emp.source.toUpperCase()})
                      </option>
                    ))}
                  </select>
                </div>
              )}

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Employee Type</label>
                  <input 
                    type="text"
                    required
                    placeholder="Operator/Admin"
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={employeeType}
                    onChange={(e) => setEmployeeType(e.target.value)}
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Pay Date</label>
                  <input 
                    type="date"
                    required
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={payDate}
                    onChange={(e) => setPayDate(e.target.value)}
                  />
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Basic Salary (₱)</label>
                  <input 
                    type="number"
                    required
                    placeholder="0"
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={basicSalary || ""}
                    onChange={(e) => setBasicSalary(Number(e.target.value))}
                  />
                </div>
                <div>
                  <label className="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5 ml-1">Allowance (₱)</label>
                  <input 
                    type="number"
                    placeholder="0"
                    className="w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-2xl text-xs font-black focus:outline-none focus:border-blue-500 transition-all"
                    value={allowance || ""}
                    onChange={(e) => setAllowance(Number(e.target.value))}
                  />
                </div>
              </div>

              <div className="grid grid-cols-3 gap-2.5">
                <div>
                  <label className="block text-[8px] font-black text-gray-400 uppercase tracking-wider mb-1">Overtime (₱)</label>
                  <input 
                    type="number"
                    placeholder="0"
                    className="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-[11px] font-bold focus:outline-none"
                    value={overtimePay || ""}
                    onChange={(e) => setOvertimePay(Number(e.target.value))}
                  />
                </div>
                <div>
                  <label className="block text-[8px] font-black text-gray-400 uppercase tracking-wider mb-1">Holiday (₱)</label>
                  <input 
                    type="number"
                    placeholder="0"
                    className="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-[11px] font-bold focus:outline-none"
                    value={holidayPay || ""}
                    onChange={(e) => setHolidayPay(Number(e.target.value))}
                  />
                </div>
                <div>
                  <label className="block text-[8px] font-black text-gray-400 uppercase tracking-wider mb-1">Night Diff (₱)</label>
                  <input 
                    type="number"
                    placeholder="0"
                    className="w-full px-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-[11px] font-bold focus:outline-none"
                    value={nightDifferential || ""}
                    onChange={(e) => setNightDifferential(Number(e.target.value))}
                  />
                </div>
              </div>

              {/* Real time cumulative visualizer */}
              <div className="bg-blue-50 border border-blue-100 p-3.5 rounded-2xl flex items-center justify-between">
                <span className="text-[10px] font-black text-blue-700 uppercase tracking-widest">Cumulative Pay:</span>
                <span className="text-md font-black text-blue-800">₱{computedTotal.toLocaleString()}</span>
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
                  Disburse Pay
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
