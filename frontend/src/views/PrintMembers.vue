<template>
  <div class="min-h-screen">
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

    <!-- Notes Modal -->
    <NotesModal
      :show="showNotesModal"
      :initial-notes="notesModalContent"
      @close="showNotesModal = false"
      @save="saveMemberNote"
    />

    <!-- Main Container -->
    <div ref="mainContainer" class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Gestion des Membres</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
      </div>

      <!-- Search Header -->
      <SearchHeader
        :active-tab="activeTab"
        :current-search-query="currentSearchQuery"
        @update:active-tab="activeTab = $event"
        @update:current-search-query="currentSearchQuery = $event"
        @show-modal-member="showModalMember = true"
      />

      <div class="members-layout" :class="{ 'members-layout--full': activeTab !== 'active' }">
        <!-- Sidebar de filtres (membres actifs uniquement) -->
        <aside v-if="activeTab === 'active'" class="filters-sidebar glass-card rounded-2xl p-5">
          <div class="fs-title">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
            Filtrer le roster
          </div>

          <div class="fs-group">
            <label class="fs-group-title">Rang</label>
            <select v-model="filterRank" class="fs-select">
              <option value="">Tous</option>
              <option v-for="r in availableRanks" :key="r" :value="r">{{ r }}</option>
            </select>
          </div>

          <div class="fs-group">
            <label class="fs-group-title">Recruteur</label>
            <select v-model="filterRecruiter" class="fs-select">
              <option value="">Tous</option>
              <option v-for="r in availableRecruiters" :key="r" :value="r">{{ r }}</option>
            </select>
          </div>

          <div class="fs-group">
            <label class="fs-group-title">Mules (min / max)</label>
            <div class="flex gap-2">
              <input v-model.number="filterMulesMin" type="number" min="0" placeholder="min" class="fs-input" />
              <input v-model.number="filterMulesMax" type="number" min="0" placeholder="max" class="fs-input" />
            </div>
          </div>

          <div class="fs-group">
            <label class="fs-group-title">Avertissements (min / max)</label>
            <div class="flex gap-2">
              <input v-model.number="filterWarningsMin" type="number" min="0" placeholder="min" class="fs-input" />
              <input v-model.number="filterWarningsMax" type="number" min="0" placeholder="max" class="fs-input" />
            </div>
          </div>

          <div class="fs-group">
            <label class="fs-group-title">Plage de dates</label>
            <ThemedDatePicker
              v-model="filterDateRange"
              range
              :format="'dd/MM/yyyy'"
              :placeholder="'Plage de dates'"
            />
          </div>

          <div class="fs-group">
            <label class="fs-group-title">Trier par</label>
            <div class="flex gap-2">
              <select v-model="sortColumn" class="fs-select flex-1">
                <option :value="null">Aucun tri</option>
                <option value="pseudo">Pseudo</option>
                <option value="rank">Rang</option>
                <option value="recruiter">Recruteur</option>
                <option value="recruited_at">Arrivée</option>
                <option value="mules">Mules</option>
                <option value="warnings">Avertissements</option>
              </select>
              <button
                @click="toggleSortOrder"
                :disabled="!sortColumn"
                class="px-3 rounded-lg border border-theme-border bg-theme-bg text-theme-text hover:border-theme-primary hover:text-theme-primary transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                :title="sortOrder === 'asc' ? 'Ordre croissant' : 'Ordre décroissant'"
              >
                {{ sortOrder === 'asc' ? '↑' : '↓' }}
              </button>
            </div>
          </div>

          <!-- Chips de filtres actifs -->
          <div v-if="activeFilterChips.length" class="flex flex-wrap gap-1.5 pt-3 mt-2 border-t border-theme-border">
            <button
              v-for="(chip, idx) in activeFilterChips"
              :key="idx"
              @click="chip.clear()"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-theme-accent/10 text-theme-accent-hover border border-theme-accent/30 hover:bg-theme-error/10 hover:text-theme-error hover:border-theme-error/30 transition-all"
            >
              {{ chip.label }}
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
          </div>

          <button @click="resetCardFilters" class="w-full mt-3 px-4 py-2 rounded-lg bg-theme-bg-muted hover:bg-theme-error hover:text-white text-theme-text-muted font-medium text-sm transition-all">
            Réinitialiser les filtres
          </button>
        </aside>

        <!-- Colonne principale -->
        <div class="members-main">
          <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <ViewToggle
              :view-mode="viewMode"
              @update:view-mode="(newMode) => { viewMode = newMode; }"
            />
            <p v-if="activeTab === 'active'" class="text-theme-text-muted text-sm">{{ filteredMembers.length }} membre(s) trouvé(s)</p>
          </div>

          <!-- Main Content -->
          <div v-if="activeTab === 'active'">
            <!-- Fiche perso -->
            <div v-if="viewMode === 'cards'">
              <MembersTable
                :filtered-members="paginatedMembers"
                :classes="classes"
                :filtered-mules-by-character="filteredMulesByCharacter"
                :character-warning-counts="characterWarningCounts"
                :editing-pseudo="editingPseudo"
                :edit-pseudo="editPseudo"
                :total-active-members="charactersNotArchived.length"
                @open-modal="openModal"
                @view-warnings="viewWarnings"
                @update-character-class="updateCharacterClass"
                @update-mule-class="updateMuleClass"
                @start-editing-pseudo="startEditingPseudo"
                @save-pseudo="savePseudo"
                @open-mule-modal="openMuleModal"
                @open-add-mule-modal="openAddMuleModal"
                @open-notes-modal="openNotesModal"
                @save-note="saveMemberNote"
                @refresh-data="refreshMembersAndMules"
                @update-recruitment="handleRecruitmentUpdate"
              />
            </div>

            <!-- Liste -->
            <div v-else>
              <MembersTableList
                :filtered-members="paginatedMembers"
                :classes="classes"
                :filtered-mules-by-character="filteredMulesByCharacter"
                :character-warning-counts="characterWarningCounts"
                :editing-pseudo="editingPseudo"
                :edit-pseudo="editPseudo"
                :total-active-members="charactersNotArchived.length"
                @open-modal="openModal"
                @view-warnings="viewWarnings"
                @update-character-class="updateCharacterClass"
                @update-mule-class="updateMuleClass"
                @start-editing-pseudo="startEditingPseudo"
                @save-pseudo="savePseudo"
                @open-mule-modal="openMuleModal"
                @open-add-mule-modal="openAddMuleModal"
                @open-notes-modal="openNotesModal"
                @update-recruitment="handleRecruitmentUpdate"
                @refresh-data="refreshMembersAndMules"
              />
            </div>

            <Pagination :page="currentPage" :total-pages="totalPages" @update:page="currentPage = $event" />
          </div>

          <!-- Archived Characters -->
          <div v-else>
            <!-- Fiche perso -->
            <ArchivedMembersTable
              v-if="viewMode === 'cards'"
              :filtered-archived-members="filteredArchivedMembers"
              :classes="classes"
              @open-unarchived-character-modal="openUnarchivedCharacterModal"
            />

            <!-- Liste -->
            <ArchivedMembersTableList
              v-else
              :filtered-archived-members="filteredArchivedMembers"
              :classes="classes"
              @open-unarchived-character-modal="openUnarchivedCharacterModal"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll to Top Button -->
    <button
      v-if="showScrollToTop"
      @click="scrollToTop"
      class="fixed bottom-8 right-8 z-[9999] w-16 h-16 bg-theme-card hover:bg-theme-bg-muted text-theme-primary rounded-full shadow-2xl hover:shadow-theme-primary/25 transition-all duration-500 transform hover:scale-110 focus:outline-none focus:ring-4 focus:ring-theme-primary/30 border-2 border-theme-primary/50 hover:border-theme-primary"
      title="Retour en haut de page"
    >
      <div class="absolute inset-0 bg-theme-primary/20 rounded-full"></div>
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
      :message="`Voulez-vous restaurer le joueur ${selectedUnarchivedCharacter?.pseudo || ''} ?`"
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
import MembersTableList from '@/components/MembersTableList.vue';
import ArchivedMembersTable from '@/components/ArchivedMembersTable.vue';
import ArchivedMembersTableList from '@/components/ArchivedMembersTableList.vue';
import ArchiveModal from '@/components/ArchiveModal.vue';
import ViewToggle from '@/components/ViewToggle.vue';
import NotesModal from '@/components/NotesModal.vue';
import ThemedDatePicker from '@/components/ThemedDatePicker.vue';
import Pagination from '@/components/Pagination.vue';
import { computed } from 'vue';
import { useThemeStore } from '@/stores/themeStore';

const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });

const API_URL = import.meta.env.VITE_API_URL;

export default {
  components: {
    ImportMember,
    Notification,
    SearchHeader,
    MembersTable,
    MembersTableList,
    ArchivedMembersTable,
    ArchivedMembersTableList,
    ArchiveModal,
    ViewToggle,
    NotesModal,
    ThemedDatePicker,
    Pagination,
  },
  setup() {
    const themeStore = useThemeStore();
    const isDarkTheme = computed(() => themeStore.currentTheme === 'dark');
    return { isDarkTheme };
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
      showUnarchivedCharacterModal: false,
      currentCharacter: null,
      selectedCharacterForMule: null, // New state for pre-selected character when adding mule
      showScrollToTop: false, // New state for scroll-to-top button visibility

      activeTab: 'active',
      archivedSearchQuery: '',
      viewMode: (localStorage.getItem('erebor-default-member-view') === 'list') ? 'list' : 'cards', // Set from localStorage before render
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
      showNotesModal: false,
      selectedMemberForNotes: null,
      notesModalContent: '',
      // Filtres avancés
      filterPseudo: '',
      filterRecruiter: '',
      filterRank: '',
      filterDateRange: [], // Tableau de deux objets Date ou []
      filterMulesMin: null,
      filterMulesMax: null,
      filterWarningsMin: null,
      filterWarningsMax: null,
      // Tri avancé (colonne et sens)
      sortColumn: null,
      sortOrder: 'asc',
      // Pagination (vue Fiche perso et Liste)
      currentPage: 1,
      pageSize: 24,
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
      let result = this.charactersNotArchived
        .filter(member => {
          // Filtres avancés
          if (this.filterPseudo && !member.pseudo.toLowerCase().includes(this.filterPseudo.toLowerCase())) return false;
          if (this.filterRecruiter && (!member.recruiter || member.recruiter.pseudo !== this.filterRecruiter)) return false;
          if (this.filterRank && (!member.rank || member.rank.name !== this.filterRank)) return false;
          if (this.filterDateRange && Array.isArray(this.filterDateRange) && this.filterDateRange.length === 2) {
            const [startDate, endDate] = this.filterDateRange;
            // Utilise createdAt (format 'YYYY-MM-DD')
            const createdAt = member.createdAt ? new Date(member.createdAt) : null;
            if (startDate instanceof Date && !isNaN(startDate) && createdAt && createdAt < startDate) return false;
            if (endDate instanceof Date && !isNaN(endDate) && createdAt && createdAt > endDate) return false;
          }
          if (this.filterMulesMin !== null && this.filterMulesMin !== undefined) {
            if (this.filteredMulesByCharacter(member.id).length < this.filterMulesMin) return false;
          }
          if (this.filterMulesMax !== null && this.filterMulesMax !== undefined) {
            if (this.filteredMulesByCharacter(member.id).length > this.filterMulesMax) return false;
          }
          if (this.filterWarningsMin !== null && this.filterWarningsMin !== undefined) {
            if ((this.characterWarningCounts[member.id] || 0) < this.filterWarningsMin) return false;
          }
          if (this.filterWarningsMax !== null && this.filterWarningsMax !== undefined) {
            if ((this.characterWarningCounts[member.id] || 0) > this.filterWarningsMax) return false;
          }
          // Recherche globale (ancienne logique)
          const normalize = str =>
            str
              .normalize('NFD')
              .replace(/\u0300-\u036f/g, '')
              .toLowerCase();
          const memberPseudoMatch = normalize(member.pseudo).includes(query);
          const recruiterPseudoMatch = normalize(member.recruiter?.pseudo || '').includes(query);
          const rankMatch = normalize(member.rank?.name || '').includes(query);
          const mulesMatch = this.filteredMulesByCharacter(member.id).some(mule =>
            normalize(mule.pseudo).includes(query)
          );
          return memberPseudoMatch || recruiterPseudoMatch || rankMatch || mulesMatch;
        });

      // Tri
      if (this.sortColumn) {
        const getValue = (member, col) => {
          switch (col) {
            case 'pseudo':
              return member.pseudo?.toLowerCase() || '';
            case 'rank':
              return member.rank?.name?.toLowerCase() || '';
            case 'recruiter':
              return member.recruiter?.pseudo?.toLowerCase() || '';
            case 'mules':
              return this.filteredMulesByCharacter(member.id).length;
            case 'warnings':
              return this.characterWarningCounts[member.id] || 0;
            case 'recruited_at':
              return member.createdAt ? new Date(member.createdAt).getTime() : 0;
            default:
              return '';
          }
        };
        result = result.slice().sort((a, b) => {
          const va = getValue(a, this.sortColumn);
          const vb = getValue(b, this.sortColumn);
          if (va < vb) return this.sortOrder === 'asc' ? -1 : 1;
          if (va > vb) return this.sortOrder === 'asc' ? 1 : -1;
          return 0;
        });
      }
      return result.map(member => ({ member, id: member.id }));
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
    activeFilterChips() {
      const chips = [];
      if (this.filterPseudo) chips.push({ label: `Pseudo: ${this.filterPseudo}`, clear: () => (this.filterPseudo = '') });
      if (this.filterRecruiter) chips.push({ label: `Recruteur: ${this.filterRecruiter}`, clear: () => (this.filterRecruiter = '') });
      if (this.filterRank) chips.push({ label: `Rang: ${this.filterRank}`, clear: () => (this.filterRank = '') });
      if (this.filterDateRange && this.filterDateRange.length === 2) {
        chips.push({ label: `Date: ${this.filterDateRange[0]} - ${this.filterDateRange[1]}`, clear: () => (this.filterDateRange = null) });
      }
      if (this.filterMulesMin !== null && this.filterMulesMin !== undefined) chips.push({ label: `Mules ≥ ${this.filterMulesMin}`, clear: () => (this.filterMulesMin = null) });
      if (this.filterMulesMax !== null && this.filterMulesMax !== undefined) chips.push({ label: `Mules ≤ ${this.filterMulesMax}`, clear: () => (this.filterMulesMax = null) });
      if (this.filterWarningsMin !== null && this.filterWarningsMin !== undefined) chips.push({ label: `Avert. ≥ ${this.filterWarningsMin}`, clear: () => (this.filterWarningsMin = null) });
      if (this.filterWarningsMax !== null && this.filterWarningsMax !== undefined) chips.push({ label: `Avert. ≤ ${this.filterWarningsMax}`, clear: () => (this.filterWarningsMax = null) });
      return chips;
    },
    availableRanks() {
      const ranks = new Set(this.charactersNotArchived.map(c => c.rank?.name).filter(Boolean));
      return Array.from(ranks).sort();
    },
    availableRecruiters() {
      const recruiters = new Set(this.charactersNotArchived.map(c => c.recruiter?.pseudo).filter(Boolean));
      return Array.from(recruiters).sort();
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.filteredMembers.length / this.pageSize));
    },
    paginatedMembers() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredMembers.slice(start, start + this.pageSize);
    },
    // Utilisée uniquement pour détecter un changement de recherche/filtre/tri et réinitialiser la pagination
    filterSignature() {
      return JSON.stringify([
        this.activeTab,
        this.viewMode,
        this.searchQuery,
        this.archivedSearchQuery,
        this.filterPseudo,
        this.filterRecruiter,
        this.filterRank,
        this.filterDateRange,
        this.filterMulesMin,
        this.filterMulesMax,
        this.filterWarningsMin,
        this.filterWarningsMax,
        this.sortColumn,
        this.sortOrder,
      ]);
    },
  },
  watch: {
    filterSignature() {
      this.currentPage = 1;
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
        this.$refs.notificationRef.showNotification('Erreur lors de l’archivage.', 'error');
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

    setSort(column) {
      if (this.sortColumn === column) {
        this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
      } else {
        this.sortColumn = column;
        this.sortOrder = 'asc';
      }
    },
    toggleSortOrder() {
      this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
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
        this.$refs.notificationRef.showNotification('Échec de la mise à jour de la classe.', 'error');
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
        this.$refs.notificationRef.showNotification('Failed to update mule class.', 'error');
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
    openNotesModal(member) {
      this.selectedMemberForNotes = member;
      this.notesModalContent = member.notes || '';
      this.showNotesModal = true;
    },
    async saveMemberNote(idOrNote, noteMaybe) {
      // Support both modal and card view: (note) or (id, note)
      let memberId, newNote;
      if (typeof noteMaybe === 'string') {
        memberId = idOrNote;
        newNote = noteMaybe;
      } else {
        memberId = this.selectedMemberForNotes?.id;
        newNote = idOrNote;
      }
      if (!memberId) return;
      try {
        await axios.put(`${API_URL}/characters/${memberId}`, {
          notes: newNote,
        });
        // Update local state
        const char = this.charactersData.find(c => c.id === memberId);
        if (char) char.notes = newNote;
        if (this.selectedMemberForNotes && this.selectedMemberForNotes.id === memberId) {
          this.selectedMemberForNotes.notes = newNote;
        }
        this.showNotesModal = false;
        this.$refs.notificationRef.showNotification('Note mise à jour avec succès !');
      } catch {
        this.$refs.notificationRef.showNotification('Erreur lors de la mise à jour de la note.', 'error');
      }
    },
    async handleRecruitmentUpdate(updatedCharacter) {
      try {
        // Map recruitedAt to createdAt for consistency with the rest of the app
        const characterData = {
          ...updatedCharacter,
          createdAt: updatedCharacter.recruitedAt || updatedCharacter.createdAt,
        };
        
        // Update the character in local state
        const characterIndex = this.charactersData.findIndex(c => c.id === characterData.id);
        if (characterIndex !== -1) {
          // Update recruiter
          if (characterData.recruiter) {
            this.charactersData[characterIndex].recruiter = characterData.recruiter;
          } else {
            this.charactersData[characterIndex].recruiter = null;
          }
          // Update recruitment date
          if (characterData.createdAt) {
            this.charactersData[characterIndex].createdAt = characterData.createdAt;
          }
        }
        
        // Also update in notArchivedCharacters if it exists
        const notArchivedIndex = this.charactersNotArchived.findIndex(c => c.id === characterData.id);
        if (notArchivedIndex !== -1) {
          if (characterData.recruiter) {
            this.charactersNotArchived[notArchivedIndex].recruiter = characterData.recruiter;
          } else {
            this.charactersNotArchived[notArchivedIndex].recruiter = null;
          }
          if (characterData.createdAt) {
            this.charactersNotArchived[notArchivedIndex].createdAt = characterData.createdAt;
          }
        }
        
        this.$refs.notificationRef.showNotification('Informations de recrutement mises à jour avec succès !');
      } catch (error) {
        console.error('Error updating recruitment info:', error);
        this.$refs.notificationRef.showNotification('Erreur lors de la mise à jour.', 'error');
      }
    },
    async refreshMembersAndMules() {
      await this.fetchCharacters();
      await this.fetchAllMules();
      await this.fetchWarningCounts();
      this.$refs.notificationRef.showNotification('Le switch a été effectué avec succès !');
    },
    resetCardFilters() {
      this.filterDateRange = [];
      this.sortColumn = null;
      this.sortOrder = 'asc';
    },
    resetListSort() {
      this.filterDateRange = [];
      this.sortColumn = null;
      this.sortOrder = 'asc';
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
    // Watch for theme changes and re-apply custom colors for smooth transition
    // This watch is now handled by the setup() function
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

/* ---------- Layout sidebar + colonne principale ---------- */
.members-layout {
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 1.5rem;
  align-items: start;
}

.members-layout--full {
  grid-template-columns: 1fr;
}

.filters-sidebar {
  position: sticky;
  top: 1rem;
  display: flex;
  flex-direction: column;
}

.fs-title {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.68rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--accent);
  margin-bottom: 1.1rem;
}

.fs-group {
  margin-bottom: 1.1rem;
}

.fs-group-title {
  display: block;
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.03em;
  margin-bottom: 0.4rem;
}

.fs-select,
.fs-input {
  width: 100%;
  padding: 0.5rem 0.6rem;
  border-radius: 0.5rem;
  border: 1px solid var(--border);
  background-color: var(--bg);
  color: var(--text);
  font-size: 0.82rem;
}

.fs-select:focus,
.fs-input:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(var(--primary-rgb), 0.35);
  border-color: var(--primary);
}

@media (max-width: 900px) {
  .members-layout {
    grid-template-columns: 1fr;
  }
  .filters-sidebar {
    position: static;
  }
}
</style>

