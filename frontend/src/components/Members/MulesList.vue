<template>
  <tr v-if="expanded" class="bg-[#ffecd2]">
    <td :colspan="colspan" class="p-4">
      <div class="w-10/12 mx-auto">
        <div v-if="mules.length > 0">
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
                v-for="(mule, muleIndex) in mules"
                :key="mule.id || muleIndex"
                :class="[muleIndex % 2 === 0 ? 'bg-[#fff5e6]' : 'bg-[#fde1c8]']"
                class="hover:bg-[#f3d9b1]"
              >
                <td class="p-2 relative">
                  <div class="relative inline-block">
                    <button @click="toggleClassDropdown(mule.id)">
                      <img
                        :src="classes[mule.class]"
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
                    v-if="editingId === mule.id"
                    @click.stop
                  >
                    <input
                      v-model="editPseudo"
                      class="border-2 border-[#b07d46] rounded-lg p-2 w-full focus:ring-2 focus:ring-[#f3d9b1]"
                      @blur="savePseudo(mule)"
                      @keydown.enter.prevent="savePseudo(mule)"
                      ref="editInput"
                    />
                  </div>
                  <div
                    v-else
                    class="flex items-center gap-2 cursor-pointer hover:text-[#942828] hover:underline"
                    @click="startEditingPseudo(mule.id, mule.pseudo)"
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
                    @click="$emit('archive-mule', mule)"
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

<script>
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  props: {
    expanded: {
      type: Boolean,
      default: false
    },
    mules: {
      type: Array,
      default: () => []
    },
    classes: {
      type: Object,
      required: true
    },
    classDropdownVisible: {
      type: Object,
      required: true
    },
    colspan: {
      type: Number,
      default: 8
    }
  },
  
  data() {
    return {
      editingId: null,
      editPseudo: ''
    };
  },
  
  methods: {
    toggleClassDropdown(id) {
      this.$emit('toggle-class-dropdown', id, 'mule');
    },
    
    updateMuleClass(muleId, newClass) {
      this.$emit('update-mule-class', muleId, newClass);
    },
    
    startEditingPseudo(id, currentPseudo) {
      this.editingId = id;
      this.editPseudo = currentPseudo || '';
      this.$nextTick(() => {
        const input = this.$refs.editInput;
        if (input) input.focus();
      });
    },
    
    async savePseudo(entity) {
      if (this.editPseudo.trim() === '') {
        console.log('Le pseudo ne peut pas être vide.');
        return;
      }

      try {
        await axios.put(`${API_URL}/mules/${entity.id}/update-pseudo`, {
          pseudo: this.editPseudo,
        });
        
        // Emit updated pseudo
        this.$emit('pseudo-updated', entity.id, this.editPseudo);
        
        this.editingId = null;
        this.editPseudo = '';
        this.$emit('notification', 'Pseudo mis à jour avec succès !');
      } catch (error) {
        console.error(
          'Erreur lors de la mise à jour du pseudo:',
          error.response?.data || error.message
        );
        this.$emit('notification', 'Erreur lors de la mise à jour du pseudo.');
      }
    }
  }
};
</script>
