<template>
  <div class="min-h-screen bg-theme-bg">
    <div class="container mx-auto px-4 py-12">
      <!-- Welcome Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl md:text-5xl font-bold text-theme-text mb-4">
          Bienvenue{{ (user?.character?.pseudo || user?.username) ? `, ${user.character?.pseudo || user.username}` : '' }} !
        </h1>
        <p class="text-lg text-theme-text-muted">
          Gestion de la guilde Erebor
        </p>
      </div>

      <!-- Admin Dashboard -->
      <div v-if="user?.roles?.includes('ROLE_ADMIN') || user?.roles?.includes('ROLE_SUPER_ADMIN') || user?.roles?.includes('ROLE_OWNERS')">
        <!-- Featured Section - Wheels -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-theme-text mb-4 flex items-center">
            <svg class="w-6 h-6 text-theme-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 5z" />
            </svg>
            Outils de Sélection
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Roue Dofus Card -->
            <RouterLink to="/wheel" class="group">
              <div class="bg-gradient-to-br from-theme-primary/20 to-theme-primary/5 border-2 border-theme-primary rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-start mb-6">
                  <div class="w-16 h-16 bg-theme-primary/20 rounded-xl flex items-center justify-center mr-6">
                    <svg class="w-8 h-8 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 5z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                      <h3 class="text-2xl font-bold text-theme-text">Roue Dofus</h3>
                      <svg class="w-6 h-6 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                    <p class="text-theme-text-muted text-lg">Sélection aléatoire parmi les membres de la guilde</p>
                  </div>
                </div>
                <div class="flex items-center text-sm text-theme-text-muted">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  <span>Tirage au sort équitable avec élimination progressive</span>
                </div>
              </div>
            </RouterLink>

            <!-- Roue des Classes Card -->
            <RouterLink to="/wheel-classes" class="group">
              <div class="bg-gradient-to-br from-theme-primary/20 to-theme-primary/5 border-2 border-theme-primary rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-start mb-6">
                  <div class="w-16 h-16 bg-theme-primary/20 rounded-xl flex items-center justify-center mr-6">
                    <svg class="w-8 h-8 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                      <h3 class="text-2xl font-bold text-theme-text">Roue des Classes</h3>
                      <svg class="w-6 h-6 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                    <p class="text-theme-text-muted text-lg">Sélection aléatoire d'une classe de Dofus</p>
                  </div>
                </div>
                <div class="flex items-center text-sm text-theme-text-muted">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  <span>Choisissez votre prochaine classe pour un nouveau personnage</span>
                </div>
              </div>
            </RouterLink>
          </div>
        </div>

        <!-- Other Tools Section -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-theme-text mb-4 flex items-center">
            <svg class="w-6 h-6 text-theme-text-muted mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Outils de Gestion
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Members Card -->
            <RouterLink to="/membres" class="group">
              <div class="bg-theme-card border border-theme-border rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-theme-primary">
                <div class="flex flex-col items-center text-center">
                  <div class="w-14 h-14 bg-theme-primary/10 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-theme-text mb-1">Membres</h3>
                  <p class="text-sm text-theme-text-muted">Gérer tous les membres</p>
                </div>
              </div>
            </RouterLink>

            <!-- Blacklist Card -->
            <RouterLink to="/blacklist" class="group">
              <div class="bg-theme-card border border-theme-border rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-theme-error">
                <div class="flex flex-col items-center text-center">
                  <div class="w-14 h-14 bg-theme-error/10 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-theme-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-theme-text mb-1">Blacklist</h3>
                  <p class="text-sm text-theme-text-muted">Joueurs bannis</p>
                </div>
              </div>
            </RouterLink>
            <!-- Avertissements Card -->
            <RouterLink 
              v-if="user?.roles?.includes('ROLE_SUPER_ADMIN') || user?.roles?.includes('ROLE_OWNERS')" 
              to="/warnings-management" 
              class="group"
            >
              <div class="bg-theme-card border border-theme-border rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-theme-warning">
                <div class="flex flex-col items-center text-center">
                  <div class="w-14 h-14 bg-theme-warning/10 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-theme-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-theme-text mb-1">Avertissements</h3>
                  <p class="text-sm text-theme-text-muted">Gestion modération</p>
                </div>
              </div>
            </RouterLink>
            <!-- Statistics Card -->
            <RouterLink to="/statistiques" class="group">
              <div class="bg-theme-card border border-theme-border rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-theme-success">
                <div class="flex flex-col items-center text-center">
                  <div class="w-14 h-14 bg-theme-success/10 rounded-xl flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-theme-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-theme-text mb-1">Statistiques</h3>
                  <p class="text-sm text-theme-text-muted">Vue d'ensemble</p>
                </div>
              </div>
            </RouterLink>
          </div>
        </div>

        <!-- Additional Tools -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-theme-text mb-4 flex items-center">
            <svg class="w-6 h-6 text-theme-text-muted mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Personnalisation
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
            <RouterLink to="/theme-customizer" class="group">
              <div class="bg-theme-card border border-theme-border rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-theme-primary">
                <div class="flex items-center">
                  <div class="w-16 h-16 bg-theme-primary/10 rounded-xl flex items-center justify-center mr-6">
                    <svg class="w-8 h-8 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <div>
                        <h3 class="text-xl font-semibold text-theme-text mb-1">Personnaliser le thème</h3>
                        <p class="text-theme-text-muted">Adaptez l'apparence de l'interface à vos préférences</p>
                      </div>
                      <svg class="w-6 h-6 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Non-Admin Dashboard -->
      <div v-else>
        <!-- Featured Section - Wheels -->
        <div class="mb-8">
          <h2 class="text-2xl font-bold text-theme-text mb-4 flex items-center">
            <svg class="w-6 h-6 text-theme-primary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 5z" />
            </svg>
            Outils de Sélection
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Roue Dofus Card -->
            <RouterLink to="/wheel" class="group">
              <div class="bg-gradient-to-br from-theme-primary/20 to-theme-primary/5 border-2 border-theme-primary rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-start mb-6">
                  <div class="w-16 h-16 bg-theme-primary/20 rounded-xl flex items-center justify-center mr-6">
                    <svg class="w-8 h-8 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 5z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                      <h3 class="text-2xl font-bold text-theme-text">Roue Dofus</h3>
                      <svg class="w-6 h-6 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                    <p class="text-theme-text-muted text-lg">Sélection aléatoire parmi les membres de la guilde</p>
                  </div>
                </div>
                <div class="flex items-center text-sm text-theme-text-muted">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                  <span>Tirage au sort équitable avec élimination progressive</span>
                </div>
              </div>
            </RouterLink>

            <!-- Roue des Classes Card -->
            <RouterLink to="/wheel-classes" class="group">
              <div class="bg-gradient-to-br from-theme-primary/20 to-theme-primary/5 border-2 border-theme-primary rounded-2xl p-8 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex items-start mb-6">
                  <div class="w-16 h-16 bg-theme-primary/20 rounded-xl flex items-center justify-center mr-6">
                    <svg class="w-8 h-8 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between mb-2">
                      <h3 class="text-2xl font-bold text-theme-text">Roue des Classes</h3>
                      <svg class="w-6 h-6 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                    <p class="text-theme-text-muted text-lg">Sélection aléatoire d'une classe de Dofus</p>
                  </div>
                </div>
                <div class="flex items-center text-sm text-theme-text-muted">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          <h2 class="text-2xl font-bold text-theme-text mb-4 flex items-center">
            <svg class="w-6 h-6 text-theme-text-muted mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            Outils et Statistiques
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Statistics Card -->
            <RouterLink to="/statistiques" class="group">
              <div class="bg-theme-card border border-theme-border rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-theme-success">
                <div class="flex items-center">
                  <div class="w-16 h-16 bg-theme-success/10 rounded-xl flex items-center justify-center mr-6">
                    <svg class="w-8 h-8 text-theme-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <div>
                        <h3 class="text-xl font-semibold text-theme-text mb-1">Statistiques</h3>
                        <p class="text-theme-text-muted">Vue d'ensemble de la guilde</p>
                      </div>
                      <svg class="w-6 h-6 text-theme-success opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            </RouterLink>

            <!-- Theme Customizer Card -->
            <RouterLink to="/theme-customizer" class="group">
              <div class="bg-theme-card border border-theme-border rounded-xl p-6 hover:shadow-lg transition-all duration-300 hover:border-theme-primary">
                <div class="flex items-center">
                  <div class="w-16 h-16 bg-theme-primary/10 rounded-xl flex items-center justify-center mr-6">
                    <svg class="w-8 h-8 text-theme-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <div>
                        <h3 class="text-xl font-semibold text-theme-text mb-1">Personnaliser le thème</h3>
                        <p class="text-theme-text-muted">Adaptez l'apparence de l'interface</p>
                      </div>
                      <svg class="w-6 h-6 text-theme-primary opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </div>
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
import { computed } from 'vue';
import { useAuthStore } from '@/stores/authStore';

const authStore = useAuthStore();
const user = computed(() => authStore.user);
</script>

<style scoped>
/* Add any custom styles here if needed */
</style>
