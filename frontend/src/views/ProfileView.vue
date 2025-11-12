<template>
  <div class="min-h-screen bg-theme-bg text-theme-text p-6">
    <div class="max-w-2xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-theme-primary mb-4">👤 Mon Profil</h1>
        <p class="text-theme-text-muted text-lg">Gérez votre personnage et vos mules</p>
      </div>

      <!-- Character Card -->
      <div v-if="user?.character" class="bg-theme-card border border-theme-border rounded-2xl p-8 shadow-2xl hover:border-theme-primary/50 transition-all duration-300">
        <!-- Character Header -->
        <div class="flex items-start justify-between mb-8">
          <div class="flex items-center space-x-5">
            <!-- Class Icon with Dropdown -->
            <ClassDropdown
              :class-name="user.character.class"
              :classes="classes"
              :entity-id="user.character.id"
              :entity-type="'character'"
              @update-class="updateCharacterClass"
            />
            <div>
              <h3 class="text-3xl font-bold text-theme-primary mb-2 drop-shadow-lg">
                {{ user.character.pseudo }}
              </h3>
              <p class="text-theme-text-muted">
                {{ user.character.ankamaPseudo }}
              </p>
            </div>
          </div>
        </div>

        <!-- Character Details -->
        <div class="grid grid-cols-2 gap-6 mb-8">
          <div class="text-center p-4 bg-theme-bg-muted/30 rounded-xl border border-theme-border">
            <p class="text-xs text-theme-text-muted uppercase tracking-wider mb-2">Classe</p>
            <p class="text-sm text-theme-text font-medium capitalize">{{ user.character.class }}</p>
          </div>
          <div class="text-center p-4 bg-theme-bg-muted/30 rounded-xl border border-theme-border">
            <p class="text-xs text-theme-text-muted uppercase tracking-wider mb-2">Rang</p>
            <p class="text-sm text-theme-text font-medium">{{ user.character.rank?.name || 'Aucun' }}</p>
          </div>
        </div>

        <!-- New Rank Unlocked Banner -->
        <div v-if="nextRankProgress?.justUnlockedRank" class="mb-8 relative overflow-hidden rounded-2xl p-8 bg-gradient-to-r from-theme-success/20 via-theme-warning/20 to-theme-success/20 border-2 border-theme-success animate-pulse">
          <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-transparent via-white/5 to-transparent animate-shimmer"></div>
          <div class="relative z-10 text-center">
            <div class="text-6xl mb-4 animate-bounce">🎉</div>
            <h3 class="text-3xl font-bold text-theme-success mb-2 animate-pulse">
              Nouveau Rang Débloqué !
            </h3>
            <p class="text-2xl font-bold text-theme-primary mb-4">
              {{ nextRankProgress.achievedRankName }}
            </p>
            <p class="text-theme-text-muted">
              Félicitations ! Vous avez atteint ce rang après {{ nextRankProgress.totalDays }} jours dans la guilde !
            </p>
          </div>
        </div>

        <!-- Next Rank Countdown -->
        <div v-if="nextRankProgress" class="mb-8 p-6 bg-gradient-to-r from-theme-primary/10 to-theme-warning/10 rounded-xl border-2 border-theme-primary/30">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
              <span class="text-2xl">⏰</span>
              <div>
                <p class="text-sm font-semibold text-theme-text uppercase tracking-wider">Prochain Rang</p>
                <p class="text-lg font-bold text-theme-primary">{{ nextRankProgress.nextRankName }}</p>
              </div>
            </div>
            <div class="text-right">
              <div v-if="nextRankProgress.daysRemaining > 0" class="text-3xl font-bold text-theme-primary">
                {{ nextRankProgress.daysRemaining }}
              </div>
              <div v-else class="text-2xl font-bold text-theme-success">
                ✓ Dispo
              </div>
              <p class="text-xs text-theme-text-muted mt-1">jour<span v-if="nextRankProgress.daysRemaining !== 1">s</span></p>
            </div>
          </div>
          
          <!-- Progress Bar -->
          <div v-if="nextRankProgress.daysRemaining > 0" class="relative h-3 bg-theme-bg-muted rounded-full overflow-hidden">
            <div 
              :class="[
                'absolute top-0 left-0 h-full bg-gradient-to-r from-theme-primary to-theme-warning',
                nextRankProgress.showAnimation ? 'transition-all duration-1000 ease-out' : ''
              ]"
              :style="{ width: `${nextRankProgress.progressPercentage}%` }"
            ></div>
          </div>
          
          <!-- Detailed Time -->
          <div v-if="nextRankProgress.daysRemaining > 0" class="mt-4 flex items-center justify-center gap-4 text-xs text-theme-text-muted">
            <div class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ nextRankProgress.totalDays }} jour{{ nextRankProgress.totalDays !== 1 ? 's' : '' }} dans la guilde</span>
            </div>
            <span>•</span>
            <div class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
              <span>{{ nextRankProgress.requiredDays }} jour{{ nextRankProgress.requiredDays !== 1 ? 's' : '' }} requis</span>
            </div>
          </div>
        </div>

        <!-- Mules Section -->
        <div v-if="user.character.mules && user.character.mules.length > 0" class="mt-8 pt-8 border-t border-theme-border">
          <h3 class="text-xl font-bold text-theme-primary mb-4 flex items-center gap-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Mes Mules ({{ user.character.mules.length }})
          </h3>
          <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div
              v-for="mule in user.character.mules"
              :key="mule.id"
              class="bg-theme-bg-muted rounded-xl p-4 border border-theme-border hover:border-theme-primary transition-all duration-300"
            >
              <div class="flex flex-col items-center text-center">
                <ClassDropdown
                  :class-name="mule.class"
                  :classes="classes"
                  :entity-id="mule.id"
                  :entity-type="'mule'"
                  @update-class="updateMuleClass"
                />
                <p class="text-sm font-semibold text-theme-text mt-2">{{ mule.pseudo }}</p>
                <p class="text-xs text-theme-text-muted">{{ mule.ankamaPseudo }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Character Message -->
      <div v-else class="bg-theme-card rounded-2xl p-12 shadow-2xl border border-theme-border text-center">
        <div class="text-6xl mb-4">👤</div>
        <h2 class="text-2xl font-bold text-theme-primary mb-2">Aucun personnage</h2>
        <p class="text-theme-text-muted">Aucun personnage principal n'est associé à votre compte.</p>
      </div>

      <!-- Upcoming Events Section (always visible) -->
      <div class="mt-8 bg-gradient-to-br from-theme-primary/10 via-theme-primary/5 to-theme-warning/10 border-2 border-theme-primary/30 rounded-2xl p-8 shadow-2xl overflow-hidden relative">
        <!-- Decorative background elements -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-theme-primary/5 rounded-full blur-3xl -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-theme-warning/5 rounded-full blur-3xl -ml-24 -mb-24"></div>
        
        <div class="relative z-10">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-theme-primary/20 rounded-xl">
                <svg class="w-8 h-8 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <h2 class="text-3xl font-bold text-theme-text">Événements à Venir</h2>
                <p class="text-theme-text-muted">Vos événements à venir auxquels vous êtes inscrit</p>
              </div>
            </div>
            <div v-if="upcomingEvents && upcomingEvents.length > 0" class="px-4 py-2 bg-theme-primary/20 rounded-full">
              <span class="text-theme-primary font-bold text-lg">{{ upcomingEvents.length }}</span>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loadingUpcomingEvents" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-10 w-10 border-b-2 border-theme-primary"></div>
            <p class="mt-4 text-theme-text-muted">Chargement des événements...</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="!upcomingEvents || upcomingEvents.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">📅</div>
            <p class="text-theme-text-muted text-lg font-medium">Aucun événement à venir</p>
            <p class="text-theme-text-muted text-sm mt-2">Inscrivez-vous à des événements pour les voir ici !</p>
          </div>

          <!-- Upcoming Events Grid -->
          <div v-else class="space-y-6">
            <!-- Next Event (Featured) -->
            <router-link
              v-if="upcomingEvents[0]"
              :to="`/events/${upcomingEvents[0].id}`"
              class="block group"
            >
              <div class="bg-theme-card border-2 border-theme-primary/50 rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-[1.02] hover:border-theme-primary">
                <div class="relative">
                  <!-- Event Image -->
                  <div v-if="upcomingEvents[0].image" class="relative h-48 md:h-64 overflow-hidden bg-gradient-to-br from-theme-primary/20 to-theme-warning/20">
                    <img
                      :src="getEventImageUrl(upcomingEvents[0].image)"
                      :alt="upcomingEvents[0].title"
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-theme-card via-transparent to-transparent"></div>
                  </div>
                  <div v-else class="h-48 md:h-64 bg-gradient-to-br from-theme-primary/30 to-theme-warning/30 flex items-center justify-center">
                    <svg class="w-24 h-24 text-theme-primary/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  
                  <!-- Countdown Badge -->
                  <div class="absolute top-4 right-4 px-4 py-2 bg-theme-primary text-white rounded-full font-bold shadow-lg flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ getTimeUntilEvent(upcomingEvents[0].date) }}</span>
                  </div>

                  <!-- Coming Soon Badge -->
                  <div class="absolute top-4 left-4 px-3 py-1 bg-theme-success/90 text-white rounded-full text-xs font-bold uppercase tracking-wider shadow-lg">
                    Prochain événement
                  </div>
                </div>

                <!-- Event Content -->
                <div class="p-6">
                  <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                      <h3 class="text-2xl font-bold text-theme-text mb-2 group-hover:text-theme-primary transition-colors">
                        {{ upcomingEvents[0].title }}
                      </h3>
                      <p class="text-theme-text-muted line-clamp-2 mb-4">{{ upcomingEvents[0].description }}</p>
                    </div>
                  </div>

                  <!-- Event Meta -->
                  <div class="flex items-center gap-6 text-sm">
                    <div class="flex items-center gap-2 text-theme-text-muted">
                      <svg class="w-5 h-5 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <span class="font-semibold">{{ formatDate(upcomingEvents[0].date) }}</span>
                    </div>
                    <div v-if="upcomingEvents[0].cashPrize" class="flex items-center gap-2 text-theme-warning font-semibold">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span>{{ upcomingEvents[0].cashPrize }} kamas</span>
                    </div>
                  </div>
                </div>
              </div>
            </router-link>

            <!-- Other Upcoming Events -->
            <div v-if="upcomingEvents.length > 1" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <router-link
                v-for="event in upcomingEvents.slice(1)"
                :key="event.id"
                :to="`/events/${event.id}`"
                class="block group"
              >
                <div class="bg-theme-card border border-theme-border rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-[1.02] hover:border-theme-primary">
                  <!-- Event Image -->
                  <div v-if="event.image" class="relative h-32 overflow-hidden bg-gradient-to-br from-theme-primary/20 to-theme-warning/20">
                    <img
                      :src="getEventImageUrl(event.image)"
                      :alt="event.title"
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-theme-card via-transparent to-transparent"></div>
                  </div>
                  <div v-else class="h-32 bg-gradient-to-br from-theme-primary/20 to-theme-warning/20 flex items-center justify-center">
                    <svg class="w-12 h-12 text-theme-primary/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>

                  <!-- Event Content -->
                  <div class="p-4">
                    <div class="flex items-start justify-between mb-2">
                      <h4 class="text-lg font-bold text-theme-text group-hover:text-theme-primary transition-colors line-clamp-1">
                        {{ event.title }}
                      </h4>
                      <div class="ml-2 px-2 py-1 bg-theme-primary/10 text-theme-primary rounded-full text-xs font-bold whitespace-nowrap">
                        {{ getTimeUntilEvent(event.date) }}
                      </div>
                    </div>
                    <p class="text-sm text-theme-text-muted line-clamp-2 mb-3">{{ event.description }}</p>
                    <div class="flex items-center gap-4 text-xs text-theme-text-muted">
                      <div class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ formatDateShort(event.date) }}
                      </div>
                      <div v-if="event.cashPrize" class="flex items-center gap-1 text-theme-warning font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ event.cashPrize }} kamas
                      </div>
                    </div>
                  </div>
                </div>
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Event Participations Section (always visible) -->
      <div class="mt-8 bg-theme-card border border-theme-border rounded-2xl p-8 shadow-2xl">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-theme-primary flex items-center gap-2">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
            Historique des Événements
          </h2>
          <div v-if="eventParticipations && eventParticipations.totalPoints !== undefined" class="text-right">
            <div class="text-sm text-theme-text-muted">Total de points</div>
            <div class="text-2xl font-bold text-theme-primary">{{ formatPoints(eventParticipations.totalPoints) }}</div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loadingParticipations" class="text-center py-8">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-theme-primary"></div>
          <p class="mt-4 text-theme-text-muted">Chargement des participations...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="!eventParticipations || !eventParticipations.participations || eventParticipations.participations.length === 0" class="text-center py-8">
          <div class="text-6xl mb-4">🎯</div>
          <p class="text-theme-text-muted text-lg">Aucune participation aux événements pour le moment</p>
          <p class="text-theme-text-muted text-sm mt-2">Participez à des événements pour gagner des points !</p>
        </div>

        <!-- Participations List -->
        <div v-else class="space-y-4">
          <div
            v-for="participation in eventParticipations.participations"
            :key="participation.id"
            class="bg-theme-bg-muted rounded-xl p-6 border border-theme-border hover:border-theme-primary transition-all duration-300"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="text-lg font-bold text-theme-text">{{ participation.event.title }}</h3>
                  <span
                    v-if="participation.rank"
                    class="px-3 py-1 rounded-full text-xs font-bold"
                    :class="{
                      'bg-yellow-500 text-white': participation.rank === 1,
                      'bg-gray-400 text-white': participation.rank === 2,
                      'bg-amber-600 text-white': participation.rank === 3,
                      'bg-theme-primary/20 text-theme-primary': participation.rank > 3
                    }"
                  >
                    #{{ participation.rank }}
                  </span>
                </div>
                <p class="text-sm text-theme-text-muted mb-3 line-clamp-2">{{ participation.event.description }}</p>
                <div class="flex items-center gap-4 text-sm text-theme-text-muted">
                  <div class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ formatDate(participation.event.date) }}
                  </div>
                  <div v-if="participation.pointsEarned" class="flex items-center gap-1 text-theme-primary font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    {{ formatPoints(participation.pointsEarned) }} pts
                  </div>
                  <div v-if="participation.prizeReceived" class="flex items-center gap-1 text-theme-warning font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ participation.prizeReceived }} kamas
                  </div>
                </div>
              </div>
              <div v-if="participation.event.image" class="ml-4">
                <img
                  :src="getEventImageUrl(participation.event.image)"
                  :alt="participation.event.title"
                  class="w-20 h-20 object-cover rounded-lg border border-theme-border"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import axios from '@/config/axios';
import ClassDropdown from '@/components/ClassDropdown.vue';
import eventService from '@/services/eventService';

const API_URL = import.meta.env.VITE_API_URL;

// Import class icons
const images = import.meta.glob('@/assets/icon_classe/*.avif', { eager: true });
const classes = {
  sram: images['/src/assets/icon_classe/sram.avif'].default,
  forgelance: images['/src/assets/icon_classe/forgelance.avif'].default,
  cra: images['/src/assets/icon_classe/cra.avif'].default,
  ecaflip: images['/src/assets/icon_classe/ecaflip.avif'].default,
  eniripsa: images['/src/assets/icon_classe/eniripsa.avif'].default,
  enutrof: images['/src/assets/icon_classe/enutrof.avif'].default,
  feca: images['/src/assets/icon_classe/feca.avif'].default,
  eliotrope: images['/src/assets/icon_classe/eliotrope.avif'].default,
  iop: images['/src/assets/icon_classe/iop.avif'].default,
  osamodas: images['/src/assets/icon_classe/osamodas.avif'].default,
  pandawa: images['/src/assets/icon_classe/pandawa.avif'].default,
  roublard: images['/src/assets/icon_classe/roublard.avif'].default,
  sacrieur: images['/src/assets/icon_classe/sacrieur.avif'].default,
  sadida: images['/src/assets/icon_classe/sadida.avif'].default,
  steamer: images['/src/assets/icon_classe/steamer.avif'].default,
  xelor: images['/src/assets/icon_classe/xelor.avif'].default,
  zobal: images['/src/assets/icon_classe/zobal.avif'].default,
  huppermage: images['/src/assets/icon_classe/huppermage.avif'].default,
  ouginak: images['/src/assets/icon_classe/ouginak.avif'].default,
};

const authStore = useAuthStore();
const user = computed(() => authStore.user);
const allRanks = ref([]);
const eventParticipations = ref(null);
const loadingParticipations = ref(false);
const upcomingEvents = ref([]);
const loadingUpcomingEvents = ref(false);

// Compute next rank progress
const nextRankProgress = computed(() => {
  if (!user.value?.character || !allRanks.value.length) return null;
  
  const character = user.value.character;
  const currentRank = character.rank;
  
  if (!currentRank || !character.recruitedAt) return null;
  
  // Only show countdown for ranks that need updates
  if (!currentRank.needUpdate) return null;
  
  // Calculate days in guild
  const recruitedDate = new Date(character.recruitedAt);
  const today = new Date();
  const totalDays = Math.floor((today - recruitedDate) / (1000 * 60 * 60 * 24));
  
  // Sort ranks by requiredDays in ascending order (lower ranks to higher ranks)
  const sortedRanks = [...allRanks.value].sort((a, b) => (a.requiredDays || 0) - (b.requiredDays || 0));
  
  // Find the highest rank the user has achieved based on their total days
  const ranksWithDays = sortedRanks.filter(r => r.requiredDays !== null);
  let achievedRank = ranksWithDays[0]; // Start with the lowest rank
  for (const rank of ranksWithDays) {
    if (totalDays >= rank.requiredDays && rank.requiredDays > achievedRank.requiredDays) {
      achievedRank = rank;
    }
  }
  
  // Find next rank (one with higher requiredDays than achieved rank)
  const nextRank = ranksWithDays.find(rank => rank.requiredDays > achievedRank.requiredDays);
  
  if (!nextRank || !nextRank.requiredDays) return null;
  
  const requiredDays = nextRank.requiredDays;
  const daysRemaining = Math.max(0, requiredDays - totalDays);
  const progressPercentage = Math.min(100, (totalDays / requiredDays) * 100);
  
  // Calculate days since reaching achieved rank
  const daysInCurrentRank = Math.max(0, totalDays - (achievedRank.requiredDays || 0));
  const showAnimation = daysInCurrentRank <= 2; // Show animation for 2 days after rank change
  
  // Check if the user has exactly reached the achieved rank
  const justUnlockedRank = totalDays >= achievedRank.requiredDays && daysInCurrentRank <= 2;
  
  return {
    nextRankName: nextRank.name,
    achievedRankName: achievedRank.name,
    totalDays,
    requiredDays,
    daysRemaining,
    progressPercentage,
    showAnimation,
    justUnlockedRank
  };
});

// Fetch all ranks
const fetchAllRanks = async () => {
  try {
    const { data } = await axios.get(`${API_URL}/ranks`);
    allRanks.value = data;
  } catch (error) {
    console.error('Error fetching ranks:', error);
  }
};

// Update character class
const updateCharacterClass = async (characterId, newClass) => {
  try {
    await axios.put(`${API_URL}/characters/${characterId}/update-class`, {
      class: newClass,
    });

    // Update the character's class locally
    if (user.value?.character) {
      user.value.character.class = newClass;
    }

    // Show success notification
    console.log('Classe mise à jour avec succès !');
  } catch (error) {
    console.error('Erreur lors de la mise à jour de la classe:', error.message);
  }
};

// Update mule class
const updateMuleClass = async (muleId, newClass) => {
  try {
    await axios.put(`${API_URL}/mules/${muleId}/update-class`, {
      class: newClass,
    });
    
    // Update the mule's class locally for instant feedback
    if (user.value?.character?.mules) {
      const mule = user.value.character.mules.find(m => m.id === muleId);
      if (mule) {
        mule.class = newClass;
      }
    }
    
    console.log('Mule class updated successfully!');
  } catch (error) {
    console.error('Error updating mule class:', error.message);
  }
};

// Format points
const formatPoints = (points) => {
  if (!points) return '0';
  const numPoints = parseFloat(points);
  return numPoints.toFixed(2);
};

// Format date
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Format date short
const formatDateShort = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diff = date - now;
  
  // If less than 24 hours, show time
  if (diff < 24 * 60 * 60 * 1000 && diff > 0) {
    return date.toLocaleTimeString('fr-FR', {
      hour: '2-digit',
      minute: '2-digit'
    });
  }
  
  // Otherwise show date
  return date.toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Get time until event (human readable)
const getTimeUntilEvent = (dateString) => {
  if (!dateString) return '';
  const eventDate = new Date(dateString);
  const now = new Date();
  const diff = eventDate - now;

  if (diff < 0) return 'Passé';

  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

  if (days > 0) {
    return `${days} j`;
  } else if (hours > 0) {
    return `${hours}h`;
  } else if (minutes > 0) {
    return `${minutes}min`;
  } else {
    return 'Bientôt';
  }
};

// Get event image URL
const getEventImageUrl = (imagePath) => {
  if (!imagePath) return '';
  // If it's already a full URL, return it
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath;
  }
  // Otherwise, prepend the API URL
  return `${API_URL}${imagePath}`;
};

// Fetch event participations
const fetchEventParticipations = async () => {
  loadingParticipations.value = true;
  try {
    const result = await eventService.getUserEventParticipations();
    if (result.success) {
      eventParticipations.value = result.data;
    } else {
      console.error('Failed to fetch event participations:', result.error);
    }
  } catch (error) {
    console.error('Error fetching event participations:', error);
  } finally {
    loadingParticipations.value = false;
  }
};

// Fetch upcoming events
const fetchUpcomingEvents = async () => {
  loadingUpcomingEvents.value = true;
  try {
    const result = await eventService.getUserUpcomingEvents();
    if (result.success) {
      upcomingEvents.value = result.data.events || [];
    } else {
      console.error('Failed to fetch upcoming events:', result.error);
    }
  } catch (error) {
    console.error('Error fetching upcoming events:', error);
  } finally {
    loadingUpcomingEvents.value = false;
  }
};

// Fetch user profile on mount to ensure we have the latest rank data
onMounted(async () => {
  await Promise.all([
    authStore.fetchUserProfile(),
    fetchAllRanks(),
    fetchEventParticipations(),
    fetchUpcomingEvents()
  ]);
});
</script>

<style scoped>
/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
</style>

