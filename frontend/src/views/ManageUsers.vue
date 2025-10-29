<template>
  <div class="min-h-screen bg-theme-bg text-theme-text">
    <!-- Notification -->
    <NotificationCenter ref="notificationRef" />

    <!-- Main Container -->
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-6xl font-bold text-theme-primary mb-6">Gestion des Utilisateurs</h1>
        <div class="w-32 h-1 bg-theme-primary mx-auto rounded-full shadow-lg shadow-theme-primary/50"></div>
        <p class="text-theme-text-muted mt-6 text-lg">Gérez tous les utilisateurs du système</p>
      </div>

      <!-- Main Content -->
      <div class="bg-theme-card rounded-2xl border border-theme-border shadow-2xl overflow-visible">
        <!-- Header -->
        <div class="bg-gradient-to-r from-theme-bg-muted to-theme-card border-b border-theme-bg-muted p-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-theme-primary rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-theme-bg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-theme-primary">Tous les Utilisateurs</h2>
                <p class="text-theme-text-muted mt-1">{{ users.length }} utilisateur(s) au total</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 relative" style="overflow: visible;">
          <!-- Search and Filter Bar -->
          <div class="mb-6 flex flex-col sm:flex-row gap-4">
            <!-- Search Bar -->
            <div class="flex-1 relative max-w-md">
              <input
                v-model="searchQuery"
                placeholder="Rechercher par nom d'utilisateur..."
                class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-xl py-3 px-4 pl-12 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400"
              />
              <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            
            <!-- Role Filter -->
            <div class="relative role-filter-container" style="z-index: 10;">
              <button
                @click="toggleRoleFilterDropdown"
                class="w-full sm:w-auto bg-gradient-to-r from-theme-bg-muted to-theme-card border-2 border-theme-border text-theme-text rounded-xl px-4 py-3 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 hover:border-theme-primary/50 shadow-sm hover:shadow-md flex items-center space-x-2 min-w-[200px]"
              >
                <svg class="w-5 h-5 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <span class="flex-1 text-left">
                  {{ selectedRoleFilter ? `Rôle: ${getRoleLabel(selectedRoleFilter)}` : 'Tous les rôles' }}
                </span>
                <svg class="w-4 h-4 text-theme-text-muted transition-transform duration-200" :class="{ 'rotate-180': showRoleFilterDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <!-- Role Filter Dropdown -->
              <Transition name="dropdown">
                <div 
                  v-if="showRoleFilterDropdown"
                  class="absolute z-50 mt-2 w-full bg-theme-card border-2 border-theme-border rounded-xl shadow-2xl max-h-72 overflow-y-auto"
                  style="top: 100%; left: 0; right: 0;"
                >
                <div
                  @click="selectRoleFilter(null)"
                  class="px-4 py-3 text-sm text-theme-text hover:bg-theme-bg-muted cursor-pointer transition-colors duration-200 flex items-center space-x-3"
                  :class="{ 'bg-theme-primary/10 border-l-4 border-l-theme-primary': !selectedRoleFilter }"
                >
                  <svg class="w-5 h-5 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  </svg>
                  <span class="font-medium flex-1">Tous les rôles</span>
                  <svg v-if="!selectedRoleFilter" class="w-5 h-5 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div
                  v-for="role in roleOptions"
                  :key="role.value"
                  @click="selectRoleFilter(role.value)"
                  class="px-4 py-3 text-sm text-theme-text hover:bg-theme-bg-muted cursor-pointer transition-colors duration-200 flex items-center space-x-3"
                  :class="{ 'bg-theme-primary/10 border-l-4 border-l-theme-primary': selectedRoleFilter === role.value }"
                >
                  <div :class="role.iconClass" class="w-6 h-6 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="role.iconPath" />
                    </svg>
                  </div>
                  <span class="font-medium flex-1">{{ role.label }}</span>
                  <svg v-if="selectedRoleFilter === role.value" class="w-5 h-5 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
              </Transition>
            </div>
          </div>

          <!-- Users Table -->
          <div v-if="displayUsers.length > 0" class="overflow-hidden rounded-xl border border-theme-bg-muted">
            <table class="w-full text-left">
              <thead class="bg-theme-bg-muted border-b border-theme-bg-muted">
                <tr class="text-theme-text">
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Utilisateur</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Rôles</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider">Personnage</th>
                  <th class="px-6 py-4 text-sm font-medium uppercase tracking-wider text-center">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-theme-card divide-y divide-gray-700">
                <tr
                  v-for="user in displayUsers"
                  :key="user.id"
                  class="hover:bg-theme-bg-muted transition-colors duration-200"
                >
                  <td class="px-6 py-4">
                    <span class="text-theme-primary font-semibold">{{ user.username }}</span>
                  </td>
                  <td class="px-6 py-4">
                    <!-- Custom Role selector -->
                    <div class="relative role-dropdown-container">
                      <button
                        @click="toggleRoleDropdown(user.id)"
                        class="w-full bg-gradient-to-r from-theme-bg-muted to-theme-card border-2 border-theme-border text-theme-text rounded-xl px-4 py-2.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 min-w-[200px] text-left flex items-center justify-between hover:border-theme-primary/50 shadow-sm hover:shadow-md"
                      >
                        <div class="flex items-center space-x-3">
                          <div :class="getRoleIconClass(user.roles[0])" class="w-6 h-6 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getRoleIconPath(user.roles[0])" />
                            </svg>
                          </div>
                          <span class="font-semibold">{{ getRoleLabel(user.roles[0]) }}</span>
                        </div>
                        <svg class="w-4 h-4 text-theme-text-muted transition-transform duration-200" :class="{ 'rotate-180': openRoleDropdowns[user.id] }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </button>
                      
                      <!-- Dropdown -->
                      <div 
                        v-if="openRoleDropdowns[user.id]"
                        class="absolute z-50 mt-2 w-full bg-theme-card border-2 border-theme-border rounded-xl shadow-2xl overflow-hidden"
                      >
                        <div
                          v-for="role in roleOptions"
                          :key="role.value"
                          @click="selectRole(user, role.value)"
                          class="px-4 py-3 text-sm text-theme-text hover:bg-theme-bg-muted cursor-pointer transition-colors duration-200 flex items-center space-x-3"
                          :class="{ 'bg-theme-primary/10 border-l-4 border-l-theme-primary': user.roles[0] === role.value }"
                        >
                          <div :class="role.iconClass" class="w-6 h-6 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="role.iconPath" />
                            </svg>
                          </div>
                          <span class="font-medium flex-1">{{ role.label }}</span>
                          <svg v-if="user.roles[0] === role.value" class="w-5 h-5 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <!-- Custom Character selector -->
                    <div class="relative character-dropdown-container">
                      <button
                        @click="toggleCharacterDropdown(user.id)"
                        class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary min-w-[200px] text-left flex items-center space-x-2"
                      >
                        <img 
                          v-if="user.characterId && getSelectedCharacter(user.characterId)"
                          :src="getClassIcon(getSelectedCharacter(user.characterId)?.class)" 
                          :alt="`Classe ${getSelectedCharacter(user.characterId)?.class}`" 
                          class="w-5 h-5 rounded border border-theme-border"
                        />
                        <span class="flex-1">
                          {{ user.characterId && getSelectedCharacter(user.characterId) ? getSelectedCharacter(user.characterId).pseudo : 'Aucun personnage' }}
                        </span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                      </button>
                      
                      <!-- Dropdown -->
                      <div 
                        v-if="openDropdowns[user.id]"
                        class="absolute z-50 mt-1 w-full bg-theme-card border border-theme-border rounded-lg shadow-lg max-h-64 overflow-auto"
                      >
                        <div
                          @click="selectCharacter(user, null)"
                          class="px-3 py-2 text-sm text-theme-text hover:bg-theme-bg-muted cursor-pointer flex items-center space-x-2"
                          :class="{ 'bg-theme-primary/10': !user.characterId }"
                        >
                          <div class="w-5 h-5"></div>
                          <span>Aucun personnage</span>
                        </div>
                        <div
                          v-for="character in characters"
                          :key="character.id"
                          @click="selectCharacter(user, character)"
                          class="px-3 py-2 text-sm text-theme-text hover:bg-theme-bg-muted cursor-pointer flex items-center space-x-2"
                          :class="{ 'bg-theme-primary/10': user.characterId === character.id }"
                        >
                          <img 
                            :src="getClassIcon(character.class)" 
                            :alt="`Classe ${character.class}`" 
                            class="w-5 h-5 rounded border border-theme-border"
                          />
                          <span>{{ character.pseudo }}</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center justify-center space-x-2">
                      <button
                        @click="forceDisconnect(user)"
                        class="px-4 py-2 bg-theme-warning hover:bg-theme-warning/80 text-white text-sm font-semibold rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-warning"
                        title="Déconnecter de force"
                      >
                        Déconnecter
                      </button>
                      <button
                        @click="confirmDeleteUser(user)"
                        class="px-4 py-2 bg-theme-error hover:bg-theme-error/80 text-white text-sm font-semibold rounded-lg transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-error"
                        title="Supprimer l'utilisateur"
                      >
                        Supprimer
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-else-if="users.length === 0" class="text-center py-12">
            <p class="text-theme-text-muted text-lg">Aucun utilisateur</p>
          </div>
          <div v-else class="text-center py-12">
            <p class="text-theme-text-muted text-lg">Aucun utilisateur ne correspond aux critères de recherche</p>
            <button
              @click="selectedRoleFilter = null; searchQuery = ''"
              class="mt-4 px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium"
            >
              Réinitialiser les filtres
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="bg-theme-card rounded-2xl border border-theme-border shadow-2xl p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-theme-error mb-4">Confirmer la suppression</h3>
        <p class="text-theme-text-muted mb-6">
          Êtes-vous sûr de vouloir supprimer l'utilisateur <strong>{{ userToDelete?.username }}</strong> ? 
          Cette action est irréversible et supprimera toutes ses données.
        </p>
        <div class="flex space-x-4">
          <button
            @click="showDeleteModal = false"
            class="flex-1 px-6 py-3 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-theme-border"
          >
            Annuler
          </button>
          <button
            @click="deleteUser"
            class="flex-1 px-6 py-3 bg-theme-error hover:bg-theme-error/80 text-white font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-theme-error"
          >
            Supprimer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from '@/config/axios';
import NotificationCenter from '@/components/NotificationCenter.vue';
import { API_URL } from '@/config/apiUrl';

const users = ref([]);
const characters = ref([]);
const searchQuery = ref('');
const selectedRoleFilter = ref(null);
const showRoleFilterDropdown = ref(false);
const showDeleteModal = ref(false);
const userToDelete = ref(null);
const notificationRef = ref(null);
const openDropdowns = ref({});
const openRoleDropdowns = ref({});

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

const getClassIcon = (className) => {
  return classes[className] || classes.cra;
};

const roleOptions = [
  {
    value: 'ROLE_USER',
    label: 'Utilisateur',
    iconClass: 'bg-blue-500/20 text-blue-400',
    iconPath: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
  },
  {
    value: 'ROLE_ADMIN',
    label: 'Administrateur',
    iconClass: 'bg-green-500/20 text-green-400',
    iconPath: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
  },
  {
    value: 'ROLE_SUPER_ADMIN',
    label: 'Super Administrateur',
    iconClass: 'bg-purple-500/20 text-purple-400',
    iconPath: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'
  },
  {
    value: 'ROLE_OWNERS',
    label: 'Propriétaires',
    iconClass: 'bg-gradient-to-br from-yellow-500/20 to-orange-500/20 text-yellow-400',
    iconPath: 'M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm3.5 6L12 10.5 8.5 8 12 5.5 15.5 8zM8.5 16L12 13.5 15.5 16 12 18.5 8.5 16z'
  }
];

const getRoleLabel = (role) => {
  const roleOption = roleOptions.find(r => r.value === role);
  return roleOption ? roleOption.label : role;
};

const getRoleIconClass = (role) => {
  const roleOption = roleOptions.find(r => r.value === role);
  return roleOption ? roleOption.iconClass : 'bg-gray-500/20 text-gray-400';
};

const getRoleIconPath = (role) => {
  const roleOption = roleOptions.find(r => r.value === role);
  return roleOption ? roleOption.iconPath : 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z';
};

const toggleRoleDropdown = (userId) => {
  openRoleDropdowns.value[userId] = !openRoleDropdowns.value[userId];
  
  // Close other dropdowns
  Object.keys(openRoleDropdowns.value).forEach(key => {
    if (key != userId) {
      openRoleDropdowns.value[key] = false;
    }
  });
  
  // Close character dropdowns when opening role dropdown
  Object.keys(openDropdowns.value).forEach(key => {
    openDropdowns.value[key] = false;
  });
};

const selectRole = (user, role) => {
  user.roles = [role];
  openRoleDropdowns.value[user.id] = false;
  updateUserRoles(user);
};

const toggleRoleFilterDropdown = () => {
  showRoleFilterDropdown.value = !showRoleFilterDropdown.value;
};

const selectRoleFilter = (role) => {
  selectedRoleFilter.value = role;
  showRoleFilterDropdown.value = false;
};

const displayUsers = computed(() => {
  let filtered = users.value;
  
  // Filter by role
  if (selectedRoleFilter.value) {
    filtered = filtered.filter(user => 
      user.roles.includes(selectedRoleFilter.value)
    );
  }
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(user => 
      user.username.toLowerCase().includes(query)
    );
  }
  
  return filtered;
});

const fetchUsers = async () => {
  try {
    const response = await axios.get(`${API_URL}/admin/users`);
    users.value = response.data.map(user => {
      // Determine the main role
      let mainRole = 'ROLE_USER';
      if (user.roles.includes('ROLE_OWNERS')) {
        mainRole = 'ROLE_OWNERS';
      } else if (user.roles.includes('ROLE_SUPER_ADMIN')) {
        mainRole = 'ROLE_SUPER_ADMIN';
      } else if (user.roles.includes('ROLE_ADMIN')) {
        mainRole = 'ROLE_ADMIN';
      }
      return {
        ...user,
        roles: [mainRole]
      };
    });
  } catch (error) {
    console.error('Error fetching users:', error);
    if (notificationRef.value) {
      notificationRef.value.showNotification('Erreur lors de la récupération des utilisateurs', 'error');
    }
  }
};

const fetchCharacters = async () => {
  try {
    const response = await axios.get(`${API_URL}/characters`);
    characters.value = response.data.filter(c => !c.isArchived);
  } catch (error) {
    console.error('Error fetching characters:', error);
  }
};

const updateUserRoles = async (user) => {
  try {
    await axios.put(`${API_URL}/admin/users/${user.id}`, {
      roles: user.roles
    });
    
    // Force disconnect if role changed
    if (notificationRef.value) {
      notificationRef.value.showNotification(
        `Rôles de ${user.username} mis à jour. L'utilisateur sera déconnecté à la prochaine requête.`,
        'success'
      );
    }
    
    // Automatically force disconnect after role change
    await forceDisconnect(user, false);
  } catch (error) {
    console.error('Error updating user roles:', error);
    if (notificationRef.value) {
      notificationRef.value.showNotification('Erreur lors de la mise à jour des rôles', 'error');
    }
  }
};

const updateUserCharacter = async (user) => {
  try {
    await axios.put(`${API_URL}/admin/users/${user.id}`, {
      characterId: user.characterId
    });
    
    if (notificationRef.value) {
      const characterName = user.characterId 
        ? characters.value.find(c => c.id === user.characterId)?.pseudo 
        : 'aucun';
      notificationRef.value.showNotification(
        `Personnage de ${user.username} mis à jour: ${characterName}. L'utilisateur sera déconnecté à la prochaine requête.`,
        'success'
      );
    }
    
    // Force disconnect after character change
    await forceDisconnect(user, false);
  } catch (error) {
    console.error('Error updating user character:', error);
    if (notificationRef.value) {
      notificationRef.value.showNotification('Erreur lors de la mise à jour du personnage', 'error');
    }
  }
};

const getSelectedCharacter = (characterId) => {
  return characters.value.find(c => c.id === characterId);
};

const toggleCharacterDropdown = (userId) => {
  openDropdowns.value[userId] = !openDropdowns.value[userId];
  
  // Close other dropdowns
  Object.keys(openDropdowns.value).forEach(key => {
    if (key != userId) {
      openDropdowns.value[key] = false;
    }
  });
};

const selectCharacter = (user, character) => {
  user.characterId = character ? character.id : null;
  openDropdowns.value[user.id] = false;
  updateUserCharacter(user);
};

const forceDisconnect = async (user, showNotif = true) => {
  try {
    await axios.post(`${API_URL}/admin/users/${user.id}/disconnect`);
    
    if (showNotif && notificationRef.value) {
      notificationRef.value.showNotification(
        `${user.username} a été déconnecté de force`,
        'warning'
      );
    }
  } catch (error) {
    console.error('Error forcing disconnect:', error);
    if (showNotif && notificationRef.value) {
      notificationRef.value.showNotification('Erreur lors de la déconnexion forcée', 'error');
    }
  }
};

const confirmDeleteUser = (user) => {
  userToDelete.value = user;
  showDeleteModal.value = true;
};

const deleteUser = async () => {
  if (!userToDelete.value) return;
  
  try {
    await axios.delete(`${API_URL}/admin/users/${userToDelete.value.id}`);
    
    if (notificationRef.value) {
      notificationRef.value.showNotification(
        `L'utilisateur ${userToDelete.value.username} a été supprimé`,
        'success'
      );
    }
    
    showDeleteModal.value = false;
    userToDelete.value = null;
    
    // Refresh users list
    await fetchUsers();
  } catch (error) {
    console.error('Error deleting user:', error);
    if (notificationRef.value) {
      const errorMsg = error.response?.data?.error || 'Erreur lors de la suppression';
      notificationRef.value.showNotification(errorMsg, 'error');
    }
    showDeleteModal.value = false;
  }
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.character-dropdown-container')) {
    Object.keys(openDropdowns.value).forEach(key => {
      openDropdowns.value[key] = false;
    });
  }
  if (!event.target.closest('.role-dropdown-container')) {
    Object.keys(openRoleDropdowns.value).forEach(key => {
      openRoleDropdowns.value[key] = false;
    });
  }
  if (!event.target.closest('.role-filter-container')) {
    showRoleFilterDropdown.value = false;
  }
};

onMounted(async () => {
  await Promise.all([fetchUsers(), fetchCharacters()]);
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Additional styles if needed */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
