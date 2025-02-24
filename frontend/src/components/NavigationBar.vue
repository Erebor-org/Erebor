<script setup>
  import { useAuthStore } from '@/stores/authStore';
  import { computed } from 'vue';
  import { useRouter } from 'vue-router';
  const authStore = useAuthStore();
  const router = useRouter();
  const logout = () => {
    authStore.logout();
    router.push('/');
  };

  const isLoggedIn = computed(() => authStore.token !== null);
  const user = computed(() => authStore.user);

</script>
<template>
  <div class="text-white grid">
    <!-- Dofus Logo Positioned Independently -->
    <div class="logo-container">
      <img :src="dofus_logo" alt="Logo" class="dofus-logo"/>
    </div>

    <!-- Top Bar -->
    <div class="flex items-center justify-between px-4 py-2">
      <!-- Left Section -->
      <div class="flex items-center space-x-4">
        <img :src="erebor_logo" alt="Logo" class="w-10 h-10" />
        <button class="text-sm uppercase underline-on-hover font-fantasy">Erebor</button>
      </div>

      <!-- Right Section -->
      <div class="flex items-center space-x-4" v-if="!isLoggedIn">
        <button class="text-sm uppercase underline-on-hover font-fantasy">
          <RouterLink to="/inscription">S'inscrire</RouterLink>
        </button>
      </div>
      <div class="flex items-center space-x-4" v-else>
        <p>  {{ user.username }}  </p>
        <img :src="profile_icon" alt="User Avatar" class="h-10 w-10 rounded-full" />
        <button class="text-sm uppercase underline-on-hover font-fantasy" @click="logout">Déconnexion</button>
      </div>
    </div>

    <!-- Green Line -->
    <div class="bg-[#93a402] h-1"></div>

    <!-- Navigation Bar -->
    <div class="bg-black text-white relative z-10 font-nav">
      <div class="container mx-auto flex items-center justify-between px-16 py-6">
        <!-- Left Menu -->
        <div class="flex items-center space-x-6">
          <nav class="flex items-center space-x-4">
            <button class="text-sm uppercase underline-on-hover">Annonce</button>
            <button class="text-sm uppercase underline-on-hover">Ladders</button>
            <button class="text-sm uppercase underline-on-hover">Events</button>
          </nav>
        </div>

        <!-- Right Menu -->
        <div class="flex items-center space-x-6">
          <button class="text-sm uppercase underline-on-hover" v-if="isLoggedIn"><RouterLink to="/membres">Membres</RouterLink></button>
          <button class="text-sm uppercase underline-on-hover" v-if="isLoggedIn"><RouterLink to="/blacklist">Blacklist</RouterLink></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import erebor_logo from '@/assets/erebor_logo.png';
import dofus_logo from '@/assets/logo_dofus_good_quality.webp';
import profile_icon from '@/assets/profile_icon.png';
export default {
  name: "NavigationBar",
  data() {
    return {
      erebor_logo,
      dofus_logo,
      profile_icon
    };
  },
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Uncial+Antiqua&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&display=swap');


.font-fantasy {
  font-family: 'Uncial Antiqua', cursive;
}
.font-nav {
  font-family: 'Oswald', cursive;
}

/* Position the logo container above the navbar */
.logo-container {
  position: absolute;
  /*top: 60px; /* Adjust to desired height */
  left: 57%;
  transform: translateX(-50%);
  z-index: 20; /* Ensure it is above other elements */
}

/* Add specific styles for the Dofus logo with reduced size */
.dofus-logo {
  width: 50%; /* Set to 50% of original size */
  height: auto; /* Maintain aspect ratio */
}
.underline-on-hover {
  position: relative; /* Ensure the pseudo-element positions correctly */
}
.underline-on-hover::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: -2px; /* Adjust to set the underline position */
  width: 100%;
  height: 2px; /* Thickness of the underline */
  background-color: #93a402;
  transform: scaleX(0); /* Initially hidden */
  transform-origin: left; /* Animation starts from the left */
  transition: transform 0.3s ease-in-out; /* Smooth animation */
  font-size: 16px;
}

.underline-on-hover:hover::after {
  transform: scaleX(1); /* Fully visible underline on hover */
}
</style>
