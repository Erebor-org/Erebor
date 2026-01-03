<script setup>
  import { useAuthStore } from '@/stores/authStore';
  import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import ThemeToggle from './ThemeToggle.vue';
  import erebor_logo from '@/assets/erebor_logo.png';
  import profile_icon from '@/assets/profile_icon.png';

  // Import class icons
  const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });
  const classes = {
    sram: images['/src/assets/icon_classe/sram.avif'].default,
    forgelance: images['/src/assets/icon_classe/forgelance.avif'].default,
    cra: images['/src/assets/icon_classe/cra.avif'].default,
    ecaflip: images['/src/assets/icon_classe/ecaflip.avif'].default,
    eniripsa: images['/src/assets/icon_classe/eniripsa.avif'].default,
    enutrof: images['/src/assets/icon_classe/enutrof.avif'].default,
    feca: images['/src/assets/icon_classe/feca.avif'].default,
    eliotrope: images['/src/assets/icon_classe/eliotrope.avif'].default,
    iop: images['/src/assets/icon_classe/iop.avif'].default,
    osamodas: images['/src/assets/icon_classe/osamodas.avif'].default,
    pandawa: images['/src/assets/icon_classe/pandawa.avif'].default,
    roublard: images['/src/assets/icon_classe/roublard.avif'].default,
    sacrieur: images['/src/assets/icon_classe/sacrieur.avif'].default,
    sadida: images['/src/assets/icon_classe/sadida.avif'].default,
    steamer: images['/src/assets/icon_classe/steamer.avif'].default,
    xelor: images['/src/assets/icon_classe/xelor.avif'].default,
    zobal: images['/src/assets/icon_classe/zobal.avif'].default,
    huppermage: images['/src/assets/icon_classe/huppermage.avif'].default,
    ouginak: images['/src/assets/icon_classe/ouginak.avif'].default,
  };

  const authStore = useAuthStore();
  const router = useRouter();
  const route = useRoute();
  const isMobileMenuOpen = ref(false);
  const showScrollToTop = ref(false);
  const showWheelDropdown = ref(false);
  const showProfileDropdown = ref(false);
  let wheelDropdownTimeout = null;

  function toggleWheelDropdown() {
    showWheelDropdown.value = !showWheelDropdown.value;
  }

  function closeWheelDropdown() {
    showWheelDropdown.value = false;
  }

  function toggleProfileDropdown() {
    showProfileDropdown.value = !showProfileDropdown.value;
  }

  function closeProfileDropdown() {
    showProfileDropdown.value = false;
  }

  // Fermer le menu si on clique ailleurs
  function handleClickOutside(event) {
    const wheelDropdown = document.getElementById('wheel-dropdown-menu');
    const wheelButton = document.getElementById('wheel-dropdown-btn');
    if (wheelDropdown && !wheelDropdown.contains(event.target) && wheelButton && !wheelButton.contains(event.target)) {
      closeWheelDropdown();
    }
    
    const profileDropdown = document.getElementById('profile-dropdown-menu');
    const profileButton = document.getElementById('profile-dropdown-btn');
    if (profileDropdown && !profileDropdown.contains(event.target) && profileButton && !profileButton.contains(event.target)) {
      closeProfileDropdown();
    }
  }
  onMounted(() => {
    document.addEventListener('mousedown', handleClickOutside);
  });
  onBeforeUnmount(() => {
    document.removeEventListener('mousedown', handleClickOutside);
  });
  
  const logout = () => {
    authStore.logout();
    router.push('/');
  };

  const user = computed(() => authStore.user);
  const isLoggedIn = computed(() => authStore.token !== null);
  
  const isAdmin = computed(() => {
    const roles = user.value?.roles || [];
    return roles.includes('ROLE_ADMIN') || 
           roles.includes('ROLE_SUPER_ADMIN') || 
           roles.includes('ROLE_OWNERS');
  });
  
  const isSuperSuperAdmin = computed(() => {
    const roles = user.value?.roles || [];
    return roles.includes('ROLE_OWNERS');
  });
  
  const canManageWarnings = computed(() => {
    const roles = user.value?.roles || [];
    return roles.includes('ROLE_OWNERS');
  });
  
  const canViewWarnings = computed(() => {
    const roles = user.value?.roles || [];
    return roles.includes('ROLE_SUPER_ADMIN') || roles.includes('ROLE_OWNERS');
  });

  // Get character icon if user has a character
  const characterIcon = computed(() => {
    if (user.value?.character?.class) {
      return classes[user.value.character.class] || profile_icon;
    }
    return profile_icon;
  });
  
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
        <RouterLink 
          v-if="isLoggedIn"
          to="/home" 
          class="flex items-center space-x-4 hover:opacity-70 transition-opacity"
        >
          <div class="flex-shrink-0">
            <img :src="erebor_logo" alt="Erebor" class="w-10 h-10" />
          </div>
          <div class="hidden md:block">
            <h1 class="text-xl font-bold text-theme-primary">
              EREBOR
            </h1>
          </div>
        </RouterLink>
        <div 
          v-else
          class="flex items-center space-x-4"
        >
          <div class="flex-shrink-0">
            <img :src="erebor_logo" alt="Erebor" class="w-10 h-10" />
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
            to="/home" 
            v-if="isLoggedIn"
            class="nav-link"
            active-class="nav-link-active"
          >
            Accueil
          </RouterLink>
          
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
            v-if="isLoggedIn && canViewWarnings"
            class="nav-link"
            active-class="nav-link-active"
          >
            Avertissements
          </RouterLink>
          
          <RouterLink 
            to="/admin/users" 
            v-if="isLoggedIn && isSuperSuperAdmin"
            class="nav-link"
            active-class="nav-link-active"
          >
            Utilisateurs
          </RouterLink>
          
          <RouterLink 
            to="/statistiques" 
            v-if="isLoggedIn"
            class="nav-link"
            active-class="nav-link-active"
          >
            Statistiques
          </RouterLink>

          <div v-if="isLoggedIn" class="relative">
            <button
              id="wheel-dropdown-btn"
              class="nav-link flex items-center gap-1"
              @click="toggleWheelDropdown"
              :aria-expanded="showWheelDropdown"
              aria-haspopup="true"
              type="button"
            >
              Roue
              <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div
              v-if="showWheelDropdown"
              id="wheel-dropdown-menu"
              class="absolute left-0 mt-2 w-48 bg-theme-card border border-theme-border rounded-lg shadow-lg z-50"
            >
              <RouterLink
                to="/wheel"
                class="block px-4 py-3 nav-link"
                :class="{ 'nav-link-active': route.path === '/wheel' }"
                @click="closeWheelDropdown"
              >
                Roue Dofus (Membres)
              </RouterLink>
              <RouterLink
                to="/wheel-classes"
                class="block px-4 py-3 nav-link"
                :class="{ 'nav-link-active': route.path === '/wheel-classes' }"
                @click="closeWheelDropdown"
              >
                Roue des Classes
              </RouterLink>
              <RouterLink
                to="/wheel-numbers"
                class="block px-4 py-3 nav-link"
                :class="{ 'nav-link-active': route.path === '/wheel-numbers' }"
                @click="closeWheelDropdown"
              >
                Roue des Nombres
              </RouterLink>
            </div>
          </div>
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
                <p class="text-sm font-medium text-theme-text">{{ user?.character?.pseudo || user?.username }}</p>
                <p class="text-xs text-theme-text-muted">Connecté</p>
              </div>
            </div>
            
            <!-- Profile Avatar Dropdown -->
            <div class="relative">
              <button
                id="profile-dropdown-btn"
                class="relative focus:outline-none"
                @click="toggleProfileDropdown"
                :aria-expanded="showProfileDropdown"
                aria-haspopup="true"
                type="button"
              >
                <img 
                  :src="characterIcon" 
                  alt="Profile" 
                  class="w-10 h-10 rounded-full border-2 border-theme-border hover:border-theme-primary transition-all duration-300 cursor-pointer hover:scale-110"
                  title="Menu profil"
                />
                <div class="absolute -bottom-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-theme-success rounded-full border border-theme-bg"></div>
              </button>
              <div
                v-if="showProfileDropdown"
                id="profile-dropdown-menu"
                class="absolute right-0 mt-2 w-48 bg-theme-card border border-theme-border rounded-lg shadow-lg z-50"
              >
                <RouterLink
                  to="/profil"
                  class="block px-4 py-3 nav-link"
                  :class="{ 'nav-link-active': route.path === '/profil' }"
                  @click="closeProfileDropdown"
                >
                  Profil
                </RouterLink>
                <RouterLink
                  to="/theme-customizer"
                  class="block px-4 py-3 nav-link"
                  :class="{ 'nav-link-active': route.path === '/theme-customizer' }"
                  @click="closeProfileDropdown"
                >
                  Thème
                </RouterLink>
              </div>
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
          to="/home" 
          v-if="isLoggedIn"
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
          v-if="isLoggedIn && canViewWarnings"
          class="mobile-nav-link"
          active-class="mobile-nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          Avertissements
        </RouterLink>
        
        <RouterLink 
          to="/admin/users" 
          v-if="isLoggedIn && isSuperSuperAdmin"
          class="mobile-nav-link"
          active-class="mobile-nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          Utilisateurs
        </RouterLink>
        
        <RouterLink 
          to="/statistiques" 
          v-if="isLoggedIn"
          class="mobile-nav-link"
          active-class="mobile-nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          Statistiques
        </RouterLink>

        <div v-if="isLoggedIn" class="">
          <div class="font-semibold text-theme-primary mb-1">Roue</div>
          <RouterLink 
            to="/wheel" 
            class="mobile-nav-link"
            :class="{ 'mobile-nav-link-active': route.path === '/wheel' }"
            @click="isMobileMenuOpen = false"
          >
            Roue Dofus (Membres)
          </RouterLink>
          <RouterLink 
            to="/wheel-classes" 
            class="mobile-nav-link"
            :class="{ 'mobile-nav-link-active': route.path === '/wheel-classes' }"
            @click="isMobileMenuOpen = false"
          >
            Roue des Classes
          </RouterLink>
          <RouterLink 
            to="/wheel-numbers" 
            class="mobile-nav-link"
            :class="{ 'mobile-nav-link-active': route.path === '/wheel-numbers' }"
            @click="isMobileMenuOpen = false"
          >
            Roue des Nombres
          </RouterLink>
        </div>

        <div v-if="isLoggedIn" class="">
          <div class="font-semibold text-theme-primary mb-1">Mon Compte</div>
          <RouterLink 
            to="/profil" 
            class="mobile-nav-link"
            :class="{ 'mobile-nav-link-active': route.path === '/profil' }"
            @click="isMobileMenuOpen = false"
          >
            Profil
          </RouterLink>
          <RouterLink 
            to="/theme-customizer" 
            class="mobile-nav-link"
            :class="{ 'mobile-nav-link-active': route.path === '/theme-customizer' }"
            @click="isMobileMenuOpen = false"
          >
            Thème
          </RouterLink>
        </div>
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
