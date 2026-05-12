import { createContext, useContext, useState, useEffect } from 'react';
import type { ReactNode } from 'react';
import axios from 'axios';
import { endpoints } from '../config/api';
import { Device } from '@capacitor/device';

interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  phone?: string;
  address?: string;
  license_number?: string;
  emergency_contact?: string;
  emergency_phone?: string;
}

interface AuthContextType {
  user: User | null;
  token: string | null;
  isLoading: boolean;
  login: (credentials: any) => Promise<any>;
  logout: () => Promise<void>;
  verifyOtp: (token: string, otp: string) => Promise<any>;
  sendOtp: (token: string, method: string) => Promise<any>;
  loginFromData: (token: string, userData: any) => void;
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [user, setUser] = useState<User | null>(null);
  const [token, setToken] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const initAuth = async () => {
      const storedToken = localStorage.getItem('auth_token');
      const storedUser = localStorage.getItem('auth_user');

      if (storedToken && storedUser) {
        setToken(storedToken);
        setUser(JSON.parse(storedUser));
        axios.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
      }
      setIsLoading(false);
    };
    initAuth();
  }, []);

  useEffect(() => {
    if (token) {
      import('../utils/notifications').then(({ initPushNotifications }) => {
        initPushNotifications();
      });
    }
  }, [token]);

  const getDeviceInfo = async () => {
    try {
      const info = await Device.getInfo();
      const id = await Device.getId();
      return {
        device_name: `${info.manufacturer} ${info.model}`,
        device_info: JSON.stringify({ ...info, uuid: id.identifier })
      };
    } catch (e) {
      return { device_name: 'Browser/Unknown', device_info: '{}' };
    }
  };

  const login = async (credentials: any) => {
    try {
      const deviceData = await getDeviceInfo();
      const response = await axios.post(endpoints.login, {
        ...credentials,
        ...deviceData
      });

      if (response.data.success && response.data.token) {
        setToken(response.data.token);
        setUser(response.data.user);
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('auth_user', JSON.stringify(response.data.user));
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
      }
      
      return response.data;
    } catch (error: any) {
      return error.response?.data || { success: false, message: 'Network error occurred' };
    }
  };

  const verifyOtp = async (userToken: string, otp: string) => {
    try {
      const deviceData = await getDeviceInfo();
      const response = await axios.post(endpoints.verifyDeviceOtp, {
        user_token: userToken,
        otp,
        ...deviceData
      });

      if (response.data.success && response.data.token) {
        setToken(response.data.token);
        setUser(response.data.user);
        localStorage.setItem('auth_token', response.data.token);
        localStorage.setItem('auth_user', JSON.stringify(response.data.user));
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
      }
      
      return response.data;
    } catch (error: any) {
      return error.response?.data || { success: false, message: 'Network error occurred' };
    }
  };

  const loginFromData = (token: string, userData: any) => {
    setToken(token);
    setUser(userData);
    localStorage.setItem('auth_token', token);
    localStorage.setItem('auth_user', JSON.stringify(userData));
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  };

  const sendOtp = async (userToken: string, method: string) => {
     try {
      const response = await axios.post(endpoints.sendDeviceOtp, {
        user_token: userToken,
        method
      });
      return response.data;
    } catch (error: any) {
      return error.response?.data || { success: false, message: 'Network error occurred' };
    }
  };

  const refreshUser = async () => {
    try {
      const response = await axios.get(endpoints.getProfile);
      if (response.data.success) {
        const profile = response.data.data;
        const updatedUser = {
          ...user!,
          name: profile.name,
          phone: profile.phone,
          address: profile.address,
          license_number: profile.license_number,
          emergency_contact: profile.emergency_contact,
          emergency_phone: profile.emergency_phone
        };
        setUser(updatedUser);
        localStorage.setItem('auth_user', JSON.stringify(updatedUser));
      }
    } catch (e) {
      console.error('Failed to refresh user', e);
    }
  };

  const logout = async () => {
    try {
      if (token) {
        await axios.post(endpoints.logout);
      }
    } catch (e) {
      console.error('Logout error', e);
    } finally {
      setToken(null);
      setUser(null);
      localStorage.removeItem('auth_token');
      localStorage.removeItem('auth_user');
      delete axios.defaults.headers.common['Authorization'];
    }
  };

  return (
    <AuthContext.Provider value={{ user, token, isLoading, login, logout, verifyOtp, sendOtp, loginFromData, refreshUser }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
