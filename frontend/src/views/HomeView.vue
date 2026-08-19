<template>
  <div class="min-h-screen bg-theme-bg home-glow">
    <div class="container mx-auto px-4 py-12">
      <!-- Bienvenue + aperçu rapide -->
      <div class="bento-top" :class="{ 'bento-top--solo': !isAdmin }">
        <div class="hero-tile">
          <div class="hero-tile-copy">
            <p class="hero-eyebrow">Erebor</p>
            <h1 class="hero-title">
              Bienvenue{{ (user?.character?.pseudo || user?.username) ? `, ${user.character?.pseudo || user.username}` : '' }}
            </h1>
            <p class="hero-subtitle">{{ characterSubtitle }}</p>
          </div>
          <div class="hero-portrait">
            <img :src="characterIcon" alt="Portrait du personnage" />
          </div>
        </div>

        <div v-if="isAdmin" class="stat-stack">
          <div class="stat-pill">
            <span class="stat-pill-value">
              <span v-if="statsLoading" class="stat-skeleton"></span>
              <span v-else>{{ stats.activeMembers }}</span>
            </span>
            <span class="stat-pill-label">Membres actifs</span>
          </div>
          <div class="stat-pill">
            <span class="stat-pill-value">
              <span v-if="statsLoading" class="stat-skeleton"></span>
              <span v-else>{{ stats.archivedMembers }}</span>
            </span>
            <span class="stat-pill-label">Membres archivés</span>
          </div>
          <div class="stat-pill">
            <span class="stat-pill-value">
              <span v-if="statsLoading" class="stat-skeleton"></span>
              <span v-else>{{ stats.recruitedByMe }}</span>
            </span>
            <span class="stat-pill-label">Recrutés par vous</span>
          </div>
        </div>
      </div>

      <!-- Admin Dashboard -->
      <div v-if="isAdmin">
        <!-- Featured Section - Wheels -->
        <div class="mb-8">
          <h2 class="section-title">
            <svg class="w-5 h-5 text-theme-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 5z" />
            </svg>
            Outils de Sélection
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Roue Dofus Card -->
            <RouterLink to="/wheel" class="group">
              <div class="wheel-tile">
                <div class="wheel-tile-ring" aria-hidden="true"></div>
                <div class="flex items-start mb-6 relative">
                  <div class="w-14 h-14 bg-theme-primary/15 rounded-xl flex items-center justify-center mr-5 flex-shrink-0">
                    <svg class="w-7 h-7 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 5z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                      <h3 class="text-xl font-serif font-bold text-theme-text">Roue des Membres</h3>
                      <svg class="w-5 h-5 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                    <p class="text-theme-text-muted">Sélection aléatoire parmi les membres de la guilde</p>
                  </div>
                </div>
                <div class="flex items-center text-sm text-theme-text-muted relative">
                  <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  <span>Tirage au sort équitable</span>
                </div>
              </div>
            </RouterLink>

            <!-- Roue des Classes Card -->
            <RouterLink to="/wheel-classes" class="group">
              <div class="wheel-tile">
                <div class="wheel-tile-ring" aria-hidden="true"></div>
                <div class="flex items-start mb-6 relative">
                  <div class="w-14 h-14 bg-theme-primary/15 rounded-xl flex items-center justify-center mr-5 flex-shrink-0">
                    <svg class="w-7 h-7 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                      <h3 class="text-xl font-serif font-bold text-theme-text">Roue des Classes</h3>
                      <svg class="w-5 h-5 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                    <p class="text-theme-text-muted">Sélection aléatoire d'une classe de Dofus</p>
                  </div>
                </div>
                <div class="flex items-center text-sm text-theme-text-muted relative">
                  <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  <span>Choisissez votre prochaine classe pour un nouveau personnage ou un reroll si vous êtes un goat</span>
                </div>
              </div>
            </RouterLink>
          </div>
        </div>

        <!-- Other Tools Section -->
        <div class="mb-8">
          <h2 class="section-title">
            <svg class="w-5 h-5 text-theme-text-muted mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Outils de Gestion
          </h2>
          <div class="tool-rail">
            <RouterLink to="/membres" class="tool-chip">
              <svg class="w-4 h-4 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span>Membres</span>
            </RouterLink>
            <RouterLink to="/blacklist" class="tool-chip">
              <svg class="w-4 h-4 text-theme-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span>Blacklist</span>
            </RouterLink>
            <RouterLink
              v-if="user?.roles?.includes('ROLE_SUPER_ADMIN') || user?.roles?.includes('ROLE_OWNERS')"
              to="/warnings-management"
              class="tool-chip"
            >
              <svg class="w-4 h-4 text-theme-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <span>Avertissements</span>
            </RouterLink>
            <RouterLink to="/statistiques" class="tool-chip">
              <svg class="w-4 h-4 text-theme-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <span>Statistiques</span>
            </RouterLink>
          </div>
        </div>

        <!-- Additional Tools -->
        <div class="mb-8">
          <h2 class="section-title">
            <svg class="w-5 h-5 text-theme-text-muted mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Personnalisation
          </h2>
          <RouterLink to="/theme-customizer" class="group">
            <div class="feature-card">
              <div class="w-14 h-14 bg-theme-primary/15 rounded-xl flex items-center justify-center mr-5 flex-shrink-0">
                <svg class="w-7 h-7 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
              </div>
              <div class="flex-1">
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-lg font-serif font-semibold text-theme-text mb-1">Personnaliser le thème</h3>
                    <p class="text-theme-text-muted text-sm">Adaptez l'apparence de l'interface à vos préférences</p>
                  </div>
                  <svg class="w-5 h-5 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </div>
            </div>
          </RouterLink>
        </div>
      </div>

      <!-- Non-Admin Dashboard -->
      <div v-else>
        <!-- Featured Section - Wheels -->
        <div class="mb-8">
          <h2 class="section-title">
            <svg class="w-5 h-5 text-theme-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 5z" />
            </svg>
            Outils de Sélection
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Roue Dofus Card -->
            <RouterLink to="/wheel" class="group">
              <div class="wheel-tile">
                <div class="wheel-tile-ring" aria-hidden="true"></div>
                <div class="flex items-start mb-6 relative">
                  <div class="w-14 h-14 bg-theme-primary/15 rounded-xl flex items-center justify-center mr-5 flex-shrink-0">
                    <svg class="w-7 h-7 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 5z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                      <h3 class="text-xl font-serif font-bold text-theme-text">Roue Dofus</h3>
                      <svg class="w-5 h-5 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                    <p class="text-theme-text-muted">Sélection aléatoire parmi les membres de la guilde</p>
                  </div>
                </div>
                <div class="flex items-center text-sm text-theme-text-muted relative">
                  <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  <span>Tirage au sort équitable avec élimination progressive</span>
                </div>
              </div>
            </RouterLink>

            <!-- Roue des Classes Card -->
            <RouterLink to="/wheel-classes" class="group">
              <div class="wheel-tile">
                <div class="wheel-tile-ring" aria-hidden="true"></div>
                <div class="flex items-start mb-6 relative">
                  <div class="w-14 h-14 bg-theme-primary/15 rounded-xl flex items-center justify-center mr-5 flex-shrink-0">
                    <svg class="w-7 h-7 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                      <h3 class="text-xl font-serif font-bold text-theme-text">Roue des Classes</h3>
                      <svg class="w-5 h-5 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                    <p class="text-theme-text-muted">Sélection aléatoire d'une classe de Dofus</p>
                  </div>
                </div>
                <div class="flex items-center text-sm text-theme-text-muted relative">
                  <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  <span>Choisissez votre prochaine classe pour un nouveau personnage</span>
                </div>
              </div>
            </RouterLink>
          </div>
        </div>

        <!-- Statistics and Theme Section -->
        <div class="mb-8">
          <h2 class="section-title">
            <svg class="w-5 h-5 text-theme-text-muted mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Outils et Statistiques
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Statistics Card -->
            <RouterLink to="/statistiques" class="group">
              <div class="feature-card">
                <div class="w-14 h-14 bg-theme-success/15 rounded-xl flex items-center justify-center mr-5 flex-shrink-0">
                  <svg class="w-7 h-7 text-theme-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-lg font-serif font-semibold text-theme-text mb-1">Statistiques</h3>
                      <p class="text-theme-text-muted text-sm">Vue d'ensemble de la guilde</p>
                    </div>
                    <svg class="w-5 h-5 text-theme-success opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                </div>
              </div>
            </RouterLink>

            <!-- Theme Customizer Card -->
            <RouterLink to="/theme-customizer" class="group">
              <div class="feature-card">
                <div class="w-14 h-14 bg-theme-primary/15 rounded-xl flex items-center justify-center mr-5 flex-shrink-0">
                  <svg class="w-7 h-7 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-lg font-serif font-semibold text-theme-text mb-1">Personnaliser le thème</h3>
                      <p class="text-theme-text-muted text-sm">Adaptez l'apparence de l'interface</p>
                    </div>
                    <svg class="w-5 h-5 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                </div>
              </div>
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import axios from 'axios';
import profile_icon from '@/assets/profile_icon.png';

// Import class icons (portrait mis en avant sur la tuile de bienvenue)
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

const API_URL = import.meta.env.VITE_API_URL;

const isAdmin = computed(() => {
  const roles = user.value?.roles || [];
  return roles.includes('ROLE_ADMIN') || roles.includes('ROLE_SUPER_ADMIN') || roles.includes('ROLE_OWNERS');
});

const characterIcon = computed(() => {
  const charClass = user.value?.character?.class;
  return (charClass && classes[charClass]) || profile_icon;
});

const characterSubtitle = computed(() => {
  const character = user.value?.character;
  if (!character) return 'Gestion de la guilde Erebor';
  const parts = [];
  if (character.class) parts.push(character.class.charAt(0).toUpperCase() + character.class.slice(1));
  if (character.rank?.name) parts.push(character.rank.name);
  return parts.length ? parts.join(' · ') : 'Gestion de la guilde Erebor';
});

// Aperçu rapide de la guilde (admins uniquement) — purement informatif,
// basé sur le même endpoint déjà utilisé par la page Membres.
const statsLoading = ref(true);
const stats = ref({ activeMembers: 0, archivedMembers: 0, recruitedByMe: 0 });

onMounted(async () => {
  if (!isAdmin.value) return;
  try {
    const charactersRes = await axios.get(`${API_URL}/characters/`);
    const characters = charactersRes.data || [];
    const myCharacterId = user.value?.character?.id;
    stats.value = {
      activeMembers: characters.filter(c => !c.isArchived).length,
      archivedMembers: characters.filter(c => c.isArchived).length,
      recruitedByMe: myCharacterId ? characters.filter(c => c.recruiter?.id === myCharacterId).length : 0,
    };
  } catch (error) {
    console.error('Erreur lors du chargement des statistiques rapides:', error);
  } finally {
    statsLoading.value = false;
  }
});
</script>

<style scoped>
/* Lueurs ambiantes très douces, rouge en haut à gauche / or en bas à droite */
.home-glow {
  background-image:
    radial-gradient(700px 420px at -5% -10%, rgba(var(--primary-rgb), 0.10), transparent 60%),
    radial-gradient(600px 460px at 105% 105%, rgba(var(--accent-rgb), 0.08), transparent 55%);
  background-repeat: no-repeat;
}

.section-title {
  @apply text-xl font-serif font-bold text-theme-text mb-4 flex items-center;
}

/* ---------- Bento du haut : bienvenue + aperçu ---------- */
.bento-top {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 1rem;
  margin-bottom: 2.5rem;
  align-items: stretch;
}

.bento-top--solo {
  grid-template-columns: 1fr;
}

.hero-tile {
  overflow: hidden;
  border-radius: 1.5rem;
  padding: 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  background-image: linear-gradient(155deg, rgba(var(--primary-rgb), 0.16), rgba(var(--accent-rgb), 0.05));
  border: 1px solid rgba(var(--accent-rgb), 0.18);
}

.hero-eyebrow {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.68rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--accent);
  margin: 0 0 0.5rem;
}

.hero-title {
  font-family: ui-serif, Georgia, Cambria, "Times New Roman", serif;
  font-weight: 700;
  font-size: clamp(1.75rem, 3vw, 2.5rem);
  color: var(--text);
  margin: 0 0 0.4rem;
  line-height: 1.1;
}

.hero-subtitle {
  color: var(--text-muted);
  font-size: 0.95rem;
  margin: 0;
}

.hero-portrait {
  flex-shrink: 0;
  width: 6.5rem;
  height: 6.5rem;
  border-radius: 9999px;
  padding: 3px;
  background-image: conic-gradient(from 180deg, var(--accent), var(--primary), var(--accent));
}

.hero-portrait img {
  width: 100%;
  height: 100%;
  border-radius: 9999px;
  object-fit: cover;
  background-color: var(--card);
}

/* ---------- Pile de statistiques compacte ---------- */
.stat-stack {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  justify-content: center;
}

.stat-pill {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.85rem 1.1rem;
  border-radius: 1rem;
  background-color: var(--card);
  border: 1px solid var(--border);
}

.stat-pill-value {
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-variant-numeric: tabular-nums;
  font-weight: 700;
  font-size: 1.35rem;
  color: var(--text);
}

.stat-pill-label {
  font-size: 0.78rem;
  color: var(--text-muted);
  text-align: right;
}

.stat-skeleton {
  display: inline-block;
  width: 2.25rem;
  height: 1.1rem;
  border-radius: 0.35rem;
  background-color: var(--bg-muted);
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

/* ---------- Tuiles "Roue" : verre + anneau dégradé ---------- */
.wheel-tile {
  position: relative;
  overflow: hidden;
  border-radius: 1.25rem;
  padding: 1.75rem;
  height: 100%;
  background-image: linear-gradient(160deg, rgba(var(--primary-rgb), 0.14), rgba(var(--accent-rgb), 0.03));
  border: 1px solid rgba(var(--primary-rgb), 0.35);
  transition: transform 0.3s, box-shadow 0.3s;
}

.group:hover .wheel-tile {
  transform: translateY(-2px);
  box-shadow: 0 20px 45px -20px rgba(var(--primary-rgb), 0.45);
}

.wheel-tile-ring {
  position: absolute;
  top: -1.5rem;
  right: -1.5rem;
  width: 5rem;
  height: 5rem;
  border-radius: 9999px;
  border: 6px solid transparent;
  border-top-color: var(--accent);
  border-right-color: var(--primary);
  opacity: 0.35;
}

/* ---------- Rangée compacte d'outils secondaires ---------- */
.tool-rail {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.tool-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  border-radius: 9999px;
  background-color: var(--card);
  border: 1px solid var(--border);
  color: var(--text);
  font-size: 0.88rem;
  font-weight: 500;
  transition: border-color 0.3s, transform 0.3s, background-color 0.3s;
}

.tool-chip:hover {
  border-color: var(--accent);
  background-color: rgba(var(--accent-rgb), 0.06);
  transform: translateY(-1px);
}

/* ---------- Carte pleine largeur (thème, statistiques...) ---------- */
.feature-card {
  display: flex;
  align-items: center;
  border-radius: 1rem;
  padding: 1.25rem 1.5rem;
  background-color: var(--card);
  border: 1px solid var(--border);
  transition: border-color 0.3s, box-shadow 0.3s;
}

.group:hover .feature-card {
  border-color: var(--primary);
  box-shadow: 0 12px 32px -18px rgba(var(--primary-rgb), 0.5);
}

@media (max-width: 640px) {
  .bento-top {
    grid-template-columns: 1fr;
  }
  .hero-tile {
    flex-direction: column;
    text-align: center;
  }
}
</style>
