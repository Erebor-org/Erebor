import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user')) || null,
  }),

  actions: {
    async login(username, password) {
      try {
        const response = await axios.post('http://localhost:8000/api/login_check', { username, password });

        if (response.data.token) {
          this.token = response.data.token;
          this.user = response.data.user;
          localStorage.setItem('token', this.token);
          localStorage.setItem('user', JSON.stringify(this.user));
        }
      } catch (error) {
        console.error("Erreur de connexion:", error.response?.data || error.message);
      }
    },

    async register(username, password) {
      try {
        await axios.post('http://localhost:8000/api/register', { username, password });
      } catch (error) {
        console.error("Erreur d'inscription:", error.response?.data || error.message);
      }
    },

    logout() {
      this.token = null;
      this.user = null;
      localStorage.removeItem('token');
      localStorage.removeItem('user');
    }
  }
});
