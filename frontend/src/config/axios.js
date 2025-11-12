import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL;

// Polling interval for checking forced disconnects (in milliseconds)
const DISCONNECT_CHECK_INTERVAL = 5000; // Check every 5 seconds

// Set up polling to check for forced disconnects
let disconnectCheckInterval = null;

export function startDisconnectPolling() {
  // Clear any existing interval
  if (disconnectCheckInterval) {
    clearInterval(disconnectCheckInterval);
  }
  
  // Only start polling if user is logged in
  const token = localStorage.getItem('token');
  if (token) {
    disconnectCheckInterval = setInterval(async () => {
      try {
        const response = await axios.get(`${API_URL}/user/check-disconnect`);
        if (response.data?.shouldDisconnect) {
          // User has been force disconnected
          localStorage.removeItem('token');
          localStorage.removeItem('user');
          if (disconnectCheckInterval) {
            clearInterval(disconnectCheckInterval);
            disconnectCheckInterval = null;
          }
          window.location.href = '/inscription';
        }
      } catch (error) {
        // Ignore errors (user might not be logged in or token expired)
        console.log('Disconnect check failed:', error);
      }
    }, DISCONNECT_CHECK_INTERVAL);
  }
}

export function stopDisconnectPolling() {
  if (disconnectCheckInterval) {
    clearInterval(disconnectCheckInterval);
    disconnectCheckInterval = null;
  }
}

// Configure axios to add JWT token to all requests
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    // For FormData, axios will automatically handle Content-Type with boundary
    // We don't need to set or delete it - axios handles it correctly
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Handle 401 unauthorized responses and forced disconnects
axios.interceptors.response.use(
  (response) => {
    // Check if this is a user profile response and check for forced disconnect
    if (response.data?.shouldDisconnect) {
      // Force disconnect detected
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/inscription';
    }
    return response;
  },
  (error) => {
    if (error.response?.status === 401) {
      // Don't redirect on 401 for characters endpoint if we're on the register page
      const isRegisterPage = window.location.pathname.includes('/inscription');
      const isCharactersEndpoint = error.config?.url?.includes('/characters/');
      
      // If it's the characters endpoint and we're on the register page, just reject the promise
      // The component will handle the error gracefully
      if (isRegisterPage && isCharactersEndpoint) {
        return Promise.reject(error);
      }
      
      // Don't redirect on 401 for profile endpoint on initial page load (refresh)
      // This allows the app to gracefully handle expired tokens without forcing logout
      const isProfileEndpoint = error.config?.url?.includes('/user/profile');
      if (isProfileEndpoint) {
        return Promise.reject(error);
      }
      
      // Don't redirect on 401 for check-disconnect endpoint
      const isCheckDisconnectEndpoint = error.config?.url?.includes('/user/check-disconnect');
      if (isCheckDisconnectEndpoint) {
        return Promise.reject(error);
      }
      
      // For other 401 errors, clear token and redirect to login
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/inscription';
    }
    return Promise.reject(error);
  }
);

export default axios;

