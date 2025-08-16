<script setup>
import { useAuthStore } from '@/stores/authStore';
import { ref, computed } from 'vue';
import erebor_logo from '@/assets/erebor_logo.png';
import register_bg from '@/assets/register_bg.webp';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();
const activeTab = ref('login');
const showPassword = ref(false);

const form = ref({
  pseudo: '',
  password: '',
  confirmPassword: '',
});

const loginForm = ref({
  pseudo: '',
  password: '',
});

const passwordsMatch = computed(() => form.value.password === form.value.confirmPassword);

const register = async () => {
  if (!passwordsMatch.value) {
    console.log('Les mots de passe ne correspondent pas !');
    return;
  }

  await authStore.register(form.value.pseudo, form.value.password);
  if (authStore.token) {
    router.push('/membres'); // Redirection après l'inscription et connexion réussies
  }
};

const login = async () => {
  await authStore.login(loginForm.value.pseudo, loginForm.value.password);
  if (authStore.token) {
    router.push('/membres'); // Redirige si le token est défini après le login
  }
};

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};
</script>

<template>
  <div
    class="w-full flex flex-col items-center justify-start bg-cover bg-center py-8"
    :style="{ backgroundImage: `url(${register_bg})` }"
  >
    <!-- Form Container -->
    <div
      class="relative w-full max-w-lg bg-theme-card p-8 rounded-xl shadow-lg border border-theme-border z-10"
    >
      <div v-if="authStore.isLoading" class="loading-spinner">🔄 Connexion en cours...</div>
      <div class="mx-auto w-24 h-24 rounded-full flex items-center justify-center">
        <img :src="erebor_logo" alt="Logo" class="w-20 h-20" />
      </div>

      <!-- Header -->
      <div class="text-center">
        <h1 class="text-4xl font-bold text-theme-text">
          Bienvenue sur le site <span class="font-fantasy">d'Erebor</span>
        </h1>
        <p class="text-theme-text-muted mt-2">Rejoignez l'univers enchanteur de votre guilde !</p>
      </div>

      <!-- Tabs -->
      <div class="flex justify-center mt-4">
        <button
          class="px-4 py-2 text-theme-text font-semibold rounded-t-md"
          :class="{
            'bg-theme-primary text-theme-bg': activeTab === 'register',
            'bg-theme-bg-muted': activeTab !== 'register',
          }"
          @click="activeTab = 'register'"
        >
          S'enregistrer
        </button>
        <button
          class="px-4 py-2 text-theme-text font-semibold rounded-t-md"
          :class="{
            'bg-theme-primary text-theme-bg': activeTab === 'login',
            'bg-theme-bg-muted': activeTab !== 'login',
          }"
          @click="activeTab = 'login'"
        >
          Connexion
        </button>
      </div>

      <!-- Forms -->
      <div v-if="activeTab === 'register'">
        <!-- Registration Form -->
        <form @submit.prevent="register" class="mt-8 space-y-6 grid grid-cols-1 gap-4">
          <!-- Pseudo -->
          <div>
            <label for="pseudo" class="block text-theme-text font-semibold">Pseudo</label>
            <input
              v-model="form.pseudo"
              type="text"
              id="pseudo"
              placeholder="Entrez votre pseudo en jeu"
              class="w-full mt-2 px-4 py-2 border border-theme-border rounded-md shadow-sm focus:outline-none focus:ring focus:ring-theme-ring"
            />
          </div>

          <!-- Mot de Passe -->
          <div class="relative">
            <label for="password" class="block text-theme-text font-semibold">Mot de Passe</label>
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              id="password"
              placeholder="Entrez votre mot de passe"
              class="w-full mt-2 px-4 py-2 border border-theme-border rounded-md shadow-sm focus:outline-none focus:ring focus:ring-theme-ring"
            />
            <span
              class="absolute top-9 right-3 cursor-pointer text-theme-primary"
              @click="togglePassword"
            >
              <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </span>
          </div>

          <!-- Confirmation du Mot de Passe -->
          <div class="relative">
            <label for="confirm-password" class="block text-theme-text font-semibold"
              >Confirmer le Mot de Passe</label
            >
            <input
              v-model="form.confirmPassword"
              :type="showPassword ? 'text' : 'password'"
              id="confirm-password"
              placeholder="Confirmez votre mot de passe"
              :class="[
                'w-full mt-2 px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring focus:ring-theme-ring',
                form.confirmPassword === form.password && form.confirmPassword !== ''
                  ? 'bg-theme-success'
                  : '',
              ]"
            />
            <span
              class="absolute top-9 right-3 cursor-pointer text-theme-primary"
              @click="togglePassword"
            >
              <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </span>
            <p
              v-if="form.confirmPassword && form.confirmPassword && !passwordsMatch"
              class="text-theme-error text-sm mb-4"
            >
              Les mots de passe ne correspondent pas !
            </p>
          </div>

          <!-- Submit Button -->
          <div class="mt-6">
            <button
              type="submit"
              class="w-full px-4 py-2 bg-theme-primary text-theme-bg font-semibold rounded-md shadow-md hover:bg-theme-primary-hover transition"
            >
              S'enregistrer
            </button>
          </div>
        </form>
      </div>

      <div v-else-if="activeTab === 'login'">
        <!-- Login Form -->
        <form @submit.prevent="login" class="mt-8 space-y-6 grid grid-cols-1 gap-4">
          <!-- Pseudo -->
          <div>
            <label for="login-pseudo" class="block text-theme-text font-semibold">Pseudo</label>
            <input
              v-model="loginForm.pseudo"
              type="text"
              id="login-pseudo"
              placeholder="Entrez votre pseudo"
              class="w-full mt-2 px-4 py-2 border border-theme-border rounded-md shadow-sm focus:outline-none focus:ring focus:ring-theme-ring"
            />
          </div>

          <!-- Mot de Passe -->
          <div class="relative">
            <label for="login-password" class="block text-theme-text font-semibold"
              >Mot de Passe</label
            >
            <input
              v-model="loginForm.password"
              :type="showPassword ? 'text' : 'password'"
              id="login-password"
              placeholder="Entrez votre mot de passe"
              class="w-full mt-2 px-4 py-2 border border-theme-border rounded-md shadow-sm focus:outline-none focus:ring focus:ring-theme-ring"
            />
            <span
              class="absolute top-9 right-3 cursor-pointer text-theme-primary"
              @click="togglePassword"
            >
              <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </span>
          </div>

          <!-- Submit Button -->
          <div class="mt-6">
            <button
              type="submit"
              class="w-full px-4 py-2 bg-theme-primary text-theme-bg font-semibold rounded-md shadow-md hover:bg-theme-primary-hover transition"
            >
              Connexion
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Uncial+Antiqua&display=swap');

.font-fantasy {
  font-family: 'Uncial Antiqua', cursive;
}
.loading-spinner {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 20px;
  font-weight: bold;
  background: rgba(var(--bg-rgb), 0.9);
  color: var(--text);
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0px 0px 10px rgba(var(--text-rgb), 0.2);
  border: 1px solid var(--border);
}
</style>
