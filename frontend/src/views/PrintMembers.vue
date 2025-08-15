<template>
  <div class="min-h-screen bg-black text-white">
    <!-- Notification -->
    <Notification ref="notificationRef" />
    
    <!-- Import Member Modal -->
    <ImportMember
      :showModalMember="showModalMember"
      :fetchNotArchivedCharacters="notArchivedCharacters"
      :selectedCharacterForMule="selectedCharacterForMule"
      @close="closeModalMember"
      @characterAdded="addCharacterToTable"
      @muleAdded="addMuleToTable"
    />

    <!-- Main Container -->
    <div ref="mainContainer" class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-6xl font-bold text-amber-400 mb-6">Gestion des Membres</h1>
        <div class="w-32 h-1 bg-gradient-to-r from-amber-400 via-yellow-500 to-amber-600 mx-auto rounded-full shadow-lg shadow-amber-500/50"></div>
        <p class="text-gray-400 mt-6 text-lg">Gérez votre communauté de manière élégante</p>
      </div>

      <!-- Search Header -->
      <SearchHeader
        :active-tab="activeTab"
        :current-search-query="currentSearchQuery"
        @update:active-tab="activeTab = $event"
        @update:current-search-query="currentSearchQuery = $event"
        @show-modal-member="showModalMember = true"
      />

      <!-- Main Content -->
      <div v-if="activeTab === 'active'" class="mt-12">
        <MembersTable
          :filtered-members="filteredMembers"
          :classes="classes"
          :filtered-mules-by-character="filteredMulesByCharacter"
          :character-warning-counts="characterWarningCounts"
          :editing-pseudo="editingPseudo"
          :edit-pseudo="editPseudo"
          @open-modal="openModal"
          @view-warnings="viewWarnings"
          @update-character-class="updateCharacterClass"
          @update-mule-class="updateMuleClass"
          @start-editing-pseudo="startEditingPseudo"
          @save-pseudo="savePseudo"
          @open-mule-modal="openMuleModal"
          @open-add-mule-modal="openAddMuleModal"
        />
      </div>

      <!-- Archived Characters -->
      <div v-if="activeTab === 'archived'" class="mt-12">
        <ArchivedMembersTable
          :filtered-archived-members="filteredArchivedMembers"
          :classes="classes"
          @open-unarchived-character-modal="openUnarchivedCharacterModal"
        />
      </div>
    </div>

    <!-- Scroll to Top Button -->
    <button
      v-if="showScrollToTop"
      @click="scrollToTop"
      class="fixed bottom-8 right-8 z-[9999] w-16 h-16 bg-gradient-to-br from-gray-900 to-black hover:from-gray-800 hover:to-gray-900 text-amber-400 rounded-full shadow-2xl hover:shadow-amber-500/25 transition-all duration-500 transform hover:scale-110 focus:outline-none focus:ring-4 focus:ring-amber-500/30 border-2 border-amber-500/50 hover:border-amber-400"
      title="Retour en haut de page"
    >
      <div class="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-transparent rounded-full"></div>
      <svg class="w-7 h-7 mx-auto relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
      </svg>
    </button>
    
    <!-- Modals -->
    <ArchiveModal
      :show="showModal"
      :message="`Voulez-vous archiver le joueur ${selectedMember?.pseudo || ''} ?`"
      confirm-button-text="Archiver"
      @close="closeModal"
      @confirm="archiveCharacter(selectedMember?.id)"
    />
    
    <ArchiveModal
      :show="showModalMule"
      :message="`Voulez-vous archiver le joueur ${selectedMule?.pseudo || ''} ?`"
      confirm-button-text="Archiver"
      @close="closeMuleModal"
      @confirm="archiveMule(selectedMule?.id)"
    />
    
    <ArchiveModal
      :show="showUnarchivedCharacterModal"
      :message="`Voulez-vous restaurer le joueur ${selectedUnarchivedCharacter?.id} ?`"
      confirm-button-text="Restaurer"
      @close="closeUnarchivedCharacterModal"
      @confirm="unarchiveCharacter(selectedUnarchivedCharacter?.id)"
    />
  </div>
</template>

<script>
import axios from 'axios';
import members_bg from '@/assets/members_bg.webp';
import ImportMember from '@/components/ImportMember.vue';
import Notification from '@/components/NotificationCenter.vue';
import SearchHeader from '@/components/SearchHeader.vue';
import MembersTable from '@/components/MembersTable.vue';
import ArchivedMembersTable from '@/components/ArchivedMembersTable.vue';
import ArchiveModal from '@/components/ArchiveModal.vue';

const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });

const API_URL = import.meta.env.VITE_API_URL;

export default {
  components: {
    ImportMember,
    Notification,
    SearchHeader,
    MembersTable,
    ArchivedMembersTable,
    ArchiveModal,
  },
  data() {
    return {
      backgroundImage: members_bg,
      iconFolder: 'src/assets/icon_classe',
      searchQuery: '',
      charactersData: [], // Holds all characters (archived + non-archived),
      notArchivedCharacters: [],
      showModal: false,
      showModalMule: false,
      showModalMember: false,
      selectedMember: null,
      editingPseudo: { type: null, id: null },
      editPseudo: '',
      selectedMule: null,
      showNotification: false,
      notArchivedMules: {},
      showMulesModal: false,
      showUnarchivedCharacterModal: false,
      currentCharacter: null,
      selectedCharacterForMule: null, // New state for pre-selected character when adding mule
      showScrollToTop: false, // New state for scroll-to-top button visibility

      activeTab: 'active',
      archivedSearchQuery: '',
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
      characterWarningCounts: {},
      selectedUnarchivedCharacter: null,
    };
  },
  computed: {
    // ✅ Use computed properties instead of redundant API calls
    charactersNotArchived() {
      const result = this.charactersData.filter(character => !character.isArchived);
      return result;
    },
    archivedCharacters() {
      return this.charactersData.filter(character => character.isArchived);
    },
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
    filteredMembers() {
      const query = this.searchQuery.toLowerCase();
      const result = this.charactersNotArchived
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
      
      return result;
    },
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
    // ✅ Fetch all characters once and store them
    async fetchCharacters() {
      try {
        const response = await axios.get(`${API_URL}/characters/`);
        this.charactersData = response.data;
        this.notArchivedCharacters = this.charactersData.filter(character => !character.isArchived);
      } catch (error) {
        console.error('Error fetching characters:', error.response?.data || error.message);
      }
    },

    // Fetch warning counts for each character
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

    // ✅ Archive a character and update state without re-fetching
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

    // ✅ Unarchive a character and update state without re-fetching
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

    // ✅ Add character and re-fetch all characters
    // ✅ Add character and re-fetch all characters
    async addCharacterToTable() {
      const response = await axios.get(`${API_URL}/characters/`);
      this.charactersData = response.data;
      this.notArchivedCharacters = this.charactersData.filter(character => !character.isArchived);

      // 🔁 mettre à jour les mules depuis charactersData
      this.notArchivedMules = {};
      this.notArchivedCharacters.forEach(char => {
        this.notArchivedMules[char.id] = (char.mules || []).filter(m => !m.isArchived);
      });
      this.$refs.notificationRef.showNotification('Le personnage a bien été ajouté');
    },
    startEditingPseudo(id, currentPseudo, type) {
      if (type === 'character') {
        this.editingPseudo = { type: 'character', id };
      } else if (type === 'mule') {
        this.editingPseudo = { type: 'mule', id };
      }
      this.editPseudo = currentPseudo || ''; // Set to the current pseudo or empty if undefined
    },
    async savePseudo(entity, type, newPseudo) {
      if (!newPseudo || newPseudo.trim() === '') {
        console.log('Le pseudo ne peut pas être vide.');
        return;
      }

      try {
        if (type === 'character') {
          await axios.put(`${API_URL}/characters/${entity.id}/update-pseudo`, {
            pseudo: newPseudo,
          });
          entity.pseudo = newPseudo; // Update locally
        } else if (type === 'mule') {
          await axios.put(`${API_URL}/mules/${entity.id}/update-pseudo`, {
            pseudo: newPseudo,
          });
          entity.pseudo = newPseudo; // Update locally
        }
        this.editingPseudo = { type: null, id: null };
        this.editPseudo = ''; // Clear the temporary pseudo
        this.$refs.notificationRef.showNotification('Pseudo mis à jour avec succès !');
      } catch (error) {
        console.error(
          'Erreur lors de la mise à jour du pseudo:',
          error.response?.data || error.message
        );
        console.log('Une erreur est survenue lors de la mise à jour du pseudo.');
      }
    },

    async addMuleToTable() {
      await this.fetchAllMules();
      this.$refs.notificationRef.showNotification('Les mules ont été mises à jour');
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
        // ✅ Update the state **without refreshing**
        Object.keys(this.notArchivedMules).forEach(characterId => {
          this.notArchivedMules[characterId] = this.notArchivedMules[characterId].filter(
            mule => mule.id !== muleId
          );
        });

        this.closeMuleModal();

        // ✅ Show notification
        const mulePseudo = this.selectedMule.pseudo || 'La mule';
        this.$refs.notificationRef.showNotification(`${mulePseudo} a bien été archivée.`);

        // Clear selection
        this.selectedMule = null;
      } catch (error) {
        console.error('Error archiving mule:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification('Erreur lors de l’archivage.');
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
    openAddMuleModal(member) {
      this.selectedCharacterForMule = member;
      this.showModalMember = true;
    },
    closeModalMember() {
      this.showModalMember = false;
      this.selectedCharacterForMule = null;
    },

    // toggleExpand method removed - expansion is now handled by the MembersTable component

    filteredMulesByCharacter(characterId) {
      if (!this.notArchivedMules || !characterId) {
        return [];
      }
      return this.notArchivedMules[characterId] || [];
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
        // Find the mule in all mules data
        Object.keys(this.notArchivedMules).forEach(characterId => {
          const mule = this.notArchivedMules[characterId].find(m => m.id === muleId);
          if (mule) {
            mule.class = newClass;
          }
        });
        this.$refs.notificationRef.showNotification('Mule class updated successfully!');
      } catch (error) {
        console.error('Error updating mule class:', error.message);
        this.$refs.notificationRef.showNotification('Failed to update mule class.');
      }
    },

    viewWarnings(characterId, member) {
      this.$router.push(`/warnings/${characterId}/${member}`);
    },

    // Scroll to top logic
    scrollToTop() {
      try {
        // Get the scrollable container (RouterView)
        const scrollContainer = document.querySelector('.h-\\[calc\\(100vh-128px\\)\\]');
        if (scrollContainer) {
          scrollContainer.scrollTo({
            top: 0,
            behavior: 'smooth',
          });
        } else {
          // Fallback to window scroll
          window.scrollTo({
            top: 0,
            behavior: 'smooth',
          });
        }
        
        // Force update of scroll state after a short delay
        setTimeout(() => {
          this.handleScroll();
        }, 100);
      } catch (error) {
        console.error('Error scrolling to top:', error);
      }
    },
    // Watch for scroll events to show/hide the button
    handleScroll() {
      // Get the scrollable container (RouterView)
      const scrollContainer = document.querySelector('.h-\\[calc\\(100vh-128px\\)\\]');
      if (scrollContainer) {
        const scrollTop = scrollContainer.scrollTop;
        this.showScrollToTop = scrollTop > 300;
      } else {
        // Fallback to window scroll
        const scrollY = window.scrollY || window.pageYOffset || 0;
        this.showScrollToTop = scrollY > 300;
      }
    },
  },
  async mounted() {
    try {
      await this.fetchCharacters(); // Fetch all characters once
      await this.fetchAllMules(); // Fetch mules once
      await this.fetchWarningCounts();
      
      // Add event listener for scroll on the RouterView container
      const scrollContainer = document.querySelector('.h-\\[calc\\(100vh-128px\\)\\]');
      if (scrollContainer) {
        scrollContainer.addEventListener('scroll', this.handleScroll);
      } else {
        // Fallback to window scroll
        window.addEventListener('scroll', this.handleScroll);
        document.addEventListener('scroll', this.handleScroll);
      }
      
      // Force a scroll test after a delay
      setTimeout(() => {
        this.handleScroll();
      }, 1000);
      
    } catch (error) {
      console.error('Error during component initialization:', error);
    }
  },
  beforeUnmount() {
    // Remove event listener for scroll
    const scrollContainer = document.querySelector('.h-\\[calc\\(100vh-128px\\)\\]');
    if (scrollContainer) {
      scrollContainer.removeEventListener('scroll', this.handleScroll);
    } else {
      // Remove fallback listeners
      window.removeEventListener('scroll', this.handleScroll);
      document.removeEventListener('scroll', this.handleScroll);
    }
  },
};
</script>

<style scoped>
/* Table styling */
table {
  border-spacing: 0;
}

/*th,
td {
  border-bottom: 2px solid #b07d46;
}*/

/* Optional row highlight
tbody tr:hover {
  background-color: #f3d9b1;
} */

/* Add this to your CSS file if not using TailwindCSS */
.group:hover .group-hover\:block {
  display: block;
}

.group-hover\:block {
  display: none;
}

td {
  height: auto;
  vertical-align: top;
}
</style>
