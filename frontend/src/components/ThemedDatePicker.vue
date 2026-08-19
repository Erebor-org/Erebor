<template>
  <div class="themed-datepicker-wrapper">
    <VueDatePicker
      v-bind="$attrs"
      :dark="isDarkTheme"
      :auto-apply="true"
      :enable-time-picker="false"
      :close-on-auto-apply="true"
      class="themed-datepicker"
    />
  </div>
</template>

<script>
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { useThemeStore } from '@/stores/themeStore';
import { computed } from 'vue';

export default {
  name: 'ThemedDatePicker',
  components: {
    VueDatePicker,
  },
  inheritAttrs: false,
  setup() {
    const themeStore = useThemeStore();
    
    const isDarkTheme = computed(() => themeStore.isDark);
    
    return {
      isDarkTheme,
    };
  },
};
</script>

<style scoped>
.themed-datepicker-wrapper {
  width: 100%;
}

.themed-datepicker {
  width: 100%;
}

/* Custom styles for VueDatePicker to use CSS variables */
:deep(.dp__input_wrap) {
  background-color: var(--bg-muted) !important;
  border-color: var(--border) !important;
  color: var(--text) !important;
}

:deep(.dp__input_wrap:hover) {
  border-color: var(--border-hover) !important;
}

:deep(.dp__input_wrap:focus-within) {
  border-color: var(--primary) !important;
  box-shadow: 0 0 0 3px var(--ring) !important;
}

:deep(.dp__input) {
  background-color: var(--bg-muted) !important;
  color: var(--text) !important;
}

:deep(.dp__input::placeholder) {
  color: var(--text-muted) !important;
}

:deep(.dp__input_icon) {
  color: var(--accent) !important;
}

:deep(.dp__menu) {
  background-color: var(--card) !important;
  border-color: rgba(var(--accent-rgb), 0.2) !important;
  border-radius: 1rem !important;
  box-shadow: 0 24px 64px -24px rgba(0, 0, 0, 0.5) !important;
}

/* En-tête des jours de la semaine (Mo Tu We...) en or, à la manière des
   libellés de section du reste du site (cf. .fs-group-title). */
:deep(.dp__calendar_header_item) {
  color: var(--accent) !important;
  font-weight: 700;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

:deep(.dp__calendar_header_item:hover) {
  background-color: transparent !important;
}

:deep(.dp__calendar_header_separator) {
  background-color: rgba(var(--accent-rgb), 0.25) !important;
}

:deep(.dp__cell_inner) {
  color: var(--text) !important;
}

:deep(.dp__cell_inner:hover) {
  background-color: rgba(var(--primary-rgb), 0.12) !important;
}

/* Date sélectionnée / bornes de plage : dégradé rouge-or du blason,
   comme .brand-gradient-bg utilisé ailleurs sur le site. */
:deep(.dp__active_date),
:deep(.dp__range_start),
:deep(.dp__range_end) {
  background-color: transparent !important;
  background-image: linear-gradient(135deg, var(--primary), var(--accent)) !important;
  color: #ffffff !important;
  box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.45);
}

:deep(.dp__active_date:hover),
:deep(.dp__range_start:hover),
:deep(.dp__range_end:hover) {
  filter: brightness(1.08);
}

:deep(.dp__range_between) {
  background-color: rgba(var(--primary-rgb), 0.16) !important;
  color: var(--primary) !important;
}

/* "Aujourd'hui" en liseré or, distinct de la sélection (dégradé plein). */
:deep(.dp__today) {
  border-color: var(--accent) !important;
  color: var(--accent) !important;
}

:deep(.dp__disabled) {
  color: var(--text-muted) !important;
  opacity: 0.4;
}

:deep(.dp__arrow_top),
:deep(.dp__arrow_bottom) {
  border-color: var(--border) !important;
}

:deep(.dp__arrow_top::before),
:deep(.dp__arrow_bottom::before) {
  background-color: var(--card) !important;
}

/* Hide action buttons (select/cancel) when auto-apply is enabled */
:deep(.dp__action_buttons) {
  display: none !important;
}

:deep(.dp__action_button) {
  display: none !important;
}

:deep(.dp__action_select) {
  display: none !important;
}

/* Improve month/year selection */
:deep(.dp__month_year_wrap) {
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 8px;
  transition: all 0.2s ease;
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", serif;
  font-weight: 700;
  font-size: 1.05rem;
  color: var(--primary) !important;
}

:deep(.dp__month_year_wrap:hover) {
  background-color: var(--primary-light) !important;
  color: var(--primary-hover) !important;
  transform: scale(1.05);
}

/* Better month/year picker view */
:deep(.dp__month_year_select) {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  padding: 8px;
}

:deep(.dp__month_year_select button) {
  padding: 12px 8px;
  border-radius: 8px;
  transition: all 0.2s ease;
  font-weight: 500;
  border: 1px solid var(--border);
  background-color: var(--bg-muted);
  color: var(--text);
}

:deep(.dp__month_year_select button:hover) {
  background-color: var(--primary-light) !important;
  color: var(--primary) !important;
  border-color: var(--primary);
  transform: scale(1.05);
}

:deep(.dp__month_year_select button.dp__active_date) {
  background-color: var(--primary) !important;
  color: #ffffff !important;
  border-color: var(--primary);
}

/* Year picker improvements */
:deep(.dp__year_select) {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  padding: 8px;
  max-height: 300px;
  overflow-y: auto;
}

:deep(.dp__year_select button) {
  padding: 12px 8px;
  border-radius: 8px;
  transition: all 0.2s ease;
  font-weight: 500;
  border: 1px solid var(--border);
  background-color: var(--bg-muted);
  color: var(--text);
}

:deep(.dp__year_select button:hover) {
  background-color: var(--primary-light) !important;
  color: var(--primary) !important;
  border-color: var(--primary);
  transform: scale(1.05);
}

:deep(.dp__year_select button.dp__active_date) {
  background-color: var(--primary) !important;
  color: #ffffff !important;
  border-color: var(--primary);
}

/* Make navigation arrows more visible */
:deep(.dp__inner_nav) {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

:deep(.dp__inner_nav:hover) {
  background-color: var(--primary-light) !important;
  color: var(--primary) !important;
  transform: scale(1.1);
}

:deep(.dp__inner_nav) {
  color: var(--text) !important;
}

:deep(.dp__inner_nav:hover) {
  background-color: var(--bg-muted) !important;
  color: var(--primary) !important;
}

:deep(.dp__calendar_header_separator) {
  background-color: var(--border) !important;
}

:deep(.dp__marker_line) {
  background-color: var(--primary) !important;
}

:deep(.dp__marker_dot) {
  background-color: var(--primary) !important;
}

/* Dark theme specific adjustments */
[data-theme="dark"] :deep(.dp__menu) {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.5) !important;
}

[data-theme="dark"] :deep(.dp__input_wrap) {
  background-color: var(--bg-muted) !important;
}

/* Light theme specific adjustments */
:not([data-theme="dark"]) :deep(.dp__menu) {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
}
</style>

