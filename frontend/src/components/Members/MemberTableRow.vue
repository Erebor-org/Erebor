<template>
  <tr class="transition-all group relative hover:bg-[#f3d9b1]/30" style="min-height: 120px">
    <td class="p-4 relative text-center align-middle">
      <div class="relative inline-block">
        <button @click="toggleClassDropdown(member.id)">
          <img
            :src="classes[member.class]"
            alt="Character Class"
            class="w-14 h-14 cursor-pointer mx-auto"
          />
        </button>
        <div
          v-if="classDropdownVisible[`character-${member.id}`]"
          class="absolute top-12 left-0 z-10 bg-[#fff5e6] border border-[#b07d46] rounded-lg shadow-lg p-2 w-80"
        >
          <div class="grid grid-cols-4 gap-2 max-h-40 overflow-y-auto">
            <div
              v-for="(icon, className) in classes"
              :key="className"
              class="flex flex-col items-center gap-1 cursor-pointer hover:bg-[#f3d9b1] p-2 rounded-lg"
              @click="updateCharacterClass(member.id, className)"
            >
              <img :src="icon" :alt="className" class="w-12 h-12 mx-auto" />
              <span class="text-sm text-[#b07d46]">{{ className }}</span>
            </div>
          </div>
        </div>
      </div>
    </td>
    <!-- Pseudo -->
    <td class="p-4 text-[#b07d46] font-bold text-center align-middle">
      <div
        v-if="editingId === member.id"
        @click.stop
      >
        <input
          v-model="editPseudo"
          class="border-2 border-[#b07d46] rounded-lg p-2 w-full focus:ring-2 focus:ring-[#f3d9b1]"
          @blur="savePseudo(member)"
          @keydown.enter.prevent="savePseudo(member)"
          ref="editInput"
        />
      </div>
      <div
        v-else
        class="flex items-center justify-center gap-2 cursor-pointer hover:text-[#942828] hover:underline"
        @click="startEditingPseudo(member.id, member.pseudo)"
      >
        {{ member.pseudo || 'Unknown' }}
        <span class="text-sm text-[#b07d46]">
          <i class="fas fa-pencil-alt"></i>
        </span>
      </div>
    </td>
    <td class="p-4 text-[#b07d46] text-center align-middle">
      {{ member.ankamaPseudo }}
    </td>
    <td class="p-4 text-[#b07d46] text-center align-middle">
      {{ member?.recruiter?.pseudo || 'No Recruiter' }}
    </td>
    <td class="p-4 text-[#b07d46] text-center align-middle">
      {{ member?.rank?.name || 'No Rank' }}
    </td>
    <td class="p-4 text-center align-middle">
      <button
        v-if="mulesCount > 0"
        @click="$emit('toggle-expand', member.id)"
        class="text-[#b02e2e] font-bold underline hover:text-[#942828] transition-all duration-300"
      >
        {{ mulesCount }}
      </button>
    </td>
    <td class="p-4 text-center align-middle">
      <button
        @click="$emit('view-warnings', member.id, member.pseudo)"
        class="text-[#b02e2e] font-bold underline hover:text-[#942828] transition-all duration-300"
      >
        {{ warningCount || 0 }}
      </button>
    </td>
    <td class="p-4 text-center align-middle">
      <div class="flex space-x-2 justify-center">
        <button
          @click="$emit('archive', member)"
          class="text-[#b02e2e] hover:text-[#942828] transition-all duration-300"
        >
          Archiver
        </button>
      </div>
    </td>
  </tr>
</template>

<script>
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  props: {
    member: {
      type: Object,
      required: true
    },
    classes: {
      type: Object,
      required: true
    },
    warningCount: {
      type: Number,
      default: 0
    },
    mulesCount: {
      type: Number,
      default: 0
    },
    classDropdownVisible: {
      type: Object,
      required: true
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
      this.$emit('toggle-class-dropdown', id, 'character');
    },
    
    updateCharacterClass(characterId, newClass) {
      this.$emit('update-class', characterId, newClass);
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
        await axios.put(`${API_URL}/characters/${entity.id}/update-pseudo`, {
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
        this.$emit('notification', 'Erreur lors de la mise à jour du pseudo');
      }
    }
  }
};
</script>
