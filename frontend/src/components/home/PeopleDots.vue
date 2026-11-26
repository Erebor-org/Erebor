<template>
  <span class="people" :class="`people--${size}`" role="img" :aria-label="ariaLabel">
    <svg
      v-for="i in slots"
      :key="i"
      class="person"
      :class="{ 'is-kept': i <= keptSlots }"
      viewBox="0 0 16 20"
      aria-hidden="true"
    >
      <circle cx="8" cy="5" r="4" />
      <path d="M1 20c0-4.4 3.1-8 7-8s7 3.6 7 8z" />
    </svg>
  </span>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Proportion dessinée en petits personnages : pleins = restés, vides = partis.
 * Jusqu'à 10 recrues, un personnage par recrue ; au-delà, ramené sur 10.
 */
const props = defineProps({
  kept: { type: Number, default: 0 },
  total: { type: Number, default: 0 },
  size: { type: String, default: 'md' }, // sm | md
});

const slots = computed(() => (props.total <= 10 ? props.total : 10));
const keptSlots = computed(() =>
  props.total <= 10 ? props.kept : Math.round((10 * props.kept) / props.total)
);
const ariaLabel = computed(() => `${props.kept} sur ${props.total} toujours là`);
</script>

<style scoped>
.people {
  display: inline-flex;
  align-items: flex-end;
  gap: 2px;
}

.person {
  width: 0.7rem;
  height: 0.9rem;
  fill: rgba(var(--text-rgb), 0.14);
}

.people--sm .person {
  width: 0.5rem;
  height: 0.65rem;
}

.person.is-kept {
  fill: var(--accent);
}
</style>
