<script setup>
import { useAuthStore } from '@/stores/authStore';
import { ref, computed } from 'vue';
import erebor_logo from '@/assets/erebor_logo.png';
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
  // Redirect is handled by authStore
};

const login = async () => {
  await authStore.login(loginForm.value.pseudo, loginForm.value.password);
  // Redirect is handled by authStore
};

const togglePassword = () => {
  showPassword.value = !showPassword.value;
};
</script>

<template>
  <div class="min-h-screen bg-theme-bg text-theme-text">
    <!-- Loading Spinner -->
    <div v-if="authStore.isLoading" class="loading-spinner">
      <div class="bg-theme-card p-6 rounded-xl shadow-lg border border-theme-border">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-theme-primary"></div>
          <span class="text-theme-text font-medium">Connexion en cours...</span>
        </div>
      </div>
    </div>

    <!-- Main Container -->
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <div class="flex justify-center mb-6">
          <img :src="erebor_logo" alt="Logo Erebor" class="w-24 h-24 transition-transform duration-300 hover:scale-110" />
        </div>
        <h1 class="text-6xl font-bold text-theme-primary mb-6">
          Bienvenue sur <span class="text-theme-text">Erebor</span>
        </h1>
        <div class="w-32 h-1 bg-gradient-to-r from-theme-primary via-theme-primary-hover to-theme-primary mx-auto rounded-full shadow-lg shadow-theme-primary/50"></div>
        <p class="text-theme-text-muted mt-6 text-lg">Rejoignez l'univers enchanteur de votre guilde !</p>
      </div>

      <!-- Form Container -->
      <div class="max-w-md mx-auto">
        <!-- Tabs -->
        <div class="flex justify-center mb-8">
          <div class="bg-theme-card rounded-lg p-1 shadow-lg border border-theme-border">
            <button
              class="px-6 py-3 text-theme-text font-semibold rounded-lg transition-all duration-300"
              :class="{
                'bg-theme-primary text-white shadow-lg': activeTab === 'register',
                'bg-transparent text-theme-text-muted hover:text-theme-text': activeTab !== 'register',
              }"
              @click="activeTab = 'register'"
            >
              S'enregistrer
            </button>
            <button
              class="px-6 py-3 text-theme-text font-semibold rounded-lg transition-all duration-300"
              :class="{
                'bg-theme-primary text-white shadow-lg': activeTab === 'login',
                'bg-transparent text-theme-text-muted hover:text-theme-text': activeTab !== 'login',
              }"
              @click="activeTab = 'login'"
            >
              Connexion
            </button>
          </div>
        </div>

        <!-- Forms -->
        <div class="bg-theme-card rounded-xl shadow-lg border border-theme-border p-8">
          <div v-if="activeTab === 'register'">
            <!-- Registration Form -->
            <form @submit.prevent="register" class="space-y-6">
              <!-- Pseudo -->
              <div>
                <label for="pseudo" class="block text-theme-text font-semibold mb-2">Pseudo</label>
                <input
                  v-model="form.pseudo"
                  type="text"
                  id="pseudo"
                  placeholder="Entrez votre pseudo en jeu"
                  class="w-full px-4 py-3 bg-theme-bg border border-theme-border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-theme-ring focus:border-theme-primary transition-all duration-300"
                />
              </div>

              <!-- Mot de Passe -->
              <div class="relative">
                <label for="password" class="block text-theme-text font-semibold mb-2">Mot de Passe</label>
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  placeholder="Entrez votre mot de passe"
                  class="w-full px-4 py-3 bg-theme-bg border border-theme-border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-theme-ring focus:border-theme-primary transition-all duration-300"
                />
                <button
                  type="button"
                  class="absolute top-11 right-3 text-theme-text-muted hover:text-theme-primary transition-colors"
                  @click="togglePassword"
                >
                  <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>

              <!-- Confirmation du Mot de Passe -->
              <div class="relative">
                <label for="confirm-password" class="block text-theme-text font-semibold mb-2">Confirmer le Mot de Passe</label>
                <input
                  v-model="form.confirmPassword"
                  :type="showPassword ? 'text' : 'password'"
                  id="confirm-password"
                  placeholder="Confirmez votre mot de passe"
                  :class="[
                    'w-full px-4 py-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-theme-ring focus:border-theme-primary transition-all duration-300',
                    form.confirmPassword === form.password && form.confirmPassword !== ''
                      ? 'bg-theme-success/20 border-theme-success'
                      : 'bg-theme-bg border-theme-border'
                  ]"
                />
                <div
                  v-if="form.confirmPassword && form.confirmPassword !== ''"
                  class="absolute top-11 right-3"
                >
                  <svg v-if="passwordsMatch" class="w-5 h-5 text-theme-success" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="w-5 h-5 text-theme-error" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
                <p
                  v-if="form.confirmPassword && form.confirmPassword !== '' && !passwordsMatch"
                  class="text-theme-error text-sm mt-2 flex items-center"
                >
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  Les mots de passe ne correspondent pas !
                </p>
              </div>

              <!-- Submit Button -->
              <div class="pt-4">
                <button
                  type="submit"
                  class="w-full px-6 py-3 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-theme-ring focus:ring-opacity-30"
                >
                  S'enregistrer
                </button>
              </div>
            </form>
          </div>

          <div v-else-if="activeTab === 'login'">
            <!-- Login Form -->
            <form @submit.prevent="login" class="space-y-6">
              <!-- Pseudo -->
              <div>
                <label for="login-pseudo" class="block text-theme-text font-semibold mb-2">Pseudo</label>
                <input
                  v-model="loginForm.pseudo"
                  type="text"
                  id="login-pseudo"
                  placeholder="Entrez votre pseudo"
                  class="w-full px-4 py-3 bg-theme-bg border border-theme-border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-theme-ring focus:border-theme-primary transition-all duration-300"
                />
              </div>

              <!-- Mot de Passe -->
              <div class="relative">
                <label for="login-password" class="block text-theme-text font-semibold mb-2">Mot de Passe</label>
                <input
                  v-model="loginForm.password"
                  :type="showPassword ? 'text' : 'password'"
                  id="login-password"
                  placeholder="Entrez votre mot de passe"
                  class="w-full px-4 py-3 bg-theme-bg border border-theme-border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-theme-ring focus:border-theme-primary transition-all duration-300"
                />
                <button
                  type="button"
                  class="absolute top-11 right-3 text-theme-text-muted hover:text-theme-primary transition-colors"
                  @click="togglePassword"
                >
                  <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>

              <!-- Submit Button -->
              <div class="pt-4">
                <button
                  type="submit"
                  class="w-full px-6 py-3 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-theme-ring focus:ring-opacity-30"
                >
                  Connexion
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom styles for the register page */
.loading-spinner {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 50;
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
</style>
