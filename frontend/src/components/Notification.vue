<template>
  <Transition name="notification">
    <div
      v-if="isVisible"
      :class="[
        'fixed top-4 right-4 z-50 max-w-sm p-4 rounded-xl shadow-xl border-l-4 backdrop-blur-sm',
        typeClasses[type]
      ]"
    >
      <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
          <component :is="icon" :class="['w-5 h-5', iconClasses[type]]" />
        </div>
        <div class="flex-1 min-w-0">
          <h4 class="font-semibold text-sm leading-5 mb-1">{{ title }}</h4>
          <p class="text-sm leading-5 opacity-90">{{ message }}</p>
        </div>
        <button
          @click="close"
          class="flex-shrink-0 opacity-70 hover:opacity-100 transition-all duration-200 hover:scale-110 p-1 rounded-full hover:bg-black/5 dark:hover:bg-white/5"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <!-- Progress bar -->
      <div class="mt-3 h-1 bg-current opacity-20 rounded-full overflow-hidden">
        <div 
          class="h-full bg-current transition-all duration-300 ease-linear"
          :style="{ width: `${progress}%` }"
        ></div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'info',
    validator: (value) => ['success', 'warning', 'error', 'info'].includes(value)
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  },
  duration: {
    type: Number,
    default: 5000
  }
})

const isVisible = ref(true)
const progress = ref(100)
let progressInterval = null

const typeClasses = {
  success: 'bg-theme-success/10 text-theme-success border-theme-success',
  warning: 'bg-theme-warning/10 text-theme-warning border-theme-warning',
  error: 'bg-theme-error/10 text-theme-error border-theme-error',
  info: 'bg-theme-primary/10 text-theme-primary border-theme-primary'
}

const iconClasses = {
  success: 'text-theme-success',
  warning: 'text-theme-warning',
  error: 'text-theme-error',
  info: 'text-theme-primary'
}

const icon = computed(() => {
  const icons = {
    success: 'CheckCircleIcon',
    warning: 'ExclamationTriangleIcon',
    error: 'XCircleIcon',
    info: 'InformationCircleIcon'
  }
  return icons[props.type] || 'InformationCircleIcon'
})

const close = () => {
  isVisible.value = false
}

const startProgress = () => {
  if (props.duration > 0) {
    const step = 100 / (props.duration / 50) // Update every 50ms
    progressInterval = setInterval(() => {
      progress.value -= step
      if (progress.value <= 0) {
        clearInterval(progressInterval)
        close()
      }
    }, 50)
  }
}

onMounted(() => {
  startProgress()
})

onUnmounted(() => {
  if (progressInterval) {
    clearInterval(progressInterval)
  }
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

/* Icon components - modern SVG implementations */
.CheckCircleIcon {
  @apply w-5 h-5;
}

.ExclamationTriangleIcon {
  @apply w-5 h-5;
}

.XCircleIcon {
  @apply w-5 h-5;
}

.InformationCircleIcon {
  @apply w-5 h-5;
}
</style>
