<template>
  <div>
    <div class="flex items-center space-x-3 mb-6">
      <div class="w-8 h-8 bg-gradient-to-br from-theme-primary to-theme-primary-hover rounded-lg flex items-center justify-center">
        <svg class="w-5 h-5 text-theme-bg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
      </div>
      <h3 class="text-2xl font-bold text-theme-primary">Mules (Optionnel)</h3>
    </div>
    
    <!-- List of added mules -->
    <div v-if="mules.length > 0" class="mb-6 space-y-3">
      <div 
        v-for="(mule, index) in mules" 
        :key="index"
        class="p-4 bg-theme-bg-muted rounded-xl border border-theme-border hover:border-theme-primary transition-all duration-200 relative group"
      >
        <div class="flex items-center space-x-4">
          <!-- Mule info summary -->
          <img :src="classes[mule.class]" :alt="`Classe ${mule.class}`" class="w-10 h-10 rounded-lg border-2 border-theme-border group-hover:border-theme-primary transition-colors duration-200" />
          <div class="flex-1">
            <span class="font-semibold text-theme-primary text-lg">{{ mule.pseudo }}</span>
            <span class="block text-sm text-theme-text-muted">({{ mule.ankamaPseudo }})</span>
          </div>
          
          <!-- Remove button -->
          <button 
            type="button" 
            @click="removeMule(index)"
                            class="w-8 h-8 bg-theme-error hover:bg-theme-error/80 text-white rounded-lg flex items-center justify-center transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-theme-error/30 shadow-sm hover:shadow-md"
            title="Supprimer la mule"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Add mule button -->
    <button
      type="button"
      @click="showAddMuleForm = !showAddMuleForm"
      class="w-full bg-theme-bg-muted hover:bg-theme-border text-theme-text hover:text-theme-primary font-medium py-3 px-4 rounded-xl border-2 border-theme-border hover:border-theme-primary transition-all duration-200 flex items-center justify-center space-x-2 mb-6"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
      </svg>
      <span>{{ !showAddMuleForm ? 'Ajouter une mule' : 'Masquer le formulaire' }}</span>
    </button>
    
    <!-- Add mule form -->
    <div v-if="showAddMuleForm" class="bg-theme-bg-muted rounded-xl p-6 border border-theme-bg-muted">
      <MuleForm 
        :classes="classes" 
        :blacklist="blacklist"
        @add-mule="addMule"
      />
    </div>
  </div>
</template>

<script>
import MuleForm from './MuleForm.vue';

export default {
  components: {
    MuleForm
  },
  props: {
    classes: {
      type: Object,
      required: true
    },
    blacklist: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      mules: [],
      showAddMuleForm: false
    };
  },
  methods: {
    addMule(mule) {
      this.mules.push({ ...mule });
      this.emitUpdate();
    },
    
    removeMule(index) {
      this.mules.splice(index, 1);
      this.emitUpdate();
    },
    
    emitUpdate() {
      this.$emit('update:mules', [...this.mules]);
    },
    
    resetForm() {
      this.mules = [];
      this.showAddMuleForm = false;
      this.emitUpdate();
    }
  }
};
</script>
