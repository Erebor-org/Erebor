<template>
  <div class="login-container">
    <h2>Login</h2>
    <form @submit.prevent="handleLogin">
      <label>Pseudo:</label>
      <input type="username" v-model="username" required />

      <label>Mot de passe:</label>
      <input type="password" v-model="password" required />

      <button type="submit">Se connecter</button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      username: '',
      password: '',
    };
  },
  methods: {
    async handleLogin() {
      try {
        const response = await axios.post(
          'https://api.erebor-dofus.fr/login',
          {
            username: this.username,
            password: this.password,
          },
          { withCredentials: true }
        );

        if (response.data.success) {
          this.$router.push('/dashboard');
        } else {
          console.log(response.data.message);
        }
      } catch (error) {
        console.error(error);
        console.log('An error occurred during login.');
      }
    },
    async login(credentials) {
      return axios.post('https://api.erebor-dofus.fr/api/login', credentials, {
        withCredentials: true,
      });
    },
  },
};
</script>

<style>
/* Add your styles here */
</style>
