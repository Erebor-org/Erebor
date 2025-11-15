import { defineStore } from 'pinia';
import axios from '@/config/axios';
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
          localStorage.removeItem('token');
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
    
    // ✅ Get token from localStorage
    const token = localStorage.getItem('token');
    
    // ✅ Consistency check: ensure token and user are both present or both absent
    // If we have token but no user (or vice versa), clear both to prevent inconsistent state
    if ((token && !user) || (!token && user)) {
      console.warn('Inconsistent auth state detected (token without user or user without token), clearing both');
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      return {
        token: null,
        user: null,
        isLoading: false,
      };
    }
    
    return {
      token: token || null,
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
    async register(username, password, characterId) {
      try {
        const response = await axios.post(`${API_URL}/register`, { 
          username, 
          password,
          characterId 
        });

        if (response.data.token && response.data.user) {
          this.token = response.data.token;
          this.user = response.data.user;
          
          localStorage.setItem('token', this.token);
          if (this.user && this.user.username) {
            localStorage.setItem('user', JSON.stringify(this.user));
          }

          // ✅ Redirect to /home using the global router
          router.push('/home');
        } else {
          throw new Error('Invalid response from server');
        }
      } catch (error) {
        console.error("Erreur d'inscription:", error.response?.data || error.message);
        // Re-throw error so calling component can handle it
        throw error;
      }
    },

    async login(username, password) {
      this.isLoading = true;
      try {
        const response = await axios.post(`${API_URL}/login`, { username, password });

        if (response.data.token && response.data.user) {
          this.token = response.data.token;
          this.user = response.data.user;
          
          // Only store if we have valid data
          localStorage.setItem("token", this.token);
          if (this.user && this.user.username) {
            localStorage.setItem("user", JSON.stringify(this.user));
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

    async updateCharacter(characterId) {
      try {
        const response = await axios.put(`${API_URL}/user/character`, 
          { characterId }
        );

        if (response.data.user) {
          // Update user in store with complete user data including character
          this.user = response.data.user;
          localStorage.setItem('user', JSON.stringify(this.user));
          
          return response.data;
        }
      } catch (error) {
        console.error('Error updating character:', error);
        throw error;
      }
    },

    async fetchUserProfile() {
      try {
        const response = await axios.get(`${API_URL}/user/profile`);
        
        // Check for forced disconnect
        try {
          const disconnectCheck = await axios.get(`${API_URL}/user/check-disconnect`);
          if (disconnectCheck.data?.shouldDisconnect) {
            // User has been force disconnected
            this.logout();
            throw new Error('Vous avez été déconnecté de force');
          }
        } catch {
          // Ignore disconnect check failures - user might have expired token but should stay logged in
        }
        
        if (response.data) {
          this.user = response.data;
          localStorage.setItem('user', JSON.stringify(this.user));
        }
        
        return response.data;
      } catch {
        // Don't throw the error - let the caller handle it
        // This prevents the App.vue from catching and logging out on every refresh
        return null;
      }
    },
  },
});
