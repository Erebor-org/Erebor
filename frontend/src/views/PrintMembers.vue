<template>
  <div
    class="min-h-screen w-full"
    :style="{
      backgroundImage: `url(${backgroundImage})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      backgroundAttachment: 'fixed',
    }"
  >
    <div class="container mx-auto py-16 px-4">
    <!-- Tab Selector -->
    <TabSelector 
      :active-tab="activeTab" 
      @update:active-tab="activeTab = $event" 
    />

    <!-- Search Bar and Add Member Button -->
    <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-8 mb-6">
      <MemberSearch 
        v-model="currentSearchQuery" 
        :placeholder="activeTab === 'active' ? 'Rechercher un membre...' : 'Rechercher dans les archives...'"
        @add-character="openImportMemberModal" 
      />
    </div>

    <!-- Active Members Table -->
    <ActiveMembersTable
      v-if="activeTab === 'active'"
      :members="filteredMembers"
      :classes="classes"
      :character-warning-counts="characterWarningCounts"
      :not-archived-mules="notArchivedMules"
      :class-dropdown-visible="classDropdownVisible"
      :expanded-rows="expandedRows"
      @toggle-class-dropdown="toggleClassDropdown"
      @update-character-class="updateCharacterClass"
      @update-mule-class="updateMuleClass"
      @update-character-pseudo="updateCharacterPseudo"
      @update-mule-pseudo="updateMulePseudo"
      @toggle-expand="toggleExpand"
      @view-warnings="viewWarnings"
      @open-modal="openModal"
      @open-mule-modal="openMuleModal"
      @notification="message => $refs.notificationRef.showNotification(message)"
    />

    <!-- Archived Members Table -->
    <ArchivedMembersTable
      v-if="activeTab === 'archived'"
      :members="filteredArchivedMembers"
      :classes="classes"
      @unarchive="openUnarchivedCharacterModal"
    />

    <!-- Member Modals -->
    <MemberModals
      :show-archive-modal="showModal"
      :show-archive-mule-modal="showModalMule"
      :show-unarchive-modal="showUnarchivedCharacterModal"
      :character="selectedMember"
      :mule="selectedMule"
      :unarchived-character="selectedUnarchivedCharacter"
      @close-archive-modal="closeModal"
      @close-archive-mule-modal="closeMuleModal"
      @close-unarchive-modal="closeUnarchivedCharacterModal"
      @confirm-archive="archiveCharacter"
      @confirm-archive-mule="archiveMule"
      @confirm-unarchive="unarchiveCharacter"
    />
    
    <!-- Notification Component -->
    <NotificationCenter ref="notificationRef" />
    
    <!-- Import Member Modal -->
    <ImportMember
      :showModalMember="showModalMember"
      :fetchNotArchivedCharacters="notArchivedCharacters"
      @close="closeImportMemberModal"
      @characterAdded="addCharacterToTable"
      @muleAdded="addMuleToTable"
    />
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { reactive } from 'vue';
import NotificationComp from '../components/NotificationCenter.vue';
import TabSelector from '../components/Members/TabSelector.vue';
import MemberSearch from '../components/Members/MemberSearch.vue';
import ActiveMembersTable from '../components/Members/ActiveMembersTable.vue';
import ArchivedMembersTable from '../components/Members/ArchivedMembersTable.vue';
import MemberModals from '../components/Members/MemberModals.vue';
import ImportMember from '../components/ImportMember.vue';

import members_bg from '../assets/members_bg.webp';

// Import class icons dynamically
const images = import.meta.glob('/src/assets/icon_classe/*.avif', { eager: true });

const API_URL = import.meta.env.VITE_API_URL;

export default {
  name: 'PrintMembers',
  
  components: {
    NotificationCenter: NotificationComp,
    TabSelector,
    MemberSearch,
    ActiveMembersTable,
    ArchivedMembersTable,
    ImportMember,
    MemberModals
  },
  data() {
    return {
      backgroundImage: members_bg,
      searchQuery: '',
      archivedSearchQuery: '',
      charactersData: [], // Holds all characters (archived + non-archived),
      notArchivedCharacters: [],
      notArchivedMules: {},
      characterWarningCounts: {},
      
      // State for modals
      showModal: false,
      showModalMule: false,
      showModalMember: false,
      showUnarchivedCharacterModal: false,
      selectedMember: null,
      selectedMule: null,
      selectedUnarchivedCharacter: null,
      
      // UI state
      activeTab: 'active',
      classDropdownVisible: reactive({}),
      expandedRows: reactive({}),
      currentCharacterId: null,
      currentCharacterMules: [],
      
      // Class icons
      classes: {
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
      },
    };
  },
  computed: {
    // Filtered characters
    charactersNotArchived() {
      return this.charactersData.filter(character => !character.isArchived);
    },
    
    archivedCharacters() {
      return this.charactersData.filter(character => character.isArchived);
    },
    
    // Search query based on active tab
    currentSearchQuery: {
      get() {
        return this.activeTab === 'active' ? this.searchQuery : this.archivedSearchQuery;
      },
      set(value) {
        if (this.activeTab === 'active') {
          this.searchQuery = value;
        } else {
          this.archivedSearchQuery = value;
        }
      },
    },
    
    // Filtered members based on search query
    filteredMembers() {
      const query = this.searchQuery.toLowerCase();
      return this.charactersNotArchived
        .filter(member => {
          // Normalize strings for search matching
          const normalize = str =>
            str
              .normalize('NFD')
              .replace(/[\u0300-\u036f]/g, '')
              .toLowerCase();

          // Match character pseudo, recruiter pseudo, rank, or mules' pseudo
          const memberPseudoMatch = normalize(member.pseudo).includes(query);
          const recruiterPseudoMatch = normalize(member.recruiter?.pseudo || '').includes(query);
          const rankMatch = normalize(member.rank?.name || '').includes(query);

          // Check mules for pseudo matches
          const mulesMatch = this.filteredMulesByCharacter(member.id).some(mule =>
            normalize(mule.pseudo).includes(query)
          );

          return memberPseudoMatch || recruiterPseudoMatch || rankMatch || mulesMatch;
        })
        .map(member => ({
          member,
          id: member.id,
        }));
    },
    
    // Filtered archived members based on search query
    filteredArchivedMembers() {
      const query = this.archivedSearchQuery.toLowerCase();
      return this.archivedCharacters
        .filter(member => {
          const normalize = str =>
            str
              .normalize('NFD')
              .replace(/[\u0300-\u036f]/g, '')
              .toLowerCase();

          const memberPseudoMatch = normalize(member.pseudo).includes(query);
          const recruiterPseudoMatch = normalize(member.recruiter?.pseudo || '').includes(query);
          const rankMatch = normalize(member.rank?.name || '').includes(query);

          return memberPseudoMatch || recruiterPseudoMatch || rankMatch;
        })
        .map(member => ({
          member,
          id: member.id,
        }));
    },
  },
  methods: {
    // Data fetching methods
    async fetchCharacters() {
      try {
        const response = await axios.get(`${API_URL}/characters/`);
        this.charactersData = response.data;
        this.notArchivedCharacters = response.data.filter(character => !character.isArchived);
      } catch (error) {
        console.error('Error fetching characters:', error.response?.data || error.message);
      }
    },

    async fetchWarningCounts() {
      try {
        const response = await axios.get(`${API_URL}/warnings`);
        const warnings = response.data;

        // Count warnings by character
        const counts = {};
        warnings.forEach(warning => {
          const characterId = warning.character.id;
          if (!counts[characterId]) {
            counts[characterId] = 0;
          }
          counts[characterId]++;
        });

        this.characterWarningCounts = counts;
      } catch (error) {
        console.error('Error fetching warning counts:', error);
      }
    },
    
    async fetchAllMules() {
      try {
        const response = await axios.get(`${API_URL}/mules`);
        const mules = response.data.filter(mule => !mule.isArchived);

        this.notArchivedMules = {};
        mules.forEach(mule => {
          const charId = mule.mainCharacter?.id;
          if (!this.notArchivedMules[charId]) {
            this.notArchivedMules[charId] = [];
          }
          this.notArchivedMules[charId].push(mule);
        });
      } catch (error) {
        console.error('Error fetching mules:', error);
      }
    },
    
    // Character/Mule operations
    async archiveCharacter(characterId) {
      try {
        await axios.put(`${API_URL}/characters/${characterId}/archive`, { isArchived: true });

        // Find and update the character locally
        const character = this.charactersData.find(c => c.id === characterId);
        if (character) character.isArchived = true;

        this.showModal = false;
        this.$refs.notificationRef.showNotification(`${character.pseudo} a bien été archivé`);
      } catch (error) {
        console.error('Error archiving character:', error.response?.data || error.message);
      }
    },

    async unarchiveCharacter(characterId) {
      try {
        await axios.put(`${API_URL}/characters/${characterId}/unarchive`, { isArchived: false });

        // Find and update the character locally
        const character = this.charactersData.find(c => c.id === characterId);
        if (character) character.isArchived = false;

        this.showUnarchivedCharacterModal = false;
        this.$refs.notificationRef.showNotification('Personnage restauré avec succès !');
      } catch (error) {
        console.error('Error unarchiving character:', error.response?.data || error.message);
      }
    },
    
    async archiveMule(muleId) {
      try {
        if (!this.selectedMule || !this.selectedMule.id) {
          console.error('Selected mule is null or missing an ID.');
          return;
        }

        // Archive the mule in the backend
        await axios.put(`${API_URL}/mule/archive/${muleId}`, {
          isArchived: true,
        });
        
        // Update the state without refreshing
        Object.keys(this.notArchivedMules).forEach(characterId => {
          this.notArchivedMules[characterId] = this.notArchivedMules[characterId].filter(
            mule => mule.id !== muleId
          );
        });

        this.closeMuleModal();

        // Show notification
        const mulePseudo = this.selectedMule.pseudo || 'La mule';
        this.$refs.notificationRef.showNotification(`${mulePseudo} a bien été archivée.`);
      } catch (error) {
        console.error('Error archiving mule:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification("Erreur lors de l'archivage.");
      }
    },
    
    async updateCharacterClass(characterId, newClass) {
      try {
        await axios.put(`${API_URL}/characters/${characterId}/update-class`, {
          class: newClass,
        });

        // Update the character's class locally
        const character = this.charactersNotArchived.find(member => member.id === characterId);
        if (character) {
          character.class = newClass;
        }

        // Close all dropdowns
        this.closeAllDropdowns();

        // Show success notification
        this.$refs.notificationRef.showNotification('Classe mise à jour avec succès !');
      } catch (error) {
        console.error('Erreur lors de la mise à jour de la classe:', error.message);
        this.$refs.notificationRef.showNotification('Échec de la mise à jour de la classe.');
      }
    },
    
    async updateMuleClass(muleId, newClass) {
      try {
        await axios.put(`${API_URL}/mules/${muleId}/update-class`, {
          class: newClass,
        });
        // Update the mule's class locally for instant feedback
        const mule = this.currentCharacterMules.find(m => m.id === muleId);
        if (mule) {
          mule.class = newClass;
        }
        this.closeAllDropdowns();
        this.$refs.notificationRef.showNotification('Classe de la mule mise à jour avec succès !');
      } catch (error) {
        console.error('Error updating mule class:', error.message);
        this.$refs.notificationRef.showNotification('Échec de la mise à jour de la classe.');
      }
    },
    
    // Update methods for child component events
    updateCharacterPseudo(characterId, newPseudo) {
      const character = this.charactersNotArchived.find(member => member.id === characterId);
      if (character) {
        character.pseudo = newPseudo;
      }
    },
    
    updateMulePseudo(muleId, newPseudo) {
      Object.keys(this.notArchivedMules).forEach(characterId => {
        const mule = this.notArchivedMules[characterId].find(m => m.id === muleId);
        if (mule) {
          mule.pseudo = newPseudo;
        }
      });
    },
    
    // UI helpers
    filteredMulesByCharacter(characterId) {
      return this.notArchivedMules[characterId] || [];
    },
    
    toggleExpand(memberId) {
      this.currentCharacterId = memberId;
      this.currentCharacterMules = this.filteredMulesByCharacter(memberId);
      this.expandedRows[memberId] = !this.expandedRows[memberId];
    },
    
    toggleClassDropdown(id, type) {
      const key = `${type}-${id}`;
      Object.keys(this.classDropdownVisible).forEach(k => {
        this.classDropdownVisible[k] = false;
      });
      this.classDropdownVisible[key] = !this.classDropdownVisible[key];
    },
    
    closeAllDropdowns() {
      Object.keys(this.classDropdownVisible).forEach(key => {
        this.classDropdownVisible[key] = false;
      });
    },
    
    handleClickOutside(event) {
      const dropdownElements = document.querySelectorAll('.relative.inline-block');
      let clickedInside = false;

      dropdownElements.forEach(dropdown => {
        if (dropdown.contains(event.target)) clickedInside = true;
      });

      if (!clickedInside) this.closeAllDropdowns();
    },
    
    // Modal handlers
    openModal(member) {
      this.selectedMember = member;
      this.showModal = true;
    },
    
    closeModal() {
      this.showModal = false;
    },
    
    openMuleModal(mule) {
      this.selectedMule = mule;
      this.showModalMule = true;
    },
    
    closeMuleModal() {
      this.selectedMule = null;
      this.showModalMule = false;
    },
    
    openUnarchivedCharacterModal(character) {
      this.selectedUnarchivedCharacter = character;
      this.showUnarchivedCharacterModal = true;
    },
    
    closeUnarchivedCharacterModal() {
      this.selectedUnarchivedCharacter = null;
      this.showUnarchivedCharacterModal = false;
    },
    
    // Navigation
    viewWarnings(characterId, member) {
      this.$router.push(`/warnings/${characterId}/${member}`);
    },
    
    // Modal management for character/mule import
    openImportMemberModal() {
      this.showModalMember = true;
    },
    
    closeImportMemberModal() {
      this.showModalMember = false;
    },
    
    // Event handlers
    async addCharacterToTable() {
      const response = await axios.get(`${API_URL}/characters/`);
      this.charactersData = response.data;
      this.notArchivedCharacters = this.charactersData.filter(character => !character.isArchived);

      // Update mules from charactersData
      this.notArchivedMules = {};
      this.notArchivedCharacters.forEach(char => {
        this.notArchivedMules[char.id] = (char.mules || []).filter(m => !m.isArchived);
      });
      this.$refs.notificationRef.showNotification('Le personnage a bien été ajouté');
      
      // Close the modal after adding a character
      this.closeImportMemberModal();
    },
    
    async addMuleToTable() {
      await this.fetchAllMules();
      this.$refs.notificationRef.showNotification('Les mules ont été mises à jour');
      
      // Close the modal after adding a mule
      this.closeImportMemberModal();
    },
  },
  async mounted() {
    try {
      await this.fetchCharacters();
      await this.fetchAllMules();
      await this.fetchWarningCounts();
      document.addEventListener('click', this.handleClickOutside);
    } catch (error) {
      console.error('Error during component initialization:', error);
    }
  },
  
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
};
</script>

<style scoped>
table {
  border-spacing: 0;
}

td {
  height: auto;
  vertical-align: top;
}
</style>
