import { defineStore } from 'pinia';
import axios from 'axios';
import router from '@/router'; // ✅ Import router globally

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user')) || null,
    isLoading: false,
  }),

  actions: {
    async register(username, password) {
      try {
        await axios.post('https://api.erebor-dofus.fr/register', { username, password });

        const response = await axios.post('https://api.erebor-dofus.fr/login', {
          username,
          password,
        });

        if (response.data.token) {
          this.token = response.data.token;
          this.user = response.data.user;
          localStorage.setItem('token', this.token);
          localStorage.setItem('user', JSON.stringify(this.user));

          // ✅ Redirect to /membres using the global router
          router.push('/membres');
        }
      } catch (error) {
        console.error("Erreur d'inscription ou de connexion:", error.response?.data || error.message);
      }
    },

    async login(username, password) {
      this.isLoading = true;
      try {
        const response = await axios.post("https://api.erebor-dofus.fr/login", { username, password });

        if (response.data.token) {
          this.token = response.data.token;
          this.user = response.data.user;
          localStorage.setItem("token", this.token);
          localStorage.setItem("user", JSON.stringify(this.user));

          // ✅ Redirect only if not already on /membres
          if (router.currentRoute.value.path !== "/membres") {
            router.push("/membres");
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
