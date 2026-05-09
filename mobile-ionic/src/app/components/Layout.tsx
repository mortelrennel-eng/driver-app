import { NavLink, Outlet } from "react-router-dom";
import { LayoutDashboard, Car, DollarSign, Wrench, Users, Award, Receipt, BarChart3, Menu, X, LogOut, Radio, Crown, FileText, Wallet, Calendar, AlertTriangle, History, TrendingUp, UserCog, Archive } from "lucide-react";
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";

export function Layout() {
  const navigate = useNavigate();
  const { logout, user, isLoading } = useAuth();
  const [sidebarOpen, setSidebarOpen] = useState(false);

  const handleLogout = async () => {
    await logout();
    navigate("/login");
  };

  const navItems = [
    { to: "/owner", icon: Crown, label: "Owner Panel", role: "super_admin" },
    { to: "/", icon: LayoutDashboard, label: "Dashboard", exact: true },
    { to: "/units", icon: Car, label: "Unit Management" },
    { to: "/drivers", icon: Users, label: "Driver Management" },
    { to: "/live-tracking", icon: Radio, label: "Live Tracking" },
    { to: "/franchise", icon: FileText, label: "Franchise" },
    { to: "/boundaries", icon: Wallet, label: "Boundaries" },
    { to: "/maintenance", icon: Wrench, label: "Maintenance" },
    { to: "/coding", icon: Calendar, label: "Coding Management" },
    { to: "/driver-behavior", icon: AlertTriangle, label: "Driver Behavior" },
    { to: "/office-expenses", icon: Receipt, label: "Office Expenses" },
    { to: "/salary", icon: DollarSign, label: "Salary Management" },
    { to: "/analytics", icon: BarChart3, label: "Analytics" },
    { to: "/history", icon: History, label: "History Logs" },
    { to: "/profitability", icon: TrendingUp, label: "Unit Profitability" },
    { to: "/staff", icon: UserCog, label: "Staff Records" },
    { to: "/archive", icon: Archive, label: "Archive" },
  ];

  // Filter items based on user role
  const filteredNavItems = navItems.filter(item => {
    if (item.role === "super_admin" && user?.role !== "super_admin") return false;
    return true;
  });

  const renderNavLinks = (items: typeof navItems, onClick?: () => void) => (
    items.map((item) => (
      <NavLink
        key={item.to}
        to={item.to}
        end={item.exact}
        onClick={onClick}
        className={({ isActive }) =>
          `group flex items-center px-3 py-2 text-sm rounded-md transition-colors ${
            isActive
              ? "bg-gray-800 text-white"
              : "text-gray-300 hover:bg-gray-800 hover:text-white"
          }`
        }
      >
        <item.icon className="mr-3 h-5 w-5" />
        {item.label}
      </NavLink>
    ))
  );

  if (isLoading) return <div className="h-screen flex items-center justify-center">Loading Eurotaxi...</div>;

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Sidebar for desktop */}
      <aside className="hidden lg:fixed lg:inset-y-0 lg:flex lg:w-64 lg:flex-col bg-white border-r border-gray-200">
        <div className="flex flex-col flex-grow pt-5 overflow-y-auto">
          <div className="flex flex-col items-center flex-shrink-0 px-4 mb-4">
            <div className="flex items-center">
              <img src="/assets/logo.png" alt="Eurotaxi" className="h-10 object-contain" />
              <span className="ml-2 text-2xl font-bold text-gray-900 tracking-tight">
                <span className="text-amber-500">Euro</span>taxi
              </span>
            </div>
            <p className="text-[10px] font-bold text-gray-500 tracking-widest mt-1">FLEET MANAGEMENT</p>
          </div>
          <nav className="mt-4 flex-1 px-3 space-y-1">
            {navItems.filter(item => {
              if (item.role === "super_admin" && user?.role !== "super_admin") return false;
              return true;
            }).map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                end={item.exact}
                className={({ isActive }) =>
                  `group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all ${
                    isActive
                      ? "bg-amber-50 text-amber-700 shadow-sm border border-amber-100"
                      : "text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                  }`
                }
              >
                <item.icon className={`mr-3 h-5 w-5 ${window.location.pathname === item.to || (item.to !== '/' && window.location.pathname.startsWith(item.to)) ? "text-amber-500" : "text-gray-400"}`} />
                {item.label}
              </NavLink>
            ))}
            <div className="pt-4 mt-4 border-t border-gray-100">
              <button
                onClick={handleLogout}
                className="w-full group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all text-red-600 hover:bg-red-50"
              >
                <LogOut className="mr-3 h-5 w-5 text-red-400" />
                Logout
              </button>
            </div>
          </nav>
        </div>
      </aside>

      {/* Mobile sidebar */}
      {sidebarOpen && (
        <>
          <div
            className="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-20 lg:hidden"
            onClick={() => setSidebarOpen(false)}
          />
          <aside className="fixed inset-y-0 left-0 flex flex-col w-64 bg-white border-r border-gray-200 z-30 lg:hidden">
            <div className="flex flex-col flex-grow pt-5 overflow-y-auto">
              <div className="flex items-center justify-between px-4 mb-4">
                <div className="flex flex-col">
                  <div className="flex items-center">
                    <img src="/assets/logo.png" alt="Eurotaxi" className="h-8 object-contain" />
                    <span className="ml-2 text-xl font-bold text-gray-900 tracking-tight">
                      <span className="text-amber-500">Euro</span>taxi
                    </span>
                  </div>
                </div>
                <button
                  onClick={() => setSidebarOpen(false)}
                  className="text-gray-400 hover:text-gray-600"
                >
                  <X className="h-6 w-6" />
                </button>
              </div>
              <nav className="mt-4 flex-1 px-3 space-y-1">
                {navItems.filter(item => {
                  if (item.role === "super_admin" && user?.role !== "super_admin") return false;
                  return true;
                }).map((item) => (
                  <NavLink
                    key={item.to}
                    to={item.to}
                    end={item.exact}
                    onClick={() => setSidebarOpen(false)}
                    className={({ isActive }) =>
                      `group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all ${
                        isActive
                          ? "bg-amber-50 text-amber-700 shadow-sm border border-amber-100"
                          : "text-gray-600 hover:bg-gray-50 hover:text-gray-900"
                      }`
                    }
                  >
                    <item.icon className={`mr-3 h-5 w-5 ${window.location.pathname === item.to || (item.to !== '/' && window.location.pathname.startsWith(item.to)) ? "text-amber-500" : "text-gray-400"}`} />
                    {item.label}
                  </NavLink>
                ))}
                <div className="pt-4 mt-4 border-t border-gray-100">
                  <button
                    onClick={handleLogout}
                    className="w-full group flex items-center px-3 py-2.5 text-sm font-semibold rounded-xl transition-all text-red-600 hover:bg-red-50"
                  >
                    <LogOut className="mr-3 h-5 w-5 text-red-400" />
                    Logout
                  </button>
                </div>
              </nav>
            </div>
          </aside>
        </>
      )}

      {/* Main content */}
      <div className="lg:pl-64 flex flex-col flex-1">
        <div className="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white shadow-sm border-b border-gray-200">
          <button
            type="button"
            className="px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-amber-500 lg:hidden"
            onClick={() => setSidebarOpen(true)}
          >
            <Menu className="h-6 w-6" />
          </button>
          <div className="flex-1 px-6 flex items-center justify-between">
            <h1 className="text-xl font-black text-gray-900 tracking-tight">
              {navItems.find(i => i.to === window.location.pathname)?.label || "Euro Fleet Management"}
            </h1>
            <div className="flex items-center space-x-4">
              <div className="flex items-center gap-3">
                <div className="text-right hidden sm:block">
                  <p className="text-sm font-bold text-gray-900 leading-tight">{user?.full_name || "Admin User"}</p>
                  <p className="text-[10px] text-gray-500 uppercase font-bold">{user?.role?.replace('_', ' ')}</p>
                </div>
                <div className="w-10 h-10 rounded-full bg-amber-500 text-white flex items-center justify-center font-black shadow-sm">
                  {user?.full_name?.charAt(0) || "A"}
                </div>
              </div>
            </div>
          </div>
        </div>

        <main className="flex-1">
          <div className="py-6">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
              <Outlet />
            </div>
          </div>
        </main>
      </div>
    </div>
  );
}
