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

      <!-- Member Table -->
      <div class="overflow-y-auto max-h-96">
        <table class="w-full text-center border-collapse">
          <thead>
            <tr class="bg-[#b02e2e] text-[#f3d9b1] text-lg">
              <th class="p-4">Classe</th>
              <th class="p-4">Pseudo</th>
              <th class="p-4">Recruteur</th>
              <th class="p-4">Rang</th>
              <th class="p-4">Mules</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="({ member, id }, index) in filteredMembers" :key="id || index">
              <!-- Main Row -->
              <tr class="hover:bg-[#f3d9b1] hover:shadow-md transition-all group relative">
                <!-- Editable Classe Icon -->
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
                <!-- Recruteur -->
                <td class="p-4 text-[#b07d46]">
                  {{ member?.recruiter?.pseudo || 'No Recruiter' }}
                </td>
                <!-- Rang -->
                <td class="p-4 text-[#b07d46]">{{ member?.rank?.name || 'No Rank' }}</td>
                <!-- View Mules -->
                <td class="p-4">
                  <button v-if="filteredMulesByCharacter(id).length > 0" class="text-[#b02e2e] font-bold underline" @click="toggleExpand(id)">
                    {{ expandedRows[id] ? 'Cacher' : filteredMulesByCharacter(id).length + ' mules' }}
                  </button>
                </td>
              </tr>

              <!-- Expanded Row -->
              <tr v-if="expandedRows[id]" class="bg-[#ffecd2]">
                <td colspan="5" class="p-4">
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
                          class="hover:bg-[#f3d9b1] border-b border-[#b07d46]"
                        >
                          <td class="p-2">
                            <img
                              :src="`${iconFolder}/${mule.class}.avif`"
                              :alt="mule.class"
                              class="w-8 h-8 mx-auto"
                            />
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
                </td>
              </tr>
            </template>
          </tbody>
        </table>
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
      console.log('toggle', memberId);
      this.currentCharacterId = memberId;
      this.currentCharacterMules = this.filteredMulesByCharacter(memberId);
      console.log('this.currentCharacterMules', this.currentCharacterMules);
      if (this.expandedRows[memberId]) {
        delete this.expandedRows[memberId]; // Remove the key if it's already expanded
      } else {
        this.expandedRows[memberId] = true; // Add the key to expandedRows
      }

      console.log('expandedRows', this.expandedRows);
    },

    async fetchNotArchivedCharacters() {
      try {
        const response = await axios.get('http://localhost:8000/characters');

        console.log('Characters Response:', response.data); // Debug log
        const notArchivedCharacters = response.data.filter(character => !character.isArchived);
        this.charactersNotArchived = response.data.filter(character => !character.isArchived);
        console.log('Not archived characters:', this.charactersNotArchived);
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
        const response = await axios.put(
          `http://localhost:8000/characters/${characterId}/archive`,
          {
            isArchived: true,
          }
        );
        console.log('Character archived successfully:', response.data);

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
        const response = await axios.put(`http://localhost:8000/mules/${muleId}/archive`, {
          isArchived: true,
        });
        console.log('Mule archived successfully:', response.data);

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
      console.log('filteredMulesByCharacter', this.notArchivedMules);
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

/* Optional row highlight */
tbody tr:hover {
  background-color: #f3d9b1;
}

/* Add this to your CSS file if not using TailwindCSS */
.group:hover .group-hover\:block {
  display: block;
}

.group-hover\:block {
  display: none;
}
</style>
