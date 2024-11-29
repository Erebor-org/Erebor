<template>
  <div
    class="min-h-screen w-full bg-black relative bg-opacity-90"
  >
    <!-- Add top margin to maintain spacing consistency -->
    <div class="flex items-center justify-center">
      <!-- Contenu des formulaires -->
      <div class="bg-[#fff5e6] border-4 border-[#b07d46] rounded-lg shadow-lg w-12/12 max-w-2x5 p-8 mt-24">
        <!-- Onglets -->
        <div class="flex mb-6">
          <button
            :class="[
              'flex-1 py-2 text-lg font-bold border-b-4',
              activeTab === 'mainCharacter'
                ? 'bg-[#b02e2e] text-[#f3d9b1] border-[#f3d9b1]'
                : 'bg-[#f3d9b1] text-[#b07d46] border-[#b07d46]'
            ]"
            @click="activeTab = 'mainCharacter'"
          >
            Personnage principal
          </button>
          <button
            :class="[
              'flex-1 py-2 text-lg font-bold border-b-4',
              activeTab === 'muleCharacter'
                ? 'bg-[#b02e2e] text-[#f3d9b1] border-[#f3d9b1]'
                : 'bg-[#f3d9b1] text-[#b07d46] border-[#b07d46]'
            ]"
            @click="activeTab = 'muleCharacter'"
          >
            Mule
          </button>
        </div>

        <!-- Formulaire Personnage Principal -->
        <form v-if="activeTab === 'mainCharacter'" @submit.prevent="submitMainCharacter">
          <h2 class="text-2xl font-bold text-[#b02e2e] mb-6">Personnage Principal</h2>

          <div class="mb-4">
            <label for="mainPseudo" class="block text-lg font-medium text-[#b07d46] mb-2">
              Pseudo:
            </label>
            <input
              type="text"
              id="mainPseudo"
              v-model="mainCharacter.pseudo"
              class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              required
            />
          </div>

          <div class="mb-4">
            <label class="block text-lg font-medium text-[#b07d46] mb-2">
              Classe du personnage:
            </label>
            <div class="grid grid-cols-7 gap-4">
              <div
                v-for="(icon, index) in classes"
                :key="index"
                :class="[
                  'cursor-pointer border-2 rounded-lg p-2',
                  mainCharacter.class === index
                    ? 'border-[#b02e2e] bg-[#f3d9b1]'
                    : 'border-[#b07d46] bg-[#fffaf0]'
                ]"
                @click="mainCharacter.class = index, selectClass(index)"
              >
                <img :src="icon" alt="Classe" class="w-full h-auto" />
              </div>
            </div>
          </div>

          <div class="mb-4">
            <label for="mainLevel" class="block text-lg font-medium text-[#b07d46] mb-2">
              Niveau (1-200):
            </label>
            <input
              type="number"
              id="mainLevel"
              v-model.number="mainCharacter.level"
              min="1"
              max="200"
              class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              required
            />
          </div>

          <div class="mb-4">
            <label for="mainDate" class="block text-lg font-medium text-[#b07d46] mb-2">
              Date:
            </label>
            <input
              type="date"
              id="mainDate"
              v-model="mainCharacter.date"
              class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
              required
            />
          </div>

          <!-- Sélectionner un recruteur -->
          <div class="mb-4">
            <label for="recruiter" class="block text-lg font-medium text-[#b07d46] mb-2">
              Sélectionner un recruteur:
            </label>
            <div class="relative">
              <select
                id="recruiter"
                v-model="mainCharacter.recruiter"
                class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1] appearance-none"
                required
              >
                <option v-for="player in players" :key="player.pseudo" :value="player.pseudo">
                  {{ player.pseudo }}
                </option>
              </select>
            </div>
          </div>

          <div v-if="errorMessage" class="text-1xl font-bold text-[#b02e2e] mb-6">
            {{ errorMessage }}
          </div>
          <button
            type="submit"
            class="w-full bg-[#b02e2e] text-[#f3d9b1] font-bold py-3 px-6 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1]"
            :disabled="!mainCharacter.class"
          >
            Importer un personnage principal
          </button>
        </form>

       
      <!-- Formulaire Personnage Mule -->
      <form v-else @submit.prevent="submitMuleCharacter">
        <h2 class="text-2xl font-bold text-[#b02e2e] mb-6">Mule</h2>

        <div class="mb-4">
          <label for="mulePseudo" class="block text-lg font-medium text-[#b07d46] mb-2">
            Pseudo:
          </label>
          <input
            type="text"
            id="mulePseudo"
            v-model="muleCharacter.pseudo"
            class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            required
          />
        </div>

        <div class="mb-4">
          <label for="linkedUser" class="block text-lg font-medium text-[#b07d46] mb-2">
            Personnage principal
          </label>
          <select
            id="linkedUser"
            v-model="muleCharacter.linkedUser"
            class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            required
          >
            <option v-for="user in users" :key="user" :value="user">{{ user }}</option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-lg font-medium text-[#b07d46] mb-2">
            Classe du personnage:
          </label>
          <div class="grid grid-cols-7 gap-4">
            <div
              v-for="(icon, index) in classes"
              :key="index"
              :class="[
                'cursor-pointer border-2 rounded-lg p-2',
                muleCharacter.class === icon
                  ? 'border-[#b02e2e] bg-[#f3d9b1]'
                  : 'border-[#b07d46] bg-[#fffaf0]'
              ]"
              @click="muleCharacter.class = icon"
            >
              <img :src="icon" alt="Classe" class="w-full h-auto" />
            </div>
          </div>
        </div>

        <div class="mb-4">
          <label for="muleLevel" class="block text-lg font-medium text-[#b07d46] mb-2">
            Niveau (1-200):
          </label>
          <input
            type="number"
            id="muleLevel"
            v-model.number="muleCharacter.level"
            min="1"
            max="200"
            class="block w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-lg p-2 text-lg focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
            required
          />
        </div>

        <button
          type="submit"
          class="w-full bg-[#b02e2e] text-[#f3d9b1] font-bold py-3 px-6 rounded-lg hover:bg-[#942828] focus:ring-4 focus:ring-[#f3d9b1]"
        >
        Importer une mule
        </button>
      </form>
      </div>
    </div>
  </div>
</template>


<script>
import register_bg from '@/assets/register_bg.webp';

export default {
  
  data() {
    return {
      register_bg,
      classes: {
        sram : "/src/assets/icon_classe/sram.avif",
        forgelance : "/src/assets/icon_classe/forgelance.avif",
        cra : "/src/assets/icon_classe/cra.avif",
        ecaflip : "/src/assets/icon_classe/ecaflip.avif",
        eniripsa : "/src/assets/icon_classe/eniripsa.avif",
        enutrof : "/src/assets/icon_classe/enutrof.avif",
        feca : "/src/assets/icon_classe/feca.avif",
        eliotrope : "/src/assets/icon_classe/eliotrope.avif",
        iop : "/src/assets/icon_classe/iop.avif",
        osamodas : "/src/assets/icon_classe/osamodas.avif",
        pandawa : "/src/assets/icon_classe/pandawa.avif",
        roublard : "/src/assets/icon_classe/roublard.avif",
        sacrieur : "/src/assets/icon_classe/sacrieur.avif",
        sadida : "/src/assets/icon_classe/sadida.avif",
        steamer : "/src/assets/icon_classe/steamer.avif",
        xelor : "/src/assets/icon_classe/xelor.avif",
        zobal : "/src/assets/icon_classe/zobal.avif",
      },
     
      errorMessage: "", // Message d'erreur à afficher
      selectedClass: null, // Classe actuellement sélectionnée
      activeTab: "mainCharacter", // Onglet actif
      users: ["User1", "User2", "User3"], // Liste des utilisateurs pour le menu déroulant
      mainCharacter: {
        pseudo: "",
        recruiter: null, // Linked pseudo selected from the dropdown
        class: null,
        level: 1,
        date: "",
      },
      players: [
        { pseudo: "Bard", icon: "/src/assets/icon_classe/enutrof.avif" },
        { pseudo: "Siisko", icon: "/src/assets/icon_classe/forgelance.avif" },
        { pseudo: "Lae", icon: "/path/to/icons/lae.png" },
        { pseudo: "Shira", icon: "/path/to/icons/shira.png" },
      ],
      muleCharacter: {
        pseudo: "",
        linkedUser: "",
        class: null,
        level: 1,
      },
      blacklistList: [
        "toto", "Bard", "Siisko", "Lae", "Shira"
      ]
    };
  },
  methods: {
    // Vérifier si le pseudo est valide
    isPseudoValid(pseudo) {
      return !this.blacklistList.includes(pseudo);
    },
    submitMainCharacter() {
      // Envoyer les données du personnage principal
      if (!this.mainCharacter.class) {
        this.errorMessage = "Veuillez sélectionner une classe avant de soumettre.";
        return;
      }
      if (!this.isPseudoValid(this.mainCharacter.pseudo)) {
        this.errorMessage = `"${this.mainCharacter.pseudo}" est blacklist d'Erebor.`;
        return;
      }
      const payload = { ...this.mainCharacter };
      console.log("Données Personnage Principal:", payload);
      console.log("players", this.players);
      // Ajoutez ici une requête POST vers Symfony avec fetch ou axios
    },
    
    submitMuleCharacter() {
      if (!this.muleCharacter.class) {
        this.errorMessage = "Veuillez sélectionner une classe avant de soumettre.";
        return;
      }
      if (!this.isPseudoValid(this.muleCharacter.pseudo)) {
        this.errorMessage = `"${this.muleCharacter.pseudo}" est blacklist d'Erebor.`;
        return;
      }
      // Envoyer les données du personnage mule
      const payload = { ...this.muleCharacter };
      console.log("Données Personnage Mule:", payload);
      // Ajoutez ici une requête POST vers Symfony avec fetch ou axios
    },
    selectClass(icon) {
      this.selectedClass = icon;
    },
    selectClassMule(icon) {
      this.selectedClass = icon;
    },
  },
};
</script>

<style>
/* Aucun style additionnel nécessaire grâce à Tailwind CSS */
/* Adjust dropdown styling to show icons alongside text */
option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

option img {
  width: 1.5rem;
  height: 1.5rem;
  border-radius: 50%;
}
</style>
