<template>
  <div class="mt-8 border-t-2 border-[#b07d46] pt-6">
    <h3 class="text-lg md:text-xl font-bold text-[#b02e2e] mb-4">
      Mules (Optionnel)
    </h3>
    
    <!-- List of added mules -->
    <div v-if="mules.length > 0" class="mb-4">
      <div 
        v-for="(mule, index) in mules" 
        :key="index"
        class="mb-3 p-3 bg-[#f3d9b1] rounded-md relative"
      >
        <div class="flex flex-wrap items-center gap-3">
          <!-- Mule info summary -->
          <img :src="classes[mule.class]" alt="Classe" class="w-12 h-12 rounded-full" />
          <span class="font-semibold text-[#b07d46]">{{ mule.pseudo }}</span>
          <span class="text-sm text-[#b07d46]">({{ mule.ankamaPseudo }})</span>
          
          <!-- Remove button -->
          <button 
            type="button" 
            @click="removeMule(index)"
            class="absolute top-2 right-2 text-[#b02e2e] hover:text-[#942828] font-bold text-xl h-8 w-8 flex items-center justify-center rounded-full hover:bg-[#fff5e6] transition-colors"
          >
            &times;
          </button>
        </div>
      </div>
    </div>
    
    <!-- Add mule button -->
    <button
      type="button"
      @click="showAddMuleForm = !showAddMuleForm"
      class="w-full bg-[#f3d9b1] text-[#b07d46] font-bold py-2 px-4 rounded-lg hover:bg-[#e3c9a1] focus:ring-2 focus:ring-[#b07d46] transition-colors flex items-center justify-center mb-4"
    >
      <span v-if="!showAddMuleForm">+ Ajouter une mule</span>
      <span v-else>- Masquer le formulaire</span>
    </button>
    
    <!-- Add mule form -->
    <MuleForm 
      v-if="showAddMuleForm" 
      :classes="classes" 
      :blacklist="blacklist"
      @add-mule="addMule"
    />
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
