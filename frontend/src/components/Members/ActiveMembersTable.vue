<template>
  <div class="bg-white border-2 border-[#b07d46] rounded-lg shadow-lg p-8 mb-6">
    <h2 class="text-3xl font-bold text-[#b02e2e] mb-4">Membres actifs</h2>
    <!-- Characters Table -->
    <div class="overflow-y-auto max-h-96 min-h-96">
      <table class="w-full text-center border-collapse bg-white rounded-lg shadow-lg">
        <thead class="sticky top-0 bg-[#b02e2e] z-10">
          <tr class="text-[#f3d9b1] text-lg">
            <th class="p-4">Classe</th>
            <th class="p-4">Pseudo</th>
            <th class="p-4">Ankama ID</th>
            <th class="p-4">Recruteur</th>
            <th class="p-4">Rang</th>
            <th class="p-4">Mules</th>
            <th class="p-4">Carton</th>
            <th class="p-4">Actions</th>
          </tr>
        </thead>
        <tbody>
          <!-- Use v-for on each element individually to avoid template key error -->
          <MemberTableRow 
            v-for="({ member, id }, index) in members"
            :key="`row-${member.id}-${index}`"
            :member="member"
            :classes="classes"
            :warning-count="characterWarningCounts[member.id] || 0"
            :mules-count="filteredMulesByCharacter(id).length"
            :class-dropdown-visible="classDropdownVisible"
            @toggle-class-dropdown="toggleClassDropdown"
            @update-class="updateCharacterClass"
            @pseudo-updated="updateCharacterPseudo"
            @toggle-expand="toggleExpand"
            @view-warnings="viewWarnings"
            @archive="openModal"
            @notification="showNotification"
          />
          
          <!-- Mules List for each member -->
          <MulesList
            v-for="({ member, id }, index) in members"
            :key="`mules-${member.id}-${index}`"
            :expanded="expandedRows[id]"
            :mules="filteredMulesByCharacter(id)"
            :classes="classes"
            :class-dropdown-visible="classDropdownVisible"
            @toggle-class-dropdown="toggleClassDropdown"
            @update-mule-class="updateMuleClass"
            @pseudo-updated="updateMulePseudo"
            @archive-mule="openMuleModal"
            @notification="showNotification"
          />
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import MemberTableRow from './MemberTableRow.vue';
import MulesList from './MulesList.vue';

export default {
  components: {
    MemberTableRow,
    MulesList
  },
  
  props: {
    members: {
      type: Array,
      required: true
    },
    classes: {
      type: Object,
      required: true
    },
    characterWarningCounts: {
      type: Object,
      default: () => ({})
    },
    notArchivedMules: {
      type: Object,
      default: () => ({})
    },
    classDropdownVisible: {
      type: Object,
      required: true
    },
    expandedRows: {
      type: Object,
      required: true
    }
  },
  
  methods: {
    filteredMulesByCharacter(characterId) {
      return this.notArchivedMules[characterId] || [];
    },
    
    toggleClassDropdown(id, type) {
      this.$emit('toggle-class-dropdown', id, type);
    },
    
    updateCharacterClass(characterId, newClass) {
      this.$emit('update-character-class', characterId, newClass);
    },
    
    updateMuleClass(muleId, newClass) {
      this.$emit('update-mule-class', muleId, newClass);
    },
    
    updateCharacterPseudo(characterId, newPseudo) {
      this.$emit('update-character-pseudo', characterId, newPseudo);
    },
    
    updateMulePseudo(muleId, newPseudo) {
      this.$emit('update-mule-pseudo', muleId, newPseudo);
    },
    
    toggleExpand(memberId) {
      this.$emit('toggle-expand', memberId);
    },
    
    viewWarnings(characterId, pseudo) {
      this.$emit('view-warnings', characterId, pseudo);
    },
    
    openModal(member) {
      this.$emit('open-modal', member);
    },
    
    openMuleModal(mule) {
      this.$emit('open-mule-modal', mule);
    },
    
    showNotification(message) {
      this.$emit('notification', message);
    }
  }
};
</script>
