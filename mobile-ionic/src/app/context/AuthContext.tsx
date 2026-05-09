import { createContext, useContext, useState, useEffect, ReactNode } from "react";
import axios from "axios";

const API_BASE_URL = import.meta.env.VITE_API_URL || "https://eurotaxisystem.site/api";

interface User {
  id: string;
  email: string;
  name: string;
  role: string;
}

interface AuthContextType {
  user: User | null;
  token: string | null;
  isLoading: boolean;
  login: (loginIdentifier: string, password: string) => Promise<any>;
  signup: (formData: any) => Promise<void>;
  logout: () => Promise<void>;
  verifyOTP: (userToken: string, otp: string, deviceName: string) => Promise<void>;
  resendOTP: (userToken: string, method: 'email' | 'phone') => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const initAuth = async () => {
      setIsLoading(true);
      try {
        const storedToken = localStorage.getItem("auth_token");
        const storedUser = localStorage.getItem("user");

        if (storedToken && storedUser) {
          // Validate token with server before trusting it
          try {
            const resp = await axios.get(`${API_BASE_URL}/user`, {
              headers: { Authorization: `Bearer ${storedToken}`, Accept: 'application/json' }
            });
            // Token is valid — use stored data
            setToken(storedToken);
            setUser(JSON.parse(storedUser));
            axios.defaults.headers.common["Authorization"] = `Bearer ${storedToken}`;
          } catch (validationErr: any) {
            // Token invalid/expired — clear everything
            localStorage.removeItem("auth_token");
            localStorage.removeItem("user");
            setToken(null);
            setUser(null);
          }
        } else {
          // No stored credentials
          setToken(null);
          setUser(null);
        }
      } catch (e) {
        console.error("Auth init error:", e);
        localStorage.removeItem("auth_token");
        localStorage.removeItem("user");
      } finally {
        setIsLoading(false);
      }
    };

    initAuth();
  }, []);

  // Initialize push notifications on mobile devices when authenticated
  useEffect(() => {
    if (token) {
      import("../services/pushNotifications").then(({ initPushNotifications }) => {
        initPushNotifications().catch(err => {
          console.error("Error launching push notifications init:", err);
        });
      });
    }
  }, [token]);

  const login = async (loginIdentifier: string, password: string) => {
    try {
      const formData = new FormData();
      formData.append('login', loginIdentifier);
      formData.append('password', password);
      formData.append('device_name', 'mobile_app');

      const response = await axios.post(`${API_BASE_URL}/login`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
          'Accept': 'application/json'
        }
      });

      if (response.data.success) {
        if (response.data.mfa_required) {
          // Return the MFA data so the Login component can show the OTP screen
          return response.data;
        }

        const { token, user } = response.data;
        localStorage.setItem("auth_token", token);
        localStorage.setItem("user", JSON.stringify(user));
        
        setToken(token);
        setUser(user);
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
        return response.data;
      } else {
        throw new Error(response.data.message || "Login failed");
      }
    } catch (error: any) {
      console.error("Login error:", error);
      throw error;
    }
  };

  const verifyOTP = async (userToken: string, otp: string, deviceName: string) => {
    try {
      const response = await axios.post(`${API_BASE_URL}/verify-device-otp`, {
        user_token: userToken,
        otp: otp,
        device_name: deviceName
      });

      if (response.data.success) {
        const { token, user } = response.data;
        localStorage.setItem("auth_token", token);
        localStorage.setItem("user", JSON.stringify(user));
        
        setToken(token);
        setUser(user);
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
      } else {
        throw new Error(response.data.message || "OTP Verification failed");
      }
    } catch (error: any) {
      console.error("OTP verification error:", error);
      throw error;
    }
  };

  const resendOTP = async (userToken: string, method: 'email' | 'phone') => {
    try {
      const response = await axios.post(`${API_BASE_URL}/send-device-otp`, {
        user_token: userToken,
        method: method
      });
      if (!response.data.success) {
        throw new Error(response.data.message || "Failed to resend OTP");
      }
    } catch (error: any) {
      console.error("Resend OTP error:", error);
      throw error;
    }
  };


  const signup = async (formData: any) => {
    try {
      const response = await axios.post(`${API_BASE_URL}/register`, formData);
      if (!response.data.success) {
        throw new Error(response.data.message || "Signup failed");
      }
    } catch (error: any) {
      console.error("Signup error:", error);
      throw error;
    }
  };

  const logout = async () => {
    try {
      if (token) {
        await axios.post(`${API_BASE_URL}/logout`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
      }
    } catch (error) {
      console.error("Logout error:", error);
    } finally {
      localStorage.removeItem("auth_token");
      localStorage.removeItem("user");
      setToken(null);
      setUser(null);
      delete axios.defaults.headers.common["Authorization"];
    }
  };

  return (
    <AuthContext.Provider value={{ user, token, isLoading, login, signup, logout, verifyOTP, resendOTP }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
}
