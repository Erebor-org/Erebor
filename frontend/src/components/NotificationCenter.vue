<template>
  <Transition name="notification">
    <div
      v-if="visible"
      :class="[
        'fixed top-4 right-4 z-50 max-w-sm p-4 rounded-xl shadow-xl border-l-4 bg-theme-card',
        typeClasses[type] || typeClasses.success
      ]"
      @click="hideNotification"
    >
      <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
          <svg v-if="type === 'success'" class="w-5 h-5 text-theme-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else-if="type === 'error'" class="w-5 h-5 text-theme-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
          <svg v-else-if="type === 'warning'" class="w-5 h-5 text-theme-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <svg v-else class="w-5 h-5 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="flex-1">
          <p :class="['font-medium', textClasses[type] || textClasses.success]">{{ message }}</p>
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

<script setup>
import { ref } from 'vue'

const visible = ref(false)
const message = ref('')
const type = ref('success')

const typeClasses = {
  success: 'border-theme-success',
  error: 'border-theme-error',
  warning: 'border-theme-warning',
  info: 'border-theme-primary'
}

const textClasses = {
  success: 'text-theme-success',
  error: 'text-theme-error',
  warning: 'text-theme-warning',
  info: 'text-theme-text'
}

const showNotification = (msg, notificationType = 'success') => {
  message.value = msg
  type.value = notificationType
  visible.value = true
  setTimeout(() => {
    visible.value = false
  }, 4000)
}

const hideNotification = () => {
  visible.value = false
}

// Expose methods to parent components
defineExpose({
  showNotification,
  hideNotification
})
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
