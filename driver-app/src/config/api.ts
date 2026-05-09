export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'https://eurotaxisystem.site/api';

export const endpoints = {
  login: `${API_BASE_URL}/login`,
  logout: `${API_BASE_URL}/logout`,
  sendDeviceOtp: `${API_BASE_URL}/send-device-otp`,
  verifyDeviceOtp: `${API_BASE_URL}/verify-device-otp`,
  // Registration OTP flow
  register: `${API_BASE_URL}/driver/register`,
  verifyRegistrationOtp: `${API_BASE_URL}/driver/register/verify-otp`,
  resendRegistrationOtp: `${API_BASE_URL}/driver/register/resend-otp`,
  // Forgot Password (via SMS)
  forgotPassword: `${API_BASE_URL}/forgot-password`,
  verifyResetOtp: `${API_BASE_URL}/verify-otp`,
  resetPassword: `${API_BASE_URL}/reset-password`,
  // Driver API
  driverPerformance: `${API_BASE_URL}/driver/performance`,
  driverEarnings: `${API_BASE_URL}/driver/earnings`,
  driverVehicle: `${API_BASE_URL}/driver/vehicle`,
  driverRescue: `${API_BASE_URL}/driver/rescue`,
  driverLocation: `${API_BASE_URL}/driver/location`,
  driverAccount: `${API_BASE_URL}/driver/account`,
  deleteAccount: `${API_BASE_URL}/driver/account/delete`,
  boundaryHistory: `${API_BASE_URL}/driver/boundary-history`,
  chargesIncentives: `${API_BASE_URL}/driver/charges-incentives`,
  nearby: `${API_BASE_URL}/driver/nearby`,
  changePassword: `${API_BASE_URL}/driver/change-password`,
  getProfile: `${API_BASE_URL}/driver/profile`,
  updateProfile: `${API_BASE_URL}/driver/update-profile`,
  saveNotificationToken: `${API_BASE_URL}/driver/notifications/save-token`,
  supportTickets: `${API_BASE_URL}/driver/support/tickets`,
  supportMessages: `${API_BASE_URL}/driver/support/messages`,
  sendSupportMessage: `${API_BASE_URL}/driver/support/messages/send`,
  uploadDocument: `${API_BASE_URL}/driver/upload-document`,
  performanceHistory: `${API_BASE_URL}/driver/performance-history`,
  driverIncidents: `${API_BASE_URL}/driver/incidents`,
};
