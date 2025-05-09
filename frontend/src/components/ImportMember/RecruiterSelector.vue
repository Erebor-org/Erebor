<template>
  <div>
    <label for="recruiter" class="block text-base font-medium text-[#b07d46] mb-1">
      Recruteur :
    </label>

    <!-- Si sélectionné -->
    <div v-if="selectedRecruiterId" class="flex items-center gap-3">
      <img :src="selectedRecruiterIcon" alt="Classe" class="w-10 h-10 rounded-full" />
      <span class="text-base font-semibold text-[#b07d46]">{{ selectedRecruiterName }}</span>
      <button
        type="button"
        @click="clearRecruiter"
        class="text-red-500 text-lg font-bold focus:outline-none"
      >
        &times;
      </button>
    </div>

    <!-- Si non sélectionné -->
    <div v-else>
      <input
        type="text"
        v-model="searchQuery"
        placeholder="Rechercher un recruteur..."
        class="w-full border-2 border-[#b07d46] bg-[#fffaf0] rounded-md p-2 text-base mb-2 focus:outline-none focus:ring-2 focus:ring-[#f3d9b1]"
      />
      <ul
        class="max-h-32 overflow-y-auto bg-[#fffaf0] border-2 border-[#b07d46] rounded-md p-2"
      >
        <li
          v-for="recruiter in filteredRecruiters"
          :key="recruiter.id"
          @click="selectRecruiter(recruiter)"
          class="flex items-center gap-3 p-2 cursor-pointer hover:bg-[#f3d9b1] rounded-md"
        >
          <img :src="classes[recruiter.class]" alt="Classe" class="w-7 h-7" />
          <span class="text-base text-[#b07d46]">{{ recruiter.pseudo }}</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    recruiters: {
      type: Array,
      default: () => []
    },
    selectedRecruiterId: {
      type: Number,
      default: null
    },
    classes: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      searchQuery: '',
      selectedRecruiterName: '',
      selectedRecruiterIcon: ''
    };
  },
  computed: {
    filteredRecruiters() {
      if (!this.searchQuery) return this.recruiters;
      const query = this.searchQuery.toLowerCase();
      return this.recruiters.filter(recruiter => 
        recruiter.pseudo.toLowerCase().includes(query)
      );
    }
  },
  methods: {
    selectRecruiter(recruiter) {
      this.selectedRecruiterName = recruiter.pseudo;
      this.selectedRecruiterIcon = this.classes[recruiter.class];
      this.searchQuery = '';
      this.$emit('select-recruiter', recruiter);
    },
    clearRecruiter() {
      this.selectedRecruiterName = '';
      this.selectedRecruiterIcon = '';
      this.$emit('clear-recruiter');
    }
  },
  watch: {
    selectedRecruiterId: {
      immediate: true,
      handler(newId) {
        if (newId && this.recruiters.length > 0) {
          const recruiter = this.recruiters.find(r => r.id === newId);
          if (recruiter) {
            this.selectedRecruiterName = recruiter.pseudo;
            this.selectedRecruiterIcon = this.classes[recruiter.class];
          }
        }
      }
    }
  }
};
</script>
