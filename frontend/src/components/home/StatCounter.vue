<template>
  <div class="stat-counter" :title="title || undefined">
    <span class="stat-counter-label">{{ label }}</span>
    <span class="stat-counter-value">
      <span v-if="loading" class="stat-counter-skeleton"></span>
      <template v-else>{{ value ?? '—' }}<span v-if="unit && value != null" class="stat-counter-unit">{{ unit }}</span></template>
    </span>
    <slot v-if="!loading" />
    <span v-if="!loading && sentence" class="stat-counter-sentence" :class="`is-${tone}`">{{ sentence }}</span>
    <span v-if="!loading && note" class="stat-counter-note">{{ note }}</span>
  </div>
</template>

<script setup>
// Compteur lisible sans explication : une valeur, puis une phrase (« 2 de moins qu'il y a
// un mois ») colorée selon que c'est une bonne nouvelle ou non.
defineProps({
  label: { type: String, required: true },
  value: { type: [Number, String], default: null },
  // Suffixe affiché en plus petit après la valeur (« sur 169 »).
  unit: { type: String, default: '' },
  sentence: { type: String, default: '' },
  // good | bad | flat
  tone: { type: String, default: 'flat' },
  // Précision discrète sous la phrase.
  note: { type: String, default: '' },
  title: { type: String, default: '' },
  loading: { type: Boolean, default: false },
});
</script>

<style scoped>
.stat-counter {
  --dash-gold: var(--accent);
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  padding: 1rem 1.15rem;
  border-radius: 0.9rem;
  background-color: rgba(var(--bg-rgb), 0.62);
  background-image: radial-gradient(120% 90% at 0% 0%, rgba(var(--primary-rgb), 0.1), transparent 60%);
  border: 1px solid rgba(var(--accent-rgb), 0.28);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), 0 14px 32px -24px rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(14px) saturate(1.15);
  -webkit-backdrop-filter: blur(14px) saturate(1.15);
}

/* Liseré rouge → or, comme les cartes */
.stat-counter::before {
  content: "";
  position: absolute;
  top: -1px;
  left: 1rem;
  right: 1rem;
  height: 2px;
  border-radius: 2px;
  background-image: linear-gradient(90deg, transparent, var(--primary) 20%, var(--dash-gold) 50%, var(--primary) 80%, transparent);
}

.stat-counter-label {
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", serif;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--dash-gold);
}

:global(:root:not([data-theme="dark"])) .stat-counter {
  --dash-gold: var(--accent-hover);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7), 0 12px 28px -22px rgba(var(--primary-rgb), 0.3);
}

.stat-counter-value {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-variant-numeric: tabular-nums;
  font-weight: 700;
  font-size: 1.85rem;
  line-height: 1.1;
  color: var(--text);
}


.stat-counter-unit {
  margin-left: 0.15rem;
  font-size: 0.55em;
  font-weight: 600;
  color: var(--text-muted);
}





.stat-counter-sentence {
  font-size: 0.8rem;
  font-weight: 600;
}

.stat-counter-note {
  font-size: 0.72rem;
  color: var(--text-muted);
}

.is-good { color: var(--success); }
.is-bad { color: var(--error); }
.is-flat { color: var(--text-muted); }

.stat-counter-skeleton {
  display: inline-block;
  width: 3rem;
  height: 1.5rem;
  border-radius: 0.35rem;
  background-color: var(--bg-muted);
  animation: sc-pulse 1.5s ease-in-out infinite;
}

/* Mobile : les trois compteurs restent sur une ligne */
@media (max-width: 640px) {
  .stat-counter {
    padding: 0.75rem 0.7rem;
  }
  .stat-counter-label {
    font-size: 0.6rem;
    letter-spacing: 0.06em;
    line-height: 1.25;
  }
  .stat-counter-value {
    font-size: 1.4rem;
  }
  .stat-counter-unit {
  margin-left: 0.15rem;
  font-size: 0.55em;
  font-weight: 600;
  color: var(--text-muted);
}



}

@keyframes sc-pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}
</style>
