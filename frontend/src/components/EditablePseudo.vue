<template>
  <div class="relative">
    <!-- Edit Mode -->
    <div
      v-if="isEditing"
      @click.stop
      class="relative"
    >
      <input
        v-model="localEditPseudo"
        class="w-full px-4 py-3 bg-theme-bg border-2 border-theme-primary rounded-xl focus:ring-2 focus:ring-theme-primary/50 focus:border-theme-primary text-theme-text placeholder-gray-500 transition-all duration-300 text-base font-bold shadow-lg shadow-theme-primary/25"
        @blur="savePseudo"
        @keydown.enter.prevent="savePseudo"
        @keydown.esc="cancelEdit"
        ref="editInput"
        placeholder="Entrez le nouveau pseudo"
      />
      <!-- Save/Cancel Indicators -->
      <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex space-x-2">
        <button
          @click="savePseudo"
          class="w-6 h-6 bg-gradient-to-r from-theme-primary to-theme-primary-hover text-theme-bg rounded-full flex items-center justify-center hover:from-theme-primary hover:to-theme-primary-hover transition-all duration-300 shadow-lg"
          title="Sauvegarder"
        >
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </button>
        <button
          @click="cancelEdit"
          class="w-6 h-6 bg-theme-error text-theme-text rounded-full flex items-center justify-center hover:bg-theme-primary transition-all duration-300 shadow-lg"
          title="Annuler"
        >
          <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Display Mode -->
    <div
      v-else
      class="group cursor-pointer hover:bg-theme-primary/10 rounded-xl px-4 py-3 transition-all duration-300 border-2 border-transparent hover:border-theme-primary/30"
      @click="startEditing"
    >
      <div class="flex items-center justify-between">
        <span class="text-base font-bold text-theme-primary group-hover:text-theme-primary-hover transition-colors duration-300">{{ entity.pseudo || 'Aucun pseudo' }}</span>
        <div class="opacity-0 group-hover:opacity-100 transition-all duration-300">
          <svg class="w-5 h-5 text-theme-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="isLoading"
      class="absolute inset-0 bg-theme-bg bg-opacity-75 rounded-xl flex items-center justify-center backdrop-blur-sm"
    >
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-theme-primary"></div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'EditablePseudo',
  props: {
    entity: {
      type: Object,
      required: true,
    },
    entityType: {
      type: String,
      required: true,
      validator: (value) => ['character', 'mule'].includes(value),
    },
    editingPseudo: {
      type: Object,
      required: false,
      default: () => ({ type: null, id: null }),
    },
    editPseudo: {
      type: String,
      required: false,
      default: '',
    },
  },
  emits: ['start-editing', 'save-pseudo'],
  data() {
    return {
      isLoading: false,
      localEditing: false,
      localPseudoValue: '',
    };
  },
  computed: {
    isEditing() {
      return this.localEditing || (this.editingPseudo && 
             this.editingPseudo.type && 
             this.editingPseudo.id && 
             this.entity && 
             this.entity.id &&
             this.editingPseudo.type === this.entityType && 
             this.editingPseudo.id === this.entity.id);
    },
    localEditPseudo: {
      get() {
        return this.localPseudoValue;
      },
      set(value) {
        this.localPseudoValue = value;
      },
    },
  },
  watch: {
    isEditing(newVal) {
      if (newVal && this.$refs.editInput) {
        this.$nextTick(() => {
          const input = this.$refs.editInput;
          if (input) {
            input.focus();
            input.select();
          }
        });
      }
    },
  },
  methods: {
    startEditing() {
      if (this.entity && this.entity.id) {
        this.localEditing = true;
        this.localPseudoValue = this.entity.pseudo || '';
        this.$emit('start-editing', this.entity.id, this.entity.pseudo, this.entityType);
      }
    },
    savePseudo() {
      if (this.entity && this.localPseudoValue.trim() !== '') {
        this.isLoading = true;
        // Emit the save event with the new pseudo value
        this.$emit('save-pseudo', this.entity, this.entityType, this.localPseudoValue.trim());
        
        // Reset local editing state
        this.localEditing = false;
        this.localPseudoValue = '';
        
        // Reset loading state after a short delay
        setTimeout(() => {
          this.isLoading = false;
        }, 1000);
      } else {
        // If empty, cancel the edit
        this.cancelEdit();
      }
    },
    cancelEdit() {
      // Reset local editing state
      this.localEditing = false;
      this.localPseudoValue = '';
      
      // Reset the editing state by emitting the same event with current values
      if (this.entity && this.entity.id) {
        this.$emit('start-editing', this.entity.id, this.entity.pseudo, this.entityType);
      }
    },
  },
};
</script>

