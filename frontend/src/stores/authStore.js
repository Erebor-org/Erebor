import { defineStore } from 'pinia';
import axios from 'axios';
import { useRouter } from 'vue-router';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user')) || null,
  }),

  actions: {
    async register(username, password) {
      const router = useRouter();

      try {
        // 🔴 Étape 1 : Enregistrement de l'utilisateur
        await axios.post('http://localhost:8000/register', { username, password });

        // 🔴 Étape 2 : Connexion immédiate après inscription
        const response = await axios.post('http://localhost:8000/login', {
          username,
          password
        });

        if (response.data.token) {
          this.token = response.data.token;
          this.user = response.data.user;
          localStorage.setItem('token', this.token);
          localStorage.setItem('user', JSON.stringify(this.user));

          // 🔴 Étape 3 : Redirection vers /membres après connexion réussie
          router.push('/membres');
        }
      } catch (error) {
        console.error("Erreur d'inscription ou de connexion:", error.response?.data || error.message);
      }
    },

    async login(username, password) {
      const router = useRouter();

      try {
        const response = await axios.post('http://localhost:8000/login', {
          username,
          password
        });

        if (response.data.token) {
          this.token = response.data.token;
          this.user = response.data.user;
          localStorage.setItem('token', this.token);
          localStorage.setItem('user', JSON.stringify(this.user));

          // 🔴 Redirection vers /membres après connexion réussie
          router.push('/membres');
        }
      } catch (error) {
        console.error("Erreur de connexion:", error.response?.data || error.message);
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
