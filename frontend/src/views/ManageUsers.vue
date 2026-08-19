<template>
  <div class="min-h-screen">
    <NotificationCenter ref="notificationRef" />

    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Gestion des Utilisateurs</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Gérez les comptes, rôles et personnages liés du système</p>
      </div>

      <!-- Toolbar -->
      <div class="glass-card rounded-2xl p-5 mb-6 relative z-20">
        <div class="flex flex-col md:flex-row md:items-center gap-4 md:justify-between">
          <div class="relative flex-1 md:max-w-md">
            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input
              v-model="searchQuery"
              placeholder="Rechercher par nom d'utilisateur..."
              class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-xl py-2.5 pl-10 pr-4 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200"
            />
          </div>

          <div class="flex items-center gap-3">
            <span class="text-sm text-theme-text-muted whitespace-nowrap">{{ displayUsers.length }} utilisateur{{ displayUsers.length === 1 ? '' : 's' }}</span>

            <div class="relative role-filter-container">
              <button
                @click="toggleRoleFilterDropdown"
                class="mu-select-btn min-w-[190px]"
              >
                <div v-if="selectedRoleFilter" :class="getRoleIconClass(selectedRoleFilter)" class="mu-role-dot"></div>
                <span class="flex-1 text-left truncate">{{ selectedRoleFilter ? getRoleLabel(selectedRoleFilter) : 'Tous les rôles' }}</span>
                <svg class="w-4 h-4 text-theme-text-muted transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': showRoleFilterDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <Transition name="dropdown">
                <div v-if="showRoleFilterDropdown" class="mu-menu">
                  <button
                    @click="selectRoleFilter(null)"
                    class="mu-menu-option"
                    :class="{ 'mu-menu-option--active': !selectedRoleFilter }"
                  >
                    <span class="font-medium flex-1 text-left">Tous les rôles</span>
                    <svg v-if="!selectedRoleFilter" class="w-4 h-4 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  </button>
                  <button
                    v-for="role in roleOptions"
                    :key="role.value"
                    @click="selectRoleFilter(role.value)"
                    class="mu-menu-option"
                    :class="{ 'mu-menu-option--active': selectedRoleFilter === role.value }"
                  >
                    <span :class="role.iconClass" class="mu-role-dot"></span>
                    <span class="font-medium flex-1 text-left">{{ role.label }}</span>
                    <svg v-if="selectedRoleFilter === role.value" class="w-4 h-4 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  </button>
                </div>
              </Transition>
            </div>
          </div>
        </div>
      </div>

      <!-- Users -->
      <div v-if="displayUsers.length > 0" class="glass-card rounded-2xl">
        <div v-for="user in displayUsers" :key="user.id" class="mu-row">
          <span class="mu-username">{{ user.username }}</span>

          <div class="relative role-dropdown-container">
            <button @click="toggleRoleDropdown(user.id)" class="mu-select-btn min-w-[170px]">
              <span :class="getRoleIconClass(user.roles[0])" class="mu-role-dot"></span>
              <span class="flex-1 text-left truncate font-semibold">{{ getRoleLabel(user.roles[0]) }}</span>
              <svg class="w-4 h-4 text-theme-text-muted transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': openRoleDropdowns[user.id] }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>

            <div v-if="openRoleDropdowns[user.id]" class="mu-menu">
              <button
                v-for="role in roleOptions"
                :key="role.value"
                @click="selectRole(user, role.value)"
                class="mu-menu-option"
                :class="{ 'mu-menu-option--active': user.roles[0] === role.value }"
              >
                <span :class="role.iconClass" class="mu-role-dot"></span>
                <span class="font-medium flex-1 text-left">{{ role.label }}</span>
                <svg v-if="user.roles[0] === role.value" class="w-4 h-4 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
              </button>
            </div>
          </div>

          <div class="relative character-dropdown-container">
            <button @click="toggleCharacterDropdown(user.id)" class="mu-select-btn min-w-[170px]">
              <img
                v-if="user.characterId && getSelectedCharacter(user.characterId)"
                :src="getClassIcon(getSelectedCharacter(user.characterId)?.class)"
                :alt="`Classe ${getSelectedCharacter(user.characterId)?.class}`"
                class="mu-char-icon"
              />
              <span class="flex-1 text-left truncate">{{ user.characterId && getSelectedCharacter(user.characterId) ? getSelectedCharacter(user.characterId).pseudo : 'Aucun personnage' }}</span>
              <svg class="w-4 h-4 text-theme-text-muted transition-transform duration-200 flex-shrink-0" :class="{ 'rotate-180': openDropdowns[user.id] }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
            </button>

            <div v-if="openDropdowns[user.id]" class="mu-menu mu-menu--scroll">
              <button @click="selectCharacter(user, null)" class="mu-menu-option" :class="{ 'mu-menu-option--active': !user.characterId }">
                <span class="w-5 h-5 flex-shrink-0"></span>
                <span class="flex-1 text-left">Aucun personnage</span>
              </button>
              <button
                v-for="character in characters"
                :key="character.id"
                @click="selectCharacter(user, character)"
                class="mu-menu-option"
                :class="{ 'mu-menu-option--active': user.characterId === character.id }"
              >
                <img :src="getClassIcon(character.class)" :alt="`Classe ${character.class}`" class="mu-char-icon" />
                <span class="flex-1 text-left truncate">{{ character.pseudo }}</span>
              </button>
            </div>
          </div>

          <div class="mu-actions">
            <button @click="forceDisconnect(user)" class="p-2 text-theme-warning hover:bg-theme-warning/15 rounded-lg transition-all duration-200" title="Déconnecter de force">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
            </button>
            <button @click="confirmDeleteUser(user)" class="p-2 text-theme-text-muted hover:text-theme-error hover:bg-theme-error/15 rounded-lg transition-all duration-200" title="Supprimer l'utilisateur">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
          </div>
        </div>
      </div>

      <div v-else-if="users.length === 0" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
        <p class="text-lg font-medium">Aucun utilisateur</p>
      </div>
      <div v-else class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
        <p class="text-lg font-medium mb-3">Aucun utilisateur ne correspond aux critères de recherche</p>
        <button
          @click="selectedRoleFilter = null; searchQuery = ''"
          class="px-4 py-2 bg-theme-primary/10 hover:bg-theme-primary/20 text-theme-primary rounded-lg transition-colors duration-200 text-sm font-medium"
        >
          Réinitialiser les filtres
        </button>
      </div>
    </div>
  </div>

  <ConfirmModal
    :show="showDeleteModal"
    title="Confirmer la suppression"
    :message="userToDelete ? `Êtes-vous sûr de vouloir supprimer l'utilisateur ${userToDelete.username} ? Cette action est irréversible et supprimera toutes ses données.` : ''"
    confirmText="Supprimer"
    @confirm="deleteUser"
    @cancel="showDeleteModal = false"
  />
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from '@/config/axios';
import NotificationCenter from '@/components/NotificationCenter.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import { getClassIcon } from '@/config/classIcons';

const API_URL = import.meta.env.VITE_API_URL;

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

const roleOptions = [
  { value: 'ROLE_USER', label: 'Utilisateur', iconClass: 'bg-blue-500/70' },
  { value: 'ROLE_ADMIN', label: 'Administrateur', iconClass: 'bg-green-500/70' },
  { value: 'ROLE_SUPER_ADMIN', label: 'Super Administrateur', iconClass: 'bg-purple-500/70' },
  { value: 'ROLE_OWNERS', label: 'Propriétaires', iconClass: 'bg-gradient-to-br from-yellow-400 to-orange-500' },
];

const getRoleLabel = (role) => {
  const roleOption = roleOptions.find(r => r.value === role);
  return roleOption ? roleOption.label : role;
};

const getRoleIconClass = (role) => {
  const roleOption = roleOptions.find(r => r.value === role);
  return roleOption ? roleOption.iconClass : 'bg-gray-500/70';
};

const toggleRoleDropdown = (userId) => {
  openRoleDropdowns.value[userId] = !openRoleDropdowns.value[userId];
  Object.keys(openRoleDropdowns.value).forEach(key => {
    if (key != userId) openRoleDropdowns.value[key] = false;
  });
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

  if (selectedRoleFilter.value) {
    filtered = filtered.filter(user => user.roles.includes(selectedRoleFilter.value));
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(user => user.username.toLowerCase().includes(query));
  }

  return filtered;
});

const fetchUsers = async () => {
  try {
    const response = await axios.get(`${API_URL}/admin/users`);
    users.value = response.data.map(user => {
      let mainRole = 'ROLE_USER';
      if (user.roles.includes('ROLE_OWNERS')) {
        mainRole = 'ROLE_OWNERS';
      } else if (user.roles.includes('ROLE_SUPER_ADMIN')) {
        mainRole = 'ROLE_SUPER_ADMIN';
      } else if (user.roles.includes('ROLE_ADMIN')) {
        mainRole = 'ROLE_ADMIN';
      }
      return { ...user, roles: [mainRole] };
    });
  } catch (error) {
    console.error('Error fetching users:', error);
    notificationRef.value?.showNotification('Erreur lors de la récupération des utilisateurs', 'error');
  }
};

const fetchCharacters = async () => {
  try {
    const response = await axios.get(`${API_URL}/characters/`);
    characters.value = response.data.filter(c => !c.isArchived);
  } catch (error) {
    console.error('Error fetching characters:', error.response?.data || error.message);
  }
};

const updateUserRoles = async (user) => {
  try {
    await axios.put(`${API_URL}/admin/users/${user.id}`, { roles: user.roles });
    notificationRef.value?.showNotification(
      `Rôles de ${user.username} mis à jour. L'utilisateur sera déconnecté à la prochaine requête.`,
      'success'
    );
    await forceDisconnect(user, false);
  } catch (error) {
    console.error('Error updating user roles:', error);
    notificationRef.value?.showNotification('Erreur lors de la mise à jour des rôles', 'error');
  }
};

const updateUserCharacter = async (user) => {
  try {
    await axios.put(`${API_URL}/admin/users/${user.id}`, { characterId: user.characterId });
    const characterName = user.characterId
      ? characters.value.find(c => c.id === user.characterId)?.pseudo
      : 'aucun';
    notificationRef.value?.showNotification(
      `Personnage de ${user.username} mis à jour: ${characterName}. L'utilisateur sera déconnecté à la prochaine requête.`,
      'success'
    );
    await forceDisconnect(user, false);
  } catch (error) {
    console.error('Error updating user character:', error);
    notificationRef.value?.showNotification('Erreur lors de la mise à jour du personnage', 'error');
  }
};

const getSelectedCharacter = (characterId) => characters.value.find(c => c.id === characterId);

const toggleCharacterDropdown = (userId) => {
  openDropdowns.value[userId] = !openDropdowns.value[userId];
  Object.keys(openDropdowns.value).forEach(key => {
    if (key != userId) openDropdowns.value[key] = false;
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
    if (showNotif) {
      notificationRef.value?.showNotification(`${user.username} a été déconnecté de force`, 'warning');
    }
  } catch (error) {
    console.error('Error forcing disconnect:', error);
    if (showNotif) {
      notificationRef.value?.showNotification('Erreur lors de la déconnexion forcée', 'error');
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
    notificationRef.value?.showNotification(`L'utilisateur ${userToDelete.value.username} a été supprimé`, 'success');
    showDeleteModal.value = false;
    userToDelete.value = null;
    await fetchUsers();
  } catch (error) {
    console.error('Error deleting user:', error);
    const errorMsg = error.response?.data?.error || 'Erreur lors de la suppression';
    notificationRef.value?.showNotification(errorMsg, 'error');
    showDeleteModal.value = false;
  }
};

const handleClickOutside = (event) => {
  if (!event.target.closest('.character-dropdown-container')) {
    Object.keys(openDropdowns.value).forEach(key => { openDropdowns.value[key] = false; });
  }
  if (!event.target.closest('.role-dropdown-container')) {
    Object.keys(openRoleDropdowns.value).forEach(key => { openRoleDropdowns.value[key] = false; });
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
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.mu-row {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.85rem 1.25rem;
  border-bottom: 1px solid var(--border);
  transition: background-color 0.2s;
}
.mu-row:last-child {
  border-bottom: none;
}
.mu-row:hover {
  background-color: rgba(var(--primary-rgb), 0.04);
}

.mu-username {
  flex: 1;
  min-width: 0;
  font-weight: 700;
  color: var(--primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.mu-select-btn {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.5rem 0.75rem;
  border-radius: 0.65rem;
  background-color: var(--bg-muted);
  border: 1px solid var(--border);
  color: var(--text);
  font-size: 0.82rem;
  transition: border-color 0.2s;
}
.mu-select-btn:hover {
  border-color: var(--primary);
}

.mu-role-dot {
  width: 0.6rem;
  height: 0.6rem;
  border-radius: 9999px;
  flex-shrink: 0;
}

.mu-char-icon {
  width: 1.4rem;
  height: 1.4rem;
  border-radius: 0.35rem;
  border: 1px solid var(--border);
  object-fit: cover;
  flex-shrink: 0;
}

.mu-actions {
  display: flex;
  align-items: center;
  gap: 0.15rem;
  flex-shrink: 0;
}

.mu-menu {
  position: absolute;
  z-index: 50;
  margin-top: 0.4rem;
  width: 100%;
  min-width: 220px;
  background-color: var(--card);
  border: 1px solid rgba(var(--accent-rgb), 0.2);
  border-radius: 0.75rem;
  box-shadow: 0 24px 64px -24px rgba(0, 0, 0, 0.5);
  padding: 0.35rem;
  overflow: hidden;
}
.mu-menu--scroll {
  max-height: 16rem;
  overflow-y: auto;
}

.mu-menu-option {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.55rem 0.65rem;
  border-radius: 0.55rem;
  font-size: 0.82rem;
  color: var(--text);
  transition: background-color 0.2s;
}
.mu-menu-option:hover {
  background-color: rgba(var(--primary-rgb), 0.08);
}
.mu-menu-option--active {
  background-color: rgba(var(--primary-rgb), 0.1);
  color: var(--primary);
}

@media (max-width: 720px) {
  .mu-row {
    flex-wrap: wrap;
  }
  .mu-username {
    width: 100%;
    flex: none;
  }
}
</style>
