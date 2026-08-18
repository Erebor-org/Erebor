<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
    @click.self="close"
  >
    <div class="glass-modal rounded-2xl max-w-md w-full mx-4 transform transition-all duration-300">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 pb-4">
        <div class="flex items-center">
          <div class="w-11 h-11 bg-theme-error/15 border border-theme-error/40 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
            <svg class="w-6 h-6 text-theme-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <h3 class="text-lg font-serif font-bold text-theme-text">Confirmation</h3>
        </div>
        <button
          @click="close"
          class="text-theme-text-muted hover:text-theme-primary transition-colors duration-200"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="px-6 pb-6">
        <p class="text-theme-text leading-relaxed text-center">
          {{ message }}
        </p>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end space-x-3 px-6 pb-6">
        <button
          @click="close"
          class="px-5 py-2.5 text-sm font-medium text-theme-text bg-theme-bg-muted border border-theme-border rounded-xl hover:bg-theme-border transition-all duration-300"
        >
          Annuler
        </button>
        <button
          @click="confirm"
          class="px-5 py-2.5 text-sm font-semibold text-white bg-theme-primary border border-transparent rounded-xl hover:bg-theme-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-theme-primary focus:ring-offset-theme-bg transition-all duration-300 shadow-lg shadow-theme-primary/30"
        >
          {{ confirmButtonText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ArchiveModal',
  props: {
    show: {
      type: Boolean,
      required: true,
    },
    message: {
      type: String,
      required: true,
    },
    confirmButtonText: {
      type: String,
      required: true,
    },
  },
  emits: ['close', 'confirm'],
  methods: {
    close() {
      this.$emit('close');
    },
    confirm() {
      this.$emit('confirm');
    },
  },
};
</script>
