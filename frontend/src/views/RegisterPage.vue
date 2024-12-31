<template>

  <div class="w-full grid bg-gradient-to-b flex items-center justify-center relative h-screen bg-cover bg-center" :style="{ backgroundImage: `url(${register_bg})` }">

    <!-- Form Container -->
    <div
      class="relative w-full max-w-lg bg-yellow-50 p-8 rounded-xl shadow-lg border border-red-700 z-10"
    >
    <div class="mx-auto w-24 h-24 rounded-full flex items-center justify-center">
          <img :src="erebor_logo" alt="Logo" class="w-20 h-20" />
        </div>
      <!-- Header -->
      <div class="text-center">
        <h1 class="text-4xl font-bold text-red-800">Rejoignez le site <span class="font-fantasy">d'Erebor</span></h1>
        <p class="text-yellow-900 mt-2">
          Rejoignez l'univers enchanteur de votre guilde !
        </p>
      </div>

      <!-- Registration Form -->
      <form @submit.prevent="register" class="mt-8 space-y-6 grid grid-cols-1  gap-4">
        <!-- Pseudo -->
        <div>
          <label for="pseudo" class="block text-red-800 font-semibold">Pseudo</label>
          <input
            v-model="form.pseudo"
            type="text"
            id="pseudo"
            placeholder="Entrez votre pseudo en jeu"
            class="w-full mt-2 px-4 py-2 border border-yellow-500 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-yellow-300"
          />
        </div>

        <!-- Mot de Passe -->
        <div class="relative">
          Attention à ne pas mettre vos mots de passes en jeu
          <label for="password" class="block text-red-800 font-semibold">Mot de Passe</label>
          <input
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            id="password"
            placeholder="Entrez votre mot de passe"
            class="w-full mt-2 px-4 py-2 border border-yellow-500 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-yellow-300"
          />
          <!-- Eye Icon -->
          <span
            class="absolute top-9 right-3 cursor-pointer text-yellow-600"
            @click="togglePassword"
          >
            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
          </span>
        </div>

        <!-- Confirmation du Mot de Passe -->
        <div class="relative">
          <label for="confirm-password" class="block text-red-800 font-semibold">Confirmer le Mot de Passe</label>
          <input
            v-model="form.confirmPassword"
            :type="showPassword ? 'text' : 'password'"
            id="confirm-password"
            placeholder="Confirmez votre mot de passe"
            :class="[
              'w-full mt-2 px-4 py-2 border rounded-md shadow-sm focus:outline-none focus:ring focus:ring-yellow-300',
              form.confirmPassword === form.password && form.confirmPassword !== ''
                ? 'bg-green-500'
                : ''
            ]"
          />
          
          <!-- Eye Icon -->
          <span
            class="absolute top-9 right-3 cursor-pointer text-yellow-600"
            @click="togglePassword"
          >
            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
          </span>
          <p
          v-if="form.confirmPassword && form.confirmPassword && !passwordsMatch"
          class="text-red-600 text-sm mb-4"
        >
          Les mots de passe ne correspondent pas !
        </p>

        </div>

        <!-- Submit Button -->
        <div class="mt-6">
          <button
            type="submit"
            class="w-full px-4 py-2 bg-red-700 text-yellow-50 font-semibold rounded-md shadow-md hover:bg-red-800 transition"
          >
            S'enregistrer
          </button>
        </div>
      </form>
    </div>
  </div>

</template>

<script>

import erebor_logo from '@/assets/erebor_logo.png';
import register_bg from '@/assets/register_bg.webp';
export default {
  
  data() {
    return {
      erebor_logo,
      register_bg,
      form: {
        pseudo: "",
        password: "",
        confirmPassword: "",
      },
      showPassword: false,
    };
  },
  computed: {
    passwordsMatch() {
      return this.form.password === this.form.confirmPassword;
    },
  },
  methods: {
    togglePassword() {
      this.showPassword = !this.showPassword;
    },
    register() {
      if (this.form.password !== this.form.confirmPassword) {
        alert("Les mots de passe ne correspondent pas !");
        return;
      }

      // Envoyer les données à Symfony
      const userData = {
        username: this.form.pseudo,
        password: this.form.password,
      };

      // Remplacez par une requête réelle à votre API Symfony
      fetch("http://localhost:8000/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(userData),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Erreur lors de l'enregistrement");
          }
          return response.json();
        })
        .then((data) => {
          console.log("Utilisateur enregistré :", data);
          alert("Enregistrement réussi !");
        })
        .catch((error) => {
          console.error(error);
          alert("Une erreur s'est produite.");
        });
    },
  },
};
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Uncial+Antiqua&display=swap');

.font-fantasy {
  font-family: 'Uncial Antiqua', cursive;
}
</style>
