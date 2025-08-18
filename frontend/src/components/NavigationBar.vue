<script setup>
  import { useAuthStore } from '@/stores/authStore';
  import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
  import { useRouter } from 'vue-router';
  import ThemeToggle from './ThemeToggle.vue';
  import erebor_logo from '@/assets/erebor_logo.png';
  import profile_icon from '@/assets/profile_icon.png';

  const authStore = useAuthStore();
  const router = useRouter();
  const isMobileMenuOpen = ref(false);
  const showScrollToTop = ref(false);
  
  const logout = () => {
    authStore.logout();
    router.push('/');
  };
  
  const isAdmin = computed(() => {
    return user.value?.roles?.includes('ROLE_ADMIN');
  });

  const isLoggedIn = computed(() => authStore.token !== null);
  const user = computed(() => authStore.user);
  
  const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value;
  };

  const handleScroll = () => {
    showScrollToTop.value = window.scrollY > 100;
  };
  onMounted(() => {
    window.addEventListener('scroll', handleScroll);
  });
  onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
  });
  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };
</script>

<template>
  <nav class="bg-theme-card border-b border-theme-border shadow-lg">
    <!-- Main Navigation Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        
        <!-- Left Section - Logo & Brand -->
        <div class="flex items-center space-x-4">
          <div class="flex-shrink-0">
            <img :src="erebor_logo" alt="Erebor" class="w-10 h-10 transition-transform duration-300 hover:scale-110" />
          </div>
          <div class="hidden md:block">
            <h1 class="text-xl font-bold text-theme-primary">
              EREBOR
            </h1>
          </div>
        </div>

        <!-- Center Section - Navigation Links (Desktop) -->
        <div class="hidden lg:flex items-center space-x-6">
          
          <RouterLink 
            to="/membres" 
            v-if="isLoggedIn && isAdmin"
            class="nav-link"
            active-class="nav-link-active"
          >
            Membres
          </RouterLink>
          
          <RouterLink 
            to="/blacklist" 
            v-if="isLoggedIn && isAdmin"
            class="nav-link"
            active-class="nav-link-active"
          >
            Blacklist
          </RouterLink>
          
          <RouterLink 
            to="/warnings-management" 
            v-if="isLoggedIn && isAdmin"
            class="nav-link"
            active-class="nav-link-active"
          >
            Avertissements
          </RouterLink>
          
          <RouterLink 
            to="/statistiques" 
            v-if="isLoggedIn && isAdmin"
            class="nav-link"
            active-class="nav-link-active"
          >
            Statistiques
          </RouterLink>

          <RouterLink 
            to="/wheel" 
            v-if="isLoggedIn"
            class="nav-link"
            active-class="nav-link-active"
          >
            Roue Dofus
          </RouterLink>
        </div>

        <!-- Right Section - User Menu & Auth -->
        <div class="flex items-center space-x-4">
          
          <!-- Theme Toggle -->
          <ThemeToggle />
          
          <!-- Not Logged In -->
          <div v-if="!isLoggedIn" class="flex items-center space-x-3">
            <RouterLink 
              to="/inscription"
              class="px-6 py-2.5 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-4 focus:ring-theme-ring focus:ring-opacity-30 shadow-lg"
            >
              S'inscrire
            </RouterLink>
          </div>

          <!-- Logged In User -->
          <div v-else class="flex items-center space-x-4">
            <!-- User Info -->
            <div class="hidden md:flex items-center space-x-3">
              <div class="text-right">
                <p class="text-sm font-medium text-theme-text">{{ user?.username }}</p>
                <p class="text-xs text-theme-text-muted">Connecté</p>
              </div>
            </div>
            
            <!-- Profile Avatar -->
            <div class="relative group">
              <img 
                :src="profile_icon" 
                alt="Profile" 
                class="w-10 h-10 rounded-full border-2 border-theme-border hover:border-theme-primary transition-all duration-300 cursor-pointer group-hover:scale-110"
                @click="router.push('/theme-customizer')"
                title="Personnalisation du profil"
                style="cursor:pointer;"
              />
              <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-theme-success rounded-full border border-theme-bg"></div>
            </div>
            
            <!-- Logout Button -->
            <button 
              @click="logout"
              class="px-4 py-2 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium rounded-lg transition-all duration-300 hover:text-theme-primary focus:outline-none focus:ring-2 focus:ring-theme-ring"
            >
              <span class="hidden sm:inline">Déconnexion</span>
              <svg class="w-5 h-5 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
            </button>
          </div>

          <!-- Mobile Menu Button -->
          <button
            @click="toggleMobileMenu"
            class="lg:hidden p-2 rounded-lg bg-theme-bg-muted hover:bg-theme-border text-theme-text transition-colors duration-200"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div 
      v-if="isMobileMenuOpen" 
      class="lg:hidden bg-theme-bg-muted border-t border-theme-border shadow-xl"
    >
      <div class="px-4 py-6 space-y-4">
        <RouterLink 
          to="/" 
          class="mobile-nav-link"
          active-class="mobile-nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          Accueil
        </RouterLink>
        
        <RouterLink 
          to="/membres" 
          v-if="isLoggedIn && isAdmin"
          class="mobile-nav-link"
          active-class="mobile-nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          Membres
        </RouterLink>
        
        <RouterLink 
          to="/blacklist" 
          v-if="isLoggedIn && isAdmin"
          class="mobile-nav-link"
          active-class="mobile-nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          Blacklist
        </RouterLink>
        
        <RouterLink 
          to="/warnings-management" 
          v-if="isLoggedIn && isAdmin"
          class="mobile-nav-link"
          active-class="mobile-nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          Avertissements
        </RouterLink>
        
        <RouterLink 
          to="/statistiques" 
          v-if="isLoggedIn && isAdmin"
          class="mobile-nav-link"
          active-class="mobile-nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          Statistiques
        </RouterLink>

        <RouterLink 
          to="/wheel" 
          v-if="isLoggedIn"
          class="mobile-nav-link"
          active-class="mobile-nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          Roue Dofus
        </RouterLink>
      </div>
    </div>
    <button
      v-if="showScrollToTop"
      @click="scrollToTop"
      class="fixed bottom-8 right-8 z-[9999] w-14 h-14 bg-theme-primary hover:bg-theme-primary-hover text-white rounded-full shadow-2xl flex items-center justify-center transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-theme-primary/30 border-2 border-theme-primary/50 hover:border-theme-primary"
      title="Remonter en haut de la page"
      style="box-shadow: 0 8px 32px rgba(0,0,0,0.18);"
    >
      <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
      </svg>
    </button>
  </nav>
</template>

<script>
import erebor_logo from '@/assets/erebor_logo.png';
import profile_icon from '@/assets/profile_icon.png';

export default {
  name: "NavigationBar",
  data() {
    return {
      erebor_logo,
      profile_icon
    };
  },
};
</script>

<style scoped>
/* Navigation Link Styles */
.nav-link {
  @apply text-theme-text-muted hover:text-theme-primary px-2 py-1.5 rounded-lg text-sm font-medium transition-all duration-300 relative;
}

.nav-link::after {
  content: '';
  @apply absolute bottom-0 left-1/2 w-0 h-0.5 bg-theme-primary transition-all duration-300 transform -translate-x-1/2;
}

.nav-link:hover::after {
  @apply w-full;
}

.nav-link-active {
  @apply text-theme-primary;
}

.nav-link-active::after {
  @apply w-full;
}

/* Mobile Navigation Link Styles */
.mobile-nav-link {
  @apply block px-4 py-3 text-theme-text-muted hover:text-theme-primary hover:bg-theme-bg-muted rounded-lg text-base font-medium transition-all duration-300;
}

.mobile-nav-link-active {
  @apply text-theme-primary bg-theme-bg-muted;
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background-color: var(--bg-muted);
}

::-webkit-scrollbar-thumb {
  background-color: var(--border);
  border-radius: 9999px;
}

::-webkit-scrollbar-thumb:hover {
  background-color: var(--text-muted);
}
</style>
