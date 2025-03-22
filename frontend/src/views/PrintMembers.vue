<template>
  <div
    class="w-full flex flex-col items-center justify-start bg-cover bg-center py-8"
    :style="{ backgroundImage: `url(${backgroundImage})` }"
  >
    <!-- Notification -->
    <Notification ref="notificationRef" />
    <ImportMember
      :showModalMember="showModalMember"
      :fetchNotArchivedCharacters="notArchivedCharacters"
      @close="showModalMember = false"
      @characterAdded="addCharacterToTable"
      @muleAdded="addMuleToTable"
    />
    <!-- Space Between Tabs and Content -->
    <div class="my-8"></div>
    <!-- Header Section -->
    <div
      class="flex flex-col w-11/12 max-w-7xl bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-4 mb-6"
    >
      <!-- Tabs (Top of the Header) -->
      <div class="flex justify-center mb-4">
        <div class="flex space-x-4">
          <button
            @click="activeTab = 'active'"
            :class="{
              'bg-[#b02e2e] text-[#f3d9b1] shadow-md': activeTab === 'active',
              'bg-[#f3d9b1] text-[#b02e2e]': activeTab !== 'active',
            }"
            class="px-6 py-2 rounded-full font-bold transition-all duration-300"
          >
            Membres actifs
          </button>
          <button
            @click="activeTab = 'archived'"
            :class="{
              'bg-[#b02e2e] text-[#f3d9b1] shadow-md': activeTab === 'archived',
              'bg-[#f3d9b1] text-[#b02e2e]': activeTab !== 'archived',
            }"
            class="px-6 py-2 rounded-full font-bold transition-all duration-300"
          >
            Membres archivés
          </button>
        </div>
      </div>

      <!-- Bottom Section: Search Bar (Left) and Button (Right) -->
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <!-- Search Bar -->
        <input
          v-model="currentSearchQuery"
          :placeholder="
            activeTab === 'active'
              ? 'Rechercher par le nom, le rang, le recruteur..'
              : 'Search archived characters...'
          "
          class="w-full md:w-1/2 border-2 border-[#b07d46] bg-[#fffaf0] rounded-full py-2 px-6 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1] mb-4 md:mb-0"
        />

        <!-- Add Character Button -->
        <button
          @click="showModalMember = true"
          class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-6 rounded-full hover:bg-[#942828] transition-all duration-300"
        >
          Ajouter un personnage
        </button>
      </div>
    </div>

    <!-- Main Content -->
    <div v-if="activeTab === 'active'" class="w-11/12 max-w-7xl">
      <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-8 mb-6">
        <h2 class="text-3xl font-bold text-[#b02e2e] mb-4">Membres actifs</h2>
        <!-- Characters Table -->
        <div class="overflow-y-auto max-h-96">
          <table class="w-full text-center border-collapse bg-white rounded-lg shadow-lg">
            <thead class="sticky top-0 bg-[#b02e2e] z-10">
              <tr class="text-[#f3d9b1] text-lg">
                <th class="p-4">Classe</th>
                <th class="p-4">Nom</th>
                <th class="p-4">Recruteur</th>
                <th class="p-4">Rang</th>
                <th class="p-4">Mules</th>
                <th class="p-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <template
                v-for="({ member, id }, index) in filteredMembers"
                :key="member.id || index"
              >
                <tr class="transition-all group relative hover:bg-[#f3d9b1]/30">
                  <td class="p-4 relative">
                    <div class="relative inline-block">
                      <button @click="toggleClassDropdown(member.id, 'character')">
                        <img
                          :src="classes[member.class]"
                          alt="Character Class"
                          class="w-10 h-10 cursor-pointer"
                        />
                      </button>
                      <div
                        v-if="classDropdownVisible[`character-${member.id}`]"
                        class="absolute top-12 left-0 z-10 bg-[#fff5e6] border border-[#b07d46] rounded-lg shadow-lg p-2 w-80"
                      >
                        <div class="grid grid-cols-4 gap-2 max-h-40 overflow-y-auto">
                          <div
                            v-for="(icon, className) in classes"
                            :key="className"
                            class="flex flex-col items-center gap-1 cursor-pointer hover:bg-[#f3d9b1] p-2 rounded-lg"
                            @click="updateCharacterClass(member.id, className)"
                          >
                            <img :src="icon" :alt="className" class="w-12 h-12" />
                            <span class="text-sm text-[#b07d46]">{{ className }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                  <!-- Pseudo -->
                  <td class="p-4 text-[#b07d46] font-bold relative">
                    <div
                      v-if="editingPseudo.type === 'character' && editingPseudo.id === member.id"
                      @click.stop
                    >
                      <input
                        v-model="editPseudo"
                        class="border-2 border-[#b07d46] rounded-lg p-2 w-full focus:ring-2 focus:ring-[#f3d9b1]"
                        @blur="savePseudo(member, 'character')"
                        @keydown.enter.prevent="savePseudo(member, 'character')"
                        ref="editInput"
                      />
                    </div>
                    <div
                      v-else
                      class="flex items-center gap-2 cursor-pointer hover:text-[#942828] hover:underline"
                      @click="startEditingPseudo(member.id, member.pseudo, 'character')"
                    >
                      {{ member.pseudo || 'Unknown' }}
                      <span class="text-sm text-[#b07d46]">
                        <i class="fas fa-pencil-alt"></i>
                      </span>
                    </div>
                  </td>
                  <td class="p-4 text-[#b07d46]">
                    {{ member?.recruiter?.pseudo || 'No Recruiter' }}
                  </td>
                  <td class="p-4 text-[#b07d46]">{{ member?.rank?.name || 'No Rank' }}</td>
                  <td class="p-4">
                    <button
                      v-if="filteredMulesByCharacter(id).length > 0"
                      @click="toggleExpand(id)"
                      class="text-[#b02e2e] font-bold underline hover:text-[#942828] transition-all duration-300"
                    >
                      {{ filteredMulesByCharacter(id).length }} mules
                    </button>
                  </td>
                  <td class="p-4">
                    <button
                      @click="openModal(member)"
                      class="text-[#b02e2e] hover:text-[#942828] transition-all duration-300"
                    >
                      Archiver
                    </button>
                  </td>
                </tr>
                <!-- Expanded Row -->
                <tr v-if="expandedRows[id]" class="bg-[#ffecd2]">
                  <td colspan="5" class="p-4">
                    <div class="w-10/12 mx-auto">
                      <div v-if="filteredMulesByCharacter(id).length > 0">
                        <table class="w-full text-center border-collapse">
                          <thead>
                            <tr class="bg-[#b07d46] text-[#fff5e6]">
                              <th class="p-2">Classe</th>
                              <th class="p-2">Pseudo</th>
                              <th class="p-2">Pseudo Ankama</th>
                              <th class="p-2">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr
                              v-for="(mule, muleIndex) in filteredMulesByCharacter(id)"
                              :key="mule.id || muleIndex"
                              :class="[muleIndex % 2 === 0 ? 'bg-[#fff5e6]' : 'bg-[#fde1c8]']"
                              class="hover:bg-[#f3d9b1]"
                            >
                              <td class="p-2 relative">
                                <div class="relative inline-block">
                                  <button @click="toggleClassDropdown(mule.id, 'mule')">
                                    <img
                                      :src="`${iconFolder}/${mule.class}.avif`"
                                      alt="Mule Class"
                                      class="w-8 h-8 cursor-pointer mx-auto"
                                    />
                                  </button>
                                  <div
                                    v-if="classDropdownVisible[`mule-${mule.id}`]"
                                    class="absolute top-12 left-0 z-10 bg-[#fff5e6] border border-[#b07d46] rounded-lg shadow-lg p-2 w-80"
                                  >
                                    <div class="grid grid-cols-4 gap-2 max-h-40 overflow-y-auto">
                                      <div
                                        v-for="(icon, className) in classes"
                                        :key="className"
                                        class="flex flex-col items-center gap-1 cursor-pointer hover:bg-[#f3d9b1] p-2 rounded-lg"
                                        @click="updateMuleClass(mule.id, className)"
                                      >
                                        <img :src="icon" :alt="className" class="w-12 h-12" />
                                        <span class="text-sm text-[#b07d46]">{{ className }}</span>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </td>
                              <td class="p-2 text-[#b07d46] font-bold relative">
                                <div
                                  v-if="
                                    editingPseudo.type === 'mule' && editingPseudo.id === mule.id
                                  "
                                  @click.stop
                                >
                                  <input
                                    v-model="editPseudo"
                                    class="border-2 border-[#b07d46] rounded-lg p-2 w-full focus:ring-2 focus:ring-[#f3d9b1]"
                                    @blur="savePseudo(mule, 'mule')"
                                    @keydown.enter.prevent="savePseudo(mule, 'mule')"
                                    ref="editInput"
                                  />
                                </div>
                                <div
                                  v-else
                                  class="flex items-center gap-2 cursor-pointer hover:text-[#942828] hover:underline"
                                  @click="startEditingPseudo(mule.id, mule.pseudo, 'mule')"
                                >
                                  {{ mule.pseudo }}
                                  <span class="text-sm text-[#b07d46]">
                                    <i class="fas fa-pencil-alt"></i>
                                  </span>
                                </div>
                              </td>
                              <td class="p-2 text-[#b07d46]">{{ mule.ankamaPseudo }}</td>
                              <td class="p-4 text-[#b07d46]">
                                <div
                                  class="cursor-pointer"
                                  title="Archiver"
                                  @click="openMuleModal(mule)"
                                >
                                  &#x22EE;
                                </div>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      <div v-else>
                        <p class="text-[#b07d46] italic">Pas de mules disponibles.</p>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Archived Characters -->
    <div v-if="activeTab === 'archived'" class="w-11/12 max-w-7xl">
      <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-8 mb-6">
        <h2 class="text-3xl font-bold text-[#b02e2e] mb-4">Archived Characters</h2>

        <!-- Archived Characters Table -->
        <div class="overflow-y-auto max-h-96">
          <table class="w-full text-center border-collapse bg-white rounded-lg shadow-lg">
            <thead class="sticky top-0 bg-[#b02e2e] z-10">
              <tr class="text-[#f3d9b1] text-lg">
                <th class="p-4">Classe</th>
                <th class="p-4">Nom</th>
                <th class="p-4">Recruteur</th>
                <th class="p-4">Rang</th>
                <th class="p-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <template
                v-for="({ member, id }, index) in filteredArchivedMembers"
                :key="member.id || index"
              >
                <tr class="transition-all group relative hover:bg-[#f3d9b1]/30">
                  <td class="p-4">
                    <img
                      :src="classes[member.class]"
                      alt="Character Class"
                      class="w-12 h-12 rounded-full mx-auto"
                    />
                  </td>
                  <td class="p-4 text-[#b07d46] font-bold">
                    {{ member.pseudo || 'Unknown' }}
                  </td>
                  <td class="p-4 text-[#b07d46]">
                    {{ member?.recruiter?.pseudo || 'No Recruiter' }}
                  </td>
                  <td class="p-4 text-[#b07d46]">{{ member?.rank?.name || 'No Rank' }}</td>
                  <td class="p-4">
                    <button
                      @click="openUnarchivedCharacterModal(member)"
                      class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-6 rounded-full hover:bg-[#942828] transition-all duration-300"
                    >
                      Unarchive
                    </button>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- Modal archive character -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 w-1/3 relative">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="closeModal"
        >
          &times;
        </button>
        <p class="text-lg text-[#b07d46] mb-6">
          Voulez-vous archiver le joueur <strong>{{ selectedMember.pseudo }}</strong> ?
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="closeModal"
            class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
          >
            Annuler
          </button>
          <button
            @click="archiveCharacter(selectedMember.id)"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
          >
            Archiver
          </button>
        </div>
      </div>
    </div>
    <!-- Modal archive mule -->
    <div
      v-if="showModalMule"
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="closeMuleModal"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 w-1/3 relative">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="closeMuleModal"
        >
          &times;
        </button>
        <p class="text-lg text-[#b07d46] mb-6">
          Voulez-vous archiver le joueur <strong>{{ selectedMule.pseudo }}</strong> ?
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="closeMuleModal"
            class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
          >
            Annuler
          </button>
          <button
            @click="archiveMule(selectedMule.id)"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
          >
            Archiver
          </button>
        </div>
      </div>
    </div>
    <!-- Modal unarchived character -->
    <div
      v-if="showUnarchivedCharacterModal"
      class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50"
      @click.self="closeUnarchivedCharacterModal"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg p-6 w-1/3 relative">
        <!-- Close Button -->
        <button
          class="absolute top-3 right-3 text-[#b02e2e] hover:text-[#942828] font-bold text-lg"
          @click="closeUnarchivedCharacterModal"
        >
          &times;
        </button>
        <p class="text-lg text-[#b07d46] mb-6">
          Voulez-vous archiver le joueur <strong>{{ selectedUnarchivedCharacter.pseudo }}</strong> ?
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="closeUnarchivedCharacterModal"
            class="bg-[#b07d46] text-[#fff5e6] font-bold py-2 px-4 rounded-lg hover:bg-[#9c682e]"
          >
            Annuler
          </button>
          <button
            @click="unarchiveCharacter(selectedUnarchivedCharacter.id)"
            class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
          >
            Archiver
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import members_bg from '@/assets/members_bg.webp';
import ImportMember from '@/components/ImportMember.vue';
import Notification from '@/components/NotificationCenter.vue';
import { reactive } from 'vue';
const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });

const API_URL = import.meta.env.VITE_API_URL;

export default {
  components: {
    ImportMember,
    Notification,
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
      currentCharacterId: null,
      currentCharacterMules: [],
      classDropdownVisible: reactive({}),
      expandedRows: reactive({}),
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
    };
  },
  computed: {
    // ✅ Use computed properties instead of redundant API calls
    charactersNotArchived() {
      return this.charactersData.filter(character => !character.isArchived);
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
      console.log(API_URL);
      try {
        const response = await axios.get(`${API_URL}/characters/`);
        this.charactersData = response.data;
        this.notArchivedCharacters = response.data.filter(character => !character.isArchived);
      } catch (error) {
        console.error('Error fetching characters:', error.response?.data || error.message);
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
    async addCharacterToTable() {
      await this.fetchCharacters();
      this.$refs.notificationRef.showNotification('Le personnage a bien été ajouté');
    },
    startEditingPseudo(id, currentPseudo, type) {
      if (type === 'character') {
        this.editingPseudo = { type: 'character', id };
      } else if (type === 'mule') {
        this.editingPseudo = { type: 'mule', id };
      }
      this.editPseudo = currentPseudo || ''; // Set to the current pseudo or empty if undefined
      this.$nextTick(() => {
        const input = this.$refs.editInput;
        if (input) input.focus(); // Focus the input field
      });
    },
    async savePseudo(entity, type) {
      console.log('entity', entity);
      console.log('type', type);
      console.log('editPseudo', this.editPseudo);
      if (this.editPseudo.trim() === '') {
        console.log('Le pseudo ne peut pas être vide.');
        return;
      }

      try {
        if (type === 'character') {
          await axios.put(`${API_URL}/characters/${entity.id}/update-pseudo`, {
            pseudo: this.editPseudo,
          });
          entity.pseudo = this.editPseudo; // Update locally
        } else if (type === 'mule') {
          await axios.put(`${API_URL}/mules/${entity.id}/update-pseudo`, {
            pseudo: this.editPseudo,
          });
          entity.pseudo = this.editPseudo; // Update locally
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
      this.$refs.notificationRef.showNotification('La mule a bien été ajoutée');
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

    toggleExpand(memberId) {
      this.currentCharacterId = memberId;
      this.currentCharacterMules = this.filteredMulesByCharacter(memberId);
      this.expandedRows[memberId] = !this.expandedRows[memberId];
    },

    filteredMulesByCharacter(characterId) {
      return this.notArchivedMules[characterId] || [];
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
        this.$refs.notificationRef.showNotification('Mule class updated successfully!');
      } catch (error) {
        console.error('Error updating mule class:', error.message);
        this.$refs.notificationRef.showNotification('Failed to update mule class.');
      }
    },
  },
  async mounted() {
    try {
      await this.fetchCharacters(); // Fetch all characters once
      await this.fetchAllMules(); // Fetch mules once
      document.addEventListener('click', this.handleClickOutside);
    } catch (error) {
      console.error('Error during component initialization:', error);
    }
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  }
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
</style>
