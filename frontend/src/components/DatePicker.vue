<template>
  <div class="relative">
    <div
      @click.stop="togglePicker"
      class="date-picker-button w-full px-4 py-3 bg-theme-bg-muted border border-theme-border rounded-lg text-theme-text focus-within:ring-2 focus-within:ring-theme-primary focus-within:border-theme-primary cursor-pointer flex items-center justify-between"
    >
      <div class="flex items-center space-x-3">
        <svg class="w-5 h-5 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span class="text-theme-text">{{ displayValue || placeholder }}</span>
      </div>
      <svg class="w-5 h-5 text-theme-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>

    <!-- Date Picker Dropdown -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        ref="pickerDropdown"
        class="absolute left-0 top-full z-50 mt-2 bg-theme-card border border-theme-border rounded-xl shadow-2xl p-4 w-full min-w-[320px]"
      >
      <!-- Month/Year Selector -->
      <div class="flex items-center justify-between mb-4">
        <button
          @click="previousMonth"
          class="p-2 hover:bg-theme-bg-muted rounded-lg transition-colors"
        >
          <svg class="w-5 h-5 text-theme-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <div class="text-lg font-semibold text-theme-text">
          {{ monthNames[currentMonth] }} {{ currentYear }}
        </div>
        <button
          @click="nextMonth"
          class="p-2 hover:bg-theme-bg-muted rounded-lg transition-colors"
        >
          <svg class="w-5 h-5 text-theme-text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- Week Days Header -->
      <div class="grid grid-cols-7 gap-1 mb-2">
        <div
          v-for="day in weekDays"
          :key="day"
          class="text-center text-xs font-medium text-theme-text-muted py-2"
        >
          {{ day }}
        </div>
      </div>

      <!-- Calendar Days -->
      <div class="grid grid-cols-7 gap-1">
        <div
          v-for="day in daysInMonth"
          :key="`${day.value}-${day.isCurrentMonth}`"
          @click="selectDate(day)"
          :class="[
            'text-center py-2 rounded-lg transition-all',
            day.isCurrentMonth && !day.isDisabled
              ? 'cursor-pointer'
              : 'cursor-not-allowed',
            day.isCurrentMonth
              ? day.isSelected
                ? 'bg-theme-primary text-white font-semibold'
                : day.isToday
                ? 'bg-theme-primary/20 text-theme-primary font-semibold'
                : 'text-theme-text hover:bg-theme-bg-muted'
              : 'text-theme-text-muted opacity-40',
            day.isDisabled ? 'opacity-30' : ''
          ]"
        >
          {{ day.value }}
        </div>
      </div>

      <!-- Time Picker -->
      <div v-if="includeTime" class="mt-4 pt-4 border-t border-theme-border">
        <div class="flex items-center justify-center space-x-4">
          <div class="flex items-center space-x-2">
            <label class="text-sm text-theme-text-muted">Heure</label>
            <input
              v-model.number="selectedHour"
              type="number"
              min="0"
              max="23"
              class="w-16 px-3 py-2 bg-theme-bg-muted border border-theme-border rounded-lg text-theme-text text-center focus:ring-2 focus:ring-theme-primary"
            />
          </div>
          <span class="text-theme-text-muted">:</span>
          <div class="flex items-center space-x-2">
            <label class="text-sm text-theme-text-muted">Minute</label>
            <input
              v-model.number="selectedMinute"
              type="number"
              min="0"
              max="59"
              step="5"
              class="w-16 px-3 py-2 bg-theme-bg-muted border border-theme-border rounded-lg text-theme-text text-center focus:ring-2 focus:ring-theme-primary"
            />
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex space-x-2 mt-4 pt-4 border-t border-theme-border">
        <button
          @click="clearDate"
          class="flex-1 px-4 py-2 bg-theme-bg-muted text-theme-text rounded-lg hover:bg-theme-border transition-colors"
        >
          Effacer
        </button>
        <button
          @click="confirmDate"
          class="flex-1 px-4 py-2 bg-theme-primary text-white rounded-lg hover:bg-theme-primary-hover transition-colors"
        >
          Valider
        </button>
      </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Sélectionner une date'
  },
  includeTime: {
    type: Boolean,
    default: true
  },
  minDate: {
    type: String,
    default: null
  }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const pickerDropdown = ref(null);
const currentDate = ref(new Date());
const selectedDate = ref(null);
const selectedHour = ref(new Date().getHours());
const selectedMinute = ref(Math.ceil(new Date().getMinutes() / 5) * 5); // Round to nearest 5 minutes

const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
const weekDays = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];

const currentYear = computed(() => currentDate.value.getFullYear());
const currentMonth = computed(() => currentDate.value.getMonth());

const displayValue = computed(() => {
  if (!selectedDate.value) return '';
  const date = new Date(selectedDate.value);
  const dateStr = date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  });
  if (props.includeTime) {
    return `${dateStr} à ${String(selectedHour.value).padStart(2, '0')}:${String(selectedMinute.value).padStart(2, '0')}`;
  }
  return dateStr;
});

const daysInMonth = computed(() => {
  const year = currentYear.value;
  const month = currentMonth.value;
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  const daysInMonthCount = lastDay.getDate();
  const startingDayOfWeek = firstDay.getDay();
  
  const days = [];
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  const minDateObj = props.minDate ? new Date(props.minDate) : null;
  if (minDateObj) minDateObj.setHours(0, 0, 0, 0);

  // Previous month days
  const prevMonthLastDay = new Date(year, month, 0).getDate();
  for (let i = startingDayOfWeek - 1; i >= 0; i--) {
    days.push({
      value: prevMonthLastDay - i,
      isCurrentMonth: false,
      isToday: false,
      isSelected: false,
      isDisabled: true
    });
  }

  // Current month days
  for (let day = 1; day <= daysInMonthCount; day++) {
    const date = new Date(year, month, day);
    date.setHours(0, 0, 0, 0);
    const isToday = date.getTime() === today.getTime();
    const isSelected = selectedDate.value && new Date(selectedDate.value).setHours(0, 0, 0, 0) === date.getTime();
    const isDisabled = minDateObj && date < minDateObj;

    days.push({
      value: day,
      isCurrentMonth: true,
      isToday,
      isSelected,
      isDisabled
    });
  }

  // Next month days (fill remaining slots)
  const remainingDays = 42 - days.length; // 6 rows * 7 days
  for (let day = 1; day <= remainingDays; day++) {
    days.push({
      value: day,
      isCurrentMonth: false,
      isToday: false,
      isSelected: false,
      isDisabled: true
    });
  }

  return days;
});

const togglePicker = (event) => {
  event?.stopPropagation();
  isOpen.value = !isOpen.value;
};

const closePicker = () => {
  isOpen.value = false;
};

const previousMonth = () => {
  currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1);
};

const nextMonth = () => {
  currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1);
};

const selectDate = (day) => {
  if (typeof day === 'object' && day.isDisabled) return;
  const dayValue = typeof day === 'object' ? day.value : day;
  selectedDate.value = new Date(currentYear.value, currentMonth.value, dayValue);
};

const clearDate = () => {
  selectedDate.value = null;
  selectedHour.value = new Date().getHours();
  selectedMinute.value = Math.ceil(new Date().getMinutes() / 5) * 5;
  emit('update:modelValue', '');
  closePicker();
};

const confirmDate = () => {
  if (!selectedDate.value) return;
  
  const date = new Date(selectedDate.value);
  date.setHours(selectedHour.value, selectedMinute.value, 0, 0);
  
  // Format as ISO string for datetime-local input
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(selectedHour.value).padStart(2, '0');
  const minutes = String(selectedMinute.value).padStart(2, '0');
  
  const isoString = `${year}-${month}-${day}T${hours}:${minutes}`;
  emit('update:modelValue', isoString);
  closePicker();
};

// Initialize from modelValue
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    const date = new Date(newValue);
    selectedDate.value = date;
    selectedHour.value = date.getHours();
    selectedMinute.value = date.getMinutes();
    currentDate.value = new Date(date.getFullYear(), date.getMonth(), 1);
  }
}, { immediate: true });

// Handle click outside
const handleClickOutside = (event) => {
  if (!isOpen.value) return;
  
  if (pickerDropdown.value && !pickerDropdown.value.contains(event.target)) {
    const pickerButton = event.target.closest('.date-picker-button');
    if (!pickerButton) {
      closePicker();
    }
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>

