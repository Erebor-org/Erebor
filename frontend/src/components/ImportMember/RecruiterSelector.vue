<template>
  <div>
    <!-- Si sélectionné -->
    <div v-if="selectedRecruiterId" class="flex items-center gap-3 bg-theme-bg-muted border-2 border-theme-border rounded-lg px-4 py-3">
      <span class="portrait-ring w-9 h-9"><img :src="selectedRecruiterIcon" alt="Classe" /></span>
      <span class="text-base font-semibold text-theme-text flex-1">{{ selectedRecruiterName }}</span>
      <button
        type="button"
        @click="clearRecruiter"
        class="text-theme-text-muted hover:text-theme-error text-lg font-bold focus:outline-none transition-colors"
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
        class="w-full bg-theme-bg-muted border-2 border-theme-border text-theme-text rounded-lg px-4 py-3 text-base mb-2 focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 placeholder-gray-400"
      />
      <ul class="max-h-32 overflow-y-auto glass-modal rounded-lg p-2">
        <li
          v-for="recruiter in filteredRecruiters"
          :key="recruiter.id"
          @click="selectRecruiter(recruiter)"
          class="flex items-center gap-3 p-2 cursor-pointer hover:bg-theme-primary/10 rounded-md transition-colors"
        >
          <span class="portrait-ring w-7 h-7"><img :src="classes[recruiter.class]" alt="Classe" /></span>
          <span class="text-sm text-theme-text">{{ recruiter.pseudo }}</span>
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
