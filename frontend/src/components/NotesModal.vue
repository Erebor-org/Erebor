<template>
  <div v-if="show" class="fixed inset-0 bg-theme-bg/80 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="closeModal">
    <div class="bg-theme-card border border-theme-border rounded-2xl shadow-2xl max-w-lg w-full p-6 relative">
      <h3 class="text-xl font-bold text-theme-primary mb-4">Note du membre</h3>
      <div v-if="!isEditing">
        <div v-if="notesContent && notesContent.trim() !== ''" class="whitespace-pre-line text-theme-text mb-4">{{ notesContent }}</div>
        <div v-else class="italic text-theme-text-muted mb-4">Aucune note pour ce membre.</div>
        <div class="flex justify-end">
          <button @click="isEditing = true" class="px-4 py-2 bg-theme-primary text-white rounded hover:bg-theme-primary-hover text-sm font-medium">Éditer</button>
        </div>
      </div>
      <div v-else>
        <textarea
          v-model="editableNotes"
          class="w-full bg-theme-bg-muted border border-theme-border rounded-lg px-3 py-2 text-sm text-theme-text focus:outline-none focus:ring-2 focus:ring-theme-primary"
          rows="5"
          placeholder="Ajouter ou modifier la note de ce membre..."
        ></textarea>
        <div class="flex justify-end mt-4 space-x-2">
          <button @click="cancelEdit" class="px-4 py-2 bg-theme-bg-muted text-theme-text rounded hover:bg-theme-border text-sm">Annuler</button>
          <button @click="saveNotes" class="px-4 py-2 bg-theme-primary text-white rounded hover:bg-theme-primary-hover text-sm font-medium">Sauvegarder</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotesModal',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    initialNotes: {
      type: String,
      default: '',
    },
  },
  data() {
    return {
      notesContent: this.initialNotes,
      editableNotes: this.initialNotes,
      isEditing: false,
    };
  },
  watch: {
    initialNotes(newVal) {
      this.notesContent = newVal;
      this.editableNotes = newVal;
      this.isEditing = false;
    },
    show(newVal) {
      if (newVal) {
        this.notesContent = this.initialNotes;
        this.editableNotes = this.initialNotes;
        this.isEditing = false;
      }
    },
  },
  methods: {
    closeModal() {
      this.isEditing = false;
      this.$emit('close');
    },
    saveNotes() {
      this.notesContent = this.editableNotes;
      this.isEditing = false;
      this.$emit('save', this.editableNotes);
    },
    cancelEdit() {
      this.editableNotes = this.notesContent;
      this.isEditing = false;
    },
  },
};
</script>
