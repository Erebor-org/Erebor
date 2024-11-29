<template>
  <div
    class="min-h-screen w-full flex flex-col items-center justify-start py-10 bg-cover bg-center"
    :style="{ backgroundImage: `url(${backgroundImage})` }"
  >

<!-- Search Bar and Table Block -->
<div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg shadow-lg w-11/12 max-w-4xl p-6 mb-6 mt-10">
  <!-- Header with Title and Button -->
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold text-[#b02e2e]">Blacklist de la Guilde</h2>
    <button
      @click="showModal = true"
      class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1]"
    >
      Ajouter un Personnage
    </button>
  </div>

  <!-- Search Bar -->
  <div class="mb-4">
    <input
      type="text"
      v-model="searchQuery"
      placeholder="Rechercher un pseudo"
      class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-3 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
    />
  </div>

  <!-- Blacklist Table -->
  <div class="overflow-y-auto max-h-[70vh]">
    <table class="w-full text-left border-collapse">
      <thead>
        <tr class="bg-[#b02e2e] text-[#f3d9b1] text-lg">
          <th class="p-4">Pseudo</th>
          <th class="p-4">Raison</th>
          <th class="p-4 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(entry, index) in filteredBlacklist"
          :key="index"
          class="hover:bg-[#f3d9b1] hover:shadow-md transition-all"
        >
          <td class="p-4 text-[#b07d46] font-bold">{{ entry.pseudo }}</td>
          <td class="p-4 text-[#b07d46]">{{ entry.reason }}</td>
          <td class="p-4 text-right">
            <button
              @click="removeFromBlacklist(index)"
              class="bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg hover:bg-[#942828]"
            >
              Supprimer
            </button>
          </td>
        </tr>
        <tr v-if="filteredBlacklist.length === 0">
          <td colspan="3" class="p-4 text-center text-[#b07d46] font-bold">
            Aucun personnage trouvé.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>


    <!-- Add Character Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg shadow-lg w-11/12 max-w-lg p-8 relative">
        <!-- Close Button -->
        <button
          class="absolute top-4 right-4 text-[#b02e2e] font-bold text-xl hover:text-[#942828]"
          @click="closeModal"
        >
          &times;
        </button>
        <h2 class="text-2xl font-bold text-[#b02e2e] mb-6 text-center">Ajouter un Personnage</h2>

        <!-- Form Fields -->
        <div class="mb-4">
          <label for="pseudo" class="block text-lg font-medium text-[#b07d46] mb-2">
            Pseudo du Personnage:
          </label>
          <input
            type="text"
            id="pseudo"
            v-model="newBlacklist.pseudo"
            class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-3 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            placeholder="Entrez le pseudo"
            required
          />
        </div>

        <div class="mb-4">
          <label for="reason" class="block text-lg font-medium text-[#b07d46] mb-2">
            Raison du Blacklist:
          </label>
          <textarea
            id="reason"
            v-model="newBlacklist.reason"
            class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-3 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            placeholder="Entrez la raison"
            rows="4"
            required
          ></textarea>
        </div>

        <!-- Add Button -->
        <button
          @click="addToBlacklist"
          class="w-full bg-[#b02e2e] text-[#f3d9b1] font-bold py-3 px-6 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1]"
        >
          Ajouter à la Blacklist
        </button>
      </div>
    </div>

    <!-- Notifications -->
    <div
      v-if="showNotificationAddBlacklist"
      class="fixed top-4 right-4 bg-[#b02e2e] text-[#f3d9b1] font-bold py-4 px-4 rounded-lg shadow-lg z-50"
    >
      Personnage ajouté à la blacklist
    </div>
    <div
      v-if="showNotificationRemoveBlacklist"
      class="fixed top-4 right-4 bg-[#b02e2e] text-[#f3d9b1] font-bold py-2 px-4 rounded-lg shadow-lg z-50"
    >
      Personnage supprimé de la blacklist
    </div>
  </div>
</template>

<script>
import register_bg from '@/assets/register_bg.webp';

export default {
  data() {
    return {
      backgroundImage: register_bg,
      showModal: false, // Modal visibility state
      showNotificationAddBlacklist: false,
      showNotificationRemoveBlacklist: false,
      searchQuery: '', // Search query
      newBlacklist: {
        pseudo: '',
        reason: '',
      },
      blacklist: [],
    };
  },
  computed: {
    filteredBlacklist() {
      return this.blacklist.filter((entry) =>
        entry.pseudo.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
  },
  methods: {
    addToBlacklist() {
      if (this.newBlacklist.pseudo && this.newBlacklist.reason) {
        this.blacklist.push({ ...this.newBlacklist });
        this.newBlacklist.pseudo = '';
        this.newBlacklist.reason = '';
        this.showNotificationAddBlacklist = true;
        this.closeModal();
        setTimeout(() => {
          this.showNotificationAddBlacklist = false;
        }, 3000);
      } else {
        alert('Veuillez remplir tous les champs !');
      }
    },
    removeFromBlacklist(index) {
      this.blacklist.splice(index, 1);
      this.showNotificationRemoveBlacklist = true;
      setTimeout(() => {
        this.showNotificationRemoveBlacklist = false;
      }, 3000);
    },
    closeModal() {
      this.showModal = false;
    },
  },
};
</script>

<style scoped>
table {
  border-spacing: 0;
}
th,
td {
  border-bottom: 2px solid #b07d46;
}
</style>
