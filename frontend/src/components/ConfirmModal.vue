<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
    <div class="bg-theme-card rounded-2xl shadow-2xl p-8 max-w-md w-full border border-theme-border">
      <h2 class="text-xl font-bold text-theme-primary mb-4">{{ title }}</h2>
      <p class="text-theme-text mb-6">{{ message }}</p>
      <div class="flex justify-end gap-3">
        <button @click="$emit('cancel')" class="px-4 py-2 rounded-lg bg-theme-bg-muted text-theme-text hover:bg-theme-error/10 border border-theme-border">Annuler</button>
        <button @click="$emit('confirm')" class="px-4 py-2 rounded-lg bg-theme-primary text-white hover:bg-theme-primary-hover font-semibold">{{ confirmText || 'Confirmer' }}</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConfirmModal',
  props: {
    show: { type: Boolean, required: true },
    title: { type: String, default: 'Confirmation' },
    message: { type: String, required: true },
    confirmText: { type: String, default: 'Confirmer' },
  },
};
</script>
