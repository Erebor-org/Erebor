<!-- ... -->
<template>
  <ImportMemberModal
    :showModal="showModalMember"
    :fetchNotArchivedCharacters="fetchNotArchivedCharacters"
    @close="closeModal"
    @character-added="handleCharacterAdded"
    @mule-added="handleMuleAdded"
  />
</template>

<script>
import ImportMemberModal from './ImportMember/ImportMemberModal.vue';

export default {
  components: {
    ImportMemberModal,
  },
  props: {
    showModalMember: {
      type: Boolean,
      required: true,
    },
    fetchNotArchivedCharacters: {
      type: Array,
      required: true,
    },
  },
  methods: {
    closeModal() {
      this.$emit('close');
    },
    handleCharacterAdded(data) {
      // Passer les données du personnage à la méthode parent pour mettre à jour le compteur de mules
      this.$emit('character-added', data);
    },
    handleMuleAdded() {
      this.$emit('mule-added');
    },
  },
};
</script>