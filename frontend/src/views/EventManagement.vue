<template>
  <div class="min-h-screen bg-theme-bg">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
      <!-- Back Button -->
      <router-link
        :to="`/events/${eventId}`"
        class="inline-flex items-center space-x-2 text-theme-text-muted hover:text-theme-primary mb-6 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        <span>Retour à l'événement</span>
      </router-link>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center items-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-theme-primary"></div>
      </div>

      <!-- Management Content -->
      <div v-else-if="event">
        <h1 class="text-4xl font-bold text-theme-text mb-8">Gestion de l'événement</h1>

        <!-- Event Info -->
        <div class="bg-theme-card border border-theme-border rounded-xl p-6 mb-6">
          <h2 class="text-2xl font-bold text-theme-text mb-4">{{ event.title }}</h2>
          <div class="flex items-center space-x-4 text-theme-text-muted">
            <span>{{ formatDate(event.date) }}</span>
            <span v-if="event.cashPrize">• {{ event.cashPrize }} kamas</span>
            <span>• {{ event.participantsCount || 0 }} participant(s)</span>
          </div>
        </div>

        <!-- Participants Ranking -->
        <div v-if="event.participants && event.participants.length > 0" class="bg-theme-card border border-theme-border rounded-xl p-6 mb-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-theme-text">Classement des participants</h2>
            <button
              v-if="!event.isFinished"
              @click="markAsFinished"
              class="px-6 py-3 bg-theme-success text-white rounded-lg hover:bg-theme-success/90 transition-colors"
            >
              Marquer comme terminé
            </button>
          </div>

          <!-- Drag and Drop Ranking -->
          <div class="space-y-3">
            <div
              v-for="(participant, index) in participants"
              :key="participant.id"
              class="flex items-center space-x-4 p-4 bg-theme-bg-muted rounded-lg border border-theme-border hover:border-theme-primary transition-colors"
            >
              <!-- Rank Number -->
              <div class="flex items-center justify-center w-12 h-12 rounded-full font-bold text-lg bg-theme-primary text-white">
                {{ participant.rank || index + 1 }}
              </div>

              <!-- Participant Info -->
              <div class="flex-1">
                <div class="font-semibold text-theme-text">{{ participant.username }}</div>
                <div class="text-sm text-theme-text-muted">
                  Inscrit le {{ formatDate(participant.subscribedAt) }}
                </div>
              </div>

              <!-- Points Input -->
              <div class="w-32">
                <label class="block text-xs text-theme-text-muted mb-1">Points</label>
                <input
                  v-model.number="participant.pointsEarned"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 bg-theme-card border border-theme-border rounded-lg text-theme-text focus:ring-2 focus:ring-theme-primary focus:border-theme-primary"
                  :disabled="event.isFinished"
                />
              </div>

              <!-- Prize Input -->
              <div class="w-32">
                <label class="block text-xs text-theme-text-muted mb-1">Prix (kamas)</label>
                <input
                  v-model.number="participant.prizeReceived"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 bg-theme-card border border-theme-border rounded-lg text-theme-text focus:ring-2 focus:ring-theme-primary focus:border-theme-primary"
                  :disabled="event.isFinished"
                />
              </div>

              <!-- Rank Input -->
              <div class="w-24">
                <label class="block text-xs text-theme-text-muted mb-1">Classement</label>
                <input
                  v-model.number="participant.rank"
                  type="number"
                  min="1"
                  :max="participants.length"
                  class="w-full px-3 py-2 bg-theme-card border border-theme-border rounded-lg text-theme-text focus:ring-2 focus:ring-theme-primary focus:border-theme-primary"
                  :disabled="event.isFinished"
                />
              </div>
            </div>
          </div>

          <!-- Finalize Button -->
          <div v-if="!event.isFinished" class="mt-6 flex justify-end space-x-4">
            <button
              @click="saveRankings"
              :disabled="saving"
              class="px-6 py-3 bg-theme-primary text-white rounded-lg hover:bg-theme-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ saving ? 'Sauvegarde...' : 'Sauvegarder les classements' }}
            </button>
          </div>
        </div>

        <!-- Event Note -->
        <div class="bg-theme-card border border-theme-border rounded-xl p-6 mb-6">
          <h2 class="text-2xl font-bold text-theme-text mb-4">Note de l'événement</h2>
          <textarea
            v-model="eventNote"
            rows="6"
            class="w-full px-4 py-3 bg-theme-bg-muted border border-theme-border rounded-lg text-theme-text focus:ring-2 focus:ring-theme-primary focus:border-theme-primary resize-none"
            placeholder="Ajoutez une note sur ce qui s'est passé pendant l'événement..."
          ></textarea>
          <button
            @click="saveNote"
            :disabled="savingNote"
            class="mt-4 px-6 py-3 bg-theme-primary text-white rounded-lg hover:bg-theme-primary-hover transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ savingNote ? 'Sauvegarde...' : 'Sauvegarder la note' }}
          </button>
        </div>

        <!-- Finalize Event -->
        <div v-if="!event.isFinished" class="bg-theme-card border border-theme-border rounded-xl p-6">
          <h2 class="text-2xl font-bold text-theme-text mb-4">Finaliser l'événement</h2>
          <p class="text-theme-text-muted mb-6">
            Une fois l'événement finalisé, les participants ne pourront plus s'inscrire ou se désinscrire, et vous pourrez attribuer les classements, points et prix.
          </p>
          <button
            @click="finalizeEvent"
            :disabled="finalizing"
            class="px-6 py-3 bg-theme-warning text-white rounded-lg hover:bg-theme-warning/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ finalizing ? 'Finalisation...' : 'Finaliser l\'événement' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import eventService from '@/services/eventService';

const route = useRoute();
const eventId = computed(() => route.params.id);

const loading = ref(true);
const event = ref(null);
const participants = ref([]);
const eventNote = ref('');
const saving = ref(false);
const savingNote = ref(false);
const finalizing = ref(false);

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const fetchEvent = async () => {
  loading.value = true;
  const result = await eventService.getEvent(eventId.value);
  if (result.success) {
    event.value = result.data;
    eventNote.value = event.value.note || '';
    
    // Initialize participants with rank if not set
    if (event.value.participants) {
      participants.value = event.value.participants.map((p, index) => ({
        ...p,
        rank: p.rank || index + 1,
        pointsEarned: p.pointsEarned || null,
        prizeReceived: p.prizeReceived || null
      }));
    }
  }
  loading.value = false;
};

const saveRankings = async () => {
  saving.value = true;
  
  const rankings = participants.value.map(p => ({
    userId: p.userId,
    rank: p.rank,
    pointsEarned: p.pointsEarned ? String(p.pointsEarned) : null,
    prizeReceived: p.prizeReceived ? String(p.prizeReceived) : null
  }));

  const result = await eventService.finalizeEvent(eventId.value, {
    rankings: rankings,
    note: eventNote.value
  });

  if (result.success) {
    alert('Classements sauvegardés avec succès');
    await fetchEvent();
  } else {
    alert(result.error || 'Erreur lors de la sauvegarde');
  }

  saving.value = false;
};

const saveNote = async () => {
  savingNote.value = true;
  
  const result = await eventService.updateEvent(eventId.value, {
    note: eventNote.value
  });

  if (result.success) {
    alert('Note sauvegardée avec succès');
    event.value.note = eventNote.value;
  } else {
    alert(result.error || 'Erreur lors de la sauvegarde');
  }

  savingNote.value = false;
};

const finalizeEvent = async () => {
  if (!confirm('Êtes-vous sûr de vouloir finaliser cet événement ? Cette action est irréversible.')) {
    return;
  }

  finalizing.value = true;

  // Prepare rankings
  const rankings = participants.value.map(p => ({
    userId: p.userId,
    rank: p.rank || null,
    pointsEarned: p.pointsEarned ? String(p.pointsEarned) : null,
    prizeReceived: p.prizeReceived ? String(p.prizeReceived) : null
  }));

  const result = await eventService.finalizeEvent(eventId.value, {
    rankings: rankings,
    note: eventNote.value
  });

  if (result.success) {
    alert('Événement finalisé avec succès');
    await fetchEvent();
  } else {
    alert(result.error || 'Erreur lors de la finalisation');
  }

  finalizing.value = false;
};

const markAsFinished = () => {
  finalizeEvent();
};

onMounted(() => {
  fetchEvent();
});
</script>




