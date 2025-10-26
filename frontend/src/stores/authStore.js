import { defineStore } from 'pinia';
import axios from 'axios';
import router from '@/router'; // ✅ Import router globally
const API_URL = import.meta.env.VITE_API_URL;
export const useAuthStore = defineStore('auth', {
  state: () => {
    // Clean up invalid data first
    const storedUser = localStorage.getItem('user');
    if (storedUser === 'undefined' || storedUser === '"undefined"' || storedUser === 'null' || storedUser === '"null"') {
      console.warn('Found invalid user data, cleaning up...');
      localStorage.removeItem('user');
      localStorage.removeItem('token');
    }
    
    // Safely parse user from localStorage
    let user = null;
    try {
      const userStr = localStorage.getItem('user');
      // Check if storedUser is valid and not the string "undefined" or "null"
      if (userStr && userStr !== 'null' && userStr !== 'undefined' && userStr !== '"undefined"' && userStr !== '"null"') {
        user = JSON.parse(userStr);
        // Validate that user has required fields
        if (!user || !user.username) {
          console.warn('User object missing username, cleaning up...');
          localStorage.removeItem('user');
          user = null;
        }
      }
    } catch (error) {
      console.error('Error parsing user from localStorage:', error);
      // Clean up invalid data
      try {
        localStorage.removeItem('user');
        localStorage.removeItem('token');
      } catch (e) {
        console.error('Error cleaning localStorage:', e);
      }
    }
    
    return {
      token: localStorage.getItem('token') || null,
      user,
      isLoading: false,
    };
  },
  
  getters: {
    username: (state) => state.user?.username || null,
    roles: (state) => state.user?.roles || [],
    isAdmin: (state) => state.user?.roles?.includes('ROLE_ADMIN') || false,
  },

  actions: {
    async register(username, password) {
      try {
        console.log('API_URL:', API_URL);
        await axios.post(`${API_URL}/register`, { username, password });

        const response = await axios.post(`${API_URL}/login`, {
          username,
          password,
        });
        
        console.log('Register/Login response:', response.data); // Debug log

        if (response.data.token && response.data.user) {
          this.token = response.data.token;
          this.user = response.data.user;
          
          console.log('User data:', this.user); // Debug log
          
          localStorage.setItem('token', this.token);
          if (this.user && this.user.username) {
            localStorage.setItem('user', JSON.stringify(this.user));
          } else {
            console.warn('User data is missing username:', this.user);
          }

          // ✅ Redirect to /home using the global router
          router.push('/home');
        }
      } catch (error) {
        console.error("Erreur d'inscription ou de connexion:", error.response?.data || error.message);
      }
    },

    async login(username, password) {
      this.isLoading = true;
      try {
        const response = await axios.post(`${API_URL}/login`, { username, password });
        
        console.log('Login response:', response.data); // Debug log

        if (response.data.token && response.data.user) {
          this.token = response.data.token;
          this.user = response.data.user;
          
          console.log('User data:', this.user); // Debug log
          
          // Only store if we have valid data
          localStorage.setItem("token", this.token);
          if (this.user && this.user.username) {
            localStorage.setItem("user", JSON.stringify(this.user));
          } else {
            console.warn('User data is missing username:', this.user);
          }

          // ✅ Redirect only if not already on /home
          if (router.currentRoute.value.path !== "/home") {
            router.push("/home");
          }
        }
      } catch (error) {
        console.error("Login error:", error);
      } finally {
        this.isLoading = false;
      }
    },

    logout() {
      this.token = null;
      this.user = null;
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      router.push('/'); // ✅ Redirect to home after logout
    },
  },
});
