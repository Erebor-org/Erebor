<template>
  <div class="min-h-screen bg-theme-bg home-glow">
    <div class="container mx-auto px-4 py-12">
      <!-- 1. Bienvenue + ma progression -->
      <div class="bento-top">
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
        <RankProgressCard :progress="dashboard?.myProgress" :loading="loading" />
      </div>

      <p v-if="loadError" class="dash-error" role="alert">
        Impossible de charger l'état de la guilde.
        <button type="button" class="dash-retry" @click="loadDashboard">Réessayer</button>
      </p>

      <!-- 2. État de la guilde -->
      <section class="dash-section" aria-label="État de la guilde">
        <div class="counter-row" :class="{ 'counter-row--two': !showRecruitedByMe && !mySeniority }">
          <StatCounter
            label="Membres"
            :value="counters.active?.value"
            :sentence="compareCount(counters.active?.delta30, 'il y a un mois')"
            :tone="toneOf(counters.active?.delta30)"
            :loading="loading"
          />
          <StatCounter
            label="Restent plus de 2 mois"
            :value="retention?.n ? outOfTen(retention.retained, retention.n) : null"
            :unit="retention?.n ? ' sur 10' : ''"
            :sentence="trendLabel(retention?.deltaPoints)"
            :tone="toneOf(retention?.deltaPoints, 3)"
            :note="retention?.n ? `${retention.retained} des ${retention.n} dernières recrues` : 'Pas encore assez de recrues'"
            :title="retentionTitle"
            :loading="loading"
          >
            <PeopleDots v-if="retention?.n" :kept="retention.retained" :total="retention.n" />
          </StatCounter>
          <StatCounter
            v-if="showRecruitedByMe"
            label="Recrutés par vous"
            :value="counters.recruitedByMe?.value"
            :sentence="counters.recruitedByMe?.delta30 ? `dont ${counters.recruitedByMe.delta30} ce mois-ci` : 'aucun ce mois-ci'"
            :tone="counters.recruitedByMe?.delta30 ? 'good' : 'flat'"
            :loading="loading"
          />
          <StatCounter
            v-else-if="mySeniority"
            label="Votre ancienneté"
            :value="ordinalFr(mySeniority.position)"
            :unit="` sur ${mySeniority.total}`"
            :sentence="mySeniority.placesGained > 0 ? `${mySeniority.placesGained} place${mySeniority.placesGained > 1 ? 's' : ''} gagnée${mySeniority.placesGained > 1 ? 's' : ''} ce mois-ci` : ''"
            tone="good"
            :note="mySeniority.total - mySeniority.position > 0 ? `plus ancien que ${mySeniority.total - mySeniority.position} membres` : 'le plus récent de la guilde'"
            title="Votre place parmi les membres actuels, du plus ancien au plus récent"
            :loading="loading"
          />
        </div>

        <div class="dash-grid dash-grid--flow">
          <FlowCard
            :flow="counters.flow30"
            :monthly="dashboard?.monthly || []"
            :undated-departures="counters.undatedDepartures || 0"
            :loading="loading"
          />
          <ActivityFeed
            title="Entrées & sorties"
            :items="feeds.movements.items"
            :has-more="feeds.movements.hasMore"
            :loading="loading"
            :loading-more="feeds.movements.loadingMore"
            empty-text="Aucune arrivée ni départ récent."
            max-height="27rem"
            @load-more="loadMore('movements')"
          />
        </div>

        <RankUpsCard
          :promotions="dashboard?.upcomingPromotions || []"
          :recent="feeds.promotions.items"
          :recruits="isAdmin ? (dashboard?.admin?.freshRecruits || []) : null"
          :has-more="feeds.promotions.hasMore"
          :loading="loading"
          :loading-more="feeds.promotions.loadingMore"
          @load-more="loadMore('promotions')"
        />

        <!-- Blocs de gestion : renvoyés par le serveur aux seuls ROLE_ADMIN et plus -->
        <GhostVoteStatus v-if="isAdmin" :ghost="dashboard?.admin?.ghost" :loading="loading" />
      </section>

      <!-- 3. Outils (blocs historiques, inchangés) -->
      <h2 class="tools-divider"><span>Outils</span></h2>

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
import { computed, reactive, ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/authStore';
import { fetchHomeDashboard, fetchActivity } from '@/services/dashboardApi';
import { compareCount, ordinalFr, outOfTen, trendLabel } from '@/utils/plainLanguage';
import PeopleDots from '@/components/home/PeopleDots.vue';
import RankProgressCard from '@/components/home/RankProgressCard.vue';
import StatCounter from '@/components/home/StatCounter.vue';
import FlowCard from '@/components/home/FlowCard.vue';
import ActivityFeed from '@/components/home/ActivityFeed.vue';
import RankUpsCard from '@/components/home/RankUpsCard.vue';
import GhostVoteStatus from '@/components/home/GhostVoteStatus.vue';
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

// État de la guilde : un seul appel, agrégé en SQL côté serveur.
const loading = ref(true);
const loadError = ref(false);
const dashboard = ref(null);
// Deux fils paginés : entrées / sorties et montées de rang.
const feeds = reactive({
  movements: { items: [], hasMore: false, loadingMore: false, pageSize: 20 },
  promotions: { items: [], hasMore: false, loadingMore: false, pageSize: 30 },
});

const counters = computed(() => dashboard.value?.counters || {});
const retention = computed(() => dashboard.value?.retention61 || null);
const mySeniority = computed(() => dashboard.value?.mySeniority || null);
const retentionTitle = computed(() => {
  const r = retention.value;
  if (!r?.n) return '';
  let text = `Parmi les ${r.n} recrues arrivées il y a 2 à ${r.cohortMonths + 2} mois, ${r.retained} sont restées au moins 2 mois.`;
  if (r.excluded) text += ` ${r.excluded} départ(s) sans date ne sont pas comptés.`;
  return text;
});

// Couleur d'une évolution : verte si ça monte, rouge si ça baisse (au-delà d'un seuil).
const toneOf = (delta, threshold = 1) => (delta == null || Math.abs(delta) < threshold ? 'flat' : delta > 0 ? 'good' : 'bad');
// « Recrutés par vous » n'a de sens que pour qui recrute.
const showRecruitedByMe = computed(() => isAdmin.value || (counters.value.recruitedByMe?.value || 0) > 0);

async function loadDashboard() {
  loading.value = true;
  loadError.value = false;
  try {
    dashboard.value = await fetchHomeDashboard();
    feeds.movements.items = dashboard.value.movements || [];
    feeds.promotions.items = dashboard.value.recentPromotions || [];
    for (const feed of Object.values(feeds)) {
      feed.hasMore = feed.items.length === feed.pageSize;
    }
  } catch (error) {
    console.error("Erreur lors du chargement du tableau de bord :", error);
    loadError.value = true;
  } finally {
    loading.value = false;
  }
}

async function loadMore(kind) {
  const feed = feeds[kind];
  feed.loadingMore = true;
  try {
    const page = await fetchActivity(kind, feed.items.length, 20);
    feed.items = [...feed.items, ...page.items];
    feed.hasMore = page.hasMore;
  } catch (error) {
    console.error("Erreur lors du chargement du fil :", error);
  } finally {
    feed.loadingMore = false;
  }
}

onMounted(loadDashboard);

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

/* ---------- Haut de page : bienvenue + ma progression ---------- */
.bento-top {
  display: grid;
  grid-template-columns: 1.6fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
  align-items: stretch;
}

.hero-tile {
  position: relative;
  overflow: hidden;
  border-radius: 0.9rem;
  padding: 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  background-image: linear-gradient(155deg, rgba(var(--primary-rgb), 0.18), rgba(var(--accent-rgb), 0.05));
  border: 1px solid rgba(var(--accent-rgb), 0.38);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05), 0 18px 40px -26px rgba(0, 0, 0, 0.65);
}

/* Même liseré rouge → or que les cartes du tableau de bord */
.hero-tile::before {
  content: "";
  position: absolute;
  top: 0;
  left: 1.5rem;
  right: 1.5rem;
  height: 2px;
  border-radius: 2px;
  background-image: linear-gradient(90deg, transparent, var(--primary) 20%, var(--accent) 50%, var(--primary) 80%, transparent);
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

/* ---------- État de la guilde ---------- */
.dash-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2.5rem;
}

.counter-row {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}

.counter-row--two {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.dash-grid {
  display: grid;
  gap: 1rem;
  align-items: stretch;
}

.dash-grid--flow {
  grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
}

.dash-grid--halves {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.dash-error {
  margin: 0 0 1rem;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  font-size: 0.85rem;
  color: var(--error);
  background-color: rgba(var(--primary-rgb), 0.08);
  border: 1px solid rgba(var(--primary-rgb), 0.25);
}

.dash-retry {
  margin-left: 0.5rem;
  text-decoration: underline;
  background: none;
  border: 0;
  color: inherit;
  cursor: pointer;
}

/* Séparateur avant les outils historiques */
.tools-divider {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin: 0 0 1.5rem;
  font-family: ui-monospace, "SF Mono", Menlo, monospace;
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--text-muted);
}

.tools-divider::before,
.tools-divider::after {
  content: "";
  flex: 1;
  height: 1px;
  background-image: linear-gradient(90deg, transparent, rgba(var(--accent-rgb), 0.45), transparent);
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

@media (max-width: 1024px) {
  .dash-grid--flow,
  .dash-grid--halves {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .bento-top {
    grid-template-columns: 1fr;
  }
  .counter-row {
    gap: 0.5rem;
  }
  .hero-tile {
    flex-direction: column;
    text-align: center;
  }
}
</style>
