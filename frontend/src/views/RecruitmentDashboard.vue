<template>
  <div class="min-h-screen bg-theme-bg rd-glow">
    <div class="container mx-auto px-4 py-10">
      <!-- En-tête : titre, périmètre, filtres -->
      <header class="rd-head">
        <div>
          <p class="rd-eyebrow">Erebor · recrutement</p>
          <h1 class="rd-title">Dashboard recrutement</h1>
          <p v-if="data?.access.scope === 'own'" class="rd-scope">
            Vous voyez uniquement vos recrues<template v-if="data.access.recruiter"> ({{ data.access.recruiter.pseudo }})</template>
            et votre position au classement.
          </p>
        </div>

        <div class="rd-filters">
          <div class="rd-week" role="group" aria-label="Semaine affichée">
            <button type="button" class="rd-btn" aria-label="Semaine précédente" @click="shiftWeek(-1)">‹</button>
            <span class="rd-week-label">{{ data ? weekLabel : '…' }}</span>
            <button type="button" class="rd-btn" aria-label="Semaine suivante" :disabled="data?.week.isCurrent" @click="shiftWeek(1)">›</button>
            <button v-if="week" type="button" class="rd-btn rd-btn--text" @click="setWeek(null)">Cette semaine</button>
          </div>
          <label class="rd-select">
            <span>Période</span>
            <select v-model.number="months" @change="load">
              <option :value="3">3 mois</option>
              <option :value="6">6 mois</option>
              <option :value="12">12 mois</option>
              <option :value="0">Depuis toujours</option>
            </select>
          </label>
          <button type="button" class="rd-btn rd-btn--tv" @click="enterTv">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
            Mode TV
          </button>
        </div>
      </header>

      <!-- Réglage réservé aux Meneurs : ce que voient les Enroleurs -->
      <div v-if="data?.access.canConfigure" class="rd-config">
        <label for="rd-admin-scope">Ce que voient les Enroleurs :</label>
        <select id="rd-admin-scope" v-model="adminScope" :disabled="savingScope" @change="saveScope">
          <option value="own">seulement leurs recrues et leur place au classement</option>
          <option value="all">toute la page</option>
          <option value="none">rien (pas d'accès)</option>
        </select>
        <span v-if="scopeSaved" class="rd-config-ok">Enregistré</span>
      </div>

      <p v-if="error" class="rd-error" role="alert">
        {{ error }}
        <button type="button" class="rd-retry" @click="load">Réessayer</button>
      </p>

      <div v-if="loading && !data" class="rd-loading">Chargement du dashboard…</div>

      <template v-if="data">
        <section class="rd-grid rd-grid--top">
          <WeekKpis :week="data.week" />
          <Leaderboard :entries="data.leaderboard.entries || []" :mine="data.leaderboard.mine" :restricted="restricted" />
        </section>
        <section class="rd-grid">
          <RecruiterTable :rows="data.recruiters" :milestones="data.milestones" :restricted="restricted" />
        </section>
        <section class="rd-grid rd-grid--halves">
          <Watchlist :items="data.watchlist" :days="data.watchDays" />
          <DepartureHistogram :departures="data.departures" :months="data.filters.months" />
        </section>
        <section class="rd-grid">
          <CohortTable :cohorts="data.cohorts" :milestones="data.milestones" />
        </section>
      </template>
    </div>

    <!-- Mode TV : plein écran, un bloc à la fois, rotation et actualisation automatiques -->
    <div v-if="tv && data" ref="tvRoot" class="rd-tv" @click="nextSlide">
      <div class="rd-tv-bar">
        <span class="rd-tv-title">Erebor · recrutement</span>
        <span class="rd-tv-dots">
          <span v-for="(s, i) in slides" :key="s" class="rd-tv-dot" :class="{ 'is-active': i === slide }"></span>
        </span>
        <span class="rd-tv-meta">actualisé à {{ refreshedAt }} · {{ clock }}</span>
        <button type="button" class="rd-btn" @click.stop="exitTv">Quitter (Échap)</button>
      </div>
      <div class="rd-tv-stage">
        <div v-if="slides[slide] === 'week'" class="rd-tv-slide rd-tv-slide--two">
          <WeekKpis :week="data.week" />
          <Leaderboard :entries="data.leaderboard.entries || []" :mine="data.leaderboard.mine" :restricted="restricted" />
        </div>
        <div v-else-if="slides[slide] === 'recruiters'" class="rd-tv-slide">
          <RecruiterTable :rows="data.recruiters" :milestones="data.milestones" :restricted="restricted" />
        </div>
        <div v-else-if="slides[slide] === 'watch'" class="rd-tv-slide rd-tv-slide--two">
          <Watchlist :items="data.watchlist" :days="data.watchDays" />
          <DepartureHistogram :departures="data.departures" :months="data.filters.months" />
        </div>
        <div v-else class="rd-tv-slide">
          <CohortTable :cohorts="data.cohorts" :milestones="data.milestones" />
        </div>
      </div>
      <div class="rd-tv-progress"><span :style="{ animationDuration: `${SLIDE_MS}ms` }" :key="slideKey"></span></div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import WeekKpis from '@/components/recruitment/WeekKpis.vue';
import Leaderboard from '@/components/recruitment/Leaderboard.vue';
import RecruiterTable from '@/components/recruitment/RecruiterTable.vue';
import Watchlist from '@/components/recruitment/Watchlist.vue';
import CohortTable from '@/components/recruitment/CohortTable.vue';
import DepartureHistogram from '@/components/recruitment/DepartureHistogram.vue';
import { fetchRecruitmentDashboard, updateAdminScope } from '@/services/recruitmentApi';
import { shiftIsoWeek, shortDate } from '@/utils/dashboardFormat';

const REFRESH_MS = 5 * 60 * 1000;
const SLIDE_MS = 20 * 1000;
const slides = ['week', 'recruiters', 'watch', 'cohorts'];

const data = ref(null);
const loading = ref(false);
const error = ref('');
const week = ref(null); // null = semaine en cours
const months = ref(12);
const adminScope = ref('own');
const savingScope = ref(false);
const scopeSaved = ref(false);

const restricted = computed(() => data.value?.access.scope === 'own');
const weekLabel = computed(() => {
  const w = data.value.week;
  return `S${Number(w.week.split('-W')[1])} · ${shortDate(w.start)} – ${shortDate(w.end)}`;
});

async function load() {
  loading.value = true;
  error.value = '';
  try {
    data.value = await fetchRecruitmentDashboard({ week: week.value, months: months.value });
    adminScope.value = data.value.access.adminScope;
    refreshedAt.value = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
  } catch (e) {
    error.value = e.response?.status === 403
      ? "Vous n'avez pas accès au dashboard recrutement."
      : 'Impossible de charger le dashboard recrutement.';
  } finally {
    loading.value = false;
  }
}

function setWeek(value) {
  week.value = value;
  load();
}

function shiftWeek(offset) {
  setWeek(shiftIsoWeek(data.value?.filters.week, offset));
}

async function saveScope() {
  savingScope.value = true;
  scopeSaved.value = false;
  try {
    await updateAdminScope(adminScope.value);
    scopeSaved.value = true;
    setTimeout(() => { scopeSaved.value = false; }, 2000);
  } catch {
    error.value = "Le réglage d'accès n'a pas pu être enregistré.";
    adminScope.value = data.value.access.adminScope;
  } finally {
    savingScope.value = false;
  }
}

// ---------------------------------------------------------------- mode TV
const tv = ref(false);
const tvRoot = ref(null);
const slide = ref(0);
const slideKey = ref(0);
const clock = ref('');
const refreshedAt = ref('');
let slideTimer = null;
let refreshTimer = null;
let clockTimer = null;

function tick() {
  clock.value = new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
}

function nextSlide() {
  slide.value = (slide.value + 1) % slides.length;
  slideKey.value++;
  clearInterval(slideTimer);
  slideTimer = setInterval(nextSlide, SLIDE_MS);
}

async function enterTv() {
  tv.value = true;
  slide.value = 0;
  tick();
  slideTimer = setInterval(nextSlide, SLIDE_MS);
  refreshTimer = setInterval(load, REFRESH_MS);
  clockTimer = setInterval(tick, 15000);
  await nextTick();
  try {
    await tvRoot.value?.requestFullscreen?.();
  } catch {
    // Plein écran refusé (navigateur, iframe) : l'affichage TV reste plein cadre.
  }
}

function exitTv() {
  tv.value = false;
  clearInterval(slideTimer);
  clearInterval(refreshTimer);
  clearInterval(clockTimer);
  if (document.fullscreenElement) {
    document.exitFullscreen?.();
  }
}

// Échap (ou toute sortie du plein écran) quitte le mode TV.
function onFullscreenChange() {
  if (!document.fullscreenElement && tv.value) exitTv();
}
function onKeydown(e) {
  if (e.key === 'Escape' && tv.value) exitTv();
}

onMounted(() => {
  load();
  document.addEventListener('fullscreenchange', onFullscreenChange);
  document.addEventListener('keydown', onKeydown);
});
onBeforeUnmount(() => {
  exitTv();
  document.removeEventListener('fullscreenchange', onFullscreenChange);
  document.removeEventListener('keydown', onKeydown);
});
</script>

<style scoped>
.rd-glow {
  background-image:
    radial-gradient(700px 420px at -5% -10%, rgba(var(--primary-rgb), 0.1), transparent 60%),
    radial-gradient(600px 460px at 105% 105%, rgba(var(--accent-rgb), 0.08), transparent 55%);
  background-repeat: no-repeat;
}

.rd-head {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.rd-eyebrow {
  margin: 0 0 0.35rem;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.68rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--accent);
}

.rd-title {
  margin: 0;
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", serif;
  font-weight: 700;
  font-size: clamp(1.6rem, 3vw, 2.3rem);
  color: var(--text);
}

.rd-scope {
  margin: 0.35rem 0 0;
  font-size: 0.85rem;
  color: var(--text-muted);
}

.rd-filters {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.6rem;
}

.rd-week {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.rd-week-label {
  min-width: 10rem;
  text-align: center;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.85rem;
  color: var(--text);
}

.rd-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.75rem;
  border-radius: 0.6rem;
  font-size: 0.85rem;
  color: var(--text);
  background-color: var(--card);
  border: 1px solid rgba(var(--accent-rgb), 0.3);
  cursor: pointer;
  transition: border-color 0.2s, background-color 0.2s;
}

.rd-btn:hover:not(:disabled) {
  border-color: var(--accent);
  background-color: rgba(var(--accent-rgb), 0.08);
}

.rd-btn:disabled {
  opacity: 0.4;
  cursor: default;
}

.rd-btn--text {
  border-color: transparent;
  background: none;
  color: var(--link);
}

.rd-btn--tv {
  color: #fff;
  background-image: linear-gradient(135deg, var(--primary), var(--primary-hover));
  border-color: transparent;
}

.rd-select {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: var(--text-muted);
}

.rd-select select,
.rd-config select {
  padding: 0.35rem 0.6rem;
  border-radius: 0.6rem;
  font-size: 0.85rem;
  color: var(--text);
  background-color: var(--card);
  border: 1px solid rgba(var(--accent-rgb), 0.3);
}

.rd-config {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.6rem;
  margin-bottom: 1.25rem;
  padding: 0.6rem 0.9rem;
  border-radius: 0.75rem;
  font-size: 0.82rem;
  color: var(--text-muted);
  background-color: rgba(var(--accent-rgb), 0.06);
  border: 1px dashed rgba(var(--accent-rgb), 0.35);
}

.rd-config-ok {
  color: var(--success);
}

.rd-error {
  margin: 0 0 1rem;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  font-size: 0.85rem;
  color: var(--error);
  background-color: rgba(var(--primary-rgb), 0.08);
  border: 1px solid rgba(var(--primary-rgb), 0.25);
}

.rd-retry {
  margin-left: 0.5rem;
  text-decoration: underline;
  background: none;
  border: 0;
  color: inherit;
  cursor: pointer;
}

.rd-loading {
  padding: 3rem 0;
  text-align: center;
  color: var(--text-muted);
}

.rd-grid {
  display: grid;
  gap: 1rem;
  margin-bottom: 1rem;
}

.rd-grid--top {
  grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
}

.rd-grid--halves {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

@media (max-width: 1024px) {
  .rd-grid--top,
  .rd-grid--halves {
    grid-template-columns: 1fr;
  }
}

/* ---------- Mode TV ---------- */
.rd-tv {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  padding: 1.25rem 2rem 0;
  background-color: var(--bg);
  background-image:
    radial-gradient(900px 500px at 0% 0%, rgba(var(--primary-rgb), 0.14), transparent 60%),
    radial-gradient(800px 500px at 100% 100%, rgba(var(--accent-rgb), 0.1), transparent 55%);
  cursor: pointer;
}

.rd-tv-bar {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-bottom: 1rem;
}

.rd-tv-title {
  font-family: ui-serif, Georgia, serif;
  font-weight: 700;
  font-size: 1.4rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--accent);
}

.rd-tv-dots {
  display: inline-flex;
  gap: 0.4rem;
}

.rd-tv-dot {
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 9999px;
  background-color: rgba(var(--accent-rgb), 0.25);
}

.rd-tv-dot.is-active {
  background-color: var(--accent);
}

.rd-tv-meta {
  margin-left: auto;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.85rem;
  color: var(--text-muted);
}

.rd-tv-stage {
  flex: 1;
  min-height: 0;
  overflow: hidden;
}

/*
 * Lisible de loin : le contenu est agrandi de 25 % par transform (et non `zoom`, que
 * Chart.js mesure mal), sa taille réelle étant réduite d'autant pour remplir l'écran.
 */
.rd-tv-slide {
  width: calc(100% / 1.25);
  height: calc(100% / 1.25);
  transform: scale(1.25);
  transform-origin: top left;
  display: grid;
  gap: 1.25rem;
}

.rd-tv-slide--two {
  grid-template-columns: minmax(0, 3fr) minmax(0, 2fr);
}

/* En TV, les listes prennent toute la hauteur disponible. */
.rd-tv-slide :deep(.rt-scroll),
.rd-tv-slide :deep(.ct-scroll),
.rd-tv-slide :deep(.lb-list),
.rd-tv-slide :deep(.wl-list),
.rd-tv-slide :deep(.week-list) {
  max-height: none;
}

.rd-tv-progress {
  height: 3px;
  margin: 0.75rem -2rem 0;
  background-color: rgba(var(--accent-rgb), 0.15);
}

.rd-tv-progress span {
  display: block;
  height: 100%;
  background-image: linear-gradient(90deg, var(--primary), var(--accent));
  animation: rd-progress linear forwards;
}

@keyframes rd-progress {
  from { width: 0; }
  to { width: 100%; }
}
</style>
