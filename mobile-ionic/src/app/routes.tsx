import React from "react";
import { createBrowserRouter } from "react-router-dom";
import { Layout } from "./components/Layout";
import { ProtectedRoute } from "./components/ProtectedRoute";
import { OwnerPanel } from "./components/OwnerPanel";

import { Dashboard } from "./components/Dashboard";
import { UnitManagement } from "./components/UnitManagement";
import { UnitDetail } from "./components/UnitDetail";
import { BoundaryManagement } from "./components/BoundaryManagement";
import { Maintenance } from "./components/Maintenance";
import { DriverManagement } from "./components/DriverManagement";
import { DriverBehavior } from "./components/DriverBehavior";
import { OfficeExpenses } from "./components/OfficeExpenses";
import { Analytics } from "./components/Analytics";
import { Login } from "./components/Login";
import { Signup } from "./components/Signup";
import { About } from "./components/About";
import { ForgotPassword } from "./components/ForgotPassword";
import { OTPVerificationStandalone } from "./components/OTPVerificationStandalone";
import { ResetPassword } from "./components/ResetPassword";
import { LiveTracking } from "./components/LiveTracking";
import { UnitTracking } from "./components/UnitTracking";
import { DashcamViewer } from "./components/DashcamViewer";

// Missing pages
import { Franchise } from "./components/Franchise";
import { CodingManagement } from "./components/CodingManagement";
import { SalaryManagement } from "./components/SalaryManagement";
import { HistoryLogs } from "./components/HistoryLogs";
import { UnitProfitability } from "./components/UnitProfitability";
import { StaffRecords } from "./components/StaffRecords";
import { Archive } from "./components/Archive";

export const router = createBrowserRouter([
  {
    path: "/login",
    Component: Login,
  },
  {
    path: "/forgot-password",
    Component: ForgotPassword,
  },
  {
    path: "/verify-otp",
    Component: OTPVerificationStandalone,
  },
  {
    path: "/reset-password",
    Component: ResetPassword,
  },
  {
    path: "/about",
    Component: About,
  },
  {
    path: "/",
    element: <ProtectedRoute><Layout /></ProtectedRoute>,
    children: [
      { index: true, Component: Dashboard },
      { path: "owner", Component: OwnerPanel },
      { path: "units", Component: UnitManagement },
      { path: "units/:id", Component: UnitDetail },
      { path: "boundaries", Component: BoundaryManagement },
      { path: "maintenance", Component: Maintenance },
      { path: "drivers", Component: DriverManagement },
      { path: "driver-behavior", Component: DriverBehavior },
      { path: "office-expenses", Component: OfficeExpenses },
      { path: "analytics", Component: Analytics },
      { path: "live-tracking", Component: LiveTracking },
      { path: "live-tracking/:unitId", Component: UnitTracking },
      { path: "live-tracking/:unitId/dashcam", Component: DashcamViewer },
      { path: "franchise", Component: Franchise },
      { path: "coding", Component: CodingManagement },
      { path: "salary", Component: SalaryManagement },
      { path: "history", Component: HistoryLogs },
      { path: "profitability", Component: UnitProfitability },
      { path: "staff", Component: StaffRecords },
      { path: "archive", Component: Archive },
    ],
  },
]);
