<template>
  <span class="rank-badge" :class="`rank-badge--${size}`" :title="visual.label">
    <span class="rank-gem" :class="`tier-${visual.color}`">
      <svg v-if="visual.icon === 'sprout'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 21V12M12 12C8.5 12 6 9.5 6 6c3.5 0 6 2.5 6 6zm0 0c0-3.5 2.5-6 6-6 0 3.5-2.5 6-6 6z" />
      </svg>
      <svg v-else-if="visual.icon === 'binoculars'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M6 4h3v3H6zM15 4h3v3h-3zM6.5 7A2.5 2.5 0 004 9.5v7a2.5 2.5 0 005 0v-7A2.5 2.5 0 006.5 7zm11 0A2.5 2.5 0 0015 9.5v7a2.5 2.5 0 005 0v-7A2.5 2.5 0 0017.5 7z" />
      </svg>
      <svg v-else-if="visual.icon === 'halberd'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M11 2h2v20h-2z" />
        <path d="M13 5l6 2.5-6 2.5z" />
        <path d="M11 6l-5 2 5 2z" />
      </svg>
      <svg v-else-if="visual.icon === 'shield'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2l7 3v6c0 5-3 8.5-7 9-4-.5-7-4-7-9V5l7-3z" />
      </svg>
      <svg v-else-if="visual.icon === 'sword'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2l1.6 3.2h-3.2z" />
        <path d="M11.2 5.2h1.6v9h-1.6z" />
        <path d="M8 14h8v1.6H8z" />
        <path d="M11.2 15.6h1.6V21h-1.6z" />
      </svg>
      <svg v-else-if="visual.icon === 'crown'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M3 8l4 3 5-6 5 6 4-3-2 10H5L3 8z" />
      </svg>
      <svg v-else-if="visual.icon === 'crossed-swords'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <g transform="rotate(45 12 12)">
          <rect x="11" y="2" width="2" height="15" rx="0.8" />
          <rect x="9" y="16" width="6" height="2" rx="0.8" />
        </g>
        <g transform="rotate(-45 12 12)">
          <rect x="11" y="2" width="2" height="15" rx="0.8" />
          <rect x="9" y="16" width="6" height="2" rx="0.8" />
        </g>
      </svg>
      <svg v-else-if="visual.icon === 'star'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2l2.5 5.5 6 .7-4.4 4 1.2 6-5.3-3-5.3 3 1.2-6-4.4-4 6-.7z" />
      </svg>
      <svg v-else-if="visual.icon === 'flag'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M5 3v18h2v-7h11l-2-3.5L18 7H7V3H5z" />
      </svg>
      <svg v-else-if="visual.icon === 'megaphone'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M2 9v6h3l8 4V5L5 9H2z" />
        <path d="M15 7c1.8 1.2 1.8 8.8 0 10l1.6 1.1c2.3-2.4 2.3-9.8 0-12.2z" />
      </svg>
      <svg v-else-if="visual.icon === 'scroll'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M6 4a2 2 0 100 4h1v8H6a2 2 0 100 4h12a2 2 0 100-4h-1V8h1a2 2 0 100-4z" />
      </svg>
      <svg v-else-if="visual.icon === 'gauntlet'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M7 22V11a5 5 0 0110 0v11z" />
        <path d="M9 11V6.5a1 1 0 112 0V11" />
        <path d="M12 11V5.5a1 1 0 112 0V11" />
        <path d="M15 11V6.5a1 1 0 112 0V11" />
      </svg>
      <svg v-else-if="visual.icon === 'scepter'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <circle cx="12" cy="5" r="3" />
        <path d="M9 10h6v1.8H9z" />
        <path d="M11 11.5h2V22h-2z" />
      </svg>
      <svg v-else-if="visual.icon === 'spark'" class="rank-gem-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2l1.8 6.2L20 10l-6.2 1.8L12 18l-1.8-6.2L4 10l6.2-1.8L12 2z" />
      </svg>
    </span>
    <span v-if="showLabel" class="rank-label">{{ visual.label }}</span>
  </span>
</template>

<script setup>
import { computed } from 'vue';
import { getRankVisual } from '@/utils/rankTiers';

const props = defineProps({
  rank: {
    type: Object,
    default: null,
  },
  size: {
    type: String,
    default: 'md', // 'sm' | 'md'
  },
  showLabel: {
    type: Boolean,
    default: true,
  },
});

const visual = computed(() => getRankVisual(props.rank));
</script>

<style scoped>
.rank-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--text);
}

.rank-badge--sm {
  font-size: 0.72rem;
  gap: 0.32rem;
}

.rank-gem {
  width: 1.3rem;
  height: 1.3rem;
  flex-shrink: 0;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.rank-badge--sm .rank-gem {
  width: 1.1rem;
  height: 1.1rem;
}

.rank-gem-icon {
  width: 62%;
  height: 62%;
  color: white;
  filter: drop-shadow(0 0 1px rgba(0, 0, 0, 0.6));
}

/* Couleur de fond = repère de palier (ancienneté croissante ou direction) ;
   l'icône reste la même quel que soit le thème clair/sombre. */
.rank-gem.tier-bronze {
  background-color: #6b5544;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
}

.rank-gem.tier-silver {
  background-color: #8992a0;
}

.rank-gem.tier-gold {
  background-color: var(--accent);
}

.rank-gem.tier-legend {
  background-image: linear-gradient(160deg, var(--accent), var(--primary));
  box-shadow: 0 0 6px 0 rgba(var(--accent-rgb), 0.6);
}

.rank-gem.tier-lead {
  background-image: linear-gradient(160deg, var(--primary), var(--accent));
  box-shadow: 0 0 0 2px var(--card), 0 0 0 3px rgba(var(--primary-rgb), 0.55);
}

.rank-gem.tier-special {
  background-color: var(--accent-hover);
}

.rank-gem.tier-none {
  background-color: var(--border);
}
</style>
