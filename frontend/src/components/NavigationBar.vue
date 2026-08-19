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
  const showManageDropdown = ref(false);
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

  function toggleManageDropdown() {
    showManageDropdown.value = !showManageDropdown.value;
  }

  function closeManageDropdown() {
    showManageDropdown.value = false;
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

    const manageDropdown = document.getElementById('manage-dropdown-menu');
    const manageButton = document.getElementById('manage-dropdown-btn');
    if (manageDropdown && !manageDropdown.contains(event.target) && manageButton && !manageButton.contains(event.target)) {
      closeManageDropdown();
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
  <nav class="nav-glass">
    <!-- Main Navigation Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Left Section - Logo & Brand -->
        <RouterLink
          v-if="isLoggedIn"
          to="/home"
          class="flex items-center space-x-3 hover:opacity-80 transition-opacity"
        >
          <div class="logo-roundel flex-shrink-0">
            <img :src="erebor_logo" alt="Erebor" />
          </div>
          <div class="hidden md:block">
            <h1 class="text-xl font-serif font-bold tracking-wide brand-gradient-text">
              EREBOR
            </h1>
          </div>
        </RouterLink>
        <div
          v-else
          class="flex items-center space-x-3"
        >
          <div class="logo-roundel flex-shrink-0">
            <img :src="erebor_logo" alt="Erebor" />
          </div>
          <div class="hidden md:block">
            <h1 class="text-xl font-serif font-bold tracking-wide brand-gradient-text">
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
            to="/fantomes"
            v-if="isLoggedIn && isAdmin"
            class="nav-link"
            active-class="nav-link-active"
          >
            Fantômes
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
              class="dropdown-glass absolute left-0 mt-2 w-52 z-50"
            >
              <RouterLink
                to="/wheel"
                class="block px-4 py-3 nav-link"
                :class="{ 'nav-link-active': route.path === '/wheel' }"
                @click="closeWheelDropdown"
              >
                Roue des Membres
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

          <!-- Gestion (regroupe les outils réservés aux rôles admin/modération) -->
          <div v-if="isLoggedIn && isAdmin" class="relative">
            <button
              id="manage-dropdown-btn"
              class="nav-link flex items-center gap-1"
              @click="toggleManageDropdown"
              :aria-expanded="showManageDropdown"
              aria-haspopup="true"
              type="button"
            >
              Gestion
              <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div
              v-if="showManageDropdown"
              id="manage-dropdown-menu"
              class="dropdown-glass absolute left-0 mt-2 w-52 z-50"
            >
              <RouterLink
                to="/blacklist"
                class="block px-4 py-3 nav-link"
                :class="{ 'nav-link-active': route.path === '/blacklist' }"
                @click="closeManageDropdown"
              >
                Blacklist
              </RouterLink>
              <RouterLink
                v-if="canViewWarnings"
                to="/warnings-management"
                class="block px-4 py-3 nav-link"
                :class="{ 'nav-link-active': route.path === '/warnings-management' }"
                @click="closeManageDropdown"
              >
                Avertissements
              </RouterLink>
              <RouterLink
                v-if="isSuperSuperAdmin"
                to="/admin/users"
                class="block px-4 py-3 nav-link"
                :class="{ 'nav-link-active': route.path === '/admin/users' }"
                @click="closeManageDropdown"
              >
                Utilisateurs
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
          <div v-else class="relative">
            <button
              id="profile-dropdown-btn"
              class="profile-pill"
              @click="toggleProfileDropdown"
              :aria-expanded="showProfileDropdown"
              aria-haspopup="true"
              type="button"
            >
              <span class="relative flex-shrink-0">
                <img
                  :src="characterIcon"
                  alt="Profile"
                  class="w-8 h-8 rounded-full object-cover"
                  title="Menu profil"
                />
                <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-theme-success rounded-full border-2 border-theme-card"></span>
              </span>
              <span class="hidden md:block text-sm font-medium text-theme-text pr-1">
                {{ user?.character?.pseudo || user?.username }}
              </span>
              <svg class="hidden md:block w-4 h-4 text-theme-text-muted mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div
              v-if="showProfileDropdown"
              id="profile-dropdown-menu"
              class="dropdown-glass absolute right-0 mt-2 w-48 z-50"
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
              <div class="dropdown-divider"></div>
              <button
                @click="closeProfileDropdown(); logout();"
                class="block w-full text-left px-4 py-3 nav-link nav-link-danger"
                type="button"
              >
                Déconnexion
              </button>
            </div>
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
          to="/fantomes"
          v-if="isLoggedIn && isAdmin"
          class="mobile-nav-link"
          active-class="mobile-nav-link-active"
          @click="isMobileMenuOpen = false"
        >
          Fantômes
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

        <div v-if="isLoggedIn && isAdmin" class="">
          <div class="font-semibold text-theme-primary mb-1">Gestion</div>
          <RouterLink
            to="/blacklist"
            class="mobile-nav-link"
            active-class="mobile-nav-link-active"
            @click="isMobileMenuOpen = false"
          >
            Blacklist
          </RouterLink>
          <RouterLink
            to="/warnings-management"
            v-if="canViewWarnings"
            class="mobile-nav-link"
            active-class="mobile-nav-link-active"
            @click="isMobileMenuOpen = false"
          >
            Avertissements
          </RouterLink>
          <RouterLink
            to="/admin/users"
            v-if="isSuperSuperAdmin"
            class="mobile-nav-link"
            active-class="mobile-nav-link-active"
            @click="isMobileMenuOpen = false"
          >
            Utilisateurs
          </RouterLink>
        </div>

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
          <button
            @click="isMobileMenuOpen = false; logout();"
            class="mobile-nav-link nav-link-danger w-full text-left"
            type="button"
          >
            Déconnexion
          </button>
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
/* Barre en verre dépoli avec liseré dégradé rouge -> or en pied de barre */
.nav-glass {
  background-color: rgba(var(--bg-rgb), 0.72);
  backdrop-filter: blur(14px) saturate(140%);
  -webkit-backdrop-filter: blur(14px) saturate(140%);
  position: relative;
  /* z-index garantit que la nav (et ses menus) reste au-dessus de tout
     contenu de page utilisant position:relative/absolute, quel que soit
     l'ordre du DOM (cf. bug où une carte pleine largeur passait devant
     le menu profil pour les comptes non-admin). */
  z-index: 30;
}

.nav-glass::after {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 1px;
  background-image: linear-gradient(90deg, transparent, rgba(var(--primary-rgb), 0.55), rgba(var(--accent-rgb), 0.55), transparent);
}

/* Médaillon du logo : anneau dégradé rouge -> or */
.logo-roundel {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.65rem;
  padding: 2px;
  background-image: linear-gradient(140deg, var(--accent), var(--primary));
}

.logo-roundel img {
  width: 100%;
  height: 100%;
  border-radius: 0.5rem;
  object-fit: cover;
  background-color: var(--card);
}

/* Menus déroulants en verre dépoli */
.dropdown-glass {
  background-color: rgba(var(--bg-rgb), 0.92);
  backdrop-filter: blur(16px) saturate(140%);
  -webkit-backdrop-filter: blur(16px) saturate(140%);
  border: 1px solid rgba(var(--accent-rgb), 0.2);
  border-radius: 0.75rem;
  box-shadow: 0 12px 32px -12px rgba(0, 0, 0, 0.4);
  overflow: hidden;
  padding: 0.35rem;
}

.dropdown-divider {
  height: 1px;
  margin: 0.35rem 0.5rem;
  background-color: var(--border);
}

/* Pastille profil : avatar + pseudo, un seul point de clic */
.profile-pill {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.25rem;
  border: 1px solid var(--border);
  border-radius: 999px;
  background-color: rgba(var(--accent-rgb), 0.04);
  transition: border-color 0.3s, background-color 0.3s;
}

.profile-pill:hover {
  border-color: var(--accent);
  background-color: rgba(var(--accent-rgb), 0.08);
}

/* Navigation Link Styles */
.nav-link {
  @apply text-theme-text-muted hover:text-theme-primary px-2 py-1.5 rounded-lg text-sm font-medium transition-all duration-300 relative;
}

.nav-link::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  width: 0;
  height: 2px;
  background-image: linear-gradient(90deg, var(--primary), var(--accent));
  transition: all 0.3s;
  transform: translateX(-50%);
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

/* Priment sur .nav-link / .mobile-nav-link grâce à la double classe */
.nav-link.nav-link-danger,
.mobile-nav-link.nav-link-danger {
  color: var(--error);
}

.nav-link.nav-link-danger:hover,
.mobile-nav-link.nav-link-danger:hover {
  color: var(--error);
  opacity: 0.85;
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
