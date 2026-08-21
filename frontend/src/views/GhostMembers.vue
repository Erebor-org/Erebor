<template>
  <div class="min-h-screen">
    <Notification ref="notificationRef" />

    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <div class="inline-flex items-center gap-2.5">
          <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Membres Fantômes</h1>
          <span class="gh-page-beta-tag">Bêta</span>
        </div>
        <div class="w-24 h-1 rounded-full mx-auto" style="background-image: linear-gradient(90deg, var(--primary), var(--accent));"></div>
        <p class="text-theme-text-muted mt-4">Vote collectif pour repérer les membres actifs mais absents de la vie de guilde</p>
      </div>

      <!-- Round en cours -->
      <div class="glass-card rounded-2xl p-5 mb-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4 md:justify-between">
          <div>
            <p class="text-xs font-bold uppercase tracking-wide text-theme-text-muted mb-1">Round en cours</p>
            <p class="text-theme-text">
              Ouvert le <span class="font-semibold">{{ round ? formatDate(round.openedAt) : '—' }}</span>
              <span class="mx-2">·</span>
              Seuil : <span class="font-semibold text-theme-primary">{{ round?.threshold ?? '—' }} vote{{ round?.threshold !== 1 ? 's' : '' }}</span>
            </p>
          </div>

          <div v-if="isOwner" class="flex items-center gap-3">
            <div class="flex items-center gap-2">
              <label class="text-sm text-theme-text-muted whitespace-nowrap">Seuil</label>
              <input
                type="number"
                min="1"
                v-model.number="thresholdDraft"
                @change="submitThreshold"
                @keyup.enter="submitThreshold"
                class="w-20 bg-theme-bg-muted border border-theme-border text-theme-text rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200"
              />
              <button
                @click="submitThreshold"
                :disabled="isSubmittingThreshold || thresholdDraft === round?.threshold"
                class="px-3 py-1.5 bg-theme-bg-muted hover:bg-theme-border text-theme-text font-medium text-sm rounded-lg transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
              >
                Enregistrer
              </button>
            </div>
            <button
              @click="openCloseModal"
              class="px-4 py-2.5 bg-theme-primary hover:bg-theme-primary-hover text-white font-semibold text-sm rounded-xl transition-all duration-200 shadow-sm hover:shadow-md whitespace-nowrap"
            >
              Clôturer le mois
            </button>
          </div>
        </div>
      </div>

      <!-- Recherche / nomination -->
      <div class="glass-card rounded-2xl p-5 mb-6">
        <label class="block text-sm font-medium text-theme-text mb-2">Signaler un personnage pas encore nominé</label>
        <input
          type="text"
          v-model="nominateSearchQuery"
          placeholder="Rechercher un pseudo..."
          class="w-full bg-theme-bg-muted border border-theme-border text-theme-text rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-theme-primary focus:border-theme-primary transition-all duration-200 mb-2"
        />
        <div v-if="nominateSearchQuery" class="gh-picklist">
          <button
            v-for="character in filteredCharactersToNominate"
            :key="character.id"
            type="button"
            @click="nominateCharacter(character)"
            class="gh-pick-option"
          >
            <img :src="getClassIcon(character.class)" :alt="`Classe ${character.class}`" class="gh-pick-icon" />
            <span>{{ character.pseudo }} <span class="text-theme-text-muted">— {{ character.rank?.name }}</span></span>
          </button>
          <p v-if="filteredCharactersToNominate.length === 0" class="text-sm text-theme-text-muted px-2 py-3">Aucun personnage actif correspondant</p>
        </div>
      </div>

      <!-- Nominés du mois -->
      <div v-if="nominees.length === 0" class="glass-card rounded-2xl text-center py-16 text-theme-text-muted">
        <p class="text-lg font-medium mb-1">Aucun personnage signalé ce mois-ci</p>
        <p class="text-sm">Utilisez la recherche ci-dessus, ou le bouton "Signaler comme fantôme" sur la page Membres</p>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <div v-for="nominee in paginatedNominees" :key="nominee.id" class="glass-card gh-fiche rounded-2xl p-5">
          <span
            v-if="nominee.eligibleExclusion"
            class="gh-fiche-exclusion-pastille"
            :title="`${nominee.timesThresholdReached} mois où le seuil a été atteint — éligible à l'exclusion`"
          >
            ⚠️ {{ nominee.timesThresholdReached }}x
          </span>

          <!-- Portrait + rang, façon fiche membre -->
          <div class="fiche-portrait-wrap">
            <span class="portrait-ring w-20 h-20">
              <img :src="getClassIcon(nominee.class)" :alt="`Classe ${nominee.class}`" />
            </span>
            <span class="fiche-rank-pill"><RankBadge :rank="nominee.rank" size="sm" /></span>
          </div>

          <h3 class="fiche-name">{{ nominee.pseudo }}</h3>

          <!-- Tuiles de statistiques, même format que la fiche membre -->
          <div class="fiche-tiles">
            <div class="fiche-tile">
              <span class="l">Recruteur</span>
              <span class="v">{{ nominee.recruiter?.pseudo || '—' }}</span>
            </div>
            <div class="fiche-tile">
              <span class="l">Mules</span>
              <span class="v">{{ nominee.mulesCount }}</span>
            </div>
            <button
              @click="toggleVote(nominee)"
              class="fiche-tile gh-vote-tile"
              :class="{ 'gh-vote-tile--active': nominee.hasVoted }"
              :title="nominee.hasVoted ? 'Retirer mon vote' : 'Voter fantôme'"
            >
              <span class="l">{{ nominee.hasVoted ? 'Mon vote' : 'Voter' }}</span>
              <span class="v gh-vote-tile-value">
                <svg v-if="!nominee.hasVoted" class="gh-vote-arrow" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 3.5l7.5 8.25h-4.5v8.75h-6v-8.75h-4.5z" />
                </svg>
                <svg v-else class="gh-vote-arrow" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 20.5l-7.5-8.25h4.5V3.5h6v8.75h4.5z" />
                </svg>
                {{ nominee.voteCount }} / {{ round?.threshold ?? '—' }}
              </span>
            </button>
          </div>

          <!-- Barre de progression vers le seuil -->
          <div class="w-full">
            <div class="gh-progress">
              <div
                class="gh-progress-fill"
                :class="{ 'gh-progress-fill--reached': round && nominee.voteCount >= round.threshold }"
                :style="{ width: progressPercent(nominee) + '%' }"
              ></div>
            </div>
            <p class="gh-progress-label">
              {{ nominee.voteCount }} / {{ round?.threshold ?? '—' }} vote{{ (round?.threshold ?? 0) !== 1 ? 's' : '' }}
              <span v-if="round && nominee.voteCount >= round.threshold" class="text-theme-primary font-semibold"> — seuil atteint</span>
            </p>
          </div>

          <div v-if="nominee.eligibleExclusion" class="gh-exclusion-banner w-full">
            <span>⚠️ {{ nominee.timesThresholdReached }}<sup>e</sup> signalement — à exclure ?</span>
            <button @click="openArchiveModal(nominee)" class="gh-exclusion-action">Archiver ce personnage</button>
          </div>

          <!-- Qui a voté : toujours visible, avec icône de classe pour identifier vite -->
          <div class="gh-voters-section w-full">
            <span class="gh-voters-label">Votants ({{ nominee.voters.length }})</span>
            <div class="gh-voters-list gh-voters-list--center">
              <span v-for="voter in nominee.voters" :key="voter.id" class="gh-voter-chip">
                <img v-if="voter.class" :src="getClassIcon(voter.class)" :alt="`Classe ${voter.class}`" class="gh-voter-icon" />
                <span>{{ voter.pseudo }}</span>
              </span>
              <span v-if="nominee.voters.length === 0" class="text-xs text-theme-text-muted">Personne n'a encore voté</span>
            </div>
          </div>

          <button class="gh-history-toggle" @click="toggleHistory(nominee.id)">
            {{ expandedHistory[nominee.id] ? 'Réduire l\'historique ▲' : 'Voir l\'historique ▼' }}
          </button>

          <!-- Historique déplié -->
          <div v-if="expandedHistory[nominee.id]" class="gh-history w-full text-left">
            <p v-if="!historyByCharacter[nominee.id]" class="text-sm text-theme-text-muted">Chargement…</p>
            <p v-else-if="historyByCharacter[nominee.id].rounds.length === 0" class="text-sm text-theme-text-muted">
              Aucun mois clôturé pour ce personnage pour l'instant.
            </p>
            <div v-else class="space-y-2">
              <div v-for="pastRound in historyByCharacter[nominee.id].rounds" :key="pastRound.roundId" class="gh-history-row">
                <span class="gh-history-date">{{ formatDate(pastRound.closedAt) }}</span>
                <span :class="pastRound.thresholdReached ? 'text-theme-primary font-semibold' : 'text-theme-text-muted'">
                  {{ pastRound.voteCount }} / {{ pastRound.threshold }} vote{{ pastRound.threshold !== 1 ? 's' : '' }}
                  <span v-if="pastRound.thresholdReached">— seuil atteint</span>
                </span>
                <span class="gh-history-voters">{{ pastRound.voters.map(v => v.pseudo).join(', ') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <Pagination :page="currentPage" :total-pages="totalPages" @update:page="currentPage = $event" />

      <!-- Registre global : qui a déjà été signalé, tous mois confondus -->
      <div class="glass-card rounded-2xl p-5 mt-8 mb-6">
        <h2 class="text-lg font-serif font-bold text-theme-primary mb-1">Registre des membres fantômes</h2>
        <p class="text-sm text-theme-text-muted mb-4">Tous les personnages déjà signalés, tous mois confondus — pour repérer d'un coup d'œil qui avertir et qui exclure.</p>

        <div v-if="registry.length === 0" class="text-center py-8 text-theme-text-muted text-sm">
          Personne n'a encore été signalé.
        </div>
        <div v-else class="gh-registry">
          <div
            v-for="entry in registry"
            :key="entry.id"
            class="gh-registry-row"
            :class="{ 'gh-registry-row--archived': entry.isArchived }"
          >
            <img :src="getClassIcon(entry.class)" :alt="`Classe ${entry.class}`" class="gh-registry-icon" />

            <div class="gh-registry-identity">
              <span class="gh-registry-pseudo">{{ entry.pseudo }}</span>
              <RankBadge :rank="entry.rank" size="sm" />
            </div>

            <div class="gh-col">
              <span class="gh-col-label">Total votes</span>
              <span class="gh-col-value">{{ entry.totalVotes }}</span>
            </div>

            <div class="gh-col">
              <span class="gh-col-label">Mois atteints</span>
              <span class="gh-col-value" :class="{ 'gh-col-value--reached': entry.timesThresholdReached > 0 }">{{ entry.timesThresholdReached }}</span>
            </div>

            <div class="gh-col">
              <span class="gh-col-label">Ce mois-ci</span>
              <span class="gh-col-value">{{ entry.currentRoundVoteCount || '—' }}</span>
            </div>

            <div class="gh-registry-status">
              <span v-if="entry.isArchived" class="meta-chip">Déjà archivé</span>
              <span v-else-if="entry.eligibleExclusion" class="gh-registry-badge gh-registry-badge--exclusion">⚠️ À exclure</span>
              <span v-else-if="entry.timesThresholdReached >= 1" class="gh-registry-badge gh-registry-badge--warning">À surveiller</span>
              <span v-else class="text-theme-text-muted text-xs">—</span>
            </div>

            <button
              v-if="entry.eligibleExclusion && !entry.isArchived"
              @click="openArchiveModal(entry)"
              class="gh-exclusion-action"
            >
              Archiver
            </button>
          </div>
        </div>
      </div>

      <!-- Historique des rounds clôturés -->
      <div class="glass-card rounded-2xl p-5 mb-6">
        <h2 class="text-lg font-serif font-bold text-theme-primary mb-1">Historique des rounds clôturés</h2>
        <p class="text-sm text-theme-text-muted mb-4">Résultat mois par mois.</p>

        <div v-if="closedRounds.length === 0" class="text-center py-8 text-theme-text-muted text-sm">
          Aucun round clôturé pour l'instant.
        </div>
        <div v-else class="space-y-2">
          <div v-for="closedRound in closedRounds" :key="closedRound.id" class="gh-round-row">
            <button class="gh-round-header" @click="toggleRoundExpand(closedRound.id)">
              <span class="font-semibold">{{ formatDate(closedRound.closedAt) }}</span>
              <span class="text-theme-text-muted text-sm">
                Seuil {{ closedRound.threshold }} · {{ closedRound.nomineeCount }} nominé{{ closedRound.nomineeCount !== 1 ? 's' : '' }} · {{ closedRound.reachedThresholdCount }} au seuil
              </span>
              <span class="gh-chevron">{{ expandedRounds[closedRound.id] ? '−' : '+' }}</span>
            </button>
            <div v-if="expandedRounds[closedRound.id]" class="gh-round-detail">
              <p v-if="closedRound.reachedThreshold.length === 0" class="text-sm text-theme-text-muted">Personne n'a atteint le seuil ce mois-là.</p>
              <div v-else class="flex flex-wrap gap-2">
                <span v-for="c in closedRound.reachedThreshold" :key="c.characterId" class="meta-chip">{{ c.pseudo }} ({{ c.voteCount }})</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ConfirmModal
      :show="showCloseModal"
      title="Clôturer le mois"
      :message="closeConfirmMessage"
      confirmText="Clôturer"
      @confirm="confirmClose"
      @cancel="showCloseModal = false"
    />

    <ConfirmModal
      :show="showArchiveModal"
      title="Archiver ce personnage"
      :message="characterToArchive ? `Voulez-vous archiver ${characterToArchive.pseudo} ?` : ''"
      confirmText="Archiver"
      @confirm="confirmArchive"
      @cancel="showArchiveModal = false"
    />
  </div>
</template>

<script>
import axios from '@/config/axios';
import Notification from '@/components/NotificationCenter.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import Pagination from '@/components/Pagination.vue';
import RankBadge from '@/components/RankBadge.vue';
import { getClassIcon } from '@/config/classIcons';
import { useAuthStore } from '@/stores/authStore';
import {
  fetchCurrentGhostRound,
  voteGhost,
  unvoteGhost,
  updateGhostThreshold,
  closeGhostRound,
  fetchGhostHistory,
  fetchGhostRegistry,
  fetchGhostRounds,
} from '@/services/ghostApi';

const API_URL = import.meta.env.VITE_API_URL;

export default {
  components: {
    Notification,
    ConfirmModal,
    Pagination,
    RankBadge,
  },
  setup() {
    const authStore = useAuthStore();
    return { authStore };
  },
  data() {
    return {
      round: null,
      nominees: [],
      allCharacters: [],
      nominateSearchQuery: '',
      thresholdDraft: null,
      isSubmittingThreshold: false,
      showCloseModal: false,
      isClosing: false,
      expandedHistory: {},
      historyByCharacter: {},
      showArchiveModal: false,
      characterToArchive: null,
      currentPage: 1,
      pageSize: 24,
      registry: [],
      closedRounds: [],
      expandedRounds: {},
    };
  },
  computed: {
    isOwner() {
      const roles = this.authStore.user?.roles || [];
      return roles.includes('ROLE_OWNERS');
    },
    nominatedIds() {
      return new Set(this.nominees.map(n => n.id));
    },
    filteredCharactersToNominate() {
      const query = this.nominateSearchQuery.trim().toLowerCase();
      return this.allCharacters
        .filter(c => !c.isArchived && !this.nominatedIds.has(c.id))
        .filter(c => c.pseudo.toLowerCase().includes(query))
        .slice(0, 20);
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.nominees.length / this.pageSize));
    },
    paginatedNominees() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.nominees.slice(start, start + this.pageSize);
    },
    closeConfirmMessage() {
      if (!this.round) return '';
      const reached = this.nominees.filter(n => n.voteCount >= this.round.threshold);
      if (reached.length === 0) {
        return 'Aucun personnage n\'a atteint le seuil ce mois-ci. Clôturer ouvrira un nouveau round vide.';
      }
      const names = reached.map(n => n.pseudo).join(', ');
      return `${reached.length} personnage(s) vont être marqués comme ayant atteint le seuil ce mois-ci et rejoindront l'historique : ${names}.`;
    },
  },
  methods: {
    getClassIcon,
    formatDate(dateString) {
      if (!dateString) return '—';
      return new Date(dateString).toLocaleDateString('fr-FR');
    },
    progressPercent(nominee) {
      if (!this.round || !this.round.threshold) return 0;
      return Math.min(100, (nominee.voteCount / this.round.threshold) * 100);
    },
    async fetchRound() {
      try {
        const data = await fetchCurrentGhostRound();
        this.round = data.round;
        this.nominees = data.nominees;
        this.thresholdDraft = data.round.threshold;
      } catch (error) {
        console.error('Error fetching ghost round:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification('Erreur lors du chargement du round en cours.', 'error');
      }
    },
    async fetchRegistry() {
      try {
        this.registry = await fetchGhostRegistry();
      } catch (error) {
        console.error('Error fetching ghost registry:', error.response?.data || error.message);
      }
    },
    async fetchClosedRounds() {
      try {
        this.closedRounds = await fetchGhostRounds();
      } catch (error) {
        console.error('Error fetching ghost rounds:', error.response?.data || error.message);
      }
    },
    toggleRoundExpand(roundId) {
      this.expandedRounds[roundId] = !this.expandedRounds[roundId];
    },
    async fetchCharacters() {
      try {
        const response = await axios.get(`${API_URL}/characters/`);
        this.allCharacters = response.data;
      } catch (error) {
        console.error('Error fetching characters:', error.response?.data || error.message);
      }
    },
    async nominateCharacter(character) {
      try {
        const state = await voteGhost(character.id);
        this.nominees.push({
          id: character.id,
          pseudo: character.pseudo,
          ankamaPseudo: character.ankamaPseudo,
          class: character.class,
          rank: character.rank,
          recruiter: character.recruiter,
          mulesCount: (character.mules || []).length,
          timesThresholdReached: 0,
          eligibleExclusion: false,
          ...state,
        });
        this.nominateSearchQuery = '';
        this.$refs.notificationRef.showNotification(`${character.pseudo} signalé comme fantôme.`);
      } catch (error) {
        console.error('Error nominating character:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification('Erreur lors du signalement.', 'error');
      }
    },
    async toggleVote(nominee) {
      try {
        const state = nominee.hasVoted ? await unvoteGhost(nominee.id) : await voteGhost(nominee.id);
        const index = this.nominees.findIndex(n => n.id === nominee.id);
        if (index !== -1) {
          this.nominees[index] = { ...this.nominees[index], ...state };
        }
        this.$refs.notificationRef.showNotification(state.hasVoted ? 'Vote enregistré.' : 'Vote retiré.');
      } catch (error) {
        console.error('Error toggling vote:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification('Erreur lors du vote.', 'error');
      }
    },
    async submitThreshold() {
      // Champ vidé/valeur invalide au blur : on revient sur la valeur connue plutôt que d'envoyer n'importe quoi
      if (!this.round || !Number.isInteger(this.thresholdDraft) || this.thresholdDraft < 1) {
        this.thresholdDraft = this.round?.threshold ?? this.thresholdDraft;
        return;
      }
      if (this.thresholdDraft === this.round.threshold) {
        return;
      }
      this.isSubmittingThreshold = true;
      try {
        const updated = await updateGhostThreshold(this.thresholdDraft);
        this.round = { ...this.round, threshold: updated.threshold };
        this.$refs.notificationRef.showNotification('Seuil mis à jour.');
      } catch (error) {
        console.error('Error updating threshold:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification('Erreur lors de la mise à jour du seuil.', 'error');
      } finally {
        this.isSubmittingThreshold = false;
      }
    },
    // Si l'utilisateur a changé le champ seuil sans le sauvegarder (pas de blur, pas de clic sur
    // Enregistrer) avant de cliquer sur "Clôturer le mois", on l'enregistre d'abord pour que la
    // clôture et le résumé de confirmation utilisent bien la nouvelle valeur.
    async openCloseModal() {
      if (this.round && this.thresholdDraft !== this.round.threshold) {
        await this.submitThreshold();
      }
      this.showCloseModal = true;
    },
    async confirmClose() {
      this.isClosing = true;
      try {
        const result = await closeGhostRound();
        this.round = result.newRound;
        this.nominees = [];
        this.thresholdDraft = result.newRound.threshold;
        this.expandedHistory = {};
        this.historyByCharacter = {};
        this.showCloseModal = false;
        const count = result.reachedThreshold.length;
        this.$refs.notificationRef.showNotification(
          count > 0
            ? `Mois clôturé. ${count} personnage(s) ont atteint le seuil.`
            : 'Mois clôturé. Aucun personnage n\'a atteint le seuil.'
        );
        await Promise.all([this.fetchRegistry(), this.fetchClosedRounds()]);
      } catch (error) {
        console.error('Error closing round:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification('Erreur lors de la clôture du mois.', 'error');
      } finally {
        this.isClosing = false;
      }
    },
    async toggleHistory(characterId) {
      this.expandedHistory[characterId] = !this.expandedHistory[characterId];
      if (this.expandedHistory[characterId] && !this.historyByCharacter[characterId]) {
        try {
          const history = await fetchGhostHistory(characterId);
          this.historyByCharacter[characterId] = history;
        } catch (error) {
          console.error('Error fetching ghost history:', error.response?.data || error.message);
        }
      }
    },
    openArchiveModal(nominee) {
      this.characterToArchive = nominee;
      this.showArchiveModal = true;
    },
    async confirmArchive() {
      if (!this.characterToArchive) return;
      try {
        await axios.put(`${API_URL}/characters/${this.characterToArchive.id}/archive`, { isArchived: true });
        this.nominees = this.nominees.filter(n => n.id !== this.characterToArchive.id);
        const registryEntry = this.registry.find(entry => entry.id === this.characterToArchive.id);
        if (registryEntry) registryEntry.isArchived = true;
        this.$refs.notificationRef.showNotification(`${this.characterToArchive.pseudo} a bien été archivé.`);
      } catch (error) {
        console.error('Error archiving character:', error.response?.data || error.message);
        this.$refs.notificationRef.showNotification('Erreur lors de l\'archivage.', 'error');
      } finally {
        this.showArchiveModal = false;
        this.characterToArchive = null;
      }
    },
  },
  async mounted() {
    await this.fetchRound();
    await this.fetchCharacters();
    await this.fetchRegistry();
    await this.fetchClosedRounds();
  },
};
</script>

<style scoped>
.gh-page-beta-tag {
  display: inline-flex;
  align-items: center;
  padding: 0.15rem 0.6rem;
  border-radius: 9999px;
  border: 1px solid rgba(var(--accent-rgb), 0.4);
  color: var(--accent);
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  white-space: nowrap;
  transform: translateY(-0.3rem);
}

.gh-picklist {
  max-height: 14rem;
  overflow-y: auto;
  border: 1px solid var(--border);
  border-radius: 0.65rem;
  padding: 0.35rem;
}
.gh-pick-option {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.5rem 0.6rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  color: var(--text);
  text-align: left;
  transition: background-color 0.2s;
}
.gh-pick-option:hover {
  background-color: rgba(var(--primary-rgb), 0.08);
}
.gh-pick-icon {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 0.4rem;
  border: 1px solid var(--border);
  object-fit: cover;
  flex-shrink: 0;
}

/* ---------- Carte façon fiche membre ---------- */
.gh-fiche {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.gh-fiche-exclusion-pastille {
  position: absolute;
  top: 1rem;
  left: 1rem;
  background-color: var(--card);
  border: 1px solid var(--error);
  color: var(--error);
  border-radius: 9999px;
  padding: 0.2rem 0.55rem;
  font-size: 0.7rem;
  font-weight: 700;
  white-space: nowrap;
}

/* Reprend exactement le style des fiches de la page Membres (portrait-wrap/rank-pill/name
   sont scopés à MembersTable.vue, donc redéfinis ici à l'identique pour la cohérence visuelle) */
.fiche-portrait-wrap {
  position: relative;
  margin-bottom: 0.6rem;
}
.fiche-rank-pill {
  position: absolute;
  bottom: -0.5rem;
  left: 50%;
  transform: translateX(-50%);
  background-color: var(--card);
  border: 1px solid var(--border);
  border-radius: 9999px;
  padding: 0.2rem 0.65rem;
  white-space: nowrap;
}
.fiche-name {
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", serif;
  font-weight: 700;
  font-size: 1.4rem;
  color: var(--primary);
  margin: 0.6rem 0 0.3rem;
}

/* Tuiles de stats, identiques à celles de la fiche membre (scopées là-bas, redéfinies ici) */
.fiche-tiles {
  width: 100%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
  margin-bottom: 0.85rem;
}
.fiche-tile {
  background-color: rgba(255, 255, 255, 0.02);
  border: 1px solid var(--border);
  border-radius: 0.85rem;
  padding: 0.55rem 0.5rem;
  text-align: center;
  transition: border-color 0.2s, background-color 0.2s;
}
.fiche-tile:hover {
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.05);
}
.fiche-tile .l {
  display: block;
  font-size: 0.62rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: var(--text-muted);
}
.fiche-tile .v {
  display: block;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--text);
  margin-top: 0.15rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Tuile de vote : même gabarit que les autres tuiles, juste cliquable et colorée selon l'état */
.gh-vote-tile {
  grid-column: span 2;
}
.gh-vote-tile--active {
  border-color: var(--primary);
  background-color: rgba(var(--primary-rgb), 0.1);
}
.gh-vote-tile--active .l,
.gh-vote-tile--active .v {
  color: var(--primary);
}
.gh-vote-tile-value {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.3rem;
}
.gh-vote-arrow {
  width: 0.85rem;
  height: 0.85rem;
  flex-shrink: 0;
}

/* ---------- Barre de progression vers le seuil ---------- */
.gh-progress {
  height: 0.45rem;
  border-radius: 9999px;
  background-color: var(--bg-muted);
  overflow: hidden;
}
.gh-progress-fill {
  height: 100%;
  background-image: linear-gradient(90deg, var(--primary), var(--accent));
  transition: width 0.3s;
}
.gh-progress-fill--reached {
  background-image: linear-gradient(90deg, var(--accent), var(--primary));
}
.gh-progress-label {
  margin-top: 0.35rem;
  font-size: 0.78rem;
  color: var(--text-muted);
  text-align: center;
}

/* ---------- Votants : toujours visibles, identifiables d'un coup d'œil ---------- */
.gh-voters-section {
  margin-top: 0.85rem;
  padding-top: 0.75rem;
  border-top: 1px dashed var(--border);
}
.gh-voters-label {
  display: block;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
  margin-bottom: 0.45rem;
  text-align: center;
}
.gh-voters-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}
.gh-voters-list--center {
  justify-content: center;
}
.gh-voter-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.25rem 0.65rem 0.25rem 0.25rem;
  border-radius: 9999px;
  background-color: rgba(var(--primary-rgb), 0.06);
  border: 1px solid var(--border);
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--text);
}
.gh-voter-icon {
  width: 1.3rem;
  height: 1.3rem;
  border-radius: 9999px;
  object-fit: cover;
  border: 1px solid var(--border);
  flex-shrink: 0;
}

.gh-history-toggle {
  margin-top: 0.6rem;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--accent);
}
.gh-history-toggle:hover {
  color: var(--primary);
}

.gh-exclusion-banner {
  margin-top: 0.6rem;
  padding: 0.6rem 0.85rem;
  border-radius: 0.65rem;
  background-color: rgba(var(--error-rgb, 220, 38, 38), 0.1);
  border: 1px solid rgba(var(--error-rgb, 220, 38, 38), 0.3);
  color: var(--error);
  font-size: 0.85rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  flex-wrap: wrap;
}
.gh-exclusion-action {
  padding: 0.35rem 0.75rem;
  border-radius: 0.5rem;
  background-color: var(--error);
  color: white;
  font-size: 0.78rem;
  font-weight: 600;
  white-space: nowrap;
}
.gh-exclusion-action:hover {
  opacity: 0.9;
}

.gh-history {
  margin-top: 0.5rem;
  padding-top: 0.6rem;
  border-top: 1px solid var(--border);
}
.gh-history-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
  font-size: 0.82rem;
  color: var(--text);
}
.gh-history-date {
  font-weight: 600;
  color: var(--accent);
  min-width: 5.5rem;
}
.gh-history-voters {
  color: var(--text-muted);
}

/* ---------- Registre global ---------- */
.gh-registry-row {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.65rem 0;
  border-bottom: 1px solid var(--border);
  flex-wrap: wrap;
}
.gh-registry-row:last-child {
  border-bottom: none;
}
.gh-registry-row--archived {
  opacity: 0.55;
}

.gh-registry-icon {
  width: 2.1rem;
  height: 2.1rem;
  border-radius: 9999px;
  border: 1px solid var(--border);
  object-fit: cover;
  flex-shrink: 0;
}

.gh-registry-identity {
  min-width: 130px;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}
.gh-registry-pseudo {
  font-weight: 700;
  color: var(--primary);
}

.gh-registry-status {
  margin-left: auto;
}

.gh-registry-badge {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 9999px;
  font-size: 0.72rem;
  font-weight: 700;
  white-space: nowrap;
}
.gh-registry-badge--exclusion {
  background-color: rgba(var(--error-rgb, 220, 38, 38), 0.12);
  color: var(--error);
  border: 1px solid rgba(var(--error-rgb, 220, 38, 38), 0.3);
}
.gh-registry-badge--warning {
  background-color: rgba(var(--accent-rgb), 0.14);
  color: var(--accent);
  border: 1px solid rgba(var(--accent-rgb), 0.35);
}

/* ---------- Historique des rounds ---------- */
.gh-round-row {
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  overflow: hidden;
}
.gh-round-header {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  text-align: left;
  transition: background-color 0.2s;
}
.gh-round-header:hover {
  background-color: rgba(var(--primary-rgb), 0.05);
}
.gh-round-header .gh-chevron {
  margin-left: auto;
}
.gh-round-detail {
  padding: 0 1rem 0.85rem;
  border-top: 1px solid var(--border);
  padding-top: 0.65rem;
}
</style>
