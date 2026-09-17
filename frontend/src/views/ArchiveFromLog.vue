<template>
  <div class="min-h-screen">
    <Notification ref="notificationRef" />

    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <!-- Header -->
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Archivage des personnages</h1>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">
          Collez le message de départ de guilde Dofus pour archiver automatiquement les personnages concernés
        </p>
      </div>

      <!-- STEP 1: import -->
      <div v-if="step === 'import'" class="glass-card rounded-2xl p-6">
        <label for="guild-log" class="block text-sm font-medium text-theme-text mb-2">Collez ici le message Dofus</label>
        <textarea
          id="guild-log"
          v-model="logText"
          rows="12"
          placeholder="[20:39] Red-Huissier ne fait plus partie de la guilde.&#10;[20:39] Worldeater ne fait plus partie de la guilde."
          class="w-full font-mono text-sm bg-theme-bg-muted border border-theme-border rounded-lg px-3 py-2.5 text-theme-text focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 resize-y"
        ></textarea>
        <p v-if="errorMessage" class="mt-3 text-sm text-theme-error">{{ errorMessage }}</p>
        <div class="flex justify-end mt-5">
          <button
            @click="handleAnalyze"
            :disabled="!logText.trim() || isAnalyzing"
            class="inline-flex items-center px-6 py-2.5 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold text-sm rounded-xl shadow-lg shadow-theme-primary/30 transition-all duration-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none"
          >
            <span v-if="isAnalyzing" class="animate-spin mr-2">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            Analyser les départs
          </button>
        </div>
      </div>

      <!-- STEP 2: preview -->
      <div v-else-if="step === 'preview'" class="space-y-6">
        <div class="glass-card rounded-2xl p-6">
          <p class="text-lg font-serif font-bold text-theme-text mb-4">
            {{ analysis.summary.detectedCount }} départ{{ analysis.summary.detectedCount > 1 ? 's' : '' }} détecté{{ analysis.summary.detectedCount > 1 ? 's' : '' }}
            <span v-if="analysis.summary.duplicateCount > 0" class="text-sm font-normal text-theme-text-muted">
              ({{ analysis.summary.duplicateCount }} doublon{{ analysis.summary.duplicateCount > 1 ? 's' : '' }})
            </span>
          </p>
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-center">
            <div class="rounded-xl bg-theme-bg-muted border border-theme-border p-3">
              <p class="text-2xl font-bold text-theme-success">{{ analysis.summary.toArchiveCount }}</p>
              <p class="text-xs text-theme-text-muted mt-1">à archiver</p>
            </div>
            <div class="rounded-xl bg-theme-bg-muted border border-theme-border p-3">
              <p class="text-2xl font-bold text-theme-text-muted">{{ analysis.summary.alreadyArchivedCount }}</p>
              <p class="text-xs text-theme-text-muted mt-1">déjà archivés</p>
            </div>
            <div class="rounded-xl bg-theme-bg-muted border border-theme-border p-3">
              <p class="text-2xl font-bold text-theme-error">{{ analysis.summary.notFoundCount }}</p>
              <p class="text-xs text-theme-text-muted mt-1">introuvables</p>
            </div>
          </div>
          <p class="text-xs text-theme-text-muted mt-4">
            Les mules d'un personnage archivé sont automatiquement archivées avec lui.
          </p>
        </div>

        <div class="glass-card rounded-2xl p-5 relative">
          <label class="block text-sm font-medium text-theme-text mb-2">Ajouter un personnage manuellement</label>
          <input
            type="text"
            v-model="addSearchQuery"
            placeholder="Rechercher un pseudo..."
            class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200"
          />
          <div v-if="addSearchQuery.trim().length >= 2" class="mt-2 max-h-56 overflow-y-auto border border-theme-border rounded-xl p-1.5 space-y-0.5">
            <button
              v-for="candidate in addSearchSuggestions"
              :key="candidate.key"
              type="button"
              @click="addManualItem(candidate)"
              class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-sm text-theme-text hover:bg-theme-primary/10 transition-colors duration-200"
            >
              <span class="portrait-ring w-7 h-7 shrink-0">
                <img :src="getClassIcon(candidate.class)" :alt="`Classe ${candidate.class}`" />
              </span>
              <span class="flex-1 text-left truncate">{{ candidate.pseudo }}</span>
              <span
                class="meta-chip text-[0.65rem]"
                :class="candidate.type === 'mule' ? '!border-theme-accent/40 !text-theme-accent !bg-theme-accent/10' : ''"
              >
                {{ candidate.type === 'mule' ? 'Mule' : 'Personnage' }}
              </span>
            </button>
            <p v-if="addSearchSuggestions.length === 0" class="text-sm text-theme-text-muted px-2 py-3">
              Aucun résultat
            </p>
          </div>
        </div>

        <div class="space-y-3 relative" :class="{ 'opacity-50 pointer-events-none': isReanalyzing }">
          <ArchiveLogMemberCard
            v-for="item in analysis.items"
            :key="item.key"
            :item="item"
            :removable="item.status === 'to_archive'"
            @remove="removeItem"
          />
        </div>

        <div
          v-if="notFoundPseudos.length"
          class="glass-card rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
        >
          <p class="text-sm text-theme-text-muted">
            {{ notFoundPseudos.length }} pseudo(s) introuvable(s) — vérifiez l'orthographe ou l'existence en base.
          </p>
          <button
            @click="copyNotFoundPseudos"
            class="px-4 py-2 text-sm font-medium text-theme-primary bg-theme-primary/10 border border-theme-primary/40 rounded-xl hover:bg-theme-primary hover:text-white transition-all duration-300 whitespace-nowrap"
          >
            Copier les pseudos introuvables
          </button>
        </div>

        <div class="glass-card rounded-2xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          <p class="text-theme-text text-center sm:text-left">
            <span class="font-semibold text-theme-primary">{{ analysis.summary.toArchiveCount }}</span>
            personnage(s) seront archivés.
          </p>
          <div class="flex items-center gap-3">
            <button
              @click="resetToImport"
              class="px-5 py-2.5 text-sm font-medium text-theme-text bg-theme-bg-muted border border-theme-border rounded-xl hover:bg-theme-border transition-all duration-300"
            >
              Annuler
            </button>
            <button
              @click="showConfirmModal = true"
              :disabled="analysis.summary.toArchiveCount === 0"
              class="px-5 py-2.5 text-sm font-semibold text-white bg-theme-primary rounded-xl hover:bg-theme-primary-hover shadow-lg shadow-theme-primary/30 transition-all duration-300 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none"
            >
              Archiver les personnages
            </button>
          </div>
        </div>
      </div>

      <!-- STEP 3: done -->
      <div v-else-if="step === 'done'" class="space-y-6">
        <div class="glass-card rounded-2xl p-6 text-center">
          <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-theme-success/15 border border-theme-success/40 flex items-center justify-center">
            <svg class="w-7 h-7 text-theme-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <p class="text-xl font-serif font-bold text-theme-text mb-4">Archivage terminé</p>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
            <div class="rounded-xl bg-theme-bg-muted border border-theme-border p-3">
              <p class="text-2xl font-bold text-theme-text">{{ result.summary.detectedCount }}</p>
              <p class="text-xs text-theme-text-muted mt-1">pseudos détectés</p>
            </div>
            <div class="rounded-xl bg-theme-bg-muted border border-theme-border p-3">
              <p class="text-2xl font-bold text-theme-text">{{ result.summary.uniqueCount }}</p>
              <p class="text-xs text-theme-text-muted mt-1">uniques</p>
            </div>
            <div class="rounded-xl bg-theme-bg-muted border border-theme-border p-3">
              <p class="text-2xl font-bold text-theme-success">{{ result.summary.toArchiveCount }}</p>
              <p class="text-xs text-theme-text-muted mt-1">archivés</p>
            </div>
            <div class="rounded-xl bg-theme-bg-muted border border-theme-border p-3">
              <p class="text-2xl font-bold text-theme-error">{{ result.summary.notFoundCount }}</p>
              <p class="text-xs text-theme-text-muted mt-1">introuvables</p>
            </div>
          </div>
        </div>

        <div v-if="archivedItems.length">
          <p class="font-semibold text-theme-text mb-3">{{ archivedItems.length }} personnage(s) archivé(s)</p>
          <div class="space-y-3">
            <ArchiveLogMemberCard v-for="item in archivedItems" :key="item.key" :item="item" />
          </div>
        </div>

        <div v-if="alreadyArchivedItems.length">
          <p class="font-semibold text-theme-text mb-3 mt-6">{{ alreadyArchivedItems.length }} déjà archivé(s)</p>
          <div class="space-y-3">
            <ArchiveLogMemberCard v-for="item in alreadyArchivedItems" :key="item.key" :item="item" />
          </div>
        </div>

        <div v-if="notFoundItems.length">
          <p class="font-semibold text-theme-text mb-3 mt-6">{{ notFoundItems.length }} pseudo(s) introuvable(s)</p>
          <div class="space-y-3">
            <ArchiveLogMemberCard v-for="item in notFoundItems" :key="item.key" :item="item" />
          </div>
        </div>

        <div class="flex justify-center pt-2">
          <button
            @click="resetToImport"
            class="px-6 py-2.5 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold text-sm rounded-xl shadow-lg shadow-theme-primary/30 transition-all duration-300"
          >
            Nouvel archivage
          </button>
        </div>
      </div>
    </div>

    <ArchiveLogConfirmModal
      :show="showConfirmModal"
      :items="itemsToArchive"
      @close="showConfirmModal = false"
      @confirm="handleConfirmArchive"
    />
  </div>
</template>

<script>
import Notification from '@/components/NotificationCenter.vue';
import ArchiveLogConfirmModal from '@/components/ArchiveLogConfirmModal.vue';
import ArchiveLogMemberCard from '@/components/ArchiveLogMemberCard.vue';
import { getClassIcon } from '@/config/classIcons';
import { analyzeGuildLog, executeGuildLogArchive, fetchRosterForManualAdd } from '@/services/archiveLogService';

export default {
  name: 'ArchiveFromLog',
  components: {
    Notification,
    ArchiveLogConfirmModal,
    ArchiveLogMemberCard,
  },
  data() {
    return {
      step: 'import', // 'import' | 'preview' | 'done'
      logText: '',
      analysis: null,
      result: null,
      isAnalyzing: false,
      isReanalyzing: false,
      isArchiving: false,
      showConfirmModal: false,
      errorMessage: '',
      excludedKeys: [],
      additionalKeys: [],
      roster: [],
      rosterLoaded: false,
      addSearchQuery: '',
    };
  },
  computed: {
    notFoundPseudos() {
      return (this.analysis?.items || []).filter((item) => item.status === 'not_found').map((item) => item.pseudo);
    },
    itemsToArchive() {
      return (this.analysis?.items || []).filter((item) => item.status === 'to_archive');
    },
    archivedItems() {
      return (this.result?.items || []).filter((item) => item.status === 'archived');
    },
    alreadyArchivedItems() {
      return (this.result?.items || []).filter((item) => item.status === 'already_archived');
    },
    notFoundItems() {
      return (this.result?.items || []).filter((item) => item.status === 'not_found');
    },
    addSearchSuggestions() {
      const query = this.addSearchQuery.trim().toLowerCase();
      if (query.length < 2) return [];

      const presentKeys = new Set((this.analysis?.items || []).map((item) => item.key));

      return this.roster
        .filter(
          (candidate) =>
            !candidate.isArchived && !presentKeys.has(candidate.key) && candidate.pseudo.toLowerCase().includes(query)
        )
        .slice(0, 8);
    },
  },
  methods: {
    getClassIcon,
    async handleAnalyze() {
      this.errorMessage = '';
      this.isAnalyzing = true;
      try {
        this.analysis = await analyzeGuildLog(this.logText, this.excludedKeys, this.additionalKeys);
        this.step = 'preview';
        this.loadRosterIfNeeded();
      } catch (error) {
        this.errorMessage = error.response?.data?.error || "Impossible d'analyser ce log. Vérifiez le format et réessayez.";
      } finally {
        this.isAnalyzing = false;
      }
    },
    async reanalyze() {
      this.isReanalyzing = true;
      try {
        this.analysis = await analyzeGuildLog(this.logText, this.excludedKeys, this.additionalKeys);
      } catch (error) {
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || 'Impossible de mettre à jour la prévisualisation.',
          'error'
        );
      } finally {
        this.isReanalyzing = false;
      }
    },
    async loadRosterIfNeeded() {
      if (this.rosterLoaded) return;
      try {
        const characters = await fetchRosterForManualAdd();
        const flattened = [];
        characters.forEach((character) => {
          flattened.push({
            key: `character:${character.id}`,
            pseudo: character.pseudo,
            class: character.class,
            type: 'character',
            isArchived: character.isArchived,
          });
          (character.mules || []).forEach((mule) => {
            flattened.push({
              key: `mule:${mule.id}`,
              pseudo: mule.pseudo,
              class: mule.class,
              type: 'mule',
              isArchived: mule.isArchived,
              mainCharacterPseudo: character.pseudo,
            });
          });
        });
        this.roster = flattened;
        this.rosterLoaded = true;
      } catch (error) {
        // Non-blocking: the manual "add" search just won't offer suggestions.
      }
    },
    addManualItem(candidate) {
      if (!this.additionalKeys.includes(candidate.key)) {
        this.additionalKeys.push(candidate.key);
      }
      this.addSearchQuery = '';
      this.reanalyze();
    },
    removeItem(item) {
      const additionalIndex = this.additionalKeys.indexOf(item.key);
      if (additionalIndex !== -1) {
        // Manually added and then removed in the same session — just undo the addition.
        this.additionalKeys.splice(additionalIndex, 1);
      } else if (!this.excludedKeys.includes(item.key)) {
        this.excludedKeys.push(item.key);
      }
      this.reanalyze();
    },
    async handleConfirmArchive() {
      this.showConfirmModal = false;
      this.isArchiving = true;
      try {
        this.result = await executeGuildLogArchive(this.logText, this.excludedKeys, this.additionalKeys);
        this.step = 'done';
        this.$refs.notificationRef.showNotification(
          `${this.result.summary.toArchiveCount} personnage(s) archivé(s) avec succès.`
        );
      } catch (error) {
        this.$refs.notificationRef.showNotification(
          error.response?.data?.error || "Erreur lors de l'archivage.",
          'error'
        );
      } finally {
        this.isArchiving = false;
      }
    },
    resetToImport() {
      this.step = 'import';
      this.logText = '';
      this.analysis = null;
      this.result = null;
      this.errorMessage = '';
      this.excludedKeys = [];
      this.additionalKeys = [];
      this.roster = [];
      this.rosterLoaded = false;
      this.addSearchQuery = '';
    },
    async copyNotFoundPseudos() {
      try {
        await navigator.clipboard.writeText(this.notFoundPseudos.join('\n'));
        this.$refs.notificationRef.showNotification('Pseudos introuvables copiés dans le presse-papiers.');
      } catch (error) {
        this.$refs.notificationRef.showNotification('Impossible de copier les pseudos.', 'error');
      }
    },
  },
};
</script>
