<template>
  <section class="dash-card" :class="{ 'dash-card--accent': accent }">
    <header v-if="title" class="dash-card-head">
      <h2 class="dash-card-title">
        <span v-if="$slots.icon" class="dash-card-icon"><slot name="icon" /></span>
        {{ title }}
      </h2>
      <div v-if="$slots.action && !loading" class="dash-card-action"><slot name="action" /></div>
    </header>
    <div v-if="loading" class="dash-card-skeleton" aria-busy="true">
      <span v-for="n in 3" :key="n" class="dash-skeleton-line"></span>
    </div>
    <slot v-else />
  </section>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  loading: { type: Boolean, default: false },
  // Variante teintée rouge/or (blocs mis en avant)
  accent: { type: Boolean, default: false },
});
</script>

<style scoped>
/*
 * Cartes façon dofus.com : verre sombre, filet doré, liseré rouge → or en tête,
 * titres en petites capitales serif dorées, coins discrètement ornés.
 */
.dash-card {
  --dash-gold: var(--accent);
  position: relative;
  display: flex;
  flex-direction: column;
  min-width: 0;
  height: 100%;
  padding: 1.35rem 1.4rem 1.25rem;
  border-radius: 0.9rem;
  background-color: rgba(var(--bg-rgb), 0.62);
  background-image:
    radial-gradient(120% 80% at 0% 0%, rgba(var(--primary-rgb), 0.09), transparent 60%),
    radial-gradient(90% 70% at 100% 100%, rgba(var(--accent-rgb), 0.06), transparent 60%);
  border: 1px solid rgba(var(--accent-rgb), 0.28);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.05),
    inset 0 0 0 1px rgba(var(--accent-rgb), 0.05),
    0 18px 40px -26px rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(14px) saturate(1.15);
  -webkit-backdrop-filter: blur(14px) saturate(1.15);
  transition: border-color 0.3s, box-shadow 0.3s;
}

/* Liseré rouge → or en tête de carte */
.dash-card::before {
  content: "";
  position: absolute;
  top: -1px;
  left: 1.25rem;
  right: 1.25rem;
  height: 2px;
  border-radius: 2px;
  background-image: linear-gradient(90deg, transparent, var(--primary) 20%, var(--dash-gold) 50%, var(--primary) 80%, transparent);
  opacity: 0.9;
}

/* Coins ornés : petites équerres dorées en haut à gauche et en bas à droite */
.dash-card::after {
  content: "";
  position: absolute;
  inset: 5px;
  border-radius: 0.6rem;
  pointer-events: none;
  background:
    linear-gradient(var(--dash-gold), var(--dash-gold)) top left / 10px 1px no-repeat,
    linear-gradient(var(--dash-gold), var(--dash-gold)) top left / 1px 10px no-repeat,
    linear-gradient(var(--dash-gold), var(--dash-gold)) bottom right / 10px 1px no-repeat,
    linear-gradient(var(--dash-gold), var(--dash-gold)) bottom right / 1px 10px no-repeat;
  opacity: 0.55;
}

.dash-card:hover {
  border-color: rgba(var(--accent-rgb), 0.45);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.06),
    inset 0 0 0 1px rgba(var(--accent-rgb), 0.08),
    0 22px 48px -24px rgba(var(--primary-rgb), 0.45);
}

.dash-card--accent {
  background-image:
    linear-gradient(155deg, rgba(var(--primary-rgb), 0.18), rgba(var(--accent-rgb), 0.06)),
    radial-gradient(90% 70% at 100% 100%, rgba(var(--accent-rgb), 0.08), transparent 60%);
  border-color: rgba(var(--accent-rgb), 0.38);
}

.dash-card-head {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 1rem;
  padding-bottom: 0.65rem;
}

/* Filet doré sous le titre, qui s'estompe vers la droite */
.dash-card-head::after {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  height: 1px;
  background-image: linear-gradient(90deg, rgba(var(--accent-rgb), 0.55), rgba(var(--accent-rgb), 0.12) 60%, transparent);
}

.dash-card-title {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  margin: 0;
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", serif;
  font-weight: 700;
  font-size: 0.82rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--dash-gold);
}

.dash-card-icon {
  display: inline-flex;
  color: var(--dash-gold);
  filter: drop-shadow(0 0 6px rgba(var(--accent-rgb), 0.35));
}

.dash-card-action {
  font-size: 0.8rem;
}

.dash-card-skeleton {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.dash-skeleton-line {
  height: 0.9rem;
  border-radius: 0.35rem;
  background-color: var(--bg-muted);
  animation: dash-pulse 1.5s ease-in-out infinite;
}

.dash-skeleton-line:nth-child(2) { width: 80%; }
.dash-skeleton-line:nth-child(3) { width: 55%; }

@keyframes dash-pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

/* Thème clair : or plus soutenu pour rester lisible sur fond clair */
:global(:root:not([data-theme="dark"])) .dash-card {
  --dash-gold: var(--accent-hover);
  box-shadow:
    inset 0 1px 0 rgba(255, 255, 255, 0.7),
    0 16px 36px -26px rgba(var(--primary-rgb), 0.35);
}
</style>
