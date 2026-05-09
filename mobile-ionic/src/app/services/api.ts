import axios from 'axios';

// Uses VITE_API_URL from .env (dev) or .env.production (build/APK)
const API_BASE_URL = import.meta.env.VITE_API_URL || 'https://eurotaxisystem.site/api';

const api = axios.create({
    baseURL: API_BASE_URL,
    timeout: 15000, // 15 second timeout
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Add a request interceptor to include the Sanctum token
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Add a response interceptor to handle unauthorized errors
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.code === 'ECONNABORTED') {
            error.message = 'Connection timed out. Please check your internet connection.';
        } else if (!error.response) {
            error.message = 'Unable to connect to server. Please check your internet connection.';
        } else if (error.response.status === 401) {
            // Clear auth state and redirect to login
            localStorage.removeItem('auth_token');
            localStorage.removeItem('user');
            if (window.location.pathname !== '/login') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);

export default api;
