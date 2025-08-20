<template>
  <Transition name="notification">
    <div
      v-if="visible"
      :class="[
        'fixed top-4 right-4 z-50 max-w-sm p-4 rounded-xl shadow-xl border-l-4 bg-theme-card',
        type === 'error' ? 'border-theme-error' : 'border-theme-success'
      ]"
      @click="hideNotification"
    >
      <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
          <svg v-if="type === 'success'" class="w-5 h-5 text-theme-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else class="w-5 h-5 text-theme-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <div class="flex-1">
          <p :class="['font-medium', type === 'error' ? 'text-theme-error' : 'text-theme-text']">{{ message }}</p>
        </div>
        <button
          @click.stop="hideNotification"
          class="flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity text-theme-text-muted hover:text-theme-text"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </Transition>
</template>

<script>
export default {
  data() {
    return {
      visible: false,
      message: '',
      type: 'success',
    };
  },
  methods: {
    showNotification(msg, type = 'success') {
      this.message = msg;
      this.type = type;
      this.visible = true;
      setTimeout(() => {
        this.visible = false;
      }, 4000);
    },
    hideNotification() {
      this.visible = false;
    },
  },
};
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.notification-enter-to,
.notification-leave-from {
  opacity: 1;
  transform: translateX(0) scale(1);
}
</style>
