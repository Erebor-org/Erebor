<template>
  <div class="min-h-screen bg-theme-bg text-theme-text">
    <!-- Notification -->
    <Notification ref="notificationRef" />

    <!-- Main Container -->
    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-6xl font-bold text-theme-primary mb-6">Gestion des Utilisateurs</h1>
        <div class="w-32 h-1 bg-theme-primary mx-auto rounded-full shadow-lg shadow-theme-primary/50"></div>
        <p class="text-theme-text-muted mt-6 text-lg">Gérez tous les utilisateurs du système</p>
      </div>

      <!-- Main Content -->
      <div class="bg-theme-card rounded-2xl border border-theme-border shadow-2xl overflow-hidden">
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
        <div class="p-6">
          <!-- Search Bar -->
          <div class="mb-6">
            <div class="relative max-w-md">
              <input
                v-model="searchQuery"
                placeholder="Rechercher par nom d'utilisateur..."
                class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-xl py-3 px-4 pl-12 text-base focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400"
              />
              <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>

          <!-- Users Table -->
          <div v-if="users.length > 0" class="overflow-hidden rounded-xl border border-theme-bg-muted">
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
                    <select
                      v-model="user.roles"
                      @change="updateUserRoles(user)"
                      class="bg-theme-bg-muted border border-theme-border text-theme-text rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary"
                    >
                      <option :value="['ROLE_USER']">Utilisateur</option>
                      <option :value="['ROLE_ADMIN']">Administrateur</option>
                    </select>
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

          <div v-else class="text-center py-12">
            <p class="text-theme-text-muted text-lg">Aucun utilisateur trouvé</p>
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
import Notification from '@/components/Notification.vue';

const API_URL = import.meta.env.VITE_API_URL;

const users = ref([]);
const characters = ref([]);
const searchQuery = ref('');
const showDeleteModal = ref(false);
const userToDelete = ref(null);
const notificationRef = ref(null);
const openDropdowns = ref({});

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

const displayUsers = computed(() => {
  if (!searchQuery.value) {
    return users.value;
  }
  const query = searchQuery.value.toLowerCase();
  return users.value.filter(user => 
    user.username.toLowerCase().includes(query)
  );
});

const fetchUsers = async () => {
  try {
    const response = await axios.get(`${API_URL}/admin/users`);
    users.value = response.data.map(user => ({
      ...user,
      roles: user.roles.includes('ROLE_ADMIN') ? ['ROLE_ADMIN'] : ['ROLE_USER']
    }));
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
</style>
