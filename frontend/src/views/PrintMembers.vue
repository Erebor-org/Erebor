<template>
  <div
    class="w-full flex flex-col items-center justify-center bg-cover bg-center"
    :style="{ backgroundImage: `url(${backgroundImage})` }"
  >
    <div>
      <Notification ref="notificationRef" />
      <!-- Rest of your component -->
    </div>
    <!-- Main Block -->
    <div
      class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg shadow-lg w-11/12 max-w-6xl p-6 mb-6"
    >
      <!-- Title and Button -->
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-[#b02e2e]">Liste des Membres</h2>
        <button
          @click="showModalMember = true"
          class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1]"
        >
          Ajouter un Personnage
        </button>
        <ImportMember
          :showModalMember="showModalMember"
          :fetchNotArchivedCharacters="fetchNotArchivedCharacters"
          @close="showModalMember = false"
        />
      </div>

      <!-- Search Bar -->
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Rechercher par pseudo, recruteur, rang ou mule"
        class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-3 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1] mb-4"
      />
      <!-- Member table -->
      <div class="overflow-y-auto max-h-96">
        <table class="w-full text-center border-collapse">
          <!-- Make the header sticky -->
          <thead class="sticky top-0 bg-[#b02e2e] z-10">
            <tr class="text-[#f3d9b1] text-lg">
              <th class="p-4">Classe</th>
              <th class="p-4">Pseudo</th>
              <th class="p-4">Recruteur</th>
              <th class="p-4">Rang</th>
              <th class="p-4">Mules</th>
              <th class="p-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="({ member, id }, index) in filteredMembers" :key="member.id || index">
              <!-- Main Row -->
              <tr class="transition-all group relative hover:bg-[#f3d9b1]">
                <td class="p-4 relative">
                  <div class="relative inline-block">
                    <button @click="toggleClassDropdown(member.id)">
                      <img
                        :src="`${iconFolder}/${member.class}.avif`"
                        alt="Character Class"
                        class="w-10 h-10 cursor-pointer"
                      />
                    </button>
                    <div
                      v-if="classDropdownVisible[member.id]"
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
                <td class="p-4 text-[#b07d46] font-bold">{{ member?.pseudo || 'Unknown' }}</td>
                <td class="p-4 text-[#b07d46]">
                  {{ member?.recruiter?.pseudo || 'No Recruiter' }}
                </td>
                <td class="p-4 text-[#b07d46]">{{ member?.rank?.name || 'No Rank' }}</td>
                <td class="p-4">
                  <button
                    v-if="filteredMulesByCharacter(id).length > 0"
                    @click="toggleExpand(id)"
                    class="text-[#b02e2e] font-bold underline"
                  >
                    {{ filteredMulesByCharacter(id).length + ' mules' }}
                  </button>
                </td>
                <td class="p-4">
                  <div
                    class="hidden group-hover:block text-[#b02e2e] cursor-pointer"
                    title="Options"
                    @click="openModal(member)"
                  >
                    &#x22EE;
                  </div>
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
                                <button @click="toggleClassDropdown(mule.id)">
                                  <img
                                    :src="`${iconFolder}/${mule.class}.avif`"
                                    alt="Mule Class"
                                    class="w-8 h-8 cursor-pointer mx-auto"
                                  />
                                </button>
                                <div
                                  v-if="classDropdownVisible[mule.id]"
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
                            <td class="p-2 text-[#b07d46] font-bold">{{ mule.pseudo }}</td>
                            <td class="p-2 text-[#b07d46]">{{ mule.ankamaPseudo }}</td>
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
    <!-- Modal archive -->
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

        <h2 class="text-xl font-bold text-[#b02e2e] mb-4">Options</h2>
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
  </div>
</template>

<script>
import axios from 'axios';
import members_bg from '@/assets/members_bg.webp';
import ImportMember from '@/components/ImportMember.vue';
import Notification from '@/components/NotificationCenter.vue';

import { reactive } from 'vue';

export default {
  components: {
    ImportMember,
    Notification,
  },
  data() {
    return {
      backgroundImage: members_bg, // Add your medieval-themed background image
      iconFolder: 'src/assets/icon_classe/', // Folder containing your class icons
      searchQuery: '',
      charactersNotArchived: [], // Holds the list of all characters
      showModal: false,
      showModalMember: false,
      selectedMember: null,
      showNotification: false,
      notArchivedMules: {}, // Stores all mules fetched at once, grouped by character ID
      showMulesModal: false,
      currentCharacter: null,
      currentCharacterId: null,
      currentCharacterMules: [],
      classDropdownVisible: reactive({}), // Tracks which dropdown is open
      expandedRows: reactive({}), // Tracks which dropdown is open
      classes: {
        sram: '/src/assets/icon_classe/sram.avif',
        forgelance: '/src/assets/icon_classe/forgelance.avif',
        cra: '/src/assets/icon_classe/cra.avif',
        ecaflip: '/src/assets/icon_classe/ecaflip.avif',
        eniripsa: '/src/assets/icon_classe/eniripsa.avif',
        enutrof: '/src/assets/icon_classe/enutrof.avif',
        feca: '/src/assets/icon_classe/feca.avif',
        eliotrope: '/src/assets/icon_classe/eliotrope.avif',
        iop: '/src/assets/icon_classe/iop.avif',
        osamodas: '/src/assets/icon_classe/osamodas.avif',
        pandawa: '/src/assets/icon_classe/pandawa.avif',
        roublard: '/src/assets/icon_classe/roublard.avif',
        sacrieur: '/src/assets/icon_classe/sacrieur.avif',
        sadida: '/src/assets/icon_classe/sadida.avif',
        steamer: '/src/assets/icon_classe/steamer.avif',
        xelor: '/src/assets/icon_classe/xelor.avif',
        zobal: '/src/assets/icon_classe/zobal.avif',
        huppermage: '/src/assets/icon_classe/huppermage.avif',
        ouginak: '/src/assets/icon_classe/ouginak.avif',
      },
    };
  },
  methods: {
    toggleExpand(memberId) {
      this.currentCharacterId = memberId;
      this.currentCharacterMules = this.filteredMulesByCharacter(memberId);
      if (this.expandedRows[memberId]) {
        delete this.expandedRows[memberId]; // Remove the key if it's already expanded
      } else {
        this.expandedRows[memberId] = true; // Add the key to expandedRows
      }
    },

    async fetchNotArchivedCharacters() {
      try {
        const response = await axios.get('http://localhost:8000/characters');
        const notArchivedCharacters = response.data.filter(character => !character.isArchived);
        this.charactersNotArchived = response.data.filter(character => !character.isArchived);
        return notArchivedCharacters;
      } catch (error) {
        console.error(
          'Error fetching not archived characters:',
          error.response?.data || error.message
        );
        alert('An error occurred while fetching not archived characters.');
      }
    },
    async archiveCharacter(characterId) {
      try {
        await axios.put(`http://localhost:8000/characters/${characterId}/archive`, {
          isArchived: true,
        });

        // Remove the character from the list
        this.charactersNotArchived = this.charactersNotArchived.filter(
          char => char.id !== characterId
        );
        this.showModal = false;
        this.showNotification = true;
        // Automatically hide the notification after 3 seconds
        setTimeout(() => {
          this.showNotification = false;
        }, 3000);
      } catch (error) {
        console.error('Error archiving character:', error.response?.data || error.message);
        alert('An error occurred while archiving the character.');
      }
    },
    async archiveMule(muleId) {
      try {
        await axios.put(`http://localhost:8000/mules/${muleId}/archive`, {
          isArchived: true,
        });

        // Remove the mule from the list in the modal
        this.notArchivedMules[this.currentCharacter.id] = this.notArchivedMules[
          this.currentCharacter.id
        ].filter(mule => mule.id !== muleId);

        // Optional: Show a notification or confirmation
        alert('Mule archivé avec succès.');
      } catch (error) {
        console.error('Error archiving mule:', error.response?.data || error.message);
        alert("Une erreur est survenue lors de l'archivage de la mule.");
      }
    },
    async fetchAllMules() {
      try {
        const response = await axios.get('http://localhost:8000/mules');
        const mules = response.data.filter(mule => !mule.isArchived);
        this.notArchivedMules = mules.reduce((acc, mule) => {
          const charId = mule.mainCharacter?.id;
          if (!acc[charId]) acc[charId] = [];
          acc[charId].push(mule);
          return acc;
        }, {});
      } catch (error) {
        console.error('Error fetching mules:', error);
      }
    },
    filteredMulesByCharacter(characterId) {
      return this.notArchivedMules[characterId] || [];
    },
    showMules(characterId) {
      this.currentCharacter = this.charactersNotArchived.find(char => char.id === characterId);
      this.currentCharacterMules = this.filteredMulesByCharacter(characterId);
      this.showMulesModal = true;
    },
    closeMulesModal() {
      this.showMulesModal = false;
      this.currentCharacter = null;
      this.currentCharacterMules = [];
    },
    openModal(member) {
      this.selectedMember = member;
      this.showModal = true;
    },
    closeModal() {
      this.showModal = false;
    },
    openModalMember() {
      this.showModalMember = true;
    },
    closeModalMember() {
      this.showModalMember = false;
    },
    editMule(mule) {
      // Handle editing logic, such as opening a modal with mule details
      console.log('Editing mule:', mule);
    },
    toggleClassDropdown(characterId) {
      if (!this.classDropdownVisible[characterId]) {
        // Open this dropdown, close all others
        Object.keys(this.classDropdownVisible).forEach(key => {
          this.classDropdownVisible[key] = false;
        });
        this.classDropdownVisible[characterId] = true;
      } else {
        // Close the dropdown
        this.classDropdownVisible[characterId] = false;
      }
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
        if (dropdown.contains(event.target)) {
          clickedInside = true;
        }
      });

      if (!clickedInside) {
        this.closeAllDropdowns();
      }
    },
    async updateCharacterClass(characterId, newClass) {
      try {
        await axios.put(`http://localhost:8000/characters/${characterId}/update-class`, {
          class: newClass,
        });
        // Update the character's class locally for instant feedback
        const character = this.filteredMembers.find(member => member.id === characterId);
        if (character) {
          character.class = newClass;
        }
        this.closeAllDropdowns();
        this.$refs.notificationRef.showNotification('Class updated successfully!');
      } catch (error) {
        console.error('Error updating character class:', error.message);
        this.$refs.notificationRef.showNotification('Failed to update class.');
      }
    },
    async updateMuleClass(muleId, newClass) {
      try {
        await axios.put(`http://localhost:8000/mules/${muleId}/update-class`, {
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
      await this.fetchNotArchivedCharacters();
      await this.fetchAllMules(); // Fetch all mules once when the component mounts
      document.addEventListener('click', this.handleClickOutside);
    } catch (error) {
      console.error('Error during component initialization:', error);
    }
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
  computed: {
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
</style>
