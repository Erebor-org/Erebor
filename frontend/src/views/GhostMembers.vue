<template>
  <div class="min-h-screen">
    <Notification ref="notificationRef" />

    <div class="container mx-auto px-4 py-8">
      <!-- Page Header -->
      <div class="text-center mb-10">
        <h1 class="text-4xl md:text-5xl font-serif font-bold brand-gradient-text mb-4">Membres Fantômes</h1>
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
              @click="showCloseModal = true"
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
      <div class="glass-card rounded-2xl overflow-hidden">
        <div v-for="nominee in paginatedNominees" :key="nominee.id" class="gh-row">
          <div class="gh-row-main">
            <span class="gh-avatar">
              <img :src="getClassIcon(nominee.class)" :alt="`Classe ${nominee.class}`" />
            </span>

            <div class="gh-identity">
              <span class="gh-pseudo">{{ nominee.pseudo }}</span>
              <span class="gh-sub">
                <RankBadge :rank="nominee.rank" size="sm" />
              </span>
            </div>

            <div class="gh-col">
              <span class="gh-col-label">Recruteur</span>
              <span class="gh-col-value">{{ nominee.recruiter?.pseudo || '—' }}</span>
            </div>

            <div class="gh-col">
              <span class="gh-col-label">Mules</span>
              <span class="gh-col-value">{{ nominee.mulesCount }}</span>
            </div>

            <div class="gh-col">
              <span class="gh-col-label">Votes</span>
              <span class="gh-col-value" :class="{ 'gh-col-value--reached': round && nominee.voteCount >= round.threshold }">
                {{ nominee.voteCount }} / {{ round?.threshold ?? '—' }}
              </span>
            </div>

            <button
              @click="toggleVote(nominee)"
              class="gh-vote-btn"
              :class="{ 'gh-vote-btn--active': nominee.hasVoted }"
            >
              {{ nominee.hasVoted ? 'Retirer mon vote' : 'Voter fantôme' }}
            </button>

            <button class="gh-chevron" @click="toggleHistory(nominee.id)" :title="expandedHistory[nominee.id] ? 'Réduire' : 'Historique'">
              {{ expandedHistory[nominee.id] ? '−' : '+' }}
            </button>
          </div>

          <div v-if="nominee.eligibleExclusion" class="gh-exclusion-banner">
            <span>⚠️ {{ nominee.timesThresholdReached }}<sup>e</sup> signalement — à exclure ?</span>
            <button @click="openArchiveModal(nominee)" class="gh-exclusion-action">Archiver ce personnage</button>
          </div>

          <div class="gh-voters">
            <span v-for="voter in nominee.voters" :key="voter.id" class="meta-chip">{{ voter.pseudo }}</span>
          </div>

          <!-- Historique déplié -->
          <div v-if="expandedHistory[nominee.id]" class="gh-history">
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

        <!-- Empty State -->
        <div v-if="nominees.length === 0" class="text-center py-16 text-theme-text-muted">
          <p class="text-lg font-medium mb-1">Aucun personnage signalé ce mois-ci</p>
          <p class="text-sm">Utilisez la recherche ci-dessus, ou le bouton "Signaler comme fantôme" sur la page Membres</p>
        </div>
      </div>

      <Pagination :page="currentPage" :total-pages="totalPages" @update:page="currentPage = $event" />
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
  },
};
</script>

<style scoped>
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

.gh-row {
  border-bottom: 1px solid var(--border);
  padding: 0.85rem 1.25rem;
}
.gh-row:last-child {
  border-bottom: none;
}

.gh-row-main {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  flex-wrap: wrap;
}

.gh-avatar {
  flex-shrink: 0;
  display: inline-flex;
  padding: 2px;
  border-radius: 9999px;
  background-image: conic-gradient(from 200deg, var(--accent), transparent 40%, var(--primary), var(--accent));
}
.gh-avatar img {
  width: 2.1rem;
  height: 2.1rem;
  border-radius: 9999px;
  object-fit: cover;
  background-color: var(--card);
  display: block;
}

.gh-identity {
  min-width: 120px;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}
.gh-pseudo {
  font-weight: 700;
  color: var(--primary);
}

.gh-col {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 4.5rem;
}
.gh-col-label {
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted);
}
.gh-col-value {
  font-size: 0.84rem;
  font-weight: 600;
  color: var(--text);
}
.gh-col-value--reached {
  color: var(--primary);
}

.gh-vote-btn {
  margin-left: auto;
  padding: 0.5rem 1rem;
  border-radius: 0.65rem;
  border: 1px solid var(--primary);
  color: var(--primary);
  font-size: 0.82rem;
  font-weight: 600;
  transition: background-color 0.2s, color 0.2s;
  white-space: nowrap;
}
.gh-vote-btn:hover {
  background-color: rgba(var(--primary-rgb), 0.1);
}
.gh-vote-btn--active {
  background-color: var(--primary);
  color: white;
}
.gh-vote-btn--active:hover {
  background-color: var(--primary-hover);
}

.gh-chevron {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 9999px;
  border: 1px solid var(--border);
  color: var(--accent);
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: border-color 0.2s, background-color 0.2s;
  flex-shrink: 0;
}
.gh-chevron:hover {
  border-color: var(--accent);
  background-color: rgba(var(--accent-rgb), 0.08);
}

.gh-voters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
  margin-top: 0.6rem;
  padding-left: calc(2.1rem + 0.85rem);
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
  margin-top: 0.75rem;
  padding-top: 0.6rem;
  padding-left: calc(2.1rem + 0.85rem);
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
</style>
